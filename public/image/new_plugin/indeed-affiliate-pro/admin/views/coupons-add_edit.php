<div class="uap-wrapper">
	<div class="uap-stuffbox">
		<form action="<?php echo $data['url-manage'];?>" method="post">

			<h3 class="uap-h3"><?php esc_html_e('Add/Edit Coupons', 'uap');?></h3>
			<div class="inside">
				<div class="uap-inside-item uap-special-line">
					<div class="row">
						<div class="col-xs-6">
							<h4><?php esc_html_e('Source', 'uap');?></h4>
							<select name="type" id="the_source"  class="form-control m-bot15 uap-js-coupons-add-edit-autocomplete"
								data-url_target="<?php echo UAP_URL . 'admin/uap-coupons-ajax-autocomplete.php?uapAdminAjaxNonce=' . wp_create_nonce( 'uapAdminAjaxNonce' ) . '&source=';?>"
								><?php
								$values = uap_get_active_services();
								if ( isset($values['ulp'])){
									 unset($values['ulp']);
								}
								foreach ($values as $k=>$v){
									$selected = ($data['metas']['type']==$k) ? 'selected' : '';
									?>
									<option value="<?php echo $k;?>" <?php echo $selected;?>><?php echo $v;?></option>
									<?php
								}
							?></select>
						</div>
					</div>
					<div class="row">
						<div class="col-xs-6">
							<div class="input-group">
								<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Coupon Code', 'uap');?></span>
								<input type="text" class="form-control" placeholder="<?php esc_html_e( 'Search coupon code', 'uap' );?>" value="<?php echo $data['metas']['code'];?>" name="code" id="coupon_code" />
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-xs-6">
							<div class="input-group">
								<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Affiliate', 'uap');?></span>
								<input type="text" placeholder="<?php esc_html_e( 'Search affiliate', 'uap' );?>" class="form-control" value="<?php echo $data['affiliate'];?>" id="affiliate_name" />
								<input type="hidden" id="affiliate_id_hidden" name="affiliate_id" value="<?php echo $data['metas']['affiliate_id'];?>"/>
							</div>

						</div>
					</div>
				</div>

				<div class="uap-inside-item">
					<div class="uap-form-line">
					<div class="row">
						<div class="col-xs-6">
						<h2><?php esc_html_e('Activate/Hold Coupon', 'uap');?></h2>
							<p><?php esc_html_e('Activate or deactivate a coupon without needing to delete it.', 'uap');?></p>
							<label class="uap_label_shiwtch uap-switch-button-margin">
								<?php $checked = ($data['metas']['status']) ? 'checked' : '';?>
								<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#offer_status');" <?php echo $checked;?> />
								<div class="switch uap-display-inline"></div>
							</label>
							<input type="hidden" name="status" value="<?php echo $data['metas']['status'];?>" id="offer_status" />
						</div>
					</div>

					<div class="uap-inside-item">
						<div class="row">
							<div class="col-xs-4">
								<h4><?php esc_html_e('Referral Amount (optional)', 'uap');?></h4>
								<p><?php esc_html_e('A special flat amount for Referral calculation when this coupon is used.', 'uap');?></p>
								<p><?php esc_html_e('Leave blank if you do not wish to replace the standard amount Rank.', 'uap');?></p>
								<div>
										<select name="amount_type" class="form-control m-bot15"><?php
											foreach ($data['amount_types'] as $k=>$v):
												$selected = ($data['metas']['amount_type']==$k) ? 'selected' : '';
												?>
												<option value="<?php echo $k;?>" <?php echo $selected;?>><?php echo $v;?></option>
												<?php
											endforeach;
										?></select>
								 </div>
								<div class="input-group">
									<span class="input-group-addon" id="basic-addon1"><?php esc_html_e('Value', 'uap');?></span>
									<input type="number" min="0" step='<?php echo uapInputNumerStep();?>' class="form-control" name="amount_value" value="<?php echo $data['metas']['amount_value'];?>" aria-describedby="basic-addon1">
								</div>
							</div>
						</div>
					</div>

				</div>
				<div id="uap_save_changes" class="uap-submit-form">
					<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large">
				</div>
			</div>
			</div>

			<input type="hidden" name="id" value="<?php echo $data['metas']['id'];?>" />
		</form>
	</div>
</div>
