<?php 
	/**
	 * Count up Shortcode
	 *
	 * @param string $atts['date']
	 * @param string $atts['format']
	 * @param string $atts['class'] Add a class name and then refer to it in your css file.
	 */

	function vu_count_up_shortcode( $atts, $content = null ) {
		$atts = shortcode_atts( array(
			'date' => '',
			'format' => 'dHMS',
			'class' => ''
		), $atts );

		wp_enqueue_script('countdown');

		ob_start();
	?>
		<div class="vu_count-up clearfix<?php echo ((!empty($atts['class'])) ? ' '. esc_attr($atts['class']) : ''); ?>" data-date="<?php echo esc_attr($atts['date']); ?>" data-format="<?php echo esc_attr($atts['format']); ?>"></div>
	<?php
		$output = ob_get_contents();
		ob_end_clean();
		
		return $output;
	}

	add_shortcode('vu_count_up', 'vu_count_up_shortcode');

	/**
	 * Count up VC Shortcode
	 */

	if( class_exists('WPBakeryShortCode') ) {
		class WPBakeryShortCode_vu_count_up extends WPBakeryShortCode {
			public function content($atts, $content = null) {
				$atts = vc_map_get_attributes("vu_count_up", $atts);

				return do_shortcode( vu_generate_shortcode('vu_count_up', $atts, $content) );
			}
		}

		vc_map(
			array(
				"name"		=> __("Count up", 'bakery'),
				"description" => __('Add count up', 'bakery'),
				"base"		=> "vu_count_up",
				"class"		=> "vc_vu_count_up",
				"icon"		=> "vu_element-icon vu_count-up-icon",
				"controls"	=> "full",
				"category"  => __('Bakery', 'bakery'),
				"params"	=> array(
					array(
						"type" => "textfield",
						"heading" => __("Date", 'bakery'),
						"param_name" => "date",
						"admin_label" => true,
						"value" => "",
						"save_always" => true,
						"description" => __("Enter date in <b>yyyy-mm-dd</b> format.", 'bakery')
					),
					array(
						"type" => "textfield",
						"heading" => __("Format", 'bakery'),
						"param_name" => "format",
						"admin_label" => true,
						"value" => "dHMS",
						"save_always" => true,
						"description" => __("Enter date format. Default is <b>dHMS</b>.", 'bakery')
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