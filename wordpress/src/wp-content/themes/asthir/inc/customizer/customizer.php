<?php

/**
 * Asthir Theme Customizer
 *
 * @package Asthir
 */

/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function asthir_customize_register($wp_customize)
{
    $wp_customize->get_setting('blogname')->transport         = 'postMessage';
    $wp_customize->get_setting('blogdescription')->transport  = 'postMessage';
    $wp_customize->get_setting('header_textcolor')->transport = 'postMessage';
    $theme = wp_get_theme();
    $theme_slug = $theme->get('TextDomain');
    //select sanitization function
    function asthir_sanitize_select($input, $setting)
    {
        $input = sanitize_key($input);
        $choices = $setting->manager->get_control($setting->id)->choices;
        return (array_key_exists($input, $choices) ? $input : $setting->default);
    }

    if ($theme_slug == 'shop-asthir') {
        $topbar_show_default = '';
    } else {
        $topbar_show_default = '1';
    }

    // Typography section
    $wp_customize->add_section('asthir_typography', array(
        'title' => __('Asthir Theme typography', 'asthir'),
        'capability'     => 'edit_theme_options',
        'description'     => __('You can setup Asthir theme typography by these options.', 'asthir'),
        'priority'       => 4,

    ));

    //add setting to your section
    $wp_customize->add_setting(
        'asthir_body_fonts',
        array(
            'default'       => 'Poppins',
            'sanitize_callback' => 'asthir_sanitize_theme_font',
            'capability'     => 'edit_theme_options',
            'type'           => 'theme_mod',
            'transport' => 'refresh',
        )
    );

    $wp_customize->add_control(
        'asthir_body_fonts',
        array(
            'label' => esc_html__('Select theme body Font', 'asthir'),
            'section' => 'asthir_typography',
            'type' => 'select',
            'choices' => array(
                'Poppins' => esc_html__('Poppins', 'asthir'),
                'Roboto' => esc_html__('Roboto', 'asthir'),
                'Open Sans' => esc_html__('Open Sans', 'asthir'),
                'Lato' => esc_html__('Lato', 'asthir'),
                'Montserrat' => esc_html__('Montserrat', 'asthir'),
            )
        )
    );

    $wp_customize->add_setting('asthir_font_size', array(
        'default' =>  '14',
        'capability'     => 'edit_theme_options',
        'type'           => 'theme_mod',
        'sanitize_callback' => 'sanitize_text_field',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control('asthir_font_size_control', array(
        'label'      => __('Body font size', 'asthir'),
        'description'     => __('Default body font size is 14px', 'asthir'),
        'section'    => 'asthir_typography',
        'settings'   => 'asthir_font_size',
        'type'       => 'text',

    ));
    $wp_customize->add_setting('asthir_font_line_height', array(
        'default' =>  '24',
        'capability'     => 'edit_theme_options',
        'type'           => 'theme_mod',
        'sanitize_callback' => 'sanitize_text_field',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control('asthir_font_line_height_control', array(
        'label'      => __('Body font line height', 'asthir'),
        'description'     => __('Default body line height is 24px', 'asthir'),
        'section'    => 'asthir_typography',
        'settings'   => 'asthir_font_line_height',
        'type'       => 'text',

    ));

    //add setting to your section
    $wp_customize->add_setting(
        'asthir_head_fonts',
        array(
            'default'       => 'Lato',
            'sanitize_callback' => 'asthir_sanitize_theme_head_font',
            'capability'     => 'edit_theme_options',
            'type'           => 'theme_mod',
            'transport' => 'refresh',
        )
    );

    $wp_customize->add_control(
        'asthir_head_fonts',
        array(
            'label' => esc_html__('Select Header Tag Font', 'asthir'),
            'section' => 'asthir_typography',
            'type' => 'select',
            'choices' => array(
                'Poppins' => esc_html__('Poppins', 'asthir'),
                'Roboto' => esc_html__('Roboto', 'asthir'),
                'Open Sans' => esc_html__('Open Sans', 'asthir'),
                'Lato' => esc_html__('Lato', 'asthir'),
                'Montserrat' => esc_html__('Montserrat', 'asthir'),
            )
        )
    );
    //add setting to your section
    $wp_customize->add_setting(
        'asthir_font_weight_head',
        array(
            'default'       => '700',
            'sanitize_callback' => 'asthir_sanitize_select',
            'capability'     => 'edit_theme_options',
            'type'           => 'theme_mod',
            'transport' => 'refresh',
        )
    );

    $wp_customize->add_control(
        'asthir_font_weight_head',
        array(
            'label' => esc_html__('Site header font weight', 'asthir'),
            'section' => 'asthir_typography',
            'type' => 'select',
            'choices' => array(
                '400' => __('Normal', 'asthir'),
                '500' => __('Semi Bold', 'asthir'),
                '700' => __('Bold', 'asthir'),
                '900' => __('Extra Bold', 'asthir'),
            )
        )
    );
    /*End typography section*/
    // Add Asthir top header section
    $wp_customize->add_section('asthir_topbar', array(
        'title' => __('Asthir Top bar', 'asthir'),
        'capability'     => 'edit_theme_options',
        'description'     => __('The beshop topbar options ', 'asthir'),
        'priority'       => 5,

    ));
    $wp_customize->add_setting('asthir_topbar_show', array(
        'capability'     => 'edit_theme_options',
        'type'           => 'theme_mod',
        'default'       =>  $topbar_show_default,
        'sanitize_callback' => 'absint',
        'transport'     => 'refresh',
    ));
    $wp_customize->add_control('asthir_topbar_show', array(
        'label'      => __('Show header topbar? ', 'asthir'),
        'description' => __('You can show or hide header topbar.', 'asthir'),
        'section'    => 'asthir_topbar',
        'settings'   => 'asthir_topbar_show',
        'type'       => 'checkbox',
    ));
    $wp_customize->add_setting('asthir_topbar_container', array(
        'default'        => 'container',
        'capability'     => 'edit_theme_options',
        'type'           => 'theme_mod',
        'sanitize_callback' => 'asthir_sanitize_select',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control('asthir_topbar_container', array(
        'label'      => __('Topbar Container Type', 'asthir'),
        'description' => __('You can set standard container or full width container. ', 'asthir'),
        'section'    => 'asthir_topbar',
        'settings'   => 'asthir_topbar_container',
        'type'       => 'select',
        'choices'    => array(
            'container' => __('Standard Container', 'asthir'),
            'container-fluid' => __('Full width Container', 'asthir'),
        ),
    ));
    $wp_customize->add_setting('asthir_topbar_mtext', array(
        'capability'     => 'edit_theme_options',
        'type'           => 'theme_mod',
        'default'       =>  esc_html__('Welcome to Our Website', 'asthir'),
        'sanitize_callback' => 'sanitize_text_field',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control('asthir_topbar_mtext', array(
        'label'      => __('Welcome text', 'asthir'),
        'description'     => esc_html__('Enter your website welcome text. Leave empty if you don\'t want the text.', 'asthir'),
        'section'    => 'asthir_topbar',
        'settings'   => 'asthir_topbar_mtext',
        'type'       => 'text',
    ));
    $wp_customize->add_setting('asthir_topbar_menushow', array(
        'capability'     => 'edit_theme_options',
        'type'           => 'theme_mod',
        'default'       =>  '1',
        'sanitize_callback' => 'absint',
        'transport'     => 'refresh',
    ));
    $wp_customize->add_control('asthir_topbar_menushow', array(
        'label'      => __('Show header topbar menu? ', 'asthir'),
        'description' => __('You can show or hide topbar menu. You need to add menu from menu section for display menu.', 'asthir'),
        'section'    => 'asthir_topbar',
        'settings'   => 'asthir_topbar_menushow',
        'type'       => 'checkbox',

    ));
    $wp_customize->add_setting('asthir_topbar_search', array(
        'capability'     => 'edit_theme_options',
        'type'           => 'theme_mod',
        'default'       =>  '1',
        'sanitize_callback' => 'absint',
        'transport'     => 'refresh',
    ));
    $wp_customize->add_control('asthir_topbar_search', array(
        'label'      => __('Show header topbar search? ', 'asthir'),
        'description' => __('You can show or hide topbar search.', 'asthir'),
        'section'    => 'asthir_topbar',
        'settings'   => 'asthir_topbar_search',
        'type'       => 'checkbox',
    ));
    // Add setting
    $wp_customize->add_setting('asthir_topbar_bg', array(
        'default' => '#000',
        'type' => 'theme_mod',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport' => 'refresh',
    ));
    // Add color control 
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'asthir_topbar_bg',
            array(
                'label' => __('Topbar Background Color', 'asthir'),
                'section' => 'asthir_topbar'
            )
        )
    );
    // Add setting
    $wp_customize->add_setting('asthir_topbar_color', array(
        'default' => '#fff',
        'type' => 'theme_mod',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport' => 'refresh',
    ));
    // Add color control 
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'asthir_topbar_color',
            array(
                'label' => __('Topbar text Color', 'asthir'),
                'section' => 'asthir_topbar'
            )
        )
    );
    // Add setting
    $wp_customize->add_setting('asthir_topbar_hcolor', array(
        'default' => '#dedede',
        'type' => 'theme_mod',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport' => 'refresh',
    ));
    // Add color control 
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'asthir_topbar_hcolor',
            array(
                'label' => __('Topbar link hover Color', 'asthir'),
                'section' => 'asthir_topbar'
            )
        )
    );

    /* Asthir theme header settings */
    $wp_customize->add_section('asthir_main_header', array(
        'title' => __('Asthir Header', 'asthir'),
        'capability'     => 'edit_theme_options',
        'description'     => __('The Asthir theme main header settings options ', 'asthir'),
        'priority'       => 20,


    ));
    $wp_customize->add_setting('asthir_header_style', array(
        'default'       => 'style1',
        'capability'     => 'edit_theme_options',
        'type'           => 'theme_mod',
        'sanitize_callback' => 'asthir_sanitize_select',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control('asthir_header_style', array(
        'label'      => __('Site Header Style', 'asthir'),
        'section'    => 'asthir_main_header',
        'settings'   => 'asthir_header_style',
        'type'       => 'select',
        'choices'    => array(
            'style1' => __('Style One', 'asthir'),
            'style2' => __('Style Two', 'asthir'),
        ),
    ));
    $wp_customize->add_setting('asthir_logo_position', array(
        'default'        => 'center',
        'capability'     => 'edit_theme_options',
        'type'           => 'theme_mod',
        'sanitize_callback' => 'asthir_sanitize_select',
        'transport' => 'refresh',
        //  'priority'       => 20,
    ));
    $wp_customize->add_control('asthir_logo_position', array(
        'label'      => __('Logo Position', 'asthir'),
        'section'    => 'asthir_main_header',
        'settings'   => 'asthir_logo_position',
        'type'       => 'select',
        'choices'    => array(
            'left' => __('Logo left', 'asthir'),
            'center' => __('Logo center', 'asthir'),
            'right' => __('Logo right', 'asthir'),
        ),
    ));
    $wp_customize->add_setting('asthir_menu_position', array(
        'default'        => 'center',
        'capability'     => 'edit_theme_options',
        'type'           => 'theme_mod',
        'sanitize_callback' => 'asthir_sanitize_select',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control('asthir_menu_position', array(
        'label'      => __('Main menu Position', 'asthir'),
        'section'    => 'asthir_main_header',
        'settings'   => 'asthir_menu_position',
        'type'       => 'select',
        'choices'    => array(
            'left' => __('Menu left', 'asthir'),
            'center' => __('Menu center', 'asthir'),
            'right' => __('Menu right', 'asthir'),
        ),
    ));



    /*
* Footer setting section
*
*/
    // Add beshop top header section


    // Footer section
    $wp_customize->add_section('asthir_footer', array(
        'title' => __('Asthir Footer Settings', 'asthir'),
        'capability'     => 'edit_theme_options',
        'description'     => __('The beshop footer settings options ', 'asthir'),

    ));

    $wp_customize->add_setting('asthir_footer_bgcolor', array(
        'default' => '',
        'type' => 'theme_mod',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport' => 'refresh',
    ));
    // Add color control 
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'asthir_footer_bgcolor',
            array(
                'label' => __('Footer background color.', 'asthir'),
                'section' => 'asthir_footer'
            )
        )
    );
    $wp_customize->add_setting('asthir_footer_color', array(
        'default' => '',
        'type' => 'theme_mod',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport' => 'refresh',
    ));
    // Add color control 
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'asthir_footer_color',
            array(
                'label' => __('Footer text color.', 'asthir'),
                'section' => 'asthir_footer'
            )
        )
    );
    $wp_customize->add_setting('asthir_footerlink_hcolor', array(
        'default' => '',
        'type' => 'theme_mod',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport' => 'refresh',
    ));
    // Add color control 
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'asthir_footerlink_hcolor',
            array(
                'label' => __('Footer Link Hover color.', 'asthir'),
                'section' => 'asthir_footer'
            )
        )
    );














    if (isset($wp_customize->selective_refresh)) {
        $wp_customize->selective_refresh->add_partial(
            'blogname',
            array(
                'selector'        => '.site-title a',
                'render_callback' => 'asthir_customize_partial_blogname',
            )
        );
        $wp_customize->selective_refresh->add_partial(
            'blogdescription',
            array(
                'selector'        => '.site-description',
                'render_callback' => 'asthir_customize_partial_blogdescription',
            )
        );
    }
}
add_action('customize_register', 'asthir_customize_register');

/**
 * Render the site title for the selective refresh partial.
 *
 * @return void
 */
function asthir_customize_partial_blogname()
{
    bloginfo('name');
}

/**
 * Render the site tagline for the selective refresh partial.
 *
 * @return void
 */
function asthir_customize_partial_blogdescription()
{
    bloginfo('description');
}

/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 */
function asthir_customize_preview_js()
{
    wp_enqueue_script('aak-customizer', get_template_directory_uri() . '/assets/js/customizer.js', array('customize-preview'), '20151215', true);
}
add_action('customize_preview_init', 'asthir_customize_preview_js');
