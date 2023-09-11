<?php
/**
 * DL Seller e-mail rich editor page.
 *
 * @package WCEX DL Seller
 */

?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>" />
		<meta name="viewport" content="width=device-width, user-scalable=no">
		<meta name="format-detection" content="telephone=no"/>
	</head>
	<body>
	<style>
		body {
			height: 600px !important;
			overflow: hidden !important;
		}
		#load_rich_editor {
			font-size: 13px !important;
			line-height: 2.15384615 !important;
			height: 600px !important;
		}
		#load_rich_editor iframe#sendmailmessage_ifr, #load_rich_editor textarea#sendmailmessage {
			height: 570px !important;
		}
		.wp-core-ui .quicktags-toolbar input.button.button-small {
			font-size: 12px;
			min-height: 26px;
			line-height: 2;
		}
		#wpfooter {display: none}
	</style>
	<div id="load_rich_editor">
		<p id="email_content_response"></p>
		<?php
		/**
		 * Set default tab use for rich editor
		 *
		 * @return string type of tab (text,html).
		 */
		function dlseller_set_rich_editor_default_open_tab() {
			if ( usces_is_html_mail() ) {
				return 'tinymce';
			}
		}
		// add function set rich editor default show tab.
		add_filter( 'wp_default_editor', 'dlseller_set_rich_editor_default_open_tab' );

		$order_id  = isset( $_GET['order_id'] ) ? stripslashes( $_GET['order_id'] ) : 0;
		$member_id = isset( $_GET['member_id'] ) ? stripslashes( $_GET['member_id'] ) : '';
		$content   = '';
		wp_editor(
			$content,
			'sendmailmessage',
			array(
				'dfw'           => true,
				'tabindex'      => 1,
				'textarea_rows' => 26,
				'textarea_name' => 'sendmailmessage',
			)
		);
		?>
	</div>
	<script>
		var settings ={
			url: "<?php echo esc_url( site_url() ); ?>/wp-admin/admin-ajax.php",
			type: 'POST',
			cache: false
		};
		window.onload = function() {
			jQuery("#email_content_response").html('');
			var member_id = '<?php echo (int) $member_id; ?>';
			var order_id = '<?php echo (int) $order_id; ?>';
			var s = settings;
			s.data = "action=dlseller_make_mail_ajax&order_id=" + order_id + "&member_id=" + member_id;
			var now_loading = "<?php esc_attr_e( 'now loading', 'usces' ); ?>";
			tinymce.get("sendmailmessage").setContent(now_loading);
			jQuery.ajax( s ).done(function( data ){
				if( 0 == data ) {
					jQuery("#email_content_response").html('<?php esc_attr_e( 'Data Error', 'dlseller' ); ?>');
					tinymce.get("sendmailmessage").setContent('');
				} else {
					jQuery('#sendmailaddress', window.parent.document).val(data.mailAddress);
					jQuery('#sendmailname', window.parent.document).val(data.name);
					jQuery('#sendmailsubject', window.parent.document).val(data.subject);
					content = data.message;
					tinymce.get("sendmailmessage").setContent( content );
				}
			}).fail(function( msg ){
				jQuery("#email_content_response").html(msg);
				tinymce.get("sendmailmessage").setContent('');
			});
		};
		function getContentEditor() {
			if (tinymce.get("sendmailmessage")) {
				return tinymce.get("sendmailmessage").getContent();
			} else {
				var sendmailmessageText = jQuery("#sendmailmessage").text();
				return sendmailmessageText;
			}
		}
	</script>
