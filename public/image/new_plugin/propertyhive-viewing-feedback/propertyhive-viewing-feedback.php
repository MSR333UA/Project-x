<?php
/**
 * Plugin Name: Property Hive Viewing Feedback Add On
 * Plugin Uri: http://wp-property-hive.com/addons/viewing-feedback/
 * Description: Add On for Property Hive allowing the ability to request feedback on a property from an applicant after a viewing.
 * Version: 1.0.4
 * Author: PropertyHive
 * Author URI: http://wp-property-hive.com
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'PH_Viewing_Feedback_Request' ) ) :

final class PH_Viewing_Feedback_Request {

    /**
     * @var string
     */
    public $version = '1.0.4';

    /**
     * @var Property Hive The single instance of the class
     */
    protected static $_instance = null;
    
    /**
     * Main Property Hive Viewing Feedback Request Instance
     *
     * Ensures only one instance of Property Hive Viewing Feedback Request is loaded or can be loaded.
     *
     * @static
     * @return Property Hive Viewing Feedback Request - Main instance
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

        $this->id    = 'viewingfeedbackrequest';
        $this->label = __( 'Viewing Feedback Request', 'propertyhive' );

        // Define constants
        $this->define_constants();

        // Include required files
        $this->includes();

        add_action( 'admin_notices', array( $this, 'viewing_feedback_request_error_notices') );

        add_filter( "plugin_action_links_" . plugin_basename( __FILE__ ), array( $this, 'plugin_add_settings_link' ) );

        add_filter( 'propertyhive_settings_tabs_array', array( $this, 'add_settings_tab' ), 19 );
        add_action( 'propertyhive_settings_' . $this->id, array( $this, 'output' ) );
        add_action( 'propertyhive_settings_save_' . $this->id, array( $this, 'save' ) );

        add_filter( 'propertyhive_admin_viewing_actions', array( $this, 'add_viewing_feedback_request_to_viewing_actions' ), 1, 2 );

        add_action( 'template_include', array( $this, 'do_viewing_feedback_request' ) );

        add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );

        add_action( 'wp_enqueue_scripts', array( $this, 'front_end_js' ) );

        add_action( 'wp_ajax_propertyhive_viewing_feedback_request', array( $this, 'viewing_feedback_request' ) );
        add_action( 'wp_ajax_propertyhive_submit_viewing_feedback', array( $this, 'submit_viewing_feedback' ) );
        add_action( 'wp_ajax_nopriv_propertyhive_submit_viewing_feedback', array( $this, 'submit_viewing_feedback' ) );

    }

    /**
     * Enqueue admin scripts
     */
    function admin_scripts( $hook ) {

        global $wp_query, $post;

        if ( $hook == 'post.php' ) {
        
            $screen       = get_current_screen();

            if ( in_array($screen->post_type, array('contact', 'property', 'viewing')) ) 
            {
                // Register scripts
                wp_register_script( 'propertyhive_viewing_feedback_addon',
                    plugins_url( '/assets/js/admin/property-hive-viewing-feedback-addon.js' , __FILE__ ),
                    array( 'jquery' ),
                    PH_VERSION
                );

                $params = array(
                  'ajax_nonce' => wp_create_nonce('viewing-actions'),
                  'post_id' => $post->ID
                );

                wp_localize_script( 'propertyhive_viewing_feedback_addon', 'propertyhive_viewing_feedback_addon_params', $params );
                wp_enqueue_script('propertyhive_viewing_feedback_addon');
            }
        }
    }

    /**
     * Enqueue front end js
     */
    function front_end_js() {
        if ( isset( $_GET['viewingfeedbackrequest'] ) &&  $_GET['viewingfeedbackrequest'] != '' ){

            wp_register_script( 'propertyhive-viewing-feedback-addon-js',
                plugins_url( '/assets/js/property-hive-viewing-feedback-addon.js' , __FILE__ ),
                array('jquery'),
                PH_VERSION
            );

            $post_id = $_GET['viewingfeedbackrequest'];

            $params = array(
                'ajax_nonce' => wp_create_nonce('viewing-actions'),
                'post_id' => $post_id,
                'ajaxurl' => admin_url('admin-ajax.php')
            );

            wp_localize_script( 'propertyhive-viewing-feedback-addon-js', 'propertyhive_viewing_feedback_addon_js_params', $params );

            wp_enqueue_script('propertyhive-viewing-feedback-addon-js');

        }

    }

    public function plugin_add_settings_link( $links )
    {
        $settings_link = '<a href="' . admin_url('admin.php?page=ph-settings&tab=viewingfeedbackrequest') . '">' . __( 'Settings' ) . '</a>';
        array_push( $links, $settings_link );
        return $links;
    }

    private function get_formatted_applicant_names($names_array)
    {
        $applicant_names_string = '';
        if ( count($names_array) == 1 )
        {
            $applicant_names_string = $names_array[0];
        }
        elseif ( count($names_array) > 1 )
        {
            $last_applicant = array_pop($names_array);
            $applicant_names_string = implode(', ', $names_array) . ' & ' . $last_applicant;
        }
        return $applicant_names_string;
    }

    public function add_viewing_feedback_request_to_viewing_actions( $actions, $post_id )
    {

        $status = get_post_meta( $post_id, '_status', TRUE );

        if ( $status == 'carried_out' ) {
        
            $viewing_feedback_request_sent_at = get_post_meta( $post_id, '_applicant_viewing_feedback_request_sent_at', TRUE );
            $feedback_status = get_post_meta( $post_id, '_feedback_status', TRUE );

            $applicant_contact_ids = get_post_meta( $post_id, '_applicant_contact_id' );

            $applicant_emails = array();
            $applicant_contact_ids = get_post_meta( $post_id, '_applicant_contact_id' );
            foreach ( $applicant_contact_ids as $applicant_contact_id )
            {
                $email_address = get_post_meta( $applicant_contact_id, '_email_address', TRUE );
                if ($email_address != '')
                {
                    $applicant_emails[] = $email_address;
                }
            }

            if ( empty( $feedback_status ) && count($applicant_emails) > 0 ) {
                $feedback_actions = '<a 
                    href="#action_panel_viewing_feedback_request" 
                    class="button viewing-action"
                    style="width:100%; margin-bottom:7px; text-align:center" 
                    >'. wp_kses_post(
                        $viewing_feedback_request_sent_at == '' ? __( 'Email Request For Feedback', 'propertyhive') : __( 'Resend Email Request For Feedback', 'propertyhive')
                    ) . '</a>';

                    
                $feedback_actions .= '<div id="viewing_applicant_request_sent_date" style="text-align:center; font-size:12px; color:#999; margin-bottom:7px;' . 
                    ( ( $viewing_feedback_request_sent_at == '' ) ? 'display:none' : '' ) . '">' . 
                    ( ( $viewing_feedback_request_sent_at != '' ) ? 'Previously sent on <span title="' . $viewing_feedback_request_sent_at . '">' .
                    date("jS F", strtotime($viewing_feedback_request_sent_at)) : '' ) .
                    '</span></div>';

                array_unshift( $actions, $feedback_actions );

            }
        }
        return $actions;
    }

    public function do_viewing_feedback_request($original_template)
    {
        global $post;

        if ( isset($_GET['viewingfeedbackrequest']) && $_GET['viewingfeedbackrequest'] != '' )
        {
            $post_id = (int)$_GET['viewingfeedbackrequest'];

            // Make sure this feedback is being submitted by the person requested            
            if ( get_post_meta( $post_id, '_applicant_viewing_feedback_request_token', TRUE ) != $_GET['token']) 
            {
                echo 'Invalid token provided.';
                die();
            }

            get_post_meta( $post_id, '_property_id', TRUE );

            $post = get_post( $post_id );

            $applicant_contact_ids = array_filter(get_post_meta( $post_id, '_applicant_contact_id' ));
            $applicant_names = array();
            foreach ( $applicant_contact_ids as $applicant_contact_id )
            {
                $applicant_names[] = get_the_title($applicant_contact_id);
            }
            $applicant_names_string = $this->get_formatted_applicant_names($applicant_names);

            $property_id = (int)get_post_meta( $post_id, '_property_id', TRUE );            

            $property = new PH_Property( $property_id );

            $viewing = new PH_Viewing( $post_id );

            if ( $viewing->_feedback_status != '' )
            {
                echo 'Feedback already received.';
                die();
            }

            $template = locate_template( array('propertyhive/viewing-feedback-request-form.php') );
            if ( !$template )
            {
                include( dirname( PH_VIEWING_FEEDBACK_REQUEST_PLUGIN_FILE ) . '/templates/viewing-feedback-request-form.php' );
            }
            else
            {
                include( $template );
            }

            die();

        } else {
            return $original_template;
        }
    }

    /**
     * Define PH Viewing Feedback Request Constants
     */
    private function define_constants() 
    {
        define( 'PH_VIEWING_FEEDBACK_REQUEST_PLUGIN_FILE', __FILE__ );
        define( 'PH_VIEWING_FEEDBACK_REQUEST_VERSION', $this->version );
    }

    private function includes()
    {
        include_once( dirname( __FILE__ ) . "/includes/class-ph-viewing-feedback-install.php" );
    }

    /**
     * Output error message if core Property Hive plugin isn't active
     */
    public function viewing_feedback_request_error_notices() 
    {
        if (!is_plugin_active('propertyhive/propertyhive.php'))
        {
            $message = __( "The Property Hive plugin must be installed and activated before you can use the Property Hive Viewing Feedback Request add-on", 'propertyhive' );
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
        
        propertyhive_admin_fields( self::get_viewing_feedback_request_settings() );
    }

    /**
     * Get viewing_feedback_request settings
     *
     * @return array Array of settings
     */
    public function get_viewing_feedback_request_settings() {

        global $post;

        $current_settings = get_option( 'propertyhive_viewing_feedback_request', array() );

        // Viewing Feedback
        $settings[] = array( 'title' => __( 'Applicant Feedback Email', 'propertyhive' ), 'type' => 'title', 'id' => 'viewing_feedback_request_email_options' );

        $settings[] = array(
            'title'   => __( 'Default Email Subject', 'propertyhive' ),
            'id'      => 'propertyhive_viewing_feedback_request_email_subject',
            'type'    => 'text',
            'css'         => 'min-width:300px;',
        );

        $settings[] = array(
            'title'   => __( 'Default Email Body', 'propertyhive' ),
            'id'      => 'propertyhive_viewing_feedback_request_email_body',
            'type'    => 'textarea',
            'css'         => 'min-width:300px; height:110px;',
        );

        $settings[] = array( 'type' => 'sectionend', 'id' => 'viewing_feedback_request_email_options' );

        return $settings;
    }

    /**
     * Uses the Property Hive options API to save settings.
     */
    public function save() {
        PH_Admin_Settings::save_fields( $this->get_viewing_feedback_request_settings() );
    }




    public function viewing_feedback_request() {

        check_ajax_referer( 'viewing-actions', 'security' );

        $post_id = (int)$_POST['viewing_id'];

        $applicant_contact_ids = array_filter(get_post_meta( $post_id, '_applicant_contact_id' ));
        $property_id = get_post_meta( $post_id, '_property_id', TRUE );

        if ( count($applicant_contact_ids) == 0 || in_array( (int)$property_id, array('', 0) ) )
        {
            $return = array('error' => 'No applicants or property ID');
            echo json_encode( $return );
            die();
        }

        $property = new PH_Property((int)$property_id);

        $to = array();
        $applicant_names = array();
        $applicant_dears = array();
        foreach ( $applicant_contact_ids as $applicant_contact_id )
        {
            $applicant_contact = new PH_Contact((int)$applicant_contact_id);
            $applicant_email = sanitize_email( $applicant_contact->email_address );
            if ( $applicant_email != '' )
            {
                $to[] = $applicant_email;
            }

            $applicant_names[] = $applicant_contact->post_title;
            $applicant_dears[] = $applicant_contact->dear();
        }
        $applicant_names_string = $this->get_formatted_applicant_names($applicant_names);
        $applicant_dears_string = $this->get_formatted_applicant_names($applicant_dears);

        if ( count($to) > 0 )
        {
            $subject = get_option( 'propertyhive_viewing_feedback_request_email_subject', '' );
            $body = get_option( 'propertyhive_viewing_feedback_request_email_body', '' );

            $subject = str_replace('[property_address]', $property->get_formatted_full_address(), $subject);

            $token = get_post_meta( $post_id, '_applicant_viewing_feedback_request_token', TRUE );
            if ( $token == '' )
            {
                $token = uniqid();
            }

            $body = str_replace('[property_address]', $property->get_formatted_full_address(), $body);
            $body = str_replace('[applicant_name]', $applicant_names_string, $body);
            $body = str_replace('[applicant_dear]', $applicant_dears_string, $body);
            $body = str_replace('[feedback_url]', get_site_url() . '/?viewingfeedbackrequest=' . $post_id . '&token=' . $token, $body);

            $from = $property->office_email_address;
            if ( sanitize_email($from) == '' )
            {
                $from = get_bloginfo('admin_email');
            }

            $headers = array();
            $headers[] = 'From: ' . get_bloginfo('name') . ' <' . $from . '>';
            $headers[] = 'Content-Type: text/plain; charset=UTF-8';

            wp_mail($to, $subject, $body, $headers);

            $current_user = wp_get_current_user();

            // Add note/comment to viewing
            $comment = array(
                'note_type' => 'action',
                'action' => 'viewing_feedback_requested',
            );

            $data = array(
                'comment_post_ID'      => $post_id,
                'comment_author'       => $current_user->display_name,
                'comment_author_email' => 'propertyhive@noreply.com',
                'comment_author_url'   => '',
                'comment_date'         => date("Y-m-d H:i:s"),
                'comment_content'      => serialize($comment),
                'comment_approved'     => 1,
                'comment_type'         => 'propertyhive_note',
            );

            $comment_id = wp_insert_comment( $data );
    
            update_post_meta( $post_id, '_applicant_viewing_feedback_request_sent_at', date("Y-m-d H:i:s") );
            update_post_meta( $post_id, '_applicant_viewing_feedback_request_token', $token );

            $return = array('success' => true);
        }
        else
        {
            $return = array('error' => 'No applicant email addresses');
        }

        echo json_encode( $return );
        die();
    }

    public function submit_viewing_feedback() {

        check_ajax_referer( 'viewing-actions', 'security' );

        $post_id = (int)$_POST['viewing_id'];

        $interested = ph_clean($_POST['interested']);

        $applicant_contact_ids = get_post_meta( $post_id, '_applicant_contact_id' );
        $applicant_names = array();
        foreach ( $applicant_contact_ids as $applicant_contact_id )
        {
            $applicant_names[] = get_the_title((int)$applicant_contact_id);
        }
        $applicant_names_string = $this->get_formatted_applicant_names($applicant_names);

        $property_id = get_post_meta( $post_id, '_property_id', TRUE );

        if ( (int)$applicant_contact_id == '' || (int)$property_id == '' || (int)$applicant_contact_id == 0 || (int)$property_id == 0 )
        {
            die();
        }

        $property = new PH_Property((int)$property_id);

        $to = $property->office_email_address;
        if ( $to == '' )
        {
            $to = get_option('admin_email');
        }
       
        $subject = 'Viewing Feedback Received';
      
        $has_or_have = strpos($applicant_names_string, '&') ? ' have ' : ' has ';
        $body = $applicant_names_string . $has_or_have . 'submitted viewing feedback for ' . $property->get_formatted_full_address(). ".\n\n";
        $body .= 'Visit the URL below to view their feedback:' . "\n\n" . admin_url('post.php?post=' . $post_id . '&action=edit');

        $from = get_option('admin_email');

        $headers = array();
        $headers[] = 'From: ' . get_bloginfo('name') . ' <' . $from . '>';
        $headers[] = 'Content-Type: text/plain; charset=UTF-8';

        wp_mail($to, $subject, $body, $headers);

        // Add note/comment to viewing
        $comment = array(
            'note_type' => 'action',
            'action' => 'viewing_feedback_submitted by ' . $applicant_names_string
        );

        $data = array(
            'comment_post_ID'      => $post_id,
            'comment_author'       => $applicant_names_string,
            'comment_author_email' => 'propertyhive@noreply.com',
            'comment_author_url'   => '',
            'comment_date'         => date("Y-m-d H:i:s"),
            'comment_content'      => serialize($comment),
            'comment_approved'     => 1,
            'comment_type'         => 'propertyhive_note',
        );

        $comment_id = wp_insert_comment( $data );

        update_post_meta( $post_id, '_applicant_viewing_feedback_submited_at', date("Y-m-d H:i:s") );

        update_post_meta( $post_id, '_feedback_status', ( $interested == 'true' ? 'interested' : 'not_interested' ) );
        update_post_meta( $post_id, '_feedback', sanitize_textarea_field( $_POST['feedback'] ) . "\n\n** This feedback was added by the applicant via the online viewing feedback request form." );

        $current_user = wp_get_current_user();

        // Add note/comment to viewing
        $comment = array(
            'note_type' => 'action',
            'action' => ( $interested ? 'viewing_applicant_interested' : 'viewing_applicant_not_interested' ),
        );

        $data = array(
            'comment_post_ID'      => $post_id,
            'comment_author'       => $applicant_names_string,
            'comment_author_email' => 'propertyhive@noreply.com',
            'comment_author_url'   => '',
            'comment_date'         => date("Y-m-d H:i:s"),
            'comment_content'      => serialize($comment),
            'comment_approved'     => 1,
            'comment_type'         => 'propertyhive_note',
        );
        $comment_id = wp_insert_comment( $data );

        $interest_template = ($interested == 'true' ? 'viewing-feedback-request-interested-thanks.php' : 'viewing-feedback-request-not-interested-thanks.php');

        $template = locate_template( array('propertyhive/' . $interest_template . '') );
        if ( !$template ) {

            include( dirname( PH_VIEWING_FEEDBACK_REQUEST_PLUGIN_FILE ) . "/templates/" . $interest_template );

        } else {

            include( $template );
        
        }

        die();
    }

}



endif;

/**
 * Returns the main instance of PH_Viewing_Feedback_Request to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return PH_Viewing_Feedback_Request
 */
function PHVFR() {
    return PH_Viewing_Feedback_Request::instance();
}

PHVFR();

if( is_admin() && file_exists(  dirname( __FILE__ ) . '/propertyhive-viewing-feedback-update.php' ) )
{
    include_once( dirname( __FILE__ ) . '/propertyhive-viewing-feedback-update.php' );
}