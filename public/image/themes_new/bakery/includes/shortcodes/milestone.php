<?php 
	/**
	 * Title Shortcode
	 *
	 * @param string $atts['title']
	 * @param string $atts['counter']
	 * @param string $atts['delay']
	 * @param string $atts['time']
	 * @param string $atts['class'] Add a class name and then refer to it in your css file.
	 */

	function vu_milestone_shortcode( $atts, $content = null ) {
		$atts = shortcode_atts( array(
			'title' => '',
			'counter' => '',
			'delay' => '',
			'time' => '',
			'class' => ''
		), $atts );

		ob_start();
	?>
		<div class="vu_milestone<?php echo ((!empty($atts['class'])) ? ' '. esc_attr($atts['class']) : '') . vu_animation(false); ?>">
			<h2><span class="vu_counter"<?php echo ((!empty($atts['delay'])) ? ' data-delay="'. esc_attr($atts['delay']) .'"' : '') . ((!empty($atts['time'])) ? ' data-time="'. esc_attr($atts['time']) .'"' : ''); ?>><?php echo esc_html($atts['counter']); ?></span></h2>
			<h6><?php echo esc_html($atts['title']); ?></h6>
		</div>
	<?php
		$output = ob_get_contents();
		ob_end_clean();
		
		return $output;
	}

	add_shortcode('vu_milestone', 'vu_milestone_shortcode');

	/**
	 * Title VC Shortcode
	 */

	if( class_exists('WPBakeryShortCode') ) {
		class WPBakeryShortCode_vu_milestone extends WPBakeryShortCode {
			public function content($atts, $content = null) {
				$atts = vc_map_get_attributes("vu_milestone", $atts);

				return do_shortcode( vu_generate_shortcode('vu_milestone', $atts, $content) );
			}
		}

		vc_map(
			array(
				"name"		=> __("Milestone", 'bakery'),
				"description" => __('Add milestone', 'bakery'),
				"base"		=> "vu_milestone",
				"class"		=> "vc_vu_milestone",
				"icon"		=> "vu_element-icon vu_milestone-icon",
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
						"description" => __("Enter milestone text.", 'bakery')
					),
					array(
						"type" => "textfield",
						"heading" => __("Counter", 'bakery'),
						"param_name" => "counter",
						"value" => "",
						"save_always" => true,
						"description" => __("Enter milestone counter.", 'bakery')
					),
					array(
						"type" => "textfield",
						"heading" => __("Delay", 'bakery'),
						"param_name" => "delay",
						"value" => "",
						"save_always" => true,
						"description" => __("Enter time delay of milestone counter. The default delay is 10ms.", 'bakery')
					),
					array(
						"type" => "textfield",
						"heading" => __("Time", 'bakery'),
						"param_name" => "time",
						"value" => "",
						"save_always" => true,
						"description" => __("Enter time between each counter increment - default 1000 milliseconds?", 'bakery')
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