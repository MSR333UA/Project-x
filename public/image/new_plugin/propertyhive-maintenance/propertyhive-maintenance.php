<?php
/**
 * Plugin Name: Property Hive Property Maintenance Add On
 * Plugin Uri: http://wp-property-hive.com/addons/property-maintenance-jobs/
 * Description: Add On for Property Hive allowing you to manage property maintenance jobs
 * Version: 1.0.5
 * Author: PropertyHive
 * Author URI: http://wp-property-hive.com
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'PH_Maintenance' ) ) :

final class PH_Maintenance {

    /**
     * @var string
     */
    public $version = '1.0.5';

    /**
     * @var PropertyHive The single instance of the class
     */
    protected static $_instance = null;
    
    /**
     * Main PropertyHive Maintenance Instance
     *
     * Ensures only one instance of PropertyHive Maintenance is loaded or can be loaded.
     *
     * @static
     * @return PropertyHive Maintenance - Main instance
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

        $this->id    = 'maintenance';
        $this->label = __( 'Property Maintenance', 'propertyhive' );

        // Define constants
        $this->define_constants();

        // Include required files
        $this->includes(); 

        add_action( 'init', array( $this, 'register_post_type' ), 5 );

        add_action( 'admin_menu', array( $this, 'admin_menu' ) );

        add_action( 'admin_notices', array( $this, 'maintenance_error_notices') );

        add_filter( 'enter_title_here', array( $this, 'enter_title_here' ), 1, 2 );

        //add_filter( 'propertyhive_settings_tabs_array', array( $this, 'add_settings_tab' ), 18 );
        //add_action( 'propertyhive_settings_' . $this->id, array( $this, 'output' ) );
        //add_action( 'propertyhive_settings_save_' . $this->id, array( $this, 'save' ) );

        add_action( 'restrict_manage_posts', array( $this, 'restrict_manage_posts' ) );
        add_filter( 'request', array( $this, 'request_query' ) );

        add_filter( 'manage_edit-maintenance_job_columns', array( $this, 'edit_columns' ) );
        add_action( 'manage_maintenance_job_posts_custom_column', array( $this, 'custom_columns' ), 2 );

        add_action( 'propertyhive_admin_field_maintenance_statuses', array( $this, 'maintenance_statuses_settings' ) );

        add_filter( 'propertyhive_post_types_with_tabs', array( $this, 'add_maintenance_jobs_to_post_types_with_tabs') );
        add_filter( 'propertyhive_post_types_with_notes', array( $this, 'add_maintenance_jobs_to_post_types_with_notes') );
        add_filter( 'propertyhive_tabs', array( $this, 'ph_maintenance_job_tabs_and_meta_boxes') );
        add_filter( 'propertyhive_tabs', array( $this, 'ph_property_tabs_and_meta_boxes') );
        add_filter( 'propertyhive_screen_ids', array( $this, 'add_maintenance_job_screen_ids') );

        add_action( 'propertyhive_process_maintenance_job_meta', 'PH_Meta_Box_Maintenance_Job_Property::save', 10, 2 );
        add_action( 'propertyhive_process_maintenance_job_meta', 'PH_Meta_Box_Maintenance_Job_Contractor::save', 20, 2 );
        add_action( 'propertyhive_process_maintenance_job_meta', 'PH_Meta_Box_Maintenance_Job_Details::save', 30, 2 );

        add_filter( 'propertyhive_my_account_pages', array( $this, 'add_maintenance_job_tab_to_landlord_login' ) );

        add_action( 'propertyhive_my_account_section_owner_maintenance_jobs', array( $this, 'propertyhive_my_account_owner_maintenance_jobs' ), 10 );

        add_shortcode( 'report_maintenance_issue_form', array( $this, 'draw_report_maintenance_issue_form' ) );

        add_action( 'wp_ajax_report_maintenance_issue', array( $this, 'report_maintenance_issue_ajax_callback' ) );
        add_action( 'wp_ajax_nopriv_report_maintenance_issue', array( $this, 'report_maintenance_issue_ajax_callback' ) );
    }

    public function restrict_manage_posts()
    {
        global $typenow, $wp_query;

        if ( $typenow == 'maintenance_job' )
        {
            $selected_status = isset( $_GET['_status'] ) && in_array( $_GET['_status'], array( 'pending', 'completed', 'paid', 'cancelled' ) ) ? $_GET['_status'] : '';
        
            // Status filtering
            $output  = '<select name="_status" id="dropdown_maintenance_job_status">';
                
                $output .= '<option value="">All Statuses</option>';

                $output .= '<option value="pending"';
                $output .= selected( 'pending', $selected_status, false );
                $output .= '>' . __( 'Pending', 'propertyhive' ) . '</option>';

                $output .= '<option value="completed"';
                $output .= selected( 'completed', $selected_status, false );
                $output .= '>' . __( 'Completed', 'propertyhive' ) . '</option>';

                $output .= '<option value="paid"';
                $output .= selected( 'paid', $selected_status, false );
                $output .= '>' . __( 'Paid', 'propertyhive' ) . '</option>';

                $output .= '<option value="cancelled"';
                $output .= selected( 'cancelled', $selected_status, false );
                $output .= '>' . __( 'Cancelled', 'propertyhive' ) . '</option>';
                
            $output .= '</select>';

            echo $output;
        }
    }

    public function request_query( $vars )
    {
        global $typenow, $wp_query;

        if ( 'maintenance_job' === $typenow ) 
        {
            if ( !isset($vars['meta_query']) ) { $vars['meta_query'] = array(); }

            if ( ! empty( $_GET['_status'] ) ) 
            {
                $vars['meta_query'][] = array(
                    'key' => '_status',
                    'value' => sanitize_text_field( $_GET['_status'] ),
                );
            }
        }
        
        return $vars;
    }

    public function propertyhive_my_account_owner_maintenance_jobs()
    {
        global $post;

        $template = locate_template( 'propertyhive/account/owner-maintenance-jobs.php' );
        if ( $template == '' )
        {
            $template = dirname( PH_MAINTENANCE_PLUGIN_FILE ) . '/templates/account/owner-maintenance-jobs.php';
        }

        $maintenance_jobs = array();

        $user_id = get_current_user_id();

        $contact = new PH_Contact( '', $user_id );

        // Get properties belonging to this owner
        $args = array(
            'post_type'   => 'property', 
            'nopaging'    => true,
            'post_status'   => 'publish',
            'fields' => 'ids',
            'meta_query'  => array(
                'relation' => 'OR',
                array(
                    'key' => '_owner_contact_id',
                    'value' => ':' . $contact->id . ';',
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => '_owner_contact_id',
                    'value' => ':"' . $contact->id . '";',
                    'compare' => 'LIKE'
                )
            )
        );

        $properties_query = new WP_Query( $args );

        $property_ids = array();

        if ( $properties_query->have_posts() )
        {
            while ( $properties_query->have_posts() )
            {
                $properties_query->the_post();

                $property_ids[] = get_the_id();
            }
        }
        wp_reset_postdata();

        if ( !empty($property_ids) )
        {
            $args = array(
                'post_type'   => 'maintenance_job', 
                'posts_per_page' => 1,
                'post_status'   => 'publish',
                'fields' => 'ids',
                'meta_query'  => array(
                    array(
                        'key' => '_property_id',
                        'value' => $property_ids,
                        'compare' => 'IN'
                    )
                )
            );
            $maintenance_jobs_query = new WP_Query( $args );

            if ( $maintenance_jobs_query->have_posts() )
            {   
                while ( $maintenance_jobs_query->have_posts() )
                {
                    $maintenance_jobs_query->the_post();

                    $maintenance_jobs[] = $post;
                }
            }
            wp_reset_postdata();
        }

        include($template);
    }

    public function draw_report_maintenance_issue_form()
    {
        $assets_path = str_replace( array( 'http:', 'https:' ), '', untrailingslashit( plugins_url( '/', __FILE__ ) ) ) . '/assets/';

        wp_register_script(
            'report-maintenance-issue',
            $assets_path . 'js/report-maintenance-issue.js',
            array('jquery'),
            PH_MAINTENANCE_MANAGEMENT_VERSION,
            true
        );

        wp_enqueue_script('report-maintenance-issue');

        $params = array(
            'ajax_url' => admin_url('admin-ajax.php'),
        );
        wp_localize_script( 'report-maintenance-issue', 'propertyhive_report_maintenance_issue', $params );

        $form_controls = $this->get_report_maintenance_form_fields();

        $form_controls = apply_filters( 'propertyhive_report_maintenance_issue_form_fields', $form_controls );

        $template = locate_template( 'propertyhive/report-maintenance-job.php' );
        if ( $template == '' )
        {
            $template = dirname( PH_MAINTENANCE_PLUGIN_FILE ) . '/templates/report-maintenance-job.php';
        }
        include($template);
    }

    public function report_maintenance_issue_ajax_callback()
    {
        $return = array();

        // Validate
        $errors = array();
        $form_controls = array();

        $form_controls = $this->get_report_maintenance_form_fields();

        $form_controls = apply_filters( 'propertyhive_report_maintenance_issue_form_fields', $form_controls );

        foreach ( $form_controls as $key => $control )
        {
            if ( isset( $control ) && isset( $control['required'] ) && $control['required'] === TRUE )
            {
                // This field is mandatory. Lets check we received it in the post
                if ( ! isset( $_POST[$key] ) || ( isset( $_POST[$key] ) && empty( $_POST[$key] ) ) )
                {
                    $errors[] = __( 'Missing required field', 'propertyhive' ) . ': ' . $key;
                }
            }
            if ( isset( $control['type'] ) && $control['type'] == 'email' && isset( $_POST[$key] ) && ! empty( $_POST[$key] ) && ! is_email( $_POST[$key] ) )
            {
                $errors[] = __( 'Invalid email address provided', 'propertyhive' ) . ': ' . $key;
            }
            if ( in_array( $key, array('recaptcha', 'recaptcha-v3') ) )
            {
                $secret = isset( $control['secret'] ) ? $control['secret'] : '';
                $response = isset( $_POST['g-recaptcha-response'] ) ? ph_clean($_POST['g-recaptcha-response']) : '';

                $response = wp_remote_post(
                    'https://www.google.com/recaptcha/api/siteverify',
                    array(
                        'method' => 'POST',
                        'body' => array( 'secret' => $secret, 'response' => $response ),
                    )
                );
                if ( is_wp_error( $response ) )
                {
                    $errors[] = $response->get_error_message();
                }
                else
                {
                    $response = json_decode($response['body'], TRUE);
                    if ( $response === FALSE )
                    {
                        $errors[] = 'Error decoding response from reCAPTCHA check';
                    }
                    else
                    {
                        if (
                            isset($response['success']) && $response['success'] == true
                            &&
                            (
                                // If we're using Recaptcha V3, check the score
                                // 1.0 is very likely a good interaction, 0.0 is very likely a bot
                                $key == 'recaptcha'
                                ||
                                ( isset($response['score']) && $response['score'] >= 0.5 )
                            )
                        )
                        {

                        }
                        else
                        {
                            $errors[] = 'Failed reCAPTCHA validation';
                        }
                    }
                }
            }

            if ( $key == 'hCaptcha' )
            {
                $secret = isset( $control['secret'] ) ? $control['secret'] : '';
                $response = isset( $_POST['h-captcha-response'] ) ? ph_clean($_POST['h-captcha-response']) : '';

                $response = wp_remote_post(
                    'https://hcaptcha.com/siteverify',
                    array(
                        'method' => 'POST',
                        'body' => array( 'secret' => $secret, 'response' => $response ),
                    )
                );

                if ( is_wp_error( $response ) )
                {
                    $errors[] = $response->get_error_message();
                }
                else
                {
                    $response = json_decode($response['body'], TRUE);
                    if ( $response === FALSE )
                    {
                        $errors[] = 'Error decoding response from hCaptcha check';
                    }
                    else
                    {
                        if ( isset($response['success']) && $response['success'] == true )
                        {

                        }
                        else
                        {
                            $errors[] = 'Failed hCaptcha validation';
                        }
                    }
                }
            }
        }

        if ( !empty($errors) )
        {
            // Failed validation
            $return['success'] = false;
            $return['reason'] = 'validation';
            $return['errors'] = $errors;
        }
        else
        {
            // Try and get office's email address first, else fallback to admin email
            $to = '';
            $office_id = isset($_POST['office_id']) ? $_POST['office_id'] : '';
            if ( $office_id != '' )
            {
                $fields_to_check = array(
                    '_office_email_address_lettings',
                    '_office_email_address_sales',
                    '_office_email_address_commercial',
                );

                foreach ( $fields_to_check as $field_to_check )
                {
                    $to = get_post_meta($office_id, $field_to_check, TRUE);
                    if ( $to != '' )
                    {
                        break;
                    }
                }
            }
            if ( $to == '' )
            {
                $to = get_option( 'admin_email' );
            }

            $subject = __( 'New Maintenance Issue Reported', 'propertyhive' );

            $message = __( "A new maintenance issue has been reported via your website. Please find details of the issue below", 'propertyhive' ) . "\n\n";

            $message = apply_filters( 'propertyhive_maintenance_report_pre_body', $message );

            $form_controls = apply_filters( 'propertyhive_maintenance_report_body_form_fields', $form_controls );

            foreach ($form_controls as $key => $control)
            {
                if ( isset($control['type']) && $control['type'] == 'html' ) { continue; }

                $label = ( isset($control['label']) ) ? $control['label'] : $key;
                $label = ( isset($control['email_label']) ) ? $control['email_label'] : $label;
                $value = ( isset($_POST[$key]) ) ? sanitize_textarea_field($_POST[$key]) : '';

                if ( $key == 'office_id' )
                {
                    $value = isset($_POST[$key]) ? get_the_title((int)$_POST[$key]) : '';
                }

                $message .= strip_tags($label) . ": " . strip_tags($value) . "\n";
            }

            $message = apply_filters( 'propertyhive_maintenance_report_post_body', $message );

            $from_email_address = get_option('propertyhive_email_from_address', '');
            if ( $from_email_address == '' )
            {
                $from_email_address = get_option('admin_email');
            }
            if ( $from_email_address == '' )
            {
                // Should never get here
                $from_email_address = $_POST['email_address'];
            }

            $headers = array();
            if ( isset($_POST['name']) && ! empty($_POST['name']) )
            {
                $headers[] = 'From: ' . html_entity_decode(ph_clean( $_POST['name'] )) . ' <' . sanitize_email( $from_email_address ) . '>';
            }
            else
            {
                $headers[] = 'From: <' . sanitize_email( $from_email_address ) . '>';
            }

            if ( isset($_POST['email_address']) && sanitize_email( $_POST['email_address'] ) != '' )
            {
                $headers[] = 'Reply-To: ' . sanitize_email( $_POST['email_address'] );
            }

            $to = apply_filters( 'propertyhive_maintenance_report_to', $to );
            $subject = apply_filters( 'propertyhive_maintenance_report_subject', $subject );
            $message = apply_filters( 'propertyhive_maintenance_report_body', $message );
            $headers = apply_filters( 'propertyhive_maintenance_report_headers', $headers );

            $sent = wp_mail( $to, $subject, $message, $headers );

            $maintenance_job_details = sanitize_textarea_field($_POST['details']);

            $contact_details_array = array(
                'Name' => isset($_POST['name']) ? $_POST['name'] : ph_clean($_POST['name']),
                'Email Address' => isset($_POST['email_address']) ? $_POST['email_address'] : ph_clean($_POST['email_address']),
                'Telephone Number' => isset($_POST['telephone_number']) ? $_POST['telephone_number'] : ph_clean($_POST['telephone_number']),
            );

            $contact_details_string = '';
            foreach ( $contact_details_array as $title => $value )
            {
                if ( $contact_details_string != '' )
                {
                    $contact_details_string .= "\n";
                }

                if ( $value != '' )
                {
                    $contact_details_string .= $title . ': ' . $value;
                }
            }

            $maintenance_job_details .= "\n\nContact Details:\n" . $contact_details_string;

            $maintenance_post = array(
                'post_title'    => '',
                'post_content'  => '',
                'post_type'  => 'maintenance_job',
                'post_status'   => 'publish',
                'comment_status'    => 'closed',
                'ping_status'    => 'closed',
            );

            // Insert the post into the database
            $maintenance_post_id = wp_insert_post( $maintenance_post );

            add_post_meta( $maintenance_post_id, '_status', 'pending' );
            add_post_meta( $maintenance_post_id, '_details', $maintenance_job_details );
            add_post_meta( $maintenance_post_id, '_acknowledged', 'no' );
            add_post_meta( $maintenance_post_id, '_externally_reported', 'yes' );

            if ( isset($_POST['office_id']) )
            {
                add_post_meta( $maintenance_post_id, '_office_id', $_POST['office_id'] );
            }

            if ( isset($_POST['property_address']) )
            {
                add_post_meta( $maintenance_post_id, '_reported_property_address', ph_clean($_POST['property_address']) );
            }

            $return['success'] = true;
        }

        header( 'Content-Type: application/json; charset=utf-8' );
        echo json_encode( $return );

        // Quit out
        die();
    }

    public function get_report_maintenance_form_fields()
    {
        $form_controls = array();

        if ( is_user_logged_in() )
        {
            $current_user = wp_get_current_user();
        }

        $form_controls['name'] = array(
            'type' => 'text',
            'label' => __( 'Full Name', 'propertyhive' ),
            'show_label' => true,
            'before' => '<div class="control control-name">',
            'required' => true,
        );
        if ( isset( $current_user ) )
        {
            $form_controls['name']['value'] = $current_user->display_name;
        }

        $form_controls['email_address'] = array(
            'type' => 'email',
            'label' => __( 'Email Address', 'propertyhive' ),
            'show_label' => true,
            'before' => '<div class="control control-email_address">',
            'required' => true,
        );
        if ( isset( $current_user ) )
        {
            $form_controls['email_address']['value'] = $current_user->user_email;
        }

        $form_controls['telephone_number'] = array(
            'type' => 'text',
            'label' => __( 'Contact Number', 'propertyhive' ),
            'show_label' => true,
            'before' => '<div class="control control-telephone_number">',
            'required' => true,
        );

        $form_controls['property_address'] = array(
            'type' => 'text',
            'label' => __( 'Property Address', 'propertyhive' ),
            'show_label' => true,
            'before' => '<div class="control control-property_address">',
            'required' => true,
        );

        $form_controls['details'] = array(
            'type' => 'textarea',
            'label' => __( 'Issue Details', 'propertyhive' ),
            'show_label' => true,
            'before' => '<div class="control control-details">',
            'required' => true,
        );

        $offices = array();
        $value = '';

        $args = array(
            'post_type' => 'office',
            'nopaging' => true,
            'orderby' => 'title',
            'order' => 'ASC',
        );

        $office_query = new WP_Query( $args );

        if ( $office_query->have_posts() )
        {
            while ( $office_query->have_posts() )
            {
                $office_query->the_post();

                $offices[get_the_ID()] = get_the_title();

                if ( get_post_meta(get_the_ID(), 'primary', TRUE) == 1 )
                {
                    $value = get_the_ID();
                }
            }
        }
        wp_reset_postdata();

        $form_controls['office_id'] = array(
            'type' => ( (count($offices) <= 1) ? 'hidden' : 'select' ),
            'label' => __( 'Office', 'propertyhive' ),
            'required' => false,
            'show_label' => true,
            'value' => $value,
            'options' => $offices,
        );

        return $form_controls;
    }

    public function add_maintenance_job_tab_to_landlord_login( $pages )
    {
        $user_id = get_current_user_id();

        $contact = new PH_Contact( '', $user_id );

        $contact_types = $contact->contact_types;
        if ( !is_array($contact_types) )
        {
            $contact_types = array($contact_types);
        }

        if ( in_array('owner', $contact_types) )
        {
            // Get properties belonging to this owner
            $args = array(
                'post_type'   => 'property', 
                'nopaging'    => true,
                'post_status'   => 'publish',
                'fields' => 'ids',
                'meta_query'  => array(
                    'relation' => 'OR',
                    array(
                        'key' => '_owner_contact_id',
                        'value' => ':' . $contact->id . ';',
                        'compare' => 'LIKE'
                    ),
                    array(
                        'key' => '_owner_contact_id',
                        'value' => ':"' . $contact->id . '";',
                        'compare' => 'LIKE'
                    )
                )
            );

            $properties_query = new WP_Query( $args );

            $property_ids = array();

            if ( $properties_query->have_posts() )
            {
                while ( $properties_query->have_posts() )
                {
                    $properties_query->the_post();

                    $property_ids[] = get_the_id();
                }
            }
            wp_reset_postdata();

            if ( !empty($property_ids) )
            {
                $args = array(
                    'post_type'   => 'maintenance_job', 
                    'posts_per_page' => 1,
                    'post_status'   => 'publish',
                    'fields' => 'ids',
                    'meta_query'  => array(
                        array(
                            'key' => '_property_id',
                            'value' => $property_ids,
                            'compare' => 'IN'
                        )
                    )
                );
                $maintenance_jobs_query = new WP_Query( $args );

                if ( $maintenance_jobs_query->have_posts() )
                {
                    $pages['owner_maintenance_jobs'] = array(
                        'name' => __( 'Maintenance Jobs', 'propertyhive' ),
                    );
                }
                wp_reset_postdata();
            }
        }

        return $pages;
    }

    /**
     * Change the columns shown in admin.
     */
    public function edit_columns( $existing_columns ) {

        if ( empty( $existing_columns ) && ! is_array( $existing_columns ) ) {
            $existing_columns = array();
        }

        unset( $existing_columns['comments'], $existing_columns['date'] );

        $existing_columns['property'] = __( 'Property', 'propertyhive' );

        $existing_columns['contractor'] = __( 'Contractor', 'propertyhive' );

        $existing_columns['date_carried_out'] = __( 'Work Carried Out', 'propertyhive' );

        $existing_columns['cost'] = __( 'Quote / Cost', 'propertyhive' );

        $existing_columns['status'] = __( 'Status', 'propertyhive' );

        return $existing_columns;
    }

    /**
     * Define our custom columns shown in admin.
     * @param  string $column
     */
    public function custom_columns( $column ) {
        global $post, $propertyhive;

        switch ( $column ) {
            case 'property' :
                
                $property_id = get_post_meta( $post->ID, '_property_id', TRUE );
                if ( $property_id != '' )
                {
                    $property = new PH_Property( (int)$property_id );
                    echo $property->get_formatted_full_address();
                }
                else
                {
                    $reported_property_address = get_post_meta( $post->ID, '_reported_property_address', TRUE );
                    if ( get_post_meta( $post->ID, '_externally_reported', TRUE ) === 'yes' && $reported_property_address !== '' )
                    {
                        echo $reported_property_address;
                    }
                    else
                    {
                        echo '-';
                    }
                }

                break;
            case 'contractor' :
                
                $contractor_id = get_post_meta(get_the_ID(), '_contractor_id', TRUE);
                if ( $contractor_id != '' && $contractor_id != '0' && get_post_type($contractor_id) == 'contact' )
                {
                    echo get_the_title($contractor_id);
                }
                else
                {
                    echo '-';
                }

                break;
            case 'date_carried_out' :
                if ( get_post_meta(get_the_ID(), '_date_carried_out', TRUE) != '' )
                {
                    echo date("jS F Y", strtotime(get_post_meta(get_the_ID(), '_date_carried_out', TRUE)));
                }
                else
                {
                    '-';
                }
                break;
            case 'cost' :
                if ( get_post_meta(get_the_ID(), '_cost', TRUE) != '' )
                {
                    echo get_post_meta(get_the_ID(), '_cost', TRUE);
                }
                else
                {
                    '-';
                }
                break;
            case 'status' :
                
                $status = ucwords(get_post_meta( $post->ID, '_status', TRUE ));

                if ( get_post_meta( $post->ID, '_externally_reported', TRUE ) === 'yes' && get_post_meta( $post->ID, '_acknowledged', TRUE ) === 'no' )
                {
                    $status .= ' - Unconfirmed';
                }

                echo $status;

                break;
            default :
                break;
        }
    }

    /**
     * Change title boxes in admin.
     * @param  string $text
     * @param  object $post
     * @return string
     */
    public function enter_title_here( $text, $post ) {
        if ( is_admin() && $post->post_type == 'maintenance_job' ) {
            return __( 'Job Title (e.g. Leaking Tap)', 'propertyhive' );
        }

        return $text;
    }

    public function add_maintenance_job_screen_ids( $screen_ids = array() )
    {
        $screen_ids[] = 'maintenance_job';
        $screen_ids[] = 'edit-maintenance_job';
        return $screen_ids;
    }

    public function ph_maintenance_job_tabs_and_meta_boxes($tabs)
    {
        $meta_boxes = array();
        $meta_boxes[5] = array(
            'id' => 'propertyhive-maintenance-job-property',
            'title' => __( 'Property', 'propertyhive' ),
            'callback' => 'PH_Meta_Box_Maintenance_Job_Property::output',
            'screen' => 'maintenance_job',
            'context' => 'normal',
            'priority' => 'high'
        );
        $meta_boxes[10] = array(
            'id' => 'propertyhive-maintenance-job-details',
            'title' => __( 'Maintenance Job Details', 'propertyhive' ),
            'callback' => 'PH_Meta_Box_Maintenance_Job_Details::output',
            'screen' => 'maintenance_job',
            'context' => 'normal',
            'priority' => 'high'
        );
        $meta_boxes[15] = array(
            'id' => 'propertyhive-maintenance-job-contractor',
            'title' => __( 'Maintenance Job Contractor', 'propertyhive' ),
            'callback' => 'PH_Meta_Box_Maintenance_Job_Contractor::output',
            'screen' => 'maintenance_job',
            'context' => 'normal',
            'priority' => 'high'
        );

        $meta_boxes = apply_filters( 'propertyhive_maintenance_job_details_meta_boxes', $meta_boxes );
        ksort($meta_boxes);

        $ids = array();
        foreach ($meta_boxes as $meta_box)
        {
            add_meta_box( $meta_box['id'], $meta_box['title'], $meta_box['callback'], $meta_box['screen'], $meta_box['context'], $meta_box['priority'] );
            $ids[] = $meta_box['id'];
        }

        $tabs['tab_maintenance_job_details'] = array(
            'name' => __( 'Maintenance Job Details', 'propertyhive' ),
            'metabox_ids' => $ids,
            'post_type' => 'maintenance_job'
        );

        $meta_boxes = array();
        $meta_boxes[5] = array(
            'id' => 'propertyhive-maintenance-job-history-notes',
            'title' => __( 'Job History &amp; Notes', 'propertyhive' ),
            'callback' => 'PH_Meta_Box_Maintenance_Job_Notes::output',
            'screen' => 'maintenance_job',
            'context' => 'normal',
            'priority' => 'high'
        );

        $meta_boxes = apply_filters( 'propertyhive_maintenance_job_notes_meta_boxes', $meta_boxes );
        ksort($meta_boxes);
        
        $ids = array();
        foreach ($meta_boxes as $meta_box)
        {
            add_meta_box( $meta_box['id'], $meta_box['title'], $meta_box['callback'], $meta_box['screen'], $meta_box['context'], $meta_box['priority'] );
            $ids[] = $meta_box['id'];
        }
        
        $tabs['tab_maintenance_job_notes'] = array(
            'name' => __( 'History &amp; Notes', 'propertyhive' ),
            'metabox_ids' => $ids,
            'post_type' => 'maintenance_job'
        );

        add_meta_box( 'propertyhive-maintenance-job-actions', __( 'Actions', 'propertyhive' ), 'PH_Meta_Box_Maintenance_Job_Actions::output', 'maintenance_job', 'side' );

        return $tabs;
    }

    public function ph_property_tabs_and_meta_boxes($tabs)
    {
        global $post;

        if ( get_post_meta( $post->ID, '_department', TRUE ) == 'residential-lettings' )
        {
            $meta_boxes = array();
            $meta_boxes[5] = array(
                'id' => 'propertyhive-property-maintenance-jobs',
                'title' => __( 'Maintenance Jobs', 'propertyhive' ),
                'callback' => 'PH_Meta_Box_Property_Maintenance_Jobs::output',
                'screen' => 'property',
                'context' => 'normal',
                'priority' => 'high'
            );

            $meta_boxes = apply_filters( 'propertyhive_property_maintenance_jobs_meta_boxes', $meta_boxes );
            ksort($meta_boxes);
            
            $ids = array();
            foreach ($meta_boxes as $meta_box)
            {
                add_meta_box( $meta_box['id'], $meta_box['title'], $meta_box['callback'], $meta_box['screen'], $meta_box['context'], $meta_box['priority'] );
                $ids[] = $meta_box['id'];
            }
            
            $tabs['tab_maintenance_jobs'] = array(
                'name' => __( 'Maintenance Jobs', 'propertyhive' ),
                'metabox_ids' => $ids,
                'post_type' => 'property',
                'ajax_actions' => array( 'get_property_maintenance_jobs_meta_box^' . wp_create_nonce( 'get_property_maintenance_jobs_meta_box' ) ),
            );
        }

        return $tabs;
    }

    /**
     * Admin Menu
     */
    public function admin_menu() 
    {
        $count = '';
        $args = array(
            'post_type' => 'maintenance_job',
            'nopaging' => true,
            'fields' => 'ids',
            'meta_query' => array(
                array(
                    'key' => '_acknowledged',
                    'value' => 'no'
                ),
                array(
                    'key' => '_externally_reported',
                    'value' => 'yes'
                ),
            ),
        );
        $maintenance_query = new WP_Query( $args );
        if ( $maintenance_query->have_posts() )
        {
            $count = ' <span class="update-plugins count-' . $maintenance_query->found_posts . '"><span class="plugin-count">' . $maintenance_query->found_posts . '</span></span>';
        }
        add_submenu_page( 'propertyhive', __( 'Maintenance Jobs', 'propertyhive' ),  __( 'Maintenance Jobs', 'propertyhive' ) . $count, 'manage_propertyhive', 'edit.php?post_type=maintenance_job' );
    }

    public function add_maintenance_jobs_to_post_types_with_tabs( $post_types = array() )
    {
        $post_types[] = 'maintenance_job';
        return $post_types;
    }

    public function add_maintenance_jobs_to_post_types_with_notes( $post_types = array() )
    {
        $post_types[] = 'maintenance_job';
        return $post_types;
    }

    /**
     * Define PH Blmexport Constants
     */
    private function define_constants() 
    {
        define( 'PH_MAINTENANCE_PLUGIN_FILE', __FILE__ );
        define( 'PH_MAINTENANCE_MANAGEMENT_VERSION', $this->version );
    }

    private function includes()
    {
        include( dirname( __FILE__ ) . "/includes/class-ph-maintenance-install.php" );
        include( dirname( __FILE__ ) . "/includes/class-ph-ajax.php" );
        include( dirname( __FILE__ ) . "/includes/meta-boxes/class-ph-meta-box-maintenance-job-property.php" );
        include( dirname( __FILE__ ) . "/includes/meta-boxes/class-ph-meta-box-maintenance-job-details.php" );
        include( dirname( __FILE__ ) . "/includes/meta-boxes/class-ph-meta-box-maintenance-job-contractor.php" );
        include( dirname( __FILE__ ) . "/includes/meta-boxes/class-ph-meta-box-maintenance-job-notes.php" );
        include( dirname( __FILE__ ) . "/includes/meta-boxes/class-ph-meta-box-maintenance-job-actions.php" );
        include( dirname( __FILE__ ) . "/includes/meta-boxes/class-ph-meta-box-property-maintenance-jobs.php" );
    }

    public function register_post_type()
    {
        register_post_type( "maintenance_job",
            apply_filters( 'propertyhive_register_post_type_property',
                array(
                    'labels' => array(
                            'name'                  => __( 'Maintenance Jobs', 'propertyhive' ),
                            'singular_name'         => __( 'Maintenance Job', 'propertyhive' ),
                            'menu_name'             => _x( 'Maintenance Jobs', 'Admin menu name', 'propertyhive' ),
                            'add_new'               => __( 'Add Maintenance Job', 'propertyhive' ),
                            'add_new_item'          => __( 'Add New Maintenance Job', 'propertyhive' ),
                            'edit'                  => __( 'Edit', 'propertyhive' ),
                            'edit_item'             => __( 'Edit Maintenance Job', 'propertyhive' ),
                            'new_item'              => __( 'New Maintenance Job', 'propertyhive' ),
                            'view'                  => __( 'View Maintenance Job', 'propertyhive' ),
                            'view_item'             => __( 'View Maintenance Job', 'propertyhive' ),
                            'search_items'          => __( 'Search Maintenance Jobs', 'propertyhive' ),
                            'not_found'             => __( 'No maintenance jobs found', 'propertyhive' ),
                            'not_found_in_trash'    => __( 'No maintenance jobs found in trash', 'propertyhive' ),
                            'parent'                => __( 'Parent Maintenance Job', 'propertyhive' )
                        ),
                    'description'           => __( 'This is where you can add new maintenance jobs to your site.', 'propertyhive' ),
                    'public'                => false,
                    'show_ui'               => true,
                    'capability_type'       => 'post',
                    'map_meta_cap'          => true,
                    'publicly_queryable'    => false,
                    'exclude_from_search'   => true,
                    'hierarchical'          => false, // Hierarchical causes memory issues - WP loads all records!
                    //'rewrite'                 => $product_permalink ? array( 'slug' => untrailingslashit( $product_permalink ), 'with_front' => false, 'feeds' => true ) : false,
                    'query_var'             => true,
                    'supports'              => array( 'title' ),
                    'show_in_nav_menus'     => false,
                    'show_in_menu'          => false,
                    'show_in_admin_bar'     => false,
                    'show_in_rest'          => false,
                )
            )
        );
    }

    /**
     * Output error message if core Property Hive plugin isn't active
     */
    public function maintenance_error_notices() 
    {
        if (!is_plugin_active('propertyhive/propertyhive.php'))
        {
            $message = "The PropertyHive plugin must be installed and activated before you can use the Property Hive Property Maintenance add-on";
            echo"<div class=\"error\"> <p>$message</p></div>";
        }
        else
        {
            global $wpdb;

            $screen = get_current_screen();

            if ( $screen->id == 'maintenance_job' && isset($_GET['post']) && get_post_type($_GET['post']) == 'maintenance_job' )
            {
                if ( get_post_meta( $_GET['post'], '_externally_reported', TRUE ) === 'yes' && get_post_meta( $_GET['post'], '_acknowledged', TRUE ) === 'no' )
                {
                    ?>
                    <div class="notice notice-info is-dismissible">
                        <p><strong>
                                <?php echo __( 'This maintenance job has been reported from your website.', 'propertyhive' ); ?>
                            </strong>
                        </p>
                    </div>
                    <?php
                }
            }
        }
    }

    /**
     * Add a new settings tab to the Property Hive settings tabs array.
     *
     * @param array $settings_tabs Array of Property Hive setting tabs & their labels
     * @return array $settings_tabs Array of Property Hive setting tabs & their labels
     */
    public function add_settings_tab( $settings_tabs ) {
        $settings_tabs['maintenance'] = __( 'Maintenance', 'propertyhive' );
        return $settings_tabs;
    }

    /**
     * Uses the PropertyHive admin fields API to output settings.
     *
     * @uses propertyhive_admin_fields()
     * @uses self::get_settings()
     */
    public function output() {

        global $current_section, $hide_save_button;

        switch ($current_section)
        {
            case "addstatus":
            {
                propertyhive_admin_fields( self::get_maintenance_status_settings() );
                break;
            }
            case "editstatus":
            {
                propertyhive_admin_fields( self::get_maintenance_status_settings() );
                break;
            }
            default:
            {
                propertyhive_admin_fields( self::get_maintenance_settings() );
            }
        }
    }

    /**
     * Get all the main settings for this plugin
     *
     * @return array Array of settings
     */
    public function get_maintenance_settings() {

        global $current_section, $post;
        
        $settings = array();

        $settings[] = array( 'title' => __( 'Property Maintenance Settings', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'maintenance_settings' );

        $settings[] = array(
            'type'      => 'maintenance_statuses',
        );

        $settings[] = array( 'type' => 'sectionend', 'id' => 'maintenance_settings');

        return $settings;

    }

    /**
     * Output list of portals
     *
     * @access public
     * @return void
     */
    public function maintenance_statuses_settings() {
        global $wpdb, $post;
        
        ?>
        <tr valign="top">
            <th scope="row" class="titledesc">
                &nbsp;
            </th>
            <td class="forminp forminp-button">
                <a href="<?php echo admin_url( 'admin.php?page=ph-settings&tab=maintenance&section=addstatus' ); ?>" class="button alignright"><?php echo __( 'Add New Maintenance Job Status', 'propertyhive' ); ?></a>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" class="titledesc"><?php _e( 'Maintenance Job Statuses', 'propertyhive' ) ?></th>
            <td class="forminp">
                <table class="ph_maintenance_job_statuses widefat" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="active"><?php _e( 'Status', 'propertyhive' ); ?></th>
                            <th class="settings">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $args = array(
                                'hide_empty' => 0
                            );
                            $terms = get_terms( 'maintenance_status', $args );
                            if ( ! empty( $terms ) && ! is_wp_error( $terms ) )
                            {
                                foreach ( $terms as $term )
                                {
                                    echo '<tr>';
                                        echo '<td class="status">' . $term->name . '</td>';
                                        echo '<td class="settings">
                                            <a class="button" href="' . admin_url( 'admin.php?page=ph-settings&tab=maintenance&section=editstatus&id=' . $term->term_id ) . '">' . __( 'Edit', 'propertyhive' ) . '</a>
                                        </td>';
                                    echo '</tr>';
                                }
                            }
                            else
                            {
                                echo '<tr>';
                                    echo '<td align="center" colspan=2"">' . __( 'No maintenance job statuses exist', 'propertyhive' ) . '</td>';
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
                <a href="<?php echo admin_url( 'admin.php?page=ph-settings&tab=maintenance&section=addstatus' ); ?>" class="button alignright"><?php echo __( 'Add New Maintenance Job Status', 'propertyhive' ); ?></a>
            </td>
        </tr>
        <?php
    }

    /**
     * Get add/edit maintenance settings
     *
     * @return array Array of settings
     */
    public function get_maintenance_status_settings() {

        global $current_section, $post;

        $current_id = ( !isset( $_REQUEST['id'] ) ) ? '' : sanitize_title( $_REQUEST['id'] );

        $status_name = '';
        if ($current_id != '')
        {
            // We're editing one
            $term = get_term( $current_id, 'maintenance_status' );
            $status_name = $term->name;
        }

        $settings = array(

            array( 'title' => __( ( $current_section == 'addstatus' ? 'Add Maintenance Job Status' : 'Edit Maintenance Job Status' ), 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'status_details' ),

            array(
                'title' => __( 'Status', 'propertyhive' ),
                'id'        => 'status_name',
                'default'   => $status_name,
                'type'      => 'text',
                'desc_tip'  =>  false,
            ),

            array( 'type' => 'sectionend', 'id' => 'status_details'),

        );

        return $settings;
    }

    /**
     * Uses the PropertyHive options API to save settings.
     *
     * @uses propertyhive_update_options()
     * @uses self::get_settings()
     */
    public function save() {
        global $current_section, $post;

        switch ($current_section)
        {
            case 'addstatus': 
            {
                wp_insert_term( $_POST['status_name'], 'maintenance_status' );

                PH_Admin_Settings::add_message( __( 'Maintenance status added successfully', 'propertyhive' ) . ' ' . '<a href="' . admin_url( 'admin.php?page=ph-settings&tab=maintenance' ) . '">' . __( 'Return to Maintenance Options', 'propertyhive' ) . '</a>' );
                    
                break;
            }
            case 'editstatus': 
            {
                $current_id = ( !isset( $_REQUEST['id'] ) ) ? '' : sanitize_title( $_REQUEST['id'] );

                wp_update_term( $current_id, 'maintenance_status', array( 'name' => $_POST['status_name'] ) );

                PH_Admin_Settings::add_message( __( 'Maintenance updated successfully', 'propertyhive' ) . ' ' . '<a href="' . admin_url( 'admin.php?page=ph-settings&tab=maintenance' ) . '">' . __( 'Return to Maintenance Options', 'propertyhive' ) . '</a>' );
                
                break;
            }
            default: 
            {
                propertyhive_update_options( self::get_maintenance_settings() );
                break;
            }
        }
    }
}

endif;

/**
 * Returns the main instance of PH_Maintenance to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return PH_Maintenance
 */
function PHMJ() {
    return PH_Maintenance::instance();
}

PHMJ();

if( is_admin() && file_exists(  dirname( __FILE__ ) . '/propertyhive-maintenance-update.php' ) )
{
    include_once( dirname( __FILE__ ) . '/propertyhive-maintenance-update.php' );
}