<?php
require_once UAP_PATH . 'admin/font-awesome_codes.php';
$font_awesome = uap_return_font_awesome();
$custom_css = '';
?>

<?php
 foreach ($font_awesome as $base_class => $code):
	$custom_css .= "." . $base_class . ":before{".
		"content: '\\".$code."';".
	"}";
endforeach;
foreach ($data['available_tabs'] as $k=>$v):
	if ( isset( $v['uap_tab_' . $k . '_icon_code'] ) ):
	$custom_css .=  ".fa-" . $k . "-account-uap:before{".
		"content: '\\".$v['uap_tab_' . $k . '_icon_code']."';".
	"}";
	 endif;
endforeach;

wp_register_style( 'dummy-handle', false );
wp_enqueue_style( 'dummy-handle' );
wp_add_inline_style( 'dummy-handle', $custom_css );
 ?>

<div class="uap-page-title">Ultimate Affiliates Pro -
	<span class="second-text">
		<?php esc_html_e('Account Page', 'uap');?>
	</span>
</div>
<div class="uap-stuffbox">
	<div class="uap-shortcode-display">
		[uap-account-page]
	</div>
</div>

<div class="metabox-holder indeed uap-admin-account-page-settings">
<form  method="post">

	<input type="hidden" name="uap_admin_forms_nonce" value="<?php echo wp_create_nonce( 'uap_admin_forms_nonce' );?>" />

	<div class="uap-stuffbox">
		<h3 class="uap-h3"><?php esc_html_e('Top Section:', 'uap');?></h3>

			<div class="inside">

			<div class="uap-register-select-template">
				<?php esc_html_e('Select Template:', 'uap');?>
				<select name="uap_ap_top_theme"><?php
					foreach ($data['top_themes'] as $k=>$v){
						$selected = ($k==$data['metas']['uap_ap_top_theme']) ? 'selected' : '';
						?>
						<option value="<?php echo $k;?>" <?php echo $selected;?>><?php echo $v;?></option>
						<?php
					}
				?></select>
			</div>
			<div class="inside">
			 <div class="input-group">
				<label class="uap_label_shiwtch uap-onbutton">
					<?php $checked = ($data['metas']['uap_ap_edit_show_avatar']) ? 'checked' : '';?>
					<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_ap_edit_show_avatar');" <?php echo $checked;?> />
					<div class="switch uap-display-inline"></div>
				</label>
				<input type="hidden" value="<?php echo $data['metas']['uap_ap_edit_show_avatar'];?>" name="uap_ap_edit_show_avatar" id="uap_ap_edit_show_avatar" />
				<label><?php esc_html_e('Show Avatar Image', 'uap');?></label>
			</div>
			 <div class="input-group">
				<label class="uap_label_shiwtch uap-onbutton">
					<?php $checked = ($data['metas']['uap_ap_edit_show_earnings']) ? 'checked' : '';?>
					<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_ap_edit_show_earnings');" <?php echo $checked;?> />
					<div class="switch uap-display-inline"></div>
				</label>
				<input type="hidden" value="<?php echo $data['metas']['uap_ap_edit_show_earnings'];?>" name="uap_ap_edit_show_earnings" id="uap_ap_edit_show_earnings" />
				<label><?php esc_html_e('Show Earning', 'uap');?></label>
			</div>
			 <div class="input-group">
				<label class="uap_label_shiwtch uap-onbutton">
					<?php $checked = ($data['metas']['uap_ap_edit_show_referrals']) ? 'checked' : '';?>
					<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_ap_edit_show_referrals');" <?php echo $checked;?> />
					<div class="switch uap-display-inline"></div>
				</label>
				<input type="hidden" value="<?php echo $data['metas']['uap_ap_edit_show_referrals'];?>" name="uap_ap_edit_show_referrals" id="uap_ap_edit_show_referrals" />
				<label><?php esc_html_e('Show Referrals', 'uap');?></label>
			</div>
			 <div class="input-group">
				<label class="uap_label_shiwtch uap-onbutton">
					<?php $checked = ($data['metas']['uap_ap_edit_show_achievement']) ? 'checked' : '';?>
					<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_ap_edit_show_achievement');" <?php echo $checked;?> />
					<div class="switch uap-display-inline"></div>
				</label>
				<input type="hidden" value="<?php echo $data['metas']['uap_ap_edit_show_achievement'];?>" name="uap_ap_edit_show_achievement" id="uap_ap_edit_show_achievement" />
				<label><?php esc_html_e('Show Achievement', 'uap');?></label>
			</div>
			 <div class="input-group">
				<label class="uap_label_shiwtch uap-onbutton">
					<?php $checked = ($data['metas']['uap_ap_edit_show_rank']) ? 'checked' : '';?>
					<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_ap_edit_show_rank');" <?php echo $checked;?> />
					<div class="switch uap-display-inline"></div>
				</label>
				<input type="hidden" value="<?php echo $data['metas']['uap_ap_edit_show_rank'];?>" name="uap_ap_edit_show_rank" id="uap_ap_edit_show_rank" />
				<label><?php esc_html_e('Show Rank', 'uap');?></label>
			</div>
				<div class="input-group">
					 <label class="uap_label_shiwtch uap-onbutton">
						 <?php $checked = ($data['metas']['uap_ap_edit_show_metrics']) ? 'checked' : '';?>
						 <input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_ap_edit_show_metrics');" <?php echo $checked;?> />
						 <div class="switch uap-display-inline"></div>
					 </label>
					 <input type="hidden" value="<?php echo $data['metas']['uap_ap_edit_show_metrics'];?>" name="uap_ap_edit_show_metrics" id="uap_ap_edit_show_metrics" />
					 <label><?php esc_html_e('Show EPC Metrics', 'uap');?></label>
			 	</div>

			</div>

			<div class="inside">
				<h4><?php esc_html_e('Welcome Message:', 'uap');?></h4>
				<div class="uap-wp_editor uap-wp-editor">
				<?php wp_editor(stripslashes($data['metas']['uap_ap_welcome_msg']), 'uap_ap_welcome_msg', array('textarea_name'=>'uap_ap_welcome_msg', 'editor_height'=>200));?>
				</div>
				<div class="uap-constants-first">
					<h4><?php esc_html_e('Regular constants', 'uap');?></h4>
					<?php
						$constants = array(	"{username}",
											"{first_name}",
											"{last_name}",
											"{user_id}",
											"{user_email}",
											"{user_registered}",
											"{flag}",
											"{account_page}",
											"{login_page}",
											"{blogname}",
											"{blogurl}",
											"{siteurl}",
											'{rank_id}',
											'{rank_name}'
							);
						$extra_constants = uap_get_custom_constant_fields();
						foreach ($constants as $v){
							?>
							<div><?php echo $v;?></div>
							<?php
						}
						?>
						</div>
						<div class="uap-constants-second">
							<h4><?php esc_html_e('Custom Fields constants', 'uap');?></h4>
						<?php
						foreach ($extra_constants as $k=>$v){
							?>
							<div><?php echo $k;?></div>
							<?php
						}
					?>
							</div>
				</div>
				<div class="uap-clear"></div>

			<div class="inside">
				<div class="input-group">
          <div class="uap-form-line">
    				<h2><?php esc_html_e('Background/Banner Image:', 'uap');?></h2>
    				<p><?php esc_html_e('The cover or background image, based on what theme you have chosen', 'uap');?></p>
    				<div class="input-group">
    				<label class="uap_label_shiwtch uap-onbutton">
					<?php $checked = ($data['metas']['uap_ap_edit_background']) ? 'checked' : '';?>
					<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#uap_ap_edit_background');" <?php echo $checked;?> />
					<div class="switch uap-display-inline"></div>
				</label>
				<input type="hidden" value="<?php echo $data['metas']['uap_ap_edit_background'];?>" name="uap_ap_edit_background" id="uap_ap_edit_background" />
				<label></label>
				</div>
					<div class="row">
						<div class="col-xs-12">
							<div class="form-group">
								<input type="text" class="form-control uap-banner-field" onClick="openMediaUp(this);" value="<?php  echo $data['metas']['uap_ap_edit_background_image'];?>" name="uap_ap_edit_background_image" id="uap_ap_edit_background_image"/>
								<i class="fa-uap fa-trash-uap" id="uap_js_edit_background_image_trash" onclick="" title="<?php esc_html_e('Remove Background Image', 'uap');?>"></i>

							</div>
						</div>
					</div>
        </div>
			</div>
			</div>
			<div class="inside">
				<div id="uap_save_changes" class="uap-wrapp-submit-bttn">
						<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large"  />

					</div>
			</div>
		</div>
		</div>
		<div class="uap-stuffbox">
		<h3 class="uap-h3"><?php esc_html_e('Content Section:', 'uap');?></h3>

			<div class="inside">
			<div class="uap-register-select-template">
				<?php esc_html_e('Select Template:', 'uap');?>
				<select name="uap_ap_theme"><?php
					foreach ($data['themes'] as $k=>$v){
						$selected = ($k==$data['metas']['uap_ap_theme']) ? 'selected' : '';
						?>
						<option value="<?php echo $k;?>" <?php echo $selected;?>><?php echo $v;?></option>
						<?php
					}
				?></select>
			</div>
			</div>
			<div>
			  <div class="inside">
          <div class="uap-form-line">
				<h2><?php esc_html_e('Menu Tabs:', 'uap');?></h2>
				<div>
					<div class="uap-ap-tabs-list">
						<?php foreach ($data['available_tabs'] as $k=>$v):?>
							<div class="uap-ap-tabs-list-item" onClick="uapApMakeVisible('<?php echo $k;?>', this);" id="<?php echo 'uap_tab-' . $k;?>"><?php echo $v['uap_tab_' . $k . '_menu_label'];?></div>
						<?php endforeach;?>
						<div class="uap-clear"></div>
					</div>
					<div class="uap-ap-tabs-settings">
						<?php

						$tabs = explode(',', $data['metas']['uap_ap_tabs']);
						$i = 0;

						foreach ($data['available_tabs'] as $k=>$v):?>
							<div class="uap-ap-tabs-settings-item" id="<?php echo 'uap_tab_item_' . $k;?>">
								<div class="input-group">
									<h2><?php echo $v['uap_tab_' . $k . '_menu_label'];?></h2>
									<div><?php esc_html_e('Activate curent Tab', 'uap');?></div>
									<label class="uap_label_shiwtch  uap-onbutton">
										<?php $checked = (in_array($k, $tabs)) ? 'checked' : '';?>
										<input type="checkbox" class="uap-switch" onClick="uapMakeInputhString(this, '<?php echo $k;?>', '#uap_ap_tabs');" <?php echo $checked;?> />
										<div class="switch uap-display-inline uap-activate-tab-btn"></div>
									</label>
								</div>
								<?php if (isset($data['metas']['uap_tab_' . $k . '_menu_label'])) : ?>
									<div class="row">
										<div class="col-xs-6">
											<div class="input-group">
												<span class="input-group-addon"><?php esc_html_e('Menu Label', 'uap');?></span>
												<input type="text" class="form-control" placeholder="" value="<?php echo $data['metas']['uap_tab_' . $k . '_menu_label'];?>" name="<?php echo 'uap_tab_' . $k . '_menu_label';?>">
											</div>
								</div>
							</div>
								<?php endif;?>
								<?php if (isset($data['metas']['uap_tab_' . $k . '_title'])) : ?>
									<div class="row">
										<div class="col-xs-6">
											<div class="input-group">
												<span class="input-group-addon"><?php esc_html_e('Title', 'uap');?></span>
												<input type="text" class="form-control" placeholder="" value="<?php echo $data['metas']['uap_tab_' . $k . '_title'];?>" name="<?php echo 'uap_tab_' . $k . '_title';?>">
											</div>
										</div>
									</div>
								<?php endif;?>


									<!-- ICON SELECT - SHINY -->
									<div class="row uap-row-icon">
										<div class="col-xs-4">
									   		<div class="input-group">
												<label><?php esc_html_e('Icon', 'uap');?></label>
											<div class="uap-icon-select-wrapper">
												<div class="uap-icon-input">
													<div id="<?php echo 'indeed_shiny_select_' . $k;?>" class="uap-shiny-select-html"></div>
												</div>
								   				<div class="uap-icon-arrow" id="<?php echo 'uap_icon_arrow_' . $k;?>"><i class="fa-uap fa-arrow-uap"></i></div>
												<div class="uap-clear"></div>
											</div>

									   		</div>
										</div>
									</div>
									<span class="uap-js-account-page-icon-details" data-slug="<?php echo $k;?>"
										data-icon_code="<?php echo isset($data['metas']['uap_tab_' . $k . '_icon_code']) ? $data['metas']['uap_tab_' . $k . '_icon_code'] : '';?>" ></span>

									<!-- ICON SELECT - SHINY -->


								<?php if (isset($data['metas']['uap_tab_' . $k . '_content'])) : ?>
									<div>
										<div class="uap-wp-editor"><?php
											wp_editor(stripslashes($data['metas']['uap_tab_' . $k . '_content']), 'uap_tab_' . $k . '_content', array('textarea_name' => 'uap_tab_' . $k . '_content', 'editor_height'=>200));
										?></div>
										<div class="uap-constants-first">
											<?php
												echo "<h4>" . esc_html__('Regular constants', 'uap') . "</h4>";
												foreach ($constants as $v){
													?>
													<div><?php echo $v;?></div>
													<?php
												}
										?>
										</div>
										<div class="uap-constants-second">
										<?php
												echo "<h4>" . esc_html__('Custom Fields constants', 'uap') . "</h4>";
												foreach ($extra_constants as $k=>$v){
													?>
													<div><?php echo $k;?></div>
													<?php
												}
											?>
										</div>
									</div>
								<?php endif;?>
							</div>
						<?php endforeach;?>
					</div>
				</div>
				<input type="hidden" value="<?php echo $data['metas']['uap_ap_tabs'];?>" id="uap_ap_tabs" name="uap_ap_tabs" />
				<div id="uap_save_changes" class="uap-wrapp-submit-bttn">
						<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large"  />
					</div>
        </div>
			   </div>
			</div>
		</div>
		<div class="uap-stuffbox">
		<h3 class="uap-h3"><?php esc_html_e('Footer Section:', 'uap');?></h3>

			<div class="inside">
        <div class="uap-form-line">
				<h2><?php esc_html_e('Footer Content:', 'uap');?></h2>
				<div class="uap-wp_editor uap-wp-editor">
				<?php wp_editor(stripslashes($data['metas']['uap_ap_footer_msg']), 'uap_ap_footer_msg', array('textarea_name'=>'uap_ap_footer_msg', 'editor_height'=>200));?>
				</div>
				<div class="uap-constants-first">
					<h4><?php esc_html_e('Regular constants', 'uap');?></h4>
					<?php
						$constants = array(	"{username}",
											"{first_name}",
											"{last_name}",
											"{user_id}",
											"{user_email}",
											"{user_registered}",
											"{account_page}",
											"{login_page}",
											"{blogname}",
											"{blogurl}",
											"{siteurl}",
											'{rank_id}',
											'{rank_name}'
							);
						$extra_constants = uap_get_custom_constant_fields();
						foreach ($constants as $v){
							?>
							<div><?php echo $v;?></div>
							<?php
						}
						?>
						</div>
						<div class="uap-constants-second">
							<h4><?php esc_html_e('Custom Fields constants', 'uap');?></h4>
						<?php
						foreach ($extra_constants as $k=>$v){
							?>
							<div><?php echo $k;?></div>
							<?php
						}
					?>
							</div>
            </div>
				</div>
				<div class="uap-clear"></div>
				<div id="uap_save_changes" class="uap-wrapp-submit-bttn">
						<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
					</div>
		</div>
			<div class="uap-stuffbox">
		<h3 class="uap-h3"><?php esc_html_e('Additional Settings:', 'uap');?></h3>
			<div class="uap-form-line">
				<h2><?php esc_html_e('Custom CSS:', 'uap');?></h2>
					<textarea id="uap_account_page_custom_css"  name="uap_account_page_custom_css" class="uap-dashboard-textarea-full uap-custom-css-box"><?php echo stripslashes($data['metas']['uap_account_page_custom_css']);?></textarea>
					<div id="uap_save_changes" class="uap-wrapp-submit-bttn">
						<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large" />
					</div>
			</div>

		</div>
</form>
</div>
