<?php
/**
 * Plugin Name: Property Hive Calendar Add On
 * Plugin Uri: http://wp-property-hive.com/addons/calendar/
 * Description: Add On for Property Hive offering a calendar showing viewings and other time based events
 * Version: 1.0.21
 * Author: PropertyHive
 * Author URI: http://wp-property-hive.com
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once( dirname(__FILE__) . '/vendor/autoload.php' );

use When\When;

if ( ! class_exists( 'PH_Calendar' ) ) :

final class PH_Calendar {

    /**
     * @var string
     */
    public $version = '1.0.21';

    /**
     * @var Property Hive The single instance of the class
     */
    protected static $_instance = null;
    
    /**
     * Main Property Hive Calendar Instance
     *
     * Ensures only one instance of Property Hive Calendar is loaded or can be loaded.
     *
     * @static
     * @return Property Hive Calendar - Main instance
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

    	$this->id    = 'calendar';
        $this->label = __( 'Calendar', 'propertyhive' );

        // Define constants
        $this->define_constants();

        // Include required files
        $this->includes(); 

        add_action( 'admin_notices', array( $this, 'calendar_error_notices') );

        add_action( 'admin_enqueue_scripts', array( $this, 'load_admin_calendar_scripts' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'load_admin_calendar_styles' ) );

        add_action( 'admin_menu', array( $this, 'admin_menu' ) );

        add_filter( 'propertyhive_settings_tabs_array', array( $this, 'add_settings_tab' ), 19 );
        add_action( 'propertyhive_settings_' . $this->id, array( $this, 'output' ) );
        add_action( 'propertyhive_settings_save_' . $this->id, array( $this, 'save' ) );

        add_action( 'wp_ajax_propertyhive_load_events', array( $this, 'ajax_propertyhive_load_events' ) );
        add_action( 'wp_ajax_propertyhive_resized_event', array( $this, 'ajax_propertyhive_resized_event' ) );
        add_action( 'wp_ajax_propertyhive_dragged_event', array( $this, 'ajax_propertyhive_dragged_event' ) );
        add_action( 'wp_ajax_propertyhive_update_view', array( $this, 'ajax_propertyhive_update_view' ) );
        add_action( 'wp_ajax_propertyhive_load_resources', array( $this, 'ajax_propertyhive_load_resources' ) );

        //add_filter( 'posts_fields', array( $this, 'events_posts_fields' ), 10, 2 );
        //add_filter( 'posts_join', array( $this, 'events_posts_join' ), 10, 2 );

        add_action( 'propertyhive_register_post_type', array( $this, 'register_post_type_appointment') );

        add_filter( 'enter_title_here', array( $this, 'enter_title_here' ), 1, 2 );

        add_filter( 'propertyhive_post_types_with_tabs', array( $this, 'add_appointments_to_post_types_with_tabs') );
        add_filter( 'propertyhive_tabs', array( $this, 'ph_appointment_tabs_and_meta_boxes') );
        add_filter( 'propertyhive_screen_ids', array( $this, 'add_appointment_screen_ids') );

        add_action( 'propertyhive_process_appointment_meta', 'PH_Meta_Box_Appointment_Details::save', 10, 2 );

        add_filter( 'propertyhive_show_my_upcoming_appointments_dashboard_widget', array( $this, 'show_my_upcoming_appointments_dashboard_widget') );
        add_filter( 'propertyhive_dashboard_my_upcoming_appointments', array( $this, 'include_appointments_on_dashboard') );

        add_filter( 'propertyhive_email_schedule_events', array( $this, 'include_appointments_in_email_schedule' ), 10, 4 );
    }

    public function include_appointments_in_email_schedule( $events, $start_date, $end_date, $user_id )
    {
        $args = array(
            'post_type' => 'appointment',
            'post_status' => array( 'publish' ),
            'nopaging' => true,
            'meta_key' => '_start_date_time',
            'orderby' => 'meta_value',
            'order' => 'ASC',
            'fields' => 'ids',
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key' => '_assigned_to',
                    'value' => '"' . $user_id  . '"',
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => '_assigned_to',
                    'value' => '',
                ),
            )
        );

        $appointments_query = new WP_Query( $args );

        if ( $appointments_query->have_posts() )
        {
            while ( $appointments_query->have_posts() )
            {
                $appointments_query->the_post();

                $start_date_time = get_post_meta( get_the_ID(), '_start_date_time', TRUE );
                $end_date_time = get_post_meta( get_the_ID(), '_end_date_time', TRUE );

                $diff_secs = strtotime( $end_date_time ) - strtotime( $start_date_time );

                $all_day = get_post_meta( get_the_ID(), '_all_day', TRUE );

                // does it have recurrence
                $recurrence_type = get_post_meta( get_the_ID(), '_recurrence_type', TRUE );
                if ( $recurrence_type != '' )
                {
                    $occurrences = array();

                    // yes it does
                    switch ( $recurrence_type )
                    {
                        case "daily":
                        {
                            if ( strtotime($start_date_time) > strtotime($start_date) )
                            {
                                $r_start_date = $start_date_time;
                            }
                            else
                            {
                                $time = date( "H:i:s", strtotime($start_date_time) );
                                $r_start_date = date( "Y-m-d", strtotime($start_date) ) . ' ' . $time;
                            }

                            if ( strtotime($r_start_date) > strtotime($end_date) )
                            {
                                continue 2;
                            }

                            $r = new When();
                            $r->startDate( new DateTime($r_start_date) )
                              ->freq("daily")
                              ->until(new DateTime($end_date))
                              ->generateOccurrences();

                            $occurrences = $r->occurrences;
                            break;
                        }
                        case "weekly":
                        {
                            $r_start_date = $start_date_time;

                            if ( strtotime($r_start_date) > strtotime($end_date) )
                            {
                                continue 2;
                            }

                            $r = new When();
                            $r->startDate( new DateTime($r_start_date) )
                              ->freq("weekly")
                              ->until(new DateTime($end_date))
                              ->generateOccurrences();

                            $occurrences = $r->occurrences;
                            break;
                        }
                        case "monthly":
                        {
                            $r_start_date = $start_date_time;

                            if ( strtotime($r_start_date) > strtotime($end_date) )
                            {
                                continue 2;
                            }

                            $r = new When();
                            $r->startDate( new DateTime($r_start_date) )
                              ->freq("monthly")
                              ->until(new DateTime($end_date))
                              ->generateOccurrences();

                            $occurrences = $r->occurrences;
                            break;
                        }
                        case "annually":
                        {
                            $r_start_date = $start_date_time;

                            if ( strtotime($r_start_date) > strtotime($end_date) )
                            {
                                continue 2;
                            }

                            $r = new When();
                            $r->startDate( new DateTime($r_start_date) )
                              ->freq("yearly")
                              ->until(new DateTime($end_date))
                              ->generateOccurrences();

                            $occurrences = $r->occurrences;
                            break;
                        }
                    }

                    if ( !empty($occurrences) )
                    {
                        foreach ( $occurrences as $occurrence )
                        {
                            $start = ( ( $all_day == 'yes' ) ? date( "Y-m-d", $occurrence->format('U') ) . 'T00:00:00' : date( "Y-m-d\TH:i:s", $occurrence->format('U') ) );
                            $end = ( ( $all_day == 'yes' ) ? date( "Y-m-d", strtotime( date( "Y-m-d", $occurrence->format('U') + $diff_secs ) . " + 1 day") ) . 'T00:00:00' : date( "Y-m-d\TH:i:s", $occurrence->format('U') + $diff_secs ) ); // +1day as end is exclusive

                            if ( strtotime($start) >= strtotime($start_date) && strtotime($start) <= strtotime($end_date) )
                            {
                                $events[strtotime($start) . uniqid()] = array(
                                    'type' => 'appointment',
                                    'id' => get_the_ID() . '-' . strtotime($start) . '-' . strtotime($end),
                                    'title' => get_the_title(get_the_ID()),
                                    'details' => get_post_meta( get_the_ID(), '_details', TRUE ),
                                    'start' => $start,
                                    'end' => $end ,
                                    'allDay' => ( ( $all_day == 'yes' ) ? true : false ),
                                    'background' => '#0075BC',
                                    'url' => get_edit_post_link(get_the_ID(), ''),
                                );
                            }
                        }
                    }
                }
                else
                {
                    if ( strtotime($start_date_time) >= strtotime($start_date) && strtotime($start_date_time) <= strtotime($end_date) )
                    {
                        $events[strtotime($start_date_time) . uniqid()] = array(
                            'type' => 'appointment',
                            'id' => get_the_ID(),
                            'title' => get_the_title(get_the_ID()),
                            'details' => get_post_meta( get_the_ID(), '_details', TRUE ),
                            'start' => ( ( $all_day == 'yes' ) ? date( "Y-m-d", strtotime($start_date_time) ) . 'T00:00:00' : date( "Y-m-d\TH:i:s", strtotime($start_date_time) ) ),
                            'end' => ( ( $all_day == 'yes' ) ? date( "Y-m-d", strtotime( date( "Y-m-d", strtotime($end_date_time)) . " + 1 day") ) . 'T00:00:00' : date( "Y-m-d\TH:i:s", strtotime($end_date_time) ) ), // +1day as end is exclusive
                            'allDay' => ( ( $all_day == 'yes' ) ? true : false ),
                            'background' => '#0075BC',
                            'url' => get_edit_post_link(get_the_ID(), ''),
                        );
                    }
                }
            }
        }
        wp_reset_postdata();

        return $events;
    }

    public function events_posts_fields( $fields, $wp_query ) 
    {
        global $wpdb;

        $do_recurrence_sql = false;
        
        if (wp_doing_ajax() && isset($_GET['action']) && $_GET['action'] == 'propertyhive_load_events')
        {
            $do_recurrence_sql = true;
        }
        $do_recurrence_sql = apply_filters( 'propertyhive_do_recurrence_sql', $do_recurrence_sql );

        if ( $do_recurrence_sql ) 
        {
            $fields .= " , `".$wpdb->prefix."ph_calendar_recurrence`.* ";
        }

        return $fields;
    }

    public function events_posts_join( $join, $wp_query ) 
    {
        global $wpdb;

        $do_recurrence_sql = false;
        
        if (wp_doing_ajax() && isset($_GET['action']) && $_GET['action'] == 'propertyhive_load_events')
        {
            $do_recurrence_sql = true;
        }
        $do_recurrence_sql = apply_filters( 'propertyhive_do_recurrence_sql', $do_recurrence_sql );

        if ( $do_recurrence_sql ) 
        {
            $join .= " LEFT JOIN `".$wpdb->prefix."ph_calendar_recurrence` ON `".$wpdb->prefix."ph_calendar_recurrence`.`post_id` = `".$wpdb->posts."`.`ID` ";
        }

        return $join;
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
        
        propertyhive_admin_fields( self::get_calendar_settings() );
    }

    /**
     * Get calendar search settings
     *
     * @return array Array of settings
     */
    public function get_calendar_settings() {

        $current_settings = get_option( 'propertyhive_calendar', array() );

        $settings = array(

            array( 'title' => __( 'Calendar Settings', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'calendar_settings' )

        );

        $settings[] = array(
            'title'     => __( 'Week Starts On', 'propertyhive' ),
            'id'        => 'week_start_day',
            'type'      => 'select',
            'default'   => ( isset($current_settings['week_start_day']) ? (int)$current_settings['week_start_day'] : '1'),
            'options'   => array(
                '1' => __( 'Monday', 'propertyhive' ),
                '2' => __( 'Tuesday', 'propertyhive' ),
                '3' => __( 'Wednesday', 'propertyhive' ),
                '4' => __( 'Thursday', 'propertyhive' ),
                '5' => __( 'Friday', 'propertyhive' ),
                '6' => __( 'Saturday', 'propertyhive' ),
                '0' => __( 'Sunday', 'propertyhive' ),
            ),
        );

        $settings[] = array(
            'title'     => __( 'Name Format', 'propertyhive' ),
            'id'        => 'assigned_to_format',
            'type'      => 'select',
            'default'   => ( isset($current_settings['assigned_to_format']) ? $current_settings['assigned_to_format'] : ''),
            'options'   => array(
                '' => __( 'Initials', 'propertyhive' ) . ' (e.g. SH, GB)',
                'extendedinitials' => __( 'Extended Initials', 'propertyhive' ) . ' (e.g. SHa, GBr)',
                'firstinitial' => __( 'First Initial, Last Name', 'propertyhive' ) . ' (e.g. S Harlem, G Brown)',
                'lastinitial' => __( 'First Name, Last Initial', 'propertyhive' ) . ' (e.g. Steve H, Gerald B)',
                'full' => __( 'Full Name', 'propertyhive' ) . ' (e.g. Steve Harlem, Gerald Brown)',
            ),
            'desc' => __( 'The format of the name shown on events within the calendar', 'propertyhive' )
        );

        $settings[] = array( 'type' => 'sectionend', 'id' => 'calendar_settings');

        return $settings;
    }

    /**
     * Uses the Property Hive options API to save settings.
     *
     * @uses propertyhive_update_options()
     * @uses self::get_settings()
     */
    public function save() {

        $existing_propertyhive_calendar = get_option( 'propertyhive_calendar', array() );

        $propertyhive_calendar = array(
            'assigned_to_format' => ( (isset($_POST['assigned_to_format'])) ? $_POST['assigned_to_format'] : '' ),
            'week_start_day' => ( (isset($_POST['week_start_day'])) ? (int)$_POST['week_start_day'] : '1' ),
        );

        update_option( 'propertyhive_calendar', $propertyhive_calendar);
    }

    public function show_my_upcoming_appointments_dashboard_widget( $return )
    {
        return true;
    }

    public function include_appointments_on_dashboard( $return )
    {
        $args = array(
            'post_type' => 'appointment',
            'fields' => 'ids',
            'post_status' => 'publish',
            'meta_query' => array(
                /*array(
                    'key' => '_status',
                    'value' => 'pending'
                ),*/
                array(
                    'key' => '_start_date_time',
                    'value' => date("Y-m-d H:i:s"),
                    'compare' => '>='
                ),
                array(
                    'relation' => 'OR',
                    array(
                        'key' => '_assigned_to',
                        'value' => '"' . get_current_user_id() . '"',
                        'compare' => 'LIKE'
                    ),
                    array(
                        'key' => '_assigned_to',
                        'value' => '',
                    ),
                )
            )
        );

        $appointments_query = new WP_Query( $args );

        if ( $appointments_query->have_posts() )
        {
            while ( $appointments_query->have_posts() )
            {
                $appointments_query->the_post();

                //$appraisal = new PH_Appraisal(get_the_ID());

                $return[] = array(
                    'ID' => get_the_ID(),
                    'edit_link' => get_edit_post_link( get_the_ID() ),
                    'start_date_time' => get_post_meta( get_the_ID(), '_start_date_time', TRUE ),
                    'start_date_time_formatted_Hi_jSFY' => date("H:i jS F Y", strtotime(get_post_meta( get_the_ID(), '_start_date_time', TRUE ))),
                    'start_date_time_timestamp' => strtotime(get_post_meta( get_the_ID(), '_start_date_time', TRUE )),
                    'title' => 'Appointment: ' . get_the_title(get_the_ID()),
                );
            }
        }

        wp_reset_postdata();

        return $return;
    }

    private function includes()
    {
        include_once( 'includes/class-ph-calendar-install.php' );

        include_once( 'includes/meta-boxes/class-ph-meta-box-appointment-details.php' );
    }

    /**
     * Define PH Calendar Constants
     */
    private function define_constants() 
    {
        define( 'PH_CALENDAR_PLUGIN_FILE', __FILE__ );
        define( 'PH_CALENDAR_VERSION', $this->version );
    }

    /**
     * Output error message if core Property Hive plugin isn't active
     */
    public function calendar_error_notices() 
    {
        if (!is_plugin_active('propertyhive/propertyhive.php'))
        {
            $message = "The Property Hive plugin must be installed and activated before you can use the Property Hive Calendar add-on";
            echo"<div class=\"error\"> <p>$message</p></div>";
        }
    }

    /**
     * Admin Menu
     */
    public function admin_menu() 
    {
        add_submenu_page( 'propertyhive', __( 'Calendar', 'propertyhive' ),  __( 'Calendar', 'propertyhive' ) , 'manage_propertyhive', 'propertyhive_calendar', array( $this, 'admin_page' ) );
    }

    public function load_admin_calendar_scripts($hook)
    {
        $options = get_option( 'propertyhive_calendar', array() );

        preg_match('/^(.*)(_page_propertyhive_calendar)$/', $hook, $matches );
        if ( empty($matches) ) 
        {
            return;
        }

        $assets_path = str_replace( array( 'http:', 'https:' ), '', untrailingslashit( plugins_url( '/', __FILE__ ) ) ) . '/assets/';

        wp_enqueue_script( 'ajax-chosen', PH()->plugin_url() . '/assets/js/chosen/ajax-chosen.jquery' . /*$suffix .*/ '.js', array('jquery', 'chosen'), PH_VERSION );

        wp_enqueue_script( 'chosen', PH()->plugin_url() . '/assets/js/chosen/chosen.jquery' . /*$suffix .*/ '.js', array('jquery'), PH_VERSION );

        wp_enqueue_script( 'multiselect', PH()->plugin_url() . '/assets/js/multiselect/jquery.multiselect' . /*$suffix .*/ '.js', array('jquery'), '2.4.18' );

        wp_register_script( 
            'ph-fullcalendar-core', 
            $assets_path . 'js/fullcalendar/core/main.js', 
            array(), 
            '4.2.0',
            true
        );

        wp_register_script( 
            'ph-fullcalendar-daygrid', 
            $assets_path . 'js/fullcalendar/daygrid/main.js', 
            array('ph-fullcalendar-core'), 
            '4.2.0',
            true
        );

        wp_register_script( 
            'ph-fullcalendar-timegrid', 
            $assets_path . 'js/fullcalendar/timegrid/main.js', 
            array('ph-fullcalendar-core'), 
            '4.2.0',
            true
        );

        wp_register_script( 
            'ph-fullcalendar-interaction', 
            $assets_path . 'js/fullcalendar/interaction/main.js', 
            array('ph-fullcalendar-core'), 
            '4.2.0',
            true
        );

        wp_register_script( 
            'moment', 
            $assets_path . 'js/fullcalendar/moment/moment.js', 
            array(), 
            '2.24.0',
            true
        );

        wp_register_script( 
            'ph-fullcalendar-moment', 
            $assets_path . 'js/fullcalendar/moment/main.js', 
            array('moment'), 
            '4.2.0',
            true
        );

        wp_register_script( 
            'ph-fullcalendar-timeline', 
            $assets_path . 'js/fullcalendar-premium/timeline/main.js', 
            array('ph-fullcalendar-core'), 
            '4.2.0',
            true
        );

        wp_register_script( 
            'ph-fullcalendar-resource-common', 
            $assets_path . 'js/fullcalendar-premium/resource-common/main.js', 
            array('ph-fullcalendar-core'), 
            '4.2.0',
            true
        );

        wp_register_script( 
            'ph-fullcalendar-resource-timeline', 
            $assets_path . 'js/fullcalendar-premium/resource-timeline/main.js', 
            array('ph-fullcalendar-core', 'ph-fullcalendar-timeline', 'ph-fullcalendar-resource-common'), 
            '4.2.0',
            true
        );

        /*wp_register_script( 
            'rrule', 
            $assets_path . 'js/rrule.min.js', 
            array(), 
            '2.6.4',
            true
        );

        wp_register_script( 
            'ph-fullcalendar-rrule', 
            $assets_path . 'js/fullcalendar/rrule/main.js', 
            array('rrule', 'ph-fullcalendar-core'), 
            '4.2.0',
            true
        );*/

        wp_register_script( 
            'ph-calendar', 
            $assets_path . 'js/ph-calendar.js', 
            array('ph-fullcalendar-core', 'ph-fullcalendar-daygrid', 'ph-fullcalendar-timegrid', 'ph-fullcalendar-interaction', 'moment', 'ph-fullcalendar-moment', 'ph-fullcalendar-timeline', 'ph-fullcalendar-resource-common', 'ph-fullcalendar-resource-timeline'), 
            PH_CALENDAR_VERSION,
            true
        );

        wp_enqueue_script( 'ph-fullcalendar-core' );
        wp_enqueue_script( 'ph-fullcalendar-daygrid' );
        wp_enqueue_script( 'ph-fullcalendar-timegrid' );
        wp_enqueue_script( 'ph-fullcalendar-interaction' );
        wp_enqueue_script( 'moment' );
        wp_enqueue_script( 'ph-fullcalendar-moment' );
        wp_enqueue_script( 'ph-fullcalendar-timeline' );
        //wp_enqueue_script( 'rrule' );
        //wp_enqueue_script( 'ph-fullcalendar-rrule' );
        wp_enqueue_script( 'ph-calendar' );

        $view = 'week';
        
        $day = date('d', time());
        $month = date('m', time());
        $year = date('Y', time());

        // get array of users for negotiator dropdown
        $args = array(
            'number' => 9999,
            'orderby' => 'display_name',
            'role__not_in' => array('property_hive_contact', 'subscriber') 
        );
        $user_query = new WP_User_Query( $args );

        $negotiators = array();

        if ( ! empty( $user_query->results ) ) 
        {
            foreach ( $user_query->results as $user ) 
            {
                $negotiators[] = array(
                    'id' => $user->ID,
                    'name' => $user->display_name
                );
            }
        }

        $selected_negotiator_ids = get_user_meta( get_current_user_id(), '_propertyhive_calendar_negotiator_ids', TRUE );
        $selected_view = get_user_meta( get_current_user_id(), '_propertyhive_calendar_view', TRUE );

        wp_localize_script( 'ph-calendar', 'ph_calendar', array( 
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'view' => ( ( $selected_view != '' && in_array($selected_view, array('day', 'week', 'month', 'timeline')) ) ? $selected_view : 'week' ), // day, week, month
            'day' => $day,
            'month' => $month,
            'year' => $year,
            'admin_url' => admin_url(),
            'tasks_enabled' => ( class_exists('PH_Tasks') ? true : false ),
            'appraisals_enabled' => ( get_option('propertyhive_module_disabled_appraisals', '') != 'yes' ? true : false ),
            'viewings_enabled' => ( get_option('propertyhive_module_disabled_viewings', '') != 'yes' ? true : false ),
            'negotiators' => $negotiators,
            'selected_negotiators' => $selected_negotiator_ids,
            'week_start_day' => isset($options['week_start_day']) ? (int)$options['week_start_day'] : '1',
        ) );
    }

    public function load_admin_calendar_styles($hook)
    {
        $screen = get_current_screen();
        if ( defined('PH_VERSION') && in_array( $screen->id, array( 'appointment' ) ) )
        {
            wp_enqueue_style( 'chosen', PH()->plugin_url() . '/assets/css/chosen.css', array(), PH_VERSION );
        }

        preg_match('/^(.*)(_page_propertyhive_calendar)$/', $hook, $matches );
        if ( empty($matches) ) 
        {
            return;
        }

        wp_enqueue_style( 'chosen', PH()->plugin_url() . '/assets/css/chosen.css', array(), PH_VERSION );

        wp_enqueue_style( 'multiselect', PH()->plugin_url() . '/assets/css/jquery.multiselect.css', array(), '2.4.18' );

        $assets_path = str_replace( array( 'http:', 'https:' ), '', untrailingslashit( plugins_url( '/', __FILE__ ) ) ) . '/assets/';

        wp_enqueue_style( 
            'ph-fullcalendar-core', 
            $assets_path . 'js/fullcalendar/core/main.css', 
            array(), 
            '4.0.0-beta.2',
            'all'
        );

        wp_enqueue_style( 
            'ph-fullcalendar-daygrid', 
            $assets_path . 'js/fullcalendar/daygrid/main.css', 
            array('ph-fullcalendar-core'), 
            '4.0.0-beta.2',
            'all'
        );

        wp_enqueue_style( 
            'ph-fullcalendar-timegrid', 
            $assets_path . 'js/fullcalendar/timegrid/main.css', 
            array('ph-fullcalendar-core'), 
            '4.0.0-beta.2',
            'all'
        );

        wp_enqueue_style( 
            'ph-fullcalendar-timeline', 
            $assets_path . 'js/fullcalendar-premium/timeline/main.css', 
            array('ph-fullcalendar-core'), 
            '4.2.0',
            'all'
        );

        wp_enqueue_style( 
            'ph-fullcalendar-resource-timeline', 
            $assets_path . 'js/fullcalendar-premium/resource-timeline/main.css', 
            array('ph-fullcalendar-core'), 
            '4.2.0',
            'all'
        );

        wp_enqueue_style( 
            'ph-calendar', 
            $assets_path . 'css/ph-calendar.css', 
            array(), 
            PH_CALENDAR_VERSION,
            'all'
        );
    }

    /**
     * Admin Page
     */
    public function admin_page() 
    {
        $view = 'week'; // day, week, month
        
        $day = date('d', time());
        $month = date('m', time());
        $year = date('Y', time());
?>
    <div class="wrap propertyhive" style="margin-top:25px;">

        <div id="ph_calendar"></div>

        <div class="ph-calendar-notification"><div class="inner">Loading...</div></div>

        <div class="ph-calendar-new-event-popup"></div>

        <div class="ph-calendar-event-details-popup"></div>

    </div>
<?php
    }

    private function get_assigned_to_output( $user_id )
    {
        $output = '';

        $user_info = get_userdata($user_id);
        if ( $user_info !== FALSE )
        {
            $display_name = trim($user_info->display_name);
            $display_name_parts = explode(" ", $display_name);

            $options = get_option( 'propertyhive_calendar', array() );

            if ( !isset($options['assigned_to_format']) || ( isset($options['assigned_to_format']) && $options['assigned_to_format'] == '' ) )
            {
                foreach ( $display_name_parts as $part ) 
                {
                    $output .= strtoupper($part[0]);
                }
            }
            else
            {
                switch ( $options['assigned_to_format'] )
                {
                    case "extendedinitials":
                    {
                        foreach ( $display_name_parts as $part ) 
                        {
                            $output .= strtoupper($part[0]);
                        }
                        if ( isset($part[1]) ) { $output .= strtolower($part[1]); }
                        break;
                    }
                    case "firstinitial":
                    {
                        $last_name = array_pop($display_name_parts);
                        foreach ( $display_name_parts as $part ) 
                        {
                            $output .= strtoupper($part[0]);
                            break;
                        }
                        $output .= ' ' . $last_name;
                        break;
                    }
                    case "lastinitial":
                    {
                        $first_name = array_shift($display_name_parts);
                        $output .= $first_name;
                        if ( count( $display_name_parts ) >= 1 )
                        {
                            $output .= ' ' . strtoupper($display_name_parts[count($display_name_parts) - 1][0]);
                        }
                        break;
                    }
                    case "full":
                    {
                        $output = $display_name;
                        break;
                    }
                }
            }
        }

        return $output;
    }

    public function ajax_propertyhive_load_resources()
    {
        $args = array(
            'number' => 9999,
            'orderby' => 'display_name',
            'role__not_in' => array('property_hive_contact', 'subscriber') 
        );
        if ( isset($_GET['negotiator_id']) && !empty($_GET['negotiator_id']) )
        {
            $args['include'] = $_GET['negotiator_id'];
        }
        $user_query = new WP_User_Query( $args );

        $negotiators = array();

        if ( ! empty( $user_query->results ) ) 
        {
            foreach ( $user_query->results as $user ) 
            {
                $negotiators[] = array(
                    'id' => $user->ID,
                    'title' => $user->display_name
                );
            }
        }

        header( 'Content-Type: application/json; charset=utf-8' );
        echo json_encode($negotiators);
        die();
    }

    public function ajax_propertyhive_load_events()
    {
        global $post;

        // Will receive:
        // $_POST['start'] // day, week, month
        // $_POST['end']
        // $_POST['negotiator_id']

        // Work out start date and end date based on date and view provided
        $start_date = date("Y-m-d H:i:s", strtotime($_GET['start']));
        $end_date = date("Y-m-d H:i:s", strtotime($_GET['end']));

        $events = array();

        // Based on date passed determine start and end date

        // Get appraisals
        $args = array(
            'post_type' => 'appraisal',
            'post_status' => array( 'publish' ),
            'nopaging' => true,
            //'meta_key' => '_start_date_time',
            //'orderby' => 'meta_value',
            //'order' => 'ASC',
            'orderby' => 'none',
            'meta_query' => array(
                array(
                    'key' => '_start_date_time',
                    'value' => array($start_date, $end_date),
                    'compare' => 'BETWEEN',
                ),
            ),
        );

        if ( isset($_GET['negotiator_id']) && is_array($_GET['negotiator_id']) && !empty($_GET['negotiator_id']) )
        {
            $negotiator_ids = ph_clean($_GET['negotiator_id']);
            if ( !is_array($negotiator_ids) )
            {
                $negotiator_ids = array( $negotiator_ids );
            }
            $args['meta_query'][] = array(
                'key' => '_negotiator_id',
                'value' => $negotiator_ids,
                'compare' => 'IN'
            );
        }

        // Remember selected negs so we can default back to this when next loading the calendar
        update_user_meta( get_current_user_id(), '_propertyhive_calendar_negotiator_ids', ( isset($_GET['negotiator_id']) ? ph_clean($_GET['negotiator_id']) : array() ) );

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


                $owner_contact_id = get_post_meta( get_the_ID(), '_property_owner_contact_id', TRUE );
                $contact_details = array(array(
                    'type' => 'owner',
                    'name' => get_the_title($owner_contact_id),
                    'phone' => get_post_meta( $owner_contact_id, '_telephone_number', TRUE ),
                    'email' => get_post_meta( $owner_contact_id, '_email_address', TRUE ),
                ));
                
                $negotiator_ids = get_post_meta( get_the_ID(), '_negotiator_id' );
                $negotiator_initials = array();
                if ( !empty($negotiator_ids) )
                {
                    foreach ( $negotiator_ids as $negotiator_id )
                    {
                        $negotiator_initials[] = $this->get_assigned_to_output( $negotiator_id );
                    }
                }

                $event_array = array(
                    'type' => 'appraisal',
                    'id' => get_the_ID(),
                    'groupId' => get_the_ID(),
                    'title' => ( $status == 'cancelled' ? 'CANCELLED - ' : '' ) . __( 'Appraisal', 'propertyhive' ) . ( $property_address != '' ? ' at ' . $property_address : '' ) . ( !empty($negotiator_initials) ? "\n" . 'Attending: ' . implode(", ", $negotiator_initials) : '' ),
                    'start' => date( "Y-m-d\TH:i:s", strtotime($start_date_time) ),
                    'end' => date( "Y-m-d\TH:i:s", strtotime($start_date_time) + get_post_meta( get_the_ID(), '_duration', TRUE ) ),
                    'backgroundColor' => '#693f7b',
                    'url' => get_edit_post_link(get_the_ID(), ''),
                    'classNames' => ( $status == 'cancelled' ? array('translucent') : array() ),
                    'resourceIds' => !empty($negotiator_ids) ? $negotiator_ids : ( (isset($_GET['negotiator_id']) && is_array($_GET['negotiator_id']) && !empty($_GET['negotiator_id'])) ? $_GET['negotiator_id'] : $_GET['all_negotiators_id'] ),
                    'propertyAddress' => $property_address,
                    'contactDetails' => $contact_details,
                );

                $events[] = $event_array;
            }
        }
        wp_reset_postdata();

        // Get viewings
        $args = array(
            'post_type' => 'viewing',
            'post_status' => array( 'publish' ),
            'nopaging' => true,
            //'meta_key' => '_start_date_time',
            //'orderby' => 'meta_value',
            //'order' => 'ASC',
            'orderby' => 'none',
            'meta_query' => array(
                 array(
                    'key' => '_start_date_time',
                    'value' => array($start_date, $end_date),
                    'compare' => 'BETWEEN',
                ),
            ),
        );

        if ( isset($_GET['negotiator_id']) && is_array($_GET['negotiator_id']) && !empty($_GET['negotiator_id']) )
        {
            $negotiator_ids = ph_clean($_GET['negotiator_id']);
            if ( !is_array($negotiator_ids) )
            {
                $negotiator_ids = array( $negotiator_ids );
            }
            $args['meta_query'][] = array(
                'relation' => 'OR',
                array(
                    'key' => '_negotiator_id',
                    'value' => $negotiator_ids,
                    'compare' => 'IN'
                ),
                array(
                    'key' => '_negotiator_id',
                    'compare' => 'NOT EXISTS'
                ),
            );
        }

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
                if ( $property_id != '' )
                {
                    $property = new PH_Property( (int)$property_id );

                    $property_address = $property->get_formatted_summary_address();
                }

                $contact_details = array();
                $owner_contact_ids = get_post_meta( $property_id, '_owner_contact_id', TRUE );
                if ( !empty($owner_contact_ids) )
                {
                    foreach( $owner_contact_ids as $contact_id )
                    {
                        $contact_details[] = array(
                            'type' => 'owner',
                            'name' => get_the_title($contact_id),
                            'phone' => get_post_meta( $contact_id, '_telephone_number', TRUE ),
                            'email' => get_post_meta( $contact_id, '_email_address', TRUE ),
                        );
                    }
                }

                $applicant_contact_ids = get_post_meta( get_the_ID(), '_applicant_contact_id' );
                $applicant_names = array();
                if ( !empty($applicant_contact_ids) )
                {
                    foreach ($applicant_contact_ids as $applicant_contact_id)
                    {
                        $applicant_names[] = get_the_title($applicant_contact_id);

                        $contact_details[] = array(
                            'type' => 'applicant',
                            'name' => get_the_title($applicant_contact_id),
                            'phone' => get_post_meta( $applicant_contact_id, '_telephone_number', TRUE ),
                            'email' => get_post_meta( $applicant_contact_id, '_email_address', TRUE ),
                        );
                    }
                    $applicant_names = array_filter($applicant_names);
                }
                
                $applicant_names_string = '';
                if ( count($applicant_names) == 1 )
                {
                    $applicant_names_string = $applicant_names[0];
                }
                elseif ( count($applicant_names) > 1 )
                {
                    $last_applicant = array_pop($applicant_names);
                    $applicant_names_string = implode(', ', $applicant_names) . ' & ' . $last_applicant;
                }

                $negotiator_ids = get_post_meta( get_the_ID(), '_negotiator_id' );
                $negotiator_initials = array();
                if ( !empty($negotiator_ids) )
                {
                    foreach ( $negotiator_ids as $negotiator_id )
                    {
                        $negotiator_initials[] = $this->get_assigned_to_output( $negotiator_id );
                    }
                }

                $title_prefix = '';
                if ( in_array( $status, array('cancelled', 'no_show') ) )
                {
                    $title_prefix = __( strtoupper(str_replace("_", " ", $status)) . ' - ' );
                }

                $event_array = array(
                    'type' => 'viewing',
                    'id' => get_the_ID(),
                    'groupId' => get_the_ID(),
                    'title' => $title_prefix . ( empty($negotiator_initials) ? 'Unaccompanied ' : '' ) . __( 'Viewing', 'propertyhive' ) . ( $property_address != '' ? ' at ' . $property_address : '' ) . ( $applicant_names_string != '' ? ' with ' . $applicant_names_string : '' ) . ( !empty($negotiator_initials) ? "\n" . 'Attending: ' . implode(", ", $negotiator_initials) : '' ),
                    'start' => date( "Y-m-d\TH:i:s", strtotime($start_date_time) ),
                    'end' => date( "Y-m-d\TH:i:s", strtotime($start_date_time) + get_post_meta( get_the_ID(), '_duration', TRUE ) ),
                    'backgroundColor' => '#39589a',
                    'url' => get_edit_post_link(get_the_ID(), ''),
                    'classNames' => ( in_array( $status, array('cancelled', 'no_show') ) ? array('translucent') : array() ),
                    'resourceIds' => !empty($negotiator_ids) ? $negotiator_ids : ( (isset($_GET['negotiator_id']) && is_array($_GET['negotiator_id']) && !empty($_GET['negotiator_id'])) ? $_GET['negotiator_id'] : $_GET['all_negotiators_id'] ),
                    'propertyAddress' => $property_address,
                    'contactDetails' => $contact_details,
                );

                $events[] = $event_array;
            }
        }
        wp_reset_postdata();

        // Get appointments
        $args = array(
            'post_type' => 'appointment',
            'post_status' => array( 'publish' ),
            'nopaging' => true,
            //'meta_key' => '_start_date_time',
            //'orderby' => 'meta_value',
            //'order' => 'ASC',
            'orderby' => 'none',
            'fields' => 'ids',
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    array(
                        'key' => '_start_date_time',
                        'value' => array($start_date, $end_date),
                        'compare' => 'BETWEEN',
                    ),
                    array(
                        'key' => '_recurrence_type',
                        'value' => '',
                        'compare' => '='
                    ),
                ),
                array(
                    array(
                        'key' => '_recurrence_type',
                        'value' => '',
                        'compare' => '!='
                    )
                )
            ),
        );

        if ( isset($_GET['negotiator_id']) && is_array($_GET['negotiator_id']) && !empty($_GET['negotiator_id']) )
        {
            $negotiator_ids = ph_clean($_GET['negotiator_id']);
            if ( !is_array($negotiator_ids) )
            {
                $negotiator_ids = array( $negotiator_ids );
            }

            $sub_meta_query = array(
                'relation' => 'OR'
            );

            foreach ( $negotiator_ids as $negotiator_id )
            {
                $sub_meta_query[] = array(
                    'key' => '_assigned_to',
                    'value' => '"' . (int)$negotiator_id . '"',
                    'compare' => 'LIKE'
                );
            }

            $sub_meta_query[] = array(
                'key' => '_assigned_to',
                'value' => '',
            );

            $args['meta_query'] = array(
                'relation' => 'AND',
                $args['meta_query'],
                $sub_meta_query
            );
        }

        $appointments_query = new WP_Query( $args );

        if ( $appointments_query->have_posts() )
        {
            while ( $appointments_query->have_posts() )
            {
                $appointments_query->the_post();

                $start_date_time = get_post_meta( get_the_ID(), '_start_date_time', TRUE );
                $end_date_time = get_post_meta( get_the_ID(), '_end_date_time', TRUE );

                // does it have recurrence
                $recurrence_type = get_post_meta( get_the_ID(), '_recurrence_type', TRUE );
                if ( $recurrence_type != '' )
                {
                    $occurrences = array();

                    $diff_secs = strtotime( $end_date_time ) - strtotime( $start_date_time );

                    // yes it does
                    switch ( $recurrence_type )
                    {
                        case "daily":
                        {
                            if ( strtotime($start_date_time) > strtotime($start_date) )
                            {
                                $r_start_date = $start_date_time;
                            }
                            else
                            {
                                $time = date( "H:i:s", strtotime($start_date_time) );
                                $r_start_date = date( "Y-m-d", strtotime($start_date) ) . ' ' . $time;
                            }

                            if ( strtotime($r_start_date) > strtotime($end_date) )
                            {
                                continue 2;
                            }

                            $r = new When();
                            $r->startDate( new DateTime($r_start_date) )
                              ->freq("daily")
                              ->until(new DateTime($end_date))
                              ->generateOccurrences();

                            $occurrences = $r->occurrences;
                            break;
                        }
                        case "weekly":
                        {
                            $r_start_date = $start_date_time;

                            if ( strtotime($r_start_date) > strtotime($end_date) )
                            {
                                continue 2;
                            }

                            $r = new When();
                            $r->startDate( new DateTime($r_start_date) )
                              ->freq("weekly")
                              ->until(new DateTime($end_date))
                              ->generateOccurrences();

                            $occurrences = $r->occurrences;
                            break;
                        }
                        case "monthly":
                        {
                            $r_start_date = $start_date_time;

                            if ( strtotime($r_start_date) > strtotime($end_date) )
                            {
                                continue 2;
                            }

                            $r = new When();
                            $r->startDate( new DateTime($r_start_date) )
                              ->freq("monthly")
                              ->until(new DateTime($end_date))
                              ->generateOccurrences();

                            $occurrences = $r->occurrences;
                            break;
                        }
                        case "annually":
                        {
                            $r_start_date = $start_date_time;

                            if ( strtotime($r_start_date) > strtotime($end_date) )
                            {
                                continue 2;
                            }

                            $r = new When();
                            $r->startDate( new DateTime($r_start_date) )
                              ->freq("yearly")
                              ->until(new DateTime($end_date))
                              ->generateOccurrences();

                            $occurrences = $r->occurrences;
                            break;
                        }
                    }

                    if ( !empty($occurrences) )
                    {
                        foreach ( $occurrences as $occurrence )
                        {
                            $all_day = get_post_meta( get_the_ID(), '_all_day', TRUE );

                            $start = ( ( $all_day == 'yes' ) ? date( "Y-m-d", $occurrence->format('U') ) . 'T00:00:00' : date( "Y-m-d\TH:i:s", $occurrence->format('U') ) );
                            $end = ( ( $all_day == 'yes' ) ? date( "Y-m-d", strtotime( date( "Y-m-d", $occurrence->format('U') + $diff_secs ) . " + 1 day") ) . 'T00:00:00' : date( "Y-m-d\TH:i:s", $occurrence->format('U') + $diff_secs ) ); // +1day as end is exclusive

                            $negotiator_ids = get_post_meta( get_the_ID(), '_assigned_to', TRUE );
                            $negotiator_initials = array();
                            if ( !empty($negotiator_ids) )
                            {
                                foreach ( $negotiator_ids as $negotiator_id )
                                {
                                    $negotiator_initials[] = $this->get_assigned_to_output( $negotiator_id );
                                }
                            }

                            $related_to = get_post_meta( get_the_ID(), '_related_to', TRUE );
                            if ( !empty($related_to) )
                            {
                                $explode_related_to = explode('|', $related_to);
                                if ( $explode_related_to[0] == 'property' )
                                {
                                    $related_property = new PH_Property( (int)$explode_related_to[1] );
                                    $related_to = 'Related to: ' . $related_property->get_formatted_full_address();
                                }
                                elseif ( $explode_related_to[0] == 'contact' )
                                {
                                    $related_to = 'Related to: ' . get_the_title($explode_related_to[1]);
                                }
                            }

                            if ( isset($_GET['schedule_mode']) )
                            {
                                $appointment_details = get_post_meta( get_the_ID(), '_details', TRUE );
                            }

                            $appointment_title = get_the_title(get_the_ID());

                            if ( isset($_GET['schedule_mode']) && !empty($appointment_details) )
                            {
                                $appointment_title .= ( !empty( $appointment_title ) ? "\n" : '' ) . $appointment_details;
                            }

                            if ( !empty($negotiator_initials) )
                            {
                                $appointment_title .= ( !empty( $appointment_title ) ? "\n" : '' ) . 'Assigned To: ' . implode(", ", $negotiator_initials);
                            }

                            if ( !empty($related_to) )
                            {
                                $appointment_title .= ( !empty( $appointment_title ) ? "\n" : '' ) . $related_to;
                            }

                            if ((strtotime($start) >= strtotime($start_date)) && (strtotime($start) <= strtotime($end_date))) {
                                $events[] = array(
                                    'type' => 'appointment',
                                    'id' => get_the_ID() . '-' . strtotime($start) . '-' . strtotime($end),
                                    'groupId' => get_the_ID(),
                                    'title' => $appointment_title,
                                    'start' => $start,
                                    'end' => $end ,
                                    'allDay' => ( ( $all_day == 'yes' ) ? true : false ),
                                    'backgroundColor' => '#0075BC',
                                    'url' => get_edit_post_link(get_the_ID(), ''),
                                    'classNames' => array(),
                                    'resourceIds' => !empty($negotiator_ids) ? $negotiator_ids : ( (isset($_GET['negotiator_id']) && is_array($_GET['negotiator_id']) && !empty($_GET['negotiator_id'])) ? $_GET['negotiator_id'] : $_GET['all_negotiators_id'] ),
                                );
                            }
                        }
                    }
                }
                else
                {
                    if ((strtotime($start_date_time) >= strtotime($start_date)) && (strtotime($start_date_time) <= strtotime($end_date))) {

                        $all_day = get_post_meta( get_the_ID(), '_all_day', TRUE );

                        $negotiator_ids = get_post_meta( get_the_ID(), '_assigned_to', TRUE );
                        $negotiator_initials = array();
                        if ( !empty($negotiator_ids) )
                        {
                            foreach ( $negotiator_ids as $negotiator_id )
                            {
                                $negotiator_initials[] = $this->get_assigned_to_output( $negotiator_id );
                            }
                        }

                        $related_to = get_post_meta( get_the_ID(), '_related_to', TRUE );
                        if ( !empty($related_to) )
                        {
                            $explode_related_to = explode('|', $related_to);
                            if ( $explode_related_to[0] == 'property' )
                            {
                                $related_property = new PH_Property( (int)$explode_related_to[1] );
                                $related_to = 'Related to: ' . $related_property->get_formatted_full_address();
                            }
                            elseif ( $explode_related_to[0] == 'contact' )
                            {
                                $related_to = 'Related to: ' . get_the_title($explode_related_to[1]);
                            }
                        }

                        if ( isset($_GET['schedule_mode']) )
                        {
                            $appointment_details = get_post_meta( get_the_ID(), '_details', TRUE );
                        }

                        $appointment_title = get_the_title(get_the_ID());

                        if ( isset($_GET['schedule_mode']) && !empty($appointment_details) )
                        {
                            $appointment_title .= ( !empty( $appointment_title ) ? "\n" : '' ) . $appointment_details;
                        }

                        if ( !empty($negotiator_initials) )
                        {
                            $appointment_title .= ( !empty( $appointment_title ) ? "\n" : '' ) . 'Assigned To: ' . implode(", ", $negotiator_initials);
                        }

                        if ( !empty($related_to) )
                        {
                            $appointment_title .= ( !empty( $appointment_title ) ? "\n" : '' ) . $related_to;
                        }

                        $events[] = array(
                            'type' => 'appointment',
                            'id' => get_the_ID(),
                            'groupId' => get_the_ID(),
                            'title' => $appointment_title,
                            'start' => ( ( $all_day == 'yes' ) ? date( "Y-m-d", strtotime($start_date_time) ) . 'T00:00:00' : date( "Y-m-d\TH:i:s", strtotime($start_date_time) ) ),
                            'end' => ( ( $all_day == 'yes' ) ? date( "Y-m-d", strtotime( date( "Y-m-d", strtotime($end_date_time)) . " + 1 day") ) . 'T00:00:00' : date( "Y-m-d\TH:i:s", strtotime($end_date_time) ) ), // +1day as end is exclusive
                            'allDay' => ( ( $all_day == 'yes' ) ? true : false ),
                            'backgroundColor' => '#0075BC',
                            'url' => get_edit_post_link(get_the_ID(), ''),
                            'classNames' => array(),
                            'resourceIds' => !empty($negotiator_ids) ? $negotiator_ids : ( (isset($_GET['negotiator_id']) && is_array($_GET['negotiator_id']) && !empty($_GET['negotiator_id'])) ? $_GET['negotiator_id'] : $_GET['all_negotiators_id'] ),
                        );
                    }
                }
            }
        }
        wp_reset_postdata();

        $events = apply_filters( 'propertyhive_calendar_loaded_events', $events, $start_date, $end_date );

        if ( isset($_GET['schedule_mode']) )
        {
            include( dirname( __FILE__ ) . '/includes/views/html-schedule-view.php');
        }
        else
        {
            header( 'Content-Type: application/json; charset=utf-8' );
            echo json_encode( $events );
        }

        die();
    }

    public function ajax_propertyhive_resized_event()
    {
        // Will receive:
        // $_POST['id']
        // $_POST['end']

        // Work out start date and end date based on date and view provided
        $end_date_time = date("Y-m-d H:i:s", strtotime($_GET['end']));

        $explode_id = explode("-", ph_clean($_GET['id']));

        if ( count($explode_id) == 3 )
        {
            // This is a recurring event
            // [0] = ID
            // [1] = original start date for this recurrence (unix timestamp)
            // [2] = original end date for this recurrence (unix timestamp)
            $appointment_id = (int)$explode_id[0];

            $diff_secs_recurrence_new_old = strtotime($end_date_time) - (int)$explode_id[2];

            $previous_end_date_time = strtotime(get_post_meta( $appointment_id, '_end_date_time', true ));

            update_post_meta( $appointment_id, '_end_date_time', date("Y-m-d H:i:s", $previous_end_date_time + $diff_secs_recurrence_new_old) );

            $post = get_post( (int)$appointment_id );
            do_action( "save_post_" . get_post_type( (int)$appointment_id ), (int)$_GET['id'], $post, false );
            do_action( "save_post", (int)$appointment_id, $post, false );
        }
        else
        {
            if ( get_post_type( (int)$_GET['id'] ) == 'appointment' )
            {
                update_post_meta( (int)$_GET['id'], '_end_date_time', $end_date_time );
            }
            else
            {
                // Get start date and work out duration in seconds
                $start_date_time = get_post_meta( (int)$_GET['id'], '_start_date_time', TRUE );

                $duration = strtotime($end_date_time) - strtotime($start_date_time);

                update_post_meta( (int)$_GET['id'], '_duration', $duration );
            }

            $post = get_post( (int)$_GET['id'] );
            do_action( "save_post_" . get_post_type( (int)$_GET['id'] ), (int)$_GET['id'], $post, false );
            do_action( "save_post", (int)$_GET['id'], $post, false );
        }

        $return = array( 'success' => true );

        header( 'Content-Type: application/json; charset=utf-8' );
        echo json_encode( $return );

        die();
    }

    public function ajax_propertyhive_dragged_event()
    {
        // Will receive:
        // $_POST['id']
        // $_POST['start']

        $start_date_time = date("Y-m-d H:i:s", strtotime(ph_clean($_GET['start'])));

        $explode_id = explode("-", ph_clean($_GET['id']));

        if ( count($explode_id) == 3 )
        {
            // This is a recurring event
            // [0] = ID
            // [1] = original start date for this recurrence (unix timestamp)
            // [2] = original end date for this recurrence (unix timestamp) - not used in this method

            $appointment_id = (int)$explode_id[0];

            $diff_secs_recurrence_new_old = strtotime($start_date_time) - (int)$explode_id[1];

            //echo 'diff_secs_recurrence_new_old: ' . $diff_secs_recurrence_new_old . "\n";

            // change the original by the same amount
            $previous_start_date_time = strtotime(get_post_meta( $appointment_id, '_start_date_time', true ));
            $previous_end_date_time = strtotime(get_post_meta( $appointment_id, '_end_date_time', true ));

            //echo '$previous_start_date_time + $diff_secs_recurrence_new_old: ' . ($previous_start_date_time + $diff_secs_recurrence_new_old) . "\n";

            update_post_meta( $appointment_id, '_start_date_time', date("Y-m-d H:i:s", $previous_start_date_time + $diff_secs_recurrence_new_old) );
            update_post_meta( $appointment_id, '_end_date_time', date("Y-m-d H:i:s", $previous_end_date_time + $diff_secs_recurrence_new_old) );

            $post = get_post( (int)$appointment_id );
            do_action( "save_post_" . get_post_type( (int)$appointment_id ), (int)$_GET['id'], $post, false );
            do_action( "save_post", (int)$appointment_id, $post, false );
        }
        else
        {
            if ( get_post_type( (int)$_GET['id'] ) == 'appointment' )
            {
                $previous_start_date_time = get_post_meta( (int)$_GET['id'], '_start_date_time', true );
                $previous_end_date_time = get_post_meta( (int)$_GET['id'], '_end_date_time', true );

                $diff_secs = strtotime($previous_end_date_time) - strtotime($previous_start_date_time);

                update_post_meta( (int)$_GET['id'], '_end_date_time', date("Y-m-d H:i:s", strtotime($start_date_time) + $diff_secs ) );
            }

            update_post_meta( (int)$_GET['id'], '_start_date_time', $start_date_time );

            $post = get_post( (int)$_GET['id'] );
            do_action( "save_post_" . get_post_type( (int)$_GET['id'] ), (int)$_GET['id'], $post, false );
            do_action( "save_post", (int)$_GET['id'], $post, false );
        }

        $return = array( 'success' => true );

        header( 'Content-Type: application/json; charset=utf-8' );
        echo json_encode( $return );

        die();
    }

    public function ajax_propertyhive_update_view()
    {
        // Will receive:
        // $_POST['view']

        $view = 'week';
        switch ( $_POST['view'] )
        {
            case "timeGridDay": { $view = 'day'; break; }
            case "dayGridMonth": { $view = 'month'; break; }
            case "resourceTimeline": { $view = 'timeline'; break; }
        }
        update_user_meta( get_current_user_id(), '_propertyhive_calendar_view', $view );

        die();
    }

    public function register_post_type_appointment()
    {
        register_post_type( "appointment",
            apply_filters( 'propertyhive_register_post_type_appointment',
                array(
                    'labels' => array(
                            'name'                  => __( 'Appointments', 'propertyhive' ),
                            'singular_name'         => __( 'Appointment', 'propertyhive' ),
                            'menu_name'             => _x( 'Appointments', 'Admin menu name', 'propertyhive' ),
                            'add_new'               => __( 'Add Appointment', 'propertyhive' ),
                            'add_new_item'          => __( 'Add New Appointment', 'propertyhive' ),
                            'edit'                  => __( 'Edit', 'propertyhive' ),
                            'edit_item'             => __( 'Edit Appointment', 'propertyhive' ),
                            'new_item'              => __( 'New Appointment', 'propertyhive' ),
                            'view'                  => __( 'View Appointment', 'propertyhive' ),
                            'view_item'             => __( 'View Appointment', 'propertyhive' ),
                            'search_items'          => __( 'Search Appointments', 'propertyhive' ),
                            'not_found'             => __( 'No appointments found', 'propertyhive' ),
                            'not_found_in_trash'    => __( 'No appointments found in trash', 'propertyhive' ),
                            'parent'                => __( 'Parent Appointment', 'propertyhive' )
                        ),
                    'description'           => __( 'This is where you can add new appointment.', 'propertyhive' ),
                    'public'                => false,
                    'show_ui'               => true,
                    'capability_type'       => 'post',
                    'map_meta_cap'          => true,
                    'publicly_queryable'    => false,
                    'exclude_from_search'   => true,
                    'hierarchical'          => false, // Hierarchical causes memory issues - WP loads all records!
                    'query_var'             => true,
                    'supports'              => array( 'title' ),
                    'show_in_nav_menus'     => false,
                    'show_in_menu'          => false,
                )
            )
        );
    }

    /**
     * Change title boxes in admin.
     * @param  string $text
     * @param  object $post
     * @return string
     */
    public function enter_title_here( $text, $post ) {
        if ( is_admin() && $post->post_type == 'appointment' ) {
            return __( 'Appointment Title', 'propertyhive' );
        }

        return $text;
    }

    public function add_appointment_screen_ids( $screen_ids = array() )
    {
        $screen_ids[] = 'appointment';
        $screen_ids[] = 'edit-appointment';
        return $screen_ids;
    }

    public function ph_appointment_tabs_and_meta_boxes($tabs)
    {
        $meta_boxes = array();
        $meta_boxes[5] = array(
            'id' => 'propertyhive-appointment-details',
            'title' => __( 'Appointment Details', 'propertyhive' ),
            'callback' => 'PH_Meta_Box_Appointment_Details::output',
            'screen' => 'appointment',
            'context' => 'normal',
            'priority' => 'high'
        );


        $meta_boxes = apply_filters( 'propertyhive_appointment_summary_meta_boxes', $meta_boxes );
        ksort($meta_boxes);

        $ids = array();
        foreach ($meta_boxes as $meta_box)
        {
            add_meta_box( $meta_box['id'], $meta_box['title'], $meta_box['callback'], $meta_box['screen'], $meta_box['context'], $meta_box['priority'] );
            $ids[] = $meta_box['id'];
        }

        $tabs['tab_appointment_summary'] = array(
            'name' => __( 'Summary', 'propertyhive' ),
            'metabox_ids' => $ids,
            'post_type' => 'appointment'
        );

        return $tabs;
    }

    public function add_appointments_to_post_types_with_tabs( $post_types = array() )
    {
        $post_types[] = 'appointment';
        return $post_types;
    }
}

endif;

/**
 * Returns the main instance of PH_Calendar to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return PH_Calendar
 */
function PHC() {
    return PH_Calendar::instance();
}

PHC();

if( is_admin() && file_exists(  dirname( __FILE__ ) . '/propertyhive-calendar-update.php' ) )
{
    include_once( dirname( __FILE__ ) . '/propertyhive-calendar-update.php' );
}