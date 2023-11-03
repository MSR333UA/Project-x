			<form action="<?php echo $data['form_action_url'];?>" method="post">

				<input type="hidden" name="uap_admin_forms_nonce" value="<?php echo wp_create_nonce( 'uap_admin_forms_nonce' );?>" />

<div class="uap-wrapper">
		<div class="uap-stuffbox">
				<h3 class="uap-h3"><?php esc_html_e('Add/Edit Notification', 'uap');?></h3>
				<div class="inside">
					<div class="uap-form-line">
						<label class="uap-labels-special"><?php esc_html_e('Action:', 'uap');?></label>
						<select name="type" id="notf_type" onChange="uapReturnNotification();">
						<?php foreach ($data['actions_available'] as $k=>$v):?>
							<?php
								switch ($k){
									case 'admin_user_register':
										echo ' <optgroup label="' . esc_html__('Register Process', 'uap') . '">';
										break;
									case 'affiliate_payment_fail':
										echo ' <optgroup label="' . esc_html__('Payments', 'uap') . '">';
										break;
									case 'reset_password_process':
										echo ' <optgroup label="' . esc_html__('Password', 'uap') . '">';
										break;
									case 'affiliate_account_approve':
										echo ' <optgroup label="' . esc_html__('Profile Update', 'uap') . '">';
										break;
									case 'admin_on_aff_change_rank':
										echo ' <optgroup label="' . esc_html__('Admin', 'uap') . '">';
										break;
									case 'email_check':
										echo ' <optgroup label="' . esc_html__('Double E-mail Verification', 'uap') . '">';
										break;
								}
							?>
							<?php $selected = ($k==$data['type']) ? 'selected' : '';?>
							<option value="<?php echo $k;?>" <?php echo $selected;?>><?php echo $v;?></option>
							<?php
								switch ($k){
									case 'register':
									case 'affiliate_payment_complete':
									case 'change_password':
									case 'rank_change':
									case 'admin_affiliate_update_profile':
									case 'email_check_success':
										echo '</optgroup>';
										break;
								}
							?>
						<?php endforeach;?>
						</select>
					</div>
					<div class="uap-form-line">
						<label class="uap-labels-special"><?php esc_html_e('Target Rank:', 'uap')?></label>
						<select name="rank_id">
						<?php foreach ($data['ranks_available'] as $k=>$v):?>
							<?php $selected = ($k==$data['rank_id']) ? 'selected' : '';?>
							<option value="<?php echo $k;?>" <?php echo $selected;?>><?php echo $v;?></option>
						<?php endforeach;?>
						</select>
					</div>
					<div class="uap-form-line">
						<label class="uap-labels-special"><?php esc_html_e('Subject:', 'uap');?></label>
						<input type="text" value="<?php echo $data['subject'];?>" name="subject" id="notf_subject" />
					</div>
					<div class="uap-form-line">
						<label  class="uap-labels-special uap-vertical-align-top" ><?php esc_html_e('Content:', 'uap');?></label>
						<div class="uap-notification-edit-editor">
							<?php wp_editor( $data['message'], 'notf_message', array('textarea_name'=>'message', 'quicktags'=>TRUE) );?>
						</div>
						<div class="uap-notification-edit-constants">
						<?php
							$constants = array(	"{username}",
												"{first_name}",
												"{last_name}",
												"{user_id}",
												'{affiliate_id}',
												"{user_email}",
												"{account_page}",
												"{login_page}",
												"{blogname}",
												"{blogurl}",
												"{siteurl}",
												'{rank_id}',
												'{rank_name}',
												'{NEW_PASSWORD}',
												'{password_reset_link}',
							);
							$extra_constants = uap_get_custom_constant_fields();
							foreach ($constants as $v){
								?>
								<div><?php echo $v;?></div>
								<?php
							}
							echo "<h4>" . esc_html__('Custom Fields constants', 'uap') . "</h4>";
							foreach ($extra_constants as $k=>$v){
								?>
								<div><?php echo $k;?></div>
								<?php
							}
						?>
						</div>

						<div class="uap-clear"></div>

					<div id="uap_save_changes" class="uap-submit-form">
						<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large">
					</div>
				</div>
			</div>
		</div>
						<!-- PUSHOVER -->
						<?php if ($indeed_db->is_magic_feat_enable('pushover')):?>
							<div class="uap-stuffbox">
							<h3 class="uap-h3"><?php esc_html_e('Pushover Notification', 'uap');?></h3>
								<div class="inside">
									<div class="iump-form-line">
										<span class="uap-labels-special"><?php esc_html_e('Pushover Notification', 'uap');?></span>
										<label class="uap_label_shiwtch uap-switch-button-margin">
											<?php $checked = (empty($data['pushover_status'])) ? '' : 'checked';?>
											<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#pushover_status');" <?php echo $checked;?> />
											<div class="switch uap-display-inline"></div>
										</label>
										<input type="hidden" name="pushover_status" value="<?php echo isset($data['pushover_status']) ? $data['pushover_status'] : '';?>" id="pushover_status" />
									</div>

									<div class="uap-form-line">
										<label class="uap-labels-special"><?php esc_html_e('Pushover Message:', 'uap');?></label>
										<textarea name="pushover_message" class="uap-pushover_message" onBlur="uapCheckFieldLimit(1024, this);"><?php echo isset($data['pushover_message']) ? stripslashes($data['pushover_message']) : '';?></textarea>
										<div><i><?php esc_html_e('Only Plain Text and up to ', 'uap');?><strong>1024</strong><?php esc_html_e(' characters are available!', 'uap');?></i></div>
									</div>
									<div id="uap_save_changes" class="uap-submit-form">
										<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large">
									</div>
								</div>
							</div>
						<?php else :?>
							<input type="hidden" name="pushover_message" value=""/>
							<input type="hidden" name="pushover_status" value=""/>
						<?php endif;?>
						<!-- PUSHOVER -->

				<input type="hidden" name="status" value="1" />
				<input type="hidden" name="id" value="<?php echo $data['id'];?>" class="uap-js-add-edit-notification-id" />
</div>
	</form>
