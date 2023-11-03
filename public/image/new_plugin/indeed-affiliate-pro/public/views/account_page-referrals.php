<div class="uap-ap-wrap">

<?php if (!empty($data['title'])):?>
	<h3><?php echo $data['title'];?></h3>
<?php endif;?>
<?php if (!empty($data['message'])):?>
	<p><?php echo do_shortcode($data['message']);?></p>
<?php endif;?>

<?php if ((!empty($data['items']) && is_array($data['items'])) || !empty($data['filtered'])): ?>
	<div>
	<?php if (!empty($data['items']) && is_array($data['items'])): ?>
	<div class="uap-row">
		<div class="uapcol-md-3 uap-account-referrals-tab1">
			<div class="uap-account-no-box uap-account-box-lightgray"><div class="uap-account-no-box-inside"><div class="uap-count"><?php echo uap_format_price_and_currency($data['currency'], $data['stats']['verified_referrals_amount']);?></div><div class="uap-detail"><?php esc_html_e("Verified Referrals Amount", 'uap');?></div></div></div>
		</div>
		<div class="uapcol-md-3 uap-account-referrals-tab2">
			<div class="uap-account-no-box uap-account-box-lightyellow"><div class="uap-account-no-box-inside"><div class="uap-count"><?php echo uap_format_price_and_currency($data['currency'], $data['stats']['unverified_referrals_amount']);?></div><div class="uap-detail"><?php esc_html_e("UnVerified Referrals Amount", 'uap');?></div></div></div>
		</div>
		<div class="uapcol-md-3 uap-account-referrals-tab3">
			<div class="uap-account-no-box uap-account-box-lightblue"><div class="uap-account-no-box-inside"><div class="uap-count"><?php echo $data['stats']['referrals'];?></div><div class="uap-detail"><?php esc_html_e('Total No of Referrals', 'uap');?></div></div></div>
		</div>
	</div>
	<?php endif; ?>
    <div class="uap-profile-box-wrapper">
        <div class="uap-profile-box-content">
        	<div class="uap-row ">
            	<div class="uap-col-xs-12">
                   <div class="uap-account-detault-message">
                   		<?php esc_html_e('Here are listed only Unpaid Referrals which have not been withdrawn  yet. For a full list of referrals check ', 'uap');?>
              					<a href="<?php echo $data['full_referrals_url'];?>">
			  						<?php esc_html_e('this section', 'uap');?>
              					</a>
                  </div>
          		</div>
             </div>
        </div>
    <div class="uap-profile-box-wrapper">
    	<div class="uap-profile-box-title"><span><?php esc_html_e("Rewards and Commissions", 'uap');?></span></div>
        <div class="uap-profile-box-content">
        	<div class="uap-row ">
            	<div class="uap-col-xs-12">
                <div class="uap-account-referrals-filter">
					<?php echo $data['filter'];?>
    			</div>
		<?php if (!empty($data['items']) && is_array($data['items'])): ?>
		<table class="uap-account-table">
			  <thead>
				<tr>
					<th class="uap-account-referrals-table-col1"><?php esc_html_e("ID", 'uap');?></th>
					<th class="uap-account-referrals-table-col2"><?php esc_html_e("Campaign", 'uap');?></th>
					<th class="uap-account-referrals-table-col3"><?php esc_html_e("Amount", 'uap');?></th>
					<th class="uap-account-referrals-table-col4"><?php esc_html_e("From", 'uap');?></th>
					<?php if (!empty($data['print_source_details'])):?>
						<th class="uap-account-referrals-table-col5"><?php esc_html_e('Source Details', 'uap');?></th>
					<?php endif;?>
					<th class="uap-account-referrals-table-col6"><?php esc_html_e("Description", 'uap');?></th>
					<th class="uap-account-referrals-table-col7"><?php esc_html_e("Received on", 'uap');?></th>
					<th class="uap-account-referrals-table-col8"><?php esc_html_e("Status", 'uap');?></th>
				</tr>
			  </thead>
			  <tbody class="uap-alternate">
			<?php foreach ($data['items'] as $array) : ?>
				<tr>
					<td class="uap-account-referrals-table-col1"><?php echo $array['id'];?></td>
					<td class="uap-account-referrals-table-col2"><?php
						if ($array['campaign']) {
							echo $array['campaign'];
						} else {
							echo '-';
						}
					?></td>
					<td  class="uap-account-referrals-table-col3"><strong><?php echo uap_format_price_and_currency($array['currency'], $array['amount']);?></strong></td>
					<td class="uap-account-referrals-table-col4"><?php echo (empty($array['source'])) ? '' : uap_service_type_code_to_title($array['source']);?></td>
					<?php if (!empty($data['print_source_details'])):?>
						<td class="uap-account-referrals-table-col5"><?php
							if ($indeed_db->referral_has_source_details($array['id'])):
								$url = add_query_arg('reference', $array['id'], $data['source_details_url']);
								?>
								<a href="<?php echo $url;?>" target="_blank"><?php esc_html_e('View', 'uap');?></a>
								<?php
							else :
								echo '-';
							endif;
						?></td>
					<?php endif;?>
					<td class="uap-account-referrals-table-col6"><?php echo $array['description'];?></td>
					<td class="uap-account-referrals-table-col7"><?php echo uap_convert_date_to_us_format($array['date']);?></td>
					<td class="uap-special-label uap-account-referrals-table-col8"><?php
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
			<?php if (!empty($data['pagination'])):?>
                <?php echo $data['pagination'];?>
            <?php endif;?>
        </div>
        </div>
        </div>
        </div>
	</div>
    <?php else: ?>
    	   <div class="uap-account-detault-message">
              <div><?php esc_html_e('Here you will see all your Rewards and Commission that will be received based on your activity. Start your Affiliate campaign to earn commission.', 'uap');?></div>
          </div>
<?php endif;?>

</div>
