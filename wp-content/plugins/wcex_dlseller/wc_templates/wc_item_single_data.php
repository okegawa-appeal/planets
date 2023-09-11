<?php
/**
 * <meta content="charset=UTF-8">
 * @package Welcart
 * @subpackage Welcart Default Theme
 */
get_header();
?>
<div id="content" class="two-column">
<div class="catbox">

<?php if (have_posts()) : the_post(); ?>

	<div <?php post_class() ?> id="post-<?php the_ID(); ?>">
		<h1 class="item_page_title"><?php the_title(); ?></h1>
		<div class="storycontent">

			<?php usces_remove_filter(); ?>
			<?php usces_the_item(); ?>
			<?php $charging_type = dlseller_get_charging_type( $post->ID ); ?>

			<div id="itempage">
				<div class="page_content"><?php the_content(); ?></div>
				<div class="itemimg">
				<a href="<?php usces_the_itemImageURL(0); ?>" <?php echo apply_filters('usces_itemimg_anchor_rel', NULL); ?>><?php usces_the_itemImage(0, 300, 300, $post); ?></a>
				</div>

			<?php usces_have_skus(); ?>
				<h2 class="item_name"><?php usces_the_itemName(); ?>&nbsp;(<?php usces_the_itemCode(); ?>)</h2>


				<div class="exp clearfix">
					<div class="field">
					<?php if( usces_the_itemCprice('return') > 0 ) : ?>
						<div class="field_name"><?php _e('List price', 'usces'); ?><?php usces_guid_tax(); ?></div>
						<div class="field_cprice"><?php usces_the_itemCpriceCr(); ?><?php if( 'continue' == $charging_type ) : ?>(<?php dlseller_frequency_name($post->ID, 'amount'); ?>)<?php endif; ?></div>
					<?php endif; ?>
						<div class="field_name"><?php _e('selling price', 'usces'); ?><?php usces_guid_tax(); ?></div>
						<div class="field_price"><?php usces_the_itemPriceCr(); ?><?php if( 'continue' == $charging_type ) : ?>(<?php dlseller_frequency_name($post->ID, 'amount'); ?>)<?php endif; ?></div>
					</div>
					<?php if( $item_custom = usces_get_item_custom( $post->ID, 'list', 'return' ) ) : ?>
					<div class="field"><?php echo $item_custom; ?></div>
					<?php endif; ?>
					<div class="field">
					<table class="dlseller">
						<tr><th><?php _e('dlValidity(days)', 'dlseller'); ?></th><td><?php esc_html_e(usces_dlseller_validity($post)); ?></td></tr>
						<tr><th><?php _e('File Name', 'dlseller'); ?></th><td><?php esc_html_e(usces_dlseller_filename($post)); ?></td></tr>
						<tr><th><?php _e('Release Date', 'dlseller'); ?></th><td><?php esc_html_e(usces_get_itemMeta('_dlseller_date', $post->ID, 'return')); ?></td></tr>
						<tr><th><?php _e('Version', 'dlseller'); ?></th><td><?php esc_html_e(usces_get_itemMeta('_dlseller_version', $post->ID, 'return')); ?></td></tr>
						<tr><th><?php _e('Author', 'dlseller'); ?></th><td><?php esc_html_e(usces_get_itemMeta('_dlseller_author', $post->ID, 'return')); ?></td></tr>
					</table>
					</div>
					<?php if( 'continue' == $charging_type ) : ?>
					<!-- Charging Type Continue shipped -->
					<div class="field">
						<table class="dlseller">
							<tr><th><?php _e('First Withdrawal Date', 'dlseller'); ?></th><td><?php echo dlseller_first_charging( $post->ID ); ?></td></tr>
							<?php if( 0 < (int)$usces_item['dlseller_interval'] ) : ?>
							<tr><th><?php _e('Contract Period', 'dlseller'); ?></th><td><?php echo $usces_item['dlseller_interval']; ?><?php _e('Month(Automatic Renewal)', 'dlseller'); ?></td></tr>
							<?php endif; ?>
						</table>
					</div>
					<?php endif; ?>
				</div><!-- end of exp -->

				<form action="<?php echo USCES_CART_URL; ?>" method="post">
					<?php usces_the_itemGpExp(); ?>
					<div class="skuform" align="right">
					<?php if (usces_is_options()) : ?>
						<table class='item_option'>
							<caption><?php _e('Please appoint an option.', 'usces'); ?></caption>
						<?php while (usces_have_options()) : ?>
							<tr><th><?php usces_the_itemOptName(); ?></th><td><?php usces_the_itemOption(usces_getItemOptName(),''); ?></td></tr>
						<?php endwhile; ?>
						</table>
					<?php endif; ?>
						<div style="margin-top:10px"><?php usces_the_itemSkuButton(__('Add to Shopping Cart', 'usces'), 0); ?></div>
					</div><!-- end of skuform -->
					<?php echo apply_filters('single_item_single_sku_after_field', NULL); ?>
					<?php do_action('usces_action_single_item_inform'); ?>
				</form>
				<?php do_action('usces_action_single_item_outform'); ?>

				<div class="itemsubimg">
			<?php $imageid = usces_get_itemSubImageNums(); ?>
			<?php foreach ( $imageid as $id ) : ?>
					<a href="<?php usces_the_itemImageURL($id); ?>" <?php echo apply_filters('usces_itemimg_anchor_rel', NULL); ?>><?php usces_the_itemImage($id, 135, 135, $post); ?></a>
			<?php endforeach; ?>
				</div><!-- end of itemsubimg -->

			<?php usces_assistance_item( $post->ID, __('An article concerned', 'usces') ); ?>

			</div><!-- end of itemspage -->
		</div><!-- end of storycontent -->
	</div>

<?php else: ?>
<p><?php _e('Sorry, no posts matched your criteria.', 'usces'); ?></p>
<?php endif; ?>

</div><!-- end of catbox -->
</div><!-- end of content -->

<?php get_sidebar( 'other' ); ?>

<?php get_footer(); ?>
