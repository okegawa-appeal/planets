<?php
/**
 * System extentions Sagawa E-Hiden III.
 * Version: 2.0.0
 * Author: Collne Inc.
 *
 * @package WCEX Sagawa Number
 */

/**
 * Main class.
 *
 * @since 1.0.0
 */
class WCEX_SAGAWA_NUMBER {
	/**
	 * Extended options.
	 *
	 * @var array
	 */
	public static $opts;

	/**
	 * Constructor.
	 */
	public function __construct() {

		self::initialize_data();

		if ( is_admin() ) {
			add_action( 'usces_action_admin_system_extentions', array( $this, 'setting_form' ) );
			add_action( 'admin_footer-welcart-shop_page_usces_system', array( $this, 'system_js' ) );
			add_action( 'init', array( $this, 'save_data' ) );
			if ( self::$opts['activate_flag'] ) {
				add_action( 'usces_action_order_list_page', array( $this, 'hiden_action' ) );
				add_action( 'usces_action_order_list_searchbox_bottom', array( $this, 'action_button' ) );
				add_filter( 'usces_filter_order_list_page_js', array( $this, 'order_list_page_js' ) );
				add_action( 'usces_action_order_list_footer', array( $this, 'order_list_footer' ) );
				add_action( 'usces_after_cart_instant', array( $this, 'after_cart_instant' ) );
			}
		}
	}

	/**
	 * Initialize.
	 */
	public function initialize_data() {
		global $usces;
		$options = get_option( 'usces_ex' );

		$options['system']['e-hidencsv']['activate_flag']   = ! isset( $options['system']['e-hidencsv']['activate_flag'] ) ? 0 : (int) $options['system']['e-hidencsv']['activate_flag'];
		$options['system']['e-hidencsv']['character_limit'] = ( ! isset( $options['system']['e-hidencsv']['character_limit'] ) ) ? '0' : (int) $options['system']['e-hidencsv']['character_limit'];
		$options['system']['e-hidencsv']['time_zone']       = empty( $options['system']['e-hidencsv']['time_zone'] ) ? "指定しない;\n午前中;01\n12:00～14:00;12\n14:00～16:00;14\n16:00～18:00;16\n18:00～20:00;18\n18:00～21:00;04\n19:00～21:00;19" : trim( $options['system']['e-hidencsv']['time_zone'] );

		$options['system']['e-hidencsv']['sponsor_flag'] = ! isset( $options['system']['e-hidencsv']['sponsor_flag'] ) ? '1' : (int) $options['system']['e-hidencsv']['sponsor_flag'];
		$options['system']['e-hidencsv']['sponsor_tel']  = ! isset( $options['system']['e-hidencsv']['sponsor_tel'] ) ? '' : trim( $options['system']['e-hidencsv']['sponsor_tel'] );
		$options['system']['e-hidencsv']['sponsor_telb'] = ! isset( $options['system']['e-hidencsv']['sponsor_telb'] ) ? '' : trim( $options['system']['e-hidencsv']['sponsor_telb'] );
		$options['system']['e-hidencsv']['sponsor_zip']  = ! isset( $options['system']['e-hidencsv']['sponsor_zip'] ) ? '' : trim( $options['system']['e-hidencsv']['sponsor_zip'] );
		$options['system']['e-hidencsv']['sponsor_add1'] = ! isset( $options['system']['e-hidencsv']['sponsor_add1'] ) ? '' : trim( $options['system']['e-hidencsv']['sponsor_add1'] );
		$options['system']['e-hidencsv']['sponsor_add2'] = ! isset( $options['system']['e-hidencsv']['sponsor_add2'] ) ? '' : trim( $options['system']['e-hidencsv']['sponsor_add2'] );
		$options['system']['e-hidencsv']['sponsor_name'] = ! isset( $options['system']['e-hidencsv']['sponsor_name'] ) ? '' : trim( $options['system']['e-hidencsv']['sponsor_name'] );
		$options['system']['e-hidencsv']['sponsor_kana'] = ! isset( $options['system']['e-hidencsv']['sponsor_kana'] ) ? '' : trim( $options['system']['e-hidencsv']['sponsor_kana'] );

		$options['system']['e-hidencsv']['sponsor_code'] = ! isset( $options['system']['e-hidencsv']['sponsor_code'] ) ? '' : trim( $options['system']['e-hidencsv']['sponsor_code'] );
		$options['system']['e-hidencsv']['billing_code'] = ! isset( $options['system']['e-hidencsv']['billing_code'] ) ? '' : trim( $options['system']['e-hidencsv']['billing_code'] );
		$options['system']['e-hidencsv']['packing']      = ! isset( $options['system']['e-hidencsv']['packing'] ) ? '' : trim( $options['system']['e-hidencsv']['packing'] );
		$options['system']['e-hidencsv']['packing_tag']  = ! isset( $options['system']['e-hidencsv']['packing_tag'] ) ? '' : trim( $options['system']['e-hidencsv']['packing_tag'] );
		$options['system']['e-hidencsv']['payment_type'] = ! isset( $options['system']['e-hidencsv']['payment_type'] ) ? '' : trim( $options['system']['e-hidencsv']['payment_type'] );
		$options['system']['e-hidencsv']['seal1']        = ! isset( $options['system']['e-hidencsv']['seal1'] ) ? '' : trim( $options['system']['e-hidencsv']['seal1'] );
		$options['system']['e-hidencsv']['seal2']        = ! isset( $options['system']['e-hidencsv']['seal2'] ) ? '' : trim( $options['system']['e-hidencsv']['seal2'] );
		$options['system']['e-hidencsv']['seal3']        = ! isset( $options['system']['e-hidencsv']['seal3'] ) ? '' : trim( $options['system']['e-hidencsv']['seal3'] );

		update_option( 'usces_ex', $options );
		self::$opts = $options['system']['e-hidencsv'];
	}

	/**
	 * Save option data.
	 */
	public function save_data() {
		global $usces;

		if ( isset( $_POST['usces_hiden_option_update'] ) ) {
			check_admin_referer( 'admin_system', 'wc_nonce' );
			$usces->stripslashes_deep_post( $_POST );
			self::$opts['activate_flag']   = isset( $_POST['hiden_activate_flag'] ) ? (int) $_POST['hiden_activate_flag'] : 0;
			self::$opts['character_limit'] = ( isset( $_POST['hiden_character_limit'] ) ) ? (int) $_POST['hiden_character_limit'] : 0;
			self::$opts['time_zone']       = ! empty( $_POST['hiden_time_zone'] ) ? trim( $_POST['hiden_time_zone'] ) : "指定しない;\n午前中;01\n12:00～14:00;12\n14:00～16:00;14\n16:00～18:00;16\n18:00～20:00;18\n18:00～21:00;04\n19:00～21:00;19";

			self::$opts['sponsor_flag'] = isset( $_POST['hiden_sponsor_flag'] ) ? (int) $_POST['hiden_sponsor_flag'] : 1;
			self::$opts['sponsor_tel']  = isset( $_POST['hiden_sponsor_tel'] ) ? trim( $_POST['hiden_sponsor_tel'] ) : '';
			self::$opts['sponsor_telb'] = isset( $_POST['hiden_sponsor_telb'] ) ? trim( $_POST['hiden_sponsor_telb'] ) : '';
			self::$opts['sponsor_zip']  = isset( $_POST['hiden_sponsor_zip'] ) ? trim( $_POST['hiden_sponsor_zip'] ) : '';
			self::$opts['sponsor_add1'] = isset( $_POST['hiden_sponsor_add1'] ) ? trim( $_POST['hiden_sponsor_add1'] ) : '';
			self::$opts['sponsor_add2'] = isset( $_POST['hiden_sponsor_add2'] ) ? trim( $_POST['hiden_sponsor_add2'] ) : '';
			self::$opts['sponsor_name'] = isset( $_POST['hiden_sponsor_name'] ) ? trim( $_POST['hiden_sponsor_name'] ) : '';
			self::$opts['sponsor_kana'] = isset( $_POST['hiden_sponsor_kana'] ) ? trim( $_POST['hiden_sponsor_kana'] ) : '';

			self::$opts['sponsor_code'] = isset( $_POST['hiden_sponsor_code'] ) ? trim( $_POST['hiden_sponsor_code'] ) : '';
			self::$opts['packing']      = isset( $_POST['packing'] ) ? trim( $_POST['packing'] ) : '';
			self::$opts['packing_tag']  = isset( $_POST['packing_tag'] ) ? trim( $_POST['packing_tag'] ) : '';
			self::$opts['payment_type'] = isset( $_POST['payment_type'] ) ? trim( $_POST['payment_type'] ) : '0';
			self::$opts['seal1']        = isset( $_POST['hiden_seal1'] ) ? trim( $_POST['hiden_seal1'] ) : '';
			self::$opts['seal2']        = isset( $_POST['hiden_seal2'] ) ? trim( $_POST['hiden_seal2'] ) : '';
			self::$opts['seal3']        = isset( $_POST['hiden_seal3'] ) ? trim( $_POST['hiden_seal3'] ) : '';

			$options                         = get_option( 'usces_ex' );
			$options['system']['e-hidencsv'] = self::$opts;
			update_option( 'usces_ex', $options );
		}
	}

	/**
	 * Setting form.
	 */
	public function setting_form() {
		$status = self::$opts['activate_flag'] ? '<span class="running">' . __( 'Running', 'usces' ) . '</span>' : '<span class="stopped">' . __( 'Stopped', 'usces' ) . '</span>';
		?>
	<form action="" method="post" name="option_form" id="e-hidencsv_form">
	<div class="postbox">
		<div class="postbox-header">
			<h2><span>佐川急便e飛伝Ⅲ連携</span><?php echo $status; ?></h2>
			<div class="handle-actions"><button type="button" class="handlediv" id="e-hidencsv"><span class="screen-reader-text"><?php echo esc_html( sprintf( __( 'Toggle panel: %s' ), '佐川急便e飛伝Ⅲ連携' ) ); ?></span><span class="toggle-indicator"></span></button></div>
		</div>
		<div class="inside">
		<table class="form_table">
			<tr height="35">
				<th class="system_th"><a style="cursor:pointer;" onclick="toggleVisibility('ex_hiden_activate_flag');"><?php esc_html_e( 'Activation', 'usces' ); ?></a></th>
				<td width="10"><input name="hiden_activate_flag" id="hiden_activate_flag0" type="radio" value="0"<?php checked( self::$opts['activate_flag'], 0 ); ?>/></td>
				<td width="100"><label for="hiden_activate_flag0"><?php esc_html_e( 'disable', 'usces' ); ?></label></td>
				<td width="10"><input name="hiden_activate_flag" id="hiden_activate_flag1" type="radio" value="1"<?php checked( self::$opts['activate_flag'], 1 ); ?>/></td>
				<td width="100"><label for="hiden_activate_flag1"><?php esc_html_e( 'enable', 'usces' ); ?></label></td>
				<td><div id="ex_hiden_activate_flag" class="explanation">e飛伝Ⅲ用のCSV入出力機能を有効化する</div></td>
			</tr>
			<tr height="35">
				<th class="system_th"><a style="cursor:pointer;" onclick="toggleVisibility( 'ex_hiden_character_limit' );">文字数制限の対応</a></th>
				<td width="10"><input name="hiden_character_limit" id="hiden_character_limit0" type="radio" value="0"<?php checked( self::$opts['character_limit'], 0 ); ?>/></td>
				<td width="100"><label for="hiden_character_limit0">カットしない</label></td>
				<td width="10"><input name="hiden_character_limit" id="hiden_character_limit1" type="radio" value="1"<?php checked( self::$opts['character_limit'], 1 ); ?>/></td>
				<td><label for="hiden_character_limit1">カットする</label></td>
				<td><div id="ex_hiden_character_limit" class="explanation">住所など文字数制限があるものに対して、文字数がオーバーしていた場合の処理</div></td>
			</tr>
			<tr height="35">
				<th class="system_th"><a style="cursor:pointer;" onclick="toggleVisibility('ex_hiden_time_zone');">配達時間帯選択肢</a></th>
				<td width="10" colspan="4"><textarea name="hiden_time_zone" id="hiden_time_zone" rows="5" ><?php echo esc_html( self::$opts['time_zone'] ); ?></textarea></td>
				<td><div id="ex_hiden_time_zone" class="explanation">対応する配達時間帯。半角セミコロン左側の文字列が配送方法で指定した選択肢と同じになるよう指定してください。</div></td>
			</tr>

			<tr height="25">
				<th class="system_th"><a style="cursor:pointer;" onclick="toggleVisibility('ex_hiden_sponsor_flag');">依頼主の出力方法</a></th>
				<td width="10"><input name="hiden_sponsor_flag" id="hiden_sponsor_flag0" type="radio" value="0"<?php checked( self::$opts['sponsor_flag'], 0 ); ?>/></td><td width="100"><label for="hiden_sponsor_flag0">購入者情報</label></td>
				<td width="10"><input name="hiden_sponsor_flag" id="hiden_sponsor_flag1" type="radio" value="1"<?php checked( self::$opts['sponsor_flag'], 1 ); ?>/></td><td width="100"><label for="hiden_sponsor_flag1">固定情報</label></td>
				<td><div id="ex_hiden_sponsor_flag" class="explanation">依頼主情報を購入者にするか、固定にするかを選択します。固定にする場合は下記依頼主情報を入力する必要があります。</div></td>
			</tr>
			<tr height="25" class="hiden_sponsor">
				<th class="system_th"><a style="cursor:pointer;" onclick="toggleVisibility('ex_hiden_sponsor_tel');">ご依頼主電話番号</a></th>
				<td width="10" colspan="4"><input name="hiden_sponsor_tel" id="hiden_sponsor_tel" type="text" value="<?php echo esc_attr( self::$opts['sponsor_tel'] ); ?>" /></td>
				<td><div id="ex_hiden_sponsor_tel" class="explanation">半角数字、"-"ハイフン入力可</div></td>
			</tr>
			<tr height="25" class="hiden_sponsor">
				<th class="system_th"><a style="cursor:pointer;" onclick="toggleVisibility('ex_hiden_sponsor_zip');">ご依頼主郵便番号</a></th>
				<td width="10" colspan="4"><input name="hiden_sponsor_zip" id="hiden_sponsor_zip" type="text" value="<?php echo esc_attr( self::$opts['sponsor_zip'] ); ?>" /></td>
				<td><div id="ex_hiden_sponsor_zip" class="explanation">半角数字、"-"ハイフン入力可</div></td>
			</tr>
			<tr height="25" class="hiden_sponsor">
				<th class="system_th"><a style="cursor:pointer;" onclick="toggleVisibility('ex_hiden_sponsor_add1');">ご依頼主住所１</a></th>
				<td width="10" colspan="4"><input name="hiden_sponsor_add1" id="hiden_sponsor_add1" type="text" value="<?php echo esc_attr( self::$opts['sponsor_add1'] ); ?>" /></td>
				<td><div id="ex_hiden_sponsor_add1" class="explanation">全角16文字</div></td>
			</tr>
			<tr height="25" class="hiden_sponsor">
				<th class="system_th"><a style="cursor:pointer;" onclick="toggleVisibility('ex_hiden_sponsor_add2');">ご依頼主住所２</a></th>
				<td width="10" colspan="4"><input name="hiden_sponsor_add2" id="hiden_sponsor_add2" type="text" value="<?php echo esc_attr( self::$opts['sponsor_add2'] ); ?>" /></td>
				<td><div id="ex_hiden_sponsor_add2" class="explanation">全角16文字</div></td>
			</tr>
			<tr height="25" class="hiden_sponsor">
				<th class="system_th"><a style="cursor:pointer;" onclick="toggleVisibility('ex_hiden_sponsor_name');">ご依頼主名称１</a></th>
				<td width="10" colspan="4"><input name="hiden_sponsor_name" id="hiden_sponsor_name" type="text" value="<?php echo esc_attr( self::$opts['sponsor_name'] ); ?>" /></td>
				<td><div id="ex_hiden_sponsor_name" class="explanation">全角16文字</div></td>
			</tr>
			<tr height="25" class="hiden_sponsor">
				<th class="system_th"><a style="cursor:pointer;" onclick="toggleVisibility('ex_hiden_sponsor_kana');">ご依頼主名称２</a></th>
				<td width="10" colspan="4"><input name="hiden_sponsor_kana" id="hiden_sponsor_kana" type="text" value="<?php echo esc_attr( self::$opts['sponsor_kana'] ); ?>" /></td>
				<td><div id="ex_hiden_sponsor_kana" class="explanation">全角16文字</div></td>
			</tr>
			<tr height="1" class="hiden_sponsor"><th></th><td colspan="5"></td></tr>

			<tr height="35" class="hiden_sponsor">
				<th class="system_th"><a style="cursor:pointer;" onclick="toggleVisibility('ex_hiden_sponsor_code');">お客様コード</a></th>
				<td width="10" colspan="4"><input name="hiden_sponsor_code" id="hiden_sponsor_code" type="text" value="<?php echo esc_attr( self::$opts['sponsor_code'] ); ?>" /></td>
				<td><div id="ex_hiden_sponsor_code" class="explanation">荷送人のお客様コード</div></td>
			</tr>
			<tr height="35">
				<th class="system_th"><a style="cursor:pointer;" onclick="toggleVisibility('ex_packing');">荷姿</a></th>
				<td width="10" colspan="4">
					<select name="packing" id="packing">
						<option value=""></option>
						<option value="001" <?php selected( self::$opts['packing'], '001' ); ?>>001 ：箱類</option>
						<option value="002" <?php selected( self::$opts['packing'], '002' ); ?>>002 ：バッグ類</option>
						<option value="003" <?php selected( self::$opts['packing'], '003' ); ?>>003 ：スーツケース</option>
						<option value="004" <?php selected( self::$opts['packing'], '004' ); ?>>004 ：封筒類</option>
						<option value="005" <?php selected( self::$opts['packing'], '005' ); ?>>005 ：ゴルフバッグ</option>
						<option value="006" <?php selected( self::$opts['packing'], '006' ); ?>>006 ：スキー</option>
						<option value="007" <?php selected( self::$opts['packing'], '007' ); ?>>007 ：スノーボード</option>
						<option value="008" <?php selected( self::$opts['packing'], '008' ); ?>>008 ：その他</option>
					</select>
				</td>
				<td><div id="ex_packing" class="explanation">梱包する荷物の形状が全て同じになる場合に設定してください。</div></td>
			</tr>
			<tr height="35">
				<th class="system_th"><a style="cursor:pointer;" onclick="toggleVisibility('ex_packing_tag');">荷札荷姿</a></th>
				<td width="10" colspan="4">
					<select name="packing_tag" id="packing_tag">
						<option value=""></option>
						<option value="001" <?php selected( self::$opts['packing_tag'], '001' ); ?>>001 ：箱類</option>
						<option value="002" <?php selected( self::$opts['packing_tag'], '002' ); ?>>002 ：バッグ類</option>
						<option value="003" <?php selected( self::$opts['packing_tag'], '003' ); ?>>003 ：スーツケース</option>
						<option value="004" <?php selected( self::$opts['packing_tag'], '004' ); ?>>004 ：封筒類</option>
						<option value="005" <?php selected( self::$opts['packing_tag'], '005' ); ?>>005 ：ゴルフバッグ</option>
						<option value="006" <?php selected( self::$opts['packing_tag'], '006' ); ?>>006 ：スキー</option>
						<option value="007" <?php selected( self::$opts['packing_tag'], '007' ); ?>>007 ：スノーボード</option>
						<option value="008" <?php selected( self::$opts['packing_tag'], '008' ); ?>>008 ：その他</option>
					</select>
				</td>
				<td><div id="ex_packing_tag" class="explanation">梱包する荷物の形状が全て同じになる場合に設定してください。</div></td>
			</tr>
			<tr height="35">
				<th class="system_th"><a style="cursor:pointer;" onclick="toggleVisibility('ex_payment_type');">決済種別</a></th>
				<td width="10" colspan="4">
					<select name="payment_type" id="payment_type">
						<option value="0" <?php selected( self::$opts['payment_type'], '0' ); ?>>0 ：指定なし</option>
						<option value="1" <?php selected( self::$opts['payment_type'], '1' ); ?>>1 ：全て可</option>
						<option value="2" <?php selected( self::$opts['payment_type'], '2' ); ?>>2 ：現金のみ</option>
						<option value="3" <?php selected( self::$opts['payment_type'], '3' ); ?>>3 ：ﾃﾞﾋﾞｯﾄ･ｸﾚｼﾞｯﾄ"</option>
					</select>
				</td>
				<td><div id="ex_payment_type" class="explanation">代引きで利用できる決済種別を指定できます。</div></td>
			</tr>
			<tr height="35">
				<th class="system_th"><a style="cursor:pointer;" onclick="toggleVisibility('ex_hiden_seal1');">指定シール１</a></th>
				<td width="10" colspan="4">
					<select name="hiden_seal1" id="hiden_seal1" value="<?php echo esc_attr( self::$opts['seal1'] ); ?>">
						<option value=""></option>
						<option value="001" <?php selected( self::$opts['seal1'], '001' ); ?>>001 ：飛脚クール便（冷蔵）</option>
						<option value="002" <?php selected( self::$opts['seal1'], '002' ); ?>>002 ：飛脚クール便（冷凍）</option>
						<option value="004" <?php selected( self::$opts['seal1'], '004' ); ?>>004 ：営業所受取サービス</option>
						<option value="005" <?php selected( self::$opts['seal1'], '005' ); ?>>005 ：指定日配達サービス</option>
						<option value="008" <?php selected( self::$opts['seal1'], '008' ); ?>>008 ：ｅコレクト（現金）</option>
						<option value="009" <?php selected( self::$opts['seal1'], '009' ); ?>>009 ：ｅコレクト（デビット／クレジット）</option>
						<option value="010" <?php selected( self::$opts['seal1'], '010' ); ?>>010 ：ｅコレクト（全て可能）</option>
						<option value="011" <?php selected( self::$opts['seal1'], '011' ); ?>>011 ：取扱注意</option>
						<option value="012" <?php selected( self::$opts['seal1'], '012' ); ?>>012 ：貴重品</option>
						<option value="013" <?php selected( self::$opts['seal1'], '013' ); ?>>013 ：天地無用</option>
						<option value="017" <?php selected( self::$opts['seal1'], '017' ); ?>>017 ：飛脚航空便</option>
						<option value="018" <?php selected( self::$opts['seal1'], '018' ); ?>>018 ：飛脚ジャストタイム便）</option>
						<option value="020" <?php selected( self::$opts['seal1'], '020' ); ?>>020 ：時間帯指定サービス（午前中）</option>
						<option value="021" <?php selected( self::$opts['seal1'], '021' ); ?>>021 ：時間帯指定サービス（18時～21時）</option>
						<option value="022" <?php selected( self::$opts['seal1'], '022' ); ?>>022 ：時間帯指定サービス（12時～14時）</option>
						<option value="023" <?php selected( self::$opts['seal1'], '023' ); ?>>023 ：時間帯指定サービス（14時～16時）</option>
						<option value="024" <?php selected( self::$opts['seal1'], '024' ); ?>>024 ：時間帯指定サービス（16時～18時）</option>
						<option value="025" <?php selected( self::$opts['seal1'], '025' ); ?>>025 ：時間帯指定サービス（18時～20時）</option>
						<option value="026" <?php selected( self::$opts['seal1'], '026' ); ?>>026 ：時間帯指定サービス（19時～21時）</option>
					</select>
				</td>
				<td><div id="ex_hiden_seal1" class="explanation">設定する場合は、指定シール１・指定シール２・指定シール３の間で矛盾がないように設定してください。</div></td>
			</tr>
			<tr height="35">
				<th class="system_th"><a style="cursor:pointer;" onclick="toggleVisibility('ex_hiden_seal2');">指定シール２</a></th>
				<td width="10" colspan="4">
					<select name="hiden_seal2" id="hiden_seal1" value="<?php echo esc_attr( self::$opts['seal2'] ); ?>" >
						<option value=""></option>
						<option value="001" <?php selected( self::$opts['seal2'], '001' ); ?>>001 ：飛脚クール便（冷蔵）</option>
						<option value="002" <?php selected( self::$opts['seal2'], '002' ); ?>>002 ：飛脚クール便（冷凍）</option>
						<option value="004" <?php selected( self::$opts['seal2'], '004' ); ?>>004 ：営業所受取サービス</option>
						<option value="005" <?php selected( self::$opts['seal2'], '005' ); ?>>005 ：指定日配達サービス</option>
						<option value="008" <?php selected( self::$opts['seal2'], '008' ); ?>>008 ：ｅコレクト（現金）</option>
						<option value="009" <?php selected( self::$opts['seal2'], '009' ); ?>>009 ：ｅコレクト（デビット／クレジット）</option>
						<option value="010" <?php selected( self::$opts['seal2'], '010' ); ?>>010 ：ｅコレクト（全て可能）</option>
						<option value="011" <?php selected( self::$opts['seal2'], '011' ); ?>>011 ：取扱注意</option>
						<option value="012" <?php selected( self::$opts['seal2'], '012' ); ?>>012 ：貴重品</option>
						<option value="013" <?php selected( self::$opts['seal2'], '013' ); ?>>013 ：天地無用</option>
						<option value="017" <?php selected( self::$opts['seal2'], '017' ); ?>>017 ：飛脚航空便</option>
						<option value="018" <?php selected( self::$opts['seal2'], '018' ); ?>>018 ：飛脚ジャストタイム便）</option>
						<option value="020" <?php selected( self::$opts['seal2'], '020' ); ?>>020 ：時間帯指定サービス（午前中）</option>
						<option value="021" <?php selected( self::$opts['seal2'], '021' ); ?>>021 ：時間帯指定サービス（18時～21時）</option>
						<option value="022" <?php selected( self::$opts['seal2'], '022' ); ?>>022 ：時間帯指定サービス（12時～14時）</option>
						<option value="023" <?php selected( self::$opts['seal2'], '023' ); ?>>023 ：時間帯指定サービス（14時～16時）</option>
						<option value="024" <?php selected( self::$opts['seal2'], '024' ); ?>>024 ：時間帯指定サービス（16時～18時）</option>
						<option value="025" <?php selected( self::$opts['seal2'], '025' ); ?>>025 ：時間帯指定サービス（18時～20時）</option>
						<option value="026" <?php selected( self::$opts['seal2'], '026' ); ?>>026 ：時間帯指定サービス（19時～21時）</option>
					</select>
				</td>
				<td><div id="ex_hiden_seal2" class="explanation">設定する場合は、指定シール１・指定シール２・指定シール３の間で矛盾がないように設定してください。</div></td>
			</tr>
			<tr height="35">
				<th class="system_th"><a style="cursor:pointer;" onclick="toggleVisibility('ex_hiden_seal3');">指定シール３</a></th>
				<td width="10" colspan="4">
					<select name="hiden_seal3" id="hiden_seal1" value="<?php echo esc_attr( self::$opts['seal3'] ); ?>" >
						<option value=""></option>
						<option value="001" <?php selected( self::$opts['seal3'], '001' ); ?>>001 ：飛脚クール便（冷蔵）</option>
						<option value="002" <?php selected( self::$opts['seal3'], '002' ); ?>>002 ：飛脚クール便（冷凍）</option>
						<option value="004" <?php selected( self::$opts['seal3'], '004' ); ?>>004 ：営業所受取サービス</option>
						<option value="005" <?php selected( self::$opts['seal3'], '005' ); ?>>005 ：指定日配達サービス</option>
						<option value="008" <?php selected( self::$opts['seal3'], '008' ); ?>>008 ：ｅコレクト（現金）</option>
						<option value="009" <?php selected( self::$opts['seal3'], '009' ); ?>>009 ：ｅコレクト（デビット／クレジット）</option>
						<option value="010" <?php selected( self::$opts['seal3'], '010' ); ?>>010 ：ｅコレクト（全て可能）</option>
						<option value="011" <?php selected( self::$opts['seal3'], '011' ); ?>>011 ：取扱注意</option>
						<option value="012" <?php selected( self::$opts['seal3'], '012' ); ?>>012 ：貴重品</option>
						<option value="013" <?php selected( self::$opts['seal3'], '013' ); ?>>013 ：天地無用</option>
						<option value="017" <?php selected( self::$opts['seal3'], '017' ); ?>>017 ：飛脚航空便</option>
						<option value="018" <?php selected( self::$opts['seal3'], '018' ); ?>>018 ：飛脚ジャストタイム便）</option>
						<option value="020" <?php selected( self::$opts['seal3'], '020' ); ?>>020 ：時間帯指定サービス（午前中）</option>
						<option value="021" <?php selected( self::$opts['seal3'], '021' ); ?>>021 ：時間帯指定サービス（18時～21時）</option>
						<option value="022" <?php selected( self::$opts['seal3'], '022' ); ?>>022 ：時間帯指定サービス（12時～14時）</option>
						<option value="023" <?php selected( self::$opts['seal3'], '023' ); ?>>023 ：時間帯指定サービス（14時～16時）</option>
						<option value="024" <?php selected( self::$opts['seal3'], '024' ); ?>>024 ：時間帯指定サービス（16時～18時）</option>
						<option value="025" <?php selected( self::$opts['seal3'], '025' ); ?>>025 ：時間帯指定サービス（18時～20時）</option>
						<option value="026" <?php selected( self::$opts['seal3'], '026' ); ?>>026 ：時間帯指定サービス（19時～21時）</option>
					</select>
				</td>
				<td><div id="ex_hiden_seal3" class="explanation">設定する場合は、指定シール１・指定シール２・指定シール３の間で矛盾がないように設定してください。</div></td>
			</tr>
		</table>
		<hr />
		<input name="usces_hiden_option_update" type="submit" class="button button-primary" value="<?php esc_html_e( 'change decision', 'usces' ); ?>" />
		</div>
	</div><!--postbox-->
		<?php wp_nonce_field( 'admin_system', 'wc_nonce' ); ?>
	</form>
		<?php
	}

	/**
	 * Hiden action
	 *
	 * @param string $order_action order_action.
	 */
	public function hiden_action( $order_action ) {

		switch ( $order_action ) {
			case 'hiden_register':
				if ( isset( $_GET['hiden_regfile'] ) && ! WCUtils::is_blank( $_GET['hiden_regfile'] ) ) {
					$res                   = $this->register_tracking_number();
					$_GET['usces_status']  = isset( $res['status'] ) ? $res['status'] : '';
					$_GET['usces_message'] = isset( $res['message'] ) ? $res['message'] : '';
					add_filter( 'usces_order_list_action_status', array( $this, 'order_list_action_status' ) );
					add_filter( 'usces_order_list_action_message', array( $this, 'order_list_action_message' ) );
				}
				break;

			case 'dl_hidencsv':
				$this->outcsv_shipping();
				break;
		}
	}

	/**
	 * Action status
	 *
	 * @param array $status status.
	 */
	public function order_list_action_status( $status ) {
		if ( isset( $_GET['usces_status'] ) && ! empty( $_GET['usces_status'] ) ) {
			$status = $_GET['usces_status'];
		}
		return $status;
	}

	/**
	 * Action message
	 *
	 * @param string $message message.
	 */
	public function order_list_action_message( $message ) {
		if ( isset( $_GET['usces_message'] ) && ! empty( $_GET['usces_message'] ) ) {
			$message = $_GET['usces_message'];
		}
		return $message;
	}

	/**
	 * Action button.
	 */
	public function action_button() {
		?>
		<input type="button" id="up_hidencsv" class="searchbutton button" value="e飛伝Ⅲ出荷データ取込" />
		<input type="button" id="dl_hidencsv" class="searchbutton button" value="e飛伝Ⅲ出荷データ出力" />
		<?php
	}

	/**
	 * Order list page js.
	 */
	public function order_list_page_js() {
		$_wp_http_referer = urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) );
		$wc_nonce         = wp_create_nonce( 'admin_system' );
		$html             = '
		$("#hiden_upload_dialog").dialog({
			bgiframe: true,
			autoOpen: false,
			title: "e飛伝Ⅲ出荷データ取込",
			height: 300,
			width: 500,
			modal: true,
			buttons: {
				Cancel: function() {
					$(this).dialog("close");
				}
			},
			close: function() {}
		});
		$("#up_hidencsv").click(function() {
			$("#hiden_upload_dialog").dialog({
				bgiframe: true,
				autoOpen: false,
				title: "e飛伝Ⅲ出荷データ取込",
				height: 300,
				width: 500,
				modal: true,
				buttons: {
					' . __( 'close', 'usces' ) . ': function() {
						$(this).dialog("close");
					}
				},
				close: function() {}
			}).dialog( "open" );
		});
		$("#dl_hidencsv").click(function() {
			if( $("input[name*=\'listcheck\']:checked").length == 0 ) {
				alert("' . __( 'Choose the data.', 'usces' ) . '");
				$("#oederlistaction").val("");
				return false;
			}
			var listcheck = "";
			$("input[name*=\'listcheck\']").each(function(i) {
				if( $(this).prop("checked") ) {
					listcheck += "&listcheck["+i+"]="+$(this).val();
				}
			});
			location.href = "' . USCES_ADMIN_URL . '?page=usces_orderlist&order_action=dl_hidencsv"+listcheck+"&noheader=true&_wp_http_referer=' . $_wp_http_referer . '&wc_nonce=' . $wc_nonce . '";
		});
		';
		echo $html;
	}

	/**
	 * System js.
	 */
	public function system_js() {
		?>
	<script type="text/javascript">
		jQuery(function($){
			$("input[name='hiden_sponsor_flag']").change(function() {
				if( $(this).val() == "0" ) {
					$(".hiden_sponsor").hide("slow");
				}else{
					$(".hiden_sponsor").show("slow");
				}
			});
			if( $("input[name='hiden_sponsor_flag']:checked").val() == "0" ) {
				$(".hiden_sponsor").hide();
			}else{
				$(".hiden_sponsor").show();
			}
		});
	</script>
		<?php
	}

	/**
	 * Date.
	 *
	 * @param string $date date.
	 */
	private function isdate( $date ) {
		if ( empty( $date ) ) {
			return false;
		}
		try {
			new DateTime( $date );
			list( $year, $month, $day ) = explode( '-', $date );
			$res                        = checkdate( (int) $month, (int) $day, (int) $year );
			return $res;
		} catch ( Exception $e ) {
			return false;
		}
	}

	/**
	 * データ取込ボタン
	 */
	public function order_list_footer() {
		$html = '
		<div id="hiden_upload_dialog">
			<p>e飛伝ⅢからのCSVファイルをアップロードして出荷データの取込を行います。<br>ファイルを選択して取込開始を押してください。</p>
			<form action="' . USCES_ADMIN_URL . '" method="post" enctype="multipart/form-data" name="hidenupform" id="hidenupform">
				<input name="usces_upcsv" type="file" id="usces_hidenupcsv" style="width:100%" />
				<input name="uploadcsv" type="submit" id="hidenupcsv" value="取込開始" />
				<input name="page" type="hidden" value="usces_hiden_tracking" />
				<input name="action" type="hidden" value="hidenupload" />
				' . wp_nonce_field( 'admin_system', 'wc_nonce', false, false ) . wp_referer_field( false ) . '
			</form>
		</div>';
		echo $html;
	}

	/**
	 * Upload　tracking　number.
	 */
	public function upload_tracking_number() {
		global $wpdb, $usces;

		check_admin_referer( 'admin_system', 'wc_nonce' );
		// Upload.
		$path = WP_CONTENT_DIR . '/uploads/';
		if ( isset( $_REQUEST['action'] ) && 'hidenupload' == $_REQUEST['action'] ) {
			$workfile = $_FILES['usces_upcsv']['tmp_name'];
			if ( ! is_uploaded_file( $workfile ) ) {
				$res['status']  = 'error';
				$res['message'] = 'ファイルをアップロードできませんでした。';
				$url            = USCES_ADMIN_URL . '?page=usces_orderlist&usces_status=' . $res['status'] . '&usces_message=' . urlencode( $res['message'] );
				wp_redirect( $url );
				exit;
			}

			list( $fname, $fext ) = explode( '.', $_FILES['usces_upcsv']['name'], 2 );
			if ( 'csv' != $fext ) {
				$res['status']  = 'error';
				$res['message'] = '対応しないファイルがアップロードされました。' . $fname . '.' . $fext;
				$url            = USCES_ADMIN_URL . '?page=usces_orderlist&usces_status=' . $res['status'] . '&usces_message=' . urlencode( $res['message'] );
				wp_redirect( $url );
				exit;
			}

			$new_filename = base64_encode( $fname . '_' . time() . '.' . $fext );
			if ( ! move_uploaded_file( $_FILES['usces_upcsv']['tmp_name'], $path . $new_filename ) ) {
				$res['status']  = 'error';
				$res['message'] = 'ファイルを保存できませんでした。' . $fname . '.' . $fext;
				$url            = USCES_ADMIN_URL . '?page=usces_orderlist&usces_status=' . $res['status'] . '&usces_message=' . urlencode( $res['message'] );
				wp_redirect( $url );
				exit;
			}
			return $new_filename;
		}
	}

	/**
	 * Register tracking number.
	 */
	public function register_tracking_number() {
		global $wpdb, $usces;

		$path = WP_CONTENT_DIR . '/uploads/';
		if ( isset( $_REQUEST['hiden_regfile'] ) && ! WCUtils::is_blank( $_REQUEST['hiden_regfile'] ) && isset( $_REQUEST['order_action'] ) && 'hiden_register' == wp_unslash( $_REQUEST['order_action'] ) ) {
			$file_name       = wp_unslash( $_REQUEST['hiden_regfile'] );
			$decode_filename = base64_decode( $file_name );
			if ( ! file_exists( $path . $file_name ) ) {
				$res['status']  = 'error';
				$res['message'] = 'CSVファイルが存在しません。' . esc_html( $decode_filename );
				return( $res );
			}
		}

		$wpdb->query( 'SET SQL_BIG_SELECTS=1' );
		set_time_limit( 3600 );

		define( 'HIDEN_TRACKING_NUMBER', 0 ); /* 荷物配送番号 */
		define( 'HIDEN_TRACKING_DATE', 2 ); /* 出荷日付 */
		define( 'HIDEN_ORDER_ID', 18 ); /* Welcart受注番号 */

		$orglines  = array();
		$sp        = ',';
		$total_num = 0;
		$comp_num  = 0;
		$err_num   = 0;
		$res       = array();

		if ( ! ( $fpo = fopen( $path . $file_name, 'r' ) ) ) {
			$res['status']  = 'error';
			$res['message'] = 'ファイルが開けません。' . esc_html( $decode_filename );
			return $res;
		}

		$fname_parts = explode( '.', $decode_filename );
		if ( 'csv' !== end( $fname_parts ) ) {
			$res['status']  = 'error';
			$res['message'] = 'このファイルはCSVファイルではありません。' . esc_html( $decode_filename );
			return $res;

		} else {
			$buf = '';
			while ( ! feof( $fpo ) ) {
				$temp = fgets( $fpo, 10240 );
				if ( 0 == strlen( $temp ) ) {
					continue;
				}
				$orglines[] = str_replace( '"', '', $temp );
			}
		}
		fclose( $fpo );

		print_r($orglines);
		foreach ( $orglines as $line ) {
			$data = explode( $sp, $line );
			if ( is_numeric( $data[0] ) ) {
				$total_num++;
				$hiden_order_id = mb_convert_encoding( $data[ HIDEN_ORDER_ID ], 'UTF-8', 'SJIS' );
				$boids          = explode( '__', $hiden_order_id, 2 );
				if ( isset( $boids[1] ) ) {
					$group_id = $boids[1];
				} else {
					$group_id = false;
				}
				$order_id = $this->get_order_id_from_dec( $boids[0] );
				// $order_id = (int) $boids[0];

				$tracking_number = mb_convert_encoding( $data[ HIDEN_TRACKING_NUMBER ], 'UTF-8', 'SJIS' );
				$order_data      = $usces->get_order_data( $order_id );
				if ( $order_data ) {
					if ( false !== $group_id ) {
						$group_value                     = unserialize( $usces->get_order_meta_value( ( 'group_' . $group_id ), $order_id ) );
						$group_value['delivery_company'] = '佐川急便';
						$group_value['tracking_number']  = trim( $tracking_number );
						$usces->set_order_meta_value( ( 'group_' . $group_id ), serialize( $group_value ), $order_id );
					} else {
						$usces->set_order_meta_value( 'tracking_number', trim( $tracking_number ), $order_id );
						$usces->set_order_meta_value( 'delivery_company', '佐川急便', $order_id );
					}
					$comp_num++;
				} else {
					$err_num++;
				}
			}
		}
		$res['status']  = 'success';
		$res['message'] = sprintf( '%1$s行中 %2$s行登録完了、%3$s行未登録。', $total_num, $comp_num, $err_num );
		unlink( $path . $file_name );

		return $res;
	}

	/**
	 * After cart instant.
	 */
	public function after_cart_instant() {
		if ( isset( $_REQUEST['page'] ) && 'usces_hiden_tracking' == $_REQUEST['page'] && isset( $_REQUEST['action'] ) && 'hidenupload' == $_REQUEST['action'] ) {
			check_admin_referer( 'admin_system', 'wc_nonce' );
			$filename = self::upload_tracking_number();
			$url      = USCES_ADMIN_URL . '?page=usces_orderlist&usces_status=none&usces_message=&order_action=hiden_register&hiden_regfile=' . $filename;
			wp_redirect( $url );
			exit;
		}
	}

	/**
	 * Get orderid from dec.
	 *
	 * @param int $dec_order_id dec_order_id.
	 */
	public function get_order_id_from_dec( $dec_order_id ) {
		global $wpdb;
		$order_meta_table_name = $wpdb->prefix . 'usces_order_meta';
		$order_id              = $wpdb->get_var( $wpdb->prepare( "SELECT order_id FROM $order_meta_table_name WHERE meta_key = %s AND meta_value = %s LIMIT 1", 'dec_order_id', $dec_order_id ) );
		return $order_id;
	}

	/**
	 * Make individual cart.
	 *
	 * @param array $cart_org cart_org.
	 */
	public function make_individual_cart( $cart_org ) {

		$individual_cart = array();
		$normal_cart     = array();

		foreach ( $cart_org as $org ) {
			$is_individual = false;
			$post_id       = (int) $org['post_id'];
			$quantity      = (int) $org['quantity'];
			if ( function_exists( 'wel_get_product' ) ) {
				$product       = wel_get_product( $post_id, false );
				$is_individual = $product['itemIndividualSCharge'];
			} else {
				$is_individual = get_post_meta( $post_id, '_itemIndividualSCharge', true );
			}
			if ( $is_individual ) {
				$org['quantity'] = 1;
				for ( $i = 0; $i < $quantity; $i++ ) {
					$individual_cart[] = array( $org );
				}
			} else {
				$normal_cart[] = $org;
			}
		}
		if ( ! empty( $normal_cart ) ) {
			$individual_cart[] = $normal_cart;
		}
		return $individual_cart;
	}

	/**
	 * Outcsv shipping.
	 */
	public function outcsv_shipping() {
		global $usces;
		check_admin_referer( 'admin_system', 'wc_nonce' );

		$filename        = 'sagawa' . wp_date( 'YmdHis' ) . '.csv';
		$character_limit = (int) self::$opts['character_limit'];
		$ids             = wp_unslash( $_GET['listcheck'] );
		$packing         = self::$opts['packing'];
		$packing_tag     = self::$opts['packing_tag'];
		$seal1           = self::$opts['seal1'];
		$seal2           = self::$opts['seal2'];
		$seal3           = self::$opts['seal3'];
		$time_zone       = self::$opts['time_zone'];
		$time_zone       = str_replace( array( "\r\n", "\r" ), "\n", trim( $time_zone ) );
		$time_zone_arr   = explode( "\n", $time_zone );
		$delivery_time   = array();
		foreach ( (array) $time_zone_arr as $zone_str ) {
			list($key, $zone)      = explode( ';', trim( $zone_str ), 2 );
			$delivery_time[ $key ] = $zone;
		}
		$line  = '';
		$ldata = array(
			'お届け先コード取得区分'   => '',
			'お届け先コード'       => '',
			'お届け先電話番号'      => '',
			'お届け先郵便番号'      => '',
			'お届け先住所１（必須）'   => '',
			'お届け先住所２'       => '',
			'お届け先住所３'       => '',
			'お届け先名称１（必須）'   => '',
			'お届け先名称２'       => '',
			'お客様管理番号'       => '',
			'お客様コード'        => '',
			'部署ご担当者コード取得区分' => '',
			'部署ご担当者コード'     => '',
			'部署ご担当者名称'      => '',
			'荷送人電話番号'       => '',
			'ご依頼主コード取得区分'   => '',
			'ご依頼主コード'       => '',
			'ご依頼主電話番号'      => '',
			'ご依頼主郵便番号'      => '',
			'ご依頼主住所１'       => '',
			'ご依頼主住所２'       => '',
			'ご依頼主名称１'       => '',
			'ご依頼主名称２'       => '',
			'荷姿'            => '',
			'品名１'           => '',
			'品名２'           => '',
			'品名３'           => '',
			'品名４'           => '',
			'品名５'           => '',
			'荷札荷姿'          => '',
			'荷札品名１'         => '',
			'荷札品名２'         => '',
			'荷札品名３'         => '',
			'荷札品名４'         => '',
			'荷札品名５'         => '',
			'荷札品名６'         => '',
			'荷札品名７'         => '',
			'荷札品名８'         => '',
			'荷札品名９'         => '',
			'荷札品名１０'        => '',
			'荷札品名１１'        => '',
			'出荷個数'          => '',
			'スピード指定'        => '',
			'クール便指定'        => '',
			'配達日'           => '',
			'配達指定時間帯'       => '',
			'配達指定時間（時分）'    => '',
			'代引金額'          => '',
			'消費税'           => '',
			'決済種別'          => '',
			'保険金額'          => '',
			'指定シール１'        => '',
			'指定シール２'        => '',
			'指定シール３'        => '',
			'営業所受取'         => '',
			'SRC区分'         => '',
			'営業所受取営業所コード'   => '',
			'元着区分'          => '',
			'メールアドレス'       => '',
			'ご不在時連絡先'       => '',
			'出荷日'           => '',
			'お問い合せ送り状No.'   => '',
			'出荷場印字区分'       => '',
			'集約解除指定'        => '',
			'編集０１'          => '',
			'編集０２'          => '',
			'編集０３'          => '',
			'編集０４'          => '',
			'編集０５'          => '',
			'編集０６'          => '',
			'編集０７'          => '',
			'編集０８'          => '',
			'編集０９'          => '',
			'編集１０'          => '',
		);
		foreach ( $ldata as $lkey => $lvalue ) {
			$line .= '"' . $lkey . '",';
		}
		rtrim( $line, ',' );
		$line .= "\r\n";

		foreach ( (array) $ids as $order_id ) {

			$data     = $usces->get_order_data( $order_id, 'direct' );
			$delivery = unserialize( $data['order_delivery'] );

			$deco_order_id = $order_id;
			if ( ! empty( $data['order_delivery_date'] ) && $this->isdate( $data['order_delivery_date'] ) ) {
				$arrivaldate = str_replace( '-', '', $data['order_delivery_date'] );
			} else {
				$arrivaldate = '';
			}

			if ( ! empty( $data['order_delivery_time'] ) ) {
				if ( array_key_exists( $data['order_delivery_time'], $delivery_time ) ) {
					$arrivaltime = $delivery_time[ $data['order_delivery_time'] ];
				} else {
					$arrivaltime = '';
				}
			} else {
				$arrivaltime = '';
			}

			$order_date     = date_i18n( 'Ymd', strtotime( $data['order_date'] ) );
			$order_modified = isset( $data['order_modified'] ) ? str_replace( '-', '', $data['order_modified'] ) : '';
			$customer_num   = usces_get_deco_order_id( $deco_order_id );
			$deli_id        = (int) $data['order_delivery_method'];
			$opt_deli       = $usces->options['delivery_method'];
			$delivery_index = $usces->get_delivery_method_index( $deli_id );
			$deli_meth      = $opt_deli[ $delivery_index ];
			$cool_cat       = isset( $deli_meth['cool_category'] ) ? (int) $deli_meth['cool_category'] : 0;
			if ( 1 === $cool_cat ) {
				$cool_delivery = '003';
			} elseif ( 2 === $cool_cat ) {
				$cool_delivery = '002';
			} else {
				$cool_delivery = '001';
			}

			$total_full_price = $data['order_item_total_price'] - $data['order_usedpoint'] + $data['order_discount'] + $data['order_shipping_charge'] + $data['order_cod_fee'] + $data['order_tax'];
			if ( $total_full_price < 0 ) {
				$total_full_price = 0;
			}
			$payments = usces_get_payments_by_name( $data['order_payment_name'] );
			if ( 'COD' == $payments['settlement'] ) {
				$cod_price    = $total_full_price;
				$payment_type = self::$opts['payment_type'];
				if ( '0' === $payment_type ) {
					$payment_type = '1';
				}
			} else {
				$cod_price    = '';
				$payment_type = '';
			}
			$payment_type = apply_filters( 'wcseh_filter_outcsv_payment_type', $payment_type, $data );

			$syukkayoteibi = wp_date( 'Y/m/d' );

			if ( ! strtotime( $data['order_delivery_date'] ) ) {
				$otodokekiboubi = '';
			} else {
				$otodokekiboubi = str_replace( '-', '', $data['order_delivery_date'] );
			}

			if ( self::$opts['sponsor_flag'] ) {
				$sponsor_tel = str_replace( '―', '-', mb_convert_kana( self::$opts['sponsor_tel'], 'n' ) );
				$sponsor_zip = str_replace( '―', '-', mb_convert_kana( self::$opts['sponsor_zip'], 'n' ) );
				if ( 0 === $character_limit ) {
					$sponsor_add1  = mb_convert_kana( self::$opts['sponsor_add1'], 'A' );
					$sponsor_add2  = mb_convert_kana( self::$opts['sponsor_add2'], 'A' );
					$sponsor_name  = mb_convert_kana( self::$opts['sponsor_name'], 'A' );
					$sponsor_name2 = mb_convert_kana( self::$opts['sponsor_kana'], 'A' );
				} else {
					$sponsor_add1  = mb_substr( mb_convert_kana( self::$opts['sponsor_add1'], 'A' ), 0, 16 );
					$sponsor_add2  = mb_substr( mb_convert_kana( self::$opts['sponsor_add2'], 'A' ), 0, 16 );
					$sponsor_name  = mb_substr( mb_convert_kana( self::$opts['sponsor_name'], 'A' ), 0, 16 );
					$sponsor_name2 = mb_substr( mb_convert_kana( self::$opts['sponsor_kana'], 'A' ), 0, 16 );
				}
			} else {
				$sponsor_tel = str_replace( '―', '-', mb_convert_kana( $data['order_tel'], 'n' ) );
				$sponsor_zip = str_replace( '―', '-', mb_convert_kana( $data['order_zip'], 'n' ) );
				if ( 0 === $character_limit ) {
					$sponsor_add1 = mb_convert_kana( $data['order_pref'] . $data['order_address1'] . $data['order_address2'], 'A' );
					$sponsor_add2 = mb_convert_kana( $data['order_address3'], 'A' );
					$sponsor_name = mb_convert_kana( $data['order_name1'] . '　' . $data['order_name2'], 'A' );
				} else {
					$sponsor_add1 = mb_substr( mb_convert_kana( $data['order_pref'] . $data['order_address1'] . $data['order_address2'], 'A' ), 0, 16 );
					$sponsor_add2 = mb_substr( mb_convert_kana( $data['order_address3'], 'A' ), 0, 16 );
					$sponsor_name = mb_substr( mb_convert_kana( $data['order_name1'] . '　' . $data['order_name2'], 'A' ), 0, 16 );
				}
			}

			$sponsor_code = self::$opts['sponsor_code'];

			if ( isset( $delivery['delivery_flag'] ) && 2 == $delivery['delivery_flag'] && ! empty( $data['mem_id'] ) && function_exists( 'msa_get_orderdestination' ) ) {
				$orderdestination = msa_get_orderdestination( $order_id );
			} else {
				$orderdestination = array();
			}

			if ( 0 < count( $orderdestination ) ) {
				$msacart = msa_get_msacart_by_order( $order_id );
				foreach ( $orderdestination as $group_id => $destination ) {
					if ( isset( $delivery['delivery_flag'] ) && 2 == $delivery['delivery_flag'] && ! empty( $data['mem_id'] ) && isset( $orderdestination[ $group_id ] ) && function_exists( 'msa_get_destination' ) ) {
						$destination_info = msa_get_destination( $data['mem_id'], $orderdestination[ $group_id ]['destination_id'] );
					} else {
						$destination_info = array();
					}
					if ( 0 === $character_limit ) {
						$msa_address1 = mb_convert_kana( $destination_info['msa_pref'] . $destination_info['msa_address1'], 'A' );
						$msa_address2 = mb_convert_kana( $destination_info['msa_address2'], 'A' );
						$msa_address3 = mb_convert_kana( $destination_info['msa_address3'], 'A' );
						$msa_name     = mb_convert_kana( $destination_info['msa_name'], 'A' );
						$msa_name2    = mb_convert_kana( $destination_info['msa_name2'], 'A' );
					} else {
						$msa_address1 = mb_substr( mb_convert_kana( $destination_info['msa_pref'] . $destination_info['msa_address1'], 'A' ), 0, 16 );
						$msa_address2 = mb_substr( mb_convert_kana( $destination_info['msa_address2'], 'A' ), 0, 16 );
						$msa_address3 = mb_substr( mb_convert_kana( $destination_info['msa_address3'], 'A' ), 0, 16 );
						$msa_name     = mb_substr( mb_convert_kana( $destination_info['msa_name'], 'A' ), 0, 16 );
						$msa_name2    = mb_substr( mb_convert_kana( $destination_info['msa_name2'], 'A' ), 0, 16 );
					}
					$cart_org        = $msacart[ $group_id ]['cart'];
					$individual_cart = self::make_individual_cart( $cart_org );
					foreach ( $individual_cart as $cart ) {
						$ldata     = array(
							'お届け先コード取得区分'   => '',
							'お届け先コード'       => '',
							'お届け先電話番号'      => str_replace( '―', '-', mb_convert_kana( $destination_info['msa_tel'], 'n' ) ),
							'お届け先郵便番号'      => str_replace( '―', '-', mb_convert_kana( $destination_info['msa_zip'], 'n' ) ),
							'お届け先住所１（必須）'   => $msa_address1,
							'お届け先住所２'       => $msa_address2,
							'お届け先住所３'       => $msa_address3,
							'お届け先名称１（必須）'   => $msa_name,
							'お届け先名称２'       => $msa_name2,
							'お客様管理番号'       => $customer_num . '__' . $group_id,
							'お客様コード'        => $sponsor_code,
							'部署ご担当者コード取得区分' => '',
							'部署ご担当者コード'     => '',
							'部署ご担当者名称'      => '',
							'荷送人電話番号'       => '',
							'ご依頼主コード取得区分'   => '',
							'ご依頼主コード'       => '',
							'ご依頼主電話番号'      => $sponsor_tel,
							'ご依頼主郵便番号'      => $sponsor_zip,
							'ご依頼主住所１'       => $sponsor_add1,
							'ご依頼主住所２'       => $sponsor_add2,
							'ご依頼主名称１'       => $sponsor_name,
							'ご依頼主名称２'       => $sponsor_name2,
							'荷姿'            => $packing,
							'品名１'           => ( 0 === $character_limit ) ? mb_convert_kana( $cart[0]['item_name'], 'RNASKV' ) : mb_substr( mb_convert_kana( $cart[0]['item_name'], 'RNASKV' ), 0, 16 ),
							'品名２'           => ( isset( $cart[1] ) ? 'その他' : '' ),
							'品名３'           => '',
							'品名４'           => '',
							'品名５'           => '',
							'荷札荷姿'          => $packing_tag,
							'荷札品名１'         => '',
							'荷札品名２'         => '',
							'荷札品名３'         => '',
							'荷札品名４'         => '',
							'荷札品名５'         => '',
							'荷札品名６'         => '',
							'荷札品名７'         => '',
							'荷札品名８'         => '',
							'荷札品名９'         => '',
							'荷札品名１０'        => '',
							'荷札品名１１'        => '',
							'出荷個数'          => '',
							'スピード指定'        => '000',
							'クール便指定'        => $cool_delivery,
							'配達日'           => '',
							'配達指定時間帯'       => '',
							'配達指定時間（時分）'    => '',
							'代引金額'          => '',
							'消費税'           => '',
							'決済種別'          => '',
							'保険金額'          => '',
							'指定シール１'        => $seal1,
							'指定シール２'        => $seal2,
							'指定シール３'        => $seal3,
							'営業所受取'         => '',
							'SRC区分'         => '',
							'営業所受取営業所コード'   => '',
							'元着区分'          => '',
							'メールアドレス'       => '',
							'ご不在時連絡先'       => '',
							'出荷日'           => $order_modified,
							'お問い合せ送り状No.'   => '',
							'出荷場印字区分'       => '',
							'集約解除指定'        => '',
							'編集０１'          => '',
							'編集０２'          => '',
							'編集０３'          => '',
							'編集０４'          => '',
							'編集０５'          => '',
							'編集０６'          => '',
							'編集０７'          => '',
							'編集０８'          => '',
							'編集０９'          => '',
							'編集１０'          => '',
						);
						$line_data = apply_filters( 'wcseh_filter_outcsv_data', $ldata, $order_id, $data, $cart );
						foreach ( $line_data as $lkey => $lvalue ) {
							$line .= '"' . $lvalue . '",';
						}
						rtrim( $line, ',' );
						$line .= "\r\n";
					}
				}
			} else {
				if ( 0 === $character_limit ) {
					$deli_address1 = mb_convert_kana( $delivery['pref'] . $delivery['address1'], 'A' );
					$deli_address2 = mb_convert_kana( $delivery['address2'], 'A' );
					$deli_address3 = mb_convert_kana( $delivery['address3'], 'A' );
					$deli_name     = mb_convert_kana( $delivery['name1'], 'A' );
					$deli_name2    = mb_convert_kana( $delivery['name2'], 'A' );
				} else {
					$deli_address1 = mb_substr( mb_convert_kana( $delivery['pref'] . $delivery['address1'], 'A' ), 0, 16 );
					$deli_address2 = mb_substr( mb_convert_kana( $delivery['address2'], 'A' ), 0, 16 );
					$deli_address3 = mb_substr( mb_convert_kana( $delivery['address3'], 'A' ), 0, 16 );
					$deli_name     = mb_substr( mb_convert_kana( $delivery['name1'], 'A' ), 0, 16 );
					$deli_name2    = mb_substr( mb_convert_kana( $delivery['name2'], 'A' ), 0, 16 );
				}
				$cart_org        = usces_get_ordercartdata( $order_id );
				$individual_cart = self::make_individual_cart( $cart_org );
				foreach ( $individual_cart as $cart ) {
					$ldata     = array(
						'お届け先コード取得区分'   => '',
						'お届け先コード'       => '',
						'お届け先電話番号'      => str_replace( '―', '-', mb_convert_kana( $delivery['tel'], 'n' ) ),
						'お届け先郵便番号'      => str_replace( '―', '-', mb_convert_kana( $delivery['zipcode'], 'n' ) ),
						'お届け先住所１（必須）'   => $deli_address1,
						'お届け先住所２'       => $deli_address2,
						'お届け先住所３'       => $deli_address3,
						'お届け先名称１（必須）'   => $deli_name,
						'お届け先名称２'       => $deli_name2,
						'お客様管理番号'       => $customer_num,
						'お客様コード'        => $sponsor_code,
						'部署ご担当者コード取得区分' => '',
						'部署ご担当者コード'     => '',
						'部署ご担当者名称'      => '',
						'荷送人電話番号'       => '',
						'ご依頼主コード取得区分'   => '',
						'ご依頼主コード'       => '',
						'ご依頼主電話番号'      => $sponsor_tel,
						'ご依頼主郵便番号'      => $sponsor_zip,
						'ご依頼主住所１'       => $sponsor_add1,
						'ご依頼主住所２'       => $sponsor_add2,
						'ご依頼主名称１'       => $sponsor_name,
						'ご依頼主名称２'       => $sponsor_name2,
						'荷姿'            => $packing,
						'品名１'           => ( 0 === $character_limit ) ? mb_convert_kana( $cart[0]['item_name'], 'RNASKV' ) : mb_substr( mb_convert_kana( $cart[0]['item_name'], 'RNASKV' ), 0, 16 ),
						'品名２'           => ( isset( $cart[1] ) ? 'その他' : '' ),
						'品名３'           => '',
						'品名４'           => '',
						'品名５'           => '',
						'荷札荷姿'          => $packing_tag,
						'荷札品名１'         => '',
						'荷札品名２'         => '',
						'荷札品名３'         => '',
						'荷札品名４'         => '',
						'荷札品名５'         => '',
						'荷札品名６'         => '',
						'荷札品名７'         => '',
						'荷札品名８'         => '',
						'荷札品名９'         => '',
						'荷札品名１０'        => '',
						'荷札品名１１'        => '',
						'出荷個数'          => '',
						'スピード指定'        => '000',
						'クール便指定'        => $cool_delivery,
						'配達日'           => $otodokekiboubi,
						'配達指定時間帯'       => $arrivaltime,
						'配達指定時間（時分）'    => '',
						'代引金額'          => $cod_price,
						'消費税'           => '',
						'決済種別'          => $payment_type,
						'保険金額'          => '',
						'指定シール１'        => $seal1,
						'指定シール２'        => $seal2,
						'指定シール３'        => $seal3,
						'営業所受取'         => '',
						'SRC区分'         => '',
						'営業所受取営業所コード'   => '',
						'元着区分'          => '',
						'メールアドレス'       => '',
						'ご不在時連絡先'       => '',
						'出荷日'           => $order_modified,
						'お問い合せ送り状No.'   => '',
						'出荷場印字区分'       => '',
						'集約解除指定'        => '',
						'編集０１'          => '',
						'編集０２'          => '',
						'編集０３'          => '',
						'編集０４'          => '',
						'編集０５'          => '',
						'編集０６'          => '',
						'編集０７'          => '',
						'編集０８'          => '',
						'編集０９'          => '',
						'編集１０'          => '',
					);
					$line_data = apply_filters( 'wcseh_filter_outcsv_data', $ldata, $order_id, $data, $cart );
					foreach ( $line_data as $lkey => $lvalue ) {
						$line .= '"' . $lvalue . '",';
					}
					rtrim( $line, ',' );
					$line .= "\r\n";
				}
			}
		}
		ob_end_clean();
		// $line = mb_convert_encoding( $line, 'SJIS-win', 'UTF-8' );
		header( 'Content-Type: application/octet-stream' );
		header( "Content-Disposition: attachment; filename=\"$filename\"" );
		// mb_http_output( "pass" );
		print( $line );
		exit();
	}
}
