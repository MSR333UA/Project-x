<div class="uap-wrapper">
<form  method="post">

	<input type="hidden" name="uap_admin_forms_nonce" value="<?php echo wp_create_nonce( 'uap_admin_forms_nonce' );?>" />

	<div class="uap-stuffbox">
		<h3 class="uap-h3"><?php esc_html_e('Public Workflow Settings', 'uap');?></h3>
		<div class="inside">
			<div class="uap-form-line">
			<div class="uap-inside-item">
				<div class="row">

					<div class="col-xs-12">
						<h2><?php esc_html_e('Hide Payment Warnings', 'uap');?></h2>
						<div class="uap-form-line">
							<label class="uap_label_shiwtch uap-switch-button-margin">
								<?php $checked = ($data['metas']['uap_hide_payments_warnings']) ? 'checked' : '';?>
								<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_hide_payments_warnings');" <?php echo $checked;?> />
								<div class="switch uap-display-inline"></div>
							</label>
							<input type="hidden" name="uap_hide_payments_warnings" value="<?php echo $data['metas']['uap_hide_payments_warnings'];?>" id="uap_hide_payments_warnings" />
							<div>
							<label class="uap-labels-special"><?php esc_html_e('Payout Service notice Message', 'uap');?></label>
							<input type="text" name="uap_payments_warnings_message" value="<?php echo stripslashes( $data['metas']['uap_payments_warnings_message'] );?>" id="uap_payments_warnings_message" />
						</div>
						</div>
					</div>

					<div class="col-xs-12">
						<h4><?php esc_html_e('Default Payment System', 'uap');?></h4>
						<p><?php esc_html_e('When a new Affiliate SignUp this will be his payment system.', 'uap');?></p>
						<div class="uap-form-line">
							<select name="uap_default_payment_system"><?php
								if ($data['payment_types']){
									foreach ($data['payment_types'] as $k=>$v):
										$selected = ($k==$data['metas']['uap_default_payment_system']) ? 'selected' : '';
										?>
										<option value="<?php echo $k;?>" <?php echo $selected;?> ><?php echo $v;?></option>
										<?php
									endforeach;
								}
							?></select>
						</div>
					</div>

					<div class="col-xs-12">
						<h4><?php esc_html_e('Disable Bank Transfer', 'uap');?></h4>
						<p><?php esc_html_e('Affiliates will not be able to use Bank Transfer anymore.', 'uap');?></p>
						<div class="uap-form-line">
							<label class="uap_label_shiwtch uap-switch-button-margin">
								<?php $checked = ($data['metas']['uap_disable_bt_payment_system']) ? 'checked' : '';?>
								<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_disable_bt_payment_system');" <?php echo $checked;?> />
								<div class="switch uap-display-inline"></div>
							</label>
							<input type="hidden" name="uap_disable_bt_payment_system" value="<?php echo $data['metas']['uap_disable_bt_payment_system'];?>" id="uap_disable_bt_payment_system" />
						</div>
					</div>

					<div class="col-xs-12">
						<h4><?php esc_html_e('Sources custom names', 'uap');?></h4>
						<?php
								$types = [
													'ump' => 'Ultimate Membership Pro',
													'ulp' => 'Ultimate Learning Pro',
													'woo' => 'WooCommerce',
													'edd' => 'Easy Download Digital',
													'bonus' => 'Bonus',
													'mlm' => 'MLM',
													'user_signup' => 'User SignUp',
													'landing_commissions' => 'Landing commisions',
													'ppc' => 'Pay per Click',
													'cpm' => 'CPM Commission',
								];
						?>
						<?php foreach ($types as $name=>$label):?>
								<div class="uap-form-line">
										<label class="uap-labels-special"><?php echo $label;?></label>
										<input type="text" value="<?php echo $data['metas']['uap_custom_source_name_' . $name];?>" name="<?php echo 'uap_custom_source_name_' . $name;?>" />
								</div>
						<?php endforeach;?>
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
