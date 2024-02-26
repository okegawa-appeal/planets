<?php
/**
 * Template Name: premium
 *
 * @package Welcart
 * @subpackage Welcart_Basic
 */
get_header('premium');
global $wpdb;
$query = "select * FROM wp_pl_event where type = '1' and open = '1' and genre = 1 order by ord desc";
$events = $wpdb->get_results($wpdb->prepare($query),"ARRAY_A");
$current_datetime = new DateTime("now", new DateTimeZone("JST"));  // 例: Current_datetimeから取得したUTC日付

?> 
	<div id="primary" class="site-content">
		<div class="content-title">EVENT</div>
		<div id="content" role="main">
			<ul class="category-container">
<?php 
foreach ($events as $row){  
	//var_dump($row);

	if (!empty($row["reserve_start"]) && !empty($row["reserve_start_time"])) {
        $target_datetime = new DateTime($row["reserve_start"] . ' ' . $row["reserve_start_time"], new DateTimeZone("Asia/Tokyo"));
        if ($target_datetime > $current_datetime) {
			//未来
            //echo '<span class="reserve">' . $row["reserve_start"] . '</span>';
        } else {
			echo '<li class="category-item"><a href="' . $row["url"] . '">';
			echo '<div><img src="' . $row["image"] . '" class="category-image"></div>';
			echo '<div class="category-actor">' . $row["talent"] . '</div>';
			echo '<div class="category-start">' . $row["event_start"] . '</div>';
			echo '<div class="category-title">' . $row["title"] . '</div>';
        }
	}

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
$query = "select * FROM wp_pl_event where type = '2' and open = '1' and genre = 1 order by ord desc";
$goods = $wpdb->get_results($wpdb->prepare($query),"ARRAY_A");
foreach ($goods as $row){  
	//var_dump($row);
	if (!empty($row["reserve_start"]) && !empty($row["reserve_start_time"])) {
        $target_datetime = new DateTime($row["reserve_start"] . ' ' . $row["reserve_start_time"], new DateTimeZone("Asia/Tokyo"));

        if ($target_datetime > $current_datetime) {
			//未来
            //echo '<span class="reserve">' . $row["reserve_start"] . '</span>';
        } else {
			echo '<li class="category-item"><a href="' . $row["url"] . '">';
			echo '<div><img src="' . $row["image"] . '" class="category-image"></div>';
			echo '<div class="category-actor">' . $row["talent"] . '</div>';
			echo '<div class="category-title">' . $row["title"] . '</div>';
			echo '</a></li>';
		}
	}
}
?>
		</div>
		<div>
			&nbsp;
		</div>
	</div><!-- #primary -->
<?php
//get_sidebar( 'home' );
get_footer();
