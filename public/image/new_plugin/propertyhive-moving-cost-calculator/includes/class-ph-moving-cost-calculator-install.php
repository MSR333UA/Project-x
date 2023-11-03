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

if ( ! class_exists( 'PH_Moving_Cost_Calculator_Install' ) ) :

/**
 * PH_Moving_Cost_Calculator_Install Class
 */
class PH_Moving_Cost_Calculator_Install {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		register_activation_hook( PH_MOVING_COST_CALCULATOR_PLUGIN_FILE, array( $this, 'install' ) );
		register_deactivation_hook( PH_MOVING_COST_CALCULATOR_PLUGIN_FILE, array( $this, 'deactivate' ) );
		register_uninstall_hook( PH_MOVING_COST_CALCULATOR_PLUGIN_FILE, array( 'PH_Moving_Cost_Calculator_Install', 'uninstall' ) );

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
	    	( get_option( 'propertyhive_moving_cost_calculator_version' ) != PHMCC()->version || get_option( 'propertyhive_moving_cost_calculator_db_version' ) != PHMCC()->version ) 
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
	 * Install Property Hive Property Import Add-On
	 */
	public function install() {
        
		$this->create_options();

		$current_version = get_option( 'propertyhive_moving_cost_calculator_version', null );
		$current_db_version = get_option( 'propertyhive_moving_cost_calculator_db_version', null );

        
        update_option( 'propertyhive_moving_cost_calculator_db_version', PHMCC()->version );

        // Update version
        update_option( 'propertyhive_moving_cost_calculator_version', PHMCC()->version );
	}

	/**
	 * Deactivate Property Hive Property Import Add-On
	 */
	public function deactivate() {

		
	}

	/**
	 * Uninstall Property Hive Property Import Add-On
	 */
	public function uninstall() {


	}


	/**
	 * Default options
	 *
	 * Sets up the default options used on the settings page
	 *
	 * @access public
	 */
	public function create_options() {
	    
	    $option = array(
	    	'solicitors' => array(),
	    	'surveyors' => array(),
	    	'removal_companies' => array(),
	    	'mortgage_advisors' => array(),
	    	'financial_advisors' => array(),
	    	'third_party_email_subject' => 'New Contact Request Received From The ' . get_bloginfo('name') . ' Website',
	    	'third_party_email_body' => "Hi there,\n\nA user has completed the moving cost calculator on the " . get_bloginfo('name') . " website and selected you as a company they would like to receive contact from.\n\nPlease find details of the user below:\n\n[user_details]",
	    	'my_email_address' => get_option('admin_email', ''),
	    );

        add_option( 'propertyhive_moving_cost_calculator', $option, '', 'no' );

    }

}

endif;

return new PH_Moving_Cost_Calculator_Install();