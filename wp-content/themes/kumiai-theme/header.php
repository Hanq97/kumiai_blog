<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Noto+Sans+JP:wght@400;500;700&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
	<?php wp_body_open(); ?>
	<header class="site-header">
		<div class="container header-container">
			<div class="site-logo">
				<a href="<?php echo esc_url( get_theme_mod( 'kumiai_logo_link', home_url( '/' ) ) ); ?>">
					<i class="fa-solid fa-clock"></i> 監理ワン
				</a>
			</div>
			
			<nav class="site-nav">
				<?php
				if ( has_nav_menu( 'primary-menu' ) ) {
					wp_nav_menu(
						array(
							'theme_location' => 'primary-menu',
							'container'      => '',
							'menu_class'     => 'header-menu-list',
						)
					);
				} else {
					echo '<ul class="header-menu-list">';
					echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">ホーム</a></li>';
					echo '<li><a href="#">機能</a></li>';
					echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">ブログ</a></li>';
					echo '<li><a href="#">お問い合わせ</a></li>';
					echo '</ul>';
				}
				?>
			</nav>
			
			<div class="header-cta">
				<a href="<?php echo esc_url( get_theme_mod( 'kumiai_cta_link', '#' ) ); ?>" class="btn-primary">無料デモを予約</a>
			</div>
		</div>
	</header>
