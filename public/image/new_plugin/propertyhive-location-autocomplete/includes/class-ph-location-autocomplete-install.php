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

if ( ! class_exists( 'PH_Location_Autocomplete_Install' ) ) :

/**
 * PH_Location_Autocomplete_Install Class
 */
class PH_Location_Autocomplete_Install {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		register_activation_hook( PH_LOCATION_AUTOCOMPLETE_PLUGIN_FILE, array( $this, 'install' ) );
		register_deactivation_hook( PH_LOCATION_AUTOCOMPLETE_PLUGIN_FILE, array( $this, 'deactivate' ) );
		register_uninstall_hook( PH_LOCATION_AUTOCOMPLETE_PLUGIN_FILE, array( 'PH_Location_Autocomplete_Install', 'uninstall' ) );

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
	    	( get_option( 'propertyhive_location_autocomplete_version' ) != PHLA()->version || get_option( 'propertyhive_location_autocomplete_db_version' ) != PHLA()->version ) 
	    ) {
			$this->install();
		}
	}

	/**
	 * Deactivate Property Hive Location Autocomplete Add-On
	 */
	public function deactivate() {

		$timestamp = wp_next_scheduled( 'phlocationautocompletecronhook' );
        wp_unschedule_event($timestamp, 'phlocationautocompletecronhook' );
        wp_clear_scheduled_hook('phlocationautocompletecronhook');

	}

	/**
	 * Uninstall Property Hive Location Autocomplete Export Add-On
	 */
	public function uninstall() {

		$timestamp = wp_next_scheduled( 'phlocationautocompletecronhook' );
        wp_unschedule_event($timestamp, 'phlocationautocompletecronhook' );
        wp_clear_scheduled_hook('phlocationautocompletecronhook');

        delete_option( 'propertyhive_location_autocomplete' );
        delete_option( 'propertyhive_location_autocomplete_data' );
        
	}

	/**
	 * Install actions
	 */
	public function install_actions() {



	}

	/**
	 * Install Property Hive Location Autocomplete Export Add-On
	 */
	public function install() {
        
		$this->create_cron();

		$current_version = get_option( 'propertyhive_location_autocomplete_version', null );
		$current_db_version = get_option( 'propertyhive_location_autocomplete_db_version', null );
        
        update_option( 'propertyhive_location_autocomplete_db_version', PHLA()->version );

        // Update version
        update_option( 'propertyhive_location_autocomplete_version', PHLA()->version );

        do_action( 'phlocationautocompletecronhook' );
	}

	/**
	 * Setup cron
	 *
	 * Sets up the automated cron to generate CSV
	 *
	 * @access public
	 */
	public function create_cron() {
	    
        $timestamp = wp_next_scheduled( 'phlocationautocompletecronhook' );
        wp_unschedule_event($timestamp, 'phlocationautocompletecronhook' );
        wp_clear_scheduled_hook('phlocationautocompletecronhook');
        
        $next_schedule = time() - 60;
        wp_schedule_event( $next_schedule, 'twicedaily', 'phlocationautocompletecronhook' );

    }

}

endif;

return new PH_Location_Autocomplete_Install();