<?php
$current_user = wp_get_current_user();
$user_meta    = get_user_meta( get_current_user_id(), PPW_Constants::USER_SUBSCRIBE );

?>
<div class="ppw_wrap_subscribe_form">
	<div class="ppw_subscribe_form">
		<h3><?php echo esc_html__( 'What\'s news?', 'password-protect-page' ); ?></h3>
		<div style="padding-left: 15px;padding-right: 15px;">
			<ul style="list-style: disc;">
				<li>
					<?php echo esc_html__( '[Feature] Display countdown timer in Sitewide password form via ', 'password-protect-page' ); ?>
					<a target="_blank" rel="noopener" href="customize.php?autofocus[panel]=ppwp_sitewide"><?php echo esc_html__( 'WordPress Customizer', 'password-protect-page' ); ?></a>.
				</li>
				<li>
					<?php echo esc_html__( '[Improvement] Provide an option to hide Sitewide password form via ', 'password-protect-page' ); ?>
					<a target="_blank" rel="noopener" href="customize.php?autofocus[panel]=ppwp_sitewide"><?php echo esc_html__( 'WordPress Customizer', 'password-protect-page' ); ?></a>.
				</li>
				<li>
					<?php echo esc_html__( '[Improvement] Secure input data.', 'password-protect-page' ); ?>
				</li>
				<li>
					<?php echo esc_html__( 'We will ', 'password-protect-page' ); ?>
					<strong><?php echo esc_html__( 'use &lt;div&gt; tag' ); ?></strong>
					<?php echo esc_html__( ' to display the PCP password form input field in the upcoming versions.', 'password-protect-page' ); ?>
				</li>
			</ul>
		</div>
	</div>
	<form id="ppw_subscribe_form" class="ppw_subscribe_form <?php echo empty( $user_meta ) ? '' : 'ppw_subscribe_hidden'; ?>">
		<h3><?php echo esc_html__( 'Feeling lucky?', 'password-protect-page' ); ?></h3>
		<p><?php echo esc_html__( 'Be the first to get our latest updates and probably 1-year Pro license for free.', 'password-protect-page' ); ?></p>
		<input type="text" id="ppw_email_subscribe" class="input_email" placeholder="you@example.com" value="<?php echo esc_attr( $current_user->user_email ); ?>"/>
		<input type="hidden" id="ppw_subscribe_form_nonce" value="<?php echo esc_attr( wp_create_nonce( PPW_Constants::SUBSCRIBE_FORM_NONCE ) ); ?>">
		<p class="ppw_subscribe_error"><?php echo esc_html__( 'Please enter your valid email.', 'password-protect-page' ); ?></p>
		<p><input type="submit" class="button button-primary" id="ppw_subscribe_button" value="<?php echo esc_html__( 'Get Lucky', 'password-protect-page' ); ?>"/></p>
	</form>
	<div id="ppw_subscribe_form_success" class="ppw_subscribe_form <?php echo empty( $user_meta ) ? 'ppw_subscribe_hidden' : ''; ?>">
		<p><?php echo esc_html__( 'Congrats! You\'ve subscribed to our newsletter and now stand a chance to win our 1-year Pro license for free.', 'password-protect-page' ); ?></p>
		<p><?php echo esc_html__( 'Stay tuned for our updates :)', 'password-protect-page' ); ?></p>
	</div>
	<div class="ppw_wrap_al">
		<h3>Go Pro with many more powerful features</h3>
		<a target="_blank" rel="noopener" href="http://bit.ly/ppwp-lsb-pro">
			<img src="<?php echo esc_url( PPW_DIR_URL ); ?>admin/images/ppwp-pro-banner.png"/>
		</a>
	</div>
</div>
