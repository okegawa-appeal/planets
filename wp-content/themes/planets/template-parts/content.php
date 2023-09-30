<?php
/**
 * Content Template
 *
 * @package Welcart
 * @subpackage Welcart_Basic
 */

?>

<article <?php post_class(); ?> id="post-<?php the_ID(); ?>">

	<header class="entry-header">
		<h1 class="entry-title"><?php the_title(); ?></h1>
	</header>

	<?php if ( is_single() ) : ?>
		<?php if ( ! usces_is_item() ) : ?>
		<?php endif; ?>
	<?php endif; ?>

	<div class="entry-content">
		<?php the_content( __( '(more...)' ) ); ?>
	</div><!-- .entry-content -->

</article>
