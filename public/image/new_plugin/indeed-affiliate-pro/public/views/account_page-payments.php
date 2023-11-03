<div class="uap-ap-wrap">
<?php if (!empty($data['title'])):?>
	<h3><?php echo $data['title'];?></h3>
<?php endif;?>
<?php if (!empty($data['message'])):?>
	<p><?php echo do_shortcode($data['message']);?></p>
<?php endif;?>

<div class="uap-row">
		<div class="uapcol-md-2 uap-account-payments-tab1">
			<div class="uap-account-no-box uap-account-box-lightgray"><div class="uap-account-no-box-inside"><div class="uap-count"><?php echo uap_format_price_and_currency($data['currency'], $data['stats']['paid_payments_value']);?></div><div class="uap-detail"><?php esc_html_e('Total Transactions Amount', 'uap');?></div></div></div>
		</div>
		<div class="uapcol-md-2 uap-account-payments-tab2">
			<div class="uap-account-no-box uap-account-box-lightblue"><div class="uap-account-no-box-inside"><div class="uap-count"><?php echo $data['stats']['payments']?></div><div class="uap-detail"><?php esc_html_e('Total number of Transactions', 'uap');?></div></div></div>
		</div>
	</div>
    <div class="uap-profile-box-wrapper">
        <div class="uap-profile-box-content uap-no-padding">
        	<div class="uap-row ">
            	<div class="uap-col-xs-12">
   						 <div class="uap-account-payment-method-link">
			  				<?php esc_html_e('You can setup or change your ', 'uap')?> <strong><?php esc_html_e(' Payout details ', 'uap');?></strong> <?php esc_html_e(' form', 'uap');?>
              					<a href="<?php echo $data['payment_settings_url'];?>">
			  						<?php esc_html_e('here', 'uap');?>
              					</a>
                    		</div>
        		</div>
        	</div>
        </div>
     </div>
    <div class="uap-profile-box-wrapper">
    	<div class="uap-profile-box-title"><span><?php esc_html_e("Withdrawn History", 'uap');?></span></div>
        <div class="uap-profile-box-content">
        	<div class="uap-row ">
            	<div class="uap-col-xs-12">

	<?php if (!empty($data['listing_items'])) : ?>
                <div class="uap-account-referrals-filter">
					<?php echo $data['filter'];?>
    			</div>
<div class="uap-wrapper">
		<table class="uap-account-table">
			<thead>
				<tr>
					<th><?php esc_html_e('Amount', 'uap');?></th>
					<th><?php esc_html_e('Payment Method', 'uap');?></th>
					<th><?php esc_html_e('Create Date', 'uap');?></th>
					<th><?php esc_html_e('Update Date', 'uap');?></th>
					<th><?php esc_html_e('Status', 'uap');?></th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th><?php esc_html_e('Amount', 'uap');?></th>
					<th><?php esc_html_e('Payment Method', 'uap');?></th>
					<th><?php esc_html_e('Create Date', 'uap');?></th>
					<th><?php esc_html_e('Update Date', 'uap');?></th>
					<th><?php esc_html_e('Status', 'uap');?></th>
				</tr>
			</tfoot>
			<tbody class="uap-alternate">
				<?php foreach ($data['listing_items'] as $key => $array): ?>
				<tr>
					<td><strong><?php echo uap_format_price_and_currency($array['currency'], $array['amount']);?></strong></td>
					<td><?php echo esc_html__($array['payment_type'], 'uap');?></td>
					<td><?php echo uap_convert_date_to_us_format($array['create_date']);?></td>
					<td><?php echo uap_convert_date_to_us_format($array['update_date']);?></td>
					<td class="uap-special-label"><?php
						switch ($array['status']){
							case 0:
								?>
									<div><?php esc_html_e('Fail', 'uap');?></div>
								<?php
								break;
							case 1:
								?>
									<div><?php esc_html_e('Pending', 'uap');?></div>
								<?php
								break;
							case 2:
								?>
									<div><?php esc_html_e('Complete', 'uap');?></div>
								<?php
								break;
						}
					?></td>
				</tr>
				<?php endforeach;?>
			</tbody>
		</table>
         </div>
    <?php else: ?>
    	   <div class="uap-account-detault-message">
              <div><?php esc_html_e('Here you will see all your proceeded Withdrawn once your earnings will be paid to your payment account.', 'uap');?></div>
          </div>
<?php endif;?>

	<?php if (!empty($data['pagination'])):?>
		<?php echo $data['pagination'];?>
	<?php endif;?>

        </div>
        </div>
        </div>
</div>
</div>
