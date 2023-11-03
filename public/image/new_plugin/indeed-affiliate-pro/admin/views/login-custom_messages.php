<form method="post" >
	<input type="hidden" name="uap_admin_forms_nonce" value="<?php echo wp_create_nonce( 'uap_admin_forms_nonce' );?>" />
				<div class="uap-stuffbox">
					<h3 class="uap-h3"><?php esc_html_e('Messages:', 'uap');?></h3>
					<div class="inside">
						<div class="row">
							<div class="col-xs-6">
							<h2><?php esc_html_e('Login Messages', 'uap');?></h2>
							<div>
								<div class="uap-labels-special"><?php esc_html_e('Successfully Message:', 'uap');?></div>
								<textarea name="uap_login_succes" class="uap-dashboard-textarea"><?php echo uap_correct_text($data['metas']['uap_login_succes']);?></textarea>
							</div>
							<div>
								<div class="uap-labels-special"><?php esc_html_e('Default message for pending users:', 'uap');?></div>
								<textarea name="uap_login_pending" class="uap-dashboard-textarea"><?php echo uap_correct_text($data['metas']['uap_login_pending']);?></textarea>
							</div>
							<div>
								<div class="uap-labels-special"><?php esc_html_e('Error Message:', 'uap');?></div>
								<textarea name="uap_login_error" class="uap-dashboard-textarea"><?php echo uap_correct_text($data['metas']['uap_login_error']);?></textarea>
							</div>
							<div>
								<div class="uap-labels-special"><?php esc_html_e('E-mail Pending:', 'uap');?></div>
								<textarea name="uap_login_error_email_pending" class="uap-dashboard-textarea"><?php echo uap_correct_text($data['metas']['uap_login_error_email_pending']);?></textarea>
							</div>
						</div>
						<div class="col-xs-6">
							<h2><?php esc_html_e('Reset Password Messages', 'uap');?></h2>
							<div>
								<div class="uap-labels-special"><?php esc_html_e('Successfully Message:', 'uap');?></div>
								<textarea name="uap_reset_msg_pass_ok" class="uap-dashboard-textarea"><?php echo uap_correct_text($data['metas']['uap_reset_msg_pass_ok']);?></textarea>
							</div>

							<div>
								<div class="uap-labels-special"><?php esc_html_e('Error Message:', 'uap');?></div>
								<textarea name="uap_reset_msg_pass_err" class="uap-dashboard-textarea"><?php echo uap_correct_text($data['metas']['uap_reset_msg_pass_err']);?></textarea>
							</div>

							<div>
								<div class="uap-labels-special"><?php esc_html_e('Error Message:', 'uap');?></div>
								<textarea name="uap_login_error_on_captcha" class="uap-dashboard-textarea"><?php echo uap_correct_text($data['metas']['uap_login_error_on_captcha']);?></textarea>
							</div>

						</div>
					</div>

						<div id="uap_save_changes" class="uap-wrapp-submit-bttn">
							<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
						</div>
					</div>
				</div>
</form>
