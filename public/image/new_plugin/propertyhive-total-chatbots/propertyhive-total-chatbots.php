<?php
/**
 * Plugin Name: Property Hive Total Chatbots Add On
 * Plugin Uri: http://wp-property-hive.com/addons/total-chatbots/
 * Description: Add On for Property Hive allowing users to display a chatbot, powered by Total Chatbots, on their website
 * Version: 1.0.0
 * Author: PropertyHive
 * Author URI: http://wp-property-hive.com
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'PH_Total_Chatbots' ) ) :
final class PH_Total_Chatbots {

    /**
     * @var string
     */
    public $version = '1.0.0';

    /**
     * @var Property Hive The single instance of the class
     */
    protected static $_instance = null;

    /**
     * Main Property Hive Total Chatbots Instance
     *
     * Ensures only one instance of Property Hive Total Chatbots is loaded or can be loaded.
     *
     * @static
     * @return Property Hive Total Chatbots - Main instance
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

        $this->id    = 'total-chatbots';
        $this->label = __( 'Total Chatbots', 'propertyhive' );

        // Define constants
        $this->define_constants();

        // Include required files
        $this->includes();

        add_action( 'admin_notices', array( $this, 'total_chatbots_error_notices') );

        add_filter( 'propertyhive_settings_tabs_array', array( $this, 'add_settings_tab' ), 19 );
        add_action( 'propertyhive_settings_' . $this->id, array( $this, 'output' ) );
        add_action( 'propertyhive_settings_save_' . $this->id, array( $this, 'save' ) );

        add_filter( "plugin_action_links_" . plugin_basename( __FILE__ ), array( $this, 'plugin_add_settings_link' ) );

        $current_settings = get_option( 'propertyhive_total_chatbots', array() );
        if ( isset($current_settings['enabled']) && $current_settings['enabled'] == '1' )
        {
            add_action( 'wp_footer', array( $this, 'display_widget_code' ), 10 );
        }
    }

    public function plugin_add_settings_link( $links )
    {
        $settings_link = '<a href="' . admin_url('admin.php?page=ph-settings&tab=total-chatbots') . '">' . __( 'Settings' ) . '</a>';
        array_push( $links, $settings_link );
        return $links;
    }

    /**
     * Define PH Total Chatbots Constants
     */
    private function define_constants()
    {
        define( 'PH_TOTAL_CHATBOTS_PLUGIN_FILE', __FILE__ );
        define( 'PH_TOTAL_CHATBOTS_VERSION', $this->version );
    }

    private function includes()
    {
    }

    /**
     * Output error message if core Property Hive plugin isn't active
     */
    public function total_chatbots_error_notices()
    {
        if (!is_plugin_active('propertyhive/propertyhive.php'))
        {
            $message = __( "The Property Hive plugin must be installed and activated before you can use the Property Hive Total Chatbots add-on", 'propertyhive' );
            echo"<div class=\"error\"> <p>$message</p></div>";
        }
    }

    /**
     * Add a new settings tab to the Property Hive settings tabs array.
     *
     * @param array $settings_tabs Array of Property Hive setting tabs & their labels
     * @return array $settings_tabs Array of Property Hive setting tabs & their labels
     */
    public function add_settings_tab( $settings_tabs )
    {
        $settings_tabs[$this->id] = $this->label;
        return $settings_tabs;
    }

    /**
     * Uses the Property Hive admin fields API to output settings.
     *
     * @uses propertyhive_admin_fields()
     * @uses self::get_settings()
     */
    public function output()
    {
        propertyhive_admin_fields( self::get_total_chatbots_settings() );
    }

    /**
     * Get Total Chatbots settings
     *
     * @return array Array of settings
     */
    public function get_total_chatbots_settings()
    {
        $current_settings = get_option( 'propertyhive_total_chatbots', array() );

        $settings = array(

            array( 'title' => __( 'Total Chatbots Settings', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'total_chatbots_settings' )

        );

        $settings[] = array(
            'title'     => __( 'Enabled', 'propertyhive' ),
            'id'        => 'enabled',
            'type'      => 'checkbox',
            'default'   => ( isset($current_settings['enabled']) && $current_settings['enabled'] == 1 ? 'yes' : ''),
            'desc'      => __( 'This add on requires an active subscription with <a href="https://www.totalchatbots.com/" target="_blank">Total Chatbots</a>. Please enter referral code \'3GF8VG\' when signing up, or mention you\'re using Property Hive.', 'propertyhive' )
        );

        $settings[] = array(
            'title'   => __( 'Widget Code', 'propertyhive' ),
            'id'      => 'widget_code',
            'type'    => 'textarea',
            'default'   => ( isset($current_settings['widget_code']) ? stripslashes($current_settings['widget_code']) : ''),
            'css'	  => 'height:150px; width:100%; max-width:600px'
        );

        $settings[] = array( 'type' => 'sectionend', 'id' => 'total_chatbots_settings');

        return $settings;
    }

    /**
     * Uses the Property Hive options API to save settings.
     *
     * @uses propertyhive_update_options()
     * @uses self::get_settings()
     */
    public function save()
    {
        $existing_propertyhive_total_chatbots = get_option( 'propertyhive_total_chatbots', array() );

        $propertyhive_total_chatbots = array(
            'enabled' => ( (isset($_POST['enabled'])) ? $_POST['enabled'] : '' ),
            'widget_code' => ( (isset($_POST['widget_code'])) ? $_POST['widget_code'] : '' ),
        );

        $propertyhive_total_chatbots = array_merge( $existing_propertyhive_total_chatbots, $propertyhive_total_chatbots );

        update_option( 'propertyhive_total_chatbots', $propertyhive_total_chatbots );
    }

    public function display_widget_code()
    {
        $current_settings = get_option( 'propertyhive_total_chatbots', array() );

        if ( isset($current_settings['widget_code']) && trim($current_settings['widget_code']) != '' )
        {
            echo $current_settings['widget_code'];
        }
    }
}

endif;

/**
 * Returns the main instance of PH_Total_Chatbots to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return PH_Total_Chatbots
 */
function PHTC() {
    return PH_Total_Chatbots::instance();
}

$PHTC = PHTC();

if( is_admin() && file_exists(  dirname( __FILE__ ) . '/propertyhive-total-chatbots-update.php' ) )
{
    include_once( dirname( __FILE__ ) . '/propertyhive-total-chatbots-update.php' );
}