<?php
/**
 * Related Products
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $product, $woocommerce_loop;

if ( empty( $product ) || ! $product->exists() ) {
	return;
}

if( vu_get_option('related-products') != true ) {
	return;
}

$related = $product->get_related( $posts_per_page );

if ( sizeof( $related ) == 0 ) return;

$columns = (vu_get_option('shop-layout') == 'no-sidebar') ? 4 : 3; //Modified by Milingona Team

$args = apply_filters( 'woocommerce_related_products_args', array(
	'post_type'            => 'product',
	'ignore_sticky_posts'  => 1,
	'no_found_rows'        => 1,
	'posts_per_page'       => absint( vu_get_option('related-products-items-count', 4) ), //$posts_per_page,
	'orderby'              => $orderby,
	'post__in'             => $related,
	'post__not_in'         => array( $product->id )
) );

$products = new WP_Query( $args );

$woocommerce_loop['columns'] = $columns;

if ( $products->have_posts() ) : ?>
	<div class="clear"></div>

	<div class="related products">

		<header class="section-header alignment-center vu_overline">
			<h2><span><?php echo esc_html(vu_get_option('related-products-title')); ?></span></h2>
			<p><?php echo esc_html(vu_get_option('related-products-subtitle')); ?></p>
		</header>

		<?php woocommerce_product_loop_start(); ?>

			<?php 
				$carousel_options = array(
					"singleItem" => false,
					"items" => absint($columns),
					"itemsDesktop" => array(1199, $columns),
					"itemsDesktopSmall" => array(980, 3),
					"itemsTablet" => array(768, 2),
					"itemsMobile" => array(479, 1),
					"navigation" => false,
					"navigationText" => array('', ''),
					"pagination" => true,
					"autoHeight" => true,
					"rewindNav" => true,
					"scrollPerPage" => true
				);
			?>

			<div class="vu_wc-related-products vu_owl-carousel" data-options="<?php echo esc_attr( json_encode($carousel_options) ); ?>">

				<?php while ( $products->have_posts() ) : $products->the_post(); ?>

					<?php wc_get_template_part( 'content', 'product' ); ?>

				<?php endwhile; // end of the loop. ?>

			</div>

		<?php woocommerce_product_loop_end(); ?>

	</div>

<?php endif;

wp_reset_postdata();
