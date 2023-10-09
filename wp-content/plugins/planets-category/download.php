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
            'purchase_date' => '購入日',
            'item_name' => '商品名',
            'path' => 'パス',
            'email' => 'メール'
        );
    }

    // Prepare data for the table
    function prepare_items()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'pl_dl_contents';
        $per_page = 100;
        $current_page = $this->get_pagenum();
        $offset = ($current_page - 1) * $per_page;

        $search = isset($_REQUEST['s']) ? sanitize_text_field($_REQUEST['s']) : '';

        $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE item_name LIKE '%$search%' OR email LIKE '%$search%'");

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

        $data = $wpdb->get_results("SELECT * FROM $table_name WHERE item_name LIKE '%$search%' OR email LIKE '%$search%' ORDER BY $orderby $order LIMIT $per_page OFFSET $offset", ARRAY_A);
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
            'item_name' => array('item_name', false),
            'path' => array('path', false),
            'email' => array('email', false)
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
    }
}

// Handle CSV import
function handle_csv_import()
{
    if ($_FILES['csv_file']['error'] == 0) {
        $file = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($file, "r");
        echo $file;
        while (($data = fgetcsv($handle, 1000, ",")) !== false) {
            $purchase_date = sanitize_text_field($data[0]);
            $item_name = sanitize_text_field($data[1]);
            $path = sanitize_text_field($data[2]);
            $email = sanitize_text_field($data[3]);

            // Check if record already exists based on email
            global $wpdb;
            $table_name = $wpdb->prefix . 'pl_dl_contents';
            $wpdb->insert($table_name, array('purchase_date' => $purchase_date, 'item_name' => $item_name, 'path' => $path, 'email' => $email));
        }

        fclose($handle);
    }
}

// Register menu page and enqueue scripts
function custom_crud_menu()
{
    add_menu_page(
        'DLコンテンツ',
        'DLコンテンツ',
        'manage_options',
        'crud-page',
        'crud_page_content',
        'dashicons-download'
		, 3.2                             // メニューが表示される位置のインデックス(0が先頭) 5=投稿,10=メディア,20=固定ページ,25=コメント,60=テーマ,65=プラグイン,70=ユーザー,75=ツール,80=設定
    );
}
add_action('admin_menu', 'custom_crud_menu');

// Callback for the menu page
function crud_page_content()
{
    global $wpdb;

    // Handle form submissions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = isset($_POST['action']) ? $_POST['action'] : '';

        if ($action === 'create') {
            $purchase_date = sanitize_text_field($_POST['purchase_date']);
            $item_name = sanitize_text_field($_POST['item_name']);
            $path = sanitize_text_field($_POST['path']);
            $email = sanitize_text_field($_POST['email']);
            $wpdb->insert($wpdb->prefix . 'pl_dl_contents', array('purchase_date' => $purchase_date, 'item_name' => $item_name, 'path' => $path, 'email' => $email));
        } elseif ($action === 'delete') {
            $ids = isset($_POST['id']) ? array_map('absint', $_POST['id']) : array();
            if (!empty($ids)) {
                $ids_str = implode(',', $ids);
                $wpdb->query("DELETE FROM " . $wpdb->prefix . "pl_dl_contents WHERE id IN ($ids_str)");
            }
        } elseif (isset($_POST['import_csv'])) {
            handle_csv_import();
        }
    }

    // Display the form and data
    echo '<div class="wrap">';
    echo '<h1>DLコンテンツ</h1>';

    // Form
    echo '<form method="post">';
    echo '<table>';
    echo '<input type="hidden" name="action" value="create">';
    echo '<tr><td>';
    echo '<label for="purchase_date">購入日:</label></td><td><input type="date" name="purchase_date" required></td></tr>';
    echo '<tr><td>';
    echo '<label for="item_name">商品名:</label></td><td><input type="text" name="item_name" required></td></tr>';
    echo '<tr><td>';
    echo '<label for="path">パス:</label></td><td><input type="text" name="path" required></td></tr>';
    echo '<tr><td>';
    echo '<label for="email">メール:</label></td><td><input type="email" name="email" required></td></tr>';
    echo '<tr><td><input type="submit" value="登録"></td></tr>';
    echo '</table>';
    echo '</form>';

    // Display data using WP_List_Table
    $list_table = new PL_DL_Contents_List_Table();
    $list_table->search_box('検索', 'search_id');
    $list_table->prepare_items();
    $list_table->add_csv_import_form();
    ?>
    <form method="post">
        <?php
        $list_table->search_box('検索', 'search_id');
        $list_table->display();
        ?>
        <input type="submit" name="bulk_delete" class="button" value="一括削除">
    </form>
    <?php
    // Handle bulk delete action
    if (isset($_POST['bulk_delete'])) {
        $ids = isset($_POST['id']) ? array_map('absint', $_POST['id']) : array();
        if (!empty($ids)) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'pl_dl_contents';
            $ids_str = implode(',', $ids);
            $wpdb->query("DELETE FROM $table_name WHERE id IN ($ids_str)");
        }
        echo "<script>window.location = '".$_SERVER['REQUEST_URI']."';</script>";
    }

    echo '</div>';
}
function add_custom_search_filter($query)
{
    if (is_admin() && isset($_GET['page']) && $_GET['page'] === 'crud-page' && isset($_GET['s'])) {
        $query->query_vars['s'] = sanitize_text_field($_GET['s']);
    }
}
add_action('pre_get_posts', 'add_custom_search_filter');

