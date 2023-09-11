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

			<?php if(usces_sku_num() === 1) : usces_have_skus(); ?>
			<!--1SKU-->
				<div class="page_content"><?php the_content(); ?></div>
				<div class="itemimg">
				<a href="<?php usces_the_itemImageURL(0); ?>" <?php echo apply_filters('usces_itemimg_anchor_rel', NULL); ?>><?php usces_the_itemImage(0, 300, 300, $post); ?></a>
				</div>

				<h2 class="item_name"><?php usces_the_itemName(); ?>&nbsp;(<?php usces_the_itemCode(); ?>)</h2>

				<div class="exp clearfix">
					<div class="field">
					<?php if( usces_the_itemCprice('return') > 0 ) : ?>
						<div class="field_name"><?php _e('List price', 'usces'); ?><?php usces_guid_tax(); ?></div>
						<div class="field_cprice"><?php usces_the_itemCpriceCr(); ?><?php if( 'continue' == $charging_type ) : ?>(<?php dlseller_frequency_name($post->ID, 'amount'); ?>)<?php endif; ?></div>
					<?php endif; ?>
						<div class="field_name"><?php _e('selling price', 'usces'); ?><?php usces_guid_tax(); ?></div>
						<div class="field_dlprice"><span class="field_price"><?php usces_the_itemPriceCr(); ?><?php if( 'continue' == $charging_type ) : ?>(<?php dlseller_frequency_name($post->ID, 'amount'); ?>)<?php endif; ?></span></div>
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

					<?php if( $item_custom = usces_get_item_custom( $post->ID, 'list', 'return' ) ) : ?>
					<div class="field"><?php echo $item_custom; ?></div>
					<?php endif; ?>

					<form action="<?php usces_url('cart'); ?>" method="post">
						<div class="service_skuform">
						<?php if (usces_is_options()) : ?>
							<table class='item_option autocharge'><caption><?php echo apply_filters('usces_filter_single_item_options_caption', __('Please appoint an option.', 'usces'), $post); ?></caption>
							<?php while (usces_have_options()) : ?>
								<tr><th><?php usces_the_itemOptName(); ?></th><td><?php usces_the_itemOption(usces_getItemOptName(),''); ?></td></tr>
							<?php endwhile; ?>
							</table>
						<?php endif; ?>
						</div><!-- end of service_skuform -->
						<?php if( !usces_have_zaiko() ) : ?>
						<div class="zaiko_status"><?php echo apply_filters('usces_filters_single_sku_zaiko_message', esc_html(usces_get_itemZaiko( 'name' ))); ?></div>
						<?php else : ?>
						<div class="field_button"><?php echo apply_filters('usces_filter_autocharge_price_label', usces_the_itemSkuDisp('return')); ?><?php usces_the_itemSkuButton(__('Add to Shopping Cart', 'usces'), 0); ?></div>
						<?php endif; ?>
						<?php echo apply_filters('single_item_multi_sku_after_field', NULL); ?>
						<?php do_action('usces_action_single_item_inform'); ?>
					</form>
					<?php do_action('usces_action_single_item_outform'); ?>
				</div><!-- end of exp -->
			<?php elseif(usces_sku_num() > 1) : usces_have_skus(); ?>
			<!--some SKU-->
				<div class="exp clearfix">
					<div class="page_content"><?php the_content(); ?></div>
					<div class="itemimg">
					<a href="<?php usces_the_itemImageURL(0); ?>" <?php echo apply_filters('usces_itemimg_anchor_rel', NULL); ?>><?php usces_the_itemImage(0, 300, 300, $post); ?></a>
					</div>

					<h2 class="item_name"><?php usces_the_itemName(); ?>&nbsp;(<?php usces_the_itemCode(); ?>)</h2>
					<?php if( $item_custom = usces_get_item_custom( $post->ID, 'list', 'return' ) ) : ?>
					<div class="field">
						<?php echo $item_custom; ?>
					</div>
					<?php endif; ?>
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
					<div class="skuform">
						<table class="skumulti">
							<thead>
							<tr>
								<th class="thborder"><?php _e('order number', 'usces'); ?></th>
								<th class="thborder"><?php _e('Title', 'usces'); ?></th>
					<?php if( usces_the_itemCprice('return') > 0 ) : ?>
								<th class="thborder">(<?php _e('List price', 'usces'); ?>)<?php _e('selling price', 'usces'); ?><?php usces_guid_tax(); ?></th>
					<?php else : ?>
								<th class="thborder"><?php _e('selling price', 'usces'); ?><?php usces_guid_tax(); ?></th>
					<?php endif; ?>
							</tr>
							</thead>
							<tbody>
					<?php do { ?>
							<tr>
								<td rowspan="2"><?php usces_the_itemSku(); ?></td>
								<td rowspan="2" class="skudisp subborder"><?php usces_the_itemSkuDisp(); ?>
						<?php if (usces_is_options()) : ?>
									<table class='item_option'>
									<caption><?php _e('Please appoint an option.', 'usces'); ?></caption>
							<?php while (usces_have_options()) : ?>
										<tr>
											<th><?php usces_the_itemOptName(); ?></th>
											<td><?php usces_the_itemOption(usces_getItemOptName(),''); ?></td>
										</tr>
							<?php endwhile; ?>
									</table>
						<?php endif; ?>
								</td>
								<td class="subborder price">
						<?php if( usces_the_itemCprice('return') > 0 ) : ?>
								<span class="cprice">(<?php usces_the_itemCpriceCr(); ?>)<?php if( 'continue' == $charging_type ) : ?>(<?php dlseller_frequency_name($post->ID, 'amount'); ?>)<?php endif; ?></span>
						<?php endif; ?>
								<span class="price"><?php usces_the_itemPriceCr(); ?><?php if( 'continue' == $charging_type ) : ?>(<?php dlseller_frequency_name($post->ID, 'amount'); ?>)<?php endif; ?></span>
								<br /><?php usces_the_itemGpExp(); ?>
								</td>
							</tr>
							<tr>
							<?php if( !usces_have_zaiko() ) : ?>
								<td class="button"><?php echo apply_filters('usces_filters_multi_sku_zaiko_message', esc_html(usces_get_itemZaiko( 'name' ))); ?></td>
							<?php else : ?>
								<td class="button"><?php echo apply_filters('usces_filter_autocharge_price_label', usces_the_itemSkuDisp('return')); ?><?php usces_the_itemSkuButton(__('Add to Shopping Cart', 'usces'), 0); ?></td>
							<?php endif; ?>
							</tr>
							<tr>
								<td colspan="3" class="error_message"><?php usces_singleitem_error_message($post->ID, usces_the_itemSku('return')); ?></td>
							</tr>

					<?php } while (usces_have_skus()); ?>
							</tbody>
						</table>
					</div><!-- end of skuform -->
					<?php echo apply_filters('single_item_multi_sku_after_field', NULL); ?>
					<?php do_action('usces_action_single_item_inform'); ?>
				</form>
				<?php do_action('usces_action_single_item_outform'); ?>

			<?php endif; ?>

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
