<?php

/**
 * The header for our theme 
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Shop Asthir
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>

<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
	<?php wp_body_open(); ?>
	<div id="page" class="site">
		<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e('Skip to content', 'shop-asthir'); ?></a>

		<?php
		do_action('asthir_mobile_menu');

		$asthir_topbar_show = get_theme_mod('asthir_topbar_show');
		if ($asthir_topbar_show) {
			do_action('asthir_header_top_display');
		}

		?>
		<header id="masthead" class="asthir-header site-header">

			<?php
			$shop_asthir_header_style = get_theme_mod('asthir_header_style', 'style1');
			$shop_asthir_navlogo = get_theme_mod('shop_asthir_navlogo', 1);
			$shop_asthir_extralogo = get_theme_mod('shop_asthir_extralogo');
			if ($shop_asthir_header_style == 'style1') {
				if ($shop_asthir_extralogo) {
					do_action('asthir_logo_text');
				}
				if ($shop_asthir_navlogo) {
					do_action('asthir_plus_main_menulogo');
				} else {
					do_action('asthir_main_menu');
				}
			} else {
				if ($shop_asthir_navlogo) {
					do_action('asthir_plus_main_menulogo');
				} else {
					do_action('asthir_main_menu');
				}
				if ($shop_asthir_extralogo) {
					do_action('asthir_logo_text');
				}
			}

			?>
		</header><!-- #masthead -->