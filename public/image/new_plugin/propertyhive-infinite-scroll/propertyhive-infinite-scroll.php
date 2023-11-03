<?php
/**
 * Plugin Name: Property Hive Infinite Scroll Add On
 * Plugin Uri: http://wp-property-hive.com/addons/infinite-scroll/
 * Description: Add On for Property Hive allowing infinite scroll functionality on the search page
 * Version: 1.0.10
 * Author: PropertyHive
 * Author URI: http://wp-property-hive.com
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'PH_Infinite_Scroll' ) ) :

final class PH_Infinite_Scroll {

    /**
     * @var string
     */
    public $version = '1.0.10';

    /**
     * @var Property Hive The single instance of the class
     */
    protected static $_instance = null;
    
    /**
     * Main Property Hive Infinite Scroll Instance
     *
     * Ensures only one instance of Property Hive Infinite Scroll is loaded or can be loaded.
     *
     * @static
     * @return Property Hive Infinite Scroll - Main instance
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

        $this->id    = 'infinitescroll';
        $this->label = __( 'Infinite Scroll', 'propertyhive' );

        // Define constants
        $this->define_constants();

        // Include required files
        $this->includes();

        add_action( 'admin_notices', array( $this, 'infinite_scroll_error_notices') );

        add_filter( 'propertyhive_settings_tabs_array', array( $this, 'add_settings_tab' ), 19 );
        add_action( 'propertyhive_settings_' . $this->id, array( $this, 'output' ) );
        add_action( 'propertyhive_settings_save_' . $this->id, array( $this, 'save' ) );

        add_action( 'wp', array( $this, 'do_load_infinite_scroll'), 20 );

        add_action( 'wp_ajax_propertyhive_infinite_load_properties', array( $this, 'ajax_propertyhive_infinite_load_properties' ) );
        add_action( 'wp_ajax_nopriv_propertyhive_infinite_load_properties', array( $this, 'ajax_propertyhive_infinite_load_properties' ) );

        add_filter( "plugin_action_links_" . plugin_basename( __FILE__ ), array( $this, 'plugin_add_settings_link' ) );
    }

    public function plugin_add_settings_link( $links )
    {
        $settings_link = '<a href="' . admin_url('admin.php?page=ph-settings&tab=infinitescroll') . '">' . __( 'Settings' ) . '</a>';
        array_push( $links, $settings_link );
        return $links;
    }

    public function do_load_infinite_scroll()
    {
        if ( $this->load_infinite_scroll() )
        {
            add_action( 'wp_enqueue_scripts', array( $this, 'load_infinite_scroll_scripts' ) );
            add_action( 'propertyhive_after_search_results_loop', array( $this, 'propertyhive_infinite_scroll_components'), 10 );

            // Output CSS which'll hide pagination
            add_action( 'wp_head', array( $this, 'hide_pagination_css' ) );
        }
    }

    public function hide_pagination_css()
    {
?>
<style type="text/css">

.propertyhive-pagination { display:none !important; }

</style>
<?php
    }

    private function load_infinite_scroll()
    {
        $current_settings = get_option( 'propertyhive_infinite_scroll', array() );

        if ( isset($current_settings['devices']) && $current_settings['devices'] == 'mobile' )
        {
            // Should only show on mobile. Check if we're on mobile
            include( dirname(__FILE__) . '/Mobile_Detect.php' );

            $mobile_detect = new Mobile_Detect();

            if ( !$mobile_detect->isMobile() )
            {
                return false;
            }
        }

        if ( isset($_REQUEST['view']) && $_REQUEST['view'] == 'map' )
        {
            return false;
        }

        return true;
    }

    public function load_infinite_scroll_scripts() {

        if ( is_post_type_archive('property') )
        {
            global $wp_query;

            $current_settings = get_option( 'propertyhive_infinite_scroll', array() );

            $assets_path = str_replace( array( 'http:', 'https:' ), '', untrailingslashit( plugins_url( '/', __FILE__ ) ) ) . '/assets/';

            wp_register_script( 
                'ph-infinite-scroll', 
                $assets_path . 'js/ph-infinite-scroll.js', 
                array('jquery'), 
                PH_INFINITE_SCROLL_VERSION,
                true
            );

            wp_enqueue_script('ph-infinite-scroll');

            parse_str($_SERVER['QUERY_STRING'], $query_string);

            wp_localize_script( 'ph-infinite-scroll', 'ph_is_ajax_object', array( 
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'query_vars' => json_encode($wp_query->query_vars),
                'query_string' => json_encode($query_string),
                'posts_per_page' => $wp_query->query_vars['posts_per_page'],
                'total_posts' => $wp_query->found_posts,
                'functionality' => ( ( isset($current_settings['functionality']) ) ? $current_settings['functionality'] : '' ),
            ) );
        }
    }

    public function ajax_propertyhive_infinite_load_properties()
    {
        if ( isset($_POST['query_string']) && $_POST['query_string'] != '' )
        {
            $request = json_decode(stripslashes($_POST['query_string']), TRUE);
            if ( $request !== FALSE )
            {
                $_REQUEST = array_merge($_REQUEST, $request);
                $_GET = array_merge($_GET, $request);
            }
        }

        $query_vars = json_decode(stripslashes($_POST['query_vars']), TRUE);

        if ( isset($_POST['paged']) )
        {
            $query_vars['paged'] = $_POST['paged'];
        }

        foreach ( $query_vars as $key => $value )
        {
            if ( taxonomy_exists($key) )
            {
                unset( $query_vars[$key] );
            }
        }

        $query_vars['post_status'] = 'publish';

        // Excludes properties with a password set
        // Performs the same operation as exclude_protected_properties() in class-ph-query.php
        $query_vars['post_password'] = '';

        $property_query = new WP_Query( $query_vars );

        if ( $property_query->have_posts() )
        {
            while ( $property_query->have_posts() )
            {
                $property_query->the_post();

                ph_get_template_part( 'content', 'property' );
            }
        }

        wp_die(); // this is required to terminate immediately and return a proper response
    }

    /**
     * If automatic loading is enabled will contain 'Loading..' indicator
     * Otherwise will display 'Load More' button
     */
    public function propertyhive_infinite_scroll_components()
    {
        $current_settings = get_option( 'propertyhive_infinite_scroll', array() );

        // TO DO: Make this an overwritable template
        if ( isset($current_settings['functionality']) && $current_settings['functionality'] == 'button' )
        {
?>
<div class="ph-infinite-scroll-button"><a href="" class="button"><?php _e( 'Load More Properties', 'propertyhive' ); ?></a></div>
<?php
        }
        else
        {
?>
<div class="ph-infinite-scroll-loading" style="display:none"><?php _e( 'Loading More Properties', 'propertyhive' ); ?>...</div>
<?php
        }
    }

    /**
     * Define PH Infinite Scroll Constants
     */
    private function define_constants() 
    {
        define( 'PH_INFINITE_SCROLL_PLUGIN_FILE', __FILE__ );
        define( 'PH_INFINITE_SCROLL_VERSION', $this->version );
    }

    private function includes()
    {
        //include_once( dirname( __FILE__ ) . "/includes/class-ph-infinite-scroll-install.php" );
    }

    /**
     * Output error message if core Property Hive plugin isn't active
     */
    public function infinite_scroll_error_notices() 
    {
        if (!is_plugin_active('propertyhive/propertyhive.php'))
        {
            $message = __( "The Property Hive plugin must be installed and activated before you can use the Property Hive Infinite Scroll add-on", 'propertyhive' );
            echo"<div class=\"error\"> <p>$message</p></div>";
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
        
        propertyhive_admin_fields( self::get_infinite_scroll_settings() );
    }

    /**
     * Get infinite scroll settings
     *
     * @return array Array of settings
     */
    public function get_infinite_scroll_settings() {

        global $post;

        $current_settings = get_option( 'propertyhive_infinite_scroll', array() );

        $settings = array(

            array( 'title' => __( 'Infinite Scroll Settings', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'infinite_scroll_settings' )

        );

        $settings[] = array(
            'title'     => __( 'Add Infinite Scroll To', 'propertyhive' ),
            'id'        => 'devices',
            'type'      => 'radio',
            'default'   => ( isset($current_settings['devices']) ? $current_settings['devices'] : ''),
            'options'   => array(
                '' => __( 'All Devices', 'propertyhive' ),
                'mobile' => __( 'Mobile Only', 'propertyhive' ),
            ),
        );

        $settings[] = array(
            'title'     => __( 'Functionality', 'propertyhive' ),
            'id'        => 'functionality',
            'type'      => 'radio',
            'default'   => ( isset($current_settings['functionality']) ? $current_settings['functionality'] : ''),
            'options'   => array(
                '' => __( 'Load Properties When User Reaches Bottom Of Page', 'propertyhive' ),
                'button' => __( 'Display A \'Load More\' Button Which Loads Properties When Clicked', 'propertyhive' ),
            ),
        );

        $settings[] = array( 'type' => 'sectionend', 'id' => 'infinite_scroll_settings');

        return $settings;
    }

    /**
     * Uses the Property Hive options API to save settings.
     *
     * @uses propertyhive_update_options()
     * @uses self::get_settings()
     */
    public function save() {

        $existing_propertyhive_infinite_scroll = get_option( 'propertyhive_infinite_scroll', array() );

        $propertyhive_infinite_scroll = array(
            'devices' => ( (isset($_POST['devices'])) ? $_POST['devices'] : '' ),
            'functionality' => ( (isset($_POST['functionality'])) ? $_POST['functionality'] : '' ),
        );

        $propertyhive_infinite_scroll = array_merge( $existing_propertyhive_infinite_scroll, $propertyhive_infinite_scroll );

        update_option( 'propertyhive_infinite_scroll', $propertyhive_infinite_scroll );
    }
}

endif;

/**
 * Returns the main instance of PH_Infinite_Scroll to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return PH_Infinite_Scroll
 */
function PHIS() {
    return PH_Infinite_Scroll::instance();
}

$PHIS = PHIS();

if( is_admin() && file_exists(  dirname( __FILE__ ) . '/propertyhive-infinite-scroll-update.php' ) )
{
    include_once( dirname( __FILE__ ) . '/propertyhive-infinite-scroll-update.php' );
}