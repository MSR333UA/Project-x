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

if ( ! class_exists( 'PH_Property_Portal_Install' ) ) :

/**
 * PH_Property_Portal_Install Class
 */
class PH_Property_Portal_Install {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		register_activation_hook( PH_PROPERTYPORTAL_PLUGIN_FILE, array( $this, 'install' ) );
		register_deactivation_hook( PH_PROPERTYPORTAL_PLUGIN_FILE, array( $this, 'deactivate' ) );
		register_uninstall_hook( PH_PROPERTYPORTAL_PLUGIN_FILE, array( 'PH_Property_Portal_Install', 'uninstall' ) );

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
	    	( get_option( 'propertyhive_property_portal_version' ) != PHPP()->version || get_option( 'propertyhive_property_portal_db_version' ) != PHPP()->version ) 
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
	 * Install Property Hive Property Portal Add-On
	 */
	public function install() {

		$this->create_cron();

		$current_version = get_option( 'propertyhive_property_portal_version', null );
		$current_db_version = get_option( 'propertyhive_property_portal_db_version', null );
        
        update_option( 'propertyhive_property_portal_db_version', PHPP()->version );

        // Update version
        update_option( 'propertyhive_property_portal_version', PHPP()->version );

        $this->create_roles();
	}

	/**
	 * Deactivate Property Hive Property Portal Add-On
	 */
	public function deactivate() {

		$timestamp = wp_next_scheduled( 'phpropertyportalcronhook' );
		wp_unschedule_event($timestamp, 'phpropertyportalcronhook' );
		wp_clear_scheduled_hook('phpropertyportalcronhook');

	}

	/**
	 * Uninstall Property Hive Property Portal Add-On
	 */
	public function uninstall() {

		$timestamp = wp_next_scheduled( 'phpropertyportalcronhook' );
		wp_unschedule_event($timestamp, 'phpropertyportalcronhook' );
		wp_clear_scheduled_hook('phpropertyportalcronhook');

	}

	/**
	 * Setup cron
	 *
	 * Sets up the automated cron to generate _branches post_metas
	 *
	 * @access public
	 */
	public function create_cron() {

        $timestamp = wp_next_scheduled( 'phpropertyportalcronhook' );
        wp_unschedule_event($timestamp, 'phpropertyportalcronhook' );
        wp_clear_scheduled_hook('phpropertyportalcronhook');

        $next_schedule = time() - 60;
        wp_schedule_event( $next_schedule, 'weekly', 'phpropertyportalcronhook' );

    }

	/**
	 * Create roles and capabilities
	 */
	public function create_roles() {
        // Property Hive Agent role
        add_role( 'property_hive_agent', __( 'Property Hive Agent', 'propertyhive' ), array(
            'read' => true,
        ) );
	}

}

endif;

return new PH_Property_Portal_Install();