<?php
/**
 * Plugin Name: WCEX Custom Member Validation Patch
 * Description: A patch to validate member data.
 * Version: 1.0.1
 * Author: Welcart Inc.
 *
 * @package Welcart
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! defined( 'USCES_VERSION' ) ) {
	return;
}

add_filter( 'usces_filter_member_check', 'wel_custom_member_check', 20 );

/**
 * Custom member validation function.
 *
 * @param string $mes The existing error message.
 * @return string Modified error message.
 */
function wel_custom_member_check( $mes ) {
	if ( ! isset( $_POST['member_regmode'] ) || 'newmemberform' !== $_POST['member_regmode'] ) {
		return $mes;
	}

	$member = array(
		'name1'    => ( isset( $_POST['member']['name1'] ) ) ? trim( $_POST['member']['name1'] ) : '',
		'name2'    => ( isset( $_POST['member']['name2'] ) ) ? trim( $_POST['member']['name2'] ) : '',
		'zipcode'  => ( isset( $_POST['member']['zipcode'] ) ) ? trim( $_POST['member']['zipcode'] ) : '',
		'pref'     => ( isset( $_POST['member']['pref'] ) ) ? trim( $_POST['member']['pref'] ) : '',
		'address1' => ( isset( $_POST['member']['address1'] ) ) ? trim( $_POST['member']['address1'] ) : '',
		'tel'      => ( isset( $_POST['member']['tel'] ) ) ? trim( $_POST['member']['tel'] ) : '',
		'fax'      => ( isset( $_POST['member']['fax'] ) ) ? trim( $_POST['member']['fax'] ) : '',
	);

	$member['country'] = ( isset( $_POST['member']['country'] ) ) ? trim( $_POST['member']['country'] ) : usces_get_base_country();
	if ( 'JP' === $member['country'] ) {
		if ( ! wel_validate_japanese_surname( $member['name1'] )
			|| ! wel_validate_japanese_surname( $member['name2'] )
			|| empty( $member['name1'] )
			|| empty( $member['name2'] )
			|| ! wel_validate_numeric_hyphen( $member['zipcode'] )
			|| ! wel_validate_japanese_prefecture( $member['pref'] )
			|| ! wel_validate_japanese_cityname( $member['address1'] )
			|| ! wel_validate_numeric_hyphen( $member['tel'] )
			|| ! wel_validate_numeric_hyphen( $member['fax'] )
		) {
			$mes .= '入力値が不正です。';
		}
	} else {
		if ( empty( $member['name1'] )
			|| empty( $member['name2'] )
			|| ! wel_validate_numeric_hyphen( $member['zipcode'] )
			|| ! wel_validate_prefecture( $member['pref'], $member['country'] )
			|| ! wel_validate_numeric_hyphen( $member['tel'] )
			|| ! wel_validate_numeric_hyphen( $member['fax'] )
		) {
			$mes .= 'Input value is incorrect.';
		}
	}

	return $mes;
}

/**
 * Validates a Japanese surname.
 *
 * @param string $surname The surname to validate.
 * @return bool True if valid, false otherwise.
 */
function wel_validate_japanese_surname( $surname ) {
	// ひらがな、カタカナ、漢字のみを許可（スペースは除外）.
	if ( ! preg_match( '/^[\p{Han}\p{Hiragana}\p{Katakana}]+$/u', $surname ) ) {
		return false;
	}

	// 長さのチェック（1文字以上6文字以下）.
	$length = mb_strlen( $surname );
	if ( $length < 1 || $length > 6 ) {
		return false;
	}

	// 一般的でない姓のパターンをチェック.
	if ( preg_match( '/^(.)\1+$/u', $surname ) ) {
		return false; // 同じ文字の繰り返しは不適切.
	}

	return true;
}

/**
 * Validates numeric input with optional hyphens.
 *
 * @param string $input The input to validate.
 * @param string $pattern Optional regex pattern.
 * @return bool True if valid, false otherwise.
 */
function wel_validate_numeric_hyphen( $input, $pattern = null ) {
	if ( empty( $input ) ) {
		return true;
	}
	$default_pattern = '/^[0-9-]+$/';
	$regex           = $pattern ?? $default_pattern;
	return 1 === preg_match( $regex, $input );
}

/**
 * Validates a prefecture name.
 *
 * @param string $input The prefecture name to validate.
 * @return bool True if valid, false otherwise.
 */
function wel_validate_japanese_prefecture( $input ) {
	if ( empty( $input ) ) {
		return true;
	}
	if ( '--選択--' === $input ) {
		return true;
	}
	$pattern = '/^[\x{4E00}-\x{9FFF}]{1,4}$/u';
	return 1 === preg_match( $pattern, $input );
}

/**
 * Validates a prefecture name.
 *
 * @param string $input The prefecture name to validate.
 * @param string $country Country code.
 * @return bool True if valid, false otherwise.
 */
function wel_validate_prefecture( $input, $country ) {
	if ( empty( $input ) ) {
		return true;
	}
	if ( '-- Select --' === $input ) {
		return true;
	}
	$options = get_option( 'usces', array() );
	if ( ! in_array( $country, $options['system']['target_market'] ) ) {
		return false;
	}
	$prefs = get_usces_states( $country );
	if ( is_array( $prefs ) && ! empty( $prefs ) ) {
		if ( ! in_array( $input, $prefs ) ) {
			return false;
		}
	} else {
		return false;
	}
	return true;
}

/**
 * Validates a city name.
 *
 * @param string $input The city name to validate.
 * @return bool True if valid, false otherwise.
 */
function wel_validate_japanese_cityname( $input ) {
	if ( empty( $input ) ) {
		return true;
	}
	$pattern = '/^[ぁ-んァ-ヶー一-龠々〆〤ヵヶ（）・]+$/u';
	return 1 === preg_match( $pattern, $input );
}
