<?php
	/**
	 * Map Shortcode
	 *
	 * @param string $atts['class'] Add a class name and then refer to it in your css file.
	 */

	function vu_map_shortcode($atts, $content = null) {
		$atts = shortcode_atts( array(
			"ignore_options" => "",
			"center_lat" => "",
			"center_lng" => "",
			"zoom_level" => "",
			"map_height" => "",
			"map_type" => "",
			"map_style" => "",
			"tilt_45" => "",
			"draggable" => "",
			"zoom_control" => "",
			"disable_double_click_zoom" => "",
			"scrollwheel" => "",
			"pan_control" => "",
			"map_type_control" => "",
			"scale_control" => "",
			"street_view_control" => "",
			"use_marker_img" => "",
			"marker_img" => "",
			"enable_map_animation" => "",
			"latitude_1" => "",
			"longitude_1" => "",
			"map_info_1" => "",
			"latitude_2" => "",
			"longitude_2" => "",
			"map_info_2" => "",
			"latitude_3" => "",
			"longitude_3" => "",
			"map_info_3" => "",
			"latitude_4" => "",
			"longitude_4" => "",
			"map_info_4" => "",
			"latitude_5" => "",
			"longitude_5" => "",
			"map_info_5" => "",
			"latitude_6" => "",
			"longitude_6" => "",
			"map_info_6" => "",
			"latitude_7" => "",
			"longitude_7" => "",
			"map_info_7" => "",
			"latitude_8" => "",
			"longitude_8" => "",
			"map_info_8" => "",
			"latitude_9" => "",
			"longitude_9" => "",
			"map_info_9" => "",
			"latitude_10" => "",
			"longitude_10" => "",
			"map_info_10" => "",
			"class" => ""
		), $atts);

		//map options
		if( $atts['ignore_options'] == "1") {
			$map_options = array(
				"zoom_level" => esc_attr($atts['zoom_level']),
				"center_lat" => esc_attr($atts['center_lat']),
				"center_lng" => esc_attr($atts['center_lng']),
				"map_type" => esc_attr($atts['map_type']),
				"tilt_45" => esc_attr($atts['tilt_45']),
				"others_options" => array(
					"draggable" => esc_attr($atts['draggable']),
					"zoomControl" => esc_attr($atts['zoom_control']),
					"disableDoubleClickZoom" => esc_attr($atts['disable_double_click_zoom']),
					"scrollwheel" => esc_attr($atts['scrollwheel']),
					"panControl" => esc_attr($atts['pan_control']),
					"mapTypeControl" => esc_attr($atts['map_type_control']),
					"scaleControl" => esc_attr($atts['scale_control']),
					"streetViewControl" => esc_attr($atts['street_view_control'])
				),
				"use_marker_img" => esc_attr($atts['use_marker_img']),
				"marker_img" => esc_url(vu_get_attachment_image_src($atts['marker_img'], 'full')),
				"enable_animation" => esc_attr($atts['enable_map_animation']),
				"locations" => array()
			);

			//locations
			for($i=1; $i<=10; $i++){
				if( $atts['latitude_'. $i] != "" && $atts['longitude_'. $i] != "" ){
					array_push($map_options['locations'], array('lat' => esc_attr($atts['latitude_'. $i]), 'lng' => esc_attr($atts['longitude_'. $i]), 'info' => esc_attr($atts['map_info_'. $i])));
				}
			}
		} else {
			$map_options = vu_get_map_options();
		}

		//styles
		if( $map_options['map_type'] == "roadmap" ){
			$map_style_array = @explode('#', vu_get_option('map-style'));
			$map_style_temp = isset($map_style_array[1]) ? $map_style_array[1] : null;

			$map_style = ($atts['ignore_options'] == "1") ? $atts['map_style'] : $map_style_temp;
			
			$map_options['styles'] = vu_get_map_style($map_style);
		}

		wp_enqueue_script('google-map');

		$map_height = ($atts['ignore_options'] == "1") ? absint($atts['map_height']) : absint(vu_get_option('map-height'));

		return '<div class="vu_map vu_m-fullwith'. vu_extra_class($atts['class'], false) .'" data-options="'. esc_attr(json_encode($map_options)) .'"'. ($map_height > 0 ? ' style="height:'. $map_height .'px;"' : '') .'></div>';
	}

	add_shortcode('vu_map', 'vu_map_shortcode');
	
	/**
	 * Map VC Shortcode
	 */

	if( class_exists('WPBakeryShortCode') ){
		class WPBakeryShortCode_vu_map extends WPBakeryShortCode {
			public function content($atts, $content = null) {
				$atts = vc_map_get_attributes("vu_map", $atts);

				return do_shortcode( vu_generate_shortcode('vu_map', $atts, $content) );
			}
		}

		vc_map(
			array(
				"name"		=> __("Map", 'bakery'),
				"description" => __('Add the map', 'bakery'),
				"base"		=> "vu_map",
				"class"		=> "vc_vu_map",
				"icon"		=> "vu_element-icon vu_map-icon",
				"controls"	=> "full",
				"category"  => __('Bakery', 'bakery'),
				"params"	=> array(
					array(
						"group" => __('General', 'bakery'),
						"type" => "checkbox",
						"heading" => __("Do not Inherit from Theme Options", 'bakery'),
						"param_name" => "ignore_options",
						"admin_label" => true,
						"value" => array(__("Yes Please", 'bakery') => "1"),
						"std" => "0",
						"save_always" => true,
						"description" => __("", 'bakery')
					),
					array(
						"group" => __('General', 'bakery'),
						"type" => "textfield",
						"heading" => __("Map Center Latitude", 'bakery'),
						"param_name" => "center_lat",
						"dependency" => array("element" => "ignore_options", "value" => "1"),
						"value" => "",
						"save_always" => true,
						"description" => __("Please enter the latitude for the map center point.", 'bakery')
					),
					array(
						"group" => __('General', 'bakery'),
						"type" => "textfield",
						"heading" => __("Map Center Longitude", 'bakery'),
						"param_name" => "center_lng",
						"dependency" => array("element" => "ignore_options", "value" => "1"),
						"value" => "",
						"save_always" => true,
						"description" => __("Please enter the longitude for the map center point.", 'bakery')
					),
					array(
						"group" => __('General', 'bakery'),
						"type" => "textfield",
						"heading" => __("Default Map Zoom Level", 'bakery'),
						"param_name" => "zoom_level",
						"dependency" => array("element" => "ignore_options", "value" => "1"),
						"value" => "",
						"save_always" => true,
						"description" => __("Value should be between 1-18, 1 being the entire earth and 18 being right at street level.", 'bakery')
					),
					array(
						"group" => __('General', 'bakery'),
						"type" => "textfield",
						"heading" => __("Map Height", 'bakery'),
						"param_name" => "map_height",
						"dependency" => array("element" => "ignore_options", "value" => "1"),
						"value" => "",
						"save_always" => true,
						"description" => __("", 'bakery')
					),
					array(
						"group" => __('General', 'bakery'),
						"type" => "dropdown",
						"heading" => __("Map Type", 'bakery'),
						"param_name" => "map_type",
						"dependency" => array("element" => "ignore_options", "value" => "1"),
						"value" => array(
							__("Roadmap", 'bakery') => "roadmap",
							__("Satellite", 'bakery') => "satellite",
							__("Hybrid", 'bakery') => "hybrid",
							__("Terrain", 'bakery') => "terrain"
						),
						"save_always" => true,
						"description" => __("", 'bakery')
					),
					array(
						"group" => __('General', 'bakery'),
						"type" => "dropdown",
						"heading" => __("Map Style", 'bakery'),
						"param_name" => "map_style",
						"dependency" => array("element" => "map_type", "value" => "roadmap"),
						"value" => array(
							__("Default", 'bakery') => "0",
							__("Subtle Grayscale", 'bakery') => "1",
							__("Blue Water", 'bakery') => "2",
							__("Shades of Grey", 'bakery') => "3",
							__("Pale Dawn", 'bakery') => "4",
							__("Apple Maps-esque", 'bakery') => "5",
							__("Light Monochrome", 'bakery') => "6",
							__("Greyscale", 'bakery') => "7",
							__("Neutral Blue", 'bakery') => "8",
							__("Become a Dinosaur", 'bakery') => "9",
							__("Blue Gray", 'bakery') => "10",
							__("Icy Blue", 'bakery') => "11",
							__("Clean Cut", 'bakery') => "12",
							__("Muted Blue", 'bakery') => "13",
							__("Old Timey", 'bakery') => "14",
							__("Red Hues", 'bakery') => "15",
							__("Nature", 'bakery') => "16",
							__("Turquoise Water", 'bakery') => "17",
							__("Just Places", 'bakery') => "18",
							__("Ultra Light", 'bakery') => "19",
							__("Subtle Green", 'bakery') => "20",
							__("Simple & Light", 'bakery') => "21"
						),
						"save_always" => true,
						"description" => __("Please select the style the map want to look like.", 'bakery')
					),
					array(
						"group" => __('General', 'bakery'),
						"type" => "checkbox",
						"heading" => __("Tilt 45°", 'bakery'),
						"param_name" => "tilt_45",
						"dependency" => array("element" => "map_type", "value" => "satellite"),
						"value" => array(__("Yes Please", 'bakery') => "1"),
						"save_always" => true,
						"description" => __("", 'bakery')
					),
					array(
						"group" => __('General', 'bakery'),
						"type" => "checkbox",
						"heading" => __("Others Options", 'bakery'),
						"param_name" => "draggable",
						"dependency" => array("element" => "ignore_options", "value" => "1"),
						"value" => array(__("Draggable", 'bakery') => "1"),
						'std' => '1',
						"save_always" => true,
						"description" => __("", 'bakery')
					),
					array(
						"group" => __('General', 'bakery'),
						"type" => "checkbox",
						"edit_field_class" => "vc_col-sm-12 vc_column vu_p-t-0",
						"heading" => __("Others Options", 'bakery'),
						"param_name" => "zoom_control",
						"dependency" => array("element" => "ignore_options", "value" => "1"),
						"value" => array(__("Zoom Control", 'bakery') => "1"),
						"save_always" => true,
						"description" => __("", 'bakery')
					),
					array(
						"group" => __('General', 'bakery'),
						"type" => "checkbox",
						"edit_field_class" => "vc_col-sm-12 vc_column vu_p-t-0",
						"param_name" => "disable_double_click_zoom",
						"dependency" => array("element" => "ignore_options", "value" => "1"),
						"value" => array(__("Disable Double Click Zoom", 'bakery') => "1"),
						"save_always" => true,
						"description" => __("", 'bakery')
					),
					array(
						"group" => __('General', 'bakery'),
						"type" => "checkbox",
						"edit_field_class" => "vc_col-sm-12 vc_column vu_p-t-0",
						"param_name" => "scrollwheel",
						"dependency" => array("element" => "ignore_options", "value" => "1"),
						"value" => array(__("Scroll Wheel", 'bakery') => "1"),
						"save_always" => true,
						"description" => __("", 'bakery')
					),
					array(
						"group" => __('General', 'bakery'),
						"type" => "checkbox",
						"edit_field_class" => "vc_col-sm-12 vc_column vu_p-t-0",
						"param_name" => "pan_control",
						"dependency" => array("element" => "ignore_options", "value" => "1"),
						"value" => array(__("Pan Control", 'bakery') => "1"),
						"save_always" => true,
						"description" => __("", 'bakery')
					),
					array(
						"group" => __('General', 'bakery'),
						"type" => "checkbox",
						"edit_field_class" => "vc_col-sm-12 vc_column vu_p-t-0",
						"param_name" => "map_type_control",
						"dependency" => array("element" => "ignore_options", "value" => "1"),
						"value" => array(__("Map Type Control", 'bakery') => "1"),
						"save_always" => true,
						"description" => __("", 'bakery')
					),
					array(
						"group" => __('General', 'bakery'),
						"type" => "checkbox",
						"edit_field_class" => "vc_col-sm-12 vc_column vu_p-t-0",
						"param_name" => "scale_control",
						"dependency" => array("element" => "ignore_options", "value" => "1"),
						"value" => array(__("Scale Control", 'bakery') => "1"),
						"save_always" => true,
						"description" => __("", 'bakery')
					),
					array(
						"group" => __('General', 'bakery'),
						"type" => "checkbox",
						"edit_field_class" => "vc_col-sm-12 vc_column vu_p-t-0",
						"param_name" => "street_view_control",
						"dependency" => array("element" => "ignore_options", "value" => "1"),
						"value" => array(__("Street View Control", 'bakery') => "1"),
						"save_always" => true,
						"description" => __("Please select the options you want to apply on the map.", 'bakery')
					),
					array(
						"group" => __('General', 'bakery'),
						"type" => "textfield",
						"heading" => __("Extra class name", 'bakery'),
						"param_name" => "class",
						"dependency" => array("element" => "ignore_options", "value" => "1"),
						"value" => "",
						"save_always" => true,
						"description" => __("If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", 'bakery')
					),
					array(
						"group" => __('Marker', 'bakery'),
						"type" => "checkbox",
						"heading" => __("Use Image for Markers", 'bakery'),
						"param_name" => "use_marker_img",
						"dependency" => array("element" => "ignore_options", "value" => "1"),
						"value" => array(__("Yes Please", 'bakery') => "1"),
						"save_always" => true,
						"description" => __("Do you want to use a custom image for the map markers?", 'bakery')
					),
					array(
						"group" => __('Marker', 'bakery'),
						"type" => "attach_image",
						"heading" => __("Marker Icon Upload", 'bakery'),
						"param_name" => "marker_img",
						"dependency" => array("element" => "use_marker_img", "value" => "1"),
						"value" => "",
						"save_always" => true,
						"description" => __("Please upload the image that will be used for all the markers on the map.", 'bakery')
					),
					array(
						"group" => __('Marker', 'bakery'),
						"type" => "checkbox",
						"heading" => __("Enable Marker Animation", 'bakery'),
						"param_name" => "enable_map_animation",
						"dependency" => array("element" => "ignore_options", "value" => "1"),
						"value" => array(__("Yes Please", 'bakery') => "1"),
						"save_always" => true,
						"description" => __("This will cause your markers to do a quick bounce as they load in.", 'bakery')
					),
					array(
						"group" => __('Locations', 'bakery'),
						"type" => "textfield",
						"heading" => __("Latitude #1", 'bakery'),
						"param_name" => "latitude_1",
						"dependency" => array("element" => "ignore_options", "value" => "1"),
						"value" => "",
						"save_always" => true,
						"description" => __("Please enter the latitude for your #1 location.", 'bakery')
					),
					array(
						"group" => __('Locations', 'bakery'),
						"type" => "textfield",
						"heading" => __("Longitude #1", 'bakery'),
						"param_name" => "longitude_1",
						"dependency" => array("element" => "ignore_options", "value" => "1"),
						"value" => "",
						"save_always" => true,
						"description" => __("Please enter the longitude for your #1 location.", 'bakery')
					),
					array(
						"group" => __('Locations', 'bakery'),
						"type" => "textarea",
						"heading" => __("Map Infowindow Text #1", 'bakery'),
						"param_name" => "map_info_1",
						"dependency" => array("element" => "ignore_options", "value" => "1"),
						"value" => "",
						"save_always" => true,
						"description" => __("Please enter the latitude for your #1 location.", 'bakery')
					),
					array(
						"group" => __('Locations', 'bakery'),
						"type" => "textfield",
						"heading" => __("Latitude #2", 'bakery'),
						"param_name" => "latitude_2",
						"dependency" => array("element" => "ignore_options", "value" => "1"),
						"value" => "",
						"save_always" => true,
						"description" => __("Please enter the latitude for your #2 location.", 'bakery')
					),
					array(
						"group" => __('Locations', 'bakery'),
						"type" => "textfield",
						"heading" => __("Longitude #2", 'bakery'),
						"param_name" => "longitude_2",
						"dependency" => array("element" => "ignore_options", "value" => "1"),
						"value" => "",
						"save_always" => true,
						"description" => __("Please enter the longitude for your #2 location.", 'bakery')
					),
					array(
						"group" => __('Locations', 'bakery'),
						"type" => "textarea",
						"heading" => __("Map Infowindow Text #2", 'bakery'),
						"param_name" => "map_info_2",
						"dependency" => array("element" => "ignore_options", "value" => "1"),
						"value" => "",
						"save_always" => true,
						"description" => __("Please enter the latitude for your #2 location.", 'bakery')
					),
					array(
						"group" => __('Locations', 'bakery'),
						"type" => "textfield",
						"heading" => __("Latitude #3", 'bakery'),
						"param_name" => "latitude_3",
						"dependency" => array("element" => "ignore_options", "value" => "1"),
						"value" => "",
						"save_always" => true,
						"description" => __("Please enter the latitude for your #3 location.", 'bakery')
					),
					array(
						"group" => __('Locations', 'bakery'),
						"type" => "textfield",
						"heading" => __("Longitude #3", 'bakery'),
						"param_name" => "longitude_3",
						"dependency" => array("element" => "ignore_options", "value" => "1"),
						"value" => "",
						"save_always" => true,
						"description" => __("Please enter the longitude for your #3 location.", 'bakery')
					),
					array(
						"group" => __('Locations', 'bakery'),
						"type" => "textarea",
						"heading" => __("Map Infowindow Text #3", 'bakery'),
						"param_name" => "map_info_3",
						"dependency" => array("element" => "ignore_options", "value" => "1"),
						"value" => "",
						"save_always" => true,
						"description" => __("Please enter the latitude for your #3 location.", 'bakery')
					),
					array(
						"group" => __('Locations', 'bakery'),
						"type" => "textfield",
						"heading" => __("Latitude #4", 'bakery'),
						"param_name" => "latitude_4",
						"dependency" => array("element" => "ignore_options", "value" => "1"),
						"value" => "",
						"save_always" => true,
						"description" => __("Please enter the latitude for your #4 location.", 'bakery')
					),
					array(
						"group" => __('Locations', 'bakery'),
						"type" => "textfield",
						"heading" => __("Longitude #4", 'bakery'),
						"param_name" => "longitude_4",
						"dependency" => array("element" => "ignore_options", "value" => "1"),
						"value" => "",
						"save_always" => true,
						"description" => __("Please enter the longitude for your #4 location.", 'bakery')
					),
					array(
						"group" => __('Locations', 'bakery'),
						"type" => "textarea",
						"heading" => __("Map Infowindow Text #4", 'bakery'),
						"param_name" => "map_info_4",
						"dependency" => array("element" => "ignore_options", "value" => "1"),
						"value" => "",
						"save_always" => true,
						"description" => __("Please enter the latitude for your #4 location.", 'bakery')
					),
					array(
						"group" => __('Locations', 'bakery'),
						"type" => "textfield",
						"heading" => __("Latitude #5", 'bakery'),
						"param_name" => "latitude_5",
						"dependency" => array("element" => "ignore_options", "value" => "1"),
						"value" => "",
						"save_always" => true,
						"description" => __("Please enter the latitude for your #5 location.", 'bakery')
					),
					array(
						"group" => __('Locations', 'bakery'),
						"type" => "textfield",
						"heading" => __("Longitude #5", 'bakery'),
						"param_name" => "longitude_5",
						"dependency" => array("element" => "ignore_options", "value" => "1"),
						"value" => "",
						"save_always" => true,
						"description" => __("Please enter the longitude for your #5 location.", 'bakery')
					),
					array(
						"group" => __('Locations', 'bakery'),
						"type" => "textarea",
						"heading" => __("Map Infowindow Text #5", 'bakery'),
						"param_name" => "map_info_5",
						"dependency" => array("element" => "ignore_options", "value" => "1"),
						"value" => "",
						"save_always" => true,
						"description" => __("Please enter the latitude for your #5 location.", 'bakery')
					),
					array(
						"group" => __('Locations', 'bakery'),
						"type" => "textfield",
						"heading" => __("Latitude #6", 'bakery'),
						"param_name" => "latitude_6",
						"dependency" => array("element" => "ignore_options", "value" => "1"),
						"value" => "",
						"save_always" => true,
						"description" => __("Please enter the latitude for your #6 location.", 'bakery')
					),
					array(
						"group" => __('Locations', 'bakery'),
						"type" => "textfield",
						"heading" => __("Longitude #6", 'bakery'),
						"param_name" => "longitude_6",
						"dependency" => array("element" => "ignore_options", "value" => "1"),
						"value" => "",
						"save_always" => true,
						"description" => __("Please enter the longitude for your #6 location.", 'bakery')
					),
					array(
						"group" => __('Locations', 'bakery'),
						"type" => "textarea",
						"heading" => __("Map Infowindow Text #6", 'bakery'),
						"param_name" => "map_info_6",
						"dependency" => array("element" => "ignore_options", "value" => "1"),
						"value" => "",
						"save_always" => true,
						"description" => __("Please enter the latitude for your #6 location.", 'bakery')
					),
					array(
						"group" => __('Locations', 'bakery'),
						"type" => "textfield",
						"heading" => __("Latitude #7", 'bakery'),
						"param_name" => "latitude_7",
						"dependency" => array("element" => "ignore_options", "value" => "1"),
						"value" => "",
						"save_always" => true,
						"description" => __("Please enter the latitude for your #7 location.", 'bakery')
					),
					array(
						"group" => __('Locations', 'bakery'),
						"type" => "textfield",
						"heading" => __("Longitude #7", 'bakery'),
						"param_name" => "longitude_7",
						"dependency" => array("element" => "ignore_options", "value" => "1"),
						"value" => "",
						"save_always" => true,
						"description" => __("Please enter the longitude for your #7 location.", 'bakery')
					),
					array(
						"group" => __('Locations', 'bakery'),
						"type" => "textarea",
						"heading" => __("Map Infowindow Text #7", 'bakery'),
						"param_name" => "map_info_7",
						"dependency" => array("element" => "ignore_options", "value" => "1"),
						"value" => "",
						"save_always" => true,
						"description" => __("Please enter the latitude for your #7 location.", 'bakery')
					),
					array(
						"group" => __('Locations', 'bakery'),
						"type" => "textfield",
						"heading" => __("Latitude #8", 'bakery'),
						"param_name" => "latitude_8",
						"dependency" => array("element" => "ignore_options", "value" => "1"),
						"value" => "",
						"save_always" => true,
						"description" => __("Please enter the latitude for your #8 location.", 'bakery')
					),
					array(
						"group" => __('Locations', 'bakery'),
						"type" => "textfield",
						"heading" => __("Longitude #8", 'bakery'),
						"param_name" => "longitude_8",
						"dependency" => array("element" => "ignore_options", "value" => "1"),
						"value" => "",
						"save_always" => true,
						"description" => __("Please enter the longitude for your #8 location.", 'bakery')
					),
					array(
						"group" => __('Locations', 'bakery'),
						"type" => "textarea",
						"heading" => __("Map Infowindow Text #8", 'bakery'),
						"param_name" => "map_info_8",
						"dependency" => array("element" => "ignore_options", "value" => "1"),
						"value" => "",
						"save_always" => true,
						"description" => __("Please enter the latitude for your #8 location.", 'bakery')
					),
					array(
						"group" => __('Locations', 'bakery'),
						"type" => "textfield",
						"heading" => __("Latitude #9", 'bakery'),
						"param_name" => "latitude_9",
						"dependency" => array("element" => "ignore_options", "value" => "1"),
						"value" => "",
						"save_always" => true,
						"description" => __("Please enter the latitude for your #9 location.", 'bakery')
					),
					array(
						"group" => __('Locations', 'bakery'),
						"type" => "textfield",
						"heading" => __("Longitude #9", 'bakery'),
						"param_name" => "longitude_9",
						"dependency" => array("element" => "ignore_options", "value" => "1"),
						"value" => "",
						"save_always" => true,
						"description" => __("Please enter the longitude for your #9 location.", 'bakery')
					),
					array(
						"group" => __('Locations', 'bakery'),
						"type" => "textarea",
						"heading" => __("Map Infowindow Text #9", 'bakery'),
						"param_name" => "map_info_9",
						"dependency" => array("element" => "ignore_options", "value" => "1"),
						"value" => "",
						"save_always" => true,
						"description" => __("Please enter the latitude for your #9 location.", 'bakery')
					),
					array(
						"group" => __('Locations', 'bakery'),
						"type" => "textfield",
						"heading" => __("Latitude #10", 'bakery'),
						"param_name" => "latitude_10",
						"dependency" => array("element" => "ignore_options", "value" => "1"),
						"value" => "",
						"save_always" => true,
						"description" => __("Please enter the latitude for your #10 location.", 'bakery')
					),
					array(
						"group" => __('Locations', 'bakery'),
						"type" => "textfield",
						"heading" => __("Longitude #10", 'bakery'),
						"param_name" => "longitude_10",
						"dependency" => array("element" => "ignore_options", "value" => "1"),
						"value" => "",
						"save_always" => true,
						"description" => __("Please enter the longitude for your #10 location.", 'bakery')
					),
					array(
						"group" => __('Locations', 'bakery'),
						"type" => "textarea",
						"heading" => __("Map Infowindow Text #10", 'bakery'),
						"param_name" => "map_info_10",
						"dependency" => array("element" => "ignore_options", "value" => "1"),
						"value" => "",
						"save_always" => true,
						"description" => __("Please enter the latitude for your #10 location.", 'bakery')
					)
				)
			)
		);
	}
?>