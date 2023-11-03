<div class="uap-ap-wrap">
<?php if (!empty($data['title'])):?>
	<h3><?php echo $data['title'];?></h3>
<?php endif;?>
<?php if (!empty($data['message'])):?>
	<p><?php echo do_shortcode($data['message']);?></p>
<?php endif;?>

<form  method="post" class="uap-change-password-form">

	<input type="hidden" value="<?php echo wp_create_nonce( 'uap_public_change_password_nonce' );?>" name="uap_public_change_password_nonce" />

	<div class="uap-change-password-field-wrap">
		<label class="uap-change-password-label"><?php esc_html_e("Current Password", 'uap');?></label>
		<input class="uap-change-password-field" type="password" value="" name="old_pass" />
        <div class="uap-change-password-field-details"><?php esc_html_e("we need your current password to confirm your changes", 'uap');?></div>
	</div>
	<div class="uap-change-password-field-wrap">
		<label class="uap-change-password-label"><?php esc_html_e("New Password", 'uap');?></label>
		<input class="uap-change-password-field" type="password" value="" name="pass1" />
	</div>
	<div class="uap-change-password-field-wrap">
		<label class="uap-change-password-label"><?php esc_html_e("Repeat New Password", 'uap');?></label>
		<input class="uap-change-password-field" type="password" value="" name="pass2" />
	</div>
	<div class="uap-change-password-field-wrap">
		<input type="submit" value="<?php esc_html_e("Save Changes", 'uap');?>" name="update_pass" class="button button-primary button-large" />
	</div>
	<?php if (!empty($data['error'])) : ?>
		<div><?php echo $data['error'];?></div>
	<?php elseif (!empty($data['success'])) : ?>
		<div><?php echo $data['success'];?></div>
	<?php endif; ?>
</form>
</div>
