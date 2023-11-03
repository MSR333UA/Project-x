<form  method="post">
	<div class="uap-stuffbox">
		<h3 class="uap-h3"><?php esc_html_e('Periodic Reports', 'uap');?><span class="uap-admin-need-help"><i class="fa-uap fa-help-uap"></i><a href="https://help.wpindeed.com/ultimate-affiliate-pro/knowledge-base/periodical-reports/" target="_blank"><?php esc_html_e('Need Help?', 'uap');?></a></span></h3>
		<div class="inside">
			<div class="uap-form-line">
			<div class="row">
				<div class="col-xs-7">
					<h2><?php esc_html_e('Activate/Hold Periodic Reports', 'uap');?></h2>
					<p><?php esc_html_e('If this module is activated, affiliates will receive periodical reports about their affiliate account and rewards. Each affiliate may decide the frequency of these reports (daily, weekly, monthly) from his "Account Page".', 'uap');?></p>
					<label class="woo_account_page_enable uap-switch-button-margin">
					<?php $checked = ($data['metas']['uap_periodically_reports_enable']) ? 'checked' : '';?>
					<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_periodically_reports_enable');" <?php echo $checked;?> />
					<div class="switch uap-display-inline"></div>
					</label>
					<input type="hidden" name="uap_periodically_reports_enable" value="<?php echo $data['metas']['uap_periodically_reports_enable'];?>" id="uap_periodically_reports_enable" />
				</div>
			</div>

			<div class="row">
				<div class="col-xs-6">
					<h4><?php esc_html_e('Report Subject', 'uap');?></h4>
					<input type="text" name="uap_periodically_reports_subject" value="<?php echo $data['metas']['uap_periodically_reports_subject'];?>" />
				</div>
			</div>

			<div class="row">
				<div class="col-xs-12">
					<h4><?php esc_html_e('Report Content', 'uap');?></h4>
					<div class="uap-wp_editor uap-wp-editor-box">
					<?php wp_editor(stripslashes($data['metas']['uap_periodically_reports_content']), 'uap_periodically_reports_content', array('textarea_name'=>'uap_periodically_reports_content', 'editor_height'=>500));?>
					</div>
					<div class="uap-wp-editor-constants">
						<?php echo "<h4>" . esc_html__('Referral Reports constants', 'uap') . "</h4>"; ?>
						<?php foreach ($data['reports_constants'] as $key=>$value) : ?>
							<div><?php echo '<span><strong>'.$value . '</strong></span> : ' . $key;?></div>
						<?php endforeach; ?>
						<?php
						echo "<h4>" . esc_html__('Native Fields constants', 'uap') . "</h4>";
							$constants = array(	"{username}",
												"{first_name}",
												"{last_name}",
												"{user_id}",
												"{user_email}",
												"{account_page}",
												"{login_page}",
												"{blogname}",
												"{blogurl}",
												"{siteurl}",
												'{rank_id}',
												'{rank_name}',
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
				</div>
			</div>

			<div class="row">
				<div class="col-xs-8">
					<h4><?php esc_html_e('Send Notification Time', 'uap');?></h4>
					<p><?php esc_html_e('Decide when it is the best time of day for your website to start managing email reports. The script runs daily on the back-end and checks the report period for each affiliate and how much time has passed since the last report. Based on this new reports are managed automatically by the system.', 'uap');?></p>
					<select name="uap_periodically_reports_cron_hour"><?php
						for ($i=0; $i<24; $i++){
							$selected = ($data['metas']['uap_periodically_reports_cron_hour']==$i) ? 'selected' : '';
							?>
							<option value="<?php echo $i;?>" <?php echo $selected;?> ><?php
								if ($i<10){
									echo 0;
								}
								echo $i;
							?></option>
							<?php
						}
					?></select>
					<p><strong><?php esc_html_e('Keep in mind that it may be necessary to send a big number of emails and your hosting provider may restrict this action. Be sure that you are able to manage email reports for all of your affiliates.', 'uap');?></strong></p>
				</div>
			</div>

			<div id="uap_save_changes" class="uap-submit-form">
				<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
			</div>
		</div>
		</div>
	</div>
</form>
