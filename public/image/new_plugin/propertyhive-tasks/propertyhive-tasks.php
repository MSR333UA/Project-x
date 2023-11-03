<?php
/**
 * Plugin Name: Property Hive Tasks Add On
 * Plugin Uri: http://wp-property-hive.com/addons/tasks/
 * Description: Add On for Property Hive allowing the addition and management of tasks
 * Version: 1.0.9
 * Author: PropertyHive
 * Author URI: http://wp-property-hive.com
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'PH_Tasks' ) ) :

final class PH_Tasks {

    /**
     * @var string
     */
    public $version = '1.0.9';

    /**
     * @var PropertyHive The single instance of the class
     */
    protected static $_instance = null;
    
    /**
     * Main Property Hive Tasks Instance
     *
     * Ensures only one instance of Property Hive Tasks is loaded or can be loaded.
     *
     * @static
     * @return Property Hive Tasks - Main instance
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

        $this->id    = 'tasks';
        $this->label = __( 'Tasks', 'propertyhive' );

        // Define constants
        $this->define_constants();

        // Include required files
        $this->includes();

        add_action( 'current_screen', array( $this, 'conditional_includes' ) );

        add_action( 'admin_notices', array( $this, 'tasks_error_notices') );

        add_action( 'admin_menu', array( $this, 'admin_menu' ) );

        add_filter( 'propertyhive_settings_tabs_array', array( $this, 'add_settings_tab' ), 19 );
        add_action( 'propertyhive_settings_' . $this->id, array( $this, 'output' ) );
        add_action( 'propertyhive_settings_save_' . $this->id, array( $this, 'save' ) );

        add_action( 'propertyhive_register_post_type', array( $this, 'register_post_type_task') );

        add_filter( 'enter_title_here', array( $this, 'enter_title_here' ), 1, 2 );

        add_filter( 'propertyhive_post_types_with_tabs', array( $this, 'add_tasks_to_post_types_with_tabs') );
        add_filter( 'propertyhive_tabs', array( $this, 'ph_task_tabs_and_meta_boxes') );
        add_filter( 'propertyhive_screen_ids', array( $this, 'add_task_screen_ids') );

        add_action( 'propertyhive_process_task_meta', 'PH_Meta_Box_Task_Details::save', 10, 2 );

        add_filter( 'propertyhive_property_summary_meta_boxes', array( $this, 'property_task_meta_box') );
        add_filter( 'propertyhive_contact_details_meta_boxes', array( $this, 'contact_task_meta_box') );

        add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );

        add_action( 'admin_print_scripts', array( $this, 'remove_month_filter' ) );

        add_filter( 'manage_edit-task_columns', array( $this, 'edit_columns' ) );
        add_action( 'manage_task_posts_custom_column', array( $this, 'custom_columns' ), 2 );
        add_filter( 'manage_edit-task_sortable_columns', array( $this, 'custom_columns_sort' ) );
        add_filter( 'request', array( $this, 'custom_columns_orderby' ) );

        add_action( 'restrict_manage_posts', array( $this, 'restrict_manage_posts' ) );
        add_filter( 'request', array( $this, 'request_query' ) );

        add_action( 'wp_ajax_propertyhive_get_task', array( $this, 'propertyhive_get_task' ) );
        add_action( 'wp_ajax_propertyhive_get_tasks', array( $this, 'propertyhive_get_tasks' ) );
        add_action( 'wp_ajax_propertyhive_create_task', array( $this, 'propertyhive_create_task' ) );
        add_action( 'wp_ajax_propertyhive_task_complete', array( $this, 'propertyhive_task_complete' ) );
        add_action( 'wp_ajax_propertyhive_task_open', array( $this, 'propertyhive_task_open' ) );
        add_action( 'wp_ajax_propertyhive_delete_task', array( $this, 'propertyhive_delete_task' ) );

        add_filter( 'propertyhive_calendar_loaded_events', array( $this, 'load_tasks_on_calendar' ), 10, 3 );

        add_filter( 'propertyhive_email_schedule_events', array( $this, 'include_tasks_in_email_schedule' ), 10, 4 );
    }

    public function include_tasks_in_email_schedule( $events, $start_date, $end_date, $user_id )
    {
        $args = array(
            'post_type' => 'task',
            'post_status' => array( 'publish' ),
            'nopaging' => true,
            'meta_key' => '_due_date',
            'orderby' => 'meta_value',
            'order' => 'ASC',
            'fields' => 'ids',
            'meta_query' => array(
                array(
                    'key' => '_due_date',
                    'value' => date("Y-m-d", strtotime($start_date)),
                    'compare' => '<='
                ),
                array(
                    'key' => '_status',
                    'value' => 'open',
                    'compare' => '='
                ),
                array(
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
            )
        );

        $tasks_query = new WP_Query( $args );

        if ( $tasks_query->have_posts() )
        {
            while ( $tasks_query->have_posts() )
            {
                $tasks_query->the_post();

                $due_date = get_post_meta( get_the_ID(), '_due_date', TRUE );

                $status = get_post_meta( get_the_ID(), '_status', TRUE );

                $details = get_post_meta( get_the_ID(), '_details', TRUE );

                if ( strtotime($due_date) < strtotime( date("Y-m-d", strtotime($start_date)) ) )
                {
                    // was due in the past
                    if ( $details != '' ) { $details .= '<br>'; }
                    $details .= '<span style="color:#900">' . __( 'Originally due on', 'propertyhive' ) . ' ' . date("jS M", strtotime($due_date)) . '</span>';
                }

                $property_address = '';
                $explode_related_to = explode("|", get_post_meta( get_the_ID(), '_related_to', TRUE ));
                if ( $explode_related_to[0] == 'property' )
                {
                    $property = new PH_Property( (int)$explode_related_to[1] );
                    $property_address = $property->get_formatted_full_address();

                    if ( $details != '' ) { $details .= '<br>'; }
                    $details .= __( 'Property', 'propertyhive' ) . ': ' . $property_address;
                }
                elseif ( $explode_related_to[0] == 'contact' )
                {
                    if ( $details != '' ) { $details .= '<br>'; }
                    $details .= __( 'Contact', 'propertyhive' ) . ': ' . get_the_title($explode_related_to[1]);
                }

                $events[strtotime($due_date) . uniqid()] = array(
                    'type' => 'task',
                    'id' => get_the_ID(),
                    'property_address' => $property_address,
                    'title' => get_the_title(get_the_ID()) . ( $status == 'completed' ? ' (Completed)' : '' ),
                    'details' => $details,
                    'start' => date( "Y-m-d", strtotime($due_date) ) . 'T00:00:00',
                    'endd' => date( "Y-m-d", strtotime($due_date) ) . 'T00:00:00',
                    'allDay' => true,
                    'background' => '#da5353',
                    'url' => get_edit_post_link(get_the_ID(), ''),
                );
            }
        }
        wp_reset_postdata();

        return $events;
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

    public function load_tasks_on_calendar( $events, $start_date, $end_date )
    {
        $explode_start_date = explode(" ", $start_date);
        if ( count($explode_start_date) == 2 )
        {
            $start_date = $explode_start_date[0];
        }

        $explode_end_date = explode(" ", $end_date);
        if ( count($explode_end_date) == 2 )
        {
            $end_date = $explode_end_date[0];
        }

        $args = array(
            'post_type' => 'task',
            'post_status' => array( 'publish' ),
            'nopaging' => true,
            'meta_key' => '_due_date',
            'orderby' => 'meta_value',
            'order' => 'ASC',
            'meta_query' => array(
                array(
                    'key' => '_due_date',
                    'value' => $start_date,
                    'compare' => '>='
                ),
                array(
                    'key' => '_due_date',
                    'value' => $end_date,
                    'compare' => '<='
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

            $sub_meta_query = array(
                'relation' => 'OR'
            );

            foreach ( $negotiator_ids as $negotiator_id )
            {
                $sub_meta_query[] = array(
                    'key' => '_assigned_to',
                    'value' => $negotiator_id,
                    'value' => '"' . (int)$negotiator_id . '"',
                    'compare' => 'LIKE'
                );
            }

            $sub_meta_query[] = array(
                'key' => '_assigned_to',
                'value' => '',
            );

            $args['meta_query'][] = $sub_meta_query;
        }

        $tasks_query = new WP_Query( $args );

        if ( $tasks_query->have_posts() )
        {
            while ( $tasks_query->have_posts() )
            {
                $tasks_query->the_post();

                $due_date = get_post_meta( get_the_ID(), '_due_date', TRUE );

                $status = get_post_meta( get_the_ID(), '_status', TRUE );

                $negotiator_ids = get_post_meta( get_the_ID(), '_assigned_to', TRUE );
                $negotiator_initials = array();
                if ( !empty($negotiator_ids) )
                {
                    foreach ( $negotiator_ids as $negotiator_id )
                    {
                        $negotiator_initials[] = $this->get_assigned_to_output( $negotiator_id );
                    }
                }

                $events[] = array(
                    'type' => 'task',
                    'id' => get_the_ID(),
                    'groupId' => get_the_ID(),
                    'title' => get_the_title(get_the_ID()) . ( $status == 'completed' ? ' (Completed)' : '' ) . ( !empty($negotiator_initials) ? "\n" . 'Assigned To: ' . implode(", ", $negotiator_initials) : '' ),
                    'start' => date( "Y-m-d", strtotime($due_date) ) . 'T00:00:00',
                    'end' => date( "Y-m-d", strtotime( date( "Y-m-d", strtotime($due_date)) . " + 1 day") ) . 'T00:00:00',
                    'allDay' => true,
                    'backgroundColor' => '#da5353',
                    'url' => get_edit_post_link(get_the_ID(), ''),
                    'classNames' => ( $status == 'completed' ? array('translucent') : array() ),
                    'resourceIds' => !empty($negotiator_ids) ? $negotiator_ids : ( (isset($_GET['negotiator_id']) && is_array($_GET['negotiator_id']) && !empty($_GET['negotiator_id'])) ? $_GET['negotiator_id'] : $_GET['all_negotiators_id'] ),
                );
            }
        }
        wp_reset_postdata();
        
        return $events;
    }

    /**
     * Change title boxes in admin.
     * @param  string $text
     * @param  object $post
     * @return string
     */
    public function enter_title_here( $text, $post ) {
        if ( is_admin() && $post->post_type == 'task' ) {
            return __( 'Task Title', 'propertyhive' );
        }

        return $text;
    }

    public function add_task_screen_ids( $screen_ids = array() )
    {
        $screen_ids[] = 'task';
        $screen_ids[] = 'edit-task';
        return $screen_ids;
    }

    public function ph_task_tabs_and_meta_boxes($tabs)
    {
        $meta_boxes = array();
        $meta_boxes[5] = array(
            'id' => 'propertyhive-task-details',
            'title' => __( 'Task Details', 'propertyhive' ),
            'callback' => 'PH_Meta_Box_Task_Details::output',
            'screen' => 'task',
            'context' => 'normal',
            'priority' => 'high'
        );


        $meta_boxes = apply_filters( 'propertyhive_task_summary_meta_boxes', $meta_boxes );
        ksort($meta_boxes);

        $ids = array();
        foreach ($meta_boxes as $meta_box)
        {
            add_meta_box( $meta_box['id'], $meta_box['title'], $meta_box['callback'], $meta_box['screen'], $meta_box['context'], $meta_box['priority'] );
            $ids[] = $meta_box['id'];
        }

        $tabs['tab_task_summary'] = array(
            'name' => __( 'Summary', 'propertyhive' ),
            'metabox_ids' => $ids,
            'post_type' => 'task'
        );

        return $tabs;
    }

    public function add_tasks_to_post_types_with_tabs( $post_types = array() )
    {
        $post_types[] = 'task';
        return $post_types;
    }

    /**
     * Remove month filter from some property hive pages
     */
    public function remove_month_filter() {
        global $typenow;
        
        if ($typenow == 'task')
        {
            add_filter('months_dropdown_results', '__return_empty_array');
        }
    }

    public function restrict_manage_posts() {
        global $typenow, $wp_query;

        if ( $typenow == 'task' ) 
        {
            // Task filtering
            $output = '';
            
            $output .= $this->task_status_filter();
            $output .= $this->task_assigned_to_filter();

            echo apply_filters( 'propertyhive_task_filters', $output );
        }
    }

    /**
     * Filters and sorting handler
     * @param  array $vars
     * @return array
     */
    public function request_query( $vars ) {
        global $typenow, $wp_query;

        if ( 'task' === $typenow ) 
        {
            if ( !isset($vars['meta_query']) ) { $vars['meta_query'] = array(); }
            if ( !isset($vars['tax_query']) ) { $vars['tax_query'] = array(); }

            if ( !isset($_GET['_status']) || ( isset($_GET['_status']) && $_GET['_status'] == '' ) ) {
                $vars['meta_query'][] = array(
                    'key' => '_status',
                    'value' => 'open',
                );
            }
            else
            {
                if ( isset($_GET['_status']) && $_GET['_status'] != 'all' ) 
                {
                    $vars['meta_query'][] = array(
                        'key' => '_status',
                        'value' => sanitize_text_field( $_GET['_status'] ),
                    );
                }
            }
            if ( !isset($_GET['_assigned_to']) || ( isset($_GET['_assigned_to']) && $_GET['_assigned_to'] == '' ) ) {
                $vars['meta_query'][] = array(
                    'relation' => 'OR',
                    array(
                        'key' => '_assigned_to',
                        'value' => '',
                    ),
                    array(
                        'key' => '_assigned_to',
                        'value' => '"' . get_current_user_id() . '"',
                        'compare' => 'LIKE'
                    ),
                );
            }
            elseif ( isset($_GET['_assigned_to']) && $_GET['_assigned_to'] != 'anyone' )
            {
                $vars['meta_query'][] = array(
                    'key' => '_assigned_to',
                    'value' => '"' . (int)$_GET['_assigned_to'] . '"',
                    'compare' => 'LIKE'
                );
            }
        }

        $vars = apply_filters( 'propertyhive_task_filter_query', $vars, $typenow );

        return $vars;
    }

    /**
     * Show a task status filter box
     */
    public function task_status_filter() {
        global $wp_query;
        
        // Department filtering
        $output  = '<select name="_status" id="dropdown_task_status">';
            
            $output .= '<option value="">' . __( 'Open', 'propertyhive' ) . '</option>';

            $output .= '<option value="completed"';
            if ( isset( $_GET['_status'] ) && ! empty( $_GET['_status'] ) )
            {
                $output .= selected( 'completed', $_GET['_status'], false );
            }
            $output .= '>' . __( 'Completed', 'propertyhive' ) . '</option>';

            $output .= '<option value="all"';
            if ( isset( $_GET['_status'] ) && ! empty( $_GET['_status'] ) )
            {
                $output .= selected( 'all', $_GET['_status'], false );
            }
            $output .= '>' . __( 'All Statuses', 'propertyhive' ) . '</option>';

        $output .= '</select>';

        return $output;
    }

    /**
     * Show a task assigned to filter box
     */
    public function task_assigned_to_filter() {
        global $wp_query;
        
        // Department filtering
        $output  = '<select name="_assigned_to" id="dropdown_task_assigned_to">';
            
            $output .= '<option value="anyone"';
            if ( isset( $_GET['_assigned_to'] ) && ! empty( $_GET['_assigned_to'] ) )
            {
                $output .= selected( 'anyone', $_GET['_assigned_to'], false );
            }
            $output .= '>' . __( 'All Tasks', 'propertyhive' ) . '</option>';

            $output .= '<option value=""';
            if ( !isset($_GET['_assigned_to']) )
            {
                $output .= ' selected';
            }
            elseif ( isset( $_GET['_assigned_to'] ) && $_GET['_assigned_to'] == '' )
            {
                $output .= selected( '', $_GET['_assigned_to'], false );
            }
            $output .= '>' . __( 'Assigned To Me', 'propertyhive' ) . '</option>';
        
            $args = array(
                'number' => 9999,
                'orderby' => 'display_name',
                'role__not_in' => array('property_hive_contact') 
            );
            $user_query = new WP_User_Query( $args );

            $negotiators = array();

            if ( ! empty( $user_query->results ) ) 
            {
                foreach ( $user_query->results as $user ) 
                {
                    $output .= '<option value="' . $user->ID . '"';
                    if ( isset( $_GET['_assigned_to'] ) && ! empty( $_GET['_assigned_to'] ) )
                    {
                        $output .= selected( $user->ID, $_GET['_assigned_to'], false );
                    }
                    $output .= '>' . $user->display_name . '</option>';
                }
            }

        $output .= '</select>';

        return $output;
    }

    /**
     * Change the columns shown in admin.
     */
    public function edit_columns( $existing_columns ) {

        if ( empty( $existing_columns ) && ! is_array( $existing_columns ) ) {
            $existing_columns = array();
        }

        unset( $existing_columns['comments'], $existing_columns['date'] );

        $existing_columns['details'] = __( 'Details', 'propertyhive' );

        $existing_columns['related_to'] = __( 'Related To', 'propertyhive' );

        $existing_columns['due_date'] = __( 'Due Date', 'propertyhive' );

        $existing_columns['assigned_to'] = __( 'Assigned To', 'propertyhive' );

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
            case 'details' :
                
                $details = get_post_meta( $post->ID, '_details', TRUE );
                if ( $details != '' )
                {
                    echo $details;
                }
                else
                {
                    echo '-';
                }

                break;
            case 'due_date' :
                
                $due_date = get_post_meta( $post->ID, '_due_date', TRUE );
                if ( $due_date != '' )
                {   
                    $overdue = false;
                    if ( get_post_meta( $post->ID, '_status', TRUE ) == 'open' && strtotime($due_date) < time() )
                    {
                        $overdue = true;
                    }
                    if ( $overdue )
                    {
                        echo '<span style="font-weight:700; color:#C00">';
                    }
                    echo date("jS F Y", strtotime($due_date));
                    if ( $overdue )
                    {
                        echo '</span>';
                    }
                }
                else
                {
                    echo '-';
                }

                break;
            case 'assigned_to' :
                
                $assigned_to_ids = get_post_meta( get_the_ID(), '_assigned_to', TRUE );
                $assigned_to_names = array();

                if ( is_array($assigned_to_ids) && !empty($assigned_to_ids) )
                {
                    foreach ( $assigned_to_ids as $assigned_to_id )
                    {
                        $user_info = get_userdata($assigned_to_id);
                        if ( $user_info !== FALSE )
                        {
                            $assigned_to_names[] = $user_info->display_name;
                        }
                        else
                        {
                            $assigned_to_names[] = '<em>' . __( 'Unknown User', 'propertyhive' ) . '</em>';
                        }
                    }
                    echo implode(", ", $assigned_to_names);
                }
                else
                {
                    echo 'Everyone';
                }

                break;
            case 'related_to' :
                $related_to = get_post_meta(get_the_ID(), '_related_to', TRUE);
                if ( $related_to != '' )
                {
                    $explode_related_to = explode("|", $related_to);
                    if ( $explode_related_to[0] == 'property' )
                    {
                        $property = new PH_Property( (int)$explode_related_to[1] );
                        echo $property->get_formatted_full_address();
                    }
                    elseif ( $explode_related_to[0] == 'contact' )
                    {
                        echo get_the_title($explode_related_to[1]);
                    }
                    else
                    {
                        echo '-';
                    }
                }
                else
                {
                     echo '-';
                }
                break;
            case 'status' :
                
                echo ucwords(get_post_meta( $post->ID, '_status', TRUE ));

                if ( get_post_meta( $post->ID, '_status', TRUE ) == 'completed' )
                {
                    echo '<br><em>on ' . date("jS F Y", strtotime(get_post_meta( $post->ID, '_completed', TRUE ))) . '</em>';
                }

                break;
            default :
                break;
        }
    }

    /**
     * Make task columns sortable
     *
     * @access public
     * @param mixed $columns
     * @return array
     */
    public function custom_columns_sort( $columns ) {
        $custom = array(
            'due_date'        => '_due_date',
            'status'          => '_status'
        );
        return wp_parse_args( $custom, $columns );
    }

    /**
     * Task column orderby
     *
     * @access public
     * @param mixed $vars
     * @return array
     */
    public function custom_columns_orderby( $vars ) {
        if ( isset( $vars['orderby'] ) ) {
            if ( '_due_date' == $vars['orderby'] ) {
                $vars = array_merge( $vars, array(
                    'meta_key'  => '_due_date',
                    'orderby'   => 'meta_value'
                ) );
            }
            elseif ( '_status' == $vars['orderby'] ) {
                $vars = array_merge( $vars, array(
                    'meta_key'  => '_status',
                    'orderby'   => 'meta_value'
                ) );
            }
        }

        return $vars;
    }

    public function propertyhive_task_complete()
    {
        check_ajax_referer( 'property_tasks', 'security' );

        update_post_meta( $_POST['task_id'], '_status', 'completed' );
        update_post_meta( $_POST['task_id'], '_completed', date("Y-m-d H:i:s") );

        wp_die();
    }

    public function propertyhive_task_open()
    {
        check_ajax_referer( 'property_tasks', 'security' );

        update_post_meta( $_POST['task_id'], '_status', 'open' );

        wp_die();
    }

    public function propertyhive_delete_task()
    {
        check_ajax_referer( 'property_tasks', 'security' );

        $return = '';

        $args = array(
            'post_type' => 'task',
            'p' => $_POST['task_id'],
        );

        $tasks_query = new WP_Query( $args );

        if ( $tasks_query->have_posts() )
        {
            while ( $tasks_query->have_posts() )
            {
                $tasks_query->the_post();

                $response = wp_delete_post( $_POST['task_id'] );
                if ( $response !== false )
                {
                    $return = 'success';
                }
            }
        }
        wp_reset_postdata();

        header("Content-Type: application/json");
        echo json_encode($return);

        wp_die();
    }

    public function propertyhive_get_task()
    {
        check_ajax_referer( 'property_tasks', 'security' );

        $return = array();

        $args = array(
            'post_type' => 'task',
            'p' => $_POST['task_id'],
        );

        $tasks_query = new WP_Query( $args );

        if ( $tasks_query->have_posts() )
        {
            while ( $tasks_query->have_posts() )
            {
                $tasks_query->the_post();

                $assigned_to_ids = get_post_meta( get_the_ID(), '_assigned_to', TRUE );

                $return = array(
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                    'details' => get_post_meta( get_the_ID(), '_details', TRUE ),
                    'due_date' => get_post_meta( get_the_ID(), '_due_date', TRUE ),
                    'assigned_to' => get_post_meta( get_the_ID(), '_assigned_to', TRUE ),
                    'status' => get_post_meta( get_the_ID(), '_status', TRUE ),
                );
            }
        }
        wp_reset_postdata();

        header("Content-Type: application/json");
        echo json_encode($return);

        wp_die();
    }

    public function propertyhive_get_tasks()
    {
        check_ajax_referer( 'property_tasks', 'security' );

        $return = array();

        $meta_query = array(
            'status_clause' => array(
                'key' => '_status',
                'compare' => 'EXISTS',
            ),
            'due_date_clause' => array(
                'key' => '_due_date',
                'compare' => 'EXISTS',
            ), 
        );

        if ( isset($_POST['related_to']) && $_POST['related_to'] != '' )
        {
            $meta_query[] = array(
                'key' => '_related_to',
                'value' => $_POST['related_to'],
            );
        }
        if ( isset($_POST['status']) && $_POST['status'] != '' )
        {
            $meta_query[] = array(
                'key' => '_status',
                'value' => $_POST['status'],
            );
        }
        if ( isset($_POST['assigned_to']) && $_POST['assigned_to'] != '' )
        {
            $meta_query[] = array(
                'relation' => 'OR',
                array(
                    'key' => '_assigned_to',
                    'value' => '',
                ),
                array(
                    'key' => '_assigned_to',
                    'value' => '"' . $_POST['assigned_to'] . '"',
                    'compare' => 'LIKE'
                ),
            );
        }

        $args = array(
            'post_type' => 'task',
            'meta_query' => $meta_query,
            'orderby' => array( 
                'status_clause' => 'DESC',
                'due_date_clause' => 'DESC',
            ),
        );

        if ( isset($_POST['posts_per_page']) )
        {
            $args['posts_per_page'] = $_POST['posts_per_page'];
        }
        else
        {
            $args['nopaging'] = true;
        }

        $tasks_query = new WP_Query( $args );

        if ( $tasks_query->have_posts() )
        {
            while ( $tasks_query->have_posts() )
            {
                $tasks_query->the_post();

                $assigned_to_ids = get_post_meta( get_the_ID(), '_assigned_to', TRUE );
                $assigned_to_names = array();

                if ( is_array($assigned_to_ids) && !empty($assigned_to_ids) )
                {
                    foreach ( $assigned_to_ids as $assigned_to_id )
                    {
                        $user_info = get_userdata($assigned_to_id);
                        $assigned_to_names[] = $user_info->display_name;
                    }
                }

                $return[] = array(
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                    'details' => get_post_meta( get_the_ID(), '_details', TRUE ),
                    'due_date' => get_post_meta( get_the_ID(), '_due_date', TRUE ),
                    'due_date_formatted' => ( 
                        ( get_post_meta( get_the_ID(), '_due_date', TRUE ) != '' ) ? 
                        ( ( strtotime(get_post_meta( get_the_ID(), '_due_date', TRUE )) < time() ) ? '<span style="color:#900">' : '' ) . 
                        date("jS F Y", strtotime(get_post_meta( get_the_ID(), '_due_date', TRUE ))) . 
                        ( ( strtotime(get_post_meta( get_the_ID(), '_due_date', TRUE )) < time() ) ? '</span>' : '' ) : 
                        ''
                    ),
                    'assigned_to' => $assigned_to_ids,
                    'assigned_to_names' => implode(", ", $assigned_to_names),
                    'completed' => get_post_meta( get_the_ID(), '_completed', TRUE ),
                    'completed_formatted' => ( 
                        ( get_post_meta( get_the_ID(), '_completed', TRUE ) != '' ) ? 
                        date("jS F Y", strtotime(get_post_meta( get_the_ID(), '_completed', TRUE ))) : 
                        ''
                    ),
                    'status' => get_post_meta( get_the_ID(), '_status', TRUE ),
                    'edit_link' => get_edit_post_link( get_the_ID() ),
                );
            }
        }
        wp_reset_postdata();

        header("Content-Type: application/json");
        echo json_encode($return);

        wp_die();
    }

    public function propertyhive_create_task()
    {
        check_ajax_referer( 'property_tasks', 'security' );

        // Create post object
        $my_post = array(
            'post_title'    => wp_strip_all_tags( $_POST['title'] ),
            'post_status'   => 'publish',
            'post_type'     => 'task',
        );

        if ( isset($_POST['task_id']) && $_POST['task_id'] != '' )
        {
            $my_post['ID'] = (int)$_POST['task_id'];

            // Update the post in the database
            $task_post_id = wp_insert_post( $my_post );
        }
        else
        {
            // Insert the post into the database
            $task_post_id = wp_insert_post( $my_post );
        }

        if ( is_wp_error($task_post_id) || $task_post_id == 0 )
        {
            echo 'error';
        }
        else
        {
            if ( isset($_POST['task_id']) && $_POST['task_id'] != '' )
            {

            }
            else
            {
                update_post_meta( $task_post_id, '_status', 'open' );
            }
            update_post_meta( $task_post_id, '_details', wp_strip_all_tags( $_POST['details'] ) );
            update_post_meta( $task_post_id, '_due_date', wp_strip_all_tags( $_POST['due_date'] ) );
            update_post_meta( $task_post_id, '_assigned_to', ( isset($_POST['assigned_to']) && is_array($_POST['assigned_to']) && !empty($_POST['assigned_to']) ) ? $_POST['assigned_to'] : '' );
            update_post_meta( $task_post_id, '_related_to', wp_strip_all_tags( $_POST['related_to'] ) );

            echo 'success';
        }

        wp_die();
    }

    public function admin_styles()
    {
        $assets_path = str_replace( array( 'http:', 'https:' ), '', untrailingslashit( plugins_url( '/', __FILE__ ) ) ) . '/assets/';

        wp_enqueue_style( 'propertyhive_tasks_styles', $assets_path . 'propertyhive-tasks.css', array(), PH_TASKS_VERSION );

        $screen = get_current_screen();

        if ( defined('PH_VERSION') && in_array( $screen->id, array( 'task' ) ) )
        {
            wp_enqueue_style( 'chosen', PH()->plugin_url() . '/assets/css/chosen.css', array(), PH_VERSION );
        }
    }

    public function admin_scripts()
    {
        $assets_path = str_replace( array( 'http:', 'https:' ), '', untrailingslashit( plugins_url( '/', __FILE__ ) ) ) . '/assets/';

        wp_register_script( 'propertyhive_tasks', $assets_path . 'propertyhive-tasks.js', array( 'jquery' ), PH_TASKS_VERSION );
        wp_enqueue_script( 'propertyhive_tasks' );

        $params = array(
            'ajax_nonce' => wp_create_nonce('property_tasks'),
            'current_user_id' => get_current_user_id(),
            'dashboard_tasks_list_link' => admin_url( 'edit.php?post_type=task' ),
        );
        wp_localize_script( 'propertyhive_tasks', 'ajax_object', $params );
    }

    public function property_task_meta_box( $meta_boxes ) 
    {
        global $post, $pagenow;

        if ( $pagenow != 'post-new.php' && get_post_type($post->ID) == 'property' )
        {
            $meta_boxes[1] = array(
                'id' => 'propertyhive-property-tasks',
                'title' => __( 'Tasks', 'propertyhive' ),
                'callback' => 'PH_Meta_Box_Property_Tasks::output',
                'screen' => 'property',
                'context' => 'normal',
                'priority' => 'high'
            );
        }

        return $meta_boxes;
    }

    public function contact_task_meta_box( $meta_boxes ) 
    {
        global $post, $pagenow;

        if ( $pagenow != 'post-new.php' && get_post_type($post->ID) == 'contact' )
        {
            $meta_boxes[1] = array(
                'id' => 'propertyhive-contact-tasks',
                'title' => __( 'Tasks', 'propertyhive' ),
                'callback' => 'PH_Meta_Box_Contact_Tasks::output',
                'screen' => 'contact',
                'context' => 'normal',
                'priority' => 'high'
            );
        }

        return $meta_boxes;
    }

    /**
     * Admin Menu
     */
    public function admin_menu() 
    {
        add_submenu_page( 'propertyhive', __( 'Tasks', 'propertyhive' ), __( 'Tasks', 'propertyhive' ), 'manage_propertyhive', 'edit.php?post_type=task' );
    }

    private function includes()
    {
        include_once( 'includes/class-ph-meta-box-property-tasks.php' );
        include_once( 'includes/class-ph-meta-box-contact-tasks.php' );

        include_once( 'includes/class-ph-meta-box-task-details.php' );
    }

    /**
     * Include admin files conditionally.
     */
    public function conditional_includes() {
        if ( ! $screen = get_current_screen() ) {
            return;
        }

        switch ( $screen->id ) {
            case 'dashboard' :
                include_once( 'includes/class-ph-admin-tasks-dashboard.php' );
            break;
        }
    }

    /**
     * Define PH Tasks Constants
     */
    private function define_constants() 
    {
        define( 'PH_TASKS_PLUGIN_FILE', __FILE__ );
        define( 'PH_TASKS_VERSION', $this->version );
    }

    /**
     * Output error message if core Property Hive plugin isn't active
     */
    public function tasks_error_notices() 
    {
        global $post;

        if (!is_plugin_active('propertyhive/propertyhive.php'))
        {
            $message = __( "The Property Hive plugin must be installed and activated before you can use the Property Hive Tasks add-on", 'propertyhive' );
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
        $settings_tabs['tasks'] = __( 'Tasks', 'propertyhive' );
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

        $settings = $this->get_tasks_settings();
        
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

        $current_settings = get_option( 'propertyhive_tasks', array() );

        $propertyhive_tasks = array(
            
        );

        $propertyhive_tasks = array_merge($current_settings, $propertyhive_tasks);

        update_option( 'propertyhive_tasks', $propertyhive_tasks );
    }

    /**
     * Get rotate property photos settings
     *
     * @return array Array of settings
     */
    public function get_tasks_settings() {

        $current_settings = get_option( 'propertyhive_tasks', array() );

        $settings = array(

            array( 'title' => __( 'Tasks', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'tasks_settings' )

        );

        /*$settings[] = array(
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
        );*/

        $settings[] = array( 'type' => 'sectionend', 'id' => 'tasks_settings');

        return $settings;
    }

    public function register_post_type_task()
    {
        register_post_type( "task",
            apply_filters( 'propertyhive_register_post_type_task',
                array(
                    'labels' => array(
                            'name'                  => __( 'Tasks', 'propertyhive' ),
                            'singular_name'         => __( 'Task', 'propertyhive' ),
                            'menu_name'             => _x( 'Tasks', 'Admin menu name', 'propertyhive' ),
                            'add_new'               => __( 'Add Task', 'propertyhive' ),
                            'add_new_item'          => __( 'Add New Task', 'propertyhive' ),
                            'edit'                  => __( 'Edit', 'propertyhive' ),
                            'edit_item'             => __( 'Edit Task', 'propertyhive' ),
                            'new_item'              => __( 'New Task', 'propertyhive' ),
                            'view'                  => __( 'View Task', 'propertyhive' ),
                            'view_item'             => __( 'View Task', 'propertyhive' ),
                            'search_items'          => __( 'Search Tasks', 'propertyhive' ),
                            'not_found'             => __( 'No tasks found', 'propertyhive' ),
                            'not_found_in_trash'    => __( 'No tasks found in trash', 'propertyhive' ),
                            'parent'                => __( 'Parent Task', 'propertyhive' )
                        ),
                    'description'           => __( 'This is where you can add new tasks.', 'propertyhive' ),
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
}

endif;

/**
 * Returns the main instance of PH_Tasks to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return PH_Tasks
 */
function PHTASKS() {
    return PH_Tasks::instance();
}

PHTASKS();

if( is_admin() && file_exists(  dirname( __FILE__ ) . '/propertyhive-tasks-update.php' ) )
{
    include_once( dirname( __FILE__ ) . '/propertyhive-tasks-update.php' );
}