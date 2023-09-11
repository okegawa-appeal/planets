<?php
add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles' );
function theme_enqueue_styles() {
wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
}
remove_filter( 'pre_term_description', 'wp_filter_kses' );
get_template_part('func/setting/theme_setting');
get_template_part('func/welcart/header/my_header_img_slic');		/* ヘッダ画像のスライド設定 */

function download_contents(){
  global $wpdb;
  $table_name = $wpdb->prefix . 'pl_dl_contents';
  $query = "SELECT * FROM $table_name where email ='".usces_memberinfo( 'mailaddress1' ,'return')."'";
  $results = $wpdb->get_results($query, ARRAY_A);

  echo '<h3>ダウンロードコンテンツ</h3>';
  echo '<table><tr>';
  echo '<th scope="row">購入日</th>';
  echo '<th class="num">タイトル</th>';
  echo '<th><a href="#">ダウンロード</a></th>';
        foreach ($results as $row) {
            echo '<tr><td>'.$row['purchase_date'].'</td>';
            echo '<td>'.$row['item_name'].'</td>';
            echo '<td><a href="'.$row['path'].'" target="_blank">Download<span class="dashicons dashicons-admin-page"></span></a></td>';
            echo '</tr>';
        }
  echo '</tr></table>';
}
add_action('usces_action_memberinfo_page_header','download_contents');


?>