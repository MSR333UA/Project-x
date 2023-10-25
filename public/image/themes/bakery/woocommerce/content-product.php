<?php
/**
 * The template for displaying product content within loops
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woothemes.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 2.6.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $product, $woocommerce_loop, $vu_product_classes;

// Store loop count we're currently on
if ( empty( $woocommerce_loop['loop'] ) ) {
	$woocommerce_loop['loop'] = 0;
}

// Store column count for displaying the grid
if ( empty( $woocommerce_loop['columns'] ) ) {
	$woocommerce_loop['columns'] = apply_filters( 'loop_shop_columns', 4 );
}

// Ensure visibility
if ( ! $product || ! $product->is_visible() ) {
	return;
}

// Increase loop count
$woocommerce_loop['loop']++;

// Extra post classes
$classes = array();
if ( 0 === ( $woocommerce_loop['loop'] - 1 ) % $woocommerce_loop['columns'] || 1 === $woocommerce_loop['columns'] ) {
	$classes[] = 'first';
}
if ( 0 === $woocommerce_loop['loop'] % $woocommerce_loop['columns'] ) {
	$classes[] = 'last';
}

$classes[] = 'vu_product-item';

$vu_product_classes = !empty($vu_product_classes) ? ' '. $vu_product_classes : '';
?>

<div class="vu_product-item-container col-xs-12 col-sm-6 col-md-<?php echo absint(12 / $woocommerce_loop['columns']); ?><?php echo $vu_product_classes; ?>">
	<article <?php post_class( $classes ); ?> data-id="<?php echo get_the_ID(); ?>">

		<?php do_action( 'woocommerce_before_shop_loop_item' ); ?>

		<div class="vu_pi-image">
			<?php
				/**
				 * woocommerce_before_shop_loop_item_title hook
				 *
				 * @hooked woocommerce_show_product_loop_sale_flash - 10
				 * @hooked woocommerce_template_loop_product_thumbnail - 10
				 */
				do_action( 'woocommerce_before_shop_loop_item_title' );
			?>
		</div>

		<div class="vu_pi-container">
			<div class="vu_pi-icons">
				<a href="<?php echo vu_get_attachment_image_src( get_post_thumbnail_id(), 'full' ); ?>" title="<?php the_title(); ?>" class="vu_pi-icon vu_lightbox"><i class="fa fa-search"></i></a>
				<a href="<?php the_permalink(); ?>" class="vu_pi-icon"><i class="fa fa-link"></i></a>
				<?php
					/**
					 * woocommerce_after_shop_loop_item hook
					 *
					 * @hooked woocommerce_template_loop_add_to_cart - 10
					 */
					do_action( 'woocommerce_after_shop_loop_item' );
				?>
			</div>

			<div class="vu_pi-content">
				<h3 class="vu_pi-name"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
				<span class="vu_pi-category"><?php vu_product_terms( get_the_ID(), true, ', ', false, 'product_cat' ); ?></span>

				<?php
					/**
					 * woocommerce_after_shop_loop_item_title hook
					 *
					 * @hooked woocommerce_template_loop_rating - 5
					 * @hooked woocommerce_template_loop_price - 10
					 */
					do_action( 'woocommerce_after_shop_loop_item_title' );
				?>
			</div>
		</div>
	</article>
</div>