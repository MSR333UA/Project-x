<div class="uap-wrapper uap-admin-reports-wrapper">
<div class="uap-page-title">Ultimate Affiliate Pro - <span class="second-text"><?php esc_html_e('Reports', 'uap');?></span></div>

		<?php if (!empty($data['subtitle'])):?>
			<h4><?php echo $data['subtitle'];?></h4>
		<?php endif;?>

<div class="uap-special-box">
<form  method="post">
			<?php esc_html_e('Select reports from:', 'uap');?>

			<select name="search" class="uap-main-search"><?php foreach ($data['select_values'] as $k=>$v):?>
				<?php $selected = ($data['selected']==$k) ? 'selected' : '';?>
				<option <?php echo $selected;?> value="<?php echo $k?>"><?php echo $v;?></option>
			<?php endforeach;?></select>
			<input type="submit" value="<?php esc_html_e('Check Results', 'uap');?>" name="submit" class="button button-primary button-large" />

		</form>
</div>
<div class="uap-stuffbox">
	<div class="inside">

		<?php if ( empty( $_GET['affiliate_id'] ) ):?>
		<div class="row">
				<div class="col-xs-12">
					<h3><?php esc_html_e('Affiliates Stats', 'uap');?></h3>
				</div>
		</div>
		<div class="row">
		<div class="col-xs-3" >
			<div class="uap-dashboard-top-box">
				<i class="fa-uap fa-affiliates-uap"></i>
				<div class="stats">
					<h4><?php echo $data['reports']['affiliates'];?></h4>
					<?php esc_html_e('New Registered Affiliates', 'uap');?>
				</div>
			</div>
		</div>
		<div class="col-xs-3" >
			<div class="uap-dashboard-top-box">
				<i class="fa-uap fa-dashboard-visits-uap"></i>
				<div class="stats">
					<h4><?php echo $data['reports']['total_affiliates'];?></h4>
					<?php esc_html_e('Total Affiliates', 'uap');?>
				</div>
			</div>
		</div>
	</div>
	<?php endif;?>

		<div class="row">
				<div class="col-xs-12">
					<h3><?php esc_html_e('Referrals Stats', 'uap');?></h3>
				</div>
		</div>

		<div class="row">
		<div class="col-xs-3" >
			<div class="uap-dashboard-top-box">
				<i class="fa-uap fa-dashboard-referrals-uap"></i>
				<div class="stats">
					<h4><?php echo $data['reports']['referrals'];?></h4>
					<?php esc_html_e('Total Referrals', 'uap');?>
				</div>
			</div>
		</div>

		<div class="col-xs-3" >
			<div class="uap-dashboard-top-box">
				<i class="fa-uap fa-payments-uap"></i>
				<div class="stats">
					<h4><?php echo uap_format_price_and_currency($data['currency'], round($data['reports']['total_amount_referrals'], 2));?></h4>
					<?php esc_html_e('Total Amount Referrals', 'uap');?>
				</div>
			</div>
		</div>

		<div class="col-xs-3" >
			<div class="uap-dashboard-top-box">
				<i class="fa-uap fa-success-rate-uap"></i>
				<div class="stats">
					<h4><?php echo uap_format_price_and_currency($data['currency'], round($data['reports']['average_amount_referrals'], 2));?></h4>
					<?php esc_html_e('Average Referral Amount', 'uap');?>
				</div>
			</div>
		</div>


		<div class="col-xs-3" >
			<div class="uap-dashboard-top-box">
				<i class="fa-uap fa-dashboard-payments-unpaid-uap"></i>
				<div class="stats">
					<h4><?php echo uap_format_price_and_currency($data['currency'], round($data['reports']['total_paid_referrals'], 2));?></h4>
					<?php esc_html_e('Paid Referrals', 'uap');?>
				</div>
			</div>
		</div>

		<div class="col-xs-3" >
			<div class="uap-dashboard-top-box">
				<i class="fa-uap fa-dashboard-payments-unpaid-uap"></i>
				<div class="stats">
					<h4><?php echo uap_format_price_and_currency($data['currency'], round($data['reports']['total_unpaid_referrals'], 2));?></h4>
					<?php esc_html_e('UnPaid Referrals', 'uap');?>
				</div>
			</div>
		</div>
	</div>

	<div class="row">
			<div class="col-xs-12">
				<h3><?php esc_html_e('Clicks Stats', 'uap');?></h3>
			</div>
	</div>
	<div class="row">
		<div class="col-xs-3" >
			<div class="uap-dashboard-top-box">
				<i class="fa-uap fa-visits-reports-uap"></i>
				<div class="stats">
					<h4><?php echo $data['reports']['visits'];?></h4>
					<?php esc_html_e('Total Clicks', 'uap');?>
				</div>
			</div>
		</div>

		<div class="col-xs-3" >
			<div class="uap-dashboard-top-box">
				<i class="fa-uap fa-success-number-uap"></i>
				<div class="stats">
					<h4><?php echo $data['reports']['conversions'];?></h4>
					<?php esc_html_e('Successful Conversions', 'uap');?>
				</div>
			</div>
		</div>

		<div class="col-xs-3">
			<div class="uap-dashboard-top-box">
				<i class="fa-uap fa-success-rate-uap"></i>
				<div class="stats">
					<h4><?php echo $data['reports']['success_rate'] . '%';?></h4>
					<?php esc_html_e('Succesfully Rate', 'uap');?>
				</div>
			</div>
		</div>
	</div>

	<div class="row">
			<div class="col-xs-12">
				<h3><?php esc_html_e('Payout Stats', 'uap');?></h3>
			</div>
	</div>
	<div class="row">
			<div class="col-xs-3">
				<div class="uap-dashboard-top-box">
					<i class="fa-uap fa-payments-uap"></i>
					<div class="stats">
						<h4><?php echo round($data['reports']['total_paid'], 2) . $data['currency'];?></h4>
						<?php esc_html_e('Total Paid Earnings', 'uap');?>
					</div>
				</div>
			</div>
			<div class="col-xs-3">
				<div class="uap-dashboard-top-box">
					<i class="fa-uap fa-dashboard-payments-unpaid-uap"></i>
					<div class="stats">
						<h4><?php echo $data['reports']['total_transactions'];?></h4>
						<?php esc_html_e('Total Transactions', 'uap');?>
					</div>
				</div>
			</div>
			<div class="col-xs-3">
				<div class="uap-dashboard-top-box">
					<i class="fa-uap fa-ranks-uap"></i>
					<div class="stats">
						<h4><?php echo $data['reports']['total_completed_transactions'];?></h4>
						<?php esc_html_e('Completed Transactions', 'uap');?>
					</div>
				</div>
			</div>
	</div>

	</div>
</div>

<?php

if ($data['visit_graph']){
	/// VISIT GRAPH
	?>
		<div class="uap-stuffbox">
			<div class="inside">
				<div id="uap-plot-1" class="uap-plot"></div>
			</div>
		</div>
	<?php
	reset($data['visit_graph']);
	$first_key = key($data['visit_graph']);
	if (isset($data['visit_graph'][$first_key])){
		$start_time = strtotime($first_key);
	}

	end($data['visit_graph']);
	$last_key = key($data['visit_graph']);
	if (isset($data['visit_graph'][$last_key])){
		$end_time = strtotime($last_key);
	}
	reset($data['visit_graph']);
	?>
	<?php
		if (!empty($data['visit_graph']) && is_array($data['visit_graph'])):
			foreach ($data['visit_graph'] as $date=>$value):?>
				<span class="uap-js-visit-graph-data" data-date="<?php echo strtotime($date) . '000';?>" data-value="<?php echo $value;?>" ></span>
			<?php endforeach;
		endif;

			if (!empty($data['visit_graph_success']) && is_array($data['visit_graph_success'])):
				if (count($data['visit_graph_success'])<2){
					if (empty($data['visit_graph_success'][$first_key])){
						$data['visit_graph_success'][$first_key] = 0;
					} else if (empty($data['visit_graph_success'][$last_key])){
						$data['visit_graph_success'][$last_key] = 0;
					}
				}
				foreach ($data['visit_graph_success'] as $date=>$value):?>
					<span class="uap-js-visit-graph-success-data" data-date="<?php echo strtotime($date) . '000';?>" data-value="<?php echo $value;?>" ></span>
				<?php endforeach;
			endif;
	?>
	<span class="uap-js-reports-labels"
			data-all_clicks="<?php esc_html_e('All Clicks', 'uap');?>"
			data-converted_clicks="<?php esc_html_e('Converted Clicks', 'uap');?>"
			data-all_referrals="<?php esc_html_e('All Referrals', 'uap');?>"
			data-refuse_referrals="<?php esc_html_e('Refuse Referrals', 'uap');?>"
			data-unverified_referrals="<?php esc_html_e('Unverified Referrals', 'uap');?>"
			data-verified_referrals="<?php esc_html_e('Verified Referrals', 'uap');?>"
	></span>
	<span class="uap-js-reports-tick-type" data-value="<?php echo $data['tick_type'];?>"></span>
	<span class="uap-js-min-time" data-value="<?php echo $start_time . '000'; ?>"></span>
	<span class="uap-js-max-time" data-value="<?php echo $end_time . '000';?>"></span>

	<?php
}


if ($data['referrals_graph']){
	/// REFERRALS GRAPH
	?>
		<div class="uap-stuffbox">
			<div class="inside">
				<div id="uap-plot-2" class="uap-plot"></div>
			</div>
		</div>
	<?php
	reset($data['referrals_graph']);
	$first_key = key($data['referrals_graph']);
	if (isset($data['referrals_graph'][$first_key])){
		$start_time = strtotime($first_key);
	}

	end($data['referrals_graph']);
	$last_key = key($data['referrals_graph']);
	if (isset($data['referrals_graph'][$last_key])){
		$end_time = strtotime($last_key);
	}
	reset($data['referrals_graph']);
	?>
	<?php
		if (!empty($data['referrals_graph']) && !empty($data['referrals_graph'])):
			foreach ($data['referrals_graph'] as $date=>$value):?>
				<span class="uap-js-referral-graph-data" data-date="<?php echo strtotime($date) . '000';?>" data-value="<?php echo $value;?>" ></span>
			<?php
			endforeach;
		endif;
	?>
	<?php
	if (!empty($data['referrals_graph-refuse']) && is_array($data['referrals_graph-refuse'])):
		if (count($data['referrals_graph-refuse'])<2 ){
			if (empty($data['referrals_graph-refuse'][$first_key])){
				$data['referrals_graph-refuse'][$first_key] = 0;
			} else if (empty($data['referrals_graph-refuse'][$last_key])){
				$data['referrals_graph-refuse'][$last_key] = 0;
			}
		}
		foreach ($data['referrals_graph-refuse'] as $date=>$value):
			?>
				<span class="uap-js-referral-graph-refuse-data" data-date="<?php echo strtotime($date) . '000';?>" data-value="<?php echo $value;?>" ></span>
			<?php
		endforeach;
	endif;
	?>
	<?php
		if (!empty($data['referrals_graph-unverified']) && is_array($data['referrals_graph-unverified'])):
			if (count($data['referrals_graph-unverified'])<2 ){
				if (empty($data['referrals_graph-unverified'][$first_key])){
					$data['referrals_graph-unverified'][$first_key] = 0;
				} else if (empty($data['referrals_graph-unverified'][$last_key])){
					$data['referrals_graph-unverified'][$last_key] = 0;
				}
			}
			foreach ($data['referrals_graph-unverified'] as $date=>$value):?>
				<span class="uap-js-referral-graph-unverified-data" data-date="<?php echo strtotime($date) . '000';?>" data-value="<?php echo $value;?>" ></span>
			<?php
			endforeach;
		endif;
	?>
	<?php
	if (!empty($data['referrals_graph-verified']) && is_array($data['referrals_graph-verified'])):
		if ( count($data['referrals_graph-verified'])<2 ){
			if (empty($data['referrals_graph-verified'][$first_key])){
				$data['referrals_graph-verified'][$first_key] = 0;
			} else if (empty($data['referrals_graph-verified'][$last_key])){
				$data['referrals_graph-verified'][$last_key] = 0;
			}
		}
		foreach ($data['referrals_graph-verified'] as $date=>$value):?>
			<span class="uap-js-referral-graph-verified-data" data-date="<?php echo strtotime($date) . '000';?>" data-value="<?php echo $value;?>" ></span>
		<?php
		endforeach;
	endif;

}
?>

</div>
