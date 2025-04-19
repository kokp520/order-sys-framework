<?php
/*
*
* asthir woocommerce related functions
*
*
*/

if (!function_exists('asthir_woocommerce_setup')) {
	function asthir_woocommerce_setup()
	{
		add_theme_support('woocommerce');
		add_theme_support('wc-product-gallery-zoom');
		add_theme_support('wc-product-gallery-lightbox');
		add_theme_support('wc-product-gallery-slider');
	}
	add_action('after_setup_theme', 'asthir_woocommerce_setup');
}

if (!function_exists('asthir_woocommerce_scripts')) {
	function asthir_woocommerce_scripts()
	{
		wp_enqueue_style('asthir-woocommerce-style', get_template_directory_uri() . '/assets/css/asthir-woocommerce.css', array(), ASTHIR_VERSION, 'all');
	}
	add_action('wp_enqueue_scripts', 'asthir_woocommerce_scripts');
}

if (!function_exists('asthir_woocommerce_cart_link_fragment')) {
	/**
	 * Cart Fragments.
	 *
	 * Ensure cart contents update when products are added to the cart via AJAX.
	 *
	 * @param array $fragments Fragments to refresh via AJAX.
	 * @return array Fragments to refresh via AJAX.
	 */
	function asthir_woocommerce_cart_link_fragment($fragments)
	{
		ob_start();
		asthir_woocommerce_cart_link();
		$fragments['.asthir-shopcart'] = ob_get_clean();

		return $fragments;
	}
}
add_filter('woocommerce_add_to_cart_fragments', 'asthir_woocommerce_cart_link_fragment');

if (!function_exists('asthir_woocommerce_cart_link')) {
	/**
	 * Cart Link.
	 *
	 * Displayed a link to the cart including the number of items present and the cart total.
	 *
	 * @return void
	 */
	function asthir_woocommerce_cart_link()
	{
		$item_count_text = sprintf(
			/* translators: number of items in the mini cart. */
			_n('(%d)', '(%d)', WC()->cart->get_cart_contents_count(), 'asthir'),
			WC()->cart->get_cart_contents_count()
		);
?>
		<div class="asthir-shopcart" data-toggle="modal" data-target="#cartModal">
			<div class="asthir-inner-shopcart">
				<i class="fas fa-shopping-cart"></i>
				<span class="count cart-contents"><?php echo esc_html($item_count_text); ?></span>
			</div>
		</div>


	<?php
	}
}

if (!function_exists('asthir_woocommerce_header_cart')) {
	/**
	 * Display Header Cart.
	 *
	 * @return void
	 */
	function asthir_woocommerce_header_cart()
	{
		if (is_cart()) {
			$class = 'current-menu-item xcart-page';
		} else {
			$class = 'not-cart-page';
		}

	?>
		<div class="asthiring-cart <?php esc_attr($class); ?>">
			<?php asthir_woocommerce_cart_link(); ?>
			<!-- Modal -->
			<div class="modal fade" id="cartModal" tabindex="-1" role="dialog" aria-labelledby="becartTitle" aria-hidden="true">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title" id="becartTitle"><?php echo esc_html__('Shopping Cart ', 'asthir'); ?></h5>
						</div>
						<div class="modal-body">
							<?php
							$instance = array(
								'title' => '',
							);

							the_widget('WC_Widget_Cart', $instance);
							?>

						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e('Close', 'asthir'); ?>
							</button>
						</div>
					</div>
				</div>
			</div>

		</div>
<?php
	}
}

if (!function_exists('asthir_woowidgets_init')) {
	function asthir_woowidgets_init()
	{
		register_sidebar(array(
			'name'          => esc_html__('Shop Sidebar', 'asthir'),
			'id'            => 'shop-sidebar',
			'description'   => esc_html__('Add shop widgets here.', 'asthir'),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		));
	}
	add_action('widgets_init', 'asthir_woowidgets_init');
}

if (!function_exists('asthir_body_wooclasses')) {
	function asthir_body_wooclasses($classes)
	{

		if (!is_active_sidebar('shop-sidebar') && is_shop()) {
			$classes[] = 'no-shop-widget';
		}
		if (is_front_page() && is_shop()) {
			$classes[] = 'befront-shop';
		}

		return $classes;
	}
	add_filter('body_class', 'asthir_body_wooclasses');
}


/**
 * Change number or products per row 
 */
add_filter('loop_shop_columns', 'asthir_loop_columns', 999);
if (!function_exists('asthir_loop_columns')) {
	function asthir_loop_columns()
	{
		if (is_active_sidebar('shop-sidebar')) {
			return 3; // 3 products per row
		}
		return 4; // 4 products per row
	}
}


function asthir_products_pagination($max_num_pages)
{
	$big = 999999999; // need an unlikely integer

	$pages = paginate_links(array(
		'base'    => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
		'format'  => '?paged=%#%',
		'current' => max(1, get_query_var('paged')),
		'total'   => $max_num_pages,
		'type'    => 'array',
		'prev_text' => __('&laquo; Previous', 'asthir'),
		'next_text' => __('Next &raquo;', 'asthir'),
	));

	if (is_array($pages)) {
		$pagination = '<ul class="page-numbers">';
		foreach ($pages as $page) {
			$pagination .= '<li>' . $page . '</li>';
		}
		$pagination .= '</ul>';
		echo $pagination;
	}
}
