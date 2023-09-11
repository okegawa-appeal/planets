<?php
/*
Plugin Name: Planets Event
Description: Planets用のイベント表示アプリ
Version: 1.0
*/

if ( ! defined( 'ABSPATH' ) ) exit;


/* フッターにメッセージを表示する */
//add_action( 'wp_footer', function() {
//	echo 'ほげええええぇぇぇ！';
//});

/* お知らせ表示する */
/*
add_action('admin_notices', function() {

  echo <<<EOF

<div class="notice notice-info is-dismissible">
	<p>お知らせ通知アラートのテスト(info=青)</p>
</div>

EOF;
	
});
*/
// https://tart.co.jp/wordpress-plugin-original-create/
//=================================================
// 管理画面に「とりあえずメニュー」を追加登録する
//=================================================
add_action('admin_menu', function(){
	
	//---------------------------------
	// メインメニュー①
	//---------------------------------	
    add_menu_page(
		'Planets Event' // ページのタイトルタグ<title>に表示されるテキスト
		, 'Planets Event'   // 左メニューとして表示されるテキスト
		, 'manage_options'       // 必要な権限 manage_options は通常 administrator のみに与えられた権限
		, 'planetsevent'        // 左メニューのスラッグ名 →URLのパラメータに使われる /wp-admin/admin.php?page=toriaezu_menu
		, 'planets_event_entry_page_contents' // メニューページを表示する際に実行される関数(サブメニュー①の処理をする時はこの値は空にする)
		, 'dashicons-category'       // メニューのアイコンを指定 https://developer.wordpress.org/resource/dashicons/#awards
		, 3.1                             // メニューが表示される位置のインデックス(0が先頭) 5=投稿,10=メディア,20=固定ページ,25=コメント,60=テーマ,65=プラグイン,70=ユーザー,75=ツール,80=設定
	);

	//---------------------------------
	// サブメニュー① ※事実上の親メニュー
	//---------------------------------
	add_submenu_page(
		'planetsevent'    // 親メニューのスラッグ
		, 'DLコンテンツ' // ページのタイトルタグ<title>に表示されるテキスト
		, 'DLコンテンツ' // サブメニューとして表示されるテキスト
		, 'manage_options' // 必要な権限 manage_options は通常 administrator のみに与えられた権限
		, 'pldlcontents'  // サブメニューのスラッグ名。この名前を親メニューのスラッグと同じにすると親メニューを押したときにこのサブメニューを表示します。一般的にはこの形式を採用していることが多い。
		, 'planets_download_contents' //（任意）このページのコンテンツを出力するために呼び出される関数
		, 3.21
	);

	//---------------------------------
	// サブメニュー②
	//---------------------------------
	add_submenu_page(
		'planetsevent'    // 親メニューのスラッグ
		, 'くじ引き商品設定' // ページのタイトルタグ<title>に表示されるテキスト
		, 'くじ引き商品設定' // サブメニューとして表示されるテキスト
		, 'manage_options' // 必要な権限 manage_options は通常 administrator のみに与えられた権限
		, 'planetsraffle' //サブメニューのスラッグ名
		, 'planets_raffle_page_contents' //（任意）このページのコンテンツを出力するために呼び出される関数
		, 3.22
	);

});

//=================================================
// イベント表示系Function
//=================================================

// データを追加
function ev_insert_data($id, $title, $url, $image,$ord) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'pl_event';
	echo $id .':'.$title.':'.$url.':'.$image.':'.$ord;
    $result = $wpdb->insert(
        $table_name,
        array(
			'id' => $id,
            'title' => $title,
            'url' => $url,
			'image' => $image,
			'ord' => $ord
        )
    );
    if ($result === false) {
        echo "Data insertion failed: " . $wpdb->last_error;
    }
}

// データを読み取り
function ev_read_data($id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'pl_event';
    $result = $wpdb->get_row("SELECT * FROM $table_name WHERE id = $id", ARRAY_A);
    return $result;
}

// データを更新
function ev_update_data($id, $title, $url,$image,$ord) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'pl_event';
    $result = $wpdb->update(
        $table_name,
        array(
            'title' => $title,
            'url' => $url,
			'image' => $image,
			'ord' => $ord
        ),
        array('id' => $id)
    );
    if ($result === false) {
        echo "Data insertion failed: " . $wpdb->last_error;
    }
}

// データを削除
function ev_delete_data($id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'pl_event';
    $wpdb->delete(
        $table_name,
        array('id' => $id)
    );
    if ($result === false) {
        echo "Data insertion failed: " . $wpdb->last_error;
    }
}

//=================================================
// サブメニューイベント表示
//=================================================
function planets_event_entry_page_contents() {

	ob_start();
    // フォームからのデータを処理
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action === 'create') {
            $id = intval($_POST['id']);
            $title = sanitize_text_field($_POST['title']);
            $url = sanitize_url($_POST['url']);
            $image = sanitize_url($_POST['image']);
            $ord = intval($_POST['ord']);
            ev_insert_data($id,$title, $url,$image,$ord);
        } elseif ($action === 'update') {
            $id = intval($_POST['id']);
            $title = sanitize_text_field($_POST['title']);
            $url = sanitize_url($_POST['url']);
            $image = sanitize_url($_POST['image']);
            $ord = intval($_POST['ord']);
            ev_update_data($id, $title, $url,$image,$ord);
        } elseif ($action === 'delete') {
            $id = intval($_POST['id']);
            ev_delete_data($id);
        }
    }

    // データの読み取りとフォームの表示
    $data = array(
        'id' => '',
        'title' => '',
        'url' => '',
        'image' => '',
        'ord' => ''
    );

    if (isset($_GET['edit'])) {
        $id = intval($_GET['edit']);
        $data = ev_read_data($id);
    }

	?>
    <h2>イベント表示制御</h2>

    <!-- データ入力フォーム -->
    <form method="post">
        <label for="id">id:</label>
        <input type="text" name="id" size=2 value="<?php echo $data['id']; ?>" required>
		<br>
        <label for="title">タイトル:</label>
        <input type="text" name="title" size=50 id="title" value="<?php echo $data['title']; ?>" required>
        <br>
        <label for="url">url:</label>
        <input type="text" name="url" size=50 id="url" value="<?php echo $data['url']; ?>" required>
        <br>
        <label for="image">image:</label>
        <input type="text" name="image" size=50 id="image" value="<?php echo $data['image']; ?>" required>
        <br>
        <label for="ord">順序(0:最新,1,2,...):</label>
        <input type="text" name="ord" id="ord" size=2 value="<?php echo $data['ord']; ?>" required>
        <br>
        <input type="hidden" name="action" value="<?php echo $data['id'] ? 'update' : 'create'; ?>">
        <input type="submit" value="<?php echo $data['id'] ? '更新' : '登録'; ?>">
    </form>

    <!-- データ一覧 -->
    <ul class="drag-list">
        <?php
        global $wpdb;
        $table_name = $wpdb->prefix . 'pl_event';
        $results = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
        foreach ($results as $row) {
            echo '<li draggable="true">';
            echo '<div><img src="' . $row['image'] . '" width=100></div>';
            echo '<div>' . $row['title'] . '</div>';
            echo '<div><a href="' . $row['url'] . '" target="_blank">表示</a></div>';
            echo '<div><a href="?page=planetsevententry&edit=' . $row['id'] . '">編集</a> | ';
            echo '<form method="post" style="display:inline;"><input type="hidden" name="id" value="' . $row['id'] . '"><input type="hidden" name="action" value="delete"><input type="submit" value="削除" onclick="return confirm(\'本当に削除しますか？\');"></form></div>';
            echo '</li>';
        }
		echo '</ul>';
}

//=================================================
// DLコンテンンツ表示系Function
//=================================================

// データを追加
function dl_insert_data($id, $purchase_date, $item_name, $path,$email) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'pl_dl_contents';
    $result = $wpdb->insert(
        $table_name,
        array(
			'id' => $id,
            'purchase_date' => $purchase_date,
            'item_name' => $item_name,
            'email' => $email,
			'path' => $path
        )
    );
    if ($result === false) {
        echo "Data insertion failed: " . $wpdb->last_error;
    }
}

// データを読み取り
function dl_read_data($id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'pl_dl_contents';
    $result = $wpdb->get_row("SELECT * FROM $table_name WHERE id = $id", ARRAY_A);
    return $result;
}

// データを更新
function dl_update_data($id, $purchase_date, $item_name, $path,$email) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'pl_dl_contents';
    $result = $wpdb->update(
        $table_name,
        array(
			'id' => $id,
            'purchase_date' => $purchase_date,
            'item_name' => $item_name,
			'path' => $path,
            'email' => $email
        ),
        array('id' => $id)
    );
    if ($result === false) {
        echo "Data insertion failed: " . $wpdb->last_error;
    }
}

// データを削除
function dl_delete_data($id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'pl_dl_contents';
    $wpdb->delete(
        $table_name,
        array('id' => $id)
    );
    if ($result === false) {
        echo "Data insertion failed: " . $wpdb->last_error;
    }
}

//=================================================
// サブメニューイベント表示
//=================================================
function planets_download_contents() {

//	ob_start();

    // エラーメッセージを表示
    if (isset($_GET['error'])) {
        $error_message = urldecode($_GET['error']);
        echo '<div class="error"><p>' . $error_message . '</p></div>';
    }
    // ページング設定
    $per_page = 10;
    $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $offset = ($current_page - 1) * $per_page;

    // 検索条件
    $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
    
    // フォームからのデータを処理
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action === 'create') {
            $id = intval($_POST['id']);
            $purchase_date = $_POST['purchase_date'];
            $item_name = sanitize_text_field($_POST['item_name']);
            $path = sanitize_url($_POST['path']);
            $email = sanitize_email($_POST['email']);
            dl_insert_data($id,$purchase_date, $item_name,$path,$email);
        } elseif ($action === 'update') {
            $id = intval($_POST['id']);
            $purchase_date = $_POST['purchase_date'];
            $item_name = sanitize_text_field($_POST['item_name']);
            $path = sanitize_url($_POST['path']);
            $email = sanitize_email($_POST['email']);
            dl_update_data($id,$purchase_date, $item_name,$path,$email);
        } elseif ($action === 'delete') {
            $id = intval($_POST['id']);
            dl_delete_data($id);
        }
    }

    // データの読み取りとフォームの表示
    $data = array(
        'id' => '',
        'purchase_date' => '',
        'item_name' => '',
        'path' => '',
        'email' => ''
    );
    if (isset($_GET['edit'])) {
        $id = intval($_GET['edit']);
        $data = dl_read_data($id);
    }
    // データの検索
    global $wpdb;
    $table_name = $wpdb->prefix . 'pl_dl_contents';
    $query = "SELECT * FROM $table_name";

    if (!empty($search)) {
        $query .= " WHERE item_name LIKE '%$search%'";
    }
    $query .= " ORDER BY id DESC LIMIT $per_page OFFSET $offset";

    $results = $wpdb->get_results($query, ARRAY_A);
	?>
    <h2>DLコンテンツ管理</h2>

    <!-- データ入力フォーム -->
    <form method="post">
        <input type="hidden" name="id" size=2 value="<?php echo $data['id']; ?>" required>
		<br>
        <label for="email">Email:</label>
        <input type="text" name="email" size=50 id="email" value="<?php echo $data['email']; ?>" required>
		<br>
        <label for="purchase_date">購入日:</label>
        <input type="text" name="purchase_date" size=50 id="purchase_date" value="<?php echo $data['purchase_date']; ?>" required>
		<br>
        <label for="item_name">item名:</label>
        <input type="text" name="item_name" size=50 id="item_name" value="<?php echo $data['item_name']; ?>" required>
        <br>
        <label for="path">path:</label>
        <input type="text" name="path" size=50 id="path" value="<?php echo $data['path']; ?>" required>
        <br>
        <input type="hidden" name="action" value="<?php echo $data['id'] ? 'update' : 'create'; ?>">
        <input type="submit" value="<?php echo $data['id'] ? '更新' : '登録'; ?>">
    </form>
    <!-- 検索フォーム -->
    <form method="get">
        <input type="hidden" name="page" value="crud-page">
        <input type="text" name="s" placeholder="検索" value="<?php echo esc_attr($search); ?>">
        <input type="submit" value="検索">
    </form>
    <!-- データ一覧 -->
    <ul class="drag-list">
        <?php
        echo '<table border>';
        echo '<tr><th>email</th><th>購入日</th><th>購入品</th><th>パス</th><th>#</th></tr>';
        foreach ($results as $row) {
            echo '<tr><td>'.$row['email'].'</td>';
            echo '<td>'.$row['purchase_date'].'</td>';
            echo '<td>'.$row['item_name'].'</td>';
            echo '<td>'.$row['path'].'</td>';
            echo '<td><a href="?page=planetsevententry&edit=' . $row['id'] . '">編集</a> | ';
            echo '<form method="post" style="display:inline;"><input type="hidden" name="id" value="' . $row['id'] . '"><input type="hidden" name="action" value="delete"><input type="submit" value="削除" onclick="return confirm(\'本当に削除しますか？\');"></form></td>';
            echo '</tr>';
        }
		echo '</table>';
    $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    if ($total_items > $per_page) {
        $total_pages = ceil($total_items / $per_page);
        echo '<div class="tablenav">';
        echo '<div class="tablenav-pages">';
        echo paginate_links(array(
            'base' => add_query_arg('paged', '%#%'),
            'format' => '',
            'prev_text' => __('&laquo;'),
            'next_text' => __('&raquo;'),
            'total' => $total_pages,
            'current' => $current_page
        ));
        echo '</div>';
        echo '</div>';
    }
//    return ob_get_clean();
}

//=================================================
// サブメニュー①ページ内容の表示・更新処理
//=================================================
function planets_raffle_page_contents() {
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
    $opt_name = 'toriaezu_message'; //オプション名の変数
    $opt_val = get_option( $opt_name ); // 既に保存してある値があれば取得
	$opt_val_old = $opt_val;
	$message_html = "";

	//---------------------------------
	// 更新されたときの処理
	//---------------------------------
    if( isset($_POST[ $opt_name ])) {

        // POST されたデータを取得
        $opt_val = $_POST[ $opt_name ];

        // POST された値を$opt_name=$opt_valでデータベースに保存(wp_options テーブル内に保存)
        update_option($opt_name, $opt_val);

        // 画面にメッセージを表示
		$message_html =<<<EOF
			
<div class="notice notice-success is-dismissible">
	<p>
		メッセージを保存しました
		({$opt_val_old}→{$opt_val})
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
	
	<h2>くじ引き商品設定</h2>
	
	<form name="form1" method="post" action="">
	

		<p>
			<input type="text" name="{$opt_name}" value="{$opt_val}" size="32" placeholder="メッセージを入力してみて下さい">
	
		</p>
	
		<hr />
	
		<p class="submit">
			<input type="submit" name="submit" class="button-primary" value="メッセージを保存" />
		</p>
	
	</form>
	
</div>

EOF;

}