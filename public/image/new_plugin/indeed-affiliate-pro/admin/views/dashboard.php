<div class="uap-dashboard-wrapper">
	<div class="uap-dashboard-title">
		Ultimate Affiliate Pro -
		<span class="second-text">
			<?php esc_html_e('Dashboard Overall', 'uap');?>
		</span>
	</div>
	<div class="row">
	<div class="col-xs-3">
		<div class="uap-dashboard-top-box">
			<i class="fa-uap fa-dashboard-visits-uap"></i>
			<div class="stats">
				<h4><?php echo $data['stats']['affiliates'];?></h4>
				<?php esc_html_e('Total Registered Affiliates', 'uap');?>
			</div>
		</div>
	</div>

	<div class="col-xs-3">
		<div class="uap-dashboard-top-box">
			<i class="fa-uap fa-dashboard-referrals-uap"></i>
			<div class="stats">
				<h4><?php echo $data['stats']['referrals'];?></h4>
				<?php esc_html_e('Total Generated Referrals', 'uap');?>
			</div>
		</div>
	</div>

	<div class="col-xs-3">
		<div class="uap-dashboard-top-box">
			<i class="fa-uap fa-dashboard-payments-unpaid-uap"></i>
			<div class="stats">
				<h4><?php echo uap_format_price_and_currency($data['currency'], round($data['stats']['unpaid_payments_value'], 2));?></h4>
				<?php esc_html_e('Total UnPaid Referrals', 'uap');?>
			</div>
		</div>
	</div>

	<div class="col-xs-3">
		<div class="uap-dashboard-top-box">
			<i class="fa-uap fa-dashboard-rank-uap"></i>
			<div class="stats">
				<h4><?php echo $data['stats']['top_rank'];?></h4>
				<?php esc_html_e('Most Assigned Rank', 'uap');?>
			</div>
		</div>
 	</div>
  </div>



<div class="row">
   <div class="col-xs-8">
	<div class="uap-box-content-dashboard" >
	 <div class="uap-dashboard-box-padded">
		<h4><?php esc_html_e('Total Affiliates per Rank', 'uap');?></h4>
		<?php if (!empty($data['rank_arr'])):?>
			<div id="uap_chart_1" class='uap-flot'></div>
		<?php endif;?>
	 </div>
	</div>

	<?php if (!empty($data['last_referrals'])):?>
	<div class="uap-box-content-dashboard uap-last-five uap-dashboard-box-padded">
		<div class="info-title"><i class="fa-uap fa-list-uap"></i><?php esc_html_e('Last Five Referrals received', 'uap');?></div>
		<?php foreach ($data['last_referrals'] as $array):?>
			<div class="uap-dashboard-las-reff">
				<i class="fa-uap fa-icon-pop-list-uap"></i>
				<span><?php echo '  ' . uap_format_price_and_currency($array['currency'], $array['amount']) . esc_html__(' for ', 'uap') .  '<strong>'.$array['affiliate_username'] .'</strong><br/>'. esc_html__(' on ', 'uap') . uap_convert_date_to_us_format($array['date']); ?></span>
			</div>
		<?php endforeach;?>
	</div>
	<?php endif;?>
   </div>

   <div class="col-xs-4">
		<?php if (!empty($data['top_affiliates'])) : ?>
			<div class="uap-box-right-dashboard">
			<div class="uap-dashboard-top-affiliate">
					<span class="uap-big-cunt">10</span>
					<span><?php esc_html_e('Top', 'uap');?><br/><?php esc_html_e('Affiliates', 'uap');?></span>
				</div>
				<?php $i = 1;?>
				<?php foreach ($data['top_affiliates'] as $key=>$value): ?>
					<div class="uap-dashboard-top-affiliate-single">
					 <div class="uap-top-name"><?php echo '<span>' . $i . '</span> ' . $value['name'] . ' (' . $key . ')';?> </div>
					 <div class="uap-top-count"><?php esc_html_e('Referrals', 'uap');?> <?php echo $value['referrals'];?> | <?php esc_html_e('Total Amount', 'uap');?> <?php echo uap_format_price_and_currency($data['currency'], $value['sum']);?> </div>
					</div>
					<?php $i++;?>
				<?php endforeach;?>
			</div>
		<?php endif;?>
   </div>
</div>
</div>
<?php if ( $data['rank_arr'] ):?>
		<?php foreach ( $data['rank_arr'] as $key => $value ):?>
				<span class="uap-js-dashboard-rank-data" data-label="<?php echo $key;?>" data-value="<?php echo $value;?>"></span>
		<?php endforeach;?>
<?php endif;?>

<?php
