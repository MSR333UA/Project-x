<?php 
	/**
	 * Image Slider Shortcode
	 *
	 * @param string $atts['images']
	 * @param string $atts['size']
	 * @param string $atts['navigation']
	 * @param string $atts['pagination']
	 * @param string $atts['color']
	 * @param string $atts['autoplay']
	 * @param string $atts['class'] Add a class name and then refer to it in your css file.
	 */

	function vu_image_slider_shortcode( $atts, $content = null ) {
		$atts = shortcode_atts( array(
			"images" => "",
			"ratio" => "",
			"navigation" => "",
			"pagination" => "",
			"color" => "",
			"autoplay" => "",
			"class" => ""
		), $atts );

		$images = @explode(',', esc_attr($atts['images']));

		$slider_options = array();

		$slider_options['singleItem'] = true;
		$slider_options['autoHeight'] = true;
		$slider_options['items'] = 1;
		$slider_options['autoPlay'] = ($atts['autoplay'] == '' || $atts['autoplay'] == '0') ? false : absint($atts['autoplay']);
		$slider_options['stopOnHover'] = true;
		$slider_options['navigation'] = ($atts['navigation'] == 'always' || $atts['navigation'] == 'hover') ? true : false;
		$slider_options['navigationText'] = array('<i class="fa fa-angle-left"></i>', '<i class="fa fa-angle-right"></i>');
		$slider_options['rewindNav'] = true;
		$slider_options['scrollPerPage'] = true;
		$slider_options['pagination'] = ($atts['pagination'] == 'always' || $atts['pagination'] == 'hover') ? true : false;;
		$slider_options['paginationNumbers'] = false;

		ob_start();
	?>
		<div class="vu_image-slider vu_is-navigation-<?php echo esc_attr($atts['navigation']); ?> vu_is-pagination-<?php echo esc_attr($atts['pagination']); ?> vu_owl-carousel<?php vu_extra_class($atts['class']); ?>" data-options="<?php echo esc_attr(json_encode($slider_options)); ?>" data-color="<?php echo esc_attr($atts['color']); ?>">
			<?php 
				foreach ($images as $image) {
					echo '<div class="vu_is-item">'. wp_get_attachment_image(absint($image), 'ratio-'. esc_attr($atts['ratio'])) .'</div>';
				}
			?>
		</div>
	<?php
		$output = ob_get_contents();
		ob_end_clean();
		
		return $output;
	}

	add_shortcode('vu_image_slider', 'vu_image_slider_shortcode');

	/**
	 * Image Slider VC Shortcode
	 */

	if( class_exists('WPBakeryShortCode') ) {
		class WPBakeryShortCode_vu_image_slider extends WPBakeryShortCode {
			public function content($atts, $content = null) {
				$atts = vc_map_get_attributes("vu_image_slider", $atts);

				return do_shortcode( vu_generate_shortcode('vu_image_slider', $atts, $content) );
			}
		}

		vc_map(
			array(
				"name"		=> __("Image Slider", 'bakery'),
				"description" => __("Animated slider with images", 'bakery'),
				"base"		=> "vu_image_slider",
				"class"		=> "vc_vu_image-slider",
				"icon"		=> "vu_element-icon vu_image-slider-icon",
				"custom_markup" => "",
				"controls"	=> "full",
				"category"  => __('Bakery', 'bakery'),
				"params"	=> array(
					array(
						"type" => "attach_images",
						"heading" => __("Images", 'bakery'),
						"param_name" => "images",
						"admin_label" => true,
						"value" => "",
						"save_always" => true,
						"description" => __("Select images from media library.", 'bakery')
					),
					array(
						"type" => "dropdown",
						"heading" => __("Ratio", 'bakery'),
						"param_name" => "ratio",
						"admin_label" => true,
						"value" => array(
							__("1:1", 'bakery') => "1:1",
							__("2:3", 'bakery') => "2:3",
							__("3:4", 'bakery') => "3:4",
							__("4:3", 'bakery') => "4:3",
							__("16:9", 'bakery') => "16:9"
						),
						"save_always" => true,
						"description" => __("Select ratio of images.", 'bakery')
					),
					array(
						"type" => "dropdown",
						"heading" => __("Show navigation", 'bakery'),
						"param_name" => "navigation",
						"value" => array(
							__("None", 'bakery') => "none",
							__("Always", 'bakery') => "always",
							__("Only on hover", 'bakery') => "hover"
						),
						"std" => 'hover',
						"save_always" => true,
						"description" => __("", 'bakery')
					),
					array(
						"type" => "dropdown",
						"heading" => __("Show pagination", 'bakery'),
						"param_name" => "pagination",
						"value" => array(
							__("None", 'bakery') => "none",
							__("Always", 'bakery') => "always",
							__("Only on hover", 'bakery') => "hover"
						),
						"std" => 'hover',
						"save_always" => true,
						"description" => __("", 'bakery')
					),
					array(
						"type" => "colorpicker",
						"heading" => __("Color", 'bakery'),
						"param_name" => "color",
						"value" => "#ffffff",
						"save_always" => true,
						"description" => __("Choose color for navigation and pagination.", 'bakery')
					),
					array(
						"type" => "textfield",
						"heading" => __("Auto play", 'bakery'),
						"param_name" => "autoplay",
						"value" => "",
						"save_always" => true,
						"description" => __("Change to any integrer for example <b>5000</b> to play every <b>5</b> seconds. Leave blank to disable autoplay.", 'bakery')
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