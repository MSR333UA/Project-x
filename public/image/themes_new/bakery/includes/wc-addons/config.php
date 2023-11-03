<?php 
	if( !function_exists('vu_wc_init') ){
		function vu_wc_init(){
			require_once('shortcodes/products.php');
			require_once('shortcodes/products-with-filter.php');
			require_once('shortcodes/featured-products.php');
		}
	}

	add_action( 'init', 'vu_wc_init' );
?>