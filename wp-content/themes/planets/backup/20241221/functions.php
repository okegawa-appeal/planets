<?php
#### CSSに日付を付与し、確実にロードさせる ####
add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles' );
function theme_enqueue_styles() {
  wp_enqueue_style( 'style', get_stylesheet_directory_uri() . '/style.css', array(), date("ymdHis", filemtime( get_stylesheet_directory().'/style.css')) );

  //くじ引き結果画面のみJS/CSSを追加
  if ( is_page('lottery') ) {
    wp_enqueue_style( 'animate', get_stylesheet_directory_uri() . '/css/animate.min.css', array( 'style' ) );
    wp_enqueue_style( 'normalize', get_stylesheet_directory_uri() . '/css/normalize.min.css', array( 'style' ) );
    wp_enqueue_style( 'lottery', get_stylesheet_directory_uri() . '/css/lottery.css', array( 'style' ) );
    wp_enqueue_style( 'pagelottery', get_stylesheet_directory_uri() . '/css/page-lottery.css', array( 'style' ) );
    wp_enqueue_script( 'jquery.min.js', get_stylesheet_directory_uri() . '/js/jquery.min.js');
//    wp_enqueue_script( 'animatedModal.min.js', get_stylesheet_directory_uri() . '/js/animatedModal.min.js');
    wp_enqueue_script( 'particles.min.js', 'https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js');
    wp_enqueue_script( 'lottery.js', get_stylesheet_directory_uri() . '/js/lottery.js' );
  }
  if(is_category()){
    wp_enqueue_style( 'lottery_category', get_stylesheet_directory_uri() . '/css/lottery_category.css', array( 'style' ) );
  }
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
    'edit-tags.php?taxonomy=category&orderby=id&order=desc', // ④スラッグ名
    '', // ⑤遷移後に実行する関数
    'dashicons-category', // ⑥アイコン
    '13' // ⑦表示位置
  );
}
add_action('admin_menu', 'add_custom_menu');

#### お問い合わせに会員情報自動登録 ####
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

  $query = "SELECT * FROM $table_name where ( mem_id = '".usces_memberinfo('ID','return')."' or email ='".usces_memberinfo( 'mailaddress1' ,'return')."' ) and open = true order by id desc";
  $results = $wpdb->get_results($query, ARRAY_A);

  
  echo '<h3>ダウンロードコンテンツ</h3>';
  echo '<table><tr>';
  echo '<th class="num">タイトル</th>';
  echo '<th scope="row">DL期限</th>';
  echo '<th><a href="#">ダウンロード</a></th>';
        foreach ($results as $row) {
            echo '<tr>';
            echo '<th>'.$row['item_name'].'</td>';
            echo '<td >'.$row['purchase_date'].'</td>';
            echo '<td><a href="'.$row['path'].'" target="_blank">Download<span class="dashicons dashicons-admin-page"></span></a></td>';
            echo '</tr>';
        }
  echo '</tr></table>';

}
add_action('usces_action_memberinfo_page_header','download_contents');

#### カテゴリページに画像を追加 ####
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
  <div class="form-field form-required">
    <label for="category-genre">ジャンル</label>
    <select name="category-genre" id="category-genre">
      <option value="normal">通常</option>
      <option value="premium">PREMIUM</option>
      <option value="lumistar">LUMISTAR</option>
    </select>
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
  <tr class="form-field term-image-wrap">
    <th scope="row"><label for="category-genre">ジャンル</label></th>
    <td>
    <?php $genre =  get_term_meta( $tag->term_id, 'category-genre', true ); ?>
    <?php echo $genre ; ?>
    <select name="category-genre" id="category-genre">
      <option value="normal" <?php echo $genre=='normal'?'selected':''; ?>>通常</option>
      <option value="premium" <?php echo $genre=='premium'?'selected':''; ?>>PREMIUM</option>
      <option value="lumistar" <?php echo $genre=='lumistar'?'selected':''; ?>>LUMISTAR</option>
    </select>
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
  $key = 'category-genre';
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

      //銀行振込期限が含まれており期限切れの場合
      $bank_expire_date = get_post_meta( $cart['post_id'], 'bank_expire_date', true );
      $bank_expire_time = get_post_meta( $cart['post_id'], 'bank_expire_time', true );  
      date_default_timezone_set('Asia/Tokyo');
      $bank_expire = strtotime($bank_expire_date . ' ' . $bank_expire_time . ' JST');
      $currentTime = time();
      if ( isset( $bank_expire_date )  && !empty($bank_expire_date)) {
        if ($currentTime > $bank_expire) {
          foreach ($payments as $key => $payment) {
            if (isset($payment['name']) && stripos($payment['name'],'銀行振込')!==false) {
                unset($payments[$key]);
            }
          }
        }
      }

      if(in_array($sku, $mysku)){
        echo 'testモード';
        echo 'この表示が出たら決済が正しく行われません。';
        echo 'お手数ですがお問い合わせよりお知らせください。';
        echo '<br>';
      }else{
        //bookend商品の場合はクレジット決済に限定する
        //$sku = wel_get_sku($cart['post_id'],$sku);
        $bookendid = get_post_meta($cart['post_id'], 'contentsid1', true ); // 現在の値を取得
        if(isset($bookendid) && !empty($bookendid)){
          $payments = array(
            array(
                'id' => 1,
                'name' => 'クレジット決済', //支払方法名
                'explanation' => 'VISA/MASTER/JCB/AMEXをご利用いただけます。', //説明
                'settlement' => 'acting_zeus_card', //決済種別
                'module' => '', //決済モジュール
                'sort' => 1, //表示順序
                'use' => 'activate', //activeで「使用」
            ),
          );  
        }
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
    $query->set( 'orderby', 'meta_value' );   
    $query->set( 'meta_key', 'itemCode' );
		$query->set( 'order', 'ASC' );
		return;
	}
}

#### カテゴリ中の商品ソート用にitemcodeをmetaに保存 ####
add_action( 'pre_get_posts', 'getcatorder' );

function set_custom_field( $post_id, $post ) {
   $product = wel_get_product( $post_id );
   $itemcode = $product['itemCode'];
   if( $itemcode ) {
      update_post_meta( $post_id, 'itemcode', $itemcode );
   }
}
add_action( 'save_post', 'set_custom_field', 99, 2 );

#### SKUにbookend contentsidを追加 ####
add_filter( 'usces_filter_sku_meta_form_advance_title', 'add_new_sku_meta_title'); //項目を追加
function add_new_sku_meta_title(){
	return '<th colspan="2">bookend Contents ID</th>';
}

add_filter( 'usces_filter_sku_meta_form_advance_field', 'add_new_sku_meta_field'); //フィールドを新規追加
function add_new_sku_meta_field(){
	return '<td colspan="2" class="item-sku-zaikonum"></td>';
}

add_filter( 'usces_filter_sku_meta_row_advance', 'add_new_sku_meta_row_advance',10,2); //フィールドを追加
function add_new_sku_meta_row_advance( $default_field, $sku ){
	$metaname = 'itemsku[' .$sku["meta_id"]. '][skuadvance]';
	return '<td colspan="2" class="item-sku-zaikonum">'.$sku["advance"].'</td>';
}

add_filter( 'usces_filter_add_item_sku_meta_value', 'add_new_sku_meta'); //新規項目を作成
function add_new_sku_meta($value){
	$skuadvance = isset($_POST['newskuadvance']) ? $_POST['newskuadvance'] : '';
	$value['advance'] = $skuadvance;
	return $value;
}

add_filter( 'usces_filter_up_item_sku_meta_value', 'up_new_sku_meta'); //項目を変更
function up_new_sku_meta($value){
	$skuadvance = isset($_POST['skuadvance']) ? $_POST['skuadvance'] : '';
	$value['advance'] = $skuadvance;
	return $value;
}

add_filter( 'usces_filter_item_save_sku_metadata', 'save_new_sku_meta',10,2 ); //項目を保持
function save_new_sku_meta( $skus, $mid ){
	$skuadvance = isset($_POST['itemsku'][$mid]['skuadvance']) ? $_POST['itemsku'][$mid]['skuadvance']: '';
	$skus['advance'] = $skuadvance;
	return $skus;
}

#### 決済確定時の処理 ####
function custome_usces_action_reg_orderdata( $args )
{
  $carts = $args['cart'];
  $email = $args['entry']['delivery']['mailaddress1'];
  foreach($carts as $cart){
    $sku = wel_get_sku($cart['post_id'],$cart['sku']);
    $bookendid = $sku['advance'];
  }
}
#add_action( 'usces_action_reg_orderdata', 'custome_usces_action_reg_orderdata' );

#### 決済確定時の処理 ####
function custome_usces_action_cartcompletion_page_body( $entries , $carts ){
  $email = $entries['customer']['mailaddress1'];
  $payment = $entries['order']['payment_name'];
  foreach($carts as $cart){
    $sku = wel_get_sku($cart['post_id'],$cart['sku']);
    $raffle_use = get_post_meta($cart['post_id'], 'raffle_use', true ); // 現在の値を取得
    if($raffle_use == '1' || $raffle_use == '2'){
      $order_id = $entries['order']['ID'];
      $itemcount = 0;
      if(substr($sku['code'],-strlen("1")) === "1"){
        $itemcount = 1*$cart['quantity'];
      } else if(substr($sku['code'],-strlen("10")) === "10"){
        $itemcount = 10*$cart['quantity'];
      }
      if($raffle_use == '2'){
        purchase_nolimit($cart['post_id'],$order_id,$itemcount);
      }else{
        echo "まだサポートしていません";
      }
    }

    //bookend商品登録
    //$bookendid = trim($sku['advance']);
    if($payment=='クレジット決済'){
      $contentsid1 = get_post_meta($cart['post_id'], 'contentsid1', true ); // 現在の値を取得
      $publish_date1 = get_post_meta($cart['post_id'], 'publish_date1', true ); // 現在の値を取得
      if(isset($contentsid1) && !empty($contentsid1)){
        bookend_entry_data($email,$contentsid1,$cart['post_id'],$publish_date1);
      }
      $contentsid2 = get_post_meta($cart['post_id'], 'contentsid2', true ); // 現在の値を取得
      $publish_date2 = get_post_meta($cart['post_id'], 'publish_date2', true ); // 現在の値を取得
      if(isset($contentsid2) && !empty($contentsid2)){
        bookend_entry_data($email,$contentsid2,$cart['post_id'],$publish_date2);
      }
      $contentsid3 = get_post_meta($cart['post_id'], 'contentsid3', true ); // 現在の値を取得
      $publish_date3 = get_post_meta($cart['post_id'], 'publish_date3', true ); // 現在の値を取得
      if(isset($contentsid3) && !empty($contentsid3)){
        bookend_entry_data($email,$contentsid3,$cart['post_id'],$publish_date3);
      }
      $contentsid4 = get_post_meta($cart['post_id'], 'contentsid4', true ); // 現在の値を取得
      $publish_date4 = get_post_meta($cart['post_id'], 'publish_date4', true ); // 現在の値を取得
      if(isset($contentsid4) && !empty($contentsid4)){
        bookend_entry_data($email,$contentsid4,$cart['post_id'],$publish_date4);
      }
//        if(!is_array($bookendid)){
//        if(isset($bookendid) && !empty($bookendid)){
//          if($bookendid != 'Array'){
//              //bookend登録処理
//            bookend_entry_data($email,$bookendid,$cart['post_id']);
//          }
//        }
      }
    }
  }

add_action( 'usces_action_cartcompletion_page_body', 'custome_usces_action_cartcompletion_page_body' ,10,2);

#### 銀行振込決済完了時（ステータス変更)　####
function custom_usces_action_collective_order_reciept_each($id, $statusstr, $old_status){
  error_log('usces_action_collective_order_reciept_each');
  error_log("PLANETS テスト : 決済完了 " .$id.":".$statusstr.":".$old_status);
}
#add_action( 'usces_action_collective_order_reciept_each', 'custom_usces_action_collective_order_reciept_each' ,10,3);

#### オーダーのステータスが変わったフック　####
function custom_usces_action_update_orderdata( $new_orderdata, $old_status, $old_orderdata, $new_cart, $old_cart ){
  error_log('usces_action_update_orderdata');
  $message = 'PLANETS テスト = ' . print_r($new_orderdata,true);
  error_log($message);
}
#add_action( 'usces_action_update_orderdata', 'custom_usces_action_update_orderdata' ,10,5);

#### オーダーのステータスが変わったフック　####
//受注リストの一括ステータス変更でfireした
function custom_usces_action_collective_order_status( $param ){
  error_log('usces_action_collective_order_status');
  $message = 'PLANETS テスト = ' . print_r($param,true);
  $log_message_parts = str_split($message, 1000);  // 1000文字ごとに分割
  foreach ($log_message_parts as $part) {
      error_log($message);
  }
}
#add_action( 'usces_action_collective_order_status', 'custom_usces_action_collective_order_status' ,10,5);

#### オーダーのステータスが変わったフック　####
//受注リストの一括ステータス変更でfireした
function custom_usces_action_collective_order_status_each( $id, $statusstr, $old_status ){
  error_log('usces_action_collective_order_status_each');
  error_log("PLANETS テスト : 一括画面操作 " .$id.":".$statusstr.":".$old_status);
}

#add_action( 'usces_action_collective_order_status_each', 'custom_usces_action_collective_order_status_each' ,10,3);


#### 入金チェックフック　####
function custom_usces_action_acting_getpoint( $order_id, $add ){
  error_log('custom_usces_action_acting_getpoint');
  $order_data = wel_get_order( $order_id );
  $message = 'PLANETS テスト = ' . print_r($order_data,true);
  
  $log_message_parts = str_split($message, 1000);  // 1000文字ごとに分割
  foreach ($log_message_parts as $part) {
      error_log($part);
  }
}
#add_action( 'usces_action_acting_getpoint', 'custom_usces_action_acting_getpoint' ,10,2);

#### 商品画面に公開停止メタボックスを追加 ####
function add_custom_metabox() {
    $itemcode = ''; $page = '';
    if ( isset($_GET['post']) ) $itemcode = get_post_meta( absint($_GET['post']), '_itemCode', true );
    if ( isset($_GET['page']) ) $page = $_GET['page'];
    if ( $page == 'usces_itemedit' || $page == 'usces_itemnew' || $itemcode ) {
        add_meta_box( 'item-metabox', '公開終了日時', 'metabox_expire', 'post', 'side', 'low' );
    }
}
add_action( 'add_meta_boxes', 'add_custom_metabox' );

function metabox_expire() {
  $post_id = get_the_ID();
  $expire_date = get_post_meta( $post_id, 'expire_date', true ); // 現在の値を取得
  $expire_time = get_post_meta( $post_id, 'expire_time', true ); // 現在の値を取得

  $next_scheduled_time = wp_next_scheduled('do_expire_post',array($post_id));
  if(!empty($next_scheduled_time)){
    $timezone = new DateTimeZone('Asia/Tokyo');
    $next_scheduled_datetime = new DateTime('@' . $next_scheduled_time);
    $next_scheduled_datetime->setTimezone($timezone);
    $next_scheduled_jst = $next_scheduled_datetime->format('Y-m-d H:i:s');
  }
  echo '次回非公開時刻: ' . $next_scheduled_jst;

  // セキュリティのために追加
  wp_nonce_field( 'wp-nonce-key', '_wp_nonce_my_option' );
  ?>
  <div class="my-metabox">
  <label for="expire_date">日付</label></td><td>
  <input type="date" name="expire_date" size=50 id="expire_date" value="<?php echo $expire_date; ?>"><br>
  <label for="expire_time">時間</label>
  <select id="expire_time" name="expire_time">
  <?php 
  for ($hour = 0; $hour < 24; $hour++) {
      for ($minute = 0; $minute < 60; $minute += 30) {
          $time = sprintf('%02d:%02d', $hour, $minute);
          $selected = ($expire_time == $time) ? 'selected' : '';
          echo '<option value="' . $time . '" ' . $selected . '>' . $time . '</option>';
      }
  } ?>
  </select><br>
  <button id="btn" onclick="document.getElementById('expire_date').value = '';document.getElementById('expire_time').value = '';">clear</button>
  </div>
  <?php
}

#### 保存時にカスタムフィールドに公開停止日時を登録＆cronに登録 ####
function save_expire($post_id) {
    // セキュリティのため追加
    if ( ! isset( $_POST['_wp_nonce_my_option'] ) || ! $_POST['_wp_nonce_my_option'] ) return;
    if ( ! check_admin_referer( 'wp-nonce-key', '_wp_nonce_my_option' ) ) return;

    update_post_meta( $post_id, 'expire_date', $_POST['expire_date'] );
    update_post_meta( $post_id, 'expire_time', $_POST['expire_time'] );

    //TODO: 保存したが、聞かない場合がある。
    //https://teratail.com/questions/srif6om25hbh2o
    // 登録内容を表示させる様にしたため一旦スルー
    if ( isset( $_POST['expire_date'] )  && !empty($_POST['expire_date'])) {
        $time_stamp = strtotime($_POST['expire_date'] . ' ' . $_POST['expire_time'] . ' JST');
        wp_clear_scheduled_hook('do_expire_post', array($post_id));
        wp_schedule_single_event($time_stamp, 'do_expire_post', array($post_id));
    }
}
add_action('save_post', 'save_expire');

####  CRONで商品を非公開に変更 ####
function do_expire_post_update($pid) {
   wp_update_post(array( 'ID' => $pid, 'post_status' => 'private' ) );
}
add_action('do_expire_post', 'do_expire_post_update');

#### 商品画面に銀行振込の期限を追加 ####
function add_custom_bank_metabox() {
    $itemcode = ''; $page = '';
    if ( isset($_GET['post']) ) $itemcode = get_post_meta( absint($_GET['post']), '_itemCode', true );
    if ( isset($_GET['page']) ) $page = $_GET['page'];
    if ( $page == 'usces_itemedit' || $page == 'usces_itemnew' || $itemcode ) {
        add_meta_box( 'bank-metabox', '銀行振込締め切り', 'metabox_bank_expire', 'post', 'side', 'low' );
    }
}
add_action( 'add_meta_boxes', 'add_custom_bank_metabox' );

function metabox_bank_expire() {
        $post_id = get_the_ID();
        $bank_expire_date = get_post_meta( $post_id, 'bank_expire_date', true ); // 現在の値を取得
        $bank_expire_time = get_post_meta( $post_id, 'bank_expire_time', true ); // 現在の値を取得

        // セキュリティのために追加
        wp_nonce_field( 'wp-nonce-key', '_wp_nonce_my_option' );
        ?>
        <div class="my-bank_metabox">
        <label for="bank_expire_date">日付</label></td><td>
        <input type="date" name="bank_expire_date" size=50 id="bank_expire_date" value="<?php echo $bank_expire_date; ?>"><br>
        <label for="bank_expire_time">時間</label>
        <select id="bank_expire_time" name="bank_expire_time">
        <?php 
        for ($hour = 0; $hour < 24; $hour++) {
            for ($minute = 0; $minute < 60; $minute += 30) {
                $time = sprintf('%02d:%02d', $hour, $minute);
                $selected = ($bank_expire_time == $time) ? 'selected' : '';
                echo '<option value="' . $time . '" ' . $selected . '>' . $time . '</option>';
            }
        } ?>
        </select><br>
        <button id="btn" onclick="document.getElementById('bank_expire_date').value = '';document.getElementById('bank_expire_time').value = '';">clear</button>
        </div>
        <?php
}
#### 保存時にカスタムフィールドに銀行振込締切を追加 ####
function save_bank_expire($post_id) {
    // セキュリティのため追加
    if ( ! isset( $_POST['_wp_nonce_my_option'] ) || ! $_POST['_wp_nonce_my_option'] ) return;
    if ( ! check_admin_referer( 'wp-nonce-key', '_wp_nonce_my_option' ) ) return;

    update_post_meta( $post_id, 'bank_expire_date', $_POST['bank_expire_date'] );
    update_post_meta( $post_id, 'bank_expire_time', $_POST['bank_expire_time'] );
}
add_action('save_post', 'save_bank_expire');

#### 商品画面にくじ引き関連情報を追加 ####
function add_custom_raffle_metabox() {
    $itemcode = ''; $page = '';
    if ( isset($_GET['post']) ) $itemcode = get_post_meta( absint($_GET['post']), '_itemCode', true );
    if ( isset($_GET['page']) ) $page = $_GET['page'];
    if ( $page == 'usces_itemedit' || $page == 'usces_itemnew' || $itemcode ) {
        add_meta_box( 'raffle-metabox', 'くじ引き情報', 'metabox_raffle', 'post', 'side', 'low' );
    }
}
add_action( 'add_meta_boxes', 'add_custom_raffle_metabox' );

function metabox_raffle() {
        $post_id = get_the_ID();
        $raffle_use = get_post_meta( $post_id, 'raffle_use', true ); // 現在の値を取得
        if($raffle_use == '1' || $raffle_use == '2'){
          $skus = wel_get_skus($post_id);
          foreach($skus as $sku){
            if(substr($sku['code'],-strlen("1")) !== "1" &&substr($sku['code'],-strlen("10")) !== "10"  ){
              echo '<font color="red">くじ引きを行う際はSKUの末尾は1もしくは10の設定だけにしてください。</font>';
            }
          }
        }
        // セキュリティのために追加
        wp_nonce_field( 'wp-nonce-key', '_wp_nonce_my_option' );
        ?>
        <div class="my-raffle_metabox">
          <div class="toggle_button">
            <label for="raffle_use1" class="toggle_label">
            <input id="raffle_use1" name="raffle_use" class="toggle_input" value="0" type="radio" <?php echo $raffle_use=="0"?'checked':'' ?> /> 設定しない
            </label><br>
            <label for="raffle_use2" class="toggle_label">
            <!-- input id="raffle_use2" name="raffle_use" class="toggle_input" value="1" type="radio" <?php echo $raffle_use=="1"?'checked':'' ?> /--> 販売制限付きくじ引き商品に設定する (まだ利用できません)
            </label><br>
            <label for="raffle_use3" class="toggle_label">
            <input id="raffle_use3" name="raffle_use" class="toggle_input" value="2" type="radio" <?php echo $raffle_use=="2"?'checked':'' ?> /> 無制限くじ引き商品に設定する
            </label><br>
          </div>
        </div>
        <?php
}
#### 保存時にカスタムフィールドに銀行振込締切を追加 ####
function save_raffle($post_id) {
    // セキュリティのため追加
    if ( ! isset( $_POST['_wp_nonce_my_option'] ) || ! $_POST['_wp_nonce_my_option'] ) return;
    if ( ! check_admin_referer( 'wp-nonce-key', '_wp_nonce_my_option' ) ) return;
    update_post_meta( $post_id, 'raffle_use', $_POST['raffle_use'] );
    
}
add_action('save_post', 'save_raffle');

#### 会員情報に生年月日を追加 ####
add_filter( 'usces_filter_custom_field_input',  'my_filter_custom_field_input', 10, 4 );
function my_filter_custom_field_input( $html, $data, $custom_field, $position ) {
    $html = preg_replace('/<input type="text" name="custom_member\[birthday\]"([^>]*)>/i', '<input type="date" name="custom_member[birthday]"$1>', $html);
    return $html;
}

#### 商品画面にbookend metaboxを追加 ####
function add_custom_bookend_metabox() {
    $itemcode = ''; $page = '';
    if ( isset($_GET['post']) ) $itemcode = get_post_meta( absint($_GET['post']), '_itemCode', true );
    if ( isset($_GET['page']) ) $page = $_GET['page'];
    if ( $page == 'usces_itemedit' || $page == 'usces_itemnew' || $itemcode ) {
        add_meta_box( 'bookend-metabox', '電子書籍情報', 'metabox_bookend', 'post', 'side', 'low' );
    }
}
add_action( 'add_meta_boxes', 'add_custom_bookend_metabox' );

function metabox_bookend() {
  global $usces;
  $post_id = get_the_ID();
  $skus = $usces->get_skus( $post_id );
  $advance = $skus[0]['advance'];
  $title = contents_info($advance);
  $contentsid1 = get_post_meta( $post_id, 'contentsid1', true ); // 現在の値を取得
  if(isset($contentsid1) && !empty($contentsid1)){
    $contentsid1_title = contents_info($contentsid1);
  }
  $contentsid2 = get_post_meta( $post_id, 'contentsid2', true ); // 現在の値を取得
  if(isset($contentsid2) && !empty($contentsid2)){
    $contentsid2_title = contents_info($contentsid2);
  }
  $contentsid3 = get_post_meta( $post_id, 'contentsid3', true ); // 現在の値を取得
  if(isset($contentsid3) && !empty($contentsid3)){
    $contentsid3_title = contents_info($contentsid3);
  }
  $contentsid4 = get_post_meta( $post_id, 'contentsid4', true ); // 現在の値を取得
  if(isset($contentsid4) && !empty($contentsid4)){
    $contentsid4_title = contents_info($contentsid4);
  }
  $publish_date1 = get_post_meta( $post_id, 'publish_date1', true ); // 現在の値を取得
  $publish_date2 = get_post_meta( $post_id, 'publish_date2', true ); // 現在の値を取得
  $publish_date3 = get_post_meta( $post_id, 'publish_date3', true ); // 現在の値を取得
  $publish_date4 = get_post_meta( $post_id, 'publish_date4', true ); // 現在の値を取得
  // セキュリティのために追加
  wp_nonce_field( 'wp-nonce-key', '_wp_nonce_my_option' );
  ?>
  <div class="my-bookend_metabox">
      <label for="contentsid1" class="toggle_label">contents id(1冊目)</label>
      <input id="contentsid1" name="contentsid1" value="<?php echo $contentsid1 ?>" type="text" size="30"><br/>
      タイトル:<?php echo $contentsid1_title ?><br/>
      <label for="publish_date1" class="toggle_label">公開日</label>
      <input id="publish_date1" name="publish_date1" value="<?php echo $publish_date1 ?>" type="date" ><br/>
      <hr/>
      <label for="contentsid2" class="toggle_label">contents id(2冊目)</label>
      <input id="contentsid2" name="contentsid2" value="<?php echo $contentsid2 ?>" type="text"  size="30"><br/>
      タイトル:<?php echo $contentsid2_title ?><br/>
      <label for="publish_date2" class="toggle_label">公開日</label>
      <input id="publish_date2" name="publish_date2" value="<?php echo $publish_date2 ?>" type="date" ><br/>
      <hr/>
      <label for="contentsid3" class="toggle_label">contents id(3冊目)</label>
      <input id="contentsid3" name="contentsid3" value="<?php echo $contentsid3 ?>" type="text"  size="30"><br/>
      タイトル:<?php echo $contentsid3_title ?><br/>
      <label for="publish_date3" class="toggle_label">公開日</label>
      <input id="publish_date3" name="publish_date3" value="<?php echo $publish_date3 ?>" type="date" ><br/>
      <hr/>
      <label for="contentsid4" class="toggle_label">contents id(4冊目)</label>
      <input id="contentsid4" name="contentsid4" value="<?php echo $contentsid4 ?>" type="text"  size="30"><br/>
      タイトル:<?php echo $contentsid4_title ?><br/>
      <label for="publish_date4" class="toggle_label">公開日</label>
      <input id="publish_date4" name="publish_date4" value="<?php echo $publish_date4 ?>" type="date" ><br/>
      <hr/>
      <br>
  </div>
  <?php
}
#### 保存時にカスタムフィールドにbookend公開日を追加 ####
function save_bookend($post_id) {
    // セキュリティのため追加
    if ( ! isset( $_POST['_wp_nonce_my_option'] ) || ! $_POST['_wp_nonce_my_option'] ) return;
    if ( ! check_admin_referer( 'wp-nonce-key', '_wp_nonce_my_option' ) ) return;
    update_post_meta( $post_id, 'contentsid1', $_POST['contentsid1'] );
    update_post_meta( $post_id, 'contentsid2', $_POST['contentsid2'] );
    update_post_meta( $post_id, 'contentsid3', $_POST['contentsid3'] );
    update_post_meta( $post_id, 'contentsid4', $_POST['contentsid4'] );
    update_post_meta( $post_id, 'publish_date1', $_POST['publish_date1'] );
    update_post_meta( $post_id, 'publish_date2', $_POST['publish_date2'] );
    update_post_meta( $post_id, 'publish_date3', $_POST['publish_date3'] );
    update_post_meta( $post_id, 'publish_date4', $_POST['publish_date4'] );
}
add_action('save_post', 'save_bookend');

#### 商品画面のカテゴリ並び順を変更 ####

function taxonomy_checklist_checked_ontop_filter ($args)
{
  //print_r($args);
  //$args['checked_ontop'] = false;
  //$args['list_only'] = true;
  return $args;

}

add_filter('wp_terms_checklist_args','taxonomy_checklist_checked_ontop_filter');


#### 商品画面にoricon metaboxを追加 ####
function add_custom_oricon_metabox() {
  $itemcode = ''; $page = '';
  if ( isset($_GET['post']) ) $itemcode = get_post_meta( absint($_GET['post']), '_itemCode', true );
  if ( isset($_GET['page']) ) $page = $_GET['page'];
  if ( $page == 'usces_itemedit' || $page == 'usces_itemnew' || $itemcode ) {
      add_meta_box( 'oricon-metabox', 'オリコン登録情報', 'metabox_oricon', 'post', 'side', 'low' );
  }
}
add_action( 'add_meta_boxes', 'add_custom_oricon_metabox' );

function metabox_oricon() {
  global $usces;
  $post_id = get_the_ID();
  $jancode = get_post_meta( $post_id, 'jancode', true ); // 現在の値を取得
  $artistname = get_post_meta( $post_id, 'artistname', true ); // 現在の値を取得
  $mediatype = get_post_meta( $post_id, 'mediatype', true ); // 現在の値を取得
   // セキュリティのために追加
  wp_nonce_field( 'wp-nonce-key', '_wp_nonce_my_option' );
  ?>
  <div class="my-oricon_metabox">
      <label for="jancode" class="toggle_label">JAN CODE</label>
      <input id="jancode" name="jancode" value="<?php echo $jancode ?>" type="text" size="30"><br/>
      <br>
      <label for="artistname" class="toggle_label">アーティスト名</label>
      <input id="artistname" name="artistname" value="<?php echo $artistname ?>" type="text" size="30"><br/>
      <br>
      <label for="mediatype" class="toggle_label">種類</label>
      <SELECT id="mediatype" name="mediatype" value="<?php echo $mediatype ?>" >
        <OPTION></OPTION>
        <OPTION VALUE="CD">CD</OPTION>
        <OPTION VALUE="DVD">DVD</OPTION>
        <OPTION VALUE="BD">BD</OPTION>
        <OPTION VALUE="BOOK">BOOK</OPTION>
      </SELECT>

      <br>
  </div>
  <?php
}
#### 保存時にカスタムフィールドにbookend公開日を追加 ####
function save_oricon($post_id) {
  // セキュリティのため追加
  if ( ! isset( $_POST['_wp_nonce_my_option'] ) || ! $_POST['_wp_nonce_my_option'] ) return;
  if ( ! check_admin_referer( 'wp-nonce-key', '_wp_nonce_my_option' ) ) return;
  update_post_meta( $post_id, 'jancode', $_POST['jancode'] );
  update_post_meta( $post_id, 'artistname', $_POST['artistname'] );
  update_post_meta( $post_id, 'mediatype', $_POST['mediatype'] );
}
add_action('save_post', 'save_oricon');

function ends_with($haystack, $needle) {
  // URLの末尾のスラッシュを除去
  $haystack = rtrim($haystack, '/');
  $needle = rtrim($needle, '/');

  $length = strlen($needle);
  if ($length == 0) {
      return true;
  }
  return (substr($haystack, -$length) === $needle);
}

?>

