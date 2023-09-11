<?php
/**
 * DL Seller completion page.
 *
 * @package WCEX DL Seller
 */

global $usces_entries, $usces_carts;
usces_get_entries();
usces_get_carts();

$html = '';

$html  .= '<h3>' . __( 'It has been sent succesfully.', 'usces' ) . '</h3>';
$html  .= '<div class="post">';
$html  .= '<div class="download">';
$html  .= '<div class="header_explanation">';
$header = '<p>' . __( 'Thank you for shopping.', 'usces' ) . '<br />' . __( "If you have any questions, please contact us by 'Contact'.", 'usces' ) . '</p>';
$html  .= apply_filters( 'usces_filter_cartcompletion_page_header', $header, $usces_entries, $usces_carts );
$html  .= '</div><!-- header_explanation -->';

$html .= dlseller_completion_info( $usces_carts, 'return' );

require USCES_PLUGIN_DIR . '/includes/completion_settlement.php';

$html .= apply_filters( 'usces_filter_cartcompletion_page_body', null, $usces_entries, $usces_carts );

$html .= '<form action="' . get_option( 'home' ) . '" method="post" onKeyDown="if (event.keyCode == 13) {return false;}">
<div class="send"><input name="top" type="submit" value="' . __( 'Back to the top page.', 'usces' ) . '" /></div>
</form>';

$html  .= '<div class="footer_explanation">';
$footer = '';
$html  .= apply_filters( 'usces_filter_cartcompletion_page_footer', $footer );
$html  .= '</div><!-- footer_explanation -->';

$html .= '</div><!-- download -->
</div><!-- post -->';
