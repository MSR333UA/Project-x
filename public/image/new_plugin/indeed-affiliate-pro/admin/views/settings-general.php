<div class="uap-wrapper">
<form  method="post">

	<input type="hidden" name="uap_admin_forms_nonce" value="<?php echo wp_create_nonce( 'uap_admin_forms_nonce' );?>" />

<div class="uap-stuffbox">
	<h3 class="uap-h3"><?php esc_html_e('General Settings', 'uap');?></h3>
	<div class="inside">
		<div class="uap-form-line">
		<div class="uap-inside-item">
			<div class="row">
				<div class="col-xs-6">
					<h2><?php esc_html_e('Which Amount should be used for Referral calculation?', 'uap');?></h2>
					<p><?php esc_html_e('If there are multiple Amounts set for the same action, like Ranks&Offers or multiple Offers decide which one will be taken in consideration', 'uap');?></p>

							<select name="uap_referral_offer_type" class="form-control m-bot15"><?php
							$types = array('lowest'=>esc_html__('Lowest Amount', 'uap'), 'biggest'=>esc_html__('Biggest Amount', 'uap'));
							foreach ($types as $key=>$value){
								$selected = ($key==$data['metas']['uap_referral_offer_type']) ? 'selected' : '';
								?>
								<option value="<?php echo $key?>" <?php echo $selected;?>><?php echo $value;?></option>
								<?php
							}
						?></select>
					
				</div>
			</div>
		</div>
		<div class="uap-inside-item">
			<div class="row">
				<div class="col-xs-6">
					<h2><?php esc_html_e('Redirect', 'uap');?></h2>
					<p><?php esc_html_e('Redirect Same Page Without URL parameters:', 'uap');?></p>
					<label class="uap_label_shiwtch uap-switch-button-margin">
						<?php $checked = ($data['metas']['uap_redirect_without_param']) ? 'checked' : '';?>
						<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_redirect_without_param');" <?php echo $checked;?> />
						<div class="switch uap-display-inline"></div>
					</label>
					<input type="hidden" name="uap_redirect_without_param" value="<?php echo $data['metas']['uap_redirect_without_param'];?>" id="uap_redirect_without_param" />
				</div>
			</div>
		</div>

		<div class="uap-inside-item">
			<div class="row">
				<div class="col-xs-4">
				<h4><?php esc_html_e('Affiliate Link Settings', 'uap');?></h4>
				<br/>
				<p><?php esc_html_e('Set the Affiliate Link Variable name', 'uap');?></p>
					<div class="form-group">
						<input type="text" class="form-control" value="<?php echo $data['metas']['uap_referral_variable'];?>" name="uap_referral_variable" />
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-xs-4">
				<h4><?php esc_html_e('Base Affiliate Link', 'uap');?></h4>
				<br/>
					<div class="form-group">
						<?php if (empty($data['metas']['uap_referral_custom_base_link'])){
							 $data['metas']['uap_referral_custom_base_link'] = get_home_url();
						}?>
						<input type="text" class="form-control" onBlur="uapCheckBaseReferralLink(this.value, '<?php echo get_site_url();?>');" value="<?php echo $data['metas']['uap_referral_custom_base_link'];?>" name="uap_referral_custom_base_link" />
					</div>
					<p id="base_referral_link_alert"><?php esc_html_e('Please insert a link from the website on which this plugin is installed.
Do not enter a link from a different website.', 'uap');?></p>
				</div>
			</div>

		</div>
		<div class="uap-inside-item">
			<div class="row">
				<div class="col-xs-4">
				<p class="uap-labels-special"><?php esc_html_e('Affiliate Link Format:', 'uap');?></p>
				<select name="uap_default_ref_format" class="form-control m-bot15"><?php
				$referral_format = array('id' => 'Affiliate ID', 'username'=>'UserName');
				foreach ($referral_format as $k=>$v){
					$selected = ($data['metas']['uap_default_ref_format']==$k) ? 'selected' : '';
					?>
					<option value="<?php echo $k;?>" <?php echo $selected;?> ><?php echo $v;?></option>
					<?php
				}
				?></select>

				</div>
			</div>
		</div>

		<div class="uap-inside-item">
			<div class="row">
				<div class="col-xs-4">
						<p class="uap-labels-special"><?php esc_html_e('Search into URL for both affiliate link format:', 'uap');?></p>
						<label class="uap_label_shiwtch uap-switch-button-margin">
							<?php $checked = ($data['metas']['uap_search_into_url_for_affid_or_username']) ? 'checked' : '';?>
							<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_search_into_url_for_affid_or_username');" <?php echo $checked;?> />
							<div class="switch uap-display-inline"></div>
						</label>
						<input type="hidden" name="uap_search_into_url_for_affid_or_username" value="<?php echo $data['metas']['uap_search_into_url_for_affid_or_username'];?>" id="uap_search_into_url_for_affid_or_username" />
				</div>
			</div>
		</div>

		<div class="uap-inside-item">
			<div class="row">
				<div class="col-xs-4">
				<div class="input-group">
					<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Cookie Expiration:', 'uap');?></span>
					<input type="number" min="1" class="form-control" value="<?php echo $data['metas']['uap_cookie_expire'];?>" name="uap_cookie_expire"/>
					<div class="input-group-addon"> <?php esc_html_e("Days", 'uap');?></div>
				</div>

				</div>
			</div>
		</div>
		<?php if ( is_multisite() ){?>
		<div class="uap-inside-item">
			<div class="row">
				<div class="col-xs-4">
						<p class="uap-labels-special"><?php esc_html_e('Cookie Sharing over all WP MultiSites', 'uap');?></p>
						<label class="uap_label_shiwtch uap-switch-button-margin">
							<?php $checked = ($data['metas']['uap_cookie_sharing']) ? 'checked' : '';?>
							<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_cookie_sharing');" <?php echo $checked;?> />
							<div class="switch uap-display-inline"></div>
						</label>
						<input type="hidden" name="uap_cookie_sharing" value="<?php echo $data['metas']['uap_cookie_sharing'];?>" id="uap_cookie_sharing" />
				</div>
			</div>
		</div>
	<?php }?>

	<div class="uap-inside-item">
		<div class="row">
			<div class="col-xs-4">
			<h4><?php esc_html_e('Affiliate Link URL Blacklist', 'uap');?></h4>
			<br/>
			<p><?php esc_html_e('If you wish to block specific affiliates’ websites from generating referrals for any reason, you can do it by placing here their website urls. Place one URL per line.', 'uap');?></p>
				<div class="form-group">
					<textarea class="form-control"  name="uap_blocked_referers"><?php echo $data['metas']['uap_blocked_referers'];?></textarea>
				</div>
			</div>
		</div>
	</div>

		<div class="uap-inside-item">
			<div class="row">
				<div class="col-xs-4">
				<h4><?php esc_html_e('Campaign Settings', 'uap');?></h4>
				<br/>
				<p><?php esc_html_e('Set the Campaign Variable name', 'uap');?></p>
					<div class="form-group">
						<input type="text" class="form-control" value="<?php echo $data['metas']['uap_campaign_variable'];?>" name="uap_campaign_variable"  />
					</div>
				</div>
			</div>
		</div>

		<div class="uap-inside-item">
			<div class="row">
				<div class="col-xs-4">
					<h4><?php esc_html_e('Amount Value Settings', 'uap');?></h4>
					<div class="uap-form-line">
						<span class="uap-labels-special"><?php esc_html_e('Currency:', 'uap');?></span>
						<select name="uap_currency" class="form-control m-bot15"><?php
							$currency = uap_get_currencies_list();
							foreach ($currency as $k=>$v){
								$selected = ($k==$data['metas']['uap_currency']) ? 'selected' : '';
								?>
								<option value="<?php echo $k;?>" <?php echo $selected;?> ><?php echo $v;?></option>
								<?php
							}
						?></select>
					</div>
					<div class="uap-form-line">
						<span class="uap-labels-special"><?php esc_html_e('Currency position:', 'uap');?></span>
						<select name="uap_currency_position" class="form-control m-bot15"><?php
							$positions = array('left' => esc_html__('Left', 'uap'), 'right' => esc_html__('Right', 'uap'));
							foreach ($positions as $k=>$v){
								$selected = ($k==$data['metas']['uap_currency_position']) ? 'selected' : '';
								?>
								<option value="<?php echo $k;?>" <?php echo $selected;?> ><?php echo $v;?></option>
								<?php
							}
						?></select>
					</div>
					<div class="uap-form-line">
						<span class="uap-labels-special"><?php esc_html_e('Thousands Separator', 'uap');?></span>
						<input type="text" value="<?php echo $data['metas']['uap_thousands_separator'];?>" name="uap_thousands_separator" class="form-control" />
					</div>

					<div class="uap-form-line">
						<span class="uap-labels-special"><?php esc_html_e('Decimals Separator', 'uap');?></span>
						<input type="text" value="<?php echo $data['metas']['uap_decimals_separator'];?>" name="uap_decimals_separator" class="form-control" />
					</div>

					<div class="uap-form-line">
						<span class="uap-labels-special"><?php esc_html_e('Number of Decimals', 'uap');?></span>
						<input type="number" min="0" value="<?php echo $data['metas']['uap_num_of_decimals'];?>" name="uap_num_of_decimals" class="form-control" />
					</div>

					<div class="uap-form-line">
						<span class="uap-labels-special"><?php esc_html_e('Exclude Shipping', 'uap');?></span>
						<label class="uap_label_shiwtch uap-switch-button-margin">
							<?php $checked = ($data['metas']['uap_exclude_shipping']) ? 'checked' : '';?>
							<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_exclude_shipping');" <?php echo $checked;?> />
							<div class="switch uap-display-inline"></div>
						</label>
						<input type="hidden" name="uap_exclude_shipping" value="<?php echo $data['metas']['uap_exclude_shipping'];?>" id="uap_exclude_shipping" />
					</div>

					<div class="uap-form-line">
						<span class="uap-labels-special"><?php esc_html_e('Exclude Tax', 'uap');?></span>
						<label class="uap_label_shiwtch uap-switch-button-margin">
							<?php $checked = ($data['metas']['uap_exclude_tax']) ? 'checked' : '';?>
							<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_exclude_tax');" <?php echo $checked;?> />
							<div class="switch uap-display-inline"></div>
						</label>
						<input type="hidden" name="uap_exclude_tax" value="<?php echo $data['metas']['uap_exclude_tax'];?>" id="uap_exclude_tax" />
					</div>

				</div>
			</div>
		</div>



		<div class="uap-inside-item">
			<div class="row">
				<div class="col-xs-6">
					<h4><?php esc_html_e('Default Country', 'uap');?></h4>
					<p><?php esc_html_e('Choose a default country for Affiliates submission form. If none is chosen default WordPress Locale will be used instead', 'uap');?></p>
					<div class="uap-form-line">
							<select name="uap_defaultcountry" class="form-control m-bot15">
							<option value="" >....</option>
							<?php
							$types = uap_get_countries();
							foreach ($types as $key=>$value){
								$key = strtolower($key);
								$selected = ($key==$data['metas']['uap_defaultcountry']) ? 'selected' : '';
								?>
								<option value="<?php echo $key?>" <?php echo $selected;?>><?php echo $value;?></option>
								<?php
							}
						?></select>
				</div>
			</div>
		</div>


		<div class="uap-inside-item">
			<div class="row">
				<div class="col-xs-6">
					<h2><?php esc_html_e('Automatically Affiliate', 'uap');?></h2>
					<p><?php esc_html_e('All new Users become Affiliates', 'uap');?></p>
					<label class="uap_label_shiwtch uap-switch-button-margin">
						<?php $checked = ($data['metas']['uap_all_new_users_become_affiliates']) ? 'checked' : '';?>
						<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_all_new_users_become_affiliates');" <?php echo $checked;?> />
						<div class="switch uap-display-inline"></div>
					</label>
					<input type="hidden" name="uap_all_new_users_become_affiliates" value="<?php echo $data['metas']['uap_all_new_users_become_affiliates'];?>" id="uap_all_new_users_become_affiliates" />
				</div>
			</div>
		</div>

		<div class="uap-inside-item">
			<div class="row">
				<div class="col-xs-6">
					<h2><?php esc_html_e('Empty Referrals', 'uap');?></h2>
					<p><?php esc_html_e('Save Empty Referrals', 'uap');?></p>
					<label class="uap_label_shiwtch uap-switch-button-margin">
						<?php $checked = ($data['metas']['uap_empty_referrals_enable']) ? 'checked' : '';?>
						<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_empty_referrals_enable');" <?php echo $checked;?> />
						<div class="switch uap-display-inline"></div>
					</label>
					<input type="hidden" name="uap_empty_referrals_enable" value="<?php echo $data['metas']['uap_empty_referrals_enable'];?>" id="uap_empty_referrals_enable" />
				</div>
			</div>
		</div>

		<div id="uap_save_changes" class="uap-submit-form">
			<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
		</div>
	</div>
</div>
</div>
</div>
</form>
</div>
