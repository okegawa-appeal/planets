<?php
/*
Plugin Name: Planets BOOKEND WEB書庫登録
Description: BOOKENDのemail-contentsidの紐付け
Version: 1.0
*/

if ( ! defined( 'ABSPATH' ) ) exit;

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

// Define custom WP_List_Table class for displaying custom table data
class PL_Bookend_History_List_Table extends WP_List_Table
{
    // Define columns for the table
    function get_columns()
    {
        return array(
            'email' => 'email',
            'contentsid' => 'contentsid',
            'errormsg' => 'errormsg',
            'createdate' => 'createdate'
        );
    }

    // Prepare data for the table
    function prepare_items()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'pl_bookend_history';
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

        $orderby = (!empty($_GET['orderby'])) ? sanitize_text_field($_GET['orderby']) : 'id';
        $order = (!empty($_GET['order']) && in_array(strtoupper($_GET['order']), array('ASC', 'DESC'))) ? strtoupper($_GET['order']) : 'ASC';

//        $data = $wpdb->get_results("SELECT * FROM $table_name ORDER BY $orderby $order LIMIT $per_page OFFSET $offset", ARRAY_A);
        $data = $wpdb->get_results("SELECT * FROM $table_name order by createdate desc LIMIT $per_page OFFSET $offset", ARRAY_A);
//        $data = $wpdb->get_results("SELECT * FROM $table_name ", ARRAY_A);

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
            'email' => array('email', false),
            'contentsid' => array('contentsid', false),
            'errormsg' => array('errormsg', false),
            'createdate' => array('createdate', false)
        );
    }
}
//=================================================
// 管理画面に「とりあえずメニュー」を追加登録する
//=================================================
add_action('admin_menu', function(){
	
	//---------------------------------
	// メインメニュー①
	//---------------------------------	
    add_menu_page(
		'WEB書庫登録' // ページのタイトルタグ<title>に表示されるテキスト
		, 'WEB書庫登録'   // 左メニューとして表示されるテキスト
		, 'manage_options'       // 必要な権限 manage_options は通常 administrator のみに与えられた権限
		, 'bookendentry'        // 左メニューのスラッグ名 →URLのパラメータに使われる /wp-admin/admin.php?page=toriaezu_menu
		, 'planets_bookend_entry' // メニューページを表示する際に実行される関数(サブメニュー①の処理をする時はこの値は空にする)
		, 'dashicons-book'       // メニューのアイコンを指定 https://developer.wordpress.org/resource/dashicons/#awards
		, 3.22                             // メニューが表示される位置のインデックス(0が先頭) 5=投稿,10=メディア,20=固定ページ,25=コメント,60=テーマ,65=プラグイン,70=ユーザー,75=ツール,80=設定
	);
});

//=================================================
// サブメニュー①ページ内容の表示・更新処理
//=================================================
function planets_bookend_entry() {
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
    $action = $_POST['action'];

    if ($action === 'create') {
        $email = $_POST['email'];
		$bookendid = $_POST['bookendid'];
        $result = bookend_entry_data($email, $bookendid);
        // 画面にメッセージを表示
		$message_html =<<<EOF
			
<div class="notice notice-success is-dismissible">
	<p>
		登録しました。<br><br>
		{$result}
	</p>
</div>
			
EOF;
	}

	//---------------------------------
	// HTML表示
	//---------------------------------
	echo $html =<<<EOF

{$message_html}

<div class="wrap">
	
	<h2>BOOKEND WEB書庫登録</h2>
	
	<table>
    <form method="post">
        <tr><td>
        <label for="email">email</label></td><td>
        <input type="text" name="email" size=50 id="email" required>
        </td></tr><tr><td>
        <label for="bookendid">bookendid</label></td><td>
        <input type="text" name="bookendid" size=50 id="bookendid" required>
        </td></tr><tr><td>
        <input type="hidden" name="action" value="create">
        <input type="submit" value="登録">
        </td></tr>
    </form></table>
	
</div>

EOF;
    $list_table = new PL_Bookend_History_List_Table();
    $list_table->prepare_items();
    $list_table->display();

}
// データを読み取り
function bookend_entry_data($email,$bookendid) {
	## bookend ユーザー取得
	$getuserurl = 'https://license.keyring.net/BookEnd/api/v1.0/GetUserID';
	$args = array(
		'method' => 'POST',
		'httpversion' => '1.1',
		'headers'  => array(
				'Content-Type' => 'application/x-www-form-urlencoded;charset=UTF-8',
		),
		'body' => array(
			'OwnerLoginName' => 'double',
			'OwnerPassword' => 'D9yEpTJf',
			'MailAddress' => $email,
			'Create' => 'true'
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

	$body = simplexml_load_string( $response['body']);
	$json = json_encode( $body );
	$data = json_decode( $json, true );
	$userid = $data['UserID'];

	## bookend コンテンツ紐付け
	$addcontentsurl = 'https://license.keyring.net/BookEnd/cloudlib/api/v1.0/AddContents';
	$args = array(
		'method' => 'POST',
		'httpversion' => '1.1',
		'headers'  => array(
				'Content-Type' => 'application/x-www-form-urlencoded;charset=UTF-8',
		),
		'body' => array(
			'OwnerLoginName' => 'double',
			'OwnerPassword' => 'D9yEpTJf',
			'UserID' => $userid,
			'ContentsID' => $bookendid
		)
	);
	$response 	= wp_remote_post( $addcontentsurl, $args );
	$result = $result . "<br><br>";
	if (is_wp_error($response)) {
		$result =  $result . 'HTTPリクエストエラー: ' . $response->get_error_message();
	} else {
		// ステータスコード
		$result = $result . 'ステータスコード: ' . wp_remote_retrieve_response_code($response) . "\n";

		// レスポンスボディ
		$result = $result .  'レスポンスボディ: ' . wp_remote_retrieve_body($response) . "\n";
		$body = simplexml_load_string( $response['body']);
		$json = json_encode( $body );
		$data = json_decode( $json, true );
		if($data['Status'] == '80000'){
			bookend_history($email,$bookendid,null);
		}else{
			bookend_history($email,$bookendid,$result);
			if($data['Status'] != '80014'){
		        echo "E-B1001:エラーが発生しました。BOOKENDのコンテンツ登録に失敗しました。";
			}
		}
	}

	return $result;
}

function bookend_history($email,$contentsid,$errormsg) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'pl_bookend_history';
    $result = $wpdb->insert(
        $table_name,
        array(
            'email' => $email,
            'contentsid' => $contentsid,
            'errormsg' => $errormsg
        )
    );
    if ($result === false) {
        echo "Data insertion failed: " . $wpdb->last_error;
    }
}
