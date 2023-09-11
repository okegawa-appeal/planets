<?php
/**
 * Page Tempalte
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

				get_template_part( 'template-parts/content', get_post_format() );

				$defaults = array(
					'before'         => '<div class="link-pages">',
					'after'          => '</div>',
					'link_before'    => '',
					'link_after'     => '',
					'next_or_number' => 'number',
					'separator'      => ' ',
					'echo'           => 1,
				);
				wp_link_pages( $defaults );

				posts_nav_link( ' &#8212; ', __( '&laquo; Newer Posts' ), __( 'Older Posts &raquo;' ) );

			endwhile;
		else :
			?>

			<p><?php esc_html_e( 'Sorry, no posts matched your criteria.', 'usces' ); ?></p>

			<?php
		endif;
		?>

		</div><!-- #content -->
	</div><!-- #primary -->

<?php
get_footer();
