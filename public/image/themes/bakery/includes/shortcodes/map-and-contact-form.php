<?php
	/**
	 * Map & Contact Form Shortcode
	 *
	 * @param string $atts['title']
	 * @param string $atts['subtitle']
	 * @param string $atts['name_placeholder']
	 * @param string $atts['email_placeholder']
	 * @param string $atts['message_placeholder']
	 * @param string $atts['submit_value']
	 * @param string $atts['class'] Add a class name and then refer to it in your css file.
	 */

	function vu_map_and_contact_form_shortcode($atts, $content = null) {
		$atts = shortcode_atts( array(
					"title" => "",
					"subtitle" => "",
					"name_placeholder" => "",
					"email_placeholder" => "",
					"show_phone" => "",
					"phone_placeholder" => "",
					"message_placeholder" => "",
					"submit_value" => "",
					"class" => ""
				), $atts);

				//map options
				$map_options = vu_get_map_options();

				if( $map_options['map_type'] == "roadmap" ){
					$map_style_array = @explode('#', vu_get_option('map-style'));
					$map_style = isset($map_style_array[1]) ? $map_style_array[1] : null;

					$map_options['styles'] = vu_get_map_style($map_style);
				}

				$map_height = absint(vu_get_option('map-height'));

				wp_enqueue_script('google-map');

				ob_start();
			?>
				<div class="row section-content<?php vu_extra_class($atts['class']); ?>">
					<div class="vu_map-absolute">
						<div class="vu_map vu_m-fullwith vu_m-fullheight" data-options="<?php echo esc_attr(json_encode($map_options)); ?>"></div>
					</div>
					
					<div class="container">
						<form class="form-contact vu_frm-ajax vu_clear-fields<?php vu_animation(true); ?>">
							<h2><?php echo esc_html($atts['title']); ?></h2>
							<p><?php echo esc_attr($atts['subtitle']); ?></p>

							<div class="hide">
								<input type="hidden" name="action" value="vu_send_mail">
								<input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('vu_contact_form_nonce'); ?>">
							</div>
							
							<div class="row">
								<div class="col-xs-12 m-t-5">
									<input type="text" class="form-control m-t-5" data-fucus="true" name="name" placeholder="<?php echo esc_attr($atts['name_placeholder']); ?>">
								</div>
								<div class="col-xs-12">
									<input type="text" name="email" class="form-control" placeholder="<?php echo esc_attr($atts['email_placeholder']); ?>">
								</div>
								<?php if( $atts['show_phone'] == '1' ) : ?>
									<div class="col-xs-12">
										<input type="text" name="phone" class="form-control" placeholder="<?php echo esc_attr($atts['phone_placeholder']); ?>">
									</div>
								<?php endif; ?>
								<div class="col-xs-12">
									<textarea name="message" class="form-control" rows="4" placeholder="<?php echo esc_attr($atts['message_placeholder']); ?>"></textarea>
								</div>
							</div>

							<div class="vu_msg m-t-10"></div>

							<div class="text-center">
								<button type="submit" class="btn btn-inverse m-t-5 m-b-10"><?php echo esc_attr($atts['submit_value']); ?></button>
							</div>
						</form>
					</div>
				</div>
			<?php
				$output = ob_get_contents();
				ob_end_clean();
				
				return $output;
	}

	add_shortcode('vu_map_and_contact_form', 'vu_map_and_contact_form_shortcode');
	
	/**
	 * Map & Contact Form VC Shortcode
	 */

	if( class_exists('WPBakeryShortCode') ){
		class WPBakeryShortCode_vu_map_and_contact_form extends WPBakeryShortCode {
			public function content($atts, $content = null) {
				$atts = vc_map_get_attributes("vu_map_and_contact_form", $atts);

				return do_shortcode( vu_generate_shortcode('vu_map_and_contact_form', $atts, $content) );
			}
		}

		vc_map(
			array(
				"name"		=> __("Map & Contact Form", 'bakery'),
				"description" => __('Add the map and the contact form', 'bakery'),
				"base"		=> "vu_map_and_contact_form",
				"class"		=> "vc_vu_map_and_contact_form",
				"icon"		=> "vu_element-icon vu_map-contact-form-icon",
				"controls"	=> "full",
				"category"  => __('Bakery', 'bakery'),
				"params"	=> array(
					array(
						"type" => "textfield",
						"heading" => __("Title", 'bakery'),
						"param_name" => "title",
						"admin_label" => true,
						"value" => __("Contact Us", 'bakery'),
						"save_always" => true,
						"description" => __("Enter section title of contact form.", 'bakery')
					),
					array(
						"type" => "textarea",
						"heading" => __("Subtitle", 'bakery'),
						"param_name" => "subtitle",
						"value" => __("Our Company is the best, meet the creative team that never sleeps. Say something to us we will answer to you.", 'bakery'),
						"save_always" => true,
						"description" => __("Enter subtitle.", 'bakery')
					),
					array(
						"type" => "textfield",
						"heading" => __("Name placeholder", 'bakery'),
						"param_name" => "name_placeholder",
						"value" => __('Your name here', 'bakery'),
						"save_always" => true,
						"description" => __("Enter name placeholder.", 'bakery')
					),
					array(
						"type" => "textfield",
						"heading" => __("Email placeholder", 'bakery'),
						"param_name" => "email_placeholder",
						"value" => __('Your email here', 'bakery'),
						"save_always" => true,
						"description" => __("Enter email placeholder.", 'bakery')
					),
					array(
						"type" => "checkbox",
						"heading" => __("Show Phone", 'bakery'),
						"param_name" => "show_phone",
						"value" =>  array( __('Yes Please', 'bakery') => '1'),
						"std" => "0",
						"save_always" => true,
						"description" => __("Check to show phone field.", 'bakery')
					),
					array(
						"type" => "textfield",
						"heading" => __("Phone placeholder", 'bakery'),
						"param_name" => "phone_placeholder",
						"dependency" => array("element" => "show_phone", "value" => "1"),
						"value" => __("Your phone here", 'bakery'),
						"save_always" => true,
						"description" => __("Enter phone placeholder.", 'bakery')
					),
					array(
						"type" => "textfield",
						"heading" => __("Message placeholder", 'bakery'),
						"param_name" => "message_placeholder",
						"value" => __('Your message here', 'bakery'),
						"save_always" => true,
						"description" => __("Enter message placeholder.", 'bakery')
					),
					array(
						"type" => "textfield",
						"heading" => __("Submit value", 'bakery'),
						"param_name" => "submit_value",
						"value" => __('Send message', 'bakery'),
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