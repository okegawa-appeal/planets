<?php
/**
 * DL Seller functions.
 *
 * @package WCEX DL Seller
 */

/**
 * Upgrade
 *
 * @return bool
 */
function dlseller_upgrade_300() {
	global $wpdb, $usces;

	$upgrade = (int) get_option( 'dlseller_upgrade' );
	if ( 0 !== $upgrade ) {
		return true;
	}

	if ( ! get_option( 'usces_db_continuation' ) || ! get_option( 'usces_db_continuation_meta' ) ) {
		dlseller_create_table();
	}

	dlseller_continuation_data_migration();

	$upgrade += WCEX_DLSELLER_UP300;
	update_option( 'dlseller_upgrade', $upgrade );

	return true;
}

/**
 * Create table
 */
function dlseller_create_table() {
	global $wpdb;

	$charset_collate = '';
	if ( ! empty( $wpdb->charset ) ) {
		$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
	}
	if ( ! empty( $wpdb->collate ) ) {
		$charset_collate .= " COLLATE $wpdb->collate";
	}

	$continuation_table      = $wpdb->prefix . 'usces_continuation';
	$continuation_meta_table = $wpdb->prefix . 'usces_continuation_meta';

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	if ( $wpdb->get_var( "SHOW TABLES LIKE '$continuation_table'" ) != $continuation_table ) {

		$sql = "CREATE TABLE `$continuation_table` (
			`con_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			`con_order_id` bigint(20) unsigned NOT NULL,
			`con_member_id` bigint(20) unsigned NOT NULL,
			`con_division` varchar(255) NOT NULL,
			`con_post_id` bigint(20) NOT NULL,
			`con_sku` varchar(255) NOT NULL,
			`con_acting` varchar(255) NOT NULL,
			`con_order_price` decimal(10,2) NOT NULL,
			`con_price` decimal(10,2) NOT NULL,
			`con_frequency` int(10) NOT NULL,
			`con_chargingday` int(10) NOT NULL,
			`con_interval` int(10) DEFAULT '0',
			`con_next_charging` char(10) DEFAULT NULL COMMENT 'chargedday',
			`con_next_contracting` char(10) DEFAULT NULL COMMENT 'contractedday',
			`con_startdate` char(10) DEFAULT NULL,
			`con_condition` varchar(255) DEFAULT NULL,
			`con_modified` char(20) DEFAULT NULL,
			`con_status` varchar(255) DEFAULT NULL, 
			PRIMARY KEY (`con_id`),
			UNIQUE KEY `con_order_id` (`con_order_id`),
			KEY `con_member_id` (`con_member_id`) USING BTREE,
			KEY `con_member_order` (`con_member_id`,`con_order_id`)
		) ENGINE=MYISAM AUTO_INCREMENT=0 $charset_collate;";

		dbDelta( $sql );
		add_option( 'usces_db_continuation', USCES_DB_CONTINUATION );
	}

	if ( $wpdb->get_var( "SHOW TABLES LIKE '$continuation_meta_table'" ) != $continuation_meta_table ) {

		$sql = "CREATE TABLE `$continuation_meta_table` (
			`conmeta_id` bigint(20) NOT NULL AUTO_INCREMENT,
			`con_id` bigint(20) NOT NULL,
			`meta_key` varchar(255) DEFAULT NULL,
			`meta_value` longtext,
			PRIMARY KEY (`conmeta_id`),
			KEY `con_id` (`con_id`),
			KEY `meta_key` (`meta_key`(191))
		) ENGINE=MyISAM $charset_collate;";

		dbDelta( $sql );
		add_option( 'usces_db_continuation_meta', USCES_DB_CONTINUATION_META );
	}
}

/**
 * Continuation data migration
 */
function dlseller_continuation_data_migration() {
	global $usces, $wpdb;

	$continuation_table = $wpdb->prefix . 'usces_continuation';
	$member_meta_table  = usces_get_tablename( 'usces_member_meta' );
	$query              = $wpdb->prepare( "SELECT * FROM {$member_meta_table} WHERE meta_key LIKE %s ORDER BY mmeta_id", 'continuepay_%' );
	$res                = $wpdb->get_results( $query, ARRAY_A );
	foreach ( $res as $data ) {
		list( $pre, $order_id ) = explode( '_', $data['meta_key'] );
		$continue_data          = unserialize( $data['meta_value'] );
		$order                  = $usces->get_order_data( $order_id );
		$cart                   = usces_get_ordercartdata( $order_id );
		if ( ! empty( $cart[0]['post_id'] ) ) {
			$division = $usces->getItemDivision( $cart[0]['post_id'] );
			$post_id  = $cart[0]['post_id'];
			$sku      = $cart[0]['sku_code'];
		} else {
			$division = '';
			$post_id  = 0;
			$sku      = ' ';
		}
		$payments  = $usces->getPayments( $order['payment_name'] );
		$frequency = ( ! empty( $continue_data['frequency'] ) ) ? (int) $continue_data['frequency'] : 0;
		if ( 99 === (int) $continue_data['chargingday'] ) {
			$chargingday = intval( substr( $continue_data['startdate'], 8, 2 ) );
		} else {
			$chargingday = (int) $continue_data['chargingday'];
		}
		$interval = ( ! empty( $continue_data['interval'] ) ) ? (int) $continue_data['interval'] : null;

		$query = $wpdb->prepare( "INSERT INTO {$continuation_table} ( `con_order_id`, `con_member_id`, `con_division`, `con_post_id`, `con_sku`, `con_acting`, `con_order_price`, `con_price`, `con_frequency`, `con_chargingday`, `con_interval`, `con_next_charging`, `con_next_contracting`, `con_startdate`, `con_condition`, `con_status` ) VALUES
			( %d, %d, %s, %d, %s, %s, %f, %f, %d, %d, %d, %s, %s, %s, %s, %s )",
			$order_id,
			$data['member_id'],
			$division,
			$post_id,
			$sku,
			$payments['settlement'],
			$order['end_price'],
			$order['end_price'],
			$frequency,
			$chargingday,
			$interval,
			$continue_data['chargedday'],
			$continue_data['contractedday'],
			$continue_data['startdate'],
			$continue_data['condition'],
			$continue_data['status']
		);
		$res   = $wpdb->query( $query );
	}
}

/**
 * Continuation member list download
 */
function dlseller_download_continue_member_list() {
	require_once USCES_WP_PLUGIN_DIR . '/' . plugin_basename( dirname( __FILE__ ) ) . '/continueMemberList.class.php';
	global $usces;

	$ext = wp_unslash( $_REQUEST['ftype'] );
	if ( 'csv' === $ext ) { /* CSV */
		$table_h = '';
		$table_f = '';
		$tr_h    = '';
		$tr_f    = '';
		$th_h1   = '"';
		$th_h    = ',"';
		$th_f    = '"';
		$td_h1   = '"';
		$td_h    = ',"';
		$td_f    = '"';
		$lf      = "\n";
	} else {
		exit();
	}

	$applyform         = usces_get_apply_addressform( $usces->options['system']['addressform'] );
	$payment_structure = get_option( 'usces_payment_structure' );

	$dlseller_opt_continue = get_option( 'dlseller_opt_continue' );
	if ( ! is_array( $dlseller_opt_continue ) ) {
		$dlseller_opt_continue = array();
	}
	$dlseller_opt_continue['ftype_con'] = $ext;
	$chk_con                            = array();
	$chk_con['order_id']                = ( isset( $_REQUEST['check']['order_id'] ) ) ? 1 : 0;
	$chk_con['deco_id']                 = ( isset( $_REQUEST['check']['deco_id'] ) ) ? 1 : 0;
	$chk_con['mem_id']                  = ( isset( $_REQUEST['check']['mem_id'] ) ) ? 1 : 0;
	$chk_con['email']                   = ( isset( $_REQUEST['check']['email'] ) ) ? 1 : 0;
	$chk_con['name']                    = ( isset( $_REQUEST['check']['name'] ) ) ? 1 : 0;
	if ( 'JP' === $applyform ) {
		$chk_con['kana'] = ( isset( $_REQUEST['check']['kana'] ) ) ? 1 : 0;
	} else {
		$chk_con['kana'] = 0;
	}
	$chk_con['limitofcard']           = ( isset( $_REQUEST['check']['limitofcard'] ) ) ? 1 : 0;
	$chk_con['price']                 = ( isset( $_REQUEST['check']['price'] ) ) ? 1 : 0;
	$chk_con['acting']                = ( isset( $_REQUEST['check']['acting'] ) ) ? 1 : 0;
	$chk_con['payment_name']          = ( isset( $_REQUEST['check']['payment_name'] ) ) ? 1 : 0;
	$chk_con['orderdate']             = ( isset( $_REQUEST['check']['orderdate'] ) ) ? 1 : 0;
	$chk_con['startdate']             = ( isset( $_REQUEST['check']['startdate'] ) ) ? 1 : 0;
	$chk_con['contractedday']         = ( isset( $_REQUEST['check']['contractedday'] ) ) ? 1 : 0;
	$chk_con['chargedday']            = ( isset( $_REQUEST['check']['chargedday'] ) ) ? 1 : 0;
	$chk_con['status']                = ( isset( $_REQUEST['check']['status'] ) ) ? 1 : 0;
	$chk_con['condition']             = ( isset( $_REQUEST['check']['condition'] ) ) ? 1 : 0;
	$dlseller_opt_continue['chk_con'] = apply_filters( 'dlseller_filter_chk_con', $chk_con );
	update_option( 'dlseller_opt_continue', $dlseller_opt_continue );

	$_REQUEST['searchIn'] = 'searchIn';
	$con_list             = new ContinuationList();
	$con_list->pageLimit  = 'off';
	$res                  = $con_list->MakeTable();
	$arr_search           = $con_list->GetSearchs();
	$rows                 = $con_list->rows;

	$line   = $table_h;
	$line_h = $tr_h;
	if ( $chk_con['order_id'] ) {
		$line_h .= $th_h1 . __( 'Order ID', 'dlseller' ) . $th_f;
	}
	if ( $chk_con['deco_id'] ) {
		$line_h .= $th_h . __( 'order number', 'usces' ) . $th_f;
	}
	if ( $chk_con['mem_id'] ) {
		$line_h .= $th_h . __( 'membership number', 'usces' ) . $th_f;
	}
	if ( $chk_con['email'] ) {
		$line_h .= $th_h . __( 'e-mail', 'usces' ) . $th_f;
	}
	if ( $chk_con['name'] ) {
		$line_h .= $th_h . __( 'name', 'usces' ) . $th_f;
	}
	if ( 'JP' === $applyform ) {
		if ( $chk_con['kana'] ) {
			$line_h .= $th_h . __( 'furigana', 'usces' ) . $th_f;
		}
	}
	if ( $chk_con['limitofcard'] ) {
		$line_h .= $th_h . __( 'Limit of Card(Month/Year)', 'dlseller' ) . $th_f;
	}
	if ( $chk_con['price'] ) {
		$line_h .= $th_h . __( 'Total Amount', 'usces' ) . '(' . __( usces_crcode( 'return' ), 'usces' ) . ')' . $th_f;
	}
	if ( $chk_con['acting'] ) {
		$line_h .= $th_h . __( 'Settlement Supplier', 'dlseller' ) . $th_f;
	}
	if ( $chk_con['payment_name'] ) {
		$line_h .= $th_h . __( 'payment method', 'usces' ) . $th_f;
	}
	if ( $chk_con['orderdate'] ) {
		$line_h .= $th_h . __( 'Application Date', 'dlseller' ) . $th_f;
	}
	if ( $chk_con['startdate'] ) {
		$line_h .= $th_h . __( 'First Withdrawal Date', 'dlseller' ) . $th_f;
	}
	if ( $chk_con['contractedday'] ) {
		$line_h .= $th_h . __( 'Renewal Date', 'dlseller' ) . $th_f;
	}
	if ( $chk_con['chargedday'] ) {
		$line_h .= $th_h . __( 'Next Withdrawal Date', 'dlseller' ) . $th_f;
	}
	if ( $chk_con['status'] ) {
		$line_h .= $th_h . __( 'Status', 'dlseller' ) . $th_f;
	}
	if ( $chk_con['condition'] ) {
		$line_h .= $th_h . __( 'Condition', 'dlseller' ) . $th_f;
	}
	$line_h .= apply_filters( 'dlseller_filter_chk_con_label', null, $dlseller_opt_continue, $rows );
	$line_h .= $tr_f . $lf;
	$line   .= ltrim( $line_h, ',' );

	foreach ( (array) $rows as $data ) {
		$line_d = $tr_h;
		if ( $chk_con['order_id'] ) {
			$line_d .= $td_h1 . $data['order_id'] . $td_f;
		}
		if ( $chk_con['deco_id'] ) {
			$line_d .= $td_h . $data['deco_id'] . $td_f;
		}
		if ( $chk_con['mem_id'] ) {
			$line_d .= $td_h . $data['mem_id'] . $td_f;
		}
		if ( $chk_con['email'] ) {
			$line_d .= $td_h . usces_entity_decode( $data['email'], $ext ) . $td_f;
		}
		if ( $chk_con['name'] ) {
			$line_d .= $td_h . usces_entity_decode( $data['name1'] . ' ' . $data['name2'], $ext ) . $td_f;
		}
		if ( 'JP' === $applyform ) {
			if ( $chk_con['kana'] ) {
				$line_d .= $td_h . usces_entity_decode( $data['name3'] . ' ' . $data['name4'], $ext ) . $td_f;
			}
		}
		if ( $chk_con['limitofcard'] ) {
			$line_d .= $td_h . $data['limitofcard'] . $td_f;
		}
		if ( $chk_con['price'] ) {
			$line_d .= $td_h . $data['price'] . $td_f;
		}
		if ( $chk_con['acting'] ) {
			$acting_name = ( array_key_exists( $data['acting'], $payment_structure ) ) ? $payment_structure[ $data['acting'] ] : $data['acting'];
			$line_d     .= $td_h . $acting_name . $td_f;
		}
		if ( $chk_con['payment_name'] ) {
			$line_d .= $td_h . $data['payment_name'] . $td_f;
		}
		if ( $chk_con['orderdate'] ) {
			$line_d .= $td_h . $data['orderdate'] . $td_f;
		}
		if ( $chk_con['startdate'] ) {
			$line_d .= $td_h . $data['startdate'] . $td_f;
		}
		if ( $chk_con['contractedday'] ) {
			$line_d .= $td_h . $data['contractedday'] . $td_f;
		}
		if ( $chk_con['chargedday'] ) {
			$line_d .= $td_h . $data['chargedday'] . $td_f;
		}
		if ( $chk_con['status'] ) {
			$continue_status = ( 'continuation' === $data['status'] ) ? __( 'continuation', 'dlseller' ) : __( 'cancellation', 'dlseller' );
			$line_d         .= $td_h . $continue_status . $td_f;
		}
		if ( $chk_con['condition'] ) {
			$line_d .= $td_h . $data['condition'] . $td_f;
		}
		$line_d .= apply_filters( 'dlseller_filter_chk_con_line', null, $dlseller_opt_continue, $data );
		$line_d .= $tr_f . $lf;
		$line   .= ltrim( $line_d, ',' );
	}
	$line .= $table_f . $lf;
	$line  = apply_filters( 'dlseller_filter_chk_con_data', $line );

	if ( 'xls' === $ext ) {
		header( 'Content-Type: application/vnd.ms-excel; charset=Shift-JIS' );
	} elseif ( 'csv' === $ext ) {
		header( 'Content-Type: application/octet-stream' );
	}
	header( 'Content-Disposition: attachment; filename=usces_continue_member_list.' . $ext );
	mb_http_output( 'pass' );
	print( mb_convert_encoding( $line, apply_filters( 'usces_filter_output_csv_encode', 'SJIS-win' ), 'UTF-8' ) );
	exit();
}
