<?php
	/**
	 * Others Shortcodes
	 */

	/**
	 * Embed Shortcode
	 *
	 * @param string $src Youtube video embed URL. eg: https://www.youtube.com/embed/{video_id} or https://player.vimeo.com/video/{video_id}
	 * @param string $ratio Video ratio that will properly scale on any device. eg: '16by9' or '4by3'. Default: '16by9'
	 */

	function vu_embed_shortcode( $atts, $content = null ) {
		$atts = shortcode_atts( array(
			'src' => '',
			'ratio' => '16by9'
		), $atts );

		return '<div class="embed-responsive embed-responsive-'. esc_attr($atts['ratio']) .'"><iframe class="embed-responsive-item" src="'. esc_url($atts['src']) .'"></iframe></div>';
	}

	add_shortcode('vu_embed', 'vu_embed_shortcode');

	/**
	 * Menu Shortcode
	 *
	 * @param int $atts['menu-slug'] The number of posts being displayed. Default: '3'
	 * @see https://codex.wordpress.org/Function_Reference/wp_get_recent_posts
	 */

	function vu_menu_shortcode( $atts, $content = null ) {
		$atts = shortcode_atts( array(
			'menu' => ''
		), $atts );

		if( empty($atts['menu']) ) {
			return '';
		}

		ob_start();

		wp_nav_menu(array(
			'menu'            => $atts['menu'],
			'container'       => 'nav',
			'container_id'    => 'vu_tb-menu',
			'container_class' => 'vu_tb-menu-container',
			'menu_id'         => 'vu_tb-menu-list',
			'menu_class'      => 'vu_tb-list list-unstyled',
			'fallback_cb'     => null
		));

		$output = ob_get_contents();
		ob_end_clean();

		return $output;
	}

	add_shortcode('vu_menu', 'vu_menu_shortcode');


	/**
	 * Recent Posts Shortcode
	 *
	 * @param int $atts['numberposts'] The number of posts being displayed. Default: '3'
	 * @see https://codex.wordpress.org/Function_Reference/wp_get_recent_posts
	 */

	function vu_recent_posts_shortcode( $atts, $content = null ) {
		$atts = shortcode_atts( array(
			'numberposts' => 3
		), $atts );

		$recent_posts = new WP_Query('orderby=date&order=DESC&posts_per_page='. absint($atts['numberposts'])); 
		
		ob_start();

		if( $recent_posts->have_posts() ) :
			while ($recent_posts->have_posts()) : $recent_posts->the_post(); ?>
				<div <?php post_class('blog-post-small'); ?>>
					<?php if( has_post_thumbnail(get_the_ID()) ) : ?>
						<div class="blog-post-small-img">
							<a href="<?php echo vu_get_attachment_image_src(get_post_thumbnail_id(get_the_ID()), 'full'); ?>" title="<?php echo esc_attr( get_the_title() ); ?>" class="vu_lightbox">
								<?php the_post_thumbnail('ratio-3:2'); ?>
								<span class="blog-post-small-img-cover"></span>
							</a>
							<div class="blog-post-small-info">
								<div class="blog-post-small-info-content"><?php echo get_the_date(); ?></div>
							</div>
						</div>
					<?php endif; ?>

					<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
					<p><?php vu_the_excerpt(20); ?></p>
				</div>
			<?php endwhile;
		endif;

		$output = ob_get_contents();
		ob_end_clean();
		
		return $output;
	}

	add_shortcode('vu_recent_posts', 'vu_recent_posts_shortcode');

	/**
	 * Popular Posts Shortcode
	 *
	 * @param int $atts['numberposts'] The number of posts being displayed. Default: '3'
	 */

	function vu_popular_posts_shortcode( $atts, $content = null ) {
		$atts = shortcode_atts( array(
			'numberposts' => 3
		), $atts );

		ob_start();
		
		$popular_posts = new WP_Query('orderby=comment_count&posts_per_page='. absint($atts['numberposts'])); 
		
		if( $popular_posts->have_posts() ) :
			while ($popular_posts->have_posts()) : $popular_posts->the_post(); ?>
				<div <?php post_class('post-preview'); ?>>
					<div class="post-preview-image">
						<?php if( has_post_thumbnail() ) : ?>
							<a href="<?php the_permalink(); ?>">
								<?php the_post_thumbnail('thumbnail'); ?>
							</a>
						<?php endif; ?>
					</div>

					<div class="post-preview-info">
						<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
						<div class="post-preview-detail"><?php echo __('by:', 'bakery'); ?> <?php the_author_posts_link(); ?></div>
					</div>
				</div>
			<?php endwhile;
		endif;

		$output = ob_get_contents();
		ob_end_clean();
		
		return $output;
	}

	add_shortcode('vu_popular_posts', 'vu_popular_posts_shortcode');

	/**
	 * MailChimp Newsletter Shortcode
	 *
	 * @param int $atts['submit_text'] The submit button text. Default: 'Subscribe'
	 * @param string $content The text to be displayed before subscribe form.
	 */

	function vu_newsletter_shortcode( $atts, $content ) {
		$atts = shortcode_atts( array(
			'submit_text' => __('Subscribe', 'bakery')
		), $atts );

		ob_start();

		echo !empty($content) ? wpautop( $content ) : ''; ?>

		<form class="form-subscribe clearfix vu_frm-ajax vu_clear-fields">
			<div class="hide">
				<input type="hidden" name="action" value="vu_newsletter">
				<input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('vu_newsletter_nonce'); ?>">
			</div>

			<div class="vu_newsletter_fields clearfix">
				<div class="email-container">
					<input type="text" name="email">
				</div>
				<div class="submit-container">
					<input type="submit" value="<?php echo esc_attr( $atts['submit_text'] ); ?>">
				</div>
			</div>

			<div class="vu_msg m-t-10"></div>
		</form>

		<?php $output = ob_get_contents();
		ob_end_clean();
		
		return $output;
	}

	add_shortcode('vu_newsletter', 'vu_newsletter_shortcode');

	/**
	 * MailChimp Subscribe for the newsletter
	 *
	 * @see https://github.com/drewm/mailchimp-api/
	 */

	function vu_subscribe_form(){
		if(!empty($_POST['email']) and is_email($_POST['email']) and !empty($_POST['_wpnonce'])){
			$email = trim($_POST['email']);
			$_wpnonce = $_POST['_wpnonce'];

			if( !wp_verify_nonce($_wpnonce, 'vu_newsletter_nonce') ){
				echo json_encode(array('status' => 'error', 'title' => 'Error!', 'msg' => __('wp nonce is wrong!', 'bakery') )); exit();
			}

			if( trim(vu_get_option('mailchimp-api')) == '' ){
				echo json_encode(array('status' => 'error', 'title' => 'Error!', 'msg' => __('Missing MailChip API or MailChip List ID!', 'bakery') )); exit();
			}

			$MailChimp = new MailChimp( vu_get_option('mailchimp-api') );
			
			$result = $MailChimp->call('lists/subscribe', array(
					'id' => vu_get_option('mailchimp-list-id'),
					'email' => array('email' => $email),
					//'merge_vars' => array('FNAME'=>'', 'LNAME'=>''),
					'double_optin' => false,
					'update_existing' => true,
					'replace_interests' => false,
					'send_welcome' => false
				)
			);

			if( isset($result['status']) and $result['status'] == 'error' ){
				echo json_encode(array('status' => 'error', 'title' => 'Error!', 'msg' => $result['error'])); exit();
			} else {
				echo json_encode(array('status' => 'success', 'title' => 'Success!', 'msg' => __('You have been subscribed successfully.', 'bakery') )); exit();
			}
		} else {
			echo json_encode(array('status' => 'error', 'title' => 'Error!', 'msg' => __('Please fill all fields!', 'bakery') )); exit();
		}
	}

	add_action("wp_ajax_vu_newsletter", "vu_subscribe_form");
	add_action("wp_ajax_nopriv_vu_newsletter", "vu_subscribe_form");

	/**
	 * Social Network Shortcode
	 *
	 * @param string $atts['url'] The url of social network. Default: '#'
	 * @param string $atts['target'] The target of link. Default: '_self'
	 * @param string $atts['icon'] The icon of social network. Please get it from Font Awesome Icons. eg: 'fa fa-facebook'
	 */

	function vu_social_network_shortcode( $atts, $content = null ) {
		$atts = shortcode_atts( array(
			'url' => '#',
			'target' => '_self',
			'icon' => ''
		), $atts );

		return '<div class="social-icon-container"><a href="'. esc_url( $atts['url'] ) .'" target="'. esc_attr( $atts['target'] ) .'"><i class="'. esc_attr( $atts['icon'] ) .'"></i></a></div>';
	}

	add_shortcode('vu_social_network', 'vu_social_network_shortcode');

	/**
	 * Flickr Shortcode
	 *
	 * @param string $atts['user'] The username of flickr account. eg: 38583880@N00
	 * @param string $atts['limit'] The number of images that will be shown. Default: '6'
	 */

	function vu_flickr_shortcode( $atts, $content = null ) {
		$atts = shortcode_atts( array(
			'user' => '',
			'limit' => 6
		), $atts );

		wp_enqueue_script( array('flickr-feed') );

		return '<div class="vu_flickr-photos vu_lightbox vu_gallery clearfix" data-user="'. esc_attr( $atts['user'] ) .'" data-limit="'. absint( $atts['limit'] ) .'"></div>';
	}

	add_shortcode('vu_flickr', 'vu_flickr_shortcode');

	/**
	 * Latest Tweets Shortcode
	 *
	 * @param string $atts['user'] The username of twitter account.
	 * @param string $atts['count'] The number of tweets that will be shown. Default: '3'
	 * @param string $atts['loading_text'] The text that will be shown before loading tweets. Default: 'Loading tweets...'
	 */

	function vu_latest_tweets_shortcode( $atts, $content = null ) {
		$atts = shortcode_atts( array(
			'user' => '',
			'count' => 3,
			'loading_text' => __('Loading tweets...', 'bakery')
		), $atts );

		wp_enqueue_script( array('twitter-feed') );

		return '<div class="vu_latest-tweets feed-post-slider vu_owl-carousel clearfix" data-owl=".tweet_list" data-autoheight="true" data-pagination="false" data-user="'. esc_attr( $atts['user'] ) .'" data-count="'. absint( $atts['count'] ) .'" data-text="'. esc_attr( $atts['loading_text'] ) .'" data-action="vu_latest_tweets"></div>';
	}

	add_shortcode('vu_latest_tweets', 'vu_latest_tweets_shortcode');

	/**
	 * Latest Tweets Shortcode
	 */

	function vu_latest_tweets_ajax(){
		if( empty($_POST['request']) ) { exit(); }

		$consumer_key = esc_attr(vu_get_option('twitter-consumer-key'));
		$consumer_secret = esc_attr(vu_get_option('twitter-consumer-secret'));
		$user_token = esc_attr(vu_get_option('twitter-user-token'));
		$user_secret = esc_attr(vu_get_option('twitter-user-secret'));

		$ezTweet = new ezTweet($consumer_key, $consumer_secret, $user_token, $user_secret, $_POST['request']);
		$ezTweet->fetch();

		exit();
	}

	add_action("wp_ajax_vu_latest_tweets", "vu_latest_tweets_ajax");
	add_action("wp_ajax_nopriv_vu_latest_tweets", "vu_latest_tweets_ajax");

	/**
	 * Google Plus Badge Shortcode
	 *
	 * @param string $atts['page_id'] The page Id of google plus account. eg: '106192958286631454676'
	 * @param int $atts['width'] Width of the google plus badge. Default: '300'
	 */

	function vu_google_plus_badge_shortcode( $atts, $content = null ) {
		$atts = shortcode_atts( array(
			'page_id' => '',
			'width' => '300'
		), $atts );

		wp_enqueue_script( array('google-apis') );

		return '<div class="vu_google-plus-badge-container clearfix"><div class="g-page" data-width="'. esc_attr( $atts['width'] ) .'" data-href="https://plus.google.com/'. esc_attr( $atts['page_id'] ) .'" data-layout="landscape" data-rel="publisher"></div></div>';
	}

	add_shortcode('vu_google_plus_badge', 'vu_google_plus_badge_shortcode');

	/**
	 * Facebook Like Box Shortcode
	 *
	 * @param string $atts['url'] The url of facebook account. eg: http://facebook.com/psdtuts
	 * @param int $atts['width'] Width of the box.
	 * @param int $atts['height'] Height of the box. Default: '300'
	 * @param int $atts['colorscheme'] Color Scheme of the box. eg: 'light' or 'dark'. Default: 'light'
	 * @param int $atts['show_face'] Show face of people who like your page. Default: 'true'
	 * @param int $atts['show_border'] Show border of the box. Default: 'true'
	 * @param int $atts['show_stream'] Show stream of the fun page. Default: 'false'
	 * @param int $atts['show_header'] Show header of the box. Default: 'false'
	 */

	function vu_facebook_like_box_shortcode( $atts, $content = null ) {
		$atts = shortcode_atts( array(
			'url' => '',
			'width' => '',
			'height' => '300',
			'colorscheme' => 'light',
			'show_face' => 'true',
			'show_border' => 'true',
			'show_stream' => 'false',
			'show_header' => 'false'
		), $atts );

		return '<div class="vu_fb-like-box-container clearfix" data-href="'. esc_url( $atts['url'] ) .'" data-width="'. esc_attr( $atts['width'] ) .'" data-height="'. esc_attr( $atts['height'] ) .'" data-colorscheme="'. esc_attr( $atts['colorscheme'] ) .'" data-show-faces="'. esc_attr( $atts['show_face'] ) .'" data-show-border="'. esc_attr( $atts['show_border'] ) .'" data-stream="'. esc_attr( $atts['show_stream'] ) .'" data-header="'. esc_attr( $atts['show_header'] ) .'"></div>';
	}

	add_shortcode('vu_facebook_like_box', 'vu_facebook_like_box_shortcode');

	/**
	 * List Item Check Shortcode
	 *
	 * @param string $content The content of the list item
	 */

	function vu_list_item_check_shortcode( $atts, $content ) {
		return '<div class="item-check">'. esc_js($content) .'</div>';
	}

	add_shortcode('vu_list_item_check', 'vu_list_item_check_shortcode');

	/**
	 * Font Awesome Icons Shortcode
	 *
	 * @link https://wordpress.org/plugins/font-awesome-shortcodes/
	 */

	function vu_font_awesome_shortcode($atts, $content = null){
		$atts = shortcode_atts( array(
			"type" => '',
			"size" => false,
			"pull" => false,
			"border" => false,
			"spin" => false,
			"list_item" => false,
			"fixed_width" => false,
			"rotate" => false,
			"flip" => false,
			"stack_size" => false,
			"inverse" => false,
			"xclass" => false
		), $atts );

		$class  = 'fa';
		$class .= ( $atts['type'] )         ? ' fa-' . $atts['type'] : '';
		$class .= ( $atts['size'] )         ? ' fa-' . $atts['size'] : '';
		$class .= ( $atts['pull'] )         ? ' pull-' . $atts['pull'] : '';
		$class .= ( $atts['border'] )       ? ' fa-border' : '';
		$class .= ( $atts['spin'] )         ? ' fa-spin' : '';
		$class .= ( $atts['list_item'] )    ? ' fa-li' : '';
		$class .= ( $atts['fixed_width'] )  ? ' fa-fw' : '';
		$class .= ( $atts['rotate'] )       ? ' fa-rotate-' . $atts['rotate'] : '';
		$class .= ( $atts['flip'] )         ? ' fa-flip-' . $atts['flip'] : '';
		$class .= ( $atts['stack_size'] )   ? ' fa-stack-' . $atts['stack_size'] : '';
		$class .= ( $atts['inverse'] )      ? ' fa-inverse' : '';
		$class .= ( $atts['xclass'] )   	? ' ' . $atts['xclass'] : '';
		  
		return sprintf( '<i class="%s"></i>', esc_attr( $class ) );
	}

	add_shortcode('vu_fa', 'vu_font_awesome_shortcode');

	/**
	 * Post Gallery Shortcode
	 *
	 * @param string $atts['ids']
	 */

	function vu_post_gallery_shortcode($atts, $content = null) {
		$atts = shortcode_atts( array(
			'ids' => ''
		), $atts );

		$ids = @explode(',', esc_attr($atts['ids']));

		$return = '<div class="blog-post-images-slider vu_owl-carousel" data-navigation="false" data-autoheight="true">';

		foreach ($ids as $id) {
			$return .= wp_get_attachment_image(absint($id), 'ratio-16:9');
		}

		$return .= '</div>';
		
		return $return;
	}

	add_shortcode('vu_post_gallery', 'vu_post_gallery_shortcode');
?>