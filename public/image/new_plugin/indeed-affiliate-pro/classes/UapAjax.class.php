<?php
class UapAjax{

	/**
	 * REGISTER ALL AJAX CALLS HERE
	 * @param none
	 * @return none
	 */
	public function __construct()
	{
		/// PUBLIC
		add_action('wp_ajax_uap_ia_ajax_return_url_for_aff',array($this, 'ia_ajax_return_url_for_aff'));
		add_action('wp_ajax_nopriv_uap_ia_ajax_return_url_for_aff', array($this, 'ia_ajax_return_url_for_aff'));

		add_action('wp_ajax_uap_check_reg_field_ajax',array($this, 'uap_check_reg_field_ajax'));
		add_action('wp_ajax_nopriv_uap_check_reg_field_ajax', array($this, 'uap_check_reg_field_ajax'));

		add_action('wp_ajax_uap_check_logic_condition_value',array($this, 'uap_check_logic_condition_value'));
		add_action('wp_ajax_nopriv_uap_check_logic_condition_value', array($this, 'uap_check_logic_condition_value'));

		add_action('wp_ajax_uap_make_wp_user_affiliate_from_public',array($this, 'uap_make_wp_user_affiliate_from_public'));
		add_action('wp_ajax_nopriv_uap_make_wp_user_affiliate_from_public', array($this, 'uap_make_wp_user_affiliate_from_public'));

		add_action('wp_ajax_uap_get_amount_for_referral_list', array($this, 'uap_get_amount_for_referral_list'));
		add_action('wp_ajax_nopriv_uap_get_amount_for_referral_list', array($this, 'uap_get_amount_for_referral_list'));

		add_action('wp_ajax_uap_delete_wallet_item_via_ajax', array($this, 'uap_delete_wallet_item_via_ajax'));
		add_action('wp_ajax_nopriv_uap_delete_wallet_item_via_ajax', array($this, 'uap_delete_wallet_item_via_ajax'));

		add_action('wp_ajax_nopriv_uap_delete_attachment_ajax_action', array($this, 'uap_delete_attachment_ajax_action'));
		add_action('wp_ajax_uap_delete_attachment_ajax_action', array($this, 'uap_delete_attachment_ajax_action'));

		add_action('wp_ajax_nopriv_uap_check_if_username_is_affiliate', array($this, 'uap_check_if_username_is_affiliate'));
		add_action('wp_ajax_uap_check_if_username_is_affiliate', array($this, 'uap_check_if_username_is_affiliate'));

		add_action('wp_ajax_nopriv_uap_ap_reset_custom_banner', array($this, 'uap_ap_reset_custom_banner'));
		add_action('wp_ajax_uap_ap_reset_custom_banner', array($this, 'uap_ap_reset_custom_banner'));

		add_action( 'wp_ajax_nopriv_uap_ajax_get_banner_for_permalink', [$this, 'uap_ajax_get_banner_for_permalink'] );
		add_action( 'wp_ajax_uap_ajax_get_banner_for_permalink', [$this, 'uap_ajax_get_banner_for_permalink'] );

		add_action( 'wp_ajax_nopriv_uap_search_product_for_product_links', [$this, 'uap_search_product_for_product_links'] );
		add_action( 'wp_ajax_uap_search_product_for_product_links', [$this, 'uap_search_product_for_product_links'] );

		add_action( 'wp_ajax_nopriv_uap_product_link_popup', [$this, 'uap_product_link_popup'] );
		add_action( 'wp_ajax_uap_product_link_popup', [$this, 'uap_product_link_popup'] );

		/// ADMIN
		add_action( 'wp_ajax_uap_register_preview_ajax', array($this, 'uap_register_preview_ajax'));
		add_action( 'wp_ajax_uap_login_form_preview', array($this, 'uap_login_form_preview'));
		add_action( 'wp_ajax_uap_serialize_json', array($this, 'uap_serialize_json'));
		add_action( 'wp_ajax_uap_make_ranks_reorder', array($this, 'uap_make_ranks_reorder'));
		add_action( 'wp_ajax_uap_update_aweber', array($this, 'uap_update_aweber') );
		add_action( 'wp_ajax_uap_get_cc_list', array($this, 'uap_get_cc_list') );
		add_action( 'wp_ajax_uap_get_notification_default_by_type', array($this, 'uap_get_notification_default_by_type'));
		add_action( 'wp_ajax_uap_ajax_admin_popup_the_shortcodes', array($this, 'uap_ajax_admin_popup_the_shortcodes'));
		add_action('wp_ajax_uap_approve_affiliate', array($this, 'uap_approve_affiliate'));
		add_action('wp_ajax_uap_make_wp_user_affiliate', array($this, 'uap_make_wp_user_affiliate'));
		add_action('wp_ajax_uap_delete_currency_code_ajax', array($this, 'uap_delete_currency_code_ajax'));
		add_action('wp_ajax_uap_remove_slug_from_aff', array($this, 'uap_remove_slug_from_aff'));
		add_action('wp_ajax_uap_preview_user_listing', array($this, 'uap_preview_user_listing'));
		add_action('wp_ajax_uap_affiliate_simple_user', array($this, 'uap_affiliate_simple_user'));
		add_action('wp_ajax_uap_approve_user_email', array($this, 'uap_approve_user_email'));
		add_action('wp_ajax_uap_check_mail_server', array($this, 'uap_check_mail_server'));
		add_action('wp_ajax_uap_do_generate_payments_csv', array($this, 'uap_do_generate_payments_csv'));
		add_action('wp_ajax_uap_get_font_awesome_popup', array($this, 'do_get_font_awesome_popup'));
		add_action('wp_ajax_uap_make_export_file', array($this, 'make_export_file'));
		add_action('wp_ajax_uap_trigger_migration', array($this, 'uap_trigger_migration'));
		add_action('wp_ajax_uap_get_empty_progress_bar', array($this, 'uap_get_empty_progress_bar'));
		add_action('wp_ajax_uap_migrate_get_status', array($this, 'uap_migrate_get_status'));
		add_action('wp_ajax_uap_migrate_reset_log', array($this, 'uap_migrate_reset_log'));
		add_action('wp_ajax_uap_close_admin_notice', array($this, 'uap_close_admin_notice'));

		add_action('wp_ajax_uap_admin_send_email_popup', array($this, 'uap_admin_send_email_popup'));
		add_action('wp_ajax_uap_admin_do_send_email', array($this, 'uap_admin_do_send_email'));

		add_action( 'wp_ajax_uap_info_affiliate_bar_do_hide', [ $this, 'uap_info_affiliate_bar_do_hide' ] );

		add_action( 'wp_ajax_nopriv_uap_remove_media_post', [$this, 'uap_remove_media_post'] );
		add_action( 'wp_ajax_uap_remove_media_post', [$this, 'uap_remove_media_post'] );

		add_action( 'wp_ajax_nopriv_uap_ajax_make_csv', [$this, 'uap_ajax_make_csv'] );
		add_action( 'wp_ajax_uap_ajax_make_csv', [$this, 'uap_ajax_make_csv'] );

		add_action( 'wp_ajax_uap_admin_delete_ranks', [$this, 'deleteRanks'] );

		add_action( 'wp_ajax_uap_delete_referrer_link_for_affiliate', [$this, 'uapDeleteReferrerLinkForAffiliate'] );

		add_action( 'wp_ajax_uap_delete_landing_page_for_affiliate', [$this, 'uapDeleteLandingPageForAffiliate'] );

		add_action( 'wp_ajax_uap_delete_coupons_for_affiliate', [$this, 'uapDeleteCouponsForAffiliate'] );

		add_action( 'wp_ajax_nopriv_uap_ajax_save_campaign', [$this, 'saveCampaign'] );
		add_action( 'wp_ajax_uap_ajax_save_campaign', [$this, 'saveCampaign'] );

		add_action( 'wp_ajax_nopriv_uap_ajax_save_simple_link', [$this, 'saveSimpleLinks'] );
		add_action( 'wp_ajax_uap_ajax_save_simple_link', [$this, 'saveSimpleLink'] );

		add_action( 'wp_ajax_uap_ajax_notification_send_test_email', [$this, 'notificationSendTestEmail'] );
		add_action( 'wp_ajax_uap_ajax_do_send_notification_test', [ $this, 'uap_ajax_do_send_notification_test' ] );

		add_action( 'wp_ajax_uap_ajax_do_remove_affiliate_link', [ $this, 'uap_ajax_do_remove_affiliate_link' ] );
		add_action( 'wp_ajax_nopriv_uap_ajax_do_remove_affiliate_link', [ $this, 'uap_ajax_do_remove_affiliate_link' ] );

		add_action( 'wp_ajax_uap_ajax_load_list_affiliate_links_table', [ $this, 'uap_ajax_load_list_affiliate_links_table' ] );
		add_action( 'wp_ajax_nopriv_uap_ajax_load_list_affiliate_links_table', [ $this, 'uap_ajax_load_list_affiliate_links_table' ] );

		add_action( 'wp_ajax_uap_ajax_close_admin_mk_notice', [ $this, 'uap_ajax_close_admin_mk_notice' ] );


	}

	/**
	 * @param none
	 * @return string
	 */
	public function ia_ajax_return_url_for_aff()
	{
		global $current_user;
		if ( empty( $current_user->ID ) ){
				die;
		}

		if ( !uapPublicVerifyNonce() ){
				die;
		}

		if (!empty($_POST['aff_id']) && !empty($_POST['url'])){
			$param = 'ref';
			$_POST['aff_id'] = sanitize_text_field( $_POST['aff_id'] );
			$value = $_POST['aff_id'];
			$campaign_variable = '';
			$campaign_value = '';

			global $indeed_db;
			$settings = $indeed_db->return_settings_from_wp_option('general-settings');
			if (!empty($settings['uap_referral_variable'])){
				$param = $settings['uap_referral_variable'];
			}

			$uid = $indeed_db->get_uid_by_affiliate_id($_POST['aff_id']);
			if ( $uid != $current_user->ID ){
					die;
			}
			if (!empty($_POST['slug'])){
				$slug = $indeed_db->get_custom_slug_for_uid($uid);
				if ($slug){
					$value = $slug;
				}
			} else if ($uid && $settings['uap_default_ref_format']=='username'){
				$user_info = get_userdata($uid);
				if (!empty($user_info->user_login)){

					$value = urlencode($user_info->user_login);
				}
			}

			if ( !empty( $_POST['post_id'] ) ){
					$postId = $_POST['post_id'];
					$url = get_permalink( $postId );
			}
			if ( !isset( $url ) ){
					$url = sanitize_text_field($_POST['url']);
			}

			if (!empty($_POST['campaign'])){
				$campaign_variable = get_option('uap_campaign_variable');
				$campaign_value = sanitize_text_field($_POST['campaign']);
			}

			$arr['url'] = uap_create_affiliate_link($url, $param, $value, $campaign_variable, $campaign_value, $_POST['friendly_links']);
			$arr['social'] = '';
			$arr['qr'] = '';

			// save it to db
			\Indeed\Uap\Db\GeneratedAffiliateLinks::save( [
						'aid'								=> esc_sql( $_POST['aff_id'] ),
						'base_url'					=> $url,
						'affiliate_url'			=> $arr['url'],
						'campaign'					=> esc_sql( $_POST['campaign'] ),
			] );

			/// SOCIAL
			if (uap_is_social_share_intalled_and_active() && get_option('uap_social_share_enable')){
				$shortcode = get_option('uap_social_share_shortcode');
				if ($shortcode){
					$shortcode = stripslashes($shortcode);
					$shortcode = str_replace(']', '', $shortcode);
					$shortcode .= " is_affiliates=1"; ///just for safe
					$shortcode .= " custom_description='" . get_option('uap_social_share_message') . "'";
					$shortcode .= " uap_no_fb_js=1 ";
					$shortcode .= " custom_url='" . $arr['url'] ."']";
					$arr['social'] = do_shortcode($shortcode);
				}
			}
			/// QR CODE
			if ($indeed_db->is_magic_feat_enable('qr_code')){
				$img = uap_generate_qr_code($arr['url'], $_POST['aff_id'] . '_custom_url');
				$arr['qr_code'] = '<div class="uap-qr-code-wrapper">
								<img src="' . $img . '" />
								<a href="' . $img . '" download="' . basename($img) . '" class="uap-qr-code-download">' . esc_html__('Download', 'uap') . '</a>
				</div>';
			}
			echo json_encode($arr);
		}
		die();
	}

	/**
	 * @param none
	 * @return string
	 */
	public function uap_register_preview_ajax()
	{
			if ( !indeedIsAdmin() ){
					die;
			}
			if ( !uapAdminVerifyNonce() ){
					die;
			}
			global $indeed_db;
			require_once UAP_PATH . 'classes/AffiliateAddEdit.class.php';
			$args = array(
						'user_id' => false,
						'type' => 'create',
						'tos' => true,
						'captcha' => true,
						'action' => '',
						'is_public' => true,
						'register_template' => isset($_REQUEST['template']) ? $_REQUEST['template'] : '',
			);
			$type = get_option( 'uap_recaptcha_version' );
			if ( $type !== false && $type == 'v3' ){
					$args['captcha'] = false;
			}
			$obj = new AffiliateAddEdit($args);
			$data = $indeed_db->return_settings_from_wp_option('register');
			$data = $obj->form();
			$data['template'] = isset($_REQUEST['template']) ? $_REQUEST['template'] : '';
			ob_start();
			require_once UAP_PATH . 'public/views/register.php';
			$output = ob_get_contents();
			ob_end_clean();
			echo $output;
			die;
	}

	/**
	 * @param none
	 * @return string
	 */
	public function uap_login_form_preview()
	{
			if ( !indeedIsAdmin() ){
					die;
			}
			if ( !uapAdminVerifyNonce() ){
					die;
			}
			$metas['uap_login_remember_me'] = $_REQUEST['remember'];
			$metas['uap_login_register'] = $_REQUEST['register_link'];
			$metas['uap_login_pass_lost'] = $_REQUEST['pass_lost'];
			$metas['uap_login_template'] = $_REQUEST['template'];
			$metas['uap_login_custom_css'] = $_REQUEST['css'];
			$metas['uap_login_show_recaptcha'] = $_REQUEST['uap_login_show_recaptcha'];
			$type = get_option( 'uap_recaptcha_version' );
			if ( $type !== false && $type == 'v3' ){
					$metas['uap_login_show_recaptcha'] = false;
			}
			require_once UAP_PATH . 'public/AffiliateLogin.class.php';
			$object = new AffiliateLogin();
			echo $object->print_login_form($metas, 'unreg');
			die;
	}

	/**
	 * @param none
	 * @return string
	 */
	public function uap_check_reg_field_ajax()
	{
		global $indeed_db;
		if ( !uapPublicVerifyNonce() ){
			die;
		}
		$register_msg = $indeed_db->return_settings_from_wp_option('register-msg');
		if (isset($_REQUEST['type']) && isset($_REQUEST['value'])){
			echo uap_check_value_field($_REQUEST['type'], $_REQUEST['value'], $_REQUEST['second_value'], $register_msg);
		} else if (isset($_REQUEST['fields_obj'])){
			$arr = $_REQUEST['fields_obj'];
			foreach ($arr as $k=>$v){
				$return_arr[] = array( 'type' => $v['type'], 'value' => uap_check_value_field($v['type'], $v['value'], $v['second_value'], $register_msg) );
			}
			echo json_encode($return_arr);
		}
		die();
	}

	/**
	 * @param none
	 * @return string
	 */
	public function uap_check_logic_condition_value()
	{
			if ( !uapPublicVerifyNonce() ){
				die;
			}
			if (isset($_REQUEST['val']) && isset($_REQUEST['field'])){
				global $indeed_db;
				$fields_meta = $indeed_db->register_get_custom_fields();
				$key = uap_array_value_exists($fields_meta, $_REQUEST['field'], 'name');
				if ($key!==FALSE){
					if (isset($fields_meta[$key]['conditional_logic_corresp_field_value'])){
						if ($fields_meta[$key]['conditional_logic_cond_type']=='has'){
							//has value
							if ($fields_meta[$key]['conditional_logic_corresp_field_value']==$_REQUEST['val']){
								echo 1;
								die;
							}
						} else {
							//contain value
							if (strpos($_REQUEST['val'], $fields_meta[$key]['conditional_logic_corresp_field_value'])!==FALSE){
								echo 1;
								die;
							}
						}
					}
				}
			}
			echo 0;
			die;
	}

	/**
	 * @param none
	 * @return string
	 */
	public function uap_make_ranks_reorder()
	{
			if ( !indeedIsAdmin() ){
					die;
			}
			if ( !uapAdminVerifyNonce() ){
					die;
			}
			if (!empty($_REQUEST['new_order']) && isset($_REQUEST['rank_id']) && isset($_REQUEST['current_label'])){
				global $indeed_db;
				$data = $indeed_db->get_ranks();
				if ($_REQUEST['rank_id']==0){
					global $wpdb;
					$table = $wpdb->prefix . 'uap_ranks';
					$query = "SELECT rank_order FROM $table ORDER BY rank_order DESC LIMIT 1";
					$data_db = $wpdb->get_row( $query );
					$old_order = (empty($data_db->rank_order)) ? 1 : $data_db->rank_order + 1;
					$arr['rank_order'] = $old_order;
					$arr['id'] = 0;
					$arr['label'] = $_REQUEST['current_label'];
					$object = new stdClass();
					foreach ($arr as $key => $value){
						$object->$key = $value;
					}
					$data[] = $object;
				}
				$ranks = uap_custom_reorder_rank($data, $_REQUEST['rank_id'], $_REQUEST['new_order']);
				if (!function_exists('uap_create_ranks_graphic')){
					require_once UAP_PATH . 'admin/utilities.php';
				}
				echo uap_create_ranks_graphic($ranks, $_REQUEST['rank_id']);
			}
			die;
	}

	/**
	 * @param none
	 * @return int
	 */
	public function uap_update_aweber()
	{
			if ( !indeedIsAdmin() ){
					die;
			}
			if ( !uapAdminVerifyNonce() ){
					die;
			}
			include_once UAP_PATH .'classes/services/email_services/aweber/aweber_api.php';
			list($consumer_key, $consumer_secret, $access_key, $access_secret) = AWeberAPI::getDataFromAweberID( $_REQUEST['auth_code'] );
			update_option( 'uap_aweber_consumer_key', $consumer_key );
			update_option( 'uap_aweber_consumer_secret', $consumer_secret );
			update_option( 'uap_aweber_acces_key', $access_key );
			update_option( 'uap_aweber_acces_secret', $access_secret );
			echo 1;
			die;
	}

	/**
	 * @param none
	 * @return string
	 */
	public function uap_get_cc_list()
	{
			if ( !indeedIsAdmin() ){
				  die;
			}
			if ( !uapAdminVerifyNonce() ){
				die;
			}
			echo json_encode(uap_return_cc_list($_REQUEST['uap_cc_user'],$_REQUEST['uap_cc_pass']));
			die;
	}

	/**
	 * @param none
	 * @return string
	 */
	public function uap_get_notification_default_by_type()
	{
			if ( !indeedIsAdmin() ){
					die;
			}
			if ( !uapAdminVerifyNonce() ){
					die;
			}
			if (!empty($_POST['type'])){
					$template = uap_return_default_notification_content($_POST['type']);
					if ($template){
							echo json_encode($template);
					}
			}
			die;
	}

	/**
	 * @param none
	 * @return string
	 */
	public function uap_ajax_admin_popup_the_shortcodes()
	{
			if ( !indeedIsAdmin() ){
					die;
			}
			if ( !uapAdminVerifyNonce() ){
					die;
			}
			require_once UAP_PATH . 'admin/views/popup-shortcodes.php';
			die;
	}

	/**
	 * @param none
	 * @return string
	 */
	public function uap_approve_affiliate()
	{
			if ( !indeedIsAdmin() ){
					die;
			}
			if ( !uapAdminVerifyNonce() ){
					die;
			}
			if (!empty($_REQUEST['uid'])){
				$role = get_option('uap_after_approve_role');
				if (empty($role)){
						$role = get_option('default_role');
				}
				$new_role = empty($role) ? 'subscriber' : $role;
				$uid = wp_update_user(array( 'ID' => $_REQUEST['uid'], 'role' => $new_role));
				uap_send_user_notifications($_REQUEST['uid'], 'affiliate_account_approve');
				echo 1;
			}
			die;
	}

	/**
	 * @param none
	 * @return string
	 */
	public function uap_make_wp_user_affiliate()
	{
			if ( !indeedIsAdmin() ){
				 die;
			}
			if ( !uapAdminVerifyNonce() ){
					die;
			}
			if (!empty($_REQUEST['uid'])){
				 	global $indeed_db;
				 	if ($indeed_db->is_user_admin($_REQUEST['uid'])){
				 		echo 2;
						die;
				 	}
					$inserted = $indeed_db->save_affiliate($_REQUEST['uid']);
					if ($inserted){
						/// put default rank on this new affiliate
						$default_rank = get_option('uap_register_new_user_rank');
						$indeed_db->update_affiliate_rank_by_uid($_REQUEST['uid'], $default_rank);
						echo 1;
					}
			}
			die;
	}

	/**
	 * @param none
	 * @return string
	 */
	public function uap_make_wp_user_affiliate_from_public()
	{
		  global $current_user, $indeed_db;
			if ( !uapPublicVerifyNonce() ){
					die;
			}
		  if (!empty($current_user) && !empty($current_user->ID)){
			 	$uid = $current_user->ID;
			 	if ($indeed_db->is_user_admin($uid)){
				 		echo 0;
						die;
			 	}

				$inserted = $indeed_db->save_affiliate($uid);
				if ($inserted){
					/// put default rank on this new affiliate
					$default_rank = get_option('uap_register_new_user_rank');
					$indeed_db->update_affiliate_rank_by_uid($uid, $default_rank);

					/// SET MLM RELATION
					$indeed_db->set_mlm_relation_on_new_affiliate($inserted);

					//SEND NOTIFICATIONS
					uap_send_user_notifications($uid, 'register', $default_rank);//notify the affiliate
					uap_send_user_notifications($uid, 'admin_user_register', $default_rank);//notify the admin
				}
				$pid = get_option('uap_general_user_page');
				if ($pid){
					 $new_url = get_permalink($pid);
				}
				if (!$new_url){
					 $new_url = get_home_url();
				}
				echo $new_url;
		 }
		 die;
	}

	/**
	 * @param none
	 * @return string
	 */
	public function uap_delete_currency_code_ajax()
	{
			if ( !indeedIsAdmin() ){
					die;
			}
			if ( !uapAdminVerifyNonce() ){
					die;
			}
			if (isset($_REQUEST['code'])){
					$data = get_option('uap_currencies_list');
					if (!empty($data[$_REQUEST['code']])){
							unset($data[$_REQUEST['code']]);
							echo 1;
					}
					update_option('uap_currencies_list', $data);
			}
			die;
	}

	/**
	 * @param none
	 * @return none
	 */
	public function uap_remove_slug_from_aff()
	{
			if ( !indeedIsAdmin() ){
					die;
			}
			if ( !uapAdminVerifyNonce() ){
					die;
			}
			if (!empty($_POST['uid'])){
			 		update_user_meta($_POST['uid'], 'uap_affiliate_custom_slug', '');
			}
			die;
	}

	/**
	 * @param none
	 * @return string
	 */
	public function uap_get_amount_for_referral_list()
	{
			global $current_user;
			if ( empty( $current_user->ID ) ){
					die;
			}
			if ( !uapPublicVerifyNonce() ){
					die;
			}
			if (!empty($_POST['r'])){
					global $indeed_db;
					$referral_list = explode(',', $_POST['r']);
					if (!empty($referral_list) && count($referral_list)){
							$amount = $indeed_db->get_amount_for_referrals($referral_list);
							echo $amount;
					}
			}
			die;
	}

	/**
	 * @param none
	 * @return int
	 */
	public function uap_delete_wallet_item_via_ajax()
	{
		 if ( !uapPublicVerifyNonce() ){
			  die;
		 }
		 if (!empty($_POST['code']) && !empty($_POST['type'])){
			 	 global $indeed_db;
				 global $current_user;
				 $uid = (empty($current_user->ID)) ? 0 : $current_user->ID;
				 $affiliate_id = $indeed_db->get_affiliate_id_by_wpuid($uid);
				 if ($affiliate_id){
						 $indeed_db->delete_wallet_item($_POST['type'], $affiliate_id, $_POST['code']);
						 echo 1;
				 }
		 }
		 die;
	}

	/**
	 * @param none
	 * @return string
	 */
	public function uap_preview_user_listing()
	{
			if ( !indeedIsAdmin() ){
					die;
			}
			if ( !uapAdminVerifyNonce() ){
					die;
			}
			if (!empty($_REQUEST['shortcode'])){
					define('IS_PREVIEW', TRUE);
					$shortcode = stripslashes($_REQUEST['shortcode']);
					require_once UAP_PATH . 'public/UapMainPublic.class.php';
					$uap_main_object = new UapMainPublic();
					echo do_shortcode($shortcode);
			}
			die;
	}

	/**
	 * @param none
	 * @return int
	 */
	public function uap_delete_attachment_ajax_action()
	{
		 $uid = isset($_POST['user_id']) ? esc_sql($_POST['user_id']) : 0;
		 $field_name = isset($_POST['field_name']) ? esc_sql($_POST['field_name']) : '';
		 $attachment_id = isset($_POST['attachemnt_id']) ? esc_sql($_POST['attachemnt_id']) : 0;

		 if (function_exists('is_user_logged_in') && is_user_logged_in()){
			 $current_user = wp_get_current_user();
			 if ( !empty($uid) && $uid == $current_user->ID ){
					 /// registered users
					 if ( !uapPublicVerifyNonce() ){
 							die;
 					 }
					 if (!empty($attachment_id)){
							 $verify_attachment_id  = get_user_meta($uid, $field_name, TRUE);
							 if ($verify_attachment_id==$attachment_id){
									 wp_delete_attachment($attachment_id, TRUE);
									 update_user_meta($uid, $field_name, '');
									 echo 0;
									 die();
							 }
					 }
			 } else if (current_user_can('manage_options')){
					/// ADMIN, no extra checks
					if ( !uapAdminVerifyNonce() ){
						  die;
					}
					wp_delete_attachment($attachment_id, TRUE);
					update_user_meta($uid, $field_name, '');
			 }
		 } else if ($uid==-1){
			 		if ( !uapPublicVerifyNonce() ){
							die;
					}
				 /// unregistered user
				 $hash_from_user = isset($_POST['h']) ? esc_sql($_POST['h']) : '';
				 $attachment_url = wp_get_attachment_url($attachment_id);
				 $attachment_hash = md5($attachment_url);
				 if (empty($hash_from_user) || empty($attachment_hash) || $hash_from_user!==$attachment_hash){
						 echo 1;die;
				 } else {
						 wp_delete_attachment($attachment_id, TRUE);
						 echo 0;
						 die;
				 }
		 }

		 echo 1;
		 die;
	}

	/**
	 * @param none
	 * @return none
	 */
	public function uap_affiliate_simple_user()
	{
			if ( !indeedIsAdmin() ){
					die;
			}
			if ( !uapAdminVerifyNonce() ){
					die;
			}
			if (!empty($_POST['uid'])){
			 	global $indeed_db;
				$indeed_db->remove_user_from_affiliate($_POST['uid']);
			}
			die;
	}

	/**
	 * @param none
	 * @return int
	 */
	public function uap_approve_user_email()
	{
		  if ( !indeedIsAdmin() ){
				die;
		  }
			if ( !uapAdminVerifyNonce() ){
				die;
			}
		  if (!empty($_POST['uid'])){
		 	  update_user_meta($_POST['uid'], 'uap_verification_status', 1);
			  echo 1;
		  }
		  die;
	}

	/**
	 * @param none
	 * @return int
	 */
	public function uap_check_mail_server()
	{
			if ( !indeedIsAdmin() ){
					die;
			}
			if ( !uapAdminVerifyNonce() ){
				  die;
			}
			$from_email = '';
			$from_name = '';
			$from_email = get_option('uap_notification_email_from');
			if (!$from_email){
				$from_email = get_option('admin_email');
			}
			$from_name = get_option('uap_notification_name');
			if (empty($from_name)){
				$from_name = get_option("blogname");
			}
			$headers[] = "From: $from_name <$from_email>";
			$headers[] = 'Content-Type: text/html; charset=UTF-8';

			$to = get_option('admin_email');
			$subject = get_option('blogname') . ': ' . esc_html__('Testing Your E-mail Server', 'uap');
			$content = esc_html__('Just a simple message to test if Your E-mail Server is working', 'uap');
			wp_mail($to, $subject, $content, $headers);
			echo 1;
			die;
	}

	/**
	 * @param none
	 * @return string
	 */
	public function uap_check_if_username_is_affiliate()
	{
		 if ( $_POST['username'] ){
			 	global $indeed_db;
			 	$affiliate_id = $indeed_db->get_affiliate_id_by_username( $_POST['username'] );
				if (!$affiliate_id){
						echo 1;
						die;
				}
		 }
		 echo 0;
		 die;
	}


	/**
	 * @param none
	 * @return string
	 */
	public function uap_do_generate_payments_csv()
	{
			if ( !indeedIsAdmin() ){
				 die;
			}
			if ( !uapAdminVerifyNonce() ){
				 die;
			}
			require_once UAP_PATH . 'classes/PayoutPaymentsExport.class.php';
			$obj = new PayoutPaymentsExport();

			if (!empty($_POST['min_date'])){
				$obj->set_min_date($_POST['min_date']);
			}
			if (!empty($_POST['max_date'])){
				$obj->set_max_date($_POST['max_date']);
			}
			if (!empty($_POST['payment_type'])){
				$obj->set_payment_type($_POST['payment_type']);
			}
			if (!empty($_POST['switch_status'])){
				$obj->set_new_status($_POST['switch_status']);
			}

			echo $obj->generate_csv();
			die;
	}

	/**
	 * @param none
	 * @return string
	 */
		public function do_get_font_awesome_popup()
		{
				if ( !indeedIsAdmin() ){
					 die;
				}
				if ( !uapAdminVerifyNonce() ){
					die;
				}
				ob_start();
				require_once UAP_PATH . 'admin/views/popup-font_awesome.php';
				$output = ob_get_contents();
				ob_end_clean();
				echo $output;
				die;
		}


	/**
	 * @param none
	 * @return string
	 */
		public function make_export_file()
		{
				if ( !indeedIsAdmin() ){
					 die;
				}
				if ( !uapAdminVerifyNonce() ){
					die;
				}
				global $wpdb, $indeed_db;
				require_once UAP_PATH . 'classes/import-export/IndeedExport.class.php';
				$export = new IndeedExport();
				$hash = bin2hex( random_bytes( 20 ) );
				$filename = $hash . '.xml';
				$targetFile = UAP_PATH . 'temporary/' . $filename;
				$export->setFile( $targetFile );
				$export->deleteOldFiles();
				if (!empty($_POST['import_users'])){

					$export->setGetUsers(TRUE);
				}
				if (!empty($_POST['import_settings'])){
					///////// SETTINGS
					$values = $indeed_db->get_all_ump_wp_options();
					$export->setEntity( array('full_table_name' => $wpdb->base_prefix . 'options', 'table_name' => 'options', 'values' => $values) );
					$export->setEntity( array('full_table_name' => $wpdb->prefix . 'uap_banners', 'table_name' => 'uap_banners') );
					$export->setEntity( array('full_table_name' => $wpdb->prefix . 'uap_notifications', 'table_name' => 'uap_notifications') );
					$export->setEntity( array('full_table_name' => $wpdb->prefix . 'uap_ranks', 'table_name' => 'uap_ranks') );
					$export->setEntity( array('full_table_name' => $wpdb->prefix . 'uap_offers', 'table_name' => 'uap_offers') );
					$export->setEntity( array('full_table_name' => $wpdb->prefix . 'uap_offers_affiliates_reference', 'table_name' => 'uap_offers_affiliates_reference') );
					$export->setEntity( array('full_table_name' => $wpdb->prefix . 'uap_mlm_relations', 'table_name' => 'uap_mlm_relations') );
					$export->setEntity( array('full_table_name' => $wpdb->prefix . 'uap_ranks_history', 'table_name' => 'uap_ranks_history') );
					$export->setEntity( array('full_table_name' => $wpdb->prefix . 'uap_landing_commissions', 'table_name' => 'uap_landing_commissions') );
					$export->setEntity( array('full_table_name' => $wpdb->prefix . 'uap_coupons_code_affiliates', 'table_name' => 'uap_coupons_code_affiliates') );
					$export->setEntity( array('full_table_name' => $wpdb->prefix . 'uap_reports', 'table_name' => 'uap_reports') );
					$export->setEntity( array('full_table_name' => $wpdb->prefix . 'uap_ref_links', 'table_name' => 'uap_ref_links') );
				}
				if ($export->run()){
					/// print link to file
					echo UAP_URL . 'temporary/' . $filename;
				} else {
					/// no entity
					echo 0;
				}
				die;
		}

	/**
	 * @param none
	 * @return none
	 */
		public function uap_trigger_migration()
		{
				if ( !indeedIsAdmin() ){
					 die;
				}
				if ( !uapAdminVerifyNonce() ){
					die;
				}
				$serviceType = isset($_POST['serviceType']) ? $_POST['serviceType'] : false;

				if (empty($serviceType)){
						echo 0;
						die;
				}
				$data = get_option('uap_do_migrate_log');
				if (isset($data[$serviceType])){
						unset($data[$serviceType]);
						update_option('uap_do_migrate_log', $data);
				}
				$assignRank = isset($_POST['assignRank']) ? $_POST['assignRank'] : '';

				$postRequest = array(
			          'service_type'  => $serviceType,
			          'entity_type'   => false,
			          'offset'        => 0,
			          'assignRank'    => $assignRank
				);
				$curl = curl_init( admin_url('admin.php?uap_act=migrate') );
				curl_setopt( $curl, CURLOPT_POSTFIELDS, $postRequest );
				curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );

				$response = curl_exec($curl);
				curl_close($curl);
				exit;
		}

	/**
	 * @param none
	 * @return string
	 */
		public function uap_get_empty_progress_bar()
		{
				if ( !indeedIsAdmin() ){
					 die;
				}
				if ( !uapAdminVerifyNonce() ){
					 die;
				}
				include UAP_PATH . 'admin/views/empty-progress-bar.php';
				die;
		}

		/**
		 * @param none
		 * @return int
		 */
		public function uap_migrate_get_status()
		{
				if ( !indeedIsAdmin() ){
					 die;
				}
				if ( !uapAdminVerifyNonce() ){
					 die;
				}
				$serviceType = isset($_POST['serviceType']) ? $_POST['serviceType'] : '';
				if (empty($serviceType)){
						echo -1;
						die;
				}
				$data = get_option('uap_do_migrate_log');
				$logData = isset($data[$serviceType]) ? $data[$serviceType] : false;
				if (empty($logData)){
					 echo -1;
					 die;
				}
				if ($logData=='completed'){
						echo 100;
						die;
				}
				$total = 0;
				$total += empty($logData['affiliates-count']) ? 0 : $logData['affiliates-count'];
				$total += empty($logData['referrals-count']) ? 0 : $logData['referrals-count'];
				$current = 0;
				$current += empty($logData['affiliates-offset']) ? 0 : $logData['affiliates-offset'];
				$current += empty($logData['referrals-offset']) ? 0 : $logData['referrals-offset'];
				$percentage = (100 * $current)/$total;
				echo (int)$percentage;
				die;
		}

		/**
		 * @param none
		 * @return int
		 */
		public function uap_migrate_reset_log()
		{
			if ( !indeedIsAdmin() ){
				 die;
			}
			if ( !uapAdminVerifyNonce() ){
				die;
			}
			$serviceType = isset($_POST['serviceType']) ? $_POST['serviceType'] : '';
			if (empty($serviceType)){
					echo -1;
					die;
			}
			$data = get_option('uap_do_migrate_log');
			if (isset($data[$serviceType])){
					unset($data[$serviceType]);
					update_option('uap_do_migrate_log', $data);
					echo 1;
			}
			die;
		}

		/**
		 * @param none
		 * @return string
		 */
		public function uap_ap_reset_custom_banner()
		{
				global $current_user;
				$uid = isset($current_user->ID) ? $current_user->ID : 0;
				if ( !uapPublicVerifyNonce() ){
					die;
				}
				if (empty($uid)){
						die;
				}
				$banner = isset($_POST['oldBanner']) ? esc_sql($_POST['oldBanner']) : '';
				if (empty($banner)){
						die;
				}
				update_user_meta($uid, 'uap_account_page_personal_header', $banner);
				die;
		}

		/**
		 * @param none
		 * @return string
		 */
		public function uap_admin_send_email_popup()
		{
				if ( !indeedIsAdmin() ){
					  die;
				}
				if ( !uapAdminVerifyNonce() ){
						die;
				}
				global $indeed_db;
				$uid = empty($_POST['uid']) ? 0 : esc_sql($_POST['uid']);
				if (empty($uid)){
						die;
				}
				$toEmail = $indeed_db->get_user_col_value($uid, 'user_email');
				if (empty($toEmail)){
						die;
				}
				$fromEmail = '';
				$fromEmail = get_option('uap_notifications_from_email_addr');
				if (empty($fromEmail)){
						$fromEmail = get_option('admin_email');
				}
				$view = new \Indeed\Uap\IndeedView();
				$view->setTemplate(UAP_PATH . 'admin/views/send-email-popup.php');
				$view->setContentData([
																'toEmail' 		=> $toEmail,
																'fromEmail' 	=> $fromEmail,
																'fullName'		=> $indeed_db->getUserFullName($uid),
																'website'			=> get_option('blogname')
				], true);
				echo $view->getOutput();
				die;
		}

		/**
		 * @param none
		 * @return string
		 */
		public function uap_admin_do_send_email()
		{
				if ( !indeedIsAdmin() ){
					 die;
				}
				if ( !uapAdminVerifyNonce() ){
					 die;
				}
				$to = empty($_POST['to']) ? '' : esc_sql($_POST['to']);
				$from = empty($_POST['from']) ? '' : esc_sql($_POST['from']);
				$subject = empty($_POST['subject']) ? '' : esc_sql($_POST['subject']);
				$message = empty($_POST['message']) ? '' :  stripslashes(htmlspecialchars_decode(uap_format_str_like_wp($_POST['message'])));
				$headers = [];

				if (empty($to) || empty($from) || empty($subject) || empty($message)){
						die;
				}

				$from_name = get_option('uap_notification_name');
				$from_name = stripslashes($from_name);
				if (!empty($from) ){ // && !empty($from_name)
					$headers[] = "From: $from_name <$from>";
				}
				$headers[] = 'Content-Type: text/html; charset=UTF-8';
				$sent = wp_mail($to, $subject, $message, $headers);
				echo $sent;
				die;
		}

		/**
		 * @param none
		 * @return string
		 */
		public function uap_info_affiliate_bar_do_hide()
		{
				setcookie( 'uap_info_affiliate_bar_hide', 1, time() + 60 * 60 * 24, '/', $_SERVER['HTTP_HOST'] );
				echo 1;
				die;
		}

		/**
		 * @param none
		 * @return string
		 */
		public function uap_ajax_get_banner_for_permalink()
		{
				if ( !uapPublicVerifyNonce() ){
				  	die;
				}
				if ( empty( $_POST['uid'] ) || empty( $_POST['affiliate_id'] ) || empty( $_POST['url'] ) || empty( $_POST['size'] ) ){
						echo '';
						die;
				}
				$AffiliateMarketingBuilder = new \Indeed\Uap\AffiliateMarketingBuilder();
				$AffiliateMarketingBuilder->setUid( esc_sql( $_POST['uid'] ) )->setAffiliateId( esc_sql( $_POST['affiliate_id'] ) )->setCurrentPermalink( esc_sql( $_POST['url'] ) );
				$banner = $AffiliateMarketingBuilder->getBannerForPermalink( esc_sql( $_POST['size'] ) );
				if ( !$banner ){
	            $banner = $AffiliateMarketingBuilder->getDefaultBAnnerForPermalink();
				}
				echo $banner;
				die;
		}

		/**
		 * @param none
		 * @return string
		 */
		public function uap_search_product_for_product_links()
		{

				if ( indeedIsAdmin() ){
						// admin check
						if ( !uapAdminVerifyNonce() ){
								die;
						}
				} else {
						// public
						if ( !uapPublicVerifyNonce() ){
								die;
						}
				}
				global $indeed_db;
				$limit = 5;
				$returnData = [
									'html'				=> '',
									'offset'			=> $_POST['offset'],
									'showMore'		=> 0,
				];

				$typeOfService = get_option( 'uap_product_links_source' );
				if ( !$typeOfService ){
						echo $returnData;
						die;
				}
				$uid = indeed_get_uid();
				if ( !$uid ){
						echo $returnData;
						die;
				}
				$affiliateId = $indeed_db->get_affiliate_id_by_wpuid( $uid );
				if ( !$affiliateId ){
						echo $returnData;
						die;
				}

				$productsObject = new \Indeed\Uap\Db\Products();
				$productsObject->setLimit( $limit )
											 ->setOffset( esc_sql( $_POST['offset'] ) )
											 ->setType( $typeOfService )
											 ->setSearchPhrase( esc_sql( $_POST['search'] ) )
											 ->setOrderBy( esc_sql( $_POST['orderBy'] ) )
											 ->setAffiliateId( $affiliateId );
				if ( !empty($_POST['category']) ){
						$productsObject->setProductCategory( esc_sql( $_POST['category'] ) );
				}
				$products = $productsObject->getResults();
				$productsCount = $productsObject->getCount();

				$viewObject = new \Indeed\Uap\IndeedView();
				$html = '';
				if ( !$products ){
						echo json_encode( $returnData );
						die;
				}

				if ( $productsCount > $returnData['offset'] + $limit ){
						$returnData['showMore'] = 1;
				}


				$AffiliateMarketingBuilder = new \Indeed\Uap\AffiliateMarketingBuilder();
				$AffiliateMarketingBuilder->setUid( $uid )->setAffiliateId( $affiliateId );
				$showReward = get_option( 'uap_product_links_reward_calculation' );

				foreach ( $products as $productData ){
						$productData['showReward'] = $showReward;
						$productData['affiliateLink'] = $AffiliateMarketingBuilder->setCurrentPermalink( $productData['permalink'] )->getPermalinkForAffiliate();
						$html .= $viewObject->setContentData( $productData )->setTemplate( UAP_PATH . 'public/views/product_links/single_product.php' )->getOutput();
				}
				$returnData['count'] = $productsCount;
				$returnData['offset'] = $_POST['offset'] + count( $products );
				$returnData['html'] = $html;
				echo json_encode( $returnData );
				die;
		}

		/**
		 * @param none
		 * @return string
		 */
		public function uap_product_link_popup()
		{
				global $indeed_db;
				if ( empty($_POST['postId']) ){
						echo '';
						die;
				}
				$permalink = get_permalink( esc_sql( $_POST['postId'] ) );
				$uid = indeed_get_uid();
				if ( empty( $uid ) ) {
						echo '';
						die;
				}
				$affiliateId = $indeed_db->get_affiliate_id_by_wpuid( $uid );
				if ( empty( $affiliateId ) ) {
						echo '';
						die;
				}
				if ( !uapPublicVerifyNonce() ){
					  die;
				}
				$AffiliateMarketingBuilder = new \Indeed\Uap\AffiliateMarketingBuilder();
				$AffiliateMarketingBuilder->setUid( $uid )->setAffiliateId( $affiliateId )->setCurrentPermalink( $permalink );
				$data = [
							'friendly_links'          	=> $indeed_db->is_magic_feat_enable('friendly_links'),
							'custom_affiliate_slug'   	=> $indeed_db->is_magic_feat_enable('custom_affiliate_slug'),
							'the_slug'									=> $indeed_db->get_custom_slug_for_uid( $uid ),
							'uap_default_ref_format'		=> get_option('uap_default_ref_format'),
							'ref_type'									=> (get_option('uap_default_ref_format')=='username') ? esc_html__('Username', 'uap') : 'Id',
							'affiliate_id'							=> $affiliateId,
							'url'												=> $AffiliateMarketingBuilder->getPermalinkForAffiliate(),
							'post_id'										=> esc_sql( $_POST['postId'] ),
				];
				$view = new \Indeed\Uap\IndeedView();
				echo $view->setContentData( $data )->setTemplate( UAP_PATH . 'public/views/product_links/show_link_popup.php' )->getOutput();
				die;
		}

		/**
		 * @param none
		 * @return string
		 */
		public function uap_close_admin_notice()
		{
				if ( !indeedIsAdmin() ){
					  die;
				}
				if ( !uapAdminVerifyNonce() ){
					  die;
				}
				update_option( 'uap_hide_admin_license_notice', 1 );
				echo 1;
				die;
		}

		/**
		 * @param none
		 * @return string
		 */
		public function uap_remove_media_post()
		{
				if ( indeedIsAdmin() ){
						// admin check
						if ( !uapAdminVerifyNonce() ){
								die;
						}
				} else {
						// public
						if ( !uapPublicVerifyNonce() ){
							die;
						}
				}
				if ( empty( $_POST['postId'] ) ){
						return;
				}
				wp_delete_attachment( esc_sql( $_POST['postId'] ), true );
				die;
		}

		/**
		 * @param none
		 * @return string
		 */
		public function uap_ajax_make_csv()
		{
				if ( !indeedIsAdmin() ){
					  die;
				}
				if ( !uapAdminVerifyNonce() ){
						die;
				}
				if ( empty( $_POST['exportType'] ) ){
						echo 0;
						die;
				}
				$filters = isset($_POST['filters']) ? unserialize( stripslashes($_POST['filters']) ) : false;
				$exportCsvObject = new \Indeed\Uap\ExportDataAsCsv();
				$link = $exportCsvObject->setTypeOfData( $_POST['exportType'] )
																->setFilters( $filters )
														 		->run()
														 		->getDownloadLink();
				if ( !$link ){
						echo 0;
						die;
				}
				echo $link;
				die;
		}

		/**
		 * @param none
		 * @return none
		 */
		public function deleteRanks()
		{
				global $indeed_db;
				if ( empty( $_POST['id'] ) || empty($_POST['uap_admin_forms_nonce']) || !wp_verify_nonce( $_POST['uap_admin_forms_nonce'], 'uap_admin_forms_nonce' ) ){
						echo 'error';
						die;
				}
				if ($indeed_db->ranks_get_count()>1){
						$indeed_db->delete_rank($_POST['id']);
						echo 'success';
						die;
				} else {
					 esc_html_e('You cannot have less than one rank.', 'uap');
					die;
				}
		}

		/**
		 * @param none
		 * @return none
		 */
		public function uapDeleteReferrerLinkForAffiliate()
		{
				global $indeed_db;
				if ( !indeedIsAdmin() ){
						die;
				}
				if ( !uapAdminVerifyNonce() ){
						die;
				}
				if ( empty( $_POST['id'] ) ){
						echo 0;
						die;
				}
				$indeed_db->simple_links_delete_link( $_POST['id'] );
				die;
		}

		/**
		 * @param none
		 * @return none
		 */
		public function uapDeleteLandingPageForAffiliate()
		{
				global $indeed_db;
				if ( !indeedIsAdmin() ){
						die;
				}
				if ( !uapAdminVerifyNonce() ){
						die;
				}
				if ( empty( $_POST['id'] ) ){
						echo 0;
						die;
				}
				update_post_meta( $_POST['id'], 'uap_landing_page_affiliate_id', '' );
				die;
		}

		public function uapDeleteCouponsForAffiliate()
		{
				global $indeed_db;
				if ( !indeedIsAdmin() ){
						die;
				}
				if ( !uapAdminVerifyNonce() ){
						die;
				}
				if ( empty( $_POST['id'] ) ){
						echo 0;
						die;
				}
				$indeed_db->delete_coupon_affiliate_pair($_POST['id']);
				die;
		}

		public function saveCampaign()
		{
				global $indeed_db;
				// public
				if ( !uapPublicVerifyNonce() ){
					die;
				}
				$uid = indeed_get_uid();
				if ( empty( $uid ) ) {
						die;
				}
				$affiliateId = $indeed_db->get_affiliate_id_by_wpuid( $uid );
				if ( empty( $affiliateId ) ){
						die;
				}
				if ( !empty($_POST['campaignName']) ){
						$_POST['campaignName'] = sanitize_text_field( $_POST['campaignName'] );
						$_POST['campaignName'] = preg_replace( '/[^A-Za-z0-9\-\_]/', '', $_POST['campaignName'] );
						$indeed_db->add_empty_campaign( $affiliateId, $_POST['campaignName']);
						echo 1;
				}
				die;
		}

		public function saveSimpleLink()
		{
				global $indeed_db;
				// public
				if ( !uapPublicVerifyNonce() ){
					die;
				}
				$uid = indeed_get_uid();
				if ( empty( $uid ) ) {
						die;
				}
				$affiliateId = $indeed_db->get_affiliate_id_by_wpuid( $uid );
				if ( empty( $affiliateId ) ){
						die;
				}
				foreach ( $_POST as $postKey => $postValue ){
						$_POST[ $postKey ] = sanitize_text_field( $postValue );
				}
				$data = [ 'affiliate_id' => $affiliateId, 'url' => $_POST['url'] ];
				$inserted = $indeed_db->simple_links_save_link($data, 0);///set status as 0 (pending)
				if ( isset( $_POST['currentUrl'] ) && $inserted < 1 ){
						$_POST['currentUrl'] = add_query_arg( 'uap_aff_subtab', 'simple_links', $_POST['currentUrl'] );
						$_POST['currentUrl'] = add_query_arg( 'message', $inserted, $_POST['currentUrl'] );
						echo $_POST['currentUrl'];
						die;
				}
				die;
		}

		public function notificationSendTestEmail()
		{
				if ( !indeedIsAdmin() ){
						die;
				}
				if ( !uapAdminVerifyNonce() ){
						die;
				}
				include_once UAP_PATH . 'admin/views/notification-send-email-test.php';
				die;
		}

		function uap_ajax_do_send_notification_test()
		{
			if ( !indeedIsAdmin() ){
					echo 0;
					die;
			}
			if ( !uapAdminVerifyNonce() ){
				 echo 0;
				 die;
			}
			$notificationId = isset($_POST['id']) ? $_POST['id'] : 0;
			$email = isset($_POST['email']) ? $_POST['email'] : '';
			uapSendTestNotification( $notificationId, $email );
			echo 1;
			die;
		}

		public function uap_ajax_do_remove_affiliate_link()
		{
				if ( !uapPublicVerifyNonce() ){
					die;
				}
				\Indeed\Uap\Db\GeneratedAffiliateLinks::delete( esc_sql( $_POST['id'] ) );
				die;
		}

		public function uap_ajax_load_list_affiliate_links_table()
		{
				global $current_user, $indeed_db;
				if ( !uapPublicVerifyNonce() ){
					die;
				}
				if ( !isset( $current_user->ID ) ){
						die;
				}
				$aid = $indeed_db->get_affiliate_id_by_wpuid( $current_user->ID );
				if ( !$aid ){
						die;
				}
				$data['affiliate_links'] = \Indeed\Uap\Db\GeneratedAffiliateLinks::getAllForAId( $aid );

				$fullPath = UAP_PATH . 'public/views/account_page-list_affiliate_links.php';
				$searchFilename = 'account_page-list_affiliate_links.php';
				$template = apply_filters('uap_filter_on_load_template', $fullPath, $searchFilename );

				require $template;

				die;
		}

		/**
		 * @param none
		 * @return none
		 */
		public function uap_ajax_close_admin_mk_notice()
		{
				if ( !indeedIsAdmin() ){
						die;
				}
				if ( !isset( $_POST['target'] ) ){
						die;
				}
				$target = esc_sql( $_POST['target'] );
				switch ( $target ){
						case 'woo':
							update_option( 'uap_disable_woo_mk_message', time() );
							break;
						case 'mycred':
							update_option( 'uap_disable_mycred_mk_message', time() );
							break;
				}
				die;
		}

}
