<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package Asthir
 */

get_header();
?>

	<main id="primary" class="site-main">
	<div class="container">
		<section class="error-404 not-found">
			<div class="page404-text text-center pb-5 pt-5 mb-5 mt-5">
				<header class="page-header">
					<h1 class="page-title text-danger"><?php esc_html_e( 'Oops! That page can&rsquo;t be found.', 'asthir' ); ?></h1>
				</header><!-- .page-header -->

				<div class="page-content">
					<p><?php esc_html_e( 'It looks like nothing was found at this location. Maybe try one of the links below or a search?', 'asthir' ); ?></p>
				</div>
				<div class="search404 mt-5">
					<?php get_search_form(); ?>
				</div>
			</div>
			
		</section><!-- .error-404 -->
	</div>
	</main><!-- #main -->

<?php
get_footer();