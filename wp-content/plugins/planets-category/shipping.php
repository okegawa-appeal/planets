<?php
/*
Plugin Name: PLANETS 発送管理
Description: 発送管理
Version: 1.0
*/ 

// Include necessary files for WP_List_Table
if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

// Define custom WP_List_Table class for displaying custom table data
class PL_Shipping_List_Table extends WP_List_Table
{
    // Define columns for the table
    function get_columns()
    {
        return array(
            'cb' => '<input type="checkbox" />',
            'order_id' => 'order_id',
            'company' => '配送会社',
            'tracking_id' => 'トラッキングID',
            'email' => '会員メール',
            'name' => '会員名',
            'update_time' => '登録日',
        );
    }

    // Prepare data for the table
    function prepare_items()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'pl_shipping';
        $per_page = 100;
        $current_page = $this->get_pagenum();
        $offset = ($current_page - 1) * $per_page;

        $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table_name ");

        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
        ));

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array($columns, $hidden, $sortable);

        $orderby = (!empty($_GET['orderby'])) ? sanitize_text_field($_GET['orderby']) : 'order_id';
        $order = (!empty($_GET['order']) && in_array(strtoupper($_GET['order']), array('ASC', 'DESC'))) ? strtoupper($_GET['order']) : 'ASC';

        $data = $wpdb->get_results("SELECT * FROM $table_name ORDER BY $orderby $order LIMIT $per_page OFFSET $offset", ARRAY_A);
        $this->items = $data;
    }

    // Display each column's content
    function column_default($item, $column_name)
    {
		return isset($item[$column_name]) ? $item[$column_name] : '';
    }

    // Display the ID column
    function column_id($item)
    {
        return $item['order_id'];
    }

    // Display checkboxes in the ID column
    function column_cb($item)
    {
        return sprintf('<input type="checkbox" name="id[]" value="%s" />', $item['order_id']);
    }

    // Make columns sortable
    function get_sortable_columns()
    {
        return array(
            'order_id' => array('order_id', false),
            'company' => array('company', false),
            'tracking_id' => array('tracking_id', false),
            'email' => array('email', false),
            'name' => array('name',false),
            'update_time' => array('update_time',false),
        );
    }

    // Add CSV import form to the menu page
    function add_csv_import_form()
    {
        echo '<h2>CSVデータインポート</h2>';
        echo '<form method="post" enctype="multipart/form-data">';
        echo '<label for="csv_file">CSVファイルを選択:</label>';
        echo '<input type="file" name="csv_file" id="csv_file" accept=".csv">';
        echo '<input type="submit" name="import_csv" value="インポート">';
        echo '</form>';
        echo '<a href="' . plugins_url( 'files/planets-shipping.csv', __FILE__ ) . '">サンプルファイル</a>';
    }
}

// Handle CSV import
function handle_shippingcsv_import()
{
    global $wpdb;
    //初期化 
    $wpdb->query("delete FROM wp_pl_shipping");

    if ($_FILES['csv_file']['error'] == 0) {
        $file = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($file, "r");
        while (($data = fgetcsv($handle, 1000, ",")) !== false) {
            $order_id = sanitize_text_field($data[0]);
            $company = mb_convert_encoding($data[1], 'UTF-8', 'SJIS');
            $tracking_id = sanitize_text_field($data[2]);
            $sql = "select mem_email,mem_name1,mem_name2 FROM wp_usces_member , wp_usces_order where wp_usces_member.ID = wp_usces_order.mem_id and wp_usces_order.ID = ". $order_id;
            $data = $wpdb->get_results($sql);
            $table_name = $wpdb->prefix . 'pl_shipping';
            $wpdb->insert($table_name, array('order_id' => $order_id, 'company' => $company, 'tracking_id' => $tracking_id,'email'=>$data[0]->mem_email,'name'=>$data[0]->mem_name1.' '.$data[0]->mem_name2));
        }

        fclose($handle);
    }
}

// Register menu page and enqueue scripts
function shipping_menu()
{
    add_menu_page(
        '発送管理',
        '発送管理',
        'manage_options',
        'pl_shipping',
        'pl_shipping_page_content',
        'dashicons-randomize'
		, 3.2                             // メニューが表示される位置のインデックス(0が先頭) 5=投稿,10=メディア,20=固定ページ,25=コメント,60=テーマ,65=プラグイン,70=ユーザー,75=ツール,80=設定
    );
}
add_action('admin_menu', 'shipping_menu');

// Callback for the menu page
function pl_shipping_page_content()
{
    global $wpdb,$usces;

    // Handle form submissions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = isset($_POST['action']) ? $_POST['action'] : '';

        if ($_POST['import_csv'] === 'インポート') {
            handle_shippingcsv_import();
        }
    }

    // Display the form and data
    echo '<div class="wrap">';
    echo '<h1>発送管理</h1>';


	// Display data using WP_List_Table
    $list_table = new PL_Shipping_List_Table();
    $list_table->prepare_items();
    $list_table->add_csv_import_form();
    ?>
    <form method="post">
        <?php
        $list_table->display();
        ?>
        <input type="submit" name="bulk_open" class="button" value="発送完了登録">
        <hr><br>
        <textarea name="messagebody" rows="10" cols="50">ご注文の商品を発送いたしました。配送会社の状況はマイページよりご確認ください。</textarea>
        <input type="submit" name="sendshippingdlmail" class="button" value="メール送信">
    </form>
    <?php
    // Handle bulk delete action
    if (isset($_POST['bulk_open'])) {
        $ids = isset($_POST['id']) ? array_map('absint', $_POST['id']) : array();
        if (!empty($ids)) {
			change_shipping_status($ids);
        }
        //echo "<script>window.location = '".$_SERVER['REQUEST_URI']."';</script>";
    }

    echo '</div>';
    if (isset($_POST['sendshippingdlmail'])) {
        $ids = isset($_POST['id']) ? array_map('absint', $_POST['id']) : array();
        if (!empty($ids)) {
            send_shipping_mail($ids,$_POST['messagebody']);
            global $wpdb;
            $table_name = $wpdb->prefix . 'pl_dl_contents';
            $ids_str = implode(',', $ids);
            $wpdb->query("UPDATE $table_name SET mailed = true WHERE id IN ($ids_str)");
            echo "<script>window.location = '".$_SERVER['REQUEST_URI']."';</script>";
        }else{
            echo 'メールの送信先を指定してください';
        }
    }
}

//発送完了にする
function change_shipping_status($ids){
	global $wpdb, $usces;

	foreach ( (array) $ids as $id ) {
		$sql = $wpdb->prepare("SELECT * FROM wp_pl_shipping WHERE order_id = %d",$id);
        $data = $wpdb->get_results($sql);
        echo '<hr>';
        $usces->set_order_meta_value( 'tracking_number', trim( $data[0]->tracking_id ), $data[0]->order_id );
		$usces->set_order_meta_value( 'delivery_company', $data[0]->company, $data[0]->order_id );

		$query         = $wpdb->prepare( "SELECT order_status, mem_id FROM wp_usces_order WHERE ID = %d", $id );
        echo $query;
        echo '<hr>';
		$order_res     = $wpdb->get_row( $query, ARRAY_A );
		$statusstr     = $order_res['order_status'];
		if ( false !== strpos( $statusstr, 'new' ) ) {
			$statusstr = str_replace( 'new', '', $statusstr );
			$statusstr = trim( $statusstr, ',' );
		}
		if ( false !== strpos( $statusstr, 'duringorder' ) ) {
			$statusstr = str_replace( 'duringorder', '', $statusstr );
			$statusstr = trim( $statusstr, ',' );
		}
		if ( false !== strpos( $statusstr, 'cancel' ) ) {
			$statusstr = str_replace( 'cancel', '', $statusstr );
			$statusstr = trim( $statusstr, ',' );
		}
		if ( false === strpos( $statusstr, 'completion' ) ) {
			if ( ',' !== substr( $statusstr, -1 ) ) {
				$statusstr .= ',';
			}
			$statusstr .= 'completion,';
		}
		$query = $wpdb->prepare( "UPDATE wp_usces_order SET order_status = %s, order_modified = %s WHERE ID = %d", $statusstr, substr( current_time( 'mysql' ), 0, 10 ), $id );
        echo $query;
        $res = $wpdb->query( $query );
		if ( false === $res ) {
			error_log('更新に失敗しました'.$query);
		}
 	}
}
function send_shipping_mail($ids,$messagebody){
	global $usces;
	$mail_data         = usces_mail_data();
    global $wpdb;
    $ids_str = implode(',', array_map('absint', $ids));
    $query = "select email FROM wp_pl_shipping where order_id in({$ids_str}) and email is not null group by email";
    $emails = $wpdb->get_results($wpdb->prepare($query),"ARRAY_A");
    $bcc = "Bcc: ";
    foreach ($emails as $email) {
        $bcc = $bcc . $email["email"] . ",";
    }
    $subject = "PLANETSからのお知らせ(発送完了)";
    $message = $messagebody . "\r\n\r\n";
    $message = $mail_data['header']['othermail'] . $message . $mail_data['footer']['othermail'];
    $headers = array($bcc);

	$para1 = array(
		'to_name'      => "PLANETS",
		'to_address'   => "staff@planets-w.jp",
		'from_name'    => get_option( 'blogname' ),
		'from_address' => $usces->options['sender_mail'],
		'return_path'  => $usces->options['sender_mail'],
		'subject'      => $subject,
		'message'      => $message,
		'headers'      => $headers,
	);
	usces_send_mail( $para1 );

}

