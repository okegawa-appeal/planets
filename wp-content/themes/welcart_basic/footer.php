<?php
/**
 * Footer Template
 *
 * @package Welcart
 * @subpackage Welcart_Basic
 */

?>

	</div><!-- #main -->

	<?php if ( ! wp_is_mobile() ) : ?>

		<div id="toTop" class="wrap fixed"><a href="#masthead"><i class="fa fa-chevron-circle-up"></i></a></div>

	<?php endif; ?>

	<footer id="colophon" role="contentinfo">
		<nav id="site-info" class="footer-navigation">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'footer',
					'menu_class'     => 'footer-menu cf',
				)
			);
			?>
		</nav>

		<p class="copyright"><?php usces_copyright(); ?></p>

	</footer><!-- #colophon -->

	<?php wp_footer(); ?>
	</body>
</html>
