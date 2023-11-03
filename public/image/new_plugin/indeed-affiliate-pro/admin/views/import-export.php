<div class="uap-wrapper">
<form  method="post">
	<div class="uap-stuffbox">
		<h3 class="uap-h3"><?php esc_html_e('Export', 'uap');?></h3>
		<div class="inside">
			<div class="uap-form-line">
				<span class="uap-labels-special"></span>
				<div class="uap-form-line">
					<h4><?php esc_html_e('Users', 'uap');?></h4>
					<label class="uap_label_shiwtch uap-switch-button-margin">
						<input type="checkbox" class="iump-switch" onClick="uapCheckAndH(this, '#import_users');" />
						<div class="switch uap-display-inline"></div>
					</label>
					<input type="hidden" name="import_users" value=0 id="import_users"/>
				</div>
				<div class="uap-form-line">
					<h4><?php esc_html_e('Settings', 'uap');?></h4>
					<label class="uap_label_shiwtch uap-switch-button-margin">
						<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#import_settings');" />
						<div class="switch uap-display-inline"></div>
					</label>
					<input type="hidden" name="import_settings" value=0 id="import_settings"/>
				</div>
			</div>

			<div class="uap-hidden-download-link uap-display-none"><a href="" target="_blank" download>export.xml</a></div>

			<div class="uap-wrapp-submit-bttn">
				<div class="button button-primary button-large uap-display-inline" onClick="uapMakeExportFile();"><?php esc_html_e('Export', 'uap');?></div>
				<div class="uap-display-inline" id="uap_loading_gif" ><span class="spinner"></span></div>
			</div>

		</div>
	</div>
</form>

<form  method="post" enctype="multipart/form-data">
	<div class="uap-stuffbox">
		<h3 class="uap-h3"><?php esc_html_e('Import', 'uap');?></h3>
		<div class="inside">
			<div class="uap-form-line">
				<span class="uap-labels-special"><?php esc_html_e('File', 'uap');?></span>
				<input type="file" name="import_file" />
			</div>

			<input type="hidden" name="uap_admin_import_nonce" value="<?php echo wp_create_nonce( 'uap_admin_import_nonce' );?>" />

			<div class="uap-wrapp-submit-bttn">
				<input type="submit" value="<?php esc_html_e('Import', 'uap');?>" name="import" class="button button-primary button-large">
			</div>
		</div>
	</div>
</form>

</div>
