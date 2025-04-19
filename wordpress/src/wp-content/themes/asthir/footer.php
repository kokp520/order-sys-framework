<?php

/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Asthir
 */

?>
<?php if (is_active_sidebar('footer-widget')) : ?>
	<div class="footer-top mt-5 pb-5 pt-5 bg-dark">
		<div class="footer-widget">
			<div class="container">
				<div class="row">
					<?php dynamic_sidebar('footer-widget') ?>
				</div>
			</div>
		</div>
	</div>
<?php endif; ?>

<footer id="colophon" class="asthir-mfooter text-white text-center">
	<div class="asthir-finfo">
		<a href="<?php echo esc_url(__('https://wordpress.org/', 'asthir')); ?>">
			<?php _e('Powered by WordPress', 'asthir'); ?>
		</a>

		<?php
		/* translators: 1: Theme name, 2: Theme author. */
		printf(__('Theme: %1$s by %2$s.', 'asthir'), 'asthir', '<a href="https://wpthemespace.com/product/asthir/">WP Theme Space</a>');
		?>
	</div><!-- .site-info -->
</footer><!-- #colophon -->
</div><!-- #page -->

<?php wp_footer(); ?>

</body>

</html>