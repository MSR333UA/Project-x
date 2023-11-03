<?php
/**
 * Plugin Name: Property Hive Email Schedule Add On
 * Plugin Uri: http://wp-property-hive.com/addons/email-schedule/
 * Description: Add On for Property Hive which sends an automated email containing a staff members upcoming schedule each day
 * Version: 1.0.3
 * Author: PropertyHive
 * Author URI: http://wp-property-hive.com
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'PH_Email_Schedule' ) ) :

final class PH_Email_Schedule {

    /**
     * @var string
     */
    public $version = '1.0.3';

    /**
     * @var Property Hive The single instance of the class
     */
    protected static $_instance = null;
    
    /**
     * Main Property Hive Email Schedule Instance
     *
     * Ensures only one instance of Property Hive Email Schedule is loaded or can be loaded.
     *
     * @static
     * @return Property Hive Email Schedule - Main instance
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

    	$this->id    = 'emailschedule';
        $this->label = __( 'Email Schedule', 'propertyhive' );

        // Define constants
        $this->define_constants();

        // Include required files
        $this->includes(); 

        add_action( 'admin_init', array( $this, 'run_custom_email_schedule_cron') );

        add_action( 'admin_notices', array( $this, 'email_schedule_error_notices') );

        add_filter( 'propertyhive_settings_tabs_array', array( $this, 'add_settings_tab' ), 19 );
        add_action( 'propertyhive_settings_' . $this->id, array( $this, 'output' ) );
        add_action( 'propertyhive_settings_save_' . $this->id, array( $this, 'save' ) );

        add_filter( "plugin_action_links_" . plugin_basename( __FILE__ ), array( $this, 'plugin_add_settings_link' ) );

        add_action( 'phemailschedulecronhook', array( $this, 'send_email_schedule' ) );

        add_action( 'wp_ajax_propertyhive_dismiss_notice_email_schedule_cron', array( $this, 'ajax_propertyhive_dismiss_notice_email_schedule_cron' ) );
    }

    public function ajax_propertyhive_dismiss_notice_email_schedule_cron()
    {
        update_option( 'email_schedule_cron_notice_dismissed', 'yes' );
        
        // Quit out
        die();
    }

    public function do_recurrence_sql( $do_recurrence_sql )
    {
        return true;
    }

    public function send_email_schedule()
    {
        $current_settings = get_option( 'propertyhive_emailschedule', array() );
        if (!is_array($current_settings))
        {
            $current_settings = array();
        }

        if ( isset($current_settings['active']) && $current_settings['active'] == 'yes' )
        {

        }
        else
        {
            return false;
        }

        add_filter( 'propertyhive_do_recurrence_sql', array( $this, 'do_recurrence_sql' ) );

        /*if ( 
            !defined('DISABLE_WP_CRON') ||
            ( defined('DISABLE_WP_CRON') && DISABLE_WP_CRON === false )
        )
        {
            // Need this to be running as a server cron job.
            //die();
        }*/

        $args = array(
            'number' => 9999,
            'role__not_in' => array('property_hive_contact') 
        );
        $user_query = new WP_User_Query( $args );

        if ( ! empty( $user_query->results ) ) 
        {
            foreach ( $user_query->results as $user ) 
            {
                // For each user
                $email_schedule_last_sent = get_user_meta( $user->ID, '_propertyhive_email_schedule_sent', TRUE );
                if ( $email_schedule_last_sent == date("Y-m-d") )
                {
                    continue;
                }

                // Get all appraisal, viewings etc
                $events = array();

                $start_date = date("Y-m-d") . ' 00:00:00';
                $end_date = date("Y-m-d") . ' 23:59:59';

                // Appraisals
                if ( get_option('propertyhive_module_disabled_appraisals', '') != 'yes' )
                {
                    $args = array(
                        'post_type' => 'appraisal',
                        'nopaging' => true,
                        'meta_key' => '_start_date_time',
                        'orderby' => 'meta_value',
                        'order' => 'ASC',
                        'meta_query' => array(
                            array(
                                'key' => '_start_date_time',
                                'value' => $start_date,
                                'compare' => '>='
                            ),
                            array(
                                'key' => '_start_date_time',
                                'value' => $end_date,
                                'compare' => '<='
                            ),
                            array(
                                'key' => '_negotiator_id',
                                'value' => $user->ID,
                                'compare' => '='
                            ),
                        ),
                    );

                    $appraisals_query = new WP_Query( $args );

                    if ( $appraisals_query->have_posts() )
                    {
                        while ( $appraisals_query->have_posts() )
                        {
                            $appraisals_query->the_post();

                            $start_date_time = get_post_meta( get_the_ID(), '_start_date_time', TRUE );

                            $status = get_post_meta( get_the_ID(), '_status', TRUE );

                            $appraisal = new PH_Appraisal( (int)get_the_ID() );

                            $property_address = '';
                            if ( $appraisal->get_formatted_summary_address() != '' )
                            {
                                $property_address = $appraisal->get_formatted_summary_address();
                            }

                            $details = '';
                            if ( $appraisal->_property_owner_contact_id != '' && get_post_type($appraisal->_property_owner_contact_id) == 'contact' )
                            {
                                $contact = new PH_Contact($appraisal->_property_owner_contact_id);

                                $details = ( $appraisal->_department == 'residential-lettings' ? __( 'Landlord', 'propertyhive' ) : __( 'Owner', 'propertyhive' ) ) . ': ' . get_the_title($appraisal->_property_owner_contact_id);
                                if ( $contact->telephone_number != '' )
                                {
                                    $details .= ' (T: ' . $contact->telephone_number . ')';
                                }
                            }

                            $events[strtotime($start_date_time) . uniqid()] = array(
                                'type' => 'appraisal',
                                'id' => get_the_ID(),
                                'property_address' => $property_address,
                                'title' => ( $status == 'cancelled' ? 'CANCELLED - ' : '' ) . __( 'Appraisal', 'propertyhive' ) . ( $property_address != '' ? ' at ' . $property_address : '' ),
                                'details' => $details,
                                'start' => date( "Y-m-d\TH:i:s", strtotime($start_date_time) ),
                                'end' => date( "Y-m-d\TH:i:s", strtotime($start_date_time) + get_post_meta( get_the_ID(), '_duration', TRUE ) ),
                                'background' => '#693f7b',
                                'url' => get_edit_post_link(get_the_ID(), ''),
                            );
                        }
                    }

                    wp_reset_postdata();
                }

                // Viewings
                if ( get_option('propertyhive_module_disabled_viewings', '') != 'yes' )
                {
                    $args = array(
                        'post_type' => 'viewing',
                        'nopaging' => true,
                        'meta_key' => '_start_date_time',
                        'orderby' => 'meta_value',
                        'order' => 'ASC',
                        'meta_query' => array(
                            array(
                                'key' => '_start_date_time',
                                'value' => $start_date,
                                'compare' => '>='
                            ),
                            array(
                                'key' => '_start_date_time',
                                'value' => $end_date,
                                'compare' => '<='
                            ),
                            array(
                                'key' => '_negotiator_id',
                                'value' => $user->ID,
                                'compare' => '='
                            ),
                        ),
                    );

                    $viewings_query = new WP_Query( $args );

                    if ( $viewings_query->have_posts() )
                    {
                        while ( $viewings_query->have_posts() )
                        {
                            $viewings_query->the_post();

                            $start_date_time = get_post_meta( get_the_ID(), '_start_date_time', TRUE );

                            $status = get_post_meta( get_the_ID(), '_status', TRUE );

                            $property_id = get_post_meta( get_the_ID(), '_property_id', TRUE );
                            $property_address = '';
                            if ( $property_id != '' && get_post_type($property_id) == 'property' )
                            {
                                $property = new PH_Property( (int)$property_id );

                                $property_address = $property->get_formatted_summary_address();
                            }

                            $details = '';

                            $applicant_contact_id = get_post_meta( get_the_ID(), '_applicant_contact_id', TRUE );
                            $applicant_name = '';
                            if ( $applicant_contact_id != '' && get_post_type($applicant_contact_id) == 'contact' )
                            {
                                $applicant_name = get_the_title($applicant_contact_id);

                                $details .= __( 'Applicant', 'propertyhive' ) . ': ' . $applicant_name;
                                if ( get_post_meta( $applicant_contact_id, '_telephone_number', TRUE ) != '' )
                                {
                                    $details .= ' (T: ' . get_post_meta( $applicant_contact_id, '_telephone_number', TRUE ) . ')';
                                }
                            }

                            if ( $property_id != '' && get_post_type($property_id) == 'property' )
                            {
                                $owner_contact_ids = $property->_owner_contact_id;
                                if ( $owner_contact_ids != '' )
                                {
                                    if ( !is_array($owner_contact_ids) )
                                    {
                                        $owner_contact_ids = array($owner_contact_ids);
                                    }

                                    foreach ( $owner_contact_ids as $owner_contact_id )
                                    {
                                        if ( $details != '' )
                                        {
                                            $details .= '<br>';
                                        }
                                        $details .= ( $property->_department == 'residential-lettings' ? __( 'Landlord', 'propertyhive' ) : __( 'Owner', 'propertyhive' ) ) . ': ' . get_the_title($owner_contact_id);
                                        if ( get_post_meta( $owner_contact_id, '_telephone_number', TRUE ) != '' )
                                        {
                                            $details .= ' (T: ' . get_post_meta( $owner_contact_id, '_telephone_number', TRUE ) . ')';
                                        }
                                    }
                                }
                            }

                            $events[strtotime($start_date_time) . uniqid()] = array(
                                'type' => 'viewing',
                                'id' => get_the_ID(),
                                'property_address' => $property_address,
                                'title' => ( $status == 'cancelled' ? 'CANCELLED - ' : '' ) . __( 'Viewing', 'propertyhive' ) . ( $property_address != '' ? ' at ' . $property_address : '' ),
                                'details' => $details,
                                'start' => date( "Y-m-d\TH:i:s", strtotime($start_date_time) ),
                                'end' => date( "Y-m-d\TH:i:s", strtotime($start_date_time) + get_post_meta( get_the_ID(), '_duration', TRUE ) ),
                                'background' => '#39589a',
                                'url' => get_edit_post_link(get_the_ID(), ''),
                            );
                        }
                    }

                    wp_reset_postdata();
                }

                // Key Dates
                if ( get_option('propertyhive_active_departments_lettings') == 'yes' )
                {
                    $meta_query[] = array(
                        array(
                            'key' => '_key_date_status',
                            'value' => 'pending',
                        ),
                    );

                    $upcoming_threshold = new DateTime('+ ' . apply_filters( 'propertyhive_key_date_upcoming_days', 7 ) . ' DAYS');
                    $meta_query[] = array(
                        'key' => '_date_due',
                        'value' => $upcoming_threshold->format('Y-m-d'),
                        'type' => 'date',
                        'compare' => '<=',
                    );

                    $args = array(
                        'post_type' => 'key_date',
                        'nopaging' => true,
                        'fields' => 'ids',
                        'post_status' => 'publish',
                        'meta_query' => $meta_query,
                        'meta_key' => '_date_due',
                        'orderby' => 'meta_value',
                        'order' => 'ASC',
                    );

                    $key_dates_query = new WP_Query( $args );

                    if ( $key_dates_query->have_posts() )
                    {
                        while ( $key_dates_query->have_posts() )
                        {
                            $key_dates_query->the_post();

                            $property_id = get_post_meta( get_the_ID(), '_property_id', TRUE );
                            $property = new PH_Property((int)$property_id);

                            if ( $property->_negotiator_id == $user->ID )
                            {
                                $key_date = new PH_Key_Date( get_post( get_the_ID() ) );

                                $due_date = $key_date->date_due();

                                $property_address = $property->get_formatted_summary_address();

                                $property_edit_link = get_edit_post_link( $property_id );

                                $details = '';
                                $schedule_start_date = new DateTime($start_date);
                                if ( $due_date < $schedule_start_date )
                                {
                                    // was due in the past
                                    $details = '<span style="color:#900">' . __( 'Originally due on', 'propertyhive' ) . ' ' . $due_date->format('jS M') . '</span>';
                                }

                                $tenancy_id = get_post_meta( get_the_ID(), '_tenancy_id', TRUE );
                                if ( !empty($tenancy_id) )
                                {
                                    $key_date_edit_link = get_edit_post_link( $tenancy_id ) . '#propertyhive-tenancy-management%7Cpropertyhive-management-dates';

                                    $tenant_contact_ids = get_post_meta( $tenancy_id, '_applicant_contact_id' );
                                    if ( is_array($tenant_contact_ids) && !empty($tenant_contact_ids) )
                                    {
                                        foreach ( $tenant_contact_ids as $tenant_contact_id )
                                        {
                                            if ( $details != '' ) { $details .= '<br>'; }

                                            $tenant_name = get_the_title($tenant_contact_id);

                                            $details .= __( 'Tenant', 'propertyhive' ) . ': ' . $tenant_name;
                                            if ( get_post_meta( $tenant_contact_id, '_telephone_number', TRUE ) != '' )
                                            {
                                                $details .= ' (T: ' . get_post_meta( $tenant_contact_id, '_telephone_number', TRUE ) . ')';
                                            }
                                        }
                                    }
                                }
                                else
                                {
                                    $key_date_edit_link = $property_edit_link . '#propertyhive-property-tenancies%7Cpropertyhive-management-dates';
                                }

                                $owner_contact_ids = $property->_owner_contact_id;
                                if ( $owner_contact_ids != '' )
                                {
                                    if ( !is_array($owner_contact_ids) )
                                    {
                                        $owner_contact_ids = array($owner_contact_ids);
                                    }

                                    foreach ( $owner_contact_ids as $owner_contact_id )
                                    {
                                        if ( $details != '' ) { $details .= '<br>'; }

                                        $details .= __( 'Landlord', 'propertyhive' ) . ': ' . get_the_title($owner_contact_id);
                                        if ( get_post_meta( $owner_contact_id, '_telephone_number', TRUE ) != '' )
                                        {
                                            $details .= ' (T: ' . get_post_meta( $owner_contact_id, '_telephone_number', TRUE ) . ')';
                                        }
                                    }
                                }

                                $eventID = $due_date->getTimestamp() . uniqid();
                                $events[$eventID] = array(
                                    'type' => 'key_date',
                                    'id' => get_the_ID(),
                                    'property_address' => $property_address,
                                    'title' => $key_date->description() . ' ' . $key_date->status() . ' on ' . $property_address,
                                    'details' => $details,
                                    'start' => $due_date->format('Y-m-d\TH:i:s'),
                                    'end' => $due_date->format('Y-m-d\TH:i:s'),
                                    'background' => '#2e703a',
                                    'url' => $key_date_edit_link,
                                );

                                if ( $due_date->format('H:i') == '00:00' )
                                {
                                    $events[$eventID]['allDay'] = true;
                                }
                            }
                        }
                    }
                    wp_reset_postdata();
                }

                $events = apply_filters( 'propertyhive_email_schedule_events', $events, $start_date, $end_date, $user->ID );

                if ( !empty( $events ) )
                {
                    ksort( $events );

                    // We have events, send the email
                    $subject = __( 'Your Schedule For', 'propertyhive' ) . ' ' . date("l jS F Y");
                    $headers = array(
                        'Content-Type: text/html; charset=UTF-8',
                    );

                    $body = '<html>
<head>

    <title>' . __( 'Your Schedule For', 'propertyhive' ) . ' ' . date("l jS F Y") . '</title>

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

    <style rel="stylesheet" type="text/css">
        @media only screen and (max-width:700px) {

            table {
                width: 100% !important;
            }

            table th {
                display: none !important;
            }

            table td
            {
                /* make the column full width on small screens and allow stacking */
                width: 100% !important;
                display: block !important;
            }
        }
    </style>
</head>
<body>

    <h3 style="text-align:center">' . __( 'Your Schedule For', 'propertyhive' ) . ' ' . date("l jS F Y") . '</h3>
    
    <table width="700" cellpadding="0" cellspacing="0" align="center">
        <tr>
            <td width="700" align="center">

                <table width="100%" cellpadding="8" cellspacing="0">
                    <tr>
                        <th style="font-family: Arial, sans-serif; text-align:left; width:100px; font-size:14px;">' . __( 'Type', 'propertyhive' ) . '</th>
                        <th style="font-family: Arial, sans-serif; text-align:left; width:100px; font-size:14px;">' . __( 'Time', 'propertyhive' ) . '</th>
                        <th style="font-family: Arial, sans-serif; text-align:left; font-size:14px;">' . __( 'Details', 'propertyhive' ) . '</th>
                        <th>&nbsp;</th>
                    </tr>';
    
    foreach ( $events as $event )
    {
        if ( isset($event['allDay']) && $event['allDay'] === true )
        {
            $time_field = 'All Day';
        }
        else
        {
            $time_field = date("H:i", strtotime($event['start']));
            if ( date("H:i", strtotime($event['start'])) !== date("H:i", strtotime($event['end'])) )
            {
                $time_field .= ' - ' . date("H:i", strtotime($event['end']));
            }
        }

        $body .= '<tr>
            <td style="font-family: Arial, sans-serif; font-size:14px;"><div style="padding:5px 0; text-align:center; background:' . $event['background'] . '; color:#FFF;">' . ucwords(str_replace("_", " ", $event['type'])) . '</div></td>
            <td style="font-family: Arial, sans-serif; font-size:14px;">' . $time_field . '</td>
            <td style="font-family: Arial, sans-serif; font-size:14px;">' . $event['title'] . ( ( isset($event['details']) && $event['details'] != '' ) ? '<div style="color:#999">' . $event['details'] . '</div>' : '' ) . '</td>
            <td style="font-family: Arial, sans-serif; font-size:14px;">
                <a href="' . $event['url'] . '" target="_blank">View</a>
                ' . ( ( isset($event['property_address']) && $event['property_address'] != '' ) ? '<br><a href="https://www.google.com/maps?saddr=My+Location&daddr=' . urlencode($event['property_address']) . '" target="_blank">Directions</a>' : '' ) . '
            </td>
        </tr>';
    }

    $body .= '
                </table>

            </td>
        </tr>
    </table>

</body>
</html>';

                    $sent = wp_mail( $user->user_email, $subject, $body, $headers );
                    if ( $sent )
                    {
                        // update user meta so we know it's been sent for today
                        update_user_meta( $user->ID, '_propertyhive_email_schedule_sent', date("Y-m-d") );
                    }
                }
            }
        }
    }

    public function plugin_add_settings_link( $links )
    {
        $settings_link = '<a href="' . admin_url('admin.php?page=ph-settings&tab=emailschedule') . '">' . __( 'Settings' ) . '</a>';
        array_push( $links, $settings_link );
        return $links;
    }

    public function run_custom_email_schedule_cron() 
    {
        if( isset($_GET['custom_email_schedule_cron']) && $_GET['custom_email_schedule_cron'] == 'phemailschedulecronhook' )
        {
            do_action($_GET['custom_email_schedule_cron']);
        }
    }

    private function includes()
    {
        include_once( 'includes/class-ph-email-schedule-install.php' );
    }

    /**
     * Define PH Email Schedule Constants
     */
    private function define_constants() 
    {
        define( 'PH_EMAIL_SCHEDULE_PLUGIN_FILE', __FILE__ );
        define( 'PH_EMAIL_SCHEDULE_VERSION', $this->version );
    }

    /**
     * Output error message if core Property Hive plugin isn't active
     */
    public function email_schedule_error_notices() 
    {
        if ( !is_plugin_active('propertyhive/propertyhive.php') )
        {
            $message = "The Property Hive plugin must be installed and activated before you can use the Property Hive Email Schedule Add On";
            echo "<div class=\"error\"> <p>$message</p></div>";
        }
        if ( get_option('email_schedule_cron_notice_dismissed', '') != 'yes' )
        {
            if ( 
                !defined('DISABLE_WP_CRON') ||
                ( defined('DISABLE_WP_CRON') && DISABLE_WP_CRON === false )
            )
            {
                $message = "Due to the fact the standard WordPress cron does not execute at set times and relies on users visiting the website to execute, we recommend changing to a <a href=\"https://www.siteground.com/tutorials/wordpress/real-cron-job/\" target=\"_blank\">cron job on the server</a> to ensure email schedules get sent accordingly. If you are unsure, we recommend speaking to your hosting company for advice on how to set this up.";
                echo "<div class=\"notice notice-info\" id=\"ph_notice_email_schedule_cron\"><p>$message</p><p><a href=\"\" class=\"button\" id=\"ph_dismiss_notice_email_schedule_cron\">Dismiss</a></p></div>";
    ?>
    <script>
    jQuery( function ( $ ) {

        $( '#ph_dismiss_notice_email_schedule_cron' ).click(function(e)
        {
            e.preventDefault();
            
            var data = {
                'action': 'propertyhive_dismiss_notice_email_schedule_cron'
            };

            $.post( ajaxurl, data, function(response) {
                $( '#ph_notice_email_schedule_cron' ).fadeOut();
            });
        });

    });
    </script>
    <?php
            }
        }
    }

    /**
     * Add a new settings tab to the Property Hive settings tabs array.
     *
     * @param array $settings_tabs Array of Property Hive setting tabs & their labels.
     * @return array $settings_tabs Array of Property Hive setting tabs & their labels.
     */
    public function add_settings_tab( $settings_tabs ) {
        $settings_tabs['emailschedule'] = __( 'Email Schedule', 'propertyhive' );
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

        $settings = $this->get_email_schedule_settings();
        
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

        $current_settings = get_option( 'propertyhive_emailschedule', array() );
        if (!is_array($current_settings))
        {
            $current_settings = array();
        }

        $propertyhive_emailschedule = array(
            'active' => ph_clean($_POST['active']),
        );

        $propertyhive_emailschedule = array_merge($current_settings, $propertyhive_emailschedule);

        update_option( 'propertyhive_emailschedule', $propertyhive_emailschedule );
    }

    /**
     * Get email schedule settings
     *
     * @return array Array of settings
     */
    public function get_email_schedule_settings() {

        $current_settings = get_option( 'propertyhive_emailschedule', array() );

        $settings = array(

            array( 'title' => __( 'Email Schedule', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'email_schedule_settings' )

        );

        $settings[] = array(
            'title' => __( 'Enabled', 'propertyhive' ),
            'id'        => 'active',
            'type'      => 'select',
            'default'   => ( isset($current_settings['active']) ? $current_settings['active'] : ''),
            'options'   => array(
                '' => __( 'No', 'propertyhive'),
                'yes' => __( 'Yes', 'propertyhive'),
            ),
            'desc_tip' => false,
            'desc' => __( 'When enabled emails will be sent daily to users with upcoming viewings and other events', 'propertyhive' ),
        );

        $settings[] = array( 'type' => 'sectionend', 'id' => 'email_schedule_settings');

        return $settings;
    }
}

endif;

/**
 * Returns the main instance of PH_Email_Schedule to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return PH_Email_Schedule
 */
function PHES() {
    return PH_Email_Schedule::instance();
}

PHES();

if( is_admin() && file_exists(  dirname( __FILE__ ) . '/propertyhive-email-schedule-update.php' ) )
{
    include_once( dirname( __FILE__ ) . '/propertyhive-email-schedule-update.php' );
}