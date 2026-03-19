<?php
// Enqueue styles
function kumiai_enqueue_styles() {
    wp_enqueue_style( 'kumiai-style', get_stylesheet_uri(), array(), wp_get_theme()->get('Version') );
}
add_action( 'wp_enqueue_scripts', 'kumiai_enqueue_styles' );

// Setup theme features (Menus, Thumbnails)
function kumiai_theme_setup() {
    add_theme_support( 'post-thumbnails' );
    register_nav_menus( array(
        'primary-menu'   => 'Header Main Menu',
        'footer-service' => 'Footer Service Menu',
        'footer-company' => 'Footer Company Menu',
    ) );
}
add_action( 'after_setup_theme', 'kumiai_theme_setup' );

// Auto-create dummy content (Version 2)
function kumiai_auto_create_content_v2() {
    if ( ! get_option( 'kumiai_fake_content_created_v2' ) ) {
        if ( ! function_exists( 'post_exists' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/post.php' );
        }

        $fake_posts = array(
            array('post_title' => '登録支援機関向け：定期報告書の新フォーマット対応について', 'post_content' => "入管庁より発表された定期報告書の新フォーマットについて、変更点と対応方法を解説します。システムは既に新フォーマットに対応済みです。\n\nExcelで管理していると、これらのフォーマット変更を手作業で直す必要がありますが、完全にその時間をゼロにできます。", 'post_status' => 'publish', 'post_date' => date('Y-m-d H:i:s', strtotime('-1 days'))),
            array('post_title' => 'システムメンテナンスのお知らせ（3月15日）', 'post_content' => "深夜にシステムメンテナンスを実施いたします。メンテナンス中はサービスをご利用いただけません。\n\nご理解とご協力のほどよろしくお願いいたします。", 'post_status' => 'publish', 'post_date' => date('Y-m-d H:i:s', strtotime('-2 days'))),
            array('post_title' => 'Excel管理からの脱却：移行事例インタビュー', 'post_content' => "長年Excel管理を続けてきた監理団体様が本システムに移行された事例をご紹介。移行のきっかけから効果まで、詳しくお聞きしました。\n\n「もっと早く導入していれば」という言葉が印象的でした。", 'post_status' => 'publish', 'post_date' => date('Y-m-d H:i:s', strtotime('-3 days'))),
            array('post_title' => '在留カードの期限切れを防ぐ！自動アラート機能の活用法', 'post_content' => "在留カードの期限切れは、行政処分の対象となる重大なリスクです。システムに搭載されている「4ヶ月前自動アラート機能」の確実な設定方法について解説します。", 'post_status' => 'publish', 'post_date' => date('Y-m-d H:i:s', strtotime('-4 days'))),
            array('post_title' => '育成就労制度に向けたコンプライアンス強化のポイント', 'post_content' => "新しい制度に向けて、企業や団体に求められるコンプライアンスはますます厳しくなります。どのような体制を敷くべきか、専門家の視点からお伝えします。", 'post_status' => 'publish', 'post_date' => date('Y-m-d H:i:s', strtotime('-5 days'))),
            array('post_title' => '多言語対応AIチャットボットがベトナム語に完全対応！', 'post_content' => "実習生からの問い合わせに24時間対応するAIチャットボットが、新たにベトナム語にも完全対応しました。これにより、通訳担当者の負担が劇的に軽減されます。", 'post_status' => 'publish', 'post_date' => date('Y-m-d H:i:s', strtotime('-6 days'))),
            array('post_title' => '請求書発行プロセスを自動化して経理業務を半減させる方法', 'post_content' => "毎月の煩雑な請求書作成から発送までのプロセスをシステムで一元化。経理担当者が本来の業務に集中できる環境の作り方をご紹介します。", 'post_status' => 'publish', 'post_date' => date('Y-m-d H:i:s', strtotime('-7 days'))),
            array('post_title' => '【事例紹介】受入企業からの問い合わせがAI導入で70%減少', 'post_content' => "パスワードの再発行や、書類の書き方など、受入企業からの単純な問い合わせに時間を奪われていませんか？AIサポート窓口を導入したB団体様の事例です。", 'post_status' => 'publish', 'post_date' => date('Y-m-d H:i:s', strtotime('-8 days'))),
            array('post_title' => 'クラウドデータ保護：最新のセキュリティ対策について', 'post_content' => "皆様の重要な個人情報を扱うシステムとして、どのようなセキュリティ対策が講じられているのか。最新のクラウドデータ保護技術について解説します。", 'post_status' => 'publish', 'post_date' => date('Y-m-d H:i:s', strtotime('-9 days'))),
            array('post_title' => '入管法改正の最新動向と今後のスケジュール予測', 'post_content' => "現在国会で議論されている入管法改正案について、その動向や今後のスケジュールを予測します。今後の準備に役立つ情報をお届けします。", 'post_status' => 'publish', 'post_date' => date('Y-m-d H:i:s', strtotime('-10 days')))
        );

        foreach ($fake_posts as $post) {
            if (!post_exists($post['post_title'])) wp_insert_post($post);
        }
        
        // Cập nhật số bài trên 1 trang thành 6 để dễ nhìn thấy Phân trang
        update_option('posts_per_page', 6);
        update_option( 'kumiai_fake_content_created_v2', 1 );
    }
}
add_action( 'init', 'kumiai_auto_create_content_v2' );

function kumiai_customize_register( $wp_customize ) {
    $wp_customize->add_section( 'kumiai_custom_links', array(
        'title'      => 'Cài đặt Link Logo & Nút bấm',
        'priority'   => 30,
    ) );

    $wp_customize->add_setting( 'kumiai_logo_link', array(
        'default'           => home_url( '/' ),
        'sanitize_callback' => 'esc_url_raw',
    ) );
    $wp_customize->add_control( 'kumiai_logo_link', array(
        'label'    => 'Đường link khi bấm vào Logo:',
        'section'  => 'kumiai_custom_links',
        'type'     => 'url',
    ) );

    $wp_customize->add_setting( 'kumiai_cta_link', array(
        'default'           => '#',
        'sanitize_callback' => 'esc_url_raw',
    ) );
    $wp_customize->add_control( 'kumiai_cta_link', array(
        'label'    => 'Đường link cho nút "無料デモを予約":',
        'section'  => 'kumiai_custom_links',
        'type'     => 'url',
    ) );
}
add_action( 'customize_register', 'kumiai_customize_register' );

// Tự động Cập nhật lại toàn bộ Slug của các bài viết Demo sang chuẩn Tiếng Anh (SEO)
function kumiai_update_dummy_slugs() {
    if ( ! get_option( 'kumiai_slugs_updated_v1' ) ) {
        $slug_map = array(
            '2027年問題に備える：育成就労制度の基礎知識' => '2027-training-system-basics',
            'Kumiaiシステム導入事例：業務時間が劇的に削減' => 'kumiai-case-study-time-reduction',
            '【お知らせ】新機能アップデートのご案内' => 'notice-new-features-update',
            '技能実習や特定技能に関わる最新の法改正まとめ' => 'latest-legal-reforms-summary',
            '登録支援機関向け：定期報告書の新フォーマット対応について' => 'new-report-format-support',
            'システムメンテナンスのお知らせ（3月15日）' => 'system-maintenance-mar15',
            'Excel管理からの脱却：移行事例インタビュー' => 'excel-migration-interview',
            '在留カードの期限切れを防ぐ！自動アラート機能の活用法' => 'residence-card-expiry-alert',
            '育成就労制度に向けたコンプライアンス強化のポイント' => 'compliance-enhancement-points',
            '多言語対応AIチャットボットがベトナム語に完全対応！' => 'vietnamese-ai-chatbot',
            '請求書発行プロセスを自動化して経理業務を半減させる方法' => 'invoice-automation-accounting',
            '【事例紹介】受入企業からの問い合わせがAI導入で70%減少' => 'ai-case-study-inquiries-70-percent-down',
            'クラウドデータ保護：最新のセキュリティ対策について' => 'cloud-data-protection-security',
            '入管法改正の最新動向と今後のスケジュール予測' => 'immigration-law-reform-schedule'
        );

        foreach ( $slug_map as $title => $slug ) {
            $post = get_page_by_title( $title, OBJECT, 'post' );
            if ( $post ) {
                wp_update_post( array(
                    'ID'        => $post->ID,
                    'post_name' => $slug
                ) );
            }
        }
        update_option( 'kumiai_slugs_updated_v1', 1 );
    }
}
add_action( 'init', 'kumiai_update_dummy_slugs' );

// Tự động Ghi đè lại Nội dung các bài post bằng Rich HTML (có chứa H2, H3 để demo tính năng Mục lục)
function kumiai_update_dummy_posts_content() {
    if ( ! get_option( 'kumiai_content_updated_v1' ) ) {
        $rich_posts = array(
            '2027年問題に備える：育成就労制度の基礎知識' => "<h2>育成就労制度とは何か？</h2>\n<p>2027年に導入予定の新しい制度について、その背景と目的を解説します。「育成就労」は人材の確保と育成を目的とした新しい在留資格です。</p>\n<h3>従来の技能実習制度との違い</h3>\n<p>転籍（転職）の要件が緩和される点が最大の変更点です。同一業務分野であれば、一定の条件下で転籍が可能となります。</p>\n<h2>監理団体が直面する3つの課題</h2>\n<p>1. 複雑化する転籍時の事務手続き・申告業務<br>2. 日本語能力試験などの進捗管理コスト<br>3. 新たな要件に対応した書類フォーマットへの移行</p>\n<h3>解決策：システムの活用</h3>\n<p>これらを乗り切るためには、早期のシステム導入が不可欠です。属人的なExcel管理からの脱却を推奨します。</p>",
            
            'Kumiaiシステム導入事例：業務時間が劇的に削減' => "<h2>導入前の課題</h2>\n<p>A協同組合様では、長年にわたりExcelと共有フォルダで実習生の管理を行っていました。しかし受け入れ人数が300名を超えたあたりからファイルが極端に重くなり、連日のようにフリーズが多発しました。</p>\n<h2>導入の決め手</h2>\n<p>何と言っても「直感的なUI」です。ITに不慣れな職員でも、マニュアルなしで直感的に操作できる点が評価されました。</p>\n<h3>圧倒的なサポート体制</h3>\n<p>導入時のデータ移行も、専任チームがフルサポートしてくれました。旧フォーマットからの流し込みも完全自動で行えました。</p>\n<h2>導入後の成果</h2>\n<p>1件あたり1時間かかっていた申請書作成がわずか5分に。さらに、自動アラート機能により、在留カードの更新忘れといった致命的なリスクがゼロになりました。</p>",
            
            'default' => "<h2>はじめに</h2>\n<p>この記事では、外国人材の受入れや管理に関する重要なポイントについて詳しく解説します。監理団体や登録支援機関の皆様にとって、日々の実務に直結する内容となっております。</p>\n<h2>最新の動向とトピック</h2>\n<p>法務省や出入国在留管理庁から随時発表される最新のガイドラインに基づき、我々が今すぐ取るべきアクションを整理しました。</p>\n<h3>ポイント1：管理体制の抜本的見直し</h3>\n<p>まずは現在の紙やExcelベースの管理体制の限界を認識し、デジタル化による一元管理へ向けた要件定義を行うことが第一歩です。</p>\n<h3>ポイント2：単純作業の自動化推進</h3>\n<p>書類作成や期限アラート通知をシステムに任せることで、担当者は受入企業への巡回指導やメンタルケアなど、本来のコア業務に専念できます。</p>\n<h2>今後の展望</h2>\n<p>法令遵守と業務効率化を高い次元で両立させるためには、柔軟なシステム基盤が不可欠です。引き続き、最新情報にキャッチアップしていきましょう。</p>"
        );

        $posts = get_posts(array('post_type' => 'post', 'numberposts' => -1));
        foreach ($posts as $post) {
            $content = isset($rich_posts[$post->post_title]) ? $rich_posts[$post->post_title] : $rich_posts['default'];
            wp_update_post( array(
                'ID'           => $post->ID,
                'post_content' => $content
            ) );
        }
        update_option( 'kumiai_content_updated_v1', 1 );
    }
}
add_action( 'init', 'kumiai_update_dummy_posts_content' );
?>
