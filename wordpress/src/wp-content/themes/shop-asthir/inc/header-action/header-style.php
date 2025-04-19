<?php

/**
 * header style action
 *
 * @link https://wpthemespace.com/product/beshop
 *
 * @package BeShop
 */

function asthir_main_menu_display()
{
	$asthir_menu_position = get_theme_mod('asthir_menu_position', 'center');

?>
	<div class="asthir-main-nav bg-dark text-white">
		<div class="container">
			<nav id="site-navigation" class="main-navigation text-<?php echo esc_attr($asthir_menu_position); ?>">
				<button class="menu-toggle" aria-controls="primary-menu" aria-expanded="false"><span class="mshow"><?php esc_html_e('Menu', 'shop-asthir'); ?></span><span class="mhide"><?php esc_html_e('Close Menu', 'shop-asthir'); ?></span></button>
				<?php
				if (has_nav_menu('menu-1')) {

					wp_nav_menu(
						array(
							'theme_location' => 'menu-1',
							'menu_id'        => 'primary-menu',
							'walker'        => new Asthir_Walker_Nav_Menu(),
						)
					);
				} elseif (!has_nav_menu('expanded')) { ?>
					<ul id="primary-menu" class="menu nav-menu">
						<?php
						wp_list_pages(
							array(
								'match_menu_classes' => true,
								'show_sub_menu_icons' => true,
								'title_li' => false,
								'walker'   => new Asthir_Walker_Page(),
							)
						);
						?>
					</ul>
				<?php

				}

				?>
				<button class="screen-reader-text mmenu-hide"><?php esc_html_e('Close Menu', 'shop-asthir'); ?></button>
			</nav><!-- #site-navigation -->
		</div>
	</div>

<?php
}
add_action('asthir_main_menu', 'asthir_main_menu_display');

// create action for text and image logo for site
function akk_logo_text_item()
{
	$asthir_dheader_text = get_theme_mod('display_header_text', 1);
	$asthir_logo_position = get_theme_mod('asthir_logo_position', 'center');

?>
	<?php if (has_header_image()) : ?>
		<div class="site-branding has-himg text-center <?php if ($asthir_dheader_text && has_custom_logo()) : ?>asthir-two-logo<?php endif; ?>">
			<div class="asthir-header-img">
				<?php the_header_image_tag(); ?>
			</div>
		<?php else : ?>
			<div class="site-branding text-center">
			<?php endif; ?>
			<div class="headerlogo-text">
				<div class="container pb-5 pt-5">
					<div class="asthir-logotext text-<?php echo esc_attr($asthir_logo_position); ?>">
						<?php the_custom_logo(); ?>
						<?php if (display_header_text() == true || (display_header_text() == true && is_customize_preview())) : ?>
							<h1 class="site-title"><a href="<?php echo esc_url(home_url('/')); ?>" rel="home"><?php bloginfo('name'); ?></a></h1>
							<?php
							$asthir_description = get_bloginfo('description', 'display');
							if ($asthir_description || is_customize_preview()) :
							?>
								<p class="site-description"><?php echo $asthir_description; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
															?></p>
							<?php endif; ?>
						<?php endif; ?>
					</div>
				</div>
			</div>
			</div><!-- .site-branding -->
		<?php
	}
	add_action('asthir_logo_text', 'akk_logo_text_item');
		?>