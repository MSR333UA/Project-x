<?php 
	$blog_layout = vu_get_option('blog-layout');

	if( $blog_layout == 'left-sidebar' or $blog_layout == 'right-sidebar' ) : ?>
		<aside class="sidebar blog-sidebar col-xs-12 col-sm-6 col-md-3<?php echo ($blog_layout == 'left-sidebar') ? ' col-md-pull-9' : ''; ?>">
			<?php dynamic_sidebar('blog-sidebar'); ?>
		</aside><!-- /blog sidebar -->
	<?php endif;
?>