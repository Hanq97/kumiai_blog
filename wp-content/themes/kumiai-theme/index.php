<?php get_header(); ?>

<!-- Hero Section -->
<section class="blog-hero">
    <div class="container">
        <h1>ブログ・お知らせ</h1>
        <p>外国人材受入れに関する法改正情報、システムの操作ガイド、<br>最新のお知らせをお届けします。</p>
    </div>
</section>

<main class="blog-main">
    <div class="container">
        <div class="blog-grid">
        <?php
        if ( have_posts() ) :
            while ( have_posts() ) : the_post();
                ?>
                <article class="blog-card">
                    <div class="card-image">
                        <a href="<?php the_permalink(); ?>">
                            <!-- Sử dụng Picsum API với Seed là ID bài viết để có ảnh random nhưng luôn cố định cho từng bài -->
                            <img src="https://picsum.photos/seed/<?php echo get_the_ID(); ?>/600/400" alt="Blog Image" style="width: 100%; height: 100%; object-fit: cover;">
                        </a>
                    </div>
                    <div class="card-content">
                        <div class="card-meta">
                            <span class="post-date"><i class="fa-regular fa-calendar" style="margin-right:4px;"></i><?php echo get_the_date('Y-m-d'); ?></span>
                        </div>
                        <a href="<?php the_permalink(); ?>">
                            <h2 class="card-title"><?php the_title(); ?></h2>
                        </a>
                        <div class="card-excerpt">
                            <?php echo strip_tags(get_the_excerpt()); ?>
                        </div>
                        <a href="<?php the_permalink(); ?>" class="read-more">続きを読む <i class="fa-solid fa-arrow-right" style="margin-left:4px;"></i></a>
                    </div>
                </article>
                <?php
            endwhile;
        else :
            echo '<p>記事が見つかりませんでした。</p>';
        endif;
        ?>
        </div>
        
        <div class="pagination">
            <?php 
                the_posts_pagination( array(
                    'mid_size'  => 2,
                    'prev_text' => '<i class="fa-solid fa-angle-left"></i> 前へ',
                    'next_text' => '次へ <i class="fa-solid fa-angle-right"></i>',
                ) );
            ?>
        </div>
    </div>
</main>

<!-- Bottom CTA -->
<section class="bottom-cta">
    <div class="container">
        <h2>2027年の法改正、準備はできていますか？</h2>
        <p>監理ワンなら、法改正への対応も自動で完了します。</p>
        <a href="<?php echo esc_url( get_theme_mod('kumiai_cta_link', '#') ); ?>" class="btn-primary btn-large">無料デモを予約する <i class="fa-solid fa-arrow-right"></i></a>
    </div>
</section>

<?php get_footer(); ?>
