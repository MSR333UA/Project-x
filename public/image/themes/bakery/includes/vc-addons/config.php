<?php 
	function vu_vc_before_init() {
		if(function_exists('vc_manager')){
			vc_manager()->setAsNetworkPlugin(false);
		}
		
		if(function_exists('vc_set_as_theme')){
			vc_set_as_theme(false);
		}
		
		// Add VC editing mode for custom post type
		if( function_exists('vc_set_default_editor_post_types') ){
			vc_set_default_editor_post_types( array('page', 'portfolio') );
		}
	}

	add_action( 'init', 'vu_vc_before_init' );

	
	// Change VC tempaltes directory
	if( function_exists('vc_set_shortcodes_templates_dir') ){
		vc_set_shortcodes_templates_dir( THEME_DIR .'includes/vc-addons/templates' );
	}

	// Filter to replace default css class names for vc_row shortcode and vc_column
	function custom_css_classes_for_vc_row_and_vc_column( $class_string, $tag ) {
		if ( $tag == 'vc_row' || $tag == 'vc_row_inner' ) {
			$class_string = str_replace( 'vc_row-fluid', 'row-fluid', $class_string );
			$class_string = str_replace( 'vc_row', 'row', $class_string );
		}

		if ( $tag == 'vc_column' || $tag == 'vc_column_inner' ) {
			$class_string = preg_replace( '/vc_col-sm-(\d{1,2})/', 'col-sm-$1', $class_string );
		}

		$class_string = str_replace( array('wpb_row', 'wpb_column', 'vc_column_container', 'wpb_wrapper'), '', $class_string );

		$class_string = preg_replace('/\s+/', ' ', $class_string);

		return trim($class_string); // Important: you should always return modified or original $class_string
	}

	add_filter( 'vc_shortcodes_css_class', 'custom_css_classes_for_vc_row_and_vc_column', 10, 2 );
?>