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

if ( ! class_exists( 'PH_Zooplarealtimefeed_Install' ) ) :

/**
 * PH_Zooplarealtimefeed_Install Class
 */
class PH_Zooplarealtimefeed_Install {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		register_activation_hook( PH_ZOOPLAREALTIMEFEED_PLUGIN_FILE, array( $this, 'install' ) );
		register_deactivation_hook( PH_ZOOPLAREALTIMEFEED_PLUGIN_FILE, array( $this, 'deactivate' ) );
		register_uninstall_hook( PH_ZOOPLAREALTIMEFEED_PLUGIN_FILE, array( 'PH_Zooplarealtimefeed_Install', 'uninstall' ) );

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
	    	( get_option( 'propertyhive_zooplarealtimefeed_version' ) != PHZRTF()->version || get_option( 'propertyhive_zooplarealtimefeed_db_version' ) != PHZRTF()->version ) 
	    ) {
			$this->install();
		}
	}

	/**
	 * Deactivate Property Hive RTDF Add-On
	 */
	public function deactivate() {

		$timestamp = wp_next_scheduled( 'phzooplarealtimefeedcronhook' );
        wp_unschedule_event($timestamp, 'phzooplarealtimefeedcronhook' );
        wp_clear_scheduled_hook('phzooplarealtimefeedcronhook');

	}

	/**
	 * Uninstall Property Hive RTDF Add-On
	 */
	public function uninstall() {

		$timestamp = wp_next_scheduled( 'phzooplarealtimefeedcronhook' );
        wp_unschedule_event($timestamp, 'phzooplarealtimefeedcronhook' );
        wp_clear_scheduled_hook('phzooplarealtimefeedcronhook');

        delete_option( 'propertyhive_zooplarealtimefeed' );

        $this->delete_tables();

	}

	public function delete_tables() {

		global $wpdb;

		$wpdb->hide_errors();

		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}ph_zooplarealtimefeed_logs_error" );
	}

	/**
	 * Install actions
	 */
	public function install_actions() {



	}

	/**
	 * Install Property Hive Real-Time Feed Add-On
	 */
	public function install() {
        
		$this->create_options();
		$this->create_tables();
		$this->create_cron();

		$current_version = get_option( 'propertyhive_zooplarealtimefeed_version', null );
		$current_db_version = get_option( 'propertyhive_zooplarealtimefeed_db_version', null );
        
        update_option( 'propertyhive_zooplarealtimefeed_db_version', PHZRTF()->version );

        // Update version
        update_option( 'propertyhive_zooplarealtimefeed_version', PHZRTF()->version );
	}

	/**
	 * Setup cron
	 *
	 * Sets up the automated cron to reconcile properties
	 *
	 * @access public
	 */
	public function create_cron() {
	    
        $timestamp = wp_next_scheduled( 'phzooplarealtimefeedcronhook' );
        wp_unschedule_event($timestamp, 'phzooplarealtimefeedcronhook' );
        wp_clear_scheduled_hook('phzooplarealtimefeedcronhook');
        
        $next_schedule = time() - 60;
        wp_schedule_event( $next_schedule, 'twicedaily', 'phzooplarealtimefeedcronhook' );

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
	 * Set up the database tables which the plugin needs to function.
	 *
	 * Tables:
	 *		propertyhive_blmexport_table_name - Table description
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

		$sql = '';

		// Create table to record individual feeds being ran
	   	$table_name = $wpdb->prefix . "ph_zooplarealtimefeed_logs_error";
	      
	   	$sql .= "CREATE TABLE $table_name (
					id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
					portal_id bigint(20) UNSIGNED NOT NULL,
					post_id bigint(20) UNSIGNED NOT NULL,
					severity tinyint(1) UNSIGNED NOT NULL,
					message varchar(255) NOT NULL,
					request text,
					response text,
					error_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
				  	PRIMARY KEY  (id)
	    		) $collate;";
		
		dbDelta( $sql );

	}

}

endif;

return new PH_Zooplarealtimefeed_Install();