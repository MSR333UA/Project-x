<?php
	/**
	 * Products with Filter Shortcode
	 *
	 * @param string $atts['product_category']
	 * @param string $atts['filter_type']
	 * @param string $atts['layout']
	 * @param string $atts['product_view']
	 * @param string $atts['class'] Add a class name and then refer to it in your css file.
	 */

	function vu_products_with_filter_shortcode( $atts, $content = null ) {
		$atts = shortcode_atts( array(
			'product_category' => '',
			'filter_type' => '',
			'layout' => '',
			'product_view' => '',
			'class' => ''
		), $atts );

		ob_start();
	?>
		<div class="section-content m-b-50 clearfix<?php vu_extra_class($atts['class']); ?>">
			<div class="container vu_products-container">
				<div class="vu_p-filter-type-<?php echo esc_attr($atts['filter_type']); ?> text-center<?php vu_animation(true); ?>">
					<div class="filter-icons-container vu_products-filters">
						<div class="filter-icon-wrapper">
								<?php 
									if( $atts['filter_type'] == 'text' ) {
										$parent_term = get_term( absint($atts['product_category']), 'product_cat' );
										
										if( !empty($parent_term) && !is_wp_error($parent_term) ){
											$parent_term_name = $parent_term->name;
										} else {
											$parent_term_name = __('All', 'bakery');
										}
									}
								?>
								<a href="#" class="vu_filter active" title="<?php echo __('All', 'bakery'); ?>" data-filter="*">
									<span class="filter-icon-content">
										<span class="filter-icon"><?php echo ($atts['filter_type'] != 'text') ? '<i class="fa fa-th-large"></i>' : $parent_term_name; ?></span>
									</span>
								</a>
							</div>
						<?php 
							$categories_ids = get_term_children( absint($atts['product_category']), 'product_cat' );

							if( !empty($categories_ids) && !is_wp_error($categories_ids) ){
								foreach ( $categories_ids as $category_id ) { 
									$category = get_term($category_id, 'product_cat');
								?>
								<div class="filter-icon-wrapper">
									<a href="<?php echo esc_url(get_term_link($category)); ?>" class="vu_filter" title="<?php echo esc_attr($category->name); ?>" data-id="<?php echo absint($category->term_id); ?>" data-filter=".<?php echo esc_attr($category->slug); ?>">
										<span class="filter-icon-content">
											<span class="filter-icon">
												<?php 
													if( $atts['filter_type'] != 'text') {
														echo wp_get_attachment_image( absint( get_woocommerce_term_meta( $category->term_id, 'thumbnail_id', true ) ) );
													} else {
														echo esc_html($category->name);
													}
												?>
											</span>
										</span>
									</a>
								</div>
							<?php 
								}
							}
						?>
					</div>
				</div>

				<div class="row vu_products">
					<?php 
						$args = array( 
							'post_type' => 'product',
							'posts_per_page' => -1,
							'tax_query' => array(
								'relation' => 'AND',
								array(
									'taxonomy' => 'product_cat',
									'field'    => 'term_id',
									'terms'    => $categories_ids,
								)
							)
						);

						if( $atts['product_view'] == 'square' ) {
							remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10);
							remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail_landscape', 10);
							add_action( 'woocommerce_before_shop_loop_item_title', 'vu_woocommerce_template_loop_product_thumbnail_square', 10);
						} else if( $atts['product_view'] == 'landscape' ) {
							remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10);
							remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail_square', 10);
							add_action( 'woocommerce_before_shop_loop_item_title', 'vu_woocommerce_template_loop_product_thumbnail_landscape', 10);
						} else {
							remove_action( 'woocommerce_before_shop_loop_item_title', 'vu_woocommerce_template_loop_product_thumbnail_square', 10);
							remove_action( 'woocommerce_before_shop_loop_item_title', 'vu_woocommerce_template_loop_product_thumbnail_landscape', 10);
							add_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10);
						}

						global $vu_product_classes, $woocommerce_loop;

						$woocommerce_loop['columns'] = absint($atts['layout']);

						$products = new WP_Query( $args );
						
						if( $products->have_posts() ) : while( $products->have_posts() ): $products->the_post();
							$vu_product_classes = vu_product_terms(get_the_ID(), false, " ", true);

							woocommerce_get_template_part('content', 'product');
						endwhile; endif;

						wp_reset_postdata();
					?>
				</div><!-- .row -->
			</div><!-- .container -->
		</div>
	<?php
		$output = ob_get_contents();
		ob_end_clean();

		wp_enqueue_script( 'isotope' );
		
		return $output;
	}

	add_shortcode('vu_products_with_filter', 'vu_products_with_filter_shortcode');

	/**
	 * Products with Filter VC Shortcode
	 */

	if( class_exists('WPBakeryShortCode')) {
		class WPBakeryShortCode_vu_products_with_filter extends WPBakeryShortCode {
			public function content($atts, $content = null) {
				$atts = vc_map_get_attributes("vu_products_with_filter", $atts);
				
				return do_shortcode('[vu_products_with_filter'. vu_shortcode_atts($atts) .']');
			}
		}

		vc_map(
			array(
				"name"		=> __("Products with Filter", 'bakery'),
				"description" => __('Show the products with filter', 'bakery'),
				"base"		=> "vu_products_with_filter",
				"class"		=> "vc_vu_products_with_filter",
				"icon"		=> "vu_element-icon vu_products-with-filter-icon",
				"controls"	=> "full",
				"category"  => __('Bakery', 'bakery'),
				"params"	=> array(
					array(
						"type" => "dropdown",
						"heading" => __("Product Category", 'bakery'),
						"param_name" => "product_category",
						"value" =>  vu_products_categories(),
						"save_always" => true,
						"description" => __("Select product category.", 'bakery')
					),
					array(
						"type" => "dropdown",
						"heading" => __("Filter type", 'bakery'),
						"param_name" => "filter_type",
						"admin_label" => true,
						"value" => array(
							__("Icon", 'bakery') => 'icon',
							__("Text", 'bakery') => 'text'
						),
						"std" => 'icon',
						"save_always" => true,
						"description" => __("Select portfolio filter type.", 'bakery'),
					),
					array(
						"type" => "dropdown",
						"heading" => __("Layout", 'bakery'),
						"param_name" => "layout",
						"admin_label" => true,
						"value" => array(
							__("1 Column", 'bakery') => '1',
							__("2 Columns", 'bakery') => '2',
							__("3 Columns", 'bakery') => '3',
							__("4 Columns", 'bakery') => '4'
						),
						"std" => '4',
						"save_always" => true,
						"description" => __("Select products layout.", 'bakery'),
					),
					array(
						"type" => "dropdown",
						"heading" => __("Product View", 'bakery'),
						"param_name" => "product_view",
						"admin_label" => true,
						"value" => array(
							__("Portrait", 'bakery') => 'portrait',
							__("Landscape", 'bakery') => 'landscape',
							__("Square", 'bakery') => 'square'
						),
						"std" => 'portrait',
						"save_always" => true,
						"description" => __("Select product view.", 'bakery'),
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