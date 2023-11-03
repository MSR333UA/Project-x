<?php
/**
 * Installation related functions and actions.
 *
 * @author 		PropertyHive
 * @category 	Admin
 * @package 	PropertyHive/Classes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'PH_Facebook_Export_Install' ) ) :

/**
 * PH_Facebook_Export_Install Class
 */
class PH_Facebook_Export_Install {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		register_activation_hook( PH_FACEBOOK_EXPORT_PLUGIN_FILE, array( $this, 'install' ) );
		register_deactivation_hook( PH_FACEBOOK_EXPORT_PLUGIN_FILE, array( $this, 'deactivate' ) );
		register_uninstall_hook( PH_FACEBOOK_EXPORT_PLUGIN_FILE, array( 'PH_Facebook_Export_Install', 'uninstall' ) );

		add_action( 'admin_init', array( $this, 'install_actions' ) );
		add_action( 'admin_init', array( $this, 'check_version' ), 5 );
	}

	/**
	 * check_version function.
	 *
	 * @access public
	 * @return void
	 */
	public function check_version() {
	    if ( 
	    	! defined( 'IFRAME_REQUEST' ) && 
	    	( get_option( 'propertyhive_facebook_export_version' ) != PHFBE()->version || get_option( 'propertyhive_facebook_export_db_version' ) != PHFBE()->version ) 
	    ) {
			$this->install();
		}
	}

	/**
	 * Deactivate Property Hive Facebook Export Add-On
	 */
	public function deactivate() {

		$timestamp = wp_next_scheduled( 'phfacebookexportcronhook' );
        wp_unschedule_event($timestamp, 'phfacebookexportcronhook' );
        wp_clear_scheduled_hook('phfacebookexportcronhook');

	}

	/**
	 * Uninstall Property Hive Facebook Export Add-On
	 */
	public function uninstall() {

		$timestamp = wp_next_scheduled( 'phfacebookexportcronhook' );
        wp_unschedule_event($timestamp, 'phfacebookexportcronhook' );
        wp_clear_scheduled_hook('phfacebookexportcronhook');

        delete_option( 'propertyhive_facebookexport' );

	}

	/**
	 * Install actions
	 */
	public function install_actions() {



	}

	/**
	 * Install Property Hive Facebook Export Add-On
	 */
	public function install() {
        
		$this->create_cron();

		$current_version = get_option( 'propertyhive_facebook_export_version', null );
		$current_db_version = get_option( 'propertyhive_facebook_export_db_version', null );
        
        update_option( 'propertyhive_facebook_export_db_version', PHFBE()->version );

        // Update version
        update_option( 'propertyhive_facebook_export_version', PHFBE()->version );
	}

	/**
	 * Setup cron
	 *
	 * Sets up the automated cron to generate CSV
	 *
	 * @access public
	 */
	public function create_cron() {
	    
        $timestamp = wp_next_scheduled( 'phfacebookexportcronhook' );
        wp_unschedule_event($timestamp, 'phfacebookexportcronhook' );
        wp_clear_scheduled_hook('phfacebookexportcronhook');
        
        $next_schedule = time() - 60;
        wp_schedule_event( $next_schedule, 'hourly', 'phfacebookexportcronhook' );

    }

}

endif;

return new PH_Facebook_Export_Install();