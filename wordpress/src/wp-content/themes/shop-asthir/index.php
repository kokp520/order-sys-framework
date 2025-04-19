<?php

/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Shop Asthir
 */

get_header();


$shop_asthir_blog_container = get_theme_mod('asthir_blog_container', 'container');
$shop_asthir_blog_style = get_theme_mod('shop_asthir_blog_style', 'style2');


if (is_active_sidebar('sidebar-left')) {
	$shop_asthir_column_set = '9';
} else {
	$shop_asthir_column_set = '12';
}

?>
<div class="<?php echo esc_attr($shop_asthir_blog_container); ?> mt-3 mb-5 pt-5 pb-3">
	<?php
	if (is_woocommerce_activated() && asthir_has_woocommerce_products() && is_front_page()) :
	?>
		<main id="primary" class="site-main">
			<?php get_template_part('template-parts/content', 'woo'); ?>
		</main>
	<?php else : ?>
		<div class="row">
			<?php if (is_active_sidebar('sidebar-left')) : ?>
				<div class="col-lg-3 left-side">
					<aside id="left-widget" class="widget-area">
						<?php dynamic_sidebar('sidebar-left'); ?>
					</aside>
				</div>
			<?php endif; ?>
			<div class="col-lg-<?php echo esc_attr($shop_asthir_column_set); ?>">
				<main id="primary" class="site-main">

					<?php
					if (have_posts()) :

						if (is_home() && !is_front_page()) :
					?>
							<header>
								<h1 class="page-title screen-reader-text"><?php single_post_title(); ?></h1>
							</header>
						<?php
						endif;

						if ($shop_asthir_blog_style == 'style2' && (!is_single())) :
						?>
							<div class="asthir-shop-article-grid">
								<div class="row bs-grid">
								<?php
							endif;

							/* Start the Loop */
							while (have_posts()) :
								the_post();

								/*
						 * Include the Post-Type-specific template for the content.
						 * If you want to override this in a child theme, then include a file
						 * called content-___.php (where ___ is the Post Type name) and that will be used instead.
						 */
								get_template_part('template-parts/content', get_post_type());

							endwhile;
							if ($shop_asthir_blog_style == 'style2' && (!is_single())) :
								?>
								</div>
							</div>
					<?php
							endif;
							the_posts_pagination();
						else :

							get_template_part('template-parts/content', 'none');

						endif;
					?>

				</main><!-- #main -->
			</div>
		</div>
	<?php endif; ?>
</div>

<?php
get_footer();
