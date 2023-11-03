<div class="uap-ap-wrap">

<?php if (!empty($data['title'])):?>
	<h3><?php echo $data['title'];?></h3>
<?php endif;?>
<?php if (!empty($data['message'])):?>
	<p><?php echo do_shortcode($data['message']);?></p>
<?php endif;?>

<?php if ((!empty($data['items']) && is_array($data['items'])) || !empty($data['filtered'])): ?>
	<div>
    <div class="uap-profile-box-wrapper">
        <div class="uap-profile-box-content">
        	<div class="uap-row ">
            	<div class="uap-col-xs-12">
                <div class="uap-account-referrals-filter">
					<?php echo $data['filter'];?>
    			</div>
                </div>
            </div>
        </div>
    </div>
		<?php if (!empty($data['items']) && is_array($data['items'])): ?>
    <table class="uap-account-table">
			  <thead>
				<tr>
					<th><?php esc_html_e("Campaign", 'uap');?></th>
					<th><?php esc_html_e("Amount", 'uap');?></th>
					<th><?php esc_html_e("From", 'uap');?></th>
					<th><?php esc_html_e("Description", 'uap');?></th>
					<th><?php esc_html_e("Date", 'uap');?></th>
					<th><?php esc_html_e('Payment', 'uap');?></th>
					<th><?php esc_html_e("Status", 'uap');?></th>
				</tr>
			  </thead>
			  <tbody class="uap-alternate">
			<?php foreach ($data['items'] as $array) : ?>
				<tr>
					<td><?php
						if ($array['campaign']) {
							echo $array['campaign'];
						} else {
							echo '-';
						}
					?></td>
					<td><strong><?php echo uap_format_price_and_currency($array['currency'], $array['amount']);?></strong></td>
					<td><?php echo (empty($array['source'])) ? '' : uap_service_type_code_to_title($array['source']);?></td>
					<td><?php echo $array['description'];?></td>
					<td><?php echo uap_convert_date_to_us_format($array['date']);?></td>
					<td><?php
						switch ($array['payment']){
							case 0:
								_e('UnPaid', 'uap');
								break;
							case 1:
								_e('Pending', 'uap');
								break;
							case 2:
								_e('Paid', 'uap');
								break;
						}
					?></td>
					<td class="uap-special-label"><?php
						if ($array['status']==0){
							_e('Refused', 'uap');
						} else if ($array['status']==1){
							_e('Unverified', 'uap');
						} else if ($array['status']==2){
							_e('Verified', 'uap');
						}
					?></td>
				</tr>
			<?php endforeach;?>
			</tbody>
		</table>
	<?php else: ?>
			 <div class="uap-account-detault-message">
						<div><?php esc_html_e('No Referrals found for your selection.', 'uap');?></div>
				</div>
<?php endif;?>
	</div>
    <?php else: ?>
    	   <div class="uap-account-detault-message">
              <div><?php esc_html_e('Here you will see all your Rewards and Commission that will be received based on your activity. Start your Affiliate campaing to earn commission.', 'uap');?></div>
          </div>
<?php endif;?>

<?php if (!empty($data['pagination'])):?>
	<?php echo $data['pagination'];?>
<?php endif;?>
</div>
