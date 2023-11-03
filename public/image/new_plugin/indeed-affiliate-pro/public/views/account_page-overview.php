<div class="uap-ap-wrap">
<?php if (!empty($data['title'])):?>
	<h3><?php echo $data['title'];?></h3>
<?php endif;?>
<?php if (!empty($data['stats'])):?>
<div class="uap-row">
		<div class="uapcol-md-4 uap-account-overview-tab1">
			<div class="uap-account-no-box uap-account-box-green uap-no-padding">
			 <div class="uap-account-no-box-inside">
			  	<div class="uap-count"> <?php echo $data['stats']['referrals']; ?> </div>
				<div class="uap-detail"><?php echo esc_html__('Total Referrals', 'uap'); ?></div>
                <div class="uap-subnote"><?php echo esc_html__('rewards and commissions received by now', 'uap'); ?></div>
			 </div>
			</div>
		</div>
		<div class="uapcol-md-4 uap-account-overview-tab2">
			<div class="uap-account-no-box uap-account-box-lightyellow uap-no-padding">
			 <div class="uap-account-no-box-inside">
			  	<div class="uap-count"> <?php echo $data['stats']['paid_referrals_count']; ?> </div>
				<div class="uap-detail"><?php echo esc_html__('Paid Referrals', 'uap'); ?></div>
                <div class="uap-subnote"><?php echo esc_html__('withdrawn number of referrals until now', 'uap'); ?></div>
			 </div>
			</div>
		</div>
		<div class="uapcol-md-4 uap-account-overview-tab3">
			<div class="uap-account-no-box uap-account-box-red uap-no-padding">
			 <div class="uap-account-no-box-inside">
			  	<div class="uap-count"> <?php echo $data['stats']['unpaid_referrals_count']; ?> </div>
				<div class="uap-detail"><?php echo esc_html__('UnPaid Referrals', 'uap'); ?></div>
                <div class="uap-subnote"><?php echo esc_html__('which have been not withdrawn yet', 'uap'); ?></div>
			 </div>
			</div>
		</div>
		<div class="uapcol-md-4 uap-account-overview-tab4">
			<div class="uap-account-no-box uap-account-box-lightblue uap-no-padding">
			 <div class="uap-account-no-box-inside">
			  	<div class="uap-count"> <?php echo $data['stats']['payments']; ?> </div>
				<div class="uap-detail"><?php echo esc_html__('Total Payout Transactions', 'uap'); ?></div>
			 </div>
			</div>
		</div>
</div>
<div class="uap-row">
	<div class="uapcol-md-2 uap-account-overview-tab5">
			<div class="uap-account-no-box uap-account-box-lightgray">
			 <div class="uap-account-no-box-inside">
			  	<div class="uap-count"> <?php echo uap_format_price_and_currency($data['stats']['currency'], round($data['stats']['paid_payments_value'], 2) );?> </div>
				<div class="uap-detail"><?php echo esc_html__('Withdrawn  Earnings by Now (total Transactions)', 'uap'); ?></div>
			 </div>
			</div>
		</div>
		<div class="uapcol-md-2 uap-account-overview-tab6">
			<div class="uap-account-no-box uap-account-box-blue">
			 <div class="uap-account-no-box-inside">
			  	<div class="uap-count"> <?php echo uap_format_price_and_currency($data['stats']['currency'], round($data['stats']['unpaid_payments_value'], 2));?> </div>
				<div class="uap-detail"><?php echo esc_html__('Current Account Balance', 'uap'); ?></div>
			 </div>
			</div>
		</div>
</div>
<div class="uap-profile-box-wrapper">
        <div class="uap-profile-box-content">
        	<div class="uap-row ">
            	<div class="uap-col-xs-12">
   						 <div class="uap-account-help-link">
			  				<?php esc_html_e('You can learn more about Affiliate program and to start earning referrals ', 'uap');?>
              					<a href="<?php echo $data['help_url'];?>">
			  						<?php esc_html_e('here', 'uap');?>
              					</a>
                    		</div>
        		</div>
        	</div>
        </div>
     </div>
     <div class="uap-profile-box-wrapper uap-account-summary-wrapper">
        <div class="uap-profile-box-content  uap-no-padding" >
        	<div class="uap-row ">
            	<div class="uap-col-xs-8">
   					<div class="uap-account-summary-month uap-account-summary-graph-warpper">
                    	<div class="uap-account-summary-month-header">
                        	<div class="uap-account-summary-graph-title">
                            	<?php esc_html_e('Earnings Overview', 'uap');?> <span><?php esc_html_e('(for Last 30 days)', 'uap');?></span>

                            </div>
                        </div>
                    	 <div class="uap-account-summary-month-content">
                            <div class="uap-account-summary-graph-content">
                            		<?php esc_html_e('Line Graph for Earnings back to 30 days.', 'uap');?>
																<?php if ( !empty( $data['statsForLast30'] ) ):?>
																	 <div class="col-4" ><canvas id="chart-1" class="uap-canvas" ></canvas></div>
																<?php endif;?>
                            </div>
                            <div class="uap-account-summary-summary-content">
                            	<div class="uap-row">
                                	<div class="uap-col-xs-6 uap-account-summary-summary-content-first-col">
                                    	<div class="uap-account-summary-summary-data-title"><?php esc_html_e('Total Earnings', 'uap');?></div>
                                        <div class="uap-account-summary-summary-data-content"><?php echo uap_format_price_and_currency($data['stats']['currency'], $data['referralsExtraStats']['total_earnings'] );?></div>
                                    </div>

                                	<div class="uap-col-xs-6">
                                    	<div class="uap-account-summary-summary-data-title"><?php esc_html_e('Clicks', 'uap');?></div>
                                        <div class="uap-account-summary-summary-data-content"><?php echo $data['referralsExtraStats']['visits'];?></div>
                                    </div>
                                </div>
                            </div>
                         </div>
                    </div>
        		</div>
                <div class="uap-col-xs-4">
                	<div class="uap-account-summary-month">
                    	<div class="uap-account-summary-month-header">
                        	<div class="uap-account-summary-month-title">
                            	<?php esc_html_e('Summary for This Month', 'uap');?>
                            </div>
                        </div>
                        <div class="uap-account-summary-month-content">
                        	<div class="uap-account-summary-month-data">
                            	<div class="uap-account-summary-month-data-row uap-row uap-account-summary-month-data-row-first">
                                	<div class="uap-col-xs-7"><?php esc_html_e('Total Referrals:', 'uap');?></div><div class="uap-col-xs-5"><?php echo $data['referralsExtraStats']['total_referrals'];?></div>
                                </div>
                                <div class="uap-account-summary-month-data-row uap-row">
                                    <div class="uap-col-xs-7"><?php esc_html_e('Total Earnings:', 'uap');?></div>
																		<div class="uap-col-xs-5">
																			<?php echo uap_format_price_and_currency($data['stats']['currency'], $data['referralsExtraStats']['total_earnings']);?>
																		</div>
                                </div>
                                <div class="uap-account-summary-month-data-row uap-row">
                                    <div class="uap-col-xs-7"><?php esc_html_e('UnVerified Referrals:', 'uap');?></div><div class="uap-col-xs-5"><?php echo $data['referralsExtraStats']['unverified_referrals'];?></div>
                                </div>
                                <div class="uap-account-summary-month-data-row uap-row">
                                    <div class="uap-col-xs-7"><?php esc_html_e('Clicks:', 'uap');?></div><div class="uap-col-xs-5"><?php echo $data['referralsExtraStats']['visits'];?></div>
                                </div>
                                <div class="uap-account-summary-month-data-row uap-row">
                                    <div class="uap-col-xs-7"><?php esc_html_e('Conversion:', 'uap');?></div><div class="uap-col-xs-5"><?php echo $data['referralsStats']['success_rate'];?>%</div>
                                </div>
                        	</div>
                        </div>

                    </div>
                </div>
        	</div>
        </div>
     </div>
	<!--div class="uap-public-general-stats">
		<div><?php echo esc_html__('Total number of Referrals:') . $data['stats']['referrals'];?></div>
		<div><?php echo esc_html__('Total number of Payments:') . $data['stats']['payments'];?></div>
		<div><?php echo esc_html__('Total number of Paid Referrals:') . $data['stats']['paid_referrals_count'];?></div>
		<div><?php echo esc_html__('Total number of UnPaid Referrals:') . $data['stats']['unpaid_referrals_count'];?></div>
		<div><?php echo esc_html__('Total value of Paid Payments:') . uap_format_price_and_currency($data['stats']['currency'], round($data['stats']['paid_payments_value'], 2));?></div>
		<div><?php echo esc_html__('Total value of Unpaid Payments:') . uap_format_price_and_currency($data['stats']['currency'], round($data['stats']['unpaid_payments_value'], 2));?></div>
	</div-->
<?php endif;?>

<?php if (!empty($data['message'])):?>
	<p><?php echo do_shortcode($data['message']);?></p>
<?php endif;?>
</div>

<?php if ( !empty( $data['statsForLast30'] ) ):?>
<?php wp_enqueue_script( 'uap-moment.js', UAP_URL . 'assets/js/moment.min.js', ['jquery'], 7.4 );?>
<?php wp_enqueue_script( 'uap-chart.js', UAP_URL . 'assets/js/chart.min.js', ['jquery'], 7.4 );?>
<?php wp_enqueue_script( 'uap-public-overview', UAP_URL . 'assets/js/public-overview.js', ['jquery'], 7.4, false );?>

<span class="uap-js-overview-earnings-received-label" data-value="<?php echo esc_html__( 'Earnings received', 'uap' ) . ' ('.$data['stats']['currency'].')';?>"></span>
<span class="uap-js-overview-earnings-label" data-value="<?php esc_html_e('Earnings', 'uap');?>"></span>

<?php
foreach( $data['statsForLast30'] as $date => $amount ):?>
		<span class="uap-js-overview-stats-last-30"
		data-date="<?php echo uap_convert_date_to_us_format($date);?>"
		data-amount="<?php echo uap_format_price_and_currency($data['stats']['currency'], $amount );?>"
		data-base_amount="<?php echo $amount;?>"
		<?php
				$temporaryDate = explode( '-', $date);
				$day = isset($temporaryDate[2]) ? $temporaryDate[2] : $date;
				echo "data-label='$day' ";
		?>
		></span>
<?php endforeach;?>


<?php endif;?>
