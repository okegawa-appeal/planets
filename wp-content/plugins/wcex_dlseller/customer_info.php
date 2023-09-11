<?php
/**
 * DL Seller customer info page.
 *
 * @package WCEX DL Seller
 */

$usces_entries  = $this->cart->get_entry();
$member_regmode = isset( $_SESSION['usces_entry']['member_regmode'] ) ? $_SESSION['usces_entry']['member_regmode'] : 'none';

$html = '<div id="customer-info">

<div class="usccart_navi">
<ol class="ucart">
<li class="ucart usccart">' . __( '1.Cart', 'usces' ) . '</li>
<li class="ucart usccustomer usccart_customer">' . __( '2.Login', 'dlseller' ) . '</li>
<li class="ucart uscdelivery">' . __( '3.Deli. & Pay.', 'usces' ) . '</li>
<li class="ucart uscconfirm">' . __( '4.Confirm', 'usces' ) . '</li>
</ol>
</div>';

$html  .= '<div class="header_explanation">';
$header = '';
$html  .= apply_filters( 'usces_filter_customer_page_header', $header );
$html  .= '</div>';

$html .= '<div class="error_message">' . $this->error_message . '</div>';

if ( usces_is_membersystem_state() ) {
	$html .= '<form action="' . USCES_CART_URL . '" method="post" name="customer_loginform" onKeyDown="if (event.keyCode == 13) {return false;}">
	<table width="100%" border="0" cellpadding="0" cellspacing="0" class="customer_form">
	<tr>
	<th scope="row">' . __( 'e-mail adress', 'usces' ) . '</th>
	<td><input name="loginmail" id="mailaddress1" type="text" value="' . esc_attr( $usces_entries['customer']['mailaddress1'] ) . '" /></td>
	</tr>
	<tr>
	<th scope="row">' . __( 'password', 'usces' ) . '</th>
	<td><input class="hidden" value=" " /><input name="loginpass" id="mailaddress1" type="password" value="" autocomplete="off" /></td>
	</tr>
	</table>
	<p id="nav">
	<a class="lostpassword" href="' . USCES_LOSTMEMBERPASSWORD_URL . '" title="' . __( 'Did you forget your password?', 'usces' ) . '">' . __( 'Did you forget your password?', 'usces' ) . '</a>
	</p>
	<p id="nav">
	<a class="newmember" href="' . USCES_NEWMEMBER_URL . '&dlseller_transition=newmember" title="' . __( 'New enrollment for membership.', 'usces' ) . '">' . __( 'New enrollment for membership.', 'usces' ) . '</a>
	</p>
	<div class="send">
	<input name="backCart" type="submit" class="back_cart_button" value="' . __( 'Back', 'usces' ) . '" />&nbsp;&nbsp;
	<input name="customerlogin" type="submit" value="' . __( ' Next ', 'usces' ) . '" /></div>';
	$html .= apply_filters( 'usces_filter_customer_inform', null );

	$noncekey = 'post_member' . $this->get_uscesid( false );
	$html    .= wp_nonce_field( $noncekey, 'wel_nonce', true, false );
	$html    .= '</form>';
}

if ( ! dlseller_have_dlseller_content() && ! dlseller_have_continue_charge() ) {
	$html .= '<h5>' . __( 'The nonmember please enter at here.', 'usces' ) . '</h5>
	<form action="' . USCES_CART_URL . '" method="post" name="customer_form" onKeyDown="if (event.keyCode == 13) {return false;}">
	<table border="0" cellpadding="0" cellspacing="0" class="customer_form">
	<tr>
	<th scope="row"><em>' . __( '*', 'usces' ) . '</em>' . __( 'e-mail adress', 'usces' ) . '</th>';

	$html .= '<td colspan="2"><input name="customer[mailaddress1]" id="mailaddress1" type="text" value="' . esc_attr( $usces_entries['customer']['mailaddress1'] ) . '" /></td>
	</tr>
	<tr>
	<th scope="row"><em>' . __( '*', 'usces' ) . '</em>' . __( 'e-mail adress', 'usces' ) . '( ' . __( 'Re-input', 'usces' ) . ')</th>
	<td colspan="2"><input name="customer[mailaddress2]" id="mailaddress2" type="text" value="' . esc_attr( $usces_entries['customer']['mailaddress2'] ) . '" /></td>
	</tr>';

	if ( usces_is_membersystem_state() ) {
		$html .= '<tr><th scope="row">';
		if ( 'editmemberfromcart' === $member_regmode ) {
			$html .= '<em>' . __( '*', 'usces' ) . '</em>';
		}
		$html .= __( 'password', 'usces' ) . '</th>
		<td colspan="2"><input class="hidden" value=" " /><input name="customer[password1]" style="width:100px" type="password" value="' . esc_attr( $usces_entries['customer']['password1'] ) . '" autocomplete="off" />';
		if ( 'editmemberfromcart' !== $member_regmode ) {
			$html .= __( 'When you enroll newly, please fill it out.', 'usces' );
		}
		$html .= '</td></tr>';
		$html .= '<tr><th scope="row">';
		if ( 'editmemberfromcart' === $member_regmode ) {
			$html .= '<em>' . __( '*', 'usces' ) . '</em>';
		}
		$html .= __( 'Password (confirm)', 'usces' ) . '</th>
		<td colspan="2"><input name="customer[password2]" style="width:100px" type="password" value="' . esc_attr( $usces_entries['customer']['password2'] ) . '" />';
		if ( 'editmemberfromcart' !== $member_regmode ) {
			$html .= __( 'When you enroll newly, please fill it out.', 'usces' );
		}
		$html .= '</td></tr>';
	}

	$html .= uesces_addressform( 'customer', $usces_entries );

	$html    .= '</table>
	<input name="member_regmode" type="hidden" value="' . $member_regmode . '" />
	<input name="member_id" type="hidden" value="' . usces_memberinfo( 'ID', 'return' ) . '" />
	<div class="send">';
	$html    .= usces_get_customer_button( 'return' );
	$html    .= '</div>';
	$html    .= usces_agree_member_field( 'return' );
	$noncekey = 'post_member' . $this->get_uscesid( false );
	$html    .= wp_nonce_field( $noncekey, 'wc_nonce', true, false );
	$html    .= apply_filters( 'usces_filter_customer_inform', null );
	$html    .= '</form>';
}

$html  .= '<div class="footer_explanation">';
$footer = '';
$html  .= apply_filters( 'usces_filter_customer_page_footer', $footer );
$html  .= '</div>';
$html  .= '</div>';
