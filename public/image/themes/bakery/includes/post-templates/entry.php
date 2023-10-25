<article id="post-<?php the_ID(); ?>" <?php post_class('blog-post'); ?>>
	<div class="blog-post-header">
		<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
		<div class="blog-post-info">
			<?php if( vu_get_option('show-author') ) : ?>
				<?php echo __('by:', 'bakery'); ?> <?php the_author_posts_link(); ?> <span class="delimiter-inline">|</span>  
			<?php endif; ?>
			<?php echo __('on', 'bakery'); ?> <span><?php echo get_the_date(); ?></span> <span class="delimiter-inline">|</span> <a href="<?php comments_link(); ?>"><?php comments_number( __('No Comments', 'bakery'), __('One Comment ', 'bakery'), __('% Comments', 'bakery') ); ?></a>
		</div>
	</div>

	<?php if( has_post_thumbnail() ) : ?>
		<div class="blog-post-preview">
			<div class="blog-post-image">
				<a href="<?php echo vu_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'full'); ?>" title="<?php the_title(); ?>" class="vu_lightbox">
					<?php the_post_thumbnail('ratio-16:9'); ?>
					<span class="blog-post-image-cover"></span>
				</a>
			</div>
		</div>
	<?php endif; ?>
	
	<div class="blog-post-content">
	<?php 
		if( is_single() ){
			the_content();
		} else {
			the_excerpt(); 
		}
	?>
	</div>
	
	<div class="blog-post-footer">
		<?php vu_blog_socials( get_permalink(), get_the_title(), $post->ID ); ?>
		
		<?php 
			if( is_single() ) {
				if( vu_get_option('show-tags') ) :
					if( is_single() && has_tag() ) :
						echo '<div class="blog-post-tags">'; 
						the_tags( __('Tags: ', 'bakery'), ', ' ,'');
						echo '</div>';
					endif;
				endif;
			} else {
		?>
			<a class="read-more-link" href="<?php the_permalink(); ?>"><?php echo __('Read more', 'bakery'); ?></a>
		<?php } ?>
	</div>
<?php if( !is_single() ) : ?>
</article><!--/article-->
<?php endif; ?>