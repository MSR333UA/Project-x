<?php
/**
 * Admin Tasks Dashboard
 *
 * @author      PropertyHive
 * @category    Admin
 * @package     PropertyHive/Admin
 * @version     1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
if ( ! class_exists( 'PH_Admin_Tasks_Dashboard' ) ) :

/**
 * PH_Admin_Tasks_Dashboard Class.
 */
class PH_Admin_Tasks_Dashboard {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		if ( current_user_can( 'manage_propertyhive' ) ) {
			add_action( 'wp_dashboard_setup', array( $this, 'init' ) );
		}
	}

	/**
	 * Init dashboard widgets.
	 */
	public function init() {
		wp_add_dashboard_widget( 'propertyhive_dashboard_tasks', __( 'Open Tasks Assigned To Me', 'propertyhive' ), array( $this, 'tasks_widget' ) );
	}

	/*
	 * Property Hive Tasks Widget
	 */
	public function tasks_widget()
	{
		echo '<div id="ph_dashboard_tasks">Loading...</div>';
	}
}

endif;

return new PH_Admin_Tasks_Dashboard();