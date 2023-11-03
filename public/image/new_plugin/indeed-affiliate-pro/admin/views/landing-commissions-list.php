<div class="uap-wrapper">
	<div class="uap-page-title">Ultimate Affiliate Pro - <span class="second-text"><?php esc_html_e('Landing Commissions (CPA)', 'uap');?></span></div>
		<a href="<?php echo $data['url-add_edit'];?>" class="uap-add-new-like-wp"><i class="fa-uap fa-add-uap"></i><span><?php esc_html_e('Add New Landing Commission (CPA)', 'uap');?></span></a>
		<span class="uap-top-message"><?php esc_html_e('...create your Landing Commission (CPA -CostPerAction) Shortcode', 'uap');?></span>
		<?php if (!empty($data['errors'])) : ?>
			<div class="uap-wrapp-the-errors"><?php echo $data['errors'];?></div>
		<?php endif;?>
		<?php if (!empty($data['listing_items'])) : ?>
			<form method="post" id="form_shortcodes">

				<input type="hidden" name="uap_admin_forms_nonce" value="<?php echo wp_create_nonce( 'uap_admin_forms_nonce' );?>" />

				<div class="uap-offer-items-wrap">
				<div class="uap-info-box"><?php esc_html_e('Just copy the landing commission (CPA) shortcode into any successful page (ex: Thank You Register page) and the affiliate will receive a certain commission based on generated referral.', 'uap');?></div>

					<?php foreach ($data['listing_items'] as $arr) : ?>
						<?php
							$inside_data = unserialize($arr['settings']);
							$color = (empty($inside_data['color']))	? '000' : '' . $inside_data['color'];
							$disabled = (empty($arr['status'])) ? 'uap-disabled-box' : '';
						?>

					   <div class="uap-admin-dashboard-offer-box-wrap <?php echo $disabled;?>">
					      <div class="uap-admin-dashboard-offer-box  uap-box-background-<?php echo $color;?>" id="uap-b-item-1">
					         <div class="uap-admin-dashboard-offer-box-main">
					            <div class="uap-admin-dashboard-offer-box-title"><?php echo $arr['slug']?></div>
					            <div class="uap-admin-dashboard-offer-box-content">
												<?php $source = isset( $inside_data['source'] ) ? $inside_data['source'] : '';?>
					            	<?php echo esc_html__('Source Name:', 'uap') . ' ' . $source;?>
								</div>
					            <div class="uap-admin-dashboard-offer-box-links-wrap">
					               <div class="uap-admin-dashboard-offer-box-links">
					                  <a href="<?php echo $data['url-add_edit'] . '&slug=' . $arr['slug'];?>" class="uap-admin-dashboard-offer-box-link"><?php esc_html_e('Edit', 'uap');?></a>
														<?php $theSlug = isset( $arr['slug'] ) ? $arr['slug'] : '';?>
														<div onclick="uapDeleteFromTable('<?php echo $theSlug;?>', 'Shortcode', '#delete_landing_referral', '#form_shortcodes');" class="uap-admin-dashboard-offer-box-link"><?php esc_html_e('Delete', 'uap');?></div>
					               </div>
					            </div>
					         </div>
					         <div class="uap-admin-dashboard-offer-box-bottom">
					            <div class="uap-admin-dashboard-offer-box-files">
									<?php $theAmount = isset( $inside_data['amount_value'] ) ? $inside_data['amount_value'] : '';?>
									<?php if ( $theAmount !== '' ):?>
											<?php echo uap_format_price_and_currency( $currency, $theAmount );?>
									<?php endif;?>
					               <div class="uap-admin-dashboard-offer-box-dest">&nbsp;</div>
					               <span class="uap-lc-shortcode">[uap-landing-commission slug='<?php echo $arr['slug'];?>']</span>
					            </div>
					            <div class="uap-admin-dashboard-offer-box-date"></div>
					            <div class="clear"></div>
					         </div>
					      </div>
					   </div>

					<?php endforeach;?>
					<div class="uap-clear"></div>
				</div>
				<input type="hidden" name="delete_landing_referral" value="" id="delete_landing_referral" />
			</form>
		<?php else : ?>
			<h4 class="uap-missing-nmessage"><?php esc_html_e('No Shortcode to show. Please, add your first Shortcode. ', 'uap');?></h4>
		<?php endif;?>
	</div>
