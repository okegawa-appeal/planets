<?php
/*
Plugin Name: Planets ラッフル
Description: Planets用のラッフルアプリ
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
		'くじ引き商品設定' // ページのタイトルタグ<title>に表示されるテキスト
		, 'くじ引き商品設定'   // 左メニューとして表示されるテキスト
		, 'manage_options'       // 必要な権限 manage_options は通常 administrator のみに与えられた権限
		, 'planetsraffle'        // 左メニューのスラッグ名 →URLのパラメータに使われる /wp-admin/admin.php?page=toriaezu_menu
		, 'planets_raffle_page_contents' // メニューページを表示する際に実行される関数(サブメニュー①の処理をする時はこの値は空にする)
		, 'dashicons-category'       // メニューのアイコンを指定 https://developer.wordpress.org/resource/dashicons/#awards
		, 3.22                             // メニューが表示される位置のインデックス(0が先頭) 5=投稿,10=メディア,20=固定ページ,25=コメント,60=テーマ,65=プラグイン,70=ユーザー,75=ツール,80=設定
	);
});

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