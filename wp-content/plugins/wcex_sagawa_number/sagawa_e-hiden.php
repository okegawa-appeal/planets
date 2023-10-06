<?php
/**
 * Plugin Name: WCEX Sagawa Number
 * Plugin URI: https://www.welcart.com/
 * Description: このプラグインは Welcart と佐川急便の送り状発行サポートシステム「e飛伝Ⅲ」との連携プラグインです。Welcart 本体と一緒にご利用ください。
 * Version: 2.0.0
 * Author: Collne Inc.
 * Author URI: https://www.collne.com/
 *
 * @package WCEX Sagawa Number
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! defined( 'USCES_VERSION' ) ) {
	return;
}

define( 'WCEX_SAGAWA_NUMBER', true );
define( 'WCEX_SAGAWA_NUMBER_VERSION', '2.0.0.2205181' );
define( 'WCEX_SAGAWA_NUMBER_DIR', WP_PLUGIN_DIR . '/' . plugin_basename( dirname( __FILE__ ) ) );
define( 'WCEX_SAGAWA_NUMBER_URL', WP_PLUGIN_URL . '/' . plugin_basename( dirname( __FILE__ ) ) );

global $sagawa_e_hiden;
if ( is_object( $sagawa_e_hiden ) ) {
	return;
}

require_once WCEX_SAGAWA_NUMBER_DIR . '/class-sagawa-number.php';
$sagawa_e_hiden = new WCEX_SAGAWA_NUMBER();
