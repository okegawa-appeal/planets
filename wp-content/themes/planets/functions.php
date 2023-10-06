<?php
#### CSSに日付を付与し、確実にロードさせる ####
add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles' );
function theme_enqueue_styles() {
  wp_enqueue_style( 'style', get_stylesheet_directory_uri() . '/style.css', array(), date("ymdHis", filemtime( get_stylesheet_directory().'/style.css')) );
}

#### 会員登録必須にするため次へボタンを消す ####
function customize_customer_button() {
   return "";
}
add_filter( 'usces_filter_customer_button', 'customize_customer_button' );

#### 管理画面のメニュー名変更 ####
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

#### お問い合わせに自動登録 ####
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

#### マイページにダウンロードを追加 ####
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

#### カテゴリページに画像を追加 ####
// カテゴリー一覧ページの新規追加エリアに要素を追加するフック
add_action( 'category_add_form_fields', 'my_category_add_form_fields' );
function my_category_add_form_fields( $taxonomy ) {
  ?>
  <div class="form-field form-required term-image-wrap">
    <label for="category-image">画像(URL)</label>
    <input name="category-image" id="category-image" type="text" value="" size="40" aria-required="true"/>
    <p>サムネイル用の画像を設定します。※写真は縦横比1:1になるようにしてください</p>
    <input type="button" name="image_select" value="選択" />
    <input type="button" name="image_clear" value="クリア" />
    <div id="image_thumbnail" class="uploded-thumbnail">
    </div>
  </div>
  <script type="text/javascript">
  (function ($) {
      var custom_uploader;
      // ①選択ボタンを押した時の処理
      $("input:button[name=image_select]").click(function(e) {
          e.preventDefault();
          if (custom_uploader) {
              custom_uploader.open();
              return;
          }
          custom_uploader = wp.media({
              title: "画像を選択してください",
              library: {
                  type: "image"
              },
              button: {
                  text: "画像の選択"
              },
              multiple: false
          });
          custom_uploader.on("select", function() {
              var images = custom_uploader.state().get("selection");
              images.each(function(file){
                  $("input:text[name=category-image]").val("");
                  $("#image_thumbnail").empty();
                  $("input:text[name=category-image]").val(file.attributes.sizes.full.url);
                  $("#image_thumbnail").append('<img src="'+file.attributes.sizes.full.url+'" style="width:50%;height:auto;"/>');
              });
          });
          custom_uploader.open();
      });
      // ②クリアボタンを押した時の処理
      $("input:button[name=image_clear]").click(function() {
          $("input:text[name=category-image]").val("");
          $("#image_thumbnail").empty();
      });
  })(jQuery);
  </script>
  <?php
}
// カテゴリー編集画面に要素を追加するフック
add_action( 'category_edit_form_fields', 'my_category_edit_form_fields', 10, 2 );
function my_category_edit_form_fields( $tag, $taxonomy ) {
  ?>
  <tr class="form-field term-image-wrap">
    <th scope="row"><label for="category-image">画像(URL)</label></th>
    <td>
      <input name="category-image" id="category-image" type="text" value="<?php echo esc_url_raw( get_term_meta( $tag->term_id, 'category-image', true ) ); ?>" size="40" aria-required="true"/>
      <p>サムネイル用の画像を設定します。※写真は縦横比1:1になるようにしてください</p>
      <input type="button" name="image_select" value="選択" />
      <input type="button" name="image_clear" value="クリア" />
      <div id="image_thumbnail" class="uploded-thumbnail">
        <?php if (get_term_meta( $tag->term_id, 'category-image', true )): ?>
          <img src="<?php echo esc_url_raw( get_term_meta( $tag->term_id, 'category-image', true ) ); ?>" alt="選択中の画像" style="width:50%;height:auto;">
        <?php endif ?>
      </div>
    </td>
  </tr>
  <script type="text/javascript">
  (function ($) {
      var custom_uploader;
      // ①選択ボタンを押した時の処理
      $("input:button[name=image_select]").click(function(e) {
          e.preventDefault();
          if (custom_uploader) {
              custom_uploader.open();
              return;
          }
          custom_uploader = wp.media({
              title: "画像を選択してください",
              library: {
                  type: "image"
              },
              button: {
                  text: "画像の選択"
              },
              multiple: false
          });
          custom_uploader.on("select", function() {
              var images = custom_uploader.state().get("selection");
              images.each(function(file){
                  $("input:text[name=category-image]").val("");
                  $("#image_thumbnail").empty();
                  $("input:text[name=category-image]").val(file.attributes.sizes.full.url);
                  $("#image_thumbnail").append('<img src="'+file.attributes.sizes.full.url+'" style="width:50%;height:auto;"/>');
              });
          });
          custom_uploader.open();
      });
      // ②クリアボタンを押した時の処理
      $("input:button[name=image_clear]").click(function() {
          $("input:text[name=category-image]").val("");
          $("#image_thumbnail").empty();
      });
  })(jQuery);
  </script>
  <?php
}
function my_edit_category( $term_id ) {
  $key = 'category-image';
  if ( isset( $_POST[ $key ] ) && esc_url_raw( $_POST[ $key ] ) ) {
    update_term_meta( $term_id, $key, $_POST[ $key ] );
  } else {
    delete_term_meta( $term_id, $key );
  }
}
add_action( 'create_category', 'my_edit_category' );
add_action( 'edit_category', 'my_edit_category' );
function my_admin_scripts() {
  wp_enqueue_media();
}
add_action( 'admin_print_scripts', 'my_admin_scripts' );

#### 翻訳を子テーマに追加 ####
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


#### 会員フィールドに注釈追加 ####
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

#### 商品によって決済種別を変える ####
add_filter('usces_fiter_the_payment_method', 'my_the_payment_method', 10, 2);
function my_the_payment_method($payments, $value){
    global $usces;
    $carts = $usces->cart->get_cart();
    $mysku = array('test'); //特定の商品のSKU
    foreach($carts as $cart){
        $sku = $cart['sku'];
        if(in_array($sku, $mysku)){
          echo 'testモード';
          echo 'この表示が出たら決済が正しく行われません。';
          echo 'お手数ですがお問い合わせよりお知らせください。';
          print_r(wel_get_product($cart['post_id']));
        }else{
        }
    }
    return $payments;
}

#### カテゴリ中の商品並び順変更 ####
function getcatorder( $query ) {
	if ( is_admin() || ! $query->is_main_query() )
		return;

	if ( $query->is_category() ) {
		$query->set( 'posts_per_page', '-1' );
		$query->set( 'orderby', 'itemCode' );
		$query->set( 'order', 'ASC' );
		return;
	}
}
add_action( 'pre_get_posts', 'getcatorder' );

?>