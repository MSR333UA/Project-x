<?php 
	
	if( !function_exists("vu_vc_remove_meta_box") ){
		function vu_vc_remove_meta_box() {
			remove_meta_box("vc_teaser", "page", "side");
		}
	}

	add_action( "admin_head", "vu_vc_remove_meta_box" );

	if( function_exists('vc_remove_element') ) {
		vc_remove_element("vc_button");
		vc_remove_element("vc_button2");
		vc_remove_element("vc_cta_button");
		vc_remove_element("vc_cta_button2");
		vc_remove_element("vc_pie");
		vc_remove_element("vc_cta");
		vc_remove_element("vc_tabs");
		vc_remove_element("vc_tour");
		vc_remove_element("vc_accordion");
	}

	// Remove params for [vc_row] shortcode
	if( function_exists('vc_remove_param') ) {
		vc_remove_param( "vc_row", "full_width" );
		vc_remove_param( "vc_row", "full_height" );
		vc_remove_param( "vc_row", "content_placement" );
		vc_remove_param( "vc_row", "parallax" );
		vc_remove_param( "vc_row", "parallax_image" );
		vc_remove_param( "vc_row", "video_bg" );
		vc_remove_param( "vc_row", "video_bg_url" );
		vc_remove_param( "vc_row", "video_bg_parallax" );
		vc_remove_param( "vc_row", "parallax_speed_video" );
		vc_remove_param( "vc_row", "parallax_speed_bg" );
		vc_remove_param( "vc_row", "gap" );
		vc_remove_param( "vc_row", "disable_element" );
		vc_remove_param( "vc_row", "columns_placement" );
		vc_remove_param( "vc_row", "equal_height" );
		vc_remove_param( "vc_row", "el_id" );
		vc_remove_param( "vc_row", "el_class" );
	}

	// Add extra params for [vc_row] shortcode
	if( function_exists('vc_add_params') ) {
		vc_add_params("vc_row", array(
			array(
				"group" => "General",
				"type" => "dropdown",
				"heading" => __("Type", 'bakery'),
				"param_name" => "type",
				"weight" => 99,
				"value" => array(
					__("In Container", 'bakery') => "in_container",
					__("Full Width Content", 'bakery') => "full_width_content"		
				),
				"save_always" => true,
				"description" => __("", 'bakery')
			),
			array(
				"group" => "General",
				"type" => "checkbox",
				"heading" => __("Parallax", 'bakery'),
				"param_name" => "bg_parallax",
				"weight" => 98,
				"value" => array( __("Yes Please", 'bakery') => "true"),
				"save_always" => true,
				"description" => __("Add parallax type background for row (Note: If no image is specified, parallax will use background image from Design Options).", 'bakery')
			),
			array(
				"group" => "General",
				"type" => "attach_image",
				"heading" => __("Image", 'bakery'),
				"param_name" => "vu_parallax_image",
				"dependency" => array("element" => "bg_parallax", "value" => "true"),
				"weight" => 97,
				"value" => "",
				"save_always" => true,
				"description" => __("Select image from media library.", 'bakery')
			),
			array(
				"group" => "General",
				"type" => "colorpicker",
				"heading" => __("Color Overlay", 'bakery'),
				"param_name" => "color_overlay",
				"weight" => 96,
				"value" => "",
				"save_always" => true,
				"description" => ""
			),
			array(
				"group" => "General",
				"type" => "checkbox",
				"heading" => __("Equal Height Columns", 'bakery'),
				"param_name" => "vu_equal_height_columns",
				"weight" => 95,
				"value" => array( __("Yes Please", 'bakery') => "true"),
				"save_always" => true,
				"description" => __("Makes the height of all columns elements exactly equal.", 'bakery')
			),
			array(
				"group" => "General",
				"type" => "textfield",
				"heading" => __("Row ID", 'bakery'),
				"param_name" => "row_id",
				"weight" => 94,
				"value" => "",
				"save_always" => true,
				"description" => __("Use this to option to add an ID onto your row. This can then be used to target the row with CSS or as an anchor point to scroll to when the relevant link is clicked.", 'bakery')
			),
			array(
				"group" => "General",
				"type" => "textfield",
				"heading" => __("Extra class name", 'bakery'),
				"param_name" => "el_class",
				"weight" => 93,
				"value" => "",
				"save_always" => true,
				"description" => __("If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", 'bakery')
			)
		));

		// Add extra params for [vc_column] shortcode
		vc_add_params("vc_column", array(
			array(
				"type" => "dropdown",
				"heading" => __("Vertical Align", 'bakery'),
				"param_name" => "vu_vertical_align",
				"weight" => 99,
				"value" => array(
					__("Top", 'bakery') => "top",
					__("Middle", 'bakery') => "middle",
					__("Bottom", 'bakery') => "bottom"	
				),
				"std" => "middle",
				"save_always" => true,
				"description" => __("", 'bakery')
			)
		));
	}
?>