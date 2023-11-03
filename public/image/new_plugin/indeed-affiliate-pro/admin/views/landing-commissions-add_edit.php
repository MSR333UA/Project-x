<div class="uap-wrapper">
	<div class="uap-stuffbox">
	<form action="<?php echo $data['url-manage'];?>" method="post">

		<input type="hidden" name="uap_admin_forms_nonce" value="<?php echo wp_create_nonce( 'uap_admin_forms_nonce' );?>" />

	<h3 class="uap-h3"><?php esc_html_e( 'Landing Commission (CPA) Shortcode', 'uap');?><span class="uap-admin-need-help"><i class="fa-uap fa-help-uap"></i><a href="https://help.wpindeed.com/ultimate-affiliate-pro/knowledge-base/landing-commissions/" target="_blank"><?php esc_html_e( 'Need Help?', 'uap');?></a></span></h3>
	<div class="inside">
		<div class="uap-inside-item">
			<div class="uap-form-line">
			<div class="row">
				<div class="col-xs-6">
				<h2><?php esc_html_e( 'Activate/Hold (CPA) Shortcode', 'uap');?></h2>
					<p><?php esc_html_e( 'Activate or deactivate a shortcode without needing to delete it.', 'uap');?></p>
					<label class="uap_label_shiwtch uap-switch-button-margin">
						<?php $checked = ($data['metas']['status']) ? 'checked' : '';?>
						<input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#the_status');" <?php echo $checked;?> />
						<div class="switch uap-display-inline"></div>
					</label>
					<input type="hidden" name="status" value="<?php echo $data['metas']['status'];?>" id="the_status" />
				</div>
			</div>
		</div>
		</div>

		<div class="uap-inside-item">
			<div class="uap-form-line">
			<div class="row">
				<div class="col-xs-6">
					<div class="input-group">
						<span class="input-group-addon"><?php esc_html_e( 'Slug', 'uap');?></span>
						<input type="text" class="form-control" placeholder="<?php esc_html_e( 'unique slug', 'uap');?>"  value="<?php echo $data['metas']['slug'];?>" name="slug" />
					</div>
					<p><i><?php esc_html_e( 'Be sure that you set a Unique Slug based only on lowercase characters and no additional symbols or spaces', 'uap');?></i></p>
				</div>
			</div>
		</div>
	</div>

		<div class="uap-inside-item">
			<div class="uap-form-line">
			<div class="row">
				<div class="col-xs-6">
					<h4><?php echo esc_html__('Commission Price', 'uap');?></h4>
					<p><?php esc_html_e( 'Based on the landing commission price, the referral amount will be calculated depending on each affiliate rank amount.', 'uap');?></p>
					 <div class="input-group">
						<span class="input-group-addon" id="basic-addon1"><?php esc_html_e( 'Value', 'uap');?></span>
						<input type="number" step='<?php echo uapInputNumerStep();?>' min="0"   class="form-control uap-lc-input-number" name="amount_value" value="<?php echo $data['metas']['amount_value'];?>" aria-describedby="basic-addon1" /> <span class="uap-lc-cookie-time"><?php echo $currency;?></span>
					 </div>
				 		<br/>
					 		<h4><?php echo esc_html__('Additional dynamic Workflow', 'uap');?></h4>
					 		<p><strong><?php esc_html_e( 'You can come out with a Dynamic reference Price via GET or POST if the  "lc_amount" variable is sent where the Landing Commission shortcode is set.', 'uap');?></strong></p>
				 		<br/>
				</div>
			</div>

			<div class="row">
				<div class="col-xs-4">
					<h4><?php esc_html_e( 'Referral Default Status', 'uap');?></h4>
					<div>
						<select name="default_referral_status" class="form-control m-bot15"><?php
							foreach (array(1=>esc_html__('Unverified', 'uap'), 2=>esc_html__('Verified', 'uap')) as $k=>$v):
								$selected = ($data['metas']['default_referral_status']==$k) ? 'selected' : '';
								?>
								<option value="<?php echo $k;?>" <?php echo $selected;?>><?php echo $v;?></option>
								<?php
							endforeach;
						?></select>
					</div>
				</div>
			</div>

		</div>
		</div>


		<div class="uap-inside-item">
			<div class="uap-form-line">
			<div class="row">
				<div class="col-xs-6">
					<div class="input-group">
						<span class="input-group-addon"><?php esc_html_e( 'Source Name', 'uap');?></span>
						<input type="text" class="form-control" placeholder=""  value="<?php echo $data['metas']['source'];?>" name="source" />
					</div>
				</div>
			</div>
		</div>
		</div>

		<div class="uap-inside-item">
			<div class="uap-form-line">
			<div class="row">
				<div class="col-xs-6">
					<div class="form-group">
						<h4><?php esc_html_e( 'Referral Description', 'uap');?></h4>
						<textarea class="form-control text-area" name="description"><?php echo $data['metas']['description'];?></textarea>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="uap-inside-item">
	<div class="uap-form-line">
			<div class="row">
				<div class="col-xs-6">
					<h4><?php echo esc_html__('Cookie Expire Time', 'uap');?></h4>
					<p><?php esc_html_e( 'Set time interval', 'uap');?></p>
					<div class="input-group">
						<?php if (!isset($data['metas']['cookie_expire'])){
							$data['metas']['cookie_expire'] = 0;
						}?>
						<input type="number" min="0" step="1" class="form-control uap-lc-input-number" name="cookie_expire" value="<?php echo $data['metas']['cookie_expire'];?>" aria-describedby="basic-addon1" />  <span class="uap-lc-cookie-time"><?php esc_html_e( 'Hours', 'uap');?></span>
					 </div>
				</div>
			</div>
		</div>
	</div>
				<div class="uap-inside-item">
					<div class="uap-form-line">
					<div class="row">
						<div class="col-xs-4">
							<h4><?php esc_html_e( 'Color', 'uap');?></h4>
							<div>
							<ul id="uap_colors_ul" class="uap-colors-ul">
                        	<?php
                                 $color_scheme = array('0a9fd8', '38cbcb', '27bebe', '0bb586', '94c523', '6a3da3', 'f1505b', 'ee3733', 'f36510', 'f8ba01');
                                 $i = 0;
                                 if (empty($data['metas']['color'])){
                                 	$data['metas']['color'] = $color_scheme[rand(0,9)];
                                 }
                                 foreach ($color_scheme as $color){
                            	     if ($i==5){
																		  echo "<li class='uap-clear'></li>";
																	 }
                                	     $class = ($color==$data['metas']['color']) ? 'uap-color-scheme-item-selected' : '';
                                         ?>
                                            <li class="uap-color-scheme-item <?php echo $class;?>  uap-box-background-<?php echo $color;?>" onClick="uapChageColor(this, '<?php echo $color;?>', '#uap_color');"></li>
                                         <?php
                                         $i++;
                                     }
                                 ?>
                            </ul>
                            <input type="hidden" name="color" id="uap_color" value="<?php echo $data['metas']['color'];?>" />
							</div>
						</div>
					</div>
				</div>
				</div>


					<div id="uap_save_changes" class="uap-submit-form">
						<input type="submit" value="<?php esc_html_e( 'Save Changes', 'uap');?>" name="save" class="button button-primary button-large">
					</div>
				</div>

				<input type="hidden" name="id" value="<?php echo $data['metas']['id'];?>" />

			</form>
		</div>

</div>
