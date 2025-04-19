<?php
/**
 * Template part for displaying header top bar
 *
 * @link https://wpthemespace.com/product/beshop
 *
 * @package BeShop
 */

?>

<?php 
 function asthir_header_top_display_item(){
 	$aak_topbar_container = get_theme_mod( 'aak_topbar_container', 'container' );
 	$asthir_topbar_mtext = get_theme_mod( 'asthir_topbar_mtext', esc_html__('Welcome to Our Website','asthir') );
	$asthir_topbar_menushow = get_theme_mod( 'asthir_topbar_menushow',1 );
	$asthir_topbar_search = get_theme_mod( 'asthir_topbar_search',1 );
 ?>
 	<div class="asthirtop-tophead text-light pt-1 pb-1">
	<div class="<?php echo esc_attr($aak_topbar_container); ?>">
			<div class="row">
			<?php if($asthir_topbar_mtext): ?>
				<div class="col-auto">
					<span class="bhtop-text pt-2"><?php echo esc_html($asthir_topbar_mtext); ?></span>
				</div>
			<?php endif; ?>
			<?php if($asthir_topbar_menushow && has_nav_menu( 'menu-top' ) || $asthir_topbar_search ): ?>
				<div class="col-auto ml-auto">
					<div class="topmenu-serch">
			<?php if($asthir_topbar_menushow && has_nav_menu( 'top-menu' )): ?>
						<div class="top-menu list-hide text-white">
							<?php 
								wp_nav_menu(
									array(
										'theme_location' => 'top-menu',
										'menu_id'        => 'asthirtop-menu',
										'menu_class'     => 'asthirtop-menu',
										'depth'          => 1,
										'fallback_cb'    => false,							
									)
								);
							 ?>
						</div>
						<?php endif; ?>
						<?php if($asthir_topbar_search): ?>
						<div class="header-top-search">
							<i class="fas fa-search"></i>
							<?php get_search_form(); ?>
						</div>	
						<?php endif; ?>
						<?php
						if ( function_exists( 'asthir_woocommerce_header_cart' ) ) {
							asthir_woocommerce_header_cart();
							}
						?>
					</div>
				</div>
			<?php endif; ?>
			</div>
		</div>
	</div>
<?php 	
 }
add_action('asthir_header_top_display','asthir_header_top_display_item');

?>

