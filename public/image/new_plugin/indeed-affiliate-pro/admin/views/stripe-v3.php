<form  method="post">
	<div class="uap-stuffbox">
		<h3 class="uap-h3"><?php esc_html_e('Stripe v3 - Payouts', 'uap');?></h3>
		<div class="inside">
			<div class="uap-form-line">
				<div class="row">
						<div class="col-xs-7">
							<h2><?php esc_html_e('Activate/Hold Stripe Gateway', 'uap');?></h2>
							<p><?php esc_html_e('Once activated you can process payments to your affiliate users via Stripe directly from the affiliate system.', 'uap');?></p>
							<label class="uap_label_shiwtch uap-switch-button-margin">
							<?php $checked = ($data['metas']['uap_stripe_v3_enable']) ? 'checked' : '';?>
								<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_stripe_v3_enable');" <?php echo $checked;?> />
								<div class="switch uap-display-inline"></div>
							</label>
							<input type="hidden" name="uap_stripe_v3_enable" value="<?php echo $data['metas']['uap_stripe_v3_enable'];?>" id="uap_stripe_v3_enable" />
						</div>
				</div>

				<div class="row">
					<div class="col-xs-6">
						<div class="uap-form-line">
							<div class="input-group">
							<label class="input-group-addon"><?php esc_html_e('Live Secret Key', 'uap');?></label>
								<input type="text" name="uap_stripe_v3_secret_key" value="<?php echo $data['metas']['uap_stripe_v3_secret_key'];?>" class="form-control" />
							</div>

							<div class="uap-form-line"></div>

							<div class="input-group">
							<label class="input-group-addon"><?php esc_html_e('Live Publishable Key', 'uap');?></label>
								<input type="text" name="uap_stripe_v3_publishable_key" value="<?php echo $data['metas']['uap_stripe_v3_publishable_key'];?>" class="form-control" />
							</div>

							<div class="uap-form-line"></div>

							<div class="input-group">
             		<label class="input-group-addon"><?php esc_html_e('Live mode client ID', 'uap');?></label>
              		<input type="text" name="uap_stripe_v3_client_id" value="<?php echo $data['metas']['uap_stripe_v3_client_id'];?>" class="form-control" />
              </div>
						</div>

          <div class="row">
					<div class="col-xs-4">
						<h2><?php esc_html_e('Sandbox', 'uap');?></h2>
						<label class="uap_label_shiwtch uap-switch-button-margin">
						<?php $checked = ($data['metas']['uap_stripe_v3_sandbox']) ? 'checked' : '';?>
						<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_stripe_v3_sandbox');" <?php echo $checked;?> />
						<div class="switch uap-display-inline"></div>
						</label>
						<input type="hidden" name="uap_stripe_v3_sandbox" value="<?php echo $data['metas']['uap_stripe_v3_sandbox'];?>" id="uap_stripe_v3_sandbox" />
					</div>
				</div>
					<div class="row">
					<div class="col-xs-6">
            <div class="uap-form-line">

							<div class="input-group">
							<label class="input-group-addon"><?php esc_html_e('Sandbox Secret Key', 'uap');?></label>
								<input type="text" name="uap_stripe_v3_sandbox_secret_key" value="<?php echo $data['metas']['uap_stripe_v3_sandbox_secret_key'];?>" class="form-control" />
							</div>

							<div class="uap-form-line"></div>

							<div class="input-group">
							<label class="input-group-addon"><?php esc_html_e('Sandbox Publishable Key', 'uap');?></label>
								<input type="text" name="uap_stripe_v3_sandbox_publishable_key" value="<?php echo $data['metas']['uap_stripe_v3_sandbox_publishable_key'];?>"  class="form-control"/>
							</div>

								<div class="uap-form-line"></div>
								<div class="input-group">
							<label class="input-group-addon"><?php esc_html_e('Test mode client ID', 'uap');?></label>
								<input type="text" name="uap_stripe_v3_sandbox_client_id" value="<?php echo $data['metas']['uap_stripe_v3_sandbox_client_id'];?>" class="form-control" />
							</div>
							</div>
							<div class="uap-form-line"><h4><?php esc_html_e('Setup Intructions', 'uap');?></h4></div>
						</div>

					<ul class="uap-info-list">
						<?php
							$notification_url = site_url();
							$notification_url = trailingslashit( $notification_url );
							$notification_url = add_query_arg( 'uap_act', 'stripe_v3_webhook', site_url() );
							$oauthRedirectBackPage = add_query_arg( 'uap_act', 'stripe_v3_auth', site_url() );
						?>

						<li><?php esc_html_e( '1. Go to http://stripe.com and login with username and password.', 'uap' );?></li>
						<li><?php esc_html_e( '2. Click on "Developers", and then select "API keys". Here you will find the "Secret Key" and "Publishable Key".', 'uap' );?></li>
						<li><?php esc_html_e( '3. Go to "Webhooks" and set Your Webhook at: ', 'uap');?> <?php echo "<b>" . $notification_url . "</b>";?></li>
						<li><?php esc_html_e( '4. Be sure you use the same currency in the Stripe account and in the "Ultimate Affiliate Pro" settings.', 'uap' );?></li>
						<li><?php esc_html_e( '5. Create a new Account application by clicking on ' , 'uap');?> <b> New Account </b> <?php esc_html_e(' top left menu button into dashboard.', 'uap'); ?></li>
						<li><?php esc_html_e( '6. In order to process payments to your affiliates go to ', 'uap');?><b>Connect</b> <?php esc_html_e(' tab menu and get started financial services and multi-party payments with Connect. ', 'uap');?></li>
						<li><?php esc_html_e( '7. Go to "Settings" > "Connect" > "Settings".  ','uap');?> </li>
						<li><?php esc_html_e( '8. From ', 'uap');?><b><?php esc_html_e(' Integration', 'uap');?></b> <?php esc_html_e(' section copy and paste "Live mode client ID" or "Test mode client ID" if ', 'uap');?> <b><?php esc_html_e(' View test data ','uap');?></b><?php esc_html_e(' is ON.', 'uap');?></li>
						<li><?php esc_html_e( '9. In ', 'uap' );?><b>Availability</b> <?php esc_html_e('Check Account types and Capabilities.', 'uap' );?></li>
						<li><?php esc_html_e( '10. Enable', 'uap' );?> <b>OAuth for Express accounts</b> <?php esc_html_e( ' and ', 'uap');?> <b>OAuth for Standard accounts</b>  <?php esc_html_e( ' from  OAuth settings.', 'uap' );?></li>
						<li><?php esc_html_e( '11. In "Redirects" add URI', 'uap' );?> <?php echo "<b>" . $oauthRedirectBackPage . "</b>";?></li>
						<li><?php esc_html_e( '12. If your account is in test mode go to Availability and manage both ', 'uap');?><b> Standard </b> <?php esc_html_e(' and ', 'uap');?><b>Express Account</b> <?php esc_html_e(' types and make sure that your country is selected in "Transfers" and "Payments".', 'uap');?></li>
						<li><?php esc_html_e( '13. Run some tests before switching to Live Transactions.', 'uap' );?></li>
				</ul>
				</div>

				<div class="row">
						<div class="col-xs-6">
							<div class="uap-form-line">
								<?php
									if ( !isset( $data['metas']['uap_stripe_v3_source_type'] ) ){
											$data['metas']['uap_stripe_v3_source_type'] = 'card';
									}
								?>
								<div class="input-group">
								<label class="input-group-addon"><?php esc_html_e('Balance Source', 'uap');?></label>
								<select name="uap_stripe_v3_source_type">
										<option value="bank_account" <?php if ( $data['metas']['uap_stripe_v3_source_type'] === 'bank_account' ) echo 'selected';?> ><?php esc_html_e('Bank Account', 'uap');?></option>
										<option value="card" <?php if ( $data['metas']['uap_stripe_v3_source_type'] === 'card' ) echo 'selected';?> ><?php esc_html_e('Card', 'uap');?></option>
										<option value="fpx" <?php if ( $data['metas']['uap_stripe_v3_source_type'] === 'fpx' ) echo 'selected';?> ><?php esc_html_e('FPX', 'uap');?></option>
								</select>
								</div>
								<p><?php esc_html_e('The source balance to use for transfers.','uap'); ?></p>
							</div>
						</div>
				</div>

				<div class="inside">
					<h5 class="uap-h5"><?php esc_html_e('Important:', 'uap');?></h5>
					<p><?php esc_html_e('1. When Affiliate connects his Stripe Account with your merchant Stripe Account, Stripe will create a Sub Account for him where payout will be transferred.','uap'); ?></p>
					<p><?php esc_html_e('2. Affiliates may see all their Sub Accounts on Profile page from Stripe Dashboard.', 'uap');?></p>
					<p><?php esc_html_e('3. In order to run some tests before switching live, make sure that in Stripe balance you have the required amount to be in the same currency as the currency saved in UAP.', 'uap');?></p>
					<p><?php esc_html_e('4. In "Settings" > "Connect settings" make sure that you have configured ', 'uap');?> <b> <?php esc_html_e('Accounts Types', 'uap');?></b> <?php esc_html_e(' and ', 'uap');?> <b> <?php esc_html_e('Capabilities.', 'uap');?></b></p>
				</div>

				<div id="uap_save_changes" class="uap-submit-form">
					<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
				</div>
			</div>
		</div>
	</div>
</div>
</div>
</form>
