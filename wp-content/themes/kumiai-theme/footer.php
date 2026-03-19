    <footer class="site-footer">
        <div class="container footer-grid">
            <div class="footer-brand">
                <div class="site-logo">
                    <i class="fa-solid fa-clock"></i> 監理ワン
                </div>
                <p>監理団体・登録支援機関の業務を徹底的に効率化。2027年育成就労制度にも自動対応する、次世代の管理システム。</p>
            </div>
            
            <div class="footer-links">
                <h3>サービス</h3>
                <?php
                if ( has_nav_menu( 'footer-service' ) ) {
                    wp_nav_menu( array('theme_location' => 'footer-service', 'container' => false) );
                } else {
                    echo '<ul>
                        <li><a href="#">特徴</a></li>
                        <li><a href="#">機能一覧</a></li>
                        <li><a href="#">選ばれる理由</a></li>
                        <li><a href="#">ブログ</a></li>
                        <li><a href="#">お問い合わせ</a></li>
                    </ul>';
                }
                ?>
            </div>
            
            <div class="footer-links">
                <h3>会社情報</h3>
                <?php
                if ( has_nav_menu( 'footer-company' ) ) {
                    wp_nav_menu( array('theme_location' => 'footer-company', 'container' => false) );
                } else {
                    echo '<ul>
                        <li><a href="#">運営会社</a></li>
                        <li><a href="#">プライバシーポリシー</a></li>
                        <li><a href="#">利用規約</a></li>
                        <li><a href="#">特定商取引法に基づく表記</a></li>
                    </ul>';
                }
                ?>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date("Y"); ?> 監理ワン. All rights reserved.</p>
        </div>
    </footer>
    <?php wp_footer(); ?>
</body>
</html>
