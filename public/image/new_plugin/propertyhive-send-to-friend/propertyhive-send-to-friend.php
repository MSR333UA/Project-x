<?php
/**
 * Plugin Name: Property Hive Send To Friend Add On
 * Plugin Uri: http://wp-property-hive.com/addons/send-to-friend/
 * Description: Add On for Property Hive allowing users to email properties to others
 * Version: 1.0.7
 * Author: PropertyHive
 * Author URI: http://wp-property-hive.com
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'PH_Send_To_Friend' ) ) :

final class PH_Send_To_Friend {

    /**
     * @var string
     */
    public $version = '1.0.7';

    /**
     * @var Property Hive The single instance of the class
     */
    protected static $_instance = null;
    
    /**
     * Main Property Hive Send To Friend Instance
     *
     * Ensures only one instance of Property Hive Send To Friend is loaded or can be loaded.
     *
     * @static
     * @return Property Hive Send To Friend - Main instance
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

        $this->id    = 'sendtofriend';
        $this->label = __( 'Send To Friend', 'propertyhive' );

        // Define constants
        $this->define_constants();

        // Include required files
        $this->includes();

        add_action( 'admin_notices', array( $this, 'send_to_friend_error_notices') );

        add_action( 'propertyhive_property_actions_list_end', array( $this, 'send_to_friend_action' ) );

        add_action( 'wp_ajax_propertyhive_send_to_friend', array( $this, 'send_to_friend_ajax_callback' ) );
        add_action( 'wp_ajax_nopriv_propertyhive_send_to_friend', array( $this, 'send_to_friend_ajax_callback' ) );

        add_shortcode( 'send_to_friend_form', array( $this, 'send_to_friend_form_shortcode' ) );
    }

    /**
     * Define PH Send To Friend Constants
     */
    private function define_constants() 
    {
        define( 'PH_SEND_TO_FRIEND_PLUGIN_FILE', __FILE__ );
        define( 'PH_SEND_TO_FRIEND_VERSION', $this->version );
    }

    private function includes()
    {
        //include_once( dirname( __FILE__ ) . "/includes/class-ph-send-to-friend-install.php" );
    }

    /**
     * Output error message if core Property Hive plugin isn't active
     */
    public function send_to_friend_error_notices() 
    {
        if (!is_plugin_active('propertyhive/propertyhive.php'))
        {
            $message = __( "The Property Hive plugin must be installed and activated before you can use the Property Hive Send To Friend add-on", 'propertyhive' );
            echo"<div class=\"error\"> <p>$message</p></div>";
        }
    }

    public function send_to_friend_ajax_callback()
    {
        $property_id = $_POST['property_id'];

        $return = array();

        // Validate
        $errors = array();

        if ( ! isset( $_POST['property_id'] ) || ( isset( $_POST['property_id'] ) && empty( $_POST['property_id'] ) ) )
        {
            $errors[] = __( 'Property ID is a required field and must be supplied', 'propertyhive' ) . ': ' . $key;
        }
        else
        {
            $post = get_post($_POST['property_id']);
                    
            $form_controls = $this->ph_get_send_to_friend_form_fields();

            $form_controls = apply_filters( 'propertyhive_send_to_friend_form_fields', $form_controls );

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
                            $errors[] = __( 'Error decoding response from reCAPTCHA check', 'propertyhive' );
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
                                $errors[] = __( 'Failed reCAPTCHA validation', 'propertyhive' );
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
            // Passed validation

            $to = $_POST['friend_email_address'];
            $subject = $_POST['name'] . ' ' .  __('Wants To Show You A Property', 'propertyhive' );
            $message = $_POST['name'] . ' has shared a property, ' . get_the_title($_POST['property_id']) . ", with you from the " . get_bloginfo('name') . " website. You can click the link below to view the property in more detail:\n\n" . get_permalink($_POST['property_id']);

            if ( isset($_POST['message']) && $_POST['message'] != '' )
            {
                $message .= "\n\n\"" . $_POST['message'] . "\"";
            }

            $from_email_address = get_option('propertyhive_email_from_address', '');
            if ( $from_email_address == '' )
            {
                $from_email_address = get_option('admin_email', '');
            }
            if ( $from_email_address == '' )
            {
                // Should never get here
                $from_email_address = $_POST['email_address'];
            }

            $headers = array();
            if ( isset($_POST['name']) && ! empty($_POST['name']) )
            {
                $headers[] = 'From: ' . sanitize_text_field( $_POST['name'] ) . ' <' . sanitize_email( $from_email_address ) . '>';
            }
            else
            {
                $headers[] = 'From: <' . sanitize_email( $from_email_address ) . '>';
            }
            if ( isset($_POST['email_address']) && sanitize_email( $_POST['email_address'] ) != '' )
            {
                $headers[] = 'Reply-To: ' . sanitize_email( $_POST['email_address'] );
            }

            $subject = apply_filters( 'propertyhive_property_send_to_friend_subject', $subject, $_POST['property_id'] );
            $message = apply_filters( 'propertyhive_property_send_to_friend_body', $message, $_POST['property_id'] );
            $headers = apply_filters( 'propertyhive_property_send_to_friend_headers', $headers, $_POST['property_id'] );

            $sent = wp_mail( $to, $subject, $message, $headers );
            
            if ( ! $sent )
            {
                $return['success'] = false;
                $return['reason'] = 'nosend';
                $return['errors'] = $errors;
            }
            else
            {
                $return['success'] = true;
            }
        }
            
        header( 'Content-Type: application/json; charset=utf-8' );
        echo json_encode( $return );

        wp_die();
    }

    public function send_to_friend_form_shortcode( $atts )
    {
        global $post, $property;

        ob_start();
        
        $this->propertyhive_send_to_friend_form();
        
        return ob_get_clean();
    }

    public function send_to_friend_action( $actions = array() )
    {
        global $post, $property;

        $template = locate_template( array('propertyhive/send-to-friend.php') );
        if ( !$template )
        {
            include( dirname( PH_SEND_TO_FRIEND_PLUGIN_FILE ) . '/templates/send-to-friend.php' );
        }
        else
        {
            include( $template );
        }

        return $actions;
    }

    private function propertyhive_send_to_friend_form()
    {
        global $post;

        $assets_path = str_replace( array( 'http:', 'https:' ), '', untrailingslashit( plugins_url( '/', __FILE__ ) ) ) . '/assets/';

        wp_register_script( 
            'ph-send-to-friend', 
            $assets_path . 'js/ph-send-to-friend.js', 
            array('jquery'), 
            PH_SEND_TO_FRIEND_VERSION,
            true
        );

        wp_enqueue_script('ph-send-to-friend');

        // Can also use the 'propertyhive_send_to_friend_script_params' filter to pass in a 'redirect_url' 
        // which will redirect to a separate page upon successful subsmission
        $params = array(
            'ajax_url' => admin_url('admin-ajax.php'),
        );
        wp_localize_script( 'ph-send-to-friend', 'propertyhive_send_to_friend', apply_filters( 'propertyhive_send_to_friend_script_params', $params ) );

        $form_controls = $this->ph_get_send_to_friend_form_fields();

        $form_controls = apply_filters( 'propertyhive_send_to_friend_form_fields', $form_controls );

        $template = locate_template( array('propertyhive/send-to-friend-form.php') );
        if ( !$template )
        {
            include( dirname( PH_SEND_TO_FRIEND_PLUGIN_FILE ) . '/templates/send-to-friend-form.php' );
        }
        else
        {
            include( $template );
        }
    }

    private function ph_get_send_to_friend_form_fields()
    {
        global $post;

        $form_controls = array();
    
        $form_controls['property_id'] = array(
            'type' => 'hidden',
            'value' => $post->ID
        );
        
        $form_controls['name'] = array(
            'type' => 'text',
            'label' => __( 'Your Name', 'propertyhive' ),
            'required' => true
        );
        if ( is_user_logged_in() )
        {
            $current_user = wp_get_current_user();

            $form_controls['name']['value'] = $current_user->display_name;
        }

        $form_controls['email_address'] = array(
            'type' => 'email',
            'label' => __( 'Your Email', 'propertyhive' ),
            'required' => true
        );
        if ( is_user_logged_in() )
        {
            $current_user = wp_get_current_user();

            $form_controls['email_address']['value'] = $current_user->user_email;
        }
        
        $form_controls['friend_email_address'] = array(
            'type' => 'email',
            'label' => __( 'Friend\'s Email Address', 'propertyhive' ),
            'required' => true
        );
        
        $form_controls['message'] = array(
            'type' => 'textarea',
            'label' => __( 'Additional Message', 'propertyhive' ),
            'required' => false
        );

        return $form_controls;
    }
}

endif;

/**
 * Returns the main instance of PH_Send_To_Friend to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return PH_Send_To_Friend
 */
function PHSTF() {
    return PH_Send_To_Friend::instance();
}

$PHSTF = PHSTF();

if( is_admin() && file_exists(  dirname( __FILE__ ) . '/propertyhive-send-to-friend-update.php' ) )
{
    include_once( dirname( __FILE__ ) . '/propertyhive-send-to-friend-update.php' );
}