<?php
/**
 * Plugin Name: Property Hive White Label Add On
 * Plugin Uri: http://wp-property-hive.com/addons/moving-cost-calculator/
 * Description: Add On for Property Hive allowing you to white label the plugin
 * Version: 1.0.2
 * Author: PropertyHive
 * Author URI: http://wp-property-hive.com
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'PH_White_Label' ) ) :

final class PH_White_Label {

    /**
     * @var string
     */
    public $version = '1.0.2';

    /**
     * @var Property Hive The single instance of the class
     */
    protected static $_instance = null;
    
    /**
     * Main Property Hive Moving Cost Calculator Instance
     *
     * Ensures only one instance of Property Hive Moving Cost Calculator is loaded or can be loaded.
     *
     * @static
     * @return Property Hive Moving Cost Calculator - Main instance
     */
    public static function instance() 
    {
        if ( is_null( self::$_instance ) ) 
        {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Constructor.
     */
    public function __construct() {
        $this->id    = 'whitelabel';
        $this->label = __( 'White Label', 'propertyhive' );

        // Define constants
        $this->define_constants();

        // Include required files
        $this->includes();

        add_action( 'admin_notices', array( $this, 'white_label_error_notices') );

        add_filter( 'propertyhive_settings_tabs_array', array( $this, 'add_settings_tab' ), 19 );
        add_action( 'propertyhive_settings_' . $this->id, array( $this, 'output' ) );
        add_action( 'propertyhive_settings_save_' . $this->id, array( $this, 'save' ) );

        add_filter( 'gettext', array( $this, 'do_white_label' ), 20, 3 );

        add_filter( 'propertyhive_screen_ids', array( $this, 'whitelabel_propertyhive_screen_ids' ) );

        add_filter( 'propertyhive_show_get_involved_settings_tab', array( $this, 'hide_get_involved_settings_tab' ) );

        add_filter( "plugin_action_links_" . plugin_basename( __FILE__ ), array( $this, 'plugin_add_settings_link' ) );
    }

    public function plugin_add_settings_link( $links )
    {
        $settings_link = '<a href="' . admin_url('admin.php?page=ph-settings&tab=whitelabel') . '">' . __( 'Settings' ) . '</a>';
        array_push( $links, $settings_link );
        return $links;
    }

    public function hide_get_involved_settings_tab( $show )
    {
        return false;
    }

    public function whitelabel_propertyhive_screen_ids( $screen_ids )
    {
        $current_settings = get_option( 'propertyhive_white_label', '' );

        if ( $current_settings != '' )
        {
            $screen_ids[] = sanitize_title($current_settings) . '_page_ph-settings';
        }

        return $screen_ids;
    }

    public function do_white_label( $translated_text, $text, $domain )
    {
        $current_settings = get_option( 'propertyhive_white_label', '' );

        if ( $current_settings != '' )
        {
            return str_replace("Property Hive", $current_settings, $translated_text);
        }
        else
        {
            return $translated_text;
        }
    }

    /**
     * Define PH White Label Constants
     */
    private function define_constants() 
    {
        define( 'PH_WHITE_LABEL_PLUGIN_FILE', __FILE__ );
        define( 'PH_WHITE_LABEL_VERSION', $this->version );
    }

    private function includes()
    {
        //include_once( 'includes/class-ph-white-label-install.php' );
    }

    /**
     * Output error message if core Property Hive plugin isn't active
     */
    public function white_label_error_notices() 
    {
        if (!is_plugin_active('propertyhive/propertyhive.php'))
        {
            $message = "The Property Hive plugin must be installed and activated before you can use the Property Hive White Label add-on";
            echo"<div class=\"error\"> <p>$message</p></div>";
        }
    }

    /**
     * Add a new settings tab to the Property Hive settings tabs array.
     *
     * @param array $settings_tabs Array of Property Hive setting tabs & their labels
     * @return array $settings_tabs Array of Property Hive setting tabs & their labels
     */
    public function add_settings_tab( $settings_tabs ) {
        $settings_tabs['whitelabel'] = __( 'White Label', 'propertyhive' );
        return $settings_tabs;
    }

    /**
     * Uses the Property Hive admin fields API to output settings.
     *
     * @uses propertyhive_admin_fields()
     * @uses self::get_settings()
     */
    public function output() {

        global $current_section, $hide_save_button;

        propertyhive_admin_fields( self::get_white_label_settings() );
    }

    /**
     * Get all the main settings for this plugin
     *
     * @return array Array of settings
     */
    public function get_white_label_settings() {

        $current_settings = get_option( 'propertyhive_white_label', '' );

        $settings = array(

            array( 'title' => __( 'White Label Settings', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'white_label_settings' ),

            array(
                'title' => __( 'White Label', 'propertyhive' ),
                'id'        => 'white_label',
                'default'   => $current_settings,
                'type'      => 'text',
                'desc_tip'  =>  false,
            ),

            array( 'type' => 'sectionend', 'id' => 'white_label_settings'),

        );

        return apply_filters( 'ph_settings_white_label', $settings );
    }

    /**
     * Uses the Property Hive options API to save settings.
     *
     * @uses propertyhive_update_options()
     * @uses self::get_settings()
     */
    public function save() {
        global $current_section, $post;

        update_option( 'propertyhive_white_label', $_POST['white_label'] );
    }
}

endif;

/**
 * Returns the main instance of PH_White_Label to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return PH_White_Label
 */
function PHWL() {
    return PH_White_Label::instance();
}

PHWL();

if( is_admin() && file_exists(  dirname( __FILE__ ) . '/propertyhive-white-label-update.php' ) )
{
    include_once( dirname( __FILE__ ) . '/propertyhive-white-label-update.php' );
}