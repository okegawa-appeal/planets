<?php
/**
 * Header Template
 *
 * @package Welcart
 * @subpackage Welcart_Basic
 */

get_header();
global $wpdb;
$query = 'select * FROM wp_pl_event order by ord';
$results = $wpdb->get_results($wpdb->prepare($query),"ARRAY_A");

?> 
	<div id="primary" class="site-content">
		<div class="content-title">LINE UP</div>
		<div id="content" role="main">
			<ul class="category-container">
<?php 
foreach ($results as $row){  
	//var_dump($row);
	echo '<li class="category-item"><a href="' . $row["url"] . '">';
	echo '<div><img src="' . $row["image"] . '" class="category-image"></div>';
	echo '<div class="category-title">' . $row["title"] . '</div><span class="reserve">9/20</span>';
	echo '</a></li>';
}
?>
<?php 
foreach ($results as $row){  
	//var_dump($row);
	echo '<li class="category-item"><a href="' . $row["url"] . '">';
	echo '<div><img src="' . $row["image"] . '" class="category-image"></div>';
	echo '<div class="category-title">' . $row["title"] . '</div><span class="onsale">受付中</span>';
	echo '</a></li>';
}
?>
<?php 
foreach ($results as $row){  
	//var_dump($row);
	echo '<li class="category-item"><a href="#">';
	echo '<div><img src="' . $row["image"] . '" class="category-image"></div>';
	echo '<div class="category-title">' . $row["title"] . '</div><span class="finish">終了</span>';
	echo '</a></li>';
}
?>
			</ul>
		</div><!-- #content -->
	</div><!-- #primary -->

<?php
//get_sidebar( 'home' );
get_footer();
