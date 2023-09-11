<?php
/**
 * DL Seller creditcard update action.
 *
 * @package WCEX DL Seller
 */

if ( ! $this->is_member_logged_in() ) {
	die();
}

$up_mode     = absint( wp_unslash( $_REQUEST['dlseller_up_mode'] ) ); /* 1:date, 2:all */
$order_id    = absint( wp_unslash( $_REQUEST['dlseller_order_id'] ) );
$order_data  = $this->get_order_data( $order_id );
$payments    = usces_get_payments_by_name( $order_data['payment_name'] );
$acting_flag = ( 'acting' === $payments['settlement'] ) ? $payments['module'] : $payments['settlement'];
$member      = $this->get_member();

$member_id = $member['ID'];
$now       = date_i18n( 'Ym', current_time( 'timestamp', 0 ) );
$infos     = $this->get_member_info( $member_id );

if ( ! isset( $infos['limitofcard'] ) ) {
	$limits = array();
	$regd   = '';
} else {
	$limits = explode( '/', $infos['limitofcard'] );
	$regd   = date_i18n( 'Ym', strtotime( substr( current_time( 'mysql', 0 ), 0, 2 ) . $limits[1] . '-' . $limits[0] . '-01' ) );
}

$html = '<div id="memberpages">
<div class="whitebox">
<div class="header_explanation">';

if ( $member_id !== $order_data['mem_id'] ) {
	$html .= __( 'Order information is different. Please contact the manager.', 'dlseller' );
} elseif ( $regd > $now && 1 === $up_mode ) {
	$html .= __( 'Update processing of card information has been completed. Thank you.', 'dlseller' );
} elseif ( 2 === $up_mode ) {

	$rand = '0000000' . sprintf( '%010d', mt_rand( 1, 9999999999 ) );
	$this->save_order_acting_data( $rand );

	$html .= __( 'Since the transition to the page of the settlement company by clicking the "Update", please fill out the information for the new card.<br />In addition, this process is intended to update the card information such as credit card expiration date, it is not in your contract renewal of service.<br />To check the current contract, please refer to the member page.', 'dlseller' );

	switch ( $acting_flag ) {

		case 'acting_remise_card':
			$acting_opts = $this->options['acting_settings']['remise'];
			$ac_memberid = $this->get_member_meta_value( 'remise_memid', $member['ID'] );
			$limitofcard = explode( '/', $this->get_member_meta_value( 'limitofcard', $member['ID'] ) );
			$expire      = substr( current_time( 'mysql', 0 ), 0, 2 ) . $limitofcard[1] . $limitofcard[0];
			$now         = date_i18n( 'Ym', current_time( 'timestamp', 0 ) );
			$job         = ( $expire >= $now ) ? 'CHECK' : 'AUTH';
			$send_url    = ( 'public' === $acting_opts['card_pc_ope'] ) ? $acting_opts['send_url_pc'] : $acting_opts['send_url_pc_test'];

			$html .= '<form name="purchase_form" action="' . $send_url . '" method="post" onKeyDown="if (event.keyCode == 13) {return false;}" accept-charset="Shift_JIS">';
			$html .= '<input type="hidden" name="SHOPCO" value="' . esc_attr( $acting_opts['SHOPCO'] ) . '" />';
			$html .= '<input type="hidden" name="HOSTID" value="' . esc_attr( $acting_opts['HOSTID'] ) . '" />';
			$html .= '<input type="hidden" name="REMARKS3" value="' . $acting_opts['REMARKS3'] . '" />';
			$html .= '<input type="hidden" name="S_TORIHIKI_NO" value="' . $rand . '" />';
			$html .= '<input type="hidden" name="JOB" value="' . $job . '" />';
			$html .= '<input type="hidden" name="MAIL" value="' . esc_attr( $member['mailaddress1'] ) . '" />';
			$html .= '<input type="hidden" name="ITEM" value="0000990" />';
			$html .= '<input type="hidden" name="RETURL" value="' . USCES_CART_URL . $this->delim . 'acting=remise_card&acting_return=1&dlseller_update=1" />';
			$html .= '<input type="hidden" name="NG_RETURL" value="' . USCES_CART_URL . $this->delim . 'acting=remise_card&acting_return=0&dlseller_update=0" />';
			$html .= '<input type="hidden" name="EXITURL" value="' . USCES_CART_URL . $this->delim . 'dlseller_card_update=login&dlseller_order_id=' . $order_id . '" />';
			$html .= '<input type="hidden" name="TOTAL" value="' . usces_crform( $order_data['end_price'], false, false, 'return', false ) . '" />';
			$html .= '<input type="hidden" name="AMOUNT" value="' . usces_crform( $order_data['end_price'], false, false, 'return', false ) . '" />';
			$html .= '<input type="hidden" name="div" value="0">';
			$html .= '<input type="hidden" name="METHOD" value="10">';
			$html .= '<input type="hidden" name="OPT" value="dlseller_card_update">';
			$html .= '<input type="hidden" name="AUTOCHARGE" value="1">';
			$html .= '<input type="hidden" name="AC_MEMBERID" value="' . $ac_memberid . '" />';
			$html .= '<input type="hidden" name="AC_S_KAIIN_NO" value="' . $member['ID'] . '">';
			$html .= '<input type="hidden" name="AC_NAME" value="' . esc_attr( $member['name1'] . $member['name2'] ) . '">';
			$html .= '<input type="hidden" name="AC_KANA" value="' . esc_attr( $member['name3'] . $member['name4'] ) . '">';
			$html .= '<input type="hidden" name="AC_TEL" value="' . esc_attr( str_replace( '-', '', mb_convert_kana( $member['tel'], 'a', 'UTF-8' ) ) ) . '">';
			$html .= '<input type="hidden" name="AC_AMOUNT" value="' . usces_crform( $order_data['end_price'], false, false, 'return', false ) . '">';
			$html .= '<input type="hidden" name="AC_TOTAL" value="' . usces_crform( $order_data['end_price'], false, false, 'return', false ) . '">';
			// $html .= '<input type="hidden" name="AC_NEXT_DATE" value="' . date( 'Ymd', mktime( 0, 0, 0, substr( $nextdate, 5, 2 ) + 1, 1, substr( $nextdate, 0, 4 ) ) ) . '">';
			// $html .= '<input type="hidden" name="AC_INTERVAL" value="1M">';

			$html .= '<input type="hidden" name="dummy" value="&#65533;" />';
			$html .= '<div class="send"><input name="purchase" type="submit" class="checkout_button" value="' . __( 'Update', 'dlseller' ) . '" onClick="document.charset=\'Shift_JIS\';" /></div>';
			$html .= '</form>';
			break;

		default:
			$update_settlement_url = add_query_arg(
				array(
					'usces_page' => 'member_update_settlement',
					're-enter'   => 1,
				),
				USCES_MEMBER_URL
			);
			$html                 .= '
			<div class="gotoedit">
			<a href="' . $update_settlement_url . '">' . __( 'Change the credit card is here >>', 'usces' ) . '</a>
			</div>';
	}
} else {
	$html .= __( 'It is illegal request.', 'dlseller' );
}
$html .= '</div></div></div>';
