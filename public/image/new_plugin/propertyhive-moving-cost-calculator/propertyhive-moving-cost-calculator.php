<?php
/**
 * Plugin Name: Property Hive Moving Cost Calculator Add On
 * Plugin Uri: http://wp-property-hive.com/addons/moving-cost-calculator/
 * Description: Add On for Property Hive allowing you to output a cost of moving calculator
 * Version: 1.0.1
 * Author: PropertyHive
 * Author URI: http://wp-property-hive.com
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'PH_Moving_Cost_Calculator' ) ) :

final class PH_Moving_Cost_Calculator {

    /**
     * @var string
     */
    public $version = '1.0.1';

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
        $this->id    = 'movingcostcalculator';
        $this->label = __( 'Moving Cost Calculator', 'propertyhive' );

        // Define constants
        $this->define_constants();

        // Include required files
        $this->includes();

        add_action( 'admin_notices', array( $this, 'moving_cost_calculator_error_notices') );

        add_filter( 'propertyhive_settings_tabs_array', array( $this, 'add_settings_tab' ), 19 );
        add_action( 'propertyhive_settings_' . $this->id, array( $this, 'output' ) );
        add_action( 'propertyhive_settings_save_' . $this->id, array( $this, 'save' ) );

        add_action( 'propertyhive_admin_field_solicitors', array( $this, 'solicitors_setting' ) );
        add_action( 'propertyhive_admin_field_surveyors', array( $this, 'surveyors_setting' ) );
        add_action( 'propertyhive_admin_field_removal_companies', array( $this, 'removal_companies_setting' ) );
        add_action( 'propertyhive_admin_field_mortgage_advisors', array( $this, 'mortgage_advisors_setting' ) );
        add_action( 'propertyhive_admin_field_financial_advisors', array( $this, 'financial_advisors_setting' ) );

        add_action( 'wp_enqueue_scripts', array( $this, 'load_moving_cost_calculator_scripts' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'load_moving_cost_calculator_styles' ) );

        add_action( 'wp_ajax_propertyhive_request_moving_quotes', array( $this, 'request_moving_quotes_ajax_callback' ) );
        add_action( 'wp_ajax_nopriv_propertyhive_request_moving_quotes', array( $this, 'request_moving_quotes_ajax_callback' ) );

        add_shortcode( 'moving_cost_calculator', array( $this, 'propertyhive_moving_cost_calculator_shortcode' ) );

        add_action( 'admin_init', array( $this, 'delete_actions') );
    }

    public function request_moving_quotes_ajax_callback()
    {
        $return = array();

        header( 'Content-Type: application/json; charset=utf-8' );

        if (defined('DOING_AJAX') && DOING_AJAX) 
        { 
            // Definitely doing AJAX request

            if (
                !isset($_POST['name']) ||
                ( isset($_POST['name']) && trim($_POST['name']) == '' ) ||
                !isset($_POST['email_address']) ||
                ( isset($_POST['email_address']) && sanitize_email($_POST['email_address']) == '' ) ||
                !is_email(sanitize_email($_POST['email_address']))
            )
            {
                // Failed validation
                $return['success'] = false;
                $return['reason'] = 'validation';
                $return['errors'] = array('Missing or invalid contact details provided');

                echo json_encode( $return );

                wp_die();
            }

            $current_settings = get_option( 'propertyhive_moving_cost_calculator', array() );

            $solicitors = ( isset($current_settings['solicitors']) ? $current_settings['solicitors'] : array() );
            $surveyors = ( isset($current_settings['surveyors']) ? $current_settings['surveyors'] : array() );
            $removal_companies = ( isset($current_settings['removal_companies']) ? $current_settings['removal_companies'] : array() );
            $mortgage_advisors = ( isset($current_settings['mortgage_advisors']) ? $current_settings['mortgage_advisors'] : array() );
            $financial_advisors = ( isset($current_settings['financial_advisors']) ? $current_settings['financial_advisors'] : array() );

            if (
                isset($_POST['third_parties']) && 
                $_POST['third_parties'] != ''
            )
            {
                $third_parties = json_decode(stripslashes($_POST['third_parties']), true);
                if ( !is_null($third_parties) && $third_parties !== FALSE && is_array($third_parties) && !empty($third_parties) )
                {
                    // Send quotes to third parties
                    if ( 
                        isset($current_settings['email_third_parties_quote_requests']) && 
                        $current_settings['email_third_parties_quote_requests'] == '1'
                    )
                    {
                        foreach ( $third_parties as $category => $category_ids )
                        {
                            if ( !empty($category_ids) )
                            {
                                foreach ( $category_ids as $category_id )
                                {
                                    $category = $$category;
                                    $third_party = $category[$category_id];
                                    
                                    if ( isset($third_party['email']) && sanitize_email($third_party['email']) != '' )
                                    {
                                        $to = sanitize_email($third_party['email']);
                                        $subject = $current_settings['third_party_email_subject'];
                                        $body = $current_settings['third_party_email_body'];

                                        // do replacement of [user_details] tag
                                        $user_details = "Name: " . $_POST['name'] . "\nEmail Address: " . $_POST['email_address'] . "\nTelephone Number: " . $_POST['telephone_number'] . "\Additional Information: " . $_POST['message'];

                                        $body = str_replace("[user_details]", $user_details, $body);

                                        wp_mail( $to, $subject, $body );
                                    }
                                }
                            }
                        }
                    }
                
                    // Send email to agent
                    if ( 
                        isset($current_settings['email_me_quote_requests']) && 
                        $current_settings['email_me_quote_requests'] == '1' 
                    )
                    {
                        $to = ( ( sanitize_email($current_settings['my_email_address']) != '' ) ? sanitize_email($current_settings['my_email_address']) : get_option('admin_email', '') );
                        $subject = 'Contact/Quotes Requested From Moving Cost Calculator';
                        $body = "A user has completed the moving cost calculator on the " . get_bloginfo('name') . " website and requested contact/quotes from third parties. Please find details below:\n\n[user_details]\n\n------\n\nContact/Quotes requested from:\n\n[quotes_requested]";

                        // do replacement of [user_details] tag
                        $user_details = "Name: " . $_POST['name'] . "\nEmail Address: " . $_POST['email_address'] . "\nTelephone Number: " . $_POST['telephone_number'] . "\Additional Information: " . $_POST['message'];

                        $quotes_requested = "";

                        foreach ( $third_parties as $category => $category_ids )
                        {
                            if ( !empty($category_ids) )
                            {
                                $quotes_requested .= ucfirst($category) . ":\n";
                                foreach ( $category_ids as $category_id )
                                {
                                    $category = $$category;
                                    $third_party = $category[$category_id];
                                    
                                    if ( 
                                        isset($third_party['name']) && trim($third_party['name']) != '' && 
                                        isset($third_party['email']) && sanitize_email($third_party['email']) != '' )
                                    {
                                        $quotes_requested .= "- " . $third_party['name'] . " (" . sanitize_email($third_party['email']) . ")\n";
                                    }
                                }
                                $quotes_requested .= "\n";
                            }
                        }

                        $body = str_replace("[user_details]", $user_details, $body);
                        $body = str_replace("[quotes_requested]", $quotes_requested, $body);

                        wp_mail( $to, $subject, $body );
                    }

                    // Failed validation
                    $return['success'] = true;

                    echo json_encode( $return );

                    wp_die();
                }
                else
                {
                    $return['success'] = false;
                    $return['reason'] = 'validation';
                    $return['errors'] = array('Failed to parse third parties, or none selected');

                    echo json_encode( $return );

                    wp_die();
                }
            }
            else
            {
                $return['success'] = false;
                $return['reason'] = 'validation';
                $return['errors'] = array('No third parties selected');

                echo json_encode( $return );

                wp_die();
            }
        }

        wp_die();
    }

    public function delete_actions()
    {
        if ( isset($_GET['action']) && isset($_GET['delete']) )
        {
            switch ($_GET['action'])
            {
                case "deletesolicitor":
                {
                    $current_moving_cost_calculator_options = get_option( 'propertyhive_moving_cost_calculator' );
                    $results = array();
                    if ($current_moving_cost_calculator_options !== FALSE)
                    {
                        if (isset($current_moving_cost_calculator_options['solicitors']))
                        {
                            $results = $current_moving_cost_calculator_options['solicitors'];
                        }
                    }

                    $new_results = array();
                    if (!empty($results))
                    {
                        foreach ($results as $i => $result)
                        {
                            if ( $i != $_GET['delete'] )
                            {
                                $new_results[] = $result;
                            }
                        }
                    }

                    $current_moving_cost_calculator_options['solicitors'] = $new_results;

                    update_option( 'propertyhive_moving_cost_calculator', $current_moving_cost_calculator_options );

                    wp_redirect( admin_url('admin.php?page=ph-settings&tab=movingcostcalculator') );
                    exit();
                }
                case "deletesurveyor":
                {
                    $current_moving_cost_calculator_options = get_option( 'propertyhive_moving_cost_calculator' );
                    $results = array();
                    if ($current_moving_cost_calculator_options !== FALSE)
                    {
                        if (isset($current_moving_cost_calculator_options['surveyors']))
                        {
                            $results = $current_moving_cost_calculator_options['surveyors'];
                        }
                    }

                    $new_results = array();
                    if (!empty($results))
                    {
                        foreach ($results as $i => $result)
                        {
                            if ( $i != $_GET['delete'] )
                            {
                                $new_results[] = $result;
                            }
                        }
                    }

                    $current_moving_cost_calculator_options['surveyors'] = $new_results;

                    update_option( 'propertyhive_moving_cost_calculator', $current_moving_cost_calculator_options );

                    wp_redirect( admin_url('admin.php?page=ph-settings&tab=movingcostcalculator') );
                    exit();
                }
                case "deleteremovalcompany":
                {
                    $current_moving_cost_calculator_options = get_option( 'propertyhive_moving_cost_calculator' );
                    $results = array();
                    if ($current_moving_cost_calculator_options !== FALSE)
                    {
                        if (isset($current_moving_cost_calculator_options['removal_companies']))
                        {
                            $results = $current_moving_cost_calculator_options['removal_companies'];
                        }
                    }

                    $new_results = array();
                    if (!empty($results))
                    {
                        foreach ($results as $i => $result)
                        {
                            if ( $i != $_GET['delete'] )
                            {
                                $new_results[] = $result;
                            }
                        }
                    }

                    $current_moving_cost_calculator_options['removal_companies'] = $new_results;

                    update_option( 'propertyhive_moving_cost_calculator', $current_moving_cost_calculator_options );

                    wp_redirect( admin_url('admin.php?page=ph-settings&tab=movingcostcalculator') );
                    exit();
                }
                case "deletemortgageadvisor":
                {
                    $current_moving_cost_calculator_options = get_option( 'propertyhive_moving_cost_calculator' );
                    $results = array();
                    if ($current_moving_cost_calculator_options !== FALSE)
                    {
                        if (isset($current_moving_cost_calculator_options['mortgage_advisors']))
                        {
                            $results = $current_moving_cost_calculator_options['mortgage_advisors'];
                        }
                    }

                    $new_results = array();
                    if (!empty($results))
                    {
                        foreach ($results as $i => $result)
                        {
                            if ( $i != $_GET['delete'] )
                            {
                                $new_results[] = $result;
                            }
                        }
                    }

                    $current_moving_cost_calculator_options['mortgage_advisors'] = $new_results;

                    update_option( 'propertyhive_moving_cost_calculator', $current_moving_cost_calculator_options );

                    wp_redirect( admin_url('admin.php?page=ph-settings&tab=movingcostcalculator') );
                    exit();
                }
                case "deletefinancialadvisor":
                {
                    $current_moving_cost_calculator_options = get_option( 'propertyhive_moving_cost_calculator' );
                    $results = array();
                    if ($current_moving_cost_calculator_options !== FALSE)
                    {
                        if (isset($current_moving_cost_calculator_options['financial_advisors']))
                        {
                            $results = $current_moving_cost_calculator_options['financial_advisors'];
                        }
                    }

                    $new_results = array();
                    if (!empty($results))
                    {
                        foreach ($results as $i => $result)
                        {
                            if ( $i != $_GET['delete'] )
                            {
                                $new_results[] = $result;
                            }
                        }
                    }

                    $current_moving_cost_calculator_options['financial_advisors'] = $new_results;

                    update_option( 'propertyhive_moving_cost_calculator', $current_moving_cost_calculator_options );

                    wp_redirect( admin_url('admin.php?page=ph-settings&tab=movingcostcalculator') );
                    exit();
                }
            }
        }
    }

    /**
     * Define PH Moving Cost Calculator Constants
     */
    private function define_constants() 
    {
        define( 'PH_MOVING_COST_CALCULATOR_PLUGIN_FILE', __FILE__ );
        define( 'PH_MOVING_COST_CALCULATOR_VERSION', $this->version );
    }

    private function includes()
    {
        include_once( 'includes/class-ph-moving-cost-calculator-install.php' );
    }

    /**
     * Output error message if core Property Hive plugin isn't active
     */
    public function moving_cost_calculator_error_notices() 
    {
        if (!is_plugin_active('propertyhive/propertyhive.php'))
        {
            $message = "The Property Hive plugin must be installed and activated before you can use the Property Hive Moving Cost Calculator add-on";
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
        $settings_tabs['movingcostcalculator'] = __( 'Moving Cost Calculator', 'propertyhive' );
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

        switch ($current_section)
        {
            case "addsolicitor":
            case "editsolicitor":
            {
                propertyhive_admin_fields( self::get_solicitor_settings() );
                break;
            }
            case "addsurveyor":
            case "editsurveyor":
            {
                propertyhive_admin_fields( self::get_surveyor_settings() );
                break;
            }
            case "addremovalcompany":
            case "editremovalcompany":
            {
                propertyhive_admin_fields( self::get_removal_company_settings() );
                break;
            }
            case "addmortgageadvisor":
            case "editmortgageadvisor":
            {
                propertyhive_admin_fields( self::get_mortgage_advisor_settings() );
                break;
            }
            case "addfinancialadvisor":
            case "editfinancialadvisor":
            {
                propertyhive_admin_fields( self::get_financial_advisor_settings() );
                break;
            }
            default:
            {
                propertyhive_admin_fields( self::get_moving_cost_calculator_settings() );
            }
        }
    }

    /**
     * Get all the main settings for this plugin
     *
     * @return array Array of settings
     */
    public function get_moving_cost_calculator_settings() {

        $current_settings = get_option( 'propertyhive_moving_cost_calculator', array() );

        $settings = array(

            array( 'title' => __( 'Moving Cost Calculator Referrals', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'moving_cost_calculator_third_parties' ),

            array(
                'type'      => 'solicitors',
            ),

            array(
                'type'      => 'surveyors',
            ),

            array(
                'type'      => 'removal_companies',
            ),

            array(
                'type'      => 'mortgage_advisors',
            ),

            array(
                'type'      => 'financial_advisors',
            ),

            array( 'type' => 'sectionend', 'id' => 'moving_cost_calculator_third_parties'),

            array( 'title' => __( 'Moving Cost Calculator Settings', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'moving_cost_calculator_settings' ),

            array(
                'title' => __( 'Agency Fees % Default', 'propertyhive' ),
                'id'        => 'agency_fees_percentage',
                'default'   => ( ( isset($current_settings['agency_fees_percentage']) ) ? $current_settings['agency_fees_percentage'] : '1.5'),
                'type'      => 'text',
                'desc_tip'  =>  false,
            ),

            array(
                'title' => __( 'Conveyancing % (Buying)', 'propertyhive' ),
                'id'        => 'conveyancing_percentage_buying',
                'default'   => ( ( isset($current_settings['conveyancing_percentage_buying']) ) ? $current_settings['conveyancing_percentage_buying'] : '0.3'),
                'type'      => 'text',
                'desc_tip'  =>  false,
            ),

            array(
                'title' => __( 'Conveyancing % (Selling)', 'propertyhive' ),
                'id'        => 'conveyancing_percentage_selling',
                'default'   => ( ( isset($current_settings['conveyancing_percentage_selling']) ) ? $current_settings['conveyancing_percentage_selling'] : '0.3'),
                'type'      => 'text',
                'desc_tip'  =>  false,
            ),

            array(
                'title' => __( 'Conveyancing Cost Minimum', 'propertyhive' ),
                'id'        => 'conveyancing_cost_minimum',
                'default'   => ( ( isset($current_settings['conveyancing_cost_minimum']) ) ? $current_settings['conveyancing_cost_minimum'] : '600'),
                'type'      => 'text',
                'desc_tip'  =>  false,
            ),

            array(
                'title' => __( 'Conveyancing Cost Maximum', 'propertyhive' ),
                'id'        => 'conveyancing_cost_maximum',
                'default'   => ( ( isset($current_settings['conveyancing_cost_maximum']) ) ? $current_settings['conveyancing_cost_maximum'] : '1800'),
                'type'      => 'text',
                'desc_tip'  =>  false,
            ),

            array(
                'title' => __( 'Other Legal Costs Default (Buying)', 'propertyhive' ),
                'id'        => 'other_legal_costs_buying',
                'default'   => ( ( isset($current_settings['other_legal_costs_buying']) ) ? $current_settings['other_legal_costs_buying'] : '350'),
                'type'      => 'text',
                'desc_tip'  =>  false,
            ),

            array(
                'title' => __( 'Other Legal Costs Default (Selling)', 'propertyhive' ),
                'id'        => 'other_legal_costs_selling',
                'default'   => ( ( isset($current_settings['other_legal_costs_selling']) ) ? $current_settings['other_legal_costs_selling'] : '40'),
                'type'      => 'text',
                'desc_tip'  =>  false,
            ),

            array(
                'title' => __( 'Homebuyer Survey Default Cost', 'propertyhive' ),
                'id'        => 'homebuyer_cost',
                'default'   => ( ( isset($current_settings['homebuyer_cost']) ) ? $current_settings['homebuyer_cost'] : '540'),
                'type'      => 'text',
                'desc_tip'  =>  false,
            ),

            array(
                'title' => __( 'Full Survey Default Cost', 'propertyhive' ),
                'id'        => 'full_survey_cost',
                'default'   => ( ( isset($current_settings['full_survey_cost']) ) ? $current_settings['full_survey_cost'] : '700'),
                'type'      => 'text',
                'desc_tip'  =>  false,
            ),

            array(
                'title' => __( 'Removal/Storage Costs Default', 'propertyhive' ),
                'id'        => 'removal_storage_costs',
                'default'   => ( ( isset($current_settings['removal_storage_costs']) ) ? $current_settings['removal_storage_costs'] : ''),
                'type'      => 'text',
                'desc_tip'  =>  false,
            ),

            array(
                'title' => __( 'Other Moving Costs Default', 'propertyhive' ),
                'id'        => 'other_moving_costs',
                'default'   => ( ( isset($current_settings['other_moving_costs']) ) ? $current_settings['other_moving_costs'] : '200'),
                'type'      => 'text',
                'desc_tip'  =>  false,
            ),

            array( 'type' => 'sectionend', 'id' => 'moving_cost_calculator_settings'),

            array( 'title' => __( 'Moving Cost Calculator Email Settings', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'moving_cost_calculator_email_settings' ),

            array(
                'title' => __( 'Email Quote Requests To Third Parties', 'propertyhive' ),
                'id'        => 'email_third_parties_quote_requests',
                'default'   => ( ( isset($current_settings['email_third_parties_quote_requests']) && $current_settings['email_third_parties_quote_requests'] == 1 ) ? 'yes' : ''),
                'type'      => 'checkbox',
                'desc_tip'  =>  false,
            ),

            array(
                'title' => __( 'Third Party Email Subject', 'propertyhive' ),
                'id'        => 'third_party_email_subject',
                'default'   => ( isset($current_settings['third_party_email_subject']) ? $current_settings['third_party_email_subject']  : ''),
                'type'      => 'text',
                'desc_tip'  =>  false,
                'css'       => 'width:100%; max-width:500px;',
            ),

            array(
                'title' => __( 'Third Party Email Body', 'propertyhive' ),
                'id'        => 'third_party_email_body',
                'default'   => ( isset($current_settings['third_party_email_body']) ? $current_settings['third_party_email_body']  : ''),
                'type'      => 'textarea',
                'desc_tip'  =>  false,
                'css'       => 'width:100%; max-width:500px; height:110px;',
            ),

            array(
                'title' => __( 'Email Details Of Quote Requests To Me', 'propertyhive' ),
                'id'        => 'email_me_quote_requests',
                'default'   => ( ( isset($current_settings['email_me_quote_requests']) && $current_settings['email_me_quote_requests'] == 1 ) ? 'yes' : ''),
                'type'      => 'checkbox',
                'desc_tip'  =>  false,
            ),

            array(
                'title' => __( 'Send Email To', 'propertyhive' ),
                'id'        => 'my_email_address',
                'default'   => ( isset($current_settings['my_email_address']) ? $current_settings['my_email_address']  : get_option('admin_email', '')),
                'type'      => 'email',
                'desc_tip'  =>  false,
                'css'       => 'width:100%; max-width:500px;',
            ),

            array( 'type' => 'sectionend', 'id' => 'moving_cost_calculator_email_settings'),

            array( 'title' => __( 'Adding The Calculator', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'moving_cost_calculator_shortcode' ),

            array(
                'html'      => 'To add the calculator to your website simply add the shortcode <code>[moving_cost_calculator]</code> where you\'d like the calculator to appear.',
                'type'      => 'html',
                'title'     => __( 'The Shortcode', 'propertyhive' ),
            ),

            array( 'type' => 'sectionend', 'id' => 'moving_cost_calculator_shortcode'),

        );

        return apply_filters( 'ph_settings_moving_cost_calculator', $settings );
    }

    /**
     * Output list of solicitors
     *
     * @access public
     * @return void
     */
    public function solicitors_setting() {
        global $wpdb, $post;

        ?>
        <tr valign="top">
            <th scope="row" class="titledesc"><?php _e( 'Solicitors', 'propertyhive' ) ?></th>
            <td class="forminp">
                <table class="ph_solicitors widefat" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="name"><?php _e( 'Name', 'propertyhive' ); ?></th>
                            <th class="email"><?php _e( 'Email Address', 'propertyhive' ); ?></th>
                            <th class="settings">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php

                            $current_moving_cost_calculator_options = get_option( 'propertyhive_moving_cost_calculator' );
                            $results = array();
                            if ($current_moving_cost_calculator_options !== FALSE)
                            {
                                if (isset($current_moving_cost_calculator_options['solicitors']))
                                {
                                    $results = $current_moving_cost_calculator_options['solicitors'];
                                }
                            }

                            if (!empty($results))
                            {
                                foreach ($results as $i => $result)
                                {
                                    echo '<tr>';
                                        echo '<td class="name">' . $result['name'] . '</td>';
                                        echo '<td class="email">' . $result['email'] . '</td>';
                                        echo '<td class="settings">
                                            <a class="button" href="' . admin_url( 'admin.php?page=ph-settings&tab=movingcostcalculator&section=editsolicitor&id=' . $i ) . '">' . __( 'Edit', 'propertyhive' ) . '</a>
                                            <a href="' . admin_url('admin.php?page=ph-settings&tab=movingcostcalculator&action=deletesolicitor&delete=' . $i) . '" class="button">' . __( 'Delete', 'propertyhive' ) . '</a>
                                        </td>';
                                    echo '</tr>';
                                }
                            }
                            else
                            {
                                echo '<tr>';
                                    echo '<td align="center" colspan="3">' . __( 'No solicitors exist', 'propertyhive' ) . '</td>';
                                echo '</tr>';
                            }
                        ?>
                    </tbody>
                </table>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" class="titledesc">
                &nbsp;
            </th>
            <td class="forminp forminp-button">
                <a href="<?php echo admin_url( 'admin.php?page=ph-settings&tab=movingcostcalculator&section=addsolicitor' ); ?>" class="button alignright"><?php echo __( 'Add New Solicitor', 'propertyhive' ); ?></a>
            </td>
        </tr>
        <?php
    }

    /**
     * Get add/edit solicitor settings
     *
     * @return array Array of settings
     */
    public function get_solicitor_settings() {

        global $current_section, $post;

        $current_id = ( !isset( $_REQUEST['id'] ) ) ? '' : sanitize_title( $_REQUEST['id'] );

        $result = array();

        if ($current_id != '')
        {
            // We're editing one

            $current_moving_cost_calculator_options = get_option( 'propertyhive_moving_cost_calculator' );

            $results = $current_moving_cost_calculator_options['solicitors'];

            if (isset($results[$current_id]))
            {
                $result = $results[$current_id];
            }
        }

        $settings = array(

            array( 'title' => __( ( $current_section == 'addsolicitor' ? 'Add Solicitor' : 'Edit Solicitor' ), 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'editrecord' ),

            array(
                'title' => __( 'Solicitor Name', 'propertyhive' ),
                'id'        => 'name',
                'default'   => ( (isset($result['name'])) ? $result['name'] : ''),
                'type'      => 'text',
                'desc_tip'  =>  false,
            ),

            array(
                'title' => __( 'Email Address', 'propertyhive' ),
                'id'        => 'email',
                'default'   => ( (isset($result['email'])) ? $result['email'] : ''),
                'type'      => 'email',
                'desc_tip'  =>  false,
            ),

            array( 'type' => 'sectionend', 'id' => 'editrecord'),
            
        );

        return $settings;
    }

    /**
     * Output list of surveyors
     *
     * @access public
     * @return void
     */
    public function surveyors_setting() {
        global $wpdb, $post;

        ?>
        <tr valign="top">
            <th scope="row" class="titledesc"><?php _e( 'Surveyors', 'propertyhive' ) ?></th>
            <td class="forminp">
                <table class="ph_surveyors widefat" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="name"><?php _e( 'Name', 'propertyhive' ); ?></th>
                            <th class="email"><?php _e( 'Email Address', 'propertyhive' ); ?></th>
                            <th class="settings">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php

                            $current_moving_cost_calculator_options = get_option( 'propertyhive_moving_cost_calculator' );
                            $results = array();
                            if ($current_moving_cost_calculator_options !== FALSE)
                            {
                                if (isset($current_moving_cost_calculator_options['surveyors']))
                                {
                                    $results = $current_moving_cost_calculator_options['surveyors'];
                                }
                            }

                            if (!empty($results))
                            {
                                foreach ($results as $i => $result)
                                {
                                    echo '<tr>';
                                        echo '<td class="name">' . $result['name'] . '</td>';
                                        echo '<td class="email">' . $result['email'] . '</td>';
                                        echo '<td class="settings">
                                            <a class="button" href="' . admin_url( 'admin.php?page=ph-settings&tab=movingcostcalculator&section=editsurveyor&id=' . $i ) . '">' . __( 'Edit', 'propertyhive' ) . '</a>
                                            <a href="' . admin_url('admin.php?page=ph-settings&tab=movingcostcalculator&action=deletesurveyor&delete=' . $i) . '" class="button">' . __( 'Delete', 'propertyhive' ) . '</a>
                                        </td>';
                                    echo '</tr>';
                                }
                            }
                            else
                            {
                                echo '<tr>';
                                    echo '<td align="center" colspan="3">' . __( 'No surveyors exist', 'propertyhive' ) . '</td>';
                                echo '</tr>';
                            }
                        ?>
                    </tbody>
                </table>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" class="titledesc">
                &nbsp;
            </th>
            <td class="forminp forminp-button">
                <a href="<?php echo admin_url( 'admin.php?page=ph-settings&tab=movingcostcalculator&section=addsurveyor' ); ?>" class="button alignright"><?php echo __( 'Add New Surveyor', 'propertyhive' ); ?></a>
            </td>
        </tr>
        <?php
    }

    /**
     * Get add/edit surveyor settings
     *
     * @return array Array of settings
     */
    public function get_surveyor_settings() {

        global $current_section, $post;

        $current_id = ( !isset( $_REQUEST['id'] ) ) ? '' : sanitize_title( $_REQUEST['id'] );

        $result = array();

        if ($current_id != '')
        {
            // We're editing one

            $current_moving_cost_calculator_options = get_option( 'propertyhive_moving_cost_calculator' );

            $results = $current_moving_cost_calculator_options['surveyors'];

            if (isset($results[$current_id]))
            {
                $result = $results[$current_id];
            }
        }

        $settings = array(

            array( 'title' => __( ( $current_section == 'addsurveyor' ? 'Add Surveyor' : 'Edit Surveyor' ), 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'editrecord' ),

            array(
                'title' => __( 'Surveyor Name', 'propertyhive' ),
                'id'        => 'name',
                'default'   => ( (isset($result['name'])) ? $result['name'] : ''),
                'type'      => 'text',
                'desc_tip'  =>  false,
            ),

            array(
                'title' => __( 'Email Address', 'propertyhive' ),
                'id'        => 'email',
                'default'   => ( (isset($result['email'])) ? $result['email'] : ''),
                'type'      => 'email',
                'desc_tip'  =>  false,
            ),

            array( 'type' => 'sectionend', 'id' => 'editrecord'),
            
        );

        return $settings;
    }

    /**
     * Output list of removal companies
     *
     * @access public
     * @return void
     */
    public function removal_companies_setting() {
        global $wpdb, $post;

        ?>
        <tr valign="top">
            <th scope="row" class="titledesc"><?php _e( 'Removal/Storage Companies', 'propertyhive' ) ?></th>
            <td class="forminp">
                <table class="ph_removal_companies widefat" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="name"><?php _e( 'Name', 'propertyhive' ); ?></th>
                            <th class="email"><?php _e( 'Email Address', 'propertyhive' ); ?></th>
                            <th class="settings">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php

                            $current_moving_cost_calculator_options = get_option( 'propertyhive_moving_cost_calculator' );
                            $results = array();
                            if ($current_moving_cost_calculator_options !== FALSE)
                            {
                                if (isset($current_moving_cost_calculator_options['removal_companies']))
                                {
                                    $results = $current_moving_cost_calculator_options['removal_companies'];
                                }
                            }

                            if (!empty($results))
                            {
                                foreach ($results as $i => $result)
                                {
                                    echo '<tr>';
                                        echo '<td class="name">' . $result['name'] . '</td>';
                                        echo '<td class="email">' . $result['email'] . '</td>';
                                        echo '<td class="settings">
                                            <a class="button" href="' . admin_url( 'admin.php?page=ph-settings&tab=movingcostcalculator&section=editremovalcompany&id=' . $i ) . '">' . __( 'Edit', 'propertyhive' ) . '</a>
                                            <a href="' . admin_url('admin.php?page=ph-settings&tab=movingcostcalculator&action=deleteremovalcompany&delete=' . $i) . '" class="button">' . __( 'Delete', 'propertyhive' ) . '</a>
                                        </td>';
                                    echo '</tr>';
                                }
                            }
                            else
                            {
                                echo '<tr>';
                                    echo '<td align="center" colspan="3">' . __( 'No removal companies exist', 'propertyhive' ) . '</td>';
                                echo '</tr>';
                            }
                        ?>
                    </tbody>
                </table>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" class="titledesc">
                &nbsp;
            </th>
            <td class="forminp forminp-button">
                <a href="<?php echo admin_url( 'admin.php?page=ph-settings&tab=movingcostcalculator&section=addremovalcompany' ); ?>" class="button alignright"><?php echo __( 'Add New Removal/Storage Company', 'propertyhive' ); ?></a>
            </td>
        </tr>
        <?php
    }

    /**
     * Get add/edit surveyor settings
     *
     * @return array Array of settings
     */
    public function get_removal_company_settings() {

        global $current_section, $post;

        $current_id = ( !isset( $_REQUEST['id'] ) ) ? '' : sanitize_title( $_REQUEST['id'] );

        $result = array();

        if ($current_id != '')
        {
            // We're editing one

            $current_moving_cost_calculator_options = get_option( 'propertyhive_moving_cost_calculator' );

            $results = $current_moving_cost_calculator_options['removal_companies'];

            if (isset($results[$current_id]))
            {
                $result = $results[$current_id];
            }
        }

        $settings = array(

            array( 'title' => __( ( $current_section == 'addsurveyor' ? 'Add Removal/Storage Company' : 'Edit Removal/Storage Company' ), 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'editrecord' ),

            array(
                'title' => __( 'Removal/Storage Company Name', 'propertyhive' ),
                'id'        => 'name',
                'default'   => ( (isset($result['name'])) ? $result['name'] : ''),
                'type'      => 'text',
                'desc_tip'  =>  false,
            ),

            array(
                'title' => __( 'Email Address', 'propertyhive' ),
                'id'        => 'email',
                'default'   => ( (isset($result['email'])) ? $result['email'] : ''),
                'type'      => 'email',
                'desc_tip'  =>  false,
            ),

            array( 'type' => 'sectionend', 'id' => 'editrecord'),
            
        );

        return $settings;
    }

    /**
     * Output list of mortgage advisors
     *
     * @access public
     * @return void
     */
    public function mortgage_advisors_setting() {
        global $wpdb, $post;

        ?>
        <tr valign="top">
            <th scope="row" class="titledesc"><?php _e( 'Mortgage Advisors', 'propertyhive' ) ?></th>
            <td class="forminp">
                <table class="ph_mortgage_advisors widefat" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="name"><?php _e( 'Name', 'propertyhive' ); ?></th>
                            <th class="email"><?php _e( 'Email Address', 'propertyhive' ); ?></th>
                            <th class="arrangement_fee"><?php _e( 'Arrangement Fee', 'propertyhive' ); ?></th>
                            <th class="settings">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php

                            $current_moving_cost_calculator_options = get_option( 'propertyhive_moving_cost_calculator' );
                            $results = array();
                            if ($current_moving_cost_calculator_options !== FALSE)
                            {
                                if (isset($current_moving_cost_calculator_options['mortgage_advisors']))
                                {
                                    $results = $current_moving_cost_calculator_options['mortgage_advisors'];
                                }
                            }

                            if (!empty($results))
                            {
                                foreach ($results as $i => $result)
                                {
                                    echo '<tr>';
                                        echo '<td class="name">' . $result['name'] . '</td>';
                                        echo '<td class="email">' . $result['email'] . '</td>';
                                        echo '<td class="arrangement_fee">&pound;' . number_format($result['arrangement_fee']) . '</td>';
                                        echo '<td class="settings">
                                            <a class="button" href="' . admin_url( 'admin.php?page=ph-settings&tab=movingcostcalculator&section=editmortgageadvisor&id=' . $i ) . '">' . __( 'Edit', 'propertyhive' ) . '</a>
                                            <a href="' . admin_url('admin.php?page=ph-settings&tab=movingcostcalculator&action=deletemortgageadvisor&delete=' . $i) . '" class="button">' . __( 'Delete', 'propertyhive' ) . '</a>
                                        </td>';
                                    echo '</tr>';
                                }
                            }
                            else
                            {
                                echo '<tr>';
                                    echo '<td align="center" colspan="4">' . __( 'No mortgage advisors exist', 'propertyhive' ) . '</td>';
                                echo '</tr>';
                            }
                        ?>
                    </tbody>
                </table>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" class="titledesc">
                &nbsp;
            </th>
            <td class="forminp forminp-button">
                <a href="<?php echo admin_url( 'admin.php?page=ph-settings&tab=movingcostcalculator&section=addmortgageadvisor' ); ?>" class="button alignright"><?php echo __( 'Add New Mortgage Advisor', 'propertyhive' ); ?></a>
            </td>
        </tr>
        <?php
    }

    /**
     * Get add/edit mortgage advisor settings
     *
     * @return array Array of settings
     */
    public function get_mortgage_advisor_settings() {

        global $current_section, $post;

        $current_id = ( !isset( $_REQUEST['id'] ) ) ? '' : sanitize_title( $_REQUEST['id'] );

        $result = array();

        if ($current_id != '')
        {
            // We're editing one

            $current_moving_cost_calculator_options = get_option( 'propertyhive_moving_cost_calculator' );

            $results = $current_moving_cost_calculator_options['mortgage_advisors'];

            if (isset($results[$current_id]))
            {
                $result = $results[$current_id];
            }
        }

        $settings = array(

            array( 'title' => __( ( $current_section == 'addmortgageadvisor' ? 'Add Mortgage Advisor' : 'Edit Mortgage Advisor' ), 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'editrecord' ),

            array(
                'title' => __( 'Mortgage Advisor Name', 'propertyhive' ),
                'id'        => 'name',
                'default'   => ( (isset($result['name'])) ? $result['name'] : ''),
                'type'      => 'text',
                'desc_tip'  =>  false,
            ),

            array(
                'title' => __( 'Email Address', 'propertyhive' ),
                'id'        => 'email',
                'default'   => ( (isset($result['email'])) ? $result['email'] : ''),
                'type'      => 'email',
                'desc_tip'  =>  false,
            ),

            array(
                'title' => __( 'Arrangement Fee (&pound;)', 'propertyhive' ),
                'id'        => 'arrangement_fee',
                'default'   => ( (isset($result['arrangement_fee'])) ? $result['arrangement_fee'] : ''),
                'type'      => 'number',
                'desc_tip'  =>  false,
            ),

            array( 'type' => 'sectionend', 'id' => 'editrecord'),
            
        );

        return $settings;
    }

    /**
     * Output list of financial advisors
     *
     * @access public
     * @return void
     */
    public function financial_advisors_setting() {
        global $wpdb, $post;

        ?>
        <tr valign="top">
            <th scope="row" class="titledesc"><?php _e( 'Financial Advisors', 'propertyhive' ) ?></th>
            <td class="forminp">
                <table class="ph_financial_advisors widefat" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="name"><?php _e( 'Name', 'propertyhive' ); ?></th>
                            <th class="email"><?php _e( 'Email Address', 'propertyhive' ); ?></th>
                            <th class="settings">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php

                            $current_moving_cost_calculator_options = get_option( 'propertyhive_moving_cost_calculator' );
                            $results = array();
                            if ($current_moving_cost_calculator_options !== FALSE)
                            {
                                if (isset($current_moving_cost_calculator_options['financial_advisors']))
                                {
                                    $results = $current_moving_cost_calculator_options['financial_advisors'];
                                }
                            }

                            if (!empty($results))
                            {
                                foreach ($results as $i => $result)
                                {
                                    echo '<tr>';
                                        echo '<td class="name">' . $result['name'] . '</td>';
                                        echo '<td class="email">' . $result['email'] . '</td>';
                                        echo '<td class="settings">
                                            <a class="button" href="' . admin_url( 'admin.php?page=ph-settings&tab=movingcostcalculator&section=editfinancialadvisor&id=' . $i ) . '">' . __( 'Edit', 'propertyhive' ) . '</a>
                                            <a href="' . admin_url('admin.php?page=ph-settings&tab=movingcostcalculator&action=deletefinancialadvisor&delete=' . $i) . '" class="button">' . __( 'Delete', 'propertyhive' ) . '</a>
                                        </td>';
                                    echo '</tr>';
                                }
                            }
                            else
                            {
                                echo '<tr>';
                                    echo '<td align="center" colspan="3">' . __( 'No financial advisors exist', 'propertyhive' ) . '</td>';
                                echo '</tr>';
                            }
                        ?>
                    </tbody>
                </table>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" class="titledesc">
                &nbsp;
            </th>
            <td class="forminp forminp-button">
                <a href="<?php echo admin_url( 'admin.php?page=ph-settings&tab=movingcostcalculator&section=addfinancialadvisor' ); ?>" class="button alignright"><?php echo __( 'Add New Financial Advisor', 'propertyhive' ); ?></a>
            </td>
        </tr>
        <?php
    }

    /**
     * Get add/edit financial advisor settings
     *
     * @return array Array of settings
     */
    public function get_financial_advisor_settings() {

        global $current_section, $post;

        $current_id = ( !isset( $_REQUEST['id'] ) ) ? '' : sanitize_title( $_REQUEST['id'] );

        $result = array();

        if ($current_id != '')
        {
            // We're editing one

            $current_moving_cost_calculator_options = get_option( 'propertyhive_moving_cost_calculator' );

            $results = $current_moving_cost_calculator_options['financial_advisors'];

            if (isset($results[$current_id]))
            {
                $result = $results[$current_id];
            }
        }

        $settings = array(

            array( 'title' => __( ( $current_section == 'addfinancialadvisor' ? 'Add Financial Advisor' : 'Edit Financial Advisor' ), 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'editrecord' ),

            array(
                'title' => __( 'Financial Advisor Name', 'propertyhive' ),
                'id'        => 'name',
                'default'   => ( (isset($result['name'])) ? $result['name'] : ''),
                'type'      => 'text',
                'desc_tip'  =>  false,
            ),

            array(
                'title' => __( 'Email Address', 'propertyhive' ),
                'id'        => 'email',
                'default'   => ( (isset($result['email'])) ? $result['email'] : ''),
                'type'      => 'email',
                'desc_tip'  =>  false,
            ),

            array( 'type' => 'sectionend', 'id' => 'editrecord'),
            
        );

        return $settings;
    }

    /**
     * Uses the Property Hive options API to save settings.
     *
     * @uses propertyhive_update_options()
     * @uses self::get_settings()
     */
    public function save() {
        global $current_section, $post;

        switch ($current_section)
        {
            case 'addsolicitor': 
            {
                $current_moving_cost_calculator_options = get_option( 'propertyhive_moving_cost_calculator' );
                
                if ($current_moving_cost_calculator_options === FALSE)
                {
                    // This is a new option
                    $new_moving_cost_calculator_options = array();
                    $new_moving_cost_calculator_options['solicitors'] = array();
                }
                else
                {
                    $new_moving_cost_calculator_options = $current_moving_cost_calculator_options;
                }

                $record = array(
                    'name' => wp_strip_all_tags( $_POST['name'] ),
                    'email' => wp_strip_all_tags( $_POST['email'] ),
                );

                $new_moving_cost_calculator_options['solicitors'][] = $record;

                update_option( 'propertyhive_moving_cost_calculator', $new_moving_cost_calculator_options );

                break;
            }
            case 'editsolicitor': 
            {
                $current_id = ( !isset( $_REQUEST['id'] ) ) ? '' : sanitize_title( $_REQUEST['id'] );

                $current_moving_cost_calculator_options = get_option( 'propertyhive_moving_cost_calculator' );
                $new_moving_cost_calculator_options = $current_moving_cost_calculator_options;

                $record = array(
                    'name' => wp_strip_all_tags( $_POST['name'] ),
                    'email' => wp_strip_all_tags( $_POST['email'] ),
                );

                $new_moving_cost_calculator_options['solicitors'][$current_id] = $record;

                update_option( 'propertyhive_moving_cost_calculator', $new_moving_cost_calculator_options );

                break;
            }
            case 'addsurveyor': 
            {
                $current_moving_cost_calculator_options = get_option( 'propertyhive_moving_cost_calculator' );
                
                if ($current_moving_cost_calculator_options === FALSE)
                {
                    // This is a new option
                    $new_moving_cost_calculator_options = array();
                    $new_moving_cost_calculator_options['surveyors'] = array();
                }
                else
                {
                    $new_moving_cost_calculator_options = $current_moving_cost_calculator_options;
                }

                $record = array(
                    'name' => wp_strip_all_tags( $_POST['name'] ),
                    'email' => wp_strip_all_tags( $_POST['email'] ),
                );

                $new_moving_cost_calculator_options['surveyors'][] = $record;

                update_option( 'propertyhive_moving_cost_calculator', $new_moving_cost_calculator_options );

                break;
            }
            case 'editsurveyor': 
            {
                $current_id = ( !isset( $_REQUEST['id'] ) ) ? '' : sanitize_title( $_REQUEST['id'] );

                $current_moving_cost_calculator_options = get_option( 'propertyhive_moving_cost_calculator' );
                $new_moving_cost_calculator_options = $current_moving_cost_calculator_options;

                $record = array(
                    'name' => wp_strip_all_tags( $_POST['name'] ),
                    'email' => wp_strip_all_tags( $_POST['email'] ),
                );

                $new_moving_cost_calculator_options['surveyors'][$current_id] = $record;

                update_option( 'propertyhive_moving_cost_calculator', $new_moving_cost_calculator_options );

                break;
            }
            case 'addremovalcompany': 
            {
                $current_moving_cost_calculator_options = get_option( 'propertyhive_moving_cost_calculator' );
                
                if ($current_moving_cost_calculator_options === FALSE)
                {
                    // This is a new option
                    $new_moving_cost_calculator_options = array();
                    $new_moving_cost_calculator_options['removal_companies'] = array();
                }
                else
                {
                    $new_moving_cost_calculator_options = $current_moving_cost_calculator_options;
                }

                $record = array(
                    'name' => wp_strip_all_tags( $_POST['name'] ),
                    'email' => wp_strip_all_tags( $_POST['email'] ),
                );

                $new_moving_cost_calculator_options['removal_companies'][] = $record;

                update_option( 'propertyhive_moving_cost_calculator', $new_moving_cost_calculator_options );

                break;
            }
            case 'editremovalcompany': 
            {
                $current_id = ( !isset( $_REQUEST['id'] ) ) ? '' : sanitize_title( $_REQUEST['id'] );

                $current_moving_cost_calculator_options = get_option( 'propertyhive_moving_cost_calculator' );
                $new_moving_cost_calculator_options = $current_moving_cost_calculator_options;

                $record = array(
                    'name' => wp_strip_all_tags( $_POST['name'] ),
                    'email' => wp_strip_all_tags( $_POST['email'] ),
                );

                $new_moving_cost_calculator_options['removal_companies'][$current_id] = $record;

                update_option( 'propertyhive_moving_cost_calculator', $new_moving_cost_calculator_options );

                break;
            }
            case 'addmortgageadvisor': 
            {
                $current_moving_cost_calculator_options = get_option( 'propertyhive_moving_cost_calculator' );
                
                if ($current_moving_cost_calculator_options === FALSE)
                {
                    // This is a new option
                    $new_moving_cost_calculator_options = array();
                    $new_moving_cost_calculator_options['mortgage_advisors'] = array();
                }
                else
                {
                    $new_moving_cost_calculator_options = $current_moving_cost_calculator_options;
                }

                $arrangement_fee = preg_replace("/[^0-9]/", '', wp_strip_all_tags( $_POST['arrangement_fee'] ));

                $record = array(
                    'name' => wp_strip_all_tags( $_POST['name'] ),
                    'email' => wp_strip_all_tags( $_POST['email'] ),
                    'arrangement_fee' => $arrangement_fee,
                );

                $new_moving_cost_calculator_options['mortgage_advisors'][] = $record;

                update_option( 'propertyhive_moving_cost_calculator', $new_moving_cost_calculator_options );

                break;
            }
            case 'editmortgageadvisor': 
            {
                $current_id = ( !isset( $_REQUEST['id'] ) ) ? '' : sanitize_title( $_REQUEST['id'] );

                $current_moving_cost_calculator_options = get_option( 'propertyhive_moving_cost_calculator' );
                $new_moving_cost_calculator_options = $current_moving_cost_calculator_options;

                $arrangement_fee = preg_replace("/[^0-9]/", '', wp_strip_all_tags( $_POST['arrangement_fee'] ));

                $record = array(
                    'name' => wp_strip_all_tags( $_POST['name'] ),
                    'email' => wp_strip_all_tags( $_POST['email'] ),
                    'arrangement_fee' => $arrangement_fee,
                );

                $new_moving_cost_calculator_options['mortgage_advisors'][$current_id] = $record;

                update_option( 'propertyhive_moving_cost_calculator', $new_moving_cost_calculator_options );

                break;
            }
            case 'addfinancialadvisor': 
            {
                $current_moving_cost_calculator_options = get_option( 'propertyhive_moving_cost_calculator' );
                
                if ($current_moving_cost_calculator_options === FALSE)
                {
                    // This is a new option
                    $new_moving_cost_calculator_options = array();
                    $new_moving_cost_calculator_options['financial_advisors'] = array();
                }
                else
                {
                    $new_moving_cost_calculator_options = $current_moving_cost_calculator_options;
                }

                $record = array(
                    'name' => wp_strip_all_tags( $_POST['name'] ),
                    'email' => wp_strip_all_tags( $_POST['email'] ),
                );

                $new_moving_cost_calculator_options['financial_advisors'][] = $record;

                update_option( 'propertyhive_moving_cost_calculator', $new_moving_cost_calculator_options );

                break;
            }
            case 'editfinancialadvisor': 
            {
                $current_id = ( !isset( $_REQUEST['id'] ) ) ? '' : sanitize_title( $_REQUEST['id'] );

                $current_moving_cost_calculator_options = get_option( 'propertyhive_moving_cost_calculator' );
                $new_moving_cost_calculator_options = $current_moving_cost_calculator_options;

                $record = array(
                    'name' => wp_strip_all_tags( $_POST['name'] ),
                    'email' => wp_strip_all_tags( $_POST['email'] ),
                );

                $new_moving_cost_calculator_options['financial_advisors'][$current_id] = $record;

                update_option( 'propertyhive_moving_cost_calculator', $new_moving_cost_calculator_options );

                break;
            }
            default:
            {
                $current_moving_cost_calculator_options = get_option( 'propertyhive_moving_cost_calculator', array() );

                $propertyhive_moving_cost_calculator = array(
                    'agency_fees_percentage' => ( (isset($_POST['agency_fees_percentage'])) ? preg_replace("/[^0-9.]/", '', $_POST['agency_fees_percentage']) : '' ),
                    'conveyancing_percentage_buying' => ( (isset($_POST['conveyancing_percentage_buying'])) ? preg_replace("/[^0-9.]/", '', $_POST['conveyancing_percentage_buying']) : '' ),
                    'conveyancing_percentage_selling' => ( (isset($_POST['conveyancing_percentage_selling'])) ? preg_replace("/[^0-9.]/", '', $_POST['conveyancing_percentage_selling']) : '' ),
                    'conveyancing_cost_minimum' => ( (isset($_POST['conveyancing_cost_minimum'])) ? preg_replace("/[^0-9.]/", '', $_POST['conveyancing_cost_minimum']) : '' ),
                    'conveyancing_cost_maximum' => ( (isset($_POST['conveyancing_cost_maximum'])) ? preg_replace("/[^0-9.]/", '', $_POST['conveyancing_cost_maximum']) : '' ),
                    'other_legal_costs_buying' => ( (isset($_POST['other_legal_costs_buying'])) ? preg_replace("/[^0-9.]/", '', $_POST['other_legal_costs_buying']) : '' ),
                    'other_legal_costs_selling' => ( (isset($_POST['other_legal_costs_selling'])) ? preg_replace("/[^0-9.]/", '', $_POST['other_legal_costs_selling']) : '' ),
                    'homebuyer_cost' => ( (isset($_POST['homebuyer_cost'])) ? preg_replace("/[^0-9.]/", '', $_POST['homebuyer_cost']) : '' ),
                    'full_survey_cost' => ( (isset($_POST['full_survey_cost'])) ? preg_replace("/[^0-9.]/", '', $_POST['full_survey_cost']) : '' ),
                    'removal_storage_costs' => ( (isset($_POST['removal_storage_costs'])) ? preg_replace("/[^0-9.]/", '', $_POST['removal_storage_costs']) : '' ),
                    'other_moving_costs' => ( (isset($_POST['other_moving_costs'])) ? preg_replace("/[^0-9.]/", '', $_POST['other_moving_costs']) : '' ),
                    'email_third_parties_quote_requests' => ( (isset($_POST['email_third_parties_quote_requests'])) ? $_POST['email_third_parties_quote_requests'] : '' ),
                    'third_party_email_subject' => ( (isset($_POST['third_party_email_subject'])) ? $_POST['third_party_email_subject'] : '' ),
                    'third_party_email_body' => ( (isset($_POST['third_party_email_body'])) ? $_POST['third_party_email_body'] : '' ),
                    'email_me_quote_requests' => ( (isset($_POST['email_me_quote_requests'])) ? $_POST['email_me_quote_requests'] : '' ),
                    'my_email_address' => ( (isset($_POST['my_email_address'])) ? $_POST['my_email_address'] : '' ),
                );

                $new_moving_cost_calculator_options = array_merge( $current_moving_cost_calculator_options, $propertyhive_moving_cost_calculator );

                update_option( 'propertyhive_moving_cost_calculator', $new_moving_cost_calculator_options );
            }
        }
    }

    public function propertyhive_moving_cost_calculator_shortcode( $atts )
    {
        $atts = shortcode_atts( array(
            
        ), $atts );

        //wp_enqueue_style( 'ph-moving-cost-calculator' );

        wp_enqueue_script( 'jquery' );
        wp_enqueue_script( 'ph-moving-cost-calculator' );

        // in JavaScript, object properties are accessed as ajax_object.ajax_url, ajax_object.we_value
        wp_localize_script( 'ph-moving-cost-calculator', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );

        ob_start();

        // get solicitors
        $current_moving_cost_calculator_options = get_option( 'propertyhive_moving_cost_calculator' );

        $solicitors = ( isset($current_moving_cost_calculator_options['solicitors']) ? $current_moving_cost_calculator_options['solicitors'] : array() );
        $surveyors = ( isset($current_moving_cost_calculator_options['surveyors']) ? $current_moving_cost_calculator_options['surveyors'] : array() );
        $removal_companies = ( isset($current_moving_cost_calculator_options['removal_companies']) ? $current_moving_cost_calculator_options['removal_companies'] : array() );
        $mortgage_advisors = ( isset($current_moving_cost_calculator_options['mortgage_advisors']) ? $current_moving_cost_calculator_options['mortgage_advisors'] : array() );
        $financial_advisors = ( isset($current_moving_cost_calculator_options['financial_advisors']) ? $current_moving_cost_calculator_options['financial_advisors'] : array() );

        echo '<script>
            var ph_mcc_solicitors = ' . json_encode($solicitors) . ';
            var ph_mcc_surveyors = ' . json_encode($surveyors) . ';
            var ph_mcc_removal_companies = ' . json_encode($removal_companies) . ';
            var ph_mcc_mortgage_advisors = ' . json_encode($mortgage_advisors) . ';
            var ph_mcc_financial_advisors = ' . json_encode($financial_advisors) . ';

            var ph_mcc_agency_fees_percentage = ' . ( ( isset($current_moving_cost_calculator_options['agency_fees_percentage']) && $current_moving_cost_calculator_options['agency_fees_percentage'] != '' ) ? $current_moving_cost_calculator_options['agency_fees_percentage'] : 1.5 ) . ';
            var ph_mcc_conveyancing_percentage_buying = ' . ( ( isset($current_moving_cost_calculator_options['conveyancing_percentage_buying']) && $current_moving_cost_calculator_options['conveyancing_percentage_buying'] != '' ) ? $current_moving_cost_calculator_options['conveyancing_percentage_buying'] : 0.3 ) . ';
            var ph_mcc_conveyancing_percentage_selling = ' . ( ( isset($current_moving_cost_calculator_options['conveyancing_percentage_selling']) && $current_moving_cost_calculator_options['conveyancing_percentage_selling'] != '' ) ? $current_moving_cost_calculator_options['conveyancing_percentage_selling'] : 0.3 ) . ';
            var ph_mcc_conveyancing_cost_minimum = ' . ( ( isset($current_moving_cost_calculator_options['conveyancing_cost_minimum']) && $current_moving_cost_calculator_options['conveyancing_cost_minimum'] != '' ) ? $current_moving_cost_calculator_options['conveyancing_cost_minimum'] : 600 ) . ';
            var ph_mcc_conveyancing_cost_maximum = ' . ( ( isset($current_moving_cost_calculator_options['conveyancing_cost_maximum']) && $current_moving_cost_calculator_options['conveyancing_cost_maximum'] != '' ) ? $current_moving_cost_calculator_options['conveyancing_cost_maximum'] : 1800 ) . ';
            var ph_mcc_other_legal_costs_buying = ' . ( ( isset($current_moving_cost_calculator_options['other_legal_costs_buying']) && $current_moving_cost_calculator_options['other_legal_costs_buying'] != '' ) ? $current_moving_cost_calculator_options['other_legal_costs_buying'] : 350 ) . ';
            var ph_mcc_other_legal_costs_selling = ' . ( ( isset($current_moving_cost_calculator_options['other_legal_costs_selling']) && $current_moving_cost_calculator_options['other_legal_costs_selling'] != '' ) ? $current_moving_cost_calculator_options['other_legal_costs_selling'] : 40 ) . ';
            var ph_mcc_homebuyer_cost = ' . ( ( isset($current_moving_cost_calculator_options['homebuyer_cost']) && $current_moving_cost_calculator_options['homebuyer_cost'] != '' ) ? $current_moving_cost_calculator_options['homebuyer_cost'] : 540 ) . ';
            var ph_mcc_full_survey_cost = ' . ( ( isset($current_moving_cost_calculator_options['full_survey_cost']) && $current_moving_cost_calculator_options['full_survey_cost'] != '' ) ? $current_moving_cost_calculator_options['full_survey_cost'] : 700 ) . ';
            var ph_mcc_removal_storage_costs = ' . ( ( isset($current_moving_cost_calculator_options['removal_storage_costs']) && $current_moving_cost_calculator_options['removal_storage_costs'] != '' ) ? $current_moving_cost_calculator_options['removal_storage_costs'] : 0 ) . ';
            var ph_mcc_other_moving_costs = ' . ( ( isset($current_moving_cost_calculator_options['other_moving_costs']) && $current_moving_cost_calculator_options['other_moving_costs'] != '' ) ? $current_moving_cost_calculator_options['other_moving_costs'] : 200 ) . ';
        </script>';

        $template = locate_template( array('propertyhive/moving-cost-calculator.php') );
        if ( !$template )
        {
            include( dirname( PH_MOVING_COST_CALCULATOR_PLUGIN_FILE ) . '/templates/moving-cost-calculator.php' );
        }
        else
        {
            include( $template );
        }

        return ob_get_clean();
    }

    public function load_moving_cost_calculator_scripts() {

        $assets_path = str_replace( array( 'http:', 'https:' ), '', untrailingslashit( plugins_url( '/', __FILE__ ) ) ) . '/assets/';

        wp_register_script( 
            'ph-moving-cost-calculator', 
            $assets_path . 'js/propertyhive-moving-cost-calculator.js', 
            array(), 
            PH_MOVING_COST_CALCULATOR_VERSION,
            true
        );
    }

    public function load_moving_cost_calculator_styles() {

        $assets_path = str_replace( array( 'http:', 'https:' ), '', untrailingslashit( plugins_url( '/', __FILE__ ) ) ) . '/assets/';

        wp_register_style( 
            'ph-moving-cost-calculator', 
            $assets_path . 'css/propertyhive-moving-cost-calculator.css', 
            array(), 
            PH_MOVING_COST_CALCULATOR_VERSION
        );
    }
}

endif;

/**
 * Returns the main instance of PH_Moving_Cost_Calculator to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return PH_Moving_Cost_Calculator
 */
function PHMCC() {
    return PH_Moving_Cost_Calculator::instance();
}

PHMCC();

if( is_admin() && file_exists(  dirname( __FILE__ ) . '/propertyhive-moving-cost-calculator-update.php' ) )
{
    include_once( dirname( __FILE__ ) . '/propertyhive-moving-cost-calculator-update.php' );
}