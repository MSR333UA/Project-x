<?php 
	get_header();

	$blog_layout = vu_get_option('blog-layout');
?>
	<div class="blog-post-single m-t-30">
		<div class="container m-b-50">
			<div class="row">
				<div class="col-xs-12 col-sm-12 col-md-<?php echo ($blog_layout == 'no-sidebar') ? '12' : '9'; echo ($blog_layout == 'left-sidebar') ? ' col-md-push-3' : ''; ?>">
						<?php 
							if ( have_posts() ) : while ( have_posts() ) : the_post();
								get_template_part( 'includes/post-templates/entry', get_post_format() );
							endwhile; endif;
						?>

						<?php wp_link_pages(); ?>

						<div class="comment-section clearfix">
							<?php comments_template(); ?>
						</div>
						
						<?php 
							$post_current_url = get_permalink();
							$post_prev_url = get_permalink(get_adjacent_post(false,'',false));
							$post_next_url = get_permalink(get_adjacent_post(false,'',true));
						?>
					</article>

					<div class="clearfix text-right">
						<?php if( !empty($post_prev_url) and ($post_prev_url != $post_current_url) ) { ?>
							<a href="<?php echo esc_url($post_prev_url); ?>" class="pagination-nav-single"><?php echo __('prev', 'bakery'); ?></a>
						<?php } if( !empty($post_next_url) and ($post_next_url != $post_current_url) ) { ?>
							<a href="<?php echo esc_url($post_next_url); ?>" class="pagination-nav-single"><?php echo __('next', 'bakery'); ?></a>
						<?php } ?>
					</div>
				</div>
			
			<?php get_sidebar(); ?>
			
			</div>
		</div>
	</div>

<?php get_footer(); ?>