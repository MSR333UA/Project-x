<?php get_header(); ?>

	<div class="section-content">
		<div class="container">
			<!-- Error 404 -->
			<div class="error-404 has-bg clearfix mb30<?php vu_animation(true); ?>">
				<div class="col-md-12">
					<div class="error-code">
						<p><?php echo __('ERROR 404', 'bakery'); ?></p>
					</div>
					
					<div class="error-message text-center">
						<p><?php echo __('The page you requested could not be found. Try refining your search, or use the navigation above to locate the post.', 'bakery'); ?></p>

						<a href="<?php echo get_home_url(); ?>" class="transition-all"><?php echo __('Return to home', 'bakery'); ?></a>
					</div>
				</div>
			</div>
			<!-- /Error 404 -->
		</div>
	</div>

<?php get_footer(); ?>