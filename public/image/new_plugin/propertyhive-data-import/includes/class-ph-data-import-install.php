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

if ( ! class_exists( 'PH_Data_Import_Install' ) ) :

/**
 * PH_Data_Import_Install Class
 */
class PH_Data_Import_Install {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		register_activation_hook( PH_DATA_IMPORT_PLUGIN_FILE, array( $this, 'install' ) );
		register_deactivation_hook( PH_DATA_IMPORT_PLUGIN_FILE, array( $this, 'deactivate' ) );
		register_uninstall_hook( PH_DATA_IMPORT_PLUGIN_FILE, array( 'PH_Data_Import_Install', 'uninstall' ) );

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
	    	( get_option( 'propertyhive_data_import_version' ) != PHDI()->version || get_option( 'propertyhive_data_import_db_version' ) != PHDI()->version ) 
	    ) {
			$this->install();
		}
	}

	/**
	 * Install actions
	 */
	public function install_actions() {



	}

	/**
	 * Install Property Hive Data Import Add-On
	 */
	public function install() {
        
		$this->create_cron();

		$current_version = get_option( 'propertyhive_data_import_version', null );
		$current_db_version = get_option( 'propertyhive_data_import_db_version', null );
        
        update_option( 'propertyhive_data_import_db_version', PHDI()->version );

        // Update version
        update_option( 'propertyhive_data_import_version', PHDI()->version );
	}

	/**
	 * Deactivate Property Hive Data Import Add-On
	 */
	public function deactivate() {

		

	}

	/**
	 * Uninstall Property Hive Data Import Add-On
	 */
	public function uninstall() {

	}

    /**
	 * Creates the scheduled event to run hourly
	 *
	 * @access public
	 */
    public function create_cron() {


    }
}

endif;

return new PH_Data_Import_Install();