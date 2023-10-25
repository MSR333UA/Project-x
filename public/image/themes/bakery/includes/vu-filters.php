<?php
	/**
	 *	Bakery WordPress Theme
	 */

	// Allow Shortcodes to be used on Text Widgets
	add_filter('widget_text', 'do_shortcode');

	// Filter wp_title
	function vu_wp_title( $title, $sep ) {
		$title = get_bloginfo('name') ." | ". ((is_home() || is_front_page()) ? get_bloginfo('description') : $title);

		return $title;
	}

	add_filter( 'wp_title', 'vu_wp_title', 10, 2 );

	// Show only blog posts on search
	function vu_show_only_blog_posts_on_search( $query ) {
		if ( !is_admin() && $query->is_main_query() && $query->is_search && get_query_var('post_type') == 'post' ) {
			$query->set('post_type', 'post');
			$query->set('posts_per_page', absint(get_option('posts_per_page')));
		}
	}

	add_action('pre_get_posts', 'vu_show_only_blog_posts_on_search');
	
	// Add 'itemprop' attribute for links in menu
	function vu_add_menu_atts( $atts, $item, $args ) {
		$atts['itemprop'] = 'url';
		return $atts;
	}

	add_filter( 'nav_menu_link_attributes', 'vu_add_menu_atts', 10, 3 );

	// Change number of products displayed per page
	function vu_loop_shop_per_page($cols){
		return absint(vu_get_option('product-count'));
	}
	
	add_filter( 'loop_shop_per_page', 'vu_loop_shop_per_page', 20 );

	// Changing the number of product columns
	function vu_loop_shop_columns($number_columns){
		return (vu_get_option('shop-layout') == 'no-sidebar') ? 4 : 3;
	}

	add_filter( 'loop_shop_columns', 'vu_loop_shop_columns', 1, 10 );

	// Widget Template for Fotter Bottom Sidebar
	function vu_footer_bottom_sidebar($params) {
		$id = $params[0]['id'];
		static $counter = 0;
		static $delay = 200;

		if ('footer-bottom-sidebar' !== $id) return $params;

		//delay animation
		$params[0]['before_widget'] = str_replace('delay_value', $delay, $params[0]['before_widget']);

		if ( 0 !== $counter and 0 === $counter % (12 / vu_get_option('footer-bottom-layout')) ){
			$params[0]['before_widget'] = '</div><div class="row">'. $params[0]['before_widget'];
			$delay = 100;
		}

		$counter++;
		$delay += 100;

		return $params;
	}

	add_filter('dynamic_sidebar_params', 'vu_footer_bottom_sidebar');

	// Widget Template for Fotter Top Sidebar
	function vu_footer_top_sidebar($params) {
		$id = $params[0]['id'];
		static $counter = 0;
		static $delay = 200;

		if ('footer-top-sidebar' !== $id) return $params;

		if ( 0 === $counter and vu_get_option('show-footer-top-logo') ){
			$params[0]['before_widget'] = '<div class="col-md-5 slim-right'. vu_animation(false, $delay) .'">'. $params[0]['before_widget'];
			$params[0]['after_widget'] .= '</div><div class="col-md-2 image-container"><img class="m-b-50" alt="" src="'. vu_get_option( array('footer-top-logo', 'url') ) .'"></div>';
		} elseif ( 1 === $counter and vu_get_option('show-footer-top-logo') ){
			$params[0]['before_widget'] = '<div class="col-md-5 slim-left'. vu_animation(false, $delay) .'">'. $params[0]['before_widget'];
			$params[0]['after_widget'] = '</div>'. $params[0]['after_widget'];
		} elseif ( 2 <= $counter and 0 === $counter % 2) {
			$params[0]['before_widget'] = '</div><div class="row"><div class="col-md-6'. vu_animation(false, $delay) .'">'. $params[0]['before_widget'];
			$params[0]['after_widget'] = '</div>'. $params[0]['after_widget'];
		} else {
			$params[0]['before_widget'] = '<div class="col-md-6'. vu_animation(false, $delay) .'">'. $params[0]['before_widget'];
			$params[0]['after_widget'] = '</div>'. $params[0]['after_widget'];
		}

		$counter++;
		$delay += 200;

		return $params;
	}

	add_filter('dynamic_sidebar_params', 'vu_footer_top_sidebar');

	// Allow uploading SVG Files
	function vu_upload_mimes($mimes){
		$mimes['svg'] = 'image/svg+xml';

		return $mimes;
	}

	add_filter('upload_mimes', 'vu_upload_mimes');
?>