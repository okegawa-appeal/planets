<?php
add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles' );
function theme_enqueue_styles() {
  wp_enqueue_style( 'style', get_stylesheet_directory_uri() . '/style.css', array(), date("ymdHis", filemtime( get_stylesheet_directory().'/style.css')) );
}
function customize_customer_button() {
   return "";
}
add_filter( 'usces_filter_customer_button', 'customize_customer_button' );
function add_custom_menu() {
  add_menu_page(
    'カテゴリ', // ①ページタイトル
    'カテゴリ', // ②ラベル
    'manage_options', // ③表示するユーザーの権限
    'edit-tags.php?taxonomy=category', // ④スラッグ名
    '', // ⑤遷移後に実行する関数
    'dashicons-category', // ⑥アイコン
    '13' // ⑦表示位置
  );
}
add_action('admin_menu', 'add_custom_menu');

function my_form_tag_filter($tag){
	if ( !is_array( $tag ) )
	return $tag;
	$user = usces_localized_name( usces_memberinfo( 'name1', 'return' ), usces_memberinfo( 'name2', 'return' ), 'return'); 
	$email =  usces_memberinfo( 'mailaddress1', 'return' ); 

	if(isset($user)) {
		$name = $tag['name'];
		if($name == 'your-name')
  		$tag['values'] = (array)$user;
	}
	if(isset($email)) {
		$name = $tag['name'];
		if($name == 'your-email')
  		$tag['values'] = (array)$email;
	}
	return $tag;
}
add_filter('wpcf7_form_tag', 'my_form_tag_filter', 11);

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


/***ウェルカート翻訳ファイルをオーバーライド***/

//ウェルカートプラグインのテキストドメインを指定
$welcart_text_domain = 'usces';

//オリジナル(元の)翻訳データ
$welcart_original_mofile = WP_PLUGIN_DIR.'/usc-e-shop/languages/'. $welcart_text_domain.'-ja.mo';

//変更後の自作翻訳データ(任意)※例では利用している子テーマのlanguagesフォルダ
$welcart_override_mofile = get_stylesheet_directory().'/languages/'.$welcart_text_domain.'-ja.mo';

//読み込まれている翻訳データをアンロード
unload_textdomain($welcart_text_domain);

//変更後の自作翻訳データをロードする
load_textdomain($welcart_text_domain, $welcart_override_mofile );

//オリジナル（元の）翻訳データをロードする
load_textdomain($welcart_text_domain, $welcart_original_mofile );

add_filter( 'usces_filter_after_zipcode', 'my_filter_after_zipcode', 10, 2);

function my_filter_after_zipcode( $str, $applyform ){
  return '111-1111(ハイフンも入れてください)';
}
add_filter( 'usces_filter_after_tel', 'my_filter_after_tel', 10, 2);
function my_filter_after_tel( $str, $applyform ){
return '03-9999-9999(ハイフンも入れてください)';
}
add_filter( 'usces_filter_after_fax', 'my_filter_after_fax', 10, 2);
function my_filter_after_fax( $str, $applyform ){
return '03-9999-9999(ハイフンも入れてください)';
}

?>