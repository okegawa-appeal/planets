<?php
/**
 * Plugin Name: WCEX DL Seller
 * Plugin URI: https://www.welcart.com/
 * Description: This plug-in is a download or service content sales extension plug-in dedicated to Welcart. Please use in conjunction with the Welcart e-Commerce.
 * Version: 3.4.7
 * Author: Collne Inc.
 * Author URI: https://www.collne.com/
 * Text Domain: dlseller
 * Domain Path: /languages/
 *
 * @package WCEX DL Seller
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! defined( 'USCES_EX_PLUGIN' ) ) {
	define( 'USCES_EX_PLUGIN', 1 );
}

define( 'WCEX_DLSELLER', true );
define( 'WCEX_DLSELLER_VERSION', '3.4.7.2307061' );
define( 'WCEX_DLSELLER_URL', plugins_url() . '/' . plugin_basename( dirname( __FILE__ ) ) );
define( 'WCEX_DLSELLER_DIR', WP_PLUGIN_DIR . '/' . plugin_basename( dirname( __FILE__ ) ) );

define( 'USCES_DB_CONTINUATION', '1.0' );
define( 'USCES_DB_CONTINUATION_META', '1.0' );
define( 'WCEX_DLSELLER_UP300', 1 );

if ( defined( 'USCES_VERSION' ) && version_compare( USCES_VERSION, '1.1', '>=' ) ) {
	// Set up localisation.
	load_plugin_textdomain( 'dlseller', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );

	// Include required files.
	require_once WCEX_DLSELLER_DIR . '/define_function11.php';
	require_once WCEX_DLSELLER_DIR . '/template_func.php';
	require_once WCEX_DLSELLER_DIR . '/utility.php';

	// Set up the database tables.
	dlseller_upgrade_300();

	// Hook into actions and filters.
	add_action( 'init', 'wcex_dlseller_init', 9 );
	add_action( 'usces_action_shop_admin_menue', 'dlseller_add_shop_admin_menue' );
	add_action( 'usces_action_management_admin_menue', 'dlseller_add_management_admin_menue' );
	add_action( 'wp_ajax_dlseller_make_mail_ajax', 'dlseller_make_mail_ajax' );
	add_action( 'wp_ajax_dlseller_send_mail_ajax', 'dlseller_send_mail_ajax' );
	add_action( 'usces_main', 'dlseller_define_functions', 1 );
	add_action( 'load-welcart-management_page_usces_continue', 'dlseller_continue_member_list_hook' );
	add_filter( 'screen_settings', 'dlseller_screen_settings', 10, 2 );

	// Update DLSeller options.
	$dlseller_options = get_option( 'dlseller' );
	if ( ! isset( $dlseller_options['dlseller_restricting'] ) || empty( $dlseller_options['dlseller_restricting'] ) ) {
		$dlseller_options['dlseller_restricting'] = 'on';
	}
	if ( ! isset( $dlseller_options['scheduled_time']['hour'] ) ) {
		$dlseller_options['scheduled_time']['hour'] = 1;
	}
	if ( ! isset( $dlseller_options['scheduled_time']['min'] ) ) {
		$dlseller_options['scheduled_time']['min'] = 0;
	}
	if ( ! isset( $dlseller_options['dlseller_member_reinforcement'] ) ) {
		$dlseller_options['dlseller_member_reinforcement'] = 'off';
	}
	update_option( 'dlseller', $dlseller_options );

	// Set up event.
	add_action( 'plugins_loaded', 'dlseller_setup' );
	register_deactivation_hook( __FILE__, 'dlseller_event_clear' );
}

if ( defined( 'USCES_VERSION' ) && version_compare( USCES_VERSION, '2.3-beta', '>=' ) ) {
	add_filter( 'usces_filter_mail_data', 'dlseller_usces_filter_mail_data', 10, 4 );
	add_action( 'usces_action_admin_mail_page', 'dlseller_usces_action_admin_mail_page' );
	add_action( 'wp_ajax_dlseller_filter_content_wp_editor_preview', 'dlseller_filter_content_wp_editor_preview' );
}

/**
 * Admin menu - Welcart Shop
 * usces_action_shop_admin_menue
 */
function dlseller_add_shop_admin_menue() {
	add_submenu_page( USCES_PLUGIN_BASENAME, __( 'DLSeller Setting', 'dlseller' ), __( 'DLSeller Setting', 'dlseller' ), 'level_6', 'wcex_dlseller', 'dlseller_shop_admin_page' );
}

/**
 * Admin menu - Welcart Management
 * usces_action_management_admin_menue
 */
function dlseller_add_management_admin_menue() {
	add_submenu_page( 'usces_orderlist', __( 'Continue Members', 'dlseller' ), __( 'Continue Members', 'dlseller' ), 'level_6', 'usces_continue', 'continue_member_list_page' );
}

/**
 * Set up initial processing
 * init
 */
function wcex_dlseller_init() {
	global $usces;

	usces_register_action( '09dlseller_card_update', 'request', 'dlseller_card_update', null, 'dlseller_card_update' );
	usces_register_action( '10dlseller_transition', 'request', 'dlseller_transition', null, 'wcex_dlseller_main' );
	add_action( 'admin_print_footer_scripts', 'dlseller_enqueue_scripts' );
	add_action( 'admin_enqueue_scripts', 'dlseller_admin_enqueue_scripts' );
	add_filter( 'usces_template_path_single_item', 'usces_dlseller_path_single_item' );
	add_filter( 'usces_template_path_customer', 'usces_dlseller_path_customer' );
	add_filter( 'usces_template_path_delivery', 'usces_dlseller_path_delivery' );
	add_filter( 'usces_template_path_ordercompletion', 'usces_dlseller_path_ordercompletion' );
	add_filter( 'usces_filter_inCart_quant', 'usces_filter_dlseller_incart_quant' );
	add_filter( 'usces_filter_single_item_inform', 'dlseller_filter_single_item_inform' );
	add_filter( 'usces_filter_get_item', 'dlseller_get_item', 10, 2 );
	add_filter( 'usces_filter_member_check', 'dlseller_member_check', 11 );
	add_filter( 'usces_filter_customer_check', 'dlseller_customer_check', 11 );
	remove_filter( 'usces_filter_customer_check', 'usces_filter_customer_check_custom_customer', 10 );
	// add_filter( 'usces_filter_customer_check', 'usces_filter_customer_check_custom_customer', 12 );
	add_filter( 'usces_filter_delivery_check', 'dlseller_delivery_check', 11 );
	remove_filter( 'usces_filter_delivery_check', 'usces_filter_delivery_check_custom_delivery', 10 );
	// add_filter( 'usces_filter_delivery_check', 'usces_filter_delivery_check_custom_delivery', 12 );
	// if ( defined( 'USCES_VERSION' ) && version_compare( USCES_VERSION, '1.3.10.2', '<' ) ) {
	add_filter( 'usces_filter_order_confirm_mail_first', 'dlseller_order_mail_first', 10, 2 );
	add_filter( 'usces_filter_send_order_mail_first', 'dlseller_order_mail_first', 10, 2 );
	// }
	add_filter( 'usces_filter_order_confirm_mail_shipping', 'dlseller_order_mail_shipping', 10, 2 );
	add_filter( 'usces_filter_send_order_mail_shipping', 'dlseller_order_mail_shipping', 10, 2 );
	add_filter( 'usces_filter_order_confirm_mail_meisai', 'dlseller_filter_order_mail_meisai', 10, 3 );
	add_filter( 'usces_filter_send_order_mail_meisai', 'dlseller_filter_order_mail_meisai', 10, 3 );
	add_filter( 'usces_filter_order_confirm_mail_payment', 'dlseller_filter_order_mail_payment', 10, 5 );
	add_filter( 'usces_filter_send_order_mail_payment', 'dlseller_filter_send_order_mail_payment', 10, 6 );
	add_filter( 'usces_filter_js_intoCart', 'dlseller_filter_js_intoCart', 10, 2 );
	add_filter( 'usces_item_master_second_section', 'dlseller_item_master_second_section', 10, 2 );
	add_filter( 'usces_filter_admin_modified_label', 'dlseller_filter_admin_modified_label', 10 );
	add_filter( 'usces_filter_confirm_prebutton_value', 'dlseller_filter_confirm_prebutton_value', 10 );
	add_filter( 'usces_filter_states_form_js', 'dlseller_filter_states_form_js', 10 );
	add_filter( 'usces_filter_history_item_name', 'dlseller_filter_history_item_name', 10, 4 );
	add_filter( 'usces_filter_member_history_header', 'dlseller_filter_member_history_header', 10, 2 );
	add_filter( 'usces_filter_confirm_shipping_info', 'dlseller_filter_confirm_shipping_info', 10 );
	add_filter( 'usces_filter_shipping_address_info', 'dlseller_filter_confirm_shipping_info', 10 );
	add_filter( 'usces_filter_payment_detail', 'dlseller_filter_payment_detail', 10, 2 );
	add_filter( 'usces_filter_remise_card_job', 'dlseller_filter_remise_card_job', 10 );
	add_filter( 'usces_filter_remise_card_item', 'dlseller_filter_remise_card_item', 10 );
	add_filter( 'usces_fiter_the_payment_method', 'dlseller_fiter_the_payment_method', 10, 2 );
	add_filter( 'usces_filter_the_payment_method_choices', 'dlseller_filter_the_payment_method_choices', 10, 2 );

	add_action( 'wp_ajax_dlseller_generate_pdf', 'dlseller_generate_pdf_ajax' );
	add_action( 'wp_ajax_nopriv_dlseller_generate_pdf', 'dlseller_generate_pdf_ajax' );

	add_action( 'save_post', 'dlseller_item_save_metadata' );
	add_action( 'wp_enqueue_scripts', 'add_dlseller_stylesheet' );
	add_action( 'wp_head', 'dlseller_shop_head' );
	add_action( 'usces_action_member_logout', 'dlseller_action_member_logout' );
	add_action( 'usces_action_reg_orderdata', 'dlseller_action_reg_orderdata', 10 );
	add_action( 'usces_action_del_orderdata', 'dlseller_action_del_orderdata', 10 );
	add_action( 'usces_action_update_orderdata', 'dlseller_action_update_orderdata', 10, 5 );
	add_filter( 'usces_filter_template_redirect', 'dlseller_filter_template_redirect', 2 );
	add_action( 'usces_action_single_item_inform', 'dlseller_action_single_item_inform' );
	add_action( 'usces_action_essential_mark', 'dlseller_action_essential_mark', 10, 2 );
	add_action( 'usces_action_item_dupricate', 'dlseller_action_item_dupricate', 10, 2 );
	add_action( 'usces_pre_reg_orderdata', 'dlseller_pre_reg_orderdata' );
	add_action( 'usces_action_pre_delete_memberdata', 'dlseller_action_pre_delete_memberdata', 10 );
	add_filter( 'usces_filter_send_delmembermail_notice', 'dlseller_filter_send_delmembermail_notice', 10, 2 );
	add_action( 'usces_action_order_print_cart_row', 'dlseller_action_order_print_cart_row', 10, 3 );
	add_action( 'usces_action_confirm_after_form', 'dlseller_action_confirm_after_form' );
	add_action( 'usces_action_edit_memberdata', 'dlseller_action_edit_memberdata', 10, 2 );
	add_filter( 'usces_filter_cart_rows_quant', 'dlseller_filter_cart_rows_quant', 10, 2 );

	$dlseller_options = get_option( 'dlseller' );
	if ( isset( $dlseller_options['reminder_mail'] ) && 'on' === $dlseller_options['reminder_mail'] ) {
		add_action( 'usces_action_admin_mailform', 'dlseller_action_admin_reminder_mailform' );
	}
	if ( isset( $dlseller_options['contract_renewal_mail'] ) && 'on' === $dlseller_options['contract_renewal_mail'] ) {
		add_action( 'usces_action_admin_mailform', 'dlseller_action_admin_contract_renewal_mailform' );
	}

	$usces_options = get_option( 'usces_ex' );
	if ( ! empty( $usces_options['system']['verifyemail']['switch_flag'] ) && ! is_admin() ) {
		add_filter( 'usces_filter_template_redirect', 'dlseller_filter_template_redirect_not_remove', 10 );
		add_action( 'usces_action_after_newmemberform_verified', 'dlseller_action_after_newmemberform_verified', 10, 2 );
		add_action( 'usces_action_after_newmemberfromcart_verified', 'dlseller_action_after_newmemberfromcart_verified', 10, 2 );
	}

	add_filter( 'usces_filter_confirm_table_after', 'dlseller_filter_confirm_table_after' );
	add_action( 'usces_action_confirm_table_after', 'dlseller_action_confirm_table_after' );
}

/**
 * Set up event
 * plugins_loaded
 */
function dlseller_setup() {
	add_action( 'wcdl_event', 'dlseller_do_event' );
	add_action( 'wp', 'dlseller_schedule' );
}

/**
 * Event scheduled
 * wp
 */
function dlseller_schedule() {
	if ( wp_next_scheduled( 'wcdl_event' ) ) {
		return;
	}

	dlseller_schedule_event();
}

/**
 * Event scheduled
 *
 * @param int $add Day to add.
 */
function dlseller_schedule_event( $add = 1 ) {
	$dlseller_options = get_option( 'dlseller' );

	$gmt_offset = get_option( 'gmt_offset' );
	$now        = current_time( 'timestamp', 0 );
	$year       = (int) date_i18n( 'Y', $now );
	$month      = (int) date_i18n( 'n', $now );
	$day        = (int) date_i18n( 'j', $now ) + 1;
	$timestamp  = mktime( (int) $dlseller_options['scheduled_time']['hour'], (int) $dlseller_options['scheduled_time']['min'], 0, $month, $day, $year ) - ( $gmt_offset * 3600 );
	wp_schedule_event( $timestamp, 'daily', 'wcdl_event' );
}

/**
 * Event execution
 * wcdl_event
 */
function dlseller_do_event() {
	global $usces, $wpdb;

	if ( ! dlseller_event_mark() ) {
		return;
	}

	$dlseller_options = get_option( 'dlseller' );
	if ( isset( $dlseller_options['reminder_mail'] ) && 'on' === $dlseller_options['reminder_mail'] ) {
		$send_days_before   = ( isset( $dlseller_options['send_days_before'] ) ) ? (int) $dlseller_options['send_days_before'] : 7;
		$reminder_mail_date = date_i18n( 'Y-m-d', strtotime( '+' . $send_days_before . ' days' ) );
	} else {
		$reminder_mail_date = '';
	}
	if ( isset( $dlseller_options['contract_renewal_mail'] ) && 'on' === $dlseller_options['contract_renewal_mail'] ) {
		$send_days_before           = ( isset( $dlseller_options['send_days_before'] ) ) ? (int) $dlseller_options['send_days_before'] : 7;
		$contract_renewal_mail_date = date_i18n( 'Y-m-d', strtotime( '+' . $send_days_before . ' days' ) );
	} else {
		$contract_renewal_mail_date = '';
	}

	$todays_charging    = array();
	$today              = date_i18n( 'Y-m-d', current_time( 'timestamp' ) );
	$continuation_table = $wpdb->prefix . 'usces_continuation';
	$order_table        = $wpdb->prefix . 'usces_order';
	$sel_query          = "SELECT `con_id`, 
		`con_order_id` AS `order_id`, 
		`con_member_id` AS `member_id`, 
		`con_acting` AS `acting`, 
		`con_price` AS `price`, 
		`con_frequency` AS `frequency`, 
		`con_chargingday` AS `chargingday`, 
		`con_interval` AS `interval`, 
		`con_next_charging` AS `chargedday`, 
		`con_next_contracting` AS `contractedday`, 
		`con_startdate` AS `startdate`, 
		`con_status` AS `status`, 
		`order_status` AS `order_status` 
		FROM {$continuation_table} 
		INNER JOIN {$order_table} AS `ord` ON `con_order_id` = ord.ID 
		WHERE `con_status` = 'continuation'";
	$res                = $wpdb->get_results( $sel_query, ARRAY_A );
	$res                = apply_filters( 'dlseller_filter_do_continuation_data', $res, $today );
	foreach ( $res as $continue_data ) {
		$order_status = $continue_data['order_status'];
		if ( false !== strpos( $order_status, 'cancel' ) || false !== strpos( $order_status, 'estimate' ) ) {
			continue;
		}
		$order_id  = $continue_data['order_id'];
		$member_id = $continue_data['member_id'];
		$update    = false;

		$continue_data = apply_filters( 'dlseller_filter_pre_do_continuation', $continue_data, $today );

		/* 次回契約更新日 */
		if ( ! empty( $continue_data['interval'] ) ) { /* 契約期間(月数)あり */
			if ( empty( $continue_data['contractedday'] ) ) {
				$contractedday = dlseller_next_contracting( $order_id, 'time' );
				if ( ! empty( $contractedday ) ) {
					$continue_data['contractedday'] = date_i18n( 'Y-m-d', $contractedday );
					$update                         = true;
				}
			} else {
				if ( $today === $continue_data['contractedday'] ) {
					$year  = (int) substr( $today, 0, 4 );
					$month = (int) substr( $today, 5, 2 );
					$day   = (int) substr( $today, 8, 2 );
					$time  = mktime( 0, 0, 0, ( $month + $continue_data['interval'] ), $day, $year );
					$time  = dlseller_get_valid_lastday( $time, $today, $continue_data['interval'] );
					if ( (int) $continue_data['chargingday'] !== $day ) {
						$time = dlseller_get_valid_date( $time, $continue_data['chargingday'] );
					}
					$continue_data['contractedday'] = date_i18n( 'Y-m-d', $time );
					do_action( 'dlseller_action_do_continuation_contracting', $today, $member_id, $order_id, $continue_data );
					$update = true;
				}
			}
		} else {
			$continue_data['contractedday'] = null;
		}

		/* 次回課金日 */
		if ( empty( $continue_data['chargedday'] ) ) {
			$continue_data['chargedday'] = date_i18n( 'Y-m-d', dlseller_next_charging( $order_id, 'time' ) );
			$update                      = true;
		}
		if ( $today === $continue_data['chargedday'] ) {
			$year  = (int) substr( $today, 0, 4 );
			$month = (int) substr( $today, 5, 2 );
			$day   = (int) substr( $today, 8, 2 );
			$time  = mktime( 0, 0, 0, ( $month + $continue_data['frequency'] ), $day, $year );
			$time  = dlseller_get_valid_lastday( $time, $today, $continue_data['frequency'] );
			if ( (int) $continue_data['chargingday'] !== $day ) {
				$time = dlseller_get_valid_date( $time, $continue_data['chargingday'] );
			}
			$continue_data['chargedday'] = date_i18n( 'Y-m-d', $time );
			$todays_charging[]           = array(
				'member_id' => $member_id,
				'order_id'  => $order_id,
				'data'      => $continue_data,
			);
			do_action( 'dlseller_action_do_continuation_charging', $today, $member_id, $order_id, $continue_data );
			$update = true;
		}

		if ( $update ) {
			$upd_query = $wpdb->prepare( "UPDATE {$continuation_table} SET `con_next_charging` = %s, `con_next_contracting` = %s WHERE `con_id` = %d",
				$continue_data['chargedday'],
				$continue_data['contractedday'],
				$continue_data['con_id']
			);
			$wpdb->query( $upd_query );
		}

		if ( ! empty( $reminder_mail_date ) && ! empty( $continue_data['chargedday'] ) && (string) $reminder_mail_date === (string) $continue_data['chargedday'] ) {
			dlseller_send_reminder_mail( $order_id, $continue_data );
		}
		if ( ! empty( $contract_renewal_mail_date ) && ! empty( $continue_data['contractedday'] ) && (string) $contract_renewal_mail_date === (string) $continue_data['contractedday'] ) {
			dlseller_send_contract_renewal_mail( $order_id, $continue_data );
		}
	}
	do_action( 'dlseller_action_do_continuation', $today, $todays_charging );
}

/**
 * Event marking
 *
 * @return bool
 */
function dlseller_event_mark() {
	global $wpdb;

	$today      = date_i18n( 'Y-m-d', current_time( 'timestamp' ) );
	// usces_log( 'dlseller_event_mark:' . $today, 'dlseller.log' );
	$table_name = $wpdb->prefix . 'usces_access';
	$query      = $wpdb->prepare( "SELECT acc_date FROM {$table_name} WHERE acc_key = %s LIMIT 1", 'wcdl_event' );
	$acc_date   = $wpdb->get_var( $query );
	if ( $acc_date === $today ) {
		return false;
	}

	sleep( rand( 1, 10 ) );

	if ( $acc_date ) {
		$query = $wpdb->prepare( "UPDATE {$table_name} SET acc_date = %s WHERE acc_key = %s LIMIT 1", $today, 'wcdl_event' );
	} else {
		$query = $wpdb->prepare( "INSERT INTO {$table_name} (acc_date, acc_key) VALUES (%s, %s)", $today, 'wcdl_event' );
	}
	$res = $wpdb->query( $query );
	if ( ! $res ) {
		// usces_log( 'dlseller_event_mark:*** Stopped the automatic processing. ***', 'dlseller.log' );
		return false;
	}
	return true;
}

/**
 * Event clear
 */
function dlseller_event_clear() {
	wp_clear_scheduled_hook( 'wcdl_event' );
}

/**
 * Item duplicate check
 * usces_action_item_dupricate
 *
 * @param int $post_id    Post ID.
 * @param int $newpost_id New Post ID.
 * @return void
 */
function dlseller_action_item_dupricate( $post_id, $newpost_id ) {
	// Processed in Welcart itself.
	return;
}

/**
 * Item essential mark
 * usces_action_essential_mark
 *
 * @param mixed $data  Field data.
 * @param mixed $field Field key.
 */
function dlseller_action_essential_mark( $data = null, $field = null ) {
	global $usces_essential_mark;

	$type             = ( is_array( $data ) && isset( $data['type'] ) ) ? $data['type'] : '';
	$dlseller_options = get_option( 'dlseller' );
	if ( ( isset( $dlseller_options['dlseller_member_reinforcement'] ) && 'on' === $dlseller_options['dlseller_member_reinforcement'] ) || 'customer' === $type || 'delivery' === $type ) {
		return;
	}

	$usces_essential_mark = array(
		'name1'    => '<em>' . __( '*', 'usces' ) . '</em>',
		'name2'    => '',
		'name3'    => '',
		'name4'    => '',
		'zipcode'  => '',
		'country'  => '',
		'states'   => '',
		'address1' => '',
		'address2' => '',
		'address3' => '',
		'tel'      => '',
		'fax'      => '',
	);
}

/**
 * Remise card item
 * usces_filter_remise_card_item
 *
 * @param string $item Item.
 * @return string
 */
function dlseller_filter_remise_card_item( $item ) {
	if ( dlseller_have_continue_charge() ) {
		$item = '0000990';
	}
	return $item;
}

/**
 * Remise card job
 * usces_filter_remise_card_job
 *
 * @param string $job Job.
 * @return string
 */
function dlseller_filter_remise_card_job( $job ) {
	if ( dlseller_have_continue_charge() ) {
		$job = 'AUTH';
	}
	return $job;
}

/**
 * Payment detail
 * usces_filter_payment_detail
 *
 * @param string $str           Payment detail message.
 * @param array  $usces_entries Entry data.
 * @return string
 */
function dlseller_filter_payment_detail( $str, $usces_entries ) {
	if ( dlseller_have_continue_charge() ) {
		$str = ' ' . __( 'Continuation Charging', 'dlseller' );
	}
	return $str;
}

/**
 * Confirm shipping information
 * usces_filter_confirm_shipping_info
 * usces_filter_confirm_shipping_info
 *
 * @param string $html HTML form.
 * @return string
 */
function dlseller_filter_confirm_shipping_info( $html ) {
	if ( ! dlseller_have_shipped() ) {
		$html = '';
	}
	return $html;
}

/**
 * Member shopping history
 * usces_filter_member_history_header
 *
 * @param string $html HTML form.
 * @param array  $umhs Member shopping history.
 * @return string
 */
function dlseller_filter_member_history_header( $html, $umhs ) {
	global $usces;

	$is_not_canceled_order = false === strpos( $umhs['order_status'], 'cancel' );
	if ( $is_not_canceled_order ) {
		$colspan = usces_is_membersystem_point() ? 8 : 6;
		$html    = '<tr><td class="retail" colspan="' . $colspan . '">';
		$html   .= '&nbsp;&nbsp;<a href="javascript:void(0)" class="bill_pdf_button" onclick="pdfWindow( \'bill\', \'' . $umhs['ID'] . '\' )">' . esc_html__( 'Invoice', 'usces' ) . ' PDF</a>';
		if ( ! preg_match( '/noreceipt/', $umhs['order_status'] ) ) {
			$receipt_not_accepted = apply_filters( 'dlseller_filter_receipt_not_accepted', array( 'COD' ), $umhs );
			$payment              = usces_get_payments_by_name( $umhs['payment_name'] );
			if ( ! empty( $payment['settlement'] ) && ! in_array( $payment['settlement'], $receipt_not_accepted ) ) {
				$html .= '&nbsp;&nbsp;<a href="javascript:void(0)" class="receipt_pdf_button" onclick="pdfWindow( \'receipt\', \'' . $umhs['ID'] . '\' )">' . esc_html__( 'Receipt', 'usces' ) . ' PDF</a>';
			}
		} else {
			$html .= '&nbsp;&nbsp;<span class="noreceipt">' . esc_html__( 'unpaid', 'usces' ) . '</span>';
		}
		$html  .= '</td>';
		$member = $usces->get_member();
		$status = dlseller_get_continue_status( $member['ID'], $umhs['ID'] );
		if ( 'continuation' === $status ) {
			$html .= '<td class="right green">' . esc_html__( 'continuation', 'dlseller' ) . '</td></tr>';
		} else {
			$html .= '<td class="retail"></td></tr>';
		}
	}
	return $html;
}

/**
 * Member shopping history item name
 * usces_filter_history_item_name
 *
 * @param string $html     HTML form.
 * @param array  $umhs     Member shopping history.
 * @param array  $cart_row Cart row.
 * @param int    $i        Cart row index.
 * @return string
 */
function dlseller_filter_history_item_name( $html, $umhs, $cart_row, $i ) {
	global $usces;

	$args     = func_get_args();
	$division = dlseller_get_division( $cart_row['post_id'] );
	if ( 'data' === $division ) {
		$html              = '';
		$member            = $usces->get_member();
		$mid               = (int) $member['ID'];
		$period            = dlseller_get_validityperiod( $mid, $cart_row['post_id'], $umhs['ID'] );
		$is_canceled_order = false !== strpos( $umhs['order_status'], 'cancel' );
		if ( preg_match( '/noreceipt/', $umhs['order_status'] ) || $is_canceled_order ) {
			$html .= '';
		} elseif ( empty( $period['lastdates'] ) || $period['lastdates'] >= date_i18n( 'Y/m/d', current_time( 'timestamp' ) ) ) {
			$index  = $cart_row['row_index'];
			$dlitem = $usces->get_item( $cart_row['post_id'] );
			$html  .= '<div class="redownload_link"><a class="redownload_button" href="' . USCES_CART_URL . $usces->delim . 'dlseller_transition=download&rid=' . $index . '&oid=' . $umhs['ID'] . apply_filters( 'dlseller_filter_download_para', '', $cart_row['post_id'], $cart_row['sku'] ) . '">' . __( 'Download the latest version', 'dlseller' ) . ( ! empty( $dlitem['dlseller_version'] ) ? '(v' . $dlitem['dlseller_version'] . ')' : '' ) . '</a></div>';
		} else {
			$html .= '<div class="limitover">' . __( 'Expired', 'dlseller' ) . '</div>';
		}
	}
	return apply_filters( 'dlseller_filter_history_item_name', $html, $args );
}

/**
 * Front script
 * usces_filter_states_form_js
 *
 * @param string $js Script form.
 * @return string
 */
function dlseller_filter_states_form_js( $js ) {
	global $usces;

	if ( ! dlseller_have_shipped() && ( ( is_page( USCES_CART_NUMBER ) || $usces->is_cart_page( $_SERVER['REQUEST_URI'] ) ) && ( 'customer' === $usces->page || 'delivery' === $usces->page ) ) ) {
		$js = '';
	} elseif ( ( true === $usces->is_member_logged_in() && WCUtils::is_blank( $usces->page ) ) || ( true === $usces->is_member_logged_in() && 'member' === $usces->page ) || 'editmemberform' === $usces->page || 'newmemberform' === $usces->page ) {
		$js .= '
<script type="text/javascript">
	function pdfWindow(type, id) {
		jQuery.ajax({
			type: "POST",
			url: "' . esc_url( admin_url( 'admin-ajax.php' ) ) . '",
			data: {
				action: "dlseller_generate_pdf",
				order_id: id,
				type: type,
				uscesid: "' . esc_js( $usces->get_uscesid() ) . '",
				_wpnonce: "' . esc_js( wp_create_nonce( 'generate_pdf_nonce' ) ) . '"
			},
			xhrFields: {
				responseType: "blob"
			}
		}).done( function( response ) {
			var blob = new Blob([response], { type: "application/pdf" });
			var pdf_url = URL.createObjectURL(blob);
			window.open(pdf_url, "_blank");
		}).fail( function( response ) {
			console.log("Error");
		});
	}
</script>' . "\n";
	}
	return $js;
}

/**
 * Generate pdf ajax.
 * dlseller_generate_pdf_ajax
 */
function dlseller_generate_pdf_ajax() {
	global $usces;

	// Check nonce for security.
	check_ajax_referer( 'generate_pdf_nonce', '_wpnonce' );

	$order_id = isset( $_POST['order_id'] ) ? intval( $_POST['order_id'] ) : 0;
	$type     = isset( $_POST['type'] ) ? sanitize_text_field( $_POST['type'] ) : '';
	$uscesid  = isset( $_POST['uscesid'] ) ? sanitize_text_field( $_POST['uscesid'] ) : '';

	if ( ! $order_id || ! $type || ! $uscesid ) {
		wp_send_json_error();
	}

	// resuming the session.
	$sessid = $usces->uscesdc( $uscesid );
	@session_destroy();
	session_id( $sessid );
	session_start();

	// Identification.
	if ( ! usces_is_login() ) {
		error_log( 'not login' );
		wp_send_json_error();
	}

	$usces->get_current_member();
	$member_id = $usces->current_member['id'];

	if ( 0 === (int) $member_id || ! $usces->is_order( $member_id, $order_id ) ) {
		error_log( 'not purchase' );
		wp_send_json_error();
	}

	// Output pdf.
	if ( ( isset( $usces->options['tax_display'] ) && 'activate' == $usces->options['tax_display'] ) && usces_is_reduced_taxrate( $order_id ) ) {
		require_once apply_filters( 'usces_filter_orderpdf_path_ex', USCES_PLUGIN_DIR . '/includes/order_print_ex.php' );
	} else {
		require_once apply_filters( 'usces_filter_orderpdf_path', USCES_PLUGIN_DIR . '/includes/order_print.php' );
	}

	wp_die();
}

/**
 * Template redirect
 * usces_filter_template_redirect
 */
function dlseller_filter_template_redirect() {
	global $usces, $post, $usces_entries, $usces_carts, $usces_members, $usces_item, $usces_gp, $member_regmode, $wp_version;

	if ( version_compare( $wp_version, '4.4-beta', '>' ) && is_embed() ) {
		return;
	}

	$parent_path = get_template_directory() . '/wc_templates';
	$child_path  = get_stylesheet_directory() . '/wc_templates';

	if ( is_single() && 'item' === $post->post_mime_type ) {
		$division   = dlseller_get_division( $post->ID );
		$usces_item = $usces->get_item( $post->ID );
		if ( 'data' === $division ) {

			if ( file_exists( $child_path . '/wc_item_single_data.php' ) ) {
				if ( ! post_password_required( $post ) ) {
					include $child_path . '/wc_item_single_data.php';
					exit;
				}
			} elseif ( file_exists( $parent_path . '/wc_item_single_data.php' ) && ! defined( 'USCES_PARENT_LOAD' ) ) {
				if ( ! post_password_required( $post ) ) {
					include $parent_path . '/wc_item_single_data.php';
					exit;
				}
			}

		} elseif ( 'service' === $division ) {

			if ( file_exists( $child_path . '/wc_item_single_service.php' ) ) {
				if ( ! post_password_required( $post ) ) {
					include $child_path . '/wc_item_single_service.php';
					exit;
				}
			} elseif ( file_exists( $parent_path . '/wc_item_single_service.php' ) && ! defined( 'USCES_PARENT_LOAD' ) ) {
				if ( ! post_password_required( $post ) ) {
					include $parent_path . '/wc_item_single_service.php';
					exit;
				}
			}

		} else {

			if ( file_exists( $child_path . '/wc_item_single.php' ) ) {
				if ( ! post_password_required( $post ) ) {
					include $child_path . '/wc_item_single.php';
					exit;
				}
			} elseif ( file_exists( $parent_path . '/wc_item_single.php' ) && ! defined( 'USCES_PARENT_LOAD' ) ) {
				if ( ! post_password_required( $post ) ) {
					include $parent_path . '/wc_item_single.php';
					exit;
				}
			}

		}
		return true;

	} elseif ( isset( $_REQUEST['usces_page'] ) && ( 'search_item' === wp_unslash( $_REQUEST['usces_page'] ) || 'usces_search' === wp_unslash( $_REQUEST['usces_page'] ) ) && $usces->is_cart_page( $_SERVER['REQUEST_URI'] ) ) {

		if ( file_exists( $child_path . '/wc_search_page.php' ) ) {
			include $child_path . '/wc_search_page.php';
			exit;
		} elseif ( file_exists( $parent_path . '/wc_search_page.php' ) && ! defined( 'USCES_PARENT_LOAD' ) ) {
			include $parent_path . '/wc_search_page.php';
			exit;
		}

	} elseif ( $usces->is_cart_page( $_SERVER['REQUEST_URI'] ) ) {

		switch ( $usces->page ) {
			case 'customer':
				if ( file_exists( $child_path . '/cart/wc_customer_page.php' ) ) {
					usces_get_entries();
					usces_get_member_regmode();
					include $child_path . '/cart/wc_customer_page.php';
					exit;
				} elseif ( file_exists( $parent_path . '/cart/wc_customer_page.php' ) && ! defined( 'USCES_PARENT_LOAD' ) ) {
					usces_get_entries();
					usces_get_member_regmode();
					include $parent_path . '/cart/wc_customer_page.php';
					exit;
				}

			case 'delivery':
				if ( file_exists( $child_path . '/cart/wc_delivery_page.php' ) ) {
					usces_get_entries();
					usces_get_carts();
					include $child_path . '/cart/wc_delivery_page.php';
					exit;
				} elseif ( file_exists( $parent_path . '/cart/wc_delivery_page.php' ) && ! defined( 'USCES_PARENT_LOAD' ) ) {
					usces_get_entries();
					usces_get_carts();
					include $parent_path . '/cart/wc_delivery_page.php';
					exit;
				}

			case 'confirm':
				if ( file_exists( $child_path . '/cart/wc_confirm_page.php' ) ) {
					usces_get_entries();
					usces_get_carts();
					usces_get_members();
					include $child_path . '/cart/wc_confirm_page.php';
					exit;
				} elseif ( file_exists( $parent_path . '/cart/wc_confirm_page.php' ) && ! defined( 'USCES_PARENT_LOAD' ) ) {
					usces_get_entries();
					usces_get_carts();
					usces_get_members();
					include $parent_path . '/cart/wc_confirm_page.php';
					exit;
				}

			case 'ordercompletion':
				if ( file_exists( $child_path . '/cart/wc_completion_page.php' ) ) {
					usces_get_entries();
					usces_get_carts();
					include $child_path . '/cart/wc_completion_page.php';
					exit;
				} elseif ( file_exists( $parent_path . '/cart/wc_completion_page.php' ) && ! defined( 'USCES_PARENT_LOAD' ) ) {
					usces_get_entries();
					usces_get_carts();
					include $parent_path . '/cart/wc_completion_page.php';
					exit;
				}

			case 'error':
				if ( file_exists( $child_path . '/cart/wc_cart_error_page.php' ) ) {
					include $child_path . '/cart/wc_cart_error_page.php';
					exit;
				} elseif ( file_exists( $parent_path . '/cart/wc_cart_error_page.php' ) && ! defined( 'USCES_PARENT_LOAD' ) ) {
					include $parent_path . '/cart/wc_cart_error_page.php';
					exit;
				}

			case 'newmemberform':
				if ( file_exists( $child_path . '/member/wc_new_member_page.php' ) ) {
					$member_regmode = 'newmemberform';
					include $child_path . '/member/wc_new_member_page.php';
					exit;
				} elseif ( file_exists( $parent_path . '/member/wc_new_member_page.php' ) && ! defined( 'USCES_PARENT_LOAD' ) ) {
					$member_regmode = 'newmemberform';
					include $parent_path . '/member/wc_new_member_page.php';
					exit;
				}

			case 'cart':
			default:
				if ( file_exists( $child_path . '/cart/wc_cart_page.php' ) ) {
					include $child_path . '/cart/wc_cart_page.php';
					exit;
				} elseif ( file_exists( $parent_path . '/cart/wc_cart_page.php' ) && ! defined( 'USCES_PARENT_LOAD' ) ) {
					include $parent_path . '/cart/wc_cart_page.php';
					exit;
				}

		}
		return true;

	} elseif ( $usces->is_inquiry_page( $_SERVER['REQUEST_URI'] ) ) {

	} elseif ( $usces->is_member_page( $_SERVER['REQUEST_URI'] ) ) {

		if ( 'activate' !== $usces->options['membersystem_state'] ) {
			return;
		}

		if ( $usces->is_member_logged_in() ) {

			$member_regmode = 'editmemberform';
			if ( file_exists( $child_path . '/member/wc_member_page.php' ) ) {
				include $child_path . '/member/wc_member_page.php';
				exit;
			} elseif ( file_exists( $parent_path . '/member/wc_member_page.php' ) && ! defined( 'USCES_PARENT_LOAD' ) ) {
				include $parent_path . '/member/wc_member_page.php';
				exit;
			}

		} else {

			switch ( $usces->page ) {
				case 'login':
					if ( file_exists( $child_path . '/member/wc_login_page.php' ) ) {
						include $child_path . '/member/wc_login_page.php';
						exit;
					} elseif ( file_exists( $parent_path . '/member/wc_login_page.php' ) && ! defined( 'USCES_PARENT_LOAD' ) ) {
						include $parent_path . '/member/wc_login_page.php';
						exit;
					}

				case 'newmemberform':
					if ( file_exists( $child_path . '/member/wc_new_member_page.php' ) ) {
						$member_regmode = 'newmemberform';
						include $child_path . '/member/wc_new_member_page.php';
						exit;
					} elseif ( file_exists( $parent_path . '/member/wc_new_member_page.php' ) && ! defined( 'USCES_PARENT_LOAD' ) ) {
						$member_regmode = 'newmemberform';
						include $parent_path . '/member/wc_new_member_page.php';
						exit;
					}

				case 'lostmemberpassword':
					if ( file_exists( $child_path . '/member/wc_lostpassword_page.php' ) ) {
						include $child_path . '/member/wc_lostpassword_page.php';
						exit;
					} elseif ( file_exists( $parent_path . '/member/wc_lostpassword_page.php' ) && ! defined( 'USCES_PARENT_LOAD' ) ) {
						include $parent_path . '/member/wc_lostpassword_page.php';
						exit;
					}

				case 'changepassword':
					if ( file_exists( $child_path . '/member/wc_changepassword_page.php' ) ) {
						include $child_path . '/member/wc_changepassword_page.php';
						exit;
					} elseif ( file_exists( $parent_path . '/member/wc_changepassword_page.php' ) && ! defined( 'USCES_PARENT_LOAD' ) ) {
						include $parent_path . '/member/wc_changepassword_page.php';
						exit;
					}

				case 'newcompletion':
				case 'editcompletion':
				case 'lostcompletion':
				case 'changepasscompletion':
					if ( file_exists( $child_path . '/member/wc_member_completion_page.php' ) ) {
						include $child_path . '/member/wc_member_completion_page.php';
						exit;
					} elseif ( file_exists( $parent_path . '/member/wc_member_completion_page.php' ) && ! defined( 'USCES_PARENT_LOAD' ) ) {
						include $parent_path . '/member/wc_member_completion_page.php';
						exit;
					}

				default:
					if ( file_exists( $child_path . '/member/wc_login_page.php' ) ) {
						include $child_path . '/member/wc_login_page.php';
						exit;
					} elseif ( file_exists( $parent_path . '/member/wc_login_page.php' ) && ! defined( 'USCES_PARENT_LOAD' ) ) {
						include $parent_path . '/member/wc_login_page.php';
						exit;
					}
			}
		}
		return true;
	}
}

/**
 * Login check
 */
function dlseller_action_usces_main() {
	global $usces;

	if ( ! isset( $_REQUEST['dlseller_transition'] ) && $usces->is_cart_page( $_SERVER['REQUEST_URI'] ) ) {
		usces_dlseller_login_check();
	}
}

/**
 * Logout
 * usces_action_member_logout
 */
function dlseller_action_member_logout() {
	unset( $_SESSION['usces_cart'] );
}

/**
 * Enqueue style
 * wp_head
 */
function dlseller_shop_head() {
	if ( file_exists( get_stylesheet_directory() . '/dlseller.css' ) ) :
		?>
		<link href="<?php echo esc_url( get_stylesheet_directory_uri() ); ?>/dlseller.css" rel="stylesheet" type="text/css" />
		<?php
	endif;
}

/**
 * Login check
 */
function usces_dlseller_login_check() {
	global $usces;

	if ( ! $usces->is_member_logged_in() ) {
		header( 'location: ' . USCES_MEMBER_URL );
		exit;
	}
}

/**
 * Card update action
 *
 * @return bool
 */
function dlseller_card_update() {
	global $usces;

	$dls_opts = get_option( 'dlseller' );
	$action   = wp_unslash( $_REQUEST['dlseller_card_update'] );
	switch ( $action ) {
		case 'login':
			if ( $usces->is_member_logged_in() ) {
				add_filter( 'usces_template_path_member', 'usces_dlseller_path_upaction' );
				add_filter( 'usces_filter_title_member', 'usces_dlseller_title_upaction' );
				$usces->page = 'member';
				add_filter( 'yoast-ga-push-after-pageview', 'usces_trackPageview_member' );
				add_action( 'the_post', array( $usces, 'action_memberFilter' ) );
			} else {
				$res = $usces->member_login();
				if ( 'login' !== $res ) {
					add_filter( 'usces_template_path_member', 'usces_dlseller_path_upaction' );
					add_filter( 'usces_filter_title_member', 'usces_dlseller_title_upaction' );
					$usces->page = 'member';
					add_filter( 'yoast-ga-push-after-pageview', 'usces_trackPageview_member' );
					add_action( 'the_post', array( $usces, 'action_memberFilter' ) );
				} else {
					add_filter( 'usces_filter_login_page_header', 'usces_dlseller_path_uplogin_header' );
					add_filter( 'usces_template_path_login', 'usces_dlseller_path_uplogin' );
					$usces->page = 'login';
					add_filter( 'usces_filter_login_form_action', 'usces_dlseller_login_form_action' );
					add_filter( 'usces_filter_login_button', 'usces_dlseller_login_button' );
					add_filter( 'usces_filter_login_inform', 'usces_dlseller_login_inform_cardup' );
					add_filter( 'usces_filter_memberTitle', 'usces_dlseller_title_login', 1 );
					add_action( 'the_post', array( $usces, 'action_memberFilter' ) );
					add_filter( 'yoast-ga-push-after-pageview', 'usces_trackPageview_login' );
				}
			}
			break;
	}
	return false;
}

/**
 * Main processing
 */
function wcex_dlseller_main() {
	global $usces, $wp_query, $usces_item, $post;

	$dls_opts = get_option( 'dlseller' );
	$action   = wp_unslash( $_REQUEST['dlseller_transition'] );
	switch ( $action ) {
		case 'single_item':
			if ( isset( $_POST['inCart'] ) && is_array( $_POST['inCart'] ) ) {
				$ids           = array_keys( $_POST['inCart'] );
				$post_id       = $ids[0];
				$division      = dlseller_get_division( $post_id );
				$charging_type = $usces->getItemChargingType( $post_id );
				if ( 'continue' === $charging_type ) {
					if ( false !== $usces->cart->num_row() ) {
						$usces->cart->crear_cart();
					}
				} else {
					if ( false !== $usces->cart->num_row() && dlseller_have_continue_charge() ) {
						$usces->cart->crear_cart();
					}
				}
			}
			return true;
			break;

		case 'login':
			$res = $usces->member_login();
			if ( 'login' !== $res ) {
				if ( false !== $usces->cart->num_row() ) {
					$member            = $usces->get_member();
					$mid               = (int) $member['ID'];
					$cart              = $usces->cart->get_cart();
					$dlseller_cart     = $cart;
					$cart_row          = $cart[0];
					$post_id           = $cart_row['post_id'];
					$sku               = $cart_row['sku'];
					$usces_item        = $usces->get_item( $post_id );
					$usces_item['sku'] = $sku;
					$period            = dlseller_get_validityperiod( $mid, $post_id );
					if ( true === $usces->is_purchased_item( $mid, $post_id ) && ( empty( $period['lastdate'] ) || $period['lastdate'] >= mysql2date( __( 'Y/m/d' ), current_time( 'mysql', 0 ) ) ) ) {
						$usces->cart->crear_cart();
						add_filter( 'usces_template_path_cart', 'usces_dlseller_path_redownload' );
						add_filter( 'usces_filter_title_cart', 'usces_dlseller_title_redownload', 1 );
					}
					$usces->page = 'cart';
					add_filter( 'yoast-ga-push-after-pageview', 'usces_trackPageview_cart' );
					add_action( 'the_post', array( $usces, 'action_cartFilter' ) );
				} else {
					$usces->page = 'member';
					add_filter( 'yoast-ga-push-after-pageview', 'usces_trackPageview_member' );
					add_action( 'the_post', array( $usces, 'action_memberFilter' ) );
				}
			} else {
				$usces->page = 'login';
				add_filter( 'usces_filter_login_form_action', 'usces_dlseller_login_form_action' );
				add_filter( 'usces_filter_login_button', 'usces_dlseller_login_button' );
				add_filter( 'usces_filter_login_inform', 'usces_dlseller_login_inform' );
				add_action( 'usces_action_login_page_inform', 'usces_dlseller_login_wc_inform' );
				add_filter( 'usces_filter_newmember_urlquery', 'usces_dlseller_newmember_urlquery' );
				add_filter( 'usces_filter_memberTitle', 'usces_dlseller_title_login', 1 );
				add_action( 'the_post', array( $usces, 'action_memberFilter' ) );
				add_filter( 'yoast-ga-push-after-pageview', 'usces_trackPageview_login' );
			}
			break;

		case 'newmember':
			$usces->page = 'newmemberform';
			add_filter( 'usces_filter_newmember_button', 'usces_dlseller_newmember_button' );
			add_filter( 'usces_filter_newmember_inform', 'usces_dlseller_newmember_inform' );
			add_action( 'usces_action_newmember_page_inform', 'usces_dlseller_newmember_wc_inform' );
			add_action( 'the_post', array( $usces, 'action_memberFilter' ) );
			add_filter( 'yoast-ga-push-after-pageview', 'usces_trackPageview_editmemberform' );
			add_action( 'template_redirect', array( $usces, 'template_redirect' ) );
			break;

		case 'regmember':
			$_POST['member_regmode'] = 'newmemberform';
			$usces_options           = get_option( 'usces_ex' );
			if ( ! empty( $usces_options['system']['verifyemail']['switch_flag'] ) && ! is_admin() ) {
				add_filter( 'usces_filter_verifymail_query', 'dlseller_filter_verifymail_query', 10, 2 );
			}
			$res = $usces->regist_member();
			if ( 'newcompletion' === $res ) {
				$email = trim( stripslashes( $_POST['member']['mailaddress1'] ) );
				$pass  = trim( stripslashes( $_POST['member']['password1'] ) );
				$lires = $usces->member_just_login( $email, $pass );
				if ( 'login' === $lires ) {
					wp_redirect( get_option( 'home' ) );
					exit;
				}
				wp_redirect( USCES_CART_URL . $usces->delim . 'customerinfo=1' );
				exit;
			} else {
				$usces->page = 'newmemberform';
				add_filter( 'usces_filter_newmember_button', 'usces_dlseller_newmember_button' );
				add_filter( 'usces_filter_newmember_inform', 'usces_dlseller_newmember_inform' );
				add_action( 'usces_action_newmember_page_inform', 'usces_dlseller_newmember_wc_inform' );
				if ( ! empty( $usces_options['system']['verifyemail']['switch_flag'] ) && ! is_admin() ) {
					add_filter( 'usces_filter_title_newmemberform', 'usces_dlseller_title_newmemberform' );
					add_filter( 'usces_template_path_member_form', 'usces_dlseller_template_path_member_form' );
				}
				add_action( 'the_post', array( $usces, 'action_memberFilter' ), 20 );
				add_filter( 'yoast-ga-push-after-pageview', 'usces_trackPageview_editmemberform' );
				add_action( 'template_redirect', array( $usces, 'template_redirect' ) );
			}
			break;

		case 'cart':
			return true;
			break;

		case 'confirm':
			return true;
			break;

		case 'download':
			dlseller_download();
			break;

		case 'member_reference':
			if ( $usces->is_member_logged_in() ) {
				$usces->page       = 'cart';
				$member            = $usces->get_member();
				$mid               = (int) $member['ID'];
				$post_id           = (int) wp_unslash( $_GET['post_id'] );
				$sku               = urldecode( wp_unslash( $_GET['sku'] ) );
				$usces_item        = $usces->get_item( $post_id );
				$usces_item['sku'] = $sku;
				$period            = dlseller_get_validityperiod( $mid, $post_id );
				if ( true === $usces->is_purchased_item( $mid, $post_id ) && ( empty( $period['lastdates'] ) || $period['lastdates'] >= date_i18n( 'Y/m/d', current_time( 'timestamp' ) ) ) ) {
					add_filter( 'usces_template_path_cart', 'usces_dlseller_path_redownload' );
					if ( 'service' === $usces_item['item_division'] ) {
						add_filter( 'yoast-ga-push-after-pageview', 'usces_trackPageview_ordercompletion' );
						add_filter( 'usces_filter_title_cart', 'usces_dlseller_title_information', 1 );
					} else {
						add_filter( 'yoast-ga-push-after-pageview', 'dlseller_trackPageview_redownload' );
						add_filter( 'usces_filter_title_cart', 'usces_dlseller_title_redownload', 1 );
					}
				}
				add_action( 'the_post', array( $usces, 'action_cartFilter' ) );
			} else {
				$usces->page = 'login';
				add_filter( 'usces_filter_login_form_action', 'usces_dlseller_login_form_action' );
				add_filter( 'usces_filter_login_button', 'usces_dlseller_login_button' );
				add_filter( 'usces_filter_login_inform', 'usces_dlseller_login_inform' );
				add_action( 'usces_action_login_page_inform', 'usces_dlseller_login_wc_inform' );
				add_filter( 'usces_filter_newmember_urlquery', 'usces_dlseller_newmember_urlquery' );
				add_filter( 'usces_filter_memberTitle', 'usces_dlseller_title_login', 1 );
				add_action( 'the_post', array( $usces, 'action_memberFilter' ) );
			}
			break;
	}
}

/**
 * Card update login
 * usces_filter_login_page_header
 *
 * @return string
 */
function usces_dlseller_path_uplogin_header() {
	$mes = '<h2>' . __( 'Credit card update processing', 'dlseller' ) . '</h2>
	<p>' . __( 'Login is necessary to update Credit card. Please work to update it according to the guidance of the next page if You can log in.', 'dlseller' ) . '</p>';
	return $mes;
}

/**
 * Login form action
 * usces_filter_login_form_action
 *
 * @param string $url URL.
 * @return string
 */
function usces_dlseller_login_form_action( $url ) {
	$url = USCES_CART_URL;
	return $url;
}

/**
 * Built-in template path
 * usces_template_path_single_item
 *
 * @param string $path Template path.
 * @return string
 */
function usces_dlseller_path_single_item( $path ) {
	$path = WCEX_DLSELLER_DIR . '/single_item.php';
	return $path;
}

/**
 * Built-in template path
 * usces_template_path_customer
 *
 * @param string $path Template path.
 * @return string
 */
function usces_dlseller_path_customer( $path ) {
	$path = WCEX_DLSELLER_DIR . '/customer_info.php';
	return $path;
}

/**
 * Built-in template path
 * usces_template_path_delivery
 *
 * @param string $path Template path.
 * @return string
 */
function usces_dlseller_path_delivery( $path ) {
	$path = WCEX_DLSELLER_DIR . '/delivery_info.php';
	return $path;
}

/**
 * Built-in template path
 * usces_template_path_confirm
 *
 * @param string $path Template path.
 * @return string
 */
function usces_dlseller_path_confirm( $path ) {
	$path = WCEX_DLSELLER_DIR . '/confirm.php';
	return $path;
}

/**
 * Built-in template path
 * usces_template_path_ordercompletion
 *
 * @param string $path Template path.
 * @return string
 */
function usces_dlseller_path_ordercompletion( $path ) {
	$path = WCEX_DLSELLER_DIR . '/completion.php';
	return $path;
}

/**
 * Built-in template path
 * usces_template_path_cart
 *
 * @param string $path Template path.
 * @return string
 */
function usces_dlseller_path_redownload( $path ) {
	$path = WCEX_DLSELLER_DIR . '/redownload.php';
	return $path;
}

/**
 * Built-in template path
 *
 * @param string $path Template path.
 * @return string
 */
function usces_dlseller_path_member_form( $path ) {
	$path = WCEX_DLSELLER_DIR . '/member_form.php';
	return $path;
}

/**
 * Built-in template path
 *
 * @param string $path Template path.
 * @return string
 */
function usces_dlseller_path_member( $path ) {
	$path = WCEX_DLSELLER_DIR . '/member.php';
	return $path;
}

/**
 * Built-in template path
 * usces_template_path_login
 *
 * @param string $path Template path.
 * @return string
 */
function usces_dlseller_path_uplogin( $path ) {
	$path = WCEX_DLSELLER_DIR . '/uplogin.php';
	return $path;
}

/**
 * Built-in template path
 * usces_template_path_member
 *
 * @param string $path Template path.
 * @return string
 */
function usces_dlseller_path_upaction( $path ) {
	$path = WCEX_DLSELLER_DIR . '/upaction.php';
	return $path;
}

/**
 * New member url query
 * usces_filter_newmember_urlquery
 *
 * @param string $query Query.
 * @return string
 */
function usces_dlseller_newmember_urlquery( $query ) {
	return '&dlseller_transition=newmember';
}

/**
 * Page title
 *
 * @param string $title Title.
 * @return string
 */
function usces_dlseller_title_cart( $title ) {
	return __( 'Checkout', 'dlseller' );
}

/**
 * Page title
 *
 * @param string $title Title.
 * @return string
 */
function usces_dlseller_title_redownload( $title ) {
	return __( 'Redownload', 'dlseller' );
}

/**
 * Page title
 * usces_filter_title_cart
 *
 * @param string $title Title.
 * @return string
 */
function usces_dlseller_title_information( $title ) {
	return __( 'Information', 'dlseller' );
}

/**
 * Page title
 *
 * @param string $title Title.
 * @return string
 */
function usces_dlseller_title_ordercompletion( $title ) {
	return __( 'Download', 'dlseller' );
}

/**
 * Page title
 * usces_filter_memberTitle
 *
 * @param string $title Title.
 * @return string
 */
function usces_dlseller_title_login( $title ) {
	return __( 'Log-in for members', 'usces' );
}

/**
 * Page title
 *
 * @param string $title Title.
 * @return string
 */
function usces_dlseller_title_upaction( $title ) {
	return __( 'Credit card update processing', 'dlseller' );
}

/**
 * Mail message
 * usces_filter_order_confirm_mail_first
 * usces_filter_send_order_mail_first
 *
 * @param string $str  Message.
 * @param array  $data Order data.
 * @return string
 */
function dlseller_order_mail_first( $str, $data ) {
	if ( isset( $data['mem_id'] ) && ! empty( $data['mem_id'] ) ) {
		if ( function_exists( 'usces_is_html_mail' ) && usces_is_html_mail() ) {
			$str .= '<tr>';
			$str .= '<td style="background-color: #f9f9f9; padding: 12px; width: 50%; border: 1px solid #ddd;">' . __( 'membership number', 'usces' ) . '</td>';
			$str .= '<td style="padding: 12px; width: 50%; border: 1px solid #ddd;">' . $data['mem_id'] . '</td>';
			$str .= '</tr>';
		}
	}
	return $str;
}

/**
 * Mail shipping message
 * usces_filter_order_confirm_mail_shipping
 * usces_filter_send_order_mail_shipping
 *
 * @param string $msg_shipping Shipping message.
 * @param array  $data         Order data.
 * @return string
 */
function dlseller_order_mail_shipping( $msg_shipping, $data ) {
	$cartdata = ( ! empty( $data['ID'] ) ) ? usces_get_ordercartdata( $data['ID'] ) : array();
	if ( dlseller_have_shipped( $cartdata ) ) {
		return $msg_shipping;
	} else {
		return '';
	}
}

/**
 * Quantity
 *
 * @param int $quant Quantity.
 * @return int
 */
function usces_filter_dlseller_incart_quant( $quant ) {
	if ( dlseller_have_continue_charge() ) {
		return 1;
	} else {
		return $quant;
	}
}

/**
 * Quantity update
 *
 * @param string $button Button form.
 * @return string
 */
function usces_filter_cart_upbutton( $button ) {
	$addtag .= '';
	return $addtag;
}

/**
 * Embedded field
 * usces_filter_single_item_inform
 *
 * @param string $html HTML form.
 * @return string
 */
function dlseller_filter_single_item_inform( $html ) {
	$html .= '<input name="dlseller_transition" type="hidden" value="single_item" />';
	return $html;
}

/**
 * Embedded field
 * usces_action_single_item_inform
 */
function dlseller_action_single_item_inform() {
	echo '<input name="dlseller_transition" type="hidden" value="single_item" />';
}

/**
 * Embedded field
 *
 * @param string $html HTML form.
 * @return string
 */
function usces_dlseller_login_inform( $html ) {
	$html .= '<input name="dlseller_transition" type="hidden" value="login" />';
	return $html;
}

/**
 * Embedded field
 *
 * @param string $html HTML form.
 * @return string
 */
function usces_dlseller_login_inform_cardup( $html ) {
	$html .= '<input name="dlseller_card_update" type="hidden" value="login" />';
	$html .= '<input name="dlseller_order_id" type="hidden" value="' . absint( wp_unslash( $_REQUEST['dlseller_order_id'] ) ) . '" />';
	$html .= '<input name="dlseller_up_mode" type="hidden" value="2" />';
	return $html;
}

/**
 * Embedded field
 *
 * @param string $html HTML form.
 * @return string
 */
function usces_dlseller_newmember_inform( $html ) {
	$html .= '<input name="dlseller_transition" type="hidden" value="regmember" />';
	return $html;
}

/**
 * Embedded field
 */
function usces_dlseller_newmember_wc_inform() {
	$html = '<input name="dlseller_transition" type="hidden" value="regmember" />';
	echo $html;
}

/**
 * Embedded field
 *
 * @param string $html HTML form.
 * @return string
 */
function usces_dlseller_cart_inform( $html ) {
	$html .= '<input name="dlseller_transition" type="hidden" value="cart" />';
	return $html;
}

/**
 * Embedded field
 *
 * @param string $html HTML form.
 * @return string
 */
function dlseller_customer_inform( $html ) {
	$html .= '<input name="dlseller_transition" type="hidden" value="customer" />';
	return $html;
}

/**
 * Embedded field
 *
 * @param string $html HTML form.
 * @return string
 */
function usces_dlseller_confirm_inform( $html ) {
	$html .= '<input name="dlseller_transition" type="hidden" value="confirm" />';
	return $html;
}

/**
 * Embedded field
 *
 * @param string $button Button form.
 * @return string
 */
function usces_dlseller_login_button( $button ) {
	$button = '<input type="submit" name="dlSeller" id="member_login" value="' . __( 'Log-in', 'usces' ) . '" tabindex="100" />';
	return $button;
}

/**
 * Embedded field
 *
 * @param string $button Button form.
 * @return string
 */
function usces_dlseller_newmember_button( $button ) {
	$button = '<input name="regmemberdl" type="submit" value="' . __( 'transmit a message', 'usces' ) . '" />';
	return $button;
}

/**
 * Download
 */
function dlseller_download() {
	global $usces;

	if ( false === $usces->is_member_logged_in() ) {
		header( 'HTTP/1.0 401 Unauthorized' );
		die( 'Unauthorized' );
	}

	$member = $usces->get_member();
	$mid    = (int) $member['ID'];
	if ( ! isset( $_GET['pid'] ) ) {
		$rid   = absint( wp_unslash( $_GET['rid'] ) );
		$oid   = absint( wp_unslash( $_GET['oid'] ) );
		$order = $usces->get_order_data( $oid );
		if ( false === $order ) {
			exit;
		}
		if ( defined( 'USCES_VERSION' ) && version_compare( USCES_VERSION, '1.4', '>=' ) ) {
			$cart = usces_get_ordercartdata( $oid );
			foreach ( $cart as $cart_row ) {
				if ( $rid == $cart_row['row_index'] ) {
					$terget_row = $cart_row;
					break;
				}
			}
			$post_id = $terget_row['post_id'];
			$sku     = $terget_row['sku_code'];
		} else {
			$cart    = $order['cart'];
			$post_id = $cart[ $rid ]['post_id'];
			$sku     = $cart[ $rid ]['sku'];
		}
	} else {
		$post_id = absint( wp_unslash( $_GET['pid'] ) );
		$sku     = isset( $_GET['sku'] ) ? wp_unslash( $_GET['sku'] ) : null;
		$oid     = ( isset( $_GET['oid'] ) ) ? absint( wp_unslash( $_GET['oid'] ) ) : null;
	}

	if ( defined( 'USCES_VERSION' ) && version_compare( USCES_VERSION, '1.9.31', '>=' ) ) {
		$purchased = $usces->is_purchased_item( $mid, $post_id, urldecode( $sku ), $oid );
	} else {
		$purchased = $usces->is_purchased_item( $mid, $post_id, urldecode( $sku ) );
	}

	if ( false === $purchased || 'noreceipt' === $purchased ) {
		header( 'Content-Type: text/html; charset=UTF-8' );
		echo esc_html__( 'An error occurred. Please contact the manager.', 'dlseller' ) . '(error:nomember or nopurchase)<br /><br /><br />';
		echo '<a href="' . get_option( 'home' ) . '">' . esc_html__( 'Back to the top page.', 'usces' ) . '</a>';
		exit;
	}

	/**
	 * Filters dlseller post_id to fix different item post_id.
	 * DLSeller enables to download a file that the ordered items only.
	 * In some case, user can download files with a capability from other item.
	 *
	 * Return post_id which will be downloaded.
	 *
	 * @param int $post_id post_id which is an ordered item.
	 */
	$post_id = apply_filters( 'dlseller_filter_fixed_item_post_id', $post_id );

	$filename         = dlseller_get_filename( $post_id, $sku );
	$product          = wel_get_product( $post_id );
	$downloads        = $product['dlseller_downloads'];
	$dl               = (int) $downloads;
	$dlseller_options = get_option( 'dlseller' );
	$delseller_path   = $dlseller_options['content_path'] . $filename;
	if ( ! file_exists( $delseller_path ) || ! is_file( $delseller_path ) ) {
		header( 'Content-Type: text/html; charset=UTF-8' );
		echo __( 'An error occurred. Please contact the manager.', 'dlseller' ) . '(error:nofile)<br /><br /><br />';
		echo '<a href="' . get_option( 'home' ) . '">' . __( 'Back to the top page.', 'usces' ) . '</a>';
		exit;
	}

	$rid      = isset( $rid ) ? $rid : null;
	$errormes = apply_filters(
		'dlseller_filter_download_error_message',
		'',
		$post_id,
		$oid,
		$rid,
		$sku,
		$purchased,
		$filename,
		$dlseller_options
	);
	if ( ! empty( $errormes ) ) {
		header( 'Content-Type: text/html; charset=UTF-8' );
		echo (string) $errormes . '<br /><br /><br />';
		echo '<a href="' . get_option( 'home' ) . '">' . __( 'Back to the top page.', 'usces' ) . '</a>';
		exit;
	}

	$content_length = filesize( $delseller_path );
	session_write_close();
	mb_http_output( 'pass' );
	header( 'Cache-Control: public' );
	header( 'Pragma: public' );
	header( 'Content-Disposition: attachment; filename=' . usces_dlseller_filename( $post_id, $sku ) );
	header( 'Content-Type: application/octet-stream' );
	header( 'Content-Length: ' . $content_length );

	set_time_limit( 0 );
	while ( ob_get_level() > 0 ) {
		ob_end_clean();
	}
	ob_start();

	$fp = fopen( $delseller_path, 'r' );
	while ( ! feof( $fp ) ) {
		print fread( $fp, round( $dlseller_options['dlseller_rate'] * 1024 ) );
		ob_flush();
	}
	ob_flush();
	fclose( $fp );
	ob_end_clean();

	$dl++;
	usces_dlseller_update_dlcount( $post_id, 0, 1 );
	exit;
}

/**
 * Admin sripts
 * admin_print_footer_scripts
 */
function dlseller_enqueue_scripts() {
	if ( ( ( isset( $_GET['page'] ) && 'usces_itemedit' === wp_unslash( $_GET['page'] ) ) && ( isset( $_GET['action'] ) && 'edit' === wp_unslash( $_GET['action'] ) ) )
		|| ( isset( $_GET['page'] ) && 'usces_itemnew' === wp_unslash( $_GET['page'] ) ) ) :
		?>
<script type="text/javascript">
jQuery(function( $) {
	$("#division_shipped").change(function() {
		$("tr.shipped").css("display","");
		$("tr.dl_service").css("display","none");
		$("tr.dl_content").css("display","none");
		$("tr.dl_data").css("display","none");
	});
	$("#division_data").change(function() {
		$("tr.shipped").css("display","none");
		$("tr.dl_service").css("display","none");
		$("tr.dl_content").css("display","");
		$("tr.dl_data").css("display","");
	});
	$("#division_service").change(function() {
		$("tr.shipped").css("display","none");
		$("tr.dl_service").css("display","");
		$("tr.dl_content").css("display","");
		$("tr.dl_data").css("display","none");
	});
	var dld = $("input[name=\'item_division\']:checked").val();
	if( "shipped" == dld ){
		$("tr.shipped").css("display","");
		$("tr.dl_service").css("display","none");
		$("tr.dl_content").css("display","none");
		$("tr.dl_data").css("display","none");
	}else if( "data" == dld ){
		$("tr.shipped").css("display","none");
		$("tr.dl_service").css("display","none");
		$("tr.dl_content").css("display","");
		$("tr.dl_data").css("display","");
	}else if( "service" == dld ){
		$("tr.shipped").css("display","none");
		$("tr.dl_service").css("display","");
		$("tr.dl_content").css("display","");
		$("tr.dl_data").css("display","none");
	}
	var dct = $("#item_charging_type").val();
	if( "0" == dct ){
		$("tr.dl_frequency").css("display","none");
		$("tr.dl_chargingday").css("display","none");
		$("tr.dl_interval").css("display","none");
	}else{
		$("tr.dl_frequency").css("display","");
		$("tr.dl_chargingday").css("display","");
		$("tr.dl_interval").css("display","");
	}
	$("#item_charging_type").change(function() {
		dct = $("#item_charging_type").val();
		if( "0" == dct ){
			$("tr.dl_frequency").css("display","none");
			$("tr.dl_chargingday").css("display","none");
			$("tr.dl_interval").css("display","none");
		}else{
			$("tr.dl_frequency").css("display","");
			$("tr.dl_chargingday").css("display","");
			$("tr.dl_interval").css("display","");
		}
	});
});
</script>
		<?php
	endif;
}

/**
 * Admin enqueue scripts
 * admin_enqueue_scripts
 *
 * @param string $hook_suffix The current admin page.
 */
function dlseller_admin_enqueue_scripts( $hook_suffix ) {
	if ( 'welcart-management_page_usces_continue' === $hook_suffix ) {
		$path = USCES_FRONT_PLUGIN_URL . '/js/jquery/jquery.cookie.js';
		wp_enqueue_script( 'usces_member_cookie', $path, array( 'jquery' ), USCES_VERSION, true );
		wp_enqueue_script( 'jquery-ui-dialog' );
	}
}

/**
 * Item edit page
 * usces_item_master_second_section
 *
 * @param string $second_section Second section area.
 * @param int    $post_ID        Post ID.
 */
function dlseller_item_master_second_section( $second_section, $post_ID ) {
	global $usces;

	$division           = dlseller_get_division( $post_ID );
	$product            = wel_get_product( $post_ID );
	$item_charging_type = $product['item_charging_type'];
	$item_frequency     = $product['item_frequency'];
	$item_chargingday   = $product['item_chargingday'];
	$dlseller_interval  = $product['dlseller_interval'];
	$dlseller_validity  = $product['dlseller_validity'];
	$dlseller_file      = $product['dlseller_file'];
	$dlseller_date      = $product['dlseller_date'];
	$dlseller_version   = $product['dlseller_version'];
	$dlseller_author    = $product['dlseller_author'];
	$dlseller_purchases = $product['dlseller_purchases'];
	$dlseller_downloads = $product['dlseller_downloads'];
	$dls_mon            = usces_dlseller_get_dlcount( $post_ID, 'month' );
	$dls_tol            = usces_dlseller_get_dlcount( $post_ID, 'total' );

	ob_start();
	?>
	<tr>
	<th><?php esc_html_e( 'Division', 'dlseller' ); ?></th>
	<td><label for="division_shipped"><input name="item_division" id="division_shipped" type="radio" value="shipped"<?php checked( $division, 'shipped' ); ?> />&nbsp;<?php esc_html_e( 'Shipped', 'dlseller' ); ?></label>&nbsp;&nbsp;
		<label for="division_data"><input name="item_division" id="division_data" type="radio" value="data"<?php checked( $division, 'data' ); ?> />&nbsp;<?php esc_html_e( 'Data file', 'dlseller' ); ?></label>&nbsp;&nbsp;
		<label for="division_service"><input name="item_division" id="division_service" type="radio" value="service"<?php checked( $division, 'service' ); ?> />&nbsp;<?php esc_html_e( 'Service', 'dlseller' ); ?></label></td>
	</tr>
	<th><?php esc_html_e( 'Charging type', 'usces' ); ?></th>
	<td>
		<select id="item_charging_type" name="item_charging_type">
			<option value="0"<?php selected( (int) $item_charging_type, 0 ); ?>><?php esc_html_e( 'Normal Charging', 'dlseller' ); ?></option>
			<option value="1"<?php selected( (int) $item_charging_type, 1 ); ?>><?php esc_html_e( 'Continuation Charging', 'dlseller' ); ?></option>
		</select>
	</td>
	</tr>
	<tr class="dl_frequency">
	<th><?php esc_html_e( 'Charging Interval', 'dlseller' ); ?></th>
	<td>
		<select id="item_frequency" name="item_frequency">
	<?php
	$frequency_options = '
		<option value="1"' . selected( (int) $item_frequency, 1, false ) . '>' . __( 'Monthly', 'dlseller' ) . '</option>
		<option value="6"' . selected( (int) $item_frequency, 6, false ) . '>' . __( 'Every six months', 'dlseller' ) . '</option>
		<option value="12"' . selected( (int) $item_frequency, 12, false ) . '>' . __( 'Every year', 'dlseller' ) . '</option>';
	echo apply_filters( 'dlseller_filter_item_frequency_options', $frequency_options, $item_frequency, $post_ID );
	?>
		</select>
	</td>
	</tr>
	<tr class="dl_chargingday">
	<th><?php esc_html_e( 'Charging Date', 'dlseller' ); ?></th>
	<td>
		<select id="item_chargingday" name="item_chargingday">
			<option value="99"<?php selected( (int) $item_chargingday, 99 ); ?>><?php esc_html_e( 'Order date', 'dlseller' ); ?></option>
		<?php for ( $i = 1; $i < 29; $i++ ) : ?>
			<option value="<?php echo esc_attr( $i ); ?>"<?php selected( (int) $item_chargingday, $i ); ?>><?php echo str_pad( $i, 2, ' ', STR_PAD_LEFT ); ?></option>
		<?php endfor; ?>
		</select>
	</td>
	<tr class="dl_interval">
	<th><?php esc_html_e( 'Contract Period(Months)', 'dlseller' ); ?></th>
	<td><input type="text" name="dlseller_interval" id="dlseller_interval" class="itemCode" value="<?php echo esc_attr( $dlseller_interval ); ?>" /></td>
	</tr>
	</tr>
	<tr class="dl_data">
	<th><?php esc_html_e( 'Validity(days)', 'dlseller' ); ?></th>
	<td><input type="text" name="dlseller_validity" id="dlseller_validity" class="itemCode" value="<?php echo esc_attr( $dlseller_validity ); ?>" /></td>
	</tr>
	<tr class="dl_data">
	<th><?php esc_html_e( 'File Name', 'dlseller' ); ?></th>
	<td><input type="text" name="dlseller_file" id="dlseller_file" class="itemCode" value="<?php echo esc_attr( $dlseller_file ); ?>" /></td>
	</tr>
	<tr class="dl_data">
	<th><?php esc_html_e( 'Release Date', 'dlseller' ); ?></th>
	<td><input type="text" name="dlseller_date" id="dlseller_date" class="itemCode" value="<?php echo esc_attr( $dlseller_date ); ?>" /></td>
	</tr>
	<tr class="dl_data">
	<th><?php esc_html_e( 'Version', 'dlseller' ); ?></th>
	<td><input type="text" name="dlseller_version" id="dlseller_version" class="itemCode" value="<?php echo esc_attr( $dlseller_version ); ?>" /></td>
	</tr>
	<tr class="dl_data">
	<th><?php esc_html_e( 'Author', 'dlseller' ); ?></th>
	<td><input type="text" name="dlseller_author" id="dlseller_author" class="itemCode" value="<?php echo esc_attr( $dlseller_author ); ?>" /></td>
	</tr>
	<tr class="dl_data">
	<th><?php esc_html_e( 'Purchases', 'dlseller' ); ?></th>
	<td><?php echo esc_html( $dls_mon['par'] ); ?>(<?php echo esc_html( $dls_tol['par'] ); ?>)</td>
	</tr>
	<tr class="dl_data">
	<th><?php esc_html_e( 'Downloads', 'dlseller' ); ?></th>
	<td><?php echo esc_html( $dls_mon['dl'] ); ?>(<?php echo esc_html( $dls_tol['dl'] ); ?>)</td>
	</tr>
	<?php
	$addtag = ob_get_contents();
	ob_end_clean();
	echo apply_filters( 'dlseller_filter_item_master_second_section', $addtag, $post_ID ) . $second_section;
}

/**
 * Add processing when saving the product.
 *
 * @return int|void
 */
function dlseller_item_save_metadata() {
	global $usces;

	$post_ID = isset( $_POST['post_ID'] ) ? absint( wp_unslash( $_POST['post_ID'] ) ) : - 1;
	if ( $post_ID < 0 ) {
		return $post_ID;
	}

	if ( isset( $_POST['post_type'] ) && 'page' == wp_unslash( $_POST['post_type'] ) ) {
		return $post_ID;
	} else {
		if ( ! current_user_can( 'edit_post', $post_ID ) ) {
			return $post_ID;
		}
	}

	$data = array();

	if ( isset( $_POST['item_division'] ) ) {
		$data['item_division'] = wp_unslash( $_POST['item_division'] );
	}
	if ( isset( $_POST['item_charging_type'] ) ) {
		$data['item_charging_type'] = absint( wp_unslash( $_POST['item_charging_type'] ) );
	}
	if ( isset( $_POST['item_frequency'] ) ) {
		$data['item_frequency'] = absint( wp_unslash( $_POST['item_frequency'] ) );
	}
	if ( isset( $_POST['item_chargingday'] ) ) {
		$data['item_chargingday'] = absint( wp_unslash( $_POST['item_chargingday'] ) );
	}
	if ( isset( $_POST['dlseller_interval'] ) ) {
		$data['dlseller_interval'] = trim( wp_unslash( $_POST['dlseller_interval'] ) );
	}
	if ( isset( $_POST['dlseller_validity'] ) ) {
		$data['dlseller_validity'] = trim( wp_unslash( $_POST['dlseller_validity'] ) );
	}
	if ( isset( $_POST['dlseller_file'] ) ) {
		$data['dlseller_file'] = trim( wp_unslash( $_POST['dlseller_file'] ) );
	}
	if ( isset( $_POST['dlseller_date'] ) ) {
		$data['dlseller_date'] = trim( wp_unslash( $_POST['dlseller_date'] ) );
	}
	if ( isset( $_POST['dlseller_version'] ) ) {
		$data['dlseller_version'] = trim( wp_unslash( $_POST['dlseller_version'] ) );
	}
	if ( isset( $_POST['dlseller_author'] ) ) {
		$data['dlseller_author'] = trim( wp_unslash( $_POST['dlseller_author'] ) );
	}
	if ( isset( $_POST['dlseller_purchases'] ) ) {
		$data['dlseller_purchases'] = trim( wp_unslash( $_POST['dlseller_purchases'] ) );
	}
	if ( isset( $_POST['dlseller_downloads'] ) ) {
		$data['dlseller_downloads'] = trim( wp_unslash( $_POST['dlseller_downloads'] ) );
	}

	$item = (array) wel_get_item( $post_ID, false );

	$data = array_merge(
		$item,
		$data
	);

	wel_update_item_data( $data, $post_ID );
}

/**
 * Admin setting page
 */
function dlseller_shop_admin_page() {
	global $usces, $wpdb, $wp_locale, $current_user;
	global $wp_query;

	if ( isset( $_POST['dlseller_transition'] ) && 'dlseller_option_update' == wp_unslash( $_POST['dlseller_transition'] ) ) {
		$_POST            = $usces->stripslashes_deep_post( $_POST );
		$dlseller_options = array();
		$dlseller_options['content_path']                  = trim( $_POST['dlseller_content_path'] );
		$dlseller_options['dlseller_terms']                = trim( $_POST['dlseller_terms'] );
		$dlseller_options['dlseller_terms2']               = trim( $_POST['dlseller_terms2'] );
		$dlseller_options['dlseller_rate']                 = (int) trim( $_POST['dlseller_rate'] );
		$dlseller_options['dlseller_member_reinforcement'] = isset( $_POST['dlseller_member_reinforcement'] ) ? $_POST['dlseller_member_reinforcement'] : 'off';
		$dlseller_options['dlseller_restricting']          = isset( $_POST['dlseller_restricting'] ) ? $_POST['dlseller_restricting'] : 'on';
		$dlseller_options['reminder_mail']                 = ( isset( $_POST['dlseller_reminder_mail'] ) ) ? $_POST['dlseller_reminder_mail'] : 'off';
		$dlseller_options['contract_renewal_mail']         = ( isset( $_POST['dlseller_contract_renewal_mail'] ) ) ? $_POST['dlseller_contract_renewal_mail'] : 'off';
		$dlseller_options['send_days_before']              = ( isset( $_POST['dlseller_send_days_before'] ) ) ? (int) $_POST['dlseller_send_days_before'] : 7;
		$dlseller_options['scheduled_time']                = $_POST['scheduled_time'];
		update_option( 'dlseller', $dlseller_options );
		$usces->action_status  = 'success';
		$usces->action_message = __( 'options are updated', 'usces' );

		if ( ( $_POST['scheduled_time']['hour'] != $_POST['scheduled_time_before']['hour'] ) ||
			( $_POST['scheduled_time']['min'] != $_POST['scheduled_time_before']['min'] ) ) {
			$add        = 1;
			$today      = date_i18n( 'Y-m-d', current_time( 'timestamp' ) );
			$table_name = $wpdb->prefix . 'usces_access';
			$query      = $wpdb->prepare( "SELECT acc_date FROM {$table_name} WHERE acc_key = %s LIMIT 1", 'wcdl_event' );
			$acc_date   = $wpdb->get_var( $query );
			if ( $acc_date && $acc_date !== $today ) {
				$now = date_i18n( 'Hi', current_time( 'timestamp' ) );
				if ( $now < $_POST['scheduled_time']['hour'] . $_POST['scheduled_time']['min'] ) {
					$add = 0;
				}
			}
			dlseller_event_clear();
			dlseller_schedule_event( $add );
		}
	}

	if ( empty( $usces->action_message ) || '' === $usces->action_message ) {
		$usces->action_status  = 'none';
		$usces->action_message = '';
	}

	require_once WCEX_DLSELLER_DIR . '/admin_dlseller_page.php';
}

/**
 * Continue member list page
 */
function continue_member_list_page() {
	global $usces;

	if ( empty( $usces->action_message ) || '' === $usces->action_message ) {
		$usces->action_status  = 'none';
		$usces->action_message = '';
	}

	$continue_action = isset( $_REQUEST['continue_action'] ) ? wp_unslash( $_REQUEST['continue_action'] ) : '';
	do_action( 'dlseller_action_continue_member_list_page', $continue_action );
	switch ( $continue_action ) {
		case 'dlcontinuememberlist':
			dlseller_download_continue_member_list();
			break;
		case 'load_rich_editor':
			wp_enqueue_style( 'colors' );
			require_once WCEX_DLSELLER_DIR . '/admin_continue_sub_rich_editor.php';
			break;
		default:
			require_once WCEX_DLSELLER_DIR . '/admin_continue_member_list_page.php';
	}
}

/**
 * Get DL Seller item
 *
 * @param array $usces_item Item data.
 * @param int   $post_id    Post ID.
 * @return array
 */
function dlseller_get_item( $usces_item, $post_id ) {
	global $usces;

	if ( ! empty( $post_id ) ) {

		$product            = wel_get_product( $post_id );
		$item_division      = $product['item_division'];
		$item_charging_type = $product['item_charging_type'];
		$item_frequency     = $product['item_frequency'];
		$dlseller_interval  = $product['dlseller_interval'];
		$dlseller_validity  = $product['dlseller_validity'];
		$dlseller_file      = $product['dlseller_file'];
		$dlseller_date      = $product['dlseller_date'];
		$dlseller_version   = $product['dlseller_version'];
		$dlseller_author    = $product['dlseller_author'];
		$dlseller_purchases = $product['dlseller_purchases'];
		$dlseller_downloads = $product['dlseller_downloads'];

		$usces_item['item_division']      = ! empty( $item_division ) ? $item_division : 'shipped';
		$usces_item['item_charging_type'] = ! empty( $item_charging_type ) ? $item_charging_type : 0;
		$usces_item['item_frequency']     = ! empty( $item_frequency ) ? $item_frequency : 1;
		$usces_item['item_chargingday']   = $usces->getItemChargingDay( $post_id );
		$usces_item['dlseller_interval']  = ! empty( $dlseller_interval ) ? $dlseller_interval : '';
		$usces_item['dlseller_validity']  = ! empty( $dlseller_validity ) ? $dlseller_validity : '';
		$usces_item['dlseller_file']      = ! empty( $dlseller_file ) ? $dlseller_file : '';
		$usces_item['dlseller_date']      = ! empty( $dlseller_date ) ? $dlseller_date : '';
		$usces_item['dlseller_version']   = ! empty( $dlseller_version ) ? $dlseller_version : '';
		$usces_item['dlseller_author']    = ! empty( $dlseller_author ) ? $dlseller_author : '';
		$usces_item['dlseller_purchases'] = (int) $dlseller_purchases;
		$usces_item['dlseller_downloads'] = (int) $dlseller_downloads;
	}
	return $usces_item;
}

/**
 * Term of validity
 *
 * @param int    $mid        Member ID.
 * @param int    $post_id    Post ID.
 * @param string $order_id   Order ID.
 * @param string $order_date Order date.
 * @return array
 */
function dlseller_get_validityperiod( $mid, $post_id, $order_id = '', $order_date = '' ) {
	global $usces;

	$firstdate = '';
	if ( ! empty( $order_id ) ) {
		$receipted_date = $usces->get_order_meta_value( 'receipted_date', $order_id );
		if ( $receipted_date ) {
			$firstdate = $receipted_date;
		} else {
			$firstdate = $order_date;
		}
	} else {
		$history = $usces->get_member_history( $mid );
		foreach ( $history as $row ) {
			$receipted_date = '';
			if ( false !== strpos( $row['order_status'], 'cancel' ) || false !== strpos( $row['order_status'], 'estimate' ) ) {
				continue;
			} else {
				$receipted_date = $usces->get_order_meta_value( 'receipted_date', $row['ID'] );
				$carts          = $row['cart'];
				foreach ( $carts as $cart ) {
					if ( (inT) $post_id === (inT) $cart['post_id'] ) {
						if ( $receipted_date ) {
							$firstdate = $receipted_date;
						} else {
							$firstdate = $row['order_date'];
						}
						break 2;
					}
				}
			}
		}
	}

	if ( ! $firstdate ) {
		$res = array(
			'firstdate' => null,
			'lastdate'  => null,
		);
	} else {
		$item = $usces->get_item( $post_id );
		if ( empty( $item['dlseller_validity'] ) ) {
			$res = array(
				'firstdate'  => ( mysql2date( __( 'Y/m/d' ), $firstdate ) ),
				'lastdate'   => null,
				'firstdates' => date_i18n( 'Y/m/d', strtotime( $firstdate ) ),
				'lastdates'  => '',
			);
		} else {
			$t        = getdate( strtotime( $firstdate ) );
			$hour     = empty( $t['hour'] ) ? 0 : $t['hour'];
			$min      = empty( $t['minutes'] ) ? 0 : $t['minutes'];
			$sec      = empty( $t['seconds'] ) ? 0 : $t['seconds'];
			$month    = empty( $t['mon'] ) ? 0 : $t['mon'];
			$day      = empty( $t['mday'] ) ? 0 : $t['mday'];
			$year     = empty( $t['year'] ) ? 0 : $t['year'];
			$lastdate = date_i18n( 'Y-m-d H:i:s', mktime( $hour, $min, $sec, $month, $day + $item['dlseller_validity'], $year ) );
			$res      = array(
				'firstdate'  => ( mysql2date( __( 'Y/m/d' ), $firstdate ) ),
				'lastdate'   => ( mysql2date( __( 'Y/m/d' ), $lastdate ) ),
				'firstdates' => date_i18n( 'Y/m/d', strtotime( $firstdate ) ),
				'lastdates'  => ( date_i18n( 'Y/m/d', strtotime( $lastdate ) ) ),
			);
		}
	}
	return $res;
}

/**
 * Password check
 *
 * @param string $password Password.
 * @return string
 */
function dlseller_get_pwd_errors( $password ) {
	if ( WCUtils::is_blank( $password ) ) {
		return __( 'Please enter a password.', 'usces' ) . '<br />';
	}

	$options = get_option( 'usces' );
	$ret     = '';

	if ( isset( $options['system'] ) ) {
		$system_option = $options['system'];

		$pwd_rule_min = $system_option['member_pass_rule_min'];
		$pwd_rule_max = empty( $system_option['member_pass_rule_max'] ) ? 30 : $system_option['member_pass_rule_max'];
		if ( strlen( $password ) < $pwd_rule_min || strlen( $password ) > $pwd_rule_max ) {
			if ( $pwd_rule_min === $pwd_rule_max ) {
				$rule = sprintf( __( '%s characters long', 'usces' ), $pwd_rule_min );
			} else {
				$rule = sprintf( __( '%1$s characters and no more than %2$s characters', 'usces' ), $pwd_rule_min, $pwd_rule_max );
			}
			$ret .= sprintf( __( 'Password must be at least %s.', 'usces' ), $rule ) . '<br />';
		}

		if ( isset( $system_option['member_pass_rule_upercase'] ) ) {
			if ( ! preg_match( '@[A-Z]@', $password ) && $system_option['member_pass_rule_upercase'] ) {
				$ret .= __( 'Password must contain at least one upper-case alphabetics character.', 'usces' ) . '<br />';
			}
		}
		if ( isset( $system_option['member_pass_rule_lowercase'] ) ) {
			if ( ! preg_match( '@[a-z]@', $password ) && $system_option['member_pass_rule_lowercase'] ) {
				$ret .= __( 'Password must contain at least one lower-case alphabetics character.', 'usces' ) . '<br />';
			}
		}
		if ( isset( $system_option['member_pass_rule_digit'] ) ) {
			if ( ! preg_match( '@[0-9]@', $password ) && $system_option['member_pass_rule_digit'] ) {
				$ret .= __( 'Password must contain at least one numeric character.', 'usces' ) . '<br />';
			}
		}
		if ( isset( $system_option['member_pass_rule_symbol'] ) ) {
			if ( ! preg_match( '@[\W]@', $password ) && $system_option['member_pass_rule_symbol'] ) {
				$ret .= __( 'Password must contain at least one symbolic character.', 'usces' ) . '<br />';
			}
		}
	}

	return $ret;
}

/**
 * Member input validation check
 * usces_filter_member_check
 *
 * @param string $mes Error message.
 * @return string
 */
function dlseller_member_check( $mes ) {
	global $usces;

	$mes = '';

	$usces_member_old = $_SESSION['usces_member'];
	foreach ( $_POST['member'] as $key => $vlue ) {
		if ( 'password1' !== $key && 'password2' !== $key ) {
			$_SESSION['usces_member'][ $key ] = trim( $vlue );
		}
	}

	if ( 'newmemberform' === wp_unslash( $_POST['member_regmode'] ) || ( 'editmemberform' === wp_unslash( $_POST['member_regmode'] ) && ! ( WCUtils::is_blank( $_POST['member']['password1'] ) && WCUtils::is_blank( $_POST['member']['password2'] ) ) ) ) {
		$mes = dlseller_get_pwd_errors( $_POST['member']['password1'] );
	}

	if ( trim( $_POST['member']['password1'] ) !== trim( $_POST['member']['password2'] ) ) {
		$mes .= __( 'Password confirm does not match.', 'usces' ) . '<br />';
	}

	if ( 'editmemberform' === wp_unslash( $_POST['member_regmode'] ) ) {
		if ( ! is_email( $_POST['member']['mailaddress1'] ) || WCUtils::is_blank( $_POST['member']['mailaddress1'] ) ) {
			$mes .= __( 'e-mail address is not correct', 'usces' ) . '<br />';
		} else {
			$usces->get_current_member();
			$mem_id = $usces->current_member['id'];
			$id     = $usces->check_member_email( $_POST['member']['mailaddress1'] );
			if ( ! empty( $id ) && $id != $mem_id ) {
				$mes .= __( 'This e-mail address can not be registered.', 'usces' ) . '<br />';
			}
		}
	} else {
		if ( ! is_email( $_POST['member']['mailaddress1'] ) || WCUtils::is_blank( $_POST['member']['mailaddress1'] ) || WCUtils::is_blank( $_POST['member']['mailaddress2'] ) || trim( $_POST['member']['mailaddress1'] ) !== trim( $_POST['member']['mailaddress2'] ) ) {
			$mes .= __( 'e-mail address is not correct', 'usces' ) . '<br />';
		} else {
			$id = $usces->check_member_email( $_POST['member']['mailaddress1'] );
			if ( ! empty( $id ) ) {
				$mes .= __( 'This e-mail address can not be registered.', 'usces' ) . '<br />';
			}
		}
	}
	if ( WCUtils::is_blank( $_POST['member']['name1'] ) ) {
		$mes .= __( 'Name is not correct', 'usces' ) . '<br />';
	}

	$dlseller_opts = get_option( 'dlseller' );
	if ( isset( $dlseller_opts['dlseller_member_reinforcement'] ) && 'on' === $dlseller_opts['dlseller_member_reinforcement'] ) {
		$zip_check   = false;
		$addressform = $usces->options['system']['addressform'];
		$applyform   = usces_get_apply_addressform( $addressform );
		if ( 'JP' === $applyform ) {
			if ( isset( $_POST['member']['country'] ) ) {
				if ( 'JP' === $_POST['member']['country'] ) {
					$zip_check = true;
				}
			} else {
				$base = usces_get_base_country();
				if ( 'JP' === $base ) {
					$zip_check = true;
				}
			}
		}
		$zip_check = apply_filters( 'usces_filter_zipcode_check', $zip_check );
		if ( $zip_check ) {
			if (  WCUtils::is_blank( $_POST['member']['zipcode'] ) ) {
				if ( usces_is_required_field( 'zipcode' ) ) {
					$mes .= __( 'postal code is not correct', 'usces' ) . '<br />';
				}
			} else {
				if ( ! preg_match( '/^[a-zA-Z0-9]+$/', $_POST['member']['zipcode'] ) ) {
					$_SESSION['usces_member']['zipcode'] = usces_convert_zipcode( $_POST['member']['zipcode'] );
				}
				if ( ! preg_match( '/^(([0-9]{3}-[0-9]{4})|([0-9]{7}))$/', $_SESSION['usces_member']['zipcode'] ) ) {
					$mes .= __( 'postal code is not correct', 'usces' ) . '<br />';
				}
			}
		}
		if ( __( '-- Select --', 'usces' ) === wp_unslash( $_POST['member']['pref'] ) || '-- Select --' === wp_unslash( $_POST['member']['pref'] ) ) {
			$mes .= __( 'enter the prefecture', 'usces' ) . '<br />';
		}
		if ( WCUtils::is_blank( $_POST['member']['address1'] ) ) {
			$mes .= __( 'enter the city name', 'usces' ) . '<br />';
		}
		if ( WCUtils::is_blank( $_POST['member']['address2'] ) ) {
			$mes .= __( 'enter house numbers', 'usces' ) . '<br />';
		}
		if ( WCUtils::is_blank( $_POST['member']['tel'] ) ) {
			$mes .= __( 'enter phone numbers', 'usces' ) . '<br />';
		}
		if ( ! WCUtils::is_blank( $_POST['member']['tel'] ) && preg_match( '/[^\d\-+]/', trim( wp_unslash( $_POST['member']['tel'] ) ) ) ) {
			$mes .= __( 'Please input a phone number with a half size number.', 'usces' ) . '<br />';
		}
	}

	if ( 'editmemberform' !== wp_unslash( $_POST['member_regmode'] ) && isset( $usces->options['agree_member'] ) && 'activate' === $usces->options['agree_member'] ) {
		if ( ! isset( $_POST['agree_member_check'] ) ) {
			$mes .= __( 'Please accept the membership agreement.', 'usces' ) . '<br />';
		}
	}

	$mes = usces_filter_member_check_custom_member( $mes );
	$mes = apply_filters( 'dlseller_filter_member_check', $mes );

	if ( 'editmemberform' === wp_unslash( $_POST['member_regmode'] ) && '' !== $mes ) {
		$_SESSION['usces_member'] = $usces_member_old;
	}

	return $mes;
}

/**
 * Customer inpit validation check
 * usces_filter_customer_check
 *
 * @param string $mes Error message.
 * @return string
 */
function dlseller_customer_check( $mes ) {
	global $usces;

	if ( dlseller_have_shipped() ) {
		$mes = '';

		if ( ! is_email( $_POST['customer']['mailaddress1'] ) || WCUtils::is_blank( $_POST['customer']['mailaddress1'] ) || WCUtils::is_blank( $_POST['customer']['mailaddress2'] ) || trim( $_POST['customer']['mailaddress1'] ) !== trim( $_POST['customer']['mailaddress2'] ) ) {
			$mes .= __( 'e-mail address is not correct', 'usces' ) . '<br />';
		}
		if ( WCUtils::is_blank( $_POST['customer']['name1'] ) ) {
			$mes .= __( 'Name is not correct', 'usces' ) . '<br />';
		}
		$zip_check   = false;
		$addressform = $usces->options['system']['addressform'];
		$applyform   = usces_get_apply_addressform( $addressform );
		if ( 'JP' === $applyform ) {
			if ( isset( $_POST['customer']['country'] ) ) {
				if ( 'JP' === $_POST['customer']['country'] ) {
					$zip_check = true;
				}
			} else {
				$base = usces_get_base_country();
				if ( 'JP' === $base ) {
					$zip_check = true;
				}
			}
		}
		$zip_check = apply_filters( 'usces_filter_zipcode_check', $zip_check );
		if ( $zip_check ) {
			if ( WCUtils::is_blank( $_POST['customer']['zipcode'] ) ) {
				if ( usces_is_required_field( 'zipcode' ) ) {
					$mes .= __( 'postal code is not correct', 'usces' ) . '<br />';
				}
			} else {
				if ( ! preg_match( '/^[a-zA-Z0-9]+$/', $_POST['customer']['zipcode'] ) ) {
					$_SESSION['usces_entry']['customer']['zipcode'] = usces_convert_zipcode( $_POST['customer']['zipcode'] );
				}
				if ( ! preg_match( '/^(([0-9]{3}-[0-9]{4})|([0-9]{7}))$/', $_SESSION['usces_entry']['customer']['zipcode'] ) ) {
					$mes .= __( 'postal code is not correct', 'usces' ) . '<br />';
				}
			}
		}
		if ( __( '-- Select --', 'usces' ) === wp_unslash( $_POST['customer']['pref'] ) || '-- Select --' === wp_unslash( $_POST['customer']['pref'] ) ) {
			$mes .= __( 'enter the prefecture', 'usces' ) . '<br />';
		}
		if ( WCUtils::is_blank( $_POST['customer']['address1'] ) ) {
			$mes .= __( 'enter the city name', 'usces' ) . '<br />';
		}
		if ( WCUtils::is_blank( $_POST['customer']['address2'] ) ) {
			$mes .= __( 'enter house numbers', 'usces' ) . '<br />';
		}
		if ( WCUtils::is_blank( $_POST['customer']['tel'] ) ) {
			$mes .= __( 'enter phone numbers', 'usces' ) . '<br />';
		}
		if ( ! WCUtils::is_blank( $_POST['customer']['tel'] ) && preg_match( '/[^\d\-+]/', trim( wp_unslash( $_POST['customer']['tel'] ) ) ) ) {
			$mes .= __( 'Please input a phone number with a half size number.', 'usces' ) . '<br />';
		}
	}

	$mes = usces_filter_customer_check_custom_customer( $mes );
	$mes = apply_filters( 'dlseller_filter_customer_check', $mes );

	return $mes;
}

/**
 * Delivery inpit validation check
 * usces_filter_delivery_check
 *
 * @param string $mes Error message.
 * @return string
 */
function dlseller_delivery_check( $mes ) {
	global $usces;

	$mes     = '';
	$ses     = '';
	$entries = $usces->cart->get_entry();

	if ( dlseller_have_shipped() ) {

		if ( isset( $_POST['delivery']['delivery_flag']) && 1 === (int) wp_unslash( $_POST['delivery']['delivery_flag'] ) ) {
			if ( WCUtils::is_blank( $_POST['delivery']['name1'] ) ) {
				$mes .= __( 'Name is not correct', 'usces' );
			}
			$zip_check   = false;
			$addressform = $usces->options['system']['addressform'];
			$applyform   = usces_get_apply_addressform( $addressform );
			if ( 'JP' === $applyform ) {
				if ( isset( $_POST['delivery']['country'] ) ) {
					if ( 'JP' === $_POST['delivery']['country'] ) {
						$zip_check = true;
					}
				} else {
					$base = usces_get_base_country();
					if ( 'JP' === $base ) {
						$zip_check = true;
					}
				}
			}
			$zip_check = apply_filters( 'usces_filter_zipcode_check', $zip_check );
			if ( $zip_check ) {
				if ( WCUtils::is_blank( $_POST['delivery']['zipcode'] ) ) {
					if ( usces_is_required_field( 'zipcode' ) ) {
						$mes .= __( 'postal code is not correct', 'usces' ) . '<br />';
					}
				} else {
					if ( ! preg_match( '/^[a-zA-Z0-9]+$/', $_POST['delivery']['zipcode'] ) ) {
						$_SESSION['usces_entry']['delivery']['zipcode'] = usces_convert_zipcode( $_POST['delivery']['zipcode'] );
					}
					if ( ! preg_match( '/^(([0-9]{3}-[0-9]{4})|([0-9]{7}))$/', $_SESSION['usces_entry']['delivery']['zipcode'] ) ) {
						$mes .= __( 'postal code is not correct', 'usces' ) . '<br />';
					}
				}
			}
			if ( __( '-- Select --', 'usces' ) === wp_unslash( $_POST['delivery']['pref'] ) || '-- Select --' === wp_unslash( $_POST['delivery']['pref'] ) ) {
				$mes .= __( 'enter the prefecture', 'usces' ) . '<br />';
			}
			if ( WCUtils::is_blank( $_POST['delivery']['address1'] ) ) {
				$mes .= __( 'enter the city name', 'usces' ) . '<br />';
			}
			if ( WCUtils::is_blank( $_POST['delivery']['address2'] ) ) {
				$mes .= __( 'enter house numbers', 'usces' ) . '<br />';
			}
			if ( WCUtils::is_blank( $_POST['delivery']['tel'] ) ) {
				$mes .= __( 'enter phone numbers', 'usces' ) . '<br />';
			}
		} else {
			if ( empty( $entries['customer']['name1'] ) ) {
				$ses .= __( 'Name is not correct', 'usces' );
			}
			if ( empty( $entries['customer']['zipcode'] ) ) {
				$ses .= __( 'postal code is not correct', 'usces' ) . '<br />';
			}
			if ( __( '-- Select --', 'usces' ) === $entries['customer']['pref'] ) {
				$ses .= __( 'enter the prefecture', 'usces' ) . '<br />';
			}
			if ( empty( $entries['customer']['address1'] ) ) {
				$ses .= __( 'enter the city name', 'usces' ) . '<br />';
			}
			if ( empty( $entries['customer']['address2'] ) ) {
				$ses .= __( 'enter house numbers', 'usces' ) . '<br />';
			}
			if ( empty( $entries['customer']['tel'] ) ) {
				$ses .= __( 'enter phone numbers', 'usces' ) . '<br />';
			}

			if ( ! empty( $ses ) ) {
				$_SESSION['usces_entry']['delivery']['delivery_flag'] = 1;
				$mes                                                 .= $ses;
			}
		}
		if ( ! isset( $_POST['offer']['delivery_method'] ) || ( empty( $_POST['offer']['delivery_method'] ) && ! WCUtils::is_zero( $_POST['offer']['delivery_method'] ) ) ) {
			$mes .= __( 'chose one from delivery method.', 'usces' ) . '<br />';
		} else {
			$d_method_index = $usces->get_delivery_method_index( (int) $_POST['offer']['delivery_method'] );
			$country        = $_SESSION['usces_entry']['delivery']['country'];
			$local_country  = usces_get_base_country();
			if ( $country === $local_country ) {
				if ( 1 === (int) $usces->options['delivery_method'][ $d_method_index ]['intl'] ) {
					$mes .= __( 'Delivery method is incorrect. Can not specify an international flight.', 'usces' ) . '<br />';
				}
			} else {
				if ( WCUtils::is_zero( $usces->options['delivery_method'][ $d_method_index ]['intl'] ) ) {
					$mes .= __( 'Delivery method is incorrect. Specify the international flights.', 'usces' ) . '<br />';
				}
			}
		}
	}

	if ( ! isset( $_POST['offer']['payment_name'] ) ) {
		$mes .= __( 'chose one from payment options.', 'usces' ) . '<br />';
	} else {
		if ( dlseller_have_shipped() ) {
			$payments = $usces->getPayments( $_POST['offer']['payment_name'] );
			if ( 'COD' === $payments['settlement'] ) {
				$total_items_price = $usces->get_total_price();
				$usces_entries     = $usces->cart->get_entry();
				$materials         = array(
					'total_items_price' => $usces_entries['order']['total_items_price'],
					'discount'          => $usces_entries['order']['discount'],
					'shipping_charge'   => $usces_entries['order']['shipping_charge'],
					'cod_fee'           => $usces_entries['order']['cod_fee'],
					'use_point'         => ( isset( $usces_entries['order']['use_point'] ) ) ? $usces_entries['order']['use_point'] : 0,
				);
				$tax               = $usces->getTax( $total_items_price, $materials );
				$total_items_price = $total_items_price + $tax;
				$cod_limit_amount  = ( isset( $usces->options['cod_limit_amount'] ) && 0 < (int) $usces->options['cod_limit_amount'] ) ? $usces->options['cod_limit_amount'] : 0;
				if ( 0 < $cod_limit_amount && $total_items_price > $cod_limit_amount ) {
					$mes .= sprintf( __( 'A total products amount of money surpasses the upper limit(%s) that I can purchase in C.O.D.', 'usces' ), usces_crform( $usces->options['cod_limit_amount'], true, false, 'return' ) ) . '<br />';
				}
			}
		}
	}
	if ( dlseller_have_shipped() ) {
		if ( isset( $d_method_index ) && isset( $payments ) ) {
			if ( 1 === (int) $usces->options['delivery_method'][ $d_method_index ]['nocod'] ) {
				if ( 'COD' === $payments['settlement'] ) {
					$mes .= __( 'COD is not available.', 'usces' ) . '<br />';
				}
			}
		}
	}
	$mes = usces_filter_delivery_check_custom_delivery( $mes );
	$mes = usces_filter_delivery_check_custom_order( $mes );
	$mes = apply_filters( 'dlseller_filter_delivery_check', $mes );

	if ( ! isset( $_POST['offer']['terms'] ) && dlseller_have_dlseller_content() && dlseller_has_terms() ) {
		$mes .= __( 'Not agree', 'dlseller' ) . '<br />';
	}

	return $mes;
}

/**
 * Preprocessing
 * usces_pre_reg_orderdata
 */
function dlseller_pre_reg_orderdata() {
	if ( ! dlseller_have_shipped() ) {
		$_SESSION['usces_entry']['order']['delivery_method'] = -1;
	}
}

/**
 * Enqueue style
 * wp_enqueue_scripts
 */
function add_dlseller_stylesheet() {
	$dlseller_style_url  = USCES_WP_PLUGIN_URL . '/wcex_dlseller/dlseller.css';
	$dlseller_style_file = USCES_WP_PLUGIN_DIR . '/wcex_dlseller/dlseller.css';
	if ( file_exists( $dlseller_style_file ) ) {
		wp_register_style( 'dlsellerStyleSheets', $dlseller_style_url );
		wp_enqueue_style( 'dlsellerStyleSheets' );
	}
}

/**
 * Validity
 *
 * @param object $post WP_Post.
 * @return string
 */
function usces_dlseller_validity( $post ) {
	$validity = usces_get_itemMeta( '_dlseller_validity', $post->ID, 'return' );
	if ( empty( $validity ) ) {
		$res = __( 'No limit', 'dlseller' );
	} else {
		$res = $validity;
	}
	$res = apply_filters( 'dlseller_filter_data_validity', $res, $validity, $post );

	return $res;
}

/**
 * Get download count
 *
 * @param int    $post_id Post ID.
 * @param string $piriod  Piriod.
 * @param int    $mon     Month.
 * @return array
 */
function usces_dlseller_get_dlcount( $post_id, $piriod = 'total', $mon = 0 ) {
	global $usces;

	$today = current_time( 'Y-m-d' );
	switch ( $piriod ) {
		case 'today':
			$startday = $today;
			$endday   = $today;
			break;
		case 'month':
			if ( $mon ) {
				$startday = $mon . '01';
				$endday   = date_i18n( 'Y-m-d', mktime( 0, 0, 0, ( substr( $mon, 5, 2 ) + 1 ), 0, substr( $mon, 0, 4 ) ) );
			} else {
				$startday = substr( $today, 0, 8 ) . '01';
				$endday   = date_i18n( 'Y-m-d', mktime( 0, 0, 0, ( substr( $today, 5, 2 ) + 1 ), 0, substr( $today, 0, 4 ) ) );
			}
			break;
		case 'total':
			$startday = '2000-01-01';
			$endday   = $today;
			break;
	}
	$data = $usces->get_access_piriod( 'dlseller', 'count', $startday, $endday );
	if ( false === $data ) {
		$res = null;
	} else {
		$res = array(
			'par' => 0,
			'dl'  => 0,
		);
		$par = 0;
		$dl  = 0;
		foreach ( $data as $values ) {
			$vals = unserialize( $values['acc_value'] );
			foreach ( (array) $vals as $key => $dls ) {
				if ( (int) $key === (int) $post_id ) {
					$par += $dls['par'];
					$dl  += $dls['dl'];
				}
			}
		}
		$res = array(
			'par' => $par,
			'dl'  => $dl,
		);
	}
	return $res;
}

/**
 * Update download count
 *
 * @param int $post_id Post ID.
 * @param int $par     Count.
 * @param int $dl      Download.
 * @return array
 */
function usces_dlseller_update_dlcount( $post_id, $par = 0, $dl = 0 ) {
	global $usces;

	$today  = current_time( 'Y-m-d' );
	$values = $usces->get_access( 'dlseller', 'count', $today );
	if ( null === $values || ! isset( $values[ $post_id ] ) ) {
		$values[ $post_id ]['par'] = $par;
		$values[ $post_id ]['dl']  = $dl;
	} else {
		$values[ $post_id ]['par'] += $par;
		$values[ $post_id ]['dl']  += $dl;
	}

	$array              = array();
	$array['acc_key']   = 'dlseller';
	$array['acc_type']  = 'count';
	$array['acc_value'] = $values;
	$array['acc_date']  = $today;

	$usces->update_access( $array );

	do_action( 'action_dlseller_update_dlcount', $post_id, $array );
}

/**
 * Contents filename
 *
 * @param object $post WP_Post.
 * @param string $sku  SKU code.
 * @return string
 */
function usces_dlseller_filename( $post, $sku = '' ) {
	if ( is_object( $post ) ) {
		$post_id = $post->ID;
	} else {
		$post_id = $post;
	}
	$path = dlseller_get_filename( $post_id, $sku );
	if ( empty( $path ) ) {
		$res = '';
	} else {
		$res = basename( $path );
	}

	return $res;
}

/**
 * Registration of order data
 * usces_action_reg_orderdata
 *
 * @param string $args {
 *     The array of order data.
 *     @type array  $cart          Cart data.
 *     @type array  $entry         Entry data.
 *     @type int    $order_id      Order ID.
 *     @type int    $member_id     Member ID.
 *     @type array  $payments      Payment data.
 *     @type int    $charging_type Charging type.
 *     @type array  $results       Results data.
 * }
 */
function dlseller_action_reg_orderdata( $args ) {
	global $usces, $wpdb;

	if ( ! isset( $args['charging_type'] ) || 'continue' !== $args['charging_type'] ) {
		return;
	}

	extract( $args );

	$order_data = $usces->get_order_data( $order_id );
	$startdate  = dlseller_first_charging( $cart[0]['post_id'] );
	$usces_item = $usces->get_item( $cart[0]['post_id'] );
	$frequency  = ( ! empty( $usces_item['item_frequency'] ) ) ? (int) $usces_item['item_frequency'] : 0;
	if ( ! empty( $usces_item['item_chargingday'] ) ) {
		if ( 99 === (int) $usces_item['item_chargingday'] ) {
			$chargingday = intval( substr( $startdate, 8, 2 ) );
		} else {
			$chargingday = (int) $usces_item['item_chargingday'];
		}
	} else {
		$chargingday = intval( substr( $startdate, 8, 2 ) );
	}
	$interval = ( ! empty( $usces_item['dlseller_interval'] ) ) ? (int) $usces_item['dlseller_interval'] : null;

	$continuation_table = $wpdb->prefix . 'usces_continuation';
	$query              = $wpdb->prepare( "INSERT INTO {$continuation_table} ( `con_order_id`, `con_member_id`, `con_division`, `con_post_id`, `con_sku`, `con_acting`, `con_order_price`, `con_price`, `con_frequency`, `con_chargingday`, `con_interval`, `con_next_charging`, `con_next_contracting`, `con_startdate`, `con_condition`, `con_status`) VALUES
		( %d, %d, %s, %d, %s, %s, %f, %f, %d, %d, %d, %s, %s, %s, %s, %s )",
		$order_id,
		$member_id,
		$usces_item['item_division'],
		$cart[0]['post_id'],
		$cart[0]['sku'],
		$payments['settlement'],
		$order_data['end_price'],
		$order_data['end_price'],
		$frequency,
		$chargingday,
		$interval,
		'',
		'',
		$startdate,
		'',
		'continuation'
	);
	$res = $wpdb->query( $query );

	if ( false === $res ) {
		$con_id = false;
		usces_log( 'dlseller_action_reg_orderdata : ' . $wpdb->last_error, 'database_error.log' );
	} else {
		$con_id = $wpdb->insert_id;
	}
	$args['con_id'] = $con_id;
	do_action( 'dlseller_action_reg_continuationdata', $args );
}

/**
 * Deletion of order data
 * usces_action_del_orderdata
 *
 * @param object $obj Order data.
 */
function dlseller_action_del_orderdata( $obj ) {
	global $usces, $wpdb;

	if ( ! $obj ) {
		return;
	}

	$continuation_table      = $wpdb->prefix . 'usces_continuation';
	$continuation_meta_table = $wpdb->prefix . 'usces_continuation_meta';
	$query                   = $wpdb->prepare( "SELECT con_id FROM {$continuation_table} WHERE con_order_id = %d", $obj->ID );
	$res                     = $wpdb->get_results( $query, ARRAY_A );
	foreach ( $res as $con_id ) {
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$continuation_meta_table} WHERE `con_id` = %d", $con_id ) );
	}
	$query = $wpdb->prepare( "DELETE FROM {$continuation_table} WHERE `con_order_id` = %d", $obj->ID );
	$res   = $wpdb->query( $query );
}

/**
 * Update order data
 * usces_action_update_orderdata
 *
 * @param object $new_orderdata New data.
 * @param string $old_status    Old status.
 * @param object $old_orderdata Old data.
 * @param array  $new_cart      New cart data.
 * @param array  $old_cart      Old cart data.
 */
function dlseller_action_update_orderdata( $new_orderdata, $old_status, $old_orderdata, $new_cart, $old_cart ) {
	global $usces, $wpdb;

	if ( ! $new_orderdata ) {
		return;
	}
	if ( ! dlseller_have_continue_charge( $new_cart ) ) {
		return;
	}

	$member_id        = $new_orderdata->mem_id;
	$total_full_price = $usces->get_total_price( $new_cart ) - $new_orderdata->order_usedpoint + $new_orderdata->order_discount + $new_orderdata->order_shipping_charge + $new_orderdata->order_tax;
	if ( 0 > $total_full_price ) {
		$total_full_price = 0;
	}

	$continuation_table = $wpdb->prefix . 'usces_continuation';
	$sel_query          = $wpdb->prepare( "SELECT * FROM {$continuation_table} WHERE `con_order_id` = %d", $new_orderdata->ID );
	$continue_data      = $wpdb->get_row( $sel_query, ARRAY_A );
	$usces_item         = $usces->get_item( $new_cart[0]['post_id'] );

	$con_id = $continue_data['con_id'];
	$status = ( false !== strpos( $new_orderdata->order_status, 'cancel' ) ) ? 'cancellation' : 'continuation';
	if ( false !== strpos( $new_orderdata->order_status, 'completion' ) || false !== strpos( $new_orderdata->order_status, 'continuation' ) ) {
		if ( 'update' === wp_unslash( $_POST['up_modified'] ) ) {
			$startdate = $new_orderdata->order_modified;
		} else {
			$startdate = $continue_data['con_startdate'];
		}
	} else {
		$startdate = ( empty( $continue_data['con_startdate'] ) ) ? $new_orderdata->order_date : $continue_data['con_startdate'];
	}
	$payments  = $usces->getPayments( $new_orderdata->order_payment_name );
	$frequency = ( ! empty( $usces_item['item_frequency'] ) ) ? (int) $usces_item['item_frequency'] : 0;
	if ( ! empty( $usces_item['item_chargingday'] ) ) {
		if ( 99 === (int) $usces_item['item_chargingday'] ) {
			$chargingday = intval( substr( $startdate, 8, 2 ) );
		} else {
			$chargingday = (int) $usces_item['item_chargingday'];
		}
	} else {
		$chargingday = intval( substr( $startdate, 8, 2 ) );
	}
	$interval = ( ! empty( $usces_item['dlseller_interval'] ) ) ? (int) $usces_item['dlseller_interval'] : null;

	$upd_query = $wpdb->prepare( "UPDATE {$continuation_table} SET 
		`con_post_id` = %d, 
		`con_sku` = %s, 
		`con_acting` = %s, 
		`con_price` = %f, 
		`con_frequency` = %d, 
		`con_chargingday` = %d, 
		`con_interval` = %d, 
		`con_startdate` = %s, 
		`con_status` = %s 
		WHERE `con_id` = %d",
		$new_cart[0]['post_id'],
		$new_cart[0]['sku_code'],
		$payments['settlement'],
		$total_full_price,
		$frequency,
		$chargingday,
		$interval,
		$startdate,
		$status,
		$con_id
	);
	$res = $wpdb->query( $upd_query );

	$limit = $usces->get_member_meta_value( 'limitofcard', $member_id );
	if ( $limit ) {
		$lmitparts = explode( '/', $limit );
		if ( 4 === strlen( $lmitparts[0] ) ) {
			$limit = $lmitparts[1] . '/' . $lmitparts[0];
			$usces->set_member_meta_value( 'limitofcard', $limit, $member_id );
		}
	}
}

/**
 * Undocumented function
 * yoast-ga-push-after-pageview
 *
 * @param array $push
 * @return array
 */
function dlseller_trackPageview_redownload( $push ) {
	$push[] = "'_trackPageview','/wc_redownload'";
	return $push;
}

/**
 * Attachment uli incontent
 *
 * @param string $content Content.
 * @return string
 */
function usces_ssl_attachment_uli_incontent( $content ) {
	global $usces;
	if ( $usces->is_cart_or_member_page( $_SERVER['REQUEST_URI'] ) || $usces->is_inquiry_page( $_SERVER['REQUEST_URI'] ) ) {
		$content = str_replace( ( 'src="' . get_option( 'siteurl' ) ), ( 'src="' . USCES_SSL_URL_ADMIN ), $content );
	}
	return $content;
}

/**
 * Check Add to Cart
 * usces_filter_js_intoCart
 *
 * @param string $js      Script form.
 * @param int    $post_id Post ID.
 * @return string
 */
function dlseller_filter_js_intoCart( $js, $post_id ) {
	global $usces;

	$division      = dlseller_get_division( $post_id );
	$charging_type = $usces->getItemChargingType( $post_id );

	if ( false !== $usces->cart->num_row() && 'continue' === $charging_type ) {
		if ( dlseller_have_continue_charge() ) {
			$js  = "if(confirm('" . __( 'You can add only one continuation charging item in your shopping cart.', 'dlseller' ) . "')){\n";
			$js .= "return true;\n";
			$js .= "}else{\n";
			$js .= "return false;\n";
			$js .= "}\n";
		} else {
			$js  = "if(confirm('" . __( 'You have the continuation charging item in your shopping cart. If you want to add this item, you have to clear the item in your cart. Is it ok to clear your cart?', 'dlseller' ) . "')){\n";
			$js .= "return true;\n";
			$js .= "}else{\n";
			$js .= "return false;\n";
			$js .= "}\n";
		}
	} elseif ( false !== $usces->cart->num_row() ) {
		if ( dlseller_have_continue_charge() ) {
			$js  = "if(confirm('" . __( 'This is the continuation charging item. If you want to add this item, you have to clear your cart. Is it ok to clear your cart?', 'dlseller' ) . "')){\n";
			$js .= "return true;\n";
			$js .= "}else{\n";
			$js .= "return false;\n";
			$js .= "}\n";
		}
	}

	return $js;
}

/**
 * Modified label
 * usces_filter_admin_modified_label
 *
 * @return string
 */
function dlseller_filter_admin_modified_label() {
	return __( 'Modified', 'dlseller' );
}

/**
 * Confirm prebutton value
 * usces_filter_confirm_prebutton_value
 *
 * @return string
 */
function dlseller_filter_confirm_prebutton_value() {
	return __( 'Back', 'usces' );
}

/**
 * Mail message
 * usces_filter_order_confirm_mail_meisai
 * usces_filter_send_order_mail_meisai
 *
 * @param string $meisai Mail message.
 * @param array  $data   Order data.
 * @param array  $cart   Cart data.
 * @return string
 */
function dlseller_filter_order_mail_meisai( $meisai, $data, $cart ) {
	global $usces, $wpdb;

	if ( empty( $data['ID'] ) ) {
		return $meisai;
	}
	$order_id = $data['ID'];
	// $cart = usces_get_ordercartdata( $order_id );
	if ( ! is_array( $cart ) || 0 == count( $cart ) ) {
		return $meisai;
	}
	$cart_row = current( $cart );
	// $post_id = $cart[0]['post_id'];
	$post_id       = $cart_row['post_id'];
	$charging_type = $usces->getItemChargingType( $post_id );
	if ( 'continue' !== $charging_type ) {
		return $meisai;
	}

	if ( function_exists( 'usces_is_html_mail' ) && usces_is_html_mail() ) {
		$meisai = dlseller_filter_order_mail_meisai_htmlbody( $meisai, $data, $cart );
	} else {
		$condition       = unserialize( $data['order_condition'] );
		$tax_display     = ( isset( $condition['tax_display'] ) ) ? $condition['tax_display'] : usces_get_tax_display();
		$reduced_taxrate = ( isset( $condition['applicable_taxrate'] ) && 'reduced' === $condition['applicable_taxrate'] ) ? true : false;
		if ( 'activate' === $tax_display && $reduced_taxrate ) {
			$usces_tax = Welcart_Tax::get_instance();
		}
		$member_system       = ( isset( $condition['membersystem_state'] ) ) ? $condition['membersystem_state'] : $usces->options['membersystem_state'];
		$member_system_point = ( isset( $condition['membersystem_point'] ) ) ? $condition['membersystem_point'] : $usces->options['membersystem_point'];
		$tax_mode            = ( isset( $condition['tax_mode'] ) ) ? $condition['tax_mode'] : usces_get_tax_mode();
		$tax_target          = ( isset( $condition['tax_target'] ) ) ? $condition['tax_target'] : usces_get_tax_target();
		$point_coverage      = ( isset( $condition['point_coverage'] ) ) ? $condition['point_coverage'] : usces_point_coverage();

		// $cart_row = $cart[0];
		$sku          = $cart_row['sku'];
		$sku_code     = urldecode( $sku );
		$quantity     = $cart_row['quantity'];
		$options      = ( empty( $cart_row['options'] ) ) ? array() : $cart_row['options'];
		$cartItemName = $usces->getCartItemName( $post_id, $sku_code );
		$skuPrice     = $cart_row['price'];
		$item_custom  = usces_get_item_custom( $post_id, 'notag', 'return' );
		$usces_item   = $usces->get_item( $post_id );
		$args         = compact( 'cart', 'cart_row', 'post_id', 'sku' );

		$meisai  = "\r\n" . __( 'Items', 'usces' ) . " : \r\n";
		$meisai .= usces_mail_line( 2, $data['order_email'] ); // --------------------
		$meisai .= apply_filters( 'usces_filter_cart_item_name_nl', $cartItemName, $args ) . "\r\n\r\n";
		if ( is_array( $options ) && count( $options ) > 0 ) {
			$optstr = '';
			foreach ( $options as $key => $value ) {
				if ( ! empty( $key ) ) {
					$key   = urldecode( $key );
					$value = maybe_unserialize( $value );
					if ( is_array( $value ) ) {
						$c       = '';
						$optstr .= $key . ' : ';
						foreach ( $value as $v ) {
							$optstr .= $c . rawurldecode( $v );
							$c       = ', ';
						}
						$optstr .= "\r\n";
					} else {
						$optstr .= $key . ' : ' . rawurldecode( $value ) . "\r\n";
					}
				}
			}
			$meisai .= apply_filters( 'usces_filter_option_adminmail', $optstr, $options, $cart_row );
		}
		$meisai .= apply_filters( 'usces_filter_advance_adminmail', '', $cart_row, $data );
		$meisai .= __( 'Unit price', 'usces' ) . ' ' . usces_crform( $skuPrice, true, false, 'return' ) . __( ' * ', 'usces' ) . $cart_row['quantity'] . "\r\n";
		$meisai .= usces_mail_line( 3, $data['order_email'] ); // ====================
		$meisai .= __( 'total items', 'usces' ) . ' : ' . usces_crform( $data['order_item_total_price'], true, false, 'return' ) . "\r\n";

		if ( defined( 'USCES_VERSION' ) && version_compare( USCES_VERSION, '1.5', '>=' ) ) {
			if ( 0.0 !== (float) $data['order_discount'] ) {
				$meisai .= apply_filters( 'usces_confirm_discount_label', __( 'Campaign discount', 'usces' ), $order_id ) . ' : ' . usces_crform( $data['order_discount'], true, false, 'return' ) . "\r\n";
			}
			if ( 'activate' === $tax_display && 'products' === $tax_target ) {
				if ( version_compare( USCES_VERSION, '2.8.15', '>=' ) ) {
					$meisai .= usces_mail_tax_label( $data );
					if ( 'exclude' === $tax_mode ) {
						$meisai .= ' : ' . usces_mail_tax( $data );
					}
				} else {
					$meisai .= usces_tax_label( $data, 'return' );
					if ( 'exclude' === $tax_mode ) {
						$meisai .= ' : ' . usces_tax( $data, 'return' );
					}
				}
				$meisai .= "\r\n";
			}
			if ( 'activate' === $member_system && 'activate' === $member_system_point && 0 === (int) $point_coverage && 0 !== (int) $data['order_usedpoint'] ) {
				$meisai .= __( 'use of points', 'usces' ) . ' : ' . number_format( $data['order_usedpoint'] ) . __( 'Points', 'usces' ) . "\r\n";
			}
			if ( dlseller_have_shipped( $cart ) ) {
				$meisai .= __( 'Shipping', 'usces' ) . ' : ' . usces_crform( $data['order_shipping_charge'], true, false, 'return' ) . "\r\n";
			}
			if ( 0 < $data['order_cod_fee'] ) {
				$meisai .= apply_filters( 'usces_filter_cod_label', __( 'COD fee', 'usces' ), $order_id ) . ' : ' . usces_crform( $data['order_cod_fee'], true, false, 'return' ) . "\r\n";
			}
			if ( 'activate' === $tax_display && 'all' === $tax_target ) {
				if ( version_compare( USCES_VERSION, '2.8.15', '>=' ) ) {
					$meisai .= usces_mail_tax_label( $data );
					if ( 'exclude' === $tax_mode ) {
						$meisai .= ' : ' . usces_mail_tax( $data );
					}
				} else {
					$meisai .= usces_tax_label( $data, 'return' );
					if ( 'exclude' === $tax_mode ) {
						$meisai .= ' : ' . usces_tax( $data, 'return' );
					}
				}
				$meisai .= "\r\n";
			}
			if ( 'activate' === $member_system && 'activate' === $member_system_point && 1 === (int) $point_coverage && 0 !== (int) $data['order_usedpoint'] ) {
				$meisai .= __( 'use of points', 'usces' ) . ' : ' . number_format( $data['order_usedpoint'] ) . __( 'Points', 'usces' ) . "\r\n";
			}
		} elseif ( defined( 'USCES_VERSION' ) && version_compare( USCES_VERSION, '1.4-beta', '>=' ) ) {
			if ( 0 !== (int) $data['order_discount'] ) {
				$meisai .= apply_filters( 'usces_confirm_discount_label', __( 'Campaign disnount', 'usces' ), $order_id ) . ' : ' . usces_crform( $data['order_discount'], true, false, 'return' ) . "\r\n";
			}
			if ( 0.00 < (float) $data['order_tax'] ) {
				$meisai .= usces_tax_label( $data, 'return' ) . ' : ' . usces_crform( $data['order_tax'], true, false, 'return' ) . "\r\n";
			}
			if ( 0 !== (int) $data['order_usedpoint'] ) {
				$meisai .= __( 'use of points', 'usces' ) . ' : ' . number_format( $data['order_usedpoint'] ) . __( 'Points', 'usces' ) . "\r\n";
			}
		} else {
			if ( 0 !== (int) $data['order_usedpoint'] ) {
				$meisai .= __( 'use of points', 'usces' ) . ' : ' . number_format( $data['order_usedpoint'] ) . __( 'Points', 'usces' ) . "\r\n";
			}
			if ( 0 !== (int) $data['order_discount'] ) {
				$meisai .= apply_filters( 'usces_confirm_discount_label', __( 'Campaign disnount', 'usces' ), $order_id ) . ' : ' . usces_crform( $data['order_discount'], true, false, 'return' ) . "\r\n";
			}
			if ( 'shipped' === dlseller_get_division( $post_id ) ) {
				$meisai .= __( 'Shipping', 'usces' ) . ' : ' . usces_crform( $data['order_shipping_charge'], true, false, 'return' ) . "\r\n";
			}
			if ( ! empty( $usces->options['tax_rate'] ) ) {
				$meisai .= __( 'consumption tax', 'usces' ) . ' : ' . usces_crform( $data['order_tax'], true, false, 'return' ) . "\r\n";
			}
		}
		$total_full_price = $data['order_item_total_price'] - $data['order_usedpoint'] + $data['order_discount'] + $data['order_shipping_charge'] + $data['order_tax'];
		if ( 0 > $total_full_price ) {
			$total_full_price = 0;
		}
		$meisai .= usces_mail_line( 2, $data['order_email'] ); // --------------------
		$meisai .= __( 'Amount', 'usces' ) . '(' . dlseller_frequency_name( $post_id, 'amount', 'return' ) . ') : ' . usces_crform( $total_full_price, true, false, 'return' ) . "\r\n";
		$meisai .= usces_mail_line( 2, $data['order_email'] ); // --------------------
		if ( usces_is_tax_display() && $reduced_taxrate ) {
			$condition = unserialize( $data['order_condition'] );
			$materials = array(
				'total_items_price' => $data['order_item_total_price'],
				'discount'          => $data['order_discount'],
				'shipping_charge'   => $data['order_shipping_charge'],
				'cod_fee'           => $data['order_cod_fee'],
				'use_point'         => $data['order_usedpoint'],
				'carts'             => $cart,
				'condition'         => $condition,
				'order_id'          => $order_id,
			);
			$usces_tax->get_order_tax( $materials );
			if ( 'include' === $condition['tax_mode'] ) {
				$po = '(';
				$pc = ')';
			} else {
				$po = '';
				$pc = '';
			}
			$meisai .= sprintf( __( 'Applies to %s%%', 'usces' ), $usces_tax->tax_rate_standard ) . ' : ' . usces_crform( $usces_tax->subtotal_standard + $usces_tax->discount_standard, true, false, 'return' ) . "\r\n";
			$meisai .= sprintf( __( '%s%% consumption tax', 'usces' ), $usces_tax->tax_rate_standard ) . ' : ' . $po . usces_crform( $usces_tax->tax_standard, true, false, 'return' ) . $pc . "\r\n";
			$meisai .= sprintf( __( 'Applies to %s%%', 'usces' ), $usces_tax->tax_rate_reduced ) . ' : ' . usces_crform( $usces_tax->subtotal_reduced + $usces_tax->discount_reduced, true, false, 'return' ) . "\r\n";
			$meisai .= sprintf( __( '%s%% consumption tax', 'usces' ), $usces_tax->tax_rate_reduced ) . ' : ' . $po . usces_crform( $usces_tax->tax_reduced, true, false, 'return' ) . $pc . "\r\n";
			$meisai .= usces_mail_line( 2, $data['order_email'] ); // --------------------
			$meisai .= $usces_tax->reduced_taxrate_mark . __( ' is reduced tax rate', 'usces' ) . "\r\n";
		}
		$meisai .= '(' . __( 'Currency', 'usces' ) . ' : ' . __( usces_crcode( 'return' ), 'usces' ) . ")\r\n\r\n";

		$continuation_table = $wpdb->prefix . 'usces_continuation';
		$query              = $wpdb->prepare( "SELECT 
			`con_interval` AS `interval`, 
			`con_next_charging` AS `next_charging`, 
			`con_next_contracting` AS `next_contracting`, 
			`con_startdate` AS `startdate` 
			FROM {$continuation_table} 
			WHERE `con_order_id` = %d", $order_id
		);
		$continue_data      = $wpdb->get_row( $query, ARRAY_A );
		if ( $continue_data ) {
			$meisai .= __( 'Application Date', 'dlseller' ) . ' : ' . date_i18n( __( 'Y/m/d' ), strtotime( $data['order_date'] ) ) . "\r\n";
			if ( 99 === (int) $usces_item['item_chargingday'] ) {
				if ( empty( $continue_data['next_charging'] ) ) {
					$next_charging = dlseller_next_charging( $order_id );
					$meisai .= __( 'First Withdrawal Date', 'dlseller' ) . ' : ' . date_i18n( __( 'Y/m/d' ), strtotime( $continue_data['startdate'] ) ) . "\r\n";
					$meisai .= __( 'Next Withdrawal Date', 'dlseller' ) . ' : ' . date_i18n( __( 'Y/m/d' ), strtotime( $next_charging ) ) . "\r\n";
				} else {
					$meisai .= __( 'Next Withdrawal Date', 'dlseller' ) . ' : ' . date_i18n( __( 'Y/m/d' ), strtotime( $continue_data['next_charging'] ) ) . "\r\n";
				}
			} else {
				if ( empty( $continue_data['next_charging'] ) ) {
					$meisai .= __( 'First Withdrawal Date', 'dlseller' ) . ' : ' . date_i18n( __( 'Y/m/d' ), strtotime( $continue_data['startdate'] ) ) . "\r\n";
				} else {
					$meisai .= __( 'Next Withdrawal Date', 'dlseller' ) . ' : ' . date_i18n( __( 'Y/m/d' ), strtotime( $continue_data['next_charging'] ) ) . "\r\n";
				}
			}
			if ( 0 < (int) $usces_item['dlseller_interval'] ) {
				$next_contracting = ( empty( $continue_data['next_contracting'] ) ) ? dlseller_next_contracting( $order_id ) : $continue_data['next_contracting'];
				$meisai          .= __( 'Renewal Date', 'dlseller' ) . ' : ' . date_i18n( __( 'Y/m/d' ), strtotime( $next_contracting ) ) . "\r\n";
			}
			$meisai .= "\r\n";
		}

		if ( $item_custom ) {
			$meisai .= $item_custom;
		}
	}

	return $meisai;
}

/**
 * Mail message
 * usces_filter_order_confirm_mail_payment
 *
 * @param string $msg_payment Payment message.
 * @param int    $order_id    Order number.
 * @param array  $payment     Payment data.
 * @param array  $cart        Cart data.
 * @param array  $data        Order data.
 * @return string
 */
function dlseller_filter_order_mail_payment( $msg_payment, $order_id, $payment, $cart, $data ) {

	if ( ! dlseller_have_shipped( $cart ) && ! dlseller_require_payment( $cart ) ) {
		$nopayment_message = apply_filters( 'dlseller_filter_nopayment_message', __( 'no payment', 'dlseller' ) );
		if ( function_exists( 'usces_is_html_mail' ) && usces_is_html_mail() ) {
			$msg_payment = '<tr><td colspan="2" style="padding: 0 0 25px 0;">' . $nopayment_message . '</td></tr>';
		} else {
			$msg_payment  = __( '** Payment method **', 'usces' ) . "\r\n";
			$msg_payment .= usces_mail_line( 1, $data['order_email'] ); // ********************
			$msg_payment .= $nopayment_message . "\r\n\r\n";
		}
	}
	return $msg_payment;
}

/**
 * Mail message
 * usces_filter_send_order_mail_payment
 *
 * @param string $msg_payment Payment message.
 * @param int    $order_id    Order number.
 * @param array  $payment     Payment data.
 * @param array  $cart        Cart data.
 * @param array  $entry       Entry data.
 * @param array  $data        Order data.
 * @return string
 */
function dlseller_filter_send_order_mail_payment( $msg_payment, $order_id, $payment, $cart, $entry, $data ) {
	return dlseller_filter_order_mail_payment( $msg_payment, $order_id, $payment, $cart, $data );
}

/**
 * Function build content order cart detail has html.
 *
 * @param string $meisai string message order detail.
 * @param array  $data data of order cart.
 * @param array  $cart data cart item.
 */
function dlseller_filter_order_mail_meisai_htmlbody( $meisai, $data, $cart ) {
	global $usces, $wpdb;

	$order_id = $data['ID'];
	// $post_id  = $cart[0]['post_id'];
	$cart_row = current( $cart );
	$post_id  = $cart_row['post_id'];

	$condition       = unserialize( $data['order_condition'] );
	$tax_display     = ( isset( $condition['tax_display'] ) ) ? $condition['tax_display'] : usces_get_tax_display();
	$reduced_taxrate = ( isset( $condition['applicable_taxrate'] ) && 'reduced' === $condition['applicable_taxrate'] ) ? true : false;
	if ( 'activate' === $tax_display && $reduced_taxrate ) {
		$usces_tax = Welcart_Tax::get_instance();
	}
	$member_system       = ( isset( $condition['membersystem_state'] ) ) ? $condition['membersystem_state'] : $usces->options['membersystem_state'];
	$member_system_point = ( isset( $condition['membersystem_point'] ) ) ? $condition['membersystem_point'] : $usces->options['membersystem_point'];
	$tax_mode            = ( isset( $condition['tax_mode'] ) ) ? $condition['tax_mode'] : usces_get_tax_mode();
	$tax_target          = ( isset( $condition['tax_target'] ) ) ? $condition['tax_target'] : usces_get_tax_target();
	$point_coverage      = ( isset( $condition['point_coverage'] ) ) ? $condition['point_coverage'] : usces_point_coverage();

	// $cart_row       = $cart[0];
	$sku            = $cart_row['sku'];
	$sku_code       = urldecode( $sku );
	$quantity       = $cart_row['quantity'];
	$options        = ( empty( $cart_row['options'] ) ) ? array() : $cart_row['options'];
	$cart_item_name = $usces->getCartItemName( $post_id, $sku_code );
	$sku_price      = $cart_row['price'];
	$item_custom    = usces_get_item_custom( $post_id, 'mail_html', 'return' );
	$usces_item     = $usces->get_item( $post_id );
	$args           = compact( 'cart', 'cart_row', 'post_id', 'sku' );

	$meisai  = '<table style="font-size: 14px; width: 100%; border-collapse: collapse; border: 1px solid #ddd;">';
	$meisai .= '<thead>';
	$meisai .= '<tr>';
	$meisai .= '<td style="text-align: center; width: 50%; padding: 12px; border: 1px solid #ddd;">' . __( 'Items', 'usces' ) . '</td>';
	$meisai .= '<td style="text-align: center; width: 25%; padding: 12px; border: 1px solid #ddd;">' . __( 'Unit price', 'usces' ) . '</td>';
	$meisai .= '<td style="text-align: center; width: 25%; padding: 12px; border: 1px solid #ddd;">' . __( 'Quantity', 'usces' ) . '</td>';
	$meisai .= '</tr>';
	$meisai .= '</thead>';
	$meisai .= '<tbody>';

	$meisai .= '<tr>';
	$meisai .= '<td style="width: 50%; padding: 12px; border: 1px solid #ddd; vertical-align: middle;">';
	$meisai .= apply_filters( 'usces_filter_cart_item_name_nl', $cart_item_name, $args ) . '<br>';
	if ( is_array( $options ) && count( $options ) > 0 ) {
		$optstr = '';
		foreach ( $options as $key => $value ) {
			if ( ! empty( $key ) ) {
				$key   = urldecode( $key );
				$value = maybe_unserialize( $value );
				if ( is_array( $value ) ) {
					$c       = '';
					$optstr .= $key . ' : ';
					foreach ( $value as $v ) {
						$optstr .= $c . rawurldecode( $v );
						$c       = ', ';
					}
					$optstr .= '<br>';
				} else {
					$optstr .= $key . ' : ' . rawurldecode( $value ) . '<br>';
				}
			}
		}
		$meisai .= apply_filters( 'usces_filter_option_adminmail', $optstr, $options, $cart_row );
	}
	$meisai .= apply_filters( 'usces_filter_advance_adminmail', '', $cart_row, $data );
	$meisai .= '</td>';
	$meisai .= '<td style="text-align: center; width: 25%; padding: 12px; border: 1px solid #ddd; vertical-align: middle;">' . usces_crform( $sku_price, true, false, 'return' ) . '</td>';
	$meisai .= '<td style="text-align: center; width: 25%; padding: 12px; border: 1px solid #ddd; vertical-align: middle;">' . $cart_row['quantity'] . '</td>';
	$meisai .= '</tr>';

	$meisai .= '</tbody><tfoot>';
	$meisai .= '<tr>';
	$meisai .= '<td colspan="2" style="text-align: right; width: 75%; padding: 12px; border: 1px solid #ddd; vertical-align: middle;">' . __( 'total items', 'usces' ) . '</td>';
	$meisai .= '<td style="text-align: right; width: 75%; padding: 12px; border: 1px solid #ddd; vertical-align: middle;">' . usces_crform( $data['order_item_total_price'], true, false, 'return' ) . '</td>';
	$meisai .= '</tr>';

	if ( defined( 'USCES_VERSION' ) && version_compare( USCES_VERSION, '1.5', '>=' ) ) {
		if ( 0.0 !== (float) $data['order_discount'] ) {
			$meisai .= '<tr>';
			$meisai .= '<td colspan="2" style="text-align: right; width: 75%; padding: 12px; border: 1px solid #ddd; vertical-align: middle;">' . apply_filters( 'usces_confirm_discount_label', __( 'Campaign discount', 'usces' ), $order_id ) . '</td>';
			$meisai .= '<td style="text-align: right; width: 75%; padding: 12px; border: 1px solid #ddd; vertical-align: middle;">' . usces_crform( $data['order_discount'], true, false, 'return' ) . '</td>';
			$meisai .= '</tr>';
		}
		if ( 'activate' === $tax_display && 'products' === $tax_target ) {
			$meisai .= '<tr>';
			if ( version_compare( USCES_VERSION, '2.8.15', '>=' ) ) {
				$meisai .= '<td colspan="2" style="text-align: right; width: 75%; padding: 12px; border: 1px solid #ddd; vertical-align: middle;">' . usces_mail_tax_label( $data ) . '</td>';
				$meisai .= '<td style="text-align: right; width: 75%; padding: 12px; border: 1px solid #ddd; vertical-align: middle;">' . usces_mail_tax( $data ) . '</td>';
			} else {
				$meisai .= '<td colspan="2" style="text-align: right; width: 75%; padding: 12px; border: 1px solid #ddd; vertical-align: middle;">' . usces_tax_label( $data, 'return' ) . '</td>';
				$meisai .= '<td style="text-align: right; width: 75%; padding: 12px; border: 1px solid #ddd; vertical-align: middle;">' . ( ( 'exclude' === $tax_mode ) ? usces_tax( $data, 'return' ) : 0 ) . '</td>';
			}
			$meisai .= '</tr>';
		}
		if ( 'activate' === $member_system && 'activate' === $member_system_point && 0 === (int) $point_coverage && 0 !== (int) $data['order_usedpoint'] ) {
			$meisai .= '<tr>';
			$meisai .= '<td colspan="2" style="text-align: right; width: 75%; padding: 12px; border: 1px solid #ddd; vertical-align: middle;">' . __( 'use of points', 'usces' ) . '</td>';
			$meisai .= '<td style="text-align: right; width: 75%; padding: 12px; border: 1px solid #ddd; vertical-align: middle;">' . number_format( $data['order_usedpoint'] ) . __( 'Points', 'usces' ) . '</td>';
			$meisai .= '</tr>';
		}
		if ( dlseller_have_shipped( $cart ) ) {
			$meisai .= '<tr>';
			$meisai .= '<td colspan="2" style="text-align: right; width: 75%; padding: 12px; border: 1px solid #ddd; vertical-align: middle;">' . __( 'Shipping', 'usces' ) . '</td>';
			$meisai .= '<td style="text-align: right; width: 75%; padding: 12px; border: 1px solid #ddd; vertical-align: middle;">' . usces_crform( $data['order_shipping_charge'], true, false, 'return' ) . '</td>';
			$meisai .= '</tr>';
		}
		if ( 0 < $data['order_cod_fee'] ) {
			$meisai .= '<tr>';
			$meisai .= '<td colspan="2" style="text-align: right; width: 75%; padding: 12px; border: 1px solid #ddd; vertical-align: middle;">' . apply_filters( 'usces_filter_cod_label', __( 'COD fee', 'usces' ), $order_id ) . '</td>';
			$meisai .= '<td style="text-align: right; width: 75%; padding: 12px; border: 1px solid #ddd; vertical-align: middle;">' . usces_crform( $data['order_cod_fee'], true, false, 'return' ) . '</td>';
			$meisai .= '</tr>';
		}
		if ( 'activate' === $tax_display && 'all' === $tax_target ) {
			$meisai .= '<tr>';
			if ( version_compare( USCES_VERSION, '2.8.15', '>=' ) ) {
				$meisai .= '<td colspan="2" style="text-align: right; width: 75%; padding: 12px; border: 1px solid #ddd; vertical-align: middle;">' . usces_mail_tax_label( $data ) . '</td>';
				$meisai .= '<td style="text-align: right; width: 75%; padding: 12px; border: 1px solid #ddd; vertical-align: middle;">' . usces_mail_tax( $data ) . '</td>';
			} else {
				$meisai .= '<td colspan="2" style="text-align: right; width: 75%; padding: 12px; border: 1px solid #ddd; vertical-align: middle;">' . usces_tax_label( $data, 'return' ) . '</td>';
				$meisai .= '<td style="text-align: right; width: 75%; padding: 12px; border: 1px solid #ddd; vertical-align: middle;">' . ( ( 'exclude' === $tax_mode ) ? usces_tax( $data, 'return' ) : 0 ) . '</td>';
			}
			$meisai .= '</tr>';
		}
		if ( 'activate' === $member_system && 'activate' === $member_system_point && 1 === (int) $point_coverage && 0 !== (int) $data['order_usedpoint'] ) {
			$meisai .= '<tr>';
			$meisai .= '<td colspan="2" style="text-align: right; width: 75%; padding: 12px; border: 1px solid #ddd; vertical-align: middle;">' . __( 'use of points', 'usces' ) . '</td>';
			$meisai .= '<td style="text-align: right; width: 75%; padding: 12px; border: 1px solid #ddd; vertical-align: middle;">' . number_format( $data['order_usedpoint'] ) . __( 'Points', 'usces' ) . '</td>';
			$meisai .= '</tr>';
		}
	} elseif ( defined( 'USCES_VERSION' ) && version_compare( USCES_VERSION, '1.4-beta', '>=' ) ) {
		if ( 0.0 !== (float) $data['order_discount'] ) {
			$meisai .= '<tr>';
			$meisai .= '<td colspan="2" style="text-align: right; width: 75%; padding: 12px; border: 1px solid #ddd; vertical-align: middle;">' . apply_filters( 'usces_confirm_discount_label', __( 'Campaign disnount', 'usces' ), $order_id ) . '</td>';
			$meisai .= '<td style="text-align: right; width: 75%; padding: 12px; border: 1px solid #ddd; vertical-align: middle;">' . usces_crform( $data['order_discount'], true, false, 'return' ) . '</td>';
			$meisai .= '</tr>';
		}
		if ( 0.00 < (float) $data['order_tax'] ) {
			$meisai .= '<tr>';
			$meisai .= '<td colspan="2" style="text-align: right; width: 75%; padding: 12px; border: 1px solid #ddd; vertical-align: middle;">' . usces_tax_label( $data, 'return' ) . '</td>';
			$meisai .= '<td style="text-align: right; width: 75%; padding: 12px; border: 1px solid #ddd; vertical-align: middle;">' . usces_crform( $data['order_tax'], true, false, 'return' ) . '</td>';
			$meisai .= '</tr>';
		}
		if ( 0 !== (int) $data['order_usedpoint'] ) {
			$meisai .= '<tr>';
			$meisai .= '<td colspan="2" style="text-align: right; width: 75%; padding: 12px; border: 1px solid #ddd; vertical-align: middle;">' . __( 'use of points', 'usces' ) . '</td>';
			$meisai .= '<td style="text-align: right; width: 75%; padding: 12px; border: 1px solid #ddd; vertical-align: middle;">' . number_format( $data['order_usedpoint'] ) . __( 'Points', 'usces' ) . '</td>';
			$meisai .= '</tr>';
		}
	} else {
		if ( 0 !== (int) $data['order_usedpoint'] ) {
			$meisai .= '<tr>';
			$meisai .= '<td colspan="2" style="text-align: right; width: 75%; padding: 12px; border: 1px solid #ddd; vertical-align: middle;">' . __( 'use of points', 'usces' ) . '</td>';
			$meisai .= '<td style="text-align: right; width: 75%; padding: 12px; border: 1px solid #ddd; vertical-align: middle;">' . number_format( $data['order_usedpoint'] ) . __( 'Points', 'usces' ) . '</td>';
			$meisai .= '</tr>';
		}
		if ( 0.0 !== (float) $data['order_discount'] ) {
			$meisai .= '<tr>';
			$meisai .= '<td colspan="2" style="text-align: right; width: 75%; padding: 12px; border: 1px solid #ddd; vertical-align: middle;">' . apply_filters( 'usces_confirm_discount_label', __( 'Campaign disnount', 'usces' ), $order_id ) . '</td>';
			$meisai .= '<td style="text-align: right; width: 75%; padding: 12px; border: 1px solid #ddd; vertical-align: middle;">' . usces_crform( $data['order_discount'], true, false, 'return' ) . '</td>';
			$meisai .= '</tr>';
		}
		if ( 'shipped' === dlseller_get_division( $post_id ) ) {
			$meisai .= '<tr>';
			$meisai .= '<td colspan="2" style="text-align: right; width: 75%; padding: 12px; border: 1px solid #ddd; vertical-align: middle;">' . __( 'Shipping', 'usces' ) . '</td>';
			$meisai .= '<td style="text-align: right; width: 75%; padding: 12px; border: 1px solid #ddd; vertical-align: middle;">' . usces_crform( $data['order_shipping_charge'], true, false, 'return' ) . '</td>';
			$meisai .= '</tr>';
		}
		if ( usces_is_tax_display() && ! empty( $usces->options['tax_rate'] ) ) {
			$meisai .= '<tr>';
			$meisai .= '<td colspan="2" style="text-align: right; width: 75%; padding: 12px; border: 1px solid #ddd; vertical-align: middle;">' . __( 'consumption tax', 'usces' ) . '</td>';
			$meisai .= '<td style="text-align: right; width: 75%; padding: 12px; border: 1px solid #ddd; vertical-align: middle;">' . usces_crform( $data['order_tax'], true, false, 'return' ) . '</td>';
			$meisai .= '</tr>';
		}
	}
	$total_full_price = $data['order_item_total_price'] - $data['order_usedpoint'] + $data['order_discount'] + $data['order_shipping_charge'] + $data['order_tax'];
	if ( 0 > $total_full_price ) {
		$total_full_price = 0;
	}
	$meisai .= '<tr>';
	$meisai .= '<td colspan="2" style="text-align: right; width: 75%; padding: 12px; border: 1px solid #ddd; vertical-align: middle;">' . __( 'Amount', 'usces' ) . '(' . dlseller_frequency_name( $post_id, 'amount', 'return' ) . ') </td>';
	$meisai .= '<td style="text-align: right; width: 75%; padding: 12px; border: 1px solid #ddd; vertical-align: middle;">' . usces_crform( $total_full_price, true, false, 'return' ) . '</td>';
	$meisai .= '</tr>';
	$meisai .= '</tfoot></table>';
	$meisai .= '<p style="margin-top: 10px; font-size: 13px;">(' . __( 'Currency', 'usces' ) . ' : ' . __( usces_crcode( 'return' ), 'usces' ) . ')</p>';
	if ( usces_is_tax_display() && $reduced_taxrate ) {
		$condition = unserialize( $data['order_condition'] );
		$materials = array(
			'total_items_price' => $data['order_item_total_price'],
			'discount'          => $data['order_discount'],
			'shipping_charge'   => $data['order_shipping_charge'],
			'cod_fee'           => $data['order_cod_fee'],
			'use_point'         => $data['order_usedpoint'],
			'carts'             => $cart,
			'condition'         => $condition,
			'order_id'          => $order_id,
		);
		$usces_tax->get_order_tax( $materials );
		if ( 'include' === $condition['tax_mode'] ) {
			$po = '(';
			$pc = ')';
		} else {
			$po = '';
			$pc = '';
		}
		$meisai .= '<p style="margin-top: 10px; font-size: 13px;">';
		$meisai .= $usces_tax->reduced_taxrate_mark . __( ' is reduced tax rate', 'usces' ) . '<br>';
		$meisai .= sprintf( __( 'Applies to %s%%', 'usces' ), $usces_tax->tax_rate_standard ) . ' : ' . usces_crform( $usces_tax->subtotal_standard + $usces_tax->discount_standard, true, false, 'return' ) . '<br>';
		$meisai .= sprintf( __( '%s%% consumption tax', 'usces' ), $usces_tax->tax_rate_standard ) . ' : ' . $po . usces_crform( $usces_tax->tax_standard, true, false, 'return' ) . $pc . '<br>';
		$meisai .= sprintf( __( 'Applies to %s%%', 'usces' ), $usces_tax->tax_rate_reduced ) . ' : ' . usces_crform( $usces_tax->subtotal_reduced + $usces_tax->discount_reduced, true, false, 'return' ) . '<br>';
		$meisai .= sprintf( __( '%s%% consumption tax', 'usces' ), $usces_tax->tax_rate_reduced ) . ' : ' . $po . usces_crform( $usces_tax->tax_reduced, true, false, 'return' ) . $pc . '<br>';
		$meisai .= '</p>';
	}

	$continuation_table = $wpdb->prefix . 'usces_continuation';
	$query              = $wpdb->prepare(
		"SELECT 
		`con_interval` AS `interval`, 
		`con_next_charging` AS `next_charging`, 
		`con_next_contracting` AS `next_contracting`, 
		`con_startdate` AS `startdate` 
		FROM {$continuation_table} 
		WHERE `con_order_id` = %d",
		$order_id
	);
	$continue_data      = $wpdb->get_row( $query, ARRAY_A );
	if ( $continue_data ) {
		$meisai .= '<table style="font-size: 14px; margin-bottom: 30px; width: 100%; border-collapse: collapse; border: 1px solid #ddd;">';
		$meisai .= '<tbody>';
		$meisai .= '<tr><td style="background-color: #f9f9f9; padding: 12px; width: 50%; border: 1px solid #ddd;">';
		$meisai .= __( 'Application Date', 'dlseller' );
		$meisai .= '</td><td style="padding: 12px; width: 50%; border: 1px solid #ddd;">';
		$meisai .= date_i18n( __( 'Y/m/d' ), strtotime( $data['order_date'] ) );
		$meisai .= '</td></tr>';
		if ( 99 === (int) $usces_item['item_chargingday'] ) {
			if ( empty( $continue_data['next_charging'] ) ) {
				$next_charging = dlseller_next_charging( $order_id );
				$meisai       .= '<tr><td style="background-color: #f9f9f9; padding: 12px; width: 50%; border: 1px solid #ddd;">';
				$meisai       .= __( 'First Withdrawal Date', 'dlseller' );
				$meisai       .= '</td><td style="padding: 12px; width: 50%; border: 1px solid #ddd;">';
				$meisai       .= date_i18n( __( 'Y/m/d' ), strtotime( $continue_data['startdate'] ) );
				$meisai       .= '</td></tr>';
				$meisai       .= '<tr><td style="background-color: #f9f9f9; padding: 12px; width: 50%; border: 1px solid #ddd;">';
				$meisai       .= __( 'Next Withdrawal Date', 'dlseller' );
				$meisai       .= '</td><td style="padding: 12px; width: 50%; border: 1px solid #ddd;">';
				$meisai       .= date_i18n( __( 'Y/m/d' ), strtotime( $next_charging ) );
				$meisai       .= '</td></tr>';
			} else {
				$meisai .= '<tr><td style="background-color: #f9f9f9; padding: 12px; width: 50%; border: 1px solid #ddd;">';
				$meisai .= __( 'Next Withdrawal Date', 'dlseller' );
				$meisai .= '</td><td style="padding: 12px; width: 50%; border: 1px solid #ddd;">';
				$meisai .= date_i18n( __( 'Y/m/d' ), strtotime( $continue_data['next_charging'] ) );
				$meisai .= '</td></tr>';
			}
		} else {
			if ( empty( $continue_data['next_charging'] ) ) {
				$meisai .= '<tr><td style="background-color: #f9f9f9; padding: 12px; width: 50%; border: 1px solid #ddd;">';
				$meisai .= __( 'First Withdrawal Date', 'dlseller' );
				$meisai .= '</td><td style="padding: 12px; width: 50%; border: 1px solid #ddd;">';
				$meisai .= date_i18n( __( 'Y/m/d' ), strtotime( $continue_data['startdate'] ) );
				$meisai .= '</td></tr>';
			} else {
				$meisai .= '<tr><td style="background-color: #f9f9f9; padding: 12px; width: 50%; border: 1px solid #ddd;">';
				$meisai .= __( 'Next Withdrawal Date', 'dlseller' );
				$meisai .= '</td><td style="padding: 12px; width: 50%; border: 1px solid #ddd;">';
				$meisai .= date_i18n( __( 'Y/m/d' ), strtotime( $continue_data['next_charging'] ) );
				$meisai .= '</td></tr>';
			}
		}
		if ( 0 < (int) $usces_item['dlseller_interval'] ) {
			$next_contracting = ( empty( $continue_data['next_contracting'] ) ) ? dlseller_next_contracting( $order_id ) : $continue_data['next_contracting'];
			$meisai          .= '<tr><td style="background-color: #f9f9f9; padding: 12px; width: 50%; border: 1px solid #ddd;">';
			$meisai          .= __( 'Renewal Date', 'dlseller' );
			$meisai          .= '</td><td style="padding: 12px; width: 50%; border: 1px solid #ddd;">';
			$meisai          .= date_i18n( __( 'Y/m/d' ), strtotime( $next_contracting ) );
			$meisai          .= '</td></tr>';
		}
		$meisai .= '</tbody></table>';
	}

	if ( $item_custom ) {
		$meisai .= $item_custom;
	}

	return $meisai;
}

/**
 * Get charge type
 *
 * @param int $post_id Post ID.
 * @return int
 */
function dlseller_get_charging_type( $post_id ) {
	global $usces;
	$charging_type = $usces->getItemChargingType( $post_id );
	return $charging_type;
}

/**
 * Get division
 *
 * @param int $post_id Post ID.
 * @return string
 */
function dlseller_get_division( $post_id ) {
	$product       = wel_get_product( $post_id );
	$division      = empty( $product['item_division'] ) ? 'shipped' : $product['item_division'];
	return $division;
}

/**
 * Have shipped item
 *
 * @param array $carts Cart data.
 * @return bool
 */
function dlseller_have_shipped( $carts = array() ) {
	if ( empty( $carts ) ) {
		global $usces;
		$carts = $usces->cart->get_cart();
	}
	$division = '';
	if ( ! empty( $carts ) ) {
		foreach ( $carts as $index => $cart ) {
			extract( $cart );
			if ( 'shipped' === dlseller_get_division( $post_id ) ) {
				$division = 'shipped';
				break;
			}
		}
	}
	if ( 'shipped' === $division ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Require payment
 *
 * @param array $cart Cart data.
 * @return bool
 */
function dlseller_require_payment( $cart = array() ) {
	global $usces;

	if ( ! $cart ) {
		$cart = $usces->cart->get_cart();
	}
	$amount = 0;
	if ( ! empty( $cart ) ) {
		foreach ( $cart as $cart_row ) {
			if ( 'shipped' !== dlseller_get_division( $cart_row['post_id'] ) ) {
				$amount += $cart_row['price'];
			}
		}
	}
	if ( 0 < $amount ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Have continuation charges item
 *
 * @param mixed|array $carts Cart data.
 * @return bool
 */
function dlseller_have_continue_charge( $carts = null ) {
	global $usces;

	if ( null == $carts ) {
		$carts = $usces->cart->get_cart();
	}
	$charging_type = 'once';
	foreach ( $carts as $cart ) {
		if ( 'continue' === $usces->getItemChargingType( $cart['post_id'] ) ) {
			$charging_type = 'continue';
			break;
		}
	}
	if ( 'continue' === $charging_type ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Have DL Seller item
 *
 * @return bool
 */
function dlseller_have_dlseller_content() {
	global $usces;

	$carts = $usces->cart->get_cart();

	$division = '';
	foreach ( $carts as $index => $cart ) {
		extract( $cart );
		$content = dlseller_get_division( $post_id );
		if ( 'data' === $content || 'service' === $content ) {
			$division = 'dlseller';
			break;
		}
	}
	if ( 'dlseller' === $division ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Terms of Use
 */
function dlseller_terms() {
	$dlseller_options = get_option( 'dlseller' );
	if ( dlseller_have_continue_charge() ) {
		$dlseller_terms = nl2br( esc_html( $dlseller_options['dlseller_terms2'] ) );
	} else {
		$dlseller_terms = nl2br( esc_html( $dlseller_options['dlseller_terms'] ) );
	}
	echo $dlseller_terms;
}

/**
 * Have terms
 *
 * @return bool
 */
function dlseller_has_terms() {
	$dlseller_options = get_option( 'dlseller' );

	$flag = false;
	
	if ( dlseller_have_continue_charge() && isset( $dlseller_options['dlseller_terms2'] ) && ! empty( $dlseller_options['dlseller_terms2'] ) ) {
		$flag = true;
	} elseif ( isset( $dlseller_options['dlseller_terms'] ) && ! empty( $dlseller_options['dlseller_terms'] ) ) {
		$flag = true;
	}
	
	return $flag;
}

/**
 * Completion information
 *
 * @param array  $usces_carts Cart data.
 * @param string $out         Return value or echo.
 * @return mixed
 */
function dlseller_completion_info( $usces_carts , $out = ' ' ) {
	global $usces, $usces_entries;

	$member = $usces->get_member();
	$oid    = isset( $usces_entries['order']['ID'] ) ? $usces_entries['order']['ID'] : '';
	if ( empty( $oid ) && isset( $usces_entries['reserve']['pre_order_id'] ) ) {
		$oid = dlseller_get_order_id_by_pre( $usces_entries['reserve']['pre_order_id'] );
	}
	$html     = '<ul class="dllist">';
	$payments = usces_get_payments_by_name( $usces_entries['order']['payment_name'] );

	$count_cart = count( $usces_carts );
	for ( $i = 0; $i < $count_cart; $i++ ) {
		$cart_row      = $usces_carts[ $i ];
		$post_id       = $cart_row['post_id'];
		$sku           = $cart_row['sku'];
		$sku_code      = esc_attr( urldecode( $cart_row['sku'] ) );
		$product       = wel_get_product( $post_id );
		$item_post     = $product['_pst'];
		$usces_item    = $usces->get_item( $post_id );
		$periods       = dlseller_get_validityperiod( $member['ID'], $post_id );
		$cartItemName  = $usces->getCartItemName( $post_id, $sku_code );
		$purchased     = $usces->is_purchased_item( $member['ID'], $post_id );
		$charging_type = $usces->getItemChargingType( $post_id );
		$item_custom   = usces_get_item_custom( $post_id, 'table', 'return' );
		$options       = ( empty( $cart_row['options'] ) ) ? array() : $cart_row['options'];
		$args          = compact( 'usces_carts', 'i', 'cart_row', 'post_id', 'sku', 'sku_code', 'item_post', 'cartItemName' );

		$list      = '<li>';
		$list     .= '<div class="thumb">';
		$itemImage = usces_the_itemImage( 0, 200, 250, $item_post, 'return' );
		$list     .= apply_filters( 'dlseller_filter_the_itemImage', $itemImage, $item_post );
		$list     .= '</div>';

		if ( 'service' === $usces_item['item_division'] ) {

			if ( 'continue' === $charging_type ) {

				$nextdate    = current_time( 'Y/m/d' );
				$chargingday = $usces->getItemChargingDay( $post_id );

				$list .= '<div class="item_info_list">';
				$list .= '<table class="dlseller">';
				$list .= '<tr><th>' . __( 'Item Name', 'dlseller' ) . '</th><td>' . apply_filters( 'usces_filter_cart_item_name', esc_html( $cartItemName ), $args ) . '</td></tr>';
				if ( is_array( $options ) && 0 < count( $options ) ) {
					foreach ( $options as $key => $value ) {
						if ( ! empty( $key ) ) {
							$key = urldecode( $key );
							if ( is_array( $value ) ) {
								$c     = '';
								$list .= '<tr><th>' . esc_html( $key ) . '</th><td>';
								foreach ( $value as $v ) {
									$list .= $c . esc_html( urldecode( $v ) );
									$c     = ', ';
								}
								$list .= '</td></tr>';
							} else {
								$list .= '<tr><th>' . esc_html( $key ) . '</th><td>' . nl2br( esc_html( urldecode( $value ) ) ) . '</td></tr>';
							}
						}
					}
				}
				$list .= '<tr><th>' . __( 'Application Date', 'dlseller' ) . '</th><td>' . date_i18n( __( 'Y/m/d' ), strtotime( $nextdate ) ) . '</td></tr>';
				$list .= '</table>';
				if ( $item_custom ) {
					$list .= $item_custom;
				}
				$list .= '<table class="dlseller">';
				$list .= '<tr><th>' . __( 'First Withdrawal Date', 'dlseller' ) . '</th><td>' . date_i18n( __( 'Y/m/d' ), strtotime( dlseller_first_charging( $post_id ) ) ) . '</td></tr>';
				if ( 0 < (int) $usces_item['dlseller_interval'] ) {
					$list .= '<tr><th>' . __( 'Contract Period', 'dlseller' ) . '</th><td>' . $usces_item['dlseller_interval'] . __( 'Month(Automatic Renewal)', 'dlseller' ) . '</td></tr>';
				}
				$list .= '</table>';
				$list .= '</div>';

			} else {

				$list .= '<div class="item_info_list">';
				$list .= '<table class="dlseller">';
				$list .= '<tr><th>' . __( 'Item Code', 'dlseller' ) . '</th><td>' . esc_html( $usces_item['itemCode'] ) . '</td></tr>';
				$list .= '<tr><th>' . __( 'Item Name', 'dlseller' ) . '</th><td>'. apply_filters( 'usces_filter_cart_item_name', esc_html( $cartItemName ), $args ) . '</td></tr>';
				if ( is_array( $options ) && 0 < count( $options ) ) {
					foreach ( $options as $key => $value ) {
						if ( ! empty( $key ) ) {
							$key = urldecode( $key );
							if ( is_array( $value ) ) {
								$c     = '';
								$list .= '<tr><th>' . esc_html( $key ) . '</th><td>';
								foreach ( $value as $v ) {
									$list .= $c . esc_html( urldecode( $v ) );
									$c     = ', ';
								}
								$list .= '</td></tr>';
							} else {
								$list .= '<tr><th>' . esc_html( $key ) . '</th><td>' . nl2br( esc_html( urldecode( $value ) ) ) . '</td></tr>';
							}
						}
					}
				}
				$list .= '</table>';
				if ( $item_custom ) {
					$list .= $item_custom;
				}
				$purchase_mes = '';
				if ( true !== $purchased ) {
					$purchase_mes = '<p>' . __( 'I contact you by an email if I can confirm the receipt of money.', 'dlseller' ) . '</p>';
				}
				$list .= apply_filters( 'dlseller_filter_service_purchase_message', $purchase_mes, $purchased );
				$list .= '</div>';
				$list .= '<div class="clear"></div>';
			}

		} elseif ( 'data' === $usces_item['item_division'] ) {

			usces_dlseller_update_dlcount( $post_id, 1, 0 );
			$files    = dlseller_get_filename( $post_id, $sku );
			$filename = basename( $files );

			if ( 'continue' === $charging_type ) {

				$chargingday = $usces->getItemChargingDay( $post_id );

				$list .= '<div class="item_info_list">';
				$list .= '<table class="dlseller">';
				$list .= '<tr><th>' . __( 'Item Code', 'dlseller' ) . '</th><td>' . esc_html( $usces_item['itemCode'] ) . '</td></tr>';
				$list .= '<tr><th>' . __( 'Item Name', 'dlseller' ) . '</th><td>'. apply_filters( 'usces_filter_cart_item_name', esc_html( $cartItemName ), $args ) . '</td></tr>';
				if ( is_array( $options ) && 0 < count( $options ) ) {
					foreach ( $options as $key => $value ) {
						if ( ! empty( $key ) ) {
							$key = urldecode( $key );
							if ( is_array( $value ) ) {
								$c     = '';
								$list .= '<tr><th>' . esc_html( $key ) . '</th><td>';
								foreach ( $value as $v ) {
									$list .= $c . esc_html( urldecode( $v ) );
									$c     = ', ';
								}
								$list .= '</td></tr>';
							} else {
								$list .= '<tr><th>' . esc_html( $key ) . '</th><td>' . nl2br( esc_html( urldecode( $value ) ) ) . '</td></tr>';
							}
						}
					}
				}
				$list .= '<tr><th>' . __( 'Version', 'dlseller' ) . '</th><td>' . esc_html( $usces_item['dlseller_version'] ) . '</td></tr>';
				$list .= '<tr><th>' . __( 'Release Date', 'dlseller' ) . '</th><td>' . esc_html( $usces_item['dlseller_date'] ) . '</td></tr>';
				$list .= '<tr><th>' . __( 'Author', 'dlseller' ) . '</th><td>' . esc_html( $usces_item['dlseller_author'] ) . '</td></tr>';
				$list .= '<tr><th>' . __( 'Re-download validity(days)', 'dlseller' ) . '</th><td>';
				if ( empty( $periods['lastdate'] ) ) {
					$list .= __( 'No limit', 'dlseller' );
				} else {
					$list .= sprintf( __( 'From %1$s to %2$s', 'dlseller' ), esc_html( $periods['firstdate'] ), esc_html( $periods['lastdate'] ) );
				}
				$list .= '</td></tr>';
				$list .= '<tr><th>' . __( 'File Name', 'dlseller' ) . '</th><td>' . esc_html( $filename ) . '</td></tr>';
				$list .= '</table>';
				if ( $item_custom ) {
					$list .= $item_custom;
				}
				$list .= '<table class="dlseller">';
				$list .= '<tr><th>' . __( 'First Withdrawal Date', 'dlseller' ) . '</th><td>' . date_i18n( __( 'Y/m/d' ), strtotime( dlseller_first_charging( $post_id ) ) ) . '</td></tr>';
				if ( 0 < (int) $usces_item['dlseller_interval'] ){
					$list .= '<tr><th>' . __( 'Contract Period', 'dlseller' ) . '</th><td>' . $usces_item['dlseller_interval'] . __( 'Month(Automatic Renewal)', 'dlseller' ) . '</td></tr>';
				}
				$list .= '</table>';
				if ( ! empty( $oid ) ) {
					$list .= '<a class="redownload_button" href="' . USCES_CART_URL . $usces->delim . 'dlseller_transition=download&rid=' . $i . '&oid=' . $oid . apply_filters( 'dlseller_filter_download_para', '', $post_id, $sku ) . '">' . __( 'Download', 'dlseller' ) . '</a>';
					$list .= '<p>' . __( 'You can download it again during your subscription period.', 'dlseller' ) . '</p>';
				}
				$list .= '</div>';
				$list .= '<div class="clear"></div>';

			} else {

				$list .= '<div class="item_info_list">';
				$list .= '<table class="dlseller">';
				$list .= '<tr><th>' . __( 'Item Code', 'dlseller' ) . '</th><td>' . esc_html( $usces_item['itemCode'] ) . '</td></tr>';
				$list .= '<tr><th>' . __( 'Item Name', 'dlseller' ) . '</th><td>' . apply_filters( 'usces_filter_cart_item_name', esc_html( $cartItemName ), $args ) . '</td></tr>';
				if ( is_array( $options ) && 0 < count( $options ) ) {
					foreach ( $options as $key => $value ) {
						if ( ! empty( $key ) ) {
							$key = urldecode( $key );
							if ( is_array( $value ) ) {
								$c     = '';
								$list .= '<tr><th>' . esc_html( $key ) . '</th><td>';
								foreach ( $value as $v ) {
									$list .= $c . esc_html( urldecode( $v ) );
									$c     = ', ';
								}
								$list .= '</td></tr>';
							} else {
								$list .= '<tr><th>' . esc_html( $key ) . '</th><td>' . nl2br( esc_html( urldecode( $value ) ) ) . '</td></tr>';
							}
						}
					}
				}
				$list .= '<tr><th>' . __( 'Version', 'dlseller' ) . '</th><td>' . esc_html( $usces_item['dlseller_version'] ) . '</td></tr>';
				$list .= '<tr><th>' . __( 'Release Date', 'dlseller' ) . '</th><td>' . esc_html( $usces_item['dlseller_date'] ) . '</td></tr>';
				$list .= '<tr><th>' . __( 'Author', 'dlseller' ) . '</th><td>' . esc_html( $usces_item['dlseller_author'] ) . '</td></tr>';
				$list .= '<tr><th>' . __( 'Re-download validity(days)', 'dlseller' ) . '</th><td>';
				if ( empty( $periods['lastdate'] ) ) {
					$list .= __( 'No limit', 'dlseller' );
				} elseif ( 'transferAdvance' === $payments['settlement'] || 'transferDeferred' === $payments['settlement'] ) {
					$list .= sprintf( __( 'From payment day for %s days', 'dlseller' ), esc_html( $usces_item['dlseller_validity'] ) );
				} else {
					$list .= sprintf( __( 'From %1$s to %2$s', 'dlseller' ), esc_html( $periods['firstdate'] ), esc_html( $periods['lastdate'] ) );
				}
				$list .= '</td></tr>';
				$list .= '<tr><th>' . __( 'File Name', 'dlseller' ) . '</th><td>' . esc_html( $filename ) . '</td></tr>';
				$list .= '</table>';
				if ( $item_custom ) {
					$list .= $item_custom;
				}
				if ( true !== $purchased ) {
					$purchase_mes  = '<p>' . __( 'After the receipt of money, you can download it from your member page.', 'dlseller' ) . '</p>';
					$purchase_mes .= '<p>' . __( 'I contact you by an email if I can confirm the receipt of money.', 'dlseller' ) . '</p>';
				} else {
					if ( ! empty( $oid ) ) {
						$purchase_mes  = '<a class="redownload_button" href="' . USCES_CART_URL . $usces->delim . 'dlseller_transition=download&rid=' . $i . '&oid=' . $oid . apply_filters( 'dlseller_filter_download_para', '', $post_id, $sku ) . '">' . __( 'Download', 'dlseller' ) . '</a>';
						$purchase_mes .= '<p>' . __( 'You can download it again during your subscription period.', 'dlseller' ) . '</p>';
					}
				}
				$list .= apply_filters( 'dlseller_filter_data_purchase_message', $purchase_mes, $purchased );
				$list .= '</div>';
				$list .= '<div class="clear"></div>';
			}

		} else { // 'shipped' == $usces_item['item_division']

			if ( 'continue' === $charging_type ) {

				$nextdate    = current_time( 'Y/m/d' );
				$chargingday = $usces->getItemChargingDay( $post_id );

				$list .= '<div class="item_info_list">';
				$list .= '<table class="dlseller">';
				$list .= '<tr><th>' . __( 'Item Name', 'dlseller' ) . '</th><td>'. apply_filters( 'usces_filter_cart_item_name', esc_html( $cartItemName ), $args ) . '</td></tr>';
				if ( is_array( $options ) && 0 < count( $options ) ) {
					foreach ( $options as $key => $value ) {
						if ( ! empty( $key ) ) {
							$key = urldecode( $key );
							if ( is_array( $value ) ) {
								$c     = '';
								$list .= '<tr><th>' . esc_html( $key ) . '</th><td>';
								foreach ( $value as $v ) {
									$list .= $c . esc_html( urldecode( $v ) );
									$c     = ', ';
								}
								$list .= '</td></tr>';
							} else {
								$list .= '<tr><th>' . esc_html( $key ) . '</th><td>' . nl2br( esc_html( urldecode( $value ) ) ) . '</td></tr>';
							}
						}
					}
				}
				$list .= '<tr><th>' . __( 'Application Date', 'dlseller' ) . '</th><td>' . date_i18n( __( 'Y/m/d' ), strtotime( $nextdate ) ) . '</td></tr>';
				$list .= '</table>';
				if ( $item_custom ) {
					$list .= $item_custom;
				}
				$list .= '<table class="dlseller">';
				$list .= '<tr><th>' . __( 'First Withdrawal Date', 'dlseller' ) . '</th><td>' . date_i18n( __( 'Y/m/d' ), strtotime( dlseller_first_charging( $post_id ) ) ) . '</td></tr>';
				if ( 0 < (int) $usces_item['dlseller_interval'] ) {
					$list .= '<tr><th>' . __( 'Contract Period', 'dlseller' ) . '</th><td>' . $usces_item['dlseller_interval'] . __( 'Month(Automatic Renewal)', 'dlseller' ) . '</td></tr>';
				}
				$list .= '</table>';
				$list .= '</div>';

			} else {

				$list .= '<div class="item_info_list">';
				$list .= '<table class="dlseller">';
				$list .= '<tr><th>' . __( 'Item Code', 'dlseller' ) . '</th><td>' . esc_html( $usces_item['itemCode'] ) . '</td></tr>';
				$list .= '<tr><th>' . __( 'Item Name', 'dlseller' ) . '</th><td>'. apply_filters( 'usces_filter_cart_item_name', esc_html( $cartItemName ), $args ) . '</td></tr>';
				if ( is_array( $options ) && 0 < count( $options ) ) {
					foreach ( $options as $key => $value ) {
						if ( ! empty( $key ) ) {
							$key = urldecode( $key );
							if ( is_array( $value ) ) {
								$c     = '';
								$list .= '<tr><th>' . esc_html( $key ) . '</th><td>';
								foreach ( $value as $v ) {
									$list .= $c . esc_html( urldecode( $v ) );
									$c     = ', ';
								}
								$list .= '</td></tr>';
							} else {
								$list .= '<tr><th>' . esc_html( $key ) . '</th><td>' . nl2br( esc_html( urldecode( $value ) ) ) . '</td></tr>';
							}
						}
					}
				}
				$list .= '</table>';
				if ( $item_custom ) {
					$list .= $item_custom;
				}
				if ( true !== $purchased && 'transferDeferred' !== $payments['settlement'] ) {
					$purchase_mes = '<p>' . __( 'I am in the dispatch of the product if I can confirm the receipt of money.', 'dlseller' ) . '</p>';
				} else {
					$purchase_mes = '';
				}
				$list .= apply_filters( 'dlseller_filter_shipped_purchase_message', $purchase_mes, $purchased );
				$list .= '</div>';
				$list .= '<div class="clear"></div>';
			}
		}

		$list .= '</li>';
		$html .= apply_filters( 'dlseller_filter_completion_list', $list, $cart_row, $i );
	}
	if ( isset( $_GET['dlseller_update'] ) ) {
		$html .= '<li>';
		$html .= '<div class="update_info">';
		$html .= '<p>' . __( 'Card information update processing was completed. Thank you.', 'dlseller' ) . '</p>';
		$html .= '</div>';
		$html .= '</li>';
	}
	$html .= '</ul>';
	$html  = apply_filters( 'dlseller_filter_completion_html', $html, $usces_carts );

	if ( 'return' === $out ) {
		return $html;
	} else {
		echo $html;
	}
}

/**
 * Update card notification
 *
 * @param int    $member_id    Member ID.
 * @param int    $order_id     Order ID.
 * @param string $cardlimit    Card limit.
 * @param string $payment_name Payment name.
 * @param string $out          Return value or echo.
 * @return mixed
 */
function dlseller_upcard_url( $member_id, $order_id, $cardlimit, $payment_name, $out = '' ) {
	global $usces;

	if ( ! $member_id || ! $order_id || ! $cardlimit ) {
		return;
	}

	$payment = usces_get_payments_by_name( $payment_name );
	$acting  = $payment['settlement'];
	if ( 'acting_remise_card' !== $acting ) {
		return '';
	}

	$html   = '';
	$limits = explode( '/', $cardlimit );
	$limit  = substr( current_time( 'mysql', 0 ), 0, 2 ) . $limits[1] . $limits[0];
	$now    = date_i18n( 'Ym', current_time( 'timestamp', 0 ) );

	//if ( $limit <= $now ) {
		$html = '<a href="javascript:void(0)" onClick="uscesMail.getMailData(\'' . $member_id . '\', \'' . $order_id . '\')">' . __( 'Update Request Email', 'dlseller' ) . '</a>';
	//}

	if ( 'return' === $out ) {
		return '<br />' . $html;
	} else {
		echo '<br />' . $html;
	}
}

/**
 * Create mail message
 * wp_ajax_dlseller_make_mail_ajax
 *
 * @return json
 */
function dlseller_make_mail_ajax() {
	global $usces;

	$order_id    = absint( wp_unslash( $_POST['order_id'] ) );
	$member_id   = absint( wp_unslash( $_POST['member_id'] ) );
	$now         = date_i18n( 'Ym', current_time( 'timestamp', 0 ) );
	$member_info = $usces->get_member_info( $member_id );
	if ( function_exists( 'usces_mail_data' ) && version_compare( USCES_VERSION, '2.3-beta', '>=' ) ) {
		$mail_data = usces_mail_data();
	} else {
		$mail_data = $usces->options['mail_data'];
	}
	$order_data    = $usces->get_order_data( $order_id, 'direct' );
	$continue_data = dlseller_get_continuation_data( $order_id, $member_id );

	$nonsessionurl = usces_url( 'cartnonsession', 'return' );
	$parts         = parse_url( $nonsessionurl );
	if ( isset( $parts['query'] ) && '' !== $parts['query'] ) {
		parse_str( $parts['query'], $query );
	}
	if ( false !== strpos( $nonsessionurl, '/usces-cart' ) ) {
		$nonsessionurl = str_replace( '/usces-cart', '/usces-member', $nonsessionurl );
	} elseif ( isset( $query['page_id'] ) && $query['page_id'] === USCES_CART_NUMBER ) {
		$nonsessionurl = str_replace( 'page_id=' . USCES_CART_NUMBER, 'page_id=' . USCES_MEMBER_NUMBER, $nonsessionurl );
	}
	$delim = ( false === strpos( $nonsessionurl, '?' ) ) ? '?' : '&';

	$limits = explode( '/', $member_info['limitofcard'] );
	$regd   = date_i18n( 'Ym', strtotime( substr( current_time( 'mysql', 0 ), 0, 2 ) . $limits[1] . '-' . $limits[0] . '-01' ) );
	if ( $regd === $now ) {
		$flag = 'NOW';
	} elseif ( $regd < $now ) {
		$flag = 'PASSED';
	} else {
		$flag = 'UPDATE';
	}

	$mail    = $member_info['mem_email'];
	$name    = usces_localized_name( $member_info['mem_name1'], $member_info['mem_name2'], 'return' );
	$subject = apply_filters( 'dlseller_filter_card_update_mail_subject', __( 'Credit Card Information Update Request', 'dlseller' ), $member_id, $order_id );

	if ( function_exists( 'usces_is_html_mail' ) && usces_is_html_mail() ) {
		$message  = '<table border="0" width="100%" cellspacing="0" cellpadding="0" bgcolor="#eeeeee">';
		$message .= '<tbody><tr><td>';
		$message .= '<table style="font-size:15px;margin-right:auto;margin-left:auto" border="0" width="600" cellspacing="0" cellpadding="0" align="center" bgcolor="#ffffff"><tbody>';
		// add body.
		$message .= '<tr><td style="padding:20px 30px">';
		$message .= '<table style="font-size:15px; margin-bottom: 40px;" border="0" width="540" cellspacing="0" cellpadding="0" align="center" bgcolor="#ffffff">';
		$message .= '<tbody><tr><td style="font-size: 22px; font-weight: bold; padding: 30px 0;">';
		$message .= get_option( 'blogname' );
		$message .= '</td></tr>';
		$message .= '<tr><td><table style="font-size: 14px; margin-bottom: 40px; width: 100%; border-collapse: collapse; border: 1px solid #ddd;">';
		$message .= '<tr><td style="background-color: #f9f9f9; padding: 12px; width: 33%; border: 1px solid #ddd; text-align: left;">';
		$message .= __( 'Member ID', 'dlseller' );
		$message .= '</td><td style="padding: 12px; width: 67%; border: 1px solid #ddd;">';
		$message .= esc_attr( $member_info['ID'] );
		$message .= '</td></tr>';
		$message .= '<tr><td style="background-color: #f9f9f9; padding: 12px; width: 33%; border: 1px solid #ddd; text-align: left;">';
		$message .= __( 'Contractor name', 'dlseller' );
		$message .= '</td><td style="padding: 12px; width: 67%; border: 1px solid #ddd;">';
		$message .= sprintf( _x( '%s', 'honorific', 'usces' ), $name );
		$message .= '</td></tr>';
		$message .= '</table></td></tr>';
		$message .= '<tr><td>';
		$message .= __( 'Thank you very much for using our service.', 'dlseller' ) . '<br><br>';
		$message .= __( 'Please be sure to check this notification because it is an important contact for continued use of the service under contract.', 'dlseller' );
		$message .= '</td></tr></tbody></table>';

		$message .= '<table style="font-size: 14px; margin-bottom: 10px; width: 100%; border-collapse: collapse; border: 1px solid #ddd;">';
		$message .= '<caption style="background-color: #111; margin-bottom: 20px; padding: 15px; color: #fff; font-size: 15px; font-weight: 700; text-align: left;">';
		$message .= __( '** Contract contents **', 'dlseller' );
		$message .= '</caption><tbody>';
		$message .= '<tr><td style="background-color: #f9f9f9; padding: 12px; width: 33%; border: 1px solid #ddd; text-align: left;">';
		$message .= __( 'Order ID', 'dlseller' );
		$message .= '</td><td style="padding: 12px; width: 67%; border: 1px solid #ddd;">';
		$message .= esc_attr( $order_id );
		$message .= '</td></tr>';
		$message .= '<tr><td style="background-color: #f9f9f9; padding: 12px; width: 33%; border: 1px solid #ddd; text-align: left;">';
		$message .= __( 'Application Date', 'dlseller' );
		$message .= '</td><td style="padding: 12px; width: 67%; border: 1px solid #ddd;">';
		$message .= esc_attr( $order_data['order_date'] );
		$message .= '</td></tr>';
		$message .= '<tr><td style="background-color: #f9f9f9; padding: 12px; width: 33%; border: 1px solid #ddd; text-align: left;">';
		$message .= __( 'Settlement amount', 'usces' );
		$message .= '</td><td style="padding: 12px; width: 67%; border: 1px solid #ddd;">';
		$message .= usces_crform( $continue_data['price'], true, false, 'return' );
		$message .= '</td></tr>';
		$message .= '</tbody></table>';
		$message .= '<p style="margin-bottom: 40px; font-size: 13px;">';
		$message .= __( 'Currency', 'usces' ) . ' : ' . __( usces_crcode( 'return' ), 'usces' );
		$message .= '</p>';
		$message .= '<hr style="margin: 40px 0 30px; border-style: none; border-top: 1px solid #ddd;">';

		$message .= '<table style="margin-bottom: 40px; font-size: 14px; width: 100%; border-collapse: collapse;">';
		$message .= '<caption style="font-size: 15px; font-weight: 700; text-align: left; margin-bottom: 15px;">';
		$message .= __( 'Notification content', 'usces' );
		$message .= '</caption><tbody>';

	} else {
		$message  = __( 'Member ID', 'dlseller' ) . ' : ' . $member_info['ID'] . "\r\n";
		$message .= __( 'Contractor name', 'dlseller' ) . ' : ' . sprintf( _x( '%s', 'honorific', 'usces' ), $name ) . "\r\n\r\n\r\n";
		$message .= __( 'Thank you very much for using our service.', 'dlseller' ) . "\r\n\r\n";
		$message .= __( 'Please be sure to check this notification because it is an important contact for continued use of the service under contract.', 'dlseller' ) . "\r\n\r\n";

		$message .= __( '** Contract contents **', 'dlseller' ) . "\r\n";
		$message .= __( 'Order ID', 'dlseller' ) . ' : ' . $order_id . "\r\n";
		$message .= __( 'Application Date', 'dlseller' ) . ' : ' . $order_data['order_date'] . "\r\n";
		$message .= __( 'Settlement amount', 'usces' ) . ' : ' . usces_crform( $continue_data['price'], true, false, 'return' ) . "\r\n";
		$message .= usces_mail_line( 2, $mail ); // --------------------
		$message .= '(' . __( 'Currency', 'usces' ) . ' : ' . __( usces_crcode( 'return' ), 'usces' ) . ")\r\n\r\n";
	}

	switch ( $flag ) {
		case 'NOW':
		case 'PASSED':
			$limit = date_i18n( __( 'F, Y' ), strtotime( substr( current_time( 'mysql', 0 ), 0, 2 ) . $limits[1] . '-' . $limits[0] . '-01' ) );
			if ( function_exists( 'usces_is_html_mail' ) && usces_is_html_mail() ) {
				$message_text = sprintf( __( 'Currently registered credit card expiration date is %s, ', 'dlseller' ), $limit ) . '<br>';
				if ( 'NOW' === $flag ) {
					$message_text .= __( 'So you keep on this you will not be able to pay next month.', 'dlseller' ) . '<br>';
				} else {
					$message_text .= __( 'So your payment of this month is outstanding payment.', 'dlseller' ) . '<br>';
				}
				$message .= '<tr><td style="background-color: #f9f9f9; padding: 30px;">';
				$message .= '<table style="width: 100%;"><tbody>';
				$message .= '<tr><td colspan="2" style="padding: 0;">';
				$message .= $message_text;
				$message .= '</td></tr>';
				$message .= '</tbody></table>';
				$message .= '</td></tr>';

				$message .= '<tr><td><table style="width: 100%; margin-top: 20px; margin-bottom: 40px;">';
				$message .= '<tbody><tr><td>';
				$message .= __( 'If you have received a new credit card, ', 'dlseller' );
				$message .= '<br>';
				$message .= __( 'Please click the URL below and update the card information during this month.', 'dlseller' );
				$message .= '<br>';
			} else {
				$message .= __( '---------------------------------------------------------', 'dlseller' ) . "\r\n";
				$message .= sprintf( __( 'Currently registered credit card expiration date is %s, ', 'dlseller' ), $limit ) . "\r\n";
				if ( 'NOW' === $flag ) {
					$message .= __( 'So you keep on this you will not be able to pay next month.', 'dlseller' ) . "\r\n";
				} else {
					$message .= __( 'So your payment of this month is outstanding payment.', 'dlseller' ) . "\r\n";
				}
				$message .= __( '---------------------------------------------------------', 'dlseller' ) . "\r\n\r\n";
				$message .= __( 'If you have received a new credit card, ', 'dlseller' ) . "\r\n";
				$message .= __( 'Please click the URL below and update the card information during this month.', 'dlseller' ) . "\r\n";
			}
			break;

		case 'UPDATE':
			if ( function_exists( 'usces_is_html_mail' ) && usces_is_html_mail() ) {
				$message .= '<tr><td style="background-color: #f9f9f9; padding: 30px;">';
				$message .= '<table style="width: 100%;"><tbody>';
				$message .= '<tr><td colspan="2" style="padding: 0;">';
				$message .= __( 'Automatic payment settlement is not completed normally.', 'dlseller' );
				$message .= '</td></tr>';
				$message .= '</tbody></table>';
				$message .= '</td></tr>';

				$message .= '<tr><td><table style="width: 100%; margin-top: 20px; margin-bottom: 40px;">';
				$message .= '<tbody><tr><td>';
				$message .= __( 'Please click the URL below and update the card information.', 'dlseller' );
				$message .= '<br>';
			} else {
				$message .= __( '---------------------------------------------------------', 'dlseller' ) . "\r\n";
				$message .= __( 'Automatic payment settlement is not completed normally.', 'dlseller' ) . "\r\n";
				$message .= __( '---------------------------------------------------------', 'dlseller' ) . "\r\n\r\n";
				$message .= __( 'Please click the URL below and update the card information.', 'dlseller' ) . "\r\n";
			}
			break;
	}

	if ( function_exists( 'usces_is_html_mail' ) && usces_is_html_mail() ) {
		$link     = $nonsessionurl . $delim . 'dlseller_card_update=login&dlseller_up_mode=1&dlseller_order_id=' . $order_id;
		$message .= __( 'Sorry for troubling you, please process it.', 'dlseller' );
		$message .= '</td></tr>';
		$message .= '<tr><td style="padding-top: 20px">';
		$message .= '<a style="display:block;text-align: center;background-color: #777;color:#fff;text-decoration: none;padding:20px" href="' . $link . '">';
		$message .= __( 'Member page', 'usces' );
		$message .= '</a></td></tr>';
		$message .= '</tbody></table>';
		$message .= '<hr style="margin: 40px 0 30px; border-style: none; border-top: 1px solid #ddd;">';
		$message .= '</td></tr>';

		$message .= '<tr><td>';
		$message .= __( 'If the card information update procedure failed, please contact us at the following email address.', 'dlseller' );
		$message .= '<br><br>';
		$message .= __( 'Thank you.', 'dlseller' );
		$message .= '</td></tr>';

		$message .= '</tbody></table>';
		$message .= '</td></tr>';

		$message .= '<tr><td>';
		$message .= '<hr style="margin: 50px 0 0; border-style: none; border-top: 3px solid #777;" />';
		$message .= '</td></tr>';

		$message .= '<tr><td style="padding:20px 30px">';
		$message .= do_shortcode( wpautop( $mail_data['footer']['ordermail'] ) );

		$message .= '</td></tr>';

		$message .= '</tbody></table>';
		$message .= '</td></tr></tbody></table>';
	} else {
		$message .= __( 'Sorry for troubling you, please process it.', 'dlseller' ) . "\r\n\r\n\r\n";
		$message .= $nonsessionurl . $delim . 'dlseller_card_update=login&dlseller_up_mode=1&dlseller_order_id=' . $order_id . "\r\n\r\n";
		$message .= __( 'If the card information update procedure failed, please contact us at the following email address.', 'dlseller' ) . "\r\n\r\n";
		$message .= __( 'Thank you.', 'dlseller' ) . "\r\n\r\n\r\n";
		$message .= $mail_data['footer']['ordermail'];
	}

	$message = apply_filters( 'dlseller_filter_card_update_mail', $message, $member_id, $order_id );

	$ret = array(
		'mailAddress' => $mail,
		'name'        => $name,
		'subject'     => $subject,
		'message'     => $message,
	);

	wp_send_json( $ret );
}

/**
 * Send mail
 * wp_ajax_dlseller_send_mail_ajax
 *
 * @return json
 */
function dlseller_send_mail_ajax() {
	global $wpdb, $usces;

	$_POST = $usces->stripslashes_deep_post( $_POST );

	$order_id  = $_POST['oid'];
	$member_id = $_POST['mid'];
	$name      = trim( urldecode( $_POST['name'] ) );
	$headers   = '';
	$message   = trim( urldecode( $_POST['message'] ) );
	if ( function_exists( 'usces_is_html_mail' ) && usces_is_html_mail() ) {
		$headers = 'Content-Type: text/html';
		$message = wpautop( $message );
	}

	$para = array(
		'to_name'      => sprintf( _x( '%s', 'honorific', 'usces' ), $name ),
		'to_address'   => trim( $_POST['mailaddress'] ),
		'from_name'    => get_option( 'blogname' ),
		'from_address' => $usces->options['sender_mail'],
		'return_path'  => $usces->options['sender_mail'],
		'subject'      => trim( urldecode( $_POST['subject'] ) ),
		'message'      => $message,
		'headers'      => $headers,
	);

	$res = usces_send_mail( $para );
	if ( $res ) {
		$continuation_table = $wpdb->prefix . 'usces_continuation';
		$sel_query          = $wpdb->prepare( "SELECT `con_condition` FROM {$continuation_table} WHERE `con_order_id` = %d", $order_id );
		$condition          = $wpdb->get_var( $sel_query );
		$condition         .= __( 'Credit card update request', 'dlseller' ) . '<br />(' . date_i18n( __( 'F j, Y' ), current_time( 'timestamp', 0 ) ) . ')';
		$upd_query          = $wpdb->prepare( "UPDATE {$continuation_table} SET `con_condition` = %s WHERE `con_order_id` = %d",
			$condition,
			$order_id
		);
		$res = $wpdb->query( $upd_query );

		$bcc_para = array(
			'to_name'      => apply_filters( 'usces_filter_bccmail_to_admin_name', 'Shop Admin' ),
			'to_address'   => $usces->options['order_mail'],
			'from_name'    => apply_filters( 'usces_filter_bccmail_from_admin_name', 'Welcart Auto BCC' ),
			'from_address' => $usces->options['sender_mail'],
			'return_path'  => $usces->options['sender_mail'],
			'subject'      => trim( urldecode( $_POST['subject'] ) ) . ' to ' . sprintf( _x( '%s', 'honorific', 'usces' ), $name ),
			'message'      => $message,
			'headers'      => $headers,
		);

		usces_send_mail( $bcc_para );

		die( 'success' );

	} else {
		die( 'error' );
	}
}

/**
 * Frequency name
 *
 * @param int    $post_id Post ID.
 * @param string $type    String or Timestamp.
 * @param string $out     Return value or echo.
 * @return mixed
 */
function dlseller_frequency_name( $post_id, $type = '', $out = '' ) {
	global $usces;

	$frequency = (int) $usces->getItemFrequency( $post_id );
	if ( 'amount' === $type ) {
		switch( $frequency ) {
			case 1:
				$name = __( 'Monthly Fee', 'dlseller' );
				break;
			case 6:
				$name = __( 'Semiannual Fee', 'dlseller' );
				break;
			case 12:
				$name = __( 'Annual Fee', 'dlseller' );
				break;
		}
	} else {
		switch ( $frequency ) {
			case 1:
				$name = __( 'Monthly Fee', 'dlseller' );
				break;
			case 6:
				$name = __( 'Semiannual Fee', 'dlseller' );
				break;
			case 12:
				$name = __( 'Annual Fee', 'dlseller' );
				break;
		}
	}
	$name = apply_filters( 'dlseller_filter_frequency_name', $name, $post_id, $type );

	if ( 'return' === $out ) {
		return $name;
	} else {
		echo $name;
	}
}

/**
 * 初回課金日
 *
 * @param int    $post_id  Post ID.
 * @param string $type     String or Timestamp.
 * @param mixed  $order_id Order ID.
 * @return mixed
 */
function dlseller_first_charging( $post_id, $type = '', $order_id = '' ) {
	global $usces, $wpdb;

	$now           = current_time( 'mysql' );
	$thisyear      = (int) substr( $now, 0, 4 );
	$thismonth     = (int) substr( $now, 5, 2 );
	$today         = (int) substr( $now, 8, 2 );
	$usces_item    = $usces->get_item( $post_id );
	$continue_data = array();

	/* 受注日課金 */
	if ( 99 === (int) $usces_item['item_chargingday'] ) {
		if ( ! empty( $order_id ) ) {
			$continuation_table = $wpdb->prefix . 'usces_continuation';
			$query              = $wpdb->prepare( "SELECT `con_chargingday` AS `chargingday`, `con_next_charging` AS `chargedday`, `con_startdate` AS `startdate` FROM {$continuation_table} WHERE `con_order_id` = %d", $order_id );
			$continue_data      = $wpdb->get_row( $query, ARRAY_A );
			if ( empty( $continue_data['chargedday'] ) ) {
				list( $year, $month, $day ) = explode( '-', $continue_data['startdate'] );
				$time                       = mktime( 0, 0, 0, (int) $month, (int) $day, (int) $year );
			} else {
				list( $year, $month, $day ) = explode( '-', $continue_data['chargedday'] );
				$time                       = mktime( 0, 0, 0, (int) $month + $usces_item['item_frequency'], (int) $day, (int) $year );
				$time                       = dlseller_get_valid_lastday( $time, $continue_data['chargedday'], $usces_item['item_frequency'] );
				if ( $continue_data['chargingday'] !== (int) $day ) {
					$time = dlseller_get_valid_date( $time, $continue_data['chargingday'] );
				}
			}
		} else {
			$nextday                    = current_time( 'Y-m-d' );
			list( $year, $month, $day ) = explode( '-', $nextday );
			$time                       = mktime( 0, 0, 0, (int) $month, (int) $day + 1, (int) $year );
		}

	/* 課金日指定 */
	} else {
		$chargingday = $usces_item['item_chargingday'];
		if ( $today < $chargingday ) {
			$month = $thismonth;
		} else {
			$month = $thismonth + 1;
		}
		$time = mktime( 0, 0, 0, $month, $chargingday, $thisyear );
	}

	$time = apply_filters( 'dlseller_filter_first_charging', $time, $post_id, $usces_item, $order_id, $continue_data );

	if ( 'time' === $type ) {
		return $time;
	} else {
		$date = date_i18n( 'Y-m-d', $time );
		return $date;
	}
}

/**
 * 次回課金日
 *
 * @param int    $order_id Order ID.
 * @param string $type     String or Timestamp.
 * @return mixed
 */
function dlseller_next_charging( $order_id, $type = '' ) {
	global $usces, $wpdb;

	$continuation_table = $wpdb->prefix . 'usces_continuation';
	$query              = $wpdb->prepare( "SELECT `con_post_id`, `con_chargingday` AS `chargingday`, `con_next_charging` AS `chargedday`, `con_startdate` AS `startdate` FROM {$continuation_table} WHERE `con_order_id` = %d", $order_id );
	$continue_data      = $wpdb->get_row( $query, ARRAY_A );

	if ( empty( $continue_data ) ) {
		return;
	}

	if ( empty( $continue_data['chargedday'] ) ) {
		$post_id    = $continue_data['con_post_id'];
		$usces_item = $usces->get_item( $post_id );

		list( $year, $month, $day ) = explode( '-', $continue_data['startdate'] );

		/* 受注日課金 */
		if ( 99 === (int) $usces_item['item_chargingday'] ) {
			$time = mktime( 0, 0, 0, (int) $month + $usces_item['item_frequency'], (int) $day, (int) $year );
			$time = dlseller_get_valid_lastday( $time, $continue_data['startdate'], $usces_item['item_frequency'] );

		/* 課金日指定 */
		} else {
			$time = mktime( 0, 0, 0, $month, $day, $year ); /* 初回課金日 */
		}

	} else {
		$chargedday                 = $continue_data['chargedday'];
		list( $year, $month, $day ) = explode( '-', $chargedday );
		$time                       = mktime( 0, 0, 0, (int) $month, (int) $day, (int) $year );
	}

	$time = apply_filters( 'dlseller_filter_next_charging', $time, $order_id, $continue_data );

	if ( 'time' == $type ) {
		return $time;
	} else{
		$date = date_i18n( 'Y-m-d', $time );
		return $date;
	}
}

/**
 * 次回契約更新日
 *
 * @param int    $order_id Order ID.
 * @param string $type     String or Timestamp.
 * @return mixed
 */
function dlseller_next_contracting( $order_id, $type = '' ) {
	global $usces, $wpdb;

	$continuation_table = $wpdb->prefix . 'usces_continuation';
	$query              = $wpdb->prepare( "SELECT `con_post_id`, 
		`con_chargingday` AS `chargingday`, 
		`con_interval` AS `interval`, 
		`con_next_contracting` AS `contractedday`, 
		`con_startdate` AS `startdate` 
		FROM {$continuation_table} 
		WHERE `con_order_id` = %d",
		$order_id
	);
	$continue_data = $wpdb->get_row( $query, ARRAY_A );

	if ( empty( $continue_data ) ) {
		return null;
	}

	if ( empty( $continue_data['interval'] ) ) {
		$time = null;
		$date = null;

	} elseif ( empty( $continue_data['contractedday'] ) ) {

		if ( empty( $continue_data['startdate'] ) ) {
			$time = null;
			$date = null;

		} else {
			$contractedday              = $continue_data['startdate'];
			list( $year, $month, $day ) = explode( '-', $contractedday );
			$time                       = mktime( 0, 0, 0, (int) $month + $continue_data['interval'], (int) $day, (int) $year );
			$time                       = dlseller_get_valid_lastday( $time, $contractedday, $continue_data['interval'] );
			$date                       = date_i18n( 'Y-m-d', $time );
		}

	} else {
		$contractedday              = $continue_data['contractedday'];
		list( $year, $month, $day ) = explode( '-', $contractedday );
		$time                       = mktime( 0, 0, 0, (int) $month + $continue_data['interval'], (int) $day, (int) $year );
		$time                       = dlseller_get_valid_lastday( $time, $contractedday, $continue_data['interval'] );
		if ( (int) $continue_data['chargingday'] !== (int) $day ) {
			$time = dlseller_get_valid_date( $time, $continue_data['chargingday'] );
		}
		$date = date_i18n( 'Y-m-d', $time );
	}

	$time = apply_filters( 'dlseller_filter_next_contracting', $time, $order_id, $continue_data );

	if ( 'time' === $type ) {
		return $time;
	} else {
		return $date;
	}
}

/**
 * Get contents filename
 *
 * @param int    $post_id Post ID.
 * @param string $sku     SKU code.
 * @return string
 */
function dlseller_get_filename( $post_id, $sku = '' ) {
	$product       = wel_get_product( $post_id );
	$file          = $product['dlseller_file'];
	return apply_filters( 'dlseller_filter_filename', $file, $post_id, $sku );
}

/**
 * Payment method
 * usces_fiter_the_payment_method
 *
 * @param array  $payments Payment method data.
 * @param string $value    Text.
 * @return array
 */
function dlseller_fiter_the_payment_method( $payments, $value ) {
	$have_continue_charge    = dlseller_have_continue_charge();
	$dl_payments             = array();
	$continue_payment_method = apply_filters( 'usces_filter_the_continue_payment_method', array( 'acting_remise_card', 'acting_paypal_ec' ) );
	$have_shipped            = dlseller_have_shipped();

	if ( $have_continue_charge ) {
		foreach ( $payments as $payment ) {
			if ( ! in_array( $payment['settlement'], $continue_payment_method ) ) {
				continue;
			}
			if ( isset( $usces->options['acting_settings']['remise']['continuation'] ) && 'on' !== $usces->options['acting_settings']['remise']['continuation'] && 'acting_remise_card' === $payment['settlement'] ) {
				continue;
			} elseif ( isset( $usces->options['acting_settings']['paypal']['continuation'] ) && 'on' !== $usces->options['acting_settings']['paypal']['continuation'] && 'acting_paypal_ec' === $payment['settlement'] ) {
				continue;
			}
			$dl_payments[] = $payment;
		}
		ksort( $dl_payments );
	} else {
		if ( ! dlseller_have_shipped() ) {
			foreach ( $payments as $payment ) {
				if ( 'COD' === $payment['settlement'] ) {
					continue;
				}
				$dl_payments[] = $payment;
			}
			ksort( $dl_payments );
		} else {
			$dl_payments = $payments;
		}
	}
	$dl_payments = apply_filters( 'dlseller_filter_the_payment_method_restriction', $dl_payments, $value );
	return $dl_payments;
}

/**
 * Payment method choices
 * usces_filter_the_payment_method_choices
 *
 * @param string $html     HTML form.
 * @param array  $payments Payment method data.
 * @return string
 */
function dlseller_filter_the_payment_method_choices( $html, $payments ) {

	if ( ! dlseller_have_shipped() && ! dlseller_require_payment() ) {
		$nopayment_message = apply_filters( 'dlseller_filter_nopayment_message', __( 'no payment', 'dlseller' ) );
		$nopayment         = apply_filters( 'dlseller_filter_nopayment_name', __( 'no payment', 'dlseller' ) );
		$html              = '<div>' . $nopayment_message . '</div><input name="offer[payment_name]" type="hidden" value="' . $nopayment . '" />';
	}
	return $html;
}

/**
 * Pre-processing of member data deletion
 * usces_action_pre_delete_memberdata
 *
 * @param int $member_id Member ID.
 */
function dlseller_action_pre_delete_memberdata( $member_id ) {
	global $wpdb;

	$continue_message   = '';
	$continuation_table = $wpdb->prefix . 'usces_continuation';
	$query              = $wpdb->prepare( "SELECT `con_order_id`, `con_next_charging` FROM {$continuation_table} WHERE `con_member_id` = %d AND `con_status` = 'continuation'", $member_id );
	$res                = $wpdb->get_results( $query, ARRAY_A );
	foreach ( $res as $row ) {
		$order_id          = $row['con_order_id'];
		$next_charging     = ( empty( $row['con_next_charging'] ) ) ? dlseller_next_charging( $order_id ) : $row['con_next_charging'];
		$continue_message .= __( 'Order ID', 'dlseller' ) . ' : ' . $order_id . ' ' . __( 'Next Withdrawal Date', 'dlseller' ) . ' : ' . date_i18n( __( 'Y/m/d' ), strtotime( $next_charging ) ) . "\r\n";
	}
	if ( '' !== $continue_message ) {
		$_SESSION[ 'usces_member_' . $member_id ]['continue_message'] = $continue_message;
	}
}

/**
 * Member data deletion notification
 * usces_filter_send_delmembermail_notice
 *
 * @param string $message Message.
 * @param array  $member  Member data.
 * @return string
 */
function dlseller_filter_send_delmembermail_notice( $message, $member ) {
	$continue_message = ( empty( $_SESSION['usces_member_' . $member['ID'] ]['continue_message'] ) ) ? '' : $_SESSION[ 'usces_member_' . $member['ID'] ]['continue_message'];
	if ( '' !== $continue_message ) {
		$message .= "\r\n" . __( '*** There is a contract of auto continuation billing.', 'dlseller' ) . "\r\n" . $continue_message . "\r\n";
		unset( $_SESSION[ 'usces_member_' . $member['ID'] ]['continue_message'] );
	}
	return $message;
}

/**
 * Continuation status
 *
 * @param string $status Status.
 * @param string $out    Return value or echo.
 * @return mixed
 */
function dlseller_status_name( $status, $out = '' ) {
	if ( 'continuation' === $status ) {
		$status_name = __( 'continuation', 'dlseller' );
	} else {
		$status_name = __( 'cancellation', 'dlseller' );
	}

	if ( 'return' === $out ) {
		return $status_name;
	} else {
		echo $status_name;
	}
}

/**
 * Get continuation status
 *
 * @param int $member_id Member ID.
 * @param int $order_id  Order ID.
 * @return string
 */
function dlseller_get_continue_status( $member_id, $order_id ) {
	global $wpdb;

	$continuation_table = $wpdb->prefix . 'usces_continuation';
	$query              = $wpdb->prepare( "SELECT `con_status` FROM {$continuation_table} WHERE `con_member_id` = %d AND `con_order_id` = %d", $member_id, $order_id );

	return $wpdb->get_var( $query );
}

/**
 * Output PDF
 * usces_action_order_print_cart_row
 *
 * @param object $pdf  TCPDF.
 * @param object $data Order data.
 * @param array  $pdf_args {
 *     The array of pdf data.
 *     @type int    $page       Page no.
 *     @type float  $x          X axis.
 *     @type float  $y          Y axis.
 *     @type float  $onep       Page height.
 *     @type float  $next_y     Next Y axis.
 *     @type array  $border     Border.
 *     @type int    $index      Index.
 *     @type array  $cart_row   Cart data.
 *     @type array  $fontsizes  Font size.
 *     @type float  $lineheight Line height.
 *     @type float  $linetop    Line top.
 *     @type float  $font       Font.
 */
function dlseller_action_order_print_cart_row( $pdf, $data, $pdf_args ) {
	global $usces, $wpdb;

	extract( $pdf_args );
	$order_id           = $data->order['ID'];
	$member_id          = $data->customer['mem_id'];
	$continuation_table = $wpdb->prefix . 'usces_continuation';
	$query              = $wpdb->prepare( "SELECT `con_interval` AS `interval`, `con_next_charging` AS `next_charging`, `con_next_contracting` AS `next_contracting` FROM {$continuation_table} WHERE `con_order_id` = %d", $order_id );
	$continue_data      = $wpdb->get_row( $query, ARRAY_A );
	if ( $continue_data ) {
		$font                                    = '';
		list( $fontsize, $lineheight, $linetop ) = usces_set_font_size( 8 );
		$next_charging = ( empty( $continue_data['next_charging'] ) ) ? dlseller_next_charging( $order_id ) : $continue_data['next_charging'];
		$line          = __( 'Next Withdrawal Date', 'dlseller' ) . ' : ' . date_i18n( __( 'Y/m/d' ), strtotime( $next_charging ) );
		$pdf->SetFont( $font, '', $fontsize );
		$pdf->SetXY( $x + 6.0, $pdf->GetY() + $linetop );
		$pdf->MultiCell( 81.6, $lineheight - 0.2, usces_conv_euc( $line ), $border, 'L' );
		$interval = $continue_data['interval'];
		if ( 0 < (int) $interval ) {
			$next_contracting = ( empty( $continue_data['next_contracting'] ) ) ? dlseller_next_contracting( $order_id ) : $continue_data['next_contracting'];
			$line             = __( 'Renewal Date', 'dlseller' ) . ' : ' . date_i18n( __( 'Y/m/d' ), strtotime( $next_contracting ) );
			$pdf->SetFont( $font, '', $fontsize );
			$pdf->SetXY( $x + 6.0, $pdf->GetY() + $linetop );
			$pdf->MultiCell( 81.6, $lineheight - 0.2, usces_conv_euc( $line ), $border, 'L' );
		}
	}
}

/**
 * Confirm message
 * usces_action_confirm_after_form
 */
function dlseller_action_confirm_after_form() {
	if ( usces_is_member_system() && usces_is_member_system_point() && usces_is_login() && ! usces_is_available_point() ) {
		$mes = '<p class="available_point_message">' . __( 'Sorry, can not use the points to the continuation accounting items.', 'dlseller' ) . '</p>';
	} else {
		$mes = '';
	}
	echo $mes;
}

/**
 * Have continuation order
 *
 * @param int $member_id Member ID.
 * @return bool
 */
function dlseller_have_member_continue_order( $member_id ) {
	global $wpdb;

	$continue = false;

	$continuation_table = $wpdb->prefix . 'usces_continuation';
	$query              = $wpdb->prepare( "SELECT * FROM {$continuation_table} WHERE `con_member_id` = %d AND `con_status` = 'continuation'", $member_id );
	$continue_order     = $wpdb->get_results( $query, ARRAY_A );
	if ( 0 < count( $continue_order ) ) {
		$continue = true;
	}
	return $continue;
}

/**
 * Reminder mail form
 */
function dlseller_action_admin_reminder_mailform() {
	global $usces;

	$usces->options = get_option( 'usces' );
	if ( function_exists( 'usces_mail_data' ) && version_compare( USCES_VERSION, '2.3-beta', '>=' ) ) {
		$mail_data         = usces_mail_data();
		$input_name_title  = 'email_dlseller_reminder[title]';
		$input_name_header = 'email_dlseller_reminder[header]';
		$input_name_footer = 'email_dlseller_reminder[footer]';
	} else {
		$mail_data         = $usces->options['mail_data'];
		$input_name_title  = 'title[dlseller_reminder]';
		$input_name_header = 'header[dlseller_reminder]';
		$input_name_footer = 'footer[dlseller_reminder]';
	}
	if ( empty( $mail_data['title']['dlseller_reminder'] ) ) {
		$mail_data['title']['dlseller_reminder'] = __( 'Guidance of the settlement date', 'dlseller' );
	}
	if ( empty( $mail_data['header']['dlseller_reminder'] ) ) {
		$mail_data['header']['dlseller_reminder'] = __( 'This e-mail is a guide for the next settlement date of the auto continuation charging.', 'dlseller' ) . "\n\n\n";
	}
	if ( empty( $mail_data['footer']['dlseller_reminder'] ) ) {
		$mail_data['footer']['dlseller_reminder'] = $mail_data['footer']['othermail'];
	}
	?>
		<div class="postbox">
		<h3 class="hndle">
			<span id="title_dlseller_reminder"><?php esc_html_e( 'Settlement reminder-email', 'dlseller' ); ?><?php esc_html_e( '(automatic transmission)', 'dlseller' ); ?></span>
			<a style="cursor:pointer;" onclick="toggleVisibility('ex_dlseller_reminder');"> (<?php esc_html_e( 'explanation', 'usces' ); ?>) </a>
		<?php if ( function_exists( 'usces_is_html_mail' ) && usces_is_html_mail() ) : ?>
			<button type="button" class="email-preview button-primary" onclick="showPopupPreviewEmail('dlseller_reminder')"><?php esc_attr_e( 'Preview', 'usces' ); ?></button>
		<?php endif ?>
		</h3>
		<div class="inside">
		<table class="form_table">
			<tr>
				<th width="150"><?php esc_html_e( 'Title', 'usces' ); ?></th>
				<td><input name="<?php echo esc_attr( $input_name_title ); ?>" id="title[dlseller_reminder]" type="text" class="mail_title" value="<?php echo esc_attr( $mail_data['title']['dlseller_reminder'] ); ?>" /></td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'header', 'usces' ); ?></th>
				<td>
				<?php
				if ( function_exists( 'usces_is_html_mail' ) && usces_is_html_mail() ) :
					wp_editor(
						$mail_data['header']['dlseller_reminder'],
						'headerdlseller_reminder',
						array(
							'dfw'           => true,
							'tabindex'      => 1,
							'textarea_name' => $input_name_header,
							'textarea_rows' => 10,
							'editor_class'  => 'mail_header_html',
							'editor_height' => '234',
						)
					);
				else :
					?>
					<textarea name="<?php echo esc_attr( $input_name_header ); ?>" id="header[dlseller_reminder]" class="mail_header"><?php echo esc_attr( $mail_data['header']['dlseller_reminder'] ); ?></textarea>
					<?php
				endif;
				?>
				</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'footer', 'usces' ); ?></th>
				<td>
				<?php
				if ( function_exists( 'usces_is_html_mail' ) && usces_is_html_mail() ) :
					wp_editor(
						$mail_data['footer']['dlseller_reminder'],
						'footerdlseller_reminder',
						array(
							'dfw'           => true,
							'tabindex'      => 1,
							'textarea_name' => $input_name_footer,
							'textarea_rows' => 10,
							'editor_class'  => 'mail_footer_html',
							'editor_height' => '234',
						)
					);
				else :
					?>
					<textarea name="<?php echo esc_attr( $input_name_footer ); ?>" id="footer[dlseller_reminder]" class="mail_footer"><?php echo esc_attr( $mail_data['footer']['dlseller_reminder'] ); ?></textarea>
					<?php
				endif;
				?>
				</td>
				<td>&nbsp;</td>
			</tr>
		</table>
		<hr size="1" color="#CCCCCC" />
		<div id="ex_dlseller_reminder" class="explanation"><?php esc_html_e( 'Reminder-email of settlement of the auto continuation charging.', 'dlseller' ); ?><?php esc_html_e( 'Send automatically on reminder-email sent date.', 'dlseller' ); ?></div>
		</div>
		</div><!--postbox-->
	<?php
}

/**
 * Contract renewal mail form
 */
function dlseller_action_admin_contract_renewal_mailform() {
	global $usces;

	$usces->options = get_option( 'usces' );
	if ( function_exists( 'usces_mail_data' ) && version_compare( USCES_VERSION, '2.3-beta', '>=' ) ) {
		$mail_data         = usces_mail_data();
		$input_name_title  = 'email_dlseller_contract_renewal[title]';
		$input_name_header = 'email_dlseller_contract_renewal[header]';
		$input_name_footer = 'email_dlseller_contract_renewal[footer]';
	} else {
		$mail_data         = $usces->options['mail_data'];
		$input_name_title  = 'title[dlseller_contract_renewal]';
		$input_name_header = 'header[dlseller_contract_renewal]';
		$input_name_footer = 'footer[dlseller_contract_renewal]';
	}
	if ( empty( $mail_data['title']['dlseller_contract_renewal'] ) ) {
		$mail_data['title']['dlseller_contract_renewal'] = __( 'Guidance of the contract renewal date', 'dlseller' );
	}
	if ( empty( $mail_data['header']['dlseller_contract_renewal'] ) ) {
		$mail_data['header']['dlseller_contract_renewal'] = __( 'This e-mail is a guide for the next contract renewal date of the auto continuation charging.', 'dlseller' ) . "\n\n\n";
	}
	if ( empty( $mail_data['footer']['dlseller_contract_renewal'] ) ) {
		$mail_data['footer']['dlseller_contract_renewal'] = $mail_data['footer']['othermail'];
	}
	?>
		<div class="postbox">
		<h3 class="hndle">
			<span id="title_dlseller_contract_renewal"><?php esc_html_e( 'Contract renewal email', 'dlseller' ); ?><?php esc_html_e( '(automatic transmission)', 'dlseller' ); ?></span>
			<a style="cursor:pointer;" onclick="toggleVisibility('ex_dlseller_contract_renewal');"> (<?php esc_html_e( 'explanation', 'usces' ); ?>) </a>
		<?php if ( function_exists( 'usces_is_html_mail' ) && usces_is_html_mail() ) : ?>
			<button type="button" class="email-preview button-primary" onclick="showPopupPreviewEmail('dlseller_contract_renewal')"><?php esc_html_e( 'Preview', 'usces' ); ?></button>
		<?php endif; ?>
		</h3>
		<div class="inside">
		<table class="form_table">
			<tr>
				<th width="150"><?php esc_html_e( 'Title', 'usces' ); ?></th>
				<td><input name="<?php echo esc_attr( $input_name_title ); ?>" id="title[dlseller_contract_renewal]" type="text" class="mail_title" value="<?php echo esc_attr( $mail_data['title']['dlseller_contract_renewal'] ); ?>" /></td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'header', 'usces' ); ?></th>
				<td>
				<?php
				if ( function_exists( 'usces_is_html_mail' ) && usces_is_html_mail() ) :
					wp_editor(
						$mail_data['header']['dlseller_contract_renewal'],
						'headerdlseller_contract_renewal',
						array(
							'dfw'           => true,
							'tabindex'      => 1,
							'textarea_name' => $input_name_header,
							'textarea_rows' => 10,
							'editor_class'  => 'mail_header_html',
							'editor_height' => '234',
						)
					);
				else :
					?>
					<textarea name="<?php echo esc_attr( $input_name_header ); ?>" id="header[dlseller_contract_renewal]" class="mail_header"><?php echo esc_attr( $mail_data['header']['dlseller_contract_renewal'] ); ?></textarea>
					<?php
				endif;
				?>
				</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'footer', 'usces' ); ?></th>
				<td>
				<?php
				if ( function_exists( 'usces_is_html_mail' ) && usces_is_html_mail() ) :
					wp_editor(
						$mail_data['footer']['dlseller_contract_renewal'],
						'footerdlseller_contract_renewal',
						array(
							'dfw'           => true,
							'tabindex'      => 1,
							'textarea_name' => $input_name_footer,
							'textarea_rows' => 10,
							'editor_class'  => 'mail_footer_html',
							'editor_height' => '234',
						)
					);
				else :
					?>
					<textarea name="<?php echo esc_attr( $input_name_footer ); ?>" id="footer[dlseller_contract_renewal]" class="mail_footer"><?php echo esc_attr( $mail_data['footer']['dlseller_contract_renewal'] ); ?></textarea>
					<?php
				endif;
				?>
				</td>
				<td>&nbsp;</td>
			</tr>
		</table>
		<hr size="1" color="#CCCCCC" />
		<div id="ex_dlseller_contract_renewal" class="explanation"><?php esc_html_e( 'Reminder-email of contract renewal of the auto continuation charging.', 'dlseller' ); ?><?php esc_html_e( 'Send automatically on reminder-email sent date.', 'dlseller' ); ?></div>
		</div>
		</div><!--postbox-->
	<?php
}

/**
 * Send reminder mail
 *
 * @param int   $order_id      Order ID.
 * @param array $continue_data Continue data.
 */
function dlseller_send_reminder_mail( $order_id, $continue_data ) {
	global $usces;

	$order_data = $usces->get_order_data( $order_id, 'direct' );
	if ( function_exists( 'usces_mail_data' ) && version_compare( USCES_VERSION, '2.3-beta', '>=' ) ) {
		$mail_data = usces_mail_data();
	} else {
		$mail_data = $usces->options['mail_data'];
	}
	$member_info = $usces->get_member_info( $continue_data['member_id'] );
	$headers     = '';

	$name        = usces_localized_name( trim( $member_info['mem_name1'] ), trim( $member_info['mem_name2'] ), 'return' );
	$subject     = ( ! empty( $mail_data['title']['dlseller_reminder'] ) ) ? $mail_data['title']['dlseller_reminder'] : __( 'Guidance of the settlement date', 'dlseller' );
	$mail_header = ( ! empty( $mail_data['header']['dlseller_reminder'] ) ) ? $mail_data['header']['dlseller_reminder'] . "\r\n\r\n\r\n" : __( 'This e-mail is a guide for the next settlement date of the auto continuation charging.', 'dlseller' ) . "\r\n\r\n\r\n";
	$mail_footer = ( ! empty( $mail_data['footer']['dlseller_reminder'] ) ) ? $mail_data['footer']['dlseller_reminder'] : $mail_data['footer']['othermail'];
	$mail_body   = apply_filters( 'dlseller_filter_reminder_mail_body', usces_order_confirm_message( $order_id ), $order_id, $continue_data );

	if ( function_exists( 'usces_is_html_mail' ) && usces_is_html_mail() ) {
		$headers  = 'Content-Type: text/html';
		$message  = '<table border="0" width="100%" cellspacing="0" cellpadding="0" bgcolor="#eeeeee"><tbody><tr><td>';
		$message .= '<table style="font-size:15px;margin-right:auto;margin-left:auto" border="0" width="600" cellspacing="0" cellpadding="0" align="center" bgcolor="#ffffff"><tbody>';
		// add header.
		$message .= '<tr><td style="padding:20px 30px;">';
		if ( isset( $usces->options['put_customer_name'] ) && 1 === (int) $usces->options['put_customer_name'] ) {
			// translators: %s: name of user.
			$dear_name = sprintf( __( 'Dear %s', 'usces' ), $name );
			if ( false !== strpos( $mail_header, '{customer_name}' ) ) {
				$mail_header = str_replace( '{customer_name}', $dear_name, $mail_header );
			} else {
				$message .= $dear_name . '<br>';
			}
		}
		$message .= do_shortcode( wpautop( $mail_header ) );
		$message .= '</td></tr>';
		// add body.
		$message .= '<tr><td style="padding:20px 30px;">';
		$message .= '<hr style="margin: 0 0 50px; border-style: none; border-top: 3px solid #777;" />';
		$message .= $mail_body;
		$message .= '<hr style="margin: 50px 0 0; border-style: none; border-top: 3px solid #777;" />';
		$message .= '</td></tr>';
		// add footer.
		$message .= '<tr><td style="padding:20px 30px;">';
		$message .= do_shortcode( wpautop( $mail_footer ) );
		$message .= '</td></tr>';
		$message .= '</tbody></table>';
		$message .= '</td></tr></tbody></table>';
	} else {
		if ( isset( $usces->options['put_customer_name'] ) && 1 === (int) $usces->options['put_customer_name'] ) {
			// translators: %s: name of user.
			$dear_name = sprintf( __( 'Dear %s', 'usces' ), $name );
			if ( false !== strpos( $mail_header, '{customer_name}' ) ) {
				$mail_header = str_replace( '{customer_name}', $dear_name, $mail_header );
			} else {
				$mail_header = $dear_name . "\r\n\r\n" . $mail_header;
			}
		}
		$message = $mail_header . $mail_body . $mail_footer;
	}

	$send_para = array(
		'to_name'      => sprintf( _x( '%s', 'honorific', 'usces' ), $name ),
		'to_address'   => $member_info['mem_email'],
		'from_name'    => get_option( 'blogname' ),
		'from_address' => $usces->options['sender_mail'],
		'return_path'  => $usces->options['sender_mail'],
		'subject'      => $subject,
		'message'      => $message,
		'headers'      => $headers,
	);
	$send_para = apply_filters( 'dlseller_filter_send_reminder_mail_para_to_customer', $send_para, $order_data, $continue_data );
	usces_send_mail( $send_para );

	$admin_para = array(
		'to_name'      => apply_filters( 'usces_filter_bccmail_to_admin_name', 'Shop Admin' ),
		'to_address'   => $usces->options['order_mail'],
		'from_name'    => apply_filters( 'usces_filter_bccmail_from_admin_name', 'Welcart Auto BCC' ),
		'from_address' => $usces->options['sender_mail'],
		'return_path'  => $usces->options['sender_mail'],
		'subject'      => $subject,
		'message'      => $message,
		'headers'      => $headers,
	);
	usces_send_mail( $admin_para );
}

/**
 * Send contract renewal mail
 *
 * @param int   $order_id      Order ID.
 * @param array $continue_data Continue data.
 */
function dlseller_send_contract_renewal_mail( $order_id, $continue_data ) {
	global $usces;

	$order_data = $usces->get_order_data( $order_id, 'direct' );
	if ( function_exists( 'usces_mail_data' ) && version_compare( USCES_VERSION, '2.3-beta', '>=' ) ) {
		$mail_data = usces_mail_data();
	} else {
		$mail_data = $usces->options['mail_data'];
	}
	$member_info = $usces->get_member_info( $continue_data['member_id'] );
	$headers     = '';

	$name        = usces_localized_name( trim( $member_info['mem_name1'] ), trim( $member_info['mem_name2'] ), 'return' );
	$subject     = ( ! empty( $mail_data['title']['dlseller_contract_renewal'] ) ) ? $mail_data['title']['dlseller_contract_renewal'] : __( 'Guidance of the contract renewal date', 'dlseller' );
	$mail_header = ( ! empty( $mail_data['header']['dlseller_contract_renewal'] ) ) ? $mail_data['header']['dlseller_contract_renewal'] . "\r\n\r\n\r\n" : __( 'This e-mail is a guide for the next contract renewal date of the auto continuation charging.', 'dlseller' ) . "\r\n\r\n\r\n";
	$mail_footer = ( ! empty( $mail_data['footer']['dlseller_contract_renewal'] ) ) ? $mail_data['footer']['dlseller_contract_renewal'] : $mail_data['footer']['othermail'];
	$mail_body   = apply_filters( 'dlseller_filter_contract_renewal_mail_body', usces_order_confirm_message( $order_id ), $order_id, $continue_data );

	if ( function_exists( 'usces_is_html_mail' ) && usces_is_html_mail() ) {
		$headers  = 'Content-Type: text/html';
		$message  = '<table border="0" width="100%" cellspacing="0" cellpadding="0" bgcolor="#eeeeee"><tbody><tr><td>';
		$message .= '<table style="font-size:15px;margin-right:auto;margin-left:auto" border="0" width="600" cellspacing="0" cellpadding="0" align="center" bgcolor="#ffffff"><tbody>';
		// add header.
		$message .= '<tr><td style="padding:20px 30px;">';
		if ( isset( $usces->options['put_customer_name'] ) && 1 === (int) $usces->options['put_customer_name'] ) {
			// translators: %s: name of user.
			$dear_name = sprintf( __( 'Dear %s', 'usces' ), $name );
			if ( false !== strpos( $mail_header, '{customer_name}' ) ) {
				$mail_header = str_replace( '{customer_name}', $dear_name, $mail_header );
			} else {
				$message .= $dear_name . '<br>';
			}
		}
		$message .= do_shortcode( wpautop( $mail_header ) );
		$message .= '</td></tr>';
		// add body.
		$message .= '<tr><td style="padding:20px 30px;">';
		$message .= '<hr style="margin: 0 0 50px; border-style: none; border-top: 3px solid #777;" />';
		$message .= $mail_body;
		$message .= '<hr style="margin: 50px 0 0; border-style: none; border-top: 3px solid #777;" />';
		$message .= '</td></tr>';
		// add footer.
		$message .= '<tr><td style="padding:20px 30px;">';
		$message .= do_shortcode( wpautop( $mail_footer ) );
		$message .= '</td></tr>';
		$message .= '</tbody></table>';
		$message .= '</td></tr></tbody></table>';
	} else {
		if ( isset( $usces->options['put_customer_name'] ) && 1 === (int) $usces->options['put_customer_name'] ) {
			// translators: %s: name of user.
			$dear_name = sprintf( __( 'Dear %s', 'usces' ), $name );
			if ( false !== strpos( $mail_header, '{customer_name}' ) ) {
				$mail_header = str_replace( '{customer_name}', $dear_name, $mail_header );
			} else {
				$mail_header = $dear_name . "\r\n\r\n" . $mail_header;
			}
		}
		$message = $mail_header . $mail_body . $mail_footer;
	}

	$send_para = array(
		'to_name'      => sprintf( _x( '%s', 'honorific', 'usces' ), $name ),
		'to_address'   => $member_info['mem_email'],
		'from_name'    => get_option( 'blogname' ),
		'from_address' => $usces->options['sender_mail'],
		'return_path'  => $usces->options['sender_mail'],
		'subject'      => $subject,
		'message'      => $message,
		'headers'      => $headers,
	);
	$send_para = apply_filters( 'dlseller_filter_send_contract_renewal_mail_para_to_customer', $send_para, $order_data, $continue_data );
	usces_send_mail( $send_para );

	$admin_para = array(
		'to_name'      => apply_filters( 'usces_filter_bccmail_to_admin_name', 'Shop Admin' ),
		'to_address'   => $usces->options['order_mail'],
		'from_name'    => apply_filters( 'usces_filter_bccmail_from_admin_name', 'Welcart Auto BCC' ),
		'from_address' => $usces->options['sender_mail'],
		'return_path'  => $usces->options['sender_mail'],
		'subject'      => $subject,
		'message'      => $message,
		'headers'      => $headers,
	);
	usces_send_mail( $admin_para );
}

/**
 * Get 'con_id'
 *
 * @param int $order_id Order ID.
 * @return int
 */
function dlseller_get_con_id( $order_id ) {
	global $wpdb;

	$continuation_table = $wpdb->prefix . 'usces_continuation';
	$query              = "SELECT con_id FROM {$continuation_table} WHERE con_order_id = " . $order_id;
	$con_id             = $wpdb->get_var( $query );
	return $con_id;
}

/**
 * Get continuation meta value
 *
 * @param string $key      Meta key.
 * @param int    $order_id Order ID.
 * @return string
 */
function dlseller_get_continuation_meta_value( $key, $con_id ) {
	global $wpdb;

	$continuation_meta_table = $wpdb->prefix . 'usces_continuation_meta';
	$query                   = $wpdb->prepare( "SELECT meta_value FROM {$continuation_meta_table} WHERE con_id = %d AND meta_key = %s",
		$con_id, $key
	);
	$res = $wpdb->get_var( $query );
	return $res;
}

/**
 * Save continuation meta value
 *
 * @param string $key        Meta key.
 * @param string $meta_value Meta value
 * @param int    $order_id   Order ID.
 * @return int
 */
function dlseller_set_continuation_meta_value( $key, $meta_value, $con_id ) {
	global $wpdb;

	if ( empty( $key ) || empty( $con_id ) ) {
		return;
	}

	$continuation_meta_table = $wpdb->prefix . 'usces_continuation_meta';
	$query                   = $wpdb->prepare( "SELECT COUNT(*) FROM {$continuation_meta_table} WHERE con_id = %d AND meta_key = %s",
		$con_id, $key
	);
	$var = $wpdb->get_var( $query );
	if ( 0 < $var ) {
		$query = $wpdb->prepare( "UPDATE {$continuation_meta_table} SET meta_value = %s WHERE con_id = %d AND meta_key = %s",
			$meta_value, $con_id, $key
		);
	} else {
		$query = $wpdb->prepare( "INSERT INTO {$continuation_meta_table} ( con_id, meta_key, meta_value ) VALUES( %d, %s, %s )",
			$con_id, $key, $meta_value
		);
	}
	$res = $wpdb->query( $query );
	return $res;
}

/**
 * Delete continuation meta value
 *
 * @param string  $key    Meta key.
 * @param int     $con_id Continuation ID.
 * @return int
 */
function dlseller_del_continuation_meta( $key = '', $con_id = 0 ) {
	global $wpdb;

	if ( empty( $con_id ) ) {
		return;
	}

	$continuation_meta_table = $wpdb->prefix . 'usces_continuation_meta';
	if ( empty( $key ) ) {
		$query = $wpdb->prepare( "DELETE FROM {$continuation_meta_table} WHERE con_id = %d", $con_id );
	} else {
		$query = $wpdb->prepare( "DELETE FROM {$continuation_meta_table} WHERE con_id = %d AND meta_key = %s", $con_id, $key );
	}
	$res = $wpdb->query( $query );
	return $res;
}

/**
 * Get valid lastday
 *
 * @param timestamp $time      Check date.
 * @param string    $base_date Base date.
 * @param int       $month     Month.
 * @param string    $sp        Separator.
 * @return timestamp
 */
function dlseller_get_valid_lastday( $time, $base_date, $month, $sp = '-' ) {
	$check_date                                   = date_i18n( 'Y-m-d', $time );
	list( $check_year, $check_month, $check_day ) = explode( $sp, $check_date );
	list( $base_year, $base_month, $base_day )    = explode( $sp, $base_date );
	$add_month                                    = (int) $base_month + (int) $month;
	if ( 12 < $add_month ) {
		$add_month = $add_month - 12;
	}
	if ( (int) $check_month !== $add_month ) {
		$m = (int) $check_month - $add_month;
		if ( 1 === $m ) {
			$time = mktime( 0, 0, 0, $check_month, 0, $check_year );
		}
	}
	return $time;
}

/**
 * Get valid date
 *
 * @param timestamp $time Check date.
 * @param int       $day  Day.
 * @param string    $sp   Separator.
 * @return timestamp
 */
function dlseller_get_valid_date( $time, $day, $sp = '-' ) {
	$check_date                                   = date_i18n( 'Y-m-d', $time );
	list( $check_year, $check_month, $check_day ) = explode( $sp, $check_date );
	$check_date                                   = sprintf( '%04d-%02d-%02d', (int) $check_year, (int) $check_month, (int) $day );
	if ( ! dlseller_check_date( $check_date ) ) {
		$d     = ( (int) $check_day < (int) $day ) ? 1 : -1;
		$check = false;
		do {
			$day = $day - $d;
			if ( $day < 1 || 31 < $day ) {
				break;
			}
			$check_date = sprintf( '%04d-%02d-%02d', (int) $check_year, (int) $check_month, (int) $day );
			$check      = dlseller_check_date( $check_date );
		} while ( ! $check );
	}
	$time = mktime( 0, 0, 0, (int) $check_month, (int) $day, (int) $check_year );
	return $time;
}

/**
 * Date validity check
 *
 * @param string $date Date.
 * @param string $sp   Separator.
 * @return bool
 */
function dlseller_check_date( $date, $sp = '-' ) {
	if ( empty( $date ) ) {
		return false;
	}
	try {
		new DateTime( $date );
		list( $year, $month, $day ) = explode( $sp, $date );
		$res                        = checkdate( (int) $month, (int) $day, (int) $year );
		return $res;
	} catch ( Exception $e ) {
		return false;
	}
}

/**
 * Update member data
 * usces_action_edit_memberdata
 *
 * @param array $post_member Member edit data.
 * @param int   $member_id   Member ID.
 */
function dlseller_action_edit_memberdata( $post_member, $member_id ) {
	global $wpdb;

	$dlseller_options   = get_option( 'dlseller' );
	$reinforcement      = ( isset( $dlseller_options['dlseller_member_reinforcement'] ) && 'on' === $dlseller_options['dlseller_member_reinforcement'] ) ? true : false;
	$continuation_table = $wpdb->prefix . 'usces_continuation';
	$order_table        = $wpdb->prefix . 'usces_order';
	$query              = $wpdb->prepare( "SELECT * FROM {$continuation_table} WHERE `con_member_id` = %d AND `con_status` = 'continuation'", $member_id );
	$continue_order     = $wpdb->get_results( $query, ARRAY_A );
	if ( 0 < count( $continue_order ) ) {
		foreach ( $continue_order as $order ) {
			if ( $reinforcement ) {
				$query = $wpdb->prepare(
					"UPDATE {$order_table} SET 
						`order_email`=%s, `order_name1`=%s, `order_name2`=%s, `order_name3`=%s, `order_name4`=%s, 
						`order_zip`=%s, `order_pref`=%s, `order_address1`=%s, `order_address2`=%s, `order_address3`=%s, 
						`order_tel`=%s, `order_fax`=%s 
					WHERE ID = %d",
						trim( $post_member['mailaddress1'] ),
						trim( $post_member['name1'] ),
						trim( $post_member['name2'] ),
						( isset( $post_member['name3'] ) ? trim( $post_member['name3'] ) : '' ),
						( isset( $post_member['name4'] ) ? trim( $post_member['name4'] ) : '' ),
						trim( $post_member['zipcode'] ),
						trim( $post_member['pref'] ),
						trim( $post_member['address1'] ),
						trim( $post_member['address2'] ),
						trim( $post_member['address3'] ),
						trim( $post_member['tel'] ),
						( isset( $post_member['fax'] ) ? trim( $post_member['fax'] ) : '' ),
						$order['con_order_id']
				);
			} else {
				$query = $wpdb->prepare(
					"UPDATE {$order_table} SET 
						`order_email`=%s, `order_name1`=%s, `order_name2`=%s, `order_name3`=%s, `order_name4`=%s 
					WHERE ID = %d",
						trim( $post_member['mailaddress1'] ),
						trim( $post_member['name1'] ),
						trim( $post_member['name2'] ),
						( isset( $post_member['name3'] ) ? trim( $post_member['name3'] ) : '' ),
						( isset( $post_member['name4'] ) ? trim( $post_member['name4'] ) : '' ),
						$order['con_order_id']
				);
			}
			$query = apply_filters( 'dlseller_filter_update_order_by_member', $query, $post_member, $order );
			$res   = $wpdb->query( $query );
		}
	}
}

/**
 * Quantity field
 *
 * @param string $row_quant
 * @param string $args {
 *     The array of cart data.
 *     @type array  $cart     Cart data.
 *     @type int    $i        Index.
 *     @type array  $cart_row Cart row data.
 *     @type int    $post_id  Post ID.
 *     @type string $sku      SKU code.
 * }
 * @return string
 */
function dlseller_filter_cart_rows_quant( $row_quant, $args ) {
	global $usces;

	extract( $args );
	$content = dlseller_get_division( $post_id );
	if ( 'data' === $content ) {
		$row_quant = '<input name="quant[' . $i . '][' . $post_id . '][' . esc_attr( $sku ) . ']" class="quantity" type="text" value="' . esc_attr( $cart_row['quantity'] ) . '" readonly />';
	}
	return $row_quant;
}

/**
 * Mail save email template data DL seller
 */
function dlseller_usces_action_admin_mail_page() {
	global $usces;

	if ( isset( $_POST['usces_option_update'] ) ) {
		check_admin_referer( 'admin_mail', 'wc_nonce' );
		$_POST = $usces->stripslashes_deep_post( $_POST );

		// save email dlseller reminder.
		$mail_dlseller_reminder = get_option( 'usces_mail_dlseller_reminder' );
		$key_dlseller_reminder  = 'dlseller_reminder';
		if ( isset( $_POST['email_dlseller_reminder']['title'] ) ) {
			if ( WCUtils::is_blank( $_POST['email_dlseller_reminder']['title'] ) ) {
				$mail_dlseller_reminder['title'] = isset( $usces->options['mail_default']['title'][ $key_dlseller_reminder ] ) ? $usces->options['mail_default']['title'][ $key_dlseller_reminder ] : '';
			} else {
				$mail_dlseller_reminder['title'] = trim( $_POST['email_dlseller_reminder']['title'] );
			}
			$usces->options['mail_data']['title'][ $key_dlseller_reminder ] = $mail_dlseller_reminder['title'];
		}

		if ( isset( $_POST['email_dlseller_reminder']['header'] ) ) {
			if ( WCUtils::is_blank( $_POST['email_dlseller_reminder']['header'] ) ) {
				$mail_dlseller_reminder['header'] = isset( $usces->options['mail_default']['header'][ $key_dlseller_reminder ] ) ? $usces->options['mail_default']['header'][ $key_dlseller_reminder ] : '';
			} else {
				$mail_dlseller_reminder['header'] = trim( $_POST['email_dlseller_reminder']['header'] );
			}
			$usces->options['mail_data']['header'][ $key_dlseller_reminder ] = $mail_dlseller_reminder['header'];
		}

		if ( isset( $_POST['email_dlseller_reminder']['footer'] ) ) {
			if ( WCUtils::is_blank( $_POST['email_dlseller_reminder']['footer'] ) ) {
				$mail_dlseller_reminder['footer'] = isset( $usces->options['mail_default']['footer'][ $key_dlseller_reminder ] ) ? $usces->options['mail_default']['footer'][ $key_dlseller_reminder ] : '';
			} else {
				$mail_dlseller_reminder['footer'] = trim( $_POST['email_dlseller_reminder']['footer'] );
			}
			$usces->options['mail_data']['footer'][ $key_dlseller_reminder ] = $mail_dlseller_reminder['footer'];
		}
		update_option( 'usces_mail_dlseller_reminder', $mail_dlseller_reminder );

		// save email dlseller contract renewal.
		$mail_dlseller_contract_renewal = get_option( 'usces_mail_dlseller_contract_renewal' );
		$key_dlseller_contract_renewal  = 'dlseller_contract_renewal';
		if ( isset( $_POST['email_dlseller_contract_renewal']['title'] ) ) {
			if ( WCUtils::is_blank( $_POST['email_dlseller_contract_renewal']['title'] ) ) {
				$mail_dlseller_contract_renewal['title'] = isset( $usces->options['mail_default']['title'][ $key_dlseller_contract_renewal ] ) ? $usces->options['mail_default']['title'][ $key_dlseller_contract_renewal ] : '';
			} else {
				$mail_dlseller_contract_renewal['title'] = trim( $_POST['email_dlseller_contract_renewal']['title'] );
			}
			$usces->options['mail_data']['title'][ $key_dlseller_contract_renewal ] = $mail_dlseller_contract_renewal['title'];
		}

		if ( isset( $_POST['email_dlseller_contract_renewal']['header'] ) ) {
			if ( WCUtils::is_blank( $_POST['email_dlseller_contract_renewal']['header'] ) ) {
				$mail_dlseller_contract_renewal['header'] = isset( $usces->options['mail_default']['header'][ $key_dlseller_contract_renewal ] ) ? $usces->options['mail_default']['header'][ $key_dlseller_contract_renewal ] : '';
			} else {
				$mail_dlseller_contract_renewal['header'] = trim( $_POST['email_dlseller_contract_renewal']['header'] );
			}
			$usces->options['mail_data']['header'][ $key_dlseller_contract_renewal ] = $mail_dlseller_contract_renewal['header'];
		}

		if ( isset( $_POST['email_dlseller_contract_renewal']['footer'] ) ) {
			if ( WCUtils::is_blank( $_POST['email_dlseller_contract_renewal']['footer'] ) ) {
				$mail_dlseller_contract_renewal['footer'] = isset( $usces->options['mail_default']['footer'][ $key_dlseller_contract_renewal ] ) ? $usces->options['mail_default']['footer'][ $key_dlseller_contract_renewal ] : '';
			} else {
				$mail_dlseller_contract_renewal['footer'] = trim( $_POST['email_dlseller_contract_renewal']['footer'] );
			}
			$usces->options['mail_data']['footer'][ $key_dlseller_contract_renewal ] = $mail_dlseller_contract_renewal['footer'];
		}
		update_option( 'usces_mail_dlseller_contract_renewal', $mail_dlseller_contract_renewal );
	}
}

/**
 * Mail get email template data DL seller
 *
 * @param array  $mail_data        Mail data.
 * @param string $mail_data_title  Mail title.
 * @param string $mail_data_header Mail header.
 * @param string $mail_data_footer Mail footer.
 * @return array
 */
function dlseller_usces_filter_mail_data( $mail_data, $mail_data_title, $mail_data_header, $mail_data_footer ) {
	// add more define email template config for dl seller reminder.
	if ( isset( $mail_data_title['dlseller_reminder'] ) && ! empty( $mail_data_title['dlseller_reminder'] ) ) {
		$mail_dlseller_reminder_title = $mail_data_title['dlseller_reminder'];
	} else {
		$mail_dlseller_reminder_title = __( 'Guidance of the settlement date', 'dlseller' );
	}
	if ( isset( $mail_data_header['dlseller_reminder'] ) && ! empty( $mail_data_header['dlseller_reminder'] ) ) {
		$mail_dlseller_reminder_header = $mail_data_header['dlseller_reminder'];
	} else {
		$mail_dlseller_reminder_header = __( 'This e-mail is a guide for the next settlement date of the auto continuation charging.', 'dlseller' );
	}
	if ( isset( $mail_data_footer['dlseller_reminder'] ) && ! empty( $mail_data_footer['dlseller_reminder'] ) ) {
		$mail_dlseller_reminder_footer = $mail_data_footer['dlseller_reminder'];
	} else {
		$mail_dlseller_reminder_footer = $mail_data['footer']['othermail'];
	}
	$mail_dlseller_reminder                   = get_option( 'usces_mail_dlseller_reminder', array( 'title' => $mail_dlseller_reminder_title, 'header' => $mail_dlseller_reminder_header, 'footer' => $mail_dlseller_reminder_footer ) );
	$mail_data['title']['dlseller_reminder']  = isset( $mail_dlseller_reminder['title'] ) ? $mail_dlseller_reminder['title'] : $mail_dlseller_reminder_title;
	$mail_data['header']['dlseller_reminder'] = isset( $mail_dlseller_reminder['header'] ) ? $mail_dlseller_reminder['header'] : $mail_dlseller_reminder_header;
	$mail_data['footer']['dlseller_reminder'] = isset( $mail_dlseller_reminder['footer'] ) ? $mail_dlseller_reminder['footer'] : $mail_dlseller_reminder_footer;
	
	// add more define email template config for dl seller contract renewal.
	if ( isset( $mail_data_title['dlseller_contract_renewal'] ) && ! empty( $mail_data_title['dlseller_contract_renewal'] ) ) {
		$mail_dlseller_contract_renewal_title = $mail_data_title['dlseller_contract_renewal'];
	} else {
		$mail_dlseller_contract_renewal_title = __( 'Guidance of the contract renewal date', 'dlseller' );
	}
	if ( isset( $mail_data_header['dlseller_contract_renewal'] ) && ! empty( $mail_data_header['dlseller_contract_renewal'] ) ) {
		$mail_dlseller_contract_renewal_header = $mail_data_header['dlseller_contract_renewal'];
	} else {
		$mail_dlseller_contract_renewal_header = __( 'This e-mail is a guide for the next contract renewal date of the auto continuation charging.', 'dlseller' );
	}
	if ( isset( $mail_data_footer['dlseller_contract_renewal'] ) && ! empty( $mail_data_footer['dlseller_contract_renewal'] ) ) {
		$mail_dlseller_contract_renewal_footer = $mail_data_footer['dlseller_contract_renewal'];
	} else {
		$mail_dlseller_contract_renewal_footer = $mail_data['footer']['othermail'];
	}
	$mail_dlseller_contract_renewal                   = get_option( 'usces_mail_dlseller_contract_renewal', array( 'title' => $mail_dlseller_contract_renewal_title, 'header' => $mail_dlseller_contract_renewal_header, 'footer' => $mail_dlseller_contract_renewal_footer ) );
	$mail_data['title']['dlseller_contract_renewal']  = isset( $mail_dlseller_contract_renewal['title'] ) ? $mail_dlseller_contract_renewal['title'] : $mail_dlseller_contract_renewal_title;
	$mail_data['header']['dlseller_contract_renewal'] = isset( $mail_dlseller_contract_renewal['header'] ) ? $mail_dlseller_contract_renewal['header'] : $mail_dlseller_contract_renewal_header;
	$mail_data['footer']['dlseller_contract_renewal'] = isset( $mail_dlseller_contract_renewal['footer'] ) ? $mail_dlseller_contract_renewal['footer'] : $mail_dlseller_contract_renewal_footer;
	
	return $mail_data;
}

/**
 * Filter content wp_editor preview.
 */
function dlseller_filter_content_wp_editor_preview() {
	global $usces;

	$_POST = $usces->stripslashes_deep_post( $_POST );
	$nonce = isset( $_POST['wc_nonce'] ) ? wp_unslash( $_POST['wc_nonce'] ) : '';
	if ( ! wp_verify_nonce( $nonce, 'dl_preview_editor_nonce' ) ) {
		$error_msg = array( 'message' => 'Your request is not valid.' );
		wp_send_json_error( $error_msg, 403 );
	}
	$mode = ( isset( $_POST['mode'] ) ) ? wp_unslash( $_POST['mode'] ) : '';
	$res  = array(
		'status' => false,
	);
	switch ( $mode ) {
		case 'preview_email_order_detail':
			$content        = isset( $_POST['content'] ) ? $_POST['content'] : '';
			$res['status']  = true;
			$res['content'] = wpautop( $content );
			break;
	}
	wp_send_json( $res );
}

/**
 * New member form title
 *
 * @param string $title Title.
 * @return string
 */
function usces_dlseller_title_newmemberform( $title ) {
	return __( 'Verifying', 'usces' );
}

/**
 * Template path
 * usces_template_path_member_form
 *
 * @param string $default_path Default path.
 * @return string
 */
function usces_dlseller_template_path_member_form( $default_path ) {
	return USCES_PLUGIN_DIR . '/templates/member/verifying.php';
}

/**
 * Template redirect
 * usces_filter_template_redirect
 *
 * @param string $default
 * @return string
 */
function dlseller_filter_template_redirect_not_remove( $default ) {
	if ( ! empty( $_POST['regmember'] )
		&& ! empty( $_POST['member_regmode'] )
		&& 'newmemberform' === $_POST['member_regmode'] )
	{
		global $usces;
		add_action( 'the_post', array( $usces, 'action_memberFilter' ), 20 );
	}
	return $default;
}

/**
 * After new member registration
 * usces_action_after_newmemberform_verified
 *
 * @param array $user User.
 * @param array $data Data.
 */
function dlseller_action_after_newmemberform_verified( $user, $data ) {
	global $usces;
	add_action( 'the_post', array( $usces, 'action_memberFilter' ), 20 );
}

/**
 * After new member registration from cart
 * usces_action_after_newmemberfromcart_verified
 *
 * @param array $user User.
 * @param array $data Data.
 */
function dlseller_action_after_newmemberfromcart_verified( $user, $data ) {
	global $usces;
	add_action( 'the_post', array( $usces, 'action_cartFilter' ), 20 );
}

/**
 * Verifymail query
 * usces_filter_verifymail_query
 *
 * @param array $query Query.
 * @param array $user  User.
 * @return array
 */
function dlseller_filter_verifymail_query( $query, $user ) {
	global $usces;

	$ext_verify_member = new USCES_VERIFY_MEMBERS_EMAIL();
	$user['regmode']   = 'newmemberfromcart';
	$encrypt_value     = $ext_verify_member->encrypt_value( $user );
	$query             = array(
		'verify'     => $encrypt_value,
		'usces_page' => 'memberverified',
		'uscesid'    => $usces->get_uscesid( false ),
	);
	return $query;
}

/**
 * The details of each payment are displayed on the content confirmation page.
 * usces_filter_confirm_table_after
 *
 * @param string $html HTML form.
 * @return string
 */
function dlseller_filter_confirm_table_after( $html ) {
	$html .= dlseller_amount_each_time();
	return stripslashes( nl2br( $html ) );
}

/**
 * The details of each payment are displayed on the content confirmation page.
 * usces_action_confirm_table_after
 */
function dlseller_action_confirm_table_after() {
	$html = dlseller_amount_each_time();
	echo $html;
}

/**
 * Payment details for each time.
 *
 * @return string
 */
function dlseller_amount_each_time() {
	global $usces;

	$table = '';
	$cart  = $usces->cart->get_cart();
	if ( ! dlseller_have_continue_charge( $cart ) ) {
		return $table;
	}

	$entry   = $usces->cart->get_entry();
	$price   = $entry['order']['total_full_price'];
	$payment = $usces->getPayments( $entry['order']['payment_name'] );
	if ( dlseller_have_shipped() && isset( $payment['settlement'] ) && 'COD' === $payment['settlement'] ) {
		$payment_description = $entry['order']['payment_name'] . __( '(Payment when the item arrives.)', 'dlseller' );
	} else {
		$payment_description = $entry['order']['payment_name'] . __( '(Payment will be automatically settled on the billing date.)', 'dlseller' );
	}
	$payment_description = apply_filters( 'dlseller_filter_amount_each_time_payment_description', $payment_description, $payment, $cart, $entry );

	$cart_row   = $cart[0];
	$post_id    = $cart_row['post_id'];
	$sku        = $cart_row['sku'];
	$item_name  = $usces->getCartItemName( $post_id, $sku );
	$usces_item = $usces->get_item( $post_id );
	$frequency  = ( ! empty( $usces_item['item_frequency'] ) ) ? (int) $usces_item['item_frequency'] : 0;
	$cycle      = '';
	switch ( $frequency ) {
		case 1:
			$cycle = __( 'One payment per month', 'dlseller' );
			break;
		case 6:
			$cycle = __( 'One payment every 6 months', 'dlseller' );
			break;
		case 12:
			$cycle = __( 'One payment per year', 'dlseller' );
			break;
	}
	$cycle = apply_filters( 'dlseller_filter_amount_each_time_cycle', $cycle, $cart_row );

	$cycle_description = __( '(The contract will continue until the customer requests to stop billing.)', 'dlseller' );
	$cycle_description = apply_filters( 'dlseller_filter_amount_each_time_cycle_description', $cycle_description, $cart_row );

	$continue_description = __( '* Billing will continue after the 6th payment.', 'dlseller' );
	$continue_description = apply_filters( 'dlseller_filter_amount_each_time_continue_description', $continue_description, $cart_row );

	$charged_date = dlseller_first_charging( $post_id );
	$cod_fee      = ( ! empty( $entry['order']['cod_fee'] ) ) ? $entry['order']['cod_fee'] : 0;

	$tax_label = '';
	if ( usces_is_tax_display() ) {
		if ( dlseller_have_shipped() ) {
			$tax_label .= __( 'Shipping', 'usces' );
			if ( 0 < $cod_fee ) {
				$tax_label .= __( '/', 'dlseller' ) . __( 'Fee', 'usces' );
			}
		} else {
			if ( 0 < $cod_fee ) {
				$tax_label .= __( 'Fee', 'usces' );
			}
		}
		if ( ! empty( $tax_label ) ) {
			$tax_label .= __( ' included', 'dlseller' ) . __( ',', 'dlseller' );
			$tax_label  = __( '(', 'usces' ) . $tax_label;
		} else {
			$tax_label .= __( '(', 'usces' );
		}
		$tax_label .= __( 'Tax', 'dlseller' ) . __( ' included', 'dlseller' ) . __( ')', 'usces' );
	}

	$table .= '<table id="amount_each_time">';
	$table .= '<tr><td rowspan="7" class="item-description">' . $item_name . '<br>' . $cycle . $cycle_description . '<br>';
	$table .= __( 'Method of Payment', 'dlseller' ) . __( ' : ', 'usces' ) . $payment_description . '</td><th colspan="2">' . __( 'Date charged', 'dlseller' ) . '</th><th>' . __( 'Quantity', 'usces' ) . '</th><th>' . __( 'Amount', 'usces' ) . $tax_label . '</th></tr>';
	$table .= '<tr><td class="times" data-label="' . __( 'Date charged', 'dlseller' ) . '">' . __( 'first time', 'dlseller' ) . '</td><td class="scheduled-date">' . $charged_date . '</td><td class="quantity" data-label="' . __( 'Quantity', 'usces' ) . '">' . $cart_row['quantity'] . '</td><td class="price" data-label="' . __( 'Amount', 'usces' ) . $tax_label . '">' . usces_crform( $price, true, false, 'return' ) . '</td></tr>';
	for ( $i = 2; $i <= 5; $i++ ) {
		$charged_date = dlseller_get_charged_date( $charged_date, $post_id );
		$table       .= '<tr><td class="times" data-label="' . __( 'Date charged', 'dlseller' ) . '">' . $i . __( 'th', 'dlseller' ) . '</td><td class="scheduled-date">' . $charged_date . '</td><td class="quantity" data-label="' . __( 'Quantity', 'usces' ) . '">' . $cart_row['quantity'] . '</td><td class="price" data-label="' . __( 'Amount', 'usces' ) . $tax_label . '">' . usces_crform( $price, true, false, 'return' ) . '</td></tr>';
	}
	$table .= '<tr><td class="continue-description" colspan="4">' . $continue_description . '</td></tr>';
	$table .= '</table>';
	$table  = apply_filters( 'dlseller_filter_amount_each_time_table', $table, $cart, $entry );

	return $table;
}

/**
 * Next date charged.
 *
 * @param string $charged_date Charged date.
 * @param int    $post_id      Post ID.
 * @return string
 */
function dlseller_get_charged_date( $charged_date, $post_id ) {
	global $usces;

	list( $year, $month, $day ) = explode( '-', $charged_date );
	$usces_item                 = $usces->get_item( $post_id );
	$time                       = mktime( 0, 0, 0, (int) $month + $usces_item['item_frequency'], (int) $day, (int) $year );
	$time                       = dlseller_get_valid_lastday( $time, $charged_date, $usces_item['item_frequency'] );
	$date                       = date_i18n( 'Y-m-d', $time );
	return $date;
}
