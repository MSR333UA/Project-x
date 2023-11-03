<?php
/**
 * Plugin Name: Property Hive Rental Affordability Calculator
 * Plugin Uri: http://wp-property-hive.com/addons/rental-affordability-calculator/
 * Description: Quickly and easily add a rental affordability calculator to your website using a simple shortcode 
 * Version: 1.0.2
 * Author: PropertyHive
 * Author URI: http://wp-property-hive.com
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'PH_Rental_Affordability_Calculator' ) ) :

final class PH_Rental_Affordability_Calculator {

    /**
     * @var string
     */
    public $version = '1.0.2';

    /**
     * @var Property Hive The single instance of the class
     */
    protected static $_instance = null;
    
    /**
     * Main Property Hive Rental Affordability Calculator Instance
     *
     * Ensures only one instance of Property Hive Rental Affordability Calculator is loaded or can be loaded.
     *
     * @static
     * @return Property Hive Rental Affordability Calculator - Main instance
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

        $this->id    = 'rentalaffordabilitycalculator';
        $this->label = __( 'Rental Affordability Calculator', 'propertyhive' );

        // Define constants
        $this->define_constants();

        // Include required files
        $this->includes();

        add_action( 'wp_enqueue_scripts', array( $this, 'load_rental_affordability_calculator_scripts' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'load_rental_affordability_calculator_styles' ) );

        add_shortcode( 'rental_affordability_calculator', array( $this, 'propertyhive_rental_affordability_calculator_shortcode' ) );
    }

    /**
     * Define PH Rental Affordability Calculator Constants
     */
    private function define_constants() 
    {
        define( 'PH_RENTAL_AFFORDABILITY_CALCULATOR_PLUGIN_FILE', __FILE__ );
        define( 'PH_RENTAL_AFFORDABILITY_CALCULATOR_VERSION', $this->version );
    }

    private function includes()
    {
        //include_once( dirname( __FILE__ ) . "/includes/class-ph-rental-affordability-calculator-install.php" );
    }

    public function propertyhive_rental_affordability_calculator_shortcode( $atts )
    {
        $atts = shortcode_atts( array(
            'price' => ''
        ), $atts );

        wp_enqueue_style( 'ph-rental-affordability-calculator' );

        wp_enqueue_script( 'jquery' );
        wp_enqueue_script( 'ph-rental-affordability-calculator' );

        ob_start();

        $template = locate_template( array('propertyhive/rental-affordability-calculator.php') );
        if ( !$template )
        {
            include( dirname( PH_RENTAL_AFFORDABILITY_CALCULATOR_PLUGIN_FILE ) . '/templates/rental-affordability-calculator.php' );
        }
        else
        {
            include( $template );
        }

        return ob_get_clean();
    }

    public function load_rental_affordability_calculator_scripts() {

        $assets_path = str_replace( array( 'http:', 'https:' ), '', untrailingslashit( plugins_url( '/', __FILE__ ) ) ) . '/assets/';

        wp_register_script( 
            'ph-rental-affordability-calculator', 
            $assets_path . 'js/propertyhive-rental-affordability-calculator.js', 
            array(), 
            PH_RENTAL_AFFORDABILITY_CALCULATOR_VERSION,
            true
        );
    }

    public function load_rental_affordability_calculator_styles() {

        $assets_path = str_replace( array( 'http:', 'https:' ), '', untrailingslashit( plugins_url( '/', __FILE__ ) ) ) . '/assets/';

        wp_register_style( 
            'ph-rental-affordability-calculator', 
            $assets_path . 'css/propertyhive-rental-affordability-calculator.css', 
            array(), 
            PH_RENTAL_AFFORDABILITY_CALCULATOR_VERSION
        );
    }
}

endif;

/**
 * Returns the main instance of PH_Rental_Affordability_Calculator to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return PH_Rental_Affordability_Calculator
 */
function PHRAC() {
    return PH_Rental_Affordability_Calculator::instance();
}

PHRAC();