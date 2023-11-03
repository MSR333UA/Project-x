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

if ( ! class_exists( 'PH_Frontend_Property_Submissions_Install' ) ) :

/**
 * PH_Frontend_Property_Submissions_Install Class
 */
class PH_Frontend_Property_Submissions_Install {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		register_activation_hook( PH_FRONTEND_PROPERTY_SUBMISSIONS_PLUGIN_FILE, array( $this, 'install' ) );
		register_deactivation_hook( PH_FRONTEND_PROPERTY_SUBMISSIONS_PLUGIN_FILE, array( $this, 'deactivate' ) );
		register_uninstall_hook( PH_FRONTEND_PROPERTY_SUBMISSIONS_PLUGIN_FILE, array( 'PH_Frontend_Property_Submissions_Install', 'uninstall' ) );

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
	    	( get_option( 'propertyhive_frontend_property_submissions_version' ) != PHFPS()->version || get_option( 'propertyhive_frontend_property_submissions_db_version' ) != PHFPS()->version ) 
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
	 * Install Property Hive Frontend Property Submissions Add-On
	 */
	public function install() {
        
		$this->create_options();

		$current_version = get_option( 'propertyhive_frontend_property_submissions_version', null );
		$current_db_version = get_option( 'propertyhive_frontend_property_submissions_db_version', null );
        
        update_option( 'propertyhive_frontend_property_submissions_db_version', PHFPS()->version );

        // Update version
        update_option( 'propertyhive_frontend_property_submissions_version', PHFPS()->version );
	}

	/**
	 * Deactivate Property Hive Frontend Property Submissions Add-On
	 */
	public function deactivate() {

		$timestamp = wp_next_scheduled( 'phblmexportcronhook' );
        wp_unschedule_event($timestamp, 'phblmexportcronhook' );
        wp_clear_scheduled_hook('phblmexportcronhook');

	}

	/**
	 * Uninstall Property Hive Frontend Property Submissions Add-On
	 */
	public function uninstall() {

        delete_option( 'propertyhive_frontend_property_submissions' );

	}

	/**
	 * Default options
	 *
	 * Sets up the default options used on the settings page
	 *
	 * @access public
	 */
	public function create_options() {
	    
        add_option( 'propertyhive_frontend_property_submissions', array('logged_in' => 1), '', 'no' );

    }

}

endif;

return new PH_Frontend_Property_Submissions_Install();