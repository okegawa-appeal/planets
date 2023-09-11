<?php
/**
 * Front customized
 *
 * @package Welcart
 * @subpackage Welcart_Basic
 */

/**
 * Reset usces_cart.css
 */
function welcart_basic_remove_usces_cart_css() {
	global $usces;
	$usces->options['system']['no_cart_css'] = 1;
}
add_action( 'wp_enqueue_scripts', 'welcart_basic_remove_usces_cart_css', 8 );

/**
 * Search results exclude the member page and cart page
 *
 * @param object $query query.
 * @return object
 */
function welcart_basic_search_filter( $query ) {
	$page_cart   = get_page_by_path( 'usces-cart' );
	$page_member = get_page_by_path( 'usces-member' );
	if ( ! $query->is_admin && $query->is_search ) {
		$query->set( 'post__not_in', array( $page_cart->ID, $page_member->ID ) );
	}
	return $query;
}
add_filter( 'pre_get_posts', 'welcart_basic_search_filter' );

/**
 * No Image
 *
 * @return array
 */
function welcart_basic_icon_dirs() {
	if ( file_exists( get_stylesheet_directory() . '/images/crystal/default.png' ) ) {
		$icon_dir     = get_stylesheet_directory() . '/images/crystal';
		$icon_dir_uri = get_stylesheet_directory_uri() . '/images/crystal';
	} else {
		$icon_dir     = get_template_directory() . '/images/crystal';
		$icon_dir_uri = get_template_directory_uri() . '/images/crystal';
	}
	$icon_dirs = array( $icon_dir => $icon_dir_uri );
	return $icon_dirs;
}
add_filter( 'icon_dirs', 'welcart_basic_icon_dirs' );

/**
 * Update settlement page sidebar
 *
 * @param string $sidebar sidebar.
 * @return string
 */
function welcart_basic_member_update_settlement_page_sidebar( $sidebar ) {
	return '';
}
add_filter( 'usces_filter_member_update_settlement_page_sidebar', 'welcart_basic_member_update_settlement_page_sidebar' );

/**
 * Image size of assistance item
 *
 * @param int $size size.
 * @return int
 */
function welcart_basic_assistance_item_size( $size ) {
	return 165;
}
add_filter( 'usces_filter_assistance_item_width', 'welcart_basic_assistance_item_size' );
add_filter( 'usces_filter_assistance_item_height', 'welcart_basic_assistance_item_size' );

/**
 * Image size of list item
 *
 * @param string $html html.
 * @param string $content content.
 * @return string
 */
function welcart_basic_item_list_loopimg( $html, $content ) {
	global $post;

	$html = '<div class="loopimg"><a href="' . get_permalink( $post->ID ) . '">' . usces_the_itemImage( 0, 300, 300, $post, 'return' ) . '</a></div>' .
	'<div class="loopexp"><div class="field">' . $content . '</div></div>';

	return $html;
}
add_filter( 'usces_filter_item_list_loopimg', 'welcart_basic_item_list_loopimg', 10, 2 );

/**
 * Get Campaign Message
 *
 * @param int $post_id post_id.
 * @return string
 */
function get_welcart_basic_campaign_message( $post_id = null ) {
	global $post, $usces;
	if ( null === $post_id ) {
		$post_id = $post->ID;
	}

	$html    = '';
	$options = $usces->options;

	if ( 'Promotionsale' === $options['display_mode'] && in_category( (int) $options['campaign_category'], $post_id ) ) {
		if ( 'discount' === $options['campaign_privilege'] && ! empty( $options['privilege_discount'] ) ) {
			$html = '<div class="campaign_message campaign_discount">' . sprintf( __( 'Save %d&#37;', 'welcart_basic' ), $options['privilege_discount'] ) . '</div>'; // phpcs:ignore
		} elseif ( 'point' === $options['campaign_privilege'] && ! empty( $options['privilege_point'] ) ) {
			$html = '<div class="campaign_message campaign_point">' . sprintf( __( '%d times more points', 'welcart_basic' ), $options['privilege_point'] ) . '</div>'; // phpcs:ignore
		}
	}

	return apply_filters( 'welcart_basic_filter_campaign_message', $html, $post_id );
}

/**
 * Campaign Message
 *
 * @param int $post_id post_id.
 */
function welcart_basic_campaign_message( $post_id = null ) {
	echo wp_kses_post( get_welcart_basic_campaign_message( $post_id ) );
}

/**
 * Remove hentry
 *
 * @param array $classes classes.
 * @return array
 */
function welcart_basic_remove_hentry( $classes ) {

	$idx = array_search( 'hentry', $classes, true );
	if ( false !== $idx ) {
		unset( $classes[ $idx ] );
	}

	return $classes;
}
add_filter( 'post_class', 'welcart_basic_remove_hentry' );

/**
 * Init
 */
function welcart_basic_widgetcart_init() {
	if ( is_admin() || ! defined( 'WCEX_WIDGET_CART_VERSION' ) ) {
		return;
	}
	if ( version_compare( WCEX_WIDGET_CART_VERSION, '1.2.2', '<' ) ) {
		remove_filter( 'usces_filter_uscesL10n', 'widgetcart_filter_uscesL10n' );
		add_filter( 'usces_filter_uscesL10n', 'welcart_basic_widgetcart_uscesL10n' );
	}
}
add_action( 'init', 'welcart_basic_widgetcart_init', 99 );

/**
 * Widget cart alert
 *
 * @return string
 */
function welcart_basic_widgetcart_usces_L10n() {
	global $usces;

	$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : null;
	if ( $request_uri && $usces->is_cart_or_member_page( $request_uri ) || $request_uri && $usces->is_inquiry_page( $request_uri ) ) {
		echo "'widgetcartUrl': '" . esc_url( WCEX_WIDGET_CART_URL ) . "',\n";
		echo "'widgetcartHome': '" . esc_url( USCES_SSL_URL ) . "',\n";
	} else {
		echo "'widgetcartUrl': '" . esc_url( WCEX_WIDGET_CART_URL ) . "',\n";
		echo "'widgetcartHome': '" . esc_url( get_option( 'home' ) ) . "',\n";
	}
	echo "'widgetcartMes01': '" . __( 'Added to the cart.', 'widgetcart' ) . '<div id="wdgctToCheckout"><a href="' . esc_url( USCES_CUSTOMER_URL ) . '">' . __( 'Proceed to checkout', 'usces' ) . '</a></div>' . "',\n";
	echo "'widgetcartMes02': '" . __( 'Deleted from the cart.', 'widgetcart' ) . "',\n";
	echo "'widgetcartMes03': '" . __( 'Putting this article in the cart.', 'widgetcart' ) . "',\n";
	echo "'widgetcartMes04': '" . __( 'Please wait for a while.', 'widgetcart' ) . "',\n";
	echo "'widgetcartMes05': '" . __( 'Deleting an article from the cart.', 'widgetcart' ) . "',\n";
	echo "'widgetcart_fout': 5000,\n";

	return '';
}
