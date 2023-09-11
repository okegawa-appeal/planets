<?php
/**
 * DL Seller setting page.
 *
 * @package WCEX DL Seller
 */

$dlseller_options = get_option( 'dlseller', array() );
$dlseller_options = maybe_unserialize( $dlseller_options );
if ( ! isset( $dlseller_options['content_path'] ) || '' === $dlseller_options['content_path'] ) {
	$dlseller_content_path = USCES_WP_PLUGIN_DIR . '/' . plugin_basename( dirname( __FILE__ ) ) . '/';
} else {
	$dlseller_content_path = $dlseller_options['content_path'];
}
$dlseller_terms  = isset( $dlseller_options['dlseller_terms'] ) ? $dlseller_options['dlseller_terms'] : '';
$dlseller_terms2 = isset( $dlseller_options['dlseller_terms2'] ) ? $dlseller_options['dlseller_terms2'] : '';
if ( isset( $dlseller_options['dlseller_rate'] ) ) {
	$dlseller_rate = $dlseller_options['dlseller_rate'];
} else {
	$dlseller_rate                     = 5000;
	$dlseller_options['dlseller_rate'] = 5000;
}
$dlseller_member_reinforcement   = ( isset( $dlseller_options['dlseller_member_reinforcement'] ) ) ? $dlseller_options['dlseller_member_reinforcement'] : 'off';
$dlseller_restricting            = ( isset( $dlseller_options['dlseller_restricting'] ) ) ? $dlseller_options['dlseller_restricting'] : 'on';
$dlseller_reminder_mail          = ( isset( $dlseller_options['reminder_mail'] ) ) ? $dlseller_options['reminder_mail'] : 'off';
$dlseller_contract_renewal_mail  = ( isset( $dlseller_options['contract_renewal_mail'] ) ) ? $dlseller_options['contract_renewal_mail'] : 'off';
$dlseller_send_days_before       = ( isset( $dlseller_options['send_days_before'] ) ) ? $dlseller_options['send_days_before'] : 7;
$dlseller_scheduled_time['hour'] = ( isset( $dlseller_options['scheduled_time']['hour'] ) ) ? $dlseller_options['scheduled_time']['hour'] : '01';
$dlseller_scheduled_time['min']  = ( isset( $dlseller_options['scheduled_time']['min'] ) ) ? $dlseller_options['scheduled_time']['min'] : '00';
?>
<div class="wrap">
<div class="usces_admin">
<h1><?php esc_html_e( 'DLSeller Setting', 'dlseller' ); ?></h1>
<p class="version_info">Version <?php echo esc_html( WCEX_DLSELLER_VERSION ); ?></p>
<?php usces_admin_action_status(); ?>
<form action="" method="post" name="option_form" id="option_form">

<h2 class="title"><?php esc_html_e( 'DLSeller Setting', 'dlseller' ); ?></h2>
<table class="form-table">
<tr>
<th scope="row"><?php esc_html_e( 'Member check strengthen', 'dlseller' ); ?></th>
<td>
<fieldset><legend class="screen-reader-text"><span><?php esc_html_e( 'Member check strengthen', 'dlseller' ); ?></span></legend>
	<label><input name="dlseller_member_reinforcement" type="radio" id="dlseller_member_reinforcement_1" value="on"<?php checked( $dlseller_member_reinforcement, 'on' ); ?> /> <span><?php esc_html_e( 'Strengthen', 'dlseller' ); ?></span></label><br />
	<label><input name="dlseller_member_reinforcement" type="radio" id="dlseller_member_reinforcement_2" value="off"<?php checked( $dlseller_member_reinforcement, 'off' ); ?> /> <span><?php esc_html_e( 'Not strengthen', 'dlseller' ); ?></span></label><br />
	<p class="description"><?php esc_html_e( 'When strengthening, address and phone number is mandatory item. Please select the "strengthen" if at settlement performing "installments" or "auto continuation charging".', 'dlseller' ); ?></p>
</fieldset>
</td>
</tr>
<tr>
<th scope="row"><?php esc_html_e( 'Contents directory path', 'dlseller' ); ?></th>
<td>
<fieldset><legend class="screen-reader-text"><span><?php esc_html_e( 'Contents directory path', 'dlseller' ); ?></span></legend>
	<input name="dlseller_content_path" type="text" id="dlseller_content_path" value="<?php echo esc_attr( $dlseller_content_path ); ?>" size="80" /><br />
	<p class="description"><?php esc_html_e( 'Please appoint the full path of the directory which contents file is in.', 'dlseller' ); ?></p>
</fieldset>
</td>
</tr>
<tr>
<th scope="row"><?php esc_html_e( 'Terms of Use', 'dlseller' ); ?></th>
<td>
<fieldset><legend class="screen-reader-text"><span><?php esc_html_e( 'Terms of Use', 'dlseller' ); ?></span></legend>
	<textarea name="dlseller_terms" cols="90" rows="10"><?php echo esc_html( $dlseller_terms ); ?></textarea>
</fieldset>
</td>
</tr>
<tr>
<th scope="row"><?php esc_html_e( 'Terms of Use for the Continuation charging', 'dlseller' ); ?></th>
<td>
<fieldset><legend class="screen-reader-text"><span><?php esc_html_e( 'Terms of Use', 'dlseller' ); ?></span></legend>
	<textarea name="dlseller_terms2" cols="90" rows="10"><?php echo esc_html( $dlseller_terms2 ); ?></textarea>
	<!--<p class="description"><?php esc_html_e( 'Terms of Use for the Continuation charging', 'dlseller' ); ?></p>-->
</fieldset>
</td>
</tr>
<tr>
<th scope="row"><?php esc_html_e( 'Transfer rate', 'dlseller' ); ?></th>
<td>
<fieldset><legend class="screen-reader-text"><span><?php esc_html_e( 'Terms of Use', 'dlseller' ); ?></span></legend>
	<input name="dlseller_rate" type="text" id="dlseller_rate" value="<?php echo esc_attr( $dlseller_rate ); ?>" size="30" /><br />
	<p class="description"><?php esc_html_e( 'The initial value is 5000.', 'dlseller' ); ?></p>
</fieldset>
</td>
</tr>
</table>

<h2 class="title"><?php esc_html_e( 'Automatic processing Setting', 'dlseller' ); ?></h2>
<table class="form-table">
<tr>
<th scope="row"><?php esc_html_e( 'Settlement reminder-email', 'dlseller' ); ?></th>
<td>
<fieldset><legend class="screen-reader-text"><span><?php esc_html_e( 'Settlement reminder-email', 'dlseller' ); ?></span></legend>
	<label><input name="dlseller_reminder_mail" type="radio" id="dlseller_reminder_mail_0" value="off"<?php checked( $dlseller_reminder_mail, 'off' ); ?> /> <span><?php esc_html_e( "Don't send", 'usces' ); ?></span></label><br />
	<label><input name="dlseller_reminder_mail" type="radio" id="dlseller_reminder_mail_1" value="on"<?php checked( $dlseller_reminder_mail, 'on' ); ?> /> <span><?php esc_html_e( 'Send', 'usces' ); ?></span></label><br />
	<p class="description"><?php esc_html_e( 'Reminder-email of settlement of the auto continuation charging.', 'dlseller' ); ?></p>
</fieldset>
</td>
</tr>
<tr>
<th scope="row"><?php esc_html_e( 'Contract renewal email', 'dlseller' ); ?></th>
<td>
<fieldset><legend class="screen-reader-text"><span><?php esc_html_e( 'Settlement reminder-email', 'dlseller' ); ?></span></legend>
	<label><input name="dlseller_contract_renewal_mail" type="radio" id="dlseller_contract_renewal_mail_0" value="off"<?php checked( $dlseller_contract_renewal_mail, 'off' ); ?> /> <span><?php esc_html_e( "Don't send", 'usces' ); ?></span></label><br />
	<label><input name="dlseller_contract_renewal_mail" type="radio" id="dlseller_contract_renewal_mail_1" value="on"<?php checked( $dlseller_contract_renewal_mail, 'on' ); ?> /> <span><?php esc_html_e( 'Send', 'usces' ); ?></span></label><br />
	<p class="description"><?php esc_html_e( 'Reminder-email of contract renewal of the auto continuation charging.', 'dlseller' ); ?></p>
</fieldset>
</td>
</tr>
<tr>
<th scope="row"><?php esc_html_e( 'Reminder-email sent date', 'dlseller' ); ?></th>
<td>
<fieldset><legend class="screen-reader-text"><span><?php esc_html_e( 'Reminder-email sent date', 'dlseller' ); ?></span></legend>
	<input name="dlseller_send_days_before" type="text" id="dlseller_send_days_before" value="<?php echo esc_attr( $dlseller_send_days_before ); ?>" size="5" /><?php esc_html_e( 'days before', 'dlseller' ); ?><br />
	<p class="description"><?php esc_html_e( 'Send reminder-email to the number of days before. Specified value is 7 days ago.', 'dlseller' ); ?></p>
</fieldset>
</td>
</tr>
<tr>
<th scope="row"><?php esc_html_e( 'Automatic processing execution time', 'dlseller' ); ?></th>
<td>
<fieldset><legend class="screen-reader-text"><span><?php esc_html_e( 'Automatic processing execution time', 'dlseller' ); ?></span></legend>
	<select name="scheduled_time[hour]">
<?php
for ( $i = 0; $i < 24; $i++ ) :
	$hour = sprintf( '%02d', $i );
	?>
		<option value="<?php echo esc_attr( $hour ); ?>"<?php selected( $dlseller_scheduled_time['hour'], $hour ); ?>><?php echo esc_html( $hour ); ?></option>
<?php endfor; ?>
	</select>:&nbsp;<select name="scheduled_time[min]">
<?php
$i = 0;
while ( $i < 60 ) :
	$min = sprintf( '%02d', $i );
	?>
		<option value="<?php echo esc_attr( $min ); ?>"<?php selected( $dlseller_scheduled_time['min'], $min ); ?>><?php echo esc_html( $min ); ?></option>
	<?php
	$i += 10;
endwhile;
?>
	</select>
<p class="description"><?php esc_html_e( 'Reminder-email will be sent to this time.', 'dlseller' ); ?></p>
</fieldset>
</td>
</tr>
</table>

<input name="dlseller_option_update" type="submit" class="button button-primary" value="<?php esc_attr_e( 'change decision', 'usces' ); ?>" />
<input type="hidden" name="post_ID" value="<?php echo esc_attr( USCES_CART_NUMBER ); ?>" />
<input type="hidden" name="dlseller_transition" value="dlseller_option_update" />
<input type="hidden" name="scheduled_time_before[hour]" value="<?php echo esc_attr( $dlseller_scheduled_time['hour'] ); ?>" />
<input type="hidden" name="scheduled_time_before[min]" value="<?php echo esc_attr( $dlseller_scheduled_time['min'] ); ?>" />
</form>
</div><!--usces_admin-->
</div><!--wrap-->
