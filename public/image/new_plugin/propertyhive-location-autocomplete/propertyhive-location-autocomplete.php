<?php
/**
 * Plugin Name: Property Hive Location Autocomplete Add On
 * Plugin Uri: http://wp-property-hive.com/addons/locrating/
 * Description: Add On for Property Hive that adds autocomplete functionality to address keyword fields in search forms
 * Version: 1.0.1
 * Author: PropertyHive
 * Author URI: http://wp-property-hive.com
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'PH_Location_Autocomplete' ) ) :
final class PH_Location_Autocomplete {


    /**
     * @var string
     */
    public $version = '1.0.1';

    /**
     * @var Property Hive The single instance of the class
     */
    protected static $_instance = null;
    
    /**
     * Main Property Hive Location Autocomplete Instance
     *
     * Ensures only one instance of Property Hive Location Autocomplete is loaded or can be loaded.
     *
     * @static
     * @return Property Hive Location Autocomplete - Main instance
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

        $this->id    = 'locationautocomplete';
        $this->label = __( 'Location Autocomplete', 'propertyhive' );

        // Define constants
        $this->define_constants();

        // Include required files
        $this->includes();

        add_action( 'admin_notices', array( $this, 'location_autocomplete_error_notices') );

        add_filter( "plugin_action_links_" . plugin_basename( __FILE__ ), array( $this, 'plugin_add_settings_link' ) );

        add_filter( 'propertyhive_settings_tabs_array', array( $this, 'add_settings_tab' ), 19 );
        add_action( 'propertyhive_settings_' . $this->id, array( $this, 'output' ) );
        add_action( 'propertyhive_settings_save_' . $this->id, array( $this, 'save' ) );

        add_action( 'phlocationautocompletecronhook', array( $this, 'location_autocomplete_cron' ) );

        add_action( 'wp_enqueue_scripts', array( $this, 'load_location_autocomplete_scripts' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'load_location_autocomplete_styles' ) );

        add_filter( 'propertyhive_google_maps_api_params', array( $this, 'google_maps_api_params' ) );
    }

    public function google_maps_api_params( $params = array() )
    {
        $current_settings = get_option( 'propertyhive_location_autocomplete', array() );

        if ( isset($current_settings['data_source']) && $current_settings['data_source'] == 'google' )
        {
            $api_key = get_option('propertyhive_google_maps_api_key', '');
            if ( $api_key != '' )
            {
                $params['key'] = $api_key;
            }

            if ( !isset($params['libraries']) )
            {
                $params['libraries'] = array();
            }
            $params['libraries'][] = 'places';

            $params['callback'] = 'init_location_autocomplete';
        }

        return $params;
    }

    public function load_location_autocomplete_scripts() {

        $current_settings = get_option( 'propertyhive_location_autocomplete', array() );

        $assets_path = str_replace( array( 'http:', 'https:' ), '', untrailingslashit( plugins_url( '/', __FILE__ ) ) ) . '/assets/';

        $location_values = array();

        if ( isset($current_settings['data_source']) && $current_settings['data_source'] == 'google' )
        {
            $params = array();
            $params = apply_filters( 'propertyhive_google_maps_api_params', $params );

            if ( isset($params['libraries']) && is_array($params['libraries']) && !empty($params['libraries']) ) { $params['libraries'] = join(",", $params['libraries']); }

            wp_register_script( 
                'googlemaps', 
                '//maps.googleapis.com/maps/api/js?' . http_build_query($params), 
                array(), 
                '3', 
                true 
            );
        }
        elseif ( isset($current_settings['data_source']) && $current_settings['data_source'] == 'manual' )
        {
            $location_values = get_option( 'propertyhive_location_autocomplete_manual', array() );
        }
        else
        {
            $location_values = get_option( 'propertyhive_location_autocomplete_data', array() );
        }

        wp_register_script( 
            'ph-location-autocomplete', 
            $assets_path . 'js/ph-location-autocomplete.js', 
            array(), 
            PH_LOCATION_AUTOCOMPLETE_VERSION,
            true
        );
        wp_enqueue_script( 'ph-location-autocomplete' );

        wp_localize_script( 'ph-location-autocomplete', 'location_autocomplete_object', array( 
            'data_source' => isset($current_settings['data_source']) ? $current_settings['data_source'] : '',
            'location_values' => is_array($location_values) ? $location_values : array(),
            'country' => strtolower(get_option('propertyhive_default_country', 'GB'))
        ) );

        if ( isset($current_settings['data_source']) && $current_settings['data_source'] == 'google' )
        {
            wp_enqueue_script( 'googlemaps' );
        }
    }

    public function load_location_autocomplete_styles() {

        $current_settings = get_option( 'propertyhive_location_autocomplete', array() );

        $assets_path = str_replace( array( 'http:', 'https:' ), '', untrailingslashit( plugins_url( '/', __FILE__ ) ) ) . '/assets/';

        wp_register_style( 
            'ph-location-autocomplete', 
            $assets_path . 'css/ph-location-autocomplete.css', 
            array(), 
            PH_LOCATION_AUTOCOMPLETE_VERSION
        );
        wp_enqueue_style( 'ph-location-autocomplete' );
    }

    public function plugin_add_settings_link( $links )
    {
        $settings_link = '<a href="' . admin_url('admin.php?page=ph-settings&tab=locationautocomplete') . '">' . __( 'Settings' ) . '</a>';
        array_push( $links, $settings_link );
        return $links;
    }

    /**
     * Define PH Location Autocomplete Constants
     */
    private function define_constants() 
    {
        define( 'PH_LOCATION_AUTOCOMPLETE_PLUGIN_FILE', __FILE__ );
        define( 'PH_LOCATION_AUTOCOMPLETE_VERSION', $this->version );
    }

    private function includes()
    {
        include_once( dirname( __FILE__ ) . "/includes/class-ph-location-autocomplete-install.php" );
    }

    /**
     * Output error message if core Property Hive plugin isn't active
     */
    public function location_autocomplete_error_notices() 
    {
        if (!is_plugin_active('propertyhive/propertyhive.php'))
        {
            $message = __( "The Property Hive plugin must be installed and activated before you can use the Property Hive Location Autocomplete add-on", 'propertyhive' );
            echo"<div class=\"error\"> <p>$message</p></div>";
        }
    }

    /**
     * Add a new settings tab to the Property Hive settings tabs array.
     *
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
        
        propertyhive_admin_fields( self::get_location_autocomplete_settings() );
    }

    /**
     * Uses the Property Hive options API to save settings.
     *
     * @uses propertyhive_update_options()
     * @uses self::get_settings()
     */
    public function save() {

        $existing_propertyhive_location_autocomplete = get_option( 'propertyhive_location_autocomplete', array() );

        $propertyhive_location_autocomplete = array(
            'data_source' => ( isset($_POST['data_source']) ? $_POST['data_source'] : 'data' ),
        );

        $propertyhive_location_autocomplete = array_merge( $existing_propertyhive_location_autocomplete, $propertyhive_location_autocomplete );

        update_option( 'propertyhive_location_autocomplete', $propertyhive_location_autocomplete );

        if ( isset($_POST['data_source']) && $_POST['data_source'] == 'manual' )
        {
            $location_values = '';
            if ( isset($_POST['manual_locations']) && $_POST['manual_locations'] != '' )
            {
                $location_values = explode("\n", $_POST['manual_locations']);
            }
            update_option( 'propertyhive_location_autocomplete_manual', $location_values, false );
        }

        do_action( 'phlocationautocompletecronhook' );
    }

    /**
     * Get location autocomplete settings
     *
     * @return array Array of settings
     */
    public function get_location_autocomplete_settings() {

        $current_settings = get_option( 'propertyhive_location_autocomplete', array() );
        $manual_data = get_option( 'propertyhive_location_autocomplete_manual', array() );

        $settings = array(

            array( 'title' => __( 'Location Autocomplete Settings', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'location_autocomplete_settings' )

        );

        $settings[] = array(
            'title'     => __( 'Autocomplete Data Source', 'propertyhive' ),
            'id'        => 'data_source',
            'type'      => 'radio',
            'default'   => ( isset($current_settings['data_source']) ? $current_settings['data_source'] : ''),
            'options'   => array(
                '' => __( 'Use Existing Property Address Data', 'propertyhive' ),
                'google' => __( 'Google Places', 'propertyhive' ),
                'manual' => __( 'Manually Manage Locations', 'propertyhive' ),
            ),
        );

        $settings[] = array(
            'id'        => 'existing_data_information',
            'type'      => 'html',
            'html'      => '<p>Property Hive will compile a unique list of all the streets, localities, towns, counties and postcodes that will then show as available autocomplete options.</p>'
        );

        $settings[] = array(
            'id'        => 'places_api_information',
            'type'      => 'html',
            'html'      => '<p>Please ensure the API key entered into the <a href="' . admin_url('admin.php?page=ph-settings&tab=general&section=map') . '" target="_blank">Property Hive settings area</a> has the <a href="https://developers.google.com/places/web-service/intro" target="_blank">Places API</a> enabled.</p>
            <p>Please also ensure you familiarise yourself with the <a href="https://developers.google.com/places/web-service/policies" target="_blank">policies surrounding usage of the Places API</a>.</p>
            <p>Works best with our <a href="https://wp-property-hive.com/addons/radial-search/" target="_blank">Radial Search add on</a>.</p>'
        );

        $settings[] = array(
            'title'     => __( 'Locations', 'propertyhive' ),
            'id'        => 'manual_locations',
            'type'      => 'textarea',
            'css'       => 'min-width:300px; height:110px;',
            'default'   => ( is_array($manual_data) && !empty($manual_data) ? implode("\n", $manual_data) : ''),
            'desc_tip'  =>  true,
            'desc'      => __( 'Enter each location on a new line', 'propertyhive' ),
            'custom_attributes' => array(
                'placeholder' => "Location One\nLocation Two"
            )
        );

        $settings[] = array(
            'id'        => 'custom_js',
            'type'      => 'html',
            'html'      => '<script>
                jQuery(document).ready(function()
                {
                    jQuery(\'input[name=data_source][type=radio]\').change(function() { ph_toggle_location_autocomplete_options(); });

                    ph_toggle_location_autocomplete_options();
                });

                function ph_toggle_location_autocomplete_options()
                {
                    var selected_val = jQuery(\'input[name=data_source][type=radio]:checked\').val();

                    jQuery(\'#row_existing_data_information\').hide();
                    jQuery(\'#row_places_api_information\').hide();
                    jQuery(\'#row_manual_locations\').hide();

                    if ( selected_val == \'\' )
                    {
                        jQuery(\'#row_existing_data_information\').show();
                    }
                    if ( selected_val == \'google\' )
                    {
                        jQuery(\'#row_places_api_information\').show();
                    }
                    if ( selected_val == \'manual\' )
                    {
                        jQuery(\'#row_manual_locations\').show();
                    }
                }
            </script>'
        );

        $settings[] = array( 'type' => 'sectionend', 'id' => 'location_autocomplete_settings');

        return $settings;
    }

    public function location_autocomplete_cron()
    {
        $existing_settings = get_option( 'propertyhive_location_autocomplete', array() );

        if ( !isset($existing_settings['data_source']) || ( isset($existing_settings['data_source']) && $existing_settings['data_source'] == '' ) )
        {
            // Need to scan all properties get unique street, address line 2, town, county and postcodes (XX0, XX0 0, XX0 0XX)
            $location_values = array();

            $args = array(
                'post_type' => 'property',
                'nopaging' => TRUE,
                'fields' => 'ids',
                'post_status' => 'publish'
            );

            $properties_query = new WP_Query( $args );

            if ( $properties_query->have_posts() )
            {
                while ( $properties_query->have_posts() )
                {
                    $properties_query->the_post();

                    $property = new PH_Property( get_the_ID() );

                    if ( ph_clean($property->_address_street) != '' && preg_match('/\\d/', ph_clean($property->_address_street)) == 0 ) { $location_values[] = ph_clean($property->_address_street); }
                    if ( ph_clean($property->_address_two) != '' && preg_match('/\\d/', ph_clean($property->_address_two)) == 0 ) { $location_values[] = ph_clean($property->_address_two); }
                    if ( ph_clean($property->_address_three) != '' ) { $location_values[] = ph_clean($property->_address_three); }
                    if ( ph_clean($property->_address_four) != '' ) { $location_values[] = ph_clean($property->_address_four); }
                    if ( ph_clean($property->_address_postcode) != '' ) 
                    { 
                        $location_values[] = ph_clean($property->_address_postcode); 

                        $explode_postcode = explode(" ", ph_clean($property->_address_postcode));
                        if ( count($explode_postcode) == 2 )
                        {
                            $location_values[] = $explode_postcode[0];
                            $location_values[] = $explode_postcode[0] . ' ' . substr($explode_postcode[1], 0, 1);
                        }
                    }
                }
            }
            wp_reset_postdata();

            $location_values = array_unique($location_values);
            $location_values = array_filter($location_values);
            sort($location_values);

            update_option( 'propertyhive_location_autocomplete_data', $location_values, false );
        }
    }
}

endif;

/**
 * Returns the main instance of PH_Location_Autocomplete to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return PH_Location_Autocomplete
 */
function PHLA() {
    return PH_Location_Autocomplete::instance();
}

$PHLA = PHLA();

if( is_admin() && file_exists(  dirname( __FILE__ ) . '/propertyhive-location-autocomplete-update.php' ) )
{
    include_once( dirname( __FILE__ ) . '/propertyhive-location-autocomplete-update.php' );
}