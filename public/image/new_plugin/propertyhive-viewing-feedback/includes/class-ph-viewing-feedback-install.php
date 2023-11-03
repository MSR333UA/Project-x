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

if ( ! class_exists( 'PH_Viewing_Feedback_Install' ) ) :

/**
 * PH_Viewing_Feedback_Install Class
 */
class PH_Viewing_Feedback_Install {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		register_activation_hook( PH_VIEWING_FEEDBACK_REQUEST_PLUGIN_FILE, array( $this, 'install' ) );
		register_deactivation_hook( PH_VIEWING_FEEDBACK_REQUEST_PLUGIN_FILE, array( $this, 'deactivate' ) );
		register_uninstall_hook( PH_VIEWING_FEEDBACK_REQUEST_PLUGIN_FILE, array( 'PH_Viewing_Feedback_Install', 'uninstall' ) );

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
	    	( get_option( 'propertyhive_viewing_feedback_version' ) != PHVFR()->version || get_option( 'propertyhive_viewing_feedback_db_version' ) != PHVFR()->version ) 
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
	 * Install Property Hive Viewing Feedback Add-On
	 */
	public function install() {

		$this->create_options();
        
		$current_version = get_option( 'propertyhive_viewing_feedback_version', null );
		$current_db_version = get_option( 'propertyhive_viewing_feedback_db_version', null );
        
        update_option( 'propertyhive_viewing_feedback_version', PHVFR()->version );

        update_option( 'propertyhive_viewing_feedback_db_version', PHVFR()->version );
	}

	/**
	 * Deactivate Property Hive Viewing Feedback Add-On
	 */
	public function deactivate() {

	}

	/**
	 * Uninstall Property Hive Viewing Feedback Add-On
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
		
		$subject = 'What Did You Think Of [property_address]?';

		add_option( 'propertyhive_viewing_feedback_request_email_subject', $subject );

		$body = "Hello [applicant_dear],

Following your recent viewing at [property_address] we'd love to hear your thoughts, good or bad, about the property. 

Please visit the link below to leave your comments:

[feedback_url]

Regards, 

" . get_bloginfo('name');	

		add_option( 'propertyhive_viewing_feedback_request_email_body', $body );	

    }
}

endif;

return new PH_Viewing_Feedback_Install();