<div class="uap-wrapper">
	<?php if (!empty($data['alert_message'])):?>
		<div class="uap-error-message"><?php echo $data['alert_message'];?></div>
	<?php endif;?>
		<div class="uap-page-title">Ultimate Affiliate Pro - <span class="second-text"><?php esc_html_e('Ranks', 'uap');?></span></div>
		<a href="<?php echo $data['url-add_edit'];?>" class="uap-add-new-like-wp"><i class="fa-uap fa-add-uap"></i><span><?php esc_html_e('Add new Rank', 'uap');?></span></a>
		<span class="uap-top-message"><?php esc_html_e('...create your Rank with new achievements!', 'uap');?></span>
		<div class="uap-clear"></div>
		<?php if (!empty($data['ranks'])) : ?>

			<form method="post" id="form_ranks">
					<table class="wp-list-table widefat fixed tags uap-admin-tables">
						<thead>
							<tr>
								<th><?php esc_html_e('Label', 'uap');?></th>
								<th><?php esc_html_e('Amount', 'uap');?></th>
								<th><?php esc_html_e('Achieve', 'uap');?></th>
								<th><?php esc_html_e('Status', 'uap');?></th>
							</tr>
						</thead>
						<tbody class="ui-sortable uap-alternate">
							<?php foreach ($data['ranks'] as $arr) : ?>
								<tr onmouseover="uapDhSelector('#rank_<?php echo $arr->id;?>', 1);" onmouseout="uapDhSelector('#rank_<?php echo $arr->id;?>', 0);">
									<td>
										<?php echo "<b>" . $arr->label . "</b>";?>
										<div id="rank_<?php echo $arr->id;?>" class="uap-visibility-hidden">
											<a href="<?php echo $data['url-add_edit'] . '&id=' . $arr->id;?>"><?php esc_html_e('Edit', 'uap');?></a>
											|
											<span class="uap-js-delete-ranks uap-delete-span" data-id="<?php echo $arr->id;?>" ><?php esc_html_e('Delete', 'uap');?></span>
										</div>
									</td>
									<td><?php
										if ($arr->amount_type){
											if (!empty($this->amount_type_list[$arr->amount_type])){
												if ('%'==$this->amount_type_list[$arr->amount_type]){
													echo $arr->amount_value . ' ' . $this->amount_type_list[$arr->amount_type];
												} else {
													echo uap_format_price_and_currency($this->amount_type_list[$arr->amount_type], $arr->amount_value);
												}
											} else {
												echo $arr->amount_value;
											}
										}
										?></td>
									<td><?php
										$achieve = json_decode($arr->achieve, TRUE);
										if ($achieve):
										for ($i=1; $i<=$achieve['i']; $i++):?>
											<div class="uap-admin-listing-ranks-achieve">
												<div><strong><?php echo $data['achieve_types'][$achieve['type_' . $i]];?></strong></div>
												<div><?php echo esc_html__('From: ', 'uap');
													if ($achieve['type_' . $i]=='total_amount'){
															echo uap_format_price_and_currency( $this->amount_type_list['flat'], $achieve['value_' . $i] );
													} else {
															echo $achieve['value_' . $i];
													}
													?></div>
											</div>
										<?php
											endfor;
										else:
											?>
											<div class="uap-admin-listing-ranks-achieve">
												<?php esc_html_e('None', 'uap');?>
											</div>
											<?php
										endif;
										?>
									</td>
									<td><?php
										if ($arr->status){
											 esc_html_e('Active', 'uap');
										}else{
											 esc_html_e('Inactive', 'uap');
										}
									?></td>
								</tr>
							<?php endforeach;?>
						</tbody>

						<tfoot>
							<tr>
								<th><?php esc_html_e('Label', 'uap');?></th>
								<th><?php esc_html_e('Amount', 'uap');?></th>
								<th><?php esc_html_e('Achieve', 'uap');?></th>
								<th><?php esc_html_e('Status', 'uap');?></th>
							</tr>
						</tfoot>
					</table>

				<input type="hidden" name="delete_rank" value="" id="delete_rank_id" />
			</form>

		<?php else :?>

			<h5><?php esc_html_e('No Ranks Available!', 'uap');?></h5>

		<?php endif;?>
</div>
<div class="uap-js-ranks-listing-delete-item-label" data-value="<?php esc_html_e( 'Are you sure that you want to delete this rank?', 'uap' );?>"></div>
<div class="uap-js-ranks-listing-delete-nonce" data-value="<?php echo wp_create_nonce( 'uap_admin_forms_nonce' );?>"></div>
