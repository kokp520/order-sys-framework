<?php
/*This file is part of Shop Asthir child theme.

All functions of this file will be loaded before of parent theme functions.
Learn more at https://codex.wordpress.org/Child_Themes.

Note: this function loads the parent stylesheet before, then child theme stylesheet
(leave it in place unless you know what you are doing.)
*/


if (!defined('SHOP_ASTHIR_VERSION')) {
	$shop_asthir_theme = wp_get_theme();
	define('SHOP_ASTHIR_VERSION', $shop_asthir_theme->get('Version'));
}



function shop_asthir_fonts_url()
{
	$fonts_url = '';

	$font_families = array();

	$font_families[] = 'Oxygen:400,500,700';
	$font_families[] = 'Rubik:400,500,500i,700,700i';

	$query_args = array(
		'family' => urlencode(implode('|', $font_families)),
		'subset' => urlencode('latin,latin-ext'),
	);

	$fonts_url = add_query_arg($query_args, 'https://fonts.googleapis.com/css');


	return esc_url_raw($fonts_url);
}


function shop_asthir_enqueue_child_styles()
{
	wp_enqueue_style('shop-asthir-google-font', shop_asthir_fonts_url(), array(), null);
	wp_enqueue_style('shop-asthir-parent-style', get_template_directory_uri() . '/style.css', array('asthir-main', 'asthir-google-font', 'asthir-default', 'asthir-woocommerce-style'), '', 'all');
	wp_enqueue_style('shop-asthir-main', get_stylesheet_directory_uri() . '/assets/css/main.css', array(), SHOP_ASTHIR_VERSION, 'all');

	wp_enqueue_script('masonry');
	wp_enqueue_script('shop-asthir-main-js', get_stylesheet_directory_uri() . '/assets/js/main.js', array('jquery'), SHOP_ASTHIR_VERSION, true);
}
add_action('wp_enqueue_scripts', 'shop_asthir_enqueue_child_styles');


function shop_asthir_remove_right_sidebar()
{
	unregister_sidebar('sidebar-1');
}
add_action('widgets_init', 'shop_asthir_remove_right_sidebar', 20);

/**
 * Customizer additions.
 */
require get_stylesheet_directory() . '/inc/customizer.php';


function shop_asthir_body_class($classes)
{
	$shop_asthir_widget_style = get_theme_mod('shop_asthir_widget_style', '2');

	$classes[] = 'asthir-widget' . $shop_asthir_widget_style;

	return $classes;
}
add_action('body_class', 'shop_asthir_body_class');
// // Nav walker for menu


function shop_asthir_main_menu_witthlogo_display()
{
	$shop_asthir_menu_position = get_theme_mod('asthir_menu_position', 'right');

?>
	<div class="asthir-main-nav bg-dark text-white">
		<div class="container">
			<div class="asthir-nav-logo">
				<div class="row">
					<div class="col-lg-auto mr-auto">
						<div class="asthir-logotext asthir-menulogo text-left">
							<?php the_custom_logo(); ?>
							<?php if (display_header_text() == true || (display_header_text() == true && is_customize_preview())) : ?>
								<h1 class="site-title"><a href="<?php echo esc_url(home_url('/')); ?>" rel="home"><?php bloginfo('name'); ?></a></h1>
								<?php
								$shop_asthir_description = get_bloginfo('description', 'display');
								if ($shop_asthir_description || is_customize_preview()) :
								?>
									<p class="site-description"><?php echo $shop_asthir_description; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
																?></p>
								<?php endif; ?>
							<?php endif; ?>
						</div>
					</div>
					<div class="shop-asthir-col col-lg-auto">
						<nav id="site-navigation" class="main-navigation text-<?php echo esc_attr($shop_asthir_menu_position); ?>">
							<button class="menu-toggle" aria-controls="primary-menu" aria-expanded="false"><span class="mshow"><?php esc_html_e('Menu', 'shop-asthir'); ?></span><span class="mhide"><?php esc_html_e('Close Menu', 'shop-asthir'); ?></span></button>
							<?php
							wp_nav_menu(
								array(
									'theme_location' => 'menu-1',
									'menu_id'        => 'primary-menu',
									'menu_class'     => 'nav-menu',
								)
							);
							?>
						</nav><!-- #site-navigation -->
					</div>
				</div>
			</div>
		</div>
	</div>
<?php
}
add_action('asthir_plus_main_menulogo', 'shop_asthir_main_menu_witthlogo_display');


/**
 * Filter the excerpt length to 20 words.
 *
 * @param int $length Excerpt length.
 * @return int Modified excerpt length.
 */
function asthir_shop_custom_excerpt_length($length)
{
	if (is_admin()) {
		return $length;
	} else {
		return 20;
	}
}
add_filter('excerpt_length', 'asthir_shop_custom_excerpt_length');

/**
 * Filter the excerpt "read more" string.
 *
 * @param string $more "Read more" excerpt string.
 * @return string Modified excerpt "read more" string.
 */
function asthir_shop_custom_excerpt_more($more)
{
	if (is_admin()) {
		return $more;
	} else {
		return ' ';
	}
}
add_filter('excerpt_more', 'asthir_shop_custom_excerpt_more');
