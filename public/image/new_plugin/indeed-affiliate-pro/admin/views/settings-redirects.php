<?php
$pages = uap_get_all_pages();
//getting pages
?>
<div class="uap-wrapper">
			<form  method="post">

				<input type="hidden" name="uap_admin_forms_nonce" value="<?php echo wp_create_nonce( 'uap_admin_forms_nonce' );?>" />

				<div class="uap-stuffbox">
					<h3 class="uap-h3"><?php esc_html_e('Default Redirects', 'uap');?></h3>
					<div class="inside">
						<div class="uap-form-line">
							<span class="uap-labels-special"><?php esc_html_e('After LogOut:', 'uap');?></span>
							<select name="uap_general_logout_redirect">
								<option value="-1" <?php echo ($data['metas']['uap_general_logout_redirect']==-1) ? 'selected' : '';?> ><?php esc_html_e('Do Not Redirect', 'uap');?></option>
								<?php
									$pages_arr = $pages + uap_get_redirect_links_as_arr_for_select();
									if ($pages_arr){
										foreach ($pages_arr as $k=>$v){
											?>
												<option value="<?php echo $k;?>" <?php echo ($data['metas']['uap_general_logout_redirect']==$k) ? 'selected' : '';?> ><?php echo $v;?></option>
											<?php
										}
									}
								?>
							</select>
							<?php echo uap_general_options_print_page_links($data['metas']['uap_general_logout_redirect']);?>
						</div>

						<div class="uap-form-line">
							<span class="uap-labels-special"><?php esc_html_e('After Registration:', 'uap');?></span>
							<select name="uap_general_register_redirect">
								<option value="-1" <?php echo ($data['metas']['uap_general_register_redirect']==-1) ? 'selected' : '';?> ><?php esc_html_e('Do Not Redirect', 'uap');?></option>
								<?php
									$pages_arr = $pages + uap_get_redirect_links_as_arr_for_select();
									if ($pages_arr){
										foreach ($pages_arr as $k=>$v){
											?>
												<option value="<?php echo $k;?>" <?php echo ($data['metas']['uap_general_register_redirect']==$k) ? 'selected' : '';?> ><?php echo $v;?></option>
											<?php
										}
									}
								?>
							</select>
							<?php echo uap_general_options_print_page_links($data['metas']['uap_general_register_redirect']);?>
						</div>

						<div class="uap-form-line">
							<span class="uap-labels-special"><?php esc_html_e('After Login:', 'uap');?></span>
							<select name="uap_general_login_redirect">
								<option value="-1" <?php echo ($data['metas']['uap_general_login_redirect']==-1) ? 'selected' : '';?> ><?php esc_html_e('Do Not Redirect', 'uap');?></option>
								<?php
									$pages_arr = $pages + uap_get_redirect_links_as_arr_for_select();
									if ($pages_arr){
										foreach ($pages_arr as $k=>$v){
											?>
												<option value="<?php echo $k;?>" <?php echo ($data['metas']['uap_general_login_redirect']==$k) ? 'selected' : '';?> ><?php echo $v;?></option>
											<?php
										}
									}
								?>
							</select>
							<?php echo uap_general_options_print_page_links($data['metas']['uap_general_login_redirect']);?>
						</div>

                        <div class="uap-form-line">
							<span class="uap-labels-special"><?php esc_html_e('After reset password:', 'uap');?></span>
							<select name="uap_general_after_reset_password_redirect">
								<option value="-1" <?php echo ($data['metas']['uap_general_lost_pass_page_logged_users_redirect']==-1) ? 'selected' : '';?> ><?php esc_html_e('Do Not Redirect', 'uap');?></option>
								<?php
									$pages_arr = $pages + uap_get_redirect_links_as_arr_for_select();
									if ($pages_arr){
										foreach ($pages_arr as $k=>$v){
											?>
												<option value="<?php echo $k;?>" <?php echo ($data['metas']['uap_general_after_reset_password_redirect']==$k) ? 'selected' : '';?> ><?php echo $v;?></option>
											<?php
										}
									}
								?>
							</select>
							<?php echo uap_general_options_print_page_links($data['metas']['uap_general_after_reset_password_redirect']);?>
						</div>

						<div id="uap_save_changes" class="uap-submit-form">
							<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
						</div>
					</div>
				</div>

				<div class="uap-stuffbox">
					<h3 class="uap-h3"><?php esc_html_e('Extra Redirects', 'uap');?></h3>
					<div class="inside">

						<div class="uap-form-line">
							<span class="uap-labels-special"><?php esc_html_e('Account Page - when affiliate user is not logged redirect to:', 'uap');?></span>
							<select name="uap_general_account_page_no_logged_redirect">
								<option value="-1" <?php echo ($data['metas']['uap_general_account_page_no_logged_redirect']==-1) ? 'selected' : '';?> ><?php esc_html_e('Do Not Redirect', 'uap');?></option>
								<?php
									$pages_arr = $pages + uap_get_redirect_links_as_arr_for_select();
									if ($pages_arr){
										foreach ($pages_arr as $k=>$v){
											?>
												<option value="<?php echo $k;?>" <?php echo ($data['metas']['uap_general_account_page_no_logged_redirect']==$k) ? 'selected' : '';?> ><?php echo $v;?></option>
											<?php
										}
									}
								?>
							</select>
							<?php echo uap_general_options_print_page_links($data['metas']['uap_general_account_page_no_logged_redirect']);?>
						</div>

						<div class="uap-form-line">
							<span class="uap-labels-special"><?php esc_html_e('Login Page - when affiliate user is already logged redirect to:', 'uap');?></span>
							<select name="uap_general_login_page_logged_users_redirect">
								<option value="-1" <?php echo ($data['metas']['uap_general_login_page_logged_users_redirect']==-1) ? 'selected' : '';?> ><?php esc_html_e('Do Not Redirect', 'uap');?></option>
								<?php
									$pages_arr = $pages + uap_get_redirect_links_as_arr_for_select();
									if ($pages_arr){
										foreach ($pages_arr as $k=>$v){
											?>
												<option value="<?php echo $k;?>" <?php echo ($data['metas']['uap_general_login_page_logged_users_redirect']==$k) ? 'selected' : '';?> ><?php echo $v;?></option>
											<?php
										}
									}
								?>
							</select>
							<?php echo uap_general_options_print_page_links($data['metas']['uap_general_login_page_logged_users_redirect']);?>
						</div>

						<div class="uap-form-line">
							<span class="uap-labels-special"><?php esc_html_e('Register Page - when affiliate user is already registered redirect to:', 'uap');?></span>
							<select name="uap_general_register_page_logged_users_redirect">
								<option value="-1" <?php echo ($data['metas']['uap_general_register_page_logged_users_redirect']==-1) ? 'selected' : '';?> ><?php esc_html_e('Do Not Redirect', 'uap');?></option>
								<?php
									$pages_arr = $pages + uap_get_redirect_links_as_arr_for_select();
									if ($pages_arr){
										foreach ($pages_arr as $k=>$v){
											?>
												<option value="<?php echo $k;?>" <?php echo ($data['metas']['uap_general_register_page_logged_users_redirect']==$k) ? 'selected' : '';?> ><?php echo $v;?></option>
											<?php
										}
									}
								?>
							</select>
							<?php echo uap_general_options_print_page_links($data['metas']['uap_general_register_page_logged_users_redirect']);?>
						</div>

						<div class="uap-form-line">
							<span class="uap-labels-special"><?php esc_html_e('LogOut Page - when affiliate user is not logged redirect to:', 'uap');?></span>
							<select name="uap_general_logout_page_non_logged_users_redirect">
								<option value="-1" <?php echo ($data['metas']['uap_general_logout_page_non_logged_users_redirect']==-1) ? 'selected' : '';?> ><?php esc_html_e('Do Not Redirect', 'uap');?></option>
								<?php
									$pages_arr = $pages + uap_get_redirect_links_as_arr_for_select();
									if ($pages_arr){
										foreach ($pages_arr as $k=>$v){
											?>
												<option value="<?php echo $k;?>" <?php echo ($data['metas']['uap_general_logout_page_non_logged_users_redirect']==$k) ? 'selected' : '';?> ><?php echo $v;?></option>
											<?php
										}
									}
								?>
							</select>
							<?php echo uap_general_options_print_page_links($data['metas']['uap_general_logout_page_non_logged_users_redirect']);?>
						</div>

						<div class="uap-form-line">
							<span class="uap-labels-special"><?php esc_html_e('Lost Password Page - when affiliate user is already logged redirect to:', 'uap');?></span>
							<select name="uap_general_lost_pass_page_logged_users_redirect">
								<option value="-1" <?php echo ($data['metas']['uap_general_lost_pass_page_logged_users_redirect']==-1) ? 'selected' : '';?> ><?php esc_html_e('Do Not Redirect', 'uap');?></option>
								<?php
									$pages_arr = $pages + uap_get_redirect_links_as_arr_for_select();
									if ($pages_arr){
										foreach ($pages_arr as $k=>$v){
											?>
												<option value="<?php echo $k;?>" <?php echo ($data['metas']['uap_general_lost_pass_page_logged_users_redirect']==$k) ? 'selected' : '';?> ><?php echo $v;?></option>
											<?php
										}
									}
								?>
							</select>
							<?php echo uap_general_options_print_page_links($data['metas']['uap_general_lost_pass_page_logged_users_redirect']);?>
						</div>



						<div id="uap_save_changes" class="uap-submit-form">
							<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
						</div>
					</div>
				</div>

			</form>
</div>
