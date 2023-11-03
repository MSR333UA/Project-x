		<div class="uap-page-title">Ultimate Affialiates Pro -
			<span class="second-text"><?php esc_html_e('Login Form', 'uap');?></span>
		</div>
			<div class="uap-stuffbox">
				<div class="uap-shortcode-display">
					[uap-login-form]
				</div>
			</div>
			<form method="post" >

				<input type="hidden" name="uap_admin_forms_nonce" value="<?php echo wp_create_nonce( 'uap_admin_forms_nonce' );?>" />
				<div class="row">
					<div class="col-xs-6">
					<div class="uap-stuffbox">
						<h3 class="uap-h3"><?php esc_html_e('Showcase Display', 'uap');?></h3>
						<div class="inside">
						  <div class="uap-register-select-template">
						  <?php esc_html_e('Login Template:', 'uap');?>
							<select name="uap_login_template" id="uap_login_template" onChange="uapLoginPreview();">
							<?php
								foreach ($data['login_templates'] as $k=>$value){
									echo '<option value="uap-login-template-'.$k.'" '. ($data['metas']['uap_login_template']=='uap-login-template-'.$k ? 'selected': '') .'>'.$value.'</option>';
								}
							?>
							</select>
						 </div>
						 <div>
							<div id="uap-preview-login"></div>
						</div>
							<div id="uap_save_changes" class="uap-wrapp-submit-bttn">
								<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="uap-form-element uap-form-element-submit button button-primary button-large" />
							</div>
						</div>
					</div>


				</div>
			   <div class="col-xs-6">
				<div class="uap-stuffbox">
					<h3 class="uap-h3"><?php esc_html_e('Additional Display Options', 'uap');?></h3>
					<div class="inside">
							<div class="uap-form-line uap-no-border">
									<input type="checkbox" class="uap-checkbox" onClick="checkAndH(this, '#uap_login_remember_me');uapLoginPreview();" <?php echo ($data['metas']['uap_login_remember_me']==1) ? 'checked' : '';?>/>
									<input type="hidden" name="uap_login_remember_me" value="<?php echo $data['metas']['uap_login_remember_me'];?>" id="uap_login_remember_me"/>
									<span><strong><?php esc_html_e('Remember Me', 'uap');?></strong></span>
							</div>
							<div class="uap-form-line uap-no-border">
									<input type="checkbox" class="uap-checkbox" onClick="checkAndH(this, '#uap_login_register');uapLoginPreview();" <?php echo ($data['metas']['uap_login_register']==1) ? 'checked' : '';?>/>
									<input type="hidden" name="uap_login_register" value="<?php echo $data['metas']['uap_login_register'];?>" id="uap_login_register"/>
									<span><strong><?php esc_html_e('Register Link', 'uap');?></strong></span>
							</div>
							<div class="uap-form-line uap-no-border">
									<input type="checkbox" class="uap-checkbox" onClick="checkAndH(this, '#uap_login_pass_lost');uapLoginPreview();" <?php echo ($data['metas']['uap_login_pass_lost']==1) ? 'checked' : '';?>/>
									<span><strong><?php esc_html_e('Lost your password', 'uap');?></strong></span>
									<input type="hidden" name="uap_login_pass_lost" value="<?php echo $data['metas']['uap_login_pass_lost'];?>" id="uap_login_pass_lost"/>
							</div>
							<div class="uap-form-line uap-no-border">
									<input type="checkbox" class="uap-checkbox" onClick="checkAndH(this, '#uap_login_show_recaptcha');uapLoginPreview();" <?php echo ($data['metas']['uap_login_show_recaptcha']==1) ? 'checked' : '';?>/>
									<span><strong><?php esc_html_e('Show ReCaptcha', 'uap');?></strong></span>
									<input type="hidden" name="uap_login_show_recaptcha" value="<?php echo $data['metas']['uap_login_show_recaptcha'];?>" id="uap_login_show_recaptcha"/>
							</div>
							<div id="uap_save_changes" class="uap-wrapp-submit-bttn">
								<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="uap-form-element uap-form-element-submit button button-primary button-large" />
							</div>
						</div>
				  </div>
				  <div class="uap-stuffbox">
						<h3 class="uap-h3"><?php esc_html_e('Custom CSS', 'uap');?></h3>
						<div class="inside">
							<textarea id="uap_login_custom_css" name="uap_login_custom_css" onBlur="uapLoginPreview();" class="uap-dashboard-textarea"><?php echo stripslashes($data['metas']['uap_login_custom_css']);?></textarea>
							<div id="uap_save_changes" class="uap-wrapp-submit-bttn">
								<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="uap-form-element uap-form-element-submit button button-primary button-large" />
							</div>
						</div>
					</div>
				</div>
			</div>
			</form>
