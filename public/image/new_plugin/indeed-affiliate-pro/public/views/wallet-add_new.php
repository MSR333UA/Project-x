<div class="uap-ap-wrap">

<?php if (!empty($data['title'])):?>
	<h3><?php echo $data['title'];?></h3>
<?php endif;?>
<?php if (!empty($data['message'])):?>
	<p><?php echo do_shortcode($data['message']);?></p>
<?php endif;?>

	<?php if ($data['services']): ?>
	<form action="<?php echo $data['form_action'];?>" method="post">
		<input type="hidden" name="uapcheck" value="<?php echo $data['hash'];?>" />
		<div class="uap-ap-field">
			<label class="uap-ap-label uap-special-label"><?php esc_html_e('Service Type', 'uap');?></label>
			<select name="service_type" class="uap-public-form-control"><?php
					foreach ($data['services'] as $k=>$v):
						?>
						<option value="<?php echo $k;?>"><?php echo $v;?></option>
						<?php
					endforeach;
			?></select>
		</div>
			<label class="uap-ap-label uap-special-label"><?php esc_html_e('Referrals', 'uap');?></label>
			<?php if ($data['referrals']):?>
				<div class="uap-wallet-table-wrapp">
					<table class="uap-account-table">
						<thead>
							<tr>
								<th><?php esc_html_e('Select', 'uap');?></th>
								<th><?php esc_html_e("Amount", 'uap');?></th>
								<th><?php esc_html_e("From", 'uap');?></th>
								<th><?php esc_html_e("Description", 'uap');?></th>
							</tr>
						</thead>
						<tbody class="uap-alternate">
						<?php foreach ($data['referrals'] as $array) : ?>
							<tr>
								<td><input type="checkbox" onClick="uapAddToWallet(this, <?php echo $array['id'];?>, '#the_referrals_list');"/></td>
								<td><strong><?php echo uap_format_price_and_currency($array['currency'], $array['amount']);?></strong></td>
								<td><?php echo (empty($array['source'])) ? '' : uap_service_type_code_to_title($array['source']);?></td>
								<td><?php echo $array['description'];?></td>
							</tr>
						<?php endforeach;?>
						</tbody>
					</table>
				</div>

			<?php endif;?>

		<input type="hidden" value="" id="the_referrals_list" name="referrals" />
		<div>
			<div class="uap-aling-float-left">
				<input type="submit" value="<?php esc_html_e('Add On Wallet', 'uap');?>" name="save" class="button button-primary button-large" />
			</div>
			<div class="uap-aling-float-right">
				<div class="uap-wallet-total"><?php esc_html_e('Total Credit: ', 'uap');?> <span id="uap_total_amount"><?php echo uap_format_price_and_currency($data['currency'], '0');?></span></div>
			</div>
			<div class="uap-clear"></div>
	   </div>
	</form>

	<?php else : ?>
		<?php esc_html_e('No service available!', 'uap');?>
	<?php endif;?>

</div>
