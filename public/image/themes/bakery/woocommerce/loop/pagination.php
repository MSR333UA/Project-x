<?php
/**
 * Pagination - Show numbered pagination for catalog pages.
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.2.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $wp_query;

if ( $wp_query->max_num_pages <= 1 ) {
	return;
}
?>
<!-- <nav class="woocommerce-pagination"> -->
	<div class="text-center"><div class="pagination">
	<?php
		echo paginate_links( apply_filters( 'woocommerce_pagination_args', array(
			'base'         => esc_url_raw( str_replace( 999999999, '%#%', remove_query_arg( 'add-to-cart', get_pagenum_link( 999999999, false ) ) ) ),
			'format'       => '',
			'add_args'     => '',
			'current'      => max( 1, get_query_var( 'paged' ) ),
			'total'        => $wp_query->max_num_pages,
			'prev_text'    => '<div class="pagination-item pagination-nav">'. __('prev', 'bakery') .'</div>',
			'next_text'    => '<div class="pagination-item pagination-nav">'. __('next', 'bakery') .'</div>',
			'type'         => 'plain',
			'end_size'     => 3,
			'mid_size'     => 3,
			'before_page_number' => '<div class="pagination-item">',
			'after_page_number' => '</div>'
		) ) );
	?>
	</div></div>
<!-- </nav> -->
<?php //vu_pagination($products); ?>