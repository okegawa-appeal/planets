<?php
/*
Plugin Name: Planets 販売レポート
Description: 販売レポート表示
Version: 1.0
*/

if ( ! defined( 'ABSPATH' ) ) exit;

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

// Define custom WP_List_Table class for displaying custom table data
class PL_Report_Table extends WP_List_Table
{
    // Define columns for the table
    function get_columns()
    {
        return array(
            'name' => 'name',
        );
    }
    function column_Name($item)
    {
        return '<a href="?page=sales_report&term_id='.$item['term_id'].'">'.$item['name'].'</a>';
    }

    // Prepare data for the table
    function prepare_items()
    {
        global $wpdb;

        $per_page = 100;
        $current_page = $this->get_pagenum();
        $offset = ($current_page - 1) * $per_page;

        $sql = "select wp_term_taxonomy.term_id,name FROM wp_term_taxonomy,wp_terms where parent = 2 and wp_term_taxonomy.term_taxonomy_id = wp_terms.term_id";


        $total_items = $wpdb->get_var($sql);
		$this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
        ));

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array($columns, $hidden, $sortable);

        $data = $wpdb->get_results($sql . " ORDER BY term_id DESC LIMIT $per_page OFFSET $offset", ARRAY_A);
        $this->items = $data;
    }

    // Display each column's content
    function column_default($item, $column_name)
    {
        return isset($item[$column_name]) ? $item[$column_name] : '';
    }

    // Make columns sortable
    function get_sortable_columns()
    {
        return array(
            'name' => array('name', false),
        );
    }
}
class PL_ReportDetail_Table extends WP_List_Table
{
    // Define columns for the table
    function get_columns()
    {
        return array(
            'item_code' => '商品コード',
            'item_name' => '商品名',
            'name' => 'SKU名',
            'order_payment_name' => '支払い方法',
            'order_status' => 'ステータス',
			'price' => '単価',
			'count' => '個数',
			'total' => '小計(税込)'
        );
    }

    // Prepare data for the table
    function prepare_items()
    {
        if(!empty($_GET['term_id'])){
            $term_id = $_GET['term_id'];
            global $wpdb;

            $per_page = 100;
            $current_page = $this->get_pagenum();
            $offset = ($current_page - 1) * $per_page;

            $sql = "select "
            ."item_name,item_code,wp_usces_skus.name,order_payment_name,order_status,sum(wp_usces_skus.price*quantity) as total,wp_usces_skus.price,SUM(quantity) as count "
            ."FROM wp_term_relationships,wp_usces_ordercart,wp_usces_order,wp_usces_skus  "  
            ."where  "
            ." wp_term_relationships.term_taxonomy_id = $term_id " 
            ." and wp_usces_ordercart.post_id = wp_term_relationships.object_id "
            ." and wp_usces_order.ID= wp_usces_ordercart.order_id "
            ." and wp_usces_ordercart.sku_code = wp_usces_skus.code "
            ." and wp_usces_ordercart.post_id = wp_usces_skus.post_id "
            ." group by item_name,item_code,wp_usces_skus.name,wp_usces_skus.price,order_payment_name,order_status ";

            $total_items = $wpdb->get_var($sql);
            $this->set_pagination_args(array(
                'total_items' => $total_items,
                'per_page'    => $per_page,
            ));

            $columns = $this->get_columns();
            $hidden = array();
            $sortable = $this->get_sortable_columns();

            $this->_column_headers = array($columns, $hidden, $sortable);

            $orderby = (!empty($_GET['orderby'])) ? sanitize_text_field($_GET['orderby']) : 'id';
            $order = (!empty($_GET['order']) && in_array(strtoupper($_GET['order']), array('ASC', 'DESC'))) ? strtoupper($_GET['order']) : 'DESC';

            $data = $wpdb->get_results($sql . " ORDER BY $orderby $order LIMIT $per_page OFFSET $offset", ARRAY_A);
            $this->items = $data;
        }
    }

    // Display each column's content
    function column_default($item, $column_name)
    {
        return isset($item[$column_name]) ? $item[$column_name] : '';
    }
    
    private function calculate_total() {
        global $wpdb;
        if(!empty($_GET['term_id'])){
            $term_id = $_GET['term_id'];
            $sql = "select "
            ."sum(price*quantity) as price "
            ."FROM wp_term_relationships,wp_usces_ordercart  "
            ."where   "
            ."wp_term_relationships.term_taxonomy_id = $term_id  "
            ."and wp_usces_ordercart.post_id = wp_term_relationships.object_id  ";
            $data = $wpdb->get_results($sql , ARRAY_A);
        }
        return $data;
    }
    private function calculate_total_omit_cancel() {
        global $wpdb;
        if(!empty($_GET['term_id'])){
            $term_id = $_GET['term_id'];
            $sql = "select "
            ."sum(price*quantity) as price "
            ."FROM wp_term_relationships,wp_usces_ordercart,wp_usces_order  "
            ."where   "
            ."wp_term_relationships.term_taxonomy_id = $term_id  "
            ."and wp_usces_ordercart.post_id = wp_term_relationships.object_id  "
            ."and wp_usces_order.ID= wp_usces_ordercart.order_id "
            ."and order_status not like '%cancel%' " ;
            $data = $wpdb->get_results($sql , ARRAY_A);
        }
        return $data;
    }

    public function display() {
        // 合計値を計算
        $data = $this->calculate_total();
        $data2 = $this->calculate_total_omit_cancel();

        // テーブルを表示
        parent::display();

        // 合計値を表示
        echo '合計: ' . "¥".number_format($data[0]['price'],0);
        echo '<hr>';
        // 合計値を表示
        echo '合計(キャンセル除く): ' . "¥".number_format($data2[0]['price'],0);
    }

    function column_Price($item)
    {
        $price = "¥".number_format($item['price'],0); // 例: 1234.56 を 1,234.56 に変換
        return $price;
    }
    function column_Total($item)
    {
        $total = "¥".number_format($item['total'],0); // 例: 1234.56 を 1,234.56 に変換
        return $total;
    }

    function column_Order_Status($item){
        $order_status = $item['order_status'];
        $order_status = str_replace( 'duringorder', '取り寄せ中', $order_status);
        $order_status = str_replace( 'noreceipt', '未入金', $order_status);
        $order_status = str_replace( 'receipted', '入金済み', $order_status);
        $order_status = str_replace( 'completion', '発送済み', $order_status);
        $order_status = str_replace( 'cancel', 'キャンセル', $order_status);
        return $order_status;

    }
    function get_sortable_columns()
    {
        return array(
            'name' => array('name', false),
            'item_code' => array('item_code', false),
            'item_name' => array('item_name', false),
            'name' => array('name', false),
            'order_payment_name' => array('order_payment_name', false),
            'order_status' => array('order_status', false),
            'total' => array('total', false),
            'price' => array('price', false),
            'count' => array('count', false),
        );
    }
}//=================================================
// 管理画面に「とりあえずメニュー」を追加登録する
//=================================================
add_action('admin_menu', function(){
	
	//---------------------------------
	// メインメニュー①
	//---------------------------------	
    add_menu_page(
		'販売レポート' // ページのタイトルタグ<title>に表示されるテキスト
		, '販売レポート'   // 左メニューとして表示されるテキスト
		, 'manage_options'       // 必要な権限 manage_options は通常 administrator のみに与えられた権限
		, 'sales_report'        // 左メニューのスラッグ名 →URLのパラメータに使われる /wp-admin/admin.php?page=toriaezu_menu
		, 'sales_report' // メニューページを表示する際に実行される関数(サブメニュー①の処理をする時はこの値は空にする)
		, 'dashicons-analytics'       // メニューのアイコンを指定 https://developer.wordpress.org/resource/dashicons/#awards
		, 3.22                             // メニューが表示される位置のインデックス(0が先頭) 5=投稿,10=メディア,20=固定ページ,25=コメント,60=テーマ,65=プラグイン,70=ユーザー,75=ツール,80=設定
	);
});

//=================================================
// サブメニュー①ページ内容の表示・更新処理
//=================================================
function sales_report() {
	//---------------------------------
    // ユーザーが必要な権限を持つか確認
	//---------------------------------
    if (!current_user_can('manage_options'))
    {
      wp_die( __('この設定ページのアクセス権限がありません') );
    }

	//---------------------------------
	// 初期化
	//---------------------------------
	$message_html = "";

	//---------------------------------
	// HTML表示
	//---------------------------------
    $list_table = new PL_Report_Table();
    $list_table->prepare_items();
    $list_table->display();

    $detail_table = new PL_ReportDetail_Table();
    $detail_table->prepare_items();
    $detail_table->display();

}

