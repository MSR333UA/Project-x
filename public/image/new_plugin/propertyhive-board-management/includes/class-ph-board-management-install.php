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

if ( ! class_exists( 'PH_Board_Management_Install' ) ) :

/**
 * PH_Board_Management_Install Class
 */
class PH_Board_Management_Install {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		register_activation_hook( PH_BOARD_MANAGEMENT_PLUGIN_FILE, array( $this, 'install' ) );
		register_deactivation_hook( PH_BOARD_MANAGEMENT_PLUGIN_FILE, array( $this, 'deactivate' ) );
		register_uninstall_hook( PH_BOARD_MANAGEMENT_PLUGIN_FILE, array( 'PH_Board_Management_Install', 'uninstall' ) );

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
	    	( get_option( 'propertyhive_board_management_version' ) != PHBM()->version || get_option( 'propertyhive_board_management_db_version' ) != PHBM()->version ) 
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
	 * Install Property Hive BLM Export Add-On
	 */
	public function install() {
        
		///$this->create_options();
		//$this->create_cron();
		//$this->create_tables();
		$this->create_terms();

		$current_version = get_option( 'propertyhive_board_management_version', null );
		$current_db_version = get_option( 'propertyhive_board_management_db_version', null );
        
        update_option( 'propertyhive_board_management_db_version', PHBM()->version );

        // Update version
        update_option( 'propertyhive_board_management_version', PHBM()->version );
	}

	/**
	 * Deactivate Property Hive BLM Export Add-On
	 */
	public function deactivate() {

		

	}

	/**
	 * Uninstall Property Hive BLM Export Add-On
	 */
	public function uninstall() {

		

	}

	public function create_terms() {

		$args = array(
            'hide_empty' => 0
        );
        $terms = get_terms( 'board_status', $args );

        if ( empty($terms) )
        {
        	// Only create default set of terms if none exist already
			$terms = array(
				'For Sale',
				'Sold STC',
				'Sold',
				'To Let',
				'Let'
			);
			foreach ( $terms as $term ) 
			{
				if ( ! get_term_by( 'slug', sanitize_title( $term ), 'board_status' ) ) 
				{
					wp_insert_term( $term, 'board_status' );
				}
			}
		}
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

return new PH_Board_Management_Install();