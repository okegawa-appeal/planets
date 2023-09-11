<?php
/**
 * Basic item list class
 *
 * @package Welcart
 */

/**
 * Basic_Item_List
 */
class Basic_Item_List extends WP_Widget {
	/**
	 * Construct
	 */
	public function __construct() {
		parent::__construct( false, $name = __( 'Welcart product list', 'welcart_basic' ) );
	}

	/**
	 * Widget
	 *
	 * @param array $args args.
	 * @param array $instance instance.
	 */
	public function widget( $args, $instance ) {

		$html    = '';
		$title   = empty( $instance['title'] ) ? '' : $instance['title'];
		$term_id = empty( $instance['term_id'] ) ? usces_get_cat_id( 'item' ) : $instance['term_id'];
		$number  = empty( $instance['number'] ) ? 10 : $instance['number'];

		echo wp_kses_post( $args['before_widget'] );
		if ( ! empty( $title ) ) {
			echo wp_kses_post( $args['before_title'] ) . esc_html( $title ) . wp_kses_post( $args['after_title'] );
		}

		$item_args  = array(
			'cat'            => $term_id,
			'posts_per_page' => $number,
		);
		$item_query = new WP_Query( $item_args );
		if ( $item_query->have_posts() ) {
			$html .= '<div class="item-list">' . "\n";
			while ( $item_query->have_posts() ) {
				$item_query->the_post();
				usces_the_item();
				$post_id           = get_the_ID();
				$list              = '<article id="post-' . $post_id . '">' . "\n";
					$list         .= '<a href="' . get_permalink( $post_id ) . '">' . "\n";
						$list     .= '<div class="itemimg">' . usces_the_itemImage( 0, 300, 300, '', 'return' ) . '</div>' . "\n";
						$list     .= '<div class="item-info-wrap"><div class="inner">' . "\n";
							$list .= '<div class="itemname">' . usces_the_itemName( 'return' ) . '</div>' . "\n";
							$list .= '<div class="itemprice">' . usces_the_firstPriceCr( 'return' ) . usces_guid_tax( 'return' ) . '</div>' . usces_crform_the_itemPriceCr_taxincluded( true, '', '', '', true, false, true, 'return' ) . get_welcart_basic_campaign_message() . "\n";
						$list     .= '</div></div>' . "\n";
					$list         .= '</a>' . "\n";
				$list             .= '</article>';
				$html             .= apply_filters( 'welcart_basic_filter_item_post', $list, $post_id );
			}
			wp_reset_postdata();
			$html .= '</div>' . "\n";
		}
		echo apply_filters( 'welcart_basic_filter_item_list', $html, $term_id, $number ); // phpcs:ignore
		echo wp_kses_post( $args['after_widget'] );
	}

	/**
	 * Form
	 *
	 * @param array $instance instance.
	 */
	public function form( $instance ) {
		$title        = empty( $instance['title'] ) ? '' : $instance['title'];
		$term_id      = empty( $instance['term_id'] ) ? usces_get_cat_id( 'item' ) : $instance['term_id'];
		$number       = empty( $instance['number'] ) ? 10 : $instance['number'];
		$target_arg   = array(
			'child_of' => usces_get_cat_id( 'item' ),
		);
		$target_terms = get_terms( 'category', $target_arg );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'term_id' ) ); ?>"><?php esc_html_e( 'Product category to show:', 'welcart_basic' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'term_id' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'term_id' ) ); ?>">
				<?php $selected = usces_get_cat_id( 'item' ) === (int) $term_id ? ' selected="selected"' : ''; ?>
				<option value="<?php echo esc_attr( usces_get_cat_id( 'item' ) ); ?>" <?php echo esc_attr( $selected ); ?>>
					<?php esc_html_e( 'Items', 'usces' ); ?>
				</option>
				<?php foreach ( $target_terms as $term ) : ?>
					<?php $selected = (int) $term_id === $term->term_id ? ' selected="selected"' : ''; ?>
					<option value="<?php echo esc_attr( $term->term_id ); ?>" <?php echo esc_attr( $selected ); ?>>
						<?php echo esc_attr( $term->name ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>"><?php esc_html_e( 'Number of posts to show:' ); ?></label>
			<input class="tiny-text" id="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'number' ) ); ?>" type="number" step="1" min="1" value="<?php echo esc_attr( $number ); ?>" size="3">
		</p>
		<?php
	}

	/**
	 * Update
	 *
	 * @param array $new_instance new_instance.
	 * @param array $old_instance old_instance.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance            = $old_instance;
		$instance['title']   = wp_strip_all_tags( $new_instance['title'] );
		$instance['term_id'] = wp_strip_all_tags( $new_instance['term_id'] );
		$instance['number']  = wp_strip_all_tags( $new_instance['number'] );
		return $instance;
	}
}
