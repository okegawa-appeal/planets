<?php
add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles' );
function theme_enqueue_styles() {
wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
}
remove_filter( 'pre_term_description', 'wp_filter_kses' );
get_template_part('func/setting/theme_setting');
get_template_part('func/welcart/header/my_header_img_slic');		/* ヘッダ画像のスライド設定 */

?>