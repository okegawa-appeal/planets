<?php
/**
 * Category Template
 *
 * @package Welcart
 * @subpackage Welcart_Basic
 */

get_header();

//カテゴリ：を削除
add_filter( 'get_the_archive_title', function( $title ) {
  return single_cat_title('', false);
});
$url = get_pagenum_link();
global $wpdb;
$query = 'select * FROM wp_pl_event where url = "'.$url.'"';
$results = $wpdb->get_results($wpdb->prepare($query),"ARRAY_A");
;
?>



	<section id="primary" class="site-content">
		<div id="content" role="main">
		
			<header class="page-header">
				<?php
				the_archive_title( '<h1 class="page-title">', '</h1>' );
				echo '<div class="categorypage-container">';
				echo '<div class="categorypage-image">';
				echo '<img class="categorypage-image" src="'.$results[0]['image'].'">';
				echo '</div>';
				echo '<div class="categorypage-description">';
				the_archive_description( '<div class="taxonomy-description">', '</div>' );
				echo '</div>';
				echo '</div>';
				?>
			</header><!-- .page-header -->



		<?php if ( usces_is_cat_of_item( get_query_var( 'cat' ) ) ) : ?>

			<?php if ( have_posts() ) : ?> 

				<div class="cat-il type-grid">

					<?php
					while ( have_posts() ) :
						the_post();
						?>
						<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
							<div class="itemimg">
								<a href="<?php the_permalink(); ?>">
									<?php usces_the_itemImage( 0, 300, 300 ); ?>
									<?php do_action( 'usces_theme_favorite_icon' ); ?>
								</a>
								<?php welcart_basic_campaign_message(); ?>
							</div>
							<div class="iteminfo">
								<div class="itemname"><a href="<?php the_permalink(); ?>"  rel="bookmark"><?php usces_the_itemName(); ?></a></div>
								<div class="itemprice">
									<?php usces_the_firstPriceCr(); ?><?php usces_guid_tax(); ?>
								</div>
								<?php usces_crform_the_itemPriceCr_taxincluded(); ?>
							</div>
							<?php if ( ! usces_have_zaiko_anyone() ) : ?>
								<div class="itemsoldout">
									<?php welcart_basic_soldout_label( get_the_ID() ); ?>
								</div>
							<?php endif; ?>
						</article>
					<?php endwhile; ?>

				</div><!-- .cat-il -->

			<?php endif; ?>

		<?php else : ?>

			<?php if ( have_posts() ) : ?>

				<div class="post-li">
					<?php
					while ( have_posts() ) :
						the_post();
						?>
						<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
							<p><time datetime="<?php the_time( 'c' ); ?>"><?php the_time( __( 'Y/m/d' ) ); ?></time></p>
							<div class="post-title">
								<a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php printf( esc_attr__( 'Permalink to %s', 'welcart_basic' ), the_title_attribute( 'echo=0' ) ); // phpcs:ignore ?>">
									<?php the_title(); ?>
								</a>
							</div>
							<?php the_excerpt(); ?>
						</article>
					<?php endwhile; ?>
				</div>

			<?php endif; ?>

		<?php endif; ?>

			<div class="pagination_wrapper">
				<?php
				$args = array(
					'type'      => 'list',
					'prev_text' => __( ' &laquo; ', 'welcart_basic' ),
					'next_text' => __( ' &raquo; ', 'welcart_basic' ),
				);
				echo wp_kses_post( paginate_links( $args ) );
				?>
			</div><!-- .pagenation-wrapper -->

		</div><!-- #content -->
	</section><!-- #primary -->

<?php
get_sidebar();
get_footer();
