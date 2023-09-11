<?php 
/*****************************************************************
ヘッダ画像のslickによるスライド
引数 $type     三画面表示するか否か　デフォルトは一画面表示
       $arrows 矢印を表示するか否か　デフォルトは表示しない
******************************************************************/
function my_header_img_slic($type=1,$arrows=false)
{
	$attr = 'type='.$type.' arrows='.$arrows;    /* ①jqueryに属性と値をセットして渡す */
?>
<div id='main-image' <?php echo $attr;?>>     
	<?php foreach (get_uploaded_header_images() as $key => $value): ?>
	    <img src='<?php echo $value["url"];?>' alt="<?php bloginfo('name'); ?>" >
	<?php endforeach;?>
</div><!-- main-image -->

<!-- スタイル設定 ---------------------------->
<style>
#main-image{
	margin:0 auto; /* PCの左右余白 */
	width:1000px;  /* PCの画像枠は固定 */
}
#main-image img{
	width:100%;
}
@media screen and (max-width:767px){
	#main-image{
		margin:0;			/* スマホの左右余白 */
		width:100%;	
	}
}
/* slick-dotsの位置の変更 */
#main-image .slick-dots{     
	position: absolute;
	bottom:5px;          /* デフォルトは画像の下25pxを左記に変更 */
}
/* slick-dotsのカラー変更 */
#main-image .slick-dots li button:before{  
	position: absolute;
	color:#fff;          /*ドットを画像の中にしたので白に変更 */
	opacity:1;
}
#main-image .slick-dots li.slick-active button:before{
	opacity: .5;
	color: #fff;
}
.slick-prev:before,
.slick-next:before {
    color: #000;
}
</style>

<?php
}
?>
