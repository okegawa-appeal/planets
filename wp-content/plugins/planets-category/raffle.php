<?php
/*
Plugin Name: Planets くじ商品管理
Description: Planets用のクジ式商品の設定
Version: 1.0
*/

if ( ! defined( 'ABSPATH' ) ) exit;

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

// Define custom WP_List_Table class for displaying custom table data
class PL_RaffleList_Table extends WP_List_Table
{
    // Define columns for the table
    function get_columns()
    {
        return array(
            'itemName' => 'くじ引き名',
            'prize_name' => '商品名',
            'prize_image' => '画像',
			'totalcount' => '総個数',
            'rate' => '当選確率',
            'control' => '編集'
        );
    }

    // Prepare data for the table
    function prepare_items()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'pl_raffle';

        $post_id = $_GET['post_id']?$_GET['post_id']:$_GET['post_id'];

        $per_page = 100;
        $current_page = $this->get_pagenum();
        $offset = ($current_page - 1) * $per_page;

        $search = isset($_REQUEST['s']) ? sanitize_text_field($_REQUEST['s']) : '';

        $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table_name where post_id = $post_id");

        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
        ));

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array($columns, $hidden, $sortable);

        $orderby = (!empty($_GET['orderby'])) ? sanitize_text_field($_GET['orderby']) : 'ID';
        $order = (!empty($_GET['order']) && in_array(strtoupper($_GET['order']), array('ASC', 'DESC'))) ? strtoupper($_GET['order']) : 'ASC';
        $sql = "select itemName,wp_pl_raffle.*,totalcount,ordered "
            ."FROM wp_pl_raffle  "
            ."left outer join wp_usces_item  "
            ."on wp_pl_raffle.post_id = wp_usces_item.post_id "
            ."left outer join (SELECT "
            ."  raffle_id, "
            ."  count(ID ) AS totalcount, "
            ."  sum(CASE WHEN order_id IS NOT NULL THEN 1 ELSE 0 END) AS ordered "
            ."FROM wp_pl_raffle_order "
            ."where wp_pl_raffle_order.post_id = $post_id "
            ."GROUP BY raffle_id "
            .") as ordercount "
            ."on ordercount.raffle_id = wp_pl_raffle.ID "
            ."where wp_pl_raffle.post_id = $post_id";
        
        $data = $wpdb->get_results( $sql . " ORDER BY $orderby $order LIMIT $per_page OFFSET $offset", ARRAY_A);

        $this->items = $data;
    }

    // Display each column's content
    function column_default($item, $column_name)
    {
		return isset($item[$column_name]) ? $item[$column_name] : '';
    }
    function column_prize_name($item){
        return '<a href="?page=planetsraffle&post_id='.$item['post_id'] .'&edit=' . $item['ID'] . '">'.$item['prize'] ."賞 ".$item['prize_name'].'</a><br>';
    }
    function column_totalcount($item){

        return $item['ordered'] ."/".$item['totalcount'];
    }
    function column_Rate($item){
        return $item['rate'] ."%";
    }
    function column_Prize_Image($item){
        return '<img src="' . $item['prize_image'] . '" width=100>' ;
    }
    // Make columns sortable
    function get_sortable_columns()
    {
        return array(
            'ID' => array('ID', false),
            'itemName' => array('itemName', false),
            'prize' => array('item_nprizeame', false),
            'prize_name' => array('prize_name', false),
            'prize_iamge' => array('prize_iamge', false),
            'totalcount' => array('totalcount',false),
            'rate' => array('rate',false)
        );
    }
    function column_Control($item){
        $data .= '<form method="post" style="display:inline;"><input type="hidden" name="post_id" value="' . $item['post_id'] . '"><input type="hidden" name="id" value="' . $item['ID'] . '"><input type="hidden" name="action" value="delete"><input type="submit" value="削除" onclick="return confirm(\'本当に削除しますか？\');"></form>';
        return $data;
    }
}
class PL_LotteryCategoryList_Table extends WP_List_Table
{
    // Define columns for the table
    function get_columns()
    {
        return array(
			'post_id' => 'ID',
			'itemName' => 'くじ引き名',
            'meta_value' => '制限',
            'download' => '結果DL'
        );
    }

    // Prepare data for the table
    function prepare_items()
    {
        global $wpdb;

        $per_page = 100;
        $current_page = $this->get_pagenum();
        $offset = ($current_page - 1) * $per_page;

        $search = isset($_REQUEST['s']) ? sanitize_text_field($_REQUEST['s']) : '';

        $total_items = $wpdb->get_var("SELECT COUNT(*) FROM wp_usces_item,wp_postmeta where wp_usces_item.post_id = wp_postmeta.post_id and meta_key = 'raffle_use' and meta_value = '1'");

        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
        ));

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array($columns, $hidden, $sortable);

        $orderby = (!empty($_GET['orderby'])) ? sanitize_text_field($_GET['orderby']) : 'post_id';
        $order = (!empty($_GET['order']) && in_array(strtoupper($_GET['order']), array('ASC', 'DESC'))) ? strtoupper($_GET['order']) : 'DESC';
         
        $data = $wpdb->get_results( "select wp_usces_item.post_id,itemName,meta_value from wp_usces_item,wp_postmeta where wp_usces_item.post_id = wp_postmeta.post_id and meta_key = 'raffle_use' and (meta_value = '1' or meta_value = '2') ORDER BY $orderby $order LIMIT $per_page OFFSET $offset", ARRAY_A);
        $this->items = $data;
    }

    // Display each column's content
    function column_default($item, $column_name)
    {
		return isset($item[$column_name]) ? $item[$column_name] : '';
    }
    function column_download($item)
    {
        return '<a href="?page=planetsraffle&action=download_csv&post_id=' . $item['post_id'] . '">結果DL</a><br>';
    }
    function column_meta_value($item)
    {
        if($item['meta_value'] == '1'){
            return '制限付き';
        }else if ($item['meta_value'] == '2'){
            return '制限なし';
        }
    }
    function column_itemname($item)
    {
        return '<a href="?page=planetsraffle&post_id=' . $item['post_id'] . '">'.$item['itemName'].'</a><br>';
    }
}

// https://tart.co.jp/wordpress-plugin-original-create/
//=================================================
// 管理画面に「とりあえずメニュー」を追加登録する
//=================================================
add_action('admin_menu', function(){
	
	//---------------------------------
	// メインメニュー①
	//---------------------------------	
    add_menu_page(
		'くじ引き商品' // ページのタイトルタグ<title>に表示されるテキスト
		, 'くじ引き商品'   // 左メニューとして表示されるテキスト
		, 'manage_options'       // 必要な権限 manage_options は通常 administrator のみに与えられた権限
		, 'planetsraffle'        // 左メニューのスラッグ名 →URLのパラメータに使われる /wp-admin/admin.php?page=toriaezu_menu
		, 'planets_raffle_contents' // メニューページを表示する際に実行される関数(サブメニュー①の処理をする時はこの値は空にする)
		, 'dashicons-awards'       // メニューのアイコンを指定 https://developer.wordpress.org/resource/dashicons/#awards
		, 3.1                             // メニューが表示される位置のインデックス(0が先頭) 5=投稿,10=メディア,20=固定ページ,25=コメント,60=テーマ,65=プラグイン,70=ユーザー,75=ツール,80=設定
	);
});

## 開封API定義
function raffle_api_endpoint() {
    register_rest_route('raffle/v1', '/open/', array(
        'methods' => 'POST',
        'callback' => 'raffle_api_callback',
    ));
    register_rest_route('raffle/v1', '/allopen/', array(
        'methods' => 'POST',
        'callback' => 'raffle_api_callback_allopen',
    ));
}
add_action('rest_api_init', 'raffle_api_endpoint');

// REST APIのコールバック関数
function raffle_api_callback(WP_REST_Request $request) {
    $id = $request->get_param('id');
    $order_id = $request->get_param('o');
    if (empty($id) || empty($order_id)) {
        return new WP_Error('invalid_params', 'id and o is required.', array('status' => 400));
    }
    global $wpdb;
    $data_to_update = array(
        'open' => true
    );

    // WHERE条件
    $where_condition = array(
        'ID' => $id,
        'order_id' => $order_id
    );

    // 更新を実行
    $updated_rows = $wpdb->update("wp_pl_raffle_order", $data_to_update, $where_condition);
    if ($updated_rows !== false) {
        $response = array('message' => 'update successfully', 'ID' => $id);
        return new WP_REST_Response($response, 200);
    } else {
        return new WP_Error('invalid_params', 'target not found.', array('status' => 400));
    }
}
// REST APIのコールバック関数
function raffle_api_callback_allopen(WP_REST_Request $request) {
    $mem_id = $request->get_param('mem');
    if (empty($mem_id) ) {
        return new WP_Error('invalid_params', 'mem is required.', array('status' => 400));
    }
    global $wpdb;

    $sql = "update wp_pl_raffle_order  inner join wp_usces_order "
        ."on wp_pl_raffle_order.order_id = wp_usces_order.ID "
        ."set open = true   "
        ."where mem_id = $mem_id "
        ."and open =false; ";

    // 更新を実行
    $updated_rows = $wpdb->query($wpdb->prepare($sql));
    if ($updated_rows !== false) {
        $response = array('message' => 'update successfully', 'MEM' => $mem_id);
        return new WP_REST_Response($response, 200);
    } else {
        return new WP_Error('invalid_params', 'target not found.', array('status' => 400));
    }
}
function raffle_api_call($id,$order_id){
	$getuserurl = home_url().'/wp-json/raffle/v1/open/';
	$args = array(
		'method' => 'POST',
		'httpversion' => '1.1',
		'headers'  => array(
				'Content-Type' => 'application/x-www-form-urlencoded;charset=UTF-8',
		),
		'body' => array(
			'id' => $id,
			'o' => $order_id
		)
	);
	$response 	= wp_remote_post( $getuserurl, $args );
	if (is_wp_error($response)) {
		$result =  'HTTPリクエストエラー: ' . $response->get_error_message();
	} else {
		// ステータスコード
		$result =  'ステータスコード: ' . wp_remote_retrieve_response_code($response) . "\n";

		// レスポンスボディ
		$result = $result .  'レスポンスボディ: ' . wp_remote_retrieve_body($response) . "\n";
	}
}
function raffle_api_call_allopen($mem_id){
	$getuserurl = home_url().'/wp-json/raffle/v1/allopen/';
	$args = array(
		'method' => 'POST',
		'httpversion' => '1.1',
		'headers'  => array(
				'Content-Type' => 'application/x-www-form-urlencoded;charset=UTF-8',
		),
		'body' => array(
			'mem' => $mem_id,
		)
	);
	$response 	= wp_remote_post( $getuserurl, $args );
	if (is_wp_error($response)) {
		$result =  'HTTPリクエストエラー: ' . $response->get_error_message();
	} else {
		// ステータスコード
		$result =  'ステータスコード: ' . wp_remote_retrieve_response_code($response) . "\n";
		// レスポンスボディ
		$result = $result .  'レスポンスボディ: ' . wp_remote_retrieve_body($response) . "\n";
	}
}
//=================================================
// イベント表示系Function
//=================================================

// データを追加
function raffle_insert_data($post_id,$prize,$prize_name,$prize_image,$totalcount,$rate,$raffle_use) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'pl_raffle';
    $result = $wpdb->insert(
        $table_name,
        array(
            'post_id' => $post_id,
            'prize' => $prize,
            'prize_name' => $prize_name,
            'prize_image' => $prize_image,
            'rate' => $rate
        )
    );
    $id = $wpdb->insert_id;
    //販売制限ありの場合のみレコードを入れる
    if($raffle_use == '1'){
        $sql = "insert into wp_pl_raffle_order(post_id,raffle_id) select $post_id,$id  FROM information_schema.tables LIMIT $totalcount";
        $wpdb->query($sql);
    }
    if ($result === false) {
        echo "Data insertion failed: " . $wpdb->last_error;
    }
}

// データを読み取り
function raffle_read_data($id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'pl_raffle';
    $sql = "select wp_pl_raffle.*,totalcount "
        ."FROM wp_pl_raffle  "
        ."left outer join (SELECT "
        ."  raffle_id, "
        ."  count(ID ) AS totalcount "
        ."FROM wp_pl_raffle_order "
        ."where wp_pl_raffle_order.raffle_id = $id "
        .") as ordercount "
        ."on ordercount.raffle_id = wp_pl_raffle.ID "
        ."where wp_pl_raffle.ID = $id";
    $result = $wpdb->get_row($sql, ARRAY_A);
    return $result;
}

// データを更新
function raffle_update_data($id,$post_id,$prize,$prize_name,$prize_image,$rate) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'pl_raffle';

    $result = $wpdb->update(
        $table_name,
        array(
            'post_id' => $post_id,
            'prize' => $prize,
            'prize_name' => $prize_name,
            'prize_image' => $prize_image,
            'rate' => $rate
        ),
        array('id' => $id)
    );
    if ($result === false) {
        echo "Data insertion failed: " . $wpdb->last_error;
    }
}

// データを削除
function raffle_delete_data($id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'pl_raffle';
    $wpdb->delete(
        $table_name,
        array('id' => $id)
    );
    $sql = "delete from wp_pl_raffle_order where raffle_id = $id and order_id is null ";
    $wpdb->query($sql);
    if ($result === false) {
        echo "Data insertion failed: " . $wpdb->last_error;
    }
}
// テスト購入(販売制限あり)
function test_purchase($post_id,$order_id,$itemcount) {
    global $wpdb;
    $sql ="update wp_pl_raffle_order "
        ." set order_id = $order_id "
        ." where post_id = $post_id and order_id IS NULL "
        ." ORDER BY RAND() "
        ." LIMIT $itemcount";
    $wpdb->query($sql);
    if ($result === false) {
        echo "Data insertion failed: " . $wpdb->last_error;
    }
}
// テスト購入(販売制限なし)
function purchase_nolimit($post_id,$order_id,$itemcount) {
    global $wpdb;
    for($i = 0;$i < $itemcount ; $i++) {
       $sql = "insert into wp_pl_raffle_order(post_id,raffle_id,order_id) "
            ."select $post_id,ID,$order_id FROM  "
            ."(select A1.ID,A1.rate,sum(A2.rate)  sum   "
            ."FROM (select * FROM wp_pl_raffle where post_id = $post_id) A1   "
            ."inner join (select * FROM wp_pl_raffle where post_id = $post_id) A2  "
            ."on A1.ID >= A2.ID  "
            ."group by A1.ID,A1.rate  "
            ."order by A1.ID) as b0,  "
            ."(select rand()*100 rand) as b1  "
            ."where b0.sum >= b1.rand  "
            ."limit 1";
        $wpdb->query($sql);
        
        if ($result === false) {
            echo "Data insertion failed: " . $wpdb->last_error;
        }
    }
}
// テスト購入リセット
function test_purchase_reset($post_id) {
    global $wpdb;
    $sql ="update wp_pl_raffle_order "
        ." set order_id = NULL "
        ." where post_id = $post_id ";
    $wpdb->query($sql);
    if ($result === false) {
        echo "Data insertion failed: " . $wpdb->last_error;
    }
}
// テスト購入リセット
function test_purchase_reset_nolimit($post_id) {
    global $wpdb;
    $sql ="delete  from wp_pl_raffle_order "
        ." where post_id = $post_id ";
    $wpdb->query($sql);
    if ($result === false) {
        echo "Data insertion failed: " . $wpdb->last_error;
    }
}

function download_csv($post_id)
{
    ob_clean();
    ob_start();
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: private", false);
    header("Content-Type: text/csv");
    header('Content-Disposition: attachment; filename="export.csv"');
    global $wpdb;
    $sql =  " SELECT "
        ." wp_pl_raffle_order.order_id as 注文番号 "
        ." ,wp_usces_order.order_date as 注文日時 "
        ." ,mem_id as 会員No "
        ." ,mem_email as Eメール "
        ." ,CONCAT(mem_name1,' ',mem_name2) as 氏名 "
        ." ,CONCAT(mem_name3,' ',mem_name4) as フリガナ "
        ." ,nickname.meta_value as ニックネーム "
        ." ,nickname_kana.meta_value as ニックネーム（読み方） "
        ." ,mem_tel as 電話番号 "
        ." ,CONCAT(order_name1,' ',order_name2) as 配送先氏名 "
        ." ,CONCAT(order_name3,' ',order_name4) as 配送先フリガナ "
        ." ,order_zip as 配送先郵便番号 "
        ." ,'日本' as 配送先国 "
        ." ,order_pref as 配送先都道府県 "
        ." ,order_address1 as 配送先市町村 "
        ." ,order_address2 as 配送先番地 "
        ." ,order_address3 as 配送先ビル名 "
        ." ,order_tel as 配送先電話番号 "
        ." ,order_payment_name as 支払方法 "
        ." ,order_status as ステータス "
        ." ,wp_usces_skus.code as SKUコード "
        ." ,itemName as 商品名 "
        ." ,CONCAT(wp_usces_skus.name,' ',prize,'賞 ',prize_name) as SKU表示名 "
        ." ,quantity as 数量 "
        ." ,wp_usces_ordercart.price as 単価 "
        ."  FROM wp_pl_raffle_order "
        ."  ,wp_pl_raffle "
        ."  ,wp_usces_member "
        ."  ,wp_usces_item "
        ."  ,wp_usces_skus "
        ."  ,wp_usces_ordercart "
        ."  ,wp_usces_order "
        ."  LEFT OUTER JOIN (select meta_value,member_id FROM wp_usces_member_meta where meta_key = 'csmb_nickname')    as nickname ON nickname.member_id = wp_usces_order.mem_id  "
        ."  LEFT OUTER JOIN (select meta_value,member_id FROM wp_usces_member_meta where meta_key = 'csmb_nickname_kana') as nickname_kana  ON nickname_kana.member_id = wp_usces_order.mem_id  "
        ."  WHERE wp_pl_raffle_order.raffle_id =  wp_pl_raffle.ID   "
        ."  AND wp_pl_raffle_order.post_id = $post_id "
        ."  AND wp_usces_member.ID = mem_id  "
        ."  AND wp_pl_raffle_order.order_id is not null  "
        ."  AND wp_usces_ordercart.order_id = wp_usces_order.ID "
        ."  AND wp_usces_item.post_id = wp_pl_raffle_order.post_id "
        ."  AND wp_usces_skus.code = wp_usces_ordercart.sku_code "
        ."  AND wp_usces_order.ID = wp_pl_raffle_order.order_id "
        ."  ORDER BY wp_usces_order.ID; ";
    $data = $wpdb->get_results($sql, ARRAY_A);

    $csv_data = "prize,prize_name,prize_sku,order_email,order_name1,order_name2,order_zip,order_pref,order_address1,order_address2,order_address3,order_tel,order_payment_name,wp_usces_order.order_date,order_status\n";
//    foreach ($data as $item) {
//        $csv_data .= "{$item['prize']},{$item['prize_name']},{$item['prize_sku']},{$item['order_email']},{$item['order_name1']},{$item['order_name2']},{$item['order_zip']},{$item['order_pref']},{$item['order_address1']},{$item['order_address2']},{$item['order_address3']},{$item['order_tel']},{$item['order_payment_name']},{$item['order_date']},{$item['order_status']}\n";
//    }
//    @header("Content-Transfer-Encoding: binary");
    echo $csv_data;
    //ob_end_flush(); // 出力バッファのフラッシュ
    exit;

}
//=================================================
// サブメニューイベント表示
//=================================================
function planets_raffle_contents() {
    global $wpdb;
    if (isset($_GET['action']) && $_GET['action'] === 'download_csv') {
        $post_id = $_GET['post_id']?$_GET['post_id']:$_GET['post_id'];
        download_csv($post_id);
        exit;
    }else if(isset($_GET['post_id']) || isset($_GET['post_id'])){
        ob_start();
        $post_id = $_GET['post_id']?$_GET['post_id']:$_GET['post_id'];
        $raffle_use = get_post_meta( $post_id, 'raffle_use', true ); // 現在の値を取得

        // フォームからのデータを処理
        if (isset($_POST['action'])) {
            $action = $_POST['action'];

            if ($action === 'create') {
                $post_id = intval($_POST['post_id']);
                $prize = $_POST['prize'];
                $prize_name = sanitize_text_field($_POST['prize_name']);
                $prize_image = sanitize_url($_POST['prize_image']);
                if($raffle_use == '1'){
                    $totalcount = intval($_POST['totalcount']);
                    $rate = 0;
                }else if($raffle_use == '2'){
                    $totalcount = 0;
                    $rate = $_POST['rate'];
                }
                raffle_insert_data($post_id,$prize,$prize_name,$prize_image,$totalcount,$rate,$raffle_use);
            } elseif ($action === 'update') {
                $id = intval($_POST['id']);
                $post_id = intval($_POST['post_id']);
                $prize = $_POST['prize'];
                $prize_name = sanitize_text_field($_POST['prize_name']);
                $prize_image = sanitize_url($_POST['prize_image']);
                if($raffle_use == '1'){
                    $totalcount = intval($_POST['totalcount']);
                    $rate = 0;
                    //販売制限付きの場合のみ
                    $sql = "select wp_pl_raffle.*,totalcount,ordered "
                        ."FROM wp_pl_raffle  "
                        ."left outer join (SELECT "
                        ."  raffle_id, "
                        ."  count(ID ) AS totalcount, "
                        ."  sum(CASE WHEN order_id IS NOT NULL THEN 1 ELSE 0 END) AS ordered "
                        ."FROM wp_pl_raffle_order "
                        ."where wp_pl_raffle_order.raffle_id = $id "
                        .") as ordercount "
                        ."on ordercount.raffle_id = wp_pl_raffle.ID "
                        ."where wp_pl_raffle.ID = $id";
                    $before_data = $wpdb->get_results($sql, ARRAY_A);
                    //増やす場合
                    if($totalcount > $before_data[0]['totalcount']){
                        $diff = $totalcount - $before_data[0]['totalcount'];
                        //既存よりも増やす場合、増加数分orderを追加
                        $sql = "insert into wp_pl_raffle_order(post_id,raffle_id) select $post_id,$id FROM information_schema.tables LIMIT $diff";
                        $wpdb->query($sql);
                        raffle_update_data($id, $post_id,$prize,$prize_name,$prize_image,$rate);
                    //減らす場合
                    }else if($totalcount < $before_data[0]['totalcount']){
                        if($totalcount < $before_data[0]['ordered']){
                            //減らす数値が購入済みよりも少ない場合
                            echo '<div class="error"><p>購入済み数よりも減らすことはできません</p></div>';
                        }else{
                            //減らす数値が購入済みよりも多い場合
                            //減らす数分orderがnullのレコードより削除を行う delete limit
                            //新しい数値をtotalcountに入れる
                            $diff = $before_data[0]['totalcount'] - $totalcount  ;
                            $sql = "delete from wp_pl_raffle_order where raffle_id = $id and order_id is null limit $diff";
                            $wpdb->query($sql);
                            raffle_update_data($id, $post_id,$prize,$prize_name,$prize_image,$rate);
                        }
                    }
                }else if($raffle_use == '2'){
                    $totalcount = 0;
                    $rate = $_POST['rate'];
                    raffle_update_data($id, $post_id,$prize,$prize_name,$prize_image,$rate);
                }
            } elseif ($action === 'delete') {
                $id = intval($_POST['id']);
                raffle_delete_data($id);
            } elseif ($action === 'purchase') {
                $post_id = intval($_POST['post_id']);
                $order_id = intval($_POST['order_id']);
                $itemcount = $_POST['itemcount'];
                test_purchase($post_id,$order_id,$itemcount);
            } elseif ($action === 'purchase_nolimit') {
                $post_id = intval($_POST['post_id']);
                $order_id = intval($_POST['order_id']);
                $itemcount = $_POST['itemcount'];
                purchase_nolimit($post_id,$order_id,$itemcount);
            } elseif ($action === 'purchase_reset') {
                $post_id = intval($_POST['post_id']);
                if($raffle_use == '1'){
                    test_purchase_reset($post_id);
                }else if($raffle_use == '2'){
                    test_purchase_reset_nolimit($post_id);
                }
            } elseif ($action === 'raffle_open') {
                $post_id = intval($_POST['post_id']);
                $id = intval($_POST['id']);
                $order_id = intval($_POST['order_id']);
                raffle_api_call($id,$order_id);
            } elseif ($action === 'raffle_allopen') {
                $mem_id = intval($_POST['mem_id']);
                raffle_api_call_allopen($mem_id);
            }
        }

        // データの読み取りとフォームの表示
        $data = array(
            'ID' => '',
            'post_id' => '',
            'prize' => '',
            'prize_name' => '',
            'prize_image' => '',
            'totalcount' => '',
            'remaining' => ''
        );

        if (isset($_GET['edit'])) {
            $id = intval($_GET['edit']);
            $data = raffle_read_data($id);
        }

        $sumratesql = "select sum(rate) from wp_pl_raffle where post_id = $post_id";
        $sumrate = $wpdb->get_var($sumratesql);
        if($raffle_use == '2' && $sumrate != 100){
            echo '<div class="error"><p>合計が100%になっていません</p></div>';
        }        


        wp_enqueue_script( 'media-uploader-main-js', plugins_url( 'js/media-uploader-main.js', __FILE__ ), array( 'jquery' ) );
        wp_enqueue_media();
        ?>
        <?php  if($raffle_use == '1'){ ?>
        <h2>くじ引き商品管理(販売制限付き)</h2>
        <?php }else if($raffle_use == '2'){ ?>
        <h2>くじ引き商品管理(無制限)</h2>
        <?php } ?>

        <!-- データ入力フォーム -->
        <table>
        <form method="post">
            <tr>
            <input type="hidden" name="id" size=2 value="<?php echo $data['ID']; ?>" required>
            <input type="hidden" name="post_id" size=2 value="<?php echo $post_id; ?>" required>
            <td>
            <label for="prize">賞</label></td><td>
            <select id="prize" name="prize">
            <option value="">選択してください</option>
            <?php 
            for($i = "A"; $i != "K"; $i++) {
                    $selected = ($i == $data['prize']) ? 'selected' : '';
                    echo '<option value="' . $i . '" ' . $selected . '>' . $i . '賞</option>';
            } ?>
            </select>
            </td></tr><tr><td>
            <label for="prize_name">商品名</label></td><td>
            <input type="text" name="prize_name" size=50 id="prize_name" value="<?php echo $data['prize_name']; ?>" required>
            </td></tr><tr><td>
            <label for="prize_image">商品画像</label></td><td>
            <?php if($data['prize_image'] == ''){ ?>
            <p><img id="image-view" src="<?php echo plugins_url( 'images/noimage.png', __FILE__ ) ?>" width="100"></p>
            <?php }else{ ?>
            <p><img id="image-view" src="<?php echo $data['prize_image']; ?>" width="100" readonly></p>
            <?php } ?>
            </div>
            <p><input id="image" type="text" name="prize_image" class="large-text" value="<?php echo $data['prize_image']; ?>"></p>
            </td></tr><tr><td>
            <?php  if($raffle_use == '1'){ ?>
            <label for="totalcount">当選本数</label></td><td>
            <input type="text" name="totalcount" size=50 id="totalcount" value="<?php echo $data['totalcount']; ?>" required>
            <?php }else if($raffle_use == '2'){ ?>
            <label for="rate">当選確率</label></td><td>
            <input type="text" name="rate" size=50 id="rate" value="<?php echo $data['rate']; ?>" required>
            <?php } ?>
            </td></tr><tr><td></td><td>
            <input type="hidden" name="action" value="<?php echo $data['ID'] ? 'update' : 'create'; ?>">
            <input type="submit" value="<?php echo $data['ID'] ? '更新' : '登録'; ?>">
            </td></tr>
        </form></table>
        <?php
            $raffle_table = new PL_RaffleList_Table();
            $raffle_table->prepare_items();
            $raffle_table->display();

        ?>
        <hr>
        <?php  if($raffle_use == '1'){ ?>
        お試し購入(購入制限付き)
        <table>
        <form method="post">
            <tr>
            <input type="hidden" name="post_id" size=2 value="<?php echo $post_id; ?>" required>
            <td>
            <label for="order_id">受注番号</label></td><td>
            <input type="text" name="order_id" size=50 id="order_id" required>
            </td></tr><tr><td>
            <label for="itemcount">本数</label></td><td>
            <input type="text" name="itemcount" size=50 id="itemcount" value="10" required>
            </td></tr><tr><td></td><td>
            <input type="hidden" name="action" value="purchase">
            <input type="submit" value="購入">
            </td></tr>
        </form></table>
        <?php }else if($raffle_use == '2'){ ?>
        <hr>
        お試し購入(制限なし)
        <table>
        <form method="post">
            <tr>
            <input type="hidden" name="post_id" size=2 value="<?php echo $post_id; ?>" required>
            <td>
            <label for="order_id">受注番号</label></td><td>
            <input type="text" name="order_id" size=50 id="order_id" required>
            </td></tr><tr><td>
            <label for="itemcount">本数</label></td><td>
            <input type="text" name="itemcount" size=50 id="itemcount" value="10" required>
            </td></tr><tr><td></td><td>
            <input type="hidden" name="action" value="purchase_nolimit">
            <input type="submit" value="購入">
            </td></tr>
        </form></table>
        <?php } ?>
        <hr>
        購入リセット
        <table>
        <form method="post">
            <tr>
            <input type="hidden" name="post_id" size=2 value="<?php echo $post_id; ?>" required>
            <td>
            <input type="hidden" name="action" value="purchase_reset">
            <input type="submit" value="購入りセット">
            </td></tr>
        </form></table>
        <hr>
        お試しオープン
        <table>
        <form method="post">
            <tr>
            <input type="hidden" name="post_id" size=2 value="<?php echo $post_id; ?>" required>
            <td>
            <label for="id">ID</label></td><td>
            <input type="text" name="id" size=50 id="id" required>
            </td></tr><tr><td>
            <label for="order_id">受注番号</label></td><td>
            <input type="text" name="order_id" size=50 id="order_id" value="10" required>
            </td></tr><tr><td></td><td>
            <input type="hidden" name="action" value="raffle_open">
            <input type="submit" value="くじ引き開封">
            </td></tr>
        </form></table>
        <hr>
        お試し全オープン
        <table>
        <form method="post">
            <tr>
            <td>
            <label for="mem_id">MEM_ID</label></td><td>
            <input type="text" name="mem_id" size=50 id="mem_id" required>
            </td></tr><tr><td>
            <input type="hidden" name="action" value="raffle_allopen">
            <input type="submit" value="くじ引き全開封">
            </td></tr>
        </form></table>
        <?php
    }else{
        ob_start();
        echo '<h2>くじ引き商品管理</h2>';
            $raffle_table = new PL_LotteryCategoryList_Table();
            $raffle_table->prepare_items();
            $raffle_table->display();
    }
}

