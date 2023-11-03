<?php 
	/**
	 * Title Shortcode
	 *
	 * @param string $atts['title']
	 * @param string $atts['subtitle']
	 * @param string $atts['alignment']
	 * @param string $atts['style']
	 * @param string $atts['class'] Add a class name and then refer to it in your css file.
	 */

	function vu_title_shortcode( $atts, $content = null ) {
		$atts = shortcode_atts( array(
			'title' => '',
			'subtitle' => '',
			'alignment' => 'center',
			'style' => 'vu_overline',
			'class' => ''
		), $atts );

		ob_start();
	?>
		<header class="section-header alignment-<?php echo esc_attr($atts['alignment']) .' '. esc_attr($atts['style']) . ((!empty($atts['class'])) ? ' '. esc_attr($atts['class']) : '') . vu_animation(false); ?>">
			<h2><span><?php echo esc_html($atts['title']); ?></span></h2>
			<?php echo !empty($atts['subtitle']) ? '<p>'. wp_kses($atts['subtitle'], array('br' => array())) .'</p>' : ''; ?>
		</header>
	<?php
		$output = ob_get_contents();
		ob_end_clean();
		
		return $output;
	}

	add_shortcode('vu_title', 'vu_title_shortcode');

	/**
	 * Title VC Shortcode
	 */

	if( class_exists('WPBakeryShortCode') ) {
		class WPBakeryShortCode_vu_title extends WPBakeryShortCode {
			public function content($atts, $content = null) {
				$atts = vc_map_get_attributes("vu_title", $atts);

				return do_shortcode( vu_generate_shortcode('vu_title', $atts, $content) );
			}
		}

		vc_map(
			array(
				"name"		=> __("Title Section", 'bakery'),
				"description" => __('Add title section', 'bakery'),
				"base"		=> "vu_title",
				"class"		=> "vc_vu_title",
				"icon"		=> "vu_element-icon vu_title-icon",
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
						"description" => __("Enter text which will be used as title. Leave blank if no title is needed.", 'bakery')
					),
					array(
						"type" => "textarea",
						"heading" => __("Subtitle", 'bakery'),
						"param_name" => "subtitle",
						"value" => "",
						"save_always" => true,
						"description" => __("Enter text which will be used as subtitle. Leave blank if no subtitle is needed.", 'bakery')
					),
					array(
						"type" => "dropdown",
						"heading" => __("Style", 'bakery'),
						"param_name" => "style",
						"value" => array(
							__('Overline', 'bakery') => 'vu_overline',
							__('Inline', 'bakery') => 'vu_inline'
						),
						"save_always" => true,
						"description" => __("Select title style.", 'bakery')
					),
					array(
						"type" => "dropdown",
						"heading" => __("Alignment", 'bakery'),
						"param_name" => "alignment",
						"value" => array(
							__('Left', 'bakery') => 'left',
							__('Center', 'bakery') => 'center',
							__('Right', 'bakery') => 'right'
						),
						"save_always" => true,
						"description" => __("Select title alignment.", 'bakery')
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