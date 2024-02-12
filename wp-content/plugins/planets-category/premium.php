<?php
/*
Plugin Name: Planets Premium
Description: Planets Premiumのイベント表示アプリ
Version: 1.0
*/

if ( ! defined( 'ABSPATH' ) ) exit;

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

// Define custom WP_List_Table class for displaying custom table data
class PL_PREMIUM_EventList_Table extends WP_List_Table
{
    // Define columns for the table
    function get_columns()
    {
        return array(
            'type' => '表示順',
            'image' => '画像',
            'desc' => '表示',
            'reserve' => '公開時間',
            'rate' => '支払い比率',
            'control' => '制御',
            'delete' => '削除'
        );
    }

    // Prepare data for the table
    function prepare_items()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'pl_event';

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = array();
        $this->_column_headers = array($columns, $hidden, $sortable);

        $data = $wpdb->get_results("SELECT * FROM $table_name WHERE type = 1 AND genre = 1 ORDER BY type,ord desc ", ARRAY_A);
        $this->items = $data;
    }

    // Display each column's content
    function column_default($item, $column_name)
    {
		return isset($item[$column_name]) ? $item[$column_name] : '';
    }

    function column_rate($item)
    {
		return isset($item['rate']) ? $item['rate'] ."%": '';
    }

    function column_type($item)
    {
        $data .= $item['ord'];
        if($item['open']==='1'){
            $data .= '<br><div class="dashicons dashicons-visibility"></div>';
        }else{
            $data .= '<br><div class="dashicons dashicons-hidden"></div>';
        }
        return $data;
    }
    function column_Image($item){
        return '<img src="' . $item['image'] . '" width=100><br>' . $item['category'];
    }
    function column_Desc($item){
        $data = $item['talent'] ;
        if($item['type']==='1'){
            $data .= '<br>' . $item['event_start'] ;
        }
        $data .= '<br>' . $item['title'];
        return $data;
    }
    function column_Reserve($item){
        return $item['reserve_start'] . ' ' . $item['reserve_start_time'] ;
    }
    function column_Control($item){
        if($item['url']){
            $data =  '<a href="' . $item['url'] . '" target="_blank">イベントURL<span class="dashicons dashicons-admin-page"></span></a>';
        }
        $data .= '<br><a href="?page=planetspremiumentry&edit=' . $item['id'] . '">編集</a>';
        if($item['slug']){
           $data .= '<br><a href="'.home_url() .'/report?term_id='.$item['category'].'&slug='.$item['slug'].'&code='.$item['code'].'" target="_blank">レポートURL<span class="dashicons dashicons-admin-page"></span></a>';
        }
        return $data;
    }    
    function column_Delete($item){
        return '<form method="post" style="display:inline;"><input type="hidden" name="id" value="' . $item['id'] . '"><input type="hidden" name="action" value="delete"><input type="submit" value="削除" onclick="return confirm(\'本当に削除しますか？\');"></form>';
    }
}

// Define custom WP_List_Table class for displaying custom table data
class PL_PREMIUM_GoodsList_Table extends WP_List_Table
{
    // Define columns for the table
    function get_columns()
    {
        return array(
            'type' => '表示順',
            'image' => '画像',
            'desc' => '表示',
            'reserve' => '公開時間',
            'rate' => '支払い比率',
            'control' => '制御',
            'delete' => '削除'
        );
    }

    // Prepare data for the table
    function prepare_items()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'pl_event';

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = array();
        $this->_column_headers = array($columns, $hidden, $sortable);

        $data = $wpdb->get_results("SELECT * FROM $table_name WHERE type = 2 AND genre = 1 ORDER BY type,ord desc ", ARRAY_A);
        $this->items = $data;
    }

    // Display each column's content
    function column_default($item, $column_name)
    {
		return isset($item[$column_name]) ? $item[$column_name] : '';
    }

    function column_type($item)
    {
        $data .= $item['ord'];
        if($item['open']==='1'){
            $data .= '<br><div class="dashicons dashicons-visibility"></div>';
        }else{
            $data .= '<br><div class="dashicons dashicons-hidden"></div>';
        }
        return $data;
    }
    function column_rate($item)
    {
		return isset($item['rate']) ? $item['rate'] ."%": '';
    }
    function column_Image($item){
        return '<img src="' . $item['image'] . '" width=100><br>' . $item['category'];
    }
    function column_Desc($item){
        $data = $item['talent'] ;
        if($item['type']==='1'){
            $data .= '<br>' . $item['event_start'] ;
        }
        $data .= '<br>' . $item['title'];
        return $data;
    }
    function column_Reserve($item){
        return $item['reserve_start'] . ' ' . $item['reserve_start_time'] ;
    }
    function column_Control($item){
        if($item['url']){
            $data =  '<a href="' . $item['url'] . '" target="_blank">イベントURL<span class="dashicons dashicons-admin-page"></span></a>';
        }
        $data .= '<br><a href="?page=planetspremiumentry&edit=' . $item['id'] . '">編集</a>';
        if($item['slug']){
           $data .= '<br><a href="'.home_url() .'/report?term_id='.$item['category'].'&slug='.$item['slug'].'&code='.$item['code'].'" target="_blank">レポートURL<span class="dashicons dashicons-admin-page"></span></a>'; 
        }
        return $data;
    }    
    function column_Delete($item){
        return '<form method="post" style="display:inline;"><input type="hidden" name="id" value="' . $item['id'] . '"><input type="hidden" name="action" value="delete"><input type="submit" value="削除" onclick="return confirm(\'本当に削除しますか？\');"></form>';
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
		'PREMIUM TOP' // ページのタイトルタグ<title>に表示されるテキスト
		, 'PREMIUM TOP'   // 左メニューとして表示されるテキスト
		, 'manage_options'       // 必要な権限 manage_options は通常 administrator のみに与えられた権限
		, 'planetspremiumentry'        // 左メニューのスラッグ名 →URLのパラメータに使われる /wp-admin/admin.php?page=toriaezu_menu
		, 'planets_premium_entry_page_contents' // メニューページを表示する際に実行される関数(サブメニュー①の処理をする時はこの値は空にする)
		, 'dashicons-category'       // メニューのアイコンを指定 https://developer.wordpress.org/resource/dashicons/#awards
		, 3.02                            // メニューが表示される位置のインデックス(0が先頭) 5=投稿,10=メディア,20=固定ページ,25=コメント,60=テーマ,65=プラグイン,70=ユーザー,75=ツール,80=設定
	);
});

//=================================================
// イベント表示系Function
//=================================================

// データを追加
function pr_insert_data($title,$talent,$reserve_start,$reserve_start_time,$reserve_end,$event_start, $url, $image,$ord,$type,$open,$category,$slug,$rate,$code) {
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
            'open' => $open,
            'category' => $category,
            'slug' => $slug,
            'code' => $code,
            'rate' => $rate,
            'genre' => 1
        )
    );
    if ($result === false) {
        echo "Data insertion failed: " . $wpdb->last_error;
    }
}

// データを読み取り
function pr_read_data($id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'pl_event';
    $result = $wpdb->get_row("SELECT * FROM $table_name WHERE id = $id", ARRAY_A);
    return $result;
}

// データを更新
function pr_update_data($id, $title,$talent,$reserve_start,$reserve_start_time,$reserve_end,$event_start, $url,$image,$ord,$type,$open,$category,$slug,$rate) {
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
            'open' => $open,
            'category' => $category,
            'slug' => $slug,
            'rate' => $rate,
            'genre' => 1
        ),
        array('id' => $id)
    );
    if ($result === false) {
        echo "Data insertion failed: " . $wpdb->last_error;
    }
}

// データを削除
function pr_delete_data($id) {
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
function planets_premium_entry_page_contents() {
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
            $category = $_POST['category'];
            $url =  get_category_link( $_POST['category']);
            $image =  get_term_meta( $_POST['category'], 'category-image', true );
            $slug =  get_category( $_POST['category'])->slug;
            //$url = sanitize_url($_POST['url']);
            //$image = sanitize_url($_POST['image']);
            $ord = intval($_POST['ord']);
            $type = intval($_POST['type']);
            $open = intval($_POST['open']);
            $rate = intval($_POST['rate']);
            $code = wp_generate_password(8, false);

            ev_insert_data($title,$talent,$reserve_start,$reserve_start_time,$reserve_end,$event_start, $url,$image,$ord,$type,$open,$category,$slug,$rate,$code);
        } elseif ($action === 'update') {
            $id = intval($_POST['id']);
            $title = sanitize_text_field($_POST['title']);
            $talent = sanitize_text_field($_POST['talent']);
            $reserve_start = sanitize_text_field($_POST['reserve_start']);
            $reserve_start_time = sanitize_text_field($_POST['reserve_start_time']);
            $reserve_end = sanitize_text_field($_POST['reserve_end']);
            $event_start = sanitize_text_field($_POST['event_start']);
            $category = $_POST['category'];
            $url =  get_category_link( $_POST['category']);
            $image =  get_term_meta( $_POST['category'], 'category-image', true );
            $slug =  get_category( $_POST['category'])->slug;
            //$url = sanitize_url($_POST['url']);
            //$image = sanitize_url($_POST['image']);
            $ord = intval($_POST['ord']);
            $type = intval($_POST['type']);
            $open = intval($_POST['open']);
            $rate = intval($_POST['rate']);
            ev_update_data($id, $title,$talent,$reserve_start,$reserve_start_time,$reserve_end,$event_start, $url,$image,$ord,$type,$open,$category,$slug,$rate);
        } elseif ($action === 'delete') {
            $id = intval($_POST['id']);
            ev_delete_data($id);
        } elseif ($action === 'changecategory') {
            $url =  get_category_link( $_POST['category']);
            $image =  get_term_meta( $_POST['category'], 'category-image', true );
            $slug =  get_category( $_POST['category'])->slug;
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
        'open' => 0,
        'category' => ''
    );

    if (isset($_GET['edit'])) {
        $id = intval($_GET['edit']);
        $data = pr_read_data($id);
    }

	?>
    <h2>PREMIUM TOP</h2>

    <!-- データ入力フォーム -->
    <table>
    <form method="post">
        <tr>
        <input type="hidden" name="id" size=2 value="<?php echo $data['id']; ?>" required>
        <tr><td>
        <label for="url">カテゴリ</label></td><td>
        <?php
        $cat_list = wp_dropdown_categories(array(
            'show_option_none' => 'カテゴリー選択',
            'orderby' => 'term_id', //カテゴリーを何を基準に並べるか
            'order' => 'DESC', //カテゴリーをどの方向に並べるか
            'show_count' => 0, //カテゴリーに属する投稿数を表示するか
            'hide_empty' => false, //投稿のないカテゴリーを表示するか
            'child_of' => 2, //カテゴリーIDで指定されたカテゴリーの子孫カテゴリーを取得
            'exclude' => '', //除外したいカテゴリーIDをコンマ区切りで指定
            'echo' => 0, //カテゴリー一覧を表示する（1）、またはPHPで使うための値を返す（0）
            'selected' => $_POST['category']?$_POST['category']:$data['category'], //初期状態で選択された状態にしておきたいカテゴリーのID（option要素にselectedが追加される）
            'hierarchical' => 1, //カテゴリー一覧を階層形式（子孫カテゴリーをインデント）で表示するか
            'name' => 'category', //select要素のname属性
            'id' => 'category', //select要素のid属性
            'class' => 'postform', //select要素のclass属性
            'depth' => 1, //カテゴリーをどの階層まで出力するか
            'tab_index' => 0, //select要素のtabindex属性の値
            'taxonomy' => 'category', //取得するタクソノミー
            'hide_if_empty' => 0, //タームが一つもない場合はドロップダウン自体を非表示
            'value_field' => 'term_id' //option要素のvalue属性に入れるターム情報
        ));
        $replace = "<select$1 onchange='document.getElementById(\"action\").value = \"changecategory\";return this.form.submit()'>";
        $cat_list  = preg_replace( '#<select([^>]*)>#', $replace, $cat_list );
        echo $cat_list;
        echo $_POST['category']?$_POST['category']:$data['category'];
        ?>    
        
        </td></tr><tr><td>
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
        <input type="text" name="url" id="url" size=50 value="<?php echo get_category_link( $_POST['category']?$_POST['category']:$data['category']); ?>" readonly required>
        </td></tr><tr><td>
        <label for="image">画像</label></td><td>
        <?php if($image == ''){ ?>
        <p><img src="<?php echo plugins_url( 'images/noimage.png', __FILE__ ) ?>" width="100"></p>
        <?php }else{ ?>
        <p><img src="<?php echo get_term_meta( $_POST['category']?$_POST['category']:$data['category'], 'category-image', true ); ?>" width="100"></p>
        <?php } ?>
        </div>
        </td></tr><tr><td>
        <label for="rate">支払い比率</label></td><td>
        <input type="text" name="rate" id="rate" size=2 value="<?php echo $data['rate']; ?>" required>
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
        <input type="hidden" name="action" id="action" value="<?php echo $data['id'] ? 'update' : 'create'; ?>">
        <input type="submit" value="<?php echo $data['id'] ? '更新' : '登録'; ?>">
        </td></tr>
    </form></table>
    <hr>
    EVENT
    <?php
        $event_table = new PL_PREMIUM_EventList_Table();
        $event_table->prepare_items();
        $event_table->display();
        echo 'GOODS';
        $goods_table = new PL_PREMIUM_GoodsList_Table();
        $goods_table->prepare_items();
        $goods_table->display();

}

