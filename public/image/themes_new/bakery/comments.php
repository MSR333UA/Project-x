<?php
	if (!empty($_SERVER['SCRIPT_FILENAME']) && 'comments.php' == basename($_SERVER['SCRIPT_FILENAME']))
		exit(__('Please do not load this page directly. Thanks!', 'bakery'));

	if ( post_password_required() ) { ?>
		<p class="nocomments"><?php echo __('This post is password protected. Enter the password to view comments.', 'bakery'); ?></p>
	<?php
		return;
	}
?>

<div id="comments" class="blog-post-comments">
	<?php if ( have_comments() ) : ?>
		<h2><?php comments_number( __('No Comments', 'bakery'), __('One Comment', 'bakery'), __('% Comments', 'bakery') ); ?></h2>

		<ul class="comment-list list-unstyled">
			<?php wp_list_comments( array('type'=> 'comment', 'callback' => 'vu_comments') ); ?>
		</ul>
		
		<div class="text-center clearfix m-b-30">
			<div class="pagination pull-left">
				<?php previous_comments_link('<span class="pagination-item pagination-nav">'. __('prev', 'bakery') .'</span>'); ?>
			</div>
			<div class="pagination pull-right">
				<?php next_comments_link('<span class="pagination-item pagination-nav">'. __('next', 'bakery') .'</span>'); ?>
			</div>
		</div>
	<?php else : // this is displayed if there are no comments so far ?>
		<?php if ( comments_open() ) : ?>
			<!-- If comments are open, but there are no comments. -->
		 <?php else : // comments are closed ?>
			<!-- If comments are closed. -->
			<!--<p class="nocomments">Comments are closed.</p>-->
		<?php endif; ?>
	<?php endif; ?>

	<?php if ( comments_open() ) : 

	$is_user_logged_in = is_user_logged_in();

	$args = array(
		'id_form'           => 'commentform',
		'id_submit'         => 'submit',
		'class_submit'      => 'submit-comment btn btn-default',
		'title_reply'       => __( 'Leave a Reply', 'bakery' ),
		'title_reply_to'    => __( 'Leave a Reply to %s', 'bakery' ),
		'cancel_reply_link' => __( 'Cancel Reply', 'bakery' ),
		'label_submit'      => __( 'Submit Comment', 'bakery' ),
		'comment_field' =>  ($is_user_logged_in ? '<div class="row m-b-10">' : '') .'<div class="col-xs-12 '. (is_user_logged_in() ? 'col-md-12' : 'col-md-7') .'"><textarea id="comment" name="comment" class="form-control" placeholder="'. __('Your comment here', 'bakery') .'"></textarea></div></div>',
		'must_log_in' => '<p class="must-log-in m-b-15">'. sprintf( __( 'You must be <a href="%s">logged in</a> to post a comment.', 'bakery' ), wp_login_url( apply_filters( 'the_permalink', get_permalink() ) ) ). '</p>',
		'logged_in_as' => '<p class="logged-in-as m-b-15">' . sprintf( __( 'Logged in as <a href="%1$s">%2$s</a>. <a href="%3$s" title="Log out of this account">Log out?</a>', 'bakery' ), admin_url( 'profile.php' ), $user_identity, wp_logout_url( apply_filters( 'the_permalink', get_permalink( ) ) ) ) . '</p>',
		'comment_notes_before' => '<p class="comment-notes m-b-15">'. __('Your email address will not be published.', 'bakery') .'</p>',
		'comment_notes_after' => '',
		'fields' => apply_filters( 'comment_form_default_fields', array(
			'author' =>
				'<div class="row m-b-10"><div class="col-xs-12 col-md-5"><div class="row"><div class="col-xs-12">'.
				'<input id="author" name="author" type="text" class="form-control" placeholder="'. __('Your name here', 'bakery') .'" value="" size="30" /></div>',
			'email' =>
				'<div class="col-xs-12">'.
				'<input id="email" name="email" type="text" class="form-control" placeholder="'. __('Your email here', 'bakery') .'" value="" size="30" /></div>',
			'url' =>
				'<div class="col-xs-12">'.
				'<input id="url" name="url" type="text" class="form-control" placeholder="'. __('Your website here', 'bakery') .'" value="" size="30" /></div></div></div>'
			)
		),
	);

	comment_form($args);

	endif; ?>
</div>