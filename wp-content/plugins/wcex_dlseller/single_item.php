<?php
/**
 * DL Seller single item page.
 *
 * @package WCEX DL Seller
 */

usces_the_item();
// $usces_item = dlseller_get_item();
$product           = wel_get_product( $post->ID );
$dlseller_interval = $product['dlseller_interval'];
usces_have_skus();
$charging_type = $this->getItemChargingType( $post->ID );
$division      = dlseller_get_division( $post->ID );

$html = '<!-- single_item.php -->';

$html      .= '<div id="itempage">';
$html      .= '<div class="itemimg">';
$html      .= '<a href="' . usces_the_itemImageURL( 0, 'return' ) . '"';
$html       = apply_filters( 'usces_itemimg_anchor_rel', $html );
$html      .= '>';
$item_image = usces_the_itemImage( 0, 300, 300, $post, 'return' );
$html      .= apply_filters( 'usces_filter_the_itemImage', $item_image, $post );
$html      .= '</a>';
$html      .= '</div>';

$html .= '<h3>' . esc_html( usces_the_itemName( 'return' ) ) . '&nbsp; ( ' . esc_html( usces_the_itemCode( 'return' ) ) . ' ) </h3>';

/* Download contents */
if ( 'data' === $division ) {

	$html .= '<div class="exp">';
	$html .= '<div class="field">';
	if ( isset( $this->itemsku['value']['cprice'] ) && 0 < $this->itemsku['value']['cprice'] ) {
		$usces_listprice = __( 'List price', 'usces' ) . usces_guid_tax( 'return' );
		$html           .= '<div class="field_name">' . apply_filters( 'usces_filter_listprice_label', $usces_listprice, __( 'List price', 'usces' ), usces_guid_tax( 'return' ) ) . '</div>';
		$html           .= '<div class="field_cprice">' . usces_the_itemCpriceCr( 'return' );
		if ( 'continue' === $charging_type ) {
			$html .= '( ' . dlseller_frequency_name( $post->ID, 'amount', 'return' ) . ' )';
		}
		$html .= '</div>';
	}
	$usces_sellingprice = __( 'selling price', 'usces' ) . usces_guid_tax( 'return' );
	$html              .= '<div class="field_name">' . apply_filters( 'usces_filter_sellingprice_label', $usces_sellingprice, __( 'selling price', 'usces' ), usces_guid_tax( 'return' ) ) . '</div>';
	$html              .= '<div class="field_price">' . usces_the_itemPriceCr( 'return' );
	if ( 'continue' === $charging_type ) {
		$html .= '( ' . dlseller_frequency_name( $post->ID, 'amount', 'return' ) . ' )';
	}
	$html .= '</div>';
	$html .= '</div>';
	if ( 'continue' === $charging_type ) {
		// Charging Type Continue.
		$html .= '<div class="field">';
		$html .= '<table class="dlseller">';
		$html .= '<tr><th>' . __( 'First Withdrawal Date', 'dlseller' ) . '</th><td>' . dlseller_first_charging( $post->ID ) . '</td></tr>';
		if ( 0 < (int) $dlseller_interval ) {
			$html .= '<tr><th>' . __( 'Contract Period', 'dlseller' ) . '</th><td>' . $dlseller_interval . __( 'Month(Automatic Renewal)', 'dlseller' ) . '</td></tr>';
		}
		$html .= '</table>';
		$html .= '</div>';
	}
	$item_custom = usces_get_item_custom( $post->ID, 'table', 'return' );
	if ( $item_custom ) {
		$html .= '<div class="field">';
		$html .= $item_custom;
		$html .= '</div>';
	}
	$html .= '<div class="field"><table class="dlseller">';
	$html .= '<tr><th>' . __( 'dlValidity(days)', 'dlseller' ) . '</th><td>' . esc_html( usces_dlseller_validity( $post ) ) . '</td></tr>';
	$html .= '<tr><th>' . __( 'File Name', 'dlseller' ) . '</th><td>' . esc_html( usces_dlseller_filename( $post ) ) . '</td></tr>';
	$html .= '<tr><th>' . __( 'Release Date', 'dlseller' ) . '</th><td>' . esc_html( usces_get_itemMeta( '_dlseller_date', $post->ID, 'return' ) ) . '</td></tr>';
	$html .= '<tr><th>' . __( 'Version', 'dlseller' ) . '</th><td>' . esc_html( usces_get_itemMeta( '_dlseller_version', $post->ID, 'return' ) ) . '</td></tr>';
	$html .= '<tr><th>' . __( 'Author', 'dlseller' ) . '</th><td>' . esc_html( usces_get_itemMeta( '_dlseller_author', $post->ID, 'return' ) ) . '</td></tr>';
	$html  = apply_filters( 'dlseller_filter_item_field', $html, $post );
	$html .= '</table>';
	$html .= '</div>';

	$html .= $content;

	$html .= '</div><!-- end of exp -->';
	$html .= usces_the_itemGpExp( 'return' );

	$html .= '<form action="' . USCES_CART_URL . '" method="post">';
	$html .= '<div class="skuform" align="right">';
	if ( usces_is_options() ) {
		$html .= '<table class="item_option"><caption>' . apply_filters( 'usces_filter_single_item_options_caption', __( 'Please appoint an option.', 'usces' ), $post ) . '</caption>';
		while ( usces_have_options() ) {
			$opttr = '<tr><th>' . esc_html( usces_getItemOptName() ) . '</th><td>' . usces_the_itemOption( usces_getItemOptName(), '', 'return' ) . '</td></tr>';
			$html .= apply_filters( 'usces_filter_singleitem_option', $opttr, usces_getItemOptName(), null );
		}
		$html .= '</table>';
	}

	$html .= '<div style="margin-top:10px">' . usces_the_itemSkuButton( __( 'Add to Shopping Cart', 'usces' ), 0, 'return' ) . '</div>';

	$html .= '</div><!-- end of skuform -->';
	$html  = apply_filters( 'usces_filter_single_item_inform', $html );
	$html .= '</form>';
	$html .= '<div class="clear"></div>';

/* Service one charge */
} elseif ( 'service' === $division ) {

	if ( 1 === usces_sku_num() ) { // 1SKU.
		$html .= '<div class="exp">';

		$html .= '<div class="field">';
		if ( isset( $this->itemsku['value']['cprice'] ) && 0 < $this->itemsku['value']['cprice'] ) {
			$usces_listprice = __( 'List price', 'usces' ) . usces_guid_tax( 'return' );
			$html           .= '<div class="field_name">' . apply_filters( 'usces_filter_listprice_label', $usces_listprice, __( 'List price', 'usces' ), usces_guid_tax( 'return' ) ) . '</div>';
			$html           .= '<div class="field_cprice">' . usces_the_itemCpriceCr( 'return' );
			if ( 'continue' === $charging_type ) {
				$html .= '( ' . dlseller_frequency_name( $post->ID, 'amount', 'return' ) . ' )';
			}
			$html .= '</div>';
		}
		$usces_sellingprice = __( 'selling price', 'usces' ) . usces_guid_tax( 'return' );
		$html              .= '<div class="field_name">' . apply_filters( 'usces_filter_sellingprice_label', $usces_sellingprice, __( 'selling price', 'usces' ), usces_guid_tax( 'return' ) ) . '</div>';
		$html              .= '<div class="field_price">' . usces_the_itemPriceCr( 'return' );
		if ( 'continue' === $charging_type ) {
			$html .= '( ' . dlseller_frequency_name( $post->ID, 'amount', 'return' ) . ' )';
		}
		$html .= '</div>';
		$html .= '</div>';
		if ( 'continue' === $charging_type ) {
			// Charging Type Continue.
			$html .= '<div class="field">';
			$html .= '<table class="dlseller">';
			$html .= '<tr><th>' . __( 'First Withdrawal Date', 'dlseller' ) . '</th><td>' . dlseller_first_charging( $post->ID ) . '</td></tr>';
			if ( 0 < (int) $dlseller_interval ) {
				$html .= '<tr><th>' . __( 'Contract Period', 'dlseller' ) . '</th><td>' . $dlseller_interval . __( 'Month(Automatic Renewal)', 'dlseller' ) . '</td></tr>';
			}
			$html .= '</table>';
			$html .= '</div>';
		}
		$item_custom = usces_get_item_custom( $post->ID, 'table', 'return' );
		if ( $item_custom ) {
			$html .= '<div class="field">';
			$html .= $item_custom;
			$html .= '</div>';
		}

		$html .= $content;

		$html .= '</div><!-- end of exp -->';
		$html .= usces_the_itemGpExp( 'return' );

		$html .= '<form action="' . USCES_CART_URL . '" method="post">';
		$html .= '<div class="skuform" align="right">';
		if ( usces_is_options() ) {
			$html .= '<table class="item_option"><caption>' . apply_filters( 'usces_filter_single_item_options_caption', __( 'Please appoint an option.', 'usces' ), $post ) . '</caption>';
			while ( usces_have_options() ) {
				$opttr = '<tr><th>' . esc_html( usces_getItemOptName() ) . '</th><td>' . usces_the_itemOption( usces_getItemOptName(), '', 'return' ) . '</td></tr>';
				$html .= apply_filters( 'usces_filter_singleitem_option', $opttr, usces_getItemOptName(), null );
			}
			$html .= '</table>';
		}

		$html .= '<div style="margin-top:10px">' . apply_filters( 'usces_filter_autocharge_price_label', usces_the_itemSkuDisp( 'return' ) ) . __( 'Quantity', 'usces' ) . usces_the_itemQuant( 'return' ) . esc_html( usces_the_itemSkuUnit( 'return' ) ) . usces_the_itemSkuButton( __( 'Add to Shopping Cart', 'usces' ), 0, 'return' ) . '</div>';

		$html .= '</div><!-- end of skuform -->';
		$html  = apply_filters( 'usces_filter_single_item_inform', $html );
		$html .= '</form>';
		$html .= '<div class="clear"></div>';

	} elseif ( 1 < usces_sku_num() ) { // Some SKU.

		$html .= '<div class="exp">';
		$html .= $content;
		if ( 'continue' === $charging_type ) {
			// Charging Type Continue.
			$html .= '<div class="field">';
			$html .= '<table class="dlseller">';
			$html .= '<tr><th>' . __( 'First Withdrawal Date', 'dlseller' ) . '</th><td>' . dlseller_first_charging( $post->ID ) . '</td></tr>';
			if ( 0 < (int) $dlseller_interval ) {
				$html .= '<tr><th>' . __( 'Contract Period', 'dlseller' ) . '</th><td>' . $dlseller_interval . __( 'Month(Automatic Renewal)', 'dlseller' ) . '</td></tr>';
			}
			$html .= '</table>';
			$html .= '</div>';
		}
		$item_custom = usces_get_item_custom( $post->ID, 'list', 'return' );
		if ( $item_custom ) {
			$html .= '<div class="field">';
			$html .= $item_custom;
			$html .= '</div>';
		}
		$html .= '</div>';

		$html .= '<form action="' . USCES_CART_URL . '" method="post">';
		$html .= '<div class="skuform">';
		$html .= '<table class="skumulti">';
		$html .= '<thead>';
		$html .= '<tr>';
		$html .= '<th class="thborder">' . __( 'order number', 'usces' ) . '</th>';
		$html .= '<th class="thborder">' . __( 'Title', 'usces' ) . '</th>';
		if ( usces_the_itemCprice( 'return' ) > 0 ) {
			$usces_bothprice = '( ' . __( 'List price', 'usces' ) . ' )' . __( 'selling price', 'usces' ) . usces_guid_tax( 'return' );
			$html .= '<th class="thborder">' . apply_filters( 'usces_filter_bothprice_label', $usces_bothprice, __( 'List price', 'usces' ), __( 'selling price', 'usces' ), usces_guid_tax( 'return' ) ) . '</th>';
		} else {
			$usces_sellingprice = __( 'selling price', 'usces' ) . usces_guid_tax( 'return' );
			$html .= '<th class="thborder">' . apply_filters( 'usces_filter_sellingprice_label', $usces_sellingprice, __( 'selling price', 'usces' ), usces_guid_tax( 'return' ) ) . '</th>';
		}
		$html .= '</tr>';
		$html .= '</thead>';
		$html .= '<tbody>';
		do {
			$html .= '<tr>';
			$html .= '<td rowspan="2">' . esc_html( usces_the_itemSku( 'return' ) ) . '</td>';
			$html .= '<td rowspan="2" class="skudisp subborder">' . apply_filters( 'usces_filter_singleitem_skudisp', esc_html( usces_the_itemSkuDisp( 'return' ) ) );
			if ( usces_is_options() ) {
				$html .= '<table class="item_option"><caption>' . apply_filters( 'usces_filter_single_item_options_caption', __( 'Please appoint an option.', 'usces' ), $post ) . '</caption>';
				while ( usces_have_options() ) {
					$opttr = '<tr><th>' . esc_html( usces_getItemOptName() ) . '</th><td>' . usces_the_itemOption( usces_getItemOptName(), '', 'return' ) . '</td></tr>';
					$html .= apply_filters( 'usces_filter_singleitem_option', $opttr, usces_getItemOptName(), null );
				}
				$html .= '</table>';
			}
			$html .= '</td>';
			$html .= '<td class="subborder price">';
			if ( usces_the_itemCprice( 'return' ) > 0 ) {
				$html .= '<span class="cprice">( ' . usces_the_itemCpriceCr( 'return' ) . ' )';
				$html .= '</span>';
			}
			$html .= '<span class="price">' . usces_the_itemPriceCr( 'return' );
			if ( 'continue' === $charging_type ) {
				$html .= '( ' . dlseller_frequency_name( $post->ID, 'amount', 'return' ) . ' )';
			}
			$html .= '</span><br />';
			$html .= usces_the_itemGpExp( 'return' ) . '</td>';
			$html .= '</tr>';
			$html .= '<tr>';
			if ( ! usces_have_zaiko() ) {
				$html .= '<td class="button">' . apply_filters( 'usces_filters_single_sku_zaiko_message', __( 'Sold Out', 'usces' ) ) . '</td>';
			} else {
				$html .= '<td class="quant">' . usces_the_itemQuant( 'return' ) . '</td>';
				$html .= '<td class="unit">' . usces_the_itemSkuUnit( 'return' ) . '</td>';
				$html .= '<td class="button">' . apply_filters( 'usces_filter_autocharge_price_label', '' ) . usces_the_itemSkuButton( __( 'Add to Shopping Cart', 'usces' ), 0, 'return' ) . '</td>';
			}
			$html .= '</tr>';
			$html .= '<tr><td colspan="3" class="error_message">' . usces_singleitem_error_message( $post->ID, usces_the_itemSku( 'return' ), 'return' ) . '</td></tr>';
		} while ( usces_have_skus() );
		$html .= '</tbody>';
		$html .= '</table>';
		$html .= '</div><!-- end of skuform -->';
		$html  = apply_filters( 'usces_filter_single_item_inform', $html );
		$html .= '</form>';
	}
	$html .= apply_filters( 'single_item_multi_sku_after_field', null );

/* Shipped item */
} else {

	if ( 1 === usces_sku_num() ) { // 1SKU.

		$html .= '<div class="exp">';
		$html .= '<div class="field">';
		if ( 0 < usces_the_itemCprice( 'return' ) ) {
			$usces_listprice = __( 'List price', 'usces' ) . usces_guid_tax( 'return' );
			$html           .= '<div class="field_name">' . apply_filters( 'usces_filter_listprice_label', $usces_listprice, __( 'List price', 'usces' ), usces_guid_tax( 'return' ) ) . '</div>';
			$html           .= '<div class="field_cprice">' . usces_the_itemCpriceCr( 'return' );
			if ( 'continue' === $charging_type ) {
				$html .= '( ' . dlseller_frequency_name( $post->ID, 'amount', 'return' ) . ' )';
			}
			$html .= '</div>';
		}
		$usces_sellingprice = __( 'selling price', 'usces' ) . usces_guid_tax( 'return' );
		$html              .= '<div class="field_name">' . apply_filters( 'usces_filter_sellingprice_label', $usces_sellingprice, __( 'selling price', 'usces' ), usces_guid_tax( 'return' ) ) . '</div>';
		$html              .= '<div class="field_price">' . usces_the_itemPriceCr( 'return' );
		if ( 'continue' === $charging_type ) {
			$html .= '( ' . dlseller_frequency_name( $post->ID, 'amount', 'return' ) . ' )';
		}
		$html       .= '</div>';
		$html       .= '</div>';
		$singlestock = '<div class="field">' . __( 'stock status', 'usces' ) . ' : ' . esc_html( usces_the_itemZaiko( 'return' ) ) . '</div>';
		$html       .= apply_filters( 'single_item_stock_field', $singlestock );
		if ( 'continue' === $charging_type ) {
			// Charging Type Continue.
			$html .= '<div class="field">';
			$html .= '<table class="dlseller">';
			$html .= '<tr><th>' . __( 'First Withdrawal Date', 'dlseller' ) . '</th><td>' . dlseller_first_charging( $post->ID ) . '</td></tr>';
			if ( 0 < (int) $dlseller_interval ) {
				$html .= '<tr><th>' . __( 'Contract Period', 'dlseller' ) . '</th><td>' . $dlseller_interval . __( 'Month(Automatic Renewal)', 'dlseller' ) . '</td></tr>';
			}
			$html .= '</table>';
			$html .= '</div>';
		}

		$item_custom = usces_get_item_custom( $post->ID, 'list', 'return' );
		if ( $item_custom ) {
			$html .= '<div class="field">';
			$html .= $item_custom;
			$html .= '</div>';
		}

		$html .= $content;
		$html .= '</div><!-- end of exp -->';
		$html .= usces_the_itemGpExp( 'return' );

		$html .= '<form action="' . USCES_CART_URL . '" method="post">';
		$html .= '<div class="skuform" align="right">';
		if ( usces_is_options() ) {
			$html .= '<table class="item_option"><caption>' . apply_filters( 'usces_filter_single_item_options_caption', __( 'Please appoint an option.', 'usces' ), $post ) . '</caption>';
			while ( usces_have_options() ) {
				$opttr = '<tr><th>' . esc_html( usces_getItemOptName() ) . '</th><td>' . usces_the_itemOption( usces_getItemOptName(), '', 'return' ) . '</td></tr>';
				$html .= apply_filters( 'usces_filter_singleitem_option', $opttr, usces_getItemOptName(), null );
			}
			$html .= '</table>';
		}
		if ( ! usces_have_zaiko() ) {
			$html .= '<div class="zaiko_status">' . apply_filters( 'usces_filters_single_sku_zaiko_message', __( 'Sold Out', 'usces' ) ) . '</div>';
		} else {
			$html .= '<div style="margin-top:10px">' . __( 'Quantity', 'usces' ) . usces_the_itemQuant( 'return' ) . esc_html( usces_the_itemSkuUnit( 'return' ) ) . usces_the_itemSkuButton( __( 'Add to Shopping Cart', 'usces' ), 0, 'return' ) . '</div>';
			$html .= '<div class="error_message">' . usces_singleitem_error_message( $post->ID, usces_the_itemSku( 'return' ), 'return' ) . '</div>';
		}

		$html .= '</div><!-- end of skuform -->';
		$html  = apply_filters( 'usces_filter_single_item_inform', $html );
		$html .= '</form>';
		$html .= apply_filters( 'single_item_single_sku_after_field', null );

	} elseif ( 1 < usces_sku_num() ) { // Some SKU.

		$html .= '<div class="exp">';
		$html .= $content;
		if ( 'continue' === $charging_type ) {
			// Charging Type Continue.
			$html .= '<div class="field">';
			$html .= '<table class="dlseller">';
			$html .= '<tr><th>' . __( 'First Withdrawal Date', 'dlseller' ) . '</th><td>' . dlseller_first_charging( $post->ID ) . '</td></tr>';
			if ( 0 < (int) $dlseller_interval ) {
				$html .= '<tr><th>' . __( 'Contract Period', 'dlseller' ) . '</th><td>' . $dlseller_interval . __( 'Month(Automatic Renewal)', 'dlseller' ) . '</td></tr>';
			}
			$html .= '</table>';
			$html .= '</div>';
		}
		$item_custom = usces_get_item_custom( $post->ID, 'list', 'return' );
		if ( $item_custom ) {
			$html .= '<div class="field">';
			$html .= $item_custom;
			$html .= '</div>';
		}
		$html .= '</div>';

		$html .= '<form action="' . USCES_CART_URL . '" method="post">';
		$html .= '<div class="skuform">';
		$html .= '<table class="skumulti">';
		$html .= '<thead>';
		$html .= '<tr>';
		$html .= '<th rowspan="2" class="thborder">' . __( 'order number', 'usces' ) . '</th>';
		$html .= '<th colspan="2">' . __( 'Title', 'usces' ) . '</th>';
		if ( usces_the_itemCprice( 'return' ) > 0 ) {
			$usces_bothprice = '( ' . __( 'List price', 'usces' ) . ' )' . __( 'selling price', 'usces' ) . usces_guid_tax( 'return' );
			$html           .= '<th colspan="2">' . apply_filters( 'usces_filter_bothprice_label', $usces_bothprice, __( 'List price', 'usces' ), __( 'selling price', 'usces' ), usces_guid_tax( 'return' ) ) . '</th>';
		} else {
			$usces_sellingprice = __( 'selling price', 'usces' ) . usces_guid_tax( 'return' );
			$html              .= '<th colspan="2">' . apply_filters( 'usces_filter_sellingprice_label', $usces_sellingprice, __( 'selling price', 'usces' ), usces_guid_tax( 'return' ) ) . '</th>';
		}
		$html .= '</tr>';
		$html .= '<tr>';
		$html .= '<th class="thborder">' . __( 'stock status', 'usces' ) . '</th>';
		$html .= '<th class="thborder">' . __( 'Quantity', 'usces' ) . '</th>';
		$html .= '<th class="thborder">' . __( 'unit', 'usces' ) . '</th>';
		$html .= '<th class="thborder">&nbsp;</th>';
		$html .= '</tr>';
		$html .= '</thead>';
		$html .= '<tbody>';
		do {
			$html .= '<tr>';
			$html .= '<td rowspan="2">' . esc_html( usces_the_itemSku( 'return' ) ) . '</td>';
			$html .= '<td colspan="2" class="skudisp subborder">' . apply_filters( 'usces_filter_singleitem_skudisp', esc_html( usces_the_itemSkuDisp( 'return' ) ) );
			if ( usces_is_options() ) {
				$html .= '<table class="item_option"><caption>' . apply_filters( 'usces_filter_single_item_options_caption', __( 'Please appoint an option.', 'usces' ), $post ) . '</caption>';
				while ( usces_have_options() ) {
					$opttr = '<tr><th>' . esc_html( usces_getItemOptName() ) . '</th><td>' . usces_the_itemOption( usces_getItemOptName(), '', 'return' ) . '</td></tr>';
					$html .= apply_filters( 'usces_filter_singleitem_option', $opttr, usces_getItemOptName(), null );
				}
				$html .= '</table>';
			}
			$html .= '</td>';
			$html .= '<td colspan="2" class="subborder price">';
			if ( usces_the_itemCprice( 'return' ) > 0 ) {
				$html .= '<span class="cprice">( ' . usces_the_itemCpriceCr( 'return' ) . ' )';
				$html .= '</span>';
			}
			$html .= '<span class="price">' . usces_the_itemPriceCr( 'return' );
			if ( 'continue' === $charging_type ) {
				$html .= '( ' . dlseller_frequency_name( $post->ID, 'amount', 'return' ) . ' )';
			}
			$html .= '</span><br />';
			$html .= usces_the_itemGpExp( 'return' ) . '</td>';
			$html .= '</tr>';
			$html .= '<tr>';
			$html .= '<td class="zaiko">' . usces_the_itemZaiko( 'return' ) . '</td>';
			if ( ! usces_have_zaiko() ) {
				$html .= '<td class="button">' . apply_filters( 'usces_filters_single_sku_zaiko_message', __( 'Sold Out', 'usces' ) ) . '</td>';
			} else {
				$html .= '<td class="quant">' . usces_the_itemQuant( 'return' ) . '</td>';
				$html .= '<td class="unit">' . usces_the_itemSkuUnit( 'return' ) . '</td>';
				$html .= '<td class="button">' . usces_the_itemSkuButton( __( 'Add to Shopping Cart', 'usces' ), 0, 'return' ) . '</td>';
			}
			$html .= '</tr>';
			$html .= '<tr><td colspan="5" class="error_message">' . usces_singleitem_error_message( $post->ID, usces_the_itemSku( 'return' ), 'return' ) . '</td></tr>';

		} while ( usces_have_skus() );
		$html .= '</tbody>';
		$html .= '</table>';
		$html .= '</div><!-- end of skuform -->';
		$html  = apply_filters( 'usces_filter_single_item_inform', $html );
		$html .= '</form>';
		$html .= apply_filters( 'single_item_multi_sku_after_field', null );
	}
}

$subimage = usces_get_itemSubImageNums();
if ( ! empty( $subimage ) ) {
	$html .= '<div class="itemsubimg">';
	foreach ( $subimage as $imageid ) {
		$html      .= '<a href="' . usces_the_itemImageURL( $imageid, 'return' ) . '"';
		$html       = apply_filters( 'usces_itemimg_anchor_rel', $html );
		$html      .= '>';
		$item_image = usces_the_itemImage( $imageid, 137, 200, $post, 'return' );
		$html      .= apply_filters( 'usces_filter_the_SubImage', $item_image, $post, $imageid );
		$html      .= '</a>';
	}
	$html .= '</div><!-- end of itemsubimg -->';
}

if ( usces_get_assistance_id_list( $post->ID ) ) {
	$html           .= '<div class="assistance_item">';
	$assistanceposts = get_posts( 'include=' . usces_get_assistance_id_list( $post->ID ) );
	if ( $assistanceposts ) {
		$html .= '<h3>' . esc_html( usces_the_itemCode( 'return' ) ) . __( 'An article concerned', 'usces' ) . '</h3>';
		$html .= '<ul class="clearfix">';
		foreach ( $assistanceposts as $assistance ) {
			setup_postdata( $assistance );
			usces_the_item();
			$html .= '<li><div class="listbox clearfix">';
			$html .= '<div class="slit"><a href="' . get_permalink( $post->ID ) . '" rel="bookmark" title="' . esc_attr( $post->post_title ) . '">' . usces_the_itemImage( 0, 100, 100, $post, 'return' ) . '</a></div>';
			$html .= '<div class="detail">';
			$html .= '<h4>' . esc_html( usces_the_itemName( 'return' ) ) . '</h4>' . $post->post_excerpt;
			$html .= '<p>';
			if ( usces_is_skus() ) {
				$html .= usces_crform( usces_the_firstPrice( 'return' ), true, false, 'return' );
			}
			$html .= '<br />';
			$html .= '&raquo; <a href="' . get_permalink( $post->ID ) . '" rel="bookmark" title="' . esc_attr( $post->post_title ) . '">' . __( 'see the details', 'usces' ) . '</a></p>';
			$html .= '</div>';
			$html .= '</div></li>';
		}
		$html .= '</ul>';
		wp_reset_postdata();
	}
	$html .= '</div><!-- end of assistance_item -->';
}

$html .= '</div><!-- end of itemspage -->';
$html  = apply_filters( 'usces_filter_single_item', $html, $post, $content );
