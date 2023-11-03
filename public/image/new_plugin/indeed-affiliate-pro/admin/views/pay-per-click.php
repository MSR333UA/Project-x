			<form  method="post">
				<div class="uap-stuffbox">
					<h3 class="uap-h3"><?php esc_html_e('PayPerClick Campaign', 'uap');?></h3>
					<div class="inside">
						<div class="uap-form-line">
						<div class="row">
						<div class="col-xs-4">
							<h2><?php esc_html_e('Activate/Hold PayPerClick Campaign', 'uap');?></h2>
							<p><?php esc_html_e('Affiliates will receive a PPC Referral with flat amount each time a new referred user visit your website.', 'uap');?></p>
							<label class="uap_label_shiwtch uap-switch-button-margin">
								<?php $checked = ($data['metas']['uap_pay_per_click_enabled']) ? 'checked' : '';?>
								<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_pay_per_click_enabled');" <?php echo $checked;?> />
								<div class="switch uap-display-inline"></div>
							</label>
							<input type="hidden" name="uap_pay_per_click_enabled" value="<?php echo $data['metas']['uap_pay_per_click_enabled'];?>" id="uap_pay_per_click_enabled" />
						</div>
						</div>

						<div class="row">
							<div class="col-xs-5">
							<?php if (!empty($data['rank_list'])) :?>
							<h2><?php esc_html_e('PPC Amount For Each Rank', 'uap');?></h2>
								<p><?php esc_html_e('Set a special PPC amount for each rank. This option will also become available in the "Rank Settings" page.', 'uap');?></p>
							<?php foreach ($data['rank_list'] as $id=>$label) :?>
								<div class="row">
									<div class="col-xs-12">
									<div class="input-group">
										<span class="input-group-addon"><?php echo $label;?></span>
									 		<input type="number" class="form-control uap-input-number" min="0" step='<?php echo uapInputNumerStep();?>'  value="<?php echo $data['rank_value_array'][$id];?>" name="<?php echo "pay_per_click_value[$id]";?>" />
									 		<div class="input-group-addon"><?php echo $data['amount_types']['flat'];?></div>
										</div>
									</div>
								</div>
								<?php endforeach;?>
							<?php endif;?>
							</div>
						</div>
						<div class="uap-inside-item">
							<div class="row">
								<div class="col-xs-5">
									<h2><?php esc_html_e('Default Referral Status', 'uap');?></h2>
									<select name="uap_pay_per_click_default_referral_sts" class="form-control m-bot15"><?php
										foreach (array(1 => esc_html__('Unverified', 'uap'), 2 => esc_html__('Verified', 'uap')) as $k=>$v){
											$selected = ($data['metas']['uap_pay_per_click_default_referral_sts']==$k) ? 'selected' : '';
											?>
											<option value="<?php echo $k;?>" <?php echo $selected;?> ><?php echo $v;?></option>
											<?php
										}
									?></select>
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

<?php
