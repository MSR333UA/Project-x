<?php

	/**
	 * Service Item Shortcode
	 *
	 * @param string $atts['type']
	 * @param string $atts['icon']
	 * @param string $atts['image_alingment']
	 * @param string $atts['title']
	 * @param string $atts['description']
	 * @param string $atts['link_text']
	 * @param string $atts['link']
	 * @param string $atts['class'] Add a class name and then refer to it in your css file.
	 */

	function vu_service_item_shortcode( $atts, $content = null ) {
		$atts = shortcode_atts(array(
			'type' => '',
			'icon' => '',
			'image' => '',
			'image_alingment' => 'top',
			'title' => '',
			'description' => '',
			'add_link' => '',
			'link_text' => '',
			'link' => '',
			'class' => ''
		), $atts);

		$link = vc_build_link( $atts['link'] );
		
		ob_start();

		if( $atts['type'] == 'icon'){ ?>
			<div class="icon-opening-wrapper big-version<?php echo vu_animation(false); ?><?php vu_extra_class($atts['class']); ?>">
				<div class="icon-opening"><i class="<?php echo esc_attr( $atts['icon'] ); ?>"></i></div>
				<div class="icon-opening-content">
					<h3><?php echo esc_html($atts['title']); ?></h3>
					<?php echo wpautop($atts['description']); ?>
					<?php
						if ( $atts['add_link'] == '1' && strlen( $atts['link'] ) > 0 && strlen( $link['url'] ) > 0 ) {
							echo '<a href="'. esc_url( $link['url'] ) .'" class="read-more-link" title="'. esc_attr( $link['title'] ) .'" target="'. ( strlen( $link['target'] ) > 0 ? esc_attr( $link['target'] ) : '_self' ) . '">'. esc_html($atts['link_text']) .'</a>';
						}
					?>
				</div>
			</div>
		<?php } else {
			if( $atts['image_alingment'] == 'top' ){ ?>
				<div class="service-box<?php echo vu_animation(false); ?><?php vu_extra_class($atts['class']); ?>">
					<div class="icon-big-container">
						<?php echo wp_get_attachment_image(absint($atts['image'])); ?>
					</div>
					<h3><?php echo esc_html($atts['title']); ?></h3>
					<div class="horizontal-delimiter"></div>
					<?php echo wpautop($atts['description']); ?>
					<?php
						if ( $atts['add_link'] == '1' && strlen( $atts['link'] ) > 0 && strlen( $link['url'] ) > 0 ) {
							echo '<a href="'. esc_url( $link['url'] ) .'" class="read-more-link" title="'. esc_attr( $link['title'] ) .'" target="'. ( strlen( $link['target'] ) > 0 ? esc_attr( $link['target'] ) : '_self' ) . '">'. esc_html($atts['link_text']) .'</a>';
						}
					?>
				</div>
		<?php } else { ?>
			<div class="article-short<?php echo vu_animation(false); ?><?php vu_extra_class($atts['class']); ?>">
				<div class="article-short-icon-container">
					<div class="article-short-icon">
						<?php echo wp_get_attachment_image(absint($atts['image'])); ?>
					</div>
				</div>
				<div class="article-short-content">
					<h3><?php echo esc_html($atts['title']); ?></h3>
					<?php echo wpautop($atts['description']); ?>
					<?php
						if ( $atts['add_link'] == '1' && strlen( $atts['link'] ) > 0 && strlen( $link['url'] ) > 0 ) {
							echo '<a href="'. esc_url( $link['url'] ) .'" class="read-more-link" title="'. esc_attr( $link['title'] ) .'" target="'. ( strlen( $link['target'] ) > 0 ? esc_attr( $link['target'] ) : '_self' ) . '">'. esc_html($atts['link_text']) .'</a>';
						}
					?>
				</div>
			</div>
		<?php
			}
		}

		$output = ob_get_contents();
		ob_end_clean();
		
		return $output;
	}

	add_shortcode('vu_service_item', 'vu_service_item_shortcode');

	/**
	 * Service Item VC Shortcode
	 */

	if( class_exists('WPBakeryShortCode') ){
		class WPBakeryShortCode_vu_service_item extends WPBakeryShortCode {
			public function content($atts, $content = null) {
				$atts = vc_map_get_attributes("vu_service_item", $atts);

				return do_shortcode( vu_generate_shortcode('vu_service_item', $atts, $content) );
			}
		}

		vc_map(
			array(
				"name"		=> __("Service Item", 'bakery'),
				"description" => __('Add a service item', 'bakery'),
				"base"		=> "vu_service_item",
				"class"		=> "vc_vu_service_item",
				"icon"		=> "vu_element-icon vu_service-item-icon",
				"controls"	=> "full",
				"category"  => __('Bakery', 'bakery'),
				"params"	=> array(
					array(
						'type' => 'dropdown',
						'class' => '',
						'heading' => __('Type', 'bakery'),
						'param_name' => 'type',
						'value' => array(
							'Icon' => 'icon',
							'Image' => 'image'
						),
						"save_always" => true,
						"description" => __("Select item service type.", 'bakery')
					),
					array(
						"type" => "iconpicker",
						"heading" => __("Icon", 'bakery'),
						"param_name" => "icon",
						"dependency" => array("element" => "type", "value" => "icon"),
						"settings" => array(
							"emptyIcon" => false,
							"iconsPerPage" => 100
						),
						"value" => "",
						"save_always" => true,
						"description" => __("Pick an icon from the library.", 'bakery')
					),
					array(
						"type" => "attach_image",
						"heading" => __("Image", 'bakery'),
						"param_name" => "image",
						"dependency" => array("element" => "type", "value" => "image"),
						"value" => "",
						"save_always" => true,
						"description" => __("Select image from media library.", 'bakery')
					),
					array(
						'type' => 'dropdown',
						'class' => '',
						'heading' => __('Image Alingment', 'bakery'),
						'param_name' => 'image_alingment',
						"dependency" => array("element" => "type", "value" => "image"),
						'value' => array(
							'Top' => 'top',
							'Left' => 'left'
						),
						"save_always" => true,
						"description" => __("Select image alignment.", 'bakery')
					),
					array(
						"type" => "textfield",
						"heading" => __("Title", 'bakery'),
						"param_name" => "title",
						"admin_label" => true,
						"value" => "",
						"save_always" => true,
						"description" => __("Enter service title.", 'bakery')
					),
					array(
						"type" => "textarea",
						"heading" => __("Description", 'bakery'),
						"param_name" => "description",
						"value" => "",
						"save_always" => true,
						"description" => __("Enter service description.", 'bakery')
					),
					array(
						"type" => "checkbox",
						"heading" => __("Add Link", 'bakery'),
						"param_name" => "add_link",
						"value" => array(__("Yes Please", 'bakery') => "1"),
						"std" => "0",
						"save_always" => true,
						"description" => __("Check to add link to service item.", 'bakery')
					),
					array(
						"type" => "textfield",
						"heading" => __("Link Text", 'bakery'),
						"param_name" => "link_text",
						"dependency" => array("element" => "add_link", "value" => "1"),
						"value" => __("Read More", 'bakery'),
						"save_always" => true,
						"description" => __("Enter link text.", 'bakery')
					),
					array(
						"type" => "vc_link",
						"heading" => __("URL (Link)", 'bakery'),
						"param_name" => "link",
						"dependency" => array("element" => "add_link", "value" => "1"),
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