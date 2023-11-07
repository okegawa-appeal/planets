<?php
/**
 * Template Name: CompanyReport
 *
 * @package Welcart
 * @subpackage Welcart_Basic
 */

get_header();
?>
        <style>
        #content table,#content td,#content th {
                border-collapse: collapse;
                border:1px solid #333;
                padding:2px;
        }
        </style>
        <div id="primary" class="site-content">
		<div id="content" role="main">
<?php

        global $wpdb;
                echo '<hr>';

        if(!empty($_GET['term_id'])){
                $term_id = $_GET['term_id'];
                $slug = $_GET['slug'];
                $orderby = (!empty($_GET['orderby'])) ? sanitize_text_field($_GET['orderby']) : 'id';
                $order = (!empty($_GET['order']) && in_array(strtoupper($_GET['order']), array('ASC', 'DESC'))) ? strtoupper($_GET['order']) : 'DESC';
                echo '<table>';
                $url = "?term_id=$term_id&slug=$slug&order=".($order=='DESC'?'ASC':'DESC');
                echo '<tr><th><a href="'.$url.'&orderby=item_name">商品名</a></th>';
                echo '<th><a href="'.$url.'&orderby=item_code">商品コード</a></th>';
                echo '<th><a href="'.$url.'&orderby=order_payment_name">支払い方法</a></th>';
                echo '<th><a href="'.$url.'&orderby=order_status">ステータス</a></th>';
                echo '<th><a href="'.$url.'&orderby=price">金額</a></th>';
                echo '<th><a href="'.$url.'&orderby=count">個数</a></th>';
                echo '</tr>';

                $sql = "select "
                ."item_name,item_code,order_payment_name,order_status,sum(price) as price,count(*) as count "
                ."FROM wp_term_relationships,wp_usces_ordercart,wp_usces_order,wp_terms "  
                ."where  "
                ." wp_term_relationships.term_taxonomy_id = $term_id " 
                ." and wp_term_relationships.term_taxonomy_id = wp_terms.term_id "
                ." and wp_terms.slug = '" . $slug ."'"
                ." and wp_usces_ordercart.post_id = wp_term_relationships.object_id "
                ." and wp_usces_order.ID= wp_usces_ordercart.order_id "
                ." group by item_name,item_code,order_payment_name,order_status ";


                $detail = $wpdb->get_results($sql . " ORDER BY $orderby $order ", ARRAY_A);
                        foreach ( (array) $detail as $detailitem ) {
                                echo '<tr><td>';
                                echo $detailitem['item_name'];
                                echo '</td><td>';
                                echo $detailitem['item_code'];
                                echo '</td><td>';
                                echo $detailitem['order_payment_name'];
                                echo '</td><td>';
                                $order_status = $detailitem['order_status'];
                                $order_status = str_replace( 'duringorder', '取り寄せ中', $order_status);
                                $order_status = str_replace( 'noreceipt', '未入金', $order_status);
                                $order_status = str_replace( 'receipted', '入金済み', $order_status);
                                $order_status = str_replace( 'completion', '発送済み', $order_status);
                                $order_status = str_replace( 'cancel', 'キャンセル', $order_status);
                                echo $order_status;
                                echo '</td><td>';
                                echo "¥".number_format($detailitem['price'],0);
                                echo '</td><td>';
                                echo $detailitem['count'];
                                echo '</td></tr>';
                        }
                        echo '</table>';

                $sql = "select "
                ."sum(price) as price "
                ."FROM wp_term_relationships,wp_usces_ordercart,wp_terms  "
                ."where   "
                ."wp_term_relationships.term_taxonomy_id = $term_id  "
                ."and wp_term_relationships.term_taxonomy_id = wp_terms.term_id "
                ."and wp_terms.slug = '" . $slug ."'"
                ."and wp_usces_ordercart.post_id = wp_term_relationships.object_id  ";
                $total = $wpdb->get_results($sql , ARRAY_A);
                echo '<hr>';
                echo '合計: ' . "¥".number_format($total[0]['price'],0);
        }

?>
		</div><!-- #content -->
	</div><!-- #primary -->

<?php
get_footer();
