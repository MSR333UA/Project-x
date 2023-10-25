<?php
	/**
	 * Blog Posts Shortcode
	 *
	 * @param string $atts['query']
	 * @param string $atts['show_author']
	 * @param string $atts['class'] Add a class name and then refer to it in your css file.
	 */

	function vu_blog_posts_shortcode($atts, $content = null) {
		$atts = shortcode_atts(array(
			"query" => "",
			"type" => "",
			"layout" => "",
			"navigation" => "",
			"show_author" => "",
			"class" => ""
		), $atts);

		if( stripos($atts['query'], 'post_type:post') === false ){
			$atts['query'] .= '|post_type:post';
		}

		if( function_exists('vc_build_loop_query') ) {
			list($args, $blog_posts) = vc_build_loop_query( esc_attr($atts['query']) );
		} else {
			$VcLoopQueryBuilder = new VcLoopQueryBuilder( esc_attr($atts['query']) );
			$blog_posts = $VcLoopQueryBuilder->build();
		}

		ob_start();
	?>
		<div class="section-content<?php vu_extra_class($atts['class']); ?>">
			<?php 
				if( $atts['type'] == 'carousel' ) {
					$carousel_options = array(
						"singleItem" => false,
						"items" => absint($atts['layout']),
						"itemsDesktop" => array(1199, absint($atts['layout'])),
						"itemsDesktopSmall" => array(980, absint($atts['layout'])),
						"itemsTablet" => array(768, 2),
						"itemsMobile" => array(479, 1),
						"navigation" => ($atts['navigation'] == '1') ? true : false,
						"navigationText" => array('', ''),
						"pagination" => false,
						"autoHeight" => true,
						"rewindNav" => true,
						"scrollPerPage" => true
					);
				}
			?>
			<?php echo ($atts['type'] == 'standard') ? '<div class="row">' : '<div class="row"><div class="post-slider vu_owl-carousel slider-arrows" data-options="'. esc_attr(json_encode($carousel_options)) .'">'; ?>
				<?php if($blog_posts->have_posts()) : while($blog_posts->have_posts()): $blog_posts->the_post(); ?>
					<?php echo ($atts['type'] == 'standard') ? '<div class="col-md-'. (12 / absint($atts['layout'])) .' col-sm-6 col-xs-12">' : '<div class="col-md-12">'; ?>
						<article id="post-<?php echo get_the_ID(); ?>" <?php post_class(array('post','text-center','m-b-50')); ?>>
							<?php 
								if( get_post_format() == 'gallery' ) {
									$vu_post_format_settings = vu_get_post_meta( get_the_ID(), 'vu_post_format_settings' );
									if( !empty($vu_post_format_settings['gallery']['images']) ) {
										echo '<div class="post-image"><div class="post-images-slider vu_owl-carousel" data-navigation="false">';
										$ids = @explode(',', $vu_post_format_settings['gallery']['images']);
										foreach ($ids as $id) {
											echo wp_get_attachment_image( $id, 'ratio-4:3' );
										}
										echo '</div></div>';
									}
								} else {
									if( has_post_thumbnail() ) {
										echo '<div class="post-image"><a href="'. get_the_permalink() .'" title="'. get_the_title() .'">';
										the_post_thumbnail('ratio-4:3');
										echo '</a></div>';
									}
								}
							?>
							<div class="post-data">
								<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
								<?php vu_the_excerpt(20); ?>
							</div>
							<div class="post-info">
								<?php if( $atts['show_author'] ) : ?>
									<?php echo __('by:', 'bakery'); ?> <?php the_author_posts_link(); ?> <span class="simple-delimiter"></span>
								<?php endif; ?>
								<?php echo __('on', 'bakery'); ?> <span><?php echo get_the_date(); ?></span> <span class="delimiter-inline">|</span> <a href="<?php comments_link(); ?>"><?php comments_number( __('No Comments', 'bakery'), __('One Comment ', 'bakery'), __('% Comments', 'bakery') ); ?></a>
							</div>
						</article><!-- .post -->
					</div>
				<?php endwhile; endif; ?>

				<?php wp_reset_postdata(); ?>
				
			<?php echo ($atts['type'] == 'standard') ? '</div>' : '</div></div>'; ?>
		</div>
	<?php
		$output = ob_get_contents();
		ob_end_clean();
		
		return $output;
	}

	add_shortcode('vu_blog_posts', 'vu_blog_posts_shortcode');

	/**
	 * Blog Posts VC Shortcode
	 */

	if( class_exists('WPBakeryShortCode') ){
		class WPBakeryShortCode_vu_blog_posts extends WPBakeryShortCode {
			public function content($atts, $content = null) {
				$atts = vc_map_get_attributes("vu_blog_posts", $atts);

				return do_shortcode( vu_generate_shortcode('vu_blog_posts', $atts, $content) );
			}
		}

		vc_map(
			array(
				"name"		=> __("Blog Posts", 'bakery'),
				"description" => __('Show the blog posts', 'bakery'),
				"base"		=> "vu_blog_posts",
				"class"		=> "vc_vu_blog_posts",
				"icon"		=> "vu_element-icon vu_blog-icon",
				"controls"	=> "full",
				"category"  => __('Bakery', 'bakery'),
				"params"	=> array(
					array(
						"type" => "loop",
						"heading" => __("Blog Post Query", 'bakery'),
						"param_name" => "query",
						'settings' => array(
							'size'          => array('hidden' => false, 'value' => '9'),
							'order_by'      => array('value' => 'date'),
							'categories'    => array('hidden' => false),
							'tags'          => array('hidden' => false),
							'tax_query'     => array('hidden' => true),
							'authors'     	=> array('hidden' => true),
							'post_type'     => array('value' => 'post', 'hidden' => true)
						),
						"save_always" => true,
						"description" => __("Create WordPress loop, to show posts from your site.", 'bakery')
					),
					array(
						"type" => "dropdown",
						"heading" => __("Type", 'bakery'),
						"param_name" => "type",
						"admin_label" => true,
						"value" => array(
							__("Standard", 'bakery') => 'standard',
							__("Carousel", 'bakery') => 'carousel'
						),
						"save_always" => true,
						"description" => __("Select type.", 'bakery'),
					),
					array(
						"type" => "dropdown",
						"heading" => __("Layout", 'bakery'),
						"param_name" => "layout",
						"admin_label" => true,
						"value" => array(
							__("1 Column", 'bakery') => '1',
							__("2 Columns", 'bakery') => '2',
							__("3 Columns", 'bakery') => '3',
							__("4 Columns", 'bakery') => '4'
						),
						"std" => '3',
						"save_always" => true,
						"description" => __("Select layout.", 'bakery'),
					),
					array(
						"type" => "checkbox",
						"heading" => __("Show navigation", 'bakery'),
						"param_name" => "navigation",
						"dependency" => array("element" => "type", "value" => "carousel"),
						"value" =>  array( 'Yes Please' => "1"),
						"std" => "1",
						"save_always" => true,
						"description" => __("", 'bakery')
					),
					array(
						"type" => "checkbox",
						"heading" => __("Show Author", 'bakery'),
						"param_name" => "show_author",
						"value" =>  array( 'Yes Please' => "1"),
						"std" => "0",
						"description" => __("Whether to show the post author", 'bakery')
					),
					array(
						"type" => "textfield",
						"heading" => __("Extra class name", 'bakery'),
						"param_name" => "class",
						"value" => "",
						"save_always" => true,
						"description" => __("If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", 'bakery')
					)
				)
			)
		);
	}
?>