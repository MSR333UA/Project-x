<?php
		wp_enqueue_script( 'uapAdminSendEmail', UAP_URL . 'assets/js/uapAdminSendEmail.js', array('jquery'), null );
		wp_enqueue_script( 'indeed_csv_export', UAP_URL . 'assets/js/csv_export.js', array('jquery'), null  );
?>


<div class="uap-wrapper uap-affiliate-list-wrapper">
		<div class="uap-page-title">Ultimate Affiliate Pro - <span class="second-text"><?php esc_html_e('Listing Affiliates', 'uap');?></span></div>
		<a href="<?php echo $data['url-add_edit'];?>" class="uap-add-new-like-wp"><i class="fa-uap fa-add-uap"></i><span><?php esc_html_e('Add new Affiliate', 'uap');?></span></a>

		<?php echo $data['errors'];?>



		<div class="uap-special-buttons-users">
			<?php
					$filters = [
						'rank' 									=> empty($_REQUEST['ordertype_rank']) ? '' : $_REQUEST['ordertype_rank'],
					];
			?>
			<div class="uap-special-button js-uap-export-csv"  data-filters='<?php echo ( isset($_REQUEST['ordertype_rank']) ) ? serialize($filters) : '';?>' data-export_type="affiliates"  >
					<i class="fa-uap fa-export-csv"></i><?php esc_html_e( 'Export CSV', 'uap' );?>
			</div>
			<div class="uap-special-button" id="uap_js_affiliate_list_add_filter"><i class="fa-uap fa-search-uap"></i><?php esc_html_e('Add Filters', 'uap')?></div>
		</div>
		<?php
			$hidded = 'uap-display-none';
			if (isset($_REQUEST['search_user'])|| isset($_REQUEST['ordertype_rank']) || isset($_REQUEST['orderby_user']) || isset($_REQUEST['ordertype_user']) ){
				 $hidded ='';
			}
		?>
		<div class="uap-filters-wrapper <?php echo $hidded; ?>" >
			<form method="get" action="<?php echo $data['base_list_url'];?>">
				<input type="hidden" name="page" value="ultimate_affiliates_pro" />
				<input type="hidden" name="tab" value="affiliates" />
				<div class="row-fluid">
					<div class="uap-span3 uap-span-box" >
						<div class="iump-form-line iump-no-border">
							<input name="search_t" type="text" value="<?php echo (isset($_REQUEST['search_t']) ? $_REQUEST['search_t'] : '') ?>" placeholder="<?php esc_html_e('Search by Name, Username or Email Address', 'uap');?>..." class="uap-search-field"/>
						</div>
					</div>
					<div class="uap-span2 uap-span-box">
						<div class="iump-form-line iump-no-border">
							<?php esc_html_e('Rank ', 'uap');?>
							<select name="ordertype_rank">
								<?php
									$ranks = array(-1=>'...') + $indeed_db->get_rank_list();
									if ($ranks!==FALSE){
										foreach ($ranks as $k=>$v){
											$selected = (isset($_REQUEST['ordertype_rank']) && $_REQUEST['ordertype_rank']==$k) ? 'selected' : '';
											?>
											<option value="<?php echo $k;?>" <?php echo $selected;?> ><?php echo $v;?></option>
											<?php
										}
									}
								?>
							</select>
						</div>
					</div>
					<div class="uap-span3 uap-span-box">
						<div class="iump-form-line iump-no-border">
							<?php esc_html_e('Order by ', 'uap');?>
							<select name="orderby_user">
								<option value="display_name" <?php echo (isset($_REQUEST['orderby_user']) && $_REQUEST['orderby_user']=='display_name') ? 'selected' : ''; ?>><?php esc_html_e('Name', 'uap');?></option>
								<option value="user_login" <?php echo (isset($_REQUEST['orderby_user']) && $_REQUEST['orderby_user']=='user_login') ? 'selected' : ''; ?>><?php esc_html_e('Username', 'uap');?></option>
								<option value="user_email" <?php echo (isset($_REQUEST['orderby_user']) && $_REQUEST['orderby_user']=='user_email') ? 'selected' : ''; ?>><?php esc_html_e('Email', 'uap');?></option>
								<option value="ID" <?php echo (isset($_REQUEST['orderby_user']) && $_REQUEST['orderby_user']=='ID') ? 'selected' : ''; ?>><?php esc_html_e('ID', 'uap');?></option>
								<option value="user_registered" <?php echo (isset($_REQUEST['orderby_user']) && $_REQUEST['orderby_user']=='user_registered') ? 'selected' : ''; ?>><?php esc_html_e('Registered Time', 'uap');?></option>
							</select>
							<select name="ordertype_user">
								<option value="ASC" <?php echo (isset($_REQUEST['ordertype_user']) && $_REQUEST['ordertype_user']=='ASC') ? 'selected' : ''; ?>><?php esc_html_e('ASC', 'uap');?></option>
								<option value="DESC" <?php echo (isset($_REQUEST['ordertype_user']) && $_REQUEST['ordertype_user']=='DESC') ? 'selected' : ''; ?>><?php esc_html_e('DESC', 'uap');?></option>
							</select>
						</div>
					</div>
					<div class="uap-span1 uap-span-box">
						<input type="submit" value="<?php esc_html_e('Apply Filter', 'uap');?>" name="search" class="button button-primary button-large">
					</div>
				</div>
			</form>
		</div>

		<?php if ($data['listing_affiliates']):?>
			<div class="uap-number-to-display">
				<strong><?php esc_html_e('Number of Affiliates to Display:', 'uap');?></strong>
				<select name="uap_limit" class="uap-js-affiliate-list-limit" data-url="<?php echo $data['base_list_url'];?>&uap_limit=">
					<?php
						foreach ($this->items_per_page as $value){
							$selected = ($value==$limit) ? 'selected' : '';
							?>
							<option value="<?php echo $value;?>" <?php echo $selected;?>><?php echo $value;?></option>
							<?php
						}
					?>
				</select>
			</div>

			<form  method="post" id="form_affiliates">

					<div class="uap-delete-button-wrapper">
						<select name="do_action">
							<option value="0" selected="">...</option>
							<option value="delete"><?php esc_html_e('Delete', 'uap');?></option>
							<option value="update_ranks"><?php esc_html_e('Update Rank', 'uap');?></option>
						</select>
						<input type="submit" name="apply_bttn" value="Apply" class="button action" onClick="checkSubmitAffiliateAction();return false;"/>
					</div>

					<table class="wp-list-table widefat fixed tags uap-admin-tables uap-affiliates-list">
						<thead>
							<tr>
								<th class="uap-size-col1"><input type="checkbox" onClick="uapSelectAllCheckboxes( this, '.uap-delete-affiliates' );" /></th>
								<th class="uap-size-col2"><?php esc_html_e('ID', 'uap');?></th>
                <th class="uap-size-col3"><?php esc_html_e('Top', 'uap');?></th>
								<th class="uap-size-col4"><?php esc_html_e('Full Name', 'uap');?></th>
								<th class="uap-size-col5"><?php esc_html_e('Email Address', 'uap');?></th>
								<th class="uap-size-col6"><?php esc_html_e('Rank', 'uap');?></th>
								<th class="uap-size-col7"><?php esc_html_e('Clicks', 'uap');?></th>
								<!--th><?php esc_html_e('Converted', 'uap');?></th-->
								<th class="uap-size-col8"><?php esc_html_e('Referrals', 'uap');?></th>
								<th class="uap-size-col9"><?php esc_html_e('Paid Amount', 'uap');?></th>
								<th class="uap-size-col10"><?php esc_html_e('UnPaid Amount', 'uap');?></th>
								<th class="uap-size-col11"><?php esc_html_e('Metrics', 'uap');?></th>
								<th class="uap-size-col12"><?php esc_html_e('WP Role', 'uap');?></th>
								<?php if (!empty($data['email_verification'])):?>
								<th><?php esc_html_e('Email Status', 'uap');?></th>
								<?php endif;?>
								<th class="uap-size-col13"><?php esc_html_e('Affiliate Since', 'uap');?></th>
								<th class="uap-size-col14"><?php esc_html_e('Details', 'uap');?></th>
							</tr>
						</thead>

				<tbody class="ui-sortable">
					<?php $i = 1;
						foreach ($data['listing_affiliates'] as $id=>$arr):?>
						<tr onmouseover="uapDhSelector('#aff_<?php echo $id;?>', 1);" onmouseout="uapDhSelector('#aff_<?php echo $id;?>', 0);" class="<?php echo ($i%2==0) ? 'alternate' : '';?>">
							<th><input type="checkbox" value="<?php echo $id;?>" name="affiliate_id_arr[]" class="uap-delete-affiliates"/></th>
							<td><?php echo $id;?></td>
                            <td class="uap-cel-top-psition">
							<?php if ( !empty( $data['rankings'] ) ):?>
											<?php if ( empty( $data['rankings'][$id] ) ):?>
                                            		<!--img src="<?php echo UAP_URL;?>assets/images/uap_trophy.png"/>
													 <div class="uap-userprofile-top-position-table">#<?php echo ( isset( $data['rankings_last_place'] ) ) ? $data['rankings_last_place'] : 'N/A';?></div-->
											<?php else :?>
													<img src="<?php echo UAP_URL;?>assets/images/uap_trophy.png" alt="<?php echo $data['rankings'][$id];?>"/>
                           							 <div class="uap-userprofile-top-position-table">#<?php echo $data['rankings'][$id];?></div>
											<?php endif;?>
									<?php endif;?>

                             </td>
							<td class="uap-column-username">
                            <img src="<?php echo uap_get_avatar_for_uid($arr['uid']);?>" class="uap-admin-affiliate-list-avatar" alt="<?php echo $arr['username'];?>"/>
                            <span class="uap-admin-affiliate-list-details">
															<span class="uap-list-affiliates-name-label"><?php echo $arr['name'];?></span>
															<span class="uap-admin-affiliate-list-details-unserame"><?php echo $this->print_flag_for_affiliate($arr['uid']) . $arr['username'];?>
														</span>
								<?php
									if ($arr['payment_settings']):

										switch ($arr['payment_settings']['type']):
											case 'paypal':
												$payment_class = ($arr['payment_settings']['is_active']) ? 'uap-payment-type-active-paypal' : '';
												?>
												<a href="<?php echo $data['base_view_payment_settings_url'] . $arr['uid'];?> " target="_blank">
													<span class="uap-admin-aff-payment-type <?php echo $payment_class;?>"><?php esc_html_e('PayPal', 'uap');?></span>
												</a>
												<?php
												break;
											case 'bt':
												$payment_class = ($arr['payment_settings']['is_active']) ? 'uap-payment-type-active-bt' : '';
												?>
												<a href="<?php echo $data['base_view_payment_settings_url'] . $arr['uid'];?> " target="_blank">
													<span class="uap-admin-aff-payment-type <?php echo $payment_class;?>"><?php esc_html_e('Bank Transfer', 'uap');?></span>
												</a>
												<?php
												break;
											case 'stripe':
												$payment_class = ($arr['payment_settings']['is_active']) ? 'uap-payment-type-active-stripe' : '';
												?>
												<a href="<?php echo $data['base_view_payment_settings_url'] . $arr['uid'];?> " target="_blank">
													<span class="uap-admin-aff-payment-type <?php echo $payment_class;?>"><?php esc_html_e('Stripe', 'uap');?></span>
												</a>
												<?php
												break;
											case 'stripe_v2':
												$payment_class = ($arr['payment_settings']['is_active']) ? 'uap-payment-type-active-stripe' : '';
												?>
												<a href="<?php echo $data['base_view_payment_settings_url'] . $arr['uid'];?> " target="_blank">
													<span class="uap-admin-aff-payment-type <?php echo $payment_class;?>"><?php esc_html_e('Stripe V2', 'uap');?></span>
												</a>
												<?php
												break;
											case 'stripe_v3':
												$payment_class = ($arr['payment_settings']['is_active']) ? 'uap-payment-type-active-stripe' : '';
												?>
												<a href="<?php echo $data['base_view_payment_settings_url'] . $arr['uid'];?> " target="_blank">
													<span class="uap-admin-aff-payment-type <?php echo $payment_class;?>"><?php esc_html_e('Stripe V3', 'uap');?></span>
												</a>
												<?php
												break;
										endswitch;
									else :
										?>
										<span class="uap-admin-aff-payment-type">-</span>
										<?php
									endif;
								?>
								</span>
								<div id="aff_<?php echo $id;?>" class="uap-visibility-hidden">
									<a href="<?php echo $data['url-add_edit'] . '&id=' . $arr['uid'];?>"><?php esc_html_e('Edit', 'uap');?></a>
                                    | <a onclick="uapDeleteFromTable(<?php echo $id;?>, 'Affiliate', '#delete_affiliate', '#form_affiliates');" href="javascript:return false;" class="uap-color-red"><?php esc_html_e('Delete', 'uap');?></a>

                                    | <a  href="<?php echo $data['affiliate_profile_url'] . '&affiliate_id=' . $id;?>" target="_blank"><?php esc_html_e('Affiliate Profile', 'uap');?></a>
                                    | <a  href="<?php echo $data['base_transations_url'] . '&affiliate=' . $id;?>"><?php esc_html_e('Transactions', 'uap');?></a>
                                    | <a  href="<?php echo $data['base_reports_url'] . '&affiliate_id=' . $id;?>"><?php esc_html_e('Reports', 'uap');?></a>
									<?php if ($arr['role']=='pending_user'):?>
										| <a onClick="uapApproveAffiliate(<?php echo $arr['uid'];?>);" href="javascript:return false;"><?php esc_html_e('Approve Affiliate', 'uap');?></a>
									<?php endif;?>
									<?php if ($arr['email_status']==-1): ?>
										<span id="<?php echo 'approve_email_' . $arr['uid'];?>" onClick="uapApproveEmail(<?php echo $arr['uid'];?>, '<?php esc_html_e("Verified", "uap");?>');">
										| <span class="uap-special-action-link"><?php esc_html_e('Approve E-mail', 'uap');?></span>
										</span>
									<?php endif;?>
								</div>
							</td>
							<td><a href="mailto:<?php echo $arr['email'];?>" target="_blank"><?php echo $arr['email'];?></a></td>
							<?php $style = (isset($arr['rank_color'])) ? 'uap-box-background-' . $arr['rank_color'] : 'uap-box-background-c9c9c9;';?>
							<td><div class="rank-type-list <?php echo $style;?>"><?php echo $arr['rank_label'];?></div></td>

							<td class="uap-affiliate-list-counts">
								<div><?php echo isset($arr['stats']['visits']) ? $arr['stats']['visits'] : '';?></div>
								<?php if (!empty($arr['stats']['visits'])): ?>
									<a href="<?php echo $data['base_visits_url'] . '&affiliate_id=' . $id;?>"><?php esc_html_e('View', 'uap');?></a>
								<?php endif;?>
							</td>

                    <td class="uap-affiliate-list-counts">
								<div>
									<?php echo isset($arr['stats']['referrals']) ? $arr['stats']['referrals'] : '';?>
								</div>
								<?php if (!empty($data['base_referrals_url']) && $arr['stats']['referrals']): ?>
									<a href="<?php echo $data['base_referrals_url'] . '&affiliate_id=' . $id;?>"><?php esc_html_e('View', 'uap');?></a>
								<?php endif;?>
							</td>
							<td class="uap-affiliate-list-counts">
								<div><?php echo isset($arr['stats']['paid_payments_value']) ? uap_format_price_and_currency($currency, $arr['stats']['paid_payments_value']) : '';?></div>
								<?php if (!empty($arr['stats']['paid_payments_value'])): ?>
									<a href="<?php echo $data['base_paid_url'] . '&affiliate=' . $id;?>"><?php esc_html_e('View', 'uap');?></a>
								<?php endif;?>
							</td>
							<td class="uap-affiliate-list-counts">
								<strong class="uap-price-color"><?php echo isset($arr['stats']['unpaid_payments_value']) ? uap_format_price_and_currency($currency, $arr['stats']['unpaid_payments_value']) : '';?></strong>
								<?php if (!empty($arr['stats']['unpaid_payments_value'])):?>
									<div><a href="<?php echo $data['base_unpaid_url'] . '&affiliate=' . $id;?>"><?php esc_html_e('Proceed', 'uap');?></a> | <a href="<?php echo $data['base_pay_now'] . '&affiliate=' . $id;?>"><?php esc_html_e('Pay All', 'uap');?></a></div>
								<?php endif;?>
							</td>
									<td class="uap-metrics-cell">
									<div class="uap-metris-leftside">
									<?php if (!empty($data['show_ppc'])):?>
										<div>
											<?php $ppc = $indeed_db->getReferralsBySourceAndAffiliate('ppc', $id);?>
											<?php echo esc_html__('CPC: ', 'uap') . $ppc;?>
										</div>
									<?php endif;?>
                                    <?php if (!empty($data['show_cpm'])): ?>
									<div>
									<?php
											$cpm = $indeed_db->getReferralsBySourceAndAffiliate('cpm', $id);
											$number = $indeed_db->getCPMForAffiliate($id);
											if ($number){
													$number = $number / 10;
											}
										echo esc_html__('CPM: ', 'uap') . $cpm ;?>
                                      <div class="uap-progress-bar"><div class="uap-progress-completed" style = " width:<?php echo  $number; ?>%;"></div></div>  									</div>
									<?php endif;?>
                                    </div>
                                    <div class="uap-metris-rightside">
                                      <div>
											<?php $epc3 = $indeed_db->getEPCdata('3months', $id);?>
											<?php echo esc_html__('3 months EPC: ', 'uap');
												echo uap_format_price_and_currency($currency, $epc3); ;?>
										</div>

                                      <div>
											<?php $epc7 = $indeed_db->getEPCdata('7days', $id);?>
											<?php echo esc_html__('7 days EPC: ', 'uap');
												echo uap_format_price_and_currency($currency, $epc7); ;?>
									  </div>
                                    </div>
                                    <div class="uap-clear"></div>
								</td>
							<?php $pending = ($arr['role']=='pending_user') ? 'uap-pending' : '';?>
							<td><div class="uap-subcr-type-list <?php echo $pending;?>"><?php echo (isset($data['ranks_list'][$arr['role']])) ? $data['ranks_list'][$arr['role']] : '';?></div></td>
							<?php if (!empty($data['email_verification'])):?>
							<td><?php
			    				$div_id = "user_email_" . $arr['uid'] . "_status";
			    				$class = 'uap-subcr-type-list';
			    				if ($arr['email_status']==1){
			    					$label = esc_html__('Verified', 'uap');
			    				} else if ($arr['email_status']==-1){
			    					$label = esc_html__('Unapproved', 'uap');
			    					$class .= ' uap-pending';
			    				} else {
				   					$label = '-';
								}
			    				?>
			    				<div id="<?php echo $div_id;?>">
			    					<span class="<?php echo $class;?>"><?php echo $label;?></span>
			    				</div>
			    			</td>
							<?php endif;?>
							<td class="uap-date-color"><?php echo uap_convert_date_to_us_format($arr['start_data']);?></td>
							<td class="uap-buttons-wrapper">
                <div class="referral-status-verified uap-affiliate-profile-button"><a  href="<?php echo $data['affiliate_profile_url'] . '&affiliate_id=' . $id;?>"  target="_blank"><?php esc_html_e('Affiliate Profile', 'uap');?></a></div>
								<div class="referral-status-verified uap-affiliate-transactions-button"><a  href="<?php echo $data['base_transations_url'] . '&affiliate=' . $id;?>"><?php esc_html_e('Transactions', 'uap');?></a></div>
								<?php if (!empty($data['mlm_on']) && $indeed_db->affiliate_has_childrens($id) ) : ?>
									<div class="referral-status-unverified uap-mlm-button"><a  href="<?php echo $data['mlm_matrix_link'] . $arr['username'];?>"><?php esc_html_e('MLM Matrix', 'uap');?></a></div>
								<?php endif;?>
								<div class="referral-status-unverified uap-reports-button" ><a href="<?php echo $data['base_reports_url'] . '&affiliate_id=' . $id;?>"><?php esc_html_e('Reports', 'uap');?></a></div>
								<div class="uap_frw_button uap_small_grey_button uap-admin-do-send-email-via-ump" data-uid="<?php echo $arr['uid'];?>"><?php esc_html_e('Direct Email', 'uap');?></div>

							</td>
						</tr>
					<?php $i++;
						endforeach;?>
				</tbody>

				<tfoot>
					<tr>
						<th><input type="checkbox" onClick="uapSelectAllCheckboxes( this, '.uap-delete-affiliates' );" /></th>
						<th><?php esc_html_e('ID', 'uap');?></th>
						<th><?php esc_html_e('Top', 'uap');?></th>
						<th><?php esc_html_e('Full Name', 'uap');?></th>
						<th><?php esc_html_e('Email Address', 'uap');?></th>
						<th><?php esc_html_e('Rank', 'uap');?></th>
						<th><?php esc_html_e('Clicks', 'uap');?></th>
						<!--th><?php esc_html_e('Converted', 'uap');?></th-->
						<th><?php esc_html_e('Referrals', 'uap');?></th>
						<th><?php esc_html_e('Paid Amount', 'uap');?></th>
						<th><?php esc_html_e('UnPaid Amount', 'uap');?></th>
						<th><?php esc_html_e('Metrics', 'uap');?></th>
						<th><?php esc_html_e('WP Role', 'uap');?></th>
						<?php if (!empty($data['email_verification'])):?>
						<th><?php esc_html_e('Email Status', 'uap');?></th>
						<?php endif;?>
						<th><?php esc_html_e('Affiliate Since', 'uap');?></th>
						<th><?php esc_html_e('Details', 'uap');?></th>
					</tr>
				</tfoot>
			</table>
			<input type="hidden" value='' name="delete_affiliate" id="delete_affiliate" />
			<input type="hidden" value="<?php echo wp_create_nonce( 'uap_admin_list_affiliate_nonce' );?>" name="uap_admin_list_affiliate_nonce" />
		</form>
		<?php
			if (!empty($data['pagination'])) :
				echo $data['pagination'];
			endif;
		?>

		<?php else : ?>
			<h4 class="uap-missing-nmessage"><?php esc_html_e('No Affiliates Stored!', 'uap');?></h4>
		<?php endif;?>
</div>
