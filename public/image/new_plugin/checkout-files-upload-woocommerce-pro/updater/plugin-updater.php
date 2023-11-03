<?php
// Exit if accessed directly
if ( ! defined('ABSPATH') ) { exit; }

/**
 * Calls the class.
 */
function wpwham_init_checkout_files_upload_updater() {
    return Wpwham_Checkout_Files_Upload_Updater::get_instance();
}
add_action( 'woocommerce_loaded', 'wpwham_init_checkout_files_upload_updater', 1 );


class Wpwham_Checkout_Files_Upload_Updater {

	private $store_url   = 'https://wpwham.com';
	private $name        = 'Checkout Files Upload for WooCommerce (Premium)';
	private $download_id = '16';
	private $file        = WPWHAM_CHECKOUT_FILES_UPLOAD_PRO_FILE;
	private $version     = WPWHAM_CHECKOUT_FILES_UPLOAD_PRO_VERSION;
	private $slug        = 'checkout_files_upload';
    
    private $license_key    = '';
    private $license_status = '';
	private $license_error  = '';
	
	protected static $single_instance = null;

    public function __construct() {

  		if ( ! class_exists( 'Wpwham_Plugin_Updater', false ) ) {
			include( plugin_dir_path( __FILE__ ) . '/class-base-updater.php' );
		}

    	// retrieve our license key info from the DB
		$this->license_key = trim( get_option( 'wpwham_' . $this->slug . '_license' ) );
		$this->license_status = trim( get_option( 'wpwham_' . $this->slug . '_license_status' ) );
		$this->license_error = trim( get_option( 'wpwham_' . $this->slug . '_license_error' ) );
		
		// hooks
        add_filter( 'wpwham_'. $this->slug . '_option_field', array( $this, 'license_field' ), 1 );
		add_action( 'admin_init', array( $this, 'plugin_updater' ), 0 );
		add_action( 'admin_init', array( $this, 'activate_license' ) );
		add_action( 'admin_init', array( $this, 'deactivate_license' ) );
		add_action( 'admin_init', array( $this, 'updater_notices' ) );
		add_action( 'wpwham_'. $this->slug . '_updater', array( $this, 'check_license' ) );
		add_filter( 'wpwham_automatic_updates_html', array( $this, 'automatic_updates_unavailable_warning' ), 10, 2 );
		
		// check cron
		if ( ! wp_next_scheduled ( 'wpwham_'. $this->slug . '_updater' ) ) {
			wp_schedule_event( time(), 'daily', 'wpwham_'. $this->slug . '_updater' );
		}
		
    }
	
	public static function get_instance() {
		if ( null === self::$single_instance ) {
			self::$single_instance = new self();
		}

		return self::$single_instance;
	}


	public function plugin_updater() {
		// setup the updater
		new Wpwham_Plugin_Updater( $this->store_url, $this->file, array(
				'version'   => $this->version, // current version number
				'license'   => $this->license_key, // license key (used get_option above to retrieve from DB)
				'item_name' => $this->name, // name of this plugin
				'author'    => 'WP Wham', // author of this plugin
				'beta'		=> false,
			)
		);

	}


	public function license_field( $output ) {
		$output .= $this->after_field();
		return $output;
	}

	public function after_field() {
		
		if ( empty( $this->license_key ) ) {
			return;
		}

		$status = '';
		if ( $this->license_status ) {
			$status = 'valid' === $this->license_status ? 'active' : $this->license_status;
			$status = '<span class="wpwham-license-status wpwham-license-'. $status .'">' . sprintf( esc_html__( 'License: %s', 'checkout-files-upload-woocommerce' ), $status ) . '</span>';
		}

		$nonce = wp_create_nonce( 'wpwham_nonce' );

		$id = $this->slug . ( 'valid' === $this->license_status ? '_license_deactivate' : '_license_activate' );

		$label = 'valid' === $this->license_status
			? esc_html__( 'Deactivate License', 'checkout-files-upload-woocommerce' )
			: esc_html__( 'Activate License', 'checkout-files-upload-woocommerce' );

		return sprintf(
			'<p>%1$s<button type="submit" class="button-secondary" name="%3$s" value="%2$s">%4$s</button></p>',
			$status,
			$nonce,
			$id,
			$label
		);

	}

	public function activate_license() {

		// listen for our activate button to be clicked
		if ( isset( $_POST[ $this->slug . '_license_activate'], $_POST[ 'wpwham_' . $this->slug . '_license' ] ) ) {

			// run a quick security check
			if ( ! wp_verify_nonce( $_POST[ $this->slug . '_license_activate'], 'wpwham_nonce' ) ) {
				return; // get out if we didn't click the Activate button
			}

			// data to send in our API request
			$api_params = array(
				'edd_action' => 'activate_license',
				'license'    => sanitize_text_field( $_POST[ 'wpwham_' . $this->slug . '_license' ] ),
				'item_name'  => urlencode( $this->name ), // the name of our product in EDD
				'url'        => home_url(),
			);

			// Call the custom API.
			$response = wp_remote_post( $this->store_url, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

			// make sure the response came back okay
			if ( is_wp_error( $response ) ) {
				return false;
			}

			// decode the license data
			$license_data   = json_decode( wp_remote_retrieve_body( $response ) );
			$license_key    = trim( $api_params['license'] );
			$license_status = trim( $license_data->license );
			
			// $license_data->license will be either "valid" or "invalid"
			if ( $license_data->license !== 'valid' && $license_data->error ) {
				$license_error = trim( $license_data->error );
			} else {
				$license_error = '';
			}
			
			update_option( 'wpwham_' . $this->slug . '_license', $license_key );
			update_option( 'wpwham_' . $this->slug . '_license_status', $license_status );
			update_option( 'wpwham_' . $this->slug . '_license_error', $license_error );

			$this->license_key    = $license_key;
			$this->license_status = $license_status;
			$this->license_error  = $license_error;
		}
	}

	public function deactivate_license() {

		// listen for our activate button to be clicked
		if ( isset( $_POST[ $this->slug . '_license_deactivate'] ) ) {

			// run a quick security check
			if ( ! wp_verify_nonce( $_POST[ $this->slug . '_license_deactivate'], 'wpwham_nonce' ) ) {
				return; // get out if we didn't click the Activate button
			}

			// data to send in our API request
			$api_params = array(
				'edd_action' => 'deactivate_license',
				'license'    => $this->license_key,
				'item_name'  => urlencode( $this->name ), // the name of our product in EDD
				'url'        => home_url(),
			);

			// Call the custom API.
			$response = wp_remote_post( $this->store_url, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

			// make sure the response came back okay
			if ( is_wp_error( $response ) ) {
				return false;
			}

			// decode the license data
			$license_data   = json_decode( wp_remote_retrieve_body( $response ) );
			$license_status = trim( $license_data->license );

			$this->license_status = $license_status;

			// $license_data->license will be either "deactivated" or "failed"
			if ( $license_data->license == 'deactivated' ) {
				update_option( 'wpwham_' . $this->slug . '_license_status', $license_status );
			}

		}
	}
	
	public function check_license() {

		if ( $this->license_key === '' ) {
			// if there's no key to check, clear out any old status and stop
			update_option( 'wpwham_' . $this->slug . '_license_status', '' );
			update_option( 'wpwham_' . $this->slug . '_license_error', '' );
			return false;
		}
		
		// otherwise, we go on...
		$api_params = array(
			'edd_action' => 'check_license',
			'license'    => $this->license_key,
			'item_name'  => urlencode( $this->name ),
			'url'        => home_url(),
		);

		// Call the custom API.
		$response = wp_remote_post( $this->store_url, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		// decode response
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );
		
		if ( ! $license_data ) {
			return false;
		}
		
		// $license_data->license will be either "valid" or "inactive"
		if ( $this->license_status !== $license_data->license ) {
			// this means there was a change in status.
			// cache new status to DB
			update_option( 'wpwham_' . $this->slug . '_license_status', trim( $license_data->license === 'site_inactive' ? 'inactive' : $license_data->license ) );
			update_option( 'wpwham_' . $this->slug . '_license_error', '' );
		}
		
		return $license_data->license;
	}
	
	public function updater_notices_clear( $exclude = '' ) {
	
		if ( class_exists( 'WC_Admin_Notices' ) ) {
		
			$notices = array(
				$this->slug . '_le', // license expired
				$this->slug . '_li', // license inactive
				$this->slug . '_ln', // license invalid
				$this->slug . '_lm', // license missing
				$this->slug . '_ld', // license disabled
			);
		
			foreach ( $notices as $notice ) {
				if ( WC_Admin_Notices::has_notice( $notice ) && $notice !== $exclude ) {
					WC_Admin_Notices::remove_notice( $notice );
				}
			}
			
		}
		
	}
	
	public function updater_notices() {
		
		global $pagenow;
		
		if ( class_exists( 'WC_Admin_Notices' ) ) {
		
			if (
				$pagenow === 'admin.php'
				&& isset( $_GET['page'] )
				&& $_GET['page'] === 'wc-settings'
				&& isset( $_POST['wpwham_' . $this->slug . '_license'] )
			) {
				// we must have just saved, so clear any existing notices first
				$this->updater_notices_clear();
				$this->check_license();
			}
		
			if ( ! $this->license_key > '' ) {
			
				if ( ! WC_Admin_Notices::has_notice( $this->slug . '_lm' ) ) {
					$this->updater_notices_clear( $this->slug . '_lm' );
					$notice_html = '<p>' . $this->name . ': ' . sprintf( __( 'No license key found.  Don\'t forget to enter your license key on the <a href="%s">Settings page</a>.', 'checkout-files-upload-woocommerce' ), admin_url( 'admin.php?page=wc-settings&tab=alg_wc_checkout_files_upload' ) ) . '</p>';
					WC_Admin_Notices::add_custom_notice( $this->slug . '_lm', $notice_html );
				}
				
			} elseif ( $this->license_status === 'valid' ) {
			
				$this->updater_notices_clear();
				
			} elseif ( $this->license_error === 'expired' || $this->license_status === 'expired' ) {
			
				if ( ! WC_Admin_Notices::has_notice( $this->slug . '_le' ) ) {
					$this->updater_notices_clear( $this->slug . '_le' );
					$notice_html = '<p>' . sprintf( __( 'Your license for %s has expired.  Please <a href="%s">renew your license</a> to continue receiving updates and support.', 'checkout-files-upload-woocommerce' ), $this->name, 'https://wpwham.com/checkout/?edd_license_key='.$this->license_key.'&download_id='.$this->download_id ) . '</p>';
					WC_Admin_Notices::add_custom_notice( $this->slug . '_le', $notice_html );
				}
				
			} elseif ( $this->license_error === 'revoked' ) {
			
				if ( ! WC_Admin_Notices::has_notice( $this->slug . '_ld' ) ) {
					$this->updater_notices_clear( $this->slug . '_ld' );
					$notice_html = '<p>' . sprintf( __( 'Your license for %s has been disabled.  Please <a href="%s">contact us</a> for assistance.', 'checkout-files-upload-woocommerce' ), $this->name, 'https://wpwham.com/contact-us/' ) . '</p>';
					WC_Admin_Notices::add_custom_notice( $this->slug . '_ld', $notice_html );
				}
			
			} elseif ( $this->license_status === 'inactive' || $this->license_status === 'site_inactive' ) {
			
				if ( ! WC_Admin_Notices::has_notice( $this->slug . '_li' ) ) {
					$this->updater_notices_clear( $this->slug . '_li' );
					$notice_html = '<p>' . sprintf( __( 'Your license for %s is not active.  Be sure to activate your license key on the <a href="%s">Settings page</a> to receive updates and support.', 'checkout-files-upload-woocommerce' ), $this->name, admin_url( 'admin.php?page=wc-settings&tab=alg_wc_checkout_files_upload' ) ) . '</p>';
					WC_Admin_Notices::add_custom_notice( $this->slug . '_li', $notice_html );
				}
				
			} elseif ( $this->license_status === 'invalid' ) {
			
				if ( ! WC_Admin_Notices::has_notice( $this->slug . '_ln' ) ) {
					$this->updater_notices_clear( $this->slug . '_ln' );
					$notice_html = '<p>' . sprintf( __( 'The license key entered for %s is not valid. Please double check your license key on the <a href="%s">Settings page</a>.', 'checkout-files-upload-woocommerce' ), $this->name, admin_url( 'admin.php?page=wc-settings&tab=alg_wc_checkout_files_upload' ) ) . '</p>';
					WC_Admin_Notices::add_custom_notice( $this->slug . '_ln', $notice_html );
				}
				
			}
			
		}
		
	}
	
	public function updater_running() {
		if ( $this->license_status === 'valid' ) {
			return true;
		} else {
			return false;
		}
	}
	
	public function automatic_updates_unavailable_warning( $html, $plugin_file ) {
		if (
			$plugin_file === plugin_basename( $this->file ) &&
			! $this->updater_running()
		) {
			$html = sprintf( 
				__( 'Auto-updates are not available for this plugin. Did you forget to activate your license key on the <a href="%s">Settings page</a>?', 'checkout-files-upload-woocommerce' ),
				admin_url( 'admin.php?page=wc-settings&tab=alg_wc_checkout_files_upload' )
			);
		}
		return $html;
	}
	

}
