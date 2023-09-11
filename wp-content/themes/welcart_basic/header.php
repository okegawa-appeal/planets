<?php
/**
 * Header Template
 *
 * @package Welcart
 * @subpackage Welcart_Basic
 */

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, user-scalable=no">
	<meta name="format-detection" content="telephone=no"/>

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

	<?php wp_body_open(); ?>

	<header id="masthead" class="site-header" role="banner">

	<div class="inner cf">

			<p class="site-description"><?php bloginfo( 'description' ); ?></p>
			<?php if ( is_home() || is_front_page() ) : ?>
				<h1 class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
			<?php else : ?>
				<div class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></div>
			<?php endif; ?>

			<?php if ( ! welcart_basic_is_cart_page() ) : ?>

			<div class="snav cf">
				<div class="search-box">
					<i class="fa fa-search"></i>
					<?php get_head_search_form(); ?>
				</div>

				<?php if ( usces_is_membersystem_state() ) : ?>
					<div class="membership">
						<i class="fa fa-user"></i>
						<ul class="cf">
							<?php do_action( 'usces_theme_action_membersystem_before' ); ?>
							<?php if ( usces_is_login() ) : ?>
								<li><?php printf( __( 'Hello %s', 'usces' ), usces_the_member_name( 'return' ) ); // phpcs:ignore ?></li>
								<li><a href="<?php echo esc_url( USCES_MEMBER_URL ); ?>"><?php esc_html_e( 'My page', 'welcart_basic' ); ?></a></li>
								<?php do_action( 'usces_theme_login_menu' ); ?>
								<li><?php usces_loginout(); ?></li>
							<?php else : ?>
								<li><?php esc_html_e( 'guest', 'usces' ); ?></li>
								<li><?php usces_loginout(); ?></li>
								<li><a href="<?php echo esc_url( USCES_NEWMEMBER_URL ); ?>"><?php esc_html_e( 'New Membership Registration', 'usces' ); ?></a></li>
							<?php endif; ?>
							<?php do_action( 'usces_theme_action_membersystem_after' ); ?>
						</ul>
					</div>
				<?php endif; ?>

				<div class="incart-btn">
					<a href="<?php echo esc_url( USCES_CART_URL ); ?>">
						<i class="fa fa-shopping-cart"><span><?php esc_html_e( 'In the cart', 'usces' ); ?></span></i>
						<?php if ( ! defined( 'WCEX_WIDGET_CART' ) ) : ?>
							<span class="total-quant"><?php usces_totalquantity_in_cart(); ?></span>
						<?php endif; ?>
					</a>
				</div>
			</div><!-- .snav -->

			<?php endif; ?>

		</div><!-- .inner -->

		<?php if ( ! welcart_basic_is_cart_page() ) : ?>

			<nav id="site-navigation" class="main-navigation" role="navigation">
				<label for="panel"><span></span></label>
				<input type="checkbox" id="panel" class="on-off" />
				<?php
					wp_nav_menu(
						array(
							'theme_location'  => 'header',
							'container_class' => 'nav-menu-open',
							'menu_class'      => 'header-nav-container cf',
						)
					);
				?>
			</nav><!-- #site-navigation -->

		<?php endif; ?>
	</header><!-- #masthead -->

	<?php if ( ( is_front_page() || is_home() ) && get_header_image() ) : ?>
		桶川さん上
		<div class="main-image">
			<img src="<?php header_image(); ?>" width="<?php echo esc_attr( get_custom_header()->width ); ?>" height="<?php echo esc_attr( get_custom_header()->height ); ?>" alt="<?php bloginfo( 'name' ); ?>">
		</div><!-- main-image -->
	<?php endif; ?>

	<?php
	if ( is_front_page() || is_home() || welcart_basic_is_cart_page() || welcart_basic_is_member_page() ) {
		$class = 'one-column';
	} else {
		$class = 'two-column right-set';
	}
	?>

	<div id="main" class="wrapper <?php echo esc_attr( $class ); ?>">
