# Kumiai WordPress Blog

A custom WordPress blog project specifically built for the **Kumiai System (監理ワン)**. It features a modern, Japanese B2B-style card-based design and a fully integrated Docker environment for easy local development and Git-based server deployments.

## Key Features
- **Minimalist UI:** A clean, 3-column card grid design optimized for modern readability and Japanese corporate standards.
- **Auto Dummy Content:** Upon the first launch, the system automatically generates 10 SEO-optimized test posts (complete with H2/H3 headings) and configures the homepage and permalinks automatically without any manual setup.
- **Dynamic Table of Content (TOC):** A custom Vanilla JS script automatically extracts H2/H3 headings from posts and generates a sticky Table of Contents in the sidebar.
- **WP Customizer Integration:** Easily update the main Logo link and the sidebar/footer CTA (Call-to-Action) buttons directly from the WordPress Admin Dashboard without looking at code.

## Prerequisites
- [Docker Desktop](https://www.docker.com/products/docker-desktop) and Docker Compose installed on your local machine or VPS.

## Local Setup Instructions

1. Clone the repository:
   ```bash
   git clone [your-repo-link]
   cd kumiai-wp
   ```
2. Start the Docker containers:
   ```bash
   docker compose up -d
   ```
   *(If no `.env` file is present, the system defaults to using `root_password` and `wp_password` for initial setup)*

3. Open your browser:
   - **Frontend:** `http://localhost:8000/`
   - **Admin Dashboard:** `http://localhost:8000/wp-admin/`
   *(Admin credentials are the ones you create during the initial 1-minute WordPress setup screen)*

## Production Server Deployment (VPS)

Platforms like Vercel or Netlify are not suitable since WordPress requires MySQL and persisted volumes. Deploying to a Docker-capable VPS (like DigitalOcean, AWS EC2, or Vultr) is the recommended approach.

1. SSH into your VPS and ensure Git and Docker are installed.
2. Clone the repository:
   ```bash
   git clone [your-repo-link]
   cd kumiai-wp
   ```
3. Configure environment:
   ```bash
   cp .env.example .env
   ```
   Open `.env` and set:
   - Strong unique values for `DB_ROOT_PASSWORD` and `DB_PASSWORD`.
   - `WP_HOME` and `WP_SITEURL` to the exact URL visitors will use (e.g. `https://blog.example.com`). **This must be set before the first start** — WordPress hard-codes the URL it sees on first request, and changing it later requires a DB search-replace.
   - `KUMIAI_ENV=production` — turns off `WP_DEBUG`, enables `FORCE_SSL_ADMIN`, and **skips the demo-content seeding** so production starts with an empty blog.
4. Run the application:
   ```bash
   docker compose up -d
   ```
5. The built-in theme initialization script will automatically populate the database with the required posts and settings.

### What Persists Across Restarts
| Path / Volume | Type | Survives `docker compose down`? |
|---|---|---|
| MySQL data | named volume `db_data` | ✅ Yes (lost only on `docker compose down -v`) |
| `wp-content/uploads/` | bind-mount to `./wp-content/uploads/` on host | ✅ Yes — back up by tarring the host directory |
| Plugins | named volume `wp_plugins` | ✅ Yes — install via WP Admin, persists |
| Theme `kumiai-theme` | bind-mount from this repo | ✅ Yes — version-controlled in git |

### HTTPS / Domain
This stack listens on plain HTTP at `WP_PORT` (default `8000`). For production:
- Put a reverse proxy (Caddy, Traefik, or nginx) in front to terminate TLS.
- Either let the proxy listen on `:80`/`:443` and forward to `127.0.0.1:8000`, or bind WP to loopback only by editing the `ports` line in `docker-compose.yml` to `"127.0.0.1:${WP_PORT}:80"`.

### Hardening defaults
The container ships with the following WP constants baked in via `WORDPRESS_CONFIG_EXTRA`:

| Constant | Value | Effect |
|---|---|---|
| `DISALLOW_FILE_EDIT` | `true` | Hides the Theme/Plugin file editor in Dashboard — blocks the most common 1-click backdoor route. |
| `AUTOMATIC_UPDATER_DISABLED` | `true` | WP version is controlled by the Docker image tag, not by auto-update. |
| `FORCE_SSL_ADMIN` | `true` (prod only) | All admin / login traffic is forced to HTTPS. |
| `WP_DEBUG` | `false` (prod) / `true` (dev) | Debug log goes to `wp-content/debug.log`; nothing leaks to visitors. |

XML-RPC is disabled at the application level (the `xmlrpc_enabled` filter returns false and the `X-Pingback` header is removed) — this closes the most common WP brute-force vector.

## Connecting a Database GUI

MySQL is bound to `127.0.0.1:${DB_PORT:-3306}` on the host so desktop tools (DBeaver, TablePlus, HeidiSQL, MySQL Workbench, ...) can connect without exposing the DB to the LAN.

| Field | Value (defaults) |
|---|---|
| Host | `127.0.0.1` |
| Port | `3306` (override via `DB_PORT` in `.env`) |
| User | `wp_user` (or `root` for admin tasks) |
| Password | `wp_password` (or `DB_ROOT_PASSWORD` for root) |
| Database | `wordpress` |

If port 3306 is already taken on your host (another MySQL/MariaDB running locally), bump `DB_PORT=3307` in `.env` and `docker compose up -d`.

## WP-CLI

Two wrapper scripts in the repo root expose [WP-CLI](https://wp-cli.org/) against the running `kumiai_wp` container — no installation needed beyond Docker.

```bash
# bash / Git Bash / WSL / Linux / macOS
./wp user list
./wp post list --post_type=post --format=table
./wp option get siteurl
./wp search-replace 'http://old.example' 'https://new.example' --dry-run
```

```powershell
# Windows PowerShell
.\wp.ps1 user list
.\wp.ps1 post list --post_type=post --format=table
.\wp.ps1 option get siteurl
```

Common one-liners:

| Task | Command |
|---|---|
| Reset admin password | `./wp user update hanq97 --user_pass='NewPassword!'` |
| Re-trigger demo content seeding | `./wp option delete kumiai_fake_content_created_v2 kumiai_slugs_updated_v1 kumiai_content_updated_v1` |
| Tail debug log | `docker compose exec wordpress tail -f /var/www/html/wp-content/debug.log` |
| Activate the theme | `./wp theme activate kumiai-theme` |
| List active plugins | `./wp plugin list --status=active` |
| Migrate URLs after domain change | `./wp search-replace 'http://localhost:8000' 'https://blog.example.com'` |

> **PowerShell quirk:** quote any flag whose value contains commas — `wp.ps1 user list "--fields=ID,user_login"`. Bash users don't need quotes.

## Code Quality (CI)

Every push and PR runs the workflow at [.github/workflows/ci.yml](.github/workflows/ci.yml):

| Check | Tool | Catches |
|---|---|---|
| Syntax | `php-parallel-lint` | Parse errors across all theme `.php` files |
| Coding standards | `phpcs` + WordPress Coding Standards | Output not escaped, SQL injection, nonce gaps, formatting |
| Static analysis | `phpstan` + `phpstan-wordpress` (level 5) | Undefined functions, wrong types, dead code |
| Compose config | `docker compose config --quiet` | Invalid YAML / missing env vars |

Run the same checks locally (no PHP install needed — uses Docker):

```bash
# One-time: install dev deps into ./vendor/
docker run --rm -v "${PWD}:/app" -w /app composer:latest install

# Run all checks (lint + phpcs + phpstan)
docker run --rm -v "${PWD}:/app" -w /app composer:latest ci

# Auto-fix style issues
docker run --rm -v "${PWD}:/app" -w /app composer:latest phpcbf
```

Or individually: `composer lint`, `composer phpcs`, `composer phpstan`.

## Managing Menus & Navigation
- While posts are auto-generated, **Header and Footer menus** are intentionally left blank for you to customize.
- Navigate to **Admin Dashboard ➔ Appearance ➔ Menus** to create and assign links to your preferred navigational areas.

---
