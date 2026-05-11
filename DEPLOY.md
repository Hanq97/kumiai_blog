# Checklist Triển Khai Production

Hướng dẫn từng bước đưa repo này thành blog hoạt động thật trên Internet.

---

## 0. Chuẩn bị

Trước khi đụng vào server, cần có sẵn:

- [ ] Tên miền (vd `blog.kumiai.jp`) mà bạn quản lý được DNS.
- [ ] Một VPS có IP public. Cấu hình tối thiểu: **1 vCPU / 1 GB RAM / 20 GB SSD** (vd DigitalOcean $6, Vultr $6, Hetzner CX11).
- [ ] SSH key vào VPS (dùng key, không dùng password).
- [ ] Đã cài Docker + Docker Compose v2 trên VPS. Lệnh cài nhanh trên Ubuntu 22.04+:
  ```bash
  curl -fsSL https://get.docker.com | sudo sh
  sudo usermod -aG docker $USER
  # đăng xuất rồi đăng nhập lại để group có hiệu lực
  ```

---

## 1. Deploy lần đầu

```bash
# Trên VPS
git clone <repo-url-của-bạn> kumiai-wp
cd kumiai-wp
cp .env.example .env
```

Sửa file `.env`:

```ini
DB_ROOT_PASSWORD=<sinh bằng: openssl rand -base64 32>
DB_PASSWORD=<sinh bằng: openssl rand -base64 32>
DB_NAME=wordpress
DB_USER=wp_user

WP_HOME=https://blog.kumiai.jp
WP_SITEURL=https://blog.kumiai.jp
WP_PORT=8000

KUMIAI_ENV=production
```

> **Quan trọng:** `WP_HOME` / `WP_SITEURL` phải đúng URL công khai **trước khi** start lần đầu. Đổi sau sẽ phải search-replace toàn bộ DB.

Bật stack lên:

```bash
docker compose up -d
docker compose ps   # cả 2 container phải 'Up', db phải 'healthy'
docker compose logs -f wordpress   # đợi đến khi thấy "apache2 -D FOREGROUND"
```

Lúc này WordPress đã chạy tại `http://<server-ip>:8000` và hiện wizard cài đặt. **Đừng hoàn thành wizard vội** — phải bật HTTPS trước (mục tiếp theo), nếu không password admin sẽ truyền đi dạng plain text.

---

## 2. HTTPS (bắt buộc cho production)

Stack hiện tại chỉ chạy HTTP. Đặt **Caddy** đứng trước để tự động xin chứng chỉ Let's Encrypt. Caddy tốt hơn nginx ở chỗ tự gia hạn cert mà không cần cấu hình gì thêm.

### 2a. Thêm Caddy vào `docker-compose.yml`

Thêm service sau (không commit credentials):

```yaml
  caddy:
    image: caddy:2-alpine
    container_name: kumiai_caddy
    restart: always
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./Caddyfile:/etc/caddy/Caddyfile:ro
      - caddy_data:/data
      - caddy_config:/config
    depends_on:
      - wordpress

volumes:
  # ... các volume hiện có ...
  caddy_data:
  caddy_config:
```

Đồng thời sửa `ports` của service `wordpress` để chỉ bind vào loopback, không cho truy cập trực tiếp:

```yaml
  wordpress:
    # ...
    ports:
      - "127.0.0.1:${WP_PORT:-8000}:80"
```

### 2b. Tạo file `Caddyfile`

```caddyfile
{$WP_HOME_DOMAIN} {
    encode zstd gzip
    reverse_proxy kumiai_wp:80 {
        header_up X-Forwarded-Proto https
        header_up X-Forwarded-Host {host}
    }
    # Chống brute-force trang login
    @login path /wp-login.php /xmlrpc.php
    rate_limit @login 5r/m
    # Security headers
    header {
        Strict-Transport-Security "max-age=31536000; includeSubDomains"
        X-Content-Type-Options "nosniff"
        X-Frame-Options "SAMEORIGIN"
        Referrer-Policy "strict-origin-when-cross-origin"
    }
}
```

Thêm vào `.env`:

```ini
WP_HOME_DOMAIN=blog.kumiai.jp
```

### 2c. Trỏ DNS

Tạo bản ghi `A`: `blog.kumiai.jp` → IP server. Đợi đến khi `dig blog.kumiai.jp` trả về đúng IP.

### 2d. Khởi động lại và xác thực

```bash
docker compose up -d
docker compose logs caddy   # tìm dòng "certificate obtained successfully"
curl -I https://blog.kumiai.jp   # phải trả về 200 kèm header HSTS
```

Bây giờ vào `https://blog.kumiai.jp/wp-admin/install.php` để hoàn thành wizard WP.

---

## 3. Backup DB tự động (bắt buộc)

Không có backup = 1 migration sai / ổ cứng chết = blog bay hết. Thêm service backup.

### 3a. Thêm service `backup` vào `docker-compose.yml`

```yaml
  backup:
    image: databack/mysql-backup:latest
    container_name: kumiai_backup
    restart: always
    depends_on:
      db:
        condition: service_healthy
    environment:
      DB_SERVER: db
      DB_USER: root
      DB_PASS: ${DB_ROOT_PASSWORD}
      DB_NAMES: ${DB_NAME:-wordpress}
      DB_DUMP_CRON: "0 3 * * *"          # 3 giờ sáng mỗi ngày
      DB_DUMP_TARGET: /backups
      RETENTION: "7d"                     # giữ 7 ngày
      COMPRESSION: gzip
    volumes:
      - ./backups:/backups
```

```bash
mkdir -p backups
echo "backups/" >> .gitignore
docker compose up -d
```

### 3b. Copy ra ngoài (khuyến nghị)

Backup nằm cùng server thì server chết = mất luôn. Sync sang S3/Backblaze hàng đêm:

```bash
# Trên host, đặt tại /etc/cron.daily/kumiai-backup-offsite
#!/bin/bash
aws s3 sync /path/đến/kumiai-wp/backups s3://my-backups/kumiai/ --delete
```

### 3c. Test restore (làm ngay lần đầu)

```bash
# Chạy backup thủ công 1 lần
docker compose exec backup /entrypoint dump

# Xem file backup
ls -lh backups/

# Restore vào DB tạm để verify dump dùng được
docker compose exec db sh -c 'mysql -uroot -p$MYSQL_ROOT_PASSWORD -e "CREATE DATABASE test_restore;"'
gunzip -c backups/db_backup_*.gz | docker compose exec -T db sh -c 'mysql -uroot -p$MYSQL_ROOT_PASSWORD test_restore'
# Nếu thành công: xoá DB tạm đi
docker compose exec db sh -c 'mysql -uroot -p$MYSQL_ROOT_PASSWORD -e "DROP DATABASE test_restore;"'
```

Backup chưa từng restore = không phải backup.

---

## 4. Tường lửa

```bash
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow 22/tcp     # SSH
sudo ufw allow 80/tcp     # Caddy HTTP (cho Let's Encrypt challenge)
sudo ufw allow 443/tcp    # Caddy HTTPS
sudo ufw enable
sudo ufw status
```

Cổng MySQL 3306 **không** expose ra host (compose chỉ map nội bộ trong Docker network), nên không cần rule firewall. Verify:

```bash
sudo ss -tlnp | grep 3306   # không có output là đúng
```

---

## 5. Verify sau khi deploy

Chạy qua list này sau lần deploy đầu tiên:

- [ ] `https://blog.kumiai.jp` load được, không có cảnh báo cert.
- [ ] Có header HSTS: `curl -sI https://blog.kumiai.jp | grep -i strict-transport`
- [ ] HTTP redirect sang HTTPS: `curl -I http://blog.kumiai.jp` trả về `301`.
- [ ] Login admin được: `https://blog.kumiai.jp/wp-admin/`
- [ ] WP Admin → Tools → Site Health → không có lỗi critical.
- [ ] WP Admin → Appearance → Editor: **"Theme File Editor" KHÔNG có** (chứng tỏ `DISALLOW_FILE_EDIT` đang chạy).
- [ ] `curl https://blog.kumiai.jp/xmlrpc.php` trả về `XML-RPC services are disabled`.
- [ ] Upload thử 1 ảnh trong WP Admin → file phải xuất hiện trong `wp-content/uploads/<năm>/<tháng>/`.
- [ ] `docker compose exec backup /entrypoint dump` sinh ra file `.sql.gz` mới trong `./backups/`.

---

## 6. Vận hành thường ngày

### Deploy thay đổi code theme

```bash
git pull
docker compose restart wordpress   # bind-mount tự cập nhật, không cần rebuild
```

(Không động vào dữ liệu. Theme bind-mount thẳng từ working tree git.)

### Update WordPress / MySQL image

Sửa tag image trong `docker-compose.yml` (vd `wordpress:6.8-php8.3-apache`), rồi:

```bash
docker compose pull
docker compose up -d
```

**Trước khi** làm trên prod: backup trước, test trên staging trước.

### Cài plugin

Vào WP Admin → Plugins. Plugin được lưu trong named volume `wp_plugins`, không mất khi restart container.

### Restore từ backup

```bash
# Chọn file backup
BACKUP=backups/db_backup_2026-05-12T03-00-00.sql.gz

# Drop và tạo lại DB
docker compose exec db sh -c 'mysql -uroot -p$MYSQL_ROOT_PASSWORD -e "DROP DATABASE wordpress; CREATE DATABASE wordpress;"'

# Restore
gunzip -c $BACKUP | docker compose exec -T db sh -c 'mysql -uroot -p$MYSQL_ROOT_PASSWORD wordpress'
```

### Chuyển sang server khác

```bash
# Trên server cũ
tar czf kumiai-migrate.tar.gz wp-content/uploads backups/$(ls -t backups/ | head -1)
scp kumiai-migrate.tar.gz user@server-mới:/tmp/

# Trên server mới: git clone repo, cp .env, giải nén tarball đè lên working tree,
# rồi chạy lệnh restore ở phần "Restore từ backup" bên trên.
```

---

## 7. Các lỗi thường gặp

| Triệu chứng | Nguyên nhân | Cách khắc phục |
|---|---|---|
| `docker compose up` treo ở `Waiting` của db | Volume DB hỏng hoặc password không khớp với volume cũ | `docker compose down -v` **(XOÁ DB!)** hoặc sửa password khớp với volume cũ |
| Caddy log `failed to obtain certificate` | DNS chưa lan ra hết, hoặc firewall chặn cổng 80 | Đợi DNS, check `ufw status` |
| WP redirect về `localhost:8000` thay vì domain | `WP_HOME` / `WP_SITEURL` chưa set trong `.env`, hoặc DB cũ còn giá trị cũ | Set env var + restart, hoặc `docker compose exec db ...` để update trực tiếp bảng `wp_options` |
| Browser cảnh báo `Mixed content` | Có URL `http://` hard-code trong post cũ | Cài plugin "Better Search Replace", thay `http://url-cũ` → `https://url-mới` |
| Site chậm | Chưa có cache layer | Cài "WP Super Cache" hoặc thêm cache plugin cho Caddy |
| Admin không upload được ảnh | `wp-content/uploads` do root sở hữu | `sudo chown -R www-data:www-data wp-content/uploads` trên host (Apache trong container chạy uid 33) |
