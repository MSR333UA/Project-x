<?php
	/**
	 * Bakery WordPress Theme
	 */

	// Constants
	define('THEME_DIR', get_template_directory() . '/');
	define('THEME_URL', get_template_directory_uri() . '/');
	define('THEME_ASSETS', THEME_URL . 'assets/');
	define('THEME_ADMIN_ASSETS', THEME_URL . 'includes/admin/');
	define('TD', 'bakery');

	// Theme Content Width
	$content_width = ! isset($content_width) ? 1170 : $content_width;

	// Initial Actions
	add_action('after_setup_theme', 'vu_after_setup_theme');
	add_action('after_setup_theme', 'vu_load_theme_textdomain');
	add_action('init', 'vu_init');
	add_action('widgets_init', 'vu_widgets_init');
	add_action('wp_enqueue_scripts', 'vu_wp_enqueue_scripts');
	add_action('wp_head', 'vu_wp_head');
	add_action('wp_footer', 'vu_wp_footer');
	add_action('admin_enqueue_scripts', 'vu_admin_enqueue_scripts');
	add_action('woocommerce_installed', 'vu_woocommerce_installed');
	add_action('tgmpa_register', 'bakery_register_required_plugins');

	// Core Files
	require_once('includes/vu-functions.php');
	require_once('includes/vu-actions.php');
	require_once('includes/vu-filters.php');

	// Meta
	require_once('includes/meta/page-header-settings.php');
	require_once('includes/meta/post-meta.php');

	// Shortcodes
	require_once('includes/shortcodes/title-section.php');
	require_once('includes/shortcodes/service-item.php');
	require_once('includes/shortcodes/menu-item.php');
	require_once('includes/shortcodes/gallery.php');
	require_once('includes/shortcodes/gallery-item.php');
	require_once('includes/shortcodes/image-slider.php');
	require_once('includes/shortcodes/blog-posts.php');
	require_once('includes/shortcodes/map.php');
	require_once('includes/shortcodes/contact-form.php');
	require_once('includes/shortcodes/map-and-contact-form.php');
	require_once('includes/shortcodes/countdown.php');
	require_once('includes/shortcodes/count-up.php');
	require_once('includes/shortcodes/milestone.php');
	require_once('includes/shortcodes/client.php');
	require_once('includes/shortcodes/others.php');

	// Theme Options
	require_once('includes/options/redux-framework.php');
	require_once('includes/options/bakery-options.php');

	// Library Files
	require_once('includes/lib/twitter/class-ezTweet.php');
	require_once('includes/lib/MailChimp.php');
	require_once('includes/lib/class-tgm-plugin-activation.php');

	// VC Files
	if( in_array('js_composer/js_composer.php', apply_filters('active_plugins', get_option('active_plugins'))) ) {
		require_once('includes/vc-addons/config.php');
		require_once('includes/vc-addons/vc-modify.php');
	} else {
		require_once('includes/vc-addons/class-VcLoopQueryBuilder.php');
		require_once('includes/vc-addons/vc-functions.php');
	}

	// WC Files
	if( in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins'))) ) {
		require_once('includes/wc-addons/config.php');
		require_once('includes/wc-addons/wc-modify.php');
		require_once('includes/wc-addons/wc-functions.php');
	}

	// Setup Menu Locations Notification
	$nav_menu_locations = get_theme_mod('nav_menu_locations');

	if( (!isset($nav_menu_locations['main-menu-full']) or $nav_menu_locations['main-menu-full'] == 0) and (!isset($nav_menu_locations['main-menu-left']) or $nav_menu_locations['main-menu-left'] == 0) and (!isset($nav_menu_locations['main-menu-right']) or $nav_menu_locations['main-menu-right'] == 0) ) {
		add_action('admin_notices', 'vu_setup_menus_notice');
	}
?>