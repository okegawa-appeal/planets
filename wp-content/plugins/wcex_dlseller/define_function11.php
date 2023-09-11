<?php
/**
 * Welcart Product CSV bulk processing.
 *
 * @package WCEX DL Seller
 */

defined( 'ABSPATH' ) || exit;

/**
 * CSV generation process and bulk update by CSV.
 *
 * @since  2.2.2
 */
function dlseller_define_functions() {

	if ( ! function_exists( 'usces_item_uploadcsv' ) ) :

		/**
		 * All columns Bulk registration & update by CSV.
		 *
		 * @since  2.2.2
		 */
		function usces_item_uploadcsv() {
			global $wpdb, $usces, $user_ID;

			$check_mode  = isset( $_REQUEST['checkcsv'] ) ? true : false;
			$check_label = $check_mode ? __( '[Check mode]', 'usces' ) : '';
			if ( $check_mode ) {
				define( 'USCES_ITEM_UP_INTERBAL', 200 );
			} else {
				define( 'USCES_ITEM_UP_INTERBAL', 100 );
			}

			if ( ! current_user_can( 'import' ) ) {
				$progress = array(
					'status'   => __( 'forced termination', 'usces' ) . $check_label,
					'progress' => __( 'The process was not completed', 'usces' ),
					'log'      => 'Error : ' . __( 'You do not have permission to do that.', 'usces' ),
					'flag'     => 'complete',
				);
				record_item_up_progress( $progress );
				return;
			}

			$upload_folder = WP_CONTENT_DIR . USCES_UPLOAD_TEMP . '/';

			// Upload.
			if ( isset( $_REQUEST['action'] ) && 'itemcsv' === $_REQUEST['action'] ) {

				$progress = array( 'log' => 'clear' );
				record_item_up_progress( $progress );

				$upload_mode  = isset( $_REQUEST['upload_mode'] ) ? $_REQUEST['upload_mode'] : '';
				$mode_name    = usces_get_upmode_name( $upload_mode );
				$org_filename = $_FILES['usces_upcsv']['name'];
				$tmp_filename = $_FILES['usces_upcsv']['tmp_name'];

				list( $fname, $fext ) = explode( '.', $org_filename, 2 );
				$new_filename         = base64_encode( $fname . '_' . time() . '.' . $fext );

				$file_info = array(
					'filename' => $org_filename,
					'mode'     => $mode_name,
					'rowcount' => '',
					'header'   => '',
				);

				$db_check = usces_item_code_duplication_check();
				if ( $db_check ) {
					foreach ( $db_check as $d_item ) {
						$code .= ' , ' . $d_item['itemCode'];
					}
					$log  = 'Error : ' . __( 'The same product cord is registered.', 'usces' ) . "\n";
					$log .= __( 'The following product code is duplicated. Please eliminate duplicates before uploading.', 'usces' ) . "\n";
					$log .= ltrim( $code, ' , ' );

					$progress = array(
						'info'     => $file_info,
						'status'   => __( 'forced termination', 'usces' ) . $check_label,
						'progress' => __( 'The process was not completed', 'usces' ),
						'log'      => $log,
						'flag'     => 'complete',
					);
					record_item_up_progress( $progress );
					return;
				}

				if ( ! is_uploaded_file( $tmp_filename ) ) {
					$progress = array(
						'info'     => $file_info,
						'status'   => __( 'forced termination', 'usces' ) . $check_label,
						'progress' => __( 'The process was not completed', 'usces' ),
						'log'      => 'Error : ' . __( 'The file was not uploaded.', 'usces' ),
						'flag'     => 'complete',
					);
					record_item_up_progress( $progress );
					return;
				}

				/* check ext */
				if ( 'csv' !== $fext ) {
					$progress = array(
						'info'     => $file_info,
						'status'   => __( 'forced termination', 'usces' ) . $check_label,
						'progress' => __( 'The process was not completed', 'usces' ),
						'flag'     => 'complete',
						'log'      => 'Error : ' . __( 'The file is not supported.', 'usces' ) . ' ( ' . $org_filename . ' )',
						'flag'     => 'complete',
					);
					record_item_up_progress( $progress );
					unlink( $upload_folder . $file_name );
					return;
				}

				if ( ! move_uploaded_file( $_FILES['usces_upcsv']['tmp_name'], $upload_folder . $new_filename ) ) {
					$progress = array(
						'info'     => $file_info,
						'status'   => __( 'forced termination', 'usces' ) . $check_label,
						'progress' => __( 'The process was not completed', 'usces' ),
						'log'      => 'Error : ' . __( 'The file was not stored.', 'usces' ),
						'flag'     => 'complete',
					);
					record_item_up_progress( $progress );
					unlink( $upload_folder . $file_name );
					return;
				}

				$progress = array(
					'info'     => $file_info,
					'status'   => __( 'Processing...', 'usces' ) . $check_label,
					'progress' => __( 'File upload is complete', 'usces' ),
				);
				record_item_up_progress( $progress );

				return $new_filename;
			}

			// Registration.
			if ( isset( $_REQUEST['regfile'] ) && ! WCUtils::is_blank( $_REQUEST['regfile'] ) && isset( $_REQUEST['action'] ) && 'upload_register' === $_REQUEST['action'] ) {

				$csv_encode_type_sjis = ( isset( $usces->options['system']['csv_encode_type'] ) && 1 === (int) $usces->options['system']['csv_encode_type'] ) ? false : true;

				$upload_mode     = isset( $_REQUEST['mode'] ) ? $_REQUEST['mode'] : '';
				$mode_name       = usces_get_upmode_name( $upload_mode );
				$file_name       = $_REQUEST['regfile'];
				$decode_filename = base64_decode( $file_name );

				list( $dfname, $dfext ) = explode( '.', $decode_filename, 2 );

				$lpos = strrpos( $dfname, '_' );
				if ( 0 < $lpos ) {
					$org_filename = substr( $dfname, 0, $lpos ) . '.' . $dfext;
				} else {
					$org_filename = $decode_filename;
				}

				$file_info = array(
					'filename' => $org_filename,
					'mode'     => $mode_name,
					'rowcount' => '',
					'header'   => '',
				);
				$progress  = array(
					'info'     => $file_info,
					'status'   => __( 'Processing...', 'usces' ) . $check_label,
					'progress' => '',
				);
				record_item_up_progress( $progress );

				if ( ! file_exists( $upload_folder . $file_name ) ) {
					$progress = array(
						'info'     => $file_info,
						'status'   => __( 'forced termination', 'usces' ) . $check_label,
						'progress' => __( 'The process was not completed', 'usces' ),
						'log'      => 'Error : ' . __( 'CSV file does not exist.', 'usces' ),
						'flag'     => 'complete',
					);
					record_item_up_progress( $progress );
					die( wp_json_encode( $progress ) );
				}
			} else {

				$progress = array(
					'info'     => $file_info,
					'status'   => __( 'forced termination', 'usces' ) . $check_label,
					'progress' => __( 'The process was not completed', 'usces' ),
					'log'      => 'Error : ' . __( 'Bad request.', 'usces' ),
					'flag'     => 'complete',
				);
				record_item_up_progress( $progress );
				unlink( $upload_folder . $file_name );
				die( wp_json_encode( $progress ) );
			}

			/* read data */
			if ( ! ( $fpo = fopen( $upload_folder . $file_name, 'r' ) ) ) {
				$progress = array(
					'info'     => $file_info,
					'status'   => __( 'forced termination', 'usces' ) . $check_label,
					'progress' => __( 'The process was not completed', 'usces' ),
					'log'      => 'Error : ' . __( 'A file does not open.', 'usces' ),
					'flag'     => 'complete',
				);
				record_item_up_progress( $progress );
				unlink( $upload_folder . $file_name );
				die( wp_json_encode( $progress ) );
			}

			// Correct line breaks in the middle of a line.
			$orglines = array();
			$buf      = '';
			while ( ! feof( $fpo ) ) {
				$temp = fgets( $fpo, 65535 );
				if ( 0 === strlen( $temp ) ) {
					continue;
				}

				$num = substr_count( $temp, '"' );
				if ( 0 === $num % 2 && '' === $buf ) {
					$orglines[] = $temp;
				} elseif ( 1 === $num % 2 && '' === $buf ) {
					$buf .= $temp;
				} elseif ( 0 === $num % 2 && '' !== $buf ) {
					$buf .= $temp;
				} elseif ( 1 === $num % 2 && '' !== $buf ) {
					$buf       .= $temp;
					$orglines[] = $buf;
					$buf        = '';
				}
			}
			fclose( $fpo );

			// Data generation and checking.
			$total_num = 0;
			$lines     = array();
			foreach ( $orglines as $index => $line ) {
				$line = trim( $line );
				if ( empty( $line ) ) {
					continue;
				}
				$lines[] = $line;
			}
			$total_num = count( $lines );
			if ( $csv_encode_type_sjis ) {
				$header = trim( mb_convert_encoding( $lines[0], 'UTF-8', 'SJIS' ) );
			} else {
				$header = trim( $lines[0] );
			}

			$file_info = array(
				'filename' => $org_filename,
				'mode'     => $mode_name,
				'rowcount' => __( 'Number of lines', 'usces' ) . ' ' . $total_num,
				'header'   => $header,
			);

			// Ready.
			$wpdb->query( 'SET SQL_BIG_SELECTS=1' );
			set_time_limit( 3600 );
			$category_format_slug = ( isset( $usces->options['system']['csv_category_format'] ) && 1 === (int) $usces->options['system']['csv_category_format'] ) ? true : false;

			// Processing branch for each mode.
			$results = apply_filters( 'usces_filter_item_uploadcsv_mode', array(), $lines, $file_info );
			if ( ! empty( $results ) ) {

				extract( $results );

			} elseif ( 'stock' === $upload_mode ) {

				$results = usces_item_stock_uploadcsv( $lines, $file_info );
				if ( ! empty( $results ) ) {
					extract( $results );
				}

			} elseif ( 'sku' === $upload_mode ) {

				$results = usces_item_sku_uploadcsv( $lines, $file_info );
				if ( ! empty( $results ) ) {
					extract( $results );
				}

			} elseif ( 'meta' === $upload_mode ) {

				$results = usces_item_meta_uploadcsv( $lines, $file_info );
				if ( ! empty( $results ) ) {
					extract( $results );
				}

			} else {

				// All columns.

				define( 'USCES_COL_POST_ID', 0 );
				define( 'USCES_COL_POST_AUTHOR', 1 );
				define( 'USCES_COL_POST_CONTENT', 2 );
				define( 'USCES_COL_POST_TITLE', 3 );
				define( 'USCES_COL_POST_EXCERPT', 4 );
				define( 'USCES_COL_POST_STATUS', 5 );
				define( 'USCES_COL_POST_COMMENT_STATUS', 6 );
				define( 'USCES_COL_POST_PASSWORD', 7 );
				define( 'USCES_COL_POST_NAME', 8 );
				define( 'USCES_COL_POST_MODIFIED', 9 );

				define( 'USCES_COL_ITEM_CODE', 10 );
				define( 'USCES_COL_ITEM_NAME', 11 );
				define( 'USCES_COL_ITEM_RESTRICTION', 12 );
				define( 'USCES_COL_ITEM_POINTRATE', 13 );
				define( 'USCES_COL_ITEM_GPNUM1', 14 );
				define( 'USCES_COL_ITEM_GPDIS1', 15 );
				define( 'USCES_COL_ITEM_GPNUM2', 16 );
				define( 'USCES_COL_ITEM_GPDIS2', 17 );
				define( 'USCES_COL_ITEM_GPNUM3', 18 );
				define( 'USCES_COL_ITEM_GPDIS3', 19 );
				define( 'USCES_COL_ITEM_ORDER_ACCEPTABLE', 20 );

				define( 'USCES_COL_ITEM_DIVISION', 21 );
				define( 'USCES_COL_ITEM_CHARGING_TYPE', 22 );

				define( 'USCES_COL_ITEM_SHIPPING', 23 );
				define( 'USCES_COL_ITEM_DELIVERYMETHOD', 24 );
				define( 'USCES_COL_ITEM_SHIPPINGCHARGE', 25 );
				define( 'USCES_COL_ITEM_INDIVIDUALSCHARGE', 26 );

				define( 'USCES_COL_ITEM_FREQUENCY', 27 );
				define( 'USCES_COL_ITEM_CHARGINGDAY', 28 );
				define( 'USCES_COL_DLSELLER_INTERVAL', 29 );
				define( 'USCES_COL_DLSELLER_VALIDITY', 30 );
				define( 'USCES_COL_DLSELLER_FILE', 31 );
				define( 'USCES_COL_DLSELLER_DATE', 32 );
				define( 'USCES_COL_DLSELLER_VERSION', 33 );
				define( 'USCES_COL_DLSELLER_AUTHOR', 34 );
				define( 'USCES_COL_DLSELLER_PURCHASES', 35 );
				define( 'USCES_COL_DLSELLER_DOWNLOADS', 36 );

				define( 'USCES_COL_CATEGORY', 37 );
				define( 'USCES_COL_POST_TAG', 38 );
				define( 'USCES_COL_CUSTOM_FIELD', 39 );

				$add_field_num = apply_filters( 'dlseller_filter_uploadcsv_item_field_num', 0 );
				$add_field_num = apply_filters( 'dlseller_filter_uploadcsv_add_item_field_num', $add_field_num );

				define( 'USCES_COL_SKU_CODE', 40 + $add_field_num );
				define( 'USCES_COL_SKU_NAME', 41 + $add_field_num );
				define( 'USCES_COL_SKU_CPRICE', 42 + $add_field_num );
				define( 'USCES_COL_SKU_PRICE', 43 + $add_field_num );
				define( 'USCES_COL_SKU_ZAIKONUM', 44 + $add_field_num );
				define( 'USCES_COL_SKU_ZAIKO', 45 + $add_field_num );
				define( 'USCES_COL_SKU_UNIT', 46 + $add_field_num );
				define( 'USCES_COL_SKU_GPTEKIYO', 47 + $add_field_num );
				define( 'USCES_COL_SKU_APPLICABLE_TAXRATE', 48 + $add_field_num );

				$normal_field_num = 49;

				$column_num    = 0;
				$comp_num      = isset( $_REQUEST['comp_num'] ) ? (int) $_REQUEST['comp_num'] : 0;
				$err_num       = isset( $_REQUEST['err_num'] ) ? (int) $_REQUEST['err_num'] : 0;
				$line_num      = 0;
				$min_field_num = apply_filters( 'dlseller_filter_uploadcsv_min_field_num', $normal_field_num + $add_field_num );
				$min_field_num = apply_filters( 'dlseller_filter_uploadcsv_add_min_field_num', $min_field_num );
				$error         = false;
				$pre_code      = '';
				$start_number  = isset( $_REQUEST['work_number'] ) ? (int) $_REQUEST['work_number'] : 0;
				$work_number   = 0;
				$sku_index     = 0;
				$date_pattern  = '/(\d{4})-(\d{2}|\d)-(\d{2}|\d) (\d{2}):(\d{2}|\d):(\d{2}|\d)/';
				$item_table    = usces_get_tablename( 'usces_item' );

				$yn    = "\n";
				$cf_sp = ';;'; // Custom field separator.

				// Registration loop.
				foreach ( $lines as $rows_num => $line ) {

					$logtemp = '';
					$line    = trim( $line );
					if ( empty( $line ) ) {
						continue;
					}

					// Divide the line and store it in $datas.
					$datas = usces_make_line_data( $line );

					if ( $column_num < count( $datas ) ) {
						$column_num = count( $datas );
					}
					$file_info = array(
						'filename' => $org_filename,
						'mode'     => $mode_name,
						'rowcount' => __( 'Number of lines', 'usces' ) . ' ' . $total_num . ' ' . __( 'Number of items', 'usces' ) . ' ' . $column_num,
						'header'   => $header,
					);

					if ( $min_field_num > $column_num || ( 0 === $rows_num && 'Post ID' !== $datas[ USCES_COL_POST_ID ] ) ) {
						$progress = array(
							'info'     => $file_info,
							'status'   => __( 'forced termination', 'usces' ) . $check_label,
							'progress' => __( 'The process was not completed', 'usces' ),
							'log'      => 'Error : ' . __( 'This file may not be the item CSV for "All columns".', 'usces' ),
							'flag'     => 'complete',
						);
						record_item_up_progress( $progress );
						unlink( $upload_folder . $file_name );
						die( wp_json_encode( $progress ) );
					}

					// Skip the first line.
					if ( 'Post ID' === $datas[ USCES_COL_POST_ID ] ) {
						continue;
					}

					$line_num  = $rows_num + 1;
					$item_code = ( $csv_encode_type_sjis ) ? trim( mb_convert_encoding( $datas[ USCES_COL_ITEM_CODE ], 'UTF-8', 'SJIS' ) ) : trim( $datas[ USCES_COL_ITEM_CODE ] );

					// Split processing.
					if ( $start_number > $work_number ) {
						if ( $pre_code !== $item_code ) {
							$work_number++;
						}
						$pre_code = $item_code;

						continue;
					}
					if ( $pre_code !== $item_code ) {
						if ( 0 === ( $work_number % USCES_ITEM_UP_INTERBAL ) && $start_number != $work_number ) {
							$progress = array(
								'info'        => $file_info,
								'status'      => __( 'Processing...', 'usces' ) . $check_label,
								'progress'    => sprintf( __( 'Successful %1$s lines, Failed %2$s lines.', 'usces' ), $comp_num, $err_num ),
								'i'           => $line_num,
								'all'         => $total_num,
								'flag'        => 'continue',
								'work_number' => $work_number,
								'comp_num'    => $comp_num,
								'err_num'     => $err_num,
							);
							record_item_up_progress( $progress );
							die( wp_json_encode( $progress ) );
						}

						$work_number++;
					}

					// Update mode determined.
					if ( $pre_code === $item_code && WCUtils::is_blank( $datas[ USCES_COL_POST_ID ] ) ) {
						$mode = 'add';

					} else {
						$post_id = ( ! WCUtils::is_blank( $datas[ USCES_COL_POST_ID ] ) ) ? (int) $datas[ USCES_COL_POST_ID ] : null;
						if ( $post_id ) {
							$db_res = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE ID = %d AND post_mime_type = %s", $post_id, 'item' ) );
							if ( ! $db_res ) {
								$err_num++;
								$mes      = 'No.' . $line_num . "\t" . sprintf( __( 'Post-ID %s is not product data.', 'usces' ), $post_id );
								$progress = array(
									'log' => $mes,
								);
								record_item_up_progress( $progress );
								$error = true;
								continue;
							}
						}
						if ( $post_id ) {
							$mode = 'upd';
						} else {
							$mode = 'add';
						}
					}

					// Column check loop.
					foreach ( $datas as $key => $data ) {

						$data = ( $csv_encode_type_sjis ) ? trim( mb_convert_encoding( $data, 'UTF-8', 'SJIS' ) ) : trim( $data );

						switch ( $key ) {
							case USCES_COL_ITEM_CODE:
								if ( 0 === strlen( $data ) ) {
									$mes      = 'No.' . $line_num . "\t" . __( 'An item cord is non-input.', 'usces' );
									$logtemp .= $mes . $yn;
								} else {
									$db_res1 = $wpdb->get_results(
										$wpdb->prepare(
											"SELECT `itemCode`, `post_id` FROM {$item_table} 
											LEFT JOIN {$wpdb->posts} ON `ID` = `post_id`
											WHERE `itemCode` = %s AND `post_type` = 'post' 
											AND `post_status` IN ('pending', 'publish', 'draft', 'private', 'future')",
											$data
										),
										ARRAY_A
									);

									if ( 'upd' === $mode ) {

										if ( $db_res1 && is_array( $db_res1 ) && 1 < count( $db_res1 ) ) {
											$mes      = 'No.' . $line_num . "\t" . __( 'This Item-Code has been duplicated.', 'usces' );
											$logtemp .= $mes . $yn;
											$mes      = '';
											foreach ( $db_res1 as $res_val ) {
												$mes .= 'itemCode=' . $res_val['itemCode'] . ', post_id=' . $res_val['post_id'];
											}
											$logtemp .= $mes . $yn;
										}
										$query = $wpdb->prepare(
											"SELECT `itemCode`, `post_id` FROM {$item_table} 
											LEFT JOIN {$wpdb->posts} ON `ID` = `post_id`
											WHERE `post_id` <> %d AND `itemCode` = %s AND `post_type` = 'post' 
											AND `post_status` IN ('pending', 'publish', 'draft', 'private', 'future')",
											$post_id,
											$data
										);
										$db_res2 = $wpdb->get_results( $query, ARRAY_A );
										if ( $db_res2 && is_array( $db_res2 ) && 0 < count( $db_res2 ) ) {
											$mes      = 'No.' . $line_num . "\t" . __( 'This Item-Code has already been used.', 'usces' );
											$logtemp .= $mes . $yn;
											$mes      = '';
											foreach ( $db_res2 as $res_val ) {
												$mes .= 'itemCode=' . $res_val['itemCode'] . ', post_id=' . $res_val['post_id'];
											}
											$logtemp .= $mes . $yn;
										}

									} elseif ( 'add' === $mode ) {

										if ( $data !== $pre_code ) {
											if ( $db_res1 && is_array( $db_res1 ) && 0 < count( $db_res1 ) ) {
												$mes      = 'No.' . $line_num . "\t" . __( 'This Item-Code has already been used.', 'usces' );
												$logtemp .= $mes . $yn;
												$mes      = '';
												foreach ( $db_res1 as $res_val ) {
													$mes .= 'itemCode=' . $res_val['itemCode'] . ', post_id=' . $res_val['post_id'];
												}
												$logtemp .= $mes . $yn;
											}
										}
									}
								}
								break;
							case USCES_COL_ITEM_NAME:
								if ( 0 === strlen( $data ) ) {
									$mes      = 'No.' . $line_num . "\t" . __( 'An item name is non-input.', 'usces' );
									$logtemp .= $mes . $yn;
								}
								break;
							case USCES_COL_ITEM_RESTRICTION:
								if ( ! preg_match( '/^[0-9;]+$/', $data ) && 0 !== strlen( $data ) ) {
									$mes      = 'No.' . $line_num . "\t" . __( 'A value of the purchase limit number is abnormal.', 'usces' );
									$logtemp .= $mes . $yn;
								}
								break;
							case USCES_COL_ITEM_POINTRATE:
								if ( ! preg_match( '/^[0-9;]+$/', $data ) ) {
									$mes      = 'No.' . $line_num . "\t" . __( 'A value of the point rate is abnormal.', 'usces' );
									$logtemp .= $mes . $yn;
								}
								break;
							case USCES_COL_ITEM_GPNUM1:
								if ( ! preg_match( '/^[0-9;]+$/', $data ) ) {
									$mes      = 'No.' . $line_num . "\t" . __( 'Business package discount', 'usces' ) . '1-' . __( 'umerical value is abnormality.', 'usces' );
									$logtemp .= $mes . $yn;
								}
								break;
							case USCES_COL_ITEM_GPDIS1:
								if ( ! preg_match( '/^[0-9;]+$/', $data ) || ( 0 < $datas[ USCES_COL_ITEM_GPNUM1 ] && 1 > $data ) ) {
									$mes      = 'No.' . $line_num . "\t" . __( 'Business package discount', 'usces' ) . '1-' . __( 'rate is abnormal.', 'usces' );
									$logtemp .= $mes . $yn;
								}
								break;
							case USCES_COL_ITEM_GPNUM2:
								if ( ! preg_match( '/^[0-9;]+$/', $data ) || ( $datas[ USCES_COL_ITEM_GPNUM1 ] >= $data && 0 !== (int) $data ) ) {
									$mes      = 'No.' . $line_num . "\t" . __( 'Business package discount', 'usces' ) . '2-' . __( 'umerical value is abnormality.', 'usces' );
									$logtemp .= $mes . $yn;
								}
								break;
							case USCES_COL_ITEM_GPDIS2:
								if ( ! preg_match( '/^[0-9;]+$/', $data ) || ( 0 < $datas[ USCES_COL_ITEM_GPNUM2 ] && 1 > $data ) ) {
									$mes      = 'No.' . $line_num . "\t" . __( 'Business package discount', 'usces' ) . '2-' . __( 'rate is abnormal.', 'usces' );
									$logtemp .= $mes . $yn;
								}
								break;
							case USCES_COL_ITEM_GPNUM3:
								if ( ! preg_match( '/^[0-9;]+$/', $data ) || ( $datas[ USCES_COL_ITEM_GPNUM2 ] >= $data && 0 !== (int) $data ) ) {
									$mes      = 'No.' . $line_num . "\t" . __( 'Business package discount', 'usces' ) . '3-' . __( 'umerical value is abnormality.', 'usces' );
									$logtemp .= $mes . $yn;
								}
								break;
							case USCES_COL_ITEM_GPDIS3:
								if ( ! preg_match( '/^[0-9;]+$/', $data ) || ( 0 < $datas[ USCES_COL_ITEM_GPNUM3 ] && 1 > $data ) ) {
									$mes      = 'No.' . $line_num . "\t" . __( 'Business package discount', 'usces' ) . '3-' . __( 'rate is abnormal.', 'usces' );
									$logtemp .= $mes . $yn;
								}
								break;
							case USCES_COL_ITEM_ORDER_ACCEPTABLE:
								if ( ! preg_match( '/^[0-9]+$/', $data ) ) {
									$mes      = 'No.' . $line_num . "\t" . __( 'A value of the Sold out limit is abnormal.', 'usces' );
									$logtemp .= $mes . $yn;
								}
								break;

							case USCES_COL_ITEM_DIVISION:
								$array_division = array( 'shipped', 'data', 'service' );
								if ( ! in_array( $data, $array_division, true ) || '' === $data ) {
									$mes      = 'No.' . $line_num . "\t" . __( 'A value of the division is abnormal.', 'dlseller' );
									$logtemp .= $mes . $yn;
								}
								break;

							case USCES_COL_ITEM_CHARGING_TYPE:
								if ( 0 > (int) $data || 2 < (int) $data ) {
									$mes      = 'No.' . $line_num . "\t" . __( 'A value of the charging type is abnormal.', 'autodelivery' );
									$logtemp .= $mes . $yn;
								}
								break;

							case USCES_COL_ITEM_SHIPPING:
								if ( ! preg_match( '/^[0-9]+$/', $data ) || 9 < $data ) {
									$mes      = 'No.' . $line_num . "\t" . __( 'A value of the shipment day is abnormal.', 'usces' );
									$logtemp .= $mes . $yn;
								}
								break;
							case USCES_COL_ITEM_DELIVERYMETHOD:
								if ( 'shipped' === $datas[ USCES_COL_ITEM_DIVISION ] ) { // Only for shipping items.
									if ( 0 === strlen( $data ) || ! preg_match( '/^[0-9;]+$/', $data ) ) {
										$mes      = 'No.' . $line_num . "\t" . __( 'Invalid value of Delivery method.', 'usces' );
										$logtemp .= $mes . $yn;
									}
								}
								break;
							case USCES_COL_ITEM_SHIPPINGCHARGE:
								if ( 0 === strlen( $data ) || ! preg_match( '/^[0-9;]+$/', $data ) ) {
									$mes      = 'No.' . $line_num . "\t" . __( 'Invalid type of shipping charge.', 'usces' );
									$logtemp .= $mes . $yn;
								}
								break;
							case USCES_COL_ITEM_INDIVIDUALSCHARGE:
								if ( ! preg_match( '/^[0-9;]+$/', $data ) || 1 < $data ) {
									$mes      = 'No.' . $line_num . "\t" . __( 'A value of the postage individual charging is abnormal.', 'usces' );
									$logtemp .= $mes . $yn;
								}
								break;

							case USCES_COL_ITEM_FREQUENCY:
								if ( 1 === (int) $datas[ USCES_COL_ITEM_CHARGING_TYPE ] ) { // Only for continuous billing.
									if ( 1 !== (int) $data && 6 !== (int) $data && 12 !== (int) $data ) {
										$mes      = 'No.' . $line_num . "\t" . __( 'The value of the Charging Interval is abnormal.', 'dlseller' );
										$logtemp .= $mes . $yn;
									}
								}
								break;
							case USCES_COL_ITEM_CHARGINGDAY:
								if ( 1 === (int) $datas[ USCES_COL_ITEM_CHARGING_TYPE ] ) { // Only for continuous billing.
									if ( ( $data < 1 || $data > 28 ) && 99 !== (int) $data ) {
										$mes      = 'No.' . $line_num . "\t" . __( 'The value of the Charging Date is abnormal.', 'dlseller' );
										$logtemp .= $mes . $yn;
									}
								}
								break;
							case USCES_COL_DLSELLER_INTERVAL:
								if ( 0 < strlen( $data ) && ! preg_match( '/^[0-9]+$/', $data ) ) {
									$mes      = 'No.' . $line_num . "\t" . __( 'A value of the period is abnormal.', 'dlseller' );
									$logtemp .= $mes . $yn;
								}
								break;
							case USCES_COL_DLSELLER_VALIDITY:
								if ( 0 < strlen( $data ) && ! preg_match( '/^[0-9]+$/', $data ) ) {
									$mes      = 'No.' . $line_num . "\t" . __( 'A value of the validity is abnormal.', 'dlseller' );
									$logtemp .= $mes . $yn;
								}
								break;
							case USCES_COL_DLSELLER_FILE:
								if ( 'data' === $datas[ USCES_COL_ITEM_DIVISION ] ) {
									if ( 0 === strlen( $data ) ) {
										$mes      = 'No.' . $line_num . "\t" . __( 'A file name is non-input.', 'dlseller' );
										$logtemp .= $mes . $yn;
									}
								}
								break;
							case USCES_COL_DLSELLER_DATE:
							case USCES_COL_DLSELLER_VERSION:
							case USCES_COL_DLSELLER_AUTHOR:
							case USCES_COL_DLSELLER_PURCHASES:
							case USCES_COL_DLSELLER_DOWNLOADS:
								break;
							case USCES_COL_POST_ID:
								if ( ! preg_match( '/^[0-9;]+$/', $data ) && 0 !== strlen( $data ) ) {
									$mes      = 'No.' . $line_num . "\t" . __( 'A value of the Post-ID is abnormal.', 'usces' );
									$logtemp .= $mes . $yn;
								}
								break;
							case USCES_COL_POST_AUTHOR:
							case USCES_COL_POST_COMMENT_STATUS:
							case USCES_COL_POST_PASSWORD:
							case USCES_COL_POST_NAME:
							case USCES_COL_POST_TITLE:
							case USCES_COL_POST_CONTENT:
							case USCES_COL_POST_EXCERPT:
								break;
							case USCES_COL_POST_STATUS:
								$array17 = array( 'publish', 'future', 'draft', 'pending', 'private' );
								if ( ! in_array( $data, $array17, true ) || WCUtils::is_blank( $data ) ) {
									$mes      = 'No.' . $line_num . "\t" . __( 'A value of the display status is abnormal.', 'usces' );
									$logtemp .= $mes . $yn;
								}
								break;
							case USCES_COL_POST_MODIFIED:
								if ( 'future' === $datas[ USCES_COL_POST_STATUS ] && ( WCUtils::is_blank( $data ) || '0000-00-00 00:00:00' === $data ) ) {
									if ( preg_match( $date_pattern, $data, $match ) ) {
										if ( checkdate( $match[2], $match[3], $match[1] )
											&& ( 0 < $match[4] && 24 > $match[4] )
											&& ( 0 < $match[5] && 60 > $match[5] )
											&& ( 0 < $match[6] && 60 > $match[6] )
										) {
											$mes = '';
										} else {
											$mes      = 'No.' . $line_num . "\t" . __( 'A value of the schedule is abnormal.', 'usces' );
											$logtemp .= $mes . $yn;
										}
									} else {
										$mes      = 'No.' . $line_num . "\t" . __( 'A value of the schedule is abnormal.', 'usces' );
										$logtemp .= $mes . $yn;
									}
								} elseif ( ! WCUtils::is_blank( $data ) && '0000-00-00 00:00:00' !== $data ) {
									if ( preg_match( '/^[0-9;]+$/', substr( $data, 0, 4 ) ) ) { // First 4 digits are numbers only.
										if ( strtotime( $data ) === false ) {
											$mes      = 'No.' . $line_num . "\t" . __( 'A value of the schedule is abnormal.', 'usces' );
											$logtemp .= $mes . $yn;
										}
									} else {
										$datetime = explode( ' ', $data );
										$date_str = usces_dates_interconv( $datetime[0] ) . ' ' . $datetime[1];
										if ( strtotime( $date_str ) === false ) {
											$mes      = 'No.' . $line_num . "\t" . __( 'A value of the schedule is abnormal.', 'usces' );
											$logtemp .= $mes . $yn;
										}
									}
								}
								break;
							case USCES_COL_CATEGORY:
								if ( 0 === strlen( $data ) ) {
									$mes      = 'No.' . $line_num . "\t" . __( 'A category is non-input.', 'usces' );
									$logtemp .= $mes . $yn;
								}
								break;
							case USCES_COL_POST_TAG:
								break;
							case USCES_COL_CUSTOM_FIELD:
								break;
							case USCES_COL_SKU_CODE:
								if ( 0 === strlen( $data ) ) {
									$mes      = 'No.' . $line_num . "\t" . __( 'A SKU cord is non-input.', 'usces' );
									$logtemp .= $mes . $yn;
								}
								break;
							case USCES_COL_SKU_NAME:
								break;
							case USCES_COL_SKU_CPRICE:
								if ( 0 < strlen( $data ) && ! preg_match( '/^\d$|^\d+\.?\d+$/', $data ) ) {
									$mes      = 'No.' . $line_num . "\t" . __( 'A value of the normal price is abnormal.', 'usces' );
									$logtemp .= $mes . $yn;
								}
								break;
							case USCES_COL_SKU_PRICE:
								if ( ! preg_match( '/^\d$|^\d+\.?\d+$/', $data ) || 0 === strlen( $data ) ) {
									$mes      = 'No.' . $line_num . "\t" . __( 'A value of the sale price is abnormal.', 'usces' );
									$logtemp .= $mes . $yn;
								}
								break;
							case USCES_COL_SKU_ZAIKONUM:
								if ( 0 < strlen( $data ) ) {
									$item_order_acceptable = (int) $datas[ USCES_COL_ITEM_ORDER_ACCEPTABLE ];
									if ( 1 !== $item_order_acceptable ) {
										if ( ! preg_match( '/^[0-9;]+$/', $data ) ) {
											$mes      = 'No.' . $line_num . "\t" . __( 'A value of the stock amount is abnormal.', 'usces' );
											$logtemp .= $mes . $yn;
										}
									} else {
										if ( ! preg_match( '/^[-]?[0-9]+$/', $data ) ) {
											$mes      = 'No.' . $line_num . "\t" . __( 'A value of the stock amount is abnormal.', 'usces' );
											$logtemp .= $mes . $yn;
										}
									}
								}
								break;
							case USCES_COL_SKU_ZAIKO:
								$stock_status = apply_filters( 'usces_filter_csv_upload_check_stock_status', $data );
								if ( ! preg_match( '/^[0-9;]+$/', $data ) || $stock_status < $data ) {
									$mes      = 'No.' . $line_num . "\t" . __( 'A value of the stock status is abnormal.', 'usces' );
									$logtemp .= $mes . $yn;
								}
								break;
							case USCES_COL_SKU_UNIT:
								break;
							case USCES_COL_SKU_GPTEKIYO:
								if ( ! preg_match( '/^[0-9;]+$/', $data ) || 1 < $data ) {
									$mes      = 'No.' . $line_num . "\t" . __( 'The value of the duties pack application is abnormal.', 'usces' );
									$logtemp .= $mes . $yn;
								}
								break;
						}
					}

					// Option column check loop.
					$opnum = ceil( ( count( $datas ) - $min_field_num ) / 4 );
					for ( $i = 0; $i < $opnum; $i++ ) {

						$val       = array();
						$oplogtemp = '';

						for ( $o = 1; $o <= 4; $o++ ) {

							$key = ( $min_field_num - 1 ) + $o + ( $i * 4 );
							if ( isset( $datas[ $key ] ) ) {
								$value = trim( $datas[ $key ] );
							} else {
								$value = null;
							}

							switch ( $o ) {
								case 1:
									if ( empty( $value ) ) {
										$oplogtemp .= 'No.' . $line_num . "\t" . sprintf( __( 'Option name of No.%s option is non-input.', 'usces' ), ( $i + 1 ) ) . $yn;
									}
									$val['name'] = $value;
									break;
								case 2:
									if ( null !== $value && ( ( 0 > (int) $value ) || ( 5 < (int) $value ) ) ) {
										$oplogtemp .= 'No.' . $line_num . "\t" . sprintf( __( 'Option-entry-field of No.%s option is abnormal.', 'usces' ), ( $i + 1 ) ) . $yn;
									}
									$val['mean'] = $value;
									break;
								case 3:
									if ( null !== $value && ( ! preg_match( '/^[0-9;]+$/', $value ) || 1 < (int) $value ) ) {
										$oplogtemp .= 'No.' . $line_num . "\t" . sprintf( __( 'Option-required-item of No.%s option is abnormal.', 'usces' ), ( $i + 1 ) ) . $yn;
									}
									$val['essential'] = $value;
									break;
								case 4:
									if ( ( null !== $value && '' === $value ) && ( 2 > $datas[ ( $key - 2 ) ] && 0 < strlen( $datas[ ( $key - 2 ) ] ) ) ) {
										$oplogtemp .= 'No.' . $line_num . "\t" . sprintf( __( 'Option-select of No.%s option is non-input.', 'usces' ), ( $i + 1 ) ) . $yn;
									}
									$val['value'] = $value;
									break;
							}
						}
						if ( ! WCUtils::is_blank( $val['name'] ) || ! WCUtils::is_blank( $val['mean'] ) || ! WCUtils::is_blank( $val['essential'] ) || ! WCUtils::is_blank( $val['value'] ) ) {
							$logtemp .= $oplogtemp;
						}
					}

					// End of data check.
					if ( 0 < strlen( $logtemp ) ) {
						$err_num++;
						$progress = array(
							'log' => $logtemp,
						);
						record_item_up_progress( $progress );
						$error = true;

						continue;
					}

					if ( ! $check_mode ) {

						/**
						 * Insert Post.
						 */

						// wp_posts data reg.
						$cdatas      = array();
						$post_fields = array();
						$sku         = array();
						$opt         = array();
						$valstr      = '';

						if ( $pre_code !== $item_code ) {
							$sku_index        = 0;
							$current_date     = current_time( 'mysql' );
							$current_date_gmt = current_time( 'mysql', 1 );
							$cdatas['ID']     = $post_id;

							$post_modified = $datas[ USCES_COL_POST_MODIFIED ];
							if ( '' === $post_modified || '0000-00-00 00:00:00' === $post_modified ) {
								if ( 'add' === $mode ) {
									$cdatas['post_date']     = $current_date;
									$cdatas['post_date_gmt'] = $current_date_gmt;
								}
								$cdatas['post_modified']     = $current_date;
								$cdatas['post_modified_gmt'] = $current_date_gmt;
							} else {
								if ( preg_match( '/^[0-9;]+$/', substr( $post_modified, 0, 4 ) ) ) { // First 4 digits are numbers only.
									$time_data = strtotime( $post_modified );
								} else {
									$datetime  = explode( ' ', $post_modified );
									$date_str  = usces_dates_interconv( $datetime[0] ) . ' ' . $datetime[1];
									$time_data = strtotime( $date_str );
								}
								$difference                  = get_option( 'gmt_offset' ) * 60 * 60;
								$cdatas['post_date']         = date_i18n( 'Y-m-d H:i:s', $time_data );
								$cdatas['post_date_gmt']     = gmdate( 'Y-m-d H:i:s', ( $time_data - $difference ) );
								$cdatas['post_modified']     = date_i18n( 'Y-m-d H:i:s', $time_data );
								$cdatas['post_modified_gmt'] = gmdate( 'Y-m-d H:i:s', ( $time_data - $difference ) );
							}
							if ( 'publish' === $datas[ USCES_COL_POST_STATUS ] ) {
								if ( mysql2date( 'U', $cdatas['post_modified'], false ) > mysql2date( 'U', $current_date, false ) ) {
									$datas[ USCES_COL_POST_STATUS ] = 'future';
								}
							} elseif ( 'future' === $datas[ USCES_COL_POST_STATUS ] ) {
								if ( mysql2date( 'U', $cdatas['post_modified'], false ) <= mysql2date( 'U', $current_date, false ) ) {
									$datas[ USCES_COL_POST_STATUS ] = 'publish';
								}
							}
							$cdatas['ID']             = $post_id;
							$cdatas['post_author']    = ( ! WCUtils::is_blank( $datas[ USCES_COL_POST_AUTHOR ] ) ) ? $datas[ USCES_COL_POST_AUTHOR ] : $user_ID;
							$cdatas['post_content']   = ( $csv_encode_type_sjis ) ? trim( mb_convert_encoding( $datas[ USCES_COL_POST_CONTENT ], 'UTF-8', 'SJIS' ) ) : trim( $datas[ USCES_COL_POST_CONTENT ] );
							$cdatas['post_title']     = ( $csv_encode_type_sjis ) ? trim( mb_convert_encoding( $datas[ USCES_COL_POST_TITLE ], 'UTF-8', 'SJIS' ) ) : trim( $datas[ USCES_COL_POST_TITLE ] );
							$cdatas['post_excerpt']   = ( $csv_encode_type_sjis ) ? trim( mb_convert_encoding( $datas[ USCES_COL_POST_EXCERPT ], 'UTF-8', 'SJIS' ) ) : trim( $datas[ USCES_COL_POST_EXCERPT ] );
							$cdatas['post_status']    = $datas[ USCES_COL_POST_STATUS ];
							$cdatas['comment_status'] = ( ! WCUtils::is_blank( $datas[ USCES_COL_POST_COMMENT_STATUS ] ) ) ? $datas[ USCES_COL_POST_COMMENT_STATUS ] : 'close';
							$cdatas['ping_status']    = 'close';
							$cdatas['post_password']  = ( 'private' === $cdatas['post_status'] ) ? '' : $datas[ USCES_COL_POST_PASSWORD ];
							$cdatas['post_type']      = 'post';
							$cdatas['post_parent']    = 0;

							$spname = ( $csv_encode_type_sjis ) ? sanitize_title( trim( mb_convert_encoding( $datas[ USCES_COL_POST_NAME ], 'UTF-8', 'SJIS' ) ) ) : sanitize_title( trim( $datas[ USCES_COL_POST_NAME ] ) );

							$cdatas['post_name']             = wp_unique_post_slug( $spname, $cdatas['ID'], $cdatas['post_status'], $cdatas['post_type'], $cdatas['post_parent'] );
							$cdatas['to_ping']               = '';
							$cdatas['pinged']                = '';
							$cdatas['menu_order']            = 0;
							$cdatas['post_mime_type']        = 'item';
							$cdatas['post_content_filtered'] = '';

							if ( empty( $cdatas['post_name'] ) && ! in_array( $cdatas['post_status'], array( 'draft', 'pending', 'auto-draft' ), true ) ) {
								$cdatas['post_name'] = sanitize_title( $cdatas['post_title'], $post_id );
							}

							$cfdata = array();
							$cfrows = ( $csv_encode_type_sjis ) ? explode( $cf_sp, trim( mb_convert_encoding( $datas[ USCES_COL_CUSTOM_FIELD ], 'UTF-8', 'SJIS' ) ) ) : explode( $cf_sp, trim( $datas[ USCES_COL_CUSTOM_FIELD ] ) );
							if ( is_array( $cfrows ) && 0 < count( $cfrows ) ) {
								reset( $cfrows );

								foreach ( $cfrows as $cfindex => $row ) {
									if ( false !== strpos( $row, '=' ) ) {
										$cfdata[] = $row;
									} else {
										$cfdend = count( $cfdata ) - 1;
										if ( $cfdend && 0 <= $cfdend ) {
											$cfdata[ $cfdend ] = $cfdata[ $cfdend ] . ';' . $row;
										}
									}
								}
							}

							$cdatas = apply_filters( 'dlseller_filter_pre_registered_data', $cdatas, $datas );

							if ( 'add' === $mode ) {
								/* Register */

								$cdatas['guid'] = '';
								if ( false === $wpdb->insert( $wpdb->posts, $cdatas ) ) {
									$err_num++;
									$pre_code = $item_code;

									$mes      = 'No.' . $line_num . "\t" . __( 'This data was not registered in the database.', 'usces' );
									$progress = array(
										'log' => $mes,
									);
									record_item_up_progress( $progress );
									$error = true;
									continue;
								}
								$post_id = $wpdb->insert_id;
								$where   = array( 'ID' => $post_id );
								$wpdb->update( $wpdb->posts, array( 'guid' => get_permalink( $post_id ) ), $where );

							} elseif ( 'upd' === $mode ) {
								/* Update */

								$where = array( 'ID' => $post_id );
								if ( false === $wpdb->update( $wpdb->posts, $cdatas, $where ) ) {
									$err_num++;
									$pre_code = $item_code;

									$mes      = 'No.' . $line_num . "\t" . __( 'The data were not registered with a database.', 'usces' );
									$progress = array(
										'log' => $mes,
									);
									record_item_up_progress( $progress );
									$error = true;
									continue;
								}
							}
							// End of wp_insert_post.

							// Delete metas of Item only.
							$meta_key_table = array(
								'_itemCode',
								'_itemName',
								'_itemRestriction',
								'_itemPointrate',
								'_itemGpNum1',
								'_itemGpDis1',
								'_itemGpNum2',
								'_itemGpDis2',
								'_itemGpNum3',
								'_itemGpDis3',
								'_itemShipping',
								'_itemDeliveryMethod',
								'_itemShippingCharge',
								'_itemIndividualSCharge',
								'_iopt_',
								'_isku_',
								'_itemPicts',
								'_itemOrderAcceptable',
								'_item_division',
								'_item_charging_type',
								'_item_frequency',
								'_item_chargingday',
								'_dlseller_interval',
								'_dlseller_validity',
								'_dlseller_file',
								'_dlseller_date',
								'_dlseller_version',
								'_dlseller_author',
							);

							if ( is_array( $cfrows ) && 0 < count( $cfrows ) ) {
								reset( $cfrows );

								foreach ( $cfdata as $row ) {
									$cf = explode( '=', $row );
									if ( ! WCUtils::is_blank( $cf[0] ) ) {
										array_push( $meta_key_table, trim( $cf[0] ) );
									}
								}
							}
							$meta_key_table = apply_filters( 'dlseller_filter_uploadcsv_delete_postmeta', $meta_key_table );
							$query          = $wpdb->prepare( "DELETE FROM {$wpdb->postmeta} WHERE meta_key IN ( %s ) AND post_id = %d", implode( "','", $meta_key_table ), $post_id );
							$query          = stripslashes( $query );
							$db_res         = $wpdb->query( $query );
							if ( false === $db_res ) {
								$err_num++;
								$pre_code = $item_code;

								$mes      = 'No.' . $line_num . "\t" . __( 'Error : delete postmeta', 'usces' );
								$progress = array(
									'log' => $mes,
								);
								record_item_up_progress( $progress );
								$error = true;
								continue;
							}

							// Delete Item wcct.
							$query  = $wpdb->prepare( "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE %s AND post_id = %d", 'wccs_%', $post_id );
							$db_res = $wpdb->query( $query );
							if ( false === $db_res ) {
								$err_num++;
								$pre_code = $item_code;

								$mes      = 'No.' . $line_num . "\t" . __( 'Error : delete wcct', 'usces' );
								$progress = array(
									'log' => $mes,
								);
								record_item_up_progress( $progress );
								$error = true;
								continue;
							}

							// Delete Item revisions.
							$query  = $wpdb->prepare( "DELETE FROM {$wpdb->posts} WHERE post_parent = %d AND post_type = %s", $post_id, 'revision' );
							$db_res = $wpdb->query( $query );
							if ( false === $db_res ) {
								$err_num++;
								$pre_code = $item_code;

								$mes      = 'No.' . $line_num . "\t" . __( 'Error : delete revisions', 'usces' );
								$progress = array(
									'log' => $mes,
								);
								record_item_up_progress( $progress );
								$error = true;
								continue;
							}

							// publish_future_post.
							if ( 'future' === $datas[ USCES_COL_POST_STATUS ] && $cdatas['post_date'] > current_time( 'Y-m-d H:i:s' ) ) {
								wp_clear_scheduled_hook( 'publish_future_post', array( $post_id ) );
								wp_schedule_single_event( strtotime( get_gmt_from_date( $cdatas['post_date'] ) . ' GMT' ), 'publish_future_post', array( $post_id ) );
							}

							// add Meta.
							// Add postmeta.
							if ( 'add' === $mode ) {
								$WelItem         = new Welcart\ItemData( $post_id, false );
								$item            = $WelItem->get_item_format();
								$item['post_id'] = $post_id;
							} elseif ( 'upd' === $mode ) {
								$item = Wel_get_item( $post_id, false );
							}

							$item_delivery_method = explode( ';', $datas[ USCES_COL_ITEM_DELIVERYMETHOD ] );

							$item['itemCode']              = $csv_encode_type_sjis ? trim( mb_convert_encoding( $datas[ USCES_COL_ITEM_CODE ], 'UTF-8', 'SJIS' ) ) : trim( $datas[ USCES_COL_ITEM_CODE ] );
							$item['itemName']              = $csv_encode_type_sjis ? trim( mb_convert_encoding( $datas[ USCES_COL_ITEM_NAME ], 'UTF-8', 'SJIS' ) ) : trim( $datas[ USCES_COL_ITEM_NAME ] );
							$item['itemRestriction']       = $csv_encode_type_sjis ? trim( mb_convert_encoding( $datas[ USCES_COL_ITEM_RESTRICTION ], 'UTF-8', 'SJIS' ) ) : trim( $datas[ USCES_COL_ITEM_RESTRICTION ] );
							$item['itemPointrate']         = $csv_encode_type_sjis ? trim( mb_convert_encoding( $datas[ USCES_COL_ITEM_POINTRATE ], 'UTF-8', 'SJIS' ) ) : trim( $datas[ USCES_COL_ITEM_POINTRATE ] );
							$item['itemGpNum1']            = $csv_encode_type_sjis ? trim( mb_convert_encoding( $datas[ USCES_COL_ITEM_GPNUM1 ], 'UTF-8', 'SJIS' ) ) : trim( $datas[ USCES_COL_ITEM_GPNUM1 ] );
							$item['itemGpDis1']            = $csv_encode_type_sjis ? trim( mb_convert_encoding( $datas[ USCES_COL_ITEM_GPDIS1 ], 'UTF-8', 'SJIS' ) ) : trim( $datas[ USCES_COL_ITEM_GPDIS1 ] );
							$item['itemGpNum2']            = $csv_encode_type_sjis ? trim( mb_convert_encoding( $datas[ USCES_COL_ITEM_GPNUM2 ], 'UTF-8', 'SJIS' ) ) : trim( $datas[ USCES_COL_ITEM_GPNUM2 ] );
							$item['itemGpDis2']            = $csv_encode_type_sjis ? trim( mb_convert_encoding( $datas[ USCES_COL_ITEM_GPDIS2 ], 'UTF-8', 'SJIS' ) ) : trim( $datas[ USCES_COL_ITEM_GPDIS2 ] );
							$item['itemGpNum3']            = $csv_encode_type_sjis ? trim( mb_convert_encoding( $datas[ USCES_COL_ITEM_GPNUM3 ], 'UTF-8', 'SJIS' ) ) : trim( $datas[ USCES_COL_ITEM_GPNUM3 ] );
							$item['itemGpDis3']            = $csv_encode_type_sjis ? trim( mb_convert_encoding( $datas[ USCES_COL_ITEM_GPDIS3 ], 'UTF-8', 'SJIS' ) ) : trim( $datas[ USCES_COL_ITEM_GPDIS3 ] );
							$item['itemShipping']          = $csv_encode_type_sjis ? trim( mb_convert_encoding( $datas[ USCES_COL_ITEM_SHIPPING ], 'UTF-8', 'SJIS' ) ) : trim( $datas[ USCES_COL_ITEM_SHIPPING ] );
							$item['itemDeliveryMethod']    = $item_delivery_method;
							$item['itemShippingCharge']    = $csv_encode_type_sjis ? trim( mb_convert_encoding( $datas[ USCES_COL_ITEM_SHIPPINGCHARGE ], 'UTF-8', 'SJIS' ) ) : trim( $datas[ USCES_COL_ITEM_SHIPPINGCHARGE ] );
							$item['itemIndividualSCharge'] = $csv_encode_type_sjis ? trim( mb_convert_encoding( $datas[ USCES_COL_ITEM_INDIVIDUALSCHARGE ], 'UTF-8', 'SJIS' ) ) : trim( $datas[ USCES_COL_ITEM_INDIVIDUALSCHARGE ] );
							$item['itemOrderAcceptable']   = $csv_encode_type_sjis ? trim( mb_convert_encoding( $datas[ USCES_COL_ITEM_ORDER_ACCEPTABLE ], 'UTF-8', 'SJIS' ) ) : trim( $datas[ USCES_COL_ITEM_ORDER_ACCEPTABLE ] );

							$item['item_division']      = $csv_encode_type_sjis ? trim( mb_convert_encoding( $datas[ USCES_COL_ITEM_DIVISION ], 'UTF-8', 'SJIS' ) ) : trim( $datas[ USCES_COL_ITEM_DIVISION ] );
							$item['item_charging_type'] = $csv_encode_type_sjis ? trim( mb_convert_encoding( $datas[ USCES_COL_ITEM_CHARGING_TYPE ], 'UTF-8', 'SJIS' ) ) : trim( $datas[ USCES_COL_ITEM_CHARGING_TYPE ] );
							$item['item_frequency']     = $csv_encode_type_sjis ? trim( mb_convert_encoding( $datas[ USCES_COL_ITEM_FREQUENCY ], 'UTF-8', 'SJIS' ) ) : trim( $datas[ USCES_COL_ITEM_FREQUENCY ] );
							$item['item_chargingday']   = $csv_encode_type_sjis ? trim( mb_convert_encoding( $datas[ USCES_COL_ITEM_CHARGINGDAY ], 'UTF-8', 'SJIS' ) ) : trim( $datas[ USCES_COL_ITEM_CHARGINGDAY ] );

							if ( isset( $datas[ USCES_COL_DLSELLER_INTERVAL ] ) ) {
								$item['dlseller_interval'] = $csv_encode_type_sjis ? trim( mb_convert_encoding( $datas[ USCES_COL_DLSELLER_INTERVAL ], 'UTF-8', 'SJIS' ) ) : trim( $datas[ USCES_COL_DLSELLER_INTERVAL ] );
							}
							if ( 'data' === $datas[ USCES_COL_ITEM_DIVISION ] ) {
								if ( isset( $datas[ USCES_COL_DLSELLER_VALIDITY ] ) ) {
									$item['dlseller_validity'] = $csv_encode_type_sjis ? trim( mb_convert_encoding( $datas[ USCES_COL_DLSELLER_VALIDITY ], 'UTF-8', 'SJIS' ) ) : trim( $datas[ USCES_COL_DLSELLER_VALIDITY ] );
								}
								if ( isset( $datas[ USCES_COL_DLSELLER_FILE ] ) ) {
									$item['dlseller_file'] = $csv_encode_type_sjis ? trim( mb_convert_encoding( $datas[ USCES_COL_DLSELLER_FILE ], 'UTF-8', 'SJIS' ) ) : trim( $datas[ USCES_COL_DLSELLER_FILE ] );
								}
								if ( isset( $datas[ USCES_COL_DLSELLER_DATE ] ) ) {
									$dlseller_date         = ltrim( $datas[ USCES_COL_DLSELLER_DATE ], "'" );
									$item['dlseller_date'] = $csv_encode_type_sjis ? trim( mb_convert_encoding( $dlseller_date, 'UTF-8', 'SJIS' ) ) : trim( $dlseller_date );
								}
								if ( isset( $datas[ USCES_COL_DLSELLER_VERSION ] ) ) {
									$item['dlseller_version'] = $csv_encode_type_sjis ? trim( mb_convert_encoding( $datas[ USCES_COL_DLSELLER_VERSION ], 'UTF-8', 'SJIS' ) ) : trim( $datas[ USCES_COL_DLSELLER_VERSION ] );
								}
								if ( isset( $datas[ USCES_COL_DLSELLER_AUTHOR ] ) ) {
									$item['dlseller_author'] = $csv_encode_type_sjis ? trim( mb_convert_encoding( $datas[ USCES_COL_DLSELLER_AUTHOR ], 'UTF-8', 'SJIS' ) ) : trim( $datas[ USCES_COL_DLSELLER_AUTHOR ] );
								}
							}

							wel_update_item_data( $item, $post_id );

							// add term_relationships, edit term_taxonomy.
							// Category.
							if ( $category_format_slug ) {
								$categories    = array();
								$category_slug = explode( ';', $datas[ USCES_COL_CATEGORY ] );
								foreach ( (array) $category_slug as $slug ) {
									$categories[] = usces_get_cat_id( $slug );
								}
							} else {
								$categories = explode( ';', $datas[ USCES_COL_CATEGORY ] );
							}
							wp_set_post_categories( $post_id, $categories );
							wp_update_term_count( $categories, 'category' );

							// Tag.
							$tags = ( $csv_encode_type_sjis ) ? explode( ';', trim( mb_convert_encoding( $datas[ USCES_COL_POST_TAG ], 'UTF-8', 'SJIS' ) ) ) : explode( ';', trim( $datas[ USCES_COL_POST_TAG ] ) );
							wp_set_post_tags( $post_id, $tags );

							// Add Custom Field.
							if ( is_array( $cfdata ) && 0 <= count( $cfdata ) ) {
								reset( $cfdata );
								$cfstr = '';

								foreach ( $cfdata as $row ) {
									preg_match( '/^([^=]+)=([\s\S]*)$/m', $row, $cf );
									if ( isset( $cf[1] ) && ! WCUtils::is_blank( $cf[1] ) ) {
										$cfstr .= '(' . $post_id . ", '" . esc_sql( $cf[1] ) . "','" . esc_sql( $cf[2] ) . "'),";
									}
								}

								if ( ! WCUtils::is_blank( $cfstr ) ) {
									$cfstr  = rtrim( $cfstr, ',' );
									$db_res = $wpdb->query( "INSERT INTO {$wpdb->postmeta} (post_id, meta_key, meta_value) VALUES {$cfstr}" );
								}
							}
							do_action( 'dlseller_action_uploadcsv_itemvalue', $post_id, $datas, $add_field_num );

							// addOption.
							// Add Item Option.
							wel_delete_all_opt_data( $post_id );

							for ( $i = 0; $i < $opnum; $i++ ) {
								$opflg    = true;
								$optvalue = array();
								for ( $o = 1; $o <= 4; $o++ ) {
									$key = ( $min_field_num - 1 ) + $o + ( $i * 4 );
									if ( 1 === $o && '' === $datas[ $key ] ) {
										$opflg = false;
										break 1;
									}
									switch ( $o ) {
										case 1:
											$optvalue['name'] = ( $csv_encode_type_sjis ) ? trim( mb_convert_encoding( $datas[ $key ], 'UTF-8', 'SJIS' ) ) : trim( $datas[ $key ] );
											break;
										case 2:
											$optvalue['means'] = (int) $datas[ $key ];
											break;
										case 3:
											$optvalue['essential'] = (int) $datas[ $key ];
											break;
										case 4:
											if ( ! empty( $datas[ $key ] ) ) {
												$cr                = array( "\r\n", "\r" );
												$datavalue         = trim( $datas[ $key ] );
												$datavalue         = str_replace( $cr, '', $datavalue );
												$optvalue['value'] = ( $csv_encode_type_sjis ) ? str_replace( ';', "\n", mb_convert_encoding( $datavalue, 'UTF-8', 'SJIS' ) ) : str_replace( ';', "\n", $datavalue );
											} else {
												$optvalue['value'] = '';
											}
											break;
									}
								}

								if ( $opflg && ! empty( $optvalue ) ) {

									$optvalue['sort'] = $i;

									$resopt = wel_add_opt_data( $post_id, $optvalue );
								}
							}
						} else {
							$sku_index++;
						}

						// add SKU.
						// Add Item SKU.

						$skus = wel_get_skus( $post_id, 'sort', false );
						$sku  = isset( $skus[ $sku_index ] ) ? $skus[ $sku_index ] : false;
						if ( false === $sku ) {
							$sku             = array();
							$sku['code']     = ( $csv_encode_type_sjis ) ? trim( mb_convert_encoding( $datas[ USCES_COL_SKU_CODE ], 'UTF-8', 'SJIS' ) ) : trim( $datas[ USCES_COL_SKU_CODE ] );
							$sku['name']     = ( $csv_encode_type_sjis ) ? trim( mb_convert_encoding( $datas[ USCES_COL_SKU_NAME ], 'UTF-8', 'SJIS' ) ) : trim( $datas[ USCES_COL_SKU_NAME ] );
							$sku['cprice']   = $datas[ USCES_COL_SKU_CPRICE ];
							$sku['price']    = $datas[ USCES_COL_SKU_PRICE ];
							$sku['stocknum'] = $datas[ USCES_COL_SKU_ZAIKONUM ];
							$sku['stock']    = $datas[ USCES_COL_SKU_ZAIKO ];
							$sku['unit']     = ( $csv_encode_type_sjis ) ? trim( mb_convert_encoding( $datas[ USCES_COL_SKU_UNIT ], 'UTF-8', 'SJIS' ) ) : trim( $datas[ USCES_COL_SKU_UNIT ] );
							$sku['gp']       = $datas[ USCES_COL_SKU_GPTEKIYO ];
							$sku['sort']     = $sku_index;
							$sku['taxrate']  = usces_csv_set_sku_applicable_taxrate( $datas[ USCES_COL_SKU_APPLICABLE_TAXRATE ] );

							$sku = apply_filters( 'dlseller_filter_uploadcsv_skuvalue', $sku, $datas );

							wel_add_sku_data( $post_id, $sku );
						} else {
							$sku['code']     = ( $csv_encode_type_sjis ) ? trim( mb_convert_encoding( $datas[ USCES_COL_SKU_CODE ], 'UTF-8', 'SJIS' ) ) : trim( $datas[ USCES_COL_SKU_CODE ] );
							$sku['name']     = ( $csv_encode_type_sjis ) ? trim( mb_convert_encoding( $datas[ USCES_COL_SKU_NAME ], 'UTF-8', 'SJIS' ) ) : trim( $datas[ USCES_COL_SKU_NAME ] );
							$sku['cprice']   = $datas[ USCES_COL_SKU_CPRICE ];
							$sku['price']    = $datas[ USCES_COL_SKU_PRICE ];
							$sku['stocknum'] = $datas[ USCES_COL_SKU_ZAIKONUM ];
							$sku['stock']    = $datas[ USCES_COL_SKU_ZAIKO ];
							$sku['unit']     = ( $csv_encode_type_sjis ) ? trim( mb_convert_encoding( $datas[ USCES_COL_SKU_UNIT ], 'UTF-8', 'SJIS' ) ) : trim( $datas[ USCES_COL_SKU_UNIT ] );
							$sku['gp']       = $datas[ USCES_COL_SKU_GPTEKIYO ];
							$sku['sort']     = $sku_index;
							$sku['taxrate']  = usces_csv_set_sku_applicable_taxrate( $datas[ USCES_COL_SKU_APPLICABLE_TAXRATE ] );

							$sku = apply_filters( 'dlseller_filter_uploadcsv_skuvalue', $sku, $datas );

							$meta_id = $sku['meta_id'];
							wel_update_sku_data_by_id( $meta_id, $post_id, $sku );
						}
					}
					$pre_code = $item_code;
					$comp_num++;

					do_action(
						'dlseller_after_uploadcsv_line_processed',
						array(
							'line'       => $line,
							'datas'      => $datas,
							'check_mode' => $check_mode,
							'post_id'    => $post_id,
							'sku'        => ! empty( $sku ) ? $sku : null,
						)
					);

					// Status update.
					if ( 0 === ( $line_num % 10 ) ) {
						$progress = array(
							'info'     => $file_info,
							'status'   => __( 'Processing...', 'usces' ) . $check_label,
							'progress' => sprintf( __( 'Successful %1$s lines, Failed %2$s lines.', 'usces' ), $comp_num, $err_num ),
							'i'        => $line_num,
							'all'      => $total_num,
						);
						record_item_up_progress( $progress );
					}
				}
			}

			do_action(
				'dlseller_after_uploadcsv_lines_processed',
				array(
					'lines'      => $lines,
					'datas'      => $datas,
					'check_mode' => $check_mode,
					'error'      => $error,
				)
			);

			// Final status.
			if ( $error ) {
				$progress = array(
					'info'     => $file_info,
					'status'   => __( 'End (with error)', 'usces' ) . $check_label,
					'progress' => sprintf( __( 'Successful %1$s lines, Failed %2$s lines.', 'usces' ), $comp_num, $err_num ),
					'i'        => $line_num,
					'all'      => $total_num,
					'flag'     => 'complete',
				);
				record_item_up_progress( $progress );
			} else {
				$progress = array(
					'info'     => $file_info,
					'status'   => __( 'End', 'usces' ) . $check_label,
					'progress' => sprintf( __( 'Successful %1$s lines, Failed %2$s lines.', 'usces' ), $comp_num, $err_num ),
					'log'      => __( 'No abnormality', 'usces' ),
					'i'        => $line_num,
					'all'      => $total_num,
					'flag'     => 'complete',
				);
				record_item_up_progress( $progress );
			}
			if ( file_exists( $upload_folder . $file_name ) ) {
				unlink( $upload_folder . $file_name );
			}
			die( wp_json_encode( $progress ) );
		}
	endif; // End of All columns Product CSV upload.

	if ( ! function_exists( 'usces_download_item_list' ) ) :

		/**
		 * All columns Product CSV download.
		 *
		 * @since  2.2.2
		 */
		function usces_download_item_list() {
			global $wpdb, $usces;

			require_once USCES_PLUGIN_DIR . '/classes/itemList.class.php';

			$ext   = 'csv';
			$th_h1 = '"';
			$th_h  = ',"';
			$th_f  = '"';
			$td_h1 = '"';
			$td_h  = ',"';
			$td_f  = '"';
			$sp    = ';';
			$cf_sp = ';;'; // custom field separator.
			$eq    = '=';
			$lf    = "\n";

			// Save the selection status of download columns.
			$usces_opt_item = get_option( 'usces_opt_item' );
			if ( ! is_array( $usces_opt_item ) ) {
				$usces_opt_item = array();
			}
			$usces_opt_item['chk_header'] = ( isset( $_REQUEST['chk_header'] ) ) ? 1 : 0;
			$usces_opt_item['ftype_item'] = $ext;
			update_option( 'usces_opt_item', $usces_opt_item );

			// Get data.
			$item_table = $wpdb->posts;
			$arr_column = array(
				__( 'item code', 'usces' )      => 'item_code',
				__( 'item name', 'usces' )      => 'item_name',
				__( 'SKU code', 'usces' )       => 'sku_key',
				__( 'selling price', 'usces' )  => 'price',
				__( 'stock', 'usces' )          => 'zaiko_num',
				__( 'stock status', 'usces' )   => 'zaiko',
				__( 'Categories', 'usces' )     => 'category',
				__( 'display status', 'usces' ) => 'display_status',
			);

			$_REQUEST['searchIn']  = 'searchIn';
			$item_data             = new dataList( $item_table, $arr_column );
			$item_data->pageLimit  = 'off';
			$item_data->exportMode = true;
			$res                   = $item_data->MakeTable();
			$rows                  = $item_data->rows;

			// Processing branch for each mode.
			$results = apply_filters( 'usces_filter_item_downloadcsv_mode', array(), $rows, $usces_opt_item );
			if ( ! empty( $results ) ) {

				extract( $results );

			} elseif ( isset( $_REQUEST['mode'] ) && 'stock' === $_REQUEST['mode'] ) {

				$results = usces_download_item_stock_list( $rows, $usces_opt_item );
				if ( ! empty( $results ) ) {
					extract( $results );
				}

			} elseif ( isset( $_REQUEST['mode'] ) && 'sku' === $_REQUEST['mode'] ) {

				$results = usces_download_item_sku_list( $rows, $usces_opt_item );
				if ( ! empty( $results ) ) {
					extract( $results );
				}

			} elseif ( isset( $_REQUEST['mode'] ) && 'meta' === $_REQUEST['mode'] ) {

				$results = usces_download_item_meta_list( $rows, $usces_opt_item );
				if ( ! empty( $results ) ) {
					extract( $results );
				}

			} else {

				$line = '';

				// Heading.
				if ( 1 === (int) $usces_opt_item['chk_header'] ) {
					$line .= $th_h1 . 'Post ID' . $th_f;
					$line .= $th_h . __( 'Post Author', 'usces' ) . $th_f;
					$line .= $th_h . __( 'explanation', 'usces' ) . $th_f;
					$line .= $th_h . __( 'Title', 'usces' ) . $th_f;
					$line .= $th_h . __( 'excerpt', 'usces' ) . $th_f;
					$line .= $th_h . __( 'display status', 'usces' ) . $th_f;
					$line .= $th_h . __( 'Comment Status', 'usces' ) . $th_f;
					$line .= $th_h . __( 'Post Password', 'usces' ) . $th_f;
					$line .= $th_h . __( 'Post Name', 'usces' ) . $th_f;
					$line .= $th_h . __( 'Publish date', 'usces' ) . $th_f;

					$line .= $th_h . __( 'item code', 'usces' ) . $th_f;
					$line .= $th_h . __( 'item name', 'usces' ) . $th_f;
					$line .= $th_h . __( 'Limited amount for purchase', 'usces' ) . $th_f;
					$line .= $th_h . __( 'Percentage of points', 'usces' ) . $th_f;
					$line .= $th_h . __( 'Business package discount', 'usces' ) . '1-' . __( 'num', 'usces' ) . $th_f . $th_h . __( 'Business package discount', 'usces' ) . '1-' . __( 'rate', 'usces' ) . $th_f;
					$line .= $th_h . __( 'Business package discount', 'usces' ) . '2-' . __( 'num', 'usces' ) . $th_f . $th_h . __( 'Business package discount', 'usces' ) . '2-' . __( 'rate', 'usces' ) . $th_f;
					$line .= $th_h . __( 'Business package discount', 'usces' ) . '3-' . __( 'num', 'usces' ) . $th_f . $th_h . __( 'Business package discount', 'usces' ) . '3-' . __( 'rate', 'usces' ) . $th_f;
					$line .= $th_h . __( 'Sold out limit', 'usces' ) . $th_f;

					$line .= $th_h . __( 'Division', 'dlseller' ) . $th_f;
					$line .= $th_h . __( 'Charging type', 'usces' ) . $th_f;

					$line .= $th_h . __( 'estimated shipping date', 'usces' ) . $th_f;
					$line .= $th_h . __( 'shipping option', 'usces' ) . $th_f;
					$line .= $th_h . __( 'Shipping', 'usces' ) . $th_f;
					$line .= $th_h . __( 'Postage individual charging', 'usces' ) . $th_f;

					$line .= $th_h . __( 'Charging Interval', 'dlseller' ) . $th_f;
					$line .= $th_h . __( 'Charging Date', 'dlseller' ) . $th_f;
					$line .= $th_h . __( 'Contract Period(Months)', 'dlseller' ) . $th_f;
					$line .= $th_h . __( 'Validity(days)', 'dlseller' ) . $th_f;
					$line .= $th_h . __( 'File Name', 'dlseller' ) . $th_f;
					$line .= $th_h . __( 'Release Date', 'dlseller' ) . $th_f;
					$line .= $th_h . __( 'Version', 'dlseller' ) . $th_f;
					$line .= $th_h . __( 'Author', 'dlseller' ) . $th_f;
					$line .= $th_h . __( 'Purchases', 'dlseller' ) . $th_f;
					$line .= $th_h . __( 'Downloads', 'dlseller' ) . $th_f;

					$line .= $th_h . __( 'Categories', 'usces' ) . $th_f;
					$line .= $th_h . __( 'tag', 'usces' ) . $th_f;
					$line .= $th_h . __( 'Custom Field', 'usces' ) . $th_f;

					$line .= apply_filters( 'dlseller_filter_downloadcsv_itemheader', '' );
					$line  = apply_filters( 'dlseller_filter_downloadcsv_add_itemheader', $line );

					$line .= $th_h . __( 'SKU code', 'usces' ) . $th_f;
					$line .= $th_h . __( 'SKU display name ', 'usces' ) . $th_f;
					$line .= $th_h . __( 'normal price', 'usces' ) . $th_f;
					$line .= $th_h . __( 'Sale price', 'usces' ) . $th_f;
					$line .= $th_h . __( 'stock', 'usces' ) . $th_f;
					$line .= $th_h . __( 'stock status', 'usces' ) . $th_f;
					$line .= $th_h . __( 'unit', 'usces' ) . $th_f;
					$line .= $th_h . __( 'Apply business package', 'usces' ) . $th_f;
					$line .= $th_h . __( 'Applicable tax rate', 'usces' ) . $th_f;

					$line .= apply_filters( 'dlseller_filter_downloadcsv_header', '' );
					$line  = apply_filters( 'dlseller_filter_downloadcsv_add_header', $line );
					$line .= $th_h . __( 'option name', 'usces' ) . $th_f . $th_h . __( 'Field type', 'usces' ) . $th_f . $th_h . __( 'Required', 'usces' ) . $th_f . $th_h . __( 'selected amount', 'usces' ) . $th_f;
					$line .= $lf;
				}

				mb_http_output( 'pass' );
				set_time_limit( 3600 );
				header( 'Content-Type: application/octet-stream' );
				header( 'Content-Disposition: attachment; filename=usces_item_list.' . $ext );
				@ob_end_flush();
				flush();

				$category_format_slug = ( isset( $usces->options['system']['csv_category_format'] ) && 1 === (int) $usces->options['system']['csv_category_format'] ) ? true : false;
				$csv_encode_type_sjis = ( isset( $usces->options['system']['csv_encode_type'] ) && 1 === (int) $usces->options['system']['csv_encode_type'] ) ? false : true;

				// Outupt data.
				foreach ( (array) $rows as $row ) {

					$post_id = $row['ID'];
					$product = wel_get_product( $post_id );
					$post    = $product['_pst'];

					// Post Data.
					$line_item  = $td_h1 . $product['ID'] . $td_f;
					$line_item .= $td_h . $post->post_author . $td_f;
					$line_item .= $td_h . usces_entity_decode( $post->post_content, $ext ) . $td_f;
					$line_item .= $td_h . usces_entity_decode( $post->post_title, $ext ) . $td_f;
					$line_item .= $td_h . usces_entity_decode( $post->post_excerpt, $ext ) . $td_f;
					$line_item .= $td_h . $post->post_status . $td_f;
					$line_item .= $td_h . $post->comment_status . $td_f;
					$line_item .= $td_h . $post->post_password . $td_f;
					$line_item .= $td_h . urldecode( $post->post_name ) . $td_f;
					$line_item .= $td_h . $post->post_date . $td_f;

					// Item Meta.
					$line_item .= $td_h . $product['itemCode'] . $td_f;
					$line_item .= $td_h . usces_entity_decode( $product['itemName'], $ext ) . $td_f;
					$line_item .= $td_h . $product['itemRestriction'] . $td_f;
					$line_item .= $td_h . $product['itemPointrate'] . $td_f;
					$line_item .= $td_h . $product['itemGpNum1'] . $td_f . $td_h . $product['itemGpDis1'] . $td_f;
					$line_item .= $td_h . $product['itemGpNum2'] . $td_f . $td_h . $product['itemGpDis2'] . $td_f;
					$line_item .= $td_h . $product['itemGpNum3'] . $td_f . $td_h . $product['itemGpDis3'] . $td_f;
					$line_item .= $td_h . $product['itemOrderAcceptable'] . $td_f;

					$line_item .= $td_h . $product['item_division'] . $td_f;
					$line_item .= $td_h . $product['item_charging_type'] . $td_f;

					$line_item .= $td_h . $product['itemShipping'] . $td_f;

					$delivery_method      = '';
					$item_delivery_method = $product['itemDeliveryMethod'];
					foreach ( (array) $item_delivery_method as $k => $v ) {
						$delivery_method .= $v . $sp;
					}
					$delivery_method = rtrim( $delivery_method, $sp );

					$line_item .= $td_h . $delivery_method . $td_f;
					$line_item .= $td_h . $product['itemShippingCharge'] . $td_f;
					$line_item .= $td_h . $product['itemIndividualSCharge'] . $td_f;

					$line_item .= $td_h . $product['item_frequency'] . $td_f;
					$line_item .= $td_h . $product['item_chargingday'] . $td_f;
					$line_item .= $td_h . $product['dlseller_interval'] . $td_f;
					$line_item .= $td_h . $product['dlseller_validity'] . $td_f;
					$line_item .= $td_h . $product['dlseller_file'] . $td_f;
					$line_item .= $td_h . $product['dlseller_date'] . $td_f;
					$line_item .= $td_h . $product['dlseller_version'] . $td_f;
					$line_item .= $td_h . $product['dlseller_author'] . $td_f;

					$dls_mon = usces_dlseller_get_dlcount( $post_id, 'month' );
					$dls_tol = usces_dlseller_get_dlcount( $post_id, 'total' );

					if ( ! empty( $item_division ) ) {
						$line_item .= $td_h . $dls_mon['par'] . $sp . $dls_tol['par'] . $td_f;
						$line_item .= $td_h . $dls_mon['dl'] . $sp . $dls_tol['dl'] . $td_f;
					} else {
						$line_item .= $td_h . $td_f;
						$line_item .= $td_h . $td_f;
					}

					// Categories.
					$category = '';
					$cat_ids  = wp_get_post_categories( $post_id );
					if ( ! empty( $cat_ids ) ) {
						if ( $category_format_slug ) {
							foreach ( $cat_ids as $id ) {
								$cat       = get_category( $id );
								$category .= $cat->slug . $sp;
							}
						} else {
							foreach ( $cat_ids as $id ) {
								$category .= $id . $sp;
							}
						}
						$category = rtrim( $category, $sp );
					}
					$line_item .= $td_h . $category . $td_f;

					// Tags.
					$tag     = '';
					$tags_ob = wp_get_object_terms( $post_id, 'post_tag' );
					foreach ( $tags_ob as $ob ) {
						$tag .= $ob->name . $sp;
					}
					$tag = rtrim( $tag, $sp );

					$line_item .= $td_h . $tag . $td_f;

					// Custom Fields.
					$cfield        = '';
					$custom_fields = wel_get_extra_data( $post_id );

					if ( $custom_fields && is_array( $custom_fields ) && 0 < count( $custom_fields ) ) {
						foreach ( $custom_fields as $cfkey => $cfvalues ) {
							if ( '_itemOrderAcceptable' === $cfkey ) {
								continue;
							}
							if ( is_array( $cfvalues ) ) {
								foreach ( $cfvalues as $value ) {
									$cfield .= usces_entity_decode( $cfkey, $ext ) . $eq . usces_entity_decode( $value, $ext ) . $cf_sp;
								}
							} else {
								$cfield .= usces_entity_decode( $cfkey, $ext ) . $eq . usces_entity_decode( $cfvalues, $ext ) . $cf_sp;
							}
						}
						$cfield = rtrim( $cfield, $sp );
					}
					$line_item .= $td_h . $cfield . $td_f;

					$line_item .= apply_filters( 'dlseller_filter_downloadcsv_itemvalue', '', $post_id );
					$line_item  = apply_filters( 'dlseller_filter_downloadcsv_add_itemvalue', $line_item, $post_id, $post );

					// Item Options.
					$line_options = '';
					$opts         = $product['_opt'];

					foreach ( $opts as $opt ) {
						$value = '';

						if ( is_array( $opt['value'] ) ) {
							foreach ( $opt['value'] as $v ) {
								$v      = usces_change_line_break( $v );
								$values = explode( "\n", $v );
								foreach ( $values as $val ) {
									$value .= $val . $sp;
								}
							}
							$value = rtrim( $value, $sp );

						} else {
							$value = usces_change_line_break( $opt['value'] );
							$value = str_replace( "\n", ';', $value );
						}
						$line_options .= $td_h . usces_entity_decode( $opt['name'], $ext ) . $td_f;
						$line_options .= $td_h . $opt['means'] . $td_f;
						$line_options .= $td_h . $opt['essential'] . $td_f;
						$line_options .= $td_h . usces_entity_decode( $value, $ext ) . $td_f;
					}

					// SKU.
					$skus = $product['_sku'];
					foreach ( $skus as $sku ) {
						$line_sku  = $td_h . $sku['code'] . $td_f;
						$line_sku .= $td_h . usces_entity_decode( $sku['name'], $ext ) . $td_f;
						$line_sku .= $td_h . usces_crform( $sku['cprice'], false, false, 'return', false ) . $td_f;
						$line_sku .= $td_h . usces_crform( $sku['price'], false, false, 'return', false ) . $td_f;
						$line_sku .= $td_h . $sku['stocknum'] . $td_f;
						$line_sku .= $td_h . $sku['stock'] . $td_f;
						$line_sku .= $td_h . usces_entity_decode( $sku['unit'], $ext ) . $td_f;
						$line_sku .= $td_h . $sku['gp'] . $td_f;
						$line_sku .= $td_h . usces_csv_get_sku_applicable_taxrate( $sku ) . $td_f;

						$line_sku .= apply_filters( 'dlseller_filter_downloadcsv_skuvalue', '', $sku );
						$line_sku  = apply_filters( 'dlseller_filter_downloadcsv_add_skuvalue', $line_sku, $sku );
						$line     .= $line_item . $line_sku . $line_options . $lf;
					}
					if ( $csv_encode_type_sjis ) {
						$line = mb_convert_encoding( $line, apply_filters( 'usces_filter_output_csv_encode', 'SJIS-win' ), 'UTF-8' );
					}
					print( $line );

					if ( ob_get_contents() ) {
						ob_flush();
						flush();
					}
					$line = '';
					wp_cache_flush();
				}
			}

			unset( $rows, $item_data, $line, $line_item, $line_options, $line_sku );
			exit();
		}
	endif; // End of All columns Product CSV download.
}
