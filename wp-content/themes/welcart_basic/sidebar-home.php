<?php
/**
 * Sidebar Home Template
 *
 * @package Welcart
 * @subpackage Welcart_Basic
 */

?>

<aside id="secondary" class="widget-area" role="complementary">

	<div class="columnleft">
	<?php
	if ( ! dynamic_sidebar( 'left-widget-area' ) ) :

		// Default Welcart Login Widget.
		if ( usces_is_membersystem_state() ) :
			$args          = array(
				'before_widget' => '<section id="welcart_login-3" class="widget widget_welcart_login">',
				'after_widget'  => '</section>',
				'before_title'  => '<h3 class="widget_title">',
				'after_title'   => '</h3>',
			);
			$welcart_login = array(
				'title' => __( 'Log-in', 'usces' ),
				'icon'  => 1,
			);
			the_widget( 'Welcart_login', $welcart_login, $args );
		endif;


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

	endif;
	?>
	</div>

	<div class="columncenter">
	<?php
	if ( ! dynamic_sidebar( 'center-widget-area' ) ) :

		// Default Welcart Recommend Widget.
		$args = array(
			'before_widget' => '<section id="welcart_featured-3" class="widget widget_welcart_featured">',
			'after_widget'  => '</section>',
			'before_title'  => '<h3 class="widget_title">',
			'after_title'   => '</h3>',
		);
		global $wp_filter;
		if ( array_key_exists( 'usces_filter_featured_widget', $wp_filter ) ) {
			$num = 5;
		} else {
			$num = 2;
		}
		$welcart_featured = array(
			'title' => __( 'Items recommended', 'usces' ),
			'icon'  => 1,
			'num'   => $num,
		);
		the_widget( 'Welcart_featured', $welcart_featured, $args );

	endif;
	?>
	</div>

	<div class="columnright">
		<?php
		if ( ! dynamic_sidebar( 'right-widget-area' ) ) :

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
	</div>

</aside><!-- #secondary -->
