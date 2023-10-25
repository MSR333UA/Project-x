<?php
	/**
	 *	Bakery WordPress Theme
	 */

	// Convert shortcode atts to string
	if( !function_exists('vu_shortcode_atts') ){
		function vu_shortcode_atts($atts){
			$return = '';

			foreach ($atts as $key => $value) {
				$return .= ' '. $key .'="'. esc_attr($value) .'"';
			}

			return $return;
		}
	}

	// Generate shortcode as string
	if( !function_exists('vu_generate_shortcode') ){
		function vu_generate_shortcode($tag, $atts, $content = null){
			$return = '['. $tag . vu_shortcode_atts($atts) .']';

			if( !empty($content) ){
				$return .= $content .'[/'. $tag .']';
			}

			return $return;
		}
	}

	// Main Menu Wrap - Add Cart
	if( !function_exists('vu_main_menu_wrap') ){
		function vu_main_menu_wrap($wc = true) {
			$wrap  = '<ul id="%1$s" class="%2$s '. (trim(vu_get_option( array('main-sub-menu-typography', 'text-align') )) != '' ? ' vu_mm-submenu-'. vu_get_option( array('main-sub-menu-typography', 'text-align') ) : '') .'">';
			$wrap .= '%3$s';
			
			//WC Cart
			if( in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins'))) and vu_get_option('shop-show-basket-icon', true) ) {
				if( $wc == true) {
					global $woocommerce;

					$wrap .= '<li class="vu_wc-menu-item">
								<a href="'. $woocommerce->cart->get_cart_url() .'" class="vu_wc-cart-link"><span><i class="fa fa-shopping-cart"></i><span class="vu_wc-count">'. $woocommerce->cart->get_cart_contents_count() .'</span></span></a>
								<div class="vu_wc-menu-container">
									<div class="vu_wc-cart-notification"><span class="vu_wc-item-name"></span>'. __("was successfully added to your cart.", 'bakery') .'</div>
									<div class="vu_wc-cart widget woocommerce widget_shopping_cart"><div class="widget_shopping_cart_content"></div></div>
							  	</div>
							  </li>';
				}
			}
					
			$wrap .= '</ul>';

			return $wrap;
		}
	}

	// Extra class for shortcode
	if( !function_exists('vu_extra_class') ){
		function vu_extra_class($class, $echo = true){
			$return = ((!empty($class)) ? ' '. esc_attr($class) : '');

			if( $echo == true ) {
				echo $return;
			} else {
				return $return;
			}
		}
	}

	// Get theme option value
	if( !function_exists('vu_get_option') ){
		function vu_get_option($option, $default = ''){
			global $vu_theme_options;

			if( is_array($option) ){
				$count = count($option);

				switch ($count) {
					case 2:
						return isset($vu_theme_options[$option[0]][$option[1]]) ? $vu_theme_options[$option[0]][$option[1]] : $default;
						break;
					case 3:
						return isset($vu_theme_options[$option[0]][$option[1]][$option[2]]) ? $vu_theme_options[$option[0]][$option[1]][$option[2]] : $default;
						break;
						
					default:
						return isset($vu_theme_options[$option[0]]) ? $vu_theme_options[$option[0]] : $default;
						break;
				}
			} else {
				return isset($vu_theme_options[$option]) ? $vu_theme_options[$option] : $default;
			}
		}
	}

	// Convert Color from HEX to RGB
	if( !function_exists('vu_hex2rgb') ){
		function vu_hex2rgb( $colour ) {
			if ( $colour[0] == '#' ) {
				$colour = substr( $colour, 1 );
			}

			if ( strlen( $colour ) == 6 ) {
				list( $r, $g, $b ) = array( $colour[0] . $colour[1], $colour[2] . $colour[3], $colour[4] . $colour[5] );
			} elseif ( strlen( $colour ) == 3 ) {
				list( $r, $g, $b ) = array( $colour[0] . $colour[0], $colour[1] . $colour[1], $colour[2] . $colour[2] );
			} else {
				return false;
			}

			$r = hexdec( $r );
			$g = hexdec( $g );
			$b = hexdec( $b );

			return array( 'red' => $r, 'green' => $g, 'blue' => $b );
		}
	}

	// Convert Color from RGB to HEX
	if( !function_exists('vu_rgb2hex') ){
		function vu_rgb2hex($r, $g=-1, $b=-1) {
			if (is_array($r) && sizeof($r) == 3) {
				list($r, $g, $b) = $r;
			}

			$r = intval($r); $g = intval($g);
			$b = intval($b);

			$r = dechex($r < 0 ? 0 : ($r > 255 ? 255 : $r));
			$g = dechex($g < 0 ? 0 : ($g > 255 ? 255 : $g));
			$b = dechex($b < 0 ? 0 : ($b > 255 ? 255 : $b));

			$color = (strlen($r) < 2 ? '0' : ''). $r;
			$color .= (strlen($g) < 2 ? '0' : ''). $g;
			$color .= (strlen($b) < 2 ? '0' : ''). $b;

			return '#'.$color;
		}
	}

	// Compress the CSS
	if( !function_exists('vu_css_compress') ){
		//https://github.com/matthiasmullie/minify/blob/master/src/CSS.php
		function vu_css_compress($content){
			// reusable bits of code throughout these regexes:
			// before & after are used to make sure we don't match lose unintended
			// 0-like values (e.g. in #000, or in http://url/1.0)
			// units can be stripped from 0 values, or used to recognize non 0
			// values (where wa may be able to strip a .0 suffix)
			$before = '(?<=[:(, ])';
			$after = '(?=[ ,);}])';
			$units = '(em|ex|%|px|cm|mm|in|pt|pc|ch|rem|vh|vw|vmin|vmax|vm)';
			// strip units after zeroes (0px -> 0)
			$content = preg_replace('/' . $before . '(-?0*(\.0+)?)(?<=0)' . $units . $after . '/', '\\1', $content);
			// strip 0-digits (.0 -> 0)
			$content = preg_replace('/' . $before . '\.0+' . $after . '/', '0', $content);
			// 50.00 -> 50, 50.00px -> 50px (non-0 can still be followed by units)
			$content = preg_replace('/' . $before . '(-?[0-9]+)\.0+' . $units . '?' . $after . '/', '\\1\\2', $content);
			// strip negative zeroes (-0 -> 0) & truncate zeroes (00 -> 0)
			$content = preg_replace('/' . $before . '-?0+' . $after . '/', '0', $content);

			//Shorthand hex color codes
			$content = preg_replace('/(?<![\'"])#([0-9a-z])\\1([0-9a-z])\\2([0-9a-z])\\3(?![\'"])/i', '#$1$2$3', $content);

			//Strip comments from source code
			$content = preg_replace('/\/\*.*?\*\//s', '', $content);

			// remove leading & trailing whitespace
			$content = preg_replace('/^\s*/m', '', $content);
			$content = preg_replace('/\s*$/m', '', $content);
			// replace newlines with a single space
			$content = preg_replace('/\s+/', ' ', $content);
			// remove whitespace around meta characters
			// inspired by stackoverflow.com/questions/15195750/minify-compress-css-with-regex
			$content = preg_replace('/\s*([\*$~^|]?+=|[{};,>~]|!important\b)\s*/', '$1', $content);
			$content = preg_replace('/([\[(:])\s+/', '$1', $content);
			$content = preg_replace('/\s+([\]\)])/', '$1', $content);
			$content = preg_replace('/\s+(:)(?![^\}]*\{)/', '$1', $content);
			// whitespace around + and - can only be stripped in selectors, like
			// :nth-child(3+2n), not in things like calc(3px + 2px) or shorthands
			// like 3px -2px
			$content = preg_replace('/\s*([+-])\s*(?=[^}]*{)/', '$1', $content);
			// remove semicolon/whitespace followed by closing bracket
			$content = preg_replace('/;}/', '}', $content);
			return trim($content);
		}
	}

	// Dynamic Theme CSS
	if( !function_exists('vu_dynamic_css') ){
		function vu_dynamic_css($echo = false){
			// Primary Color - Default : #fdb822 or rgb(253, 184, 34)
			if( trim(vu_get_option('primary-color')) != '' ){
				$primary_color_hex = esc_attr(vu_get_option('primary-color'));
			} else {
				$primary_color_hex = '#fdb822';
			}

			$primary_color_rgb = vu_hex2rgb($primary_color_hex);

			// Secondary Color - Default : #684f40 or rgb(104, 79, 64)
			if( trim(vu_get_option('secondary-color')) != '' ){
				$secondary_color_hex = esc_attr(vu_get_option('secondary-color'));
			} else {
				$secondary_color_hex = '#684f40';
			}

			$secondary_color_rgb = vu_hex2rgb($secondary_color_hex);

			ob_start();
		?>
			a:hover {
			  color: <?php echo $primary_color_hex; ?>;
			}
			h1, h2, h3, h4, h5, h6 {
			  color: <?php echo $secondary_color_hex; ?>;
			}
			blockquote {
			  border-left-color: <?php echo $secondary_color_hex; ?>;
			}
			.article-short:hover .article-short-icon-container {
			  border-color: <?php echo $primary_color_hex; ?>;
			  color: <?php echo $primary_color_hex; ?>;
			}
			.article-short:hover .article-short-icon-container .article-short-icon svg {
			  fill: <?php echo $primary_color_hex; ?>;
			}
			.article-short .article-short-icon-container {
			  color: <?php echo $secondary_color_hex; ?>;
			  border-color: <?php echo $secondary_color_hex; ?>;
			}
			.article-short .article-short-icon-container .article-short-icon svg {
			  fill: <?php echo $secondary_color_hex; ?>;
			}
			.article-short .article-short-content h3:after {
			  background-color: <?php echo $primary_color_hex; ?>;
			}
			.content-box {
			  border-color: <?php echo $secondary_color_hex; ?>;
			}
			.button {
			  background-color: <?php echo $primary_color_hex; ?>;
			}
			.button:hover {
			  background-color: <?php echo $secondary_color_hex; ?>;
			}
			.button-void {
			  color: <?php echo $secondary_color_hex; ?>;
			  border-color: <?php echo $secondary_color_hex; ?>;
			}
			.button-void:hover {
			  background-color: <?php echo $secondary_color_hex; ?>;
			}
			.heading-huge {
			  color: <?php echo $secondary_color_hex; ?>;
			}
			.heading-small {
			  color: <?php echo $secondary_color_hex; ?>;
			}
			.highlight {
			  color: <?php echo $primary_color_hex; ?>;
			}
			.horizontal-delimiter {
			  background-color: <?php echo $primary_color_hex; ?>;
			}
			.icon-big-container {
			  color: <?php echo $secondary_color_hex; ?>;
			  border-color: <?php echo $secondary_color_hex; ?>;
			}
			.icon-big-container img,
			.icon-big-container svg {
			  fill: <?php echo $secondary_color_hex; ?>;
			}
			.icon-big-container:hover {
			  border-color: <?php echo $primary_color_hex; ?>;
			}
			.icon-big-container:hover svg {
			  fill: <?php echo $primary_color_hex; ?>;
			}
			.item-check:after {
			  color: <?php echo $primary_color_hex; ?>;
			}
			.list-arrows li:hover:after,
			.list-arrows li.active:after,
			.list-arrows li:hover .list-arrows-content,
			.list-arrows li.active .list-arrows-content {
			  color: <?php echo $primary_color_hex; ?>;
			}
			.list-arrows li:hover .list-arrows-value,
			.list-arrows li.active .list-arrows-value {
			  background-color: <?php echo $primary_color_hex; ?>;
			}
			.list-values li:hover {
			  color: <?php echo $primary_color_hex; ?>;
			}
			.page-header {
			  color: <?php echo $secondary_color_hex; ?>;
			}
			.full-header {
			  background-color: rgba(<?php echo $secondary_color_rgb['red'] .','. $secondary_color_rgb['green'] .','. $secondary_color_rgb['blue']; ?>, 0.7);
			}
			.full-header h1:after {
			  background-color: <?php echo $primary_color_hex; ?>;
			}
			.full-header-container {
			  border-top-color: <?php echo $secondary_color_hex; ?>;
			  border-bottom-color: <?php echo $secondary_color_hex; ?>;
			}
			.article-header-3 h1:after {
			  background-color: <?php echo $primary_color_hex; ?>;
			}
			.article-header-5:after {
			  background-color: <?php echo $primary_color_hex; ?>;
			}
			.blog-post {
			  border-color: <?php echo $secondary_color_hex; ?>;
			}
			.post .post-image {
			  border-color: <?php echo $secondary_color_hex; ?>;
			}
			.post .post-info a:hover {
			  color: <?php echo $primary_color_hex; ?>;
			}
			.post .post-info:after {
			  background-color: <?php echo $primary_color_hex; ?>;
			}
			.post:hover .post-image {
			  border-color: <?php echo $primary_color_hex; ?>;
			}
			.post-preview .post-preview-detail a:hover {
			  color: <?php echo $primary_color_hex; ?>;
			}
			.blog-post-comments:after {
			  background-color: <?php echo $primary_color_hex; ?>;
			}
			.blog-post-comments .blog-post-comment-heading {
			  color: <?php echo $secondary_color_hex; ?>;
			}
			.blog-post-comments .blog-post-comment-heading a:hover {
			  color: <?php echo $primary_color_hex; ?>;
			}
			.blog-post-comments .submit-comment {
			  background-color: <?php echo $secondary_color_hex; ?>;
			}
			#respond .comment-reply-title {
			  color: <?php echo $secondary_color_hex; ?>;
			}
			#respond .comment-reply-title #cancel-comment-reply-link {
			  color: <?php echo $primary_color_hex; ?>;
			  border-left-color: <?php echo $secondary_color_hex; ?>;
			}
			.blog-post .blog-post-footer:after {
			  background-color: <?php echo $primary_color_hex; ?>;
			}
			.blog-post .blog-post-footer .socials {
			  color: <?php echo $secondary_color_hex; ?>;
			}
			.blog-post .blog-post-header:after {
			  background-color: <?php echo $primary_color_hex; ?>;
			}
			.blog-post .blog-post-header a:hover {
			  color: <?php echo $primary_color_hex; ?>;
			}
			.blog-post .blog-post-image .blog-post-image-cover:after,
			.blog-post .blog-post-image .blog-post-image-cover:before {
			  background-color: <?php echo $secondary_color_hex; ?>;
			}
			.blog-post .blog-post-tags {
			  color: <?php echo $secondary_color_hex; ?>;
			}
			.blog-post-small .blog-post-small-img:hover .blog-post-small-info-content {
			  color: <?php echo $secondary_color_hex; ?>;
			}
			.blog-post-small .blog-post-small-img .blog-post-small-img-cover:after,
			.blog-post-small .blog-post-small-img .blog-post-small-img-cover:before {
			  background-color: <?php echo $secondary_color_hex; ?>;
			}
			.blog-post-small .blog-post-small-info .blog-post-small-info-content {
			  background-color: <?php echo $secondary_color_hex; ?>;
			}
			.vu_latest-tweets a,
			.vu_latest-tweets a {
			  color: <?php echo $primary_color_hex; ?>;
			}
			.filter-icon-content {
			  background-color: <?php echo $secondary_color_hex; ?>;
			}
			.filter-icon-content:after {
			  border-color: <?php echo $secondary_color_hex; ?>;
			  background-color: <?php echo $secondary_color_hex; ?>;
			}
			.vu_filter.active .filter-icon-content,
			.vu_filter:hover .filter-icon-content {
			  background-color: <?php echo $primary_color_hex; ?>;
			}
			.vu_filter.active .filter-icon-content:after,
			.vu_filter:hover .filter-icon-content:after {
			  border-color: <?php echo $primary_color_hex; ?>;
			}
			.form-contact {
			  background-color: rgba(<?php echo $secondary_color_rgb['red'] .','. $secondary_color_rgb['green'] .','. $secondary_color_rgb['blue']; ?>, 0.9);
			}
			.form-contact .form-control:hover,
			.form-contact .form-control:active,
			.form-contact .form-control:focus {
			  border-color: <?php echo $primary_color_hex; ?>;
			}
			.offset-borders:after,
			.offset-borders:before {
			  background-color: <?php echo $secondary_color_hex; ?>;
			}
			.owl-controls .owl-next,
			.owl-controls .owl-prev {
			  color: <?php echo $secondary_color_hex; ?>;
			  border-color: <?php echo $secondary_color_hex; ?>;
			}
			.owl-controls .owl-next:hover,
			.owl-controls .owl-prev:hover {
			  color: <?php echo $primary_color_hex; ?>;
			  border-color: <?php echo $primary_color_hex; ?>;
			}
			.owl-controls .owl-page {
			  border-color: <?php echo $secondary_color_hex; ?>;
			}
			.owl-controls .owl-page:after {
			  background-color: <?php echo $primary_color_hex; ?>;
			}
			.owl-controls .owl-page.active {
			  border-color: <?php echo $primary_color_hex; ?>;
			}
			.pagination .pagination-item:hover,
			.pagination .pagination-nav:hover {
			  background-color: <?php echo $secondary_color_hex; ?>;
			  border-color: <?php echo $secondary_color_hex; ?>;
			}
			.pagination .current .pagination-item {
			  color: <?php echo $secondary_color_hex; ?>;
			  border-color: <?php echo $secondary_color_hex; ?>;
			}
			.pagination-nav-single {
			  border-color: <?php echo $secondary_color_hex; ?>;
			  color: <?php echo $secondary_color_hex; ?>;
			}
			.pagination-nav-single:hover {
			  background-color: <?php echo $secondary_color_hex; ?>;
			}
			.vu_menu-item a .vu_menu-item-img:after {
			  background-color: rgba(<?php echo $primary_color_rgb['red'] .','. $primary_color_rgb['green'] .','. $primary_color_rgb['blue']; ?>, 0.6);
			}
			.vu_menu-item a:hover .vu_menu-item-img img {
			  border-color: <?php echo $primary_color_hex; ?>;
			}
			.vu_menu-item-detail h5.vu_menu-item-price {
			  color: <?php echo $secondary_color_hex; ?>;
			}
			.price-label:after {
			  background-color: <?php echo $primary_color_hex; ?>;
			}
			.product .product-detail-container .product-detail {
			  background-color: rgba(<?php echo $primary_color_rgb['red'] .','. $primary_color_rgb['green'] .','. $primary_color_rgb['blue']; ?>, 0.9);
			}
			.product .product-icon-container {
			  color: <?php echo $primary_color_hex; ?>;
			  border-color: <?php echo $primary_color_hex; ?>;
			}
			.product .product-icon-container a {
			  color: <?php echo $primary_color_hex; ?>;
			}
			.product .product-icon-container:hover {
			  background-color: <?php echo $secondary_color_hex; ?>;
			  border-color: <?php echo $secondary_color_hex; ?>;
			}
			.product .product-icons:after {
			  background-color: rgba(<?php echo $primary_color_rgb['red'] .','. $primary_color_rgb['green'] .','. $primary_color_rgb['blue']; ?>, 0.9);
			}
			.product:hover {
			  border-color: <?php echo $primary_color_hex; ?>;
			}
			.profile .profile-box {
			  color: <?php echo $secondary_color_hex; ?>;
			}
			.profile .profile-box:after {
			  border-color: <?php echo $secondary_color_hex; ?>;
			}
			.profile .profile-photo .profile-photo-info {
			  color: <?php echo $secondary_color_hex; ?>;
			  background-color: rgba(<?php echo $secondary_color_rgb['red'] .','. $secondary_color_rgb['green'] .','. $secondary_color_rgb['blue']; ?>, 0.6);
			  box-shadow: 0 0 7px rgba(<?php echo $secondary_color_rgb['red'] .','. $secondary_color_rgb['green'] .','. $secondary_color_rgb['blue']; ?>, 0.16);
			}
			.profile .profile-photo .profile-photo-info .profile-icon a {
			  color: <?php echo $secondary_color_hex; ?>;
			}
			.profile .profile-photo .profile-photo-info .profile-icon:hover {
			  background-color: <?php echo $primary_color_hex; ?>;
			}
			.profile:hover .profile-box {
			  color: <?php echo $primary_color_hex; ?>;
			}
			.profile:hover .profile-box:after {
			  border-color: <?php echo $primary_color_hex; ?>;
			}
			.read-more-link {
			  color: <?php echo $secondary_color_hex; ?>;
			}
			.read-more-link:hover {
			  color: <?php echo $primary_color_hex; ?>;
			}
			.section-black-cover:after,
			.section-color-cover:after,
			.section-white-cover:after {
			  background-color: rgba(<?php echo $primary_color_rgb['red'] .','. $primary_color_rgb['green'] .','. $primary_color_rgb['blue']; ?>, 0.8);
			}
			.section-color-cover:after {
			  background-color: rgba(<?php echo $primary_color_rgb['red'] .','. $primary_color_rgb['green'] .','. $primary_color_rgb['blue']; ?>, 0.8);
			}
			.section-header.vu_inline h2 span {
			  background-color: <?php echo $primary_color_hex; ?>;
			}
			.section-header h2:after {
			  background-color: <?php echo $primary_color_hex; ?>;
			}
			.service-box:hover .icon-big-container {
			  border-color: <?php echo $primary_color_hex; ?>;
			}
			.service-box:hover .icon-big-container svg {
			  fill: <?php echo $primary_color_hex; ?>;
			}
			.countdown-period {
			  color: <?php echo $secondary_color_hex; ?>;
			}
			.gallery-slider-it .gallery-slider-detail-container {
			  background-color: rgba(<?php echo $primary_color_rgb['red'] .','. $primary_color_rgb['green'] .','. $primary_color_rgb['blue']; ?>, 0.9);
			}
			.sidebar .widget_title {
			  color: <?php echo $secondary_color_hex; ?>;
			}
			.page-footer
			.sidebar .widget_title a {
			  color: <?php echo $secondary_color_hex; ?>;
			}
			.search-form input {
			  border-color: <?php echo $secondary_color_hex; ?>;
			}
			.tabs-big-container .nav > li:after {
			  border-top-color: <?php echo $primary_color_hex; ?>;
			  border-left-color: <?php echo $primary_color_hex; ?>;
			}
			.tabs-big-container .nav > li:before {
			  border-color: <?php echo $primary_color_hex; ?>;
			}
			.tabs-big-container .tab-content {
			  border-color: <?php echo $primary_color_hex; ?>;
			}
			.testimonial .testimonial-name {
			  background-color: <?php echo $secondary_color_hex; ?>;
			}
			.testimonials-slider .owl-controls .owl-page:after {
			  background-color: <?php echo $secondary_color_hex; ?>;
			}
			.testimonials-slider .owl-controls .owl-page.active {
			  border-color: <?php echo $secondary_color_hex; ?>;
			}
			.form-control:hover,
			.form-control:active,
			.form-control:focus {
			  border-color: <?php echo $secondary_color_hex; ?>;
			}
			.vu_multiselect-wrapper .open .vu_btn-multiselect {
			  border-color: <?php echo $secondary_color_hex; ?>;
			}
			.vu_multiselect-wrapper .multiselect-container.dropdown-menu > .active > a,
			.vu_multiselect-wrapper .multiselect-container.dropdown-menu > .active > a:hover,
			.vu_multiselect-wrapper .multiselect-container.dropdown-menu > .active > a:focus {
			  background-color: <?php echo $secondary_color_hex; ?>;
			}
			.btn {
			  background-color: <?php echo $secondary_color_hex; ?>;
			}
			.btn:hover,
			.btn:active,
			.btn:focus {
			  background-color: <?php echo $primary_color_hex; ?>;
			}
			.btn:active,
			.btn:focus {
			  background-color: <?php echo $secondary_color_hex; ?>;
			}
			.btn.btn-inverse {
			  color: <?php echo $secondary_color_hex; ?>;
			}
			.btn.btn-inverse:active,
			.btn.btn-inverse:focus {
			  color: <?php echo $secondary_color_hex; ?>;
			}
			.error-404 .error-code p {
			  color: <?php echo $primary_color_hex; ?>;
			}
			.error-404 .error-message a {
			  border-color: <?php echo $secondary_color_hex; ?>;
			  background-color: <?php echo $secondary_color_hex; ?>;
			}
			.page-footer a:hover {
			  color: <?php echo $primary_color_hex; ?>;
			}
			.widget_nav_menu .menu > li > a {
			  color: <?php echo $secondary_color_hex; ?>;
			}
			.widget_nav_menu ul li a:hover,
			.widget_nav_menu ul li.current-menu-parent > a,
			.widget_nav_menu ul li.current-menu-ancestor > a,
			.widget_nav_menu ul li.current-menu-item > a {
			  border-color: <?php echo $primary_color_hex; ?>;
			  background-color: <?php echo $primary_color_hex; ?>;
			}
			.social-icon-container,
			.social-icon-container-big,
			.social-icon-container-small {
			  background-color: <?php echo $secondary_color_hex; ?>;
			}
			.social-icon-container:hover,
			.social-icon-container-big:hover,
			.social-icon-container-small:hover {
			  background-color: <?php echo $primary_color_hex; ?>;
			}
			.vu_socials h3 {
			  color: <?php echo $secondary_color_hex; ?>;
			}
			.widget.widget_archive li a:hover,
			.widget.widget_pages li a:hover,
			.widget.widget_recent_comments li a:hover,
			.widget.widget_nav_menu li a:hover,
			.widget.widget_recent_entries li a:hover,
			.widget.widget_meta li a:hover,
			.widget.widget_categories li a:hover {
			  color: <?php echo $primary_color_hex; ?> !important;
			}
			.widget.widget_tag_cloud a.active,
			a.active span.tag,
			.widget.widget_tag_cloud a:hover,
			a:hover span.tag {
			  background-color: <?php echo $primary_color_hex; ?>;
			}
			.widget.widget_rss .rss-date {
			  color: <?php echo $secondary_color_hex; ?>;
			}
			.vu_gallery .vu_g-filters .vu_g-filter.active,
			.vu_gallery .vu_g-filters .vu_g-filter:hover {
			  color: <?php echo $primary_color_hex; ?>;
			}
			/* Gallery Item */
			.vu_gallery-item .vu_gi-details-container {
			  background-color: rgba(<?php echo $primary_color_rgb['red'] .','. $primary_color_rgb['green'] .','. $primary_color_rgb['blue']; ?>, 0.9);
			}
			.vu_top-bar a:hover {
			  color: <?php echo $primary_color_hex; ?>;
			}
			.vu_tb-list .sub-menu li a:hover {
			  color: <?php echo $primary_color_hex; ?>;
			}
			.vu_main-menu > ul > li {
			  border-left-color: rgba(<?php echo $secondary_color_rgb['red'] .','. $secondary_color_rgb['green'] .','. $secondary_color_rgb['blue']; ?>, 0.2);
			}
			.vu_main-menu > ul > li a:hover {
			  color: <?php echo $primary_color_hex; ?>;
			}
			
			.vu_main-menu > ul > li.active > a,
			.vu_main-menu > ul > li.current-menu-parent > a,
			.vu_main-menu > ul > li.current-menu-ancestor > a,
			.vu_main-menu > ul > li.current-menu-item > a {
			  color: <?php echo $primary_color_hex; ?>;
			}
			.vu_main-menu ul li ul.sub-menu li a:hover, .vu_main-menu ul li ul.sub-menu li.current-menu-item > a { color: <?php echo $primary_color_hex; ?>; background-color: <?php echo $secondary_color_hex; ?>; }
			.vu_main-menu ul.sub-menu li:hover > a, .vu_main-menu ul.sub-menu li.current-menu-ancestor > a { color: <?php echo $primary_color_hex; ?>; background-color: <?php echo $secondary_color_hex; ?>; }
			.vu_mobile-menu ul li.current-menu-item > a {
			  color: <?php echo $primary_color_hex; ?>;
			}
			.vu_mobile-menu ul li a:hover,
			.vu_mobile-menu .vu_mm-remove:hover {
			  color: <?php echo $primary_color_hex; ?>;
			  background-color: <?php echo $secondary_color_hex; ?>;
			}
			.vu_mm-open:hover,
			.vu_mm-open:focus,
			.vu_wc-menu-item.vu_wc-responsive:hover,
			.vu_wc-menu-item.vu_wc-responsive:focus {
			  color: <?php echo $secondary_color_hex; ?>;
			}
			.vu_with-border {
			  border-color: <?php echo $secondary_color_hex; ?>;
			}
			.wpb_revslider_element {
			  border-color: <?php echo $secondary_color_hex; ?>;
			}
			.vu_product-item {
			  border-color: <?php echo $secondary_color_hex; ?>;
			}
			.vu_product-item .vu_pi-container {
			  background-color: rgba(<?php echo $primary_color_rgb['red'] .','. $primary_color_rgb['green'] .','. $primary_color_rgb['blue']; ?>, 0.9);
			}
			.vu_product-item .vu_pi-icon {
			  color: <?php echo $primary_color_hex; ?>;
			  border-color: <?php echo $primary_color_hex; ?>;
			}
			.vu_product-item .vu_pi-icon:hover {
			  background-color: <?php echo $secondary_color_hex; ?>;
			  border-color: <?php echo $secondary_color_hex; ?>;
			}
			
			.vu_main-menu-container .vu_logo-container {
			  width:<?php echo (absint(vu_get_option('logo-width')) + 30); ?>px;
			}
			.vu_main-menu-container .vu_logo-container img {
			  max-width:<?php echo absint(vu_get_option('logo-width')); ?>px;
			}
			
			@media (max-width: <?php echo absint(vu_get_option('hamburger-menu')); ?>px) {
			  .vu_main-menu {
			    display: none !important;
			  }
			  .vu_mm-open,
			  .vu_wc-menu-item.vu_wc-responsive {
			    display: block !important;
			  }
			}
		<?php 
			if( in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins'))) ) {
				vu_wc_dynamic_css($primary_color_hex, $primary_color_rgb, $secondary_color_hex, $secondary_color_rgb);
			}

			$dynamic_css = ob_get_contents();
			ob_end_clean();

			// Custom CSS from Theme Options
			if( trim(vu_get_option('custom-css')) != '' ){
				$dynamic_css .= vu_get_option('custom-css');
			}

			// Preloader Image
			if( vu_get_option('preloader') == true and trim(vu_get_option( array('preloader-image', 'url') )) != '' ) {
				$dynamic_css .= '#vu_preloader { background-image: url('. esc_url(vu_get_option( array('preloader-image', 'url') )) .');}';
			}

			// Background Pattern
			if( trim(vu_get_option( array('body-background', 'background-image') )) == '' and trim(vu_get_option('bg-pattern')) != '' ) {
				$dynamic_css .= 'body { background-image: url('. esc_url(vu_get_option('bg-pattern')) .') !important;}';
			}

			if( $echo ){
				echo vu_css_compress($dynamic_css);
			} else {
				return vu_css_compress($dynamic_css);
			}
		}
	}

	// Get the URL (src) for an image attachment
	if( !function_exists('vu_get_attachment_image_src') ){
		function vu_get_attachment_image_src($attachment_id, $size = 'thumbnail', $icon = false, $return = 'url'){
			$image_attributes = wp_get_attachment_image_src( $attachment_id, $size, $icon );

			if( $image_attributes ) {
				switch ($return) {
					case 'all':
						$return = $image_attributes;
						break;
					case 'url':
						$return = $image_attributes[0];
						break;
					case 'width':
						$return = $image_attributes[1];
						break;
					case 'height':
						$return = $image_attributes[2];
						break;
					case 'resized ':
						$return = $image_attributes[3];
						break;
					
					default:
						$return = $image_attributes[0];
						break;
				}
			}

			return $return;
		}
	}

	// Animate element if animation option is enabled
	if( !function_exists('vu_animation') ){
		function vu_animation($echo = false, $delay = null){
			$return = (vu_get_option('animation') == true) ? ' onscroll-animate'. ((!empty($delay)) ? '" data-delay="'. esc_attr($delay) : '') : '';

			if( $echo ){
				echo $return;
			} else {
				return $return;
			}
		}
	}

	// Update Post Meta Data
	if( !function_exists('vu_update_post_meta') ) {
		function vu_update_post_meta( $post_id, $meta_key, $meta_value, $prev_value = null ){
			if( is_array($meta_value) )
				$meta_value = vu_json_encode( $meta_value );

			update_post_meta( $post_id, $meta_key, $meta_value, $prev_value );
		}
	}

	// Get Post Meta Data
	if( !function_exists('vu_get_post_meta') ) {
		function vu_get_post_meta( $post_id, $key, $json = true ){
			$return = get_post_meta( $post_id, $key, true );

			if( $json )
				$return = vu_json_decode( $return );

			return $return;
		}
	}

	// JSON Encode
	if( !function_exists('vu_json_encode') ) {
		function vu_json_encode( $array ){
			return wp_slash(json_encode($array));
		}
	}

	// JSON Decode
	if( !function_exists('vu_json_decode') ) {
		function vu_json_decode( $json ){
			return ( !empty($json) ? wp_unslash(json_decode($json, true)) : false );
		}
	}

	// Get Page Header Background
	if( !function_exists('vu_get_page_header_bg') ) {
		function vu_get_page_header_bg($post_id, &$title = null, &$subtitle = null){
			$vu_page_header_settings = vu_get_post_meta( $post_id, 'vu_page_header_settings' );

			if( $vu_page_header_settings !== false ) {
				if( is_array($vu_page_header_settings) and isset($vu_page_header_settings['style']) and $vu_page_header_settings['style'] == 'custom' ){
					$title = $vu_page_header_settings['title'];
					$subtitle = $vu_page_header_settings['subtitle'];
					
					return $vu_page_header_settings['bg'];
				}
			}

			return false;
		}
	}

	// Print Header Section
	if( !function_exists('vu_header_section') ) {
		function vu_header_section($title, $subtitle = null, $bg = null){
		?>
			<section class="top-section">
				<div class="offset-borders">
					<div class="full-header-container<?php echo (vu_get_option('header-title-bg-prallax') != true) ? ' vu_lazy-load' : ''; ?>"<?php echo (!empty($bg) && (vu_get_option('header-title-bg-prallax') == true)) ? ' data-parallax="scroll" data-image-src="'. esc_url( ((is_ssl()) ? str_replace('http://', 'https://', $bg) : $bg) ) .'"' : ' data-img="'. esc_url( ((is_ssl()) ? str_replace('http://', 'https://', $bg) : $bg) ) .'"'; ?>>
						<div class="full-header">
							<div class="container">
								<?php echo !empty($title) ? '<h1>'. $title .'</h1>' : ''; ?>
								<?php echo !empty($subtitle) ? '<h3>'. $subtitle .'</h3>' : ''; ?>
							</div>
						</div>
					</div>
				</div>
			</section>
		<?php 
		}
	}

	// Print Pagination
	if( !function_exists('vu_pagination') ) {
		function vu_pagination($query = null){
			global $wp_query, $wp_rewrite;

			if( !empty($query) ){
				$wp_query = $query;
			}
				
			$wp_query->query_vars['paged'] > 1 ? $current = $wp_query->query_vars['paged'] : $current = 1; 
			$total_pages = $wp_query->max_num_pages; 

			if ($total_pages > 1){
				$permalink_structure = get_option('permalink_structure');
				$query_type = (count($_GET)) ? '&' : '?';	
				$format = empty( $permalink_structure ) ? $query_type.'paged=%#%' : 'page/%#%/';  
			
				echo '<div class="text-center"><span class="pagination">';
				 
				echo paginate_links(array(
					'base' => get_pagenum_link(1) . '%_%',
					'format' => $format,
					'current' => $current,
					'total' => $total_pages,
					'prev_text' => '<span class="pagination-item pagination-nav">'. __('prev', 'bakery') .'</span>',
					'next_text' => '<span class="pagination-item pagination-nav">'. __('next', 'bakery') .'</span>',
					'before_page_number' => '<span class="pagination-item">',
					'after_page_number' => '</span>'
				)); 
				
				echo  '</span></div>'; 
			}
		}
	}

	// Print Blog Socials Networks
	if( !function_exists('vu_blog_socials') ) {
		function vu_blog_socials($url, $title = null, $post_id = null){
			if( vu_get_option('blog-social') ) : ?>
				<div class="socials">
					<?php echo __('Share', 'bakery'); ?>
					<?php if( vu_get_option( array('blog-social-networks', 'facebook') ) == '1' ) { ?>
						<div class="social-icon-container-small">
							<a href="#" class="vu_social-link" data-href="http://www.facebook.com/sharer.php?u=<?php echo esc_url($url); ?>&amp;t=<?php echo urlencode($title); ?>"><i class="fa fa-facebook"></i></a>
						</div>
					<?php } if( vu_get_option( array('blog-social-networks', 'twitter') ) == '1' ) { ?>
						<div class="social-icon-container-small">
							<a href="#" class="vu_social-link" data-href="https://twitter.com/share?text=<?php echo urlencode($title); ?>&amp;url=<?php echo esc_url($url); ?>"><i class="fa fa-twitter"></i></a>
						</div>
					<?php } if( vu_get_option( array('blog-social-networks', 'google-plus') ) == '1' ) { ?>
						<div class="social-icon-container-small">
							<a href="#" class="vu_social-link" data-href="https://plus.google.com/share?url=<?php echo esc_url($url); ?>"><i class="fa fa-google-plus"></i></a>
						</div>
					<?php } if( vu_get_option( array('blog-social-networks', 'pinterest') ) == '1' ) { ?>
						<div class="social-icon-container-small">
							<a href="#" class="vu_social-link" data-href="http://pinterest.com/pin/create/button/?url=<?php echo urlencode($url); ?>&amp;description=<?php echo urlencode($title); ?>&amp;media=<?php echo vu_get_attachment_image_src($post_id, array(705, 470)); ?>"><i class="fa fa-pinterest"></i></a>
						</div>
					<?php } if( vu_get_option( array('blog-social-networks', 'linkedin') ) == '1' ) { ?>
						<div class="social-icon-container-small">
							<a href="#" class="vu_social-link" data-href="http://linkedin.com/shareArticle?mini=true&amp;title=<?php echo urlencode($title); ?>&amp;url=<?php echo esc_url($url); ?>"><i class="fa fa-linkedin"></i></a>
						</div>
					<?php } ?>
				</div>
		<?php 
			endif;
		}
	}

	// Single Comment Template
	if( !function_exists('vu_comments') ) {
		function vu_comments($comment, $args, $depth){
			$GLOBALS['comment'] = $comment;
		?>
		<li <?php comment_class('blog-post-comment clearfix'. vu_animation(false) ); ?> id="li-comment-<?php comment_ID(); ?>">
			<div id="comment-<?php comment_ID(); ?>" class="m-b-15 clearfix">
				<article>
					<div class="blog-post-comment-avatar">
						<?php echo get_avatar( $comment->comment_author_email, 64 ); ?>
					</div>
					<div class="blog-post-comment-content">
						<header class="blog-post-comment-heading"><?php echo __('by', 'bakery') .' <cite>'. $comment->comment_author .'</cite>, '. __('post on', 'bakery') .' <time>'. date('d.m.Y', strtotime($comment->comment_date)) .'</time>'; ?><?php edit_comment_link(__('Edit', 'bakery'),' <span class="delimiter-inline">|</span>','') ?> <span class="delimiter-inline">|</span> <a href="#" class="vu_comment-reply-link" data-id="<?php comment_ID(); ?>"><?php echo __('Reply', 'bakery'); ?></a></header>
						<?php if ($comment->comment_approved == '0') : ?>
							<em><?php echo __('Your comment is awaiting moderation.', 'bakery'); ?></em>
						<?php endif; ?>

						<?php comment_text(); ?>

					</div>
				</article>
			</div>
		<?php 
		}
	}

	// Get Product Category or Product Tags
	if( !function_exists('vu_product_terms') ) {
		function vu_product_terms($post_id, $echo = true, $implode = ", ", $slug = false, $taxonomy = 'product_cat'){
			$terms = get_the_terms( $post_id, $taxonomy );

			$return = '&nbsp;';
								
			if ( $terms && ! is_wp_error( $terms ) ) {
				$terms_return = array();

				foreach ( $terms as $term ) {
					if( $slug ){
						$terms_return[] = $term->slug;
					} else {
						$terms_return[] = $term->name;
					}
				}
				
				$return = implode( $implode, $terms_return );
			}

			if( $echo ){
				echo $return;
			} else {
				return $return;
			}
		}
	}

	// Get Products
	if( !function_exists('vu_get_products') ) {
		function vu_get_products(){
			$args = array(
				'post_type' => 'product',
				'post_status' => 'publish',
				'posts_per_page' => -1,
				'ignore_sticky_posts'=> 1
			);

			$return = array('' => '');

			$products_query = new WP_Query($args);

			if( $products_query->have_posts() ) {
				while ($products_query->have_posts()) : $products_query->the_post();
					$return[get_the_title()] = get_the_ID();
				endwhile;
			}
			
			wp_reset_query();

			return $return;
		}
	}

	// Print Excerpt with Custom Length
	if( !function_exists('vu_the_excerpt') ) {
		function vu_the_excerpt($num_of_words, $post_excerpt = null) {
			$excerpt = empty($post_excerpt) ? get_the_excerpt() : $post_excerpt;

			$exwords = explode( ' ', trim( mb_substr( $excerpt, 0, mb_strlen($excerpt) - 5 ) ) );

			if( count($exwords) > $num_of_words ){
				$excerpt = array();

				$i = 0;
				foreach ($exwords as $value) {
					if( $i >= $num_of_words ) break;
					array_push($excerpt, $value);
					$i++;
				}

				echo implode(' ', $excerpt) . ' [...]';
			} else {
				echo $excerpt;
			}
		}
	}

	// Get Map Options from Theme Options
	if( !function_exists('vu_get_map_options') ) {
		function vu_get_map_options(){
			$map_options = array(
				'zoom_level' => esc_attr(vu_get_option('zoom-level')),
				'center_lat' => esc_attr(vu_get_option('center-lat')),
				'center_lng' => esc_attr(vu_get_option('center-lng')),
				"map_type" => esc_attr(vu_get_option('map-type')),
				"tilt_45" => esc_attr(vu_get_option('map-tilt-45')),
				'others_options' => array(
					"draggable" => esc_attr(vu_get_option( array('map-others-options', 'draggable') )),
					"zoomControl" => esc_attr(vu_get_option( array('map-others-options', 'zoomControl') )),
					"disableDoubleClickZoom" => esc_attr(vu_get_option( array('map-others-options', 'disableDoubleClickZoom') )),
					"scrollwheel" => esc_attr(vu_get_option( array('map-others-options', 'scrollwheel') )),
					"panControl" => esc_attr(vu_get_option( array('map-others-options', 'panControl') )),
					"mapTypeControl" => esc_attr(vu_get_option( array('map-others-options', 'mapTypeControl') )),
					"scaleControl" => esc_attr(vu_get_option( array('map-others-options', 'scaleControl') )),
					"streetViewControl" => esc_attr(vu_get_option( array('map-others-options', 'streetViewControl') ))
				),
				'use_marker_img' => esc_attr(vu_get_option('use-marker-img')),
				'marker_img' => esc_attr(vu_get_option( array('marker-img', 'url') )),
				'enable_animation' => esc_attr(vu_get_option('enable-map-animation')),
				'locations' => array()
			);

			$number_of_locations = vu_get_option('number-of-locations');

			for($i=1; $i<=$number_of_locations; $i++){
				if( vu_get_option('map-point-'. $i) != false ){
					array_push($map_options['locations'], array('lat' => esc_attr(vu_get_option('latitude'. $i)), 'lng' => esc_attr(vu_get_option('longitude'. $i)), 'info' => esc_attr(vu_get_option('map-info'. $i))));
				}
			}

			return $map_options;
		}
	}

	// Get Map Style
	if( !function_exists('vu_get_map_style') ) {
		function vu_get_map_style($map_style){
			switch ($map_style) {
				case '1':
					return '[{"featureType":"landscape","stylers":[{"saturation":-100},{"lightness":65},{"visibility":"on"}]},{"featureType":"poi","stylers":[{"saturation":-100},{"lightness":51},{"visibility":"simplified"}]},{"featureType":"road.highway","stylers":[{"saturation":-100},{"visibility":"simplified"}]},{"featureType":"road.arterial","stylers":[{"saturation":-100},{"lightness":30},{"visibility":"on"}]},{"featureType":"road.local","stylers":[{"saturation":-100},{"lightness":40},{"visibility":"on"}]},{"featureType":"transit","stylers":[{"saturation":-100},{"visibility":"simplified"}]},{"featureType":"administrative.province","stylers":[{"visibility":"off"}]},{"featureType":"water","elementType":"labels","stylers":[{"visibility":"on"},{"lightness":-25},{"saturation":-100}]},{"featureType":"water","elementType":"geometry","stylers":[{"hue":"#ffff00"},{"lightness":-25},{"saturation":-97}]}]';
					break;
				case '2':
					return '[{"featureType":"administrative","elementType":"labels.text.fill","stylers":[{"color":"#444444"}]},{"featureType":"landscape","elementType":"all","stylers":[{"color":"#f2f2f2"}]},{"featureType":"poi","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"road","elementType":"all","stylers":[{"saturation":-100},{"lightness":45}]},{"featureType":"road.highway","elementType":"all","stylers":[{"visibility":"simplified"}]},{"featureType":"road.arterial","elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"transit","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"water","elementType":"all","stylers":[{"color":"#46bcec"},{"visibility":"on"}]}]';
					break;
				case '3':
					return '[{"featureType":"all","elementType":"labels.text.fill","stylers":[{"saturation":36},{"color":"#000000"},{"lightness":40}]},{"featureType":"all","elementType":"labels.text.stroke","stylers":[{"visibility":"on"},{"color":"#000000"},{"lightness":16}]},{"featureType":"all","elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"administrative","elementType":"geometry.fill","stylers":[{"color":"#000000"},{"lightness":20}]},{"featureType":"administrative","elementType":"geometry.stroke","stylers":[{"color":"#000000"},{"lightness":17},{"weight":1.2}]},{"featureType":"landscape","elementType":"geometry","stylers":[{"color":"#000000"},{"lightness":20}]},{"featureType":"poi","elementType":"geometry","stylers":[{"color":"#000000"},{"lightness":21}]},{"featureType":"road.highway","elementType":"geometry.fill","stylers":[{"color":"#000000"},{"lightness":17}]},{"featureType":"road.highway","elementType":"geometry.stroke","stylers":[{"color":"#000000"},{"lightness":29},{"weight":0.2}]},{"featureType":"road.arterial","elementType":"geometry","stylers":[{"color":"#000000"},{"lightness":18}]},{"featureType":"road.local","elementType":"geometry","stylers":[{"color":"#000000"},{"lightness":16}]},{"featureType":"transit","elementType":"geometry","stylers":[{"color":"#000000"},{"lightness":19}]},{"featureType":"water","elementType":"geometry","stylers":[{"color":"#000000"},{"lightness":17}]}]';
					break;
				case '4':
					return '[{"featureType":"administrative","elementType":"all","stylers":[{"visibility":"on"},{"lightness":33}]},{"featureType":"landscape","elementType":"all","stylers":[{"color":"#f2e5d4"}]},{"featureType":"poi.park","elementType":"geometry","stylers":[{"color":"#c5dac6"}]},{"featureType":"poi.park","elementType":"labels","stylers":[{"visibility":"on"},{"lightness":20}]},{"featureType":"road","elementType":"all","stylers":[{"lightness":20}]},{"featureType":"road.highway","elementType":"geometry","stylers":[{"color":"#c5c6c6"}]},{"featureType":"road.arterial","elementType":"geometry","stylers":[{"color":"#e4d7c6"}]},{"featureType":"road.local","elementType":"geometry","stylers":[{"color":"#fbfaf7"}]},{"featureType":"water","elementType":"all","stylers":[{"visibility":"on"},{"color":"#acbcc9"}]}]';
					break;
				case '5':
					return '[{"featureType":"landscape.man_made","elementType":"geometry","stylers":[{"color":"#f7f1df"}]},{"featureType":"landscape.natural","elementType":"geometry","stylers":[{"color":"#d0e3b4"}]},{"featureType":"landscape.natural.terrain","elementType":"geometry","stylers":[{"visibility":"off"}]},{"featureType":"poi","elementType":"labels","stylers":[{"visibility":"off"}]},{"featureType":"poi.business","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"poi.medical","elementType":"geometry","stylers":[{"color":"#fbd3da"}]},{"featureType":"poi.park","elementType":"geometry","stylers":[{"color":"#bde6ab"}]},{"featureType":"road","elementType":"geometry.stroke","stylers":[{"visibility":"off"}]},{"featureType":"road","elementType":"labels","stylers":[{"visibility":"off"}]},{"featureType":"road.highway","elementType":"geometry.fill","stylers":[{"color":"#ffe15f"}]},{"featureType":"road.highway","elementType":"geometry.stroke","stylers":[{"color":"#efd151"}]},{"featureType":"road.arterial","elementType":"geometry.fill","stylers":[{"color":"#ffffff"}]},{"featureType":"road.local","elementType":"geometry.fill","stylers":[{"color":"black"}]},{"featureType":"transit.station.airport","elementType":"geometry.fill","stylers":[{"color":"#cfb2db"}]},{"featureType":"water","elementType":"geometry","stylers":[{"color":"#a2daf2"}]}]';
					break;
				case '6':
					return '[{"featureType":"administrative.locality","elementType":"all","stylers":[{"hue":"#2c2e33"},{"saturation":7},{"lightness":19},{"visibility":"on"}]},{"featureType":"landscape","elementType":"all","stylers":[{"hue":"#ffffff"},{"saturation":-100},{"lightness":100},{"visibility":"simplified"}]},{"featureType":"poi","elementType":"all","stylers":[{"hue":"#ffffff"},{"saturation":-100},{"lightness":100},{"visibility":"off"}]},{"featureType":"road","elementType":"geometry","stylers":[{"hue":"#bbc0c4"},{"saturation":-93},{"lightness":31},{"visibility":"simplified"}]},{"featureType":"road","elementType":"labels","stylers":[{"hue":"#bbc0c4"},{"saturation":-93},{"lightness":31},{"visibility":"on"}]},{"featureType":"road.arterial","elementType":"labels","stylers":[{"hue":"#bbc0c4"},{"saturation":-93},{"lightness":-2},{"visibility":"simplified"}]},{"featureType":"road.local","elementType":"geometry","stylers":[{"hue":"#e9ebed"},{"saturation":-90},{"lightness":-8},{"visibility":"simplified"}]},{"featureType":"transit","elementType":"all","stylers":[{"hue":"#e9ebed"},{"saturation":10},{"lightness":69},{"visibility":"on"}]},{"featureType":"water","elementType":"all","stylers":[{"hue":"#e9ebed"},{"saturation":-78},{"lightness":67},{"visibility":"simplified"}]}]';
					break;
				case '7':
					return '[{"featureType":"all","elementType":"all","stylers":[{"saturation":-100},{"gamma":0.5}]}]';
					break;
				case '8':
					return '[{"featureType":"water","elementType":"geometry","stylers":[{"color":"#193341"}]},{"featureType":"landscape","elementType":"geometry","stylers":[{"color":"#2c5a71"}]},{"featureType":"road","elementType":"geometry","stylers":[{"color":"#29768a"},{"lightness":-37}]},{"featureType":"poi","elementType":"geometry","stylers":[{"color":"#406d80"}]},{"featureType":"transit","elementType":"geometry","stylers":[{"color":"#406d80"}]},{"elementType":"labels.text.stroke","stylers":[{"visibility":"on"},{"color":"#3e606f"},{"weight":2},{"gamma":0.84}]},{"elementType":"labels.text.fill","stylers":[{"color":"#ffffff"}]},{"featureType":"administrative","elementType":"geometry","stylers":[{"weight":0.6},{"color":"#1a3541"}]},{"elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"poi.park","elementType":"geometry","stylers":[{"color":"#2c5a71"}]}]';
					break;
				case '9':
					return '[{"elementType":"labels.text","stylers":[{"visibility":"off"}]},{"featureType":"landscape.natural","elementType":"geometry.fill","stylers":[{"color":"#f5f5f2"},{"visibility":"on"}]},{"featureType":"administrative","stylers":[{"visibility":"off"}]},{"featureType":"transit","stylers":[{"visibility":"off"}]},{"featureType":"poi.attraction","stylers":[{"visibility":"off"}]},{"featureType":"landscape.man_made","elementType":"geometry.fill","stylers":[{"color":"#ffffff"},{"visibility":"on"}]},{"featureType":"poi.business","stylers":[{"visibility":"off"}]},{"featureType":"poi.medical","stylers":[{"visibility":"off"}]},{"featureType":"poi.place_of_worship","stylers":[{"visibility":"off"}]},{"featureType":"poi.school","stylers":[{"visibility":"off"}]},{"featureType":"poi.sports_complex","stylers":[{"visibility":"off"}]},{"featureType":"road.highway","elementType":"geometry","stylers":[{"color":"#ffffff"},{"visibility":"simplified"}]},{"featureType":"road.arterial","stylers":[{"visibility":"simplified"},{"color":"#ffffff"}]},{"featureType":"road.highway","elementType":"labels.icon","stylers":[{"color":"#ffffff"},{"visibility":"off"}]},{"featureType":"road.highway","elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"road.arterial","stylers":[{"color":"#ffffff"}]},{"featureType":"road.local","stylers":[{"color":"#ffffff"}]},{"featureType":"poi.park","elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"poi","elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"water","stylers":[{"color":"#71c8d4"}]},{"featureType":"landscape","stylers":[{"color":"#e5e8e7"}]},{"featureType":"poi.park","stylers":[{"color":"#8ba129"}]},{"featureType":"road","stylers":[{"color":"#ffffff"}]},{"featureType":"poi.sports_complex","elementType":"geometry","stylers":[{"color":"#c7c7c7"},{"visibility":"off"}]},{"featureType":"water","stylers":[{"color":"#a0d3d3"}]},{"featureType":"poi.park","stylers":[{"color":"#91b65d"}]},{"featureType":"poi.park","stylers":[{"gamma":1.51}]},{"featureType":"road.local","stylers":[{"visibility":"off"}]},{"featureType":"road.local","elementType":"geometry","stylers":[{"visibility":"on"}]},{"featureType":"poi.government","elementType":"geometry","stylers":[{"visibility":"off"}]},{"featureType":"landscape","stylers":[{"visibility":"off"}]},{"featureType":"road","elementType":"labels","stylers":[{"visibility":"off"}]},{"featureType":"road.arterial","elementType":"geometry","stylers":[{"visibility":"simplified"}]},{"featureType":"road.local","stylers":[{"visibility":"simplified"}]},{"featureType":"road"},{"featureType":"road"},{},{"featureType":"road.highway"}]';
					break;
				case '10':
					return '[{"featureType":"water","stylers":[{"visibility":"on"},{"color":"#b5cbe4"}]},{"featureType":"landscape","stylers":[{"color":"#efefef"}]},{"featureType":"road.highway","elementType":"geometry","stylers":[{"color":"#83a5b0"}]},{"featureType":"road.arterial","elementType":"geometry","stylers":[{"color":"#bdcdd3"}]},{"featureType":"road.local","elementType":"geometry","stylers":[{"color":"#ffffff"}]},{"featureType":"poi.park","elementType":"geometry","stylers":[{"color":"#e3eed3"}]},{"featureType":"administrative","stylers":[{"visibility":"on"},{"lightness":33}]},{"featureType":"road"},{"featureType":"poi.park","elementType":"labels","stylers":[{"visibility":"on"},{"lightness":20}]},{},{"featureType":"road","stylers":[{"lightness":20}]}]';
					break;
				case '11':
					return '[{"stylers":[{"hue":"#2c3e50"},{"saturation":250}]},{"featureType":"road","elementType":"geometry","stylers":[{"lightness":50},{"visibility":"simplified"}]},{"featureType":"road","elementType":"labels","stylers":[{"visibility":"off"}]}]';
					break;
				case '12':
					return '[{"featureType":"road","elementType":"geometry","stylers":[{"lightness":100},{"visibility":"simplified"}]},{"featureType":"water","elementType":"geometry","stylers":[{"visibility":"on"},{"color":"#C6E2FF"}]},{"featureType":"poi","elementType":"geometry.fill","stylers":[{"color":"#C5E3BF"}]},{"featureType":"road","elementType":"geometry.fill","stylers":[{"color":"#D1D1B8"}]}]';
					break;
				case '13':
					return '[{"featureType":"all","stylers":[{"saturation":0},{"hue":"#e7ecf0"}]},{"featureType":"road","stylers":[{"saturation":-70}]},{"featureType":"transit","stylers":[{"visibility":"off"}]},{"featureType":"poi","stylers":[{"visibility":"off"}]},{"featureType":"water","stylers":[{"visibility":"simplified"},{"saturation":-60}]}]';
					break;
				case '14':
					return '[{"featureType":"administrative","stylers":[{"visibility":"off"}]},{"featureType":"poi","stylers":[{"visibility":"simplified"}]},{"featureType":"road","stylers":[{"visibility":"simplified"}]},{"featureType":"water","stylers":[{"visibility":"simplified"}]},{"featureType":"transit","stylers":[{"visibility":"simplified"}]},{"featureType":"landscape","stylers":[{"visibility":"simplified"}]},{"featureType":"road.highway","stylers":[{"visibility":"off"}]},{"featureType":"road.local","stylers":[{"visibility":"on"}]},{"featureType":"road.highway","elementType":"geometry","stylers":[{"visibility":"on"}]},{"featureType":"water","stylers":[{"color":"#84afa3"},{"lightness":52}]},{"stylers":[{"saturation":-77}]},{"featureType":"road"}]';
					break;
				case '15':
					return '[{"stylers":[{"hue":"#dd0d0d"}]},{"featureType":"road","elementType":"labels","stylers":[{"visibility":"off"}]},{"featureType":"road","elementType":"geometry","stylers":[{"lightness":100},{"visibility":"simplified"}]}]';
					break;
				case '16':
					return '[{"featureType":"landscape","stylers":[{"hue":"#FFA800"},{"saturation":0},{"lightness":0},{"gamma":1}]},{"featureType":"road.highway","stylers":[{"hue":"#53FF00"},{"saturation":-73},{"lightness":40},{"gamma":1}]},{"featureType":"road.arterial","stylers":[{"hue":"#FBFF00"},{"saturation":0},{"lightness":0},{"gamma":1}]},{"featureType":"road.local","stylers":[{"hue":"#00FFFD"},{"saturation":0},{"lightness":30},{"gamma":1}]},{"featureType":"water","stylers":[{"hue":"#00BFFF"},{"saturation":6},{"lightness":8},{"gamma":1}]},{"featureType":"poi","stylers":[{"hue":"#679714"},{"saturation":33.4},{"lightness":-25.4},{"gamma":1}]}]';
					break;
				case '17':
					return '[{"stylers":[{"hue":"#16a085"},{"saturation":0}]},{"featureType":"road","elementType":"geometry","stylers":[{"lightness":100},{"visibility":"simplified"}]},{"featureType":"road","elementType":"labels","stylers":[{"visibility":"off"}]}]';
					break;
				case '18':
					return '[{"featureType":"road","elementType":"geometry","stylers":[{"visibility":"off"}]},{"featureType":"poi","elementType":"geometry","stylers":[{"visibility":"off"}]},{"featureType":"landscape","elementType":"geometry","stylers":[{"color":"#fffffa"}]},{"featureType":"water","stylers":[{"lightness":50}]},{"featureType":"road","elementType":"labels","stylers":[{"visibility":"off"}]},{"featureType":"transit","stylers":[{"visibility":"off"}]},{"featureType":"administrative","elementType":"geometry","stylers":[{"lightness":40}]}]';
					break;
				case '19':
					return '[{"featureType":"water","elementType":"geometry","stylers":[{"color":"#e9e9e9"},{"lightness":17}]},{"featureType":"landscape","elementType":"geometry","stylers":[{"color":"#f5f5f5"},{"lightness":20}]},{"featureType":"road.highway","elementType":"geometry.fill","stylers":[{"color":"#ffffff"},{"lightness":17}]},{"featureType":"road.highway","elementType":"geometry.stroke","stylers":[{"color":"#ffffff"},{"lightness":29},{"weight":0.2}]},{"featureType":"road.arterial","elementType":"geometry","stylers":[{"color":"#ffffff"},{"lightness":18}]},{"featureType":"road.local","elementType":"geometry","stylers":[{"color":"#ffffff"},{"lightness":16}]},{"featureType":"poi","elementType":"geometry","stylers":[{"color":"#f5f5f5"},{"lightness":21}]},{"featureType":"poi.park","elementType":"geometry","stylers":[{"color":"#dedede"},{"lightness":21}]},{"elementType":"labels.text.stroke","stylers":[{"visibility":"on"},{"color":"#ffffff"},{"lightness":16}]},{"elementType":"labels.text.fill","stylers":[{"saturation":36},{"color":"#333333"},{"lightness":40}]},{"elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"transit","elementType":"geometry","stylers":[{"color":"#f2f2f2"},{"lightness":19}]},{"featureType":"administrative","elementType":"geometry.fill","stylers":[{"color":"#fefefe"},{"lightness":20}]},{"featureType":"administrative","elementType":"geometry.stroke","stylers":[{"color":"#fefefe"},{"lightness":17},{"weight":1.2}]}]';
					break;
				case '20':
					return '[{"stylers":[{"visibility":"on"},{"saturation":-100}]},{"featureType":"water","stylers":[{"visibility":"on"},{"saturation":100},{"hue":"#00ffe6"}]},{"featureType":"road","elementType":"geometry","stylers":[{"saturation":100},{"hue":"#00ffcc"}]},{"featureType":"poi","stylers":[{"visibility":"off"}]},{"featureType":"poi.park","stylers":[{"visibility":"on"}]}]';
					break;
				case '21':
					return '[{"featureType":"administrative","elementType":"all","stylers":[{"visibility":"simplified"}]},{"featureType":"landscape","elementType":"geometry","stylers":[{"visibility":"simplified"},{"color":"#fcfcfc"}]},{"featureType":"poi","elementType":"geometry","stylers":[{"visibility":"simplified"},{"color":"#fcfcfc"}]},{"featureType":"road.highway","elementType":"geometry","stylers":[{"visibility":"simplified"},{"color":"#dddddd"}]},{"featureType":"road.arterial","elementType":"geometry","stylers":[{"visibility":"simplified"},{"color":"#dddddd"}]},{"featureType":"road.local","elementType":"geometry","stylers":[{"visibility":"simplified"},{"color":"#eeeeee"}]},{"featureType":"water","elementType":"geometry","stylers":[{"visibility":"simplified"},{"color":"#dddddd"}]}]';;
					break;

				default:
					return "";
					break;
			}
		}
	}

	// Send Mail through Contact Form
	if( !function_exists('vu_send_mail') ) {
		function vu_send_mail(){
			if(!empty($_POST['name']) and !empty($_POST['email']) and !empty($_POST['message']) and !empty($_POST['_wpnonce'])){
				$name = $_POST['name'];
				$email = $_POST['email'];
				$phone = isset($_POST['phone']) ? $_POST['phone'] : '';
				$subject = isset($_POST['subject']) ? $_POST['subject'] : '';
				$message = $_POST['message'];
				$_wpnonce = $_POST['_wpnonce'];

				if( !wp_verify_nonce($_wpnonce, 'vu_contact_form_nonce') or !is_email($email) ){
					echo json_encode(array('status' => 'error', 'title' => 'Error!', 'msg' => vu_get_option('msg-validation-error', __('Validation errors occurred. Please confirm the fields and submit it again.', 'bakery')))); exit();
				}

				$to = vu_get_option('mail-recipient');
				$subject = str_replace(array('[name]', '[email]', '[phone]', '[subject]'), array($name, $email, $phone, $subject), vu_get_option('mail-subject'));
				$message = str_replace(array('[name]', '[email]', '[phone]', '[subject]', '[message]'), array($name, $email, $phone, $subject, $message), vu_get_option('mail-body'));
				
				$headers = 'From: '. vu_get_option('mail-sender') . "\n". vu_get_option('mail-additional-headers') ."\n" . (vu_get_option('mail-use-html') ? 'Content-Type: text/html; charset=UTF-8;'. "\n" : '');
				$headers = str_replace(array('[name]', '[email]', '[phone]', '[subject]'), array($name, $email, $phone, $subject), $headers);

				if( wp_mail($to, $subject, $message, $headers) ){
					echo json_encode(array('status' => 'success', 'title' => 'Success!', 'msg' => vu_get_option('msg-mail-sent-ok', __('Your message has been sent successfully.', 'bakery')))); exit();
				} else {
					echo json_encode(array('status' => 'error', 'title' => 'Error!', 'msg' => vu_get_option('msg-mail-sent-ng', __('Failed to send your message. Please try again.', 'bakery')))); exit();
				}
			} else {
				echo json_encode(array('status' => 'error', 'title' => 'Error!', 'msg' => vu_get_option('msg-invalid-required', __('Please fill in all the required fields.', 'bakery')))); exit();
			}
		}
	}

	add_action("wp_ajax_vu_send_mail", "vu_send_mail");
	add_action("wp_ajax_nopriv_vu_send_mail", "vu_send_mail");

	// Get attachment Id by slug
	if( !function_exists('vu_get_attachment_id_by_slug') ) {
		function vu_get_attachment_id_by_slug( $slug ) {
			$args = array(
				'post_type' => 'attachment',
				'name' => sanitize_title($slug),
				'posts_per_page' => 1,
				'post_status' => 'inherit',
			);

			$_attachment = get_posts( $args );
			$attachment = $_attachment ? array_pop($_attachment) : null;

			return $attachment ? $attachment->ID : '';
		}
	}

	// Changing demo title in options panel so it's not folder name.
	if ( !function_exists( 'wbc_filter_title' ) ) {
		function wbc_importer_directory_title( $title ) {
			return trim( ucfirst( str_replace( "-", " ", $title ) ) );
		}

		add_filter( 'wbc_importer_directory_title', 'wbc_importer_directory_title', 10 );
	}

	// Deactivate widgets from all sidebars
	if ( !function_exists( 'vu_wbc_importer_before_widget_import' ) ) {
		function vu_wbc_importer_before_widget_import( $demo_active_import , $demo_data_directory_path ) {
			update_option( 'sidebars_widgets', array() );
		}

		add_action('wbc_importer_before_widget_import', 'vu_wbc_importer_before_widget_import', 10, 2 );
	}

	// Run After Demo Content Import
	if ( !function_exists( 'vu_wbc_importer_after_content_import' ) ) {
		function vu_wbc_importer_after_content_import( $demo_active_import , $demo_directory_path ) {
			reset( $demo_active_import );
			$current_key = key( $demo_active_import );

			// Import slider(s) for the current demo being imported
			if ( class_exists( 'RevSlider' ) ) {
				//If it's demo3 or demo5
				$wbc_sliders_array = array(
					'default' => 'bakery-main-slider.zip',
					'shop' => 'bakery-main-slider.zip',
				);

				if ( isset( $demo_active_import[$current_key]['directory'] ) && !empty( $demo_active_import[$current_key]['directory'] ) && array_key_exists( $demo_active_import[$current_key]['directory'], $wbc_sliders_array ) ) {
					$wbc_slider_import = $wbc_sliders_array[$demo_active_import[$current_key]['directory']];

					if ( file_exists( $demo_directory_path.$wbc_slider_import ) ) {
						$slider = new RevSlider();
						$slider->importSliderFromPost( true, true, $demo_directory_path.$wbc_slider_import );
					}
				}
			}

			// Setting Menus
			$wbc_menu_array = array( 'default', 'shop' );

			if ( isset( $demo_active_import[$current_key]['directory'] ) && !empty( $demo_active_import[$current_key]['directory'] ) && in_array( $demo_active_import[$current_key]['directory'], $wbc_menu_array ) ) {
				$nav_menu_locations = get_theme_mod('nav_menu_locations');
				
				// Main Menu Left
				$main_menu_left = get_term_by( 'name', 'Main Menu Left', 'nav_menu' );

				if ( isset( $main_menu_left->term_id ) ) {
					$nav_menu_locations['main-menu-left'] = $main_menu_left->term_id;
				}
				
				// Main Menu Right
				$main_menu_right = get_term_by( 'name', 'Main Menu Right', 'nav_menu' );

				if ( isset( $main_menu_right->term_id ) ) {
					$nav_menu_locations['main-menu-right'] = $main_menu_right->term_id;
				}

				// Set Menus
				set_theme_mod('nav_menu_locations', $nav_menu_locations);
			}

			// Set Home Page
			$wbc_home_pages = array(
				'default' => 'Home',
				'shop' => 'Home'
			);

			if ( isset( $demo_active_import[$current_key]['directory'] ) && !empty( $demo_active_import[$current_key]['directory'] ) && array_key_exists( $demo_active_import[$current_key]['directory'], $wbc_home_pages ) ) {
				$page = get_page_by_title( $wbc_home_pages[$demo_active_import[$current_key]['directory']] );
				if ( isset( $page->ID ) ) {
					update_option( 'page_on_front', $page->ID );
					update_option( 'show_on_front', 'page' );
				}
			}

			// Set Blog Page
			$wbc_blog_pages = array(
				'default' => 'Blog',
				'shop' => 'Blog'
			);

			if ( isset( $demo_active_import[$current_key]['directory'] ) && !empty( $demo_active_import[$current_key]['directory'] ) && array_key_exists( $demo_active_import[$current_key]['directory'], $wbc_blog_pages ) ) {
				$page = get_page_by_title( $wbc_blog_pages[$demo_active_import[$current_key]['directory']] );
				if ( isset( $page->ID ) ) {
					update_option( 'page_for_posts', $page->ID );
				}
			}

			// Shop Pages
			if ( isset( $demo_active_import[$current_key]['directory'] ) && !empty( $demo_active_import[$current_key]['directory'] ) && $demo_active_import[$current_key]['directory'] == 'shop' ) {
				//Shop Page
				$shop_page = get_page_by_title('Shop');
				
				if ( isset( $shop_page->ID ) ) {
					update_option( 'woocommerce_shop_page_id', $shop_page->ID );
				}

				//Cart Page
				$cart_page = get_page_by_title('Cart');

				if ( isset( $cart_page->ID ) ) {
					update_option( 'woocommerce_cart_page_id', $cart_page->ID );
				}

				//Checkout Page
				$checkout_page = get_page_by_title('Checkout');

				if ( isset( $checkout_page->ID ) ) {
					update_option( 'woocommerce_checkout_page_id', $checkout_page->ID );
				}

				//My Account Page
				$myaccount_page = get_page_by_title('My Account');

				if ( isset( $myaccount_page->ID ) ) {
					update_option( 'woocommerce_myaccount_page_id', $myaccount_page->ID );
				}
			}

			// Fix Portfolio Pages for 'Default' Demo
			if ( isset( $demo_active_import[$current_key]['directory'] ) && !empty( $demo_active_import[$current_key]['directory'] ) && $demo_active_import[$current_key]['directory'] == 'default' ) {
				// Fix SVG Icons for Portfolio Categories
				$upload_dir = wp_upload_dir();

				//Term: Breads
				$term_breads = get_term_by( 'slug', 'breads', 'portfolio-category' );
				update_option('vu_portfolio-category-image-'. $term_breads->term_id, $upload_dir['baseurl'] . '/2015/08/breads.svg');

				//Term: Cakes
				$term_cakes = get_term_by( 'slug', 'cakes', 'portfolio-category' );
				update_option('vu_portfolio-category-image-'. $term_cakes->term_id, $upload_dir['baseurl'] . '/2015/08/cakes.svg');
				
				//Term: Cookies
				$term_cookies = get_term_by( 'slug', 'cookies', 'portfolio-category' );
				update_option('vu_portfolio-category-image-'. $term_cookies->term_id, $upload_dir['baseurl'] . '/2015/08/cookies.svg');
				
				//Term: Croissants
				$term_croissants = get_term_by( 'slug', 'croissants', 'portfolio-category' );
				update_option('vu_portfolio-category-image-'. $term_croissants->term_id, $upload_dir['baseurl'] . '/2015/08/croissants.svg');
				
				//Term: Muffins
				$term_muffins = get_term_by( 'slug', 'muffins', 'portfolio-category' );
				update_option('vu_portfolio-category-image-'. $term_muffins->term_id, $upload_dir['baseurl'] . '/2015/08/muffins.svg');

				//Fix Content (Term Id of Term 'All') Portfolio with Filter Pages
				$term_all = get_term_by( 'slug', 'all', 'portfolio-category' );

				$pages_slug = array(
					'products-with-filter',
					'products-3-columns-with-filter',
					'products-square-3-columns-with-filter',
					'products-square-4-columns-with-filter'
				);

				foreach ($pages_slug as $page_slug) {
					$page = get_posts( array( 'name' => $page_slug , 'post_type' => 'page' ) );

					$page[0]->post_content = str_replace('portfolio_category="21"', 'portfolio_category="'. $term_all->term_id .'"', $page[0]->post_content);
					
					wp_update_post($page[0]);
				}
			}

			// Fix Products Pages for 'Shop' Demo
			if ( isset( $demo_active_import[$current_key]['directory'] ) && !empty( $demo_active_import[$current_key]['directory'] ) && $demo_active_import[$current_key]['directory'] == 'shop' ) {
				// Fix SVG Icons for Products Categories

				//Term: Breads
				$term_breads = get_term_by( 'slug', 'breads', 'product_cat' );
				update_woocommerce_term_meta( $term_breads->term_id, 'thumbnail_id', vu_get_attachment_id_by_slug('breads') );

				//Term: Cakes
				$term_cakes = get_term_by( 'slug', 'cakes', 'product_cat' );
				update_woocommerce_term_meta( $term_cakes->term_id, 'thumbnail_id', vu_get_attachment_id_by_slug('cakes') );
				
				//Term: Cookies
				$term_cookies = get_term_by( 'slug', 'cookies', 'product_cat' );
				update_woocommerce_term_meta( $term_cookies->term_id, 'thumbnail_id', vu_get_attachment_id_by_slug('cookies') );
				
				//Term: Croissants
				$term_croissants = get_term_by( 'slug', 'croissants', 'product_cat' );
				update_woocommerce_term_meta( $term_croissants->term_id, 'thumbnail_id', vu_get_attachment_id_by_slug('croissants') );
				
				//Term: Muffins
				$term_muffins = get_term_by( 'slug', 'muffins', 'product_cat' );
				update_woocommerce_term_meta( $term_muffins->term_id, 'thumbnail_id', vu_get_attachment_id_by_slug('muffins') );

				//Fix Content (Term Id of Term 'All') Products with Filter Pages
				$term_all = get_term_by( 'slug', 'all', 'product_cat' );

				$pages_slug = array(
					'products-with-filter',
					'products-3-columns-with-filter',
					'products-square-3-columns-with-filter',
					'products-square-4-columns-with-filter'
				);

				foreach ($pages_slug as $page_slug) {
					$page = get_posts( array( 'name' => $page_slug , 'post_type' => 'page' ) );

					$page[0]->post_content = str_replace('product_category="33"', 'product_category="'. $term_all->term_id .'"', $page[0]->post_content);
					
					wp_update_post($page[0]);
				}
			}

			// Posts per Page and RSS
			update_option('posts_per_page', '3');
			update_option('posts_per_rss', '3');

			// Delete default posts
			wp_delete_post(1); //Hello World!
			wp_delete_post(2); //Sample Page
		}

		add_action( 'wbc_importer_after_content_import', 'vu_wbc_importer_after_content_import', 10, 2 );
	}
?>