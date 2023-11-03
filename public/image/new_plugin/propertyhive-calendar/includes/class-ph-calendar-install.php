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

if ( ! class_exists( 'PH_Calendar_Install' ) ) :

/**
 * PH_Calendar_Install Class
 */
class PH_Calendar_Install {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		register_activation_hook( PH_CALENDAR_PLUGIN_FILE, array( $this, 'install' ) );
		register_deactivation_hook( PH_CALENDAR_PLUGIN_FILE, array( $this, 'deactivate' ) );
		register_uninstall_hook( PH_CALENDAR_PLUGIN_FILE, array( 'PH_Calendar_Install', 'uninstall' ) );

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
	    	( get_option( 'propertyhive_calendar_version' ) != PHC()->version || get_option( 'propertyhive_calendar_db_version' ) != PHC()->version ) 
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
	 * Install Property Hive Calendar Add-On
	 */
	public function install() {

		$this->create_tables();

		$current_version = get_option( 'propertyhive_calendar_version', null );
		$current_db_version = get_option( 'propertyhive_calendar_db_version', null );
        
        update_option( 'propertyhive_calendar_db_version', PHC()->version );

        // Update version
        update_option( 'propertyhive_calendar_version', PHC()->version );
	}

	/**
	 * Deactivate Property Hive Calendar Add-On
	 */
	public function deactivate() {

	}

	/**
	 * Uninstall Property Hive Calendar Add-On
	 */
	public function uninstall() {

	}

	/**
	 * Set up the database tables which the plugin needs to function.
	 *
	 * Tables:
	 *		ph_calendar_recurrence - Table description
	 *
	 * @access public
	 * @return void
	 */
	private function create_tables() {

		global $wpdb;

		$wpdb->hide_errors();

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) {
			if ( ! empty($wpdb->charset ) ) {
				$collate .= "DEFAULT CHARACTER SET $wpdb->charset";
			}
			if ( ! empty($wpdb->collate ) ) {
				$collate .= " COLLATE $wpdb->collate";
			}
		}

		// Create table to record individual feeds being ran
	   	$table_name = $wpdb->prefix . "ph_calendar_recurrence";
	      
	   	$sql = "CREATE TABLE $table_name (
					recurrence_id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
					post_id bigint(20) UNSIGNED NOT NULL,
					repeat_start int(11) UNSIGNED NOT NULL,
					repeat_year varchar(4) DEFAULT NULL,
					repeat_month varchar(2) DEFAULT NULL,
					repeat_day varchar(2) DEFAULT NULL,
					repeat_week varchar(2) DEFAULT NULL,
					repeat_weekday varchar(1) DEFAULT NULL,
				  	PRIMARY KEY (recurrence_id)
	    		) $collate;";
		
		dbDelta( $sql );

	}

}

endif;

return new PH_Calendar_Install();