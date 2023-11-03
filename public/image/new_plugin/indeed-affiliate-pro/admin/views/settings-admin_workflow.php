<div class="uap-wrapper">
<form  method="post">

	<input type="hidden" name="uap_admin_forms_nonce" value="<?php echo wp_create_nonce( 'uap_admin_forms_nonce' );?>" />

	<div class="uap-stuffbox">
		<h3 class="uap-h3"><?php esc_html_e('Admin Workflow Settings', 'uap');?></h3>
		<div class="inside">
			<div class="uap-form-line">
			<div class="uap-inside-item">
				<div class="row">
					<div class="col-xs-4">
						<h2><?php esc_html_e('Updates', 'uap');?></h2>
						<div class="uap-form-line">
							<span class="uap-labels-special"><?php esc_html_e('Update Affiliates Rank:', 'uap');?></span>
							<select name="uap_update_ranks_interval" class="form-control m-bot15"><?php
								$values = array(
													'hourly' => esc_html__('Hourly', 'uap'),
													'twicedaily' => esc_html__('At every 12hours', 'uap'),
													'daily' => esc_html__('Daily', 'uap'),
								);
								foreach ($values as $k=>$v){
									$selected = ($data['metas']['uap_update_ranks_interval']==$k) ? 'selected' : '';
									?>
									<option value="<?php echo $k;?>" <?php echo $selected;?> ><?php echo $v;?></option>
									<?php
								}
							?></select>
						</div>

						<div class="uap-form-line">
							<span class="uap-labels-special"><?php esc_html_e('Update Payments Status:', 'uap');?></span>
							<select name="uap_update_payments_status" class="form-control m-bot15"><?php
								$values = array(
													'hourly' => esc_html__('Hourly', 'uap'),
													'twicedaily' => esc_html__('At every 12hours', 'uap'),
													'daily' => esc_html__('Daily', 'uap'),
								);
								foreach ($values as $k=>$v){
									$selected = ($data['metas']['uap_update_payments_status']==$k) ? 'selected' : '';
									?>
									<option value="<?php echo $k;?>" <?php echo $selected;?> ><?php echo $v;?></option>
									<?php
								}
							?></select>
						</div>
					</div>
				</div>
			</div>
			<div class="uap-inside-item">
				<div class="row">
					<div class="col-xs-12">
						<h2><?php esc_html_e('Keep Referral Status as Unverified', 'uap');?></h2>
						<div class="uap-form-line">
							<span class="uap-labels-special"><?php esc_html_e("Don't change the Referral Status to Verified", 'uap');?></span>
							<div class="uap-form-line">
								<label class="uap_label_shiwtch uap-switch-button-margin">
									<?php $checked = ($data['metas']['uap_workflow_referral_status_dont_automatically_change']) ? 'checked' : '';?>
									<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_workflow_referral_status_dont_automatically_change');" <?php echo $checked;?> />
									<div class="switch uap-display-inline"></div>
								</label>
								<input type="hidden" name="uap_workflow_referral_status_dont_automatically_change" value="<?php echo $data['metas']['uap_workflow_referral_status_dont_automatically_change'];?>" id="uap_workflow_referral_status_dont_automatically_change" />
							</div>
						</div>
					</div>

					<div class="col-xs-12">
						<h2><?php esc_html_e('Show Dashboard Notifications', 'uap');?></h2>
						<div class="uap-form-line">
							<span class="uap-labels-special"><?php esc_html_e("New Affiliates & Referrals", 'uap');?></span>
							<div class="uap-form-line">
								<label class="uap_label_shiwtch uap-switch-button-margin">
									<?php $checked = ($data['metas']['uap_admin_workflow_dashboard_notifications']) ? 'checked' : '';?>
									<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_admin_workflow_dashboard_notifications');" <?php echo $checked;?> />
									<div class="switch uap-display-inline"></div>
								</label>
								<input type="hidden" name="uap_admin_workflow_dashboard_notifications" value="<?php echo $data['metas']['uap_admin_workflow_dashboard_notifications'];?>" id="uap_admin_workflow_dashboard_notifications" />
							</div>
						</div>
					</div>

					<div class="col-xs-12">
							<h2><?php esc_html_e('Uninstall Settings:', 'uap');?></h2>
							<div class="uap-form-line">
								<span class="uap-labels-special"><?php esc_html_e("Keep data after delete plugin:", 'uap');?></span>
								<label class="uap_label_shiwtch uap-switch-button-margin">
									<?php $checked = ($data['metas']['uap_keep_data_after_delete']) ? 'checked' : '';?>
									<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_keep_data_after_delete');" <?php echo $checked;?> />
									<div class="switch uap-display-inline"></div>
								</label>
								<input type="hidden" name="uap_keep_data_after_delete" value="<?php echo $data['metas']['uap_keep_data_after_delete'];?>" id="uap_keep_data_after_delete" />
							</div>
					</div>


				</div>
			</div>

			<div id="uap_save_changes" class="uap-submit-form">
				<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
			</div>
		</div>
		</div>
	</div>
</form>
</div>

<?php
