<?php
// Enqueue styles
function kumiai_enqueue_styles() {
	wp_enqueue_style( 'kumiai-style', get_stylesheet_uri(), array(), wp_get_theme()->get( 'Version' ) );
}
add_action( 'wp_enqueue_scripts', 'kumiai_enqueue_styles' );

// Disable XML-RPC — a common brute-force / pingback-DDoS attack surface that this blog does not use.
add_filter( 'xmlrpc_enabled', '__return_false' );
add_filter(
	'wp_headers',
	function ( $headers ) {
		unset( $headers['X-Pingback'] );
		return $headers;
	}
);

// Setup theme features (Menus, Thumbnails, Title Tag, HTML5, Custom Logo)
function kumiai_theme_setup() {
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 60,
			'width'       => 200,
			'flex-width'  => true,
			'flex-height' => true,
		)
	);
	register_nav_menus(
		array(
			'primary-menu'   => 'Header Main Menu',
			'footer-service' => 'Footer Service Menu',
			'footer-company' => 'Footer Company Menu',
		)
	);
}
add_action( 'after_setup_theme', 'kumiai_theme_setup' );

// Skip all demo-content scaffolding when running in production.
function kumiai_is_demo_env() {
	return ! defined( 'KUMIAI_ENV' ) || KUMIAI_ENV !== 'production';
}

// Auto-create dummy content (Version 2)
function kumiai_auto_create_content_v2() {
	if ( ! kumiai_is_demo_env() ) {
		return;
	}
	if ( ! get_option( 'kumiai_fake_content_created_v2' ) ) {
		if ( ! function_exists( 'post_exists' ) ) {
			require_once ABSPATH . 'wp-admin/includes/post.php';
		}

		$fake_posts = array(
			array(
				'post_title'   => '登録支援機関向け：定期報告書の新フォーマット対応について',
				'post_content' => "入管庁より発表された定期報告書の新フォーマットについて、変更点と対応方法を解説します。システムは既に新フォーマットに対応済みです。\n\nExcelで管理していると、これらのフォーマット変更を手作業で直す必要がありますが、完全にその時間をゼロにできます。",
				'post_status'  => 'publish',
				'post_date'    => gmdate( 'Y-m-d H:i:s', strtotime( '-1 days' ) ),
			),
			array(
				'post_title'   => 'システムメンテナンスのお知らせ（3月15日）',
				'post_content' => "深夜にシステムメンテナンスを実施いたします。メンテナンス中はサービスをご利用いただけません。\n\nご理解とご協力のほどよろしくお願いいたします。",
				'post_status'  => 'publish',
				'post_date'    => gmdate( 'Y-m-d H:i:s', strtotime( '-2 days' ) ),
			),
			array(
				'post_title'   => 'Excel管理からの脱却：移行事例インタビュー',
				'post_content' => "長年Excel管理を続けてきた監理団体様が本システムに移行された事例をご紹介。移行のきっかけから効果まで、詳しくお聞きしました。\n\n「もっと早く導入していれば」という言葉が印象的でした。",
				'post_status'  => 'publish',
				'post_date'    => gmdate( 'Y-m-d H:i:s', strtotime( '-3 days' ) ),
			),
			array(
				'post_title'   => '在留カードの期限切れを防ぐ！自動アラート機能の活用法',
				'post_content' => '在留カードの期限切れは、行政処分の対象となる重大なリスクです。システムに搭載されている「4ヶ月前自動アラート機能」の確実な設定方法について解説します。',
				'post_status'  => 'publish',
				'post_date'    => gmdate( 'Y-m-d H:i:s', strtotime( '-4 days' ) ),
			),
			array(
				'post_title'   => '育成就労制度に向けたコンプライアンス強化のポイント',
				'post_content' => '新しい制度に向けて、企業や団体に求められるコンプライアンスはますます厳しくなります。どのような体制を敷くべきか、専門家の視点からお伝えします。',
				'post_status'  => 'publish',
				'post_date'    => gmdate( 'Y-m-d H:i:s', strtotime( '-5 days' ) ),
			),
			array(
				'post_title'   => '多言語対応AIチャットボットがベトナム語に完全対応！',
				'post_content' => '実習生からの問い合わせに24時間対応するAIチャットボットが、新たにベトナム語にも完全対応しました。これにより、通訳担当者の負担が劇的に軽減されます。',
				'post_status'  => 'publish',
				'post_date'    => gmdate( 'Y-m-d H:i:s', strtotime( '-6 days' ) ),
			),
			array(
				'post_title'   => '請求書発行プロセスを自動化して経理業務を半減させる方法',
				'post_content' => '毎月の煩雑な請求書作成から発送までのプロセスをシステムで一元化。経理担当者が本来の業務に集中できる環境の作り方をご紹介します。',
				'post_status'  => 'publish',
				'post_date'    => gmdate( 'Y-m-d H:i:s', strtotime( '-7 days' ) ),
			),
			array(
				'post_title'   => '【事例紹介】受入企業からの問い合わせがAI導入で70%減少',
				'post_content' => 'パスワードの再発行や、書類の書き方など、受入企業からの単純な問い合わせに時間を奪われていませんか？AIサポート窓口を導入したB団体様の事例です。',
				'post_status'  => 'publish',
				'post_date'    => gmdate( 'Y-m-d H:i:s', strtotime( '-8 days' ) ),
			),
			array(
				'post_title'   => 'クラウドデータ保護：最新のセキュリティ対策について',
				'post_content' => '皆様の重要な個人情報を扱うシステムとして、どのようなセキュリティ対策が講じられているのか。最新のクラウドデータ保護技術について解説します。',
				'post_status'  => 'publish',
				'post_date'    => gmdate( 'Y-m-d H:i:s', strtotime( '-9 days' ) ),
			),
			array(
				'post_title'   => '入管法改正の最新動向と今後のスケジュール予測',
				'post_content' => '現在国会で議論されている入管法改正案について、その動向や今後のスケジュールを予測します。今後の準備に役立つ情報をお届けします。',
				'post_status'  => 'publish',
				'post_date'    => gmdate( 'Y-m-d H:i:s', strtotime( '-10 days' ) ),
			),
		);

		foreach ( $fake_posts as $post ) {
			if ( ! post_exists( $post['post_title'] ) ) {
				wp_insert_post( $post );
			}
		}

		// Cập nhật số bài trên 1 trang thành 6 để dễ nhìn thấy Phân trang
		update_option( 'posts_per_page', 6 );
		update_option( 'kumiai_fake_content_created_v2', 1 );
	}
}
add_action( 'init', 'kumiai_auto_create_content_v2' );

function kumiai_customize_register( WP_Customize_Manager $wp_customize ) {
	$wp_customize->add_section(
		'kumiai_custom_links',
		array(
			'title'    => 'Cài đặt Link Logo & Nút bấm',
			'priority' => 30,
		)
	);

	$wp_customize->add_setting(
		'kumiai_logo_link',
		array(
			'default'           => home_url( '/' ),
			'sanitize_callback' => 'esc_url_raw',
		)
	);
	$wp_customize->add_control(
		'kumiai_logo_link',
		array(
			'label'   => 'Đường link khi bấm vào Logo:',
			'section' => 'kumiai_custom_links',
			'type'    => 'url',
		)
	);

	$wp_customize->add_setting(
		'kumiai_cta_link',
		array(
			'default'           => '#',
			'sanitize_callback' => 'esc_url_raw',
		)
	);
	$wp_customize->add_control(
		'kumiai_cta_link',
		array(
			'label'   => 'Đường link cho nút "無料デモを予約":',
			'section' => 'kumiai_custom_links',
			'type'    => 'url',
		)
	);

	// Dedicated footer logo (the dark-background-friendly variant).
	$wp_customize->add_setting(
		'kumiai_footer_logo',
		array(
			'default'           => '',
			'sanitize_callback' => 'absint',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Media_Control(
			$wp_customize,
			'kumiai_footer_logo',
			array(
				'label'       => 'Logo cho Footer (nền tối)',
				'description' => 'Tùy chọn: phiên bản logo đã được làm sáng/trắng cho nền tối ở footer. Bỏ trống nếu muốn dùng logo chính + tự động tint trắng.',
				'section'     => 'title_tagline',
				'mime_type'   => 'image',
			)
		)
	);
}
add_action( 'customize_register', 'kumiai_customize_register' );

// Tự động Cập nhật lại toàn bộ Slug của các bài viết Demo sang chuẩn Tiếng Anh (SEO)
function kumiai_update_dummy_slugs() {
	if ( ! kumiai_is_demo_env() ) {
		return;
	}
	if ( ! get_option( 'kumiai_slugs_updated_v1' ) ) {
		$slug_map = array(
			'登録支援機関向け：定期報告書の新フォーマット対応について'  => 'new-report-format-support',
			'システムメンテナンスのお知らせ（3月15日）'        => 'system-maintenance-mar15',
			'Excel管理からの脱却：移行事例インタビュー'       => 'excel-migration-interview',
			'在留カードの期限切れを防ぐ！自動アラート機能の活用法'    => 'residence-card-expiry-alert',
			'育成就労制度に向けたコンプライアンス強化のポイント'     => 'compliance-enhancement-points',
			'多言語対応AIチャットボットがベトナム語に完全対応！'    => 'vietnamese-ai-chatbot',
			'請求書発行プロセスを自動化して経理業務を半減させる方法'   => 'invoice-automation-accounting',
			'【事例紹介】受入企業からの問い合わせがAI導入で70%減少' => 'ai-case-study-inquiries-70-percent-down',
			'クラウドデータ保護：最新のセキュリティ対策について'     => 'cloud-data-protection-security',
			'入管法改正の最新動向と今後のスケジュール予測'        => 'immigration-law-reform-schedule',
		);

		foreach ( $slug_map as $title => $slug ) {
			$query = new WP_Query(
				array(
					'post_type'      => 'post',
					'title'          => $title,
					'posts_per_page' => 1,
					'fields'         => 'ids',
					'no_found_rows'  => true,
				)
			);
			if ( $query->have_posts() ) {
				wp_update_post(
					array(
						'ID'        => $query->posts[0],
						'post_name' => $slug,
					)
				);
			}
		}
		update_option( 'kumiai_slugs_updated_v1', 1 );
	}
}
add_action( 'init', 'kumiai_update_dummy_slugs' );

// Tự động Ghi đè lại Nội dung các bài post bằng Rich HTML (có chứa H2, H3 để demo tính năng Mục lục)
function kumiai_update_dummy_posts_content() {
	if ( ! kumiai_is_demo_env() ) {
		return;
	}
	if ( ! get_option( 'kumiai_content_updated_v1' ) ) {
		$rich_posts = array(
			'default' => "<h2>はじめに</h2>\n<p>この記事では、外国人材の受入れや管理に関する重要なポイントについて詳しく解説します。監理団体や登録支援機関の皆様にとって、日々の実務に直結する内容となっております。</p>\n<h2>最新の動向とトピック</h2>\n<p>法務省や出入国在留管理庁から随時発表される最新のガイドラインに基づき、我々が今すぐ取るべきアクションを整理しました。</p>\n<h3>ポイント1：管理体制の抜本的見直し</h3>\n<p>まずは現在の紙やExcelベースの管理体制の限界を認識し、デジタル化による一元管理へ向けた要件定義を行うことが第一歩です。</p>\n<h3>ポイント2：単純作業の自動化推進</h3>\n<p>書類作成や期限アラート通知をシステムに任せることで、担当者は受入企業への巡回指導やメンタルケアなど、本来のコア業務に専念できます。</p>\n<h2>今後の展望</h2>\n<p>法令遵守と業務効率化を高い次元で両立させるためには、柔軟なシステム基盤が不可欠です。引き続き、最新情報にキャッチアップしていきましょう。</p>",
		);

		$posts = get_posts(
			array(
				'post_type'   => 'post',
				'numberposts' => -1,
			)
		);
		foreach ( $posts as $post ) {
			$content = isset( $rich_posts[ $post->post_title ] ) ? $rich_posts[ $post->post_title ] : $rich_posts['default'];
			wp_update_post(
				array(
					'ID'           => $post->ID,
					'post_content' => $content,
				)
			);
		}
		update_option( 'kumiai_content_updated_v1', 1 );
	}
}
add_action( 'init', 'kumiai_update_dummy_posts_content' );
