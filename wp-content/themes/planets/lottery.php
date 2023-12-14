<?php
/**
 * Template Name: lottery
 *
 * @package Welcart
 * @subpackage Welcart_Basic
 */

get_header();
$mem_id = usces_memberinfo('ID','return');
global $wpdb;
$open = $_GET['open'];
?>
        <div id="primary" class="site-content page-lottery">
		<div id="content" role="main">
                <div class="lottery-mainvisual" id="MainVisual">
                <div class="mv-contents">
                      <p>PLANET<span>S</span>くじ結果</p>
                </div>
                <ul>
                <?php
                echo '<li '. ((!isset($open) || $open == 0) ? 'class="active"' : '').'><a href="?open=0">未開封</a></li>';
                echo '<li '. ((isset($open) && $open == 1 ) ? 'class="active"' : '').'><a href="?open=1">開封済み</a></li>';
                echo '<li '. ((isset($open) && $open == 2) ? 'class="active"' : '').'><a href="?open=2">決済待ち</a></li>';
                ?>
                </ul>
                </div>
                <?php
                //開封済み
                if(isset($open) && $open == 1){
                        $per_page = 20;
                        $current_page = get_query_var('paged')?:1;
                        $offset = ($current_page - 1) * $per_page;

                        $sqlcount = "select count(*) "
                        ."FROM wp_pl_raffle_order , wp_usces_order , wp_pl_raffle,wp_usces_item "
                        ."where wp_pl_raffle_order.order_id = wp_usces_order.ID  "
                        ."and wp_usces_order.mem_id = $mem_id "
                        ."and wp_pl_raffle.post_id = wp_usces_item.post_id "
                        ."and wp_pl_raffle.ID = wp_pl_raffle_order.raffle_id "
                        ."and wp_pl_raffle_order.open = true "
                        ."and order_status like '%receipted%' "
                        ."and order_status not like '%cancel%' "
                        ."order by wp_pl_raffle_order.order_date desc ";
                        $total_count = $wpdb->get_var($sqlcount);

                        $sql = "select  wp_pl_raffle.*,wp_pl_raffle_order.open,wp_usces_order.order_status,order_id,wp_pl_raffle_order.ID as raffle_order_id,itemName "
                        ."FROM wp_pl_raffle_order , wp_usces_order , wp_pl_raffle,wp_usces_item "
                        ."where wp_pl_raffle_order.order_id = wp_usces_order.ID  "
                        ."and wp_usces_order.mem_id = $mem_id "
                        ."and wp_pl_raffle.post_id = wp_usces_item.post_id "
                        ."and wp_pl_raffle.ID = wp_pl_raffle_order.raffle_id "
                        ."and wp_pl_raffle_order.open = true "
                        ."and order_status like '%receipted%' "
                        ."and order_status not like '%cancel%' "
                        ."order by wp_pl_raffle_order.order_date desc "
                        ."LIMIT $per_page OFFSET $offset " ;
                        $results = $wpdb->get_results($sql,ARRAY_A);
                        echo '<div class="pagination">';
                        echo paginate_links(array(
                                'total' => ($total_count / $per_page) + 1,
                                'current' => $current_page,
                                'prev_text' => FALSE,
                        ));
                        echo '</div>';
                        echo '<div class="js-hide-contents">';
                        echo '<div class="lottery-container">';
                        echo '<ul>';
                        foreach ( (array) $results as $row ) {
                                echo '<li><button class="award-status-unopen">';
                                if (false !== strpos($row['order_status'], 'completion')) {
                                        echo '<p class="lottery-ribbon">発送済</p>';
                                }
                                echo '<div class="lottery-image">';
                                echo '<img src="'. $row['prize_image'] .'" >';
                                echo '</div>';
                                echo '<p class="lottery-title">'.$row['itemName'].' </p>';
                                echo '<p class="lottery-title">'.$row['prize'].'賞 ' .$row['prize_name'].' </p>';
                                echo '</button></li>';
                        }
                        echo '</ul>';
                        echo '</div>';
                        echo '</div>';
                        echo '<div class="pagination">';
                        echo paginate_links(array(
                                'total' => ($total_count / $per_page) + 1,
                                'current' => $current_page,
                                'prev_text' => FALSE,
                        ));
                        echo '</div>';
                //未開封
                }else if(isset($open) && $open == 2){
                        $sql = "select  wp_pl_raffle.*,wp_pl_raffle_order.open,wp_usces_order.order_status,order_id,wp_pl_raffle_order.ID as raffle_order_id,wp_pl_raffle_order.order_date,itemName "
                        ."FROM wp_pl_raffle_order , wp_usces_order , wp_pl_raffle,wp_usces_item "
                        ."where wp_pl_raffle_order.order_id = wp_usces_order.ID  "
                        ."and wp_usces_order.mem_id = $mem_id "
                        ."and wp_pl_raffle.post_id = wp_usces_item.post_id "
                        ."and wp_pl_raffle.ID = wp_pl_raffle_order.raffle_id "
                        ."and wp_pl_raffle_order.open = false "
                        ."and order_status like '%noreceipt%' "
                        ."and order_status not like '%cancel%' "
                        ."order by wp_pl_raffle_order.ID ASC ";

                        $results = $wpdb->get_results($sql,ARRAY_A);
                        echo '<div class="js-hide-contents">';
                        echo '<div class="lottery-container">';
                        echo '<ul>';
                        foreach ( (array) $results as $row ) {
                                echo '<li><button class="award-status-unopen">';
                                echo '<div class="lottery-image">';
                                echo '<img src="'. get_stylesheet_directory_uri().'/images/lotteryimage.png">';
                                echo '</div>';
                                echo '<p class="lottery-title">'.$row['itemName'].' </p>';
                                echo '<p class="lottery-title">'.$row['order_date'].' 購入</p>';
                                echo '</button></li>';
                        }
                        echo '</ul>';
                        echo '</div>';
                        echo '</div>';
                }else{
                        $sql = "select  wp_pl_raffle.*,wp_pl_raffle_order.open,wp_usces_order.order_status,order_id,wp_pl_raffle_order.ID as raffle_order_id,wp_pl_raffle_order.order_date,itemName "
                        ."FROM wp_pl_raffle_order , wp_usces_order , wp_pl_raffle,wp_usces_item "
                        ."where wp_pl_raffle_order.order_id = wp_usces_order.ID  "
                        ."and wp_usces_order.mem_id = $mem_id "
                        ."and wp_pl_raffle.post_id = wp_usces_item.post_id "
                        ."and wp_pl_raffle.ID = wp_pl_raffle_order.raffle_id "
                        ."and wp_pl_raffle_order.open = false "
                        ."and order_status like '%receipted%' "
                        ."and order_status not like '%cancel%' "
                        ."order by RAND()" ;

                        $results = $wpdb->get_results($sql,ARRAY_A);
                        if(count($results)>0){
                                echo '<a href="?open=1">演出をSKIPして全て開封する</a><br>';
                                echo '<div class="js-hide-contents">';
                                echo '<div class="lottery-container">';
                                echo '<ul>';
                                foreach ( (array) $results as $row ) {
                                        echo '<li><button class="start-award award-status-unopen" ';
                                        echo 'data-index="'.$row['raffle_order_id'].'" ';
                                        echo 'data-order="'.$row['order_id'].'" ';
                                        echo 'data-award="'.$row['prize'].'" ';
                                        echo 'data-image="'.$row['prize_image'].'" ';
                                        echo 'data-description="'.$row['prize_name'].'" ';
                                        echo '>';
                                        echo '<p class="lottery-ribbon">開封!!</p>';
                                        echo '<div class="lottery-image">';
                                        echo '<img src="'.get_stylesheet_directory_uri().'/images/lotteryimage.png" />';
                                        echo '</div>';
                                        echo '<p class="lottery-title">'.$row['itemName'].' </p>';
                                        echo '<p class="lottery-title">'.$row['order_date'].' 購入</p>';
                                        echo '</button></li>';
                                }
                                ?>

                                </ul>
                                </div>
                                </div>
                                <div id="Award-box">
                                        <div id="Sky"></div>
                                        <div id="SkyInner">
                                                <div class="night">
                                                <div class="shooting_star"></div>
                                                <div class="shooting_star"></div>
                                                <div class="shooting_star"></div>
                                                <div class="shooting_star"></div>
                                                <div class="shooting_star"></div>
                                                <div class="shooting_star"></div>
                                                <div class="shooting_star"></div>
                                                <div class="shooting_star"></div>
                                                <div class="shooting_star"></div>
                                                <div class="shooting_star"></div>
                                                <div class="shooting_star"></div>
                                                <div class="shooting_star"></div>
                                                <div class="shooting_star"></div>
                                                <div class="shooting_star"></div>
                                                <div class="shooting_star"></div>
                                                <div class="shooting_star"></div>
                                                <div class="shooting_star"></div>
                                                <div class="shooting_star"></div>
                                                <div class="shooting_star"></div>
                                                <div class="shooting_star"></div>
                                                </div>
                                        </div>
                                        <div id="Stars"></div>
                                        <div id="wrapper">
                                                <div class="award" >
                                                <div class="award--badge hide"></div>
                                                <div class="award--photo hide"></div>
                                                <div class="award--infomation hide"></div>
                                                </div>
                                        </div>
                                </div>
                      <?php
                        }
                }
                ?>
                </div><!-- #content -->
	</div><!-- #primary -->

<?php
get_footer();

