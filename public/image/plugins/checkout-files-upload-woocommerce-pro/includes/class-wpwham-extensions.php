<?php
/**
 * WP Wham Extensions
 *
 * @version 1.0.2
 * @author  WP Wham
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( class_exists( 'Wpwham_Extensions' ) ) {
	return;
}

class Wpwham_Extensions {
	
	protected static $instance = null;
	
	public $extensions = array();
	
	public static function get_instance() {
		
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	public function __construct() {
		
		$this->extensions = array(
			'checkout-files-upload-for-woocommerce' => array(
				'title'       => __( 'Checkout Files Upload for WooCommerce', 'checkout-files-upload-woocommerce' ),
				'description' => __( 'Let your customers upload files on (or after) WooCommerce checkout.', 'checkout-files-upload-woocommerce' ),
				'url'         => 'https://wpwham.com/products/checkout-files-upload-for-woocommerce/',
				'image'       => 'https://wpwham.com/wp-content/uploads/edd/2018/10/Checkout-Files-Upload-for-WooCommerce-150x150.jpg',
			),
			'product-open-pricing-name-your-price-for-woocommerce' => array(
				'title'       => __( 'Product Open Pricing (Name Your Price) for WooCommerce', 'checkout-files-upload-woocommerce' ),
				'description' => __( 'Create open price (i.e. "name your price" or "pay your price") products in WooCommerce where customers enter their own price for a product.', 'checkout-files-upload-woocommerce' ),
				'url'         => 'https://wpwham.com/products/product-open-pricing-name-your-price-for-woocommerce/',
				'image'       => 'https://wpwham.com/wp-content/uploads/edd/2019/09/Product-Open-Pricing-Name-Your-Price-for-WooCommerce-Pro-150x150.jpg',
			),
			'product-visibility-by-user-role-for-woocommerce' => array(
				'title'       => __( 'Product Visibility by User Role for WooCommerce', 'checkout-files-upload-woocommerce' ),
				'description' => __( 'Display WooCommerce products by customer\'s user role.', 'checkout-files-upload-woocommerce' ),
				'url'         => 'https://wpwham.com/products/product-visibility-by-user-role-for-woocommerce/',
				'image'       => 'https://wpwham.com/wp-content/uploads/edd/2019/08/Product-Visibility-by-User-Role-for-WooCommerce-150x150.png',
			),
			'sku-generator-for-woocommerce' => array(
				'title'       => __( 'SKU Generator for WooCommerce', 'checkout-files-upload-woocommerce' ),
				'description' => __( 'Add full Stock Keeping Unit (SKU) support to WooCommerce.', 'checkout-files-upload-woocommerce' ),
				'url'         => 'https://wpwham.com/products/sku-generator-for-woocommerce/',
				'image'       => 'https://wpwham.com/wp-content/uploads/edd/2018/10/SKU-for-WooCommerce-Plugin-150x150.jpg',
			),
			'currency-switcher-for-woocommerce' => array(
				'title'       => __( 'Currency Switcher for WooCommerce', 'checkout-files-upload-woocommerce' ),
				'description' => __( 'Add currency switcher to your WooCommerce shop.', 'checkout-files-upload-woocommerce' ),
				'url'         => 'https://wpwham.com/products/currency-switcher-for-woocommerce/',
				'image'       => 'https://wpwham.com/wp-content/uploads/edd/2019/07/Currency-Switcher-for-WooCommerce-plugin-150x150.jpg',
			),
			'custom-price-labels-for-woocommerce' => array(
				'title'       => __( 'Custom Price Labels for WooCommerce', 'checkout-files-upload-woocommerce' ),
				'description' => __( 'Create any custom price label for any WooCommerce product.', 'checkout-files-upload-woocommerce' ),
				'url'         => 'https://wpwham.com/products/custom-price-labels-for-woocommerce/',
				'image'       => 'https://wpwham.com/wp-content/uploads/edd/2018/10/WooCommerce-Custom-Price-Label-Plugin-150x150.jpg',
			),
			'more-sorting-options-for-woocommerce' => array(
				'title'       => __( 'More Sorting Options for WooCommerce', 'checkout-files-upload-woocommerce' ),
				'description' => __( 'Add new custom, rearrange, remove or rename WooCommerce sorting options.', 'checkout-files-upload-woocommerce' ),
				'url'         => 'https://wpwham.com/products/more-sorting-options-for-woocommerce/',
				'image'       => 'https://wpwham.com/wp-content/uploads/edd/2019/06/WooCommerce-More-Sorting-150x150.png',
			),
			'multi-order-for-woocommerce' => array(
				'title'       => __( 'Multi Order for WooCommerce', 'checkout-files-upload-woocommerce' ),
				'description' => __( 'Split your WooCommerce orders in suborders.', 'checkout-files-upload-woocommerce' ),
				'url'         => 'https://wpwham.com/products/multi-order-for-woocommerce/',
				'image'       => 'https://wpwham.com/wp-content/uploads/edd/2019/09/Multi-Order-for-WooCommerce-150x150.png',
			),
			'bulk-regenerate-download-permissions-for-woocommerce-orders' => array(
				'title'       => __( 'Bulk Regenerate Download Permissions for WooCommerce', 'checkout-files-upload-woocommerce' ),
				'description' => __( 'Bulk regenerate download permissions for WooCommerce orders.', 'checkout-files-upload-woocommerce' ),
				'url'         => 'https://wpwham.com/products/bulk-regenerate-download-permissions-for-woocommerce-orders/',
				'image'       => 'https://wpwham.com/wp-content/uploads/edd/2019/09/Bulk-Regenerate-Download-Permissions-for-WooCommerce-Orders-150x150.png',
			),
			'crowdfunding-for-woocommerce' => array(
				'title'       => __( 'Crowdfunding for WooCommerce', 'checkout-files-upload-woocommerce' ),
				'description' => __( 'Create a fully functional crowdfunding site with WooCommerce.', 'checkout-files-upload-woocommerce' ),
				'url'         => 'https://wpwham.com/products/crowdfunding-for-woocommerce/',
				'image'       => 'https://wpwham.com/wp-content/uploads/edd/2019/09/Crowdfunding-for-WooCommerce-150x150.jpg',
			),
			'all-currencies-for-woocommerce' => array(
				'title'       => __( 'All Currencies for WooCommerce', 'checkout-files-upload-woocommerce' ),
				'description' => __( 'Add all world currencies and cryptocurrencies to WooCommerce, change symbol for any currency, or add custom currencies.', 'checkout-files-upload-woocommerce' ),
				'url'         => 'https://wpwham.com/products/all-currencies-for-woocommerce/',
				'image'       => 'https://wpwham.com/wp-content/uploads/edd/2019/05/All-Currencies-for-WooCommerce-150x150.jpg',
			),
			'product-visibility-by-country-for-woocommerce' => array(
				'title'       => __( 'Product Visibility by Country for WooCommerce', 'checkout-files-upload-woocommerce' ),
				'description' => __( 'Display WooCommerce products by customer country.', 'checkout-files-upload-woocommerce' ),
				'url'         => 'https://wpwham.com/products/product-visibility-by-country-for-woocommerce/',
				'image'       => 'https://wpwham.com/wp-content/uploads/edd/2020/02/Product-Visibility-by-Country-for-WooCommerce-v2-150x150.png',
			),
		);
		
		add_filter( 'woocommerce_get_sections_advanced', array( $this, 'settings_section' ) );
		add_filter( 'woocommerce_get_settings_advanced', array( $this, 'settings_get_settings' ), 10, 2 );
		add_action( 'admin_init', array( $this, 'init' ) );
		
	}
	
	public function init() {
		
		if ( ! self::show_suggestions() ) {
			return;
		}
		
		add_action( 'woocommerce_product_data_tabs', array( $this, 'product_data_tabs' ), PHP_INT_MAX );
		add_action( 'woocommerce_product_data_panels', array( $this, 'product_data_panels' ), PHP_INT_MAX );
		
	}
	
	/**
	 * Product data tabs filter
	 *
	 * Adds a new Extensions tab to the product data meta box.
	 *
	 * @param array $tabs Existing tabs.
	 *
	 * @return array
	 */
	public static function product_data_tabs( $tabs ) {
		$tabs['marketplace-suggestions'] = array(
			'label'    => _x( 'Get more options', 'Marketplace suggestions', 'checkout-files-upload-woocommerce' ),
			'target'   => 'wpw_marketplace_suggestions',
			'class'    => array(),
			'priority' => 1000,
		);

		return $tabs;
	}

	/**
	 * Render additional panels in the product data metabox.
	 */
	public static function product_data_panels() {
		#region product_data_panels
		
		// this should not be necessary... but something unknown out there in the wild is changing
		// our non-static hook into a static one, for some people.  I haven't been able to reproduce
		// it myself.  Probably it's some 3rd party plugin, but I haven't figured out which one.  So
		// for now let's do this to be safe:
		$WPWE = Wpwham_Extensions::get_instance();
		
		?>
		<style type="text/css">
			@charset "UTF-8";
			a.suggestion-dismiss {
				border: none;
				box-shadow: none;
				color: #ddd
			}

			a.suggestion-dismiss:hover {
				color: #aaa
			}

			a.suggestion-dismiss::before {
				font-family: Dashicons;
				speak: none;
				font-weight: 400;
				font-variant: normal;
				text-transform: none;
				line-height: 1;
				-webkit-font-smoothing: antialiased;
				content: "";
				text-decoration: none;
				font-size: 1.5em
			}

			#woocommerce-product-data ul.wc-tabs li.wpw-marketplace-suggestions_tab a::before {
				font-family: Dashicons;
				speak: none;
				font-weight: 400;
				font-variant: normal;
				text-transform: none;
				line-height: 1;
				-webkit-font-smoothing: antialiased;
				content: "";
				text-decoration: none
			}

			@media only screen and (max-width: 900px) {
				#woocommerce-product-data ul.wc-tabs li.wpw-marketplace-suggestions_tab a::before {
					line-height:40px
				}
			}

			#woocommerce-product-data ul.wc-tabs li.wpw-marketplace-suggestions_tab a span {
				margin: 0 .618em
			}

			.wpw-marketplace-suggestions-metabox-nosuggestions-placeholder {
				max-width: 325px;
				margin: 2em auto;
				text-align: center
			}

			.wpw-marketplace-suggestions-metabox-nosuggestions-placeholder .wpw-marketplace-suggestion-placeholder-content {
				margin-bottom: 1em
			}

			.wpw-marketplace-suggestions-metabox-nosuggestions-placeholder a,.wpw-marketplace-suggestions-metabox-nosuggestions-placeholder h4,.wpw-marketplace-suggestions-metabox-nosuggestions-placeholder p {
				margin: auto;
				text-align: center;
				display: block;
				margin-top: .75em;
				line-height: 1.75
			}

			.wpw-marketplace-suggestions-container.showing-suggestion {
				text-align: left
			}

			.wpw-marketplace-suggestions-container.showing-suggestion .wpw-marketplace-suggestion-container {
				-webkit-box-align: start;
				align-items: flex-start;
				display: -webkit-box;
				display: flex;
				-webkit-box-orient: vertical;
				-webkit-box-direction: normal;
				flex-direction: column;
				position: relative
			}

			.wpw-marketplace-suggestions-container.showing-suggestion .wpw-marketplace-suggestion-container img.wpw-marketplace-suggestion-icon {
				height: 40px;
				margin: 0;
				margin-right: 1.5em;
				-webkit-box-flex: 0;
				flex: 0 0 40px
			}

			.wpw-marketplace-suggestions-container.showing-suggestion .wpw-marketplace-suggestion-container .wpw-marketplace-suggestion-container-content {
				-webkit-box-flex: 1;
				flex: 1 1 60%
			}

			.wpw-marketplace-suggestions-container.showing-suggestion .wpw-marketplace-suggestion-container .wpw-marketplace-suggestion-container-content h4 {
				margin: 0
			}

			.wpw-marketplace-suggestions-container.showing-suggestion .wpw-marketplace-suggestion-container .wpw-marketplace-suggestion-container-content p {
				margin: 0;
				margin-top: 4px;
				color: #444
			}

			.wpw-marketplace-suggestions-container.showing-suggestion .wpw-marketplace-suggestion-container .wpw-marketplace-suggestion-container-cta {
				-webkit-box-flex: 1;
				flex: 1 1 30%;
				min-width: 160px;
				text-align: right
			}

			.wpw-marketplace-suggestions-container.showing-suggestion .wpw-marketplace-suggestion-container .wpw-marketplace-suggestion-container-cta .suggestion-dismiss {
				text-decoration: none;
				position: absolute;
				top: 1em;
				right: 1em
			}

			@media screen and (min-width: 600px) {
				.wpw-marketplace-suggestions-container.showing-suggestion .wpw-marketplace-suggestion-container {
					-webkit-box-align:center;
					align-items: center;
					-webkit-box-orient: horizontal;
					-webkit-box-direction: normal;
					flex-direction: row
				}
			}

			.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=orders-list-empty-footer] .wpw-marketplace-suggestion-container .wpw-marketplace-suggestion-container-content h4,.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=orders-list-empty-header] .wpw-marketplace-suggestion-container .wpw-marketplace-suggestion-container-content h4,.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=product-edit-meta-tab-footer] .wpw-marketplace-suggestion-container .wpw-marketplace-suggestion-container-content h4,.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=product-edit-meta-tab-header] .wpw-marketplace-suggestion-container .wpw-marketplace-suggestion-container-content h4,.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=products-list-empty-footer] .wpw-marketplace-suggestion-container .wpw-marketplace-suggestion-container-content h4,.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=products-list-empty-header] .wpw-marketplace-suggestion-container .wpw-marketplace-suggestion-container-content h4 {
				font-size: 1.1em;
				margin: 0;
				margin-bottom: 0
			}

			.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=orders-list-empty-footer],.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=products-list-empty-footer] {
				margin-bottom: 6em
			}

			.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=orders-list-empty-footer] .wpw-marketplace-suggestion-container,.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=product-edit-meta-tab-footer] .wpw-marketplace-suggestion-container,.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=products-list-empty-footer] .wpw-marketplace-suggestion-container {
				-webkit-box-orient: horizontal;
				-webkit-box-direction: reverse;
				flex-direction: row-reverse
			}

			.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=orders-list-empty-footer] .wpw-marketplace-suggestion-container .wpw-marketplace-suggestion-container-cta,.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=product-edit-meta-tab-footer] .wpw-marketplace-suggestion-container .wpw-marketplace-suggestion-container-cta,.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=products-list-empty-footer] .wpw-marketplace-suggestion-container .wpw-marketplace-suggestion-container-cta {
				text-align: left
			}

			.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=orders-list-empty-footer] .wpw-marketplace-suggestion-container .wpw-marketplace-suggestion-container-content.has-manage-link,.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=product-edit-meta-tab-footer] .wpw-marketplace-suggestion-container .wpw-marketplace-suggestion-container-content.has-manage-link,.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=products-list-empty-footer] .wpw-marketplace-suggestion-container .wpw-marketplace-suggestion-container-content.has-manage-link {
				text-align: right
			}

			.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=product-edit-meta-tab-body] .wpw-marketplace-suggestion-container,.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=product-edit-meta-tab-footer] .wpw-marketplace-suggestion-container,.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=product-edit-meta-tab-header] .wpw-marketplace-suggestion-container {
				padding: 1em 1.5em
			}

			.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=product-edit-meta-tab-body] .wpw-marketplace-suggestion-container .wpw-marketplace-suggestion-container-content p,.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=product-edit-meta-tab-footer] .wpw-marketplace-suggestion-container .wpw-marketplace-suggestion-container-content p,.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=product-edit-meta-tab-header] .wpw-marketplace-suggestion-container .wpw-marketplace-suggestion-container-content p {
				padding: 0;
				line-height: 1.5
			}

			.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=orders-list-empty-footer] .wpw-marketplace-suggestion-container,.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=orders-list-empty-header] .wpw-marketplace-suggestion-container,.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=products-list-empty-footer] .wpw-marketplace-suggestion-container,.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=products-list-empty-header] .wpw-marketplace-suggestion-container {
				padding: 1.5em
			}

			.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=orders-list-empty-body] .wpw-marketplace-suggestion-container,.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=products-list-empty-body] .wpw-marketplace-suggestion-container {
				padding: .75em 1.5em
			}

			.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=orders-list-empty-body] .wpw-marketplace-suggestion-container:first-child,.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=products-list-empty-body] .wpw-marketplace-suggestion-container:first-child {
				padding-top: 1.5em
			}

			.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=orders-list-empty-body] .wpw-marketplace-suggestion-container:last-child,.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=products-list-empty-body] .wpw-marketplace-suggestion-container:last-child {
				padding-bottom: 1.5em
			}

			.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=orders-list-empty-body] .wpw-marketplace-suggestion-container .wpw-marketplace-suggestion-container-content p:last-child,.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=products-list-empty-body] .wpw-marketplace-suggestion-container .wpw-marketplace-suggestion-container-content p:last-child {
				margin-bottom: 0
			}

			.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=orders-list-empty-body],.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=orders-list-empty-footer],.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=orders-list-empty-header],.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=product-edit-meta-tab-body],.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=product-edit-meta-tab-footer],.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=product-edit-meta-tab-header],.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=products-list-empty-body],.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=products-list-empty-footer],.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=products-list-empty-header] {
				display: none
			}

			.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=orders-list-empty-body] .wpw-marketplace-suggestion-container .wpw-marketplace-suggestion-container-cta a.button,.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=orders-list-empty-footer] .wpw-marketplace-suggestion-container .wpw-marketplace-suggestion-container-cta a.button,.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=orders-list-empty-header] .wpw-marketplace-suggestion-container .wpw-marketplace-suggestion-container-cta a.button,.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=product-edit-meta-tab-body] .wpw-marketplace-suggestion-container .wpw-marketplace-suggestion-container-cta a.button,.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=product-edit-meta-tab-footer] .wpw-marketplace-suggestion-container .wpw-marketplace-suggestion-container-cta a.button,.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=product-edit-meta-tab-header] .wpw-marketplace-suggestion-container .wpw-marketplace-suggestion-container-cta a.button,.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=products-list-empty-body] .wpw-marketplace-suggestion-container .wpw-marketplace-suggestion-container-cta a.button,.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=products-list-empty-footer] .wpw-marketplace-suggestion-container .wpw-marketplace-suggestion-container-cta a.button,.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=products-list-empty-header] .wpw-marketplace-suggestion-container .wpw-marketplace-suggestion-container-cta a.button {
				display: inline-block;
				min-width: 120px;
				text-align: center;
				margin: 0
			}

			.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=orders-list-empty-body] .wpw-marketplace-suggestion-container .wpw-marketplace-suggestion-container-cta a.linkout,.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=orders-list-empty-footer] .wpw-marketplace-suggestion-container .wpw-marketplace-suggestion-container-cta a.linkout,.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=orders-list-empty-header] .wpw-marketplace-suggestion-container .wpw-marketplace-suggestion-container-cta a.linkout,.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=product-edit-meta-tab-body] .wpw-marketplace-suggestion-container .wpw-marketplace-suggestion-container-cta a.linkout,.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=product-edit-meta-tab-footer] .wpw-marketplace-suggestion-container .wpw-marketplace-suggestion-container-cta a.linkout,.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=product-edit-meta-tab-header] .wpw-marketplace-suggestion-container .wpw-marketplace-suggestion-container-cta a.linkout,.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=products-list-empty-body] .wpw-marketplace-suggestion-container .wpw-marketplace-suggestion-container-cta a.linkout,.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=products-list-empty-footer] .wpw-marketplace-suggestion-container .wpw-marketplace-suggestion-container-cta a.linkout,.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=products-list-empty-header] .wpw-marketplace-suggestion-container .wpw-marketplace-suggestion-container-cta a.linkout {
				font-size: 1.1em;
				text-decoration: none
			}

			.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=orders-list-empty-body] .wpw-marketplace-suggestion-container .wpw-marketplace-suggestion-container-cta a.linkout .dashicons,.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=orders-list-empty-footer] .wpw-marketplace-suggestion-container .wpw-marketplace-suggestion-container-cta a.linkout .dashicons,.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=orders-list-empty-header] .wpw-marketplace-suggestion-container .wpw-marketplace-suggestion-container-cta a.linkout .dashicons,.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=product-edit-meta-tab-body] .wpw-marketplace-suggestion-container .wpw-marketplace-suggestion-container-cta a.linkout .dashicons,.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=product-edit-meta-tab-footer] .wpw-marketplace-suggestion-container .wpw-marketplace-suggestion-container-cta a.linkout .dashicons,.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=product-edit-meta-tab-header] .wpw-marketplace-suggestion-container .wpw-marketplace-suggestion-container-cta a.linkout .dashicons,.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=products-list-empty-body] .wpw-marketplace-suggestion-container .wpw-marketplace-suggestion-container-cta a.linkout .dashicons,.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=products-list-empty-footer] .wpw-marketplace-suggestion-container .wpw-marketplace-suggestion-container-cta a.linkout .dashicons,.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=products-list-empty-header] .wpw-marketplace-suggestion-container .wpw-marketplace-suggestion-container-cta a.linkout .dashicons {
				margin-left: 4px;
				bottom: 2px;
				position: relative
			}

			.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=orders-list-empty-body] .wpw-marketplace-suggestion-container .wpw-marketplace-suggestion-container-cta .suggestion-dismiss,.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=orders-list-empty-footer] .wpw-marketplace-suggestion-container .wpw-marketplace-suggestion-container-cta .suggestion-dismiss,.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=orders-list-empty-header] .wpw-marketplace-suggestion-container .wpw-marketplace-suggestion-container-cta .suggestion-dismiss,.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=product-edit-meta-tab-body] .wpw-marketplace-suggestion-container .wpw-marketplace-suggestion-container-cta .suggestion-dismiss,.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=product-edit-meta-tab-footer] .wpw-marketplace-suggestion-container .wpw-marketplace-suggestion-container-cta .suggestion-dismiss,.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=product-edit-meta-tab-header] .wpw-marketplace-suggestion-container .wpw-marketplace-suggestion-container-cta .suggestion-dismiss,.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=products-list-empty-body] .wpw-marketplace-suggestion-container .wpw-marketplace-suggestion-container-cta .suggestion-dismiss,.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=products-list-empty-footer] .wpw-marketplace-suggestion-container .wpw-marketplace-suggestion-container-cta .suggestion-dismiss,.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=products-list-empty-header] .wpw-marketplace-suggestion-container .wpw-marketplace-suggestion-container-cta .suggestion-dismiss {
				position: relative;
				top: 5px;
				right: auto;
				margin-left: 1em
			}

			@media screen and (min-width: 600px) {
				.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=orders-list-empty-body],.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=orders-list-empty-footer],.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=orders-list-empty-header],.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=product-edit-meta-tab-body],.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=product-edit-meta-tab-footer],.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=product-edit-meta-tab-header],.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=products-list-empty-body],.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=products-list-empty-footer],.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=products-list-empty-header] {
					display:block
				}
			}

			.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=product-edit-meta-tab-footer],.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=product-edit-meta-tab-header] {
				border: none
			}

			.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=product-edit-meta-tab-body] {
				border: none;
				border-top: 1px solid #eee;
				border-bottom: 1px solid #eee
			}

			.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=orders-list-empty-body],.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=orders-list-empty-footer],.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=orders-list-empty-header],.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=products-list-empty-body],.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=products-list-empty-footer],.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=products-list-empty-header] {
				border: 1px solid #ddd;
				border-bottom: none
			}

			.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=orders-list-empty-body]:last-child,.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=orders-list-empty-footer]:last-child,.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=orders-list-empty-header]:last-child,.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=products-list-empty-body]:last-child,.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=products-list-empty-footer]:last-child,.wpw-marketplace-suggestions-container.showing-suggestion[data-wpw-marketplace-suggestions-context=products-list-empty-header]:last-child {
				border-bottom: 1px solid #ddd
			}
		</style>
		<div id="wpw_marketplace_suggestions" class="panel woocommerce_options_panel hidden">
			<div class="wpw-marketplace-suggestions-container showing-suggestion" data-wpw-marketplace-suggestions-context="product-edit-meta-tab-header">
				<div class="wpw-marketplace-suggestion-container" data-suggestion-slug="product-edit-meta-tab-header">
					<div class="wpw-marketplace-suggestion-container-content">
						<h4><?php _e( 'Recommended extensions', 'checkout-files-upload-woocommerce' ); ?></h4>
					</div>
					<div class="wpw-marketplace-suggestion-container-cta"></div>
				</div>
			</div>
			<?php if ( count( $WPWE->extensions ) > 0 ): ?>
				<div class="wpw-marketplace-suggestions-container showing-suggestion" data-wpw-marketplace-suggestions-context="product-edit-meta-tab-body">
					<?php $count = 0; ?>
					<?php foreach ( $WPWE->extensions as $extension ): ?>
						<?php $count++; if ( $count > 4 ) { break; } ?>
						<div class="wpw-marketplace-suggestion-container" data-suggestion-slug="product-edit-name-your-price">
							<img src="<?php echo $extension['image']; ?>" class="wpw-marketplace-suggestion-icon">
							<div class="wpw-marketplace-suggestion-container-content">
								<h4><?php echo $extension['title']; ?></h4>
								<p><?php echo $extension['description']; ?></p>
							</div>
							<div class="wpw-marketplace-suggestion-container-cta">
								<a href="<?php echo $extension['url']; ?>?utm_source=editproduct&utm_campaign=marketplacesuggestions&utm_medium=product" target="blank" class="button"><?php _e( 'Learn More', 'checkout-files-upload-woocommerce' ); ?></a>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php else: ?>
				<div class="wpw-marketplace-suggestions-metabox-nosuggestions-placeholder">
					<img src="https://woocommerce.com/wp-content/plugins/wccom-plugins/marketplace-suggestions/icons/get_more_options.svg" class="wpw-marketplace-suggestion-icon">
					<div class="wpw-marketplace-suggestion-placeholder-content">
						<h4><?php _e( 'Enhance your products', 'checkout-files-upload-woocommerce' ); ?></h4>
						<p><?php _e( 'Extensions can add new functionality to your product pages that make your store stand out', 'checkout-files-upload-woocommerce' ); ?></p>
					</div>
					<a href="https://wpwham.com/products/?utm_source=editproduct&utm_campaign=marketplacesuggestions&utm_medium=product" target="blank" class="button"><?php _e( 'Browse the Marketplace', 'checkout-files-upload-woocommerce' ); ?></a><br>
					<a class="wpw-marketplace-suggestion-manage-link" href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings&tab=advanced&section=wpwham_com' ) ); ?>"><?php _e( 'Manage suggestions', 'checkout-files-upload-woocommerce' ); ?></a>
				</div>
			<?php endif; ?>
			<div class="wpw-marketplace-suggestions-container showing-suggestion" data-wpw-marketplace-suggestions-context="product-edit-meta-tab-footer">
				<div class="wpw-marketplace-suggestion-container" data-suggestion-slug="product-edit-meta-tab-footer-browse-all">
					<div class="wpw-marketplace-suggestion-container-content has-manage-link">
						<a class="wpw-marketplace-suggestion-manage-link linkout" href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings&tab=advanced&section=wpwham_com' ) ); ?>"><?php _e( 'Manage suggestions', 'checkout-files-upload-woocommerce' ); ?></a>
					</div>
					<div class="wpw-marketplace-suggestion-container-cta">
						<a href="https://wpwham.com/products/?utm_source=editproduct&utm_campaign=marketplacesuggestions&utm_medium=product" target="blank" class="linkout"><?php _e( 'Browse the Marketplace', 'checkout-files-upload-woocommerce' ); ?><span class="dashicons dashicons-external"></span></a>
					</div>
				</div>
			</div>
		</div>
		<?php
		#endregion product_data_panels
	}
	
	public function register( $extension ) {
		unset( $this->extensions[ $extension ] );
	}
	
	public static function settings_get_settings( $settings, $current_section ) {
		if ( $current_section === 'wpwham_com' ) {
			$settings = array(
				array(
					'title' => esc_html__( 'Marketplace suggestions', 'checkout-files-upload-woocommerce' ),
					'type'  => 'title',
					'id'    => 'marketplace_suggestions',
					'desc'  => __( 'We show contextual suggestions for official extensions that may be helpful to your store.', 'checkout-files-upload-woocommerce' ),
				),
				array(
					'title'         => __( 'Show Suggestions', 'checkout-files-upload-woocommerce' ),
					'desc'          => __( 'Display suggestions within WooCommerce', 'checkout-files-upload-woocommerce' ),
					'desc_tip'      => esc_html__( 'Leave this box unchecked if you do not want to see suggested extensions.', 'checkout-files-upload-woocommerce' ),
					'id'            => 'wpw_show_marketplace_suggestions',
					'type'          => 'checkbox',
					'checkboxgroup' => 'start',
					'default'       => 'yes',
					'autoload'      => false,
				),
				array(
					'type' => 'sectionend',
					'id'   => 'marketplace_suggestions',
				),
			);
		}
		return $settings;
	}
	
	public static function settings_section( $sections ) {
		$sections['wpwham_com'] = __( 'WPWham.com', 'checkout-files-upload-woocommerce' );
		return $sections;
	}
	
	/**
	 * Should suggestions be displayed?
	 *
	 * @return bool
	 */
	public static function show_suggestions() {
		
		// Suggestions are only displayed if user can install plugins.
		if ( ! current_user_can( 'install_plugins' ) ) {
			return false;
		}

		// Suggestions may be disabled via a setting under Accounts & Privacy.
		if ( 
			get_option( 'woocommerce_show_marketplace_suggestions', 'yes' ) === 'no' ||
			get_option( 'wpw_show_marketplace_suggestions', 'yes' ) === 'no'
		) {
			return false;
		}

		// User can disabled all suggestions via filter.
		return apply_filters( 'wpw_allow_marketplace_suggestions', true );
	}
	
}
