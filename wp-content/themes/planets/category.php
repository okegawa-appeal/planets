<?php
/**
 * Category Template
 *
 * @package Welcart
 * @subpackage Welcart_Basic
 */

global $wpdb;
global $usces;
$category_id = get_queried_object_id();
$genre = get_term_meta( $category_id, 'category-genre', true );
if($genre == 'premium'){
	get_header('premium');
}else{
	get_header();
}
//カテゴリ：を削除
add_filter( 'get_the_archive_title', function( $title ) {
  return single_cat_title('', false);
});
//$category_image = get_term_meta( $item->term_id, 'category-image', true );
$category_image = get_term_meta( $category_id, 'category-image', true );
if(!empty($category_image)){
	$image = $category_image;
}else{
	$url = get_pagenum_link();
	$query = 'select * FROM wp_pl_event where url = "'.$url.'"';
	$results = $wpdb->get_results($wpdb->prepare($query),"ARRAY_A");
	$image = $results[0]['image'];
}
?>

	<section id="primary" class="site-content">
		<div id="content" role="main">
		
			<header class="page-header">
				<?php
				the_archive_title( '<h1 class="page-title">', '</h1>' );
				echo '<div class="categorypage-container">';
				echo '<div class="categorypage-image">';
				echo '<img class="categorypage-image" src="'.$image.'">';
				echo '</div>';
				echo '<div class="categorypage-description">';
				the_archive_description( '<div class="taxonomy-description">', '</div>' );
				echo '</div>';
				echo '</div>';

				//くじ引き商品の場合
				$sql = "select post_id "
					."FROM wp_term_relationships ,wp_postmeta "
					."WHERE term_taxonomy_id = $category_id "
					."and object_id = post_id "
					."and meta_key = 'raffle_use' " 
					."and meta_value in ('1','2') ";
		        $post_id = $wpdb->get_var($sql);
				if(isset($post_id)){
					echo '<hr>';
					$raffle_use = get_post_meta( $post_id, 'raffle_use', true );
					if($raffle_use) {
						// TODO:残数が欲しい
						if(usces_is_login()){
							$member = $usces->get_member();
							$query = "SELECT * FROM wp_pl_raffle "
								."LEFT OUTER JOIN "
								."(SELECT raffle_id,count(*) as count FROM wp_usces_order,wp_pl_raffle_order "
								."WHERE mem_id = ".$member['ID'] ." AND wp_usces_order.ID= order_id "
								."AND post_id = $post_id GROUP BY raffle_id) as result "
								."ON wp_pl_raffle.ID = result.raffle_id "
								."WHERE post_id = $post_id "
								."ORDER BY prize , ID ";
						}else{
							$query = "SELECT * FROM wp_pl_raffle "
								."WHERE post_id = $post_id "
								."ORDER BY prize , ID ";
						}
						$results = $wpdb->get_results($wpdb->prepare($query),"ARRAY_A");
						//賞毎に詰め替える
						$prizelist = array();
						foreach($results as $result){
							if(!array_key_exists($result['prize'],$prizelist)){
								$prizelist[$result['prize']] = array($result);
							}else{
								$tmplist = $prizelist[$result['prize']];
								array_push($tmplist,$result);
								$prizelist[$result['prize']] = $tmplist;
							}
						}
            			echo '<article class="lottory-items design-pattern__group">';
						foreach($prizelist as $prize){
							$sumrate = 0;
							foreach($prize as $p){
								$sumrate += $p['rate'];
							}
							echo '<section class="award__'.$prize[0]['prize'].'">';
							echo '<div class="award--header">';
							echo '<p class="award--badge">'.$prize[0]['prize'].'賞</p>';
							echo '<p class="award--lineup">LINE UP</p>';
							if($raffle_use=='2'){
							echo '<p class="award--probability">当選確率<em>'.$sumrate.'</em>%</p>';
							}
							echo '</div>';
			                echo '<ul class="awards">';
							foreach($prize as $p){
								echo '<li>';
								echo '<figure class="award--image">';
								echo '<img src="'.$p['prize_image'].'"/>';
								echo '</figure>';
								echo '<p class="award--title">'.$p['prize_name'].'</p>';
								echo '<p class="award--infomation">';
								if($raffle_use=='1'){
									if($p['count'] != 0){
										echo '残 '.$p['count'].'個';
									}else{
										echo '売り切れ';
									}
								}else{
									echo ''.$p['rate'].'%';
								}
								echo '</p>';
								echo '</li>';
							}
							echo '</ul>';
							echo '</section>';
						}
						echo '</article>';
					}
				}
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
							<?php 
							if(count(wel_get_skus(get_the_ID()))==1){
							?>
								<div class="itemprice">
									<?php usces_the_firstPriceCr(); ?><?php usces_guid_tax(); ?>
								</div>
								<?php usces_crform_the_itemPriceCr_taxincluded(); ?>
							<?php } ?>
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
