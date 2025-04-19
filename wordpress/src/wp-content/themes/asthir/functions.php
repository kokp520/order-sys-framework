<?php

/**
 * Asthir functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Asthir
 */

if (!defined('ASTHIR_VERSION')) {
	// Replace the version number of the theme on each release.
	$asthir_theme = wp_get_theme();
	define('ASTHIR_VERSION', $asthir_theme->get('Version'));
}

if (!function_exists('asthir_setup')) :
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 */
	function asthir_setup()
	{
		/*
		 * Make theme available for translation.
		 * Translations can be filed in the /languages/ directory.
		 * If you're building a theme based on Asthir, use a find and replace
		 * to change 'asthir' to the name of your theme in all the template files.
		 */
		load_theme_textdomain('asthir');

		// Add default posts and comments RSS feed links to head.
		add_theme_support('automatic-feed-links');

		/*
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and expect WordPress to
		 * provide it for us.
		 */
		add_theme_support('title-tag');

		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 *
		 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		 */
		add_theme_support('post-thumbnails');

		// This theme uses wp_nav_menu() in one location.
		register_nav_menus(
			array(
				'top-menu' => esc_html__('Top Menu', 'asthir'),
				'menu-1' => esc_html__('Main Menu', 'asthir'),
			)
		);

		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support(
			'html5',
			array(
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
				'style',
				'script',
			)
		);

		// Add support for Block Styles.
		add_theme_support('wp-block-styles');

		// Add support for full and wide align images.
		add_theme_support('align-wide');

		// Set up the WordPress core custom background feature.
		add_theme_support(
			'custom-background',
			apply_filters(
				'asthir_custom_background_args',
				array(
					'default-color' => 'ffffff',
					'default-image' => '',
				)
			)
		);
		if (!current_theme_supports('woocommerce')) {
			add_theme_support('woocommerce');
		}

		/**
		 * Add support for core custom logo.
		 *
		 * @link https://codex.wordpress.org/Theme_Logo
		 */
		add_theme_support(
			'custom-logo',
			array(
				'height'      => 250,
				'width'       => 250,
				'flex-width'  => true,
				'flex-height' => true,
			)
		);
	}
endif;
add_action('after_setup_theme', 'asthir_setup');

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function asthir_content_width()
{
	// This variable is intended to be overruled from themes.
	// Open WPCS issue: {@link https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards/issues/1043}.
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	$GLOBALS['content_width'] = apply_filters('asthir_content_width', 1170);
}
add_action('after_setup_theme', 'asthir_content_width', 0);

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function asthir_widgets_init()
{
	register_sidebar(
		array(
			'name'          => esc_html__('Left Sidebar', 'asthir'),
			'id'            => 'sidebar-left',
			'description'   => esc_html__('Add widgets here for left sidebar.', 'asthir'),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		)
	);
	register_sidebar(
		array(
			'name'          => esc_html__('Right Sidebar', 'asthir'),
			'id'            => 'sidebar-1',
			'description'   => esc_html__('Add widgets here for right sidebar.', 'asthir'),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		)
	);
	register_sidebar(
		array(
			'name'          => esc_html__('Footer Widget', 'asthir'),
			'id'            => 'footer-widget',
			'description'   => esc_html__('Add Footer widgets here.', 'asthir'),
			'before_widget' => '<div class="col-lg-3"><div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div></div>',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		)
	);
}
add_action('widgets_init', 'asthir_widgets_init');

function asthir_gb_block_style()
{

	wp_enqueue_style('asthir-gb-block', get_theme_file_uri('/assets/css/admin-block.css'), false, '1.0', 'all');
}
add_action('enqueue_block_assets', 'asthir_gb_block_style');

/**
 * Beshop Google fonts fuction
 */
if (!function_exists('asthir_body_fonts')) :
	function asthir_body_fonts()
	{
		$asthir_body_fonts = get_theme_mod('asthir_body_fonts', 'Poppins');
		$asthir_head_fonts = get_theme_mod('asthir_head_fonts', 'Lato');

		$fonts_url = '';

		$font_families = array();
		if ($asthir_body_fonts == $asthir_head_fonts) {
			$font_families[] = $asthir_body_fonts . ':300,400,500,600,700,800';
		} else {
			$font_families[] = $asthir_body_fonts . ':300,400,500,600,700,800';
			$font_families[] = $asthir_head_fonts . ':300,400,500,600,700,800';
		}

		$query_args = array(
			'family' => urlencode(implode('|', $font_families)),
			'subset' => urlencode('latin,latin-ext'),
		);
		$fonts_url = add_query_arg($query_args, 'https://fonts.googleapis.com/css');

		return esc_url_raw($fonts_url);
	}
endif;

/**
 * Enqueue scripts and styles.
 */
function asthir_scripts()
{
	wp_enqueue_style('asthir-google-font', asthir_body_fonts(), array(), null);
	wp_enqueue_style('asthir-default', get_template_directory_uri() . '/assets/css/default.css', array(), ASTHIR_VERSION, 'all');
	wp_enqueue_style('bootstrap', get_template_directory_uri() . '/assets/css/bootstrap.css', array(), '4.5.0', 'all');
	wp_enqueue_style('font-awesome-five-all', get_template_directory_uri() . '/assets/css/all.css', array(), '5.14.0', 'all');
	wp_enqueue_style('asthir-block-style', get_template_directory_uri() . '/assets/css/block.css', array(), '1.0');
	wp_enqueue_style('asthir-main', get_template_directory_uri() . '/assets/css/asthir-main.css', array(), ASTHIR_VERSION, 'all');
	wp_enqueue_style('asthir-style', get_stylesheet_uri(), array(), ASTHIR_VERSION);

	wp_enqueue_script('bootstrap', get_template_directory_uri() . '/assets/js/bootstrap.js', array('jquery'), '4.5.0', true);
	wp_enqueue_script('asthir-mobile-menu', get_template_directory_uri() . '/assets/js/mobile-menu.js', array(), '1.0', true);
	wp_enqueue_script('asthir-scripts', get_template_directory_uri() . '/assets/js/scripts.js', array(), ASTHIR_VERSION, true);

	if (is_singular() && comments_open() && get_option('thread_comments')) {
		wp_enqueue_script('comment-reply');
	}
}
add_action('wp_enqueue_scripts', 'asthir_scripts');

/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/class-walker-nav-menu.php';
require get_template_directory() . '/inc/class-asthir-walker-page.php';
/**
 * customizer
 */
require get_template_directory() . '/inc/customizer/customizer-helper.php';
require get_template_directory() . '/inc/customizer/customizer.php';
/**
 * Recommend plugin 
 */
require get_template_directory() . '/inc/class-tgm-plugin-activation.php';
require get_template_directory() . '/inc/plugin-recomend.php';

/**
 * add inline style
 */
require get_template_directory() . '/inc/inline-style.php';
/**
 * header action
 */
require get_template_directory() . '/inc/header-action/header-style.php';
require get_template_directory() . '/inc/header-action/header-top.php';

/**
 * Load Jetpack compatibility file.
 */
if (defined('JETPACK__VERSION')) {
	require get_template_directory() . '/inc/jetpack.php';
}

/**
 * Load WooCommerce compatibility file.
 */
if (class_exists('WooCommerce')) {
	require get_template_directory() . '/inc/asthir-woocommerce.php';
}

/**
 * Filter the except length to 20 words.
 *
 * @param int $length Excerpt length.
 * @return int (Maybe) modified excerpt length.
 */
add_filter('excerpt_more', 'asthir_exerpt_empty_string', 999);
function asthir_exerpt_empty_string($more)
{
	if (is_admin()) {
		return $more;
	}
	return '';
}


require get_template_directory() . '/inc/info/class-customize.php';
if (is_admin()) {
	require_once trailingslashit(get_template_directory()) . 'inc/about/class.about.php';
	require_once trailingslashit(get_template_directory()) . 'inc/about/about.php';
}
