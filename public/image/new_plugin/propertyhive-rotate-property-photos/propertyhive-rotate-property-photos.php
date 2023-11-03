<?php
/**
 * Plugin Name: Property Hive Rotate Property Photos Add On
 * Plugin Uri: http://wp-property-hive.com/addons/rotate-property-photos/
 * Description: Add On for Property Hive which provides the ability to set automatic rotation of property photos
 * Version: 1.0.0
 * Author: PropertyHive
 * Author URI: http://wp-property-hive.com
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'PH_Rotate_Property_Photos' ) ) :

final class PH_Rotate_Property_Photos {

    /**
     * @var string
     */
    public $version = '1.0.0';

    /**
     * @var PropertyHive The single instance of the class
     */
    protected static $_instance = null;
    
    /**
     * Main Property Hive Rotate Property Photos Instance
     *
     * Ensures only one instance of Property Hive Rotate Property Photos is loaded or can be loaded.
     *
     * @static
     * @return Property Hive Rotate Property Photos - Main instance
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

        $this->id    = 'rotate-property-photos';
        $this->label = __( 'Rotate Property Photos', 'propertyhive' );

        // Define constants
        $this->define_constants();

        // Include required files
        $this->includes();

        add_action( 'admin_init', array( $this, 'run_custom_rotate_photos_cron') );

        add_action( 'admin_notices', array( $this, 'rotate_property_photos_error_notices') );

        add_filter( 'propertyhive_settings_tabs_array', array( $this, 'add_settings_tab' ), 19 );
        add_action( 'propertyhive_settings_' . $this->id, array( $this, 'output' ) );
        add_action( 'propertyhive_settings_save_' . $this->id, array( $this, 'save' ) );

        add_action( 'phrotatephotoscronhook', array( $this, 'execute_photo_rotation' ) );
    }

    public function run_custom_rotate_photos_cron()
    {
        if( isset($_GET['custom_rotate_photos_cron']) )
        {
            do_action($_GET['custom_rotate_photos_cron']);
        }
    }

    public function execute_photo_rotation()
    {
        $current_settings = get_option( 'propertyhive_rotate_property_photos', array() );

        // Get all on market properties and check the last date that photos were rotated
        $args = array(
            'post_type' => 'property',
            'post_status' => 'publish',
            'nopaging' => true,
            'fields' => 'ids',
            'meta_query' => array(
                array(
                    'key' => '_on_market',
                    'value' => 'yes'
                )
            ),
        );

        $property_query = new WP_Query( $args );

        if ( $property_query->have_posts() )
        {
            while ( $property_query->have_posts() )
            {
                $property_query->the_post();

                $date_photos_rotated = get_post_meta( get_the_ID(), '_date_photos_rotated', TRUE );

                if ( $date_photos_rotated == '' || ( (time() - strtotime($date_photos_rotated)) / (60 * 60 * 24) ) >= $current_settings['frequency'] )
                {
                    $this->rotate_photos( get_the_ID(), $current_settings['number'] );
                }
            }
        }
    }

    /* Currently not used */
    /*public function manual_photo_rotation()
    {
        $current_settings = get_option( 'propertyhive_rotate_property_photos', array() );

        // Get all on market properties and rotate the photos
        $args = array(
            'post_type' => 'property',
            'post_status' => 'publish',
            'nopaging' => true,
            'fields' => 'ids',
            'meta_query' => array(
                array(
                    'key' => '_on_market',
                    'value' => 'yes'
                )
            ),
        );

        $property_query = new WP_Query( $args );

        if ( $property_query->have_posts() )
        {
            while ( $property_query->have_posts() )
            {
                $property_query->the_post();

                $this->rotate_photos( get_the_ID(), $current_settings['number'] );
            }
        }
    }*/

    private function rotate_photos( $post_id, $number )
    {
        $photos = get_post_meta( $post_id, '_photos', TRUE );

        if ( is_array($photos) && count($photos) > 1 )
        {
            // we have more than one photo and can do the rotation
            if ( count($photos) < $number || $number == '' )
            {
                // There aren't enough photos to do just the first X. Have to just rotate the photos we do have
                // Or, we're rotating all photos
                array_push($photos, array_shift($photos));
            }
            else
            {
                // Rotate the first X photos
                $to_rotate = array_slice( $photos, 0, $number );
                $to_leave = array_slice( $photos, $number );

                array_push($to_rotate, array_shift($to_rotate));
                $photos = array_merge($to_rotate, $to_leave);
            }

            $post = get_post( $post_id );
            do_action( "save_post_property", $post_id, $post, false );
            do_action( "save_post", $post_id, $post, false );
        }

        update_post_meta( $post_id, '_photos', $photos );
        update_post_meta( $post_id, '_date_photos_rotated', date("Y-m-d") );
    }

    private function includes()
    {
        //include_once( 'includes/class-ph-rotate-property-photos-install.php' );
    }

    /**
     * Define PH Rotate Property Photos Constants
     */
    private function define_constants() 
    {
        define( 'PH_ROTATE_PROPERTY_PHOTOS_PLUGIN_FILE', __FILE__ );
        define( 'PH_ROTATE_PROPERTY_PHOTOS_VERSION', $this->version );
    }

    /**
     * Output error message if core Property Hive plugin isn't active
     */
    public function rotate_property_photos_error_notices() 
    {
        global $post;

        if (!is_plugin_active('propertyhive/propertyhive.php'))
        {
            $message = __( "The Property Hive plugin must be installed and activated before you can use the Property Hive Rotate Property Photos add-on", 'propertyhive' );
            echo "<div class=\"error\"> <p>$message</p></div>";
        }
    }

    /**
     * Add a new settings tab to the Property Hive settings tabs array.
     *
     * @param array $settings_tabs Array of Property Hive setting tabs & their labels.
     * @return array $settings_tabs Array of Property Hive setting tabs & their labels.
     */
    public function add_settings_tab( $settings_tabs ) {
        $settings_tabs['rotate-property-photos'] = __( 'Rotate Photos', 'propertyhive' );
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

        $settings = $this->get_rotate_property_photos_settings();
        
        propertyhive_admin_fields( $settings );
    }

    /**
     * Uses the Property Hive options API to save settings.
     *
     * @uses propertyhive_update_options()
     * @uses self::get_settings()
     */
    public function save() {

        global $current_section;

        $current_settings = get_option( 'propertyhive_rotate_property_photos', array() );

        $propertyhive_rotate_property_photos = array(
            'number' => $_POST['number'],
            'frequency' => $_POST['frequency'],
        );

        $propertyhive_rotate_property_photos = array_merge($current_settings, $propertyhive_rotate_property_photos);

        update_option( 'propertyhive_rotate_property_photos', $propertyhive_rotate_property_photos );

        $timestamp = wp_next_scheduled( 'phrotatephotoscronhook' );
        wp_unschedule_event($timestamp, 'phrotatephotoscronhook' );
        wp_clear_scheduled_hook('phrotatephotoscronhook');

        if ( $_POST['frequency'] != '' )
        {
            // Set to be rotated automatically. Need to setup scheduled event

            $next_schedule = strtotime('tomorrow');
            wp_schedule_event( $next_schedule, 'daily', 'phrotatephotoscronhook' );
        }
    }

    /**
     * Get rotate property photos settings
     *
     * @return array Array of settings
     */
    public function get_rotate_property_photos_settings() {

        $current_settings = get_option( 'propertyhive_rotate_property_photos', array() );

        $settings = array(

            array( 'title' => __( 'Rotate Property Photos', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'rotate_property_photos_settings' )

        );

        $settings[] = array(
            'title' => __( 'Rotate Photos', 'propertyhive' ),
            'id'        => 'number',
            'type'      => 'select',
            'default'   => ( isset($current_settings['number']) ? $current_settings['number'] : ''),
            'options'   => array(
                '' => __( 'All Photos', 'propertyhive'),
                '2' => __( 'First Two Photos Only', 'propertyhive'),
                '3' => __( 'First Three Photos Only', 'propertyhive'),
                '4' => __( 'First Four Photos Only', 'propertyhive'),
            ),
        );

        $settings[] = array(
            'title' => __( 'Automatically Rotate', 'propertyhive' ),
            'id'        => 'frequency',
            'type'      => 'select',
            'default'   => ( isset($current_settings['frequency']) ? $current_settings['frequency'] : ''),
            'options'   => array(
                '' => __( 'Never', 'propertyhive'),
                '1' => __( 'Every Day', 'propertyhive'),
                '3' => __( 'Every Three Days', 'propertyhive'),
                '7' => __( 'Every Week', 'propertyhive'),
                '14' => __( 'Every Two Weeks', 'propertyhive'),
            )
        );

        $settings[] = array( 'type' => 'sectionend', 'id' => 'rotate_property_photos_settings');

        return $settings;
    }
}

endif;

/**
 * Returns the main instance of PH_Rotate_Property_Photos to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return PH_Rotate_Property_Photos
 */
function PHRPP() {
    return PH_Rotate_Property_Photos::instance();
}

PHRPP();

if( is_admin() && file_exists(  dirname( __FILE__ ) . '/propertyhive-rotate-property-photos-update.php' ) )
{
    include_once( dirname( __FILE__ ) . '/propertyhive-rotate-property-photos-update.php' );
}