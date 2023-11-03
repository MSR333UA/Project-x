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

if ( ! class_exists( 'PH_Email_Schedule_Install' ) ) :

/**
 * PH_Email_Schedule_Install Class
 */
class PH_Email_Schedule_Install {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		register_activation_hook( PH_EMAIL_SCHEDULE_PLUGIN_FILE, array( $this, 'install' ) );
		register_deactivation_hook( PH_EMAIL_SCHEDULE_PLUGIN_FILE, array( $this, 'deactivate' ) );
		register_uninstall_hook( PH_EMAIL_SCHEDULE_PLUGIN_FILE, array( 'PH_Email_Schedule_Install', 'uninstall' ) );

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
	    	( get_option( 'propertyhive_email_schedule_version' ) != PHES()->version || get_option( 'propertyhive_email_schedule_db_version' ) != PHES()->version ) 
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
	 * Install Property Hive Email Schedule Add-On
	 */
	public function install() {

		$this->create_options();
		$this->create_cron();

		$current_version = get_option( 'propertyhive_email_schedule_version', null );
		$current_db_version = get_option( 'propertyhive_email_schedule_db_version', null );
        
        update_option( 'propertyhive_email_schedule_db_version', PHES()->version );

        // Update version
        update_option( 'propertyhive_email_schedule_version', PHES()->version );
	}

	/**
	 * Deactivate Property Hive Email Schedule Add-On
	 */
	public function deactivate() {

		$timestamp = wp_next_scheduled( 'phemailschedulecronhook' );
        wp_unschedule_event($timestamp, 'phemailschedulecronhook' );
        wp_clear_scheduled_hook('phemailschedulecronhook');

	}

	/**
	 * Uninstall Property Hive Email Schedule Add-On
	 */
	public function uninstall() {

		$timestamp = wp_next_scheduled( 'phemailschedulecronhook' );
        wp_unschedule_event($timestamp, 'phemailschedulecronhook' );
        wp_clear_scheduled_hook('phemailschedulecronhook');

        delete_option( 'propertyhive_emailschedule' );

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
	 * Setup cron
	 *
	 * Sets up the automated cron to send email schedule
	 *
	 * @access public
	 */
	public function create_cron() {
	    
        $timestamp = wp_next_scheduled( 'phemailschedulecronhook' );
        wp_unschedule_event($timestamp, 'phemailschedulecronhook' );
        wp_clear_scheduled_hook('phemailschedulecronhook');

        $next_schedule = time() - 60;
        wp_schedule_event( $next_schedule, 'hourly', 'phemailschedulecronhook' );

    }

}

endif;

return new PH_Email_Schedule_Install();