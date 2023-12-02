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
                if(isset($open) && $open == 1){
                        echo '<li ><a href="?open=0">未開封</a></li>';
                        echo '<li class="active"><a href="ottery?open=1">開封履歴</a></li>';
                        echo '<li ><a href="?open=2">決済待ち</a></li>';
                }else if(isset($open) && $open == 2){
                        echo '<li ><a href="?open=0">未開封</a></li>';
                        echo '<li ><a href="?open=1">開封履歴</a></li>';
                        echo '<li class="active"><a href="?open=2">決済待ち</a></li>';
                }else{
                        echo '<li class="active"><a href="?open=0">未開封</a></li>';
                        echo '<li ><a href="?open=1">開封履歴</a></li>';
                        echo '<li ><a href="?open=2">決済待ち</a></li>';
                }
                ?>
                </ul>
                </div>
                <?php
                if(isset($open) && $open == 1){
                        $sql = "select  wp_pl_raffle.*,wp_pl_raffle_order.open,wp_usces_order.order_status,order_id,wp_pl_raffle_order.ID as raffle_order_id "
                        ."FROM wp_pl_raffle_order , wp_usces_order , wp_pl_raffle "
                        ."where wp_pl_raffle_order.order_id = wp_usces_order.ID  "
                        ."and wp_usces_order.mem_id = $mem_id "
                        ."and wp_pl_raffle.ID = wp_pl_raffle_order.raffle_id "
                        ."and wp_pl_raffle_order.open = true "
                        ."and order_status like '%receipted%' "
                        ."and order_status not like '%cancel%' "
                        ."order by wp_pl_raffle_order.order_date desc" ;

                        $results = $wpdb->get_results($sql,ARRAY_A);
                        echo '<div class="js-hide-contents">';
                        echo '<div class="lottery-container">';
                        echo '<ul>';
                        foreach ( (array) $results as $row ) {
                                echo '<li>';
                                echo '<p class="lottery-ribbon">開封済</p>';
                                echo '<div>'.$row['prize'].'賞</div>';
                                echo '<div><img src="'. $row['prize_image'] .'" width=100px></div>';
                                echo '<div>'.$row['prize_name'].'</div>';
                                if (false !== strpos($row['order_status'], 'completion')) {
                                        echo '<div>発送済み</div>';
                                }else{
                                        echo '<div>未発送</div>';
                                }
                                echo '</li>';
                        }
                        echo '</ul>';
                        echo '</div>';
                        echo '</div>';
                }else if(isset($open) && $open == 2){
                        $sql = "select  wp_pl_raffle.*,wp_pl_raffle_order.open,wp_usces_order.order_status,order_id,wp_pl_raffle_order.ID as raffle_order_id "
                        ."FROM wp_pl_raffle_order , wp_usces_order , wp_pl_raffle "
                        ."where wp_pl_raffle_order.order_id = wp_usces_order.ID  "
                        ."and wp_usces_order.mem_id = $mem_id "
                        ."and wp_pl_raffle.ID = wp_pl_raffle_order.raffle_id "
                        ."and wp_pl_raffle_order.open = false "
                        ."and order_status like '%noreceipt%' "
                        ."and order_status not like '%cancel%' "
                        ."order by RAND()" ;

                        $results = $wpdb->get_results($sql,ARRAY_A);
                        echo '<div class="js-hide-contents">';
                        echo '<div class="lottery-container">';
                        echo '<ul>';
                        foreach ( (array) $results as $row ) {
                                echo '<li><a>';
                                echo '<p class="lottery-ribbon">未開封</p>';
                                echo '<div><img src="'. get_stylesheet_directory_uri().'/images/lotteryimage.png" width=100px></div>';
                                echo '<div>未開封くじ(決済待ち)</div>';
                                echo '</a></li>';
                        }
                        echo '</ul>';
                        echo '</div>';
                        echo '</div>';
                }else{
                        $sql = "select  wp_pl_raffle.*,wp_pl_raffle_order.open,wp_usces_order.order_status,order_id,wp_pl_raffle_order.ID as raffle_order_id "
                        ."FROM wp_pl_raffle_order , wp_usces_order , wp_pl_raffle "
                        ."where wp_pl_raffle_order.order_id = wp_usces_order.ID  "
                        ."and wp_usces_order.mem_id = $mem_id "
                        ."and wp_pl_raffle.ID = wp_pl_raffle_order.raffle_id "
                        ."and wp_pl_raffle_order.open = false "
                        ."and order_status like '%receipted%' "
                        ."and order_status not like '%cancel%' "
                        ."order by RAND()" ;

                        $results = $wpdb->get_results($sql,ARRAY_A);
                        if(count($results)>0){
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
                                        echo '<p class="lottery-ribbon">開封済</p>';
                                        echo '<div class="lottery-image">';
                                        echo '<img src="'.get_stylesheet_directory_uri().'/images/lotteryimage.png" />';
                                        echo '</div>';
                                        echo '<p class="lottery-title">未開封くじ'.$row['prize'].'<br />2023/12/02</p>';
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
                                                <div class="award--close hide"></div>
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

