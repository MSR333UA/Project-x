<?php
		wp_enqueue_script( 'indeed_csv_export', UAP_URL . 'assets/js/csv_export.js', ['jquery'] );
?>
<div class="uap-wrapper">
	<div class="uap-page-title">Ultimate Affiliate Pro - <span class="second-text"><?php esc_html_e('Referrals (rewards)', 'uap');?></span></div>

		<?php if (!empty($data['error'])):?>
			<div class="uap-wrapp-the-errors">
				<?php echo $data['error'];?>
			</div>
		<?php endif;?>

		<?php if (!empty($data['subtitle'])):?>
			<h4><?php echo $data['subtitle'];?></h4>
		<?php endif;?>

		<a href="<?php echo $data['url-add_edit'];?>" class="uap-add-new-like-wp"><i class="fa-uap fa-add-uap"></i><?php esc_html_e('Add New Referral', 'uap');?></a>
		<span class="uap-top-message"><?php esc_html_e('...add manual Referral (reward) for specific Affiliate', 'uap');?></span>

		<div class="uap-special-buttons-users">
			<?php
					$filters = [
						'start' 									=> empty($_REQUEST['udf']) ? '' : $_REQUEST['udf'],
						'end' 										=> empty($_REQUEST['udu']) ? '' : $_REQUEST['udu'],
						'status' 									=> isset($_REQUEST['u_sts']) ? $_REQUEST['u_sts'] : -1,
						'affiliate_username'			=> isset($_REQUEST['aff_u']) ? $_REQUEST['aff_u'] : '',
						'source'									=> isset($_REQUEST['u_source']) ? $_REQUEST['u_source'] : -1,
					];
			?>
			<div class="uap-special-button js-uap-export-csv" data-filters='<?php echo ( isset($_REQUEST) ) ? serialize($filters) : '';?>' data-export_type="referrals" >
					<i class="fa-uap fa-export-csv"></i><?php esc_html_e( 'Export CSV', 'uap' );?>
			</div>
		</div>

		<div class="uap-special-box uap-referrals-list-filter-wrapper" >
		<?php echo $data['filter'];?>
		</div>

		<?php if (!empty($data['listing_items'])) : ?>
			<form  method="post" id="form_referrals">

				<input type="hidden" name="uap_admin_forms_nonce" value="<?php echo wp_create_nonce( 'uap_admin_forms_nonce' );?>" />

				<div class="uap-referrals-list-action-wrapper">
					<select name="list_action"><?php
						foreach ($data['actions'] as $k=>$v):
							?>
							<option value="<?php echo $k;?>" <?php echo ($data['current_actions']==$k) ? 'selected' : '';?>><?php echo $v;?></option>
							<?php
						endforeach;
					?></select>
					<input type="submit" name="apply_bttn" value="<?php esc_html_e('Apply', 'uap');?>" class="button action" />
				</div>

				<div class="uap-referrals-list-numbers-wrapper">
					<strong><?php esc_html_e('Number of Referrals to Display:', 'uap');?></strong>
					<select name="uap_limit" class="uap-js-referral-list-limit-number" data-url="<?php echo $data['base_list_url'] . '&uap_limit=';?>" >
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
				<div class="uap-referrals-list-pagination-wrapper">
					<?php
						if (!empty($data['pagination'])) :
							echo $data['pagination'];
						endif;
					?>
				</div>

					<table class="wp-list-table widefat fixed tags uap-admin-tables">
						<thead>
							<tr>
								<th class="uap-table-check-col"><input type="checkbox" onClick="uapSelectAllCheckboxes( this, '.uap-delete-referral' );" /></th>
								<th class="uap-table-referrals-affiliate-col"><?php esc_html_e('Affiliate ID', 'uap');?></th>
								<th><?php esc_html_e('Affiliate Username', 'uap');?></th>
								<th><?php esc_html_e('ID', 'uap');?></th>
								<th><?php esc_html_e('From', 'uap');?></th>
								<th><?php esc_html_e('Reference', 'uap');?></th>
								<th><?php esc_html_e('Description', 'uap');?></th>
								<th><?php esc_html_e('Amount', 'uap');?></th>
								<th><?php esc_html_e('Date', 'uap');?></th>
								<th><?php esc_html_e('Status', 'uap');?></th>
							</tr>
						</thead>

						<tbody class="ui-sortable uap-alternate">
							<?php foreach ($data['listing_items'] as $array) :  ?>
								<tr onmouseover="uapDhSelector('.hidden-div-referral-<?php echo $array['id'];?>', 1);" onmouseout="uapDhSelector('.hidden-div-referral-<?php echo $array['id'];?>', 0);">
									<th class="uap-vertical-align-top"><input type="checkbox" value="<?php echo $array['id'];?>" name="referral_list[]" class="uap-delete-referral"/></th>
									<?php $temp_uid = $indeed_db->get_uid_by_affiliate_id($array['affiliate_id']);?>
									<td><a href="<?php echo admin_url('user-edit.php?user_id=' . $temp_uid);?>" target="_blank"><?php echo $array['affiliate_id'];?></a></td>
									<td><?php
										echo '<div class="uap-list-affiliates-name-label">';
											if (!empty($array['username']))
												echo $array['username'];
											else esc_html_e('Unknown', 'uap');
										echo '</div>';
									?>
									<div id="referral_<?php echo $array['id'];?>" class=" uap-visibility-hidden <?php echo 'hidden-div-referral-' . $array['id'];?>">
											<a href="<?php echo $data['url-add_edit'] . '&id=' . $array['id'];?>"><?php esc_html_e('Edit', 'uap');?></a>
											|
											<a onclick="uapDeleteFromTable(<?php echo $array['id'];?>, 'Refferal', '#delete_referral_id', '#form_referrals');" href="javascript:return false;" class="uap-color-red"><?php esc_html_e('Delete', 'uap');?></a>
										</div>
									</td>
									<td><?php echo $array['id'];?></td>
									<td><?php echo uap_service_type_code_to_title($array['source']);?></td>
									<td>
										<?php
											$link = '';
											if (!empty($array['reference'])){
												switch ($array['source']){
													case 'woo':
														if (!empty($data['woo_order_base_link'])){
															$link = $data['woo_order_base_link'] . $array['reference'] . '&action=edit';
														}
														break;
													case 'ulp':
														if (!empty($data['ulp_order_base_link'])){
															$link = $data['ulp_order_base_link'] . $array['reference'] . '&action=edit';
														}
														break;
													case 'edd':
														if (!empty($data['edd_order_base_link'])){
															$link = $data['edd_order_base_link'] . $array['reference'];
														}
														break;
													case 'ump':
														if (function_exists('ihc_get_payment_id_by_order_id')){
															$payment_id = ihc_get_payment_id_by_order_id($array['reference']);
															if ($payment_id){
																if (!empty($data['ump_order_base_link'])){
																	$link = $data['ump_order_base_link'] . $payment_id;
																}
															}
														}
														break;
													case 'mlm':
														$the_ref = $array['reference'];
														$the_ref = str_replace('mlm_', '', $the_ref);
														$link = $data['mlm_order_base_link'] . $the_ref;
														break;
													case 'User SignUp':
														if (!empty($array['reference']) && strpos($array['reference'], 'user_id_')!==FALSE){
															$uid_sign_up = str_replace('user_id_', '', $array['reference']);
															$link = $data['user_sign_up_link'] . $uid_sign_up;
														}
														break;
													default:
														$link = apply_filters( 'uap_admin_dashboard_custom_referrence_link', '', $array );
														break;
												}
											}
											if (!empty($link)){
												echo '<a href="' . $link . '" target="_blank">' . $array['reference'] . '</a>';
											} else {
												echo $array['reference'];
											}
										?>
									</td>
									<td><?php echo $array['description'];?></td>
									<td><?php echo '<b>' . uap_format_price_and_currency($array['currency'], $array['amount']) . '</b>';?></td>
									<td class="uap-referrals-list-date-color"><?php echo uap_convert_date_to_us_format($array['date']);?></td>
									<td><?php
											/*
											 * 1 - UNVERIFIED
											 * 2 - VERIFIED
											 * 0 - REFUSE
											 */
										if (!$array['status']){
											?>
											<div class="referral-status-refuse"><?php esc_html_e('Refused', 'uap');?></div>
											<?php
										} else if ($array['status']==1){
											?>
											<div class="referral-status-unverified"><?php esc_html_e('Unverified', 'uap');?></div>
											<?php
										} else if ($array['status']==2){
											?>
											<div class="referral-status-verified"><?php esc_html_e('Verified', 'uap');?></div>
											<?php
										}
									?><div>
											<?php
												$status_arr = array(0 => esc_html__('Refused', 'uap'), 1 => esc_html__('Unverified', 'uap'), 2 => esc_html__('Verified', 'uap') );
												$i = 1;
												foreach ($status_arr as $k=>$v){
													if ($k!=$array['status']){
													 if($i != 1){
														  echo " | ";
													 }
													  $i++;
													?>
													<span class="refferal-chang-status uap-js-referral-list-change-status" data-value="<?php echo $array['id'] . '-' . $k;?>" ><?php esc_html_e('Mark as ', 'uap');?><?php echo $v;?></span>
													<?php
													}
												}
											?>
									</div>
									</td>
								</tr>
							<?php endforeach;?>
						</tbody>
						<tfoot>
							<tr>
								<th><input type="checkbox" onClick="uapSelectAllCheckboxes( this, '.uap-delete-referral' );" /></th>
								<th ><?php esc_html_e('Affiliate ID', 'uap');?></th>
								<th><?php esc_html_e('Affiliate Username', 'uap');?></th>
								<th><?php esc_html_e('ID', 'uap');?></th>
								<th><?php esc_html_e('From', 'uap');?></th>
								<th><?php esc_html_e('Referance', 'uap');?></th>
								<th><?php esc_html_e('Description', 'uap');?></th>
								<th><?php esc_html_e('Amount', 'uap');?></th>
								<th><?php esc_html_e('Date', 'uap');?></th>
								<th><?php esc_html_e('Status', 'uap');?></th>
							</tr>
						</tfoot>
					</table>

				<div class="uap-referrals-list-pagination-wrapper">
					<?php
						if (!empty($data['pagination'])) :
							echo $data['pagination'];
						endif;
					?>
				</div>
				<input type="hidden" name="change_status" value="" id="change_status" />
				<input type="hidden" name="delete_referral[]" value="" id="delete_referral_id" />

				<div class="uap-referrals-list-action-wrapper">
					<select name="list_action_2"><?php
						foreach ($data['actions'] as $k=>$v):
							?>
							<option value="<?php echo $k;?>" <?php echo ($data['current_actions']==$k) ? 'selected' : '';?>><?php echo $v;?></option>
							<?php
						endforeach;
					?></select>
					<input type="submit" name="apply_bttn" value="<?php esc_html_e('Apply', 'uap');?>" class="button action" />
				</div>

			</form>
		<?php else : ?>
			<h4 class="uap-missing-nmessage"><?php esc_html_e('No Referrals Stored!', 'uap');?></h4>
		<?php endif;?>
</div>
