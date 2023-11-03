<?php
/**
 * Plugin Name: Property Hive Radial Search Add On
 * Plugin Uri: http://wp-property-hive.com/addons/radial-search/
 * Description: Add On for Property Hive allowing users to perform radial searches based on a location
 * Version: 1.0.26
 * Author: PropertyHive
 * Author URI: http://wp-property-hive.com
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'PH_Radial_Search' ) ) :

final class PH_Radial_Search {

    /**
     * @var string
     */
    public $version = '1.0.26';

    /**
     * @var PropertyHive The single instance of the class
     */
    protected static $_instance = null;
    
    /**
     * Main PropertyHive Radial Search Instance
     *
     * Ensures only one instance of Property Hive Radial Search is loaded or can be loaded.
     *
     * @static
     * @return PropertyHive Radial Search - Main instance
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

        $this->id    = 'radialsearch';
        $this->label = __( 'Radial Search', 'propertyhive' );

        // Define constants
        $this->define_constants();

        // Include required files
        $this->includes();

        add_action( 'admin_notices', array( $this, 'radial_search_error_notices') );

        add_action( 'admin_init', array( $this, 'check_delete_cache') );

        add_filter( "plugin_action_links_" . plugin_basename( __FILE__ ), array( $this, 'plugin_add_settings_link' ) );

        add_action( 'save_post', array( $this, 'radial_search_save_lat_lng' ) );

        add_filter( 'propertyhive_settings_tabs_array', array( $this, 'add_settings_tab' ), 19 );
        add_action( 'propertyhive_settings_' . $this->id, array( $this, 'output' ) );
        add_action( 'propertyhive_settings_save_' . $this->id, array( $this, 'save' ) );

        $query_string = (isset($_POST['query_string']) && json_decode(stripslashes($_POST['query_string']), TRUE) !== FALSE) ? $_REQUEST = array_merge(json_decode(stripslashes($_POST['query_string']), TRUE), $_REQUEST) : array();

        add_action( 'pre_get_posts', array( $this, 'do_radius_actions' ), 1 );

        add_filter( 'posts_fields',  array( $this, 'select_field_radius' ), 10, 2 );
        add_filter( 'posts_orderby',  array( $this, 'orderby_radius' ), 10, 2 );

        add_action( 'phradialsearchcronhook', array( $this, 'radial_search_fill_lat_lng_post' ) );

        add_filter( 'propertyhive_results_orderby', array( $this, 'add_distance_order_by') );

        add_filter( 'propertyhive_search_form_fields', array( $this, 'add_current_location_option'), 999, 1 );

        add_action( 'wp_enqueue_scripts', array( $this, 'load_radial_search_scripts' ) );

        add_filter( 'propertyhive_use_google_maps_geocoding_api_key', array( $this, 'enable_separate_geocoding_api_key' ) );

        add_action( 'propertyhive_contact_applicant_requirements_details_fields', array( $this, 'add_applicant_location_radius_field' ), 1, 2 );
        add_action( 'propertyhive_save_contact_applicant_requirements', array( $this, 'save_applicant_location_radius_field' ), 10, 2 );

        add_action( 'propertyhive_applicant_requirements_form_fields', array( $this, 'add_applicant_location_radius_form_field' ), 1, 2 );
        add_action( 'propertyhive_applicant_registered', array( $this, 'save_applicant_location_radius_form_field' ), 10, 2 );
        add_action( 'propertyhive_account_requirements_updated', array( $this, 'save_applicant_location_radius_form_field' ), 10, 2 );

        add_filter( 'propertyhive_location_used_when_matching_applicants', array( $this, 'remove_location_when_matching_applicants' ), 10, 2 );
        add_filter( 'propertyhive_matching_applicants_check', array( $this, 'applicant_location_radius_matching_applicants_check' ), 10, 4 );

        $shortcodes = array(
            'properties',
            'recent_properties',
            'featured_properties',
            'similar_properties',
        );

        foreach ( $shortcodes as $shortcode )
        {
            add_filter( 'shortcode_atts_' . $shortcode, array( $this, 'add_radius_as_attribute_to_shortcode' ), 10, 4 );

            add_filter( 'propertyhive_shortcode_' . $shortcode . '_query', array( $this, 'handle_shortcode_radius_attribute' ), 99, 2 );
        }
    }

    // don't use normal location filter if we need to do a radial search instead
    public function remove_location_when_matching_applicants( $enabled, $applicant_profile )
    {
        if ( get_option('propertyhive_applicant_locations_type') == 'text' )
        {
            if ( isset( $applicant_profile['location_text'] ) && $applicant_profile['location_text'] != '' && isset( $applicant_profile['location_radius'] ) && $applicant_profile['location_radius'] != '' )
            {
                return false;
            }
        }

        return $enabled;
    }

    public function do_radius_actions()
    {
        if (
            ( !is_admin() && isset($_REQUEST['radius']) && sanitize_text_field( $_REQUEST['radius'] ) != '' && sanitize_text_field( $_REQUEST['radius'] ) != '0' )
            ||
            ( defined('DOING_AJAX') && DOING_AJAX && isset($_REQUEST['radius']) && sanitize_text_field( $_REQUEST['radius'] ) != '' && sanitize_text_field( $_REQUEST['radius'] ) != '0' )
        )
        {
            add_action( 'propertyhive_property_query', array( $this, 'radial_search_remove_existing_address_keyword_meta_query' ) );
            add_action( 'pre_get_posts', array( $this, 'radial_search_pre_get_posts' ) );
            add_action( 'wp', array( $this, 'radial_search_remove_radius_query' ) );
        }

        if ( is_admin() && isset($_GET['page']) && $_GET['page'] == 'ph-matching-properties' )
        {
            if ( get_option('propertyhive_applicant_locations_type') == 'text' )
            {
                $applicant_profile = get_post_meta( (int)$_GET['contact_id'], '_applicant_profile_' . (int)$_GET['applicant_profile'], TRUE );

                if ( isset( $applicant_profile['location_text'] ) && $applicant_profile['location_text'] != '' && isset( $applicant_profile['location_radius'] ) && $applicant_profile['location_radius'] != '' )
                {
                    // get applicant profile and set $GLOBALS addres_keyword and radius so we can use the existing where and join functionality
                    $GLOBALS['address_keyword'] = $applicant_profile['location_text'];
                    $GLOBALS['radius'] = $applicant_profile['location_radius'];
                    
                    add_filter( 'propertyhive_matching_properties_args', array( $this, 'applicant_property_match_remove_existing_address_keyword_meta_query' ), 10, 3 );
                    add_action( 'pre_get_posts', array( $this, 'radial_search_pre_get_posts' ) );
                }
            }
        }
    }

    public function add_radius_as_attribute_to_shortcode( $out, $pairs, $atts, $shortcode )
    {
        $out['radius'] = ( isset($atts['radius']) ? $atts['radius'] : '' );

        return $out;
    }

    public function handle_shortcode_radius_attribute( $args, $atts )
    {
        if ( 
            isset( $atts['address_keyword'] ) && $atts['address_keyword'] != ''
            && 
            isset( $atts['radius'] ) && $atts['radius'] != ''
        )
        {
            $GLOBALS['address_keyword'] = $atts['address_keyword'];
            $GLOBALS['radius'] = (int)$atts['radius'];

            add_filter( 'pre_get_posts', array( $this, 'radial_search_pre_get_posts' ) );

            $new_meta_query = array();

            foreach ( $args['meta_query'] as $meta_query_element )
            {
                if ( 
                    isset($meta_query_element['relation']) && $meta_query_element['relation'] == 'OR' && 
                    isset($meta_query_element[0]) && isset($meta_query_element[0]['key']) && $meta_query_element[0]['key'] == '_reference_number'
                )
                {

                }
                else
                {
                    $new_meta_query[] = $meta_query_element;
                }
            }

            $args['meta_query'] = $new_meta_query;
        }

        return $args;
    }

    public function enable_separate_geocoding_api_key( $return )
    {
        return true;
    }

    public function load_radial_search_scripts() {

        $current_settings = get_option( 'propertyhive_radial_search', array() );

        if ( isset($current_settings['current_location_enabled']) && $current_settings['current_location_enabled'] == 1 )
        {
            $map_settings = get_option( 'propertyhive_map_search', array() );

            $assets_path = str_replace( array( 'http:', 'https:' ), '', untrailingslashit( plugins_url( '/', __FILE__ ) ) ) . '/assets/';

            $api_key = get_option('propertyhive_google_maps_geocoding_api_key', '');
            if ( $api_key == '' )
            {
                $api_key = get_option('propertyhive_google_maps_api_key', '');
            }

            $params = array();
            if ( $api_key != '' && $api_key !== FALSE )
            {
                $params[] = 'key=' . $api_key;
            }
            if ( isset($map_settings['draw_a_search_enabled']) && $map_settings['draw_a_search_enabled'] == '1' )
            {
                $params[] = 'libraries=drawing,geometry';
            }

            wp_register_script( 
                'googlemaps', 
                '//maps.googleapis.com/maps/api/js?' . implode("&", $params), 
                array(), 
                '3', 
                true 
            );

            wp_register_script( 
                'ph-radial-search', 
                $assets_path . 'js/ph-radial-search.js', 
                array(), 
                PH_RADIAL_SEARCH_VERSION,
                true
            );

            wp_enqueue_script('googlemaps');
            wp_enqueue_script('ph-radial-search');
        }
    }

    public function add_current_location_option( $form_fields = array() )
    {
        $current_settings = get_option( 'propertyhive_radial_search', array() );

        if ( isset($current_settings['current_location_enabled']) && $current_settings['current_location_enabled'] == 1 )
        {
            foreach ( $form_fields as $key => $field )
            {
                if ( $key == 'address_keyword' )
                {
                    $assets_path = str_replace( array( 'http:', 'https:' ), '', untrailingslashit( plugins_url( '/', __FILE__ ) ) ) . '/assets/';

                    if ( !isset($field['after']) )
                    {
                        $field['after'] = '</div>';
                    }
                    $field['after'] = '<div class="current-location" style="position:absolute; right:15px; bottom:10px; width:30px;"><a href="" title="Use Current Location"><img src="' . $assets_path . '/images/current-location.png" alt="Use Current Location"></a></div>' . $field['after'];

                    $form_fields[$key] = $field;
                }
            }

            $form_fields['lat'] = array(
                'type' => 'hidden',
            );
            $form_fields['lng'] = array(
                'type' => 'hidden',
            );
        }

        return $form_fields;
    }

    public function plugin_add_settings_link( $links )
    {
        $settings_link = '<a href="' . admin_url('admin.php?page=ph-settings&tab=radialsearch') . '">' . __( 'Settings' ) . '</a>';
        array_push( $links, $settings_link );
        return $links;
    }

    public function check_delete_cache()
    {
        global $wpdb;

        if ( isset($_GET['ph_action']) && $_GET['ph_action'] == 'delete-latlng-cache' )
        {
            // Delete any existing co-ordinates
            $wpdb->query( "DELETE FROM " . $wpdb->prefix . "ph_radial_search_lat_lng_cache" );
        }
    }

    public function add_distance_order_by( $orderby_options )
    {
        if (
            ( isset($_REQUEST['address_keyword']) && sanitize_text_field( $_REQUEST['address_keyword'] ) != '' )
            ||
            ( isset($_REQUEST['location']) && sanitize_text_field( $_REQUEST['location'] ) != '' )
        )
        {
            $orderby_options['radius'] = 'Sort by distance: nearest first';
        }
        return $orderby_options;
    }

    // $args - array( $fields, $query );
    public function select_field_radius( $fields, $query )
    {
        if ( 
            ( is_post_type_archive( 'property' ) && isset( $query->query_vars['post_type'] ) && 'property' == $query->query_vars['post_type'] )
            ||
            ( defined('DOING_AJAX') && DOING_AJAX && isset($_REQUEST['radius']) )
        )
        {
            global $wpdb;

            $location_keyword = '';
            if ( isset($_REQUEST['address_keyword']) && sanitize_text_field( $_REQUEST['address_keyword'] ) != '' )
            {
                $location_keyword = sanitize_text_field( $_REQUEST['address_keyword'] );
            }
            if ( isset($_REQUEST['location']) && sanitize_text_field( $_REQUEST['location'] ) != '' )
            {
                // Get term name from ID
                $term = get_term( sanitize_text_field( $_REQUEST['location'] ), 'location' );
                if ( !is_wp_error($term) && !is_null($term) )
                {
                    $location_keyword = $term->name;
                }
            }

            if ( $location_keyword != '' )
            {

                if ( ( isset($_REQUEST['lat']) && trim($_REQUEST['lat']) != '') && (isset($_REQUEST['lng']) && trim($_REQUEST['lng']) != '' ) )
                {
                    $lat = $_REQUEST['lat'];
                    $lng = $_REQUEST['lng'];
                }
                else
                {
                    list($lat, $lng) = $this->get_cached_lat_long_or_cache_new( $location_keyword );
                }

                if ( $lat != '' && $lng != '' )
                {
                    $fields .= ",(SELECT ( 3959 * acos( cos( radians(" . trim($lat) . ") )
                                            * cos( radians( lat ) )
                                            * cos( radians( lng )
                                            - radians(" . trim($lng) . ") )
                                            + sin( radians(" . trim($lat) . ") )
                                            * sin( radians( lat ) ) ) )
                                FROM " . $wpdb->prefix . "ph_radial_search_lat_lng_post 
                                WHERE
                                    " . $wpdb->prefix . "ph_radial_search_lat_lng_post.post_id = " . $wpdb->prefix . "posts.ID
                                             ) AS radius";
                }
                else
                {
                    $fields .= ",0 AS radius";
                }
            }
        }

        return $fields;
    }

    // $args - array( $orderby, $query );
    public function orderby_radius( $orderby, $query )
    {
        if ( 
            ( is_post_type_archive( 'property' ) && isset( $query->query_vars['post_type'] ) && 'property' == $query->query_vars['post_type'] )
            ||
            ( defined('DOING_AJAX') && DOING_AJAX && isset($_REQUEST['radius']) )
        )
        {
            if ( 
                isset($_REQUEST['orderby']) && 
                $_REQUEST['orderby'] == 'radius' &&
                (
                    ( isset($_REQUEST['address_keyword']) && sanitize_text_field( $_REQUEST['address_keyword'] ) != '' )
                    ||
                    ( isset($_REQUEST['location']) && sanitize_text_field( $_REQUEST['location'] ) != '' )
                )
            )
            {
                $orderby = 'radius ASC' . ( ( $orderby != '' ) ? ',' : '' ) . $orderby;
            }
        }
        return $orderby;
    }

    public function add_applicant_location_radius_field($contact_post_id, $applicant_profile_id)
    {
        if ( get_option('propertyhive_applicant_locations_type') == 'text' )
        {
            $applicant_profile = get_post_meta( $contact_post_id, '_applicant_profile_' . $applicant_profile_id, true );
            
            $options = array(
                '' => __( 'This Area Only', 'propertyhive' ),
                '1' => __( 'Within 1 Mile', 'propertyhive' ),
                '2' => __( 'Within 2 Miles', 'propertyhive' ),
                '3' => __( 'Within 3 Miles', 'propertyhive' ),
                '5' => __( 'Within 5 Miles', 'propertyhive' ),
                '10' => __( 'Within 10 Miles', 'propertyhive' ),
            );
            
            $args = array( 
                'id' => '_applicant_location_radius_' . $applicant_profile_id, 
                'label' => __( 'Location Radius', 'propertyhive' ), 
                'desc_tip' => false,
                'options' => $options,
                'value' => isset( $applicant_profile['location_radius'] ) ? $applicant_profile['location_radius'] : '',
            );
            propertyhive_wp_select( $args );
        }
    }

    public function save_applicant_location_radius_field( $contact_post_id, $applicant_profile_id )
    {
        if ( get_option('propertyhive_applicant_locations_type') == 'text' )
        {
            $applicant_profile = get_post_meta( $contact_post_id, '_applicant_profile_' . $applicant_profile_id, TRUE );

            if ( isset($_POST['_applicant_location_radius_' . $applicant_profile_id]) )
            {
                $applicant_profile['location_radius'] = ph_clean($_POST['_applicant_location_radius_' . $applicant_profile_id]);
                update_post_meta( $contact_post_id, '_applicant_profile_' . $applicant_profile_id, $applicant_profile );

                if ( $applicant_profile['location_radius'] != '' && isset($applicant_profile['location_text']) && $applicant_profile['location_text'] != '' )
                {
                    list($lat, $lng) = $this->get_cached_lat_long_or_cache_new( $applicant_profile['location_text'] );
                }
            }
        }
    }

    public function add_applicant_location_radius_form_field( $form_controls )
    {
        $temp_form_controls = $form_controls;
        if ( get_option('propertyhive_applicant_locations_type') == 'text' && isset($form_controls['location_text']) )
        {
            $applicant_profile = array();
            if ( is_user_logged_in() )
            {
                $current_user = wp_get_current_user();
                $applicant_profile = false;

                if ( $current_user instanceof WP_User )
                {
                    $contact = new PH_Contact( '', $current_user->ID );

                    if ( is_array($contact->contact_types) && in_array('applicant', $contact->contact_types) )
                    {
                        if (
                            $contact->applicant_profiles != '' &&
                            $contact->applicant_profiles > 0 &&
                            $contact->applicant_profile_0 != '' &&
                            is_array($contact->applicant_profile_0)
                        )
                        {
                            $applicant_profile = $contact->applicant_profile_0;
                        }
                    }
                }
            }

            $options = array(
                '' => __( 'This Area Only', 'propertyhive' ),
                '1' => __( 'Within 1 Mile', 'propertyhive' ),
                '2' => __( 'Within 2 Miles', 'propertyhive' ),
                '3' => __( 'Within 3 Miles', 'propertyhive' ),
                '5' => __( 'Within 5 Miles', 'propertyhive' ),
                '10' => __( 'Within 10 Miles', 'propertyhive' ),
            );

            $radius_control = array(
                'type' => 'select',
                'label' => __( 'Location Radius', 'propertyhive' ),
                'options' => $options,
                'required' => false
            );

            if ( isset($applicant_profile['location_radius']) && $applicant_profile['location_radius'] != '' )
            {
                $radius_control['value'] = $applicant_profile['location_radius'];
            }

            $location_text_position = array_search('location_text', array_keys($form_controls))+1;

            $form_controls = array_merge(
                array_slice($temp_form_controls, 0, $location_text_position),
                array('location_radius' => $radius_control),
                array_slice($temp_form_controls, $location_text_position, null)
            );
        }
        return $form_controls;
    }

    public function save_applicant_location_radius_form_field( $contact_post_id, $user_id )
    {
        if ( get_option('propertyhive_applicant_locations_type') == 'text' )
        {
            if ( isset($_POST['location_radius']) && !empty($_POST['location_radius']) && isset($_POST['location_text']) && !empty($_POST['location_text']) )
            {
                $applicant_profile = get_post_meta( $contact_post_id, '_applicant_profile_0', TRUE );
                $applicant_profile['location_radius'] = ph_clean($_POST['location_radius']);
                update_post_meta( $contact_post_id, '_applicant_profile_0', $applicant_profile );

                list($lat, $lng) = $this->get_cached_lat_long_or_cache_new( $_POST['location_text'] );
            }
        }
    }

    // if radius and location entered on applicant profile then first remove existing address lookup query
    public function applicant_property_match_remove_existing_address_keyword_meta_query( $args, $contact_post_id, $applicant_profile )
    {
        if ( get_option('propertyhive_applicant_locations_type') == 'text' )
        {
            if ( isset( $applicant_profile['location_text'] ) && $applicant_profile['location_text'] != '' && isset( $applicant_profile['location_radius'] ) && $applicant_profile['location_radius'] != '' )
            {
                if ( isset($args['meta_query']) && !empty($args['meta_query']) )
                {
                    $new_meta_query = array();
                    foreach ( $args['meta_query'] as $key => $meta_query_element )
                    {
                        if (
                            isset($meta_query_element['relation']) && $meta_query_element['relation'] == 'OR' &&
                            isset($meta_query_element[0]) && isset($meta_query_element[0]['key']) && $meta_query_element[0]['key'] == '_address_street'
                        )
                        {

                        }
                        else
                        {
                            $new_meta_query[] = $meta_query_element;
                        }
                    }
                    $args['meta_query'] = $new_meta_query;
                }
            }
        }
        return $args;
    }

    public function applicant_location_radius_matching_applicants_check( $check, $property, $contact_post_id, $applicant_profile )
    {
        if ( get_option('propertyhive_applicant_locations_type') == 'text' )
        {
            if ( isset( $applicant_profile['location_text'] ) && $applicant_profile['location_text'] != '' && isset( $applicant_profile['location_radius'] ) && $applicant_profile['location_radius'] != '' )
            {
                $applicant_location = $applicant_profile['location_text'];
                $applicant_radius = $applicant_profile['location_radius'];

                $in_address = propertyhive_is_location_in_address( $property, $applicant_location );
                if ($in_address)
                {
                    // location entered is in the address. We can stop here
                    return true;
                }

                // now do radial search bit
                $property_lat = $property->_latitude;
                $property_lng = $property->_longitude;

                list($applicant_lat, $applicant_lng) = $this->get_cached_lat_long_or_cache_new($applicant_location);

                if ( !empty($property_lat) && !empty($property_lng) && !empty($applicant_lat) && !empty($applicant_lng) )
                {
                    $distance = $this->distance_between_two_points($property_lat, $property_lng, $applicant_lat, $applicant_lng);

                    if ( $distance <= $applicant_radius )
                    {
                        return true;
                    }
                }

                return false;
            }
        }

        return $check;
    }

    private function distance_between_two_points($lat1, $lon1, $lat2, $lon2, $unit = 'M')
    {
        if (($lat1 == $lat2) && ($lon1 == $lon2)) 
        {
            return 0;
        }
        else 
        {
            $theta = $lon1 - $lon2;
            $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
            $dist = acos($dist);
            $dist = rad2deg($dist);
            $miles = $dist * 60 * 1.1515;
            $unit = strtoupper($unit);

            if ($unit == "K") 
            {
                return ($miles * 1.609344);
            } 
            else 
            {
                return $miles;
            }
        }
    }

    public function radial_search_fill_lat_lng_post()
    {
        // Placeholder for potential cron that runs daily to fill in lat_lng_post table
        // ...
    }

    /**
     * Define PH Radial Search Constants
     */
    private function define_constants() 
    {
        define( 'PH_RADIAL_SEARCH_PLUGIN_FILE', __FILE__ );
        define( 'PH_RADIAL_SEARCH_VERSION', $this->version );
    }

    private function includes()
    {
        include_once( dirname( __FILE__ ) . "/includes/class-ph-radial-search-install.php" );
    }

    /**
     * Output error message if core PropertyHive plugin isn't active
     */
    public function radial_search_error_notices() 
    {
        if (!is_plugin_active('propertyhive/propertyhive.php'))
        {
            $message = "The PropertyHive plugin must be installed and activated before you can use the PropertyHive Radial Search add-on";
            echo"<div class=\"error\"> <p>$message</p></div>";
        }
        if ( !ini_get('allow_url_fopen') )
        {
            $message = "The '<a href=\"http://php.net/manual/en/filesystem.configuration.php#ini.allow-url-fopen\" target=\"_blank\">allow_url_fopen</a>' PHP setting must be turned on to perform radial searches. Please enable it";
            echo"<div class=\"error\"> <p>$message</p></div>";
        }
    }

    public function radial_search_save_lat_lng( $post_id )
    {
        global $wpdb;

        if ( 'property' != get_post_type($post_id) ) 
        {
            return;
        }
        
        // Delete any existing co-ordinates
        $wpdb->delete( $wpdb->prefix . "ph_radial_search_lat_lng_post", array( 'post_id' => $post_id ) );
        
        $lat = get_post_meta( $post_id, '_latitude', TRUE );
        $lng = get_post_meta( $post_id, '_longitude', TRUE );
        $on_market = get_post_meta( $post_id, '_on_market', TRUE );

        if ( $lat != '' && $lng != '' && $on_market == 'yes'  )
        {
            $wpdb->insert( 
                $wpdb->prefix . "ph_radial_search_lat_lng_post", 
                array( 
                    'post_id' => $post_id,
                    'lat' => trim($lat), 
                    'lng' => trim($lng)
                ), 
                array( 
                    '%d',
                    '%f', 
                    '%f' 
                ) 
            );
        }
    }

    public function radial_search_get_lat_lng_from_location( $location = '' )
    {
        $lat = '';
        $lng = '';

        if ( trim($location) != '' )
        {
            $location = apply_filters( 'propertyhive_radial_search_location_lookup', $location );

            // Perform geocode
            $region = strtolower(get_option( 'propertyhive_default_country', 'GB' ));
            if ( trim($region) == '' )
            {
                $region = 'gb';
            }
            $request_url = "https://maps.googleapis.com/maps/api/geocode/xml?address=" . urlencode($location) . ", " . $region . "&sensor=false&region=" . $region; // the request URL you'll send to google to get back your XML feed 

            $api_key = get_option('propertyhive_google_maps_geocoding_api_key', '');
            if ( $api_key == '' )
            {
                $api_key = get_option('propertyhive_google_maps_api_key', '');
            }
            if ( $api_key != '' )
            {
                $request_url .= "&key=" . $api_key;
            }    
            
            $response = wp_remote_get( $request_url );

            if ( !is_wp_error($response) && is_array($response) && isset($response['body']) )
            {
                $xml = simplexml_load_string($response['body']);
            
                $status = $xml->status; // GET the request status as google's api can return several responses
                
                if ( $status == "OK" ) 
                {
                    //request returned completed time to get lat / lang for storage
                    $lat = (string)$xml->result->geometry->location->lat;
                    $lng = (string)$xml->result->geometry->location->lng;
                }
            }
        }
        
        if ( $lat == '' || $lng == '' )
        {
           // We couldn't get lat/lng for this location. Should we do anything with this?
        }

        return array($lat, $lng);
    }

    public function get_cached_lat_long_or_cache_new($location_keyword)
    {
        global $wpdb;

        $lat = '';
        $lng = '';

        // We have a location. Check if it's one we have cached already
        $lat_lng_row = $wpdb->get_row("SELECT lat, lng FROM " . $wpdb->prefix . "ph_radial_search_lat_lng_cache WHERE location = '" . strtolower( esc_sql($location_keyword) ) . "'", ARRAY_A);

        if ( $lat_lng_row != null ) 
        {
            $lat = $lat_lng_row['lat'];
            $lng = $lat_lng_row['lng'];
        }
        else
        {
            list($lat, $lng) = $this->radial_search_get_lat_lng_from_location( $location_keyword );

            if ( $lat != '' && $lng != '' )
            {
                $wpdb->insert( 
                    $wpdb->prefix . 'ph_radial_search_lat_lng_cache', 
                    array( 
                        'location' => strtolower( $location_keyword ), 
                        'lat' => sanitize_text_field($lat),
                        'lng' => sanitize_text_field($lng) 
                    ), 
                    array( 
                        '%s', 
                        '%f',
                        '%f'
                    ) 
                );
            }
        }
        return array($lat, $lng);
    }

    public function radial_search_remove_existing_address_keyword_meta_query( $q )
    {
        global $post;

        $query_string = (isset($_POST['query_string']) && json_decode(stripslashes($_POST['query_string']), TRUE) !== FALSE) ? $_REQUEST = array_merge(json_decode(stripslashes($_POST['query_string']), TRUE), $_REQUEST) : array();

        if ( 
            ( $q->is_main_query() && is_post_type_archive( 'property' ) && isset($_REQUEST['radius']) && sanitize_text_field( $_REQUEST['radius'] ) != '' && sanitize_text_field( $_REQUEST['radius'] ) != '0' ) 
            || 
            ( defined('DOING_AJAX') && DOING_AJAX && isset($_REQUEST['radius']) && sanitize_text_field( $_REQUEST['radius'] ) != '' && sanitize_text_field( $_REQUEST['radius'] ) != '0' )
        )
        {
            if ( 
                ( isset($_REQUEST['address_keyword']) && sanitize_text_field( trim( $_REQUEST['address_keyword'] ) ) != '' ) ||
                ( isset($GLOBALS['address_keyword']) && sanitize_text_field( trim( $GLOBALS['address_keyword'] ) ) != '' ) ||
                ( isset($_REQUEST['location']) && sanitize_text_field( trim( $_REQUEST['location'] ) ) != '' )
            )
            {
                $meta_query = $q->get( 'meta_query' );

                // Loop through existing meta_query and remove the address keyword related part

                $new_meta_query = array();

                foreach ($meta_query as $meta_query_element)
                {
                    if ( 
                        isset($meta_query_element['relation']) && $meta_query_element['relation'] == 'OR' && 
                        isset($meta_query_element[0]) && isset($meta_query_element[0]['key']) && $meta_query_element[0]['key'] == '_reference_number'
                    )
                    {

                    }
                    else
                    {
                        $new_meta_query[] = $meta_query_element;
                    }
                }

                $meta_query = $new_meta_query;

                $q->set( 'meta_query', $meta_query );

                $tax_query = $q->get( 'tax_query' );

                // Loop through existing tax_query and remove the location related part

                $new_tax_query = array();

                foreach ($tax_query as $tax_query_element)
                {
                    if (
                        $tax_query_element['taxonomy'] == 'location'
                    )
                    {

                    }
                    else
                    {
                        $new_tax_query[] = $tax_query_element;
                    }
                }

                $tax_query = $new_tax_query;

                $q->set( 'tax_query', $tax_query );
            }
        }
    }

    public function radial_search_pre_get_posts( $q )
    {
        add_filter( 'posts_join', array( $this, 'radial_search_location_posts_join' ), 10, 2 );
        add_filter( 'posts_where', array( $this, 'radial_search_location_posts_where' ), 10, 2 );
    }

    public function radial_search_location_posts_join( $join, $query )
    {
        global $wpdb;

        $query_string = (isset($_POST['query_string']) && json_decode(stripslashes($_POST['query_string']), TRUE) !== FALSE) ? $_REQUEST = array_merge(json_decode(stripslashes($_POST['query_string']), TRUE), $_REQUEST) : array();

        if ( 
            ( is_post_type_archive( 'property' ) && isset( $query->query_vars['post_type'] ) && 'property' == $query->query_vars['post_type'] )
            ||
            ( defined('DOING_AJAX') && DOING_AJAX && isset($_REQUEST['radius']) && sanitize_text_field( $_REQUEST['radius'] ) != '' && sanitize_text_field( $_REQUEST['radius'] ) != '0' )
            ||
            ( isset($GLOBALS['address_keyword']) && $GLOBALS['address_keyword'] != '' && isset($GLOBALS['radius']) && $GLOBALS['radius'] != '' )
        )
        {

            $join .= " INNER JOIN $wpdb->postmeta AS mtcustom ON ( $wpdb->posts.ID = mtcustom.post_id ) ";
        }

        return $join;
    }

    public function radial_search_location_posts_where( $where, $query )
    {
        $query_string = (isset($_POST['query_string']) && json_decode(stripslashes($_POST['query_string']), TRUE) !== FALSE) ? $_REQUEST = array_merge(json_decode(stripslashes($_POST['query_string']), TRUE), $_REQUEST) : array();

        if ( 
            ( is_post_type_archive( 'property' ) && isset( $query->query_vars['post_type'] ) && 'property' == $query->query_vars['post_type'] )
            ||
            ( defined('DOING_AJAX') && DOING_AJAX && isset($_REQUEST['radius']) && sanitize_text_field( $_REQUEST['radius'] ) != '' && sanitize_text_field( $_REQUEST['radius'] ) != '0' )
            ||
            ( isset($GLOBALS['address_keyword']) && $GLOBALS['address_keyword'] != '' && isset($GLOBALS['radius']) && $GLOBALS['radius'] != '' )
        )
        {
            $location_keyword = '';
            if ( isset($_REQUEST['address_keyword']) && sanitize_text_field( trim( $_REQUEST['address_keyword'] ) ) != '' )
            {
                $location_keyword = sanitize_text_field( trim( $_REQUEST['address_keyword'] ) );

                // Remove country code from end (i.e. ', UK')
                $location_keyword = preg_replace('/\,\s?[A-Z][A-Z]$/', '', $location_keyword);
            }
            elseif ( isset($GLOBALS['address_keyword']) && sanitize_text_field( trim( $GLOBALS['address_keyword'] ) ) != '' )
            {
                $location_keyword = sanitize_text_field( trim( $GLOBALS['address_keyword'] ) );

                // Remove country code from end (i.e. ', UK')
                $location_keyword = preg_replace('/\,\s?[A-Z][A-Z]$/', '', $location_keyword);
            }
            elseif ( isset($_REQUEST['location']) && sanitize_text_field( trim( $_REQUEST['location'] ) ) != '' )
            {
                // Get term name from ID
                $term = get_term( sanitize_text_field( trim( $_REQUEST['location'] ) ), 'location' );
                if ( !is_wp_error($term) && !is_null($term) )
                {
                    $location_keyword = $term->name;
                }
            }

            if ( $location_keyword != '' )
            {
                global $wpdb;

                $radius = 0;
                if ( isset($_REQUEST['radius']) && sanitize_text_field( $_REQUEST['radius'] ) != '' && sanitize_text_field( $_REQUEST['radius'] ) != '0' )
                {
                    $radius = sanitize_text_field( $_REQUEST['radius'] );
                }
                elseif ( isset($GLOBALS['radius']) && sanitize_text_field( $GLOBALS['radius'] ) != '' && sanitize_text_field( $GLOBALS['radius'] ) != '0' )
                {
                    $radius = sanitize_text_field( $GLOBALS['radius'] );
                }

                if ( ( isset($_REQUEST['lat']) && trim($_REQUEST['lat']) != '') && (isset($_REQUEST['lng']) && trim($_REQUEST['lng']) != '' ) )
                {
                    $lat = $_REQUEST['lat'];
                    $lng = $_REQUEST['lng'];
                }
                else
                {
                    list($lat, $lng) = $this->get_cached_lat_long_or_cache_new( $location_keyword );
                }
                
                if ( $radius != 0 )
                {
                    $where .= " AND ( ";

                    if ( $lat != '' && $lng != '' )
                    {
                        $where .= " (
                                $wpdb->posts.ID IN (
                                    SELECT post_id FROM " . $wpdb->prefix . "ph_radial_search_lat_lng_post WHERE
                                        ( 3959 * acos( cos( radians(" . trim($lat) . ") )
                                        * cos( radians( lat ) )
                                        * cos( radians( lng )
                                        - radians(" . trim($lng) . ") )
                                        + sin( radians(" . trim($lat) . ") )
                                        * sin( radians( lat ) ) ) ) <= " . $radius . "
                                )
                            ) OR ";
                    }

                    $address_fields_to_query = array(
                        '_reference_number',
                        '_address_street',
                        '_address_two',
                        '_address_three',
                        '_address_four',
                        '_address_postcode'
                    );

                    if ( strpos( $location_keyword, ', ' ) !== FALSE )
                    {
                        $address_fields_to_query[] = '_address_concatenated';
                    }

                    $address_fields_to_query = apply_filters( 'propertyhive_address_fields_to_query', $address_fields_to_query );
                            
                    $where .= " ( ";
                    if ( get_option( 'propertyhive_address_keyword_compare', '=' ) == '=' )
                    {
                        $subwhere = array();
                        foreach ( $address_fields_to_query as $address_field )
                        {
                            if ( in_array( $address_field, array('_address_postcode', '_address_concatenated') ) ) { continue; } // ignore postcode and address concat field as they are handled differently afterwards

                            $subwhere[] = " ( mtcustom.meta_key = '" . $address_field . "' AND CAST(mtcustom.meta_value AS CHAR) = '" . esc_sql($location_keyword) . "' ) ";
                        }
                        $where .= implode( " OR ", $subwhere );
                    }
                    else
                    {
                        $subwhere = array();
                        foreach ( $address_fields_to_query as $address_field )
                        {
                            if ( in_array( $address_field, array('_address_postcode', '_address_concatenated') ) ) { continue; } // ignore postcode and address concat field as they are handled differently afterwards

                            $subwhere[] = " ( mtcustom.meta_key = '" . $address_field . "' AND CAST(mtcustom.meta_value AS CHAR) LIKE '%" . esc_sql($location_keyword) . "%' ) ";
                        }
                        $where .= implode( " OR ", $subwhere );
                    }
                    // postcode
                    if ( in_array('_address_postcode', $address_fields_to_query) )
                    {
                        $where .= " OR ";
                        if ( strlen($location_keyword) <= 4 )
                        {
                            $where .= " ( mtcustom.meta_key = '_address_postcode' AND CAST(mtcustom.meta_value AS CHAR) = '" . esc_sql($location_keyword) . "' )";
                            $where .= " OR ";
                            $where .= " ( mtcustom.meta_key = '_address_postcode' AND CAST(mtcustom.meta_value AS CHAR) RLIKE '^" . esc_sql($location_keyword) . "[a-zA-Z]?[ ]' )";
                        }
                        else
                        {
                            $postcode = ph_clean( $location_keyword );

                            if ( preg_match('#^(GIR ?0AA|[A-PR-UWYZ]([0-9]{1,2}|([A-HK-Y][0-9]([0-9ABEHMNPRV-Y])?)|[0-9][A-HJKPS-UW])[0-9][ABD-HJLNP-UW-Z]{2})$#i', $postcode) )
                            {
                                // UK postcode found with no space

                                if ( strlen($postcode) == 5 )
                                {
                                    $first_part = substr($postcode, 0, 2);
                                    $last_part = substr($postcode, 2, 3);

                                    $postcode = $first_part . ' ' . $last_part;
                                }
                                elseif ( strlen($postcode) == 6 )
                                {
                                    $first_part = substr($postcode, 0, 3);
                                    $last_part = substr($postcode, 3, 3);

                                    $postcode = $first_part . ' ' . $last_part;
                                }
                                elseif ( strlen($postcode) == 7 )
                                {
                                    $first_part = substr($postcode, 0, 4);
                                    $last_part = substr($postcode, 4, 3);

                                    $postcode = $first_part . ' ' . $last_part;
                                }
                            }

                            $where .= " ( mtcustom.meta_key = '_address_postcode' AND CAST(mtcustom.meta_value AS CHAR) LIKE '%" . esc_sql($postcode) . "%' )";
                        }
                    }
                    if ( in_array('_address_concatenated', $address_fields_to_query) )
                    {
                        $where .= " OR ( mtcustom.meta_key = '_address_concatenated' AND CAST(mtcustom.meta_value AS CHAR) LIKE '%" . esc_sql($location_keyword) . "%' )";
                    }
                    $where .= " )
                        )
                    ";
                }
            }
        }
        
        return $where;
    }

    public function radial_search_remove_radius_query()
    {
        remove_filter( 'pre_get_posts', array( $this, 'radial_search_location_posts_where' ) );
    }

    /**
     * Add a new settings tab to the Property Hive settings tabs array.
     *
     * @param array $settings_tabs Array of Property Hive setting tabs & their labels, excluding the Subscription tab.
     * @return array $settings_tabs Array of Property Hive setting tabs & their labels, including the Subscription tab.
     */
    public function add_settings_tab( $settings_tabs ) {
        $settings_tabs['radialsearch'] = __( 'Radial Search', 'propertyhive' );
        return $settings_tabs;
    }

    /**
     * Uses the Property Hive options API to save settings.
     *
     * @uses propertyhive_update_options()
     * @uses self::get_settings()
     */
    public function save() {
        global $current_section, $post;

        $existing_propertyhive_radial_search = get_option( 'propertyhive_radial_search', array() );

        $propertyhive_radial_search = array(
            'current_location_enabled' => ( (isset($_POST['current_location_enabled'])) ? $_POST['current_location_enabled'] : '' ),
        );

        $propertyhive_radial_search = array_merge( $existing_propertyhive_radial_search, $propertyhive_radial_search );

        update_option( 'propertyhive_radial_search', $propertyhive_radial_search );
    }

    /**
     * Uses the Property Hive admin fields API to output settings.
     *
     * @uses propertyhive_admin_fields()
     * @uses self::get_settings()
     */
    public function output() {

        global $current_section, $hide_save_button;
        
        propertyhive_admin_fields( self::get_radial_search_settings() );
    }

    /**
     * Get all the main settings for this plugin
     *
     * @return array Array of settings
     */
    public function get_radial_search_settings() {

        global $wpdb;

        $current_settings = get_option( 'propertyhive_radial_search', array() );

        $cache = '';
        $lat_lng_cache_results = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "ph_radial_search_lat_lng_cache ORDER BY location");
        if ( $lat_lng_cache_results )
        {
            $cache .= '<div id="cached" style="display:none; height:150px; overflow:scroll; margin:20px 0 0; border:1px solid #CCC; padding:10px;"><table>
                <tr>
                    <td style="padding:3px 10px; font-weight:700;">Location</td>
                    <td style="padding:3px 10px; font-weight:700;">Latitude</td>
                    <td style="padding:3px 10px; font-weight:700;">Longitude</td>
                </tr>';
            foreach ( $lat_lng_cache_results as $lat_lng_cache )
            {
                $cache .= '<tr>
                    <td style="padding:3px 10px;">' . $lat_lng_cache->location . '</td>
                    <td style="padding:3px 10px;">' . $lat_lng_cache->lat . '</td>
                    <td style="padding:3px 10px;">' . $lat_lng_cache->lng . '</td>
                </tr>';
            }
            $cache .= '</table></div>';
        }

        $cache_html = '<p>When a user searches for a location we convert the address/postcode entered to a latitude and longitude which allows us to then return the relevant properties.</p>';
        $cache_html .= '<p>We cache these results which prevents us having to do the same lookups again and again, thus improving speed of searches, and reducing the need to keep performing the same Geocoding requests.</p>';
        $cache_html .= '<p>We perform this address-to-coordinates operation by using Google\'s Geocoding Service.</p>';
        $cache_html .= '<br><p><a href="" onclick="jQuery(\'#cached\').show(); return false;" class="button button-primary" ' . ( ( $cache == '' ) ? ' disabled title="No cached co-ordinates"' : '' ) . '>View Cache</a> <a href="' . admin_url('admin.php?page=ph-settings&tab=radialsearch') . '&ph_action=delete-latlng-cache" class="button button-primary" ' . ( ( $cache == '' ) ? ' disabled title="No cached co-ordinates"' : '' ) . '>Delete Cache</a></p>';

        $cache_html .= $cache;
        

        $instructions_html = '<p>The easiest way to add a radius search dropdown to search forms is to install our free <a href="https://wp-property-hive.com/addons/template-assistant/" target="_blank">Template Assistant add on</a>. This add on comes with a drag-and-drop search form builder meaning you can drag a \'Radius\' field into the list of active fields, customise the options available and more.</p>';
        $instructions_html .= '<br><p><a href="https://wp-property-hive.com/addons/template-assistant/" target="_blank" class="button button-primary">View Template Assistant Add On</a></p>';

        $troubleshooting_api_key_html = '<p>As mentioned above, we use the Google Geocoding service to convert addresses to co-ordinates. As such for this to work you must have:</p>';
        $troubleshooting_api_key_html .= '<p>a) A Google Maps API key entered. This can be <a href="' . admin_url('admin.php?page=ph-settings&tab=general&section=map') . '">entered here</a>.';
        

        $api_key = get_option('propertyhive_google_maps_geocoding_api_key', '');
        if ( $api_key == '' )
        {
            $api_key = get_option('propertyhive_google_maps_api_key', '');
        }
        if ( $api_key == '' )
        {
            $troubleshooting_api_key_html .= ' - <span style="font-weight:700; color:#900">No API key entered</span>';
        }

        $troubleshooting_api_key_html .= '</p>';
        $troubleshooting_api_key_html .= '<p>b) The API key entered must have the <a href="https://console.cloud.google.com/apis/library/geocoding-backend.googleapis.com" target="_blank">Geocoding Service library</a> enabled</p>';
        
        $troubleshooting_no_lat_lng_html = '<p>Properties must have a latitude and longitude set in order to appear when a radial search takes place.</p>';
        $args = array(
            'post_type' => 'property',
            'nopaging' => true,
            'fields' => 'ids',
            'meta_query' => array(
                array(
                    'key' => '_on_market',
                    'value' => 'yes'
                )
            )
        );
        $property_query = new WP_Query( $args );
        if ( $property_query->have_posts() )
        {
            $properties_without_lat_lng = array();
            while ( $property_query->have_posts() )
            {
                $property_query->the_post();

                if ( get_post_meta(get_the_ID(), '_latitude', TRUE) == '' )
                {
                    $properties_without_lat_lng[] = array('id' => get_the_ID(), 'title' => get_the_title());
                }
                elseif ( get_post_meta(get_the_ID(), '_longitude', TRUE) == '' )
                {
                    $properties_without_lat_lng[] = array('id' => get_the_ID(), 'title' => get_the_title());
                }
            }
            if ( !empty($properties_without_lat_lng) )
            {
                $troubleshooting_no_lat_lng_html .= '<p style="color:#900; font-weight:700;">There are ' . count($properties_without_lat_lng) . ' properties with no co-ordinates present:</p>';
                $troubleshooting_no_lat_lng_html .= '<div style="height:150px; overflow:scroll; margin:10px 0; border:1px solid #CCC; padding:10px;">';
                foreach ( $properties_without_lat_lng as $property )
                {
                    $troubleshooting_no_lat_lng_html .= '<a href="' . get_edit_post_link($property['id']) . '" target="_blank">' . $property['title'] . '</a><br>';
                }
                $troubleshooting_no_lat_lng_html .= '</div>';
            }
            else
            {
                $troubleshooting_no_lat_lng_html .= '<p style="color:#090; font-weight:700;">Good job. All properties have co-ordinates present.</p>';
            }
        }
        wp_reset_postdata();

        $PH_Countries = new PH_Countries();
        $country = $PH_Countries->get_country( get_option( 'propertyhive_default_country', 'GB' ) );

        $settings = array(

            array( 'title' => __( 'Co-ordinate Cache', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'cache' ),

            array(
                'type'      => 'html',
                'title'     => __( 'Co-ordinate Cache', 'propertyhive' ),
                'html'      => $cache_html
            ),

            array( 'type' => 'sectionend', 'id' => 'cache'),

            /*array( 'title' => __( 'Region Biasing', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'bias' ),

            array(
                'type'      => 'html',
                'title'     => __( 'Region Biasing', 'propertyhive' ),
                'html'      => 'When someone searches for a location we make the request to the Geocoding Service and that will return all results found within ' . $country['name'] . '.</p>
                <p>That\'s fine if searching for a postcode or unique town name, but searching for a common street or area name (i.e. High Street or Castle Hill) could produce issues.</p>
                <p>By specifying a region below you can specify the town or county that searches should take place in. For example, if you enter \'Shropshire\' below and someone then searches for \'High Street\', we\'ll only then search for \'High Street\'s that appear within \'Shropshire.</p',
            ),

            array( 'type' => 'sectionend', 'id' => 'bias'),*/

            array( 'title' => __( 'Search Forms', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'search_form' ),

            array(
                'type'      => 'html',
                'title'     => __( 'Adding To Search Forms', 'propertyhive' ),
                'html'      => $instructions_html
            ),

            array(
                'type'      => 'checkbox',
                'id'        => 'current_location_enabled',
                'title'     => __( 'Enable \'Current Location\' Functionality', 'propertyhive' ),
                'desc'      => 'If enabled and you have the Address Keyword search field in your search form, this will allow the user to search based on their current location.',
                'default'   => ( ( isset($current_settings['current_location_enabled']) && $current_settings['current_location_enabled'] == 1 ) ? 'yes' : ''),
            ),

            array( 'type' => 'sectionend', 'id' => 'search_form'),

            array( 'title' => __( 'Troubleshooting', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'troubleshooting' ),

            array(
                'type'      => 'html',
                'title'     => __( 'Missing or Invalid API Key', 'propertyhive' ),
                'html'      => $troubleshooting_api_key_html
            ),

            array(
                'type'      => 'html',
                'title'     => __( 'Properties Without Co-ordinates', 'propertyhive' ),
                'html'      => $troubleshooting_no_lat_lng_html
            ),

            array( 'type' => 'sectionend', 'id' => 'troubleshooting'),
        );
        return apply_filters( 'ph_settings_radial_search_settings', $settings );

    }

}

endif;

/**
 * Returns the main instance of PH_Radial_Search to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return PH_Radial_Search
 */
function PHRS() {
    return PH_Radial_Search::instance();
}

PHRS();

if( is_admin() && file_exists(  dirname( __FILE__ ) . '/propertyhive-radial-search-update.php' ) )
{
    include_once( dirname( __FILE__ ) . '/propertyhive-radial-search-update.php' );
}