<?php

/**
 * The template for displaying search results pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#search-result
 *
 * @package Shop Asthir
 */

get_header();
$shop_asthir_blog_container = get_theme_mod('asthir_blog_container', 'container');
if (is_active_sidebar('sidebar-left')) {
	$shop_asthir_column_set = '9';
} else {
	$shop_asthir_column_set = '12';
}
?>
<div class="<?php echo esc_attr($shop_asthir_blog_container); ?> mt-5 mb-5 pt-3 pb-3">
	<div class="row">
		<?php if (is_active_sidebar('sidebar-left')): ?>
			<div class="col-lg-3">
				<aside id="left-widget" class="widget-area">
					<?php dynamic_sidebar('sidebar-left'); ?>
				</aside>
			</div>
		<?php endif; ?>
		<div class="col-lg-<?php echo esc_attr($shop_asthir_column_set); ?>">
			<main id="primary" class="site-main">

				<?php if (have_posts()) : ?>

					<header class="page-header search-header text-center mb-5">
						<h1 class="page-title">
							<?php
							/* translators: %s: search query. */
							printf(esc_html__('Search Results for: %s', 'shop-asthir'), '<span>' . get_search_query() . '</span>');
							?>
						</h1>
					</header><!-- .page-header -->

					<?php
					if (is_home() && ! is_front_page()) :
					?>
						<header>
							<h1 class="page-title screen-reader-text"><?php single_post_title(); ?></h1>
						</header>
					<?php
					endif;
					$shop_asthir_blog_style = get_theme_mod('shop_asthir_blog_style', 'style2');

					?>
					<div class="bs-gridh">
						<div class="row bs-grid">
							<?php


							/* Start the Loop */
							while (have_posts()) :
								the_post();

								/*
						 * Include the Post-Type-specific template for the content.
						 * If you want to override this in a child theme, then include a file
						 * called content-___.php (where ___ is the Post Type name) and that will be used instead.
						 */
								get_template_part('template-parts/content', 'grid');

							endwhile;
							?>
						</div>
					</div>
				<?php

					the_posts_pagination();
				else :

					get_template_part('template-parts/content', 'none');

				endif;
				?>

			</main><!-- #main -->
		</div>

	</div>
</div>

<?php
get_footer();
