<form  method="post">

	<input type="hidden" name="uap_admin_forms_nonce" value="<?php echo wp_create_nonce( 'uap_admin_forms_nonce' );?>" />

	<div class="uap-stuffbox">
		<h3 class="uap-h3"><?php esc_html_e('Notifications Settings', 'uap');?></h3>
		<div class="inside">
			<div class="uap-form-line">
				<label class="uap-labels-special"><?php esc_html_e("'From' E-mail Address:", 'uap');?></label> <input type="text" name="uap_notification_email_from" value="<?php echo $data['metas']['uap_notification_email_from'];?>" class="uap-deashboard-middle-text-input"/>
			</div>
			<div class="uap-form-line">
				<label class="uap-labels-special"><?php esc_html_e("'From' Name:", 'uap');?></label> <input type="text" name="uap_notification_name" value="<?php echo $data['metas']['uap_notification_name'];?>" class="uap-deashboard-middle-text-input" />
			</div>
			<div id="uap_save_changes" class="uap-submit-form">
				<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" onClick="" class="button button-primary button-large" />
			</div>
		</div>
	</div>
</form>
