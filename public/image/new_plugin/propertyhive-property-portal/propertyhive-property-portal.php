<?php
/**
 * Plugin Name: Property Hive Property Portal Add On
 * Plugin Uri: http://wp-property-hive.com/addons/property-portal/
 * Description: Add On for Property Hive allowing you to turn your site into a portal by assigning and showing properties for multiple agents
 * Version: 1.0.9
 * Author: PropertyHive
 * Author URI: http://wp-property-hive.com
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'PH_Property_Portal' ) ) :

final class PH_Property_Portal {

    /**
     * @var string
     */
    public $version = '1.0.9';

    /**
     * @var PropertyHive The single instance of the class
     */
    protected static $_instance = null;
    
    /**
     * Main PropertyHive Property Portal Instance
     *
     * Ensures only one instance of Property Hive Property Portal is loaded or can be loaded.
     *
     * @static
     * @return Property Hive Property Portal - Main instance
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

        $this->id    = 'propertyportal';
        $this->label = __( 'Property Portal', 'propertyhive' );

        // Define constants
        $this->define_constants();

        // Include required files
        $this->includes();

        add_action( 'init', array( __CLASS__, 'register_post_types' ), 5 );

        add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );

        add_action( 'admin_notices', array( $this, 'propertyimport_error_notices') );

        add_action( 'admin_menu', array( $this, 'admin_menu' ) );

        add_action( 'admin_print_scripts', array( $this, 'remove_month_filter' ) );

        add_action( 'wp_enqueue_scripts', array( $this, 'frontend_styles' ) );

        // Admin Columns
        add_filter( 'manage_edit-agent_columns', array( $this, 'edit_columns' ) );
        add_action( 'manage_agent_posts_custom_column', array( $this, 'custom_columns' ), 2 );

        add_filter( 'manage_edit-property_columns', array( $this, 'edit_property_columns' ), 99 );
        add_action( 'manage_property_posts_custom_column', array( $this, 'custom_property_columns' ), 99 );

        add_filter( 'propertyhive_property_filters', array( $this, 'property_agent_branch_filter' ) );
        add_filter( 'request', array( $this, 'request_query' ) );

        // Agent meta boxes
        add_action( 'add_meta_boxes', 'ph_agent_add_meta_boxes', 30 );
        add_action( 'save_post', array( $this, 'save_meta_boxes' ), 1, 2 );
        add_action( 'propertyhive_process_agent_meta', 'PH_Meta_Box_Agent_Details::save', 10, 2 );
        add_action( 'propertyhive_process_agent_meta', 'PH_Meta_Box_Agent_Branches::save', 10, 2 );
        add_action( 'propertyhive_process_property_meta', 'PH_Meta_Box_Property_Agent::save', 10, 2 );

        // Property meta boxes
        add_action( 'propertyhive_process_property_meta', 'PH_Meta_Box_Property_Agent::save', 11, 2 );

        add_filter( 'propertyhive_property_summary_meta_boxes', array( $this, 'add_property_agent_meta_box' ) );

        add_filter( 'propertyhive_settings_tabs_array', array( $this, 'add_settings_tab' ), 19 );
        add_action( 'propertyhive_settings_' . $this->id, array( $this, 'output' ) );
        add_action( 'propertyhive_settings_save_' . $this->id, array( $this, 'save' ) );

        // Template loader
        add_filter( 'propertyhive_loaded_template', array( $this, 'loaded_template' ) );
        add_filter( 'is_propertyhive', array( $this, 'is_propertyhive' ) );

        add_filter( 'propertyhive_property_query_meta_query', array( $this, 'property_meta_query' ), 10, 2 );

        // Add hidden fields to search form to retain currently selected agent and/or branch
        add_filter( 'propertyhive_search_form_fields_default', array( $this, 'propertyhive_add_search_form_hidden_fields' ), 10, 1 );

        add_filter( 'propertyhive_screen_ids', array( $this, 'add_agent_to_screen_ids' ) );

        add_filter( 'property_negotiator_exclude_roles', array( $this, 'negotiator_exclude_agent_role' ) );
        add_filter( 'propertyhive_allowed_login_post_type', array( $this, 'allow_agent_login_post_type' ) );

        add_action( 'wp_ajax_propertyhive_create_agent_login', array( $this, 'create_agent_login' ) );

        add_action( 'phpropertyportalcronhook', array( $this, 'generate_branches_post_meta_cron' ) );

        add_action( 'delete_user', array( $this, 'remove_agent_user_link' ) );
    }

    public function allow_agent_login_post_type( $post_types )
    {
        $post_types[] = 'agent';
        return $post_types;
    }

    public function negotiator_exclude_agent_role( $roles )
    {
        $roles[] = 'property_hive_agent';
        return $roles;
    }

    public function create_agent_login()
    {
        check_ajax_referer( 'create-login', 'security' );

        header( 'Content-Type: application/json; charset=utf-8' );

        if (empty($_POST['agent_id']))
        {
            $return = array('error' => 'No agent selected');
            echo json_encode( $return );
            die();
        }

        if (empty($_POST['email_address']))
        {
            $return = array('error' => 'No email address entered');
            echo json_encode( $return );
            die();
        }

        if (!is_email($_POST['email_address']))
        {
            $return = array('error' => 'Invalid email address entered');
            echo json_encode( $return );
            die();
        }

        if (empty($_POST['password']))
        {
            $return = array('error' => 'No password entered');
            echo json_encode( $return );
            die();
        }

         // Create user
        $userdata = array(
            'display_name' => get_the_title((int)$_POST['agent_id']),
            'user_login' => sanitize_email($_POST['email_address']),
            'user_email' => sanitize_email($_POST['email_address']),
            'user_pass'  => $_POST['password'],
            'role' => 'property_hive_agent',
            'show_admin_bar_front' => 'false',
        );

        $user_id = wp_insert_user( $userdata );

        // On success
        if ( ! is_wp_error( $user_id ) )
        {
            // Assign user ID to CPT
            add_post_meta( (int)$_POST['agent_id'], '_user_id', $user_id );

            $return = array('success' => true);
        }
        else
        {
            $return = array('error' => 'Failed to create agent login');
        }

        echo json_encode( $return );
        die();
    }

    public function remove_agent_user_link( $user_id )
    {
        global $post;

        $args = array(
            'post_type' => 'agent',
            'nopaging' => true,
            'meta_query' => array(
                array(
                    'key' => '_user_id',
                    'value' => (int)$user_id,
                )
            )
        );
        $agent_query = new WP_Query( $args );
        if ( $agent_query->have_posts() )
        {
            while ( $agent_query->have_posts() )
            {
                $agent_query->the_post();

                delete_post_meta( $post->ID, '_user_id' );

                wp_reset_postdata();
            }
        }
    }

    public function add_agent_to_screen_ids( $screen_ids )
    {
        $screen_ids[] = 'agent';
        return $screen_ids;
    }

    public function propertyhive_add_search_form_hidden_fields($fields)
    {
        $fields['agent_id'] = array(
            'type' => 'hidden',
            'value' => ( (isset($_GET['agent_id'])) ? $_GET['agent_id'] : '' )
        );

        $fields['branch_id'] = array(
            'type' => 'hidden',
            'value' => ( (isset($_GET['branch_id'])) ? $_GET['branch_id'] : '' )
        );

        return $fields;
    }

    public function property_meta_query( $meta_query, $q )
    {
        if ( isset( $_REQUEST['agent_id'] ) && $_REQUEST['agent_id'] != '' )
        {
            $meta_query[] = array(
                'key'     => '_agent_id',
                'value'   => sanitize_text_field( $_REQUEST['agent_id'] )
            );
        }

        if ( isset( $_REQUEST['branch_id'] ) && $_REQUEST['branch_id'] != '' )
        {
            $meta_query[] = array(
                'key'     => '_branch_id',
                'value'   => sanitize_text_field( $_REQUEST['branch_id'] )
            );
        }

        return $meta_query;
    }

    public function frontend_styles()
    {
        wp_enqueue_style( 'propertyhive-property-portal', PHPP()->plugin_url() . '/assets/css/propertyhive-property-portal.css', '', PH_PROPERTYPORTAL_VERSION, 'all' );
    }

    public function loaded_template( $template )
    {
        $file = '';

        if ( is_post_type_archive( 'agent' ) || ( function_exists( 'ph_get_page_id' ) && is_page( ph_get_page_id( 'agent_directory' ) ) ) ) {

            $file   = 'archive-agent.php';
            $find[] = $file;
            $find[] = PH_TEMPLATE_PATH . $file;

        }

        if ( $file != '' ) 
        {
            $template = locate_template( array_unique($find) );
            if ( ! $template )
            {
                $template = untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/templates/' . $file;
            }
        }

        return $template;
    }

    public function is_propertyhive( $is_propertyhive )
    {
        if ( is_post_type_archive( 'agent' ) || ( function_exists( 'ph_get_page_id' ) && is_page( ph_get_page_id( 'agent_directory' ) ) ) )
        {
            return true;
        }
        return $is_propertyhive;
    }

    /**
     * Add a new settings tab to the Property Hive settings tabs array.
     *
     * @param array $settings_tabs Array of Property Hive setting tabs & their labels, excluding the Subscription tab.
     * @return array $settings_tabs Array of Property Hive setting tabs & their labels, including the Subscription tab.
     */
    public function add_settings_tab( $settings_tabs ) {
        $settings_tabs['propertyportal'] = __( 'Property Portal', 'propertyhive' );
        return $settings_tabs;
    }

    public function get_property_portal_settings() {

        return apply_filters( 'propertyhive_property_portal_settings', array(

            array( 'title' => __( 'Property Portal Options', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'property_portal_options' ),

            array(
                'title' => __( 'Agent Directory Page', 'propertyhive' ),
                'id'        => 'propertyhive_agent_directory_page_id',
                'type'      => 'single_select_page',
                'default'   => '',
                'css'       => 'min-width:300px;',
                'desc'  => __( 'This sets the page of your \'Agent Directory\', if you require one on your website', 'propertyhive' ),
            ),
            
            array( 'type' => 'sectionend', 'id' => 'property_portal_options'),

        ) ); // End property portal settings

    }

    /**
     * Output the settings
     */
    public function output() {
        $settings = $this->get_property_portal_settings();

        PH_Admin_Settings::output_fields( $settings );
    }

    /**
     * Save settings
     */
    public function save() {

        $settings = $this->get_property_portal_settings();

        PH_Admin_Settings::save_fields( $settings );

        flush_rewrite_rules();
    }

    public function add_property_agent_meta_box( $meta_boxes = array() )
    {
        $meta_boxes[7] = array(
            'id' => 'propertyhive-property-agent',
            'title' => __( 'Property Agent', 'propertyhive' ),
            'callback' => 'PH_Meta_Box_Property_Agent::output',
            'screen' => 'property',
            'context' => 'normal',
            'priority' => 'high'
        );

        return $meta_boxes;
    }

    /**
     * Define PH Property Import Constants
     */
    private function define_constants() 
    {
        define( 'PH_PROPERTYPORTAL_PLUGIN_FILE', __FILE__ );
        define( 'PH_PROPERTYPORTAL_VERSION', $this->version );
    }

    private function includes()
    {
        include_once( dirname( __FILE__ ) . "/includes/class-ph-property-portal-install.php" );

        include_once( dirname( __FILE__ ) . "/includes/class-ph-agent.php" );
        include_once( dirname( __FILE__ ) . "/includes/class-ph-agent-branch.php" );
        include_once( dirname( __FILE__ ) . "/includes/class-ph-property-agent-branch.php" );

        include_once( dirname( __FILE__ ) . "/includes/meta-boxes.php" );
        include_once( dirname( __FILE__ ) . "/includes/meta-boxes/class-ph-meta-box-agent-details.php" );
        include_once( dirname( __FILE__ ) . "/includes/meta-boxes/class-ph-meta-box-agent-branches.php" );
        include_once( dirname( __FILE__ ) . "/includes/meta-boxes/class-ph-meta-box-agent-actions.php" );
        include_once( dirname( __FILE__ ) . "/includes/meta-boxes/class-ph-meta-box-property-agent.php" );
    }

    /**
     * Check if we're saving, then trigger an action based on the post type
     *
     * @param  int $post_id
     * @param  object $post
     */
    public function save_meta_boxes( $post_id, $post ) {
        // $post_id and $post are required
        if ( empty( $post_id ) || empty( $post ) ) {
            return;
        }

        // Dont' save meta boxes for revisions or autosaves
        if ( defined( 'DOING_AUTOSAVE' ) || is_int( wp_is_post_revision( $post ) ) || is_int( wp_is_post_autosave( $post ) ) ) {
            return;
        }
        
        // Check the nonce
        if ( empty( $_POST['propertyhive_meta_nonce'] ) || ! wp_verify_nonce( $_POST['propertyhive_meta_nonce'], 'propertyhive_save_data' ) ) {
            return;
        } 

        // Check the post being saved == the $post_id to prevent triggering this call for other save_post events
        if ( empty( $_POST['post_ID'] ) || $_POST['post_ID'] != $post_id ) {
            return;
        }

        // Check user has permission to edit
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // Check the post type
        if ( ! in_array( $post->post_type, array( 'agent' ) ) ) {
            return;
        }

        do_action( 'propertyhive_process_' . $post->post_type . '_meta', $post_id, $post );

        // Save _branches post_meta on agent
        $this->update_branches_post_meta($post_id);
    }

    public function update_branches_post_meta( $agent_post_id )
    {
        // Save _branches post_meta on agent
        $branches_array = array();
        $args = array(
            'post_type' => 'branch',
            'nopaging' => true,
            'orderby' => 'menu_order',
            'order' => 'ASC',
            'meta_query' => array(
                array(
                    'key' => '_agent_id',
                    'value' => $agent_post_id
                )
            )
        );
        $branches_query = new WP_Query( $args );

        if ($branches_query->have_posts())
        {
            while ($branches_query->have_posts())
            {
                $branches_query->the_post();

                $branches_array[get_the_ID()] = array(
                    'name' => get_the_title(),
                );
            }
        }
        wp_reset_postdata();

        update_post_meta( $agent_post_id, '_branches', $branches_array );
    }

    /**
     * Output error message if core PropertyHive plugin isn't active
     */
    public function propertyimport_error_notices() 
    {
        if (!is_plugin_active('propertyhive/propertyhive.php'))
        {
            $message = "The PropertyHive plugin must be installed and activated before you can use the PropertyHive Property Portal add-on";
            echo"<div class=\"error\"> <p>$message</p></div>";
        }
    }

    /**
     * Enqueue styles
     */
    public function admin_styles() {
        global $wp_scripts;

        if (is_plugin_active('propertyhive/propertyhive.php'))
        {
            $screen = get_current_screen();

            if ( $screen->id == 'agent' )
            {
                wp_enqueue_style( 'propertyhive_admin_styles', PH()->plugin_url() . '/assets/css/admin.css', array(), PH_VERSION );
                wp_enqueue_style( 'font_awesome', PH()->plugin_url() . '/assets/css/font-awesome.min.css', array(), PH_VERSION );
                wp_enqueue_style( 'propertyhive_property_portal_admin_styles', PHPP()->plugin_url() . '/assets/css/admin.css', array(), PH_PROPERTYPORTAL_VERSION );
            }
        }
    }

    /**
     * Enqueue scripts
     */
    public function admin_scripts() {
        global $wp_query, $post;

        if (is_plugin_active('propertyhive/propertyhive.php'))
        {
            $screen  = get_current_screen();

            if ( $screen->id == 'agent' )
            {
                wp_enqueue_media();
                wp_enqueue_script( 'propertyhive_admin_meta_boxes' );
            }
        }
    }

    /**
     * Get the plugin url.
     *
     * @return string
     */
    public function plugin_url() {
        return untrailingslashit( plugins_url( '/', __FILE__ ) );
    }

    /**
     * Admin Menu
     */
    public function admin_menu() 
    {
        add_submenu_page( 'propertyhive', __( 'Agents', 'propertyhive' ), __( 'Agents', 'propertyhive' ), 'manage_propertyhive', 'edit.php?post_type=agent' );
    }

    /**
     * Remove month filter from agent property hive pages
     */
    public function remove_month_filter() {
        global $typenow;
        
        if ($typenow == 'agent')
        {
            add_filter('months_dropdown_results', '__return_empty_array');
        }
    }

    /**
     * Register core post types
     */
    public static function register_post_types() {
        
        if ( post_type_exists('agent') )
            return;

        register_post_type( "agent",
            apply_filters( 'propertyhive_register_post_type_agent',
                array(
                    'labels' => array(
                            'name'                  => __( 'Agents', 'propertyhive' ),
                            'singular_name'         => __( 'Agent', 'propertyhive' ),
                            'menu_name'             => _x( 'Agents', 'Admin menu name', 'propertyhive' ),
                            'add_new'               => __( 'Add Agent', 'propertyhive' ),
                            'add_new_item'          => __( 'Add New Agent', 'propertyhive' ),
                            'edit'                  => __( 'Edit', 'propertyhive' ),
                            'edit_item'             => __( 'Edit Agent', 'propertyhive' ),
                            'new_item'              => __( 'New Agent', 'propertyhive' ),
                            'view'                  => __( 'View Agent', 'propertyhive' ),
                            'view_item'             => __( 'View Agent', 'propertyhive' ),
                            'search_items'          => __( 'Search Agents', 'propertyhive' ),
                            'not_found'             => __( 'No agents found', 'propertyhive' ),
                            'not_found_in_trash'    => __( 'No agents found in trash', 'propertyhive' ),
                            'parent'                => __( 'Parent Agent', 'propertyhive' )
                        ),
                    'description'           => __( 'This is where you can add new agents to your site.', 'propertyhive' ),
                    'public'                => true,
                    'show_ui'               => true,
                    'capability_type'       => 'post',
                    'map_meta_cap'          => true,
                    'publicly_queryable'    => true,
                    'exclude_from_search'   => false,
                    'hierarchical'          => false, // Hierarchical causes memory issues - WP loads all records!
                    'query_var'             => true,
                    'supports'              => array( 'title' ),
                    'has_archive'           => ( function_exists( 'ph_get_page_id' ) && $agent_directory_page_id = ph_get_page_id( 'agent_directory' ) ) && get_page( $agent_directory_page_id ) ? get_page_uri( $agent_directory_page_id ) : false,
                    'show_in_nav_menus'     => false,
                    'show_in_menu'          => false,
                    'show_in_admin_bar'     => true,
                )
            )
        );

        register_post_type( "branch",
            apply_filters( 'propertyhive_register_post_type_branch',
                array(
                    'public'                => false,
                    'show_ui'               => false,
                    'capability_type'       => 'post',
                    'map_meta_cap'          => true,
                    'publicly_queryable'    => true,
                    'exclude_from_search'   => true,
                    'hierarchical'          => false, // Hierarchical causes memory issues - WP loads all records!
                    'query_var'             => true,
                    'supports'              => array( 'title' ),
                    'show_in_nav_menus'     => false,
                    'show_in_menu'          => false
                )
            )
        );

    }

    /**
     * Change the columns shown in admin.
     */
    public function edit_columns( $existing_columns ) {

        if ( empty( $existing_columns ) && ! is_array( $existing_columns ) ) {
            $existing_columns = array();
        }

        $columns = array();
        $columns['cb'] = $existing_columns['cb'];
        $columns['logo'] = '<span class="ph-image tips" data-tip="' . __( 'Logo', 'propertyhive' ) . '">' . __( 'Logo', 'propertyhive' ) . '</span>';
        $columns['name'] = __( 'Name', 'propertyhive' );
        $columns['branches'] = __( 'Branches', 'propertyhive' );

        return $columns;
    }

    /**
     * Define our custom columns shown in admin.
     * @param  string $column
     */
    public function custom_columns( $column ) {
        global $post, $propertyhive;

        switch ( $column ) {
            case 'logo' :
                
                $logo_src = wp_get_attachment_image_src( get_post_meta( $post->ID, '_logo', true ), 'thumbnail' );
                
                echo '<a href="' . get_edit_post_link( $post->ID ) . '">';
                if ($logo_src !== FALSE)
                {
                    echo '<img src="' . $logo_src[0] . '" alt="' . get_the_title() . '" width="100">';
                }
                else
                {
                    // placeholder image
                }
                echo '</a>';
                break;
            case 'name' :
                
                $edit_link        = get_edit_post_link( $post->ID );
                $title            = get_the_title();
                if ( empty($title) )
                {
                    $title = __( '(no title)' );
                }
                $post_type_object = get_post_type_object( $post->post_type );
                $can_edit_post    = current_user_can( $post_type_object->cap->edit_post, $post->ID );

                echo '<strong><a class="row-title" href="' . esc_url( $edit_link ) .'">' . $title.'</a></strong>';

                // Excerpt view
                if ( isset( $_GET['mode'] ) && 'excerpt' == $_GET['mode'] ) {
                    echo apply_filters( 'the_excerpt', $post->post_excerpt );
                }

                // Get actions
                $actions = array();
                if ( $can_edit_post && 'trash' != $post->post_status ) {
                    $actions['edit'] = '<a href="' . get_edit_post_link( $post->ID, true ) . '" title="' . esc_attr( __( 'Edit this item', 'propertyhive' ) ) . '">' . __( 'Edit', 'propertyhive' ) . '</a>';
                }
                if ( current_user_can( $post_type_object->cap->delete_post, $post->ID ) ) {
                    if ( 'trash' == $post->post_status ) {
                        $actions['untrash'] = '<a title="' . esc_attr( __( 'Restore this item from the Trash', 'propertyhive' ) ) . '" href="' . wp_nonce_url( admin_url( sprintf( $post_type_object->_edit_link . '&amp;action=untrash', $post->ID ) ), 'untrash-post_' . $post->ID ) . '">' . __( 'Restore', 'propertyhive' ) . '</a>';
                    } elseif ( EMPTY_TRASH_DAYS ) {
                        $actions['trash'] = '<a class="submitdelete" title="' . esc_attr( __( 'Move this item to the Trash', 'propertyhive' ) ) . '" href="' . get_delete_post_link( $post->ID ) . '">' . __( 'Trash', 'propertyhive' ) . '</a>';
                    }

                    if ( 'trash' == $post->post_status || ! EMPTY_TRASH_DAYS ) {
                        $actions['delete'] = '<a class="submitdelete" title="' . esc_attr( __( 'Delete this item permanently', 'propertyhive' ) ) . '" href="' . get_delete_post_link( $post->ID, '', true ) . '">' . __( 'Delete Permanently', 'propertyhive' ) . '</a>';
                    }
                }

                $actions = apply_filters( 'post_row_actions', $actions, $post );

                echo '<div class="row-actions">';

                $i = 0;
                $action_count = sizeof($actions);

                foreach ( $actions as $action => $link ) {
                    ++$i;
                    ( $i == $action_count ) ? $sep = '' : $sep = ' | ';
                    echo '<span class="' . $action . '">' . $link . $sep . '</span>';
                }
                echo '</div>';
            break;
            case 'branches' :

                $branches = get_post_meta($post->ID, '_branches', true);
                if ( is_array($branches) )
                {
                    $i = 1;
                    foreach( $branches as $branch )
                    {
                        echo $branch['name'];
                        if ( $i !== count($branches) )
                        {
                            echo '<br>';
                        }
                    }
                }
                break;
            default :
                break;
        }
    }

    /**
     * Change the columns shown in admin.
     */
    public function edit_property_columns( $existing_columns ) {

        if ( empty( $existing_columns ) && ! is_array( $existing_columns ) ) {
            $existing_columns = array();
        }

        $existing_columns['agent_branch'] = __( 'Agent / Branch', 'propertyhive' );

        return $existing_columns;
    }

    /**
     * Define our custom columns shown in admin.
     * @param  string $column
     */
    public function custom_property_columns( $column ) {
        global $post, $propertyhive;

        switch ( $column ) {
            case 'agent_branch' :

                $property = new PH_Property( $post->ID );
                
                if ( $property->agent_id != '' )
                {
                    $logo_src = wp_get_attachment_image_src( get_post_meta( $property->agent_id, '_logo', true ), 'medium' );
                
                    if ($logo_src !== FALSE)
                    {
                        echo '<img src="' . $logo_src[0] . '" alt="' . get_the_title() . '" width="100"><br>';
                    }
                    else
                    {
                        // placeholder image
                    }

                    echo get_the_title( $property->agent_id );

                    if ( $property->branch_id != '' )
                    {
                        echo '<br>' . get_the_title( $property->branch_id );
                    }
                }
                else
                {
                    echo '-';
                }
                
                break;
            default :
                break;
        }
    }

    public function property_agent_branch_filter( $output )
    {
        global $post;

         // Department filtering
        $output  .= '<select name="_agent_branch" id="dropdown_property_agent_branch">';
            
        $output .= '<option value="">' . __( 'All Agents', 'propertyhive' ) . '</option>
        <option value="unassigned" ' . selected( 'unassigned', ( isset( $_GET['_agent_branch'] ) ? $_GET['_agent_branch'] : '' ) , false ) . '>' . __( 'Not Assigned To Agent / Branch', 'propertyhive' ) . '</option>';
        
        $args = array(
            'post_type' => 'agent',
            'nopaging' => true,
            'orderby' => 'title',
            'order' => 'ASC'
        );
        $agent_query = new WP_Query( $args );
        if ( $agent_query->have_posts() )
        {
            while ( $agent_query->have_posts() )
            {
                $agent_query->the_post();

                $agent_id = $post->ID;
                
                $output .= '<option value="' . $agent_id . '"';
                if ( isset( $_GET['_agent_branch'] ) && ! empty( $_GET['_agent_branch'] ) )
                {
                    $output .= selected( $agent_id, $_GET['_agent_branch'], false );
                }
                $output .= '>' . get_the_title() . '</option>';

                $agent_branches = get_post_meta($agent_id, '_branches', true);

                if ( !empty($agent_branches) && is_array($agent_branches) )
                {
                    foreach( $agent_branches as $branch_id => $branch)
                    {
                        $output .= '<option value="' . $agent_id . '|' . $branch_id . '"';
                        if ( isset( $_GET['_agent_branch'] ) && ! empty( $_GET['_agent_branch'] ) )
                        {
                            $output .= selected( $agent_id . '|' . $branch_id, $_GET['_agent_branch'], false );
                        }
                        $output .= '>- ' . $branch['name'] . '</option>';
                    }
                }
            }
        }

        $output .= '</select>';

        return $output;
    }

    /**
     * Filters and sorting handler
     * @param  array $vars
     * @return array
     */
    public function request_query( $vars ) {
        global $typenow, $wp_query;

        if ( 'property' === $typenow ) 
        {
            if ( ! empty( $_GET['_agent_branch'] ) ) 
            {
                if ( !isset($vars['meta_query']) ) { $vars['meta_query'] = array(); }

                if ( $_GET['_agent_branch'] == 'unassigned' )
                {
                    $vars['meta_query'][] = array(
                        'relation' => 'OR',
                        array(
                            'key' => '_branch_id',
                            'compare' => 'NOT EXISTS',
                        ),
                        array(
                            'key' => '_branch_id',
                            'value' => '',
                            'compare' => '=',
                        )
                    );
                }
                else
                {
                    $explode_agent_branch = explode( "|", sanitize_text_field( $_GET['_agent_branch'] ) );
                    
                    if ( count($explode_agent_branch) == 2 )
                    {
                        $vars['meta_query'][] = array(
                            'key' => '_branch_id',
                            'value' => $explode_agent_branch[1],
                        );
                    }
                    else
                    {
                        $vars['meta_query'][] = array(
                            'key' => '_agent_id',
                            'value' => $explode_agent_branch[0],
                        );
                    }
                }
            }
        }


        return $vars;
    }

    public function generate_branches_post_meta_cron()
    {
        $args = array(
            'post_type' => 'agent',
            'nopaging' => true,
            'fields' => 'ids',
            'orderby' => 'rand' // done in the event there are lots of agents and it can potentially timeout. Hacky but at least it'd do different agents
        );
        $agent_query = new WP_Query($args);

        if ($agent_query->have_posts())
        {
            while ($agent_query->have_posts())
            {
                $agent_query->the_post();

                $this->update_branches_post_meta(get_the_ID());
            }
        }
        $agent_query->reset_postdata();
    }
}

endif;

/**
 * Returns the main instance of PH_Property_Portal to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return PH_Property_Portal
 */
function PHPP() {
    return PH_Property_Portal::instance();
}

PHPP();

if( is_admin() && file_exists(  dirname( __FILE__ ) . '/propertyhive-property-portal-update.php' ) )
{
    include_once( dirname( __FILE__ ) . '/propertyhive-property-portal-update.php' );
}