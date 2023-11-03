<?php
/**
 * Plugin Name: Property Hive Postcode Lookup Add On
 * Plugin Uri: http://wp-property-hive.com/addons/postcode-lookup/
 * Description: Add On for Property Hive allowing you to quickly and accurately enter property addresses
 * Version: 1.0.3
 * Author: PropertyHive
 * Author URI: http://wp-property-hive.com
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'PH_Postcode_Lookup' ) ) :

final class PH_Postcode_Lookup {

    /**
     * @var string
     */
    public $version = '1.0.3';

    /**
     * @var Property Hive The single instance of the class
     */
    protected static $_instance = null;
    
    /**
     * Main Property Hive Postcode Lookup Instance
     *
     * Ensures only one instance of Property Hive Postcode Lookup is loaded or can be loaded.
     *
     * @static
     * @return Property Hive Postcode Lookup - Main instance
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

        $this->id    = 'postcodelookup';
        $this->label = __( 'Postcode Lookup', 'propertyhive' );

        // Define constants
        $this->define_constants();

        // Include required files
        $this->includes();

        add_action( 'admin_notices', array( $this, 'postcode_lookup_error_notices') );

        add_filter( "plugin_action_links_" . plugin_basename( __FILE__ ), array( $this, 'plugin_add_settings_link' ) );

        add_filter( 'propertyhive_settings_tabs_array', array( $this, 'add_settings_tab' ), 19 );
        add_action( 'propertyhive_settings_' . $this->id, array( $this, 'output' ) );
        add_action( 'propertyhive_settings_save_' . $this->id, array( $this, 'save' ) );

        add_action( 'admin_enqueue_scripts', array( $this, 'load_postcode_lookup_scripts' ) );
    }

    public function plugin_add_settings_link( $links )
    {
        $settings_link = '<a href="' . admin_url('admin.php?page=ph-settings&tab=postcodelookup') . '">' . __( 'Settings' ) . '</a>';
        array_push( $links, $settings_link );
        return $links;
    }

    /**
     * Define PH Postcode Lookup Constants
     */
    private function define_constants() 
    {
        define( 'PH_POSTCODE_LOOKUP_PLUGIN_FILE', __FILE__ );
        define( 'PH_POSTCODE_LOOKUP_VERSION', $this->version );
    }

    private function includes()
    {
        //include_once( dirname( __FILE__ ) . "/includes/class-ph-postcode-lookup-install.php" );
    }

    /**
     * Output error message if core Property Hive plugin isn't active
     */
    public function postcode_lookup_error_notices() 
    {
        if (!is_plugin_active('propertyhive/propertyhive.php'))
        {
            $message = __( "The Property Hive plugin must be installed and activated before you can use the Property Hive Postcode Lookup add-on", 'propertyhive' );
            echo"<div class=\"error\"> <p>$message</p></div>";
        }
    }

    public function load_postcode_lookup_scripts() 
    {
        $screen = get_current_screen();

        $current_settings = get_option( 'propertyhive_postcode_lookup', array() );

        if ( ( $screen->id == 'property' || $screen->id == 'contact' ) && $screen->action == 'add' && isset($current_settings['service']) && $current_settings['service'] != '' )
        {
            // Only include script on 'Add New Property' screen
            
            $assets_path = str_replace( array( 'http:', 'https:' ), '', untrailingslashit( plugins_url( '/', __FILE__ ) ) ) . '/assets/';

            wp_register_script( 
                'ph-postcode-lookup', 
                $assets_path . 'js/ph-postcode-lookup.js', 
                array(), 
                PH_POSTCODE_LOOKUP_VERSION,
                true
            );
            wp_enqueue_script('ph-postcode-lookup');

            $params = array(
                'service'              => $current_settings['service'],
                'api_key'              => $current_settings['api_key'],
            );
            wp_localize_script( 'ph-postcode-lookup', 'propertyhive_postcode_lookup', $params );
        }
    }

    /**
     * Add a new settings tab to the Property Hive settings tabs array.
     *
     * @param array $settings_tabs Array of Property Hive setting tabs & their labels, excluding the Subscription tab.
     * @return array $settings_tabs Array of Property Hive setting tabs & their labels, including the Subscription tab.
     */
    public function add_settings_tab( $settings_tabs ) {
        $settings_tabs[$this->id] = $this->label;
        return $settings_tabs;
    }

    /**
     * Uses the Property Hive admin fields API to output settings.
     *
     * @uses propertyhive_admin_fields()
     * @uses self::get_settings()
     */
    public function output() {

        global $current_section;
        
        propertyhive_admin_fields( self::get_postcode_lookup_settings() );
    }

    /**
     * Uses the Property Hive options API to save settings.
     *
     * @uses propertyhive_update_options()
     * @uses self::get_settings()
     */
    public function save() {

        $propertyhive_postcode_lookup = array(
            'service' => ( (isset($_POST['service'])) ? $_POST['service'] : '' ),
            'api_key' => ( (isset($_POST['api_key'])) ? $_POST['api_key'] : '' ),
        );

        update_option( 'propertyhive_postcode_lookup', $propertyhive_postcode_lookup );
    }

    /**
     * Get postcode lookup settings
     *
     * @return array Array of settings
     */
    public function get_postcode_lookup_settings() {

        $current_settings = get_option( 'propertyhive_postcode_lookup', array() );

        $settings = array(

            array( 'title' => __( 'Postcode Lookup Settings', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'postcode_lookup_settings' )

        );

        $settings[] = array(
            'title'     => __( 'Postcode Lookup Service', 'propertyhive' ),
            'id'        => 'service',
            'type'      => 'radio',
            'default'   => ( isset($current_settings['service']) ? $current_settings['service'] : ''),
            'options'   => array(
                '' => __( 'None', 'propertyhive' ),
                'Postcode Anywhere' => __( 'Postcode Anywhere', 'propertyhive' ) . ' <small>(<a href="http://www.pcapredict.com/en-gb/address-capture-software/" target="_blank">http://www.pcapredict.com/</a>)</small>',
                'getAddress' => __( 'getAddress()', 'propertyhive' ) . ' <small>(<a href="https://getaddress.io/" target="_blank">https://getaddress.io/</a>)</small>',
                'IdealPostcodes' => __( 'Ideal Postcodes', 'propertyhive' ) . ' <small>(<a href="https://ideal-postcodes.co.uk" target="_blank">https://ideal-postcodes.co.uk</a>)</small>',
                'Google Geocoding' => __( 'Google Geocoding', 'propertyhive' ) . ' <small>(<a href="https://developers.google.com/maps/documentation/geocoding/start" target="_blank">https://developers.google.com/maps/documentation/geocoding/start</a>)</small>',
            ),
        );

        $settings[] = array(
            'title'     => __( 'API Key', 'propertyhive' ),
            'id'        => 'api_key',
            'type'      => 'text',
            'default'   => ( isset($current_settings['api_key']) ? $current_settings['api_key'] : ''),
        );

        $settings[] = array(
            'type'      => 'html',
            'html'      => "<script>
                jQuery(document).ready(function()
                {
                    jQuery('#api_key').after('<span id=\"google_tip\">We\'ll use your Google Maps API Key entered <a href=\"" . admin_url('admin.php?page=ph-settings&tab=general&section=map') . "\" target=\"_blank\">here</a>. More information about setting up the key and debugging can be found <a href=\"https://docs.wp-property-hive.com/user-guide/maps-co-ordinates-and-geocoding/\" target=\"_blank\">here</a>.</span>');

                    toggle_api_key();

                    jQuery('input[name=\'service\']').change(function()
                    {
                        toggle_api_key();
                    });
                });

                function toggle_api_key()
                {
                    if ( jQuery('input[name=\'service\']:checked').val() == 'Google Geocoding' )
                    {
                        jQuery('#api_key').hide();
                        jQuery('#google_tip').show();
                    }
                    else
                    {
                        jQuery('#api_key').show();
                        jQuery('#google_tip').hide();
                    }
                }
            </script>",
        );

        $settings[] = array( 'type' => 'sectionend', 'id' => 'postcode_lookup_settings');

        return $settings;
    }
}

endif;

/**
 * Returns the main instance of PH_Postcode_Lookup to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return PH_Postcode_Lookup
 */
function PHPCL() {
    return PH_Postcode_Lookup::instance();
}

PHPCL();

if( is_admin() && file_exists(  dirname( __FILE__ ) . '/propertyhive-postcode-lookup-update.php' ) )
{
    include_once( dirname( __FILE__ ) . '/propertyhive-postcode-lookup-update.php' );
}