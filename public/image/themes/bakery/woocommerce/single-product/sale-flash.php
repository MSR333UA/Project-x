<?php
/**
 * Single Product Sale Flash
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $post, $product;

?>
<?php if ( $product->is_on_sale() ) : ?>

	<?php echo apply_filters( 'woocommerce_sale_flash', '<div class="vu_pi-label-container"><div class="vu_pi-label">
' . __( 'Sale!', 'woocommerce' ) . '</div><div class="vu_pi-label-bottom"></div></div>', $post, $product ); ?>

<?php endif; ?>
