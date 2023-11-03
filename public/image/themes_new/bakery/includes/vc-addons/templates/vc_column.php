<?php 
	/**
	 * @var $this WPBakeryShortCode_VC_Column
	 */

	$output = $font_color = $vu_vertical_align = $el_class = $width = $offset = '';

	extract( shortcode_atts( array(
		'vu_vertical_align' => 'top',
		'el_class' => '',
		'width' => '1/1',
		'css' => '',
		'offset' => ''
	), $atts ) );

	$el_class = $this->getExtraClass( $el_class );
	$el_class .= ' wpb_column vc_column_container';

	$el_class .= ' col-'. $vu_vertical_align;
	
	$width = wpb_translateColumnWidthToSpan( $width );
	$width = vc_column_offset_class_merge( $offset, $width );

	$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, $width . $el_class, $this->settings['base'], $atts );
	
	$output .= "\n\t" . '<div class="' . $css_class . ' ' . vc_shortcode_custom_css_class( $css, ' ' ) . '">';
	$output .= "\n\t\t" . '<div class="wpb_wrapper">';
	$output .= "\n\t\t\t" . wpb_js_remove_wpautop( $content );
	$output .= "\n\t\t" . '</div> ' . $this->endBlockComment( '.wpb_wrapper' );
	$output .= "\n\t" . '</div> ' . $this->endBlockComment( $el_class ) . "\n";
	
	echo $output;
?>