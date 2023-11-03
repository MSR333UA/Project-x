<?php
	/**
	 *	Bakery WordPress Theme
	 */

	// Theme initialization
	function vu_init(){
		// Font Awesome
		wp_register_style('font-awesome-fa', THEME_ASSETS . 'font-awesome/css/font-awesome.min.css', null, null);

		// Bootstrap
		wp_register_style('bootstrap', THEME_ASSETS . 'bootstrap/css/bootstrap.min.css', null, null);
		wp_register_script('bootstrap', THEME_ASSETS . 'bootstrap/js/bootstrap.min.js', array('jquery'), null, true);

		// Owl Carousel
		wp_register_style('owl-carousel', THEME_ASSETS . 'owl-carousel/owl.carousel.css', null, null);
		wp_register_script('owl-carousel', THEME_ASSETS . 'owl-carousel/owl.carousel.min.js', array('jquery'), null, true);

		// Magnific Popup
		wp_register_style('magnific-popup', THEME_ASSETS . 'magnific-popup/magnific-popup.css', null, null);
		wp_register_script('magnific-popup', THEME_ASSETS . 'magnific-popup/magnific-popup.min.js', array('jquery'), null, true);

		// Others Scripts
		wp_register_script('modernizr', THEME_ASSETS . 'scripts/modernizr.js', null, null, false);
		wp_register_script('google-map', '//maps.googleapis.com/maps/api/js?v=3.24&key='. vu_get_option('google-map-api-key'), null, null, true);
		wp_register_script('google-apis', '//apis.google.com/js/platform.js', null, null, true);
		wp_register_script('scrollTo', THEME_ASSETS . 'scripts/jquery.scrollTo.min.js', array('jquery'), null, true);
		wp_register_script('parallax', THEME_ASSETS . 'scripts/parallax.min.js', array('jquery'), null, true);
		wp_register_script('placeholder-fallback', THEME_ASSETS . 'scripts/placeholder-fallback.js', array('jquery'), null, true);
		wp_register_script('inview', THEME_ASSETS . 'scripts/jquery.inview.min.js', array('jquery'), null, true);
		wp_register_script('isotope', THEME_ASSETS . 'scripts/jquery.isotope.min.js', array('jquery'), null, true);
		wp_register_script('flickr-feed', THEME_ASSETS . 'scripts/jflickrfeed.min.js', array('jquery'), null, true);
		wp_register_script('twitter-feed', THEME_ASSETS . 'scripts/jquery.tweet.min.js', array('jquery'), null, true);
		wp_register_script('imagesloaded', THEME_ASSETS . 'scripts/jquery.imagesloaded.min.js', array('jquery'), null, true);
		wp_register_script('counterup', THEME_ASSETS . 'scripts/jquery.counterup.js', array('jquery'), null, true);
		wp_register_script('waypoints', THEME_ASSETS . 'scripts/waypoints.js', array('jquery'), null, true);
		wp_register_script('countdown', THEME_ASSETS . 'scripts/jquery.countdown.min.js', array('jquery'), null, true);
		wp_register_script('smooth-scroll', THEME_ASSETS . 'scripts/jquery.smooth-scroll.min.js', array('jquery'), null, true);

		// Bakery Theme
		wp_register_style('bakery-main', THEME_URL . 'style.css', null, null);
		wp_register_style('bakery-rtl', THEME_URL . 'rtl.css', array('bakery-main'), null);

		if( in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins'))) ) {
			wp_register_script('bakery-woocommerce', THEME_ASSETS . 'scripts/woocommerce.js', array('jquery'), null, true);
			wp_register_style('bakery-woocommerce', THEME_ASSETS . 'styles/woocommerce.css', array('bakery-main'), null);
		}

		wp_register_script('bakery-main', THEME_ASSETS . 'scripts/main.js', array('jquery'), null, true);
		
		// Config Object
		wp_localize_script( 'bakery-main', 'vu_config',
			array(
				'ajaxurl' => admin_url("admin-ajax.php"),
				'home_url' => get_home_url()
			)
		);

		// Admin
		wp_register_style('vu_admin-custom-style', THEME_ADMIN_ASSETS . 'styles/custom.css', null, null);
		wp_register_script( 'vu_admin-custom-script', THEME_ADMIN_ASSETS . 'scripts/custom.js', array( 'jquery', 'wp-color-picker' ), null, true );
	}

	// After Setup Theme
	function vu_after_setup_theme(){
		// Theme Support
		add_theme_support('menus');
		add_theme_support('widgets');
		add_theme_support('automatic-feed-links');
		add_theme_support('post-thumbnails');
		add_theme_support('featured-image');
		add_theme_support('woocommerce');
		add_theme_support('post-formats', array('video', 'audio', 'image', 'gallery') );

		// Attachment sizes
		add_image_size('ratio-1:1', 600, 600, true);
		#add_image_size('ratio-1:2', 380, 760, true);
		add_image_size('ratio-3:2', 800, 533, true);
		add_image_size('ratio-3:4', 570, 760, true);
		add_image_size('ratio-4:3', 800, 600, true);
		#add_image_size('ratio-5:4', 900, 720, true);
		add_image_size('ratio-16:9', 1024, 576, true);
		
		// Register Menus
		register_nav_menus(
			array(
				'main-menu-full' => 'Main Menu Full',
				'main-menu-left' => 'Main Menu Left',
				'main-menu-right' => 'Main Menu Right'
			)
		);
	}

	// Theme Textdomain
	function vu_load_theme_textdomain(){
		if ( $loaded = load_theme_textdomain( 'bakery', trailingslashit( WP_LANG_DIR ) . 'bakery' ) ) {
			return $loaded;
		} else if ( $loaded = load_theme_textdomain( 'bakery', get_stylesheet_directory() . '/languages' ) ) {
			return $loaded;
		} else {
			load_theme_textdomain( 'bakery', get_template_directory() . '/languages' );
		}
	}

	// Enqueue Scritps
	function vu_wp_enqueue_scripts(){
		// Styles
		wp_enqueue_style(array('font-awesome-fa', 'bootstrap', 'owl-carousel', 'magnific-popup', 'bakery-main'));

		// WooCommerce Style
		if( in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins'))) ) {
			wp_enqueue_style('bakery-woocommerce');
		}
		
		// RTL
		if( is_rtl() ){
			wp_enqueue_style(array('bakery-rtl'));
		}
		
		// Scripts
		wp_enqueue_script(array('modernizr', 'jquery'));
	}

	// Enqueue Admin Scritps
	function vu_admin_enqueue_scripts(){
		// Styles
		wp_enqueue_style( array('font-awesome-fa', 'wp-color-picker', 'vu_admin-custom-style') );

		//Media Frame
		wp_enqueue_media();

		wp_localize_script( 'vu_admin-custom-script', 'vu_admin_config',
			array(
				'media' => array(
					'title' => array(
						"single" => __( 'Add Image', 'bakery' ), // This will be used as the default title
						"multiple" => __( 'Add Images', 'bakery' )
					),
					'button' => array(
						"single" => __( 'Add Image', 'bakery' ),
						"multiple" => __( 'Add Images', 'bakery' )
					)
				)
			)
		);

		// Scripts
		wp_enqueue_script( 'vu_admin-custom-script' );
	}

	// Head Init
	function vu_wp_head(){
	?>
		<link rel="icon" href="<?php echo trim(vu_get_option( array('favicon', 'url') )) != '' ? vu_get_option( array('favicon', 'url') ) : THEME_ASSETS .'images/milingona/favicon.png'; ?>" type="image/png">
		
		<link rel="apple-touch-icon" href="<?php echo trim(vu_get_option( array('apple-touch-icon', 'url') )) != '' ? vu_get_option( array('apple-touch-icon', 'url') ) : THEME_ASSETS .'images/milingona/apple-touch-icon.png'; ?>">
		<link rel="apple-touch-icon" sizes="72x72" href="<?php echo trim(vu_get_option( array('apple-touch-icon', 'url') )) != '' ? vu_get_option( array('apple-touch-icon', 'url') ) : THEME_ASSETS .'images/milingona/apple-touch-icon-72x72.png'; ?>">
		<link rel="apple-touch-icon" sizes="114x114" href="<?php echo trim(vu_get_option( array('apple-touch-icon', 'url') )) != '' ? vu_get_option( array('apple-touch-icon', 'url') ) : THEME_ASSETS .'images/milingona/apple-touch-icon-114x114.png'; ?>">

		<?php if( vu_get_option('open-graph-meta-data', true) ) : ?>
			<?php global $post; ##### FaceBook Open Graph ##### ?>

			<?php if ( is_single() ) : ?>
				<meta property="og:url" content="<?php the_permalink() ?>"/>
				<meta property="og:title" content="<?php single_post_title(''); ?>" />
				<meta property="og:description" content="<?php $description = trim(preg_replace('/\s+/', ' ', str_replace(array("\n", "\r", "\t"), ' ', strip_tags($post->post_content)))); $desc_array = explode(' ', $description); echo esc_attr(implode(" ", array_splice($desc_array, 0, 25))); ?>" />
				<meta property="og:type" content="article" />
				<meta property="og:image" content="<?php if (function_exists('wp_get_attachment_thumb_url')) {echo wp_get_attachment_thumb_url(get_post_thumbnail_id($post->ID)); }?>" />
			<?php else : ?>
				<meta property="og:site_name" content="<?php bloginfo('name'); ?>" />
				<meta property="og:description" content="<?php bloginfo('description'); ?>" />
				<meta property="og:type" content="website" />
				<meta property="og:image" content="<?php echo esc_url(vu_get_option( array('logo', 'url') )); ?>" />
			<?php endif; ?>
		<?php endif; ?>
		
		<meta name="generator" content="Powered by Milingona" />
		<?php echo '<style type="text/css" id="vu_dynamic-css">'. vu_dynamic_css() .'</style>'; ?>
	<?php
	}

	// Footer Init
	function vu_wp_footer(){
		// Smooth Scroll
		if( vu_get_option('smooth-scroll') ){
			wp_enqueue_script('smooth-scroll');
		}

		// WooCommerce Script
		if( in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins'))) ) {
			wp_enqueue_script('bakery-woocommerce');
		}

		// Scripts
		wp_enqueue_script(array('bootstrap', 'owl-carousel', 'magnific-popup', 'scrollTo', 'parallax', 'placeholder-fallback', 'inview', 'google-map', 'counterup', 'waypoints', 'imagesloaded', 'bakery-main'));

		// Custom JS form Theme Options
		if( trim(vu_get_option('custom-js')) !== '' ) {
			echo '<script type="text/javascript">'. stripslashes(str_replace(array('\n', '\r', '\t'), '', vu_get_option('custom-js'))) .'</script>';
		}

		// Google Analytics Tracking Code
		if( trim(vu_get_option('google-analytics-tracking-code')) !== '' ) {
			echo vu_get_option('google-analytics-tracking-code');
		}
	}

	// Widgets Init
	function vu_widgets_init(){
		// Blog Sidebar
		$blog_sidebar = array(
			'id' => 'blog_sidebar',
			'name' => 'Blog Sidebar',
			
			'before_widget' => '<div class="widget %2$s %1$s clearfix'. vu_animation(false) .'">',
			'after_widget' => '</div>',
			
			'before_title' => '<h3 class="widget_title">',
			'after_title' => '</h3>'
		);
		
		register_sidebar($blog_sidebar);

		// Shop Sidebar
		$shop_sidebar = array(
			'id' => 'shop_sidebar',
			'name' => 'Shop Sidebar',
			
			'before_widget' => '<div class="widget %2$s %1$s clearfix'. vu_animation(false) .'">',
			'after_widget' => '</div>',
			
			'before_title' => '<h3 class="widget_title">',
			'after_title' => '</h3>'
		);
		
		register_sidebar($shop_sidebar);
		
		// Footer Top Sidebar
		$footer_top_sidebar = array(
			'id' => 'footer-top-sidebar',
			'name' => 'Footer Top Sidebar',
			
			'before_widget' => '<div class="widget m-b-50 clearfix %2$s %1$s">',
			'after_widget' => '</div>',
			
			'before_title' => '<h3 class="widget_title">',
			'after_title' => '</h3>'
		);
		
		register_sidebar($footer_top_sidebar);
		
		// Footer Bottom Sidebar
		$footer_bottom_sidebar = array(
			'id' => 'footer-bottom-sidebar',
			'name' => 'Footer Bottom Sidebar',
			
			'before_widget' => '<div class="col-xs-12 col-sm-6 col-md-'. (trim(vu_get_option('footer-bottom-layout')) != '' ? vu_get_option('footer-bottom-layout') : '3') . vu_animation(false, 'delay_value') .'"><div class="widget m-b-40 clearfix %2$s %1$s">',
			'after_widget' => '</div></div>',
			
			'before_title' => '<h3 class="widget_title">',
			'after_title' => '</h3>'
		);
		
		register_sidebar($footer_bottom_sidebar);
	}

	// Setup Menu Location
	function vu_setup_menus_notice() {
		if( !isset($_GET['action']) or $_GET['action'] != 'locations') { ?>
			<div class="updated">
				<p>
					<strong><?php _e( 'Warning:', 'bakery'); ?></strong>
					<?php _e('Please set up the menu location for this theme. <a href="'. admin_url("nav-menus.php?action=locations") .'">Click here to go to the menu location settings &raquo;</a>', 'bakery'); ?>
				</p>
			</div>
		<?php
		}
	}

	// Change Default WooCommerce Images Sizes
	function vu_woocommerce_installed() {
		$shop_thumbnail_image_size = array(
			'width' => 180,
			'height' => 180,
			'crop' => true
		);

		update_option( 'shop_thumbnail_image_size', $shop_thumbnail_image_size );

		$shop_single_image_size = array(
			'width' => 600,
			'height' => 600,
			'crop' => true
		);

		update_option( 'shop_single_image_size', $shop_single_image_size );

		$shop_catalog_image_size = array(
			'width' => 570,
			'height' => 760,
			'crop' => true
		);

		update_option( 'shop_catalog_image_size', $shop_catalog_image_size );
	}

	// Register Theme Plugins
	function bakery_register_required_plugins() {
		$plugins = array(
			array(
				'name'                   => 'Bakery Custom Post Types',
				'slug'                   => 'bakery-cpt',
				'source'                 => 'http://dl.milingona.co/bakery/1.3.6/bakery-cpt.zip',
				'required'               => true,
				'version'                => '1.1.8',
				'force_activation'       => false,
				'force_deactivation'     => false,
			),
			array(
				'name'                   => 'WP Bakery Visual Composer',
				'slug'                   => 'js_composer',
				'source'                 => 'http://dl.milingona.co/bakery/1.3.6/js_composer.zip',
				'required'               => true,
				'version'                => '4.12',
				'force_activation'       => false,
				'force_deactivation'     => false,
			),
			array(
				'name'                   => 'Revolution Slider',
				'slug'                   => 'revslider',
				'source'                 => 'http://dl.milingona.co/bakery/1.3.6/revslider.zip',
				'required'               => true,
				'version'                => '5.2.6',
				'force_activation'       => false,
				'force_deactivation'     => false,
			),
			array(
				'name'                   => 'Envato WordPress Toolkit',
				'slug'                   => 'envato-wordpress-toolkit',
				'source'                 => 'http://dl.milingona.co/bakery/1.3.6/envato-wordpress-toolkit.zip',
				'required'               => false,
				'version'                => '1.7.3',
				'force_activation'       => false,
				'force_deactivation'     => false,
			)
		);
		
		$config = array(
			'domain'                              => 'bakery', 
			'default_path'                        => '',
			'parent_slug'                         => 'themes.php',
			'capability'                          => 'edit_theme_options',
			'menu'                                => 'install-required-plugins',
			'has_notices'                         => true,
			'dismissable'                         => true,
			'is_automatic'                        => false,
			'message'                             => '',
			'strings'                             => array(
				'page_title'                         => __( 'Install Required Plugins', 'bakery' ),
				'menu_title'                         => __( 'Install Plugins', 'bakery' ),
				'installing'                         => __( 'Installing Plugin: %s', 'bakery' ),
				'oops'                               => __( 'Something went wrong with the plugin API.', 'bakery' ),
				'notice_can_install_required'        => _n_noop( 'This theme requires the following plugin: %1$s.', 'This theme requires the following plugins: %1$s.' ),
				'notice_can_install_recommended'     => _n_noop( 'This theme recommends the following plugin: %1$s.', 'This theme recommends the following plugins: %1$s.' ),
				'notice_cannot_install'              => _n_noop( 'Sorry, but you do not have the correct permissions to install the %s plugin. Contact the administrator of this site for help on getting the plugin installed.', 'Sorry, but you do not have the correct permissions to install the %s plugins. Contact the administrator of this site for help on getting the plugins installed.' ),
				'notice_can_activate_required'       => _n_noop( 'The following required plugin is currently inactive: %1$s.', 'The following required plugins are currently inactive: %1$s.' ),
				'notice_can_activate_recommended'    => _n_noop( 'The following recommended plugin is currently inactive: %1$s.', 'The following recommended plugins are currently inactive: %1$s.' ), 
				'notice_cannot_activate'             => _n_noop( 'Sorry, but you do not have the correct permissions to activate the %s plugin. Contact the administrator of this site for help on getting the plugin activated.', 'Sorry, but you do not have the correct permissions to activate the %s plugins. Contact the administrator of this site for help on getting the plugins activated.' ),
				'notice_ask_to_update'               => _n_noop( 'The following plugin needs to be updated to its latest version to ensure maximum compatibility with this theme: %1$s.', 'The following plugins need to be updated to their latest version to ensure maximum compatibility with this theme: %1$s.' ),
				'notice_cannot_update'               => _n_noop( 'Sorry, but you do not have the correct permissions to update the %s plugin. Contact the administrator of this site for help on getting the plugin updated.', 'Sorry, but you do not have the correct permissions to update the %s plugins. Contact the administrator of this site for help on getting the plugins updated.' ),
				'install_link'                       => _n_noop( 'Begin installing plugin', 'Begin installing plugins' ),
				'activate_link'                      => _n_noop( 'Activate installed plugin', 'Activate installed plugins' ),
				'return'                             => __( 'Return to Required Plugins Installer', 'bakery' ),
				'plugin_activated'                   => __( 'Plugin activated successfully.', 'bakery' ),
				'complete'                           => __( 'All plugins installed and activated successfully. %s', 'bakery' ), 
				'nag_type'                           => 'updated'
			)
		);

		tgmpa( $plugins, $config );
	}
?>