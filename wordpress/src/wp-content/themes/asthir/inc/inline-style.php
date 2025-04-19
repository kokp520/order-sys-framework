<?php

/**
 * Asthir theme add inline style 
 *
 * 
 */
if (!function_exists('asthir_inline_css')) :
  function asthir_inline_css()
  {

    $style = '';

    //top bar settings  
    $asthir_topbar_bg = get_theme_mod('asthir_topbar_bg', '#343a40');
    $asthir_topbar_color = get_theme_mod('asthir_topbar_color', '#fff');
    $asthir_topbar_hcolor = get_theme_mod('asthir_topbar_hcolor', '#dedede');

    if ($asthir_topbar_bg != '#343a40') {
      $style .= '.asthirtop-tophead{background-color:' . $asthir_topbar_bg . '!important;}';
    }
    if ($asthir_topbar_color != '#fff') {
      $style .= '.asthirtop-tophead, .asthirtop-tophead a,.asthirtop-menu li a, .asthirtop-tophead span, .asthirtop-tophead input,.asthirtop-tophead i{color:' . $asthir_topbar_color . ';}';
    }
    if ($asthir_topbar_hcolor != '#dedede') {
      $style .= '.asthirtop-tophead a:hover,.asthirtop-menu li a:hover{color:' . $asthir_topbar_hcolor . ';}';
    }

    // font settings
    $asthir_body_fonts = get_theme_mod('asthir_body_fonts', 'Poppins');
    $asthir_font_size = get_theme_mod('asthir_font_size', '14');
    $asthir_font_line_height = get_theme_mod('asthir_font_line_height', '24');
    $asthir_head_fonts = get_theme_mod('asthir_head_fonts', 'Lato');
    $asthir_font_weight_head = get_theme_mod('asthir_font_weight_head', '700');

    if ($asthir_body_fonts != 'Poppins') {
      $style .= 'body, p{font-family:\'' . esc_attr($asthir_body_fonts) . '\', sans-serif;}';
    }
    if ($asthir_font_size != '14') {
      $style .= 'body, p{font-size:' . esc_attr($asthir_font_size) . 'px;}';
    }
    if ($asthir_font_line_height != '24') {
      $style .= 'body, p{line-height:' . esc_attr($asthir_font_line_height) . 'px;}';
    }

    if ($asthir_head_fonts != 'Lato') {
      $style .= 'h1,h1 a, h2,h2 a, h3,h3 a, h4,h4 a, h5, h6{font-family:\'' . esc_attr($asthir_head_fonts) . '\', sans-serif;}';
    }
    if ($asthir_font_weight_head != '700') {
      $style .= 'h1, h2, h3, h4, h5, h6{font-weight:' . esc_attr($asthir_font_weight_head) . ';}';
    }
    $asthir_topfooter_bgcolor = get_theme_mod('asthir_topfooter_bgcolor');
    if ($asthir_topfooter_bgcolor) {
      $style .= '.footer-top.bg-dark{background:' . $asthir_topfooter_bgcolor . '  !important;}';
    }
    $asthir_tftitle_color = get_theme_mod('asthir_tftitle_color');
    if ($asthir_tftitle_color) {
      $style .= '.footer-widget .widget-title{color:' . $asthir_tftitle_color . '  !important;}';
    }
    $asthir_tftext_color = get_theme_mod('asthir_tftext_color');
    if ($asthir_tftext_color) {
      $style .= '.footer-widget, .footer-widget p, .footer-widget a, .footer-widget #wp-calendar caption, .footer-widget .search-form input.search-submit{color:' . $asthir_tftext_color . '  !important;}';
    }
    $asthir_tflink_hovercolor = get_theme_mod('asthir_tflink_hovercolor');
    if ($asthir_tflink_hovercolor) {
      $style .= '.footer-widget a:hover{color:' . $asthir_tflink_hovercolor . '  !important;}';
    }
    $asthir_footer_bgcolor = get_theme_mod('asthir_footer_bgcolor');
    if ($asthir_footer_bgcolor) {
      $style .= 'footer.asthir-mfooter{background:' . $asthir_footer_bgcolor . '  !important;}';
    }
    $asthir_footer_color = get_theme_mod('asthir_footer_color');
    if ($asthir_footer_color) {
      $style .= 'footer.asthir-mfooter,footer.asthir-mfooter a,footer.asthir-mfooter p{color:' . $asthir_footer_color . '  !important;}';
    }
    $asthir_footerlink_hcolor = get_theme_mod('asthir_footerlink_hcolor');
    if ($asthir_footerlink_hcolor) {
      $style .= 'footer.asthir-mfooter a:hover,footer.asthir-mfooter a:focus{color:' . $asthir_footerlink_hcolor . '  !important;}';
    }





    wp_add_inline_style('asthir-main', $style);
  }
  add_action('wp_enqueue_scripts', 'asthir_inline_css');
endif;
