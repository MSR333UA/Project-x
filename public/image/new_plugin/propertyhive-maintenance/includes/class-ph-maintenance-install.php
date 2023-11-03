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

if ( ! class_exists( 'PH_Maintenance_Install' ) ) :

/**
 * PH_Maintenance_Install Class
 */
class PH_Maintenance_Install {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		register_activation_hook( PH_MAINTENANCE_PLUGIN_FILE, array( $this, 'install' ) );
		register_deactivation_hook( PH_MAINTENANCE_PLUGIN_FILE, array( $this, 'deactivate' ) );
		register_uninstall_hook( PH_MAINTENANCE_PLUGIN_FILE, array( 'PH_Maintenance_Install', 'uninstall' ) );

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
	    	( get_option( 'propertyhive_maintenance_version' ) != PHMJ()->version || get_option( 'propertyhive_maintenance_db_version' ) != PHMJ()->version ) 
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
	 * Install Property Hive Maintenance Add-On
	 */
	public function install() {
        
		///$this->create_options();
		//$this->create_cron();
		//$this->create_tables();
		$this->create_terms();

		$current_version = get_option( 'propertyhive_maintenance_version', null );
		$current_db_version = get_option( 'propertyhive_maintenance_db_version', null );
        
        update_option( 'propertyhive_maintenance_db_version', PHMJ()->version );

        // Update version
        update_option( 'propertyhive_maintenance_version', PHMJ()->version );
	}

	/**
	 * Deactivate Property Hive Maintenance Add-On
	 */
	public function deactivate() {

		

	}

	/**
	 * Uninstall Property Hive Maintenance Add-On
	 */
	public function uninstall() {

		

	}

	public function create_terms() {

		
	}

	/**
	 * Default options
	 *
	 * Sets up the default options used on the settings page
	 *
	 * @access public
	 */
	public function create_options() {
	    
        //add_option( 'option_name', 'yes', '', 'yes' );

    }

    /**
	 * Creates the scheduled event to run hourly
	 *
	 * @access public
	 */
    public function create_cron() {
        

    }

    /**
	 * Set up the database tables which the plugin needs to function.
	 *
	 * Tables:
	 *		propertyhive_blmexport_table_name - Table description
	 *
	 * @access public
	 * @return void
	 */
	private function create_tables() {

		
	}

}

endif;

return new PH_Maintenance_Install();