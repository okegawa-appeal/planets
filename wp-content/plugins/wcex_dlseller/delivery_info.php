<?php
/**
 * DL Seller delivery info page.
 *
 * @package WCEX DL Seller
 */

global $usces_entries, $usces_carts;
usces_get_entries();
usces_get_carts();
$dlseller_options  = get_option( 'dlseller' );
$usces_secure_link = get_option( 'usces_secure_link' );
$html              = '';

// $html .= usces_delivery_info_script( 'return' );
if ( $this->use_js ) {
	require USCES_PLUGIN_DIR . '/includes/delivery_info_script.php';
}

$html .= '<div id="delivery-info">
	<div class="usccart_navi">
	<ol class="ucart">
	<li class="ucart usccart">' . __( '1.Cart', 'usces' ) . '</li>
	<li class="ucart usccustomer">' . __( '2.Customer Info', 'usces' ) . '</li>
	<li class="ucart uscdelivery usccart_delivery">' . __( '3.Deli. & Pay.', 'usces' ) . '</li>
	<li class="ucart uscconfirm">' . __( '4.Confirm', 'usces' ) . '</li>
	</ol>
	</div>';

$html  .= '<div class="header_explanation">';
$header = '';
$html  .= apply_filters( 'usces_filter_delivery_page_header', $header );
$html  .= '</div>';

$html .= '<div class="error_message">' . $this->error_message . '</div>';

$html .= '<form action="' . USCES_CART_URL . '" method="post">';

if ( dlseller_have_shipped() ) {
	$html .= '<table class="customer_form">
		<tr>
		<th rowspan="2" scope="row">' . __( 'shipping address', 'usces' ) . '</th>
		<td><input name="delivery[delivery_flag]" type="radio" id="delivery_flag1" onclick="document.getElementById(\'delivery_table\').style.display = \'none\';" value="0"';
	if ( 0 === (int) $usces_entries['delivery']['delivery_flag'] ) {
		$html .= ' checked';
	}
	$html .= ' onKeyDown="if (event.keyCode == 13) {return false;}" /> <label for="delivery_flag1">' . __( 'same as customer information', 'usces' ) . '</label></td>
		</tr>
		<tr>
		<td><input name="delivery[delivery_flag]" id="delivery_flag2" onclick="document.getElementById(\'delivery_table\').style.display = \'block\'" type="radio" value="1"';
	if ( 1 === (int) $usces_entries['delivery']['delivery_flag'] ) {
		$html .= ' checked';
	}
	$html .= ' onKeyDown="if (event.keyCode == 13) {return false;}" /> <label for="delivery_flag2">' . __( 'Chose another shipping address.', 'usces' ) . '</label></td>
		</tr>
		</table>
		<table class="customer_form" id="delivery_table">';

	$html .= uesces_addressform( 'delivery', $usces_entries );

	$html .= '</table>';
}

$html .= '<table class="customer_form" id="time">';

$cart_delivery_field = '';
if ( dlseller_have_shipped() ) {
	$cart_delivery_field .= '<tr>
		<th scope="row">' . __( 'shipping option', 'usces' ) . '</th>
		<td colspan="2">' . usces_the_delivery_method( $usces_entries['order']['delivery_method'], 'return' ) . '</td>
		</tr>
		<tr>
		<th scope="row">' . __( 'Delivery date', 'usces' ) . '</th>
		<td colspan="2">' . usces_the_delivery_date( $usces_entries['order']['delivery_date'], 'return' ) . '</td>
		</tr>
		<tr>
		<th scope="row">' . __( 'Delivery Time', 'usces' ) . '</th>
		<td colspan="2">' . usces_the_delivery_time( $usces_entries['order']['delivery_time'], 'return' ) . '</td>
		</tr>';
}
$html .= apply_filters( 'usces_filter_cart_delivery_field', $cart_delivery_field, $usces_entries );
$html .= '<tr>
	<th scope="row"><em>' . __( '*', 'usces' ) . '</em>' . __( 'payment method', 'usces' ) . '</th>
	<td colspan="2">' . usces_the_payment_method( $usces_entries['order']['payment_name'], 'return' ) . '</td>
	</tr>';
$html .= '
	</table>';

require USCES_PLUGIN_DIR . '/includes/delivery_secure_form.php';
$meta = usces_has_custom_field_meta( 'order' );
if ( ! empty( $meta ) && is_array( $meta ) ) {
	$html .= '
	<table class="customer_form" id="custom_order">';
	$html .= usces_custom_field_input( $usces_entries, 'order', '', 'return' );
	$html .= '
	</table>';
}

$entry_order_note = empty( $usces_entries['order']['note'] ) ? apply_filters( 'usces_filter_default_order_note', null ) : $usces_entries['order']['note'];

if ( dlseller_have_continue_charge() || dlseller_have_dlseller_content() ) {
	if ( dlseller_have_continue_charge() ) {
		$dlseller_terms = ( ! empty( $dlseller_options['dlseller_terms2'] ) ) ? nl2br( esc_html( $dlseller_options['dlseller_terms2'] ) ) : '';
	} elseif ( dlseller_have_dlseller_content() ) {
		$dlseller_terms = ( ! empty( $dlseller_options['dlseller_terms'] ) ) ? nl2br( esc_html( $dlseller_options['dlseller_terms'] ) ) : '';
	} else {
		$dlseller_terms = '';
	}
	$html .= '<table class="customer_form">
		<tr>
		<th rowspan="2" scope="row"><em>' . __( '*', 'usces' ) . '</em>' . __( 'Terms of Use', 'dlseller' ) . '</th>
		<td colspan="2"><div class="dlseller_terms">' . $dlseller_terms . '</div></td>
		</tr>
		<tr>
		<td colspan="2"><label for="terms"><input type="checkbox" name="offer[terms]" id="terms" />' . __( 'Agree', 'dlseller' ) . '</label></td>
		</tr>
		</table>';
}

$html  .= '<table class="customer_form" id="notes_table">
	<tr>
	<th scope="row">' . __( 'Notes', 'usces' ) . '</th>
	<td colspan="2"><textarea name="offer[note]" id="note" class="notes">' . esc_html( $entry_order_note ) . '</textarea></td>
	</tr>
	</table>';
$cus_id = ( empty( $this->cus_id ) ) ? '' : $this->cus_id;
$html  .= '<div class="send"><input name="offer[cus_id]" type="hidden" value="' . $cus_id . '" />
	<input name="backCustomer" type="submit" class="back_to_customer_button" value="' . __( 'Back', 'usces' ) . '"' . apply_filters( 'usces_filter_deliveryinfo_prebutton', null ) . ' />&nbsp;&nbsp;
	<input name="confirm" type="submit" class="to_confirm_button" value="' . __( ' Next ', 'usces' ) . '"' . apply_filters( 'usces_filter_deliveryinfo_nextbutton', null ) . ' /></div>
	</form>';

$html  .= '<div class="footer_explanation">';
$footer = '';
$html  .= apply_filters( 'usces_filter_delivery_page_footer', $footer );
$html  .= '</div>';

$html .= '</div>';
