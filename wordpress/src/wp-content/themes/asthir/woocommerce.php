<?php

/**
 * The template for displaying all pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package asthir
 */
if (is_cart() || is_checkout() || is_account_page()) {
	$asthir_column_set = '12';
} elseif (is_active_sidebar('sidebar-1')) {
	$asthir_column_set = '9';
} else {
	$asthir_column_set = '12'; // No sidebar, so use full width
}
get_header();
?>
<div class="container mt-3 mb-5 pt-5 pb-3">
	<div class="row">
		<div class="<?php echo esc_attr($asthir_column_set); ?>">
			<div id="primary" class="content-area">
				<main id="main" class="site-main">

					<?php woocommerce_content(); ?>

				</main><!-- #main -->
			</div><!-- #primary -->
		</div><!-- #primary -->
		<?php if (is_active_sidebar('shop-sidebar')): ?>
			<div class="col-lg-3">
				<aside id="secondary" class="widget-area shop-sidebar">
					<?php dynamic_sidebar('shop-sidebar'); ?>
				</aside><!-- #secondary -->
			</div>
		<?php
		endif; ?>
	</div>
</div>
<?php
get_footer();
