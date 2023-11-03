<?php
$pages = uap_get_all_pages();
//getting pages
?>
<div class="uap-wrapper">
			<form  method="post">

				<input type="hidden" name="uap_admin_forms_nonce" value="<?php echo wp_create_nonce( 'uap_admin_forms_nonce' );?>" />

				<div class="uap-stuffbox">
					<h3 class="uap-h3"><?php esc_html_e('Default Pages', 'uap');?></h3>
					<div class="inside">

						<div class="uap-form-line">
							<label class="uap-labels-special"><?php esc_html_e('Register:', 'uap');?></label>
							<select name="uap_general_register_default_page">
								<option value="-1" <?php echo ($data['metas']['uap_general_register_default_page']==-1) ? 'selected' : '';?> >...</option>
								<?php
									if ($pages){
										foreach ($pages as $k=>$v){
											?>
												<option value="<?php echo $k;?>" <?php echo ($data['metas']['uap_general_register_default_page']==$k) ? 'selected' : '';?> ><?php echo $v;?></option>
											<?php
										}
									}
								?>
							</select>
							<?php echo uap_general_options_print_page_links($data['metas']['uap_general_register_default_page']);?>
						</div>

						<div class="uap-form-line">
							<span class="uap-labels-special"><?php esc_html_e('Login Page:', 'uap');?></span>
							<select name="uap_general_login_default_page">
								<option value="-1" <?php echo ($data['metas']['uap_general_login_default_page']==-1) ? 'selected' : '';?> >...</option>
								<?php
									if ($pages){
										foreach ($pages as $k=>$v){
											?>
												<option value="<?php echo $k;?>" <?php echo ($data['metas']['uap_general_login_default_page']==$k) ? 'selected' : '';?> ><?php echo $v;?></option>
											<?php
										}
									}
								?>
							</select>
							<?php echo uap_general_options_print_page_links($data['metas']['uap_general_login_default_page']);?>
						</div>

						<div class="uap-form-line">
							<span class="uap-labels-special"><?php esc_html_e('Logout Page:', 'uap');?></span>
							<select name="uap_general_logout_page">
								<option value="-1" <?php echo ($data['metas']['uap_general_logout_page']==-1) ? 'selected' : '';?> >...</option>
								<?php
									if ($pages){
										foreach ($pages as $k=>$v){
											?>
												<option value="<?php echo $k;?>" <?php echo ($data['metas']['uap_general_logout_page']==$k) ? 'selected' : '';?> ><?php echo $v;?></option>
											<?php
										}
									}
								?>
							</select>
							<?php echo uap_general_options_print_page_links($data['metas']['uap_general_logout_page']);?>
						</div>

						<div class="uap-form-line">
							<span class="uap-labels-special"><?php esc_html_e('User Account Page:', 'uap');?></span>
							<select name="uap_general_user_page">
								<option value="-1" <?php echo ($data['metas']['uap_general_user_page']==-1) ? 'selected' : '';?> >...</option>
								<?php
									if ($pages){
										foreach ($pages as $k=>$v){
											?>
												<option value="<?php echo $k;?>" <?php echo ($data['metas']['uap_general_user_page']==$k) ? 'selected' : '';?> ><?php echo $v;?></option>
											<?php
										}
									}
								?>
							</select>
							<?php echo uap_general_options_print_page_links($data['metas']['uap_general_user_page']);?>
						</div>

						<div class="uap-form-line">
							<span class="uap-labels-special"><?php esc_html_e('TOS Page:', 'uap');?></span>
							<select name="uap_general_tos_page">
								<option value="-1" <?php echo ($data['metas']['uap_general_tos_page']==-1) ? 'selected' : '';?> >...</option>
								<?php
									if ($pages){
										foreach ($pages as $k=>$v){
											?>
												<option value="<?php echo $k;?>" <?php echo ($data['metas']['uap_general_tos_page']==$k) ? 'selected' : '';?> ><?php echo $v;?></option>
											<?php
										}
									}
								?>
							</select>
							<?php echo uap_general_options_print_page_links($data['metas']['uap_general_tos_page']);?>
						</div>

						<div class="uap-form-line">
							<span class="uap-labels-special"><?php esc_html_e('Lost Password:', 'uap');?></span>
							<select name="uap_general_lost_pass_page">
								<option value="-1" <?php echo ($data['metas']['uap_general_lost_pass_page']==-1) ? 'selected' : '';?> >...</option>
								<?php
									if ($pages){
										foreach ($pages as $k=>$v){
											?>
												<option value="<?php echo $k;?>" <?php echo ($data['metas']['uap_general_lost_pass_page']==$k) ? 'selected' : '';?> ><?php echo $v;?></option>
											<?php
										}
									}
								?>
							</select>
							<?php echo uap_general_options_print_page_links($data['metas']['uap_general_lost_pass_page']);?>
						</div>

						<div id="uap_save_changes" class="uap-submit-form">
							<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
						</div>
					</div>
				</div>
			</form>
</div>
