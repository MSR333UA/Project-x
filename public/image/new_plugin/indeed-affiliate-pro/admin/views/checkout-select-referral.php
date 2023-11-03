<form  method="post">
	<div class="uap-stuffbox">
		<h3 class="uap-h3"><?php esc_html_e('Fair Checkout Reward', 'uap');?><span class="uap-admin-need-help"><i class="fa-uap fa-help-uap"></i><a href="https://help.wpindeed.com/ultimate-affiliate-pro/knowledge-base/fair-checkout-reward/" target="_blank"><?php esc_html_e('Need Help?', 'uap');?></a></span></h3>
		<div class="inside">
			<div class="uap-form-line">
			<div class="row">
				<div class="col-xs-8">
					<h2><?php esc_html_e('Activate/Hold  Checkout Reward', 'uap');?></h2>
					<p><?php esc_html_e('Once activated the customer will have the option to select an affiliate for commission during the checkout process.', 'uap');?></p>


						<label class="uap_label_shiwtch uap-switch-button-margin">
							<?php $checked = ($data['metas']['uap_checkout_select_referral_enable']) ? 'checked' : '';?>
							<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_checkout_select_referral_enable');" <?php echo $checked;?> />
							<div class="switch uap-display-inline"></div>
						</label>
						<input type="hidden" name="uap_checkout_select_referral_enable" value="<?php echo $data['metas']['uap_checkout_select_referral_enable'];?>" id="uap_checkout_select_referral_enable" />
				</div>
			</div>

			<div class="row">
				<div class="col-xs-5">
					<h4><?php esc_html_e('Form Label field', 'uap');?></h4>
					<p><?php esc_html_e('The label name for the additional field available inside the checkout form.', 'uap');?></p>
					<div class="input-group">
					<label class="input-group-addon"><?php esc_html_e('Label', 'uap');?></label>
						<input type="text" class="form-control" name="uap_checkout_select_referral_label" value="<?php echo $data['metas']['uap_checkout_select_referral_label'];?>" />
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-xs-4">
					<h2><?php esc_html_e('Field Settings', 'uap');?></h2>
					<div class="uap-form-line">
						<h4><?php esc_html_e('Selection Type', 'uap');?></h4>
						<select name="uap_checkout_select_referral_s_type" class="form-control m-bot15"><?php
							foreach (array(1 => esc_html__('Client select Affiliate from list', 'uap'), 2 => esc_html__('Client write the Username of Affiliate', 'uap')) as $k=>$v):
								$selected = ($data['metas']['uap_checkout_select_referral_s_type']==$k) ? 'selected' : '';
								?>
								<option value="<?php echo $k;?>" <?php echo $selected;?> ><?php echo $v;?></option>
								<?php
							endforeach;
						?></select>
					</div>
					<div class="uap-form-line">
						<h4><?php esc_html_e('Affiliate Name Display', 'uap');?></h4>
						<p><?php esc_html_e('How affiliates will be displayed.', 'uap');?></p>
						<select name="uap_checkout_select_referral_name" class="form-control m-bot15"><?php
							foreach (array('user_login' => esc_html__('Username', 'uap'), 'display_name' => esc_html__('Display Name', 'uap'), 'user_nicename' => esc_html__('User Nicename', 'uap') ) as $k=>$v):
								$selected = ($data['metas']['uap_checkout_select_referral_name']==$k) ? 'selected' : '';
								?>
								<option value="<?php echo $k;?>" <?php echo $selected;?> ><?php echo $v;?></option>
								<?php
							endforeach;
						?></select>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-xs-4">
					<div class="uap-form-line">
						<h4><?php esc_html_e('Affiliate List', 'uap');?></h4>
						<p><?php esc_html_e('Choose specific affiliates to show up in checkout selection if you do not want to display all of them.', 'uap');?></p>
						<input type="text" id="usernames_search" class="form-control"/>
						<?php $value = (is_array($data['metas']['uap_checkout_select_affiliate_list'])) ? implode(',', $data['metas']['uap_checkout_select_affiliate_list']) : $data['metas']['uap_checkout_select_affiliate_list'];?>
						<input type="hidden" value="<?php echo $value;?>" name="uap_checkout_select_affiliate_list" id="usernames_search_hidden" />
						<div id="uap_username_search_tags"><?php
							if (!empty($aff_list)){
								foreach ($aff_list as $value){
									if ($value){
										$id = 'uap_username_tag_' . $value;
										?>
										<div id="<?php echo $id;?>" class="uap-tag-item"><?php echo $usernames[$value];?><div class="uap-remove-tag" onclick="uapRemoveTag('<?php echo $value;?>', '#<?php echo $id;?>', '#usernames_search_hidden');" title="<?php esc_html_e('Removing tag', 'uap');?>">x</div></div>
										<?php
										}
									}
								}
							?></div>
					</div>

				</div>
			</div>



			<div class="row">
				<div class="col-xs-5">
				<h2><?php esc_html_e('Additional Settings', 'uap');?></h2>
					<div class="uap-form-line">
						<h4><?php esc_html_e('Rewrite current Affiliate', 'uap');?></h4>
						<p><?php esc_html_e('If there is an affiliate already assigned for the current visitor/customer this one can be ignored. If not, the checkout option will not show up.', 'uap');?></p>
						<label class="uap_label_shiwtch uap-switch-button-margin">
							<?php $checked = ($data['metas']['uap_checkout_select_referral_rewrite']) ? 'checked' : '';?>
							<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_checkout_select_referral_rewrite');" <?php echo $checked;?> />
							<div class="switch uap-display-inline"></div>
						</label>
						<input type="hidden" name="uap_checkout_select_referral_rewrite" value="<?php echo $data['metas']['uap_checkout_select_referral_rewrite'];?>" id="uap_checkout_select_referral_rewrite" />
					</div>


					<div class="uap-form-line">
						<h4><?php esc_html_e('Require select Affiliate', 'uap');?></h4>
						<p><?php esc_html_e('Force the customer to select an affiliate to continue the checkout process. The additional field will be set as required.', 'uap');?></p>

						<label class="uap_label_shiwtch uap-switch-button-margin">
							<?php $checked = ($data['metas']['uap_checkout_select_referral_require']) ? 'checked' : '';?>
							<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_checkout_select_referral_require');" <?php echo $checked;?> />
							<div class="switch uap-display-inline"></div>
						</label>
						<input type="hidden" name="uap_checkout_select_referral_require" value="<?php echo $data['metas']['uap_checkout_select_referral_require'];?>" id="uap_checkout_select_referral_require" />
					</div>
					</div>
				</div>
					<div class="row">
						<div class="col-xs-4">
						</div>
					</div>

			<div id="uap_save_changes" class="uap-submit-form">
				<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
			</div>
		</div>
	</div>
</div>
</form>
