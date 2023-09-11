<?php
/**
 * DL Seller continue members list page.
 *
 * @package WCEX DL Seller
 */

require_once USCES_WP_PLUGIN_DIR . '/' . plugin_basename( dirname( __FILE__ ) ) . '/continueMemberList.class.php';

$con_list   = new ContinuationList();
$arr_column = $con_list->get_column();
$res        = $con_list->MakeTable();

$arr_search            = $con_list->GetSearchs();
$arr_header            = $con_list->GetListheaders();
$data_table_navigation = $con_list->GetDataTableNavigation();
$rows                  = $con_list->rows;
$action_status         = $con_list->get_action_status();
$action_message        = $con_list->get_action_message();
$action_status         = ( isset( $usces->action_status ) && 'none' !== $usces->action_status ) ? $usces->action_status : apply_filters( 'dlseller_continue_member_list_action_status', $action_status );
$action_message        = ( ! empty( $usces->action_message ) ) ? $usces->action_message : apply_filters( 'dlseller_continue_member_list_action_message', $action_message );

$usces_admin_path = '';
$admin_perse      = explode( '/', $_SERVER['REQUEST_URI'] );
$apct             = count( $admin_perse ) - 1;
for ( $ap = 0; $ap < $apct; $ap++ ) {
	$usces_admin_path .= $admin_perse[ $ap ] . '/';
}
$list_option       = get_option( 'usces_continuelist_option' );
$payment_structure = get_option( 'usces_payment_structure' );
$curent_url        = urlencode( esc_url( USCES_ADMIN_URL . '?' . $_SERVER['QUERY_STRING'] ) );

$dlseller_opt_continue = get_option( 'dlseller_opt_continue' );
$dlseller_opt_continue = apply_filters( 'dlseller_filter_opt_continue', $dlseller_opt_continue );
$chk_con               = ( isset( $dlseller_opt_continue['chk_con'] ) ) ? $dlseller_opt_continue['chk_con'] : array();
$applyform             = usces_get_apply_addressform( $usces->options['system']['addressform'] );
?>
<style>
#dialog_parent #wrap_icon_loading {
	display: inline-block;
	width: 20px;
	height: 20px;
	position: relative;
}
#dialog_parent #wrap_icon_loading > img {
	position: absolute;
	top: 10px;
}
</style>
<div class="wrap">
<div class="usces_admin">
<h1>Welcart Management <?php esc_html_e( 'Continue Members', 'dlseller' ); ?></h1>
<p class="version_info">Version <?php echo esc_html( WCEX_DLSELLER_VERSION ); ?></p>
<?php usces_admin_action_status( $action_status, $action_message ); ?>

<form action="<?php echo esc_url( USCES_ADMIN_URL . '?page=usces_continue' ); ?>" method="post" name="tablesearch" id="form_tablesearch">
<div id="datatable">
<div class="usces_tablenav usces_tablenav_top">
	<?php echo wp_kses_post( $data_table_navigation ); ?>
	<div id="searchVisiLink" class="screen-field"><?php esc_html_e( 'Show the Operation field', 'usces' ); ?><span class="dashicons dashicons-arrow-down"></span></div>
	<div class="refresh"><a href="<?php echo esc_url( USCES_ADMIN_URL . '?page=usces_continue&refresh' ); ?>"><span class="dashicons dashicons-update"></span><?php esc_html_e( 'updates it to latest information', 'usces' ); ?></a></div>
</div>

<?php do_action( 'dlseller_action_continue_member_list_table_header' ); ?>
<div id="tablesearch" class="usces_tablesearch">
<div id="searchBox">

	<table class="search_table">
	<tr>
		<td class="label"><?php esc_html_e( 'Order Search', 'usces' ); ?></td>
		<td>
			<div class="order_search_item search_item">
				<p class="search_item_label"><?php esc_html_e( 'From order information', 'usces' ); ?></p>
				<p>
					<select name="search[order_column][0]" id="searchorderselect_0" class="searchselect">
						<option value=""> </option>
					<?php
					foreach ( (array) $arr_column as $key => $value ) :
						if ( 'ID' === $key ) {
							continue;
						}
						?>
						<option value="<?php echo esc_attr( $key ); ?>"<?php selected( $arr_search['order_column'][0], $key ); ?>><?php echo esc_html( $value ); ?></option>
					<?php endforeach; ?>
					</select>
					<span id="searchorderword_0">
					<input name="search[order_word][0]" type="text" value="<?php echo esc_attr( $arr_search['order_word'][0] ); ?>" class="regular-text" maxlength="50" />
					<select name="search[order_word_term][0]" class="termselect">
						<option value="contain"<?php selected( $arr_search['order_word_term'][0], 'contain' ); ?>><?php esc_html_e( 'Contain', 'usces' ); ?></option>
						<option value="notcontain"<?php selected( $arr_search['order_word_term'][0], 'notcontain' ); ?>><?php esc_html_e( 'Not Contain', 'usces' ); ?></option>
						<option value="equal"<?php selected( $arr_search['order_word_term'][0], 'equal' ); ?>><?php esc_html_e( 'Equal', 'usces' ); ?></option>
						<option value="morethan"<?php selected( $arr_search['order_word_term'][0], 'morethan' ); ?>><?php esc_html_e( 'More than', 'usces' ); ?></option>
						<option value="lessthan"<?php selected( $arr_search['order_word_term'][0], 'lessthan' ); ?>><?php esc_html_e( 'Less than', 'usces' ); ?></option>
					</select>
					</span>
				</p>
				<p>
					<select name="search[order_term]" class="termselect">
						<option value="AND">AND</option>
						<option value="OR"<?php selected( $arr_search['order_term'], 'OR' ); ?>>OR</option>
					</select>
				</p>
				<p>
					<select name="search[order_column][1]" id="searchorderselect_1" class="searchselect">
						<option value=""> </option>
					<?php
					foreach ( (array) $arr_column as $key => $value ) :
						if ( 'ID' === $key ) {
							continue;
						}
						?>
						<option value="<?php echo esc_attr( $key ); ?>"<?php selected( $arr_search['order_column'][1], $key ); ?>><?php echo esc_html( $value ); ?></option>
					<?php endforeach; ?>
					</select>
					<span id="searchorderword_1">
					<input name="search[order_word][1]" type="text" value="<?php echo esc_attr( $arr_search['order_word'][1] ); ?>" class="regular-text" maxlength="50" />
					<select name="search[order_word_term][1]" class="termselect">
						<option value="contain"<?php selected( $arr_search['order_word_term'][1], 'contain' ); ?>><?php esc_html_e( 'Contain', 'usces' ); ?></option>
						<option value="notcontain"<?php selected( $arr_search['order_word_term'][1], 'notcontain' ); ?>><?php esc_html_e( 'Not Contain', 'usces' ); ?></option>
						<option value="equal"<?php selected( $arr_search['order_word_term'][1], 'equal' ); ?>><?php esc_html_e( 'Equal', 'usces' ); ?></option>
						<option value="morethan"<?php selected( $arr_search['order_word_term'][1], 'morethan' ); ?>><?php esc_html_e( 'More than', 'usces' ); ?></option>
						<option value="lessthan"<?php selected( $arr_search['order_word_term'][1], 'lessthan' ); ?>><?php esc_html_e( 'Less than', 'usces' ); ?></option>
					</select>
					</span>
				</p>
			</div>

			<div class="search_separate">AND</div>

			<div class="product_search_item search_item">
				<p class="search_item_label"><?php esc_html_e( 'From product information', 'usces' ); ?></p>
				<p>
					<select name="search[product_column][0]" id="searchproductselect_0" class="searchselect">
						<option value=""> </option>
						<option value="item_code"<?php selected( $arr_search['product_column'][0], 'item_code' ); ?>><?php esc_html_e( 'item code', 'usces' ); ?></option>
						<option value="item_name"<?php selected( $arr_search['product_column'][0], 'item_name' ); ?>><?php esc_html_e( 'item name', 'usces' ); ?></option>
						<option value="sku_code"<?php selected( $arr_search['product_column'][0], 'sku_code' ); ?>><?php esc_html_e( 'SKU code', 'usces' ); ?></option>
						<option value="sku_name"<?php selected( $arr_search['product_column'][0], 'sku_name' ); ?>><?php esc_html_e( 'SKU name', 'usces' ); ?></option>
						<option value="item_option"<?php selected( $arr_search['product_column'][0], 'item_option' ); ?>><?php esc_html_e( 'options for items', 'usces' ); ?></option>
					</select>
					<span id="searchproductword_0"><input name="search[product_word][0]" type="text" value="<?php echo esc_attr( $arr_search['product_word'][0] ); ?>" class="regular-text" maxlength="50" /></span>
				</p>
				<p>
					<select name="search[product_term]" class="termselect">
						<option value="AND">AND</option>
						<option value="OR"<?php selected( $arr_search['product_term'], 'OR' ); ?>>OR</option>
					</select>
				</p>
				<p>
					<select name="search[product_column][1]" id="searchproductselect_1" class="searchselect">
						<option value=""> </option>
						<option value="item_code"<?php selected( $arr_search['product_column'][1], 'item_code' ); ?>><?php esc_html_e( 'item code', 'usces' ); ?></option>
						<option value="item_name"<?php selected( $arr_search['product_column'][1], 'item_name' ); ?>><?php esc_html_e( 'item name', 'usces' ); ?></option>
						<option value="sku_code"<?php selected( $arr_search['product_column'][1], 'sku_code' ); ?>><?php esc_html_e( 'SKU code', 'usces' ); ?></option>
						<option value="sku_name"<?php selected( $arr_search['product_column'][1], 'sku_name' ); ?>><?php esc_html_e( 'SKU name', 'usces' ); ?></option>
						<option value="item_option"<?php selected( $arr_search['product_column'][1], 'item_option' ); ?>><?php esc_html_e( 'options for items', 'usces' ); ?></option>
					</select>
					<span id="searchproductword_1"><input name="search[product_word][1]" type="text" value="<?php echo esc_attr( $arr_search['product_word'][1] ); ?>" class="regular-text" maxlength="50" /></span>
				</p>
			</div>
			<div class="search_submit">
				<input name="searchIn" type="submit" class="button button-primary" value="<?php esc_attr_e( 'Search', 'usces' ); ?>" />
				<input name="searchOut" type="submit" class="button" value="<?php esc_attr_e( 'Cancellation', 'usces' ); ?>" />
			</div>
		</td>
	</tr>
	<tr>
		<td class="label"><?php esc_html_e( 'Action', 'usces' ); ?></td>
		<td id="dl_list_table">
			<div class="action_button">
				<input type="button" id="dl_continuemember_list" class="button" value="<?php esc_attr_e( 'Download Continue Member List', 'dlseller' ); ?>" />
				<?php do_action( 'dlseller_action_dl_list_table' ); ?>
			</div>
		</td>
	</tr>
	</table>
<?php
$class = ( has_action( 'dlseller_action_continue_memberlist_searchbox_bottom' ) ) ? ' class="searchbox_bottom"' : '';
?>
<div<?php echo esc_attr( $class ); ?>>
<?php do_action( 'dlseller_action_continue_memberlist_searchbox_bottom' ); ?>
</div>
</div><!-- searchBox -->
<?php do_action( 'dlseller_action_continue_memberlist_searchbox' ); ?>
</div><!-- tablesearch -->

<table id="mainDataTable" class="new-table order-new-table">
<?php
$list_header = '<th scope="col"><input name="allcheck" type="checkbox" value="" /></th>';
foreach ( (array) $arr_header as $key => $value ) {
	if ( 'ID' === $key ) {
		continue;
	}
	if ( ! isset( $list_option['view_column'][ $key ] ) || ! $list_option['view_column'][ $key ] ) {
		continue;
	}
	$list_header .= '<th scope="col">' . $value . '</th>';
}

$usces_serchproduct_column = array( 'item_code', 'item_name', 'sku_code', 'sku_name', 'item_option' );
if ( in_array( $arr_search['product_column'][0], $usces_serchproduct_column ) || in_array( $arr_search['product_column'][1], $usces_serchproduct_column ) ) {
	$list_header .= '<th scope="col">' . __( 'item code', 'usces' ) . '</th>';
	$list_header .= '<th scope="col">' . __( 'item name', 'usces' ) . '</th>';
	$list_header .= '<th scope="col">' . __( 'SKU code', 'usces' ) . '</th>';
	$list_header .= '<th scope="col">' . __( 'SKU name', 'usces' ) . '</th>';
	$list_header .= '<th scope="col">' . __( 'option name', 'usces' ) . '</th>';
	$list_header .= '<th scope="col">' . __( 'option value', 'usces' ) . '</th>';
}
?>
	<thead>
	<tr>
		<?php echo apply_filters( 'dlseller_filter_continue_member_list_header', $list_header, $arr_header ); ?>
	</tr>
	</thead>
<?php
foreach ( (array) $rows as $data ) :
	$list_detail = '<td align="center"><input name="listcheck[]" type="checkbox" value="' . $data['order_id'] . '" /></td>';

	foreach ( (array) $data as $key => $value ) {
		if ( isset( $list_option['view_column'][ $key ] ) && ! $list_option['view_column'][ $key ] ) {
			continue;
		}

		if ( WCUtils::is_blank( $value ) ) {
			$value = '&nbsp;';
		}

		$detail = '';
		switch ( $key ) {
			case 'ID':
				break;
			case 'order_id':
			case 'deco_id':
				$detail = '<td><a href="' . USCES_ADMIN_URL . '?page=usces_orderlist&order_action=edit&order_id=' . $data['order_id'] . '&wc_nonce=' . wp_create_nonce( 'order_list' ) . '">' . esc_html( $value ) . '</a></td>';
				break;
			case 'limitofcard':
				$limitofcard = $value . dlseller_upcard_url( $data['mem_id'], $data['order_id'], $data['limitofcard'], $data['payment_name'], 'return' );
				$limitofcard = apply_filters( 'dlseller_filter_continue_member_list_limitofcard', $limitofcard, $data['mem_id'], $data['order_id'], $data );
				$detail      = '<td class="center">' . $limitofcard . '</td>';
				break;
			case 'price':
				$detail = '<td class="price">' . usces_crform( $value, true, false, 'return' ) . '</td>';
				break;
			case 'acting':
				$acting_name = ( array_key_exists( $value, $payment_structure ) ) ? $payment_structure[ $value ] : '';
				$acting_name = apply_filters( 'dlseller_filter_continue_member_list_acting_name', $acting_name, $data['mem_id'], $data['order_id'], $data );
				$detail      = '<td>' . $acting_name . '</td>';
				break;
			case 'orderdate':
			case 'startdate':
			case 'contractedday':
			case 'chargedday':
				$detail = '<td class="center">' . $value . '</td>';
				break;
			case 'status':
				if ( 'continuation' === $value ) {
					$continue_status = '<td class="green center">' . __( 'continuation', 'dlseller' ) . '</td>';
				} else {
					$continue_status = '<td class="red center">' . __( 'cancellation', 'dlseller' ) . '</td>';
				}
				$detail = apply_filters( 'dlseller_filter_continue_member_list_continue_status', $continue_status, $data['mem_id'], $data['order_id'], $data );
				break;
			case 'condition':
				$condition = apply_filters( 'dlseller_filter_continue_member_list_condition', $value, $data['mem_id'], $data['order_id'], $data );
				$detail    = '<td class="center">' . $condition . '</td>';
				break;
			default:
				$detail = '<td>' . esc_html( $value ) . '</td>';
		}
		$list_detail .= apply_filters( 'dlseller_filter_continue_member_list_detail_value', $detail, $value, $key, $data['mem_id'], $data['order_id'] );
	}
	?>
	<tbody>
		<tr<?php echo apply_filters( 'dlseller_filter_continue_member_list_detail_trclass', '', $data ); ?>>
			<?php echo apply_filters( 'dlseller_filter_continue_member_list_detail', $list_detail, $data, $curent_url ); ?>
		</tr>
	</tbody>
<?php endforeach; ?>
</table>
<div class="usces_tablenav usces_tablenav_bottom">
	<?php echo wp_kses_post( $data_table_navigation ); ?>
</div>
</div><!-- datatable -->

<input name="con_id" type="hidden" id="con_id" value="" />
<input name="member_id" type="hidden" id="member_id" value="" />
<input name="order_id" type="hidden" id="order_id" value="" />
<input name="usces_referer" type="hidden" id="usces_referer" value="<?php echo esc_url( $curent_url ); ?>" />
<?php do_action( 'dlseller_action_continue_member_list_table_footer' ); ?>
<div id="dlContinueMemberListDialog" title="<?php esc_attr_e( 'Download Continue Member List', 'dlseller' ); ?>" class="download_dialog" style="display:none;">
	<p><?php esc_html_e( 'Select the item you want, please press the download.', 'usces' ); ?></p>
	<input type="button" class="button" id="dl_con" value="<?php esc_attr_e( 'Download', 'usces' ); ?>" />
	<fieldset><legend><?php esc_html_e( 'Continuation charging member information', 'dlseller' ); ?></legend>
		<label for="chk_con[order_id]"><input type="checkbox" class="check_con" id="chk_con[order_id]" value="order_id"<?php usces_checked( $chk_con, 'order_id' ); ?> /><?php esc_html_e( 'Order ID', 'dlseller' ); ?></label>
		<label for="chk_con[deco_id]"><input type="checkbox" class="check_con" id="chk_con[deco_id]" value="deco_id"<?php usces_checked( $chk_con, 'deco_id' ); ?> /><?php esc_html_e( 'order number', 'usces' ); ?></label>
		<label for="chk_con[mem_id]"><input type="checkbox" class="check_con" id="chk_con[mem_id]" value="mem_id"<?php usces_checked( $chk_con, 'mem_id' ); ?> /><?php esc_html_e( 'membership number', 'usces' ); ?></label>
		<label for="chk_con[email]"><input type="checkbox" class="check_con" id="chk_con[email]" value="email"<?php usces_checked( $chk_con, 'email' ); ?> /><?php esc_html_e( 'e-mail', 'usces' ); ?></label>
		<label for="chk_con[name]"><input type="checkbox" class="check_con" id="chk_con[name]" value="name"<?php usces_checked( $chk_con, 'name' ); ?> /><?php esc_html_e( 'name', 'usces' ); ?></label>
	<?php if ( 'JP' === $applyform ) : ?>
		<label for="chk_con[kana]"><input type="checkbox" class="check_con" id="chk_con[kana]" value="kana"<?php usces_checked( $chk_con, 'kana' ); ?> /><?php esc_html_e( 'furigana', 'usces' ); ?></label>
	<?php endif; ?>
		<label for="chk_con[limitofcard]"><input type="checkbox" class="check_con" id="chk_con[limitofcard]" value="limitofcard"<?php usces_checked( $chk_con, 'limitofcard' ); ?> /><?php esc_html_e( 'Limit of Card(Month/Year)', 'dlseller' ); ?></label>
		<label for="chk_con[price]"><input type="checkbox" class="check_con" id="chk_con[price]" value="price"<?php usces_checked( $chk_con, 'price' ); ?> /><?php esc_html_e( 'Total Amount', 'usces' ); ?></label>
		<label for="chk_con[acting]"><input type="checkbox" class="check_con" id="chk_con[acting]" value="acting"<?php usces_checked( $chk_con, 'acting' ); ?> /><?php esc_html_e( 'Settlement Supplier', 'dlseller' ); ?></label>
		<label for="chk_con[payment_name]"><input type="checkbox" class="check_con" id="chk_con[payment_name]" value="payment_name"<?php usces_checked( $chk_con, 'payment_name' ); ?> /><?php esc_html_e( 'payment method', 'usces' ); ?></label>
		<label for="chk_con[orderdate]"><input type="checkbox" class="check_con" id="chk_con[orderdate]" value="orderdate" <?php usces_checked( $chk_con, 'orderdate' ); ?> /><?php esc_html_e( 'Application Date', 'dlseller' ); ?></label>
		<label for="chk_con[startdate]"><input type="checkbox" class="check_con" id="chk_con[startdate]" value="startdate" <?php usces_checked( $chk_con, 'startdate' ); ?> /><?php esc_html_e( 'First Withdrawal Date', 'dlseller' ); ?></label>
		<label for="chk_con[contractedday]"><input type="checkbox" class="check_con" id="chk_con[contractedday]" value="contractedday"<?php usces_checked( $chk_con, 'contractedday' ); ?> /><?php esc_html_e( 'Renewal Date', 'dlseller' ); ?></label>
		<label for="chk_con[chargedday]"><input type="checkbox" class="check_con" id="chk_con[chargedday]" value="chargedday"<?php usces_checked( $chk_con, 'chargedday' ); ?> /><?php esc_html_e( 'Next Withdrawal Date', 'dlseller' ); ?></label>
		<label for="chk_con[status]"><input type="checkbox" class="check_con" id="chk_con[status]" value="status"<?php usces_checked( $chk_con, 'status' ); ?> /><?php esc_html_e( 'Status', 'dlseller' ); ?></label>
		<label for="chk_con[condition]"><input type="checkbox" class="check_con" id="chk_con[condition]" value="condition"<?php usces_checked( $chk_con, 'condition' ); ?> /><?php esc_html_e( 'Condition', 'dlseller' ); ?></label>
	</fieldset>
</div>
<?php do_action( 'dlseller_action_continue_member_list_footer' ); ?>
<?php wp_nonce_field( 'continue_member_list', 'wc_nonce' ); ?>
</form>
</div><!--usces_admin-->
</div><!--wrap-->

<div id="mailSendDialog" title="" style="display:none;">
	<div id="order-response"></div>
	<fieldset>
		<p><?php esc_html_e( "Check the mail and click 'send'", 'usces' ); ?></p>
		<label><?php esc_html_e( 'e-mail adress', 'usces' ); ?></label><input type="text" name="sendmailaddress" id="sendmailaddress" class="text" /><br />
		<label><?php esc_html_e( 'Client name', 'usces' ); ?></label><input type="text" name="sendmailname" id="sendmailname" class="text" /><br />
		<label><?php esc_html_e( 'subject', 'usces' ); ?></label><input type="text" name="sendmailsubject" id="sendmailsubject" class="text" />
		<?php if ( function_exists( 'usces_is_html_mail' ) && usces_is_html_mail() ) : ?>
			<div id="wrap_load_rich_editor" style="margin-top: 10px">
				<div id="loading_iframe"></div>
				<iframe style="display:none" width="660" height="800" id="iframeLoadEditor" src="" ></iframe>
			</div>
		<?php else : ?>
			<textarea name="sendmailmessage" id="sendmailmessage"></textarea>
		<?php endif; ?>
		<input name="mailChecked" id="mailChecked" type="hidden" />
	</fieldset>
</div>
<div id="dialog_parent" style="position:fixed"></div>
<?php if ( function_exists( 'usces_is_html_mail' ) && usces_is_html_mail() ) : ?>
	<div id="previewEmailDialog" title="">
		<iframe src="" width="660" height="3000" frameborder="0" class="content_email_preview" id='iframePreviewEmail'></iframe>
	</div>
<?php endif; ?>
<div id="mailSendAlert" title="">
	<div id="order-response"></div>
	<fieldset>
	</fieldset>
</div>
<script type="text/javascript">
jQuery(function($) {

	$("#mailSendDialog").dialog({
		bgiframe: true,
		autoOpen: false,
		height: 650,
		width: 700,
		resizable: true,
		modal: true,
		buttons: [
			<?php if ( function_exists( 'usces_is_html_mail' ) && usces_is_html_mail() ) : ?>
			{
				text: "<?php esc_html_e( 'Preview', 'usces' ); ?>",
				"class": 'button',
				"id": 'usces_email_bnt_preview',
				click: function() {
					uscesMail.previewMail();
				}
			},
			<?php endif; ?>
			{
				text: "<?php esc_html_e( 'send', 'usces' ); ?>",
				"class": 'button',
				"id": 'usces_email_bnt_send',
				click: function() {
					uscesMail.sendMail();
				}
			},
			{
				text: "<?php esc_html_e( 'close', 'usces' ); ?>",
				click: function() {
					$(this).dialog('close');
				}
			}
		],
		appendTo:"#dialog_parent",
		close: function() {
		<?php if ( function_exists( 'usces_is_html_mail' ) && usces_is_html_mail() ) : ?>
			$('#iframeLoadEditor').attr('src', '');
		<?php else : ?>
			$("#sendmailmessage").html( "" );
		<?php endif; ?>
			$('#sendmailaddress').val('');
			$('#sendmailname').val('');
		}
	});
	<?php if ( function_exists( 'usces_is_html_mail' ) && usces_is_html_mail() ) : ?>
	$("#previewEmailDialog").dialog({
		bgiframe: true,
		autoOpen: false,
		height: 650,
		width: 700,
		resizable: true,
		modal: true,
		buttons: [
			{
				text: "<?php esc_html_e( 'close', 'usces' ); ?>",
				click: function() {
					$(this).dialog('close');
				}
			}
		],
		appendTo:"#dialog_parent",
		close: function() {
			$('#previewEmailDialog').dialog('option', 'title', "");
			var dstFrame = document.getElementById('iframePreviewEmail');
			var dstDoc = dstFrame.contentDocument || dstFrame.contentWindow.document;
			dstDoc.write("");
			dstDoc.close();
		}
	});
	<?php endif; ?>

	$("#mailSendAlert").dialog({
		bgiframe: true,
		autoOpen: false,
		height: 200,
		width: 200,
		resizable: false,
		modal: false
	});

	$("#sendmail").click(function() {
		uscesMail.sendMail();
	});

	uscesMail = {
		sendMail: function() {
			if( $("#sendmailaddress").val() == "" ) {
				return;
			}

			var address = encodeURIComponent($("#sendmailaddress").val());
		<?php if ( function_exists( 'usces_is_html_mail' ) && usces_is_html_mail() ) : ?>
			var iframe = document.getElementById("iframeLoadEditor");
			var message = encodeURIComponent(iframe.contentWindow.getContentEditor());
		<?php else : ?>
			var message = encodeURIComponent($("#sendmailmessage").val());
		<?php endif; ?>
			var name = encodeURIComponent($("#sendmailname").val());
			var subject = encodeURIComponent($("#sendmailsubject").val());
			var order_id = $("#order_id").val();
			var member_id = $("#member_id").val();
			// console.log('OK1');
			// add loading gif
			var img_loading = '<div id="wrap_icon_loading"><img src="' + uscesL10n.USCES_PLUGIN_URL + 'images/loading.gif" /></div>';
			$("#usces_email_bnt_send").prop("disabled", true);
			$(img_loading).insertBefore("#usces_email_bnt_send");

			var s = uscesMail.settings;
			s.data = "action=dlseller_send_mail_ajax&mailaddress=" + address + "&message=" + message + "&name=" + name + "&subject=" + subject + "&oid=" + order_id + "&mid=" + member_id;
			$.ajax( s ).done(function( data, dataType ) {
				if( data == 'success' ) {
					// console.log('OK2');
					$("#wrap_icon_loading").remove();
					$("#usces_email_bnt_send").removeAttr('disabled');

					$('#mailSendAlert').dialog('option', 'buttons', {
						'OK': function() {
							$(this).dialog('close');
							$('#mailSendDialog').dialog('close');
						}
					});
					$('#mailSendAlert').dialog('option', 'title', 'SUCCESS');
					$('#mailSendAlert fieldset').html('<p><?php esc_html_e( 'E-mail has been sent.', 'usces' ); ?></p>');
					$('#mailSendAlert').dialog('open');

				} else if( data == 'error' ) {
					// console.log('NG');
					$("#wrap_icon_loading").remove();
					$("#usces_email_bnt_send").removeAttr('disabled');

					$('#mailSendAlert').dialog('option', 'buttons', {
						'OK': function() {
							$(this).dialog('close');
						}
					});
					$('#mailSendAlert fieldset').dialog('option', 'title', 'ERROR');
					$('#mailSendAlert fieldset').html('<p><?php esc_html_e( 'Failure in sending e-mails.', 'usces' ); ?></p>');
					$('#mailSendAlert').dialog('open');
				}
			}).fail(function( data, dataType ) {
				$("#wrap_icon_loading").remove();
				$("#usces_email_bnt_send").removeAttr('disabled');

				$('#mailSendAlert').dialog('option', 'buttons', {
					'OK': function() {
						$(this).dialog('close');
					}
				});
				$('#mailSendAlert fieldset').dialog('option', 'title', 'ERROR');
				$('#mailSendAlert fieldset').html('<p><?php esc_html_e( 'Failure in sending e-mails.', 'usces' ); ?></p>');
				$('#mailSendAlert').dialog('open');
			});
			return false;
		},
		previewMail: function() {
			var iframe = document.getElementById("iframeLoadEditor");
			var content = iframe.contentWindow.getContentEditor();
			var img_loading = '<div id="wrap_icon_loading"><img src="' + uscesL10n.USCES_PLUGIN_URL + 'images/loading.gif" /></div>';
			$(img_loading).insertBefore("#usces_email_bnt_preview");
			$("#usces_email_bnt_preview").prop("disabled", true);

			var setting = Object.assign({}, uscesMail.settings);
			setting.data = {
				'action': 'dlseller_filter_content_wp_editor_preview',
				'mode': 'preview_email_order_detail',
				'wc_nonce': '<?php echo wp_create_nonce( 'dl_preview_editor_nonce' ); ?>',
				'content': content,
			};
			$.ajax(setting).done(function(res) {
				$("#wrap_icon_loading").remove();
				if (res && res.status) {
					var dstFrame = document.getElementById('iframePreviewEmail');
					var dstDoc = dstFrame.contentDocument || dstFrame.contentWindow.document;
					dstDoc.write(res.content);
					dstDoc.close();

					$('#previewEmailDialog').dialog('option', 'title', "<?php esc_html_e( 'Preview email content', 'usces' ); ?>");
					$('#previewEmailDialog').dialog('open');
				} else {
					uscesMail.contentPreviewErrorShow( '<?php esc_html_e( 'Failure preview email.', 'usces' ); ?>' );
				}
				$("#usces_email_bnt_preview").removeAttr('disabled');
			}).fail(function(msg) {
				$("#wrap_icon_loading").remove();
				$("#usces_email_bnt_preview").removeAttr('disabled');
				uscesMail.contentPreviewErrorShow( msg );
			});
		},
		contentPreviewErrorShow: function( msg ) {
			var dstFrame = document.getElementById('iframePreviewEmail');
			var dstDoc = dstFrame.contentDocument || dstFrame.contentWindow.document;
			dstDoc.write( msg );
			dstDoc.close();
			$('#previewEmailDialog').dialog('option', 'title', 'ERROR');
			$('#previewEmailDialog').dialog('open');
		},

		getMailData : function( member_id, order_id ) {
			$("#order_id").val(order_id);
			$("#member_id").val(member_id);
			<?php if ( function_exists( 'usces_is_html_mail' ) && usces_is_html_mail() ) : ?>
				uscesMail.getMailMessageRichEditor( member_id, order_id );
			<?php else : ?>
				var p = uscesMail.settings;
				p.url = uscesL10n.requestFile;
				p.data = "action=dlseller_make_mail_ajax&order_id=" + order_id + "&member_id=" + member_id;
				$.ajax( p ).done(function(data, dataType){
					if( 0 == data ) {
						alert('<?php esc_html_e( 'Data Error', 'dlseller' ); ?>');
					} else {
						$("#sendmailaddress").val( data.mailAddress );
						$("#sendmailname").val( data.name );
						$("#sendmailsubject").val( data.subject );
						$("#sendmailmessage").val( data.message );
						$('#mailSendDialog').dialog('option', 'title', '<?php esc_html_e( 'Update Request Email', 'dlseller' ); ?>');
						$('#mailSendDialog').dialog('open');
					}
				}).fail(function( data, dataType ) {
					alert('<?php esc_html_e( 'Send Error', 'dlseller' ); ?>');
				});
				return false;
			<?php endif; ?>
		},
		getMailMessageRichEditor : function( member_id, order_id ) {
			$("#loading_iframe").text(uscesL10n.now_loading);
			$("#usces_email_bnt_preview").prop("disabled", true);
			$("#usces_email_bnt_send").prop("disabled", true);
			$("#loading_iframe").show();
			// show dialog
			$('#mailSendDialog').dialog('option', 'title', '<?php esc_html_e( 'Update Request Email', 'dlseller' ); ?>');
			$('#mailSendDialog').dialog('open');
			var usceAdminUrl = '<?php echo esc_url_raw( USCES_ADMIN_URL ); ?>';
			usceAdminUrl += '?page=usces_continue&continue_action=load_rich_editor&order_id=' + order_id + '&member_id=' + member_id + '&noheader=true';
			$('#iframeLoadEditor').attr('src', usceAdminUrl);
			$('#iframeLoadEditor').load(function(){
				$("#loading_iframe").hide();
				$(this).show();
				$("#usces_email_bnt_preview").removeAttr('disabled');
				$("#usces_email_bnt_send").removeAttr('disabled');
			});
		},

		settings: {
			url: uscesL10n.requestFile,
			type: 'POST',
			cache: false,
		}
	};

	$("input[name='allcheck']").click(function () {
		if( $(this).prop("checked") ) {
			$("input[name*='listcheck']").prop( "checked", true );
		} else {
			$("input[name*='listcheck']").prop( "checked", false );
		}
	});

	operation = {
		change_order_search_field_0: function() {
			var html = '';
			var column = $("#searchorderselect_0").val();

			if( column == 'acting' ) {
				html = '<select name="search[order_word][0]" class="searchselect">';
			<?php foreach ( (array) $payment_structure as $idx => $payment ) : ?>
				html += '<option value="<?php echo esc_attr( $idx ); ?>"<?php str_replace( "'", '"', selected( ( isset( $arr_search['order_word'][0] ) && $idx === $arr_search['order_word'][0] ), true, false ) ); ?>><?php echo esc_html( $payment ); ?></option>';
			<?php endforeach; ?>
				html += '</select>';
			} else if( column == 'status' ) {
				html = '<select name="search[order_word][0]" class="searchselect">';
				html += '<option value="continuation"<?php str_replace( "'", '"', selected( ( isset( $arr_search['order_word'][0] ) && 'continuation' === $arr_search['order_word'][0] ), true, false ) ); ?>><?php esc_html_e( 'continuation', 'dlseller' ); ?></option>';
				html += '<option value="cancellation"<?php str_replace( "'", '"', selected( ( isset( $arr_search['order_word'][0] ) && 'cancellation' === $arr_search['order_word'][0] ), true, false ) ); ?>><?php esc_html_e( 'cancellation', 'dlseller' ); ?></option>';
				html += '</select>';
			} else {
				html = '<input name="search[order_word][0]" type="text" value="<?php echo esc_attr( $arr_search['order_word'][0] ); ?>" class="regular-text" maxlength="50" />';
				html += '<select name="search[order_word_term][0]" class="termselect">';
				html += '<option value="contain"<?php str_replace( "'", '"', selected( $arr_search['order_word_term'][0], 'contain', false ) ); ?>><?php esc_html_e( 'Contain', 'usces' ); ?></option>';
				html += '<option value="notcontain"<?php str_replace( "'", '"', selected( $arr_search['order_word_term'][0], 'notcontain', false ) ); ?>><?php esc_html_e( 'Not Contain', 'usces' ); ?></option>';
				html += '<option value="equal"<?php str_replace( "'", '"', selected( $arr_search['order_word_term'][0], 'equal', false ) ); ?>><?php esc_html_e( 'Equal', 'usces' ); ?></option>';
				html += '<option value="morethan"<?php str_replace( "'", '"', selected( $arr_search['order_word_term'][0], 'morethan', false ) ); ?>><?php esc_html_e( 'More than', 'usces' ); ?></option>';
				html += '<option value="lessthan"<?php str_replace( "'", '"', selected( $arr_search['order_word_term'][0], 'lessthan', false ) ); ?>><?php esc_html_e( 'Less than', 'usces' ); ?></option>';
				html += '</select>';
			}
			$("#searchorderword_0").html( html );
		},

		change_order_search_field_1: function() {
			var html = '';
			var column = $("#searchorderselect_1").val();

			if( column == 'acting' ) {
				html = '<select name="search[order_word][1]" class="searchselect">';
		<?php foreach ( (array) $payment_structure as $idx => $payment ) : ?>
				html += '<option value="<?php echo esc_attr( $idx ); ?>"<?php str_replace( "'", '"', selected( ( isset( $arr_search['order_word'][1] ) && $idx === $arr_search['order_word'][1] ), true, false ) ); ?>><?php echo esc_html( $payment ); ?></option>';
		<?php endforeach; ?>
				html += '</select>';
			} else if( column == 'status' ) {
				html = '<select name="search[order_word][1]" class="searchselect">';
				html += '<option value="continuation"<?php str_replace( "'", '"', selected( ( isset( $arr_search['order_word'][1] ) && 'continuation' === $arr_search['order_word'][1] ), true, false ) ); ?>><?php esc_html_e( 'continuation', 'dlseller' ); ?></option>';
				html += '<option value="cancellation"<?php str_replace( "'", '"', selected( ( isset( $arr_search['order_word'][1] ) && 'cancellation' === $arr_search['order_word'][1] ), true, false ) ); ?>><?php esc_html_e( 'cancellation', 'dlseller' ); ?></option>';
				html += '</select>';
			} else {
				html = '<input name="search[order_word][1]" type="text" value="<?php echo esc_attr( $arr_search['order_word'][1] ); ?>" class="regular-text" maxlength="50" />';
				html += '<select name="search[order_word_term][1]" class="termselect">';
				html += '<option value="contain"<?php str_replace( "'", '"', selected( $arr_search['order_word_term'][1], 'contain', false ) ); ?>><?php esc_html_e( 'Contain', 'usces' ); ?></option>';
				html += '<option value="notcontain"<?php str_replace( "'", '"', selected( $arr_search['order_word_term'][1], 'notcontain', false ) ); ?>><?php esc_html_e( 'Not Contain', 'usces' ); ?></option>';
				html += '<option value="equal"<?php str_replace( "'", '"', selected( $arr_search['order_word_term'][1], 'equal', false ) ); ?>><?php esc_html_e( 'Equal', 'usces' ); ?></option>';
				html += '<option value="morethan"<?php str_replace( "'", '"', selected( $arr_search['order_word_term'][1], 'morethan', false ) ); ?>><?php esc_html_e( 'More than', 'usces' ); ?></option>';
				html += '<option value="lessthan"<?php str_replace( "'", '"', selected( $arr_search['order_word_term'][1], 'lessthan', false ) ); ?>><?php esc_html_e( 'Less than', 'usces' ); ?></option>';
				html += '</select>';
			}
			$("#searchorderword_1").html( html );
		},

		change_product_search_field_0: function() {
			var html = '';
			var column = $("#searchproductselect_0").val();

			if( column == 'item_option' ) {
				html = '<?php esc_html_e( 'option key', 'usces' ); ?>:<input name="search[product_word][0]" type="text" value="<?php echo esc_attr( $arr_search['product_word'][0] ); ?>" class="text" maxlength="50" /> <?php esc_html_e( 'option value', 'usces' ); ?>:<input name="search[option_word][0]" type="text" value="<?php echo esc_attr( $arr_search['option_word'][0] ); ?>" class="text" maxlength="50" />';
			} else {
				html = '<input name="search[product_word][0]" type="text" value="<?php echo esc_attr( $arr_search['product_word'][0] ); ?>" class="regular-text" maxlength="50" />';
				html += '<select name="search[product_word_term][0]" class="termselect">';
				html += '<option value="contain"<?php str_replace( "'", '"', selected( $arr_search['product_word_term'][0], 'contain', false ) ); ?>><?php esc_html_e( 'Contain', 'usces' ); ?></option>';
				html += '<option value="notcontain"<?php str_replace( "'", '"', selected( $arr_search['product_word_term'][0], 'notcontain', false ) ); ?>><?php esc_html_e( 'Not Contain', 'usces' ); ?></option>';
				html += '<option value="equal"<?php str_replace( "'", '"', selected( $arr_search['product_word_term'][0], 'equal', false ) ); ?>><?php esc_html_e( 'Equal', 'usces' ); ?></option>';
				html += '<option value="morethan"<?php str_replace( "'", '"', selected( $arr_search['product_word_term'][0], 'morethan', false ) ); ?>><?php esc_html_e( 'More than', 'usces' ); ?></option>';
				html += '<option value="lessthan"<?php str_replace( "'", '"', selected( $arr_search['product_word_term'][0], 'lessthan', false ) ); ?>><?php esc_html_e( 'Less than', 'usces' ); ?></option>';
				html += '</select>';
			}
			$("#searchproductword_0").html( html );
		},

		change_product_search_field_1: function() {
			var html = '';
			var column = $("#searchproductselect_1").val();

			if( column == 'item_option' ) {
				html = '<?php esc_html_e( 'option key', 'usces' ); ?>:<input name="search[product_word][1]" type="text" value="<?php echo esc_attr( $arr_search['product_word'][1] ); ?>" class="text" maxlength="50" /> <?php esc_html_e( 'option value', 'usces' ); ?>:<input name="search[option_word][1]" type="text" value="<?php echo esc_attr( $arr_search['option_word'][1] ); ?>" class="text" maxlength="50" />';
			} else {
				html = '<input name="search[product_word][1]" type="text" value="<?php echo esc_attr( $arr_search['product_word'][1] ); ?>" class="regular-text" maxlength="50" />';
				html += '<select name="search[product_word_term][1]" class="termselect">';
				html += '<option value="contain"<?php str_replace( "'", '"', selected( $arr_search['product_word_term'][1], 'contain', false ) ); ?>><?php esc_html_e( 'Contain', 'usces' ); ?></option>';
				html += '<option value="notcontain"<?php str_replace( "'", '"', selected( $arr_search['product_word_term'][1], 'notcontain', false ) ); ?>><?php esc_html_e( 'Not Contain', 'usces' ); ?></option>';
				html += '<option value="equal"<?php str_replace( "'", '"', selected( $arr_search['product_word_term'][1], 'equal', false ) ); ?>><?php esc_html_e( 'Equal', 'usces' ); ?></option>';
				html += '<option value="morethan"<?php str_replace( "'", '"', selected( $arr_search['product_word_term'][1], 'morethan', false ) ); ?>><?php esc_html_e( 'More than', 'usces' ); ?></option>';
				html += '<option value="lessthan"<?php str_replace( "'", '"', selected( $arr_search['product_word_term'][1], 'lessthan', false ) ); ?>><?php esc_html_e( 'Less than', 'usces' ); ?></option>';
				html += '</select>';
			}
			$("#searchproductword_1").html( html );
		}
	};

	$("#searchorderselect_0").change(function () {
		operation.change_order_search_field_0();
	});
	$("#searchorderselect_1").change(function () {
		operation.change_order_search_field_1();
	});
	$("#searchproductselect_0").change(function () {
		operation.change_product_search_field_0();
	});
	$("#searchproductselect_1").change(function () {
		operation.change_product_search_field_1();
	});
	operation.change_order_search_field_0();
	operation.change_order_search_field_1();
	operation.change_product_search_field_0();
	operation.change_product_search_field_1();

	$('table#mainDataTable tbody input[type=checkbox]').change(
		function() {
			$('input').closest('tbody').removeClass('select');
			$(':checked').closest('tbody').addClass('select');
		}
	).trigger('change');

	$("#searchVisiLink").click(function() {
		if( $("#searchBox").css("display") != "block" ) {
			$("#searchBox").slideDown(300);
			$("#searchVisiLink").html('<?php esc_html_e( 'Hide the Operation field', 'usces' ); ?><span class="dashicons dashicons-arrow-up"></span>');
			$.cookie("orderSearchBox", 1, { path: "<?php echo esc_url( $usces_admin_path ); ?>", domain: "<?php echo esc_attr( $_SERVER['SERVER_NAME'] ); ?>"}) == true;
		} else {
			$("#searchBox").slideUp(300);
			$("#searchVisiLink").html('<?php esc_html_e( 'Show the Operation field', 'usces' ); ?><span class="dashicons dashicons-arrow-down"></span>');
			$.cookie("orderSearchBox", 0, { path: "<?php echo esc_url( $usces_admin_path ); ?>", domain: "<?php echo esc_attr( $_SERVER['SERVER_NAME'] ); ?>"}) == true;
		}
	});

	if( $.cookie("orderSearchBox") == true ) {
		$("#searchVisiLink").html('<?php esc_html_e( 'Hide the Operation field', 'usces' ); ?><span class="dashicons dashicons-arrow-up"></span>');
		$("#searchBox").show();
	} else if( $.cookie("orderSearchBox") == false ) {
		$("#searchVisiLink").html('<?php esc_html_e( 'Show the Operation field', 'usces' ); ?><span class="dashicons dashicons-arrow-down"></span>');
		$("#searchBox").hide();
	}

	(function setCookie() {
		<?php
		$data_cookie                       = array();
		$data_cookie['placeholder_escape'] = $con_list->placeholder_escape;
		$data_cookie['startRow']           = $con_list->startRow;        /* 表示開始行番号 */
		$data_cookie['sortColumn']         = $con_list->sortColumn;      /* 現在ソート中のフィールド */
		$data_cookie['totalRow']           = $con_list->totalRow;        /* 全行数 */
		$data_cookie['selectedRow']        = $con_list->selectedRow;     /* 絞り込まれた行数 */
		$data_cookie['currentPage']        = $con_list->currentPage;     /* 現在のページNo */
		$data_cookie['previousPage']       = $con_list->previousPage;    /* 前のページNo */
		$data_cookie['nextPage']           = $con_list->nextPage;        /* 次のページNo */
		$data_cookie['lastPage']           = $con_list->lastPage;        /* 最終ページNo */
		$data_cookie['userHeaderNames']    = $con_list->userHeaderNames; /* 全てのフィールド */
		$data_cookie['sortSwitchs']        = $con_list->sortSwitchs;     /* 各フィールド毎の昇順降順スイッチ */
		$data_cookie['searchWhere']        = $con_list->searchWhere;
		$data_cookie['searchHaving']       = $con_list->searchHaving;
		$data_cookie['arr_search']         = $con_list->arr_search;
		if ( 'on' === $con_list->pageLimit ) {
			$data_cookie['currentPageIds'] = $con_list->currentPageIds;
		}
		?>
		$.cookie('<?php echo "{$con_list->table}"?>', '<?php echo str_replace( "'", "\'", json_encode( $data_cookie ) ); ?>', { path: "<?php echo esc_url( $usces_admin_path ); ?>", domain: "<?php echo esc_attr( $_SERVER['SERVER_NAME'] ); ?>"});
	})();

	$("#dlContinueMemberListDialog").dialog({
		bgiframe: true,
		autoOpen: false,
		height: 400,
		width: 700,
		resizable: true,
		modal: true,
		buttons: {
			'<?php esc_html_e( 'close', 'usces' ); ?>': function() {
				$(this).dialog('close');
			}
		},
		close: function() {
		}
	});

	$('#dl_con').click(function() {
		var args = "&ftype=csv&returnList=1";
		$(".check_con").each(function(i) {
			if($(this).prop('checked')) {
				args += '&check['+$(this).val()+']=on';
			}
		});
		location.href = "<?php echo esc_url( USCES_ADMIN_URL ); ?>?page=usces_continue&continue_action=dlcontinuememberlist&noheader=true"+args;
	});

	$('#dl_continuemember_list').click(function() {
		$('#dlContinueMemberListDialog').dialog('open');
	});
<?php do_action( 'dlseller_action_continue_member_list_page_js' ); ?>
});
</script>
<?php do_action( 'dlseller_action_continue_member_list_page_footer' ); ?>
