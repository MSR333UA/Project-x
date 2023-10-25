<?php
	/**
	 * Gallery Item Shortcode
	 *
	 * @param string $atts['image']
	 * @param string $atts['title']
	 * @param string $atts['subtitle']
	 * @param string $atts['category']
	 * @param string $atts['link_type']
	 * @param string $atts['link']
	 * @param string $atts['class'] Add a class name and then refer to it in your css file.
	 */

	function vu_gallery_item_shortcode( $atts, $content = null ) {
		$atts = shortcode_atts( array(
			"image" => "",
			"title" => "",
			"subtitle" => "",
			"category" => "",
			"link_type" => "",
			"link" => "",
			"class" => "",
			"wrap" => ""
		), $atts );

		$categories = @explode(',', $atts['category']);

		if( is_array($categories) ){
			foreach ($categories as $key => $value) {
				$categories[$key] = sanitize_title($value);
			}
		} else {
			$categories = sanitize_title($categories);
		}

		ob_start();
	?>
		<?php echo (!empty($atts['wrap'])) ? '<div class="'. trim( $atts['wrap'] .' '. esc_attr( @implode(' ', $categories) ) ) .'">' : ''; ?>
		<div class="vu_gallery-item<?php vu_extra_class($atts['class']); ?>">
			<div class="vu_gi-image">
				<?php echo wp_get_attachment_image( $atts['image'], 'ratio-4:3' ); ?>
			</div>
			<div class="vu_gi-details-container<?php echo (empty($atts['title']) && empty($atts['subtitle'])) ? ' vu_gi-empty' : ''; ?>">
				<div class="vu_gi-details">
					<?php if( $atts['link_type'] == 'lightbox' ) { ?>
						<a href="<?php echo vu_get_attachment_image_src( $atts['image'], 'full' ); ?>" title="<?php echo esc_attr($atts['title']); ?>" class="vu_gi-lightbox vu_gi-content-container">
					<?php } else if( $atts['link_type'] == 'url' ) { ?>
						<?php $link = vc_build_link( $atts['link'] ); ?>
						<a href="<?php echo esc_url($link['url']); ?>" title="<?php echo esc_attr($link['title']); ?>" class="vu_gi-content-container" target="<?php echo (strlen($link['target']) > 0 ? esc_attr($link['target']) : '_self' ); ?>">
					<?php } else { ?>
						<span class="vu_gi-content-container">
					<?php } ?>
							<div class="vu_gi-content">
								<?php echo !empty($atts['title']) ? '<h3 class="vu_gi-title">'. esc_html($atts['title']) .'</h3>' : ''; ?>
								<?php echo !empty($atts['subtitle']) ? '<span class="vu_gi-subtitle">'. esc_html($atts['subtitle']) .'</span>' : ''; ?>
							</div>
					<?php if( $atts['link_type'] == 'none' ) { ?>
						</span>
					<?php } else { ?>
						</a>
					<?php } ?>
				</div>
			</div>
		</div>
		<?php echo (!empty($atts['wrap'])) ? '</div>' : ''; ?>
	<?php 
		$output = ob_get_contents();
		ob_end_clean();
		
		return $output;
	}

	add_shortcode('vu_gallery_item', 'vu_gallery_item_shortcode');

	/**
	 * Gallery VC Shortcode
	 */

	if( class_exists('WPBakeryShortCode') ) {
		class WPBakeryShortCode_vu_gallery_item extends WPBakeryShortCode {
			public function content($atts, $content = null) {
				$atts = vc_map_get_attributes("vu_gallery_item", $atts);

				return do_shortcode( vu_generate_shortcode('vu_gallery_item', $atts, $content) );
			}
		}

		vc_map(
			array(
				"name" => __("Gallery Item", 'bakery'),
				"description" => __('Add gallery item', 'bakery'),
				"base" => "vu_gallery_item",
				"icon" => "vu_gallery-item-icon",
				"custom_markup" => '<input type="hidden" class="wpb_vc_param_value image attach_image" name="image" value="1"><h4 class="wpb_element_title">'. __('Gallery Item', 'bakery') .' <img width="150" height="150" src="'. vc_asset_url('vc/blank.gif') .'" class="attachment-thumbnail vc_element-icon vu_element-icon" data-name="image" alt="" title="" style="display: none;"><span class="no_image_image vc_element-icon vu_element-icon vu_gallery-item-icon"></span></h4><span class="vc_admin_label admin_label_title hidden-label"><label>Title</label>: </span><span class="vc_admin_label admin_label_category hidden-label"><label>Category</label>: </span>',
				"controls" => "full",
				"as_child" => array( 'only' => 'vu_gallery' ),
				"category" => __('Bakery', 'bakery'),
				"params" => array(
					array(
						"type" => "attach_image",
						"heading" => __("Image", 'bakery'),
						"param_name" => "image",
						"value" => "",
						"save_always" => true,
						"description" => __("Select image from media library.", 'bakery')
					),
					array(
						"type" => "textfield",
						"heading" => __("Title", 'bakery'),
						"param_name" => "title",
						"admin_label" => true,
						"value" => "",
						"save_always" => true,
						"description" => __("Enter gallery item title.", 'bakery')
					),
					array(
						"type" => "textfield",
						"heading" => __("Subtitle", 'bakery'),
						"param_name" => "subtitle",
						"value" => "",
						"save_always" => true,
						"description" => __("Enter gallery item subtitle.", 'bakery')
					),
					array(
						"type" => "textfield",
						"heading" => __("Category", 'bakery'),
						"param_name" => "category",
						"admin_label" => true,
						"value" => "",
						"save_always" => true,
						"description" => __("Enter gallery item category.", 'bakery')
					),
					array(
						"type" => "dropdown",
						"heading" => __("Link Type", 'bakery'),
						"param_name" => "link_type",
						"value" => array(
							"No Link" => "none",
							"Lightbox" => "lightbox",
							"Link to URL" => "url"
						),
						"save_always" => true,
						"description" => __("Select gallery item link type.", 'bakery'),
					),
					array(
						"type" => "vc_link",
						"heading" => __("URL (Link)", 'bakery'),
						"param_name" => "link",
						"dependency" => array("element" => "link_type", "value" => "url"),
						"value" => "",
						"save_always" => true,
						"description" => __("Add link to service.", 'bakery')
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