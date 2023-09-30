<?php
/**
 * Index Template
 *
 * @package Welcart
 * @subpackage Welcart_Basic
 */

get_header();
?>

	<div id="primary" class="site-content">
		<div id="content" role="main">
		<?php
		if ( have_posts() ) :
			while ( have_posts() ) :
				the_post();
				?>
			<article <?php post_class(); ?> id="post-<?php the_ID(); ?>">
				<h2 class="entry-title"><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h2>
				<div class="entry-content">
					<?php the_content(); ?>
				</div>
			</article>
					<?php endwhile; else : ?>
			<p><?php esc_html_e( 'Sorry, no posts matched your criteria.', 'usces' ); ?></p>
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
	</div><!-- #primary -->


<?php
get_sidebar( 'home' );
get_footer();
