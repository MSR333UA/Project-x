<div class="uap-wrapper">
		<div class="uap-page-title">Ultimate Affiliate Pro - <span class="second-text"><?php esc_html_e('Notifications', 'uap');?></span></div>
		<a href="<?php echo $data['url-add_edit'];?>" class="uap-add-new-like-wp"><i class="fa-uap fa-add-uap"></i><?php esc_html_e('Activate New Notification', 'uap');?></a>
		<a href="javascript:void(0)" class="button button-primary button-large uap-check-email-button" onClick="uapCheckEmailServer();"><?php esc_html_e('Check Mail Server', 'uap');?></a>
        <span class="uap-admin-need-help"><i class="fa-uap fa-help-uap"></i><a href="https://help.wpindeed.com/ultimate-affiliate-pro/knowledge-base/how-to-send-notifications/" target="_blank"><?php esc_html_e('Need Help?', 'uap');?></a></span>
		<div class="uap-clear"></div>
		<div>
		<?php if (!empty($data['listing_items'])) : ?>

			<form method="post" id="form_notification" class="uap-notifications-list">

					<input type="hidden" name="uap_admin_forms_nonce" value="<?php echo wp_create_nonce( 'uap_admin_forms_nonce' );?>" />

					<table class="wp-list-table widefat fixed tags uap-admin-tables">
						<thead>
							<tr>
								<th><?php esc_html_e('Subject', 'uap');?></th>
								<th><?php esc_html_e('Action', 'uap');?></th>
								<th><?php esc_html_e('Goes to', 'uap');?></th>
								<th><?php esc_html_e('Target Ranks', 'uap');?></th>
								<?php if ($indeed_db->is_magic_feat_enable('pushover')):?>
								<th class="manage-column uap-text-center"><?php esc_html_e('Mobile Notifications', 'uap');?></th>
								<?php endif;?>
								<th><?php esc_html_e('Options', 'uap');?></th>
							</tr>
						</thead>

						<tbody class="ui-sortable uap-alternate">
							<?php
								$admin_notifications = array(
															'admin_user_register',
															'admin_on_aff_change_rank',
															'admin_affiliate_update_profile',
								);
							?>
							<?php foreach ($data['listing_items'] as $arr) : ?>
								<?php
									if (empty($data['email_verification']) && ($arr->type=='email_check' || $arr->type=='email_check_success')){
										continue;
									}
								?>
								<tr onmouseover="uapDhSelector('#notification_<?php echo $arr->id;?>', 1);" onmouseout="uapDhSelector('#notification_<?php echo $arr->id;?>', 0);">
									<td>
										<?php echo $arr->subject;?>
										<div id="notification_<?php echo $arr->id;?>" class="uap-visibility-hidden">
											<a href="<?php echo $data['url-add_edit'] . '&id=' . $arr->id;?>"><?php esc_html_e('Edit', 'uap');?></a>
											|
											<a onclick="uapDeleteFromTable(<?php echo $arr->id;?>, 'Notification', '#delete_notification_id', '#form_notification');" href="javascript:return false;" class="uap-color-red"><?php esc_html_e('Delete', 'uap');?></a>
										</div>
									</td>
									<td><div class="uap-list-affiliates-name-label"><?php if (!empty($data['actions_available'][$arr->type])){
										echo $data['actions_available'][$arr->type];
									}?></div></td>
									<td><?php
										if (in_array($arr->type, $admin_notifications)){
											echo 'Admin';
										} else {
											echo 'Affiliate';
										}
									?></td>
									<td><?php
										if ($arr->rank_id==-1){
											 esc_html_e("All", 'uap');
										}elseif (!empty($data['ranks'][$arr->rank_id])){
											 echo $data['ranks'][$arr->rank_id];
										}?>
									</td>
									<?php if ($indeed_db->is_magic_feat_enable('pushover')):?>
										<td class="uap-text-center">
											<?php if (!empty($arr->pushover_status)):?>
												<i class="fa-uap fa-pushover-on-uap"></i>
											<?php endif;?>
										</td>
									<?php endif;?>
									<td>
											<div class="uap-js-notifications-fire-notification-test uap-notifications-list-send"
														data-notification_id="<?php echo $arr->id;?>"
														data-email="<?php echo get_option( 'admin_email' );?>"
											><?php esc_html_e('Send Test Email', 'uap');?></div>
									</td>
								</tr>
							<?php endforeach;?>
						</tbody>
						<tfoot>
							<tr>
								<th><?php esc_html_e('Subject', 'uap');?></th>
								<th><?php esc_html_e('Action', 'uap');?></th>
								<th><?php esc_html_e('Goes to', 'uap');?></th>
								<th><?php esc_html_e('Target Ranks', 'uap');?></th>
								<?php if ($indeed_db->is_magic_feat_enable('pushover')):?>
								<th class="manage-column uap-text-center"><?php esc_html_e('Mobile Notifications', 'uap');?></th>
								<?php endif;?>
								<th><?php esc_html_e('Options', 'uap');?></th>
							</tr>
						</tfoot>
					</table>

				<input type="hidden" name="delete_notification" value="" id="delete_notification_id" />
			</form>

		<?php else :?>

			<h5><?php esc_html_e('No Notification Available!', 'uap');?></h5>

		<?php endif;?>
	</div>
</div>





<?php
