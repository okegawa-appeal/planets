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
});

//=================================================
// イベント表示系Function
//=================================================

// データを追加
function ev_insert_data($title,$talent,$reserve_start,$reserve_start_time,$reserve_end,$event_start, $url, $image,$ord,$type,$open) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'pl_event';
    $result = $wpdb->insert(
        $table_name,
        array(
            'title' => $title,
            'talent' => $talent,
            'reserve_start' => $reserve_start,
            'reserve_start_time' => $reserve_start_time,
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
function ev_update_data($id, $title,$talent,$reserve_start,$reserve_start_time,$reserve_end,$event_start, $url,$image,$ord,$type,$open) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'pl_event';
    $result = $wpdb->update(
        $table_name,
        array(
            'title' => $title,
            'talent' => $talent,
            'reserve_start' => $reserve_start,
            'reserve_start_time' => $reserve_start_time,
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
            $reserve_start_time = sanitize_text_field($_POST['reserve_start_time']);
            $reserve_end = sanitize_text_field($_POST['reserve_end']);
            $event_start = sanitize_text_field($_POST['event_start']);
            $url = sanitize_url($_POST['url']);
            $image = sanitize_url($_POST['image']);
            $ord = intval($_POST['ord']);
            $type = intval($_POST['type']);
            $open = intval($_POST['open']);
            ev_insert_data($title,$talent,$reserve_start,$reserve_start_time,$reserve_end,$event_start, $url,$image,$ord,$type,$open);
        } elseif ($action === 'update') {
            $id = intval($_POST['id']);
            $title = sanitize_text_field($_POST['title']);
            $talent = sanitize_text_field($_POST['talent']);
            $reserve_start = sanitize_text_field($_POST['reserve_start']);
            $reserve_start_time = sanitize_text_field($_POST['reserve_start_time']);
            $reserve_end = sanitize_text_field($_POST['reserve_end']);
            $event_start = sanitize_text_field($_POST['event_start']);
            $url = sanitize_url($_POST['url']);
            $image = sanitize_url($_POST['image']);
            $ord = intval($_POST['ord']);
            $type = intval($_POST['type']);
            $open = intval($_POST['open']);
            ev_update_data($id, $title,$talent,$reserve_start,$reserve_start_time,$reserve_end,$event_start, $url,$image,$ord,$type,$open);
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
        'reserve_start_time' => '',
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
    <h2>TOP画面 表示</h2>

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
        <input type="date" name="reserve_start" size=50 id="title" value="<?php echo $data['reserve_start']; ?>" required>
        <label for="reserve_start_time">時間：</label>
        <select id="reserve_start_time" name="reserve_start_time">
        <?php 
        for ($hour = 0; $hour < 24; $hour++) {
            for ($minute = 0; $minute < 60; $minute += 30) {
                $time = sprintf('%02d:%02d', $hour, $minute);
                $selected = ($data['reserve_start_time'] == $time) ? 'selected' : '';
                echo '<option value="' . $time . '" ' . $selected . '>' . $time . '</option>';
            }
        } ?>
        </select>
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
            echo '<div style="margin:1px;border: 1px solid #000000;"><div>' . $row['reserve_start'] . ' ' . $row['reserve_start_time'] . '</div></div>';
            echo '<div style="margin:1px;border: 1px solid #000000;"><div><a href="' . $row['url'] . '" target="_blank">表示</a></div>';
            echo '<div><a href="?page=planetsevententry&edit=' . $row['id'] . '">編集</a></div>';
            echo '<div><form method="post" style="display:inline;"><input type="hidden" name="id" value="' . $row['id'] . '"><input type="hidden" name="action" value="delete"><input type="submit" value="削除" onclick="return confirm(\'本当に削除しますか？\');"></form></div>';
            echo '</div></li>';
        }
		echo '</ul>';
}

