<div class="uap-wrapper">
	<div class="uap-page-title">Ultimate Affiliate Pro - <span class="second-text"><?php esc_html_e('Listing Banners', 'uap');?></span></div>
		<a href="<?php echo $data['url-add_edit'];?>" class="uap-add-new-like-wp"><i class="fa-uap fa-add-uap"></i><?php esc_html_e('Add New Banner', 'uap');?></a>
		<span class="uap-top-message"><?php esc_html_e('...create Banners for your Affiliates', 'uap');?></span>
		<?php if (!empty($data['listing_items'])) : ?>
			<form method="post" id="form_banners">

				<input type="hidden" name="uap_admin_forms_nonce" value="<?php echo wp_create_nonce( 'uap_admin_forms_nonce' );?>" />

				<div class="uap-delete-wrapp">
					<input type="submit" value="<?php esc_html_e('Delete', 'uap');?>" name="delete" class="button button-primary button-large">
				</div>
					<table class="wp-list-table widefat fixed tags uap-admin-tables">
						<thead>
							<tr>
								<th class="uap-table-check-col"><input type="checkbox" onClick="uapSelectAllCheckboxes( this, '.uap-delete-banner' );" /></th>
								<th><?php esc_html_e('Name', 'uap');?></th>
								<th><?php esc_html_e('Image', 'uap');?></th>
								<th><?php esc_html_e('URL', 'uap');?></th>
								<th><?php esc_html_e('Create Time', 'uap');?></th>
							</tr>
						</thead>

						<tbody class="ui-sortable uap-alternate">
							<?php foreach ($data['listing_items'] as $arr) : ?>
								<tr onmouseover="uapDhSelector('#banner_<?php echo $arr->id;?>', 1);" onmouseout="uapDhSelector('#banner_<?php echo $arr->id;?>', 0);">
									<th class="uap-vertical-align-top"><input type="checkbox" value="<?php echo $arr->id;?>" name="delete_banner[]" class="uap-delete-banner"/></th>
									<td>
										<div class="uap-list-affiliates-name-label"><?php echo $arr->name;?></div>
										<div id="banner_<?php echo $arr->id;?>" class="uap-visibility-hidden">
											<a href="<?php echo $data['url-add_edit'] . '&id=' . $arr->id;?>">Edit</a>
											|
											<a onclick="uapDeleteFromTable(<?php echo $arr->id;?>, 'Banner', '#delete_banner_id', '#form_banners');" href="javascript:return false;"  class="uap-color-red"><?php esc_html_e('Delete', 'uap');?></a>
										</div>
									</td>
									<td><img src="<?php echo $arr->image;?>" class="uap-list-banner-img" alt="<?php echo $arr->name;?>"/></td>
									<td><?php echo '<a href ="'.$arr->url.'" target="_blank">'.$arr->url.'</a>';?></td>
									<td><?php echo uap_convert_date_to_us_format($arr->DATE);?></td>
								</tr>
							<?php endforeach;?>
						</tbody>
						<tfoot>
							<tr>
								<th><input type="checkbox" onClick="uapSelectAllCheckboxes( this, '.uap-delete-banner' );" /></th>
								<th><?php esc_html_e('Name', 'uap');?></th>
								<th><?php esc_html_e('Image', 'uap');?></th>
								<th><?php esc_html_e('URL', 'uap');?></th>
								<th><?php esc_html_e('Create Time', 'uap');?></th>
							</tr>
						</tfoot>
					</table>
				<div class="uap-delete-wrapp">
					<input type="submit" value="<?php esc_html_e('Delete', 'uap');?>" name="delete" class="button button-primary button-large">
				</div>
				<input type="hidden" name="delete_banner[]" value="" id="delete_banner_id" />
			</form>
		<?php else : ?>
			<h5><?php esc_html_e('No Banners Available!', 'uap');?></h5>
		<?php endif;?>
</div>
