<?php
if (!class_exists('UapMainAdmin')){
	class UapMainAdmin{
		private $version_param_name_db = 'uap_plugin_version';
		private $amount_type_list = array();
		private $admin_view_path = '';
		private $base_admin_url;
		private $error_messages = array();
		private $items_per_page = array(5, 25, 50, 100, 200, 500);
		private $new_affiliates = 0;
		private $new_referrals = 0;
		private $plugin_version = '';

		public function __construct(){
			/*
			 * @param none
			 * @return none
			 */

			$this->plugin_version = UAP_PLUGIN_VER;
			/// INSTALL / UPDATE
			$current_plugin_version = $this->get_current_plugin_version();
			if ($current_plugin_version===FALSE){
				//plugin first activation
				$this->run_install();
				update_option($this->version_param_name_db, UAP_PLUGIN_VER);
			} else if (UAP_PLUGIN_VER!=$current_plugin_version){
				// run updates
				$this->run_updates();
				update_option($this->version_param_name_db, UAP_PLUGIN_VER);
			}

			$this->plugin_version = $current_plugin_version;

			$this->admin_view_path = UAP_PATH . 'admin/views/';
			require_once UAP_PATH . 'admin/utilities.php';

			/// check for cron, curl, etc
			$this->check_system();


			// CREATE plugin menu
			add_action('admin_menu', array($this, 'add_menu'), 72 );

			//SCRIPTS && STYLE
			add_action("admin_enqueue_scripts", array($this, 'add_style_scripts') );

			/// pages & posts editor buttons
			add_action('init', array($this, 'add_custom_bttns'), 23);

			/// print column on pages
			add_filter( 'display_post_states', array($this, 'dashboard_print_uap_column'), 999, 2 );

			/// Meta Box
			add_action('add_meta_boxes', array($this, 'create_page_meta_box') );

			/// save meta form values
			add_action('save_post', array($this, 'save_meta_box_values'), 99, 3);

			/// global errors
			add_action('admin_notices', array($this, 'return_global_errors'), 99);

			/// edit wordpress user stuff
			add_action('edit_user_profile', array($this, 'edit_wp_user'), 99);
			add_action('show_user_profile', array($this, 'edit_wp_user'), 99);

			/// DELETE USER FROM WP
			add_action('deleted_user', array($this, 'uap_delete_affiliate_by_uid'), 99, 1);


			$this->referral_action();

			add_action( 'uap_print_admin_page', [ $this, 'hooksAndFilters' ], 99, 1 );

			// user page
			$userPage = new \Indeed\Uap\Admin\UserProfile();

			// woo coupons
			add_action( 'woocommerce_coupon_options', [ $this, 'wooCouponsSettings' ] );
			add_action( 'woocommerce_coupon_options_save', [ $this, 'wooSaveCoupons' ] );

			//Adds Extra links into Plugins table
			add_filter( 'plugin_row_meta', array($this, 'uap_plugin_row_meta'), 10, 2 );

			//if (!uap_is_ihc_active()){
				add_action( 'admin_menu' , array($this, 'uap_manage_ump') );
				add_action( 'admin_head', array($this, 'uap_manage_ump_add_jquery') );
			//}

			add_action( 'admin_notices', [ $this, 'marketingBanners'] );
		}


		private function get_current_plugin_version()
		{
				if ( is_multisite() ){
						global $wpdb;
						$table = $wpdb->base_prefix . 'options';
						$query = $wpdb->prepare( "SELECT option_value FROM $table WHERE option_name=%s ", $this->version_param_name_db );
						$data = $wpdb->get_row( $query );
						if ( $data && !empty( $data->option_value ) ){
								return $data->option_value;
						}
						return false;
				}
				return get_option( $this->version_param_name_db );
		}

		private function run_install(){
			/*
			 * Run only @ first activation. Create DB Tables. Default Settings.
			 * @param none
			 * @return none
			 */
			global $indeed_db;
			$indeed_db->create_tables();
			$indeed_db->create_pending_role();
			$indeed_db->create_default_pages();
			$indeed_db->create_demo_banners();
			$indeed_db->create_default_redirects();
			$this->install_default_notifications();
			$this->install_default_ranks();
		}

		private function run_updates(){
			/*
			 * @param none
			 * @return none
			 */
			 global $indeed_db;
			 $indeed_db->create_tables();
			 $indeed_db->modify_tables();

			 /// Register Fields
			 $post_data = array( 'id' => 0, 'display_admin'=>1, 'display_public_reg'=>1, 'display_public_ap'=>1, 'name'=>'uap_country', 'label'=>'Country', 'type'=>'uap_country', 'native_wp' => 0, 'req' => 0, 'sublabel' => '' );
			 $indeed_db->register_save_custom_field($post_data);
			 $data = $indeed_db->register_get_custom_fields();
			 $pass1_key = uap_get_array_key_for_subarray_element($data, 'name', 'pass1');
			 if ( $pass1_key > -1 ){
			 	 $arr = $data[$pass1_key];
				 $arr['id'] = $pass1_key;
				 $arr['display_public_ap'] = 0;
				 $indeed_db->register_save_custom_field($arr);
			 }
			 if (isset($arr)){
				  unset($arr);
			 }
			 $pass2_key = uap_get_array_key_for_subarray_element($data, 'name', 'pass2');
			 if ( $pass2_key > -1 ){
				 $arr = $data[$pass2_key];
				 $arr['id'] = $pass2_key;
				 $arr['display_public_ap'] = 0;
				 $indeed_db->register_save_custom_field($arr);
			 }

			 $key = uap_get_array_key_for_subarray_element( $data, 'name', 'uap_optin_accept' );

			 if ( $key == -1 ){
					  $array = [
						 				'id' 										=> 0,
										'display_admin'					=> 0,
										'display_public_reg'		=> 0,
										'display_public_ap'			=> 0,
										'name'									=> 'uap_optin_accept',
										'label'									=> esc_html__( 'Accept Opt-in', 'uap' ),
										'type'									=> 'single_checkbox',
										'native_wp' 						=> 0,
										'req' 									=> 0,
										'sublabel' 							=> ''
					  ];
					  $indeed_db->register_save_custom_field( $array );
			 }

			 $indeed_db->check_update_notifications();
			 $this->removeOldImportFiles();
		}

		private function removeOldImportFiles()
		{
				$directory = UAP_PATH;
				$files = scandir( $directory );
				foreach ( $files as $file ){
						$fileFullPath = $directory . $file;
						if ( file_exists( $fileFullPath ) && filetype( $fileFullPath ) == 'file' ){
								$extension = pathinfo( $fileFullPath, PATHINFO_EXTENSION );
								if ( $extension == 'xml' && $file == 'export.xml' ){
										unlink( $fileFullPath );
								} else if ( $extension == 'csv' && ( $file == 'affiliates.csv' || $file == 'referrals.csv' || $file == 'visits.csv' ) ){
										unlink( $fileFullPath );
								}
						}
				}
		}

		private function check_system(){
			/*
			 * @param none
			 * @return none
			 */

			$wp_cron = ( defined('DISABLE_WP_CRON') && DISABLE_WP_CRON ) ? FALSE : TRUE;
			if ( !$wp_cron ){
				$this->error_messages[] = esc_html__('Crons are disabled on your WordPress Website. Some functionality and processes may not work properly.', 'uap');
			}
			global $indeed_db;

			/// curl
			if ( !function_exists('curl_version') || !curl_version() ){
					$this->error_messages[] = esc_html__('cURL is not working or is disabled on your Website environment. Please contact your Hosting provider.', 'uap');
			}

			// cookie
			$uap_test_cookie = get_option( 'uap_test_cookie' );
			if ( !$uap_test_cookie || !isset($_COOKIE['uap_test_cookie']) ){
					setcookie( 'uap_test_cookie', 1, time() + 360 * 30 * 60 * 60, '/' );
					update_option( 'uap_test_cookie', 1 );
			} elseif ($uap_test_cookie != 0){
					if (isset($_COOKIE['uap_test_cookie'])) {
							//verification is closed until cookie will expire
							update_option( 'uap_test_cookie', 0 );
					} else {
							$this->error_messages[] = esc_html__('Cookies are not stored or setcookie() PHP function is disabled on your Website environment.', 'uap');
							//try to set again to check if the problem gone
							setcookie( 'uap_test_cookie', 1, time() + 360 * 30 * 60 * 60, '/' );
					}
			}

			if ( $indeed_db->is_magic_feat_enable('paypal') ){
				$temp_array = $indeed_db->return_settings_from_wp_option('paypal');
				if ( empty( $temp_array['uap_paypal_sandbox'] ) ){
					if ( empty( $temp_array['uap_paypal_client_id'] ) || empty( $temp_array['uap_paypal_client_secret'] ) ){
						$this->error_messages[] = esc_html__('PayPal Payout module is not set properly!', 'uap');
					}
				} else {
					if ( empty( $temp_array['uap_paypal_sandbox_client_id'] ) || empty( $temp_array['uap_paypal_sandbox_client_secret'] ) ){
						$this->error_messages[] = esc_html__('PayPal Payout module is not set properly!', 'uap');
					}
				}
			}
			if ( $indeed_db->is_magic_feat_enable('stripe') ){
				$temp_array = $indeed_db->return_settings_from_wp_option('stripe');
				if ( empty( $temp_array['uap_stripe_sandbox'])){
					if ( empty( $temp_array['uap_stripe_secret_key'] ) || empty($temp_array['uap_stripe_publishable_key'] ) ){
						$this->error_messages[] = esc_html__('Stripe Payout module is not set properly!', 'uap');
					}
				} else {
					if ( empty( $temp_array['uap_stripe_sandbox_secret_key'] ) || empty($temp_array['uap_stripe_sandbox_publishable_key'] ) ){
						$this->error_messages[] = esc_html__('Stripe Payout module is not set properly!', 'uap');
					}
				}
			}

			if ( $indeed_db->affiliates_with_no_rank_exists() ){
				$this->error_messages[] = esc_html__('Some Affiliates users do not have assigned a Rank and they may not be rewarded. Check your stage ', 'uap') . ' <a href="' . admin_url('admin.php?page=ultimate_affiliates_pro&tab=affiliates') . '" target="_blank">' . esc_html__('here', 'uap') . '</a>';
			}

			$cropFunctions = [
												'getimagesize',
												'imagecreatefrompng',
												'imagecreatefromjpeg',
												'imagecreatefromgif',
												'imagecreatetruecolor',
												'imagecopyresampled',
												'imagerotate',
												'imagesx',
												'imagesy',
												'imagecolortransparent',
												'imagecolorallocate',
												'imagejpeg',
			];
			foreach ( $cropFunctions as $cropFunction ){
					if ( !function_exists( $cropFunction ) ){
							$functionsErrors[] = $cropFunction .'()';
					}
			}
			if ( !empty($functionsErrors) ){
					$this->error_messages[] = esc_html__('Following functions: ', 'uap') . implode( ', ', $functionsErrors )
					. esc_html__( ' are disabled on your Website environment. Avatar feature may not work properly. Please contract your Hosting provider.', 'uap');
			}
		}

		public function return_global_errors(){
			/*
			 * @param none
			 * @return none
			 */
			if (current_user_can('manage_options')){
				echo $this->print_unregistered_notice(TRUE);
			}
		}

		private function print_unregistered_notice($is_global=FALSE){
			/*
			 * @param boolean
			 * @return string
			 */
			 if (defined('UAP_LICENSE_SET') && !UAP_LICENSE_SET){
				 $hide = get_option( 'uap_hide_admin_license_notice' );
				 $currentPage = isset($_GET['page']) ? $_GET['page'] : '';
				 if ( $currentPage != 'ultimate_affiliates_pro' && $hide ){
						 return '';
				 }
			 	$data['err_class'] = ($is_global) ? 'error' : 'uap-error-global-dashboard-message';
				$data['url'] = admin_url('admin.php?page=ultimate_affiliates_pro&tab=help');
				ob_start();
				require $this->admin_view_path . 'global-errors.php';
				$output = ob_get_contents();
				ob_end_clean();
				return $output;
			 }
		}

		private function printFopenError()
		{
				$allow = ini_get( 'allow_url_fopen' );
				if ( !$allow ){
					echo '<div class="uap-error-global-dashboard-message"><strong>' . esc_html__("'allow_url_fopen' directive is disabled. In order for Ultimate Affiliate Pro to work properly this directive has to be set 'on'. Contact your hosting provider for more details.", 'uap') . ' </strong></div>';
				}
		}

		private function install_default_notifications(){
			/*
			 * @param none
			 * @return none
			 */
			global $indeed_db;
			$array = array( 'admin_user_register',
							'register',
							'reset_password_process',
							'reset_password',
							'change_password',
							'user_update',
							'rank_change',
							'email_check',
							'email_check_success',
							'register_lite_send_pass_to_user',
			);
			foreach ($array as $type){
				if (!$indeed_db->notification_type_exists($type)){
					$template = uap_return_default_notification_content($type); ///get default notification content
					$data['type'] = $type;
					$data['rank_id'] = -1;
					$data['subject'] = addslashes($template['subject']);
					$data['message'] = addslashes($template['content']);
					$data['status'] = 1;
					$data['pushover_message'] = '';
					$data['pushover_status'] = '';
					$indeed_db->save_notification($data);///and save it
					unset($data);
				}
			}
		}

		private function install_default_ranks(){
			/*
			 * @param none
			 * @return none
			 */
			global $indeed_db;
			$rank_1 = array(
								'id' => 1,
								'slug' => 'rank_1',
								'label' => 'Basic',
								'amount_type' => 'percentage',
								'amount_value' => 10,
								'achieve' => '',
								'rank_order' => 1,
								'color' => '0bb586',
								'description' => 'A Demo Rank',
								'bonus' => '',
								'pay_per_click' => '',
								'cpm_commission' => '',
								'sign_up_amount_value' => -1,
								'lifetime_amount_type' => '',
								'lifetime_amount_value' => -1,
								'reccuring_amount_type' => '',
								'reccuring_amount_value' => -1,
								'mlm_amount_type' => '',
								'mlm_amount_value' => '',
								'status' => 1,
			);
			$rank_2 = array(
								'id' => 2,
								'slug' => 'rank_2',
								'label' => 'Premium',
								'amount_type' => 'percentage',
								'amount_value' => 15,
								'achieve' => '{"i":1,"type_1":"referrals_number","value_1":"100"}',
								'rank_order' => 2,
								'color' => 'f8ba01',
								'description' => 'A Demo Rank',
								'bonus' => '',
								'pay_per_click' => '',
								'cpm_commission' => '',
								'sign_up_amount_value' => -1,
								'lifetime_amount_type' => '',
								'lifetime_amount_value' => -1,
								'reccuring_amount_type' => '',
								'reccuring_amount_value' => -1,
								'mlm_amount_type' => '',
								'mlm_amount_value' => '',
								'status' => 1,
			);
			$indeed_db->rank_save_update($rank_1);
			$indeed_db->rank_save_update($rank_2);
		}

		public function add_menu(){
			/*
			 * @param none
			 * @return none
			 */
			add_menu_page('Ultimate Affiliate Pro', '<span>Ultimate Affiliate Pro</span>', 'manage_options',	'ultimate_affiliates_pro', array($this, 'output') , 'dashicons-networking');
		}

		public function output(){
			/*
			 * @param none
			 * @return none (print html)
			 */
			$currency = get_option('uap_currency');
			$this->amount_type_list = array('flat' => $currency, 'percentage'=> '%');

			$tab = (empty($_GET['tab'])) ? 'dashboard' : $_GET['tab'];
			$this->base_admin_url = admin_url('admin.php?page=ultimate_affiliates_pro&tab=' . $tab);
			$this->print_head($tab);
			switch ($tab){
				case 'dashboard':
					$this->print_dashboard();
					break;
				case 'affiliates':
					$this->print_affiliates();
					break;
				case 'ranks':
					$this->print_ranks();
					break;
				case 'offers':
					$this->print_offers();
					break;
				case 'landing_commissions':
					$this->print_landing_commissions();
					break;
				case 'banners':
					$this->print_banners();
					break;
				case 'visits':
					$this->print_visits();
					break;
				case 'referrals':
					$this->print_referrals();
					break;
				case 'payments':
					$this->print_payments();
					break;
				case 'notifications':
					$this->print_notifications();
					break;
				case 'reports':
					$this->print_reports();
					break;
				case 'settings':
					$this->print_settings();
					break;
				case 'showcases':
					$this->print_showcases();
					break;
				case 'register':
					$this->print_register();
					break;
				case 'login':
					$this->print_login();
					break;
				case 'account_page':
					$this->print_account_page();
					break;
				case 'opt_in':
					$this->print_opt_in();
					break;
				case 'magic_features':
					$this->print_magic_features();
					break;
				case 'shortcodes':
					$this->print_shortcodes();
					break;
				case 'help':
					$this->print_help();
					break;
				case 'top_affiliates':
					$this->print_top_affiliates();
					break;
				case 'top_affiliates_settings':
					$this->print_top_affiliates_settings();
					break;
				case 'referral_list_details':
					$this->referral_list_details();
					break;
				case 'view_payment_settings':
					$this->print_view_payment_settings();
					break;
				case 'import_export':
					$this->print_import_export();
					break;
				default:
					do_action( 'uap_print_admin_page', $tab );
					break;
			}
			$this->print_footer();
		}

		private function print_head($tab){
			/*
			 * @param string
			 * @return string
			 */
			global $indeed_db;
			$data['admin_workflow'] = $indeed_db->return_settings_from_wp_option('general-admin_workflow');

			switch ($tab){
				case 'affiliates':
					$indeed_db->reset_dashboard_notification('affiliates');
					break;
				case 'referrals':
					$indeed_db->reset_dashboard_notification('referrals');
					break;
			}

			if ($data['admin_workflow']['uap_admin_workflow_dashboard_notifications']){
				$data['affiliates_notification_count'] = $indeed_db->get_dashboard_notification_value('affiliates');
				$data['referrals_notification_count'] = $indeed_db->get_dashboard_notification_value('referrals');;
			}


			$data['tab'] = $tab;
			$data['base_url'] = admin_url('admin.php?page=ultimate_affiliates_pro');
			$data['menu_items'] = array(
											'affiliates' => esc_html__('Affiliates', 'uap'),
											'ranks' => esc_html__('Ranks', 'uap'),
											'offers' => esc_html__('Offers', 'uap'),
											'landing_commissions' => esc_html__('Landing Commissions (CPA)', 'uap'),
											'banners' => esc_html__('Creatives', 'uap'),
											'showcases' => esc_html__('Showcases', 'uap'),
											'visits' => esc_html__('Clicks', 'uap'),
											'referrals' => esc_html__('Referrals', 'uap'),
											'payments' => esc_html__('Payments', 'uap'),
											'magic_features' => esc_html__('Extensions', 'uap'),
											'notifications' => esc_html__('Email Notifications', 'uap'),
											'reports' => esc_html__('Reports', 'uap'),
											'settings' => esc_html__('General Options', 'uap'),
			);
			$data['right_tabs'] = array(
											'shortcodes' => esc_html__('Shortcodes', 'uap'),
											'import_export' => esc_html__('Import/Export', 'uap'),
											'help' => esc_html__('Help', 'uap'),
			);
			$plugin_vs = $this->plugin_version;

			require_once $this->admin_view_path . 'header.php';
		}

		private function print_top_messages(){
			/*
			 * @param none
			 * @return string
			 */
			echo $this->print_unregistered_notice(FALSE);
			echo $this->printFopenError();
			require_once $this->admin_view_path . 'top-messages.php';
		}

		private function print_footer(){

			$plugin_vs = $this->plugin_version;
			require_once $this->admin_view_path . 'footer.php';
		}

		private function print_dashboard(){
			/*
			 * @param none
			 * @return string
			 */
			global $indeed_db;
			$data['stats'] = $indeed_db->stats_for_dashboard();
			$data['currency'] = get_option('uap_currency');
			$data['rank_arr'] = $indeed_db->get_affilitated_per_rank();
			$data['last_referrals'] = $indeed_db->get_last_referrals();
			$data['top_affiliates'] = $indeed_db->get_top_affiliates_by_amount();
			$this->print_top_messages();
			require_once $this->admin_view_path . 'dashboard.php';
		}

		private function print_help(){
			/*
			 * @param none
			 * @return string
			 */
			global $indeed_db;

			$data = $indeed_db->return_settings_from_wp_option('licensing');
			$disabled = ($this->check_curl()) ? '' : 'disabled';
			require_once $this->admin_view_path . 'help.php';
		}

		/*
		 * @param none
		 * @return none
		 */
		private function print_import_export(){
			global $indeed_db;
			if (!empty($_POST['import']) && !empty($_FILES['import_file']) && !empty($_POST['uap_admin_import_nonce']) && wp_verify_nonce( $_POST['uap_admin_import_nonce'], 'uap_admin_import_nonce' ) ){
				////////////////// IMPORT
				$filename = UAP_PATH . 'import.xml';
				move_uploaded_file($_FILES['import_file']['tmp_name'], $filename);
				require_once UAP_PATH . 'classes/import-export/IndeedImport.class.php';
				require_once UAP_PATH . 'classes/import-export/UapIndeedImport.class.php';
				$import = new UapIndeedImport();
				$import->setFile($filename);
				$import->run();
			}
			$this->print_top_messages();
			require_once $this->admin_view_path . 'import-export.php';
		}

		private function check_curl(){
			/*
			 * @param none
			 * @return boolean
			 */
			return (function_exists('curl_version')) ? TRUE : FALSE;
		}

		private function print_affiliates(){
			/*
			 * @param none
			 * @return string
			 */
			$this->print_top_messages();
			global $indeed_db;
			require_once UAP_PATH . 'classes/AffiliateAddEdit.class.php';
			$current_url = UAP_PROTOCOL . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			$current_url = remove_query_arg('uap_list_item', $current_url);
			$currency = get_option('uap_currency');

			if (isset($_POST['Update'])){
				/// UPDATE AFFILIATE
				$args = array(
						'type' => 'edit',
						'tos' => FALSE,
						'captcha' => FALSE,
						'is_public' => FALSE,
						'user_id' => $_POST['user_id'],
				);
				$obj = new AffiliateAddEdit($args);
				$save_err = $obj->save_update_user();
			} else if (isset($_POST['Submit'])){
				/// CREATE AFFILIATE
				$args = array(
						'user_id' => FALSE,
						'type' => 'create',
						'tos' => FALSE,
						'captcha' => FALSE,
						'is_public' => FALSE,
				);
				$obj = new AffiliateAddEdit($args);
				$save_err = $obj->save_update_user();
			} else if (!empty($_POST['delete_affiliate']) && !empty($_POST['uap_admin_list_affiliate_nonce']) && wp_verify_nonce( $_POST['uap_admin_list_affiliate_nonce'], 'uap_admin_list_affiliate_nonce' )){
				/// DELETE AFFILIATE
				$indeed_db->delete_affiliates(array($_POST['delete_affiliate']));
			} else if (!empty($_POST['do_action']) && !empty($_POST['affiliate_id_arr']) && !empty($_POST['uap_admin_list_affiliate_nonce']) && wp_verify_nonce( $_POST['uap_admin_list_affiliate_nonce'], 'uap_admin_list_affiliate_nonce' ) ){
				if ( $_POST['do_action']=='delete' ){
					$indeed_db->delete_affiliates($_POST['affiliate_id_arr']);
				} else if ($_POST['do_action']=='update_ranks'){
					require_once UAP_PATH . 'public/ChangeRanks.class.php';
					$update_rank_object = new ChangeRanks($_POST['affiliate_id_arr']);
				}
			}

			$data['url-add_edit'] = admin_url('admin.php?page=ultimate_affiliates_pro&tab=affiliates&subtab=add_edit');
			$data['url-manage'] = admin_url('admin.php?page=ultimate_affiliates_pro&tab=affiliates');
			$data['subtab'] = (empty($_GET['subtab'])) ? 'list' : $_GET['subtab'];
			$data['show_cpm'] = $indeed_db->is_magic_feat_enable('cpm_commission') ? true : false;
			$data['show_ppc'] = $indeed_db->is_magic_feat_enable('pay_per_click') ? true : false;

			/// OUTPUT
			if ($data['subtab']=='list'){
				/// MANAGE AFFILIATES

				$url = admin_url('admin.php?page=ultimate_affiliates_pro&tab=affiliates');
				$limit = (isset($_GET['uap_limit'])) ? $_GET['uap_limit'] : 25;
				$current_page = (empty($_GET['uap_list_item'])) ? 1 : $_GET['uap_list_item'];
				$total_items = $indeed_db->get_affiliates(-1, -1, TRUE, '', '');

				if ($current_page>1){
					$offset = ( $current_page - 1 ) * $limit;
				} else {
					$offset = 0;
				}
				if ($offset + $limit>$total_items){
					$limit = $total_items - $offset;
				}

				require_once UAP_PATH . 'classes/UapPagination.class.php';
				$limit = (isset($_GET['uap_limit'])) ? $_GET['uap_limit'] : 25;
				$pagination = new UapPagination(array(
						'base_url' => $current_url,
						'param_name' => 'uap_list_item',
						'total_items' => $total_items,
						'items_per_page' => $limit,
						'current_page' => $current_page,
				));

				$order_by = 'a.start_data';
				$order_type = 'DESC';
				if (!empty($_REQUEST['orderby_user'])){
					switch ($_REQUEST['orderby_user']){
						case 'display_name':
							$order_by = 'u.display_name';
							break;
						case 'user_login':
							$order_by = 'u.user_login';
							break;
						case 'user_email':
							$order_by = 'u.user_email';
							break;
						case 'ID':
							$order_by = 'u.ID';
							break;
						case 'user_registered':
							$order_by = 'u.user_registered';
							break;
					}
				}
				if (!empty($_REQUEST['ordertype_user'])){
					$order_type = $_REQUEST['ordertype_user'];
				}

				$data['ranks_list'] = uap_get_wp_roles_list();
				$data['pagination'] = $pagination->output();
				$data['listing_affiliates'] = $indeed_db->get_affiliates($limit, $offset, FALSE, $order_by, $order_type);
				$data['errors'] = uap_return_errors();
				$data['base_list_url'] = $url;
				$data['base_visits_url'] = admin_url('admin.php?page=ultimate_affiliates_pro&tab=visits');
				$data['base_referrals_url'] = admin_url('admin.php?page=ultimate_affiliates_pro&tab=referrals');
				$data['base_paid_url'] = admin_url('admin.php?page=ultimate_affiliates_pro&tab=payments&subtab=paid_referrals');
				$data['base_unpaid_url'] = admin_url('admin.php?page=ultimate_affiliates_pro&tab=payments&subtab=unpaid');
				$data['base_pay_now'] = admin_url('admin.php?page=ultimate_affiliates_pro&tab=payments&subtab=payment_form');
				$data['base_reports_url'] = admin_url('admin.php?page=ultimate_affiliates_pro&tab=reports');
				$data['affiliate_profile_url'] = admin_url('admin.php?page=ultimate_affiliates_pro&tab=user_profile');
				$data['base_transations_url'] = admin_url('admin.php?page=ultimate_affiliates_pro&tab=payments&subtab=transactions');
				$data['base_view_payment_settings_url'] = admin_url('admin.php?page=ultimate_affiliates_pro&tab=view_payment_settings&uid=');
				$data['email_verification'] = $indeed_db->is_magic_feat_enable('email_verification');
				$data['mlm_on'] = $indeed_db->is_magic_feat_enable('mlm');
				$data['mlm_matrix_link'] = admin_url('admin.php?page=ultimate_affiliates_pro&tab=magic_features&subtab=mlm_view_affiliate_children&affiliate_name=');
				$data['rankings'] = $indeed_db->affiliatesGetRanking();
				if ( $data['rankings'] ){
						end($data['rankings']);
						$key = current( $data['rankings'] );
						if ( $key ){
								$data['rankings_last_place'] = $key + 1;
						}
						reset( $data['rankings'] );
				}
				require_once $this->admin_view_path . 'affiliates-list.php';
			} else {
				/// ADD EDIT AFFILIATES
				$id = (empty($_GET['id'])) ? FALSE : $_GET['id'];
				$type = $id ? 'edit' : 'create';
				$args = array(
						'user_id' => $id,
						'type' => $type,
						'tos' => FALSE,
						'captcha' => FALSE,
						'action' => $data['url-manage'],
						'is_public' => FALSE,
				);
				$obj = new AffiliateAddEdit($args);
				$data = $obj->form();
				$data['template'] = '';
				$data['action'] = admin_url('admin.php?page=ultimate_affiliates_pro&tab=affiliates');
				ob_start();
				require_once UAP_PATH . 'public/views/register.php';
				$data['output'] = ob_get_contents();
				ob_end_clean();
				require_once $this->admin_view_path . 'affiliates-add_edit.php';
			}

		}

		private function print_ranks(){
			/*
			 * @param none
			 * @return string
			 */
			$this->print_top_messages();
			global $indeed_db;
			$data['url-add_edit'] = admin_url('admin.php?page=ultimate_affiliates_pro&tab=ranks&subtab=add_edit');
			$data['url-manage'] = admin_url('admin.php?page=ultimate_affiliates_pro&tab=ranks');
			$data['subtab'] = (empty($_GET['subtab'])) ? 'list' : $_GET['subtab'];
			$data['achieve_types'] = array(-1=>'...', 'referrals_number'=>'Number of Referrals', 'total_amount'=>'Total Amount');

			/// OUTPUT
			if ($data['subtab']=='list'){
				if (!empty($_POST['save']) && !empty($_POST['uap_admin_forms_nonce']) && wp_verify_nonce( $_POST['uap_admin_forms_nonce'], 'uap_admin_forms_nonce' ) ){
					$indeed_db->rank_save_update($_POST);
				}

				$data['ranks'] = $indeed_db->get_ranks();
				$data['ranks'] = uap_reorder_ranks($data['ranks']);//reorder
				require_once $this->admin_view_path . 'ranks-list.php';
			} else {
				$id = (empty($_GET['id'])) ? 0 : $_GET['id'];
				$data['ranks'] = $indeed_db->get_ranks();
				$data['graphic'] = uap_create_ranks_graphic($data['ranks'], $id);
				$data['maximum_ranks'] = count($data['ranks']);
				if ($id==0){
					 $data['maximum_ranks']++;
				}
				$data['metas'] = $indeed_db->get_rank($id);
				$data['amount_types'] = $this->amount_type_list;
				$temp_data = $indeed_db->return_settings_from_wp_option('sign_up_referrals');
				$data['display-signup_referrals'] = $indeed_db->is_magic_feat_enable('sign_up_referrals');
				$data['display-lifetime_commissions'] = $indeed_db->is_magic_feat_enable('lifetime_commissions');
				$data['display-reccuring_referrals'] = $indeed_db->is_magic_feat_enable('reccuring_referrals');
				$data['display-mlm'] = $indeed_db->is_magic_feat_enable('mlm');
				$data['mlm_matrix_depth'] = get_option('uap_mlm_matrix_depth');
				$data['bonus_enabled'] = $indeed_db->is_magic_feat_enable('bonus_on_rank');
				$data['pay_per_click_enabled'] = $indeed_db->is_magic_feat_enable('pay_per_click');
				$data['cpm_commission_enabled'] = $indeed_db->is_magic_feat_enable('cpm_commission');
				require_once $this->admin_view_path . 'ranks-add_edit.php';
			}
		}

		private function print_offers(){
			/*
			 * @param none
			 * @return string
			 */
			$this->print_top_messages();
			global $indeed_db;
			$data['url-add_edit'] = admin_url('admin.php?page=ultimate_affiliates_pro&tab=offers&subtab=add_edit');
			$data['url-manage'] = admin_url('admin.php?page=ultimate_affiliates_pro&tab=offers');
			$data['subtab'] = (empty($_GET['subtab'])) ? 'list' : $_GET['subtab'];
			$currency = get_option('uap_currency');

			if ($data['subtab']=='add_edit'){
				/// ADD EDIT
				$id = (empty($_GET['id'])) ? 0 : $_GET['id'];
				$data['metas'] = $indeed_db->get_offer($id);
				$data['amount_types'] = $this->amount_type_list;
				if (!empty($data['metas']['affiliates'])){
					foreach ($data['metas']['affiliates'] as $id){
						$data['affiliates']['username'][$id] = $indeed_db->get_wp_username_by_affiliate_id($id);
					}
					$data['affiliates']['username'][-1] = 'All';
				}

				if (!empty($data['metas']['products'])){
					$data['metas']['products'] = explode(',', $data['metas']['products']);
					switch ($data['metas']['source']){
						case 'woo':
							foreach ($data['metas']['products'] as $id){
								$data['products']['label'][$id] = $indeed_db->woo_get_product_title_by_id($id);
							}
							break;
						case 'ump':
							foreach ($data['metas']['products'] as $id){
								$data['products']['label'][$id] = $indeed_db->ump_get_level_label_by_id($id);
							}
							break;
						case 'edd':
							foreach ($data['metas']['products'] as $id){
								$data['products']['label'][$id] = $indeed_db->edd_get_label_by_id($id);
							}
							break;
						case 'ulp':
							foreach ($data['metas']['products'] as $id){
								$data['products']['label'][$id] = $indeed_db->ulp_get_label_by_id($id);
							}
							break;
					}
				}
				require_once $this->admin_view_path . 'offers-add_edit.php';
			} else {
				/// LISTING
				if ( !empty($_POST['save']) && !empty($_POST['uap_admin_forms_nonce']) && wp_verify_nonce( $_POST['uap_admin_forms_nonce'], 'uap_admin_forms_nonce' ) ){
					$saved = $indeed_db->save_offer($_POST);
					if ($saved<1){
						$data['errors'] = esc_html__('Be sure that you have filled all the reguired fields: Name, Amount, Date Range.', 'uap');
					}
				} else if (!empty($_POST['delete_offers'])  && !empty($_POST['uap_admin_forms_nonce']) && wp_verify_nonce( $_POST['uap_admin_forms_nonce'], 'uap_admin_forms_nonce' ) ){
					$indeed_db->delete_offers($_POST['delete_offers']);
				}
				$data['listing_items'] = $indeed_db->get_offers();
				require_once $this->admin_view_path . 'offers-list.php';
			}
		}

		private function print_landing_commissions(){
			/*
			 * @param none
			 * @return string
			 */
			$this->print_top_messages();
			global $indeed_db;
			$data['url-add_edit'] = admin_url('admin.php?page=ultimate_affiliates_pro&tab=landing_commissions&subtab=add_edit');
			$data['url-manage'] = admin_url('admin.php?page=ultimate_affiliates_pro&tab=landing_commissions');
			$data['subtab'] = (empty($_GET['subtab'])) ? 'list' : $_GET['subtab'];
			$currency = get_option('uap_currency');

			if ($data['subtab']=='add_edit'){
				/// ADD EDIT
				$id = (empty($_GET['slug'])) ? '' : $_GET['slug'];
				$data['metas'] = $indeed_db->get_landing_commission($id);
				require_once $this->admin_view_path . 'landing-commissions-add_edit.php';
			} else {
				/// LISTING
				if (!empty($_POST['save']) && !empty($_POST['uap_admin_forms_nonce']) && wp_verify_nonce( $_POST['uap_admin_forms_nonce'], 'uap_admin_forms_nonce' ) ){
					$saved = $indeed_db->save_landing_commission($_POST);
					if ($saved<1){
						$data['errors'] = esc_html__('Be sure that you have filled all the required fields: Slug and Amount.', 'uap');
					}
				} else if (!empty($_POST['delete_landing_referral']) && !empty($_POST['uap_admin_forms_nonce']) && wp_verify_nonce( $_POST['uap_admin_forms_nonce'], 'uap_admin_forms_nonce' ) ){
					$indeed_db->delete_landing_commission($_POST['delete_landing_referral']);
				}
				$data['listing_items'] = $indeed_db->get_landing_commissions();

				require_once $this->admin_view_path . 'landing-commissions-list.php';
			}
		}

		private function print_referrals(){
			/*
			 * @param none
			 * @return string
			 */
			$this->print_top_messages();
			global $indeed_db;
			$data['url-add_edit'] = admin_url('admin.php?page=ultimate_affiliates_pro&tab=referrals&subtab=add_edit');
			$data['url-manage'] = admin_url('admin.php?page=ultimate_affiliates_pro&tab=referrals');
			$data['subtab'] = (empty($_GET['subtab'])) ? 'list' : $_GET['subtab'];

			if ($data['subtab']=='add_edit'){
				/// ADD EDIT
				$data['affiliates'] = $indeed_db->get_affiliates();
				$id = (empty($_GET['id'])) ? 0 : $_GET['id'];
				$data['metas'] = $indeed_db->get_referral($id);
				$data['status_posible'] = array(0=>'Refused', 1=>'Unverfied', 2=>'Verified');
				$data['payment_posible'] = array(0 => 'Unpaid', 1 => 'Pending', 2 => 'Complete');
				require_once $this->admin_view_path . 'referrals-add_edit.php';
			} else {
				/// LISTING
				if (!empty($_POST['save']) && !empty($_POST['uap_admin_forms_nonce']) && wp_verify_nonce( $_POST['uap_admin_forms_nonce'], 'uap_admin_forms_nonce' ) ){
					/// SAVE UPDATE
					$save_answer = $indeed_db->save_referral_from_admin($_POST);
					if (!$save_answer){
						$data['error'] = esc_html__('Error', 'uap');
					} else {
						/// SAVE affiliate referral relation
						$old_affiliate = $indeed_db->search_affiliate_id_for_current_user($_POST['refferal_wp_uid']);
						if ($old_affiliate){
							$rewrite_referrals = get_option('uap_rewrite_referrals_enable');
							if ($rewrite_referrals){
								/// update user - affiliate relation, use new affiliate
								$indeed_db->update_affiliate_referral_user_relation_by_ids($old_affiliate, $_POST['affiliate_id'], $_POST['refferal_wp_uid']);
							}
						} else {
							/// insert user - affiliate relation
							$indeed_db->insert_affiliate_referral_user_new_relation($_POST['affiliate_id'], $_POST['refferal_wp_uid']);
						}
					}
				} else if (!empty($_POST['change_status']) && !empty($_POST['uap_admin_forms_nonce']) && wp_verify_nonce( $_POST['uap_admin_forms_nonce'], 'uap_admin_forms_nonce' ) ){
					/// CHANGE STATUS
					if (strpos($_POST['change_status'], '-')!==FALSE){
						$status_data = explode('-', $_POST['change_status']);
						if (isset($status_data[0]) && isset($status_data[1])){
							$indeed_db->change_referral_status($status_data[0], $status_data[1]);
						}
					}
				} else if (isset($_POST['referral_list']) && $_POST['referral_list'] &&
						(isset($_POST['list_action']) && $_POST['list_action']!=-1) || (isset($_POST['list_action_2']) && $_POST['list_action_2']!=-1)
					&& !empty($_POST['uap_admin_forms_nonce']) && wp_verify_nonce( $_POST['uap_admin_forms_nonce'], 'uap_admin_forms_nonce' ) ){
					/// CHANGE STATUS && DELETE

					if (isset($_POST['list_action']) && $_POST['list_action']!=-1){
						$action = $_POST['list_action'];
					} else if (isset($_POST['list_action_2']) && $_POST['list_action_2']!=-1){
						$action = $_POST['list_action_2'];
					}

					if ($action=='delete'){
						$data['current_actions'] = 'delete';
						foreach ($_POST['referral_list'] as $id){
							$indeed_db->delete_referrals($id);
						}
					} else {
						switch ($action){
							case 'refuse':
								$data['current_actions'] = 'refuse';
								$status = 0;
								break;
							case 'pending':
								$data['current_actions'] = 'pending';
								$status = 1;
								break;
							case 'complete':
								$data['current_actions'] = 'complete';
								$status = 2;
								break;
						}
						foreach ($_POST['referral_list'] as $id){
							$indeed_db->change_referral_status($id, $status);
						}
					}
				} else if (!empty($_POST['delete_referral']) && !empty($_POST['uap_admin_forms_nonce']) && wp_verify_nonce( $_POST['uap_admin_forms_nonce'], 'uap_admin_forms_nonce' ) ){
					/// single delete
					foreach ($_POST['delete_referral'] as $id){
						$indeed_db->delete_referrals($id);
					}
				}
				$available_systems = uap_get_active_services();
				/// VIEW
				$where = array();
				if (!empty($_REQUEST['udf']) && !empty($_REQUEST['udu'])){
					$where[] = " r.date>'" . $_REQUEST['udf'] . "' ";
					$where[] = " r.date<'" . $_REQUEST['udu'] . "' ";
					$data['url-manage'] .= '&udf=' . $_REQUEST['udf'] . '&udu=' . $_REQUEST['udu'];
				}
				if (isset($_REQUEST['u_sts']) && $_REQUEST['u_sts']!=-1){
					$where[] = " r.status='" . $_REQUEST['u_sts'] . "' ";
					$data['url-manage'] .= '&u_sts=' . $_REQUEST['u_sts'];
				}
				if (!empty($_REQUEST['aff_u'])){
					$where[] = " ((u.user_login LIKE '%" . $_REQUEST['aff_u'] . "%') OR  (u.user_email LIKE '%" . $_REQUEST['aff_u'] . "%') )";
					$data['url-manage'] .= '&aff_u=' . $_REQUEST['aff_u'];
				}
				if (isset($_REQUEST['u_source']) && $_REQUEST['u_source']!=-1){
					$where[] = " r.source LIKE '%" . $_REQUEST['u_source'] . "%' ";
					$data['url-manage'] .= '&u_source=' . $_REQUEST['u_source'];
				}
				if (!empty($_GET['affiliate_id'])){
					$where[] = "r.affiliate_id=" . $_GET['affiliate_id'];
					$data['url-manage'] .= '&affiliate_id=' . $_GET['affiliate_id'];
					$wpuid = $indeed_db->get_uid_by_affiliate_id($_GET['affiliate_id']);
					$username = $indeed_db->get_username_by_wpuid($wpuid);
					$full_name = $indeed_db->get_full_name_of_user($_GET['affiliate_id']);
					$data['subtitle'] = esc_html__('View Referrals for', 'uap') . " $full_name ($username)";
				}

				$limit = (empty($_GET['uap_limit'])) ? 25 : $_GET['uap_limit'];
				$data['url-manage'] .= '&uap_limit=' . $limit;
				$current_page = (empty($_GET['uap_list_item'])) ? 1 : $_GET['uap_list_item'];
				$total_items = $indeed_db->get_referrals(-1, -1, TRUE, '', '', $where);
				if ($current_page>1){
					$offset = ( $current_page - 1 ) * $limit;
				} else {
					$offset = 0;
				}
				if ($offset + $limit>$total_items){
					$limit = $total_items - $offset;
				}
				require_once UAP_PATH . 'classes/UapPagination.class.php';
				$limit = (empty($_GET['uap_limit'])) ? 25 : $_GET['uap_limit'];
				$pagination = new UapPagination(array(
						'base_url' => $data['url-manage'],
						'param_name' => 'uap_list_item',
						'total_items' => $total_items,
						'items_per_page' => $limit,
						'current_page' => $current_page,
				));

				$data['base_list_url'] = $data['url-manage'];
				$data['pagination'] = $pagination->output();
				$data['listing_items'] = $indeed_db->get_referrals($limit, $offset, FALSE, 'r.id', 'DESC', $where); /// r.date DESC
				$source_arr = array();
				foreach($available_systems as $v=>$k){
					$source_arr[$v] = uap_service_type_code_to_title($k);
				}
				$data['filter'] = uap_return_date_filter($data['url-manage'],
															array(
																0 => esc_html__('Refused', 'uap'),
					 											1 => esc_html__('Unverified', 'uap'),
					 											2 => esc_html__('Verified', 'uap'),
															),
															$source_arr,
															TRUE
				);

				$data['actions'] = array(
											-1 => '...',
											'delete' => esc_html__('Delete', 'uap'),
											'refuse' => esc_html__('Mark as Refused', 'uap'),
											'pending' => esc_html__('Mark as Unverified', 'uap'),
											'complete' => esc_html__('Mark as Verified', 'uap'),
				);
				if (empty($data['current_actions'])){
					$data['current_actions'] = -1;
				}

				if (!empty($available_systems['woo'])){
					$data['woo_order_base_link'] = admin_url('post.php?post=');// must add &action=edit after id
				}
				if (!empty($available_systems['ulp'])){
					$data['ulp_order_base_link'] = admin_url('post.php?post=');// must add &action=edit after id
				}
				if (!empty($available_systems['edd'])){
					$data['edd_order_base_link'] = admin_url('edit.php?post_type=download&page=edd-payment-history&view=view-order-details&id=');
				}
				if (!empty($available_systems['ump'])){
					$data['ump_order_base_link'] = admin_url('admin.php?page=ihc_manage&tab=payments&details_id=');
				}
				$data['mlm_order_base_link'] = admin_url('admin.php?page=ultimate_affiliates_pro&tab=referral_list_details&id=');
				$data['user_sign_up_link'] = admin_url('user-edit.php?user_id=');
				require_once $this->admin_view_path . 'referrals-list.php';
			}
		}

		private function referral_list_details(){
			/*
			 * @param none
			 * @return string
			 */
			 global $indeed_db;
			 $data['currency'] = get_option('uap_currency');
			 $data['metas'] = array();
			 if(isset($_GET['id'])){
				 $data['metas'] = $indeed_db->get_referral($_GET['id']);
			 }

			 $available_systems = uap_get_active_services();
			 if (in_array('woo', $available_systems)){
				$data['woo_order_base_link'] = admin_url('post.php?post=');// must add &action=edit after id
			 }
			 if (in_array('ulp', $available_systems)){
				$data['ulp_order_base_link'] = admin_url('post.php?post=');// must add &action=edit after id
			 }
			 if (in_array('edd', $available_systems)){
				$data['edd_order_base_link'] = admin_url('edit.php?post_type=download&page=edd-payment-history&view=view-order-details&id=');
			 }
			 if (in_array('ump', $available_systems)){
			 	$data['ump_order_base_link'] = admin_url('admin.php?page=ihc_manage&tab=payments&details_id=');
			 }
			 require_once $this->admin_view_path . 'referral-list-details.php';
		}

		private function print_banners(){
			/*
			 * @param none
			 * @return string
			 */
			$this->print_top_messages();
			global $indeed_db;
			if (!empty($_POST['save']) && !empty($_POST['uap_admin_forms_nonce']) && wp_verify_nonce( $_POST['uap_admin_forms_nonce'], 'uap_admin_forms_nonce' ) ){
				/// SAVE
				$indeed_db->save_banner($_POST);
			} else if (!empty($_POST['delete_banner']) && !empty($_POST['uap_admin_forms_nonce']) && wp_verify_nonce( $_POST['uap_admin_forms_nonce'], 'uap_admin_forms_nonce' ) ){
				/// DELETE
				$indeed_db->delete_banners($_POST);
			}

			/// SET METAS
			$data['subtab'] = (empty($_GET['subtab'])) ? 'list' : $_GET['subtab'];
			$data['url-add_edit'] = admin_url('admin.php?page=ultimate_affiliates_pro&tab=banners&subtab=add_edit');
			$data['form_action_url'] = admin_url('admin.php?page=ultimate_affiliates_pro&tab=banners');
			if ($data['subtab']=='add_edit'){
				$banner_id = (empty($_GET['id'])) ? 0 : $_GET['id'];
				$metas = $indeed_db->get_banner($banner_id);
				$data = array_merge($data, $metas);
			} else {
				$data['listing_items'] = $indeed_db->get_banners();
			}

			/// FINAL OUTPUT
			if ($data['subtab']=='add_edit'){
				require_once $this->admin_view_path . 'banners-add_edit.php';
			} else {
				require_once $this->admin_view_path . 'banners-list.php';
			}

		}

		private function print_view_payment_settings(){
			/*
			 * @param none
			 * @return string
			 */
			global $indeed_db;
			$uid = (empty($_GET['uid'])) ? 0 : $_GET['uid'];
			$data['metas'] = $indeed_db->get_affiliate_payment_settings($uid);

			$this->print_top_messages();
			require_once $this->admin_view_path . 'affiliate-payment-settings.php';
		}

		private function print_payments(){
			/*
			 * @param none
			 * @return string
			 */
			/// PRINT SUBMENU
			$data['submenu'] = array(
									admin_url('admin.php?page=ultimate_affiliates_pro&tab=payments&subtab=list') 							=> esc_html__('Payments For Affiliates', 'uap'),
									admin_url('admin.php?page=ultimate_affiliates_pro&tab=payments&subtab=list_all_unpaid') 	=> esc_html__('All UnPaid Referrals', 'uap'),
									admin_url('admin.php?page=ultimate_affiliates_pro&tab=payments&subtab=list_all_paid') 		=> esc_html__('All Paid Referrals', 'uap'),
									admin_url('admin.php?page=ultimate_affiliates_pro&tab=payments&subtab=transactions') 			=> esc_html__('All Transactions', 'uap'),
			);
			$data['submenu'] = apply_filters( 'uap_admin_filter_payments_submenu', $data['submenu'] );
			require_once $this->admin_view_path . 'submenu.php';
			///
			$this->print_top_messages();
			global $indeed_db;
			$subtab = (empty($_GET['subtab'])) ? 'list' : $_GET['subtab'];
			switch ($subtab){
				case 'list':

					/// VIEW
					$limit = 30;
					$current_page = (empty($_GET['uap_list_item'])) ? 1 : $_GET['uap_list_item'];
					$total_items = $indeed_db->get_payments(-1, -1, TRUE);
					$total_items = (empty($total_items[0])) ? 0 : $total_items[0];
					if ($current_page>1){
						$offset = ( $current_page - 1 ) * $limit;
					} else {
						$offset = 0;
					}
					if ($offset + $limit>$total_items){
						$limit = $total_items - $offset;
					}
					$url = admin_url('admin.php?page=ultimate_affiliates_pro&tab=payments&subtab=list');
					require_once UAP_PATH . 'classes/UapPagination.class.php';
					$limit = 30;
					$pagination = new UapPagination(array(
							'base_url' => $url,
							'param_name' => 'uap_list_item',
							'total_items' => $total_items,
							'items_per_page' => $limit,
							'current_page' => $current_page,
					));
					$data['pagination'] = $pagination->output();

					$data['listing_items'] = $indeed_db->get_payments($limit, $offset, FALSE);
					$data['stats'] = $indeed_db->get_stats_for_payments();
					$data['stats']['currency'] = get_option('uap_currency');
					$data['pay_link'] = admin_url('admin.php?page=ultimate_affiliates_pro&tab=payments&subtab=payment_form');
					$data['unpaid_link'] = admin_url('admin.php?page=ultimate_affiliates_pro&tab=payments&subtab=unpaid');
					$data['paid_link'] = admin_url('admin.php?page=ultimate_affiliates_pro&tab=payments&subtab=transactions');
					$data['paid_referrals'] = admin_url('admin.php?page=ultimate_affiliates_pro&tab=payments&subtab=paid_referrals');

					if ($data['listing_items']){
						$data['payments_settings'] = array();
						foreach ($data['listing_items'] as $id=>$arr){
							if (empty($data['payments_settings'][$id])){
								$data['payments_settings'][$id] = $indeed_db->get_affiliate_payment_type(0, $id);
							}
						}
					}

					require_once $this->admin_view_path . 'payments.php';
					break;
				case 'list_all_unpaid':
					/// VIEW
					$url = admin_url('admin.php?page=ultimate_affiliates_pro&tab=payments&subtab=list_all_unpaid');
					$limit = 30;
					$where = array();
					if (!empty($_REQUEST['udf']) && !empty($_REQUEST['udu'])){
						$where[] = " date>'" . $_REQUEST['udf'] . "' ";
						$where[] = " date<'" . $_REQUEST['udu'] . "' ";
						$url .= '&udf=' . $_REQUEST['udf'] . '&udu=' . $_REQUEST['udu'];
					}
					$current_page = (empty($_GET['uap_list_item'])) ? 1 : $_GET['uap_list_item'];
					$total_items = $indeed_db->get_all_referral_by_payment_status(0, -1, -1, TRUE, '', '', $where);
					$total_items = (empty($total_items[0])) ? 0 : $total_items[0];
					if ($current_page>1){
						$offset = ( $current_page - 1 ) * $limit;
					} else {
						$offset = 0;
					}
					if ($offset + $limit>$total_items){
						$limit = $total_items - $offset;
					}
					$data['pay_link'] = admin_url('admin.php?page=ultimate_affiliates_pro&tab=payments&subtab=payment_form');
					$data['listing_items'] = $indeed_db->get_all_referral_by_payment_status(0, $limit, $offset, FALSE, 'date', 'DESC', $where);
					if ($data['listing_items']){
						$data['payments_settings'] = array();
						foreach ($data['listing_items'] as $arr){
							if (empty($data['payments_settings'][$arr['affiliate_id']])){
								$data['payments_settings'][$arr['affiliate_id']] = $indeed_db->get_affiliate_payment_type(0, $arr['affiliate_id']);
							}
						}
					}
					require_once UAP_PATH . 'classes/UapPagination.class.php';
					$limit = 30;
					$pagination = new UapPagination(array(
							'base_url' => $url,
							'param_name' => 'uap_list_item',
							'total_items' => $total_items,
							'items_per_page' => $limit,
							'current_page' => $current_page,
					));
					$data['pagination'] = $pagination->output();
					$data['filter'] = uap_return_date_filter($url);
					require_once $this->admin_view_path . 'payments-list-all_unpaid.php';
					break;
				case 'list_all_paid':
					$url = admin_url('admin.php?page=ultimate_affiliates_pro&tab=payments&subtab=list_all_paid');
					$limit = 30;
					$where = array();
					if (!empty($_REQUEST['udf']) && !empty($_REQUEST['udu'])){
						$where[] = " date>'" . $_REQUEST['udf'] . "' ";
						$where[] = " date<'" . $_REQUEST['udu'] . "' ";
						$url .= '&udf=' . $_REQUEST['udf'] . '&udu=' . $_REQUEST['udu'];
					}
					$current_page = (empty($_GET['uap_list_item'])) ? 1 : $_GET['uap_list_item'];
					$total_items = $indeed_db->get_all_referral_by_payment_status(2, -1, -1, TRUE, '', '', $where);
					$total_items = (empty($total_items[0])) ? 0 : $total_items[0];
					if ($current_page>1){
						$offset = ( $current_page - 1 ) * $limit;
					} else {
						$offset = 0;
					}
					if ($offset + $limit>$total_items){
						$limit = $total_items - $offset;
					}
					require_once UAP_PATH . 'classes/UapPagination.class.php';
					$limit = 30;
					$data['listing_items'] = $indeed_db->get_all_referral_by_payment_status(2, $limit, $offset, FALSE, 'date', 'DESC', $where);
					$pagination = new UapPagination(array(
							'base_url' => $url,
							'param_name' => 'uap_list_item',
							'total_items' => $total_items,
							'items_per_page' => $limit,
							'current_page' => $current_page,
					));
					$data['pagination'] = $pagination->output();
					$data['filter'] = uap_return_date_filter($url);
					require_once $this->admin_view_path . 'payments-list-all_paid.php';
					break;
				case 'transactions':
					/// ACTIONS
					if (!empty($_POST['do_payment']) && !empty($_POST['uap_admin_payment_nonce']) && wp_verify_nonce( $_POST['uap_admin_payment_nonce'], 'uap_admin_payment_nonce' ) ){
						if (empty($_POST['affiliates'])){
							$errors = $this->do_single_payment($_POST);
							if (!empty($errors['error_users'])){
								$data['error_users'] = $errors['error_users'];
							}

							if ( !empty( $errors['error_details_for_users'] ) ){
									$data['error_details_for_users'] = $errors['error_details_for_users'];
							}
						} else {
							$errors = $this->do_multiple_payments($_POST);

							if (!empty($errors['error_users'])){
								$data['error_users'] = $errors['error_users'];
							}
							if ( !empty( $errors['error_details_for_users'] ) ){
									$data['error_details_for_users'] = $errors['error_details_for_users'];
							}
						}
					}

					if (!empty($_POST['transaction_id']) && !empty($_POST['uap_admin_forms_nonce']) && wp_verify_nonce( $_POST['uap_admin_forms_nonce'], 'uap_admin_forms_nonce' ) ){
						$indeed_db->change_transaction_status($_POST['transaction_id'], $_POST['new_status']);
					} else if (!empty($_POST['delete_transaction']) && !empty($_POST['uap_admin_forms_nonce']) && wp_verify_nonce( $_POST['uap_admin_forms_nonce'], 'uap_admin_forms_nonce' ) ){
						$indeed_db->cancel_transaction($_POST['delete_transaction']);
					} else if (!empty($_GET['do_update_payments']) && !empty($_POST['uap_admin_forms_nonce']) && wp_verify_nonce( $_POST['uap_admin_forms_nonce'], 'uap_admin_forms_nonce' )){
						$indeed_db->update_paypal_transactions();
					}

					/// VIEW
					$affiliate_id = (empty($_GET['affiliate'])) ? 0 : $_GET['affiliate'];
					if ($affiliate_id){
						$wpuid = $indeed_db->get_uid_by_affiliate_id($affiliate_id);
						$username = $indeed_db->get_username_by_wpuid($wpuid);
						$full_name = $indeed_db->get_full_name_of_user($affiliate_id);
						$data['subtitle'] = esc_html__('View Transactions for', 'uap') . " $full_name ($username)";
					}
					$limit = 30;
					$where = array();
					if (!empty($_REQUEST['udf']) && !empty($_REQUEST['udu'])){
						$where[] = " create_date>'" . $_REQUEST['udf'] . "' ";
						$where[] = " create_date<'" . $_REQUEST['udu'] . "' ";
						$url .= '&udf=' . $_REQUEST['udf'] . '&udu=' . $_REQUEST['udu'];
					}
					$current_page = (empty($_GET['uap_list_item'])) ? 1 : $_GET['uap_list_item'];
					$total_items = (int)($indeed_db->get_transactions($affiliate_id, -1, -1, TRUE, '', '', $where));

					if ($current_page>1){
						$offset = ( $current_page - 1 ) * $limit;
					} else {
						$offset = 0;
					}
					if ($offset + $limit>$total_items){
						$limit = $total_items - $offset;
					}
					$url = admin_url('admin.php?page=ultimate_affiliates_pro&tab=payments&subtab=transactions');
					require_once UAP_PATH . 'classes/UapPagination.class.php';
					$limit = 30;
					$pagination = new UapPagination(array(
							'base_url' => $url,
							'param_name' => 'uap_list_item',
							'total_items' => $total_items,
							'items_per_page' => $limit,
							'current_page' => $current_page,
					));

					$data['listing_items'] = $indeed_db->get_transactions($affiliate_id, $limit, $offset, FALSE, 'create_date', 'DESC', $where);
					$data['pagination'] = $pagination->output();
					$data['filter'] = uap_return_date_filter($url);
					$data['view_transaction_url'] = admin_url('admin.php?page=ultimate_affiliates_pro&tab=payments&subtab=view_transaction_details');
					$data['update_payments'] = admin_url('admin.php?page=ultimate_affiliates_pro&tab=payments&subtab=transactions&do_update_payments=1');
					require_once $this->admin_view_path . 'transactions.php';
					break;
				case 'unpaid':
					$limit = 30;
					$current_page = (empty($_GET['uap_list_item'])) ? 1 : $_GET['uap_list_item'];
					$url = admin_url('admin.php?page=ultimate_affiliates_pro&tab=payments&subtab=unpaid&affiliate=' . $_GET['affiliate']);
					$where = array();
					if (!empty($_REQUEST['udf']) && !empty($_REQUEST['udu'])){
						$where[] = " date>'" . $_REQUEST['udf'] . "' ";
						$where[] = " date<'" . $_REQUEST['udu'] . "' ";
						$url .= '&udf=' . $_REQUEST['udf'] . '&udu=' . $_REQUEST['udu'];
					}
					if (!empty($_GET['affiliate'])){
						$wpuid = $indeed_db->get_uid_by_affiliate_id($_GET['affiliate']);
						$username = $indeed_db->get_username_by_wpuid($wpuid);
						$full_name = $indeed_db->get_full_name_of_user($_GET['affiliate']);
						$data['subtitle'] = esc_html__('View UnPaid Referrals for', 'uap') . " $full_name ($username)";
					}
					$total_items = $indeed_db->get_unpaid_payments_for_affiliate($_GET['affiliate'], -1, -1, TRUE, '', '', $where);
					$total_items = (empty($total_items[0])) ? 0 : $total_items[0];
					if ($current_page>1){
						$offset = ( $current_page - 1 ) * $limit;
					} else {
						$offset = 0;
					}
					if ($offset + $limit>$total_items){
						$limit = $total_items - $offset;
					}
					$data['listing_items'] = $indeed_db->get_unpaid_payments_for_affiliate($_GET['affiliate'], $limit, $offset, FALSE, '', '', $where);
					if ($data['listing_items']){
						$data['payments_settings'] = array();
						if(isset($_GET['affiliate'])){
							$data['payments_settings'][$_GET['affiliate']] = $indeed_db->get_affiliate_payment_type(0, $_GET['affiliate']);
						}

					}
					require_once UAP_PATH . 'classes/UapPagination.class.php';
					$limit = 30;
					$pagination = new UapPagination(array(
							'base_url' 					=> $url,
							'param_name' 				=> 'uap_list_item',
							'total_items' 			=> $total_items,
							'items_per_page' 		=> $limit,
							'current_page' 			=> $current_page,
					));
					$data['pagination'] = $pagination->output();
					$data['pay_link'] = admin_url('admin.php?page=ultimate_affiliates_pro&tab=payments&subtab=payment_form&affiliate=' . $_GET['affiliate']);
					$data['filter'] = uap_return_date_filter($url);
					$data['selected_referrences'] = [];
					$data['selected_referrences'] = apply_filters( 'uap_admin_payments_list_unpaid_for_affiliate_selected_referrences', $data['selected_referrences'] );
					require_once $this->admin_view_path . 'payments-list-all_unpaid.php';
					break;
				case 'paid_referrals':
					/// VIEW
					$limit = 30;
					$current_page = (empty($_GET['uap_list_item'])) ? 1 : $_GET['uap_list_item'];
					$where = array();
					if (!empty($_REQUEST['udf']) && !empty($_REQUEST['udu'])){
						$where[] = " date>'" . $_REQUEST['udf'] . "' ";
						$where[] = " date<'" . $_REQUEST['udu'] . "' ";
						$url .= '&udf=' . $_REQUEST['udf'] . '&udu=' . $_REQUEST['udu'];
					}
					if (!empty($_GET['affiliate'])){
						$wpuid = $indeed_db->get_uid_by_affiliate_id($_GET['affiliate']);
						$username = $indeed_db->get_username_by_wpuid($wpuid);
						$full_name = $indeed_db->get_full_name_of_user($_GET['affiliate']);
						$data['subtitle'] = esc_html__('View Paid Referrals for', 'uap') . " $full_name ($username)";
					}
					$total_items = $indeed_db->get_paid_referrals_for_affiliate($_GET['affiliate'], -1, -1, TRUE, '', '', $where);
					$total_items = (empty($total_items[0])) ? 0 : $total_items[0];
					if ($current_page>1){
						$offset = ( $current_page - 1 ) * $limit;
					} else {
						$offset = 0;
					}
					if ($offset + $limit>$total_items){
						$limit = $total_items - $offset;
					}
					$url = admin_url('admin.php?page=ultimate_affiliates_pro&tab=payments&subtab=paid_referrals&affiliate=' . $_GET['affiliate']);
					$data['listing_items'] = $indeed_db->get_paid_referrals_for_affiliate($_GET['affiliate'], $limit, $offset, FALSE, '', '', $where);
					require_once UAP_PATH . 'classes/UapPagination.class.php';
					$limit = 30;
					$pagination = new UapPagination(array(
							'base_url' => $url,
							'param_name' => 'uap_list_item',
							'total_items' => $total_items,
							'items_per_page' => $limit,
							'current_page' => $current_page,
					));
					$data['pagination'] = $pagination->output();
					$data['filter'] = uap_return_date_filter($url);
					require_once $this->admin_view_path . 'payments-list-all_paid.php';
					break;
				case 'payment_form':
					/// ACTIONS
					if (!empty($_POST['referrals'])){
						$ids = implode(',', $_POST['referrals']);
						if (empty($_GET['affiliate'])){
							/// get details for affiliate that has the selected referrals
							$data['multiple_affiliates'] = $indeed_db->get_affiliate_payment_details_for_referral_list($ids);
						} else {
							//details for one affiliate
							$data['affiliate_pay'] = $indeed_db->get_affiliate_payment_details($_GET['affiliate'], $ids);
						}
					} else if (!empty($_GET['affiliate'])){
						$data['affiliate_pay'] = $indeed_db->get_affiliate_payment_details($_GET['affiliate']);
					}


					/// VIEW
					$data['currency'] = get_option('uap_currency');
					$data['submit_link'] = admin_url('admin.php?page=ultimate_affiliates_pro&tab=payments&subtab=transactions');
					$data['return_url'] = admin_url('admin.php?page=ultimate_affiliates_pro&tab=payments');
					$data['paypal'] = $indeed_db->is_magic_feat_enable('paypal');
					$data['stripe'] = $indeed_db->is_magic_feat_enable('stripe');
					$data['stripe_v2'] = $indeed_db->is_magic_feat_enable('stripe_v2');
					$data['stripe_v3'] = $indeed_db->is_magic_feat_enable('stripe_v3');
					require_once $this->admin_view_path . 'payment-form.php';
					break;
				case 'view_transaction_details':
					$data['listing_items'] = array();
					if(isset($_GET['id'])){
						$data['listing_items'] = $indeed_db->get_transation_details($_GET['id']);
					}

					if ($data['listing_items'] && isset($data['listing_items'][0]) && isset($data['listing_items'][0]['affiliate_id'])){
						$affiliate_id = $data['listing_items'][0]['affiliate_id'];
						$data['payments_settings'] = $data['payments_settings']= $indeed_db->get_affiliate_payment_type(0, $affiliate_id);
					}
					$data['payment_details_on_transaction'] = array();
					if(isset($_GET['id'])){
						$data['payment_details_on_transaction'] = $indeed_db->get_payment_details_on_transaction_by_id($_GET['id']);
					}

					require_once $this->admin_view_path . 'payments-list-all_paid.php';
					break;
				default:
					do_action( 'uap_admin_dashboard_payment_tab_custom_subtab', $subtab );
					break;
			}

		}

		private function do_single_payment($post_data=array()){
			/*
			 * @param array
			 * @return boolean
			 */
			if (empty($post_data)){
				 return;
			}
			global $indeed_db;
			$ids = (empty($post_data["referrals_in"])) ? array() : explode(',', $post_data["referrals_in"]);
			switch ($post_data['paywith']):
				case 'bank_transfer':
					/// bank transfer
					$indeed_db->change_referrals_status($ids, $post_data['payment_status']);/// set referral payment as complete
					$data = array(
									'payment_type' => 'bank_transfer',
									'transaction_id' => '-',
									'referral_ids' => $post_data["referrals_in"],
									'affiliate_id' => $post_data['affiliate_id'],
									'amount' => $post_data['amount'],
									'currency' => $post_data['currency'],
									'create_date' => current_time( 'Y-m-d H:i:s' ), //date('Y-m-d H:i:s', time()),
									'update_date' => current_time( 'Y-m-d H:i:s' ), //date('Y-m-d H:i:s', time()),
									'status' => $post_data['payment_status'],
					);
					$indeed_db->add_payment($data);
					return TRUE;
					break;
				case 'paypal':
					/// paypal
					require_once UAP_PATH . 'classes/PayoutPayPal.class.php';
					$object = new PayoutPayPal();
					$email = $indeed_db->get_paypal_email_addr($post_data['affiliate_id']);
					$object->add_payment( $email, $post_data['amount'], $post_data['currency'] );
					$batch_id = $object->do_payout();
					if ($batch_id){
						$indeed_db->change_referrals_status($ids, 1);/// set referral payment status as pending
						$data = array(
								'payment_type' => 'paypal',
								'transaction_id' => $batch_id,
								'referral_ids' => $post_data["referrals_in"],
								'affiliate_id' => $post_data['affiliate_id'],
								'amount' => $post_data['amount'],
								'currency' => $post_data['currency'],
								'create_date' => current_time( 'Y-m-d H:i:s' ),
								'update_date' => current_time( 'Y-m-d H:i:s' ),
								'status' => 1,
						);
						$indeed_db->add_payment($data);
						return TRUE;
					} else {
							$errorDetails = $object->getErrorDetails();
							if ( $errorDetails ){
									/// error deatils
									$return['error_details_for_users'][] = [
											'username'				=> $indeed_db->get_wp_username_by_affiliate_id($post_data['affiliate_id']),
											'error_message'		=> $errorDetails,
									];
							} else {
									// general error
									$return['error_users'][] = $indeed_db->get_wp_username_by_affiliate_id($post_data['affiliate_id']);
									$errorDetails = esc_html__( 'The Payment cannot be proceed because of the payment settings.', 'uap' );
							}
							$data = array(
									'payment_type' 				=> 'paypal',
									'transaction_id' 			=> '',
									'referral_ids' 				=> $post_data["referrals_in"],
									'affiliate_id' 				=> $post_data['affiliate_id'],
									'amount' 							=> $post_data["amount"],
									'currency' 						=> $post_data['currency'],
									'create_date' 				=> current_time( 'Y-m-d H:i:s' ),
									'update_date' 				=> current_time( 'Y-m-d H:i:s' ),
									'status' 							=> 0,
									'errors'							=> $errorDetails,
							);

							$indeed_db->add_payment($data);
					}

					return $return;
					break;
				case 'stripe':
					require_once UAP_PATH . 'classes/PayoutStripe.class.php';
					$object = new PayoutStripe();
					$transaction_id = $object->do_payout(0, $post_data['affiliate_id'], $post_data['amount'], $post_data['currency']);
					if ($transaction_id){
						$indeed_db->change_referrals_status($ids, 1);/// set referral payment status as pending
						$data = array(
								'payment_type' => 'stripe',
								'transaction_id' => $transaction_id,
								'referral_ids' => $post_data["referrals_in"],
								'affiliate_id' => $post_data['affiliate_id'],
								'amount' => $post_data['amount'],
								'currency' => $post_data['currency'],
								'create_date' => current_time( 'Y-m-d H:i:s' ),
								'update_date' => current_time( 'Y-m-d H:i:s' ),
								'status' => 1,
						);
						$indeed_db->add_payment($data);
						return TRUE;
					}
					$return['error_users'][] = $indeed_db->get_wp_username_by_affiliate_id($post_data['affiliate_id']);
					return $return;
					break;
				case 'stripe_v2':
					require_once UAP_PATH . 'classes/PayoutStripeV2.class.php';
					$object = new PayoutStripeV2();
					$transaction_id = $object->do_payout(0, $post_data['affiliate_id'], $post_data['amount'], $post_data['currency']);
					if ($transaction_id){
						$indeed_db->change_referrals_status($ids, 1);/// set referral payment status as pending
						$data = array(
								'payment_type' => 'stripe_v2',
								'transaction_id' => $transaction_id,
								'referral_ids' => $post_data["referrals_in"],
								'affiliate_id' => $post_data['affiliate_id'],
								'amount' => $post_data['amount'],
								'currency' => $post_data['currency'],
								'create_date' => current_time( 'Y-m-d H:i:s' ),
								'update_date' => current_time( 'Y-m-d H:i:s' ),
								'status' => 1,
						);
						$indeed_db->add_payment($data);
						return TRUE;
					}
					$return['error_users'][] = $indeed_db->get_wp_username_by_affiliate_id($post_data['affiliate_id']);
					return $return;
					break;
				case 'stripe_v3':
					$object = new \Indeed\Uap\PayoutStripeV3();
					$transaction_id = $object->do_payout(0, $post_data['affiliate_id'], $post_data['amount'], $post_data['currency'] );
					if ($transaction_id){
						$indeed_db->change_referrals_status($ids, 1);/// set referral payment status as pending
						$data = array(
								'payment_type' 				=> 'stripe_v3',
								'transaction_id' 			=> $transaction_id,
								'referral_ids' 				=> $post_data["referrals_in"],
								'affiliate_id' 				=> $post_data['affiliate_id'],
								'amount' 							=> $post_data['amount'],
								'currency' 						=> $post_data['currency'],
								'create_date' 				=> current_time( 'Y-m-d H:i:s' ),
								'update_date' 				=> current_time( 'Y-m-d H:i:s' ),
								'status' 							=> 1,
						);
						$indeed_db->add_payment($data);
						return TRUE;
					}
					$return['error_users'][] = $indeed_db->get_wp_username_by_affiliate_id($post_data['affiliate_id']);
					break;
			endswitch;
		}

		private function do_multiple_payments($post_data=array()){
			/*
			 * @param array
			 * @return none
			 */
			if (empty($post_data)){
				 return;
			}
			global $indeed_db;
			switch ($post_data['paywith']):
				case 'bank_transfer':
					/// bank transfer
					$affiliates_arr = (empty($post_data['affiliates'])) ? '' : explode(',', $post_data['affiliates']);
					if ($affiliates_arr){
						foreach ($affiliates_arr as $affiliate_id){
							$ids = (empty($post_data["referrals"][$affiliate_id])) ? array() : explode(',', $post_data["referrals"][$affiliate_id]);
							$indeed_db->change_referrals_status($ids, $post_data['payment_status']);/// set referral payment status as complete
							$data = array(
									'payment_type' => 'bank_transfer',
									'transaction_id' => '-',
									'referral_ids' => $post_data["referrals"][$affiliate_id],
									'affiliate_id' => $affiliate_id,
									'amount' => $post_data["amount"][$affiliate_id],
									'currency' => $post_data['currency'][$affiliate_id],
									'create_date' => current_time( 'Y-m-d H:i:s' ),
									'update_date' => current_time( 'Y-m-d H:i:s' ),
									'status' => $post_data['payment_status'],
							);
							$indeed_db->add_payment($data);
						}
						return TRUE;
					}
					break;
				case 'paypal':
					/// paypal
					$return = array();
					$affiliates_arr = (empty($post_data['affiliates'])) ? '' : explode(',', $post_data['affiliates']);

					if ($affiliates_arr){
						require_once UAP_PATH . 'classes/PayoutPayPal.class.php';
						foreach ($affiliates_arr as $affiliate_id){
							$ids = (empty($post_data["referrals"][$affiliate_id])) ? array() : explode(',', $post_data["referrals"][$affiliate_id]);

							$object = new PayoutPayPal();
							$email = $indeed_db->get_paypal_email_addr($affiliate_id);
							$object->add_payment($email, $post_data["amount"][$affiliate_id], $post_data['currency'][$affiliate_id]);
							$batch_id = $object->do_payout();

							if ($batch_id){
								$indeed_db->change_referrals_status($ids, 1);///set referral payment status as pending
								$status = 1;
							} else {
									if (empty($return['error_users'])){
										$return['error_users'] = array();
									}
									$errorDetails = $object->getErrorDetails();
									if ( $errorDetails ){
											/// error deatils
											$return['error_details_for_users'][] = [
													'username'				=> $indeed_db->get_wp_username_by_affiliate_id($affiliate_id),
													'error_message'		=> $errorDetails,
											];
									} else {
											// general error
											$return['error_users'][] = $indeed_db->get_wp_username_by_affiliate_id($affiliate_id);
											$errorDetails = esc_html__( 'The Payment cannot be proceed because of the payment settings.', 'uap' );
									}
									$batch_id = '';
									$status = 0;
							}

							/// insert transaction
							$data = array(
									'payment_type' 				=> 'paypal',
									'transaction_id' 			=> $batch_id,
									'referral_ids' 				=> $post_data["referrals"][$affiliate_id],
									'affiliate_id' 				=> $affiliate_id,
									'amount' 							=> $post_data["amount"][$affiliate_id],
									'currency' 						=> $post_data['currency'][$affiliate_id],
									'create_date' 				=> current_time( 'Y-m-d H:i:s' ),
									'update_date' 				=> current_time( 'Y-m-d H:i:s' ),
									'status' 							=> $status,
									'errors'							=> (isset($errorDetails)) ? $errorDetails : '',
							);
							$indeed_db->add_payment($data);

							unset($object);
						}
					}
					return $return;
					break;
				case 'stripe':
					$return = array();
					$affiliates_arr = (empty($post_data['affiliates'])) ? '' : explode(',', $post_data['affiliates']);
					if ($affiliates_arr){
						require_once UAP_PATH . 'classes/PayoutStripe.class.php';
						foreach ($affiliates_arr as $affiliate_id){
							$ids = (empty($post_data["referrals"][$affiliate_id])) ? array() : explode(',', $post_data["referrals"][$affiliate_id]);
							$object = new PayoutStripe();
							$transaction_id = $object->do_payout(0, $affiliate_id, $post_data["amount"][$affiliate_id], $post_data['currency'][$affiliate_id]);
							if ($transaction_id){
								$indeed_db->change_referrals_status($ids, 1);/// set referral payment status as pending
								$data = array(
										'payment_type' => 'stripe',
										'transaction_id' => $transaction_id,
										'referral_ids' => $post_data["referrals"][$affiliate_id],
										'affiliate_id' => $affiliate_id,
										'amount' => $post_data["amount"][$affiliate_id],
										'currency' => $post_data['currency'][$affiliate_id],
										'create_date' => current_time( 'Y-m-d H:i:s' ),
										'update_date' => current_time( 'Y-m-d H:i:s' ),
										'status' => 1,
								);
								$indeed_db->add_payment($data);
							} else {
								if (empty($return['error_users'])){
									$return['error_users'] = array();
								}
								$return['error_users'][] = $indeed_db->get_wp_username_by_affiliate_id($affiliate_id);
							}
							unset($object);
						}
					}
					return $return;
					break;
				case 'stripe_v2':
					$return = array();
					$affiliates_arr = (empty($post_data['affiliates'])) ? '' : explode(',', $post_data['affiliates']);
					if ($affiliates_arr){
						require_once UAP_PATH . 'classes/PayoutStripeV2.class.php';
						foreach ($affiliates_arr as $affiliate_id){
							$ids = (empty($post_data["referrals"][$affiliate_id])) ? array() : explode(',', $post_data["referrals"][$affiliate_id]);
							$object = new PayoutStripeV2();
							$transaction_id = $object->do_payout(0, $affiliate_id, $post_data["amount"][$affiliate_id], $post_data['currency'][$affiliate_id]);
							if ($transaction_id){
								$indeed_db->change_referrals_status($ids, 1);/// set referral payment status as pending
								$data = array(
										'payment_type' => 'stripe_v2',
										'transaction_id' => $transaction_id,
										'referral_ids' => $post_data["referrals"][$affiliate_id],
										'affiliate_id' => $affiliate_id,
										'amount' => $post_data["amount"][$affiliate_id],
										'currency' => $post_data['currency'][$affiliate_id],
										'create_date' => current_time( 'Y-m-d H:i:s' ),
										'update_date' => current_time( 'Y-m-d H:i:s' ),
										'status' => 1,
								);
								$indeed_db->add_payment($data);
							} else {
								if (empty($return['error_users'])){
									$return['error_users'] = array();
								}
								$return['error_users'][] = $indeed_db->get_wp_username_by_affiliate_id($affiliate_id);
							}
							unset($object);
						}
					}
					return $return;
					break;
				case 'stripe_v3':
					$return = [];
					$affiliates_arr = (empty($post_data['affiliates'])) ? '' : explode(',', $post_data['affiliates']);
					if ($affiliates_arr){
						foreach ($affiliates_arr as $affiliate_id){
							$ids = (empty($post_data["referrals"][$affiliate_id])) ? array() : explode(',', $post_data["referrals"][$affiliate_id]);
							$object = new \Indeed\Uap\PayoutStripeV3();
							$transaction_id = $object->do_payout( 0, $affiliate_id, $post_data["amount"][$affiliate_id], $post_data['currency'][$affiliate_id] );
							if ($transaction_id){
								$indeed_db->change_referrals_status( $ids, 1 );/// set referral payment status as pending
								$data = array(
										'payment_type' 			=> 'stripe_v3',
										'transaction_id' 		=> $transaction_id,
										'referral_ids' 			=> $post_data["referrals"][$affiliate_id],
										'affiliate_id' 			=> $affiliate_id,
										'amount' 						=> $post_data["amount"][$affiliate_id],
										'currency' 					=> $post_data['currency'][$affiliate_id],
										'create_date' 			=> current_time( 'Y-m-d H:i:s' ),
										'update_date' 			=> current_time( 'Y-m-d H:i:s' ),
										'status' 						=> 1,
								);
								$indeed_db->add_payment($data);
							} else {
								if (empty($return['error_users'])){
									$return['error_users'] = array();
								}
								$return['error_users'][] = $indeed_db->get_wp_username_by_affiliate_id($affiliate_id);
							}
							unset($object);
						}
					}
					return $return;
					break;
			endswitch;
		}

		private function print_notifications(){
			/*
			 * @param none
			 * @return string
			 */
			$this->print_top_messages();
			global $indeed_db;

			if (!empty($_POST['save']) && !empty($_POST['uap_admin_forms_nonce']) && wp_verify_nonce( $_POST['uap_admin_forms_nonce'], 'uap_admin_forms_nonce' ) ){
				/// SAVE
				$indeed_db->save_notification($_POST);
			} else if (!empty($_POST['delete_notification']) && !empty($_POST['uap_admin_forms_nonce']) && wp_verify_nonce( $_POST['uap_admin_forms_nonce'], 'uap_admin_forms_nonce' ) ){
				/// DELETE
				$indeed_db->delete_notification($_POST['delete_notification']);
			}

			$data['form_action_url'] = admin_url('admin.php?page=ultimate_affiliates_pro&tab=notifications');
			$data['subtab'] = (empty($_GET['subtab'])) ? 'list' : $_GET['subtab'];
			$data['url-add_edit'] = admin_url('admin.php?page=ultimate_affiliates_pro&tab=notifications&subtab=add_edit');
			$data['actions_available'] = array(
												'admin_user_register' => esc_html__('Affiliate Register - Admin Notification', 'uap'),
												'register' => esc_html__('Affiliate Register - User Notification', 'uap'),
												'affiliate_account_approve' => esc_html__('Affiliate - Approve Account', 'uap'),
												'affiliate_profile_delete' => esc_html__('Affiliate - Delete Account', 'uap'),
												'user_update' => esc_html__('User Profile Updates', 'uap'),
												'rank_change' => esc_html__('Affiliate get new Rank', 'uap'),
												'reset_password_process' => esc_html__('Reset Password - Start Process', 'uap'),
												'reset_password' => esc_html__('Reset Password - New Password', 'uap'),
												'change_password' => esc_html__('Change Password', 'uap'),
												'admin_on_aff_change_rank' => esc_html__('Admin - Affiliate get new Rank', 'uap'),
												'admin_affiliate_update_profile' => esc_html__('Admin - Affiliate update profile', 'uap'),
												'affiliate_payment_fail' => esc_html__('Affiliate - Payment Inform - Fail', 'uap'),
												'affiliate_payment_pending' => esc_html__('Affiliate - Payment Inform - Pending', 'uap'),
												'affiliate_payment_complete' => esc_html__('Affiliate - Payment Inform - Complete', 'uap'),
												'register_lite_send_pass_to_user' => esc_html__('Affiliate - Send generated password', 'uap'),
												'email_check' => esc_html__('Affiliate - Double E-mail Verification Request', 'uap'),
												'email_check_success' => esc_html__('Affiliate - Double E-mail Verification Validated', 'uap'),
			);
			$data['email_verification'] = $indeed_db->is_magic_feat_enable('email_verification');
			if (empty($data['email_verification'])){
				unset($data['actions_available']['email_check']);
				unset($data['actions_available']['email_check_success']);
			}
			$data['ranks'] = $indeed_db->get_rank_list();

			if ($data['subtab']=='add_edit'){
				$data['ranks_available'] = array( -1 => esc_html__('All', 'uap') ) + $data['ranks'];
				//and the rest of ranks..

				$id = (empty($_GET['id'])) ? 0 : $_GET['id'];
				$metas = $indeed_db->get_notification($id);
				$data = array_merge($data, $metas);
			} else {
				$data['listing_items'] = $indeed_db->get_notifications();
			}

			if ($data['subtab']=='add_edit'){
				require_once $this->admin_view_path . 'notifications-add_edit.php';
			} else {
				require_once $this->admin_view_path . 'notifications-list.php';
			}

		}

		private function print_showcases(){
			/*
			 * @param none
			 * @return string
			 */
			$this->print_top_messages();
			$data['url_register_settings'] = admin_url('admin.php?page=ultimate_affiliates_pro&tab=register');
			$data['url_login'] = admin_url('admin.php?page=ultimate_affiliates_pro&tab=login');
			$data['url_account_page'] = admin_url('admin.php?page=ultimate_affiliates_pro&tab=account_page');
			$data['url_top_affiliates'] = admin_url('admin.php?page=ultimate_affiliates_pro&tab=top_affiliates');
			require_once $this->admin_view_path . 'showcases.php';
		}

		private function print_reports(){
			/*
			 * @param none
			 * @return string
			 */
			$data['submenu'] = array(
					admin_url('admin.php?page=ultimate_affiliates_pro&tab=reports&subtab=reports') => esc_html__('Reports', 'uap'),
					admin_url('admin.php?page=ultimate_affiliates_pro&tab=reports&subtab=achievements') => esc_html__('Achievements', 'uap'),
			);
			require_once $this->admin_view_path . 'submenu.php';
			$this->print_top_messages();

			global $indeed_db;
			$subtab = (empty($_GET['subtab'])) ? 'reports' : $_GET['subtab'];
			if ($subtab=='reports'){
				$data['select_values'] = array(
						'today' => esc_html__('Today', 'uap'),
						'yesterday' => esc_html__('Yesterday', 'uap'),
						'last_week' => esc_html__('Last Week', 'uap'),
						'last_month' => esc_html__('Last Month', 'uap'),
						'all_time' => esc_html__('All Time', 'uap'),
				);
				$data['selected'] = (isset($_POST['search'])) ? $_POST['search'] : 'all_time';
				$affiliate_id = (empty($_REQUEST['affiliate_id'])) ? 0 : $_REQUEST['affiliate_id'];
				if ($affiliate_id){
					$wpuid = $indeed_db->get_uid_by_affiliate_id($affiliate_id);
					$username = $indeed_db->get_username_by_wpuid($wpuid);
					$full_name = $indeed_db->get_full_name_of_user($affiliate_id);
					$data['subtitle'] =  " $full_name ($username)";
				}

				$data['reports'] = $indeed_db->get_stats_for_reports($data['selected'], $affiliate_id);
				$data['currency'] = get_option('uap_currency');

				/*********** GRAPHS STUFF *******/
				if ($data['selected']=='today' || $data['selected']=='yesterday'){
					$data['tick_type'] = 'hour';
				} else if ($data['selected']=='last_week' || $data['selected']=='last_month'){
					$data['tick_type'] = 'day';
				} else {
					$data['tick_type'] = 'month';
				}
				/// GRAPH VISITS
				$data['visit_graph'] = $indeed_db->get_visits_for_graph($data['selected'], 'all', $affiliate_id);
				$data['visit_graph_success'] = $indeed_db->get_visits_for_graph($data['selected'], 'success', $affiliate_id);
				/// GRAPH REFERRALS
				$data['referrals_graph'] = $indeed_db->get_referrals_for_graph($data['selected'], -1, $affiliate_id);
				$data['referrals_graph-refuse'] = $indeed_db->get_referrals_for_graph($data['selected'], 0, $affiliate_id);
				$data['referrals_graph-unverified'] = $indeed_db->get_referrals_for_graph($data['selected'], 1, $affiliate_id);
				$data['referrals_graph-verified'] = $indeed_db->get_referrals_for_graph($data['selected'], 2, $affiliate_id);

				require_once $this->admin_view_path . 'reports.php';
			} else if ($subtab=='achievements') {
				if ( !empty($_POST['uap_admin_forms_nonce']) && wp_verify_nonce( $_POST['uap_admin_forms_nonce'], 'uap_admin_forms_nonce' ) ){
						$search = (empty($_POST['search'])) ? '' : $_POST['affiliate_username'];
				} else {
						$search = '';
				}

				$data['current_url'] = admin_url('admin.php?page=ultimate_affiliates_pro&tab=reports&subtab=achievements_for_affiliate');
				$data['history'] = $indeed_db->get_last_rank_achievements(50, $search);
				require_once $this->admin_view_path . 'achievements.php';
			}
		}

		private function print_settings(){
			/*
			 * @param none
			 * @return string
			 */
			/// PRINT SUBMENU
			$data['submenu'] = array(
					admin_url('admin.php?page=ultimate_affiliates_pro&tab=settings&subtab=general') => esc_html__('General Settings', 'uap'),
					admin_url('admin.php?page=ultimate_affiliates_pro&tab=settings&subtab=admin_workflow') => esc_html__('Admin Workflow', 'uap'),
					admin_url('admin.php?page=ultimate_affiliates_pro&tab=settings&subtab=public_workflow') => esc_html__('Public Workflow', 'uap'),
					admin_url('admin.php?page=ultimate_affiliates_pro&tab=settings&subtab=notification_settings') => esc_html__('Notifications Settings', 'uap'),
					admin_url('admin.php?page=ultimate_affiliates_pro&tab=settings&subtab=default_pages') => esc_html__('Default Pages', 'uap'),
					admin_url('admin.php?page=ultimate_affiliates_pro&tab=settings&subtab=redirects') => esc_html__('Default Redirects', 'uap'),
					admin_url('admin.php?page=ultimate_affiliates_pro&tab=settings&subtab=access') => esc_html__('WP Dashboard Access', 'uap'),
					admin_url('admin.php?page=ultimate_affiliates_pro&tab=settings&subtab=captcha') => esc_html__('reCaptcha Setup', 'uap'),
					admin_url('admin.php?page=ultimate_affiliates_pro&tab=settings&subtab=uploads') => esc_html__('Upload Settings', 'uap'),
			);
			require_once $this->admin_view_path . 'submenu.php';
			///

			$this->print_top_messages();
			global $indeed_db;
			$data['subtab'] = (empty($_GET['subtab'])) ? 'general' : $_GET['subtab'];

			switch ($data['subtab']){
				case 'general':
					if (!empty($_POST['save']) && !empty($_POST['uap_admin_forms_nonce']) && wp_verify_nonce( $_POST['uap_admin_forms_nonce'], 'uap_admin_forms_nonce' ) ){
						$indeed_db->save_settings_wp_option('general-settings', $_POST);
					}
					$data['metas'] = $indeed_db->return_settings_from_wp_option('general-settings');
					require_once $this->admin_view_path . 'settings-general.php';
					break;

				case 'redirects':
					if (!empty($_POST['save']) && !empty($_POST['uap_admin_forms_nonce']) && wp_verify_nonce( $_POST['uap_admin_forms_nonce'], 'uap_admin_forms_nonce' ) ){
						$indeed_db->save_settings_wp_option('general-redirects', $_POST);
					}
					$data['metas'] = $indeed_db->return_settings_from_wp_option('general-redirects');
					require_once $this->admin_view_path . 'settings-redirects.php';
					break;
				case 'default_pages':
					if (!empty($_POST['save']) && !empty($_POST['uap_admin_forms_nonce']) && wp_verify_nonce( $_POST['uap_admin_forms_nonce'], 'uap_admin_forms_nonce' ) ){
						$indeed_db->save_settings_wp_option('general-default_pages', $_POST);
					}
					$data['metas'] = $indeed_db->return_settings_from_wp_option('general-default_pages');
					require_once $this->admin_view_path . 'settings-default_pages.php';
					break;
				case 'captcha':
					if (!empty($_POST['save']) && !empty($_POST['uap_admin_forms_nonce']) && wp_verify_nonce( $_POST['uap_admin_forms_nonce'], 'uap_admin_forms_nonce' ) ){
						$indeed_db->save_settings_wp_option('general-captcha', $_POST);
					}
					$data['metas'] = $indeed_db->return_settings_from_wp_option('general-captcha');
					require_once $this->admin_view_path . 'settings-captcha.php';
					break;
				case 'uploads':
					if (!empty($_POST['save']) && !empty($_POST['uap_admin_forms_nonce']) && wp_verify_nonce( $_POST['uap_admin_forms_nonce'], 'uap_admin_forms_nonce' ) ){
						$indeed_db->save_settings_wp_option('general-uploads', $_POST);
					}
					$data['metas'] = $indeed_db->return_settings_from_wp_option('general-uploads');
					require_once $this->admin_view_path . 'settings-uploads.php';
					break;
				case 'notification_settings':
					if (!empty($_POST['save']) && !empty($_POST['uap_admin_forms_nonce']) && wp_verify_nonce( $_POST['uap_admin_forms_nonce'], 'uap_admin_forms_nonce' ) ){
						$indeed_db->save_settings_wp_option('general-notification', $_POST);
					}
					$data['metas'] = $indeed_db->return_settings_from_wp_option('general-notification');
					require_once $this->admin_view_path . 'notification-settings.php';
					break;
				case 'access':
					if (!empty($_POST['save']) && !empty($_POST['uap_admin_forms_nonce']) && wp_verify_nonce( $_POST['uap_admin_forms_nonce'], 'uap_admin_forms_nonce' ) ){
						update_option('uap_dashboard_allowed_roles', $_POST['uap_dashboard_allowed_roles']);
					}
					$meta_value = get_option('uap_dashboard_allowed_roles');
					$meta_values = (empty($meta_value)) ? array() : explode(',', $meta_value);
					require_once $this->admin_view_path . 'access.php';
					break;
				case 'admin_workflow':
					if (!empty($_POST['save']) && !empty($_POST['uap_admin_forms_nonce']) && wp_verify_nonce( $_POST['uap_admin_forms_nonce'], 'uap_admin_forms_nonce' ) ){
						$data['metas'] = $indeed_db->return_settings_from_wp_option('general-admin_workflow');
						$indeed_db->save_settings_wp_option('general-admin_workflow', $_POST);
						if (!empty($data['metas']['uap_update_ranks_interval']) && strcmp($data['metas']['uap_update_ranks_interval'], $_POST['uap_update_ranks_interval'])<>0){
							/// cron settings has been change
							require_once UAP_PATH . 'classes/UapCronJobs.class.php';
							$cron_object = new UapCronJobs();
							$cron_object->update_cron_time($_POST['uap_update_ranks_interval']);
						}
					}
					$data['metas'] = $indeed_db->return_settings_from_wp_option('general-admin_workflow');
					require_once $this->admin_view_path . 'settings-admin_workflow.php';
					break;
				case 'public_workflow':
					if (!empty($_POST['save']) && !empty($_POST['uap_admin_forms_nonce']) && wp_verify_nonce( $_POST['uap_admin_forms_nonce'], 'uap_admin_forms_nonce' ) ){
						$indeed_db->save_settings_wp_option('general-public_workflow', $_POST);
					}
					$data['metas'] = $indeed_db->return_settings_from_wp_option('general-public_workflow');
					$data['payment_types'] = $indeed_db->get_payment_types_available();
					require_once $this->admin_view_path . 'settings-public_workflow.php';
					break;
			}
		}

		private function print_register(){
			/*
			 * @param none
			 * @return string
			 */

			/// PRINT SUBMENU
			$data['submenu'] = array(
					admin_url('admin.php?page=ultimate_affiliates_pro&tab=register&subtab=register_showcase') => esc_html__('Register Showcase', 'uap'),
					admin_url('admin.php?page=ultimate_affiliates_pro&tab=register&subtab=custom_messages') => esc_html__('Custom Messages', 'uap'),
					admin_url('admin.php?page=ultimate_affiliates_pro&tab=register&subtab=custom_fields') => esc_html__('Custom Fields', 'uap'),
					admin_url('admin.php?page=ultimate_affiliates_pro&tab=opt_in') => esc_html__('Opt-In Settings', 'uap'),
			);
			require_once $this->admin_view_path . 'submenu.php';
			///

			$this->print_top_messages();
			global $indeed_db;

			$subtab = (empty($_GET['subtab'])) ? 'register_showcase' : $_GET['subtab'];
			switch ($subtab){
				case 'register_showcase':
					/// REGISTER SETTINGS
					if (isset($_POST['save']) && !empty($_POST['uap_admin_forms_nonce']) && wp_verify_nonce( $_POST['uap_admin_forms_nonce'], 'uap_admin_forms_nonce' ) ){
						$indeed_db->save_settings_wp_option('register', $_POST);
					}
					$data['metas'] = $indeed_db->return_settings_from_wp_option('register', FALSE, FALSE);
					require_once $this->admin_view_path . 'register-settings.php';
					break;
				case 'custom_fields':
					/// SAVE/UPDATE/DELETE
					if (isset($_POST['delete_custom_field']) && $_POST['delete_custom_field']!='' && !empty($_POST['uap_admin_forms_nonce']) && wp_verify_nonce( $_POST['uap_admin_forms_nonce'], 'uap_admin_forms_nonce' ) ){
						$indeed_db->register_delete_custom_field($_POST['delete_custom_field']);
					} else if ( !empty($_POST['save_field']) && !empty($_POST['uap_admin_forms_nonce']) && wp_verify_nonce( $_POST['uap_admin_forms_nonce'], 'uap_admin_forms_nonce' ) ){
						$indeed_db->register_save_custom_field($_POST);
					} else if ( !empty($_POST['save']) && !empty($_POST['uap_admin_forms_nonce']) && wp_verify_nonce( $_POST['uap_admin_forms_nonce'], 'uap_admin_forms_nonce' ) ){
						//update order of fields...
						$indeed_db->register_update_order($_POST);
					}
					///
					/// MANAGE CUSTOM FIELDS
					$data['register_fields'] = $indeed_db->register_get_custom_fields();
					ksort($data['register_fields']);
					$data['url_edit_custom_fields'] = admin_url('admin.php?page=ultimate_affiliates_pro&tab=register&subtab=custom_fields-add_edit');
					require_once $this->admin_view_path . 'register-custom_fields.php';
					break;
				case 'custom_fields-add_edit':
					/// ADD/UPDATE CUSTOM FIELD
					$data['form_submit'] = admin_url('admin.php?page=ultimate_affiliates_pro&tab=register&subtab=custom_fields');
					$data['field_types'] = array(
													'text' => esc_html__('Text', 'uap'),
													'textarea' => esc_html__('Textarea', 'uap'),
													'date' => esc_html__('Date Picker', 'uap'),
													'number' => esc_html__('Number', 'uap'),
													'select' => esc_html__('Select', 'uap'),
													'multi_select' => esc_html__('Multiselect Box', 'uap'),
													'checkbox' => esc_html__('Checkbox', 'uap'),
													'radio' => esc_html__('Radio', 'uap'),
													'file' => esc_html__('File Upload', 'uap'),
													'plain_text' => esc_html__('Plain Text', 'uap'),
													'conditional_text' => esc_html__('Verification Code', 'uap'),
					);
					$data['id'] = (!isset($_GET['id'])) ? '' : $_GET['id'];
					$data['metas'] = $indeed_db->register_get_field($data['id']);
					$data['disabled_items'] = array(
																						'confirm_email',
																						'tos',
																						'name',
																						'recaptcha',
																						'uap_avatar',
																						'uap_country',
																						'uap_optin_accept',
					);
					$data['disabled'] = (in_array($data['metas']['name'], $data['disabled_items'])) ? 'disabled' : '';
					$data['register_fields'] = array('-1'=>'...') + $indeed_db->register_get_custom_fields(TRUE, array('social_media', 'upload_image', 'plain_text', 'file', 'capcha'));
					if (empty($data['metas']['conditional_logic_corresp_field'])){
						$data['metas']['conditional_logic_corresp_field'] = -1;
					}
					require_once $this->admin_view_path . 'register-custom_fields_add_edit.php';
					break;
				case 'custom_messages':
					if ( isset($_POST['save']) && !empty($_POST['uap_admin_forms_nonce']) && wp_verify_nonce( $_POST['uap_admin_forms_nonce'], 'uap_admin_forms_nonce' ) ){
						$indeed_db->save_settings_wp_option('register-msg', $_POST);
					}
					$data['metas'] = $indeed_db->return_settings_from_wp_option('register-msg', FALSE, FALSE);
					require_once $this->admin_view_path . 'register-custom_messages.php';
					break;
			}
		}

		private function print_login(){
			/*
			 * @param none
			 * @return string
			 */
			/// PRINT SUBMENU
			$data['submenu'] = array(
					admin_url('admin.php?page=ultimate_affiliates_pro&tab=login') => esc_html__('Login Showcase', 'uap'),
					admin_url('admin.php?page=ultimate_affiliates_pro&tab=login&subtab=custom_messages') => esc_html__('Custom Messages', 'uap')
			);
			require_once $this->admin_view_path . 'submenu.php';
			///

			$this->print_top_messages();
			global $indeed_db;
			$data['subtab'] = (empty($_GET['subtab'])) ? '' : $_GET['subtab'];

			if ($data['subtab']=='custom_messages'){
				if (!empty($_POST['save']) && !empty($_POST['uap_admin_forms_nonce']) && wp_verify_nonce( $_POST['uap_admin_forms_nonce'], 'uap_admin_forms_nonce' ) ){
					$indeed_db->save_settings_wp_option('login-messages', $_POST);
				}
				$data['metas'] = $indeed_db->return_settings_from_wp_option('login-messages');
				require_once $this->admin_view_path . 'login-custom_messages.php';
			} else {
				if (!empty($_POST['save']) && !empty($_POST['uap_admin_forms_nonce']) && wp_verify_nonce( $_POST['uap_admin_forms_nonce'], 'uap_admin_forms_nonce' ) ){
					$indeed_db->save_settings_wp_option('login', $_POST);
				}
				$data['login_templates'] = array(

													9 => '(#9) ' . esc_html__('Radius Gradient Theme', 'uap'),
													8 => '(#8) ' . esc_html__('Border Pink Theme', 'uap'),
													10 => '(#10) ' . esc_html__('Simple BootStrap Theme', 'uap'),
													11 => '(#11) ' . esc_html__('Flat new Style', 'uap'),
													13 => '(#13) ' . esc_html__('Ultimate Member', 'uap'),
													12 => '(#12) ' . esc_html__('MegaBox', 'uap'),
													1 => '(#1) ' . esc_html__('Standard Theme', 'uap'),
													2 =>'(#2) '. esc_html__('Basic Theme', 'uap'),
													3 => '(#3) ' . esc_html__('BlueBox Theme', 'uap'),
													4 =>  '(#4) ' . esc_html__('Simple Green Theme', 'uap'),
													5 => '(#5) ' . esc_html__('Labels Theme', 'uap'),
													6 => '(#6) ' . esc_html__('Premium Theme', 'uap'),
													7 => '(#7) ' . esc_html__('Double Long Theme', 'uap')
				);
				$data['metas'] = $indeed_db->return_settings_from_wp_option('login');
				require_once $this->admin_view_path . 'login.php';
			}
		}

		private function print_account_page(){
			/*
			 * @param none
		 	 * @return string
			 */
			$this->print_top_messages();
			global $indeed_db;
			if (!empty($_POST['save']) && !empty($_POST['uap_admin_forms_nonce']) && wp_verify_nonce( $_POST['uap_admin_forms_nonce'], 'uap_admin_forms_nonce' ) ){
				$indeed_db->save_settings_wp_option('account_page', $_POST);
			}
			$data['top_themes'] = array(
									'uap-ap-top-theme-1' => esc_html__('Theme 1', 'uap'),
									'uap-ap-top-theme-2' => esc_html__('Theme 2', 'uap'),
									'uap-ap-top-theme-3' => esc_html__('Theme 3', 'uap'),
			);
			$data['themes'] = array(
									'uap-ap-theme-1' => esc_html__('Theme 1', 'uap'),
									'uap-ap-theme-2' => esc_html__('Theme 2', 'uap'),
									'uap-ap-theme-3' => esc_html__('Theme 3', 'uap'),
									'uap-ap-theme-4' => esc_html__('Theme 4', 'uap'),
			);
			$data['metas'] = $indeed_db->return_settings_from_wp_option('account_page');
			$data['available_tabs'] = $indeed_db->account_page_get_menu();
			require_once $this->admin_view_path . 'account-page.php';
		}

		private function print_opt_in(){
			/*
			 * @param none
			 * @return string
			 */
			$this->print_top_messages();
			global $indeed_db;
			if (!empty($_POST['save'])){
				$indeed_db->save_settings_wp_option('opt_in', $_POST);
			}
			$email_list = get_option('uap_email_list');
			$data['email_list'] = (empty($email_list)) ? '' : $email_list;
			$data['metas'] = $indeed_db->return_settings_from_wp_option('opt_in');
			require_once UAP_PATH . 'classes/OptInMailServices.class.php';
			$obj = new OptInMailServices();
			require_once $this->admin_view_path . 'opt-in.php';
		}

		private function print_visits(){
			/*
			 * @param none
			 * @return string
			 */
			$this->print_top_messages();
			global $indeed_db;
			if (!empty($_POST['delete_visits']) && !empty($_POST['uap_admin_forms_nonce']) && wp_verify_nonce( $_POST['uap_admin_forms_nonce'], 'uap_admin_forms_nonce' ) ){
				$indeed_db->delete_visits($_POST['delete_visits']);
			}

			$url = admin_url('admin.php?page=ultimate_affiliates_pro&tab=visits');
			$where = array();
			if (!empty($_REQUEST['udf']) && !empty($_REQUEST['udu'])){
				$where[] = " v.visit_date>'" . $_REQUEST['udf'] . "' ";
				$where[] = " v.visit_date<'" . $_REQUEST['udu'] . "' ";
				$url .= '&udf=' . $_REQUEST['udf'] . '&udu=' . $_REQUEST['udu'];
			}
			if (!empty($_REQUEST['aff_u'])){
				$where[] = " ((u.user_login LIKE '%" . $_REQUEST['aff_u'] . "%') OR  (u.user_email LIKE '%" . $_REQUEST['aff_u'] . "%') )";
				$url .= '&aff_u=' . $_REQUEST['aff_u'];
			}
			if (isset($_REQUEST['u_sts'])){
				switch($_REQUEST['u_sts']){
					case 0:
							$where[] = " v.referral_id =0";

							break;
					case 1:
							$where[] = " v.referral_id!=0 ";
							break;
				}
				$url .= '&u_sts=' . $_REQUEST['u_sts'];
			}

			if (!empty($_REQUEST['affiliate_id'])){
				$where[] = "v.affiliate_id=" . $_REQUEST['affiliate_id'];
				$url .= '&affiliate_id=' . $_REQUEST['affiliate_id'];
				$wpuid = $indeed_db->get_uid_by_affiliate_id($_REQUEST['affiliate_id']);
				$username = $indeed_db->get_username_by_wpuid($wpuid);
				$full_name = $indeed_db->get_full_name_of_user($_REQUEST['affiliate_id']);
				$data['subtitle'] = esc_html__('View Clicks for', 'uap') . " $full_name ($username)";
			}

			$data['base_list_url'] = $url;
			$limit = (empty($_GET['uap_limit'])) ? 25 : $_GET['uap_limit'];
			$url .= '&uap_limit=' . $limit;
			$current_page = (empty($_GET['uap_list_item'])) ? 1 : $_GET['uap_list_item'];
			$total_items = (int)$indeed_db->get_visits(-1, -1, TRUE, '', '', $where );
			if ($current_page>1){
				$offset = ( $current_page - 1 ) * $limit;
			} else {
				$offset = 0;
			}
			if ($offset + $limit>$total_items){
				$limit = $total_items - $offset;
			}
			$data['listing_items'] = $indeed_db->get_visits($limit, $offset, FALSE, 'v.visit_date', 'DESC', $where);
			$data['filter'] = uap_return_date_filter($url,
																								array(
																									0 => esc_html__('Just Visit', 'uap'),
																									1 => esc_html__('Converted', 'uap'),
																								),
																								 array(),
																								 TRUE);

			require_once UAP_PATH . 'classes/UapPagination.class.php';
			$limit = (empty($_GET['uap_limit'])) ? 25 : $_GET['uap_limit'];

			$pagination = new UapPagination(array(
														'base_url' => $url,
														'param_name' => 'uap_list_item',
														'total_items' => $total_items,
														'items_per_page' => $limit,
														'current_page' => $current_page,
			));
			$data['pagination'] = $pagination->output();

			require_once $this->admin_view_path . 'visits.php';
		}

		private function print_magic_features(){
			/*
			 * @param none
			 * @return string
			 */
			global $indeed_db;
			$data['feature_types'] = $indeed_db->get_magic_feat_item_list();
			if (!UAP_LICENSE_SET){
				/// remove some features
				$data['feature_types']['paypal']['enabled'] = FALSE;
				$data['feature_types']['paypal']['link'] = '#';
				$data['feature_types']['paypal']['extra_class'] = 'uap-magic-feat-not-available';
				$data['feature_types']['lifetime_commissions']['enabled'] = FALSE;
				$data['feature_types']['lifetime_commissions']['link'] = '#';
				$data['feature_types']['lifetime_commissions']['extra_class'] = 'uap-magic-feat-not-available';
				$data['feature_types']['mlm']['enabled'] = FALSE;
				$data['feature_types']['mlm']['link'] = '#';
				$data['feature_types']['mlm']['extra_class'] = 'uap-magic-feat-not-available';
				$data['feature_types']['bonus_on_rank']['enabled'] = FALSE;
				$data['feature_types']['bonus_on_rank']['link'] = '#';
				$data['feature_types']['bonus_on_rank']['extra_class'] = 'uap-magic-feat-not-available';
				$data['feature_types']['wallet']['enabled'] = FALSE;
				$data['feature_types']['wallet']['link'] = '#';
				$data['feature_types']['wallet']['extra_class'] = 'uap-magic-feat-not-available';
				$data['feature_types']['referral_notifications']['enabled'] = FALSE;
				$data['feature_types']['referral_notifications']['link'] = '#';
				$data['feature_types']['referral_notifications']['extra_class'] = 'uap-magic-feat-not-available';
				$data['feature_types']['admin_referral_notifications']['enabled'] = FALSE;
				$data['feature_types']['admin_referral_notifications']['link'] = '#';
				$data['feature_types']['admin_referral_notifications']['extra_class'] = 'uap-magic-feat-not-available';
				$data['feature_types']['periodically_reports']['enabled'] = FALSE;
				$data['feature_types']['periodically_reports']['link'] = '#';
				$data['feature_types']['periodically_reports']['extra_class'] = 'uap-magic-feat-not-available';
				$data['feature_types']['stripe_v2']['enabled'] = FALSE;
				$data['feature_types']['stripe_v2']['link'] = '#';
				$data['feature_types']['stripe_v2']['extra_class'] = 'uap-magic-feat-not-available';
				$data['feature_types']['pay_per_click']['enabled'] = FALSE;
				$data['feature_types']['pay_per_click']['link'] = '#';
				$data['feature_types']['pay_per_click']['extra_class'] = 'uap-magic-feat-not-available';
				$data['feature_types']['cpm_commission']['enabled'] = FALSE;
				$data['feature_types']['cpm_commission']['link'] = '#';
				$data['feature_types']['cpm_commission']['extra_class'] = 'uap-magic-feat-not-available';
			}
			/// PRINT SUBMENU
			foreach ($data['feature_types'] as $k=>$v){
				if($k != 'new_extension'){
					$data['submenu'][$v['link']] = $v['label'];
				}
			}
			require_once $this->admin_view_path . 'submenu.php';
			///

			if (!empty($_GET['subtab'])){
				switch ($_GET['subtab']){
					case 'sign_up_referrals':
						$this->print_sign_up_referrals();
						break;
					case 'lifetime_commissions':
						$this->print_lifetime_commissions();
						break;
					case 'reccuring_referrals':
						$this->print_reccuring_referrals();
						break;
					case 'social_share':
						$this->print_social_share();
						break;
					case 'paypal':
						$this->print_paypal();
						break;
					case 'allow_one_referrence':
						$this->print_allow_own_referrence();
						break;
					case 'mlm':
						$this->print_mlm();
						break;
					case 'edit_affiliate_referral_relation':
						$this->edit_affiliate_referral_relation();
						break;
					case 'add_new_affiliate_referral_relation':
						$this->add_new_affiliate_referral_relation();
						break;
					case 'rewrite_referrals':
						$this->print_rewrite_referrals();
						break;
					case 'bonus_on_rank':
						$this->print_bonus_on_rank();
						break;
					case 'opt_in':
						$this->print_opt_in();
						break;
					case 'stripe':
						$this->print_stripe();
						break;
					case 'coupons':
						$this->print_coupons();
						break;
					case 'friendly_links':
						$this->print_friendly_links();
						break;
					case 'custom_affiliate_slug':
						$this->print_custom_affiliate_slug();
						break;
					case 'mlm_view_affiliate_children':
						$this->print_mlm_view_affiliate_children();
						break;
					case 'wallet':
						$this->print_wallet();
						break;
					case 'checkout_select_referral':
						$this->print_checkout_select_referral();
						break;
					case 'woo_account_page':
						$this->print_woo_account_page();
						break;
					case 'bp_account_page':
						$this->print_bp_account_page();
						break;
					case 'referral_notifications':
						$this->print_referral_notifications();
						break;
					case 'admin_referral_notifications':
						$this->print_admin_referral_notifications();
						break;
					case 'periodically_reports':
						$this->print_periodically_reports();
						break;
					case 'qr_code':
						$this->print_qr_code();
						break;
					case 'email_verification':
						$this->print_email_verification();
						break;
					case 'custom_currencies':
						$this->print_custom_currencies();
						break;
					case 'source_details':
						$this->print_source_details();
						break;
					case 'wp_social_login':
						$this->print_wp_social_login();
						break;
					case 'stripe_v2':
						$this->print_stripe_v2();
						break;
					case 'pushover':
						$this->print_pushover();
						break;
					case 'max_amount':
						$this->print_max_amount();
						break;
					case 'simple_links':
						$this->print_simple_links();
						break;
					case 'account_page_menu':
						$this->print_account_page_menu();
						break;
					case 'migrate_affiliates_pro':
						$this->migrate_affiliates_pro();
						break;
					case 'migrate_wp_affiliates':
						$this->migrate_wp_affiliates();
						break;
					case 'migrate_affiliate_wp':
						$this->migrate_affiliate_wp();
						break;
					case 'ranks_pro':
						$this->ranks_pro();
						break;
					case 'landing_pages':
						$this->landing_pages();
						break;
					case 'pay_per_click':
						$this->pay_per_click();
						break;
					case 'cpm_commission':
						$this->cpm_commission();
						break;
					case 'pushover_referral_notifications':
						$this->pushover_referral_notifications();
						break;
					case 'rest_api':
						$this->rest_api();
						break;
					case 'pay_to_become_affiliate':
						$this->pay_to_become_affiliate();
						break;
					case 'info_affiliate_bar':
						$this->info_affiliate_bar();
						break;
					case 'product_links':
						$this->product_links();
						break;
					case 'stripe_v3':
						$this->stripe_v3();
						break;
				}
			} else {
				/// LIST THE FEATURES
				$this->print_top_messages();
				require_once $this->admin_view_path . 'magic-features.php';
			}
		}

		private function print_wp_social_login(){
			/*
			 * @param none
			 * @return string
			 */
			$this->print_top_messages();
			global $indeed_db;
			if (!empty($_POST['save'])){
				$indeed_db->save_settings_wp_option('wp_social_login', $_POST);
			}
			$data['metas'] = $indeed_db->return_settings_from_wp_option('wp_social_login');
			$data['pages'] = $indeed_db->uap_get_all_pages();
			$data['ranks'] = $indeed_db->get_ranks();
			$data['ranks'] = uap_reorder_ranks($data['ranks']);//reorder
			require_once $this->admin_view_path . 'wp-social-login.php';
		}

		private function print_sign_up_referrals(){
			/*
			 * @param none
			 * @return string
			 */
			$this->print_top_messages();
			global $indeed_db;
			if (!empty($_POST['save'])){
				$indeed_db->save_settings_wp_option('sign_up_referrals', $_POST);
				if (isset($_POST['signup_ranks_value'])){
					foreach ($_POST['signup_ranks_value'] as $id=>$value){
						if ($value==''){
							$value = -1;
						}
						$indeed_db->update_rank_column('sign_up_amount_value', $id, $value);
					}
				}
			}
			$data['amount_types'] = $this->amount_type_list;
			$data['metas'] = $indeed_db->return_settings_from_wp_option('sign_up_referrals');
			$data['rank_list'] = $indeed_db->get_rank_list();
			$data['rank_value_array'] = $indeed_db->get_column_value_for_each_rank('sign_up_amount_value');
			require_once $this->admin_view_path . 'sign-up-referrals.php';
		}

		private function print_lifetime_commissions(){
			/*
			 * @param none
			 * @return string
			 */
			$this->print_top_messages();
			global $indeed_db;
			if (!empty($_GET['delete'])){
				$indeed_db->affiliate_referrals_delete_relation($_GET['delete']);
			} else if (!empty($_POST['save'])){
				$indeed_db->save_settings_wp_option('lifetime_commissions', $_POST);
				if (isset($_POST['lifetime_ranks_amount_type'])){
					foreach ($_POST['lifetime_ranks_amount_type'] as $id=>$value){
						$indeed_db->update_rank_column('lifetime_amount_type', $id, $value);
					}
				}
				if (isset($_POST['lifetime_ranks_value'])){
					foreach ($_POST['lifetime_ranks_value'] as $id=>$value){
						if ($value==''){
							$value = -1;
						}
						$indeed_db->update_rank_column('lifetime_amount_value', $id, $value);
					}
				}
			} else if (!empty($_POST['search'])){
				$data['affiliate_referrals_table_data'] = $indeed_db->get_affiliate_user_relation($_POST['affiliate_username'], $_POST['username']);
			} else if ( !empty($_POST['save-new-relation'] ) && !empty( $_POST['affiliate'] ) && !empty( $_POST['referral_uid'] ) ){
					$indeed_db->insert_affiliate_referral_user_new_relation( $_POST['affiliate'], $_POST['referral_uid'] );
			}
			$data['metas'] = $indeed_db->return_settings_from_wp_option('lifetime_commissions');
			$data['rank_list'] = $indeed_db->get_rank_list();
			$data['default_rank_amount_type_array'] = $indeed_db->get_column_value_for_each_rank('amount_type');
			$data['default_rank_amount_value_array'] = $indeed_db->get_column_value_for_each_rank('amount_value');
			$data['rank_amount_type_array'] = $indeed_db->get_column_value_for_each_rank('lifetime_amount_type');
			$data['rank_amount_value_array'] = $indeed_db->get_column_value_for_each_rank('lifetime_amount_value');
			$data['amount_types'] = $this->amount_type_list;
			$data['current_url'] = admin_url('admin.php?page=ultimate_affiliates_pro&tab=magic_features&subtab=lifetime_commissions');
			$data['edit_relation'] = admin_url('admin.php?page=ultimate_affiliates_pro&tab=magic_features&subtab=edit_affiliate_referral_relation');
			require_once $this->admin_view_path . 'lifetime-commissions.php';
		}

		private function edit_affiliate_referral_relation(){
			/*
			 * @param none
			 * @return none
			 */
			$this->print_top_messages();
			global $indeed_db;
			if (!empty($_POST['save'])){
				$indeed_db->update_affiliate_referral_user_relation($_POST['id'], $_POST['affiliate']);
			}
			$data['edit_data'] = array();
			if(isset($_GET['id'])){
				$data['edit_data'] = $indeed_db->get_affiliate_user_relation_by_id($_GET['id']);
			}

			$data['affiliates'] = $indeed_db->get_affiliates_username_id_pair();
			require_once $this->admin_view_path . 'edit-affiliate-referral-relation.php';
		}

		/**
		 * @param none
		 * @return none
		 */
		private function add_new_affiliate_referral_relation()
		{
				global $indeed_db;
				$this->print_top_messages();
				$data = [
									'affiliates'  => $indeed_db->get_affiliates_username_id_pair(),
									'users'			  => $indeed_db->getUsers(),
				];
				require_once $this->admin_view_path . 'add-new-affiliate-referral-relation.php';
		}

		private function print_reccuring_referrals(){
			/*
			 * @param none
			 * @return string
			 */
			$this->print_top_messages();
			global $indeed_db;
			if (!empty($_POST['save'])){
				$indeed_db->save_settings_wp_option('reccuring_referrals', $_POST);
				if (isset($_POST['reccuring_ranks_amount_type'])){
					foreach ($_POST['reccuring_ranks_amount_type'] as $id=>$value){
						$indeed_db->update_rank_column('reccuring_amount_type', $id, $value);
					}
				}
				if (isset($_POST['reccuring_ranks_value'])){
					foreach ($_POST['reccuring_ranks_value'] as $id=>$value){
						if ($value==''){
							$value = -1;
						}
						$indeed_db->update_rank_column('reccuring_amount_value', $id, $value);
					}
				}
			}

			$data['metas'] = $indeed_db->return_settings_from_wp_option('reccuring_referrals');
			$data['rank_list'] = $indeed_db->get_rank_list();
			$data['default_rank_amount_type_array'] = $indeed_db->get_column_value_for_each_rank('amount_type');
			$data['default_rank_amount_value_array'] = $indeed_db->get_column_value_for_each_rank('amount_value');
			$data['rank_amount_type_array'] = $indeed_db->get_column_value_for_each_rank('reccuring_amount_type');
			$data['rank_amount_value_array'] = $indeed_db->get_column_value_for_each_rank('reccuring_amount_value');
			$data['amount_types'] = $this->amount_type_list;
			require_once $this->admin_view_path . 'reccuring-referrals.php';
		}

		private function print_social_share(){
			/*
			 * @param none
			 * @return string
			 */
			$this->print_top_messages();
			global $indeed_db;
			if (!empty($_POST['save'])){
				$indeed_db->save_settings_wp_option('social_share', $_POST);
			}
			$data['metas'] = $indeed_db->return_settings_from_wp_option('social_share');
			$data['social_share_page'] = admin_url('admin.php?page=ism_manage&tab=shortcode');
			require_once $this->admin_view_path . 'social-share.php';
		}

		private function print_paypal(){
			/*
			 * @param none
			 * @return string
			 */
			$this->print_top_messages();
			global $indeed_db;
			$phpversion = phpversion();
			if (!empty($_POST['save'])){
				$indeed_db->save_settings_wp_option('paypal', $_POST);
			}
			$data['metas'] = $indeed_db->return_settings_from_wp_option('paypal');
			require_once $this->admin_view_path . 'paypal.php';
		}

		private function print_stripe(){
			/*
			 * @param none
			 * @return string
			 */
			$this->print_top_messages();
			global $indeed_db;
			if (!empty($_POST['save'])){
				$indeed_db->save_settings_wp_option('stripe', $_POST);
			}
			$data['metas'] = $indeed_db->return_settings_from_wp_option('stripe');
			require_once $this->admin_view_path . 'stripe.php';
		}

		private function stripe_v3()
		{
				$this->print_top_messages();
				global $indeed_db;
				if (!empty($_POST['save'])){
					$indeed_db->save_settings_wp_option('stripe_v3', $_POST);
				}
				$data['metas'] = $indeed_db->return_settings_from_wp_option('stripe_v3');
				require_once $this->admin_view_path . 'stripe-v3.php';
		}

		private function print_stripe_v2(){
			/*
			 * @param none
			 * @return string
			 */
			$this->print_top_messages();
			global $indeed_db;
			if (!empty($_POST['save'])){
				$indeed_db->save_settings_wp_option('stripe_v2', $_POST);
			}
			$data['metas'] = $indeed_db->return_settings_from_wp_option('stripe_v2');
			require_once $this->admin_view_path . 'stripe-v2.php';
		}

		/*
		 * @param none
		 * @return string
		 */
		private function print_pushover(){
			$this->print_top_messages();
			global $indeed_db;
			if (!empty($_POST['uap_save'])){
				$indeed_db->save_settings_wp_option('pushover', $_POST);

				if ( empty( $_POST['uap_pushover_enabled'] ) ){
						// deactivate tab
						$indeed_db->deactivateApTab( 'pushover' );
				} else {
						// activate tab
						$indeed_db->activateApTab( 'pushover' );
				}
			}
			$data['metas'] = $indeed_db->return_settings_from_wp_option('pushover');
			require_once $this->admin_view_path . 'pushover.php';
		}

		private function print_coupons(){
			/*
			 * @param none
			 * @return string
			 */
			 global $indeed_db;
			 if (!empty($_POST['delete_coupons'])){
			 	$indeed_db->delete_coupon_affiliate_pair($_POST['delete_coupons']);
			 }
			 $data['amount_types'] = $this->amount_type_list;
			 $data['url-add_edit'] = admin_url('admin.php?page=ultimate_affiliates_pro&tab=magic_features&subtab=coupons');
			 $data['url-manage'] = admin_url('admin.php?page=ultimate_affiliates_pro&tab=magic_features&subtab=coupons');
			 if (isset($_GET['add_edit'])){
			 	$data['metas'] = $indeed_db->get_coupon_data($_GET['add_edit']);
				$data['affiliate'] = $indeed_db->get_wp_username_by_affiliate_id($data['metas']['affiliate_id']);
			 	require_once $this->admin_view_path . 'coupons-add_edit.php';
			 } else {
				 // coupon entity
				if (!empty($_POST['save'])){
						$saved = $indeed_db->save_coupon_affiliate_pair($_POST);
						if ($saved<1){
							$data['errors'] = esc_html__('Be sure that you have filled all the reguired fields: Code, Affiliate.', 'uap');
						}
				} else if (!empty($_POST['delete_coupon'])){
						$indeed_db->delete_coupon_affiliate_pair($_POST['delete_coupon']);
				}
				/// coupon settings
				if (!empty($_POST['uap_save'])){
						$indeed_db->save_settings_wp_option('coupons', $_POST);

						if ( empty( $_POST['uap_coupons_enable'] ) ){
								// deactivate tab
								$indeed_db->deactivateApTab( 'coupons' );
						} else {
								// activate tab
								$indeed_db->activateApTab( 'coupons' );
						}
				}
				$data['metas'] = $indeed_db->return_settings_from_wp_option('coupons');
				$data['listing_items'] = $indeed_db->get_coupons_affiliates_pairs();
			 	require_once $this->admin_view_path . 'coupons-list.php';
			 }
		}

		private function print_allow_own_referrence(){
			/*
			 * @param none
			 * @return string
			 */
			$this->print_top_messages();
			global $indeed_db;
			if (!empty($_POST['save'])){
				$indeed_db->save_settings_wp_option('allow_own_referrence', $_POST);
			}
			$data['metas'] = $indeed_db->return_settings_from_wp_option('allow_own_referrence');
			require_once $this->admin_view_path . 'allow-own-referrence.php';
		}

		private function print_mlm(){
			/*
			 * @param none
			 * @return string
			 */
			$this->print_top_messages();
			global $indeed_db;
			if (!empty($_POST['save'])){
				$indeed_db->save_settings_wp_option('mlm', $_POST);

				if ( empty( $_POST['uap_mlm_enable'] ) ){
						// deactivate tab
						$indeed_db->deactivateApTab( 'mlm' );
				} else {
						// activate tab
						$indeed_db->activateApTab( 'mlm' );
				}
			}
			$data['metas'] = $indeed_db->return_settings_from_wp_option('mlm');
			$data['amount_types'] = $this->amount_type_list;
			$data['matrix_types'] = array(
													'unilevel' => esc_html__('The Unilevel Plan', 'uap'),
													'force' => esc_html__('The Force Matrix Plan', 'uap'),
													'binary' => esc_html__('Binary Plan', 'uap'),
			);
			$data['search_submit_url'] = admin_url('admin.php?page=ultimate_affiliates_pro&tab=magic_features&subtab=mlm_view_affiliate_children');
			require_once $this->admin_view_path . 'mlm.php';
		}

		private function print_mlm_view_affiliate_children(){
			/*
			 * @param none
			 * @return string
			 */
			$this->print_top_messages();
			global $indeed_db;
			$affiliate_name = (empty($_REQUEST['affiliate_name'])) ? 0 : $_REQUEST['affiliate_name'];

			$affiliate_id = $indeed_db->get_affiliate_id_by_username($affiliate_name);


	  	$affiliate_full_name = $indeed_db->get_full_name_of_user($affiliate_id);
			if($affiliate_full_name == ''){
				 $affiliate_full_name = 	$affiliate_name;
			}
			$affiliateuid = $indeed_db->get_uid_by_affiliate_id($affiliate_id);
			$affiliate_avatar = uap_get_avatar_for_uid($affiliateuid );

			require_once UAP_PATH . 'classes/MLMGetChildren.class.php';
			$children_object = new MLMGetChildren($affiliate_id);
			$data['items'] = $children_object->get_results();
			$data['parent'] = $indeed_db->mlm_get_parent($affiliate_id);
			if (empty($data['parent'])){
					$data['parent'] = '';
					$data['parent_id'] ='';
			} else {
					$parentUid = $indeed_db->get_uid_by_affiliate_id($data['parent']);
					$data['parent_id'] = $parentUid;
					$data['parent_full_name'] = $indeed_db->get_full_name_of_user($data['parent']);
					$data['parent'] = $indeed_db->get_username_by_wpuid($parentUid);
					if($data['parent_full_name'] == ''){
						 $data['parent_full_name'] = $data['parent'];
					}
					$data['parent_avatar'] = uap_get_avatar_for_uid($parentUid);
			}
			require_once $this->admin_view_path . 'mlm-view_affiliate_children.php';
		}

		private function print_rewrite_referrals(){
			/*
			 * @param none
			 * @return string
			 */
			$this->print_top_messages();
			global $indeed_db;
			if (!empty($_POST['save'])){
				$indeed_db->save_settings_wp_option('rewrite_referrals', $_POST);
			}
			$data['metas'] = $indeed_db->return_settings_from_wp_option('rewrite_referrals');
			require_once $this->admin_view_path . 'rewrite-referrals.php';
		}

		private function print_bonus_on_rank(){
			/*
			 * @param none
			 * @return string
			 */
			$this->print_top_messages();
			global $indeed_db;
			if (!empty($_POST['save'])){
				$indeed_db->save_settings_wp_option('bonus_on_rank', $_POST);
				if (isset($_POST['bonus_ranks_value'])){
					foreach ($_POST['bonus_ranks_value'] as $id=>$value){
						$indeed_db->update_rank_column_force_empty('bonus', $id, $value);
					}
				}
			}
			$data['metas'] = $indeed_db->return_settings_from_wp_option('bonus_on_rank');
			$data['rank_list'] = $indeed_db->get_rank_list();
			$data['rank_value_array'] = $indeed_db->get_column_value_for_each_rank('bonus');
			$data['amount_types'] = $this->amount_type_list;

			require_once $this->admin_view_path . 'bonus-on-rank.php';
		}

		private function print_friendly_links(){
			/*
			 * @oaram none
			 * @return string
			 */
			$this->print_top_messages();
			global $indeed_db;
			if (!empty($_POST['save'])){
				if ( empty( $_POST['uap_friendly_links'] ) ){
						// deactivate tab
						$indeed_db->deactivateApTab( 'friendly_links' );
				} else {
						// activate tab
						$indeed_db->activateApTab( 'friendly_links' );
				}

				$indeed_db->save_settings_wp_option('friendly_links', $_POST);
			}
			$data['metas'] = $indeed_db->return_settings_from_wp_option('friendly_links');
			require_once $this->admin_view_path . 'friendly-links.php';
		}

		private function print_wallet(){
			/*
			 * @param none
			 * @return string
			 */
			$this->print_top_messages();
			global $indeed_db;
			if (!empty($_POST['save'])){
				$indeed_db->save_settings_wp_option('wallet', $_POST);

				if ( empty( $_POST['uap_wallet_enable'] ) ){
						// deactivate tab
						$indeed_db->deactivateApTab( 'wallet' );
				} else {
						// activate tab
						$indeed_db->activateApTab( 'wallet' );
				}

			}
			$data['metas'] = $indeed_db->return_settings_from_wp_option('wallet');
			require_once $this->admin_view_path . 'wallet.php';
		}

		private function print_max_amount(){
			/*
			 * @param none
			 * @return string
			 */
			$this->print_top_messages();
			global $indeed_db;
			if (!empty($_POST['save'])){
				$indeed_db->save_settings_wp_option('max_amount', $_POST);
			}
			$data['ranks'] = $indeed_db->get_ranks();
			$data['metas'] = $indeed_db->return_settings_from_wp_option('max_amount');
			$data['amount_types'] = $this->amount_type_list;
			require_once $this->admin_view_path . 'max-amount.php';
		}

		private function print_simple_links(){
			/*
			 * @param none
			 * @return string
			 */
			$this->print_top_messages();
			global $indeed_db;
			if (!empty($_POST['save'])){
				$indeed_db->save_settings_wp_option('simple_links', $_POST);
				$indeed_db->simple_links_save_link($_POST, 1);
			} else if (!empty($_GET['delete'])){
				$indeed_db->simple_links_delete_link($_GET['delete']);
			} else if (!empty($_GET['approve'])){
				$indeed_db->simple_links_approve_link($_GET['approve']);
			}
			$data['metas'] = $indeed_db->return_settings_from_wp_option('simple_links');

			if (!empty($_GET['affiliate_id'])){
				$where = " affiliate_id=" . $_GET['affiliate_id'] . " ";
			} else {
				$where = '';
			}
			$current_url = UAP_PROTOCOL . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			$url = admin_url('admin.php?page=ultimate_affiliates_pro&tab=magic_features&subtab=simple_links');
			$limit = (isset($_GET['uap_limit'])) ? $_GET['uap_limit'] : 25;
			$current_page = (empty($_GET['uap_list_item'])) ? 1 : $_GET['uap_list_item'];
			$total_items = $indeed_db->simple_links_get_counts($where);
			if ($current_page>1){
				$offset = ( $current_page - 1 ) * $limit;
			} else {
				$offset = 0;
			}
			if ($offset + $limit>$total_items){
				$limit = $total_items - $offset;
			}
			require_once UAP_PATH . 'classes/UapPagination.class.php';
			$limit = (isset($_GET['uap_limit'])) ? $_GET['uap_limit'] : 25;
			$pagination = new UapPagination(array(
					'base_url' => $current_url,
					'param_name' => 'uap_list_item',
					'total_items' => $total_items,
					'items_per_page' => $limit,
					'current_page' => $current_page,
			));
			$data['pagination'] = $pagination->output();
			$limit = 25;
			$data['items'] = $indeed_db->simple_links_get_items($limit, $offset, '', '', $where);

			require_once $this->admin_view_path . 'simple-links.php';
		}


		/*
		 * @param none
		 * @return string
		 */
		private function print_account_page_menu(){
			global $indeed_db;
			if (!empty($_POST['save'])){
				$indeed_db->save_settings_wp_option('account_page_menu', $_POST);
				if (!empty($_POST['slug'])){
					$indeed_db->account_page_save_custom_menu_item($_POST);
				}
			} else if (!empty($_GET['delete'])){
				$indeed_db->account_page_menu_delete_custom_item($_GET['delete']);
			}
			$data['metas'] = $indeed_db->return_settings_from_wp_option('account_page_menu');
			$data['menu'] = $indeed_db->account_page_get_menu(TRUE);
			$data['standard_tabs'] = $indeed_db->account_page_get_menu(TRUE, TRUE);
			$this->print_top_messages();
			require_once $this->admin_view_path . 'account-page_menu.php';
		}


		private function print_top_affiliates(){
			/*
			 * @param none
			 * @return string
			 */
			global $indeed_db;
			$data['submenu'] = array(
					admin_url('admin.php?page=ultimate_affiliates_pro&tab=top_affiliates') => esc_html__('Shortcode Generator', 'uap'),
					admin_url('admin.php?page=ultimate_affiliates_pro&tab=top_affiliates_settings') => esc_html__('Settings', 'uap')
			);
			require_once $this->admin_view_path . 'submenu.php';
			$this->print_top_messages();
			require_once $this->admin_view_path . 'top-affiliates.php';
		}

		private function print_top_affiliates_settings(){
			/*
			 * @param none
			 * @return string
			 */
			global $indeed_db;
			if (!empty($_POST['save'])){
				$indeed_db->save_settings_wp_option('top_affiliate_list', $_POST);
			}
			$data['metas'] = $indeed_db->return_settings_from_wp_option('top_affiliate_list');

			$data['submenu'] = array(
					admin_url('admin.php?page=ultimate_affiliates_pro&tab=top_affiliates') => esc_html__('Shortcode Generator', 'uap'),
					admin_url('admin.php?page=ultimate_affiliates_pro&tab=top_affiliates_settings') => esc_html__('Settings', 'uap')
			);
			require_once $this->admin_view_path . 'submenu.php';
			$this->print_top_messages();
			require_once $this->admin_view_path . 'top-affiliates_settings.php';
		}

		private function print_custom_affiliate_slug(){
			/*
			 * @oaram none
			 * @return string
			 */
			$this->print_top_messages();
			global $indeed_db;
			if (!empty($_POST['save'])){
				$indeed_db->save_settings_wp_option('custom_affiliate_slug', $_POST);

				if ( empty( $_POST['uap_custom_affiliate_slug_on'] ) ){
						// deactivate tab
						$indeed_db->deactivateApTab( 'custom_affiliate_slug' );
				} else {
						// activate tab
						$indeed_db->activateApTab( 'custom_affiliate_slug' );
				}
			} else if (!empty($_POST['affiliate_id']) && !empty($_POST['slug'])){
				$uid = $indeed_db->get_uid_by_affiliate_id($_POST['affiliate_id']);
				if ($uid){
					$saved = $indeed_db->save_custom_slug_for_uid($uid, $_POST['slug']);
				}
			}

			$url = admin_url('admin.php?page=ultimate_affiliates_pro&tab=magic_features&subtab=custom_affiliate_slug');
			$current_url = $url;
			$limit = 25;
			$current_page = (empty($_GET['uap_list_item'])) ? 1 : $_GET['uap_list_item'];
			$total_items = $indeed_db->get_all_affiliates_slug(0, 0, TRUE);

			if ($current_page>1){
				$offset = ( $current_page - 1 ) * $limit;
			} else {
				$offset = 0;
			}
			if ($offset + $limit>$total_items){
				$limit = $total_items - $offset;
			}

			require_once UAP_PATH . 'classes/UapPagination.class.php';
			$pagination = new UapPagination(array(
					'base_url' => $current_url,
					'param_name' => 'uap_list_item',
					'total_items' => $total_items,
					'items_per_page' => $limit,
					'current_page' => $current_page,
			));

			$data['pagination'] = $pagination->output();
			$limit = 25;
			$data['items'] = $indeed_db->get_all_affiliates_slug($limit, $offset);
			$data['metas'] = $indeed_db->return_settings_from_wp_option('custom_affiliate_slug');
			require_once $this->admin_view_path . 'custom-affiliate-slug.php';
		}

		private function print_checkout_select_referral(){
			/*
			 * @param none
			 * @return string
			 */
			$this->print_top_messages();
			global $indeed_db;
			if (!empty($_POST['save'])){
				$indeed_db->save_settings_wp_option('checkout_select_referral', $_POST);
			}
			$data['metas'] = $indeed_db->return_settings_from_wp_option('checkout_select_referral');

			$usernames = array();
			$aff_list = '';
			if (!empty($data['metas']['uap_checkout_select_affiliate_list'])){
				$aff_list = explode(',', $data['metas']['uap_checkout_select_affiliate_list']);
				foreach ($aff_list as $id){
					$usernames[$id] = $indeed_db->get_wp_username_by_affiliate_id($id);
				}
				$usernames[-1] = 'All';
			}

			require_once $this->admin_view_path . 'checkout-select-referral.php';
		}

		private function print_woo_account_page(){
			/*
			 * @param none
			 * @return string
			 */
			 $this->print_top_messages();
			 global $indeed_db;
			 if (!empty($_POST['save'])){
			     $indeed_db->save_settings_wp_option('woo_account_page', $_POST);
			 }
			 $data['metas'] = $indeed_db->return_settings_from_wp_option('woo_account_page');
			 require_once $this->admin_view_path . 'woo-account_page.php';
		}

		private function print_bp_account_page(){
			/*
			 * @param none
			 * @return string
			 */
			 $this->print_top_messages();
			 global $indeed_db;
			 if (!empty($_POST['save'])){
			     $indeed_db->save_settings_wp_option('bp_account_page', $_POST);
			 }
			 $data['metas'] = $indeed_db->return_settings_from_wp_option('bp_account_page');
			 require_once $this->admin_view_path . 'bp-account-page.php';
		}

		private function migrate_affiliates_pro()
		{
				global $indeed_db;
				$this->print_top_messages();
				$data['ranks_available'] = $indeed_db->get_rank_list();
				require_once $this->admin_view_path . 'migrate-affiliates_pro.php';
		}

		private function migrate_wp_affiliates()
		{
				global $indeed_db;
				$this->print_top_messages();
				$data['ranks_available'] = $indeed_db->get_rank_list();
				require_once $this->admin_view_path . 'migrate-wp_affiliates.php';
		}

		private function migrate_affiliate_wp()
		{
				global $indeed_db;
				$this->print_top_messages();
				$data['ranks_available'] = $indeed_db->get_rank_list();
				require_once $this->admin_view_path . 'migrate-affiliate_wp.php';
		}

		private function ranks_pro()
		{
				global $indeed_db;
				$this->print_top_messages();
				if (!empty($_POST['save'])){
						if (get_option('uap_ranks_pro_reset_day')!=$_POST['uap_ranks_pro_reset_day']){
								update_option('uap_ranks_pro_reset_day', $_POST['uap_ranks_pro_reset_day']);
								$object = new \Indeed\Uap\ResetRanks();
								$object->doSchedule();
						}
						$indeed_db->save_settings_wp_option('ranks_pro', $_POST);
				}
				$data['metas'] = $indeed_db->return_settings_from_wp_option('ranks_pro');
				require_once $this->admin_view_path . 'ranks-pro.php';
		}

		private function landing_pages()
		{
				global $indeed_db;
				$this->print_top_messages();
				if (!empty($_GET['delete'])){
						$indeed_db->removeAffiliateLandingPage($_GET['delete']);
				}
				if (!empty($_POST['save'])){
						$indeed_db->save_settings_wp_option('landing_pages', $_POST);

						if ( empty( $_POST['uap_landing_pages_enabled'] ) ){
								// deactivate tab
								$indeed_db->deactivateApTab( 'landing_pages' );
						} else {
								// activate tab
								$indeed_db->activateApTab( 'landing_pages' );
						}
				}
				$data['metas'] = $indeed_db->return_settings_from_wp_option('landing_pages');
				$data['items'] = $indeed_db->getLandingPages();
				require_once $this->admin_view_path . 'landing-pages.php';
		}

		private function pay_per_click()
		{
				global $indeed_db;
				$this->print_top_messages();
				if (!empty($_POST['save'])){
					$indeed_db->save_settings_wp_option('pay_per_click', $_POST);
					if (isset($_POST['pay_per_click_value'])){
						foreach ($_POST['pay_per_click_value'] as $id=>$value){
							$indeed_db->update_rank_column_force_empty('pay_per_click', $id, $value);
						}
					}
				}
				$data['metas'] = $indeed_db->return_settings_from_wp_option('pay_per_click');
				$data['rank_list'] = $indeed_db->get_rank_list();
				$data['rank_value_array'] = $indeed_db->get_column_value_for_each_rank('pay_per_click');
				$data['amount_types'] = $this->amount_type_list;

				require_once $this->admin_view_path . 'pay-per-click.php';
		}

		private function cpm_commission()
		{
				global $indeed_db;
				$this->print_top_messages();
				if (!empty($_POST['save'])){
					$indeed_db->save_settings_wp_option('cpm_commission', $_POST);
					if (isset($_POST['cpm_commission_value'])){
						foreach ($_POST['cpm_commission_value'] as $id=>$value){
							$indeed_db->update_rank_column_force_empty('cpm_commission', $id, $value);
						}
					}
				}
				$data['metas'] = $indeed_db->return_settings_from_wp_option('cpm_commission');
				$data['rank_list'] = $indeed_db->get_rank_list();
				$data['rank_value_array'] = $indeed_db->get_column_value_for_each_rank('cpm_commission');
				$data['amount_types'] = $this->amount_type_list;

				require_once $this->admin_view_path . 'cpm-commission.php';
		}

		private function pushover_referral_notifications()
		{
				global $indeed_db;
				$this->print_top_messages();
				if (!empty($_POST['save'])){
					$indeed_db->save_settings_wp_option('pushover_referral_notifications', $_POST);
				}
				$data['metas'] = $indeed_db->return_settings_from_wp_option('pushover_referral_notifications');
				require_once $this->admin_view_path . 'pushover-referral_notifications.php';
		}

		private function rest_api()
		{
				global $indeed_db;
				$this->print_top_messages();
				if (!empty($_POST['save'])){
					$indeed_db->save_settings_wp_option('rest_api', $_POST);
				}
				$data['metas'] = $indeed_db->return_settings_from_wp_option('rest_api');
				$data['base_url'] = get_option('siteurl');
				require_once $this->admin_view_path . 'rest-api.php';
		}

		private function pay_to_become_affiliate()
		{
				global $indeed_db;
				$this->print_top_messages();
				if (!empty($_POST['save'])){
					$indeed_db->save_settings_wp_option('pay_to_become_affiliate', $_POST);
				}
				$data['metas'] = $indeed_db->return_settings_from_wp_option('pay_to_become_affiliate');
				$data['metas']['products'] = array();
				if (!empty($data['metas']['uap_pay_to_become_affiliate_target_products'])){
					$data['metas']['products'] = explode(',', $data['metas']['uap_pay_to_become_affiliate_target_products']);
					switch ($data['metas']['uap_pay_to_become_affiliate_target_product_group']){
						case 'woo':
							foreach ($data['metas']['products'] as $id){
								$data['products']['label'][$id] = $indeed_db->woo_get_product_title_by_id($id);
							}
							break;
						case 'ump':
							foreach ($data['metas']['products'] as $id){
								$data['products']['label'][$id] = $indeed_db->ump_get_level_label_by_id($id);
							}
							break;
					}
				}
				$data['base_url'] = get_option('siteurl');
				require_once $this->admin_view_path . 'pay-to-become-affiliate.php';
		}

		private function info_affiliate_bar()
		{
				global $indeed_db;
				$this->print_top_messages();
				if (!empty($_POST['save'])){
					$indeed_db->save_settings_wp_option('info_affiliate_bar', $_POST);
				}
				$data['metas'] = $indeed_db->return_settings_from_wp_option( 'info_affiliate_bar' );
				require_once $this->admin_view_path . 'info-affiliate-bar.php';
		}

		private function product_links()
		{
				global $indeed_db;
				$this->print_top_messages();
				if (!empty($_POST['save'])){
						$indeed_db->save_settings_wp_option( 'product_links', $_POST );

						if ( empty( $_POST['product_links_enable'] ) ){
								// deactivate tab
								$indeed_db->deactivateApTab( 'product_links' );
						} else {
								// activate tab
								$indeed_db->activateApTab( 'product_links' );
						}
				}
				$data['metas'] = $indeed_db->return_settings_from_wp_option( 'product_links' );
				require_once $this->admin_view_path . 'product-links.php';
		}

		private function print_referral_notifications(){
			/*
			 * @param none
			 * @return string
			 */
			 global $indeed_db;
			 if (!empty($_POST['save'])){
			     $indeed_db->save_settings_wp_option('referral_notifications', $_POST);
			 }
			 $data['metas'] = $indeed_db->return_settings_from_wp_option('referral_notifications');

			 if (!class_exists('AffiliateNotificationReports')){
				 require_once UAP_PATH . 'classes/AffiliateNotificationReports.class.php';
			 }
			 $object = new AffiliateNotificationReports();
			 $data['notification_constants'] = $object->notification_constants();

			 $this->print_top_messages();
			 require_once $this->admin_view_path . 'referral-notifications.php';
		}


		/**
		 * @param none
		 * @return none
		 */
		private function print_admin_referral_notifications(){
			 global $indeed_db;
			 if (!empty($_POST['save'])){
			     $indeed_db->save_settings_wp_option('admin_referral_notifications', $_POST);
			 }
			 $data['metas'] = $indeed_db->return_settings_from_wp_option('admin_referral_notifications');

			 if (!class_exists('AffiliateNotificationReports')){
				 require_once UAP_PATH . 'classes/AffiliateNotificationReports.class.php';
			 }
			 $object = new AffiliateNotificationReports();
			 $data['notification_constants'] = $object->notification_constants();

			 $this->print_top_messages();
			 require_once $this->admin_view_path . 'admin_referral_notifications.php';
		}

		private function print_periodically_reports(){
			/*
			 * @param none
			 * @return string
			 */
			 global $indeed_db;
			 if (!empty($_POST['save'])){

				/// CRON
			 	$data['metas'] = $indeed_db->return_settings_from_wp_option('periodically_reports');
			    if ($_POST['uap_periodically_reports_cron_hour']!=$data['metas']['uap_periodically_reports_cron_hour']){
			    	/// fire the CRON
			    	$base_time = strtotime(date('m/d/Y', time()));
					$input_hour = $_POST['uap_periodically_reports_cron_hour'];
					$time = $base_time + ($input_hour*3600);
					wp_schedule_event($time, 'daily', 'uap_cron_send_reports_to_affiliate');
			    }

				/// SAVE THE OPTIONS
			    $indeed_db->save_settings_wp_option('periodically_reports', $_POST);
			 }

			 $schedule = wp_next_scheduled('uap_cron_send_reports_to_affiliate');

			 $data['metas'] = $indeed_db->return_settings_from_wp_option('periodically_reports');
			 if (!class_exists('AffiliateNotificationReports')){
				 require_once UAP_PATH . 'classes/AffiliateNotificationReports.class.php';
			 }
			 $object = new AffiliateNotificationReports();
			 $data['reports_constants'] = $object->report_constants();

			 $this->print_top_messages();
			 require_once $this->admin_view_path . 'periodically-reports.php';
		}

		private function print_qr_code(){
			/*
			 * @param none
			 * @return string
			 */
			 global $indeed_db;
			 if (!empty($_POST['save'])){
			     $indeed_db->save_settings_wp_option('qr_code', $_POST);
			 }
			 $data['metas'] = $indeed_db->return_settings_from_wp_option('qr_code');

			 $this->print_top_messages();
			 require_once $this->admin_view_path . 'qr_code.php';
		}

		private function print_email_verification(){
			/*
			 * @param none
			 * @return string
			 */
			global $indeed_db;
			if (!empty($_POST['save'])){
				$indeed_db->save_settings_wp_option('email_verification', $_POST);
			}
			$data['metas'] = $indeed_db->return_settings_from_wp_option('email_verification');
			$data['payment_types'] = $indeed_db->get_payment_types_available();
			$data['pages'] = $indeed_db->uap_get_all_pages();
			require_once $this->admin_view_path . 'email-verification.php';
		}

		private function print_custom_currencies(){
			/*
			 * @param none
			 * @return none
			 */
			if (!empty($_POST['new_currency_code']) && !empty($_POST['new_currency_name'])){
				$db_data = get_option('uap_currencies_list');
				if (empty($db_data[$_POST['new_currency_code']])){
					$db_data[$_POST['new_currency_code']] = $_POST['new_currency_name'];
				}
				update_option('uap_currencies_list', $db_data);
			}
			$currencies = uap_get_currencies_list('custom');
			require_once $this->admin_view_path . 'settings-custom_currencies.php';
		}

		public function add_custom_bttns(){
			/*
			 * @param none
			 * @return none
			 */
			if (defined('DOING_AJAX') && DOING_AJAX) {
				return;
			}
			if (is_user_logged_in()){
				if (!current_user_can('edit_posts') || !current_user_can('edit_pages')){
					 return;
				}
				if (get_user_option('rich_editing') == 'true') {
					add_filter('mce_buttons', array($this, 'uap_register_button'));
					add_filter("mce_external_plugins", array($this, "uap_js_bttns_return"));
				}
			}
		}

		public function uap_register_button($arr=array()){
			/*
			 * @param array
			 * @return array
			 */
			array_push($arr, 'uap_button_forms');
			return $arr;
		}

		public function uap_js_bttns_return($arr=array()){
			/*
			 * @param array
			 * @return array
			 */
			$arr['uap_button_forms'] =  UAP_URL . 'assets/js/admin-bttns.js';
			return $arr;
		}

		public function add_style_scripts(){
			/*
			 * @param none
			 * @return none
			 */
			global $pagenow, $wp_version;
			$is_plugin_page = FALSE;
			if (!empty($_GET['page']) && $_GET['page']=='ultimate_affiliates_pro'){
				$is_plugin_page = TRUE;
			}
			wp_enqueue_style('uap_font_awesome', UAP_URL . 'assets/css/font-awesome.css');
			wp_enqueue_style('uap_main_admin_style', UAP_URL . 'assets/css/main_admin.css', array(), 7.4);
			if ($is_plugin_page){
				wp_enqueue_style('uap_bootstrap_style', UAP_URL . 'assets/css/bootstrap.css');
				wp_enqueue_style('uap_bootstrap_theme_style', UAP_URL . 'assets/css/bootstrap-theme.css');
				wp_enqueue_style('uap_main_public_style', UAP_URL . 'assets/css/main_public.min.css', array(), 7.4);
				wp_enqueue_style('uap_templates', UAP_URL . 'assets/css/templates.min.css', array(), 7.4);
			}
			if ( !isset( $_GET['page'] ) || ( $_GET['page']!='et_divi_options' && $_GET['page']!='wpcf7' ) ){
					wp_enqueue_style('uap_jquery-ui.min.css', UAP_URL . 'assets/css/jquery-ui.min.css');
			}

			wp_enqueue_script('jquery');
			wp_enqueue_media();
			wp_register_script('uap_admin_js', UAP_URL . 'assets/js/admin-functions.min.js', ['jquery'], 7.4   );

			if ( version_compare ( $wp_version , '5.7', '>=' ) ){
					wp_add_inline_script( 'uap_admin_js', "var uap_url='" . get_site_url() . "';" );
					wp_add_inline_script( 'uap_admin_js', "var uapPluginUrl='" . UAP_URL . "';" );
					wp_add_inline_script( 'uap_admin_js', "var uapAdminAjaxNonce='" . wp_create_nonce( 'uapAdminAjaxNonce' ) . "';" );
			} else {
					wp_localize_script( 'uap_admin_js', 'uap_url', get_site_url() );
					wp_localize_script( 'uap_admin_js', 'uapPluginUrl', UAP_URL );
					wp_localize_script( 'uap_admin_js', 'uapAdminAjaxNonce', wp_create_nonce( 'uapAdminAjaxNonce' ) );
			}
			if ( $pagenow == 'plugins.php' ){
					if ( version_compare ( $wp_version , '5.7', '>=' ) ){
							wp_add_inline_script( 'uap_admin_js', "var uapKeepData=" . get_option('uap_keep_data_after_delete') . ";" );
					} else {
							wp_localize_script( 'uap_admin_js', 'uapKeepData', get_option('uap_keep_data_after_delete') );
					}
					wp_enqueue_script( 'indeed_sweetalert_js', UAP_URL . 'assets/js/sweetalert.js', ['jquery'] );
					wp_enqueue_style( 'indeed_sweetalert_css', UAP_URL . 'assets/css/sweetalert.css' );
			}
			wp_enqueue_script('uap_admin_js');

			if ($is_plugin_page){
				wp_enqueue_script('jquery-ui-sortable');
				wp_enqueue_script('jquery-ui-datepicker');
				wp_enqueue_script('jquery-ui-autocomplete');
				wp_enqueue_script('uap-jquery.flot.js', UAP_URL . 'assets/js/jquery.flot.js', array( 'jquery' ), null);
				wp_enqueue_script('uap-bootstrap.js', UAP_URL . 'assets/js/bootstrap.js', array('jquery'), null);
				wp_enqueue_script('uap-jquery.flot.pie.js', UAP_URL . 'assets/js/jquery.flot.pie.js', array('jquery'), null);
				wp_enqueue_script('uap-jquery_form_module', UAP_URL . 'assets/js/jquery.form.js', array('jquery'), null );
				wp_enqueue_script('uap-jquery.uploadfile', UAP_URL . 'assets/js/jquery.uploadfile.min.js', array('jquery'), null );
				wp_enqueue_script('uap-jquery.flot.time.js', UAP_URL . 'assets/js/jquery.flot.time.js', array('jquery'), null);
				wp_register_script('uap_public', UAP_URL . 'assets/js/public-functions.min.js', ['jquery'], 7.4   );

				if ( version_compare ( $wp_version , '5.7', '>=' ) ){
						wp_add_inline_script( 'uap_public', "var ajax_url='" . admin_url('admin-ajax.php') . "';" );
						wp_add_inline_script( 'uap_public', "var uapMigrationCompleteMessage='" . esc_html__('Process completed!', 'uap') . "';" );
				} else {
						wp_localize_script( 'uap_public', 'ajax_url', admin_url('admin-ajax.php') );
						wp_localize_script( 'uap_public', 'uapMigrationCompleteMessage', esc_html__('Process completed!', 'uap') );
				}
				wp_enqueue_script('uap_public');
				wp_enqueue_style('uap_select2_style', UAP_URL . 'assets/css/select2.min.css' );
				wp_enqueue_script( 'uap-select2', UAP_URL . 'assets/js/select2.min.js', array(), null );

				wp_enqueue_script( 'indeed_sweetalert_js', UAP_URL . 'assets/js/sweetalert.js', ['jquery'] );
				wp_enqueue_style( 'indeed_sweetalert_css', UAP_URL . 'assets/css/sweetalert.css' );
			}

		}


		public function referral_action(){
			/*
			 * @param none
			 * @return none
			 */
			/// main referral
			require_once UAP_PATH . 'public/Referral_Main.class.php';
			$object = new Referral_Main();

			/************** services ****************/
			/// WOO
			require_once UAP_PATH . 'public/services/Uap_Woo.class.php';
			$woo = new Uap_Woo();

			/// UMP
			require_once UAP_PATH . 'public/services/Uap_UMP.class.php';
			$ump = new Uap_UMP();

			/// EDD
			require_once UAP_PATH . 'public/services/Uap_Easy_Digital_Download.class.php';
			$edd = new Uap_Easy_Digital_Download();

			/// ULP
			require_once UAP_PATH . 'public/services/Uap_Ulp.php';
			$ulp = new Uap_Ulp();
		}


		public function dashboard_print_uap_column($states, $post){
			/*
			 * @param string, object
			 * @return none, print a string if it's case
			 */
			if (isset($post->ID) ){
				$str = '';
				//////////// DEFAULT PAGES
				if (get_post_type($post->ID)=='page'){
					global $indeed_db;
					$pages = $indeed_db->return_settings_from_wp_option('general-default_pages');

					switch ($post->ID){
						case $pages['uap_general_login_default_page']:
							$print = esc_html__('Affiliates - Login Page', 'uap');
							break;
						case $pages['uap_general_register_default_page']:
							$print = esc_html__('Affiliates - Register Page', 'uap');
							break;
						case $pages['uap_general_lost_pass_page']:
							$print = esc_html__('Affiliates - Lost Password', 'uap');
							break;
						case $pages['uap_general_logout_page']:
							$print = esc_html__('Affiliates - LogOut Page', 'uap');
							break;
						case $pages['uap_general_user_page']:
							$print = esc_html__('Affiliates - User Page', 'uap');
							break;
						case $pages['uap_general_tos_page']:
							$print = esc_html__('Affiliates - TOS', 'uap');
							break;
					}
					if (!empty($print)){
						$str .= '<div class="uap-dashboard-list-posts-col-default-pages">' . $print . '</div>';
					}
				}
				if (!empty($str)){
					$states[] = $str;
				}
			}
			return $states;
		}

		public function create_page_meta_box(){
			/*
			 * @param
			 * @return
			 */
			global $post, $indeed_db;
			add_meta_box(
						'uap_default_pages',//id
						__('Affiliates Pro - Default Pages', 'uap'),
						array($this, 'print_page_meta_box'),
						'page',
						'side',
						'high'
			);

			$postTypes = $indeed_db->get_all_post_types();
			foreach ($postTypes as $postType){
					add_meta_box(
								'uap_affiliate_landing_page',//id
								__('Affiliates Pro - Affiliate Landing Pages', 'uap'),
								array($this, 'print_affiliate_landing_page_meta_box'),
								$postType,
								'side',
								'high'
					);
			}

			// woo
			add_meta_box(
						'uap_woo_referral_details',//id
						__('Affiliates Pro - Referral Details', 'uap'),
						array($this, 'wooMetaBoxReferralDetails'),
						'shop_order',
						'side',
						'high'
			);

		}

		public function wooMetaBoxReferralDetails()
		{
				global $post, $indeed_db;
				$postId = isset( $post->ID ) ? $post->ID : 0;
				$view = new \Indeed\Uap\IndeedView();
				$referralDetails = $indeed_db->getReferralsForReferrence( $postId );
				echo $view->setTemplate( UAP_PATH . 'admin/views/uap-woo-orders-metabox.php' )
									->setContentData( [ 'referrals' => $referralDetails ] )
									->getOutput();

		}

		public function print_affiliate_landing_page_meta_box()
		{
				global $post, $indeed_db;
				$data['uap_landing_page_affiliate_id'] = get_post_meta($post->ID, 'uap_landing_page_affiliate_id', true);
				$data['affiliate_uid'] = $indeed_db->get_uid_by_affiliate_id($data['uap_landing_page_affiliate_id']);
				$data['affiliate_usename'] = $indeed_db->get_username_by_wpuid($data['affiliate_uid']);
				require_once $this->admin_view_path . 'landing-pages_meta_box.php';
		}

		public function print_page_meta_box(){
			/*
			 * @param none
			 * @return string
			 */
			global $post;
			global $indeed_db;
			$data['types'] = array(
							'uap_general_login_default_page' => esc_html__('Login', 'uap'),
							'uap_general_register_default_page' => esc_html__('Register', 'uap'),
							'uap_general_lost_pass_page' =>  esc_html__('Lost Password', 'uap'),
							'uap_general_logout_page' =>  esc_html__('LogOut', 'uap'),
							'uap_general_user_page' =>  esc_html__('User Account', 'uap'),
							'uap_general_tos_page' => esc_html__('TOS', 'uap'),
			);
			$data['current_page_type'] = $indeed_db->get_current_page_type($post->ID);
			$data['unset_pages'] = $indeed_db->get_default_unset_pages();
			require_once $this->admin_view_path . 'page-meta_box.php';
		}

		public function save_meta_box_values($post_id=0){
			/*
			 * @param int
			 * @return none
			 */
			if (!empty($_POST['uap_set_page_as_default_something'])){
				global $indeed_db;
				$indeed_db->set_default_page($_POST['uap_set_page_as_default_something'], $_POST['uap_post_id']);
			}
			if (isset($_POST['uap_landing_page_affiliate_id'])){
					update_post_meta($post_id, 'uap_landing_page_affiliate_id', $_POST['uap_landing_page_affiliate_id']);
			}
		}

		public function print_shortcodes(){
			/*
			 * @param none
			 * @return string
			 */
			 require_once $this->admin_view_path . 'shortcodes.php';
		}

		public function edit_wp_user($user_object){
			/*
			 * @param object
			 * @return string
			 */
			if (current_user_can('edit_user') && current_user_can('manage_options') && $user_object && !empty($user_object->data) && !empty($user_object->data->user_login)){
				global $indeed_db;
				$data['is_affiliate'] = $indeed_db->get_affiliate_id_by_username($user_object->data->user_login);
				$data['id'] = $user_object->data->ID;
				ob_start();
				require $this->admin_view_path . 'edit-wp-user.php';
				$output = ob_get_contents();
				ob_end_clean();
				echo $output;
			}
		}

		public function uap_delete_affiliate_by_uid($id=0){
			/*
			 * FIRE WHEN A USER IT's DELETED FROM WP
			 * @param int
			 * @return none
			 */
			 if ($id){
				global $indeed_db;
				$affiliate_id = $indeed_db->get_affiliate_id_by_wpuid($id);
				if ($affiliate_id){
					$indeed_db->delete_affiliate_details($affiliate_id);
				}
			}
		}

		private function print_flag_for_affiliate($user_id=0){
			/*
			 * @param int
			 * @return string
			 */
			 if ($user_id){
			 	 $flag_src = get_user_meta($user_id, 'uap_country', TRUE);
				 if ($flag_src){
				 	$countries = uap_get_countries();
					$country = $countries[strtoupper($flag_src)];
					$title = (empty($country)) ? '' : $country;
 				 	return '<span class="uap-affiliate-flag-wrapp"><img src="' . UAP_URL . 'assets/flags/' . $flag_src . '.svg" title="' . $title . '" class="uap-affiliate-admin-flag" /></span>';
				 }
			 }
			 return '';
		}

		private function print_source_details(){
			/*
			 * @param none
			 * @return string
			 */
			global $indeed_db;
			if (!empty($_POST['save'])){
				$indeed_db->save_settings_wp_option('source_details', $_POST);
			}
			$data['fields_available'] = array(
												'user_login' => 'Username',
												'first_name' => esc_html__('First Name', 'uap'),
												'last_name' => esc_html__('Last Name', 'uap'),
												'phone' => esc_html__('Phone', 'uap'),
												'email' => esc_html__('E-mail', 'uap'),
												'order_date' => esc_html__('Order Date', 'uap'),
												'order_amount' => esc_html__('Order Amount', 'uap'),
												'shipping_address' => esc_html__('Shipping Address', 'uap'),
												'billing_address' => esc_html__('Billing Address', 'uap'),
												'cart_items' => esc_html__('Cart Items', 'uap'),
			);
			$data['metas'] = $indeed_db->return_settings_from_wp_option('source_details');
			require_once $this->admin_view_path . 'source-details.php';
		}

		public function hooksAndFilters( $tab='' )
		{
				if ( !$tab ){
						return;
				}
				if ( $tab != 'hooks' ){
						return;
				}
				$object = new \Indeed\Uap\SearchFiltersAndHooks();
				$object->setPluginName( 'indeed-affiliate-pro' )->setNameShouldContain( [ 'uap' ] )->SearchFiles( UAP_PATH );
				$data = $object->getResults();
				$view = new \Indeed\Uap\IndeedView();
				echo $view->setTemplate( UAP_PATH . 'admin/views/hooks.php' )
									->setContentData( $data )
									->getOutput();
		}

		public function wooCouponsSettings()
		{
				global $post, $indeed_db;
				$data = [
									'product_id' => isset( $post->ID ) ? $post->ID : 0,
									'types'			 => [
																		'flat' 					=> esc_html__( 'Flat ', 'uap') . '(' . get_option('uap_currency') .')',
																		'percentage'		=> esc_html__( 'Percentage ', 'uap') . '(%)',
									],
									'uap_affiliate_username'		=> '',
									'uap_amount_type'						=> '',
									'uap_amount_value'					=> '',
									'id'												=> 0,
				];
				$code = isset( $post->post_title ) ? $post->post_title : '';
				$couponData = $indeed_db->get_coupon_data( $code );
				if ( $couponData && isset( $couponData['affiliate_id'] ) ){
						$data['uap_affiliate_username'] = $indeed_db->get_wp_username_by_affiliate_id( $couponData['affiliate_id'] );
						$data['uap_amount_type'] = isset( $couponData['amount_type'] ) ? $couponData['amount_type'] : '';
						$data['uap_amount_value'] = isset( $couponData['amount_value'] ) ? $couponData['amount_value'] : '';
						$data['id'] = isset( $couponData['id'] ) ? $couponData['id'] : 0;
						$data['uap_status'] = isset( $couponData['status'] ) ? $couponData['status'] : 0;
				}
				$view = new \Indeed\Uap\IndeedView();
				echo $view->setTemplate( UAP_PATH . 'admin/views/woo-coupons-settings.php' )
									->setContentData( $data )
									->getOutput();
		}

		public function wooSaveCoupons()
		{
				global $indeed_db;
				if ( empty( $_POST['uap_affiliate_username'] ) ){
						return;
				}
				if ( empty( $_POST['post_title'] ) ){
						return;
				}
				$affiliateId = $indeed_db->get_affiliate_id_by_username( esc_sql( $_POST['uap_affiliate_username'] ) );
				$data = [
									'amount_type' 		=> isset( $_POST['uap_amount_type'] ) ? $_POST['uap_amount_type'] : '',
									'amount_value' 		=> isset( $_POST['uap_amount_value'] ) ? $_POST['uap_amount_value'] : '',
									'affiliate_id'		=> $affiliateId,
									'type'						=> 'woo',
									'code'						=> esc_sql( $_POST['post_title'] ),
									'id'							=> isset( $_POST['uap_static_coupon_id'] ) ? $_POST['uap_static_coupon_id'] : false,
									'status'					=> isset( $_POST['uap_status'] ) ? $_POST['uap_status'] : false,
				];
				$indeed_db->save_coupon_affiliate_pair( $data );
		}

		/**
		 * Plugin row meta links
		 *
		 * @param array $input already defined meta links
		 * @param string $file plugin file path and name being processed
		 * @return array $input
		 */
		public function uap_plugin_row_meta( $input, $file ) {

			if ( $file != 'indeed-affiliate-pro/indeed-affiliate-pro.php' ) {
				return $input;
			}

			$links = [
				'<a href="https://help.wpindeed.com/ultimate-affiliate-pro/knowledge-base/" target="_blank">' . esc_html__( 'Knowledge Base', 'uap' ) . '</a>',
				'<a href="https://store.wpindeed.com/addon/category/ultimate-affiliate-pro/" target="_blank">' . esc_html__( 'Pro Extensions', 'uap' ) . '</a>',
				'<a href="https://affiliate.wpindeed.com/changelog/" target="_blank">' . esc_html__( 'ChangeLog', 'uap' ) . '</a>'
			];

			$input = array_merge( $input, $links );

			return $input;

		}

		public function uap_manage_ump() {
			global $submenu;

			$submenu['ultimate_affiliates_pro'][100] = array( 'Ultimate Affiliate Pro', 'manage_options' , 'ultimate_affiliates_pro', 'Ultimate Affiliate Pro' );
			$submenu['ultimate_affiliates_pro'][200] = array( 'Pro Addons', 'manage_options' , 'https://store.wpindeed.com/addon/category/ultimate-affiliate-pro/', 'uap-addons-link-wrapp', 'uap-addons-link-wrapp', '_blank' );
			if (!uap_is_ihc_active()){
				$submenu['ultimate_affiliates_pro'][3200] = array( 'Ultimate Membership Pro', 'manage_options' , 'https://wpindeed.com/ultimate-membership-pro', 'uap-ihc-link-wrapp', 'uap-ihc-link-wrapp', '_blank' );
			}
		}

		public function uap_manage_ump_add_jquery() {
			?>
		    <script type="text/javascript">
		        jQuery( function() {
		            jQuery('.uap-ihc-link-wrapp').attr('target','_blank');
		        });
		    </script>
		    <?php
		}

		public function marketingBanners()
		{
			if ( !current_user_can('manage_options') ){
					return;
			}

			$woo = uapSearchForWooAndExtension();
			if ( isset( $woo['status'] ) && $woo['status'] === 1 ){
				// print the message
				$messages[] = $woo['message'];
			}
			$myCred = uapSearchForMyCredAndExtension();
			if ( isset( $myCred['status'] ) && $myCred['status'] === 1 ){
				// print the message
				$messages[] = $myCred['message'];
			}

			if ( !isset( $messages ) ){
					// no message to print
					return;
			}
			if ( count( $messages ) === 1){
					// print the message
					echo $messages[0];
			} else if ( count( $messages ) > 1 ){
					// print a random messages from the array
					$total = count( $messages ) - 1;
					$print = rand( 0, $total );
					if ( isset( $messages[$print] ) ){
							echo $messages[$print];
					}
			}

		}

	}
}
