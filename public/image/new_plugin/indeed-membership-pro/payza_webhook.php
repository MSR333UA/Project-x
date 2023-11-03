<?php
if (empty($no_load)){
	require_once '../../../wp-load.php';
	require_once 'utilities.php';
}
Ihc_User_Logs::write_log( __('Payza Payment Webhook: Start process', 'ihc'), 'payments');

if (!empty($_POST['token'])){
	define("IPN_V2_HANDLER", "https://secure.payza.eu/ipn2.ashx");
	define("TOKEN_IDENTIFIER", "token=");
	$token = TOKEN_IDENTIFIER . urlencode($_POST['token']);

	Ihc_User_Logs::write_log( __('Payza Payment Webhook: Init cURL', 'ihc'), 'payments');
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, IPN_V2_HANDLER);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $token);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_TIMEOUT, 60);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	$response = curl_exec($ch);
	curl_close($ch);

	if (strlen($response)>0){
		$response_decoded = urldecode($response);
		if ("INVALID TOKEN"==$response_decoded){
			Ihc_User_Logs::write_log( __('Payza Payment Webhook: Invalid response. Error', 'ihc'), 'payments');
            exit;
        } else {
			$response_array = explode("&", $response_decoded);
			$ipn_data = array();
			foreach ($response_array as $value){
                $temp = explode("=", $value);
                $ipn_data[$temp[0]] = $temp[1];
            }
			if (!empty($ipn_data['apc_1'])){
				$custom_data = json_decode($ipn_data['apc_1'], TRUE);
			} else {
				/// initialize the array, just to avoid errors
				$custom_data = array('user_id' => '', 'level_id' => '');
			}
			Ihc_User_Logs::set_user_id($custom_data['user_id']);
			Ihc_User_Logs::set_level_id($custom_data['level_id']);

			switch ($ipn_data['ap_status']){
				case 'Success':
				case 'Subscription-Payment-Success':
						$level_data = ihc_get_level_by_id($custom_data['level_id']);//getting details about current level
					if (!empty($ipn_data['ap_trialtimeunit']) && !empty($ipn_data['ap_trialperiodlength'])){
						/// its trial
						ihc_set_level_trial_time_for_no_pay($custom_data['level_id'], $custom_data['user_id']);
						Ihc_User_Logs::write_log( __("Payza Payment Webhook: Update user level expire time (Trial).", 'ihc'), 'payments');
						ihc_send_user_notifications($custom_data['user_id'], 'payment', $custom_data['level_id']);//send notification to user
						ihc_send_user_notifications($custom_data['user_id'], 'admin_user_payment', $custom_data['level_id']);//send notification to admin
						do_action( 'ihc_payment_completed', $custom_data['user_id'], $custom_data['level_id'] );
						// @description run on payment complete. @param user id (integer), level id (integer)
						ihc_switch_role_for_user($custom_data['user_id']);
					} else {
						ihc_update_user_level_expire($level_data, $custom_data['level_id'], $custom_data['user_id']);
						ihc_send_user_notifications($custom_data['user_id'], 'payment', $custom_data['level_id']);//send notification to user
						ihc_send_user_notifications($custom_data['user_id'], 'admin_user_payment', $custom_data['level_id']);//send notification to admin
						do_action( 'ihc_payment_completed', $custom_data['user_id'], $custom_data['level_id'] );
						// @description run on payment complete. @param user id (integer), level id (integer)
						ihc_switch_role_for_user($custom_data['user_id']);
						Ihc_User_Logs::write_log( __("Payza Payment Webhook: Update user level expire time.", 'ihc'), 'payments');
					}
					break;
				case 'Subscription-Canceled':
				case 'Subscription-Payment-Failed':
				case 'Subscription-Expired':
				case 'Refunded':
					if (!function_exists('ihc_is_user_level_expired')){
						require_once IHC_PATH . 'public/functions.php';
					}
					$expired = ihc_is_user_level_expired($custom_data['user_id'], $custom_data['level_id'], FALSE, TRUE);
					if ($expired){
						//it's expired and we must delete user - level relationship
						ihc_delete_user_level_relation($custom_data['level_id'], $custom_data['user_id']);
						Ihc_User_Logs::write_log( __("PayPal Payment IPN: Delete user level.", 'ihc'), 'payments');
					}
					break;
			}
			if ($ipn_data['ap_status']=='Subscription-Expired'){
				exit();
			}
			$ipn_data['uid'] = @$custom_data['user_id'];
			$ipn_data['lid'] = @$custom_data['level_id'];
			$ipn_data['amount'] = @$ipn_data['ap_amount'];
			$ipn_data['ihc_payment_type'] = 'payza';
			$ipn_data['currency'] = $ipn_data['ap_currency'];
			ihc_insert_update_transaction($ipn_data['uid'], $ipn_data['ap_referencenumber'], $ipn_data);

		}
	} else {
		Ihc_User_Logs::write_log( __('Payza Payment Webhook: Invalid response. Error', 'ihc'), 'payments');
	}
}
