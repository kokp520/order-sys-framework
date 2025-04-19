<?php

/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Shop Asthir
 */
$shop_asthir_blogdate = get_theme_mod('asthir_blogdate', 1);
$shop_asthir_blogauthor = get_theme_mod('asthir_blogauthor', 1);

if (is_active_sidebar('sidebar-1')) {
	$shop_asthir_gcolumn = '6';
} else {
	$shop_asthir_gcolumn = '4';
}

?>
<div class="col-lg-<?php echo esc_attr($shop_asthir_gcolumn); ?> bsgrid-item">
	<article class="asthir-shop-grid-item">
		<?php if (has_post_thumbnail()) : ?>
			<div class="asthir-shop-article-image">
				<?php the_post_thumbnail('medium'); ?>
			</div>
		<?php endif; ?>

		<h2 class="asthir-shop-article-title">
			<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		</h2>

		<div class="asthir-shop-article-excerpt">
			<?php the_excerpt(); ?>
		</div>

		<div class="asthir-shop-article-meta">
			<?php
			// Display first category with link
			$categories = get_the_category();
			if (!empty($categories)) {
				echo '<span class="asthir-shop-article-category"><a href="' . esc_url(get_category_link($categories[0]->term_id)) . '">' . esc_html($categories[0]->name) . '</a></span>';
			}
			?>

			<span class="asthir-shop-article-author">by <a href="<?php echo esc_url(get_author_posts_url(get_the_author_meta('ID'))); ?>"><?php the_author(); ?></a></span>

			<span class="asthir-shop-article-date">
				<?php
				$year = get_the_time('Y');
				$month = get_the_time('m');
				$day = get_the_time('d');
				?>
				<a href="<?php echo esc_url(get_day_link($year, $month, $day)); ?>"><?php echo get_the_date('F j, Y'); ?></a>
			</span>
		</div>
	</article>
</div>