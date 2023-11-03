			<form method="post" >

				<input type="hidden" name="uap_admin_forms_nonce" value="<?php echo wp_create_nonce( 'uap_admin_forms_nonce' );?>" />

				<div class="uap-stuffbox">
					<h3 class="uap-h3"><?php esc_html_e('Custom Messages', 'uap');?></h3>
					<div class="inside">

						<div class="row">
							<div class="col-xs-6">
						<div>
							<div>
								<div class="uap-labels-special"><b><?php esc_html_e('Error - Username is taken:', 'uap');?></b></div>
								<textarea name="uap_register_username_taken_msg" class="uap-dashboard-textarea"><?php echo uap_correct_text($data['metas']['uap_register_username_taken_msg']);?></textarea>
							</div>

							<div>
								<div class="uap-labels-special"><b><?php esc_html_e('Error - Invalid Username:', 'uap');?></b></div>
								<textarea name="uap_register_error_username_msg" class="uap-dashboard-textarea"><?php echo uap_correct_text($data['metas']['uap_register_error_username_msg']);?></textarea>
							</div>

							<div>
								<div class="uap-labels-special"><b><?php esc_html_e('Error - Email is taken:', 'uap');?></b></div>
								<textarea name="uap_register_email_is_taken_msg" class="uap-dashboard-textarea"><?php echo uap_correct_text($data['metas']['uap_register_email_is_taken_msg']);?></textarea>
							</div>

							<div>
								<div class="uap-labels-special"><b><?php esc_html_e('Error - Invalid Email Address:', 'uap');?></b></div>
								<textarea name="uap_register_invalid_email_msg" class="uap-dashboard-textarea"><?php echo uap_correct_text($data['metas']['uap_register_invalid_email_msg']);?></textarea>
							</div>

							<div>
								<div class="uap-labels-special"><b><?php esc_html_e('Error - Email Addresses did not Match:', 'uap');?></b></div>
								<textarea name="uap_register_emails_not_match_msg" class="uap-dashboard-textarea"><?php echo uap_correct_text($data['metas']['uap_register_emails_not_match_msg']);?></textarea>
							</div>

							<div>
								<div class="uap-labels-special"><b><?php esc_html_e('Error - Password did not match:', 'uap');?></b></div>
								<textarea name="uap_register_pass_not_match_msg" class="uap-dashboard-textarea"><?php echo uap_correct_text($data['metas']['uap_register_pass_not_match_msg']);?></textarea>
							</div>

							<div>
								<div class="uap-labels-special"><b><?php esc_html_e('Error - Password Only Characters and Digits:', 'uap');?></b></div>
								<textarea name="uap_register_pass_letter_digits_msg" class="uap-dashboard-textarea"><?php echo uap_correct_text($data['metas']['uap_register_pass_letter_digits_msg']);?></textarea>
							</div>
						</div>
					</div>
							<div class="col-xs-6">
						<div>
							<div>
								<div class="uap-labels-special"><b><?php esc_html_e('Error - Password Min Length:', 'uap');?></b></div>
								<textarea name="uap_register_pass_min_char_msg" class="uap-dashboard-textarea"><?php echo uap_correct_text($data['metas']['uap_register_pass_min_char_msg']);?></textarea>
								<div class="uap-dashboard-mini-msg-alert"><?php esc_html_e('Where {X} will be the minimum length of password.', 'uap');?></div>
							</div>

							<div>
								<div class="uap-labels-special"><b><?php esc_html_e('Error - Password Characters, Digits and minimum one uppercase letter:', 'uap');?></b></div>
								<textarea name="uap_register_pass_let_dig_up_let_msg" class="uap-dashboard-textarea"><?php echo uap_correct_text($data['metas']['uap_register_pass_let_dig_up_let_msg']);?></textarea>
							</div>

							<div>
								<div class="uap-labels-special"><b><?php esc_html_e('Error - Pending User:', 'uap');?></b></div>
								<textarea name="uap_register_pending_user_msg" class="uap-dashboard-textarea"><?php echo uap_correct_text($data['metas']['uap_register_pending_user_msg']);?></textarea>
							</div>

							<div>
								<div class="uap-labels-special"><b><?php esc_html_e('Error - Empty Required Fields:', 'uap');?></b></div>
								<textarea name="uap_register_err_req_fields" class="uap-dashboard-textarea"><?php echo uap_correct_text($data['metas']['uap_register_err_req_fields']);?></textarea>
							</div>

							<div>
								<div class="uap-labels-special"><b><?php esc_html_e('Error - ReCaptcha:', 'uap');?></b></div>
								<textarea name="uap_register_err_recaptcha" class="uap-dashboard-textarea"><?php echo uap_correct_text($data['metas']['uap_register_err_recaptcha']);?></textarea>
							</div>

							<div>
								<div class="uap-labels-special"><b><?php esc_html_e('Error - TOS:', 'uap');?></b></div>
								<textarea name="uap_register_err_tos" class="uap-dashboard-textarea"><?php echo uap_correct_text($data['metas']['uap_register_err_tos']);?></textarea>
							</div>

							<div>
								<div class="uap-labels-special"><b><?php esc_html_e('Success Message:', 'uap');?></b></div>
								<textarea name="uap_register_success_meg" class="uap-dashboard-textarea"><?php echo uap_correct_text($data['metas']['uap_register_success_meg']);?></textarea>
							</div>
						</div>
					</div>
				</div>
						<div id="uap_save_changes" class="uap-submit-form">
							<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" onClick="" class="button button-primary button-large" />
						</div>
					</div>
				</div>
			</form>
