<?php
/**
 * Shop Asthir Theme Customizer
 *
 * @package Shop Asthir
 */

/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */


function shop_asthir_customize_register( $wp_customize ) {

    $wp_customize->remove_section( 'asthir_topbar' );

  //  $wp_customize->remove_control('asthir_blog_style');
    $wp_customize->remove_control('asthir_shop_style');

        
    $wp_customize->add_setting( 'shop_asthir_navlogo' , array(
        'capability'     => 'edit_theme_options',
        'type'           => 'theme_mod',
        'default'       =>  '1',
        'sanitize_callback' => 'absint',
        'transport'     => 'refresh',
        ) );
        $wp_customize->add_control( 'shop_asthir_navlogo', array(
            'label'      => __('Show Nav Logo & Title ', 'shop-asthir'),
            'description'=> __('You can show or hide nav logo.', 'shop-asthir'),
            'section'    => 'asthir_main_header',
            'settings'   => 'shop_asthir_navlogo',
            'type'       => 'checkbox',
            
        ) );
        
    $wp_customize->add_setting( 'shop_asthir_extralogo' , array(
        'capability'     => 'edit_theme_options',
        'type'           => 'theme_mod',
        'default'       =>  '',
        'sanitize_callback' => 'absint',
        'transport'     => 'refresh',
        ) );
        $wp_customize->add_control( 'shop_asthir_extralogo', array(
            'label'      => __('Show Extra Logo & Title ', 'shop-asthir'),
            'description'=> __('You can show or hide extra logo.', 'shop-asthir'),
            'section'    => 'asthir_main_header',
            'settings'   => 'shop_asthir_extralogo',
            'type'       => 'checkbox',
            
        ) );
  
    $wp_customize->add_section('asthir_blog', array(
		'title' => __('Shop Asthir Blog Settings', 'shop-asthir'),
		'capability'     => 'edit_theme_options',
		'description'     => __('Shop Asthir blog setup.', 'shop-asthir'),
	//	'priority'       => 4,

	));
    
    $wp_customize->add_setting('shop_asthir_blog_style', array(
        'default'        => 'style2',
        'capability'     => 'edit_theme_options',
        'type'           => 'theme_mod',
        'sanitize_callback' => 'asthir_sanitize_select',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control('shop_asthir_blog_style', array(
        'label'      => __('Select Blog Style', 'shop-asthir'),
        'section'    => 'asthir_blog',
        'settings'   => 'shop_asthir_blog_style',
        'type'       => 'select',
        'choices'    => array(
            'style1' => __('List Style', 'shop-asthir'),
            'style2' => __('Grid Style', 'shop-asthir'),
        ),
    ));   
    $wp_customize->add_setting('shop_asthir_widget_style', array(
        'default'        => '2',
        'capability'     => 'edit_theme_options',
        'type'           => 'theme_mod',
        'sanitize_callback' => 'asthir_sanitize_select',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control('shop_asthir_widget_style', array(
        'label'      => __('Select Widget Style', 'shop-asthir'),
        'section'    => 'asthir_blog',
        'settings'   => 'shop_asthir_widget_style',
        'type'       => 'select',
        'choices'    => array(
            '1' => __('Style one', 'shop-asthir'),
            '2' => __('Style Two', 'shop-asthir'),
        ),
    ));

    $wp_customize->add_setting('asthir_shop_style', array(
        'default'        => '2',
        'capability'     => 'edit_theme_options',
        'type'           => 'theme_mod',
        'sanitize_callback' => 'asthirwoo_sanitize_select',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control('asthir_shop_style', array(
        'label'      => __('Select Products Style', 'shop-asthir'),
        'section'    => 'asthir_shop',
        'settings'   => 'asthir_shop_style',
        'type'       => 'select',
        'choices'    => array(
            '1' => __('Style One', 'shop-asthir'),
            '2' => __('Style Two', 'shop-asthir'),
        ),
    ));
    

}
add_action( 'customize_register', 'shop_asthir_customize_register' );

