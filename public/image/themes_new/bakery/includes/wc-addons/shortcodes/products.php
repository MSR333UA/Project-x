<?php
	/**
	 * Products Shortcode
	 *
	 * @param string $atts['query']
	 * @param string $atts['show_avatar']
	 * @param string $atts['show_name']
	 * @param string $atts['class'] Add a class name and then refer to it in your css file.
	 */

	function vu_products_shortcode( $atts, $content = null ) {
		$atts = shortcode_atts( array(
			'query' => '',
			'type' => '',
			'product_view' => '',
			'layout' => '',
			'rows' => '',
			'scroll_per_page' => '',
			'autoplay' => '',
			'show_button' => '',
			'button_text' => '',
			'button_link' => '',
			'class' => ''
		), $atts );

		if( stripos($atts['query'], 'post_type:product') === false ){
			$atts['query'] .= '|post_type:product';
		}

		if( function_exists('vc_build_loop_query') ) {
			list($args, $products) = vc_build_loop_query( esc_attr($atts['query']) );
		} else {
			$VcLoopQueryBuilder = new VcLoopQueryBuilder( esc_attr($atts['query']) );
			$products = $VcLoopQueryBuilder->build();
		}

		global $woocommerce_loop;

		$woocommerce_loop['columns'] = absint($atts['layout']);

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

		ob_start();
	?>
		<div class="vu_sh-products m-b-50<?php vu_extra_class($atts['class']); ?>">
			<?php if( $atts['type'] == 'carousel' ) : 
				$carousel_options = array(
					"singleItem" => false,
					"items" => absint($atts['layout']),
					"itemsDesktop" => array(1199, 3),
					"itemsDesktopSmall" => array(980, 3),
					"itemsTablet" => array(768, 2),
					"itemsMobile" => array(479, 1),
					"navigation" => false,
					"navigationText" => false,
					"pagination" => true,
					"autoHeight" => true,
					"scrollPerPage" => ($atts['scroll_per_page'] == '1') ? true : false,
					"autoPlay" => ($atts['autoplay'] == '' || $atts['autoplay'] == '0') ? false : absint($atts['autoplay']),
					"stopOnHover" => true
				); ?>
				<div class="row">
					<div class="vu_sh_p-carousel vu_owl-carousel" data-options="<?php echo esc_attr(json_encode($carousel_options)); ?>">
					<?php 
						$rows = absint($atts['rows']);
						$item = 1;
						$count = 1;

						if($products->have_posts()) : while( $products->have_posts() ): $products->the_post();
							if( $item == 1 ) {
								echo '<div>';
							}

							woocommerce_get_template_part('content', 'product');

							if( ($item % $rows) == 0 || $count >= $products->post_count ) {
								echo '</div>';
								$item = 0;
							}

							$item++;
							$count++;
						endwhile; endif;
					?>
					</div>
				</div>
			<?php else : ?>
				<div class="row">
					<?php 
						if($products->have_posts()) : while( $products->have_posts() ): $products->the_post();
							woocommerce_get_template_part('content', 'product');
						endwhile; endif;
					?>
				</div>
			<?php endif; ?>

			<?php if( $atts['show_button'] == '1' ) : ?>
				<?php  $button_link = vc_build_link( $atts['button_link'] ); ?>
				<div class="text-center<?php vu_animation(true); ?>">
					<a href="<?php echo esc_url( $button_link['url'] ); ?>" class="button-void" title="<?php echo esc_attr( $button_link['title'] ); ?>" target="<?php echo strlen( $button_link['target'] ) > 0 ? esc_attr( $button_link['target'] ) : '_self'; ?>"><?php echo esc_html($atts['button_text']); ?></a>
				</div>
			<?php endif; ?>
		</div>
	<?php
		$output = ob_get_contents();
		ob_end_clean();
		
		return $output;
	}

	add_shortcode('vu_products', 'vu_products_shortcode');

	/**
	 * Products VC Shortcode
	 */

	if( class_exists('WPBakeryShortCode')) {
		class WPBakeryShortCode_vu_products extends WPBakeryShortCode {
			public function content($atts, $content = null) {
				$atts = vc_map_get_attributes("vu_products", $atts);
				
				return do_shortcode('[vu_products'. vu_shortcode_atts($atts) .'"]');
			}
		}

		vc_map(
			array(
				"name"		=> __("Products", 'bakery'),
				"description" => __('Show products', 'bakery'),
				"base"		=> "vu_products",
				"class"		=> "vc_vu_products",
				"icon"		=> "vu_element-icon vu_products-icon",
				"controls"	=> "full",
				"category"  => __('Bakery', 'bakery'),
				"params"	=> array(
					array(
						"type" => "loop",
						"heading" => __("Products Query", 'bakery'),
						"param_name" => "query",
						'settings' => array(
							'size'          => array('hidden' => false, 'value' => 'All'),
							'order_by'      => array('value' => 'date'),
							'categories'    => array('hidden' => true),
							'tags'          => array('hidden' => true),
							'tax_query'     => array('hidden' => false),
							'authors'     	=> array('hidden' => true),
							'post_type'     => array('value' => 'product', 'hidden' => true)
						),
						"save_always" => true,
						"description" => __("Create WordPress loop, to show products from your site.", 'bakery')
					),
					array(
						"type" => "dropdown",
						"heading" => __("Type", 'bakery'),
						"param_name" => "type",
						"admin_label" => true,
						"value" =>  array(
							'Standard' => 'standard',
							'Carousel' => 'carousel'
						),
						"save_always" => true,
						"description" => __("Select products type.", 'bakery')
					),
					array(
						"type" => "textfield",
						"heading" => __("Rows", 'bakery'),
						"param_name" => "rows",
						"dependency" => array("element" => "type", "value" => "carousel"),
						"value" => '1',
						"save_always" => true,
						"description" => __("Enter number of row with products.", 'bakery')
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
						"type" => "checkbox",
						"heading" => __("Scroll per page", 'bakery'),
						"param_name" => "scroll_per_page",
						"dependency" => array("element" => "type", "value" => "carousel"),
						"value" =>  array( __('Yes Please', 'bakery') => '1'),
						"save_always" => true,
						"description" => __("Check to scroll per page.", 'bakery')
					),
					array(
						"type" => "textfield",
						"heading" => __("Auto play", 'bakery'),
						"param_name" => "autoplay",
						"dependency" => array("element" => "type", "value" => "carousel"),
						"value" => "",
						"save_always" => true,
						"description" => __("Change to any integrer for example <b>5000</b> to play every <b>5</b> seconds. Leave blank to disable autoplay.", 'bakery')
					),
					array(
						"type" => "checkbox",
						"heading" => __("Show Button", 'bakery'),
						"param_name" => "show_button",
						"value" =>  array( __('Yes Please', 'bakery') => '1'),
						"save_always" => true,
						"description" => __("Check to show button.", 'bakery')
					),
					array(
						"type" => "textfield",
						"heading" => __("Button Text", 'bakery'),
						"param_name" => "button_text",
						"dependency" => array("element" => "show_button", "value" => "1"),
						"value" => __('See all our products', 'bakery'),
						"save_always" => true,
						"description" => __("Enter button text.", 'bakery')
					),
					array(
						"type" => "vc_link",
						"heading" => __("Button URL (Link)", 'bakery'),
						"param_name" => "button_link",
						"dependency" => array("element" => "show_button", "value" => "1"),
						"value" => "",
						"save_always" => true,
						"description" => __("Add link to button.", 'bakery')
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