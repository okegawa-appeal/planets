<?php
/**
 * Sidebar Other Tenplate
 *
 * @package Welcart
 * @subpackage Welcart_Basic
 */

?>

<aside id="secondary" class="widget-area" role="complementary">

	<?php
	if ( ! dynamic_sidebar( 'side-widget-area2' ) ) :

		// Default Welcart Calendar Widget.
		$args             = array(
			'before_widget' => '<section id="welcart_calendar-3" class="widget widget_welcart_calendar">',
			'after_widget'  => '</section>',
			'before_title'  => '<h3 class="widget_title">',
			'after_title'   => '</h3>',
		);
		$welcart_calendar = array(
			'title' => __( 'Business Calendar', 'usces' ),
			'icon'  => 1,
		);
		the_widget( 'Welcart_calendar', $welcart_calendar, $args );

	endif;
	?>

</aside><!-- #secondary -->
