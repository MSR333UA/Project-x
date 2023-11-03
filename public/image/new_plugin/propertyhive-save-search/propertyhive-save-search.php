<?php
/**
 * Plugin Name: Property Hive Save Search Add On
 * Plugin Uri: http://wp-property-hive.com/addons/saved-searches/
 * Description: Add On for Property Hive allowing users to save property searches
 * Version: 1.0.1
 * Author: PropertyHive
 * Author URI: http://wp-property-hive.com
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'PH_Save_Search' ) ) :

final class PH_Save_Search {

    /**
     * @var string
     */
    public $version = '1.0.1';

    /**
     * @var Property Hive The single instance of the class
     */
    protected static $_instance = null;
    
    /**
     * Main Property Hive Save Search Instance
     *
     * Ensures only one instance of Property Hive Save Search is loaded or can be loaded.
     *
     * @static
     * @return Property Hive Save Search - Main instance
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

        $this->id    = 'save_search';
        $this->label = __( 'Save Search', 'propertyhive' );

        // Define constants
        $this->define_constants();

        add_action( 'admin_notices', array( $this, 'save_search_error_notices') );

        add_action( 'propertyhive_before_search_results_loop', array( $this, 'save_search_button' ), 99 );

        add_action( 'wp_ajax_save_search', array( $this, 'save_search_ajax_callback' ) );

        add_action( 'wp_ajax_remove_saved_search', array( $this, 'remove_saved_search_ajax_callback' ) );

        add_action( 'wp_ajax_update_search_send_emails', array( $this, 'update_search_send_emails_ajax_callback' ) );

        add_shortcode( 'save_search_button', array( $this, 'save_search_button' ) );

        add_action( 'wp_enqueue_scripts', array( $this, 'load_save_search_scripts' ) );

        add_filter( 'propertyhive_account_params', array( $this, 'set_login_redirect' ) );

        add_filter( 'propertyhive_my_account_pages', array( $this, 'add_saved_searches_tab_to_my_account' ) );

        add_action( 'propertyhive_my_account_section_saved_searches', array( $this, 'propertyhive_my_account_saved_searches' ), 10 );
    }

    public function add_saved_searches_tab_to_my_account( $pages )
    {
        $user_id = get_current_user_id();

        $contact = new PH_Contact( '', $user_id );

        $contact_types = $contact->contact_types;
        if ( !is_array($contact_types) )
        {
            $contact_types = array($contact_types);
        }

        if ( in_array('applicant', $contact_types) )
        {
            $pages['saved_searches'] = array(
                'name' => __( 'Saved Searches', 'propertyhive' ),
            );
        }

        return $pages;
    }

    public function propertyhive_my_account_saved_searches()
    {
        $assets_path = str_replace( array( 'http:', 'https:' ), '', untrailingslashit( plugins_url( '/', __FILE__ ) ) ) . '/assets/';

        wp_register_script( 
            'ph-save-search', 
            $assets_path . 'js/ph-save-search.js', 
            array('jquery'), 
            PH_SAVE_SEARCH_VERSION,
            true
        );

        wp_enqueue_script('ph-save-search');

        $remove_link_text = __( 'Remove Saved Search', 'propertyhive' );
        $removing_text = __( 'Removing...', 'propertyhive' );

        $params = array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'remove_link_text' => $remove_link_text,
            'removing_text' => $removing_text,
        );
        wp_localize_script( 'ph-save-search', 'propertyhive_save_search', $params );
        
        $atts = array(
            'columns'           => '1',
            'no_results_output' => 'Saved searches will appear here',
        );

        if ( $this->is_user_valid_contact() )
        {
            $saved_searches = array();
            
            $current_user = wp_get_current_user();
            $contact = new PH_Contact( '', $current_user->ID );

            $num_existing_profiles = (int)get_post_meta( $contact->id, '_applicant_profiles', TRUE );

            for ( $i = 0; $i < $num_existing_profiles; $i++)
            {
                $applicant_profile = get_post_meta( $contact->id, '_applicant_profile_' . $i, TRUE );
                if ( is_array($applicant_profile) && isset($applicant_profile['saved_search']) )
                {
                    $saved_searches[$i] = $applicant_profile;
                }
            }
            
            if ( count($saved_searches) > 0 )
            {
                $form_controls = ph_get_search_form_fields();
                $form_controls = apply_filters( 'propertyhive_search_form_fields_default', $form_controls );
                $form_controls = apply_filters( 'propertyhive_search_form_fields', $form_controls );

                foreach ($saved_searches as $key => $saved_search)
                {
                    $search_parameters = $this->process_search_url_to_array($saved_search['search_string']);
                    echo '<div>';
                        $search_name = isset( $saved_search['relationship_name'] ) ? $saved_search['relationship_name'] : 'Unnamed Search' ;
                        echo '<h4>' . $search_name . '</h4>';
                        echo '<span style="color: #869099;">';
                        foreach ($search_parameters as $search_field => $search_value)
                        {
                            if ( isset($form_controls[$search_field]) )
                            {
                                $label = isset( $form_controls[$search_field]['label'] ) ? $form_controls[$search_field]['label'] : ucwords(str_replace("_", " ", $search_field));

                                $value = is_array($search_value) ? implode(', ', $search_value) : $search_value;
                                if ( isset( $form_controls[$search_field]['options'] ) )
                                {
                                    if ( !is_array($search_value) )
                                    {
                                        $search_value = array($search_value);
                                    }
                                    $values_array = array();
                                    foreach ($search_value as $single_search_value)
                                    {
                                        if ( isset($form_controls[$search_field]['options'][$single_search_value]) )
                                        {
                                            $values_array[] = $form_controls[$search_field]['options'][$single_search_value];
                                        }
                                    }
                                    $value = implode(', ', $values_array);
                                }
                                else
                                {
                                    if ( $form_controls[$search_field]['type'] == 'text' )
                                    {
                                        $value = $search_value;
                                    }
                                    else
                                    {
                                        if ( !is_array($search_value) )
                                        {
                                            $search_value = array($search_value);
                                        }
                                        $values_array = array();
                                        foreach ($search_value as $single_search_value)
                                        {
                                            if ( taxonomy_exists($search_field) )
                                            {
                                                $term = get_term( $single_search_value, $search_field );
                                                if ( !empty( $term ) && !is_wp_error( $term ) )
                                                {
                                                    $values_array[] = $term->name; 
                                                }
                                            }
                                        }
                                        $value = implode(', ', $values_array);
                                    }
                                    
                                }
                                echo $label . ($label != '' ? ': ' : '') . $value . '<br>';
                            }
                            else
                            {
                                // TODO: Decide what to do with these
                                if ( is_array($search_value) )
                                {
                                    $search_value = implode(', ', $search_value);
                                }
                                echo ucwords(str_replace("_", " ", $search_field)) . ': ' . $search_value . '<br>';
                            }
                        }

                        echo '</span><br>';

                        $field = array(
                            'class' => 'update_search_send_emails',
                            'type' => 'checkbox',
                            'label' => __( 'Receive Email Alerts For Properties Matching This Search', 'propertyhive' ),
                            'show_label' => true,
                            'value' => $key,
                            'checked' => ( isset( $saved_search['send_matching_properties'] ) && $saved_search['send_matching_properties'] == 'yes'),
                        );
                        ph_form_field( 'update_search_send_emails', $field );
                        
                        echo '<a href="' . get_post_type_archive_link( 'property' ) . '?' . $saved_search['search_string'] . '" class="button" rel="nofollow">' . __( 'View Properties', 'propertyhive' ) . '</a>';
                        echo '&nbsp;<a class="button" id="remove_saved_search" profile_to_remove="' . $key . '" rel="nofollow">' . $remove_link_text . '</a>';
                    echo '</div><br><hr>';
                }
            }
            else
            {
                echo "You haven't saved any searches yet";
            }
        }
        else
        {
            $assets_path = str_replace( array( 'http:', 'https:' ), '', PH()->plugin_url() ) . '/assets/';
            wp_enqueue_script( 'propertyhive_account', $assets_path . 'js/frontend/account.js', array( 'jquery' ), PH_VERSION, true );

            $current_url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

            $params = array(
                'redirect_url' => $current_url,
            );
            wp_localize_script( 'propertyhive_account', 'propertyhive_account_params', $params );

            echo "Please log in to be able to save searches.<br>";
            echo do_shortcode('[propertyhive_login_form]');
        }
    }

    public function load_save_search_scripts()
    {
        $assets_path = str_replace( array( 'http:', 'https:' ), '', PH()->plugin_url() ) . '/assets/';
        $suffix = '';

        wp_enqueue_script( 'propertyhive_fancybox', $assets_path . 'js/fancybox/jquery.fancybox' . $suffix . '.js', array( 'jquery' ), '3.1.5', true );
        wp_enqueue_style( 'propertyhive_fancybox_css', $assets_path . 'css/jquery.fancybox' . $suffix . '.css' );
    }

    public function set_login_redirect( $params )
    {
        if ( is_post_type_archive('property') && !$this->is_user_valid_contact() )
        {
            $params['redirect_url'] = home_url($_SERVER['REQUEST_URI']) . '?' . $_SERVER['QUERY_STRING'] . '#savesearch';
        }

        return $params;
    }

    public function save_search_button()
    {
        global $wp;

        $assets_path = str_replace( array( 'http:', 'https:' ), '', untrailingslashit( plugins_url( '/', __FILE__ ) ) ) . '/assets/';

        wp_register_script( 
            'ph-save-search', 
            $assets_path . 'js/ph-save-search.js', 
            array('jquery'), 
            PH_SAVE_SEARCH_VERSION,
            true
        );

        wp_enqueue_script('ph-save-search');

        $save_link_text = __( 'Save Search', 'propertyhive' );
        $loading_text = __( 'Saving...', 'propertyhive' );

        $params = array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'save_link_text' => $save_link_text,
            'loading_text' => $loading_text
        );
        wp_localize_script( 'ph-save-search', 'propertyhive_save_search', $params );
        ?>

        <a data-fancybox data-src="#save_search_popup" href="javascript:;" class="button propertyhive-save-search-button"><?php echo __( 'Save Search', 'propertyhive' ); ?></a>
        <!-- LIGHTBOX FORM -->
        <div id="save_search_popup" style="display:none;">
        <?php
        if ( $this->is_user_valid_contact() )
        {
        ?>
            <h2><?php _e( 'Save Search', 'propertyhive' ); ?></h2>

            <div id="save_search_success" style="display:none;" class="alert alert-success alert-box success">
                <?php _e( 'Search successfully saved to your account', 'propertyhive' ); ?>
            </div>

            <div id="save_search_form">
                <p><?php echo 'Save this search to be able to quickly retrieve it later'; ?></p>

                <?php
                    $field = array(
                        'type' => 'text',
                        'label' => __( 'Search Name ', 'propertyhive' ),
                        'show_label' => true,
                    );
                    ph_form_field( 'saved_search_name', $field );

                    $field = array(
                        'type' => 'checkbox',
                        'label' => __( 'Receive Email Alerts For Properties Matching This Search', 'propertyhive' ),
                        'show_label' => true,
                    );
                    ph_form_field( 'saved_search_email_alerts', $field );
                ?>
                <a id="save_search_button" class="button propertyhive-save-search-button"><?php echo __( 'Save Search', 'propertyhive' ); ?></a>
            </div>
        <?php
        }
        else
        {
            echo '<p>Please log in to be able to save searches</p>';
            echo do_shortcode('[propertyhive_login_form]');
        }
        ?>
        </div>
        <!-- END LIGHTBOX FORM -->
        <?php
    }

    /**
     * Define PH Save Search Constants
     */
    private function define_constants() 
    {
        define( 'PH_SAVE_SEARCH_VERSION', $this->version );
    }

    /**
     * Output error message if core Property Hive plugin isn't active
     */
    public function save_search_error_notices() 
    {
        if (!is_plugin_active('propertyhive/propertyhive.php'))
        {
            $message = __( "The Property Hive plugin must be installed and activated before you can use the Property Hive Save Search add-on", 'propertyhive' );
            echo"<div class=\"error\"> <p>$message</p></div>";
        }
    }

    public function save_search_ajax_callback()
    {
        header( 'Content-Type: application/json; charset=utf-8' );

        if ($_POST['search_parameters'] == '')
        {
            $_POST['search_parameters'] = 'department=' . get_option( 'propertyhive_primary_department' );
        }

        $search_name = $_POST['saved_search_name'] == '' ? 'Unnamed Search' : $_POST['saved_search_name'];

        $search_parameters = $this->process_search_url_to_array($_POST['search_parameters']);

        $applicant_array = array();

        if ( isset($search_parameters['maximum_price']) || isset($search_parameters['minimum_price']) )
        {
            if ( isset($search_parameters['maximum_price']) )
            {
                $applicant_array['max_price'] = $search_parameters['maximum_price'];
                $applicant_array['max_price_actual'] = $search_parameters['maximum_price'];
                $applicant_array['match_price_range_higher'] = $search_parameters['maximum_price'];
                $applicant_array['match_price_range_higher_actual'] = $search_parameters['maximum_price'];

                if ( isset($search_parameters['minimum_price']) )
                {
                    $applicant_array['match_price_range_lower'] = isset($search_parameters['minimum_price']) ? $search_parameters['minimum_price'] : '0';
                    $applicant_array['match_price_range_lower_actual'] = isset($search_parameters['minimum_price']) ? $search_parameters['minimum_price'] : '0';
                }
            }
            else
            {
                $applicant_array['max_price'] = $search_parameters['minimum_price'];
                $applicant_array['max_price_actual'] = $search_parameters['minimum_price'];
                $applicant_array['match_price_range_higher'] = '100000000';
                $applicant_array['match_price_range_higher_actual'] = '100000000';
                $applicant_array['match_price_range_lower'] = $search_parameters['minimum_price'];
                $applicant_array['match_price_range_lower_actual'] = $search_parameters['minimum_price'];
            }
            unset($search_parameters['maximum_price']);
            unset($search_parameters['minimum_price']);
        }

        if ( isset($search_parameters['minimum_rent']) && !isset($search_parameters['maximum_rent']) )
        {
            $applicant_array['max_rent'] = '100000';
        }

        foreach ($search_parameters as $search_field => $search_value)
        {
            switch ( $search_field )
            {
                case 'department':
                    $applicant_array[$search_field] = $search_value;
                    break;
                case 'maximum_rent':
                    $applicant_array['max_rent'] = $search_value;
                    $applicant_array['max_rent_actual'] = $search_value;
                    $applicant_array['rent_frequency'] = 'pcm';
                    break;
                case 'minimum_bedrooms':
                    $applicant_array['min_beds'] = $search_value;
                    break;
                case 'minimum_floor_area':
                    $applicant_array['min_floor_area'] = $search_value;
                    $applicant_array['min_floor_area_actual'] = $search_value;
                    $applicant_profile['floor_area_units'] = 'sqft';
                    break;
                case 'maximum_floor_area':
                    $applicant_array['max_floor_area'] = $search_value;
                    $applicant_array['max_floor_area_actual'] = $search_value;
                    $applicant_profile['floor_area_units'] = 'sqft';
                    break;
                case 'property_type':
                    $applicant_array['property_types'] = $search_value;
                    break;
                case 'address_keyword':
                    if ( get_option('propertyhive_applicant_locations_type') == 'text' )
                    {
                        $applicant_array['location_text'] = $search_value;
                    }
                    else
                    {
                        if ( isset($applicant_array['notes']) )
                        {
                            $applicant_array['notes'] .= ', Location: ' . $search_value;
                        }
                        else
                        {
                            $applicant_array['notes'] = 'Location: ' . $search_value;
                        }
                    }
                    break;
                case 'location':
                    if ( get_option('propertyhive_applicant_locations_type') == 'text' )
                    {
                        $location_term = get_term( $search_value, 'location' );
                        if ( !empty($location_term) )
                        {
                            $applicant_array['location_text'] = $location_term->name;
                        }
                    }
                    else
                    {
                        $applicant_array['locations'] = array($search_value);
                    }
                    break;
                case 'radius':
                    if ( get_option('propertyhive_applicant_locations_type') == 'text' && class_exists('PH_Radial_Search') )
                    {
                        $applicant_array['location_radius'] = $search_value;
                    }
                    else
                    {
                        if ( isset($applicant_array['notes']) )
                        {
                            $applicant_array['notes'] .= ', Radius: ' . $search_value;
                        }
                        else
                        {
                            $applicant_array['notes'] = 'Radius: ' . $search_value;
                        }
                    }
                    break;
                default:
                    $formatted_search_field = ucwords(str_replace("_", " ", $search_field));
                    if ( isset($applicant_array['notes']) )
                    {
                        $applicant_array['notes'] .= ', ' . $formatted_search_field . ': ' . $search_value;
                    }
                    else
                    {
                        $applicant_array['notes'] = $formatted_search_field . ': ' . $search_value;
                    }
                    break;
            }
        }
        if ( $this->is_user_valid_contact() )
        {
            $current_user = wp_get_current_user();
            $contact = new PH_Contact( '', $current_user->ID );

            $num_existing_profiles = (int)get_post_meta( (int)$contact->id, '_applicant_profiles', TRUE );

            $applicant_array['search_string'] = $_POST['search_parameters'];
            $applicant_array['saved_search'] = true;
            $applicant_array['relationship_name'] = $search_name;
            $applicant_array['send_matching_properties'] = ( $_POST['saved_search_email_alerts'] == 'true' ? 'yes' : '' );

            add_post_meta( $contact->id, '_applicant_profile_' . $num_existing_profiles, $applicant_array );
            update_post_meta($contact->id, '_applicant_profiles', $num_existing_profiles+1);

            $existing_contact_types = get_post_meta( (int)$contact->id, '_contact_types', TRUE );
            if ( !is_array($existing_contact_types) )
            {
                $existing_contact_types = array();
            }
            if ( !in_array( 'applicant', $existing_contact_types ) )
            {
                $existing_contact_types[] = 'applicant';
                update_post_meta( (int)$contact->id, '_contact_types', $existing_contact_types );
            }
            echo json_encode( array('success' => true ) );
            wp_die();
        }
        else
        {
            echo json_encode( array('success' => false, 'error_message' => 'Please log in and try again' ) );
            wp_die();
        }
        wp_die();
    }

    public function remove_saved_search_ajax_callback()
    {
        header( 'Content-Type: application/json; charset=utf-8' );
        if ( $this->is_user_valid_contact() )
        {
            $current_user = wp_get_current_user();
            $contact = new PH_Contact( '', $current_user->ID );

                
            $num_applicant_profiles = (int)get_post_meta( (int)$contact->id, '_applicant_profiles', TRUE );

            for ( $i = 0; $i < $num_applicant_profiles; ++$i )
            {
                if ( $i == $_POST['profile_to_remove'] ) 
                {
                    $deleting_applicant_profile = $i;

                    // We're deleting this one
                    delete_post_meta( (int)$contact->id, '_applicant_profile_' . $i );

                    // Now need to rename any that are higher than $deleting_applicant_profile
                    for ( $j = 0; $j < $num_applicant_profiles; ++$j )
                    {
                        if ( $j > $deleting_applicant_profile )
                        {
                            $this_applicant_profile = get_post_meta( (int)$contact->id, '_applicant_profile_' . $j );
                            update_post_meta( (int)$contact->id, '_applicant_profile_' . ($j - 1), $this_applicant_profile[0] );
                            delete_post_meta( (int)$contact->id, '_applicant_profile_' . $j );
                        }
                    }

                    // remove from _contact_types if no more profiles left
                    if ( $num_applicant_profiles == 1 )
                    {
                        $existing_contact_types = get_post_meta( (int)$contact->id, '_contact_types', TRUE );
                        if ( $existing_contact_types == '' || !is_array($existing_contact_types) )
                        {
                            $existing_contact_types = array();
                        }
                        if( ( $key = array_search('applicant', $existing_contact_types) ) !== false )
                        {
                            unset($existing_contact_types[$key]);
                        }
                        update_post_meta( (int)$contact->id, '_contact_types', $existing_contact_types );
                    }
                    update_post_meta( (int)$contact->id, '_applicant_profiles', $num_applicant_profiles - 1 );
                }
            }
            echo json_encode( array('success' => true ) );
            wp_die();
        }
        else
        {
            echo json_encode( array('success' => false, 'error_message' => 'Please log in and try again' ) );
            wp_die();
        }
    }

    public function update_search_send_emails_ajax_callback()
    {
        header( 'Content-Type: application/json; charset=utf-8' );
        if ( $this->is_user_valid_contact() )
        {
            $current_user = wp_get_current_user();
            $contact = new PH_Contact( '', $current_user->ID );

            if ( !empty($contact->id) )
            {
                $applicant_profile = get_post_meta( (int)$contact->id, '_applicant_profile_' . $_POST['profile_to_update'], TRUE );

                if ( !empty($applicant_profile) && is_array($applicant_profile) )
                {
                    unset($applicant_profile['send_matching_properties']);
                    $applicant_profile['send_matching_properties'] = $_POST['send_email_alerts'];

                    update_post_meta( (int)$contact->id, '_applicant_profile_' . $_POST['profile_to_update'], $applicant_profile );
                }
                echo json_encode( array('success' => true ) );
                wp_die();
            }
        }
        else
        {
            echo json_encode( array('success' => false, 'error_message' => 'Please log in and try again' ) );
            wp_die();
        }
    }

    public function process_search_url_to_array( $url_string )
    {
        $explode_url_string = explode( '&', urldecode( $url_string ) );
        $parameter_array = array();
        foreach ($explode_url_string as $parameter)
        {
            list($field, $value) = explode('=', $parameter);
            if ( $value != '' )
            {
                if ( substr($field, -2) == '[]' )
                {
                    $field = preg_replace('/\[\]$/s', '', $field);
                    if ( isset($parameter_array[$field]) )
                    {
                        $parameter_array[$field][] = $value;
                    }
                    else
                    {
                        $parameter_array[$field] = array($value);
                    }
                }
                else
                {
                    $parameter_array[$field] = $value;
                }
            }
        }
        return $parameter_array;
    }

    public function is_user_valid_contact()
    {
        if ( is_user_logged_in() )
        {
            $current_user = wp_get_current_user();

            if ( $current_user instanceof WP_User )
            {
                $saved_searches = array();

                $contact = new PH_Contact( '', $current_user->ID );

                if ( !empty($contact->id) )
                {
                    return true;
                }
            }
        }
        return false;
    }
}

endif;

/**
 * Returns the main instance of PH_Save_Search to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return PH_Save_Search
 */
function PHSS() {
    return PH_Save_Search::instance();
}

$PHSS = PHSS();

if( is_admin() && file_exists(  dirname( __FILE__ ) . '/propertyhive-save-search-update.php' ) )
{
    include_once( dirname( __FILE__ ) . '/propertyhive-save-search-update.php' );
}