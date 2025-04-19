<?php
/*
*
* The file for display blog content for asthir theme
*
*/

?>
<div class="asthir-blog-list">
	<?php asthir_single_post_cat(); ?>
	<?php if (has_post_thumbnail()) : ?>
		<div class="asthir-list-flex">
			<div class="asthir-blog-img">
				<?php asthir_post_thumbnail(); ?>
			</div>
		<?php else : ?>
			<div class="asthir-list-flex no-img">
			<?php endif; ?>

			<div class="asthir-blog-text">
				<header class="entry-header">
					<?php
					the_title('<h2 class="entry-title"><a href="' . esc_url(get_permalink()) . '" rel="bookmark">', '</a></h2>');

					if ('post' === get_post_type()) :
					?>
						<div class="entry-meta">
							<?php
							asthir_posted_on();
							asthir_posted_by();
							?>
						</div><!-- .entry-meta -->
					<?php endif; ?>
				</header><!-- .entry-header -->



				<div class="entry-content">
					<?php
					the_excerpt();
					?>
					<a href="<?php the_permalink(); ?>"><?php esc_html_e('Read More', 'asthir'); ?></a>
				</div><!-- .entry-content -->


			</div>


			</div>




		</div>