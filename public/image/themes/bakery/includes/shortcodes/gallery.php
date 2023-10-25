<?php 
	/**
	 * Gallery Shortcode
	 *
	 * @param string $atts['type']
	 * @param string $atts['layout']
	 * @param string $atts['style']
	 * @param string $atts['filterable']
	 * @param string $atts['items']
	 * @param string $atts['autoplay']
	 * @param string $atts['class'] Add a class name and then refer to it in your css file.
	 */

	function vu_gallery_shortcode( $atts, $content = null ) {
		$atts = shortcode_atts( array(
			"type" => "",
			"layout" => "",
			"style" => "",
			"filterable" => "",
			"category_text" => "",
			"categories" => "",
			"items" => "",
			"autoplay" => "",
			"class" => ""
		), $atts );

		ob_start();

		if( $atts['type'] == 'standard' ) :
	?>
		<div class="vu_gallery vu_lightbox vu_g-style-<?php echo esc_attr($atts['style']); ?><?php echo ($atts['filterable'] == '1') ? ' vu_g-filterable' : ''; ?><?php vu_extra_class($atts['class']); ?>" data-delegate="a.vu_gi-lightbox">
			<?php if( $atts['filterable'] == '1') { ?>
				<div class="vu_g-filters">
					<span class="vu_g-filter active" data-filter="*"><?php echo esc_html($atts['category_text']); ?></span>

					<?php 
						$categories = @explode("\n", strip_tags($atts['categories']));

						if( is_array($categories) ) {
							foreach ($categories as $category) {
								echo '<span class="vu_g-filter" data-filter=".'. esc_attr(sanitize_title($category)) .'">'. esc_html($category) .'</span>';
							}
						}

						wp_enqueue_script( 'isotope' );
					?>
				</div>
			<?php } ?>

			<div class="vu_g-items">
				<?php 
					$content = preg_replace('/\[(\[?)(vu_gallery_item)(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)/', '[$1$2$3$4 wrap="vu_g-item col-md-'. (12 / absint($atts['layout'])) .' col-sm-6 col-xs-6 col-tn-12"]', $content);

					echo do_shortcode($content);
				?>
			</div>
		</div>
	<?php 
		else : 
			$carousel_options = array();

			$carousel_options['singleItem'] = false;
			$carousel_options['autoHeight'] = true;
			$carousel_options['items'] = absint($atts['items']);
			$carousel_options['autoPlay'] = ($atts['autoplay'] == '' || $atts['autoplay'] == '0') ? false : absint($atts['autoplay']);
			$carousel_options['stopOnHover'] = true;
			$carousel_options['navigation'] = false;
			$carousel_options['rewindNav'] = true;
			$carousel_options['scrollPerPage'] = false;
			$carousel_options['pagination'] = false;
			$carousel_options['paginationNumbers'] = false;
	?>
		<div class="vu_gallery vu_lightbox<?php vu_extra_class($atts['class']); ?>" data-delegate="a.vu_gi-lightbox">
			<div class="vu_g-carousel vu_owl-carousel" data-options="<?php echo esc_attr(json_encode($carousel_options)); ?>">
				<?php echo do_shortcode($content); ?>
			</div>
		</div>
	<?php
		endif;

		$output = ob_get_contents();
		ob_end_clean();
		
		return $output;
	}

	add_shortcode('vu_gallery', 'vu_gallery_shortcode');

	/**
	 * Gallery VC Shortcode
	 */

	if( class_exists('WPBakeryShortCodesContainer') ) {
		class WPBakeryShortCode_vu_gallery extends WPBakeryShortCodesContainer {
		}

		vc_map(
			array(
				"name" => __("Gallery", 'bakery'),
				"description" => __('Add gallery', 'bakery'),
				"base" => "vu_gallery",
				"class" => "vc_vu_gallery",
				"icon" => "vu_element-icon vu_gallery-icon",
				"controls" => "full",
				"as_parent" => array( 'only' => 'vu_gallery_item' ),
				"js_view" => 'VcColumnView',
				"content_element" => true,
				"is_container" => true,
				"container_not_allowed" => false,
				"category" => __('Bakery', 'bakery'),
				"default_content" => '',
				"params" => array(
					array(
						"type" => "dropdown",
						"heading" => __("Type", 'bakery'),
						"param_name" => "type",
						"value" => array(
							__("Standard", 'bakery') => 'standard',
							__("Carousel", 'bakery') => 'carousel'
						),
						"save_always" => true,
						"description" => __("Select gallery type.", 'bakery'),
					),
					array(
						"type" => "dropdown",
						"heading" => __("Layout", 'bakery'),
						"param_name" => "layout",
						"dependency" => array("element" => "type", "value" => "standard"),
						"value" => array(
							__("1 Column", 'bakery') => '1',
							__("2 Columns", 'bakery') => '2',
							__("3 Columns", 'bakery') => '3',
							__("4 Columns", 'bakery') => '4'
						),
						"save_always" => true,
						"description" => __("Select gallery layout.", 'bakery'),
					),
					array(
						"type" => "dropdown",
						"heading" => __("Style", 'bakery'),
						"param_name" => "style",
						"dependency" => array("element" => "type", "value" => "standard"),
						"value" => array(
							__("With Space", 'bakery') => 'with-space',
							__("Without Space", 'bakery') => 'without-space'
						),
						"save_always" => true,
						"description" => __("Select gallery style.", 'bakery')
					),
					array(
						"type" => "checkbox",
						"heading" => __("Make gallery filterable?", 'bakery'),
						"param_name" => "filterable",
						"dependency" => array("element" => "type", "value" => "standard"),
						"value" => array(__("Yes Please", 'bakery') => "1"),
						"save_always" => true,
						"description" => __("", 'bakery')
					),
					array(
						"type" => "textfield",
						"heading" => __("All category text", 'bakery'),
						"param_name" => "category_text",
						"dependency" => array("element" => "filterable", "value" => "1"),
						"value" => "All",
						"save_always" => true,
						"description" => __("Enter in all category text.", 'bakery')
					),
					array(
						"type" => "textarea",
						"heading" => __("Categories", 'bakery'),
						"param_name" => "categories",
						"dependency" => array("element" => "filterable", "value" => "1"),
						"value" => "",
						"save_always" => true,
						"description" => __("Enter one category per line.", 'bakery')
					),
					array(
						"type" => "textfield",
						"heading" => __("Items to display on screen", 'bakery'),
						"param_name" => "items",
						"dependency" => array("element" => "type", "value" => "carousel"),
						"value" => "5",
						"save_always" => true,
						"description" => __("Maximum items to display at a time.", 'bakery')
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