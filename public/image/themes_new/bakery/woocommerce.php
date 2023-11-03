<?php
	/*
		Template Name: WooCommerce
	*/

	get_header();

	$shop_layout = vu_get_option('shop-layout');
?>
	<div class="container vu_page-content vu_woocommerce m-t-80 m-b-70">
		<div class="row">
			<div class="col-xs-12 col-sm-12 col-md-<?php echo ($shop_layout == 'no-sidebar') ? '12' : '9'; echo ($shop_layout == 'left-sidebar') ? ' col-md-push-3' : ''; ?>">
				<?php if( function_exists('woocommerce_content') ) { woocommerce_content(); } ?>
			</div>

			<?php if( $shop_layout == 'left-sidebar' or $shop_layout == 'right-sidebar' ) : ?>
				<aside class="sidebar col-xs-12 col-md-3<?php echo ($shop_layout == 'left-sidebar') ? ' col-md-pull-9' : ''; ?>">
					<?php dynamic_sidebar('shop_sidebar'); ?>
				</aside>
			<?php endif; ?>
		</div>
	</div>
<?php get_footer(); ?>