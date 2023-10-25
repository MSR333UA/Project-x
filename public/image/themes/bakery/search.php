<?php 
	get_header();

	$blog_layout = vu_get_option('blog-layout');
?>
	<div class="m-t-30">
		<div class="container m-b-50">
			<div class="row">
				<div class="col-xs-12 col-sm-12 col-md-<?php echo ($blog_layout == 'no-sidebar') ? '12' : '9'; echo ($blog_layout == 'left-sidebar') ? ' col-md-push-3' : ''; ?>">
					<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
						<div class="row">
							<div class="col-xs-12<?php vu_animation(true); ?>">
								<?php get_template_part( 'includes/post-templates/entry', get_post_format() ); ?>
							</div>
						</div>
					<?php endwhile; else : 
							echo '<p>' . __('No results found.', 'bakery') . '</p>';
						endif; ?>

					<?php if( get_next_posts_link() or get_previous_posts_link() ) : ?>
						<div class="text-center">
							<div class="pagination pull-left">
								<?php echo get_previous_posts_link('<span class="pagination-item pagination-nav">'. __('prev', 'bakery') .'</span>'); ?>
							</div>
							<div class="pagination pull-right">
								<?php echo get_next_posts_link('<span class="pagination-item pagination-nav">'. __('next', 'bakery') .'</span>'); ?>
							</div>
						</div>
					<?php endif; ?>
				</div>

				<?php get_sidebar(); ?>
			</div>
		</div>
	</div>

<?php get_footer(); ?>