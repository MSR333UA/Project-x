<?php
function uap_create_ranks_graphic($ranks_arr, $current){
	/*
	 * @param array
	 * @return string
	 */
	if (is_array($ranks_arr)){
		$new_arr = uap_reorder_ranks($ranks_arr);//reorder ranks by order attr
		$output = '';
		$padding = 7;
		foreach ($new_arr as $k=>$v){
			$class = 'uap-rank-item';
			if ($v->id==$current){
				$current_printed = TRUE;
				$class .= ' uap-current-rank';
			}
			$output .= '<div class="'.$class.'" style = "padding: ' . $padding . 'px 5px;">' . $v->label . '</div>';
			$padding += 7;
		}
		if (empty($current_printed)){
			$output .= '<div class="uap-rank-item uap-current-rank" style = "padding: ' . $padding . 'px 5px;">' . esc_html__('Current Rank', 'uap') . '</div>';
		}
		$output = '<div class="rank-graphic-representation">' . $output . '</div>';
		return $output;
	}
	return '';
}

function uap_return_errors(){
	/*
	 * @param none
	 * @return string
	 */
	$output = '';
	global $uap_error_register;
	if (!empty($uap_error_register)){
		$output = '<div class="uap-wrapp-the-errors">';
		foreach ($uap_error_register as $key=>$err){
			$output .= esc_html__('Field ', 'uap') . $key . ': ' . $err;
		}
		$output .= '</div>';
	}
	return $output;
}

function uap_return_payment_details_for_admin_table($payment_details=array()){
	/*
	 * @param array
	 * @return string
	 */
	 $output = '-';
	 if (!empty($payment_details['type'])){
	 	switch ($payment_details['type']){
			case 'bt':
				if (!empty($payment_details['settings'])){
					$output = '<div class="uap-payment-details-do-payment">' . esc_html__('Bank Transfer Details: ', 'uap') . $payment_details['settings'] . '</div>';
				} else {
					$output = '<div class="uap-payment-details-do-payment">' . esc_html__('Incomplete Payment Settings', 'uap') . '</div>';
				}
				break;
			case 'paypal':
				if (!empty($payment_details['settings'])){
					$output = '<div class="uap-payment-details-do-payment">' . esc_html__('PayPal E-mail Address: ', 'uap') . $payment_details['settings'] . '</div>';
				} else {
					$output = '<div class="uap-payment-details-do-payment">' . esc_html__('Incomplete Payment Settings', 'uap') . '</div>';
				}
				break;
			case 'stripe':
				if (!empty($payment_details['settings']['uap_affiliate_stripe_name'])
					&& !empty($payment_details['settings']['uap_affiliate_stripe_card_number'])
					&& !empty($payment_details['settings']['uap_affiliate_stripe_expiration_month']) && !empty($payment_details['settings']['uap_affiliate_stripe_expiration_year'])){ //&& !empty($payment_details['settings']['uap_affiliate_stripe_cvc'])
					$output = '<div class="uap-payment-details-do-payment">';
					$output .= esc_html__("Name on Card: ", 'uap') . $payment_details['settings']['uap_affiliate_stripe_name'] . ', ';
					$output .= esc_html__("Card Number: ", 'uap') . $payment_details['settings']['uap_affiliate_stripe_card_number'] . ', ';
				
					$output .= esc_html__("Expiration: ", 'uap') . $payment_details['settings']['uap_affiliate_stripe_expiration_month'] . '/' . $payment_details['settings']['uap_affiliate_stripe_expiration_year'];
					$output .= '</div>';
				} else {
					$output = '<div class="uap-payment-details-do-payment">' . esc_html__('Incomplete Payment Settings', 'uap') . '</div>';
				}
				break;
			case 'stripe_v2':
				$output = '';
				break;
			case 'stripe_v3':
				if ( empty( $payment_details['account_id'] ) ){
						$output = '<div class="uap-payment-details-do-payment">' . esc_html__('Incomplete Payment Settings', 'uap') . '</div>';
				} else {
					$stripe_link = '';
					$sandbox = get_option( 'uap_stripe_v3_sandbox' );
					if ( $sandbox ){		
							$stripe_link = 'https://dashboard.stripe.com/test/connect/accounts/'.$payment_details['account_id'];
					}else{
							$stripe_link = 'https://dashboard.stripe.com/connect/accounts/'.$payment_details['account_id'];
					} 
						$output = '<div class="uap-payment-details-do-payment"><a href="' . $stripe_link . '" target="_blank">' . esc_html__( 'View Stripe Affiliate Account', 'uap') . '</a></div>';
				}
				break;
	 	}
	 }
	 return $output;
}
