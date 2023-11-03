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

	<?php $vu_post_format_settings = vu_get_post_meta( $post->ID, 'vu_post_format_settings' ); ?>
	
	<?php if( !empty($vu_post_format_settings['audio']['mp3-file-url']) or !empty($vu_post_format_settings['audio']['oga-file-url']) ) { ?>
		<div class="blog-post-preview">
			<audio controls style="width: 100%;">
				<?php if( !empty($vu_post_format_settings['audio']['oga-file-url']) ) : ?>
					<source src="<?php echo esc_url($vu_post_format_settings['audio']['oga-file-url']); ?>" type="audio/ogg">
				<?php endif; ?>
				<?php if( !empty($vu_post_format_settings['audio']['mp3-file-url']) ) : ?>
					<source src="<?php echo esc_url($vu_post_format_settings['audio']['mp3-file-url']); ?>" type="audio/mpeg">
				<?php endif; ?>	
				<?php echo __('Your browser does not support the audio element.', 'bakery'); ?>
			</audio>
		</div>
	<?php } ?>
	
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