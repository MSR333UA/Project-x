<div class="uap-wrapper">
<form  method="post">

	<input type="hidden" name="uap_admin_forms_nonce" value="<?php echo wp_create_nonce( 'uap_admin_forms_nonce' );?>" />

	<div class="uap-stuffbox">
		<h3 class="uap-h3"><?php esc_html_e('Uploads Settings', 'uap');?></h3>
		<div class="inside">
			<div class="uap-form-line">
				<span class="uap-labels-special"><?php esc_html_e("Upload File Accepted Extensions:", 'uap');?></span>
				<div class="inside">
					<div class="row">
						<div class="col-xs-4">
					<textarea name="uap_upload_extensions" class="uap-custom-css-box"><?php echo $data['metas']['uap_upload_extensions'];?></textarea>
					<div><?php esc_html_e("Write the extensions with comma between values! ex: pdf,jpg,mp3", 'uap');?></div>
				</div>
			</div>
				</div>
			</div>

			<div class="uap-form-line">
				<span class="uap-labels-special"><?php esc_html_e("Upload File Maximum File Size:", 'uap');?></span>
				<div class="inside">
					<input type="number" value="<?php echo $data['metas']['uap_upload_max_size'];?>" name="uap_upload_max_size" min="0.1" step="0.1" /> MB
				</div>
			</div>

			<div class="uap-form-line">
				<span class="uap-labels-special"><?php esc_html_e("Avatar Maximum File Size:", 'uap');?></span>
				<div class="inside">
					<input type="number" value="<?php echo $data['metas']['uap_avatar_max_size'];?>" name="uap_avatar_max_size" min="0.1" step="0.1" /> MB
				</div>
			</div>

			<div id="uap_save_changes" class="uap-submit-form">
				<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
			</div>
		</div>
	</div>
</form>
</div>
