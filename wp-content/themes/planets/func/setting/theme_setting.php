<?php
/*****************************************************************************
CSSやスクリプトファイルの読み込み
*******************************************************************************/
function theme_setting() {
/* スクリプトファイルの読み込み */
wp_enqueue_script('jQuery',"https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js");// ①GoogleのJavaScriptライブラリ
wp_enqueue_script('slick',get_stylesheet_directory_uri().'/slick/slick.min.js');//②slickプラグイン
wp_enqueue_script('my_js_slick',get_stylesheet_directory_uri().'/js/planets.js');//③自作jQueryプログラム	
/* Welcartのファイルの読み込み */
wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
wp_enqueue_style( 'parent-cart' , get_template_directory_uri() . '/usces_cart.css', array('parent-style', 'usces_default_css') );
wp_enqueue_style('slick',get_stylesheet_directory_uri().'/slick/slick.css'); //④slick
wp_enqueue_style('slick_theme',get_stylesheet_directory_uri().'/slick/slick-theme.css');//⑤slick
}
add_action('wp_enqueue_scripts', 'theme_setting');
?>
