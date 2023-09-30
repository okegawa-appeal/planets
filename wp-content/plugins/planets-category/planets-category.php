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
		'TOP画面' // ページのタイトルタグ<title>に表示されるテキスト
		, 'TOP画面'   // 左メニューとして表示されるテキスト
		, 'manage_options'       // 必要な権限 manage_options は通常 administrator のみに与えられた権限
		, 'planetsevententry'        // 左メニューのスラッグ名 →URLのパラメータに使われる /wp-admin/admin.php?page=toriaezu_menu
		, 'planets_event_entry_page_contents' // メニューページを表示する際に実行される関数(サブメニュー①の処理をする時はこの値は空にする)
		, 'dashicons-category'       // メニューのアイコンを指定 https://developer.wordpress.org/resource/dashicons/#awards
		, 3.1                             // メニューが表示される位置のインデックス(0が先頭) 5=投稿,10=メディア,20=固定ページ,25=コメント,60=テーマ,65=プラグイン,70=ユーザー,75=ツール,80=設定
	);

	//---------------------------------
	// サブメニュー① ※事実上の親メニュー
	//---------------------------------
	add_submenu_page(
		'planetsevententry'    // 親メニューのスラッグ
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
		'planetsevententry'    // 親メニューのスラッグ
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
function ev_insert_data($title,$talent,$reserve_start,$reserve_end,$event_start, $url, $image,$ord,$type,$open) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'pl_event';
	echo $id .':'.$title.':'.$url.':'.$image.':'.$ord;
    $result = $wpdb->insert(
        $table_name,
        array(
            'title' => $title,
            'talent' => $talent,
            'reserve_start' => $reserve_start,
            'reserve_end' => $reserve_end,
            'event_start' => $event_start,
            'url' => $url,
			'image' => $image,
			'ord' => $ord,
            'type' => $type,
            'open' => $open
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
function ev_update_data($id, $title,$talent,$reserve_start,$reserve_end,$event_start, $url,$image,$ord,$type,$open) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'pl_event';
    $result = $wpdb->update(
        $table_name,
        array(
            'title' => $title,
            'talent' => $talent,
            'reserve_start' => $reserve_start,
            'reserve_end' => $reserve_end,
            'event_start' => $event_start,
            'url' => $url,
			'image' => $image,
			'ord' => $ord,
            'type' => $type,
            'open' => $open
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
            $title = sanitize_text_field($_POST['title']);
            $talent = sanitize_text_field($_POST['talent']);
            $reserve_start = sanitize_text_field($_POST['reserve_start']);
            $reserve_end = sanitize_text_field($_POST['reserve_end']);
            $event_start = sanitize_text_field($_POST['event_start']);
            $url = sanitize_url($_POST['url']);
            $image = sanitize_url($_POST['image']);
            echo 'aaaaa';
            echo $image;
            $ord = intval($_POST['ord']);
            $type = intval($_POST['type']);
            $open = intval($_POST['open']);
            ev_insert_data($title,$talent,$reserve_start,$reserve_end,$event_start, $url,$image,$ord,$type,$open);
        } elseif ($action === 'update') {
            $id = intval($_POST['id']);
            $title = sanitize_text_field($_POST['title']);
            $talent = sanitize_text_field($_POST['talent']);
            $reserve_start = sanitize_text_field($_POST['reserve_start']);
            $reserve_end = sanitize_text_field($_POST['reserve_end']);
            $event_start = sanitize_text_field($_POST['event_start']);
            $url = sanitize_url($_POST['url']);
            $image = sanitize_url($_POST['image']);
            $ord = intval($_POST['ord']);
            $type = intval($_POST['type']);
            $open = intval($_POST['open']);
            ev_update_data($id, $title,$talent,$reserve_start,$reserve_end,$event_start, $url,$image,$ord,$type,$open);
        } elseif ($action === 'delete') {
            $id = intval($_POST['id']);
            ev_delete_data($id);
        }
    }

    // データの読み取りとフォームの表示
    $data = array(
        'id' => '',
        'title' => '',
        'talent' => '',
        'reserve_start' => '',
        'reserve_end' => '',
        'event_start' => '',
        'url' => '',
        'image' => '',
        'ord' => '',
        'type' => 1,
        'open' => 0
    );

    if (isset($_GET['edit'])) {
        $id = intval($_GET['edit']);
        $data = ev_read_data($id);
    }

    wp_enqueue_script( 'media-uploader-main-js', plugins_url( 'js/media-uploader-main.js', __FILE__ ), array( 'jquery' ) );
    wp_enqueue_media();

	?>
    <h2>イベント表示制御</h2>

    <!-- データ入力フォーム -->
    <table>
    <form method="post">
        <tr>
        <input type="hidden" name="id" size=2 value="<?php echo $data['id']; ?>" required>
        <tr><td>
        <label for="talent">タレント名</label></td><td>
        <input type="text" name="talent" size=50 id="title" value="<?php echo $data['talent']; ?>" required>
        </td></tr><tr><td>
        <label for="event_start">イベント時間</label></td><td>
        <input type="text" name="event_start" size=50 id="title" value="<?php echo $data['event_start']; ?>" required>
        </td></tr><tr><td>
        <label for="title">タイトル</label></td><td>
        <input type="text" name="title" size=50 id="title" value="<?php echo $data['title']; ?>" required>
        </td></tr><tr><td>
        <label for="reserve_start">告知開始日</label></td><td>
        <input type="text" name="reserve_start" size=50 id="title" value="<?php echo $data['reserve_start']; ?>" required>
        </td></tr><tr><td>
        <label for="url">url</label></td><td>
        <input type="text" name="url" size=50 id="url" value="<?php echo $data['url']; ?>" required>
        </td></tr><tr><td>
        <label for="image">画像</label></td><td>
        <?php if($data['image'] == ''){ ?>
        <p><img id="image-view" src="<?php echo plugins_url( 'images/noimage.png', __FILE__ ) ?>" width="130"></p>
        <?php }else{ ?>
        <p><img id="image-view" src="<?php echo $data['image']; ?>" width="130"></p>
        <?php } ?>
        <p><input id="image" type="text" name="image" class="large-text" value="<?php echo $data['image']; ?>"></p>
        </div>
        </td></tr><tr><td>
        <label for="ord">順序(降順)</label></td><td>
        <input type="text" name="ord" id="ord" size=2 value="<?php echo $data['ord']; ?>" required>
        </td></tr><tr><td>
        <label for="type">種別</label></td><td>
        <select name="type" id="type" >
           <option value="1" <?php if ( ! empty( $data['type'] ) ) { if ( '1' === $data['type'] ) { echo 'selected'; } } ?>>Event</option>
           <option value="2" <?php if ( ! empty( $data['type'] ) ) { if ( '2' === $data['type'] ) { echo 'selected'; } } ?>>Goods</option>
        </select>
        </td></tr><tr><td>
        <label for="open">公開</label></td><td>
        <select name="open" id="open" >
           <option value="1" <?php if ( ! empty( $data['open'] ) ) { if ( '1' === $data['open'] ) { echo 'selected'; } } ?>>公開</option>
           <option value="0" <?php if ( ! empty( $data['open'] ) ) { if ( '0' === $data['open'] ) { echo 'selected'; } } ?>>非公開</option>
        </select>
        </td></tr><tr><td></td><td>
        <input type="hidden" name="action" value="<?php echo $data['id'] ? 'update' : 'create'; ?>">
        <input type="submit" value="<?php echo $data['id'] ? '更新' : '登録'; ?>">
        </td></tr>
    </form></table>
    <hr>
    <!-- データ一覧 -->
    <ul class="drag-list">
        <?php
        global $wpdb;
        $table_name = $wpdb->prefix . 'pl_event';
        $results = $wpdb->get_results("SELECT * FROM $table_name order by type,ord desc", ARRAY_A);
        foreach ($results as $row) {
            echo '<li draggable="true" style="margin:20px;border: 2px solid #000000;">  ';
            echo '<div style="display:flex;border"><div style="margin:1px;border: 1px solid #000000;">';
            if($row['type']==='1'){
            echo '<div>EVENT</div>';
            }else{
            echo '<div>GOODS</div>';
            }
            echo '<div>'.$row['ord'].'</div>';
            if($row['open']==='1'){
            echo '<div>公開</div>';
            }else{
            echo '<div>非公開</div>';
            }
            echo '</div><div><img src="' . $row['image'] . '" width=100></div>';
            echo '<div style="margin:1px;border: 1px solid #000000;"><div>' . $row['talent'] . '</div>';
            if($row['type']==='1'){
            echo '<div>' . $row['event_start'] . '</div>';
            }
            echo '<div>' . $row['title'] . '</div></div>';
            echo '<div style="margin:1px;border: 1px solid #000000;"><div>' . $row['reserve_start'] . '</div></div>';
            echo '<div style="margin:1px;border: 1px solid #000000;"><div><a href="' . $row['url'] . '" target="_blank">表示</a></div>';
            echo '<div><a href="?page=planetsevententry&edit=' . $row['id'] . '">編集</a></div>';
            echo '<div><form method="post" style="display:inline;"><input type="hidden" name="id" value="' . $row['id'] . '"><input type="hidden" name="action" value="delete"><input type="submit" value="削除" onclick="return confirm(\'本当に削除しますか？\');"></form></div>';
            echo '</div></li>';
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
// データを更新
function dl_upsert_data($id, $purchase_date, $item_name, $path,$email) {
    global $wpdb;
    $sql = "INSERT INTO {$wpdb->prefix}pl_dl_contents (id,purchase_date,item_name,path,email) ";
    $sql .= "VALUES (%d,%s,%s,%s,%s) ON conflict(id) do update ";
    $sql .= "set purchase_date = %s,item_name = %s ,path = %s,email = %s";
    var_dump($sql); // debug
    $sql = $wpdb->prepare($sql,$purchase_date,$item_name,$path,$email,$purchase_date,$item_name,$path,$email);
    var_dump($sql); // debug 
    $result = $wpdb->query($sql);
    echo $result;
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
            //dl_insert_data($id,$purchase_date, $item_name,$path,$email);
            dl_upsert_data($id,$purchase_date, $item_name,$path,$email);
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

    // CSVアップロード処理
    if (isset($_FILES['csv_file']['tmp_name'])) {
        $csv_file = $_FILES['csv_file']['tmp_name'];
        if (is_uploaded_file($csv_file)) {
            $handle = fopen($csv_file, 'r');
            if ($handle !== false) {
                while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                    $name = sanitize_text_field($data[0]);
                    $email = sanitize_email($data[1]);
                    upsert_data($name, $email);
                }
                fclose($handle);
                $success_message = "CSVファイルのアップロードが完了しました。";
                echo '<div class="updated"><p>' . $success_message . '</p></div>';
            }
        }
    }

?>
    <h2>DLコンテンツ管理</h2>

    <!-- データ入力フォーム -->
    <table>
    <form method="post">
        <input type="hidden" name="id" size=2 value="<?php echo $data['id']; ?>" required>
		<tr><td>
        <label for="email">Email:</label></td><td>
        <input type="text" name="email" size=50 id="email" value="<?php echo $data['email']; ?>" required>
        </td></tr><tr><td>
        <label for="purchase_date">購入日:</label></td><td>
        <input type="text" name="purchase_date" size=50 id="purchase_date" value="<?php echo $data['purchase_date']; ?>" required>
        </td></tr><tr><td>
        <label for="item_name">item名:</label></td><td>
        <input type="text" name="item_name" size=50 id="item_name" value="<?php echo $data['item_name']; ?>" required>
        </td></tr><tr><td>
        <label for="path">path:</label></td><td>
        <input type="text" name="path" size=50 id="path" value="<?php echo $data['path']; ?>" required>
        </td></tr><tr><td></td><td>
        <input type="hidden" name="action" value="<?php echo $data['id'] ? 'update' : 'create'; ?>">
        <input type="submit" value="<?php echo $data['id'] ? '更新' : '登録'; ?>">
        </td></tr>
    </form></table>
    <hr>
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
            echo '<td><a href="?page=pldlcontents&edit=' . $row['id'] . '">編集</a> | ';
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
?>
    <h2>DLコンテンツアップロード</h2>
    <h3>emailとファイルパスを更新して下さい</h3>
    <form method="post" enctype="multipart/form-data">
        <label for="csv_file">CSVファイルを選択:</label>
        <input type="file" name="csv_file" id="csv_file" accept=".csv">
        <input type="submit" value="アップロード">
    </form>
<?php
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