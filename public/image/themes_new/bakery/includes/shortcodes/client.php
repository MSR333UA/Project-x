<?php 
	/**
	 * Client Shortcode
	 *
	 * @param string $atts['image']
	 * @param string $atts['link']
	 * @param string $atts['class'] Add a class name and then refer to it in your css file.
	 */

	function vu_client_shortcode( $atts, $content = null ) {
		$atts = shortcode_atts( array(
			"image" => "",
			"link" => "",
			"class" => ""
		), $atts );

		$url = vc_build_link( $atts['link'] );
		$image = wp_get_attachment_image($atts['image'], 'full');

		ob_start();
	?>
		<div class="vu_client<?php vu_extra_class($atts['class']); ?>">
			<?php 
				if ( strlen( $atts['link'] ) > 0 && strlen( $url['url'] ) > 0 ) {
					echo '<a href="'. esc_url( $url['url'] ) .'" title="'. esc_attr( $url['title'] ) .'" target="'. ( strlen( $url['target'] ) > 0 ? esc_attr( $url['target'] ) : '_self' ) . '">'. $image .'</a>';
				} else {
					echo $image;
				}
			?>
		</div>
	<?php
		$output = ob_get_contents();
		ob_end_clean();
		
		return $output;
	}

	add_shortcode('vu_client', 'vu_client_shortcode');

	/**
	 * Client VC Shortcode
	 */

	if( class_exists('WPBakeryShortCode') ) {
		class WPBakeryShortCode_vu_client extends WPBakeryShortCode {
			public function content($atts, $content = null) {
				$atts = vc_map_get_attributes("vu_client", $atts);

				return do_shortcode( vu_generate_shortcode('vu_client', $atts, $content) );
			}
		}

		vc_map(
			array(
				"name"		=> __("Client", 'bakery'),
				"description" => __('Add a client/partner', 'bakery'),
				"base"		=> "vu_client",
				"class"		=> "vc_vu_client",
				"icon"		=> "vu_client-icon",
				"custom_markup" => '<input type="hidden" class="wpb_vc_param_value image attach_image" name="image" value="1"><h4 class="wpb_element_title">'. __('Client', 'bakery') .' <img width="150" height="150" src="'. vc_asset_url('vc/blank.gif') .'" class="attachment-thumbnail vc_element-icon" data-name="image" alt="" title="" style="display: none;"><span class="no_image_image vc_element-icon vu_client-icon"></span></h4>',
				"controls"	=> "full",
				"category"  => __('Bakery', 'bakery'),
				"params"	=> array(
					array(
						"type" => "attach_image",
						"heading" => __("Image", 'bakery'),
						"param_name" => "image",
						"value" => "",
						"save_always" => true,
						"description" => __("Select image from media library.", 'bakery')
					),
					array(
						"type" => "vc_link",
						"heading" => __("URL (Link)", 'bakery'),
						"param_name" => "link",
						"value" => "",
						"save_always" => true,
						"description" => __("Add the button link, link target and link title.", 'bakery')
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