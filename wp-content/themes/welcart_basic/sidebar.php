<?php
/**
 * Sidebar Tempalate
 *
 * @package Welcart
 * @subpackage Welcart_Basic
 */

?>

<aside id="secondary" class="widget-area" role="complementary">

	<?php
	if ( ! dynamic_sidebar( 'side-widget-area1' ) ) :

		// Default Welcart Category Widget.
		$args             = array(
			'before_widget' => '<section id="welcart_category-3" class="widget widget_welcart_category">',
			'after_widget'  => '</section>',
			'before_title'  => '<h3 class="widget_title">',
			'after_title'   => '</h3>',
		);
		$welcart_category = array(
			'title'    => __( 'Item Category', 'usces' ),
			'icon'     => 1,
			'cat_slug' => 'itemgenre',
		);
		the_widget( 'Welcart_category', $welcart_category, $args );

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
