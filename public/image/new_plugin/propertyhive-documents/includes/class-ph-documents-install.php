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

if ( ! class_exists( 'PH_Documents_Install' ) ) :

/**
 * PH_Documents_Install Class
 */
class PH_Documents_Install {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		register_activation_hook( PH_DOCUMENTS_PLUGIN_FILE, array( $this, 'install' ) );
		register_deactivation_hook( PH_DOCUMENTS_PLUGIN_FILE, array( $this, 'deactivate' ) );
		register_uninstall_hook( PH_DOCUMENTS_PLUGIN_FILE, array( 'PH_Documents_Install', 'uninstall' ) );

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
	    	( get_option( 'propertyhive_documents_version' ) != PHDOCS()->version || get_option( 'propertyhive_documents_db_version' ) != PHDOCS()->version ) 
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
	 * Install Property Hive Documents Add-On
	 */
	public function install() {
        
		$current_version = get_option( 'propertyhive_documents_version', null );
		$current_db_version = get_option( 'propertyhive_documents_db_version', null );
        
        update_option( 'propertyhive_documents_db_version', PHDOCS()->version );

        // Update version
        update_option( 'propertyhive_documents_version', PHDOCS()->version );
	}

	/**
	 * Deactivate Property Hive Documents Add-On
	 */
	public function deactivate() {

	}

	/**
	 * Uninstall Property Hive Documents Add-On
	 */
	public function uninstall() {


	}
}

endif;

return new PH_Documents_Install();