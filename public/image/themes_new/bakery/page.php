<?php
	get_header();

	if ( have_posts() ) : while ( have_posts() ) : the_post();
		if( has_shortcode( $post->post_content, 'vc_row' ) ) {
			the_content();
		} else { ?>
			<div class="container vu_page-content m-t-80 m-b-70">
				<div class="row">
					<div class="col-xs-12">
						<?php the_content(); ?>
					</div>
				</div>
			</div>
	<?php }
	endwhile; endif;

	get_footer();
?>