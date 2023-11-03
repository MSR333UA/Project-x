<div class="uap-wrapper">
<div class="uap-stuffbox">
  <form action="<?php echo $data['form_action_url'];?>" method="post">

  <input type="hidden" name="uap_admin_forms_nonce" value="<?php echo wp_create_nonce( 'uap_admin_forms_nonce' );?>" />

	<h3 class="uap-h3"><?php esc_html_e('Manage Banners', 'uap');?></h3>
	<div class="inside">
    <div class="uap-form-line">
	  <div class="uap-inside-item">
		<div class="row">
			<div class="col-xs-4">
			<div class="input-group">
				<span class="input-group-addon"><?php esc_html_e('Name', 'uap');?></span>
				<input type="text" class="form-control" placeholder="..."  value="<?php echo $data['name'];?>" name="name"  />
			</div>
			</div>
		</div>
	 </div>
 </div>

	 <div class="uap-inside-item">
     <div class="uap-form-line">
		<div class="row">
			<div class="col-xs-4">
				<h4><?php esc_html_e('Description:', 'uap');?></h4>
				<textarea name="description" class="form-control text-area" cols="30" rows="5" placeholder="<?php esc_html_e('Some details...', 'uap');?>"><?php echo $data['description'];?></textarea>
			</div>
		</div>
  </div>
	</div>
	<div class="uap-inside-item uap-special-line">
    <div class="uap-form-line">
		<div class="row">
			<div class="col-xs-4">
			<h4><?php esc_html_e('Banner Options:', 'uap');?></h4>
			<p><?php esc_html_e('Predefined URL And Image for your Custom Banner available for Affiliates', 'uap');?></p>
			<div class="input-group">
				<span class="input-group-addon"><?php esc_html_e('URL:', 'uap');?></span>
				<input type="text" class="form-control" value="<?php echo $data['url'];?>" name="url" />
			</div>
      <div class="uap-form-line"></div>
      <h5><?php esc_html_e('Banner Image:', 'uap');?></h5>
			<div class="form-group">

			<input type="text" class="form-control uap-display-inline uap-banner-settings-image-input" onClick="openMediaUp(this);" value="<?php echo $data['image'];?>" name="image" id="uap_the_image" />
			<i class="fa-uap fa-trash-uap" id="uap_js_add_edit_banners_trash" title="<?php esc_html_e('Remove Banner Image', 'uap');?>"></i>
			</div>
			</div>
    </div>
		</div>
	</div>
		<input type="hidden" name="status" value="1" />
		<div id="uap_save_changes" class="uap-submit-form">
			<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large">
		</div>
	</div>
	<input type="hidden" name="id" value="<?php echo $data['id'];?>" />
  </form>
</div>

</div>
