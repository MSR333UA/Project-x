<?php 
	// Change WC loop product product item - Square size (Ratio 1:1)
	if( !function_exists('vu_woocommerce_template_loop_product_thumbnail_square') ){
		function vu_woocommerce_template_loop_product_thumbnail_square(){
			echo woocommerce_get_product_thumbnail('ratio-1:1');
		}
	}

	// Change WC loop product product item - Landscape size (Ratio 4:3)
	if( !function_exists('vu_woocommerce_template_loop_product_thumbnail_landscape') ){
		function vu_woocommerce_template_loop_product_thumbnail_landscape(){
			echo woocommerce_get_product_thumbnail('ratio-4:3');
		}
	}

	// Add an extra class for WC fields
	if( !function_exists('vu_woocommerce_form_field_args') ) {
		function vu_woocommerce_form_field_args( $args, $key, $value ) {
			$args['input_class'] = array('form-control');

			if( $key == 'state_select' ) {
				print_r($args);
			}

			return $args;
		}
	}

	add_filter( 'woocommerce_form_field_args', 'vu_woocommerce_form_field_args', 10, 3 );

	// Print Product Socials Networks
	if( !function_exists('vu_product_socials') ) {
		function vu_product_socials(){
			global $post;

			$url = get_permalink();
			$title = get_the_title();
			$post_id = get_the_ID();

			if( vu_get_option('product-social') ) : ?>
				<div class="vu_p-socials vu_socials clearfix<?php vu_animation(true); ?>">
					<?php if( vu_get_option( array('product-social-networks','facebook') ) == '1' ) { ?>
						<div class="social-icon-container-small">
							<a href="#" class="vu_social-link" data-href="http://www.facebook.com/sharer.php?u=<?php echo esc_url($url); ?>&amp;t=<?php echo urlencode($title); ?>"><i class="fa fa-facebook"></i></a>
						</div>
					<?php } if( vu_get_option( array('product-social-networks','twitter') ) == '1' ) { ?>
						<div class="social-icon-container-small">
							<a href="#" class="vu_social-link" data-href="https://twitter.com/share?text=<?php echo urlencode($title); ?>&amp;url=<?php echo esc_url($url); ?>"><i class="fa fa-twitter"></i></a>
						</div>
					<?php } if( vu_get_option( array('product-social-networks','google-plus') ) == '1' ) { ?>
						<div class="social-icon-container-small">
							<a href="#" class="vu_social-link" data-href="https://plus.google.com/share?url=<?php echo esc_url($url); ?>"><i class="fa fa-google-plus"></i></a>
						</div>
					<?php } if( vu_get_option( array('product-social-networks','pinterest') ) == '1' ) { ?>
						<div class="social-icon-container-small">
							<a href="#" class="vu_social-link" data-href="http://pinterest.com/pin/create/button/?url=<?php echo urlencode($url); ?>&amp;description=<?php echo urlencode($title); ?>&amp;media=<?php echo vu_get_attachment_image_src($post_id, array(705, 470)); ?>"><i class="fa fa-pinterest"></i></a>
						</div>
					<?php } if( vu_get_option( array('product-social-networks','linkedin') ) == '1' ) { ?>
						<div class="social-icon-container-small">
							<a href="#" class="vu_social-link" data-href="http://linkedin.com/shareArticle?mini=true&amp;title=<?php echo urlencode($title); ?>&amp;url=<?php echo esc_url($url); ?>"><i class="fa fa-linkedin"></i></a>
						</div>
					<?php } ?>
				</div>
		<?php 
			endif;
		}
	}

	add_action('woocommerce_share', 'vu_product_socials');
?>