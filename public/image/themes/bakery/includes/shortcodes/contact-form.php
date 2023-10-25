<?php
	/**
	 * Contact Form Shortcode
	 *
	 * @param string $atts['title']
	 * @param string $atts['class'] Add a class name and then refer to it in your css file.
	 */

	function vu_contact_form_shortcode($atts, $content = null) {
		$atts = shortcode_atts( array(
			"title" => "",
			"name_text" => "",
			"email_text" => "",
			"show_phone" => "",
			"phone_text" => "",
			"subject_text" => "",
			"submit_text" => "",
			"class" => ""
		), $atts, "vu_contact_form" );

		ob_start();
	?>
		<article<?php echo !empty($atts['class']) ? 'class="'. esc_attr($atts['class']) .'"' : ''; ?>>
			<h3 class="fs-15 text-upper m-t-30 m-b-20"><?php echo esc_html($atts['title']); ?></h3>

			<form class="form-contact-alt vu_frm-ajax vu_clear-fields">
				<div class="hide">
					<input type="hidden" name="action" value="vu_send_mail">
					<input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('vu_contact_form_nonce'); ?>">
				</div>

				<div class="row">
					<div class="col-sm-5 pull-right-sm input-description">
						<i class="fa fa-user"></i> <?php echo esc_html($atts['name_text']); ?>
					</div>
					<div class="col-sm-7 pull-left-sm">
						<input type="text" name="name" class="form-control" data-focus="true">
					</div>
				</div>
				
				<div class="row">
					<div class="col-sm-5 pull-right-sm input-description">
						<i class="fa fa-envelope"></i> <?php echo esc_html($atts['email_text']); ?>
					</div>
					<div class="col-sm-7 pull-left-sm">
						<input type="text" name="email" class="form-control">
					</div>
				</div>
				
				<?php if( $atts['show_phone'] == '1' ) : ?>
					<div class="row">
						<div class="col-sm-5 pull-right-sm input-description">
							<i class="fa fa-phone"></i> <?php echo esc_html($atts['phone_text']); ?>
						</div>
						<div class="col-sm-7 pull-left-sm">
							<input type="text" name="phone" class="form-control">
						</div>
					</div>
				<?php endif; ?>

				<div class="row">
					<div class="col-sm-5 pull-right-sm input-description">
						<i class="fa fa-file"></i> <?php echo esc_html($atts['subject_text']); ?>
					</div>
					<div class="col-sm-7 pull-left-sm">
						<input type="text" name="subject" class="form-control">
					</div>
				</div>

				<div class="row">
					<div class="col-sm-12">
						<textarea name="message" rows="4" class="form-control"></textarea>
					</div>
				</div>

				<div class="clearfix"></div>

				<div class="vu_msg m-t-10"></div>

				<div class="row m-t-15 m-b-15">
					<div class="col-xs-4">
						<div class="pull-left vu_progress hide" style="height: 64px;"></div>
					</div>

					<div class="col-xs-8 text-right">
						<div class="submit-container no-margin">
							<button type="submit" class="btn"><i class="fa fa-envelope-o"></i> <?php echo esc_html($atts['submit_text']); ?></button>
						</div>
					</div>
				</div>
			</form>
		</article>
	<?php
		$output = ob_get_contents();
		ob_end_clean();
		
		return $output;
	}

	add_shortcode('vu_contact_form', 'vu_contact_form_shortcode');
	
	/**
	 * Contact Form VC Shortcode
	 */

	if( class_exists('WPBakeryShortCode') ){
		class WPBakeryShortCode_vu_contact_form extends WPBakeryShortCode {
			public function content($atts, $content = null) {
				$atts = vc_map_get_attributes("vu_contact_form", $atts);

				return do_shortcode( vu_generate_shortcode('vu_contact_form', $atts, $content) );
			}
		}

		vc_map(
			array(
				"name"		=> __("Contact Form", 'bakery'),
				"description" => __('Add the contact form', 'bakery'),
				"base"		=> "vu_contact_form",
				"class"		=> "vc_vu_contact_form",
				"icon"		=> "vu_element-icon vu_contact-form-icon",
				"controls"	=> "full",
				"category"  => __('Bakery', 'bakery'),
				"params"	=> array(
					array(
						"type" => "textfield",
						"heading" => __("Title", 'bakery'),
						"param_name" => "title",
						"admin_label" => true,
						"value" => "",
						"save_always" => true,
						"description" => __("Enter contact form title.", 'bakery')
					),
					array(
						"type" => "textfield",
						"heading" => __("Name Text", 'bakery'),
						"param_name" => "name_text",
						"value" => __("Name", 'bakery'),
						"save_always" => true,
						"description" => __("Enter name text.", 'bakery')
					),
					array(
						"type" => "textfield",
						"heading" => __("Email Text", 'bakery'),
						"param_name" => "email_text",
						"value" => __("Email", 'bakery'),
						"save_always" => true,
						"description" => __("Enter email text.", 'bakery')
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
						"heading" => __("Phone Text", 'bakery'),
						"param_name" => "phone_text",
						"dependency" => array("element" => "show_phone", "value" => "1"),
						"value" => __("Phone", 'bakery'),
						"save_always" => true,
						"description" => __("Enter phone text.", 'bakery')
					),
					array(
						"type" => "textfield",
						"heading" => __("Subject Text", 'bakery'),
						"param_name" => "subject_text",
						"value" => __("Subject", 'bakery'),
						"save_always" => true,
						"description" => __("Enter subject text.", 'bakery')
					),
					array(
						"type" => "textfield",
						"heading" => __("Submit Text", 'bakery'),
						"param_name" => "submit_text",
						"value" => __("Submit Message", 'bakery'),
						"save_always" => true,
						"description" => __("Enter subject text.", 'bakery')
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