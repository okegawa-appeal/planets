<?php
/**
 * Header Template
 *
 * @package Welcart
 * @subpackage Welcart_Basic
 */

get_header();
global $wpdb;
$query = "select * FROM wp_pl_event where type = '1' and open = '1'  order by ord desc";
$events = $wpdb->get_results($wpdb->prepare($query),"ARRAY_A");

?> 
	<div id="primary" class="site-content">
		<div class="content-title">EVENT</div>
		<div id="content" role="main">
			<ul class="category-container">
<?php 
foreach ($events as $row){  
	//var_dump($row);
	echo '<li class="category-item"><a href="' . $row["url"] . '">';
	echo '<div><img src="' . $row["image"] . '" class="category-image"></div>';
	echo '<div class="category-actor">' . $row["talent"] . '</div>';
	echo '<div class="category-start">' . $row["event_start"] . '</div>';
	echo '<div class="category-title">' . $row["title"] . '</div>';
	echo '<span class="reserve">' . $row["reserve_start"] . '</span>';
	echo '</a></li>';
}
?>
			</ul>
		</div><!-- #content -->
		<div class="content-title">GOODS</div>
		<div id="content" role="main">
			<ul class="category-container">
<?php 
global $wpdb;
$query = "select * FROM wp_pl_event where type = '2' and open = '1' order by ord desc";
$goods = $wpdb->get_results($wpdb->prepare($query),"ARRAY_A");
foreach ($goods as $row){  
	//var_dump($row);
	echo '<li class="category-item"><a href="' . $row["url"] . '">';
	echo '<div><img src="' . $row["image"] . '" class="category-image"></div>';
	echo '<div class="category-actor">' . $row["talent"] . '</div>';
	echo '<div class="category-title">' . $row["title"] . '</div>';
	echo '</a></li>';
}
?>
		</div>
		<div class="content-title">NEWS</div>
		<div class="announce_container">
			<div class="announce_news">
				<ul>
<?php
$args = array(
	'numberposts'	=> 20,
	'category'		=> 9
);
$postslist = get_posts( $args );
if( ! empty( $postslist ) ){
	foreach ( $postslist as $p ){
		echo '<li><a href="' . get_permalink( $p->ID ) . '">';
		echo '<p class="annouce_news_date">'.date('Y/m/d',strtotime($p->post_modified)).'</p>';
		echo '<p class="annouce_news_title">'.$p->post_title.'</p>';
		echo '</a></li>';
	}
}
?>
				</ul>
			</div>
			<div class="announce_twitter">
				<!-- a class="twitter-timeline" data-lang="ja" data-height="400" href="https://twitter.com/MUVUS_oshirase?ref_src=twsrc%5Etfw">Tweets by MUVUS_oshirase</a> <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script -->
			</div>
		</div>
		<div>
			&nbsp;
		</div>
	</div><!-- #primary -->
<?php
//get_sidebar( 'home' );
get_footer();
