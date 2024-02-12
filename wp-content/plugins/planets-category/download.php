<?php
/*
Plugin Name: PLANETS DOWNLOAD商品の登録
Description: Download商品の商品URL登録
Version: 1.0
*/

// Include necessary files for WP_List_Table
if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

// Define custom WP_List_Table class for displaying custom table data
class PL_DL_Contents_List_Table extends WP_List_Table
{
    // Define columns for the table
    function get_columns()
    {
        return array(
            'cb' => '<input type="checkbox" />',
            'id' => 'ID',
            'status' => 'status',
            'purchase_date' => 'DL期限',
            'event_name' => 'イベント名',
            'item_name' => '商品名',
            'path' => 'パス',
            'email' => 'メール',
            'mem_name1' => '名前'
        );
    }
    public function extra_tablenav($which) {
        global $wpdb;
        $data = $wpdb->get_results("select event_name from wp_pl_dl_contents group by event_name order by id desc", ARRAY_A);

        if ($which === 'top') {
            echo '<div class="alignleft actions">';
            echo '<label for="filter-by-status" class="screen-reader-text">ステータスでフィルタ:</label>';
            echo '<select name="filter_by_status" id="filter-by-status">';
            echo '<option value="">最終アップロードデータ</option>';
            foreach ($data as $row) {
                echo '<option value="'.$row['event_name'].'" '.($_POST['filter_by_status'] == $row['event_name']?"SELECTED":"").'>'.$row['event_name'].'</option>';
            }
            echo '</select>';
            submit_button('フィルタ', 'button', false, false, array('id' => 'status-filter-submit'));
            echo '</div>';
        }
    }

    // Prepare data for the table
    function prepare_items()
    {
        global $wpdb;
        if (isset($_POST['filter_by_status']) && $_POST['filter_by_status'] !== '') {
            // カテゴリでフィルタリングする処理を実装する
            $status_value = $_POST['filter_by_status'];
            $filter_option = "WHERE event_name = '" .$status_value."'";
        }else{
            $filter_option = "WHERE work = true";
        }

        $table_name = $wpdb->prefix . 'pl_dl_contents';
        $per_page = 100;
        $current_page = $this->get_pagenum();
        $offset = ($current_page - 1) * $per_page;

        $search = isset($_REQUEST['s']) ? sanitize_text_field($_REQUEST['s']) : '';

        $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table_name left outer join wp_usces_member on (wp_pl_dl_contents.email = wp_usces_member.mem_email) $filter_option");

        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
        ));

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array($columns, $hidden, $sortable);

        $orderby = (!empty($_GET['orderby'])) ? sanitize_text_field($_GET['orderby']) : 'id';
        $order = (!empty($_GET['order']) && in_array(strtoupper($_GET['order']), array('ASC', 'DESC'))) ? strtoupper($_GET['order']) : 'ASC';

        $data = $wpdb->get_results("SELECT wp_pl_dl_contents.*,mem_name1 FROM $table_name left outer join wp_usces_member on (wp_pl_dl_contents.mem_id = wp_usces_member.ID) $filter_option ORDER BY $orderby $order LIMIT $per_page OFFSET $offset", ARRAY_A);
        $this->items = $data;
    }

    // Display each column's content
    function column_default($item, $column_name)
    {
        if($column_name === 'status'){
            echo $item['open']?'<div class="dashicons dashicons-visibility"></div>':'<div class="dashicons dashicons-hidden"></div>';
            echo $item['mailed']?'<div class="dashicons dashicons-email"></div>':'';
        }else{
            return isset($item[$column_name]) ? $item[$column_name] : '';
        }
    }

    // Display the ID column
    function column_id($item)
    {
        return $item['id'];
    }

    // Display checkboxes in the ID column
    function column_cb($item)
    {
        return sprintf('<input type="checkbox" name="id[]" value="%s" />', $item['id']);
    }

    // Make columns sortable
    function get_sortable_columns()
    {
        return array(
            'id' => array('id', false),
            'purchase_date' => array('purchase_date', false),
            'event_name' => array('event_name', false),
            'item_name' => array('item_name', false),
            'path' => array('path', false),
            'email' => array('email', false),
            'open' => array('open',false),
            'mailed' => array('mailed',false),
            'mem_name1' => array('mem_name1',false)
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
        echo '<a href="' . plugins_url( 'files/planets-download-importfile_20240210.csv', __FILE__ ) . '">サンプルファイル(ver20240210)</a>';
    }
}

// Handle CSV import
function handle_csv_import()
{
    global $wpdb;
    $wpdb->query("update wp_pl_dl_contents set work = false where work = true");

    if ($_FILES['csv_file']['error'] == 0) {
        $file = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($file, "r");
        $count = 1;
        while (($data = fgetcsv($handle, 1000, ",")) !== false) {
            $purchase_date = sanitize_text_field($data[0]);
            $event_name = mb_convert_encoding($data[1], 'UTF-8', 'SJIS');
            $item_name = mb_convert_encoding($data[2], 'UTF-8', 'SJIS');
            $path = sanitize_text_field($data[3]);
            $email = sanitize_text_field($data[4]);
            // mem_idを取得
            $sql = "select mem_id from wp_usces_order where order_email = '$email'";
            $mem_id = $wpdb->get_var($sql);
            //TODO: ２つとれた
            //TODO: なかった
            $error_flg = false;
            if($purchase_date == NULL){
                echo '<div class="error"><p>'.$count.'行目 日付が入っていません。登録スキップします。</p></div>';
                $error_flg = true;
            }
            if($event_name == NULL){
                echo '<div class="error"><p>'.$count.'行目 イベント名が入っていません。登録スキップします。</p></div>';
                $error_flg = true;
            }
            if($item_name == NULL){
                echo '<div class="error"><p>'.$count.'行目 商品名が入っていません。登録スキップします。</p></div>';
                $error_flg = true;
            }
            if($path == NULL){
                echo '<div class="error"><p>'.$count.'行目 パスが入っていません。登録スキップします。</p></div>';
                $error_flg = true;
            }
            if($email == NULL){
                echo '<div class="error"><p>'.$count.'行目 メールが入っていません。登録スキップします。</p></div>';
                $error_flg = true;
            }
            // Check if record already exists based on email
            if($error_flg == false){
                $table_name = $wpdb->prefix . 'pl_dl_contents';
                $wpdb->insert($table_name, array('purchase_date' => $purchase_date, 'item_name' => $item_name, 'path' => $path, 'email' => $email,'mem_id'=>$mem_id,'event_name'=>$event_name,'work'=>true));
            }
            $count++;
        }

        fclose($handle);
    }
}

// Register menu page and enqueue scripts
function dl_contents_menu()
{
    add_menu_page(
        'DLコンテンツ',
        'DLコンテンツ',
        'manage_options',
        'dl-contents',
        'dl_page_content',
        'dashicons-download'
		, 3.2                             // メニューが表示される位置のインデックス(0が先頭) 5=投稿,10=メディア,20=固定ページ,25=コメント,60=テーマ,65=プラグイン,70=ユーザー,75=ツール,80=設定
    );
}
add_action('admin_menu', 'dl_contents_menu');

// Callback for the menu page
function dl_page_content()
{
    global $wpdb;

    // Handle form submissions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = isset($_POST['action']) ? $_POST['action'] : '';

        if (isset($_POST['import_csv'])) {
            handle_csv_import();
        }
    }
    // Handle bulk delete action
    if (isset($_POST['bulk_open'])) {
        $ids = isset($_POST['id']) ? array_map('absint', $_POST['id']) : array();
        if (!empty($ids)) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'pl_dl_contents';
            $ids_str = implode(',', $ids);
            $wpdb->query("UPDATE $table_name SET open = true WHERE id IN ($ids_str)");
        }
//        echo "<script>window.location = '".$_SERVER['REQUEST_URI']."';</script>";
    }
/*    if (isset($_POST['bulk_hidden'])) {
        $ids = isset($_POST['id']) ? array_map('absint', $_POST['id']) : array();
        if (!empty($ids)) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'pl_dl_contents';
            $ids_str = implode(',', $ids);
            $wpdb->query("UPDATE $table_name SET open = false WHERE id IN ($ids_str)");
        }
        echo "<script>window.location = '".$_SERVER['REQUEST_URI']."';</script>";
    }*/
    if (isset($_POST['bulk_delete'])) {
        $ids = isset($_POST['id']) ? array_map('absint', $_POST['id']) : array();
        if (!empty($ids)) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'pl_dl_contents';
            $ids_str = implode(',', $ids);
            $wpdb->query("DELETE FROM $table_name WHERE id IN ($ids_str)");
        }
//        echo "<script>window.location = '".$_SERVER['REQUEST_URI']."';</script>";
    }
    if (isset($_POST['senddlmail'])) {
        $ids = isset($_POST['id']) ? array_map('absint', $_POST['id']) : array();
        if (!empty($ids)) {
            send_dl_mail($ids,$_POST['messagebody']);
            global $wpdb;
            $table_name = $wpdb->prefix . 'pl_dl_contents';
            $ids_str = implode(',', $ids);
            $wpdb->query("UPDATE $table_name SET mailed = true WHERE id IN ($ids_str)");
//            echo "<script>window.location = '".$_SERVER['REQUEST_URI']."';</script>";
        }else{
            echo 'メールの送信先を指定してください';
        }
    }
    // Display the form and data
    echo '<div class="wrap">';
    echo '<h1>DLコンテンツ</h1>';

    // Form
    /*echo '<form method="post">';
    echo '<table>';
    echo '<input type="hidden" name="action" value="create">';
    echo '<tr><td>';
    echo '<label for="purchase_date">DL期限:</label></td><td><input type="date" name="purchase_date" required></td></tr>';
    echo '<tr><td>';
    echo '<label for="item_name">商品名:</label></td><td><input type="text" name="item_name" required></td></tr>';
    echo '<tr><td>';
    echo '<label for="path">パス:</label></td><td><input type="text" name="path" required></td></tr>';
    echo '<tr><td>';
    echo '<label for="email">メール:</label></td><td><input type="email" name="email" required></td></tr>';
    echo '<tr><td><input type="submit" value="登録"></td></tr>';
    echo '</table>';
    echo '</form>';
    */

    // Display data using WP_List_Table
    $list_table = new PL_DL_Contents_List_Table();
    $list_table->prepare_items();
    $list_table->add_csv_import_form();
    ?>
    <form method="post">
        <?php
//        $list_table->search_box('商品名で検索', 'search_id');
        $list_table->display();
        ?>
        <input type="submit" name="bulk_open" class="button" value="一括公開">
        <!-- input type="submit" name="bulk_hidden" class="button" value="一括未公開" -->
        <input type="submit" name="bulk_delete" class="button" value="一括削除" onclick="return confirm('一括削除しますか？');">
        <hr><br>
        <textarea name="messagebody" rows="10" cols="50">DLコンテンツの準備が整いました。マイページよりご確認ください。</textarea>
        <input type="submit" name="senddlmail" class="button" value="メール送信">
    </form>
    <?php


    echo '</div>';

}
/*function add_custom_search_filter($query)
{
    if (is_admin() && isset($_GET['page']) && $_GET['page'] === 'crud-page' && isset($_GET['s'])) {
        $query->query_vars['s'] = sanitize_text_field($_GET['s']);
    }
}
add_action('pre_get_posts', 'add_custom_search_filter');
*/
function send_dl_mail($ids,$messagebody){
	global $usces;
	$mail_data         = usces_mail_data();
    global $wpdb;
    $ids_str = implode(',', array_map('absint', $ids));
    $query = "select email FROM wp_pl_dl_contents where id in({$ids_str}) and email is not null group by email";
    $emails = $wpdb->get_results($wpdb->prepare($query),"ARRAY_A");
    $bcc = "Bcc: ";
    foreach ($emails as $email) {
        $bcc = $bcc . $email["email"] . ",";
    }
    $subject = "PLANETSからのお知らせ(DLコンテンツ準備完了)";
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

