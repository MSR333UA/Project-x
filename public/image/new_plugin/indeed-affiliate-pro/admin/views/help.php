<?php
$responseNumber = isset($_GET['response']) ? $_GET['response'] : false;
if ( !empty($_GET['token'] ) && $responseNumber == 1 ){
		$ElCheck = new \Indeed\Uap\ElCheck();
		$responseNumber = $ElCheck->responseFromGet();
}
if ( $responseNumber !== false ){
		$ElCheck = new \Indeed\Uap\ElCheck();
		$responseMessage = $ElCheck->responseCodeToMessage( $responseNumber, 'uap-danger-box', 'uap-success-box', 'uap' );
}
$license = get_option('uap_license_set');
?>
<div class="uap-wrapper">
<div class="uap-page-title">Ultimate Affiliate Pro - <span class="second-text"><?php esc_html_e('Help', 'uap');?></span></div>

<div class="uap-stuffbox">
	<h3 class="uap-h3">
		<?php esc_html_e('Activate Ultimate Affiliate Pro', 'uap');?>
	</h3>

	<form method="post" >
		<div class="inside">
			<?php if ($disabled):?>
				<div class="uap-form-line uap-no-border uap-color-red"><strong><?php esc_html_e("cURL is disabled. You need to enable if for further activation request.", 'uap');?></strong></div>
			<?php endif;?>
			<div class="uap-form-line uap-no-border uap-help-seetings-sectionone">
				<label class="uap-labels"><?php esc_html_e('Your Purchase Code:', 'uap');?></label>
			</div>
			<div class="uap-form-line uap-no-border uap-help-seetings-sectiontwo">
				<input name="uap_envato_code" type="password" value="<?php echo $data['uap_envato_code'];?>"/>
			</div>
			<div class="uap-stuffbox-submit-wrap uap-license-button uap-help-seetings-sectionthree">
				<?php if ( $license ):?>
                	<div class="uap-revoke-license uap-js-revoke-license"><?php esc_html_e( 'Revoke License', 'uap' );?></div>
                <?php else: ?>
                	<input type="submit" value="<?php esc_html_e('Activate License', 'uap');?>" name="uap_save_licensing_code" <?php echo $disabled;?> class="button button-primary button-large" />
                <?php endif;?>
			</div>

			<div class="uap-clear"></div>
			<div class="uap-license-status">
        	<?php
						if ( $responseNumber !== false ){
								echo $responseMessage;
						} else if ( !empty( $_GET['revoke'] ) ){
								?>
								<div class="uap-success-box"><?php esc_html_e( 'You have just revoke your license for Ultimate Affiliate Pro plugin.', 'uap' );?></div>
								<?php
						} else if ( $license ){ ?>
									<div class="uap-success-box"><?php esc_html_e( 'Your license for Ultimate Affiliate Pro is currently Active.', 'uap' );?></div>
          <?php } ?>
      </div>

					<div class="uap-license-status">
								<?php
						if ( isset($_GET['extraCode']) && isset( $_GET['extraMess'] ) && $_GET['extraMess'] != '' ){
								$_GET['extraMess'] = stripslashes($_GET['extraMess']);
								if ( $_GET['extraCode'] > 0 ){
										// success
										?>
										<div class="uap-success-box"><?php echo urldecode( $_GET['extraMess'] );?></div>
										<?php
								} else if ( $_GET['extraCode'] < 0 ){
										// errors
										?>
										<div class="uap-danger-box"><?php echo urldecode( $_GET['extraMess'] );?></div>
										<?php
								} else if ( $_GET['extraCode'] == 0 ){
										// warning
										?>
										<div class="uap-warning-box"><?php echo urldecode( $_GET['extraMess'] );?></div>
										<?php
								}
						}
					?>
					</div>

      </div>

			<div class="uap-help-seetings-description-wrapper">
				<p><?php esc_html_e('A valid purchase code Activate the Full Version of', 'uap');?><strong> Ultimate Affiliate Pro</strong> <?php esc_html_e('plugin and provides access on support system. A purchase code can only be used for ', 'uap');?><strong><?php esc_html_e('ONE', 'uap');?></strong> Ultimate Affiliate Pro <?php esc_html_e('for WordPress installation on', 'uap');?> <strong><?php esc_html_e('ONE', 'uap');?></strong> <?php esc_html_e('WordPress site at a time. If you previosly activated your purchase code on another website, then you have to get a', 'uap');?> <a href="https://codecanyon.net/item/ultimate-affiliate-pro-wordpress-plugin/16527729?ref=azzaroco" target="_blank"><?php esc_html_e('new Licence', 'uap');?></a>.</p>
				<h4><?php esc_html_e('Where can I find my Purchase Code?', 'uap');?></h4>
				<a href="https://codecanyon.net/item/ultimate-affiliate-pro-wordpress-plugin/16527729?ref=azzaroco" target="_blank">
					<img src="<?php echo UAP_URL;?>assets/images/purchase_code.jpg" alt="Purchase Code"/>
					</a>
				</div>

	</form>
	</div>


<div class="uap-stuffbox">
		<h3 class="uap-h3">
				<?php esc_html_e('Contact Support', 'uap');?>
		</h3>
		<div class="inside">
			<div class="submit uap-help-contact-sectionone">
				<?php esc_html_e('In order to contact Indeed support team you need to create a ticket providing all the necessary details via our support system:', 'uap');?> support.wpindeed.com
			</div>
			<div class="submit uap-help-contact-sectiontwo">
				<a href="http://support.wpindeed.com/open.php?topicId=19" target="_blank" class="button button-primary button-large"> <?php esc_html_e('Submit Ticket', 'uap');?></a>
			</div>
			<div class="clear"></div>
		</div>
	</div>

	<div class="uap-stuffbox">
		<h3 class="uap-h3">
		    	<?php esc_html_e('Documentation', 'uap');?>
		</h3>
		<div class="inside">
			<iframe src="https://affiliate.wpindeed.com/documentation/" width="100%" height="1000px" ></iframe>
		</div>
	</div>

</div>
<div class="uap-js-help-section-nonce" data-value="<?php echo wp_create_nonce('uap_license_nonce');?>"<</div>
<div class="uap-js-help-section-revoke-url" data-value="<?php echo admin_url('admin.php?page=ultimate_affiliates_pro&tab=help&revoke=true');?>"<</div>
