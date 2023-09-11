<?php
/**
 * Wc search page
 *
 * @package Welcart
 * @subpackage Welcart_Basic
 */

get_header(); ?>

<div id="primary" class="site-content">
	<div id="content" role="main">

	<?php
	if ( have_posts() ) :
		have_posts();
		the_post();
		?>

		<header class="page-header">
			<h1 class="page-title"><?php the_title(); ?></h1>
		</header><!-- .page-header -->

		<section class="post" id="<?php echo esc_attr( $post->post_name ); ?>">

		<?php $uscpaged = ( isset( $_REQUEST['paged'] ) ) ? (int) $_REQUEST['paged'] : 1; ?>
			<script type="text/javascript">
				function usces_nextpage() {
					document.getElementById('usces_paged').value = <?php echo esc_attr( ( $uscpaged + 1 ) ); ?>;
					document.searchindetail.submit();
				}
				function usces_prepage() {
					document.getElementById('usces_paged').value = <?php echo esc_attr( ( $uscpaged - 1 ) ); ?>;
					document.searchindetail.submit();
				}
				function newsubmit() {
					document.getElementById('usces_paged').value = 1;
				}
			</script>

			<div id="searchbox" class="search-li">

				<?php
				usces_remove_filter();
				if ( isset( $_REQUEST['usces_search'] ) ) :
					$catresult    = usces_search_categories();
					$search_query = array(
						'category__and'  => $catresult,
						'posts_per_page' => 10,
						'paged'          => $uscpaged,
					);
					$search_query = apply_filters( 'usces_filter_search_query', $search_query );
					$my_query     = new WP_Query( $search_query );
					?>

					<div class="title"><?php esc_html_e( 'Search results', 'usces' ); ?>&nbsp;&nbsp;<?php echo number_format( $my_query->found_posts ); ?><?php esc_html_e( 'cases', 'usces' ); ?></div>

					<?php if ( $my_query->have_posts() ) : ?>
						<?php
						$search_result = apply_filters( 'usces_filter_search_result', null, $my_query );
						if ( ! empty( $search_result ) ) :

								echo $search_result;

						else :

							while ( $my_query->have_posts() ) :
								$my_query->the_post();
								usces_the_item();
								?>
								<article class="searchitems">
									<div class="itemimg">
										<a href="<?php the_permalink(); ?>">
											<?php usces_the_itemImage( 0, 300, 300 ); ?>
											<?php do_action( 'usces_theme_favorite_icon' ); ?>
										</a>
										<?php wel_campaign_message(); ?>
									</div>
									<div class="itemprice">
										<?php
										usces_the_firstPriceCr();
										usces_guid_tax();
										?>
									</div>
									<?php usces_crform_the_itemPriceCr_taxincluded(); ?>
									<?php if ( ! usces_have_zaiko_anyone() ) : ?>
										<div class="itemsoldout">
											<?php welcart_basic_soldout_label( get_the_ID() ); ?>
										</div>
									<?php endif; ?>
									<div class="itemname"><a href="<?php the_permalink(); ?>"  rel="bookmark"><?php usces_the_itemName(); ?></a></div>
								</article><!-- .searchitems -->
								<?php
							endwhile;
						endif;
						?>

						<div class="navigation cf">
							<?php if ( $uscpaged > 1 ) : ?>
								<a style="float:left; cursor:pointer;" onclick="usces_prepage();"><?php esc_html_e( '&laquo; Previous article', 'usces' ); ?></a>
							<?php endif; ?>
							<?php if ( $uscpaged < $my_query->max_num_pages ) : ?>
								<a style="float:right; cursor:pointer;" onclick="usces_nextpage();"><?php esc_html_e( 'Next article &raquo;', 'usces' ); ?></a>
							<?php endif; ?>
						</div>

					<?php else : ?>

						<div class="searchitems"><p><?php esc_html_e( 'The article was not found.', 'usces' ); ?></p></div>

					<?php endif; ?>

					<?php
				endif;
				wp_reset_postdata();
				?>

				<form name="searchindetail" action="<?php echo esc_url_raw( wel_get_cart_url() ); ?>usces_page=search_item" method="post">

					<div class="field">
						<label class="outlabel"><?php esc_html_e( 'Categories: AND Search', 'usces' ); ?></label><?php usces_categories_checkbox(); ?>
					</div>

					<div class="send">
						<input name="usces_search_button" class="usces_search_button" type="submit" value="<?php esc_html_e( 'Search', 'usces' ); ?>" onclick="newsubmit()" />
						<input name="paged" id="usces_paged" type="hidden" value="<?php echo esc_attr( $uscpaged ); ?>" />
						<input name="usces_search" type="hidden" />
					</div>
					<?php do_action( 'usces_action_search_item_inform', 'usces' ); ?>

				</form>

			</div><!-- #searchbox -->

		</section><!-- .post -->
	<?php endif; ?>

	</div><!-- #content -->
</div><!-- #primary -->

<?php
get_sidebar();
get_footer();
