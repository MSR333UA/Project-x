<?php 
	// Get All Products Categories with levels for VC
	if( !function_exists('vu_products_categories') ) {
		function vu_products_categories(){
			ob_start();

			wp_dropdown_categories( 'taxonomy=product_cat&hierarchical=1' );

			$output = ob_get_contents();
			ob_end_clean();

			$output = preg_replace('/(&nbsp;&nbsp;&nbsp;)/', '– ', $output);

			preg_match_all('#<option.*?value="(\d+)">(.*?)</option>#', $output, $matches);

			if( !empty($matches[1]) and !empty($matches[2]) ) {
				return array("None" => "0") + array_combine((array)$matches[2], (array)$matches[1]);
			} else {
				return array("None" => "0");
			}
		}
	}

	if( !function_exists('vu_wc_dynamic_css') ) {
		function vu_wc_dynamic_css($primary_color_hex, $primary_color_rgb, $secondary_color_hex, $secondary_color_rgb) {
	?>
		.woocommerce.widget_product_categories a:hover + .count {
		  color: <?php echo $primary_color_hex; ?>;
		}
		.woocommerce ul.cart_list li .star-rating,
		.woocommerce ul.product_list_widget li .star-rating {
		  color: <?php echo $primary_color_hex; ?>;
		}
		.woocommerce.widget_product_tag_cloud a.active,
		.woocommerce.widget_product_tag_cloud a:hover {
		  background-color: <?php echo $primary_color_hex; ?>;
		}
		.woocommerce .widget_price_filter .ui-slider .ui-slider-range {
		  background-color: <?php echo $primary_color_hex; ?>;
		}
		.woocommerce .widget_price_filter .price_slider_amount .button {
		  background-color: <?php echo $primary_color_hex; ?>;
		}
		.vu_wc-menu-item .vu_wc-cart-link:hover {
		  color: <?php echo $secondary_color_hex; ?>;
		}
		.vu_wc-menu-item .vu_wc-count {
		  background-color: <?php echo $primary_color_hex; ?>;
		}
		.vu_wc-menu-item .vu_wc-cart-notification {
		  background-color: <?php echo $primary_color_hex; ?>;
		}
		.vu_wc-menu-item .vu_wc-cart-notification:before {
		  border-bottom-color: <?php echo $primary_color_hex; ?>;
		}
		.vu_wc-menu-item .vu_wc-cart-notification .vu_wc-item-name {
		  color: <?php echo $secondary_color_hex; ?>;
		}
		.vu_wc-menu-item .vu_wc-cart .widget_shopping_cart_content {
		  border-bottom-color: <?php echo $primary_color_hex; ?>;
		}
		.woocommerce.widget_shopping_cart .widget_shopping_cart_content .cart_list li a:hover {
		  color: <?php echo $primary_color_hex; ?>;
		}
		.woocommerce .widget_price_filter .ui-slider .ui-slider-handle {
		  background-color: <?php echo $primary_color_hex; ?>;
		}
		.woocommerce .quantity .vu_qty-button:hover {
		  background-color: <?php echo $primary_color_hex; ?>;
		}
		.woocommerce input.button,
		.woocommerce button.button,
		.woocommerce a.button {
		  background-color: <?php echo $primary_color_hex; ?> !important;
		}
		.woocommerce input.alt,
		.woocommerce button.alt,
		.woocommerce a.alt {
		  color: <?php echo $secondary_color_hex; ?> !important;
		  background-color: transparent !important;
		  border-color: <?php echo $secondary_color_hex; ?>;
		}
		.woocommerce input.button:hover,
		.woocommerce button.button:hover,
		.woocommerce a.button:hover {
		  background-color: <?php echo $secondary_color_hex; ?> !important;
		}
		.vu_wc-shipping-calculator .shipping-calculator-button {
		  color: <?php echo $secondary_color_hex; ?>;
		}
		.vu_wc-shipping-calculator .shipping-calculator-button:hover {
		  color: <?php echo $primary_color_hex; ?>;
		}
		.vu_product-single .product_title {
		  color: <?php echo $secondary_color_hex; ?>;
		}
		.vu_product-single .vu_p-rating .star-rating {
		  color: <?php echo $primary_color_hex; ?>;
		}
		.vu_product-single .vu_p-rating .woocommerce-review-link:hover {
		  color: <?php echo $secondary_color_hex; ?>;
		}
		.vu_product-single .vu_p-price .amount,
		.vu_product-single .single_variation .amount {
		  color: <?php echo $primary_color_hex; ?>;
		}
		.vu_product-single .vu_p-price del,
		.vu_product-single .single_variation del {
		  color: <?php echo $secondary_color_hex; ?>;
		}
		.vu_product-single .vu_p-price del .amount,
		.vu_product-single .single_variation del .amount {
		  color: <?php echo $secondary_color_hex; ?>;
		}
		.vu_product-single .vu_p-price ins,
		.vu_product-single .single_variation ins {
		  color: <?php echo $primary_color_hex; ?>;
		}
		.vu_product-single .vu_p-image .vu_p-image-hover {
		  color: <?php echo $secondary_color_hex; ?>;
		}
		.vu_product-single .woocommerce-tabs ul.tabs li.active a,
		.vu_product-single .woocommerce-tabs ul.tabs li a:hover {
		  border-color: <?php echo $primary_color_hex; ?>;
		  background-color: <?php echo $primary_color_hex; ?>;
		}
		.vu_product-single #reviews #comments ol.commentlist li .star-rating {
		  color: <?php echo $primary_color_hex; ?>;
		}
		.vu_product-single #review_form #respond p.stars .active {
		  color: <?php echo $primary_color_hex; ?>;
		}
		.woocommerce form .form-row .required {
		  color: <?php echo $primary_color_hex; ?>;
		}
		.vu_wc-title:after {
		  background-color: <?php echo $primary_color_hex; ?>;
		}
		/* WooCommerce: Sorting Dropdown */
		.vu_dropdown {
		  border-color: <?php echo $secondary_color_hex; ?>;
		  color: <?php echo $secondary_color_hex; ?>;
		}
		.vu_dropdown.active {
		  background-color: <?php echo $secondary_color_hex; ?>;
		}
		.vu_dropdown:after {
		  border-color: <?php echo $secondary_color_hex; ?> transparent;
		}
		.vu_dropdown .vu_dd-options {
		  border-color: <?php echo $secondary_color_hex; ?>;
		}
		.vu_wc-product-category-item {
		  border-color: <?php echo $secondary_color_hex; ?>;
		}
		.vu_wc-product-category-item:hover {
		  border-color: <?php echo $primary_color_hex; ?>;
		}
		.vu_wc-product-category-item h3 {
		  color: <?php echo $secondary_color_hex; ?>;
		}
		.vu_wc-product-category-item h3 .count {
		  background-color: <?php echo $primary_color_hex; ?>;
		}
	<?php 
		}
	}
?>