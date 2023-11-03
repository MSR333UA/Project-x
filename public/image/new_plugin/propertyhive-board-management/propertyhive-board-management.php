<?php
/**
 * Plugin Name: Property Hive Board Management Add On
 * Plugin Uri: http://wp-property-hive.com/addons/board-management/
 * Description: Add On for Property Hive allowing you manage property boards
 * Version: 1.0.3
 * Author: PropertyHive
 * Author URI: http://wp-property-hive.com
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'PH_Board_Management' ) ) :

final class PH_Board_Management {

    /**
     * @var string
     */
    public $version = '1.0.3';

    /**
     * @var PropertyHive The single instance of the class
     */
    protected static $_instance = null;
    
    /**
     * Main PropertyHive Appraisals Instance
     *
     * Ensures only one instance of PropertyHive Board Management is loaded or can be loaded.
     *
     * @static
     * @return PropertyHive Board Management - Main instance
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

        $this->id    = 'boardmanagement';
        $this->label = __( 'Board Management', 'propertyhive' );

        // Define constants
        $this->define_constants();

        // Include required files
        $this->includes(); 

        add_action( 'init', array( $this, 'register_taxonomies' ), 5 );

        add_action( 'admin_notices', array( $this, 'board_management_error_notices') );

        add_filter( 'propertyhive_settings_tabs_array', array( $this, 'add_settings_tab' ), 18 );
        add_action( 'propertyhive_settings_' . $this->id, array( $this, 'output' ) );
        add_action( 'propertyhive_settings_save_' . $this->id, array( $this, 'save' ) );

        add_action( 'propertyhive_admin_field_board_statuses', array( $this, 'board_statuses_settings' ) );

        add_filter( 'propertyhive_tabs', array( $this, 'ph_board_management_property_tab') );

        add_action( 'add_meta_boxes', 'ph_board_management_add_meta_boxes', 30 );
        add_action( 'propertyhive_process_property_meta', 'PH_Meta_Box_Property_Board_Contractor::save', 10, 2 );
        add_action( 'propertyhive_process_property_meta', 'PH_Meta_Box_Property_Board_Details::save', 50, 2 );
    }

    public function ph_board_management_property_tab($tabs)
    {
        $tabs['tab_board'] = array(
            'name' => __( 'Board', 'propertyhive' ),
            'metabox_ids' => array('propertyhive-property-board-contractor', 'propertyhive-property-board-details'),
            'post_type' => 'property'
        );

        return $tabs;
    }

    /**
     * Define PH Blmexport Constants
     */
    private function define_constants() 
    {
        define( 'PH_BOARD_MANAGEMENT_PLUGIN_FILE', __FILE__ );
        define( 'PH_BOARD_MANAGEMENT_VERSION', $this->version );
    }

    private function includes()
    {
        include( dirname( __FILE__ ) . "/includes/class-ph-board-management-install.php" );
        include( dirname( __FILE__ ) . "/includes/class-ph-ajax.php" );
        include( dirname( __FILE__ ) . "/includes/meta-boxes.php" );
        include( dirname( __FILE__ ) . "/includes/meta-boxes/class-ph-meta-box-property-board-contractor.php" );
        include( dirname( __FILE__ ) . "/includes/meta-boxes/class-ph-meta-box-property-board-details.php" );
    }

    public function register_taxonomies()
    {
        register_taxonomy( 'board_status',
            'property',
            array(
                'hierarchical'          => true,
                'show_ui'               => false,
                'show_in_nav_menus'     => false,
                'query_var'             => is_admin(),
                'rewrite'               => false,
                'public'                => true
            )
        );
    }

    /**
     * Output error message if core PropertyHive plugin isn't active
     */
    public function board_management_error_notices() 
    {
        if (!is_plugin_active('propertyhive/propertyhive.php'))
        {
            $message = "The PropertyHive plugin must be installed and activated before you can use the PropertyHive BLM Export add-on";
            echo"<div class=\"error\"> <p>$message</p></div>";
        }
        else
        {

        }
    }

    /**
     * Add a new settings tab to the PropertyHive settings tabs array.
     *
     * @param array $settings_tabs Array of PropertyHive setting tabs & their labels
     * @return array $settings_tabs Array of PropertyHive setting tabs & their labels
     */
    public function add_settings_tab( $settings_tabs ) {
        $settings_tabs['boardmanagement'] = __( 'Board Management', 'propertyhive' );
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
                propertyhive_admin_fields( self::get_board_status_settings() );
                break;
            }
            case "editstatus":
            {
                propertyhive_admin_fields( self::get_board_status_settings() );
                break;
            }
            default:
            {
                propertyhive_admin_fields( self::get_board_management_settings() );
            }
        }
    }

    /**
     * Get all the main settings for this plugin
     *
     * @return array Array of settings
     */
    public function get_board_management_settings() {

        global $current_section, $post;

        $board_contractors = array( '' => '' );

        $args = array(
            'post_type' => 'contact',
            'nopaging' => true,
            'meta_query' => array(
                array(
                    'key' => '_contact_types',
                    'value' => 'thirdparty',
                    'compare' => 'LIKE'
                ),
                array(
                    'relation' => 'OR',
                    array(
                        'key' => '_third_party_categories',
                        'value' => ':"3";', // 3 = Board contractor
                        'compare' => 'LIKE'
                    ),
                    array(
                        'key' => '_third_party_categories',
                        'value' => ':3;', // 3 = Board contractor
                        'compare' => 'LIKE'
                    )
                )
            )
        );
        $contact_query = new WP_Query( $args );

        if ( $contact_query->have_posts() )
        {
            while ( $contact_query->have_posts() )
            {
                $contact_query->the_post();

                $board_contractors[get_the_ID()] = get_the_title();
            }
        }
        wp_reset_postdata();
        

        $settings = array();

        $settings[] = array( 'title' => __( 'Board Management Settings', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'board_management_settings' );

        $settings[] = array(
            'type'      => 'board_statuses',
        );

        $settings[] = array(
            'title'   => __( 'Default Board Contractor', 'propertyhive' ),
            'id'      => 'propertyhive_default_board_contractor',
            'type'    => 'select',
            'css'     => '',
            'options' => $board_contractors,
            'desc'    => __( 'Add board contractors by navigating to \'Third Party Contacts\', adding a new one, and setting the \'Category\'', 'propertyhive' )
        );

        $settings[] = array( 'type' => 'sectionend', 'id' => 'board_management_settings');

        return $settings;

    }

    /**
     * Output list of portals
     *
     * @access public
     * @return void
     */
    public function board_statuses_settings() {
        global $wpdb, $post;
        
        ?>
        <tr valign="top">
            <th scope="row" class="titledesc">
                &nbsp;
            </th>
            <td class="forminp forminp-button">
                <a href="<?php echo admin_url( 'admin.php?page=ph-settings&tab=boardmanagement&section=addstatus' ); ?>" class="button alignright"><?php echo __( 'Add New Board Status', 'propertyhive' ); ?></a>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" class="titledesc"><?php _e( 'Board Statuses', 'propertyhive' ) ?></th>
            <td class="forminp">
                <table class="ph_board_statues widefat" cellspacing="0">
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
                            $terms = get_terms( 'board_status', $args );
                            if ( ! empty( $terms ) && ! is_wp_error( $terms ) )
                            {
                                foreach ( $terms as $term )
                                {
                                    echo '<tr>';
                                        echo '<td class="status">' . $term->name . '</td>';
                                        echo '<td class="settings">
                                            <a class="button" href="' . admin_url( 'admin.php?page=ph-settings&tab=boardmanagement&section=editstatus&id=' . $term->term_id ) . '">' . __( 'Edit', 'propertyhive' ) . '</a>
                                        </td>';
                                    echo '</tr>';
                                }
                            }
                            else
                            {
                                echo '<tr>';
                                    echo '<td align="center" colspan=2"">' . __( 'No board statuses exist', 'propertyhive' ) . '</td>';
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
                <a href="<?php echo admin_url( 'admin.php?page=ph-settings&tab=boardmanagement&section=addstatus' ); ?>" class="button alignright"><?php echo __( 'Add New Board Status', 'propertyhive' ); ?></a>
            </td>
        </tr>
        <?php
    }

    /**
     * Get add/edit portal settings
     *
     * @return array Array of settings
     */
    public function get_board_status_settings() {

        global $current_section, $post;

        $current_id = ( !isset( $_REQUEST['id'] ) ) ? '' : sanitize_title( $_REQUEST['id'] );

        $status_name = '';
        if ($current_id != '')
        {
            // We're editing one
            $term = get_term( $current_id, 'board_status' );
            $status_name = $term->name;
        }

        $settings = array(

            array( 'title' => __( ( $current_section == 'addstatus' ? 'Add Board Status' : 'Edit Board Status' ), 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'status_details' ),

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
                wp_insert_term( $_POST['status_name'], 'board_status' );

                PH_Admin_Settings::add_message( __( 'Board status added successfully', 'propertyhive' ) . ' ' . '<a href="' . admin_url( 'admin.php?page=ph-settings&tab=boardmanagement' ) . '">' . __( 'Return to Board Management Options', 'propertyhive' ) . '</a>' );
                    
                break;
            }
            case 'editstatus': 
            {
                $current_id = ( !isset( $_REQUEST['id'] ) ) ? '' : sanitize_title( $_REQUEST['id'] );

                wp_update_term( $current_id, 'board_status', array( 'name' => $_POST['status_name'] ) );

                PH_Admin_Settings::add_message( __( 'Board status updated successfully', 'propertyhive' ) . ' ' . '<a href="' . admin_url( 'admin.php?page=ph-settings&tab=boardmanagement' ) . '">' . __( 'Return to Board Management Options', 'propertyhive' ) . '</a>' );
                
                break;
            }
            default: 
            {
                propertyhive_update_options( self::get_board_management_settings() );
                break;
            }
        }
    }
}

endif;

/**
 * Returns the main instance of PH_Board_Management to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return PH_Board_Management
 */
function PHBM() {
    return PH_Board_Management::instance();
}

PHBM();

if( is_admin() && file_exists(  dirname( __FILE__ ) . '/propertyhive-board-management-update.php' ) )
{
    include_once( dirname( __FILE__ ) . '/propertyhive-board-management-update.php' );
}