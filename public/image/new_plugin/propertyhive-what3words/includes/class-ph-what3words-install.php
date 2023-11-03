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

if ( ! class_exists( 'PH_What3words_Install' ) ) :

/**
 * PH_What3words_Install Class
 */
class PH_What3words_Install {

    /**
     * Hook in tabs.
     */
    public function __construct() {
        register_activation_hook( PH_WHAT3WORDS_PLUGIN_FILE, array( $this, 'install' ) );
        register_deactivation_hook( PH_WHAT3WORDS_PLUGIN_FILE, array( $this, 'deactivate' ) );
        register_uninstall_hook( PH_WHAT3WORDS_PLUGIN_FILE, array( 'PH_What3words_Install', 'uninstall' ) );

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
            ( get_option( 'propertyhive_what3words_version' ) != PHW3W()->version || get_option( 'propertyhive_what3words_db_version' ) != PHW3W()->version )
        ) {
            $this->install();
        }
    }

    /**
     * Deactivate Property Hive what3words Add-On
     */
    public function deactivate() {

    }

    /**
     * Uninstall Property Hive what3words Add-On
     */
    public function uninstall() {

    }

    /**
     * Install actions
     */
    public function install_actions() {

    }

    /**
     * Install Property Hive what3words Add-On
     */
    public function install() {

        $this->create_options();

        update_option( 'propertyhive_what3words_db_version', PHW3W()->version );

        // Update version
        update_option( 'propertyhive_what3words_version', PHW3W()->version );
    }

    /**
     * Default options
     *
     * Sets up the default options used on the settings page
     *
     * @access public
     */
    public function create_options() {

        // Create custom location defaults
        $propertyhive_what3words = array(
            'api_key' => '',
            'location_types' => array(
                1 => array(
                    'name'   => 'Front Entrance',
                    'colour' => '#FCB43A',
                ),
                2 => array(
                    'name'   => 'Parking Area',
                    'colour' => '#23212C',
                ),
            ),
        );
        add_option( 'propertyhive_what3words', $propertyhive_what3words );
    }
}

endif;

return new PH_What3words_Install();