<?php
/**
 * DL Seller template functions.
 *
 * @package WCEX DL Seller
 */

if ( ! function_exists( 'usces_point_coverage' ) ) {
	/**
	 * Point coverage.
	 *
	 * @return string
	 */
	function usces_point_coverage() {
		global $usces;
		return $usces->options['point_coverage'];
	}
}

if ( ! function_exists( 'usces_get_tax_target' ) ) {
	/**
	 * Target to tax.
	 *
	 * @return string
	 */
	function usces_get_tax_target() {
		global $usces;
		return $usces->options['tax_target'];
	}
}

if ( ! function_exists( 'usces_tax_label' ) ) {
	/**
	 * Tax label.
	 *
	 * @param array  $data Order data.
	 * @param string $out  Return value or echo.
	 * @return string|void
	 */
	function usces_tax_label( $data = array(), $out = '' ) {
		global $usces;

		if ( 'deactivate' === $usces->options['tax_display'] ) {
			$label = '';
		} else {
			if ( 'exclude' === $usces->options['tax_mode'] ) {
				$label = __( 'consumption tax', 'usces' );
			} else {
				$label = __( 'Internal tax', 'usces' );
			}
			$label = apply_filters( 'usces_filter_tax_label', $label );
		}

		if ( 'return' === $out ) {
			return $label;
		} else {
			echo esc_html( $label );
		}
	}
}

if ( ! function_exists( 'usces_is_tax_display' ) ) {
	/**
	 * Is tax display.
	 *
	 * @return bool
	 */
	function usces_is_tax_display() {
		global $usces, $usces_entries;
		if ( defined( 'USCES_VERSION' ) && version_compare( USCES_VERSION, '1.5', '>=' ) ) {
			if ( isset( $usces->options['tax_display'] ) && 'deactivate' === $usces->options['tax_display'] ) {
				return false;
			} else {
				return true;
			}
		} else {
			if ( empty( $usces_entries['order']['tax'] ) ) {
				return false;
			} else {
				return true;
			}
		}
	}
}

if ( ! function_exists( 'usces_admin_action_status' ) ) {
	/**
	 * Show Admin Status.
	 *
	 * @param string $action_status  Action status.
	 * @param string $action_message Action message.
	 */
	function usces_admin_action_status( $action_status = '', $action_message = '' ) {
		global $usces;
		if ( empty( $action_status ) ) {
			$action_status        = $usces->action_status;
			$usces->action_status = 'none';
		}
		if ( empty( $action_message ) ) {
			$action_message        = $usces->action_message;
			$usces->action_message = '';
		}
		$class = '';
		if ( 'success' === $action_status ) {
			$class = 'updated';
		} elseif ( 'caution' === $action_status ) {
			$class = 'update-nag';
		} elseif ( 'error' === $action_status ) {
			$class = 'error';
		}
		if ( '' !== $class ) :
			?>
	<div id="usces_admin_status">
		<div id="usces_action_status" class="<?php echo esc_attr( $class ); ?> notice is-dismissible">
			<p><strong><?php echo esc_html( $action_message ); ?></strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.' ); ?></span></button>
		</div>
	</div>
			<?php
		else :
			?>
	<div id="usces_admin_status"></div>
			<?php
		endif;
	}
}

if ( ! function_exists( 'usces_change_line_break' ) ) {
	/**
	 * Linefeed code conversion.
	 *
	 * @param string $value Text.
	 * @return string
	 */
	function usces_change_line_break( $value ) {
		$cr    = array( "\r\n", "\r" );
		$value = trim( $value );
		$value = str_replace( $cr, "\n", $value );
		return $value;
	}
}

/**
 * Orderlist Flag.
 *
 * @return int
 */
function dlseller_is_orderlist_flag() {
	$orderlist_flag = 0;
	if ( defined( 'USCES_VERSION' ) && version_compare( USCES_VERSION, '1.8.0', '>=' ) ) {
		$ex_options     = get_option( 'usces_ex' );
		$orderlist_flag = ( isset( $ex_options['system']['datalistup']['orderlist_flag'] ) ) ? (int) $ex_options['system']['datalistup']['orderlist_flag'] : 1;
	}
	return $orderlist_flag;
}

/**
 * Continuation member list.
 * load-welcart-management_page_usces_continue
 *
 * @param string $hook The current admin page.
 */
function dlseller_continue_member_list_hook( $hook ) {

	if ( ! isset( $_POST['continue_memberlist_options_apply'] ) ) {
		return;
	}

	$list_option = get_option( 'usces_continuelist_option' );
	foreach ( $list_option['view_column'] as $key => $value ) {
		$list_option['view_column'][ $key ] = ( isset( $_POST['hide'][ $key ] ) ) ? 1 : 0;
	}
	$list_option['max_row'] = absint( wp_unslash( $_POST['continue_memberlist_per_page'] ) );

	update_option( 'usces_continuelist_option', $list_option );
}

/**
 * Filters the screen settings text displayed in the Screen Options tab.
 * screen_settings
 *
 * @param string $screen_settings Screen settings.
 * @param object $screen          The Wp_Screen.
 * @return string
 */
function dlseller_screen_settings( $screen_settings, $screen ) {

	if ( 'welcart-management_page_usces_continue' !== $screen->id || isset( $_REQUEST['continue_action'] ) ) {
		return $screen_settings;
	}

	require_once USCES_WP_PLUGIN_DIR . '/' . plugin_basename( dirname( __FILE__ ) ) . '/continueMemberList.class.php';

	$ctn_list    = new ContinuationList();
	$arr_column  = $ctn_list->get_column();
	$list_option = get_option( 'usces_continuelist_option' );
	$init_view   = apply_filters( 'dlseller_filter_continue_memberlist_column_init_view', array( 'deco_id', 'mem_id', 'name1', 'name2', 'limitofcard', 'price', 'acting', 'order_date', 'contractedday', 'chargedday', 'con_status', 'con_condition' ) );

	$screen_settings = '
	<fieldset class="metabox-prefs">
		<legend>' . __( 'Columns' ) . '</legend>';
	foreach ( $arr_column as $key => $value ) {
		if ( 'ID' === $key ) {
			continue;
		}

		if ( ! isset( $list_option['view_column'][ $key ] ) && in_array( $key, $init_view ) ) {
			$list_option['view_column'][ $key ] = 1;
		} elseif ( ! isset( $list_option['view_column'][ $key ] ) ) {
			$list_option['view_column'][ $key ] = 0;
		}
		$checked          = ( isset( $list_option['view_column'][ $key ] ) && $list_option['view_column'][ $key ] ) ? ' checked="checked"' : '';
		$screen_settings .= '<label><input class="hide-column-tog" name="hide[' . $key . ']" type="checkbox" id="' . $key . '-hide" value="' . $key . '"' . $checked . ' />' . esc_html( $value ) . '</label>';
	}
	$screen_settings .= '</fieldset>';

	if ( ! isset( $list_option['max_row'] ) ) {
		$list_option['max_row'] = 50;
	}

	$screen_settings .= '<fieldset class="screen-options">
		<legend>' . __( 'Pagination' ) . '</legend>
		<label for="edit_post_per_page">' . __( 'Number of items per page:' ) . '</label>
		<input type="order" step="1" min="1" max="999" class="screen-per-page" name="continue_memberlist_per_page" id="continue_memberlist_per_page" maxlength="3" value="' . (int) $list_option['max_row'] . '" />
	</fieldset>
	<p class="submit"><input type="submit" name="continue_memberlist_options_apply" id="screen-options-apply" class="button button-primary" value="' . __( 'Apply' ) . '"  /></p>';

	update_option( 'usces_continuelist_option', $list_option );

	return $screen_settings;
}

/**
 * Get continuation data.
 *
 * @param int $order_id  Order ID.
 * @param int $member_id Member ID.
 * @return array
 */
function dlseller_get_continuation_data( $order_id, $member_id ) {
	global $wpdb;

	$continuation_table_name = $wpdb->prefix . 'usces_continuation';

	$query = $wpdb->prepare(
		"SELECT 
		`con_acting` AS `acting`, 
		`con_order_price` AS `order_price`, 
		`con_price` AS `price`, 
		`con_next_charging` AS `chargedday`, 
		`con_next_contracting` AS `contractedday`, 
		`con_startdate` AS `startdate`, 
		`con_status` AS `status` 
		FROM {$continuation_table_name} 
		WHERE con_order_id = %d AND con_member_id = %d",
		$order_id, $member_id
	);
	$data = $wpdb->get_row( $query, ARRAY_A );
	return $data;
}

if ( ! function_exists( 'usces_is_reduced_taxrate' ) ) {
	/**
	 * Is reduced taxrate.
	 *
	 * @param string|int $order_id Order ID.
	 * @return string
	 */
	function usces_is_reduced_taxrate( $order_id = '' ) {
		global $usces;

		if ( empty( $order_id ) ) {
			$reduced = ( isset( $usces->options['applicable_taxrate'] ) && 'reduced' === $usces->options['applicable_taxrate'] ) ? true : false;
		} else {
			$condition = usces_get_order_condition( $order_id );
			$reduced   = ( isset( $condition['applicable_taxrate'] ) && 'reduced' === $condition['applicable_taxrate'] ) ? true : false;
		}
		return $reduced;
	}
}

if ( ! function_exists( 'usces_get_tablename' ) ) {
	/**
	 * Get table name.
	 *
	 * @param string $target Table name.
	 * @return string
	 */
	function usces_get_tablename( $target ) {
		global $wpdb;

		$prefix = '';
		if ( defined( 'WELCART_DB_PREFIX' ) ) {
			$prefix = WELCART_DB_PREFIX;
		}
		if ( ! $prefix ) {
			$prefix = $wpdb->prefix;
		}
		$tablename = $prefix . $target;

		return $tablename;
	}
}

/**
 * Get 'order_id' from 'pre_order_id'.
 *
 * @param string $pre_order_id 'pre_order_id'.
 * @return int
 */
function dlseller_get_order_id_by_pre( $pre_order_id ) {
	global $wpdb;

	if ( empty( $pre_order_id ) ) {
		return false;
	}
	$query    = $wpdb->prepare( "SELECT order_id FROM {$wpdb->prefix}usces_order_meta WHERE meta_key = %s AND meta_value = %s", 'pre_order_id', $pre_order_id );
	$order_id = $wpdb->get_var( $query );
	return $order_id;
}

if ( ! function_exists( 'usces_convert_zipcode' ) ) {
	/**
	 * Zip code conversion.
	 *
	 * @param string $zipcode Zip code.
	 * @return string
	 */
	function usces_convert_zipcode( $zipcode ) {
		$zipcode = mb_convert_kana( $zipcode, 'a' );
		$zipcode = str_replace( 'ー', '-', $zipcode );
		$zipcode = str_replace( '―', '-', $zipcode );
		$zipcode = str_replace( '－', '-', $zipcode );
		return $zipcode;
	}
}
