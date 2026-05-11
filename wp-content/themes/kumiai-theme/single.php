<?php get_header(); ?>
<main class="single-main" style="background: var(--bg-color); padding: 40px 0;">
	<div class="container blog-container">
		
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article class="single-article">
				
				<!-- Header Meta -->
				<div class="single-top-meta">
					<span class="post-date"><i class="fa-regular fa-calendar" style="margin-right:6px;"></i> <?php echo get_the_date( 'Y-m-d' ); ?></span>
					<span class="read-time"><i class="fa-regular fa-clock" style="margin-right:6px;"></i> 3分で読めます</span>
				</div>
				
				<h1 class="single-title">
					<?php the_title(); ?>
				</h1>
				
				<!-- Author Box -->
				<div class="author-box">
					<div class="author-avatar"><i class="fa-regular fa-user"></i></div>
					<div class="author-info">
						<div class="author-name">佐藤 美咲</div>
						<div class="author-role">カスタマーサクセス</div>
					</div>
				</div>

				<div class="single-layout">
					<!-- Main Content Left -->
					<div class="single-content-wrapper">
						<div class="single-header-image">
							<!-- Ảnh nền ngẫu nhiên giống thẻ bên ngoài -->
							<img src="https://picsum.photos/seed/<?php echo (int) get_the_ID(); ?>/800/450" alt="<?php the_title_attribute(); ?>">
						</div>
						
						<div class="single-text">
							<?php the_content(); ?>
						</div>
					</div>
					
					<!-- Sidebar Right -->
					<aside class="single-sidebar">
						<div class="toc-box" id="dynamic-toc" style="display: none;">
							<h3>目次</h3>
							<ul id="toc-list"></ul>
						</div>
						
						<script>
						document.addEventListener('DOMContentLoaded', function() {
							const content = document.querySelector('.single-text');
							if (!content) return;
							
							const headings = content.querySelectorAll('h2, h3');
							const tocBox = document.getElementById('dynamic-toc');
							const tocList = document.getElementById('toc-list');
							
							if (headings.length > 0) {
								tocBox.style.display = 'block';
								let currentParent = tocList;
								let index = 0;

								headings.forEach(heading => {
									const level = parseInt(heading.tagName.replace('H', ''), 10);
									const id = 'heading-' + (++index);
									heading.id = id;
									heading.style.scrollMarginTop = '90px'; // For sticky header clearance
									heading.style.paddingTop = '10px';
									
									const li = document.createElement('li');
									const a = document.createElement('a');
									a.href = '#' + id;
									a.textContent = heading.textContent;
									li.appendChild(a);
									
									if (level === 2) {
										tocList.appendChild(li);
										currentParent = li; 
									} else if (level === 3) {
										let ul = currentParent.querySelector('ul');
										if (!ul) {
											ul = document.createElement('ul');
											currentParent.appendChild(ul);
										}
										ul.appendChild(li);
									}
								});
							}
						});
						</script>
						
						<div class="sidebar-cta">
							<h3>このシステムについて詳しく知る</h3>
							<p>監理ワンの機能や導入事例について、詳しくご説明いたします。</p>
							<a href="<?php echo esc_url( get_theme_mod( 'kumiai_cta_link', '#' ) ); ?>" class="btn-primary" style="background:#0ba376; width:100%; text-align:center; padding:12px 0; display:block; box-sizing:border-box;">無料デモを予約 <i class="fa-solid fa-arrow-right" style="margin-left:5px;"></i></a>
						</div>
					</aside>
				</div> <!-- /.single-layout -->
			</article>

			<!-- Back to grid button -->
			<div style="margin-top: 60px; text-align: center;">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn-primary" style="background: #fff; color: var(--primary-color); border: 2px solid var(--primary-color); font-weight: 600; padding: 12px 30px;"><i class="fa-solid fa-arrow-left" style="margin-right:8px;"></i> ブログ一覧へ戻る</a>
			</div>
			
		<?php endwhile; ?>
		
	</div>

	<!-- Related Posts -->
	<div class="container related-posts-section">
		<h2 class="section-heading">関連記事</h2>
		<div class="blog-grid limit-3" style="margin-bottom:0;">
			<?php
			// Truy vấn 3 bài viết liên quan ngẫu nhiên
			$related = new WP_Query(
				array(
					'post_type'      => 'post',
					'posts_per_page' => 3,
					'post__not_in'   => array( get_the_ID() ),
					'orderby'        => 'rand',
				)
			);

			if ( $related->have_posts() ) :
				while ( $related->have_posts() ) :
					$related->the_post();
					?>
					<article class="blog-card" style="margin-bottom:0;">
						<div class="card-image">
							<a href="<?php the_permalink(); ?>">
								<img src="https://picsum.photos/seed/<?php echo (int) get_the_ID(); ?>/600/400" alt="<?php the_title_attribute(); ?>" style="width: 100%; height: 100%; object-fit: cover;">
							</a>
						</div>
						<div class="card-content">
							<div class="card-meta">
								<span class="post-date"><?php echo get_the_date( 'Y-m-d' ); ?></span>
							</div>
							<a href="<?php the_permalink(); ?>">
								<h2 class="card-title"><?php the_title(); ?></h2>
							</a>
						</div>
					</article>
					<?php
				endwhile;
				wp_reset_postdata();
			endif;
			?>
		</div>
	</div>
</main>
<?php get_footer(); ?>
