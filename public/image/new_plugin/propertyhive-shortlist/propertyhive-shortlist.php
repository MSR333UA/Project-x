<?php
/**
 * Plugin Name: Property Hive Shortlist Add On
 * Plugin Uri: http://wp-property-hive.com/addons/property-shortlist/
 * Description: Add On for Property Hive allowing users to save properties to a shortlist
 * Version: 1.0.15
 * Author: PropertyHive
 * Author URI: http://wp-property-hive.com
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'PH_Shortlist' ) ) :

final class PH_Shortlist {

    /**
     * @var string
     */
    public $version = '1.0.15';

    /**
     * @var Property Hive The single instance of the class
     */
    protected static $_instance = null;
    
    /**
     * Main Property Hive Shortlist Instance
     *
     * Ensures only one instance of Property Hive Shortlist is loaded or can be loaded.
     *
     * @static
     * @return Property Hive Shortlist - Main instance
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

        $this->id    = 'shortlist';
        $this->label = __( 'Shortlist', 'propertyhive' );

        // Define constants
        $this->define_constants();

        // Include required files
        $this->includes();

        add_action( 'init', array( $this, 'set_cache_constant' ) );

        add_action( 'admin_notices', array( $this, 'shortlist_error_notices') );

        add_filter( 'propertyhive_single_property_actions', array( $this, 'add_shortlist_action' ) );

        add_action( 'wp_ajax_add_to_shortlist', array( $this, 'add_to_shortlist_ajax_callback' ) );
        add_action( 'wp_ajax_nopriv_add_to_shortlist', array( $this, 'add_to_shortlist_ajax_callback' ) );

        add_action( 'wp_ajax_check_if_shortlisted', array( $this, 'check_if_shortlisted_ajax_callback' ) );
        add_action( 'wp_ajax_nopriv_check_if_shortlisted', array( $this, 'check_if_shortlisted_ajax_callback' ) );

        add_shortcode( 'shortlist_button', array( $this, 'shortlist_button_shortcode' ) );
        add_shortcode( 'shortlisted_properties', array( $this, 'shortlisted_properties_shortcode' ) );

        add_action('propertyhive_user_logged_in', array( $this, 'user_session_available' ), 10, 2);
        add_action('propertyhive_applicant_registered', array( $this, 'user_session_available' ), 10, 2);

        add_filter( 'propertyhive_my_account_pages', array( $this, 'add_shortlisted_properties_my_account_tab' ) );
        add_action( 'propertyhive_my_account_section_shortlisted_properties', array( $this, 'shortlisted_properties_my_account_content' ) );

        add_filter( 'propertyhive_property_query_meta_query', array( $this, 'remove_department_constraint' ), 999, 2 );
        add_filter( 'pre_get_posts', array( $this, 'pre_get_posts' ), 999 );
        add_filter( 'propertyhive_page_title', array( $this, 'propertyhive_page_title' ), 999 );
        add_action( 'init', array( $this, 'remove_search_form' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'load_mass_enquiry_scripts' ) );
        add_action( 'propertyhive_before_search_results_loop', array( $this, 'shortlist_enquiry_button' ), 99 );
    }

    public function set_cache_constant()
    {
        if ( isset($_REQUEST['shortlisted']) && $_REQUEST['shortlisted'] == 1 && !defined('DONOTCACHEPAGE') )
        {
            define('DONOTCACHEPAGE', true);
        }
    }

    public function load_mass_enquiry_scripts()
    {
        if ( !isset($_REQUEST['shortlisted']) || ( isset($_REQUEST['shortlisted']) && $_REQUEST['shortlisted'] != 1 ) )
            return;

        if ( ! is_post_type_archive('property') )
            return;

        $assets_path          = str_replace( array( 'http:', 'https:' ), '', PH()->plugin_url() ) . '/assets/';
        $suffix = '';

        wp_enqueue_script( 'propertyhive_fancybox', $assets_path . 'js/fancybox/jquery.fancybox' . $suffix . '.js', array( 'jquery' ), '3.1.5', true );
        wp_enqueue_style( 'propertyhive_fancybox_css', $assets_path . 'css/jquery.fancybox' . $suffix . '.css' );
    }

    public function shortlist_enquiry_button()
    {
        if ( !isset($_REQUEST['shortlisted']) || ( isset($_REQUEST['shortlisted']) && $_REQUEST['shortlisted'] != 1 ) )
            return;

        $explode_shortlist = $this->get_shortlisted_properties();

        if ( empty($explode_shortlist) )
            return;
?>
<a data-fancybox data-src="#shortlistEnquiry" href="javascript:;" class="button propertyhive-shortlist-enquiry-button"><?php echo __( 'Enquire About All Shortlisted Properties', 'propertyhive' ); ?></a>

<!-- LIGHTBOX FORM -->
    <div id="shortlistEnquiry" style="display:none;">
        
        <h2><?php _e( 'Make Enquiry', 'propertyhive' ); ?> - <?php echo count($explode_shortlist) . ' Propert' . ( ( count($explode_shortlist) == 1 ) ? 'y' : 'ies' ); ?></h2>
        
        <p><?php _e( 'Please complete the form below and a member of staff will be in touch shortly.', 'propertyhive' ); ?></p>
        
        <?php propertyhive_enquiry_form( implode('|', $explode_shortlist) ); ?>
        
    </div>
    <!-- END LIGHTBOX FORM -->
<?php
    }

    public function remove_department_constraint( $meta_query, $query )
    {
        if ( !isset($_REQUEST['shortlisted']) || ( isset($_REQUEST['shortlisted']) && $_REQUEST['shortlisted'] != 1 ) )
            return $meta_query;

        $new_meta_query = array();
        foreach ( $meta_query as $meta_query_part )
        {
            if ( isset($meta_query_part['key']) && $meta_query_part['key'] == '_department' )
            {

            }
            else
            {
                $new_meta_query[] = $meta_query_part;
            }
        }
        return $new_meta_query;
    }

    public function get_shortlisted_properties()
    {
        $explode_shortlist = ( isset($_COOKIE['propertyhive_shortlist']) ) ? explode("|", $_COOKIE['propertyhive_shortlist']) : array();

        if ( is_user_logged_in() )
        {
            $current_user = wp_get_current_user();

            if ( $current_user instanceof WP_User )
            {
                $contact = new PH_Contact( '', $current_user->ID );
                
                $existing = get_post_meta( $contact->id, '_shortlisted_properties', TRUE );
                if ( !is_array($existing) )
                {
                    $existing = array();
                }

                $explode_shortlist = array_merge($existing, $explode_shortlist);
            }
        }

        $explode_shortlist = array_filter($explode_shortlist);

        $explode_shortlist = array_unique($explode_shortlist);

        return $explode_shortlist;
    }

    public function pre_get_posts( $q )
    {
        if ( !isset($_REQUEST['shortlisted']) || ( isset($_REQUEST['shortlisted']) && $_REQUEST['shortlisted'] != 1 ) )
            return;

        // We only want to affect the main query
        if ( ! $q->is_main_query() )
            return;

        if ( ! is_post_type_archive('property') )
            return;

        $explode_shortlist = $this->get_shortlisted_properties();

        if ( empty($explode_shortlist) )
        {
            $q->set( 'post__in', array(1) ); // do this so no results get returned
        }
        else
        {
            $q->set( 'post__in', $explode_shortlist );
        }
    }

    public function propertyhive_page_title( $page_title )
    {
        if ( !isset($_REQUEST['shortlisted']) || ( isset($_REQUEST['shortlisted']) && $_REQUEST['shortlisted'] != 1 ) )
            return $page_title;

        return __( 'Shortlisted Properties', 'propertyhive' );
    }

    public function remove_search_form()
    {
        if ( !isset($_REQUEST['shortlisted']) || ( isset($_REQUEST['shortlisted']) && $_REQUEST['shortlisted'] != 1 ) )
            return;

        remove_action( 'propertyhive_before_search_results_loop', 'propertyhive_search_form', 10 );
    }

    public function add_shortlisted_properties_my_account_tab( $pages )
    {
        $pages['shortlisted_properties']  = array(
            'name' => __( 'Shortlisted Properties', 'propertyhive' )
        );

        return $pages;
    }

    public function shortlisted_properties_my_account_content()
    {
        $existing = array();
        
        $current_user = wp_get_current_user();

        if ( $current_user instanceof WP_User )
        {
            $contact = new PH_Contact( '', $current_user->ID );

            $existing = apply_filters('propertyhive_shortlist_my_account_shortlisted_properties', get_post_meta( $contact->id, '_shortlisted_properties', TRUE ));
            if ( !is_array($existing) )
            {
                $existing = array();
            }
        }
?>
<div class="propertyhive-shortlisted-properties">

    <?php
        if ( !empty($existing) )
        {
            echo '
            <table class="viewings-table upcoming-viewings-table" width="100%">
                <tr>
                    <th>&nbsp;</th>
                    <th>' . __( 'Address', 'propertyhive' ) . '</th>
                    <th>' . __( 'Price', 'propertyhive' ) . '</th>
                    <th>' . __( 'Status', 'propertyhive' ) . '</th>
                </tr>
            ';
            foreach ($existing as $existing_property)
            {
                $property = new PH_Property( (int)$existing_property );

                $link_prefix = ( ( $property->on_market == 'yes' ) ? '<a href="' . get_permalink( $property->id ) . '">' : '' );
                $link_suffix = ( ( $property->on_market == 'yes' ) ? '</a>' : '' );

                echo '<tr>
                    <td>' . ( ( $property->get_main_photo_src() != '' ) ? $link_prefix . '<img src="' . $property->get_main_photo_src() . '" width="75" alt="' . get_the_title( $property->id ) . '">' : '' ) . $link_suffix . '</td>
                    <td>' . $link_prefix . get_the_title( $property->id ) . $link_suffix . '</td>
                    <td>' . $property->get_formatted_price() . '</td>
                    <td>' . $property->availability . '<br>' . ( ( $property->on_market == 'yes' ) ? 'On Market' : '<span style="color:red">Not On Market</span>' ) . '</td>
                </tr>';
            }
            echo '</table>';
        }
        else
        {
            '<p class="propertyhive-info">' . _e( 'No shortlisted properties', 'propertyhive' ) . '</p>';
        }
    ?>

</div>
<?php
    }

    /**
     * Define PH Shortlist Constants
     */
    private function define_constants() 
    {
        define( 'PH_SHORTLIST_PLUGIN_FILE', __FILE__ );
        define( 'PH_SHORTLIST_VERSION', $this->version );
    }

    private function includes()
    {
        //include_once( dirname( __FILE__ ) . "/includes/class-ph-shortlist-install.php" );
    }

    /**
     * Output error message if core Property Hive plugin isn't active
     */
    public function shortlist_error_notices() 
    {
        if (!is_plugin_active('propertyhive/propertyhive.php'))
        {
            $message = __( "The Property Hive plugin must be installed and activated before you can use the Property Hive Shortlist add-on", 'propertyhive' );
            echo"<div class=\"error\"> <p>$message</p></div>";
        }
    }

    public function user_session_available( $contact_post_id, $user_id )
    {
        // A user has logged in or registered
        // Check cookie for existing shortlisted properties and merge with saved properties already assigned to the account
        $explode_shortlist = ( isset($_COOKIE['propertyhive_shortlist']) ) ? explode("|", $_COOKIE['propertyhive_shortlist']) : array();

        if ( empty($explode_shortlist) )
        {
            return false;
        }

        // get list of existsing saved properties
        $existing = get_post_meta( $contact_post_id, '_shortlisted_properties', TRUE );
        if ( !is_array($existing) )
        {
            $existing = array();
        }

        $new = array_merge($existing, $explode_shortlist);

        $new = array_filter($new);

        $new = array_unique($new);

        if ( !empty($new) )
        {
            update_post_meta( $contact_post_id, '_shortlisted_properties', $new );
        }
    }

    public function shortlisted_properties_shortcode( $atts )
    {
        $atts = shortcode_atts( array(
            'columns'           => '1',
            'no_results_output' => 'Shortlisted properties will appear here',
        ), $atts );

        $explode_shortlist = ( isset($_COOKIE['propertyhive_shortlist']) ) ? explode("|", $_COOKIE['propertyhive_shortlist']) : array();

        if ( is_user_logged_in() )
        {
            $current_user = wp_get_current_user();

            if ( $current_user instanceof WP_User )
            {
                $contact = new PH_Contact( '', $current_user->ID );
                
                $existing = get_post_meta( $contact->id, '_shortlisted_properties', TRUE );
                if ( !is_array($existing) )
                {
                    $existing = array();
                }

                $explode_shortlist = array_merge($existing, $explode_shortlist);
            }
        }

        $explode_shortlist = array_filter($explode_shortlist);

        $explode_shortlist = array_unique($explode_shortlist);

        if ( empty($explode_shortlist) )
        {
            return $atts['no_results_output'];
        }

        $args = array(
            'post_type'           => 'property',
            'post_status'         => 'publish',
            'post__in'            => $explode_shortlist,
            'nopaging'            => true,
            'meta_query'          => array(
                array(
                    'key' => '_on_market',
                    'value' => 'yes',
                )
            )
        );

        ob_start();

        $properties = new WP_Query( $args );

        if ( $properties->have_posts() ) : ?>

            <?php propertyhive_property_loop_start(); ?>

                <?php while ( $properties->have_posts() ) : $properties->the_post(); ?>

                    <?php ph_get_template_part( 'content', 'property' ); ?>

                <?php endwhile; // end of the loop. ?>

            <?php propertyhive_property_loop_end(); ?>

        <?php else: ?>

            <?php echo $atts['no_results_output']; ?>

        <?php endif;

        wp_reset_postdata();
    
        return '<div class="propertyhive propertyhive-shortlist columns-' . $atts['columns'] . '">' . ob_get_clean() . '</div>';
    }

    public function add_to_shortlist_ajax_callback()
    {
        header( 'Content-Type: application/json; charset=utf-8' );

        $property_id = $_POST['property_id'];

        $explode_shortlist = ( isset($_COOKIE['propertyhive_shortlist']) ) ? explode("|", $_COOKIE['propertyhive_shortlist']) : array();

        $existing = array();
        if ( is_user_logged_in() )
        {
            // get contact ID from userID
            $current_user = wp_get_current_user();

            if ( $current_user instanceof WP_User )
            {
                $contact = new PH_Contact( '', $current_user->ID );

                $existing = get_post_meta( $contact->id, '_shortlisted_properties', TRUE );
                if ( !is_array($existing) )
                {
                    $existing = array();
                }
            }
        }

        $explode_shortlist = array_merge($explode_shortlist, $existing);

        $explode_shortlist = array_filter($explode_shortlist);

        $explode_shortlist = array_unique($explode_shortlist);

        $comment = '';

        if ( ($key = array_search($property_id, $explode_shortlist)) !== FALSE ) 
        {
            unset($explode_shortlist[$key]);
            ph_setcookie( 'propertyhive_shortlist', implode("|", $explode_shortlist), time() + (7 * DAY_IN_SECONDS), is_ssl() );
            echo json_encode( array('success' => true, 'action' => 'removed', 'shortlist_count' => count($explode_shortlist) ) );
            $comment = 'Removed <a href="' . get_edit_post_link($property_id) . '">' . get_the_title($property_id) . '</a> from shortlist';
        }
        else
        {
            $explode_shortlist[] = $property_id;
            ph_setcookie( 'propertyhive_shortlist', implode("|", $explode_shortlist), time() + (7 * DAY_IN_SECONDS), is_ssl() );
            echo json_encode( array('success' => true, 'action' => 'added', 'shortlist_count' => count($explode_shortlist) ) );
            $comment = 'Added <a href="' . get_edit_post_link($property_id) . '">' . get_the_title($property_id) . '</a> to shortlist';
        }

        if ( is_user_logged_in() )
        {
            // get contact ID from userID
            $current_user = wp_get_current_user();

            if ( $current_user instanceof WP_User )
            {
                $contact = new PH_Contact( '', $current_user->ID );

                $explode_shortlist = array_values($explode_shortlist);

                update_post_meta( $contact->id, '_shortlisted_properties', $explode_shortlist );

                // Add note/comment to viewing
                $comment = array(
                    'note_type' => 'note',
                    'note' => $comment,
                );

                $data = array(
                    'comment_post_ID'      => $contact->id,
                    'comment_author'       => 'Property Hive',
                    'comment_author_email' => 'propertyhive@noreply.com',
                    'comment_author_url'   => '',
                    'comment_date'         => date("Y-m-d H:i:s"),
                    'comment_content'      => serialize($comment),
                    'comment_approved'     => 1,
                    'comment_type'         => 'propertyhive_note',
                );
                $comment_id = wp_insert_comment( $data );
            }
        }

        wp_die();
    }

    public function check_if_shortlisted_ajax_callback()
    {
        header( 'Content-Type: application/json; charset=utf-8' );

        if ( !is_user_logged_in() )
        {
            $explode_shortlist = ( isset($_COOKIE['propertyhive_shortlist']) ) ? explode("|", $_COOKIE['propertyhive_shortlist']) : array();

            echo json_encode( array('success' => true, 'on_shortlist' => in_array($_POST['property_id'], $explode_shortlist) ) );
        }
        else
        {
            echo json_encode( array('success' => false ) );
        }

        wp_die();
    }

    public function shortlist_button_shortcode( $atts )
    {
        global $post, $property;

        $atts = shortcode_atts( array(
            'class'           => 'button',
        ), $atts );

        $assets_path = str_replace( array( 'http:', 'https:' ), '', untrailingslashit( plugins_url( '/', __FILE__ ) ) ) . '/assets/';

        wp_register_script( 
            'ph-shortlist', 
            $assets_path . 'js/ph-shortlist.js', 
            array('jquery'), 
            PH_SHORTLIST_VERSION,
            true
        );

        wp_enqueue_script('ph-shortlist');

        $add_link_text = __( 'Add To Shortlist', 'propertyhive' );
        $remove_link_text = __( 'Remove From Shortlist', 'propertyhive' );
        $loading_text = __( 'Loading', 'propertyhive' );

        $params = array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'add_link_text' => $add_link_text,
            'remove_link_text' => $remove_link_text,
            'loading_text' => $loading_text,
        );
        wp_localize_script( 'ph-shortlist', 'propertyhive_shortlist', $params );

        $link_text = $add_link_text;

        $explode_shortlist = ( isset($_COOKIE['propertyhive_shortlist']) ) ? explode("|", $_COOKIE['propertyhive_shortlist']) : array();

        if ( ($key = array_search($post->ID, $explode_shortlist)) !== FALSE ) 
        {
            $link_text = $remove_link_text;
        }

        return '<a href="" data-add-to-shortlist="' . $post->ID . '" class="' . $atts['class'] . '" rel="nofollow">' . $link_text . '</a>';
    }

    public function add_shortlist_action( $actions = array() )
    {
        global $post, $property;

        $assets_path = str_replace( array( 'http:', 'https:' ), '', untrailingslashit( plugins_url( '/', __FILE__ ) ) ) . '/assets/';

        wp_register_script( 
            'ph-shortlist', 
            $assets_path . 'js/ph-shortlist.js', 
            array('jquery'), 
            PH_SHORTLIST_VERSION,
            true
        );

        wp_enqueue_script('ph-shortlist');

        $add_link_text = __( 'Add To Shortlist', 'propertyhive' );
        $remove_link_text = __( 'Remove From Shortlist', 'propertyhive' );
        $loading_text = __( 'Loading', 'propertyhive' );

        $params = array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'add_link_text' => $add_link_text,
            'remove_link_text' => $remove_link_text,
            'loading_text' => $loading_text,
        );
        wp_localize_script( 'ph-shortlist', 'propertyhive_shortlist', $params );

        $link_text = $add_link_text;

        $explode_shortlist = ( isset($_COOKIE['propertyhive_shortlist']) ) ? explode("|", $_COOKIE['propertyhive_shortlist']) : array();

        if ( ($key = array_search($post->ID, $explode_shortlist)) !== FALSE ) 
        {
            $link_text = $remove_link_text;
        }

        $actions[] = array(
            'href' =>  '',
            'label' => $link_text,
            'class' => 'action-shortlist',
            'attributes' => array(
                'rel' => 'nofollow',
                'data-add-to-shortlist' => $post->ID,
            )
        );

        return $actions;
    }
}

endif;

/**
 * Returns the main instance of PH_Shortlist to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return PH_Shortlist
 */
function PHSL() {
    return PH_Shortlist::instance();
}

$PHSL = PHSL();

if( is_admin() && file_exists(  dirname( __FILE__ ) . '/propertyhive-shortlist-update.php' ) )
{
    include_once( dirname( __FILE__ ) . '/propertyhive-shortlist-update.php' );
}