<?php

	/*
		Template Name: Bordered
	*/

	get_header();
?>
	<div>
		<div class="container">
			<div class="content-box m-b-70">
				<?php if(have_posts()) : while(have_posts()) : the_post(); ?>
					<?php the_content(); ?>
				<?php endwhile; endif; ?>
			</div>
		</div>
	</div>

<?php get_footer(); ?>