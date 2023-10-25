<?php
	/**
	 * Week Offers Shortcode
	 *
	 * @param string $atts['product_1']
	 * @param string $atts['product_2']
	 * @param string $atts['product_3']
	 * @param string $atts['ribbon_text']
	 * @param string $atts['order_button_text']
	 * @param string $atts['class'] Add a class name and then refer to it in your css file.
	 */

	function vu_week_offers_shortcode( $atts, $content = null ) {
		$atts = shortcode_atts(array(
			'product_1' => '',
			'product_2' => '',
			'product_3' => '',
			'ribbon_text' => '',
			'order_button_text' => __("Order Now", 'bakery'),
			'class' => ''
		), $atts);

		$product_ids = array();

		if( !empty($atts['product_1']) ){
			array_push($product_ids, absint($atts['product_1']));
		}

		if( !empty($atts['product_2']) ){
			array_push($product_ids, absint($atts['product_2']));
		}

		if( !empty($atts['product_3']) ){
			array_push($product_ids, absint($atts['product_3']));
		}

		$args = array(
			'post__in' => $product_ids,
			'post_type' => 'product',
			'post_status' => 'publish',
			'orderby' => 'none',
			'posts_per_page' => -1,
			'ignore_sticky_posts' => 1
		);

		$offer_products = new WP_Query($args);
		wp_reset_query();

		ob_start();
	?>
		<!-- tabs big container -->
		<div class="tabs-big-container centered-columns<?php vu_extra_class($atts['class']); ?>">
			<div class="centered-column centered-column-top">
				<!-- Nav tabs -->
				<ul class="nav" role="tablist">
					<?php
						$delay = 200;
						$is_first = true;

						if( $offer_products->have_posts() ) : 
							while ($offer_products->have_posts()) : $offer_products->the_post(); ?>
								<li class="<?php echo ($is_first == true) ? 'active' : ''. vu_animation(false, $delay); ?>"><a href="#vu_offer-product-<?php echo get_the_ID(); ?>" role="tab" data-toggle="tab"><?php if( has_post_thumbnail() ) { the_post_thumbnail('thumbnail'); } ?></a></li>
							<?php 
								$delay += 200;
								$is_first = false;
							endwhile; 
						endif;
					?>
				</ul>
				<!-- .nav tabs -->
			</div>
			
			<div class="centered-column tab-content centered-column-top">
				<?php 
					$is_first = true;

					if( $offer_products->have_posts() ) : 
						while ($offer_products->have_posts()) : $offer_products->the_post(); ?>
							<?php $vu_product_settings = vu_get_post_meta( get_the_ID(), 'vu_product_settings' ); ?>
							<!-- tab pane -->
							<div role="tabpanel" class="tab-pane fade<?php echo ($is_first == true) ? ' active in' : '' ?>" id="vu_offer-product-<?php echo get_the_ID(); ?>">
								<article class="<?php echo implode(' ', get_post_class('no-border', get_the_ID() )); ?>">
									<div class="centered-columns offer-box">
										<div class="offer-box-left centered-column">
											<div class="offer-info">
												<h2><?php the_title(); ?></h2>
												<?php the_content(); ?>
												<div class="clearfix m-b-100"></div>
												<h3 class="text-huge">
													<?php woocommerce_get_template( 'loop/price.php' ); ?>
													<a href="<?php the_permalink(); ?>" class="button"><?php echo esc_html($atts['order_button_text']); ?> <i class="fa fa-long-arrow-right"></i></a>
												</h3>
												<div class="margin-20"></div>
											</div>
										</div>
										<div class="offer-box-right centered-column" <?php if( has_post_thumbnail() ) { echo 'style="background-image:url(\''. esc_url(vu_get_attachment_image_src( get_post_thumbnail_id(), 'full' )) .'\');"'; } ?>>
											<?php if( !empty($atts['ribbon_text']) ) : ?>
												<div class="product-label-container big-label">
													<div class="product-label"><?php echo esc_attr($atts['ribbon_text']); ?></div>
													<div class="product-label-bottom"></div>
												</div>
											<?php endif; ?>
										</div>
									</div>
								</article>
							</div><!-- /tab-pane -->
						<?php 
							$is_first = false;
						endwhile; 
					endif;
					wp_reset_postdata();
				?>
			</div>
		</div><!-- .tabs big container -->
	<?php
		$output = ob_get_contents();
		ob_end_clean();
		
		return $output;
	}

	add_shortcode('vu_week_offers', 'vu_week_offers_shortcode');

	/**
	 * Week Offers VC Shortcode
	 */

	if( class_exists('WPBakeryShortCode')) {
		class WPBakeryShortCode_vu_week_offers extends WPBakeryShortCode {
			public function content($atts, $content = null) {
				$atts = vc_map_get_attributes("vu_week_offers", $atts);
				
				return do_shortcode('[vu_week_offers'. vu_shortcode_atts($atts) .']');
			}
		}

		$vu_products = vu_get_products();

		vc_map(
			array(
				"name"		=> __("Featured Products", 'bakery'),
				"description" => __('Show featured products', 'bakery'),
				"base"		=> "vu_week_offers",
				"class"		=> "vc_vu_week_offers",
				"icon"		=> "vu_element-icon vu_featured-products-icon",
				"controls"	=> "full",
				"category"  => __('Bakery', 'bakery'),
				"params"	=> array(
					array(
						"type" => "dropdown",
						"heading" => __("Product 1", 'bakery'),
						"param_name" => "product_1",
						"admin_label" => true,
						"value" => $vu_products,
						"save_always" => true,
						"description" => __("Select first product to be shown is the offer.", 'bakery')
					),
					array(
						"type" => "dropdown",
						"heading" => __("Product 2", 'bakery'),
						"param_name" => "product_2",
						"admin_label" => true,
						"value" => $vu_products,
						"save_always" => true,
						"description" => __("Select second product to be shown is the offer.", 'bakery')
					),
					array(
						"type" => "dropdown",
						"heading" => __("Product 3", 'bakery'),
						"param_name" => "product_3",
						"admin_label" => true,
						"value" => $vu_products,
						"save_always" => true,
						"description" => __("Select third product to be shown is the offer.", 'bakery')
					),
					array(
						"type" => "textfield",
						"heading" => __("Ribbon Text", 'bakery'),
						"param_name" => "ribbon_text",
						"value" => '',
						"save_always" => true,
						"description" => __("Enter ribbon text of the shortcode.", 'bakery')
					),
					array(
						"type" => "textfield",
						"heading" => __("Order Button Text", 'bakery'),
						"param_name" => "order_button_text",
						"value" => __("Order Now", 'bakery'),
						"save_always" => true,
						"description" => __("Enter button text.", 'bakery')
					),
					array(
						"type" => "textfield",
						"heading" => __("Extra class name", 'bakery'),
						"param_name" => "class",
						"value" => "",
						"save_always" => true,
						"description" => __("If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", 'bakery')
					)
				)
			)
		);
	}
?>