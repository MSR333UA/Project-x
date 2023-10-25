<?php
/** @var $this WPBakeryShortCode_VC_Row */
$output = $bg_image = $bg_color = $bg_image_repeat = $font_color = $padding = $margin_bottom = $type = $vu_parallax = $vu_parallax_image = $color_overlay = $vu_equal_height_columns = $row_id = $el_class = $css = $vu_bg_image = '';
extract( shortcode_atts( array(
	'bg_image' => '',
	'bg_color' => '',
	'bg_image_repeat' => '',
	'font_color' => '',
	'padding' => '',
	'margin_bottom' => '',
	'css' => '',
	'type' => 'in_container',
	'bg_parallax' => '',
	'vu_parallax_image' => '',
	'color_overlay' => '',
	'vu_equal_height_columns' => '',
	'row_id' => '',
	'el_class' => '',
), $atts ) );

$vu_parallax = ($bg_parallax == 'true') ? '1' : '0';

$el_class = $this->getExtraClass( $el_class );

$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, 'vc_row wpb_row ' . ( $this->settings( 'base' ) === 'vc_row_inner' ? 'vc_inner ' : '' ) . (($vu_parallax == '1' || !empty($color_overlay)) ? ' vu_r-with-parallax' : ''), $this->settings['base'], $atts );

//Get bg image url from css code
if( preg_match('~\bbackground(-image)?\s*:(.*?)\(\s*(\'|")?(?<image>.*?)\3?\s*\)~i', $css, $vu_bg_image) ) {
	$vu_bg_image = $vu_bg_image['image'];
}

//Parallax image
$vu_parallax_image = !empty($vu_parallax_image) ? vu_get_attachment_image_src($vu_parallax_image, 'full') : (!empty($vu_bg_image) ? $vu_bg_image : false);

$output .= '<div '. (!empty($row_id) ? 'id="'. esc_attr($row_id) .'" ' : '') .' class="'. esc_attr( $css_class ) .'">'; //Row

$output .= ($this->settings('base') === 'vc_row') ? '<div class="'. (!empty($color_overlay) ? 'vu_color-overlay ' : 'clearfix ') . vc_shortcode_custom_css_class( $css, ' ' ) . $el_class .'"'. ( !empty($color_overlay) ? ' data-color-overlay="'. esc_attr($color_overlay) .'"' : '' ) . ( ($vu_parallax == '1' && !empty($vu_parallax_image)) ? ' data-parallax="scroll" data-image-src="'. esc_url($vu_parallax_image) .'"' : '' ) . ( ($vu_equal_height_columns == 'true') ? ' data-equal-height-columns="true"' : '' ) .'>' : ''; //Color Overlay

$output .= ( ($type == 'in_container') && ($this->settings('base') === 'vc_row') ) ? '<div class="container no-padding">' : ''; //In Container

$output .= wpb_js_remove_wpautop( $content );

$output .= ( ($type == 'in_container') && ($this->settings('base') === 'vc_row') ) ? '</div>' : ''; //End In Container

$output .= ($this->settings('base') === 'vc_row') ? '</div>' : ''; //End Color Overlay

$output .= '</div>' . $this->endBlockComment( 'row' );

echo ($output);
?>