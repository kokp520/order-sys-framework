<?php
/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Shop Asthir
 */
$shop_asthir_blog_style = get_theme_mod( 'shop_asthir_blog_style','style2'  );
?>


	<?php if(is_single()): ?>
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<?php get_template_part( 'template-parts/content', 'single' ); ?>
		</article><!-- #post-<?php the_ID(); ?> -->
	<?php else: ?>
		<?php
			if( $shop_asthir_blog_style == 'style2' ){
				get_template_part( 'template-parts/content', 'grid' );
			}else{
				get_template_part( 'template-parts/content', 'blog' );

			}
			
			?>
	<?php endif; ?>
	

