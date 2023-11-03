<?php
/**
 * Plugin Name: Property Hive Map Search Add On
 * Plugin Uri: http://wp-property-hive.com/addons/map-search/
 * Description: Add On for Property Hive allowing users to view their search results on a map
 * Version: 1.1.34
 * Author: PropertyHive
 * Author URI: http://wp-property-hive.com
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'PH_Map_Search' ) ) :

final class PH_Map_Search {

    /**
     * @var string
     */
    public $version = '1.1.34';

    /**
     * @var Property Hive The single instance of the class
     */
    protected static $_instance = null;
    
    /**
     * Main Property Hive Map Search Instance
     *
     * Ensures only one instance of Property Hive Map Search is loaded or can be loaded.
     *
     * @static
     * @return Property Hive Map Search - Main instance
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

        $this->id    = 'mapsearch';
        $this->label = __( 'Map Search', 'propertyhive' );

        // Define constants
        $this->define_constants();

        // Include required files
        $this->includes();

        add_action( 'admin_notices', array( $this, 'map_search_error_notices') );

        add_filter( 'propertyhive_settings_tabs_array', array( $this, 'add_settings_tab' ), 19 );
        add_action( 'propertyhive_settings_' . $this->id, array( $this, 'output' ) );
        add_action( 'propertyhive_settings_save_' . $this->id, array( $this, 'save' ) );
        add_action( 'propertyhive_admin_field_map_marker_icon', array( $this, 'map_marker_icon_file_upload' ) );

        $current_settings = get_option( 'propertyhive_map_search', array() );

        if ( isset($current_settings['icon_type']) && $current_settings['icon_type'] == 'custom_per_type' )
        {
            add_filter( 'propertyhive_custom_field_property_type_settings', array( $this, 'add_marker_icon_upload_to_property_type' ), 10, 1 );

            add_filter( 'propertyhive_custom_field_commercial_property_type_settings', array( $this, 'add_marker_icon_upload_to_property_type' ), 10, 1 );

            add_action( 'propertyhive_custom_field_property_type_table_before_header_column', array( $this, 'add_marker_icon_to_custom_field_header_column' ), 10 );
            add_action( 'propertyhive_custom_field_property_type_table_before_row_column', array( $this, 'add_marker_icon_to_custom_field_row_column' ), 10, 2 );

            add_action( 'propertyhive_custom_field_commercial_property_type_table_before_header_column', array( $this, 'add_marker_icon_to_custom_field_header_column' ), 10 );
            add_action( 'propertyhive_custom_field_commercial_property_type_table_before_row_column', array( $this, 'add_marker_icon_to_custom_field_row_column' ), 10, 2 );

            add_action( 'propertyhive_settings_save_customfields', array( $this, 'save_property_type_marker_icon' ) );
        }

        if ( isset($current_settings['icon_type']) && $current_settings['icon_type'] == 'custom_per_availability' )
        {
            add_filter( 'propertyhive_custom_field_availability_settings', array( $this, 'add_marker_icon_upload_to_availability' ), 10, 1 );

            add_action( 'propertyhive_custom_field_availability_table_before_header_column', array( $this, 'add_marker_icon_to_custom_field_header_column' ), 10 );
            add_action( 'propertyhive_custom_field_availability_table_before_row_column', array( $this, 'add_marker_icon_to_custom_field_row_column' ), 10, 2 );

            add_action( 'propertyhive_settings_save_customfields', array( $this, 'save_availability_marker_icon' ) );
        }

        add_action( 'wp_enqueue_scripts', array( $this, 'load_map_search_scripts' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'load_map_search_styles' ) );

        add_filter( 'propertyhive_google_maps_api_params', array( $this, 'google_maps_api_params' ) );

        // Displaying map view as separate view
        if ( isset($current_settings['format']) && ( $current_settings['format'] == 'view' || $current_settings['format'] == 'split' ) )
        {
            add_action( 'pre_get_posts', array( $this, 'do_map_actions' ) );
        }

        add_action( 'wp_ajax_propertyhive_load_map_properties', array( $this, 'ajax_propertyhive_load_map_properties' ) );
        add_action( 'wp_ajax_nopriv_propertyhive_load_map_properties', array( $this, 'ajax_propertyhive_load_map_properties' ) );

        add_shortcode( 'propertyhive_map_search', array( $this, 'propertyhive_map_search_shortcode' ) );

        add_filter( "plugin_action_links_" . plugin_basename( __FILE__ ), array( $this, 'plugin_add_settings_link' ) );
    }

    public function plugin_add_settings_link( $links )
    {
        $settings_link = '<a href="' . admin_url('admin.php?page=ph-settings&tab=mapsearch') . '">' . __( 'Settings' ) . '</a>';
        array_push( $links, $settings_link );
        return $links;
    }

    public function do_map_actions()
    {
        global $wp_query;

        $current_settings = get_option( 'propertyhive_map_search', array() );

        if ( isset($_GET['pgp']) && $_GET['pgp'] != '' )
        {
            // We have a polygon. Amend MySQL query.
            add_filter( 'posts_where' , array( $this, 'where_properties_in_polygon' ), 1, 2 );
        }

        if ( isset($current_settings['format']) && $current_settings['format'] == 'view' )
        {
            // Add views before search results loop
            add_action( 'propertyhive_before_search_results_loop', array( $this, 'propertyhive_results_views' ), 25 );
        }

        if ( 
            ( isset($_GET['view']) && $_GET['view'] == 'map' )
            ||
            ( !isset($_GET['view']) && isset($current_settings['format']) && $current_settings['format'] == 'split' )
        )
        {
            if ( isset($current_settings['format']) && $current_settings['format'] == 'view' )
            {
                // Prevent the main list of results showing if we're on map view
                add_filter( 'propertyhive_show_results', array( $this, 'propertyhive_map_search_return_false' ), 1 );

                // Hide pagination on map view
                add_filter( 'propertyhive_pagination_args', array( $this, 'hide_pagination_on_map_view' ), 1 );

                // Hide order by dropdown on map view
                add_filter( 'propertyhive_results_orderby', array( $this, 'propertyhive_map_search_return_array' ), 1 );
            }
            if ( isset($current_settings['format']) && $current_settings['format'] == 'split' )
            {
                // Output the map
                add_action( 'propertyhive_before_search_results_loop', array( $this, 'propertyhive_half_map_open' ), 100 );
                add_action( 'propertyhive_before_search_results_loop', array( $this, 'propertyhive_half_map_close_list_open' ), 105 );
                add_action( 'propertyhive_after_search_results_loop', array( $this, 'propertyhive_half_list_close' ), 100 );

                add_action( 'wp_head', array( $this, 'propertyhive_half_css' ), 1 );
            }
            if ( isset($current_settings['format']) && ( $current_settings['format'] == 'view' || $current_settings['format'] == 'split' ) )
            {
                // Output the map
                add_action( 'propertyhive_before_search_results_loop', array( $this, 'propertyhive_output_map_view' ), 100 );
            }
        }
    }

    public function propertyhive_half_css()
    {
        echo '<style type="text/css">
            .half-map-view { float:right; width:49% }
            .half-list-view { float:left; width:49% }
        </style>';
    }

    public function propertyhive_half_map_open()
    {
        echo '<div style="clear:both"></div><div class="half-map-view">';
    }

    public function propertyhive_half_map_close_list_open()
    {
        echo '</div><div class="half-list-view">';
    }

    public function propertyhive_half_list_close()
    {
        echo '</div><div style="clear:both"></div>';
    }

    public function propertyhive_map_search_return_false()
    {
        return false;
    }

    public function propertyhive_map_search_return_array()
    {
        return array();
    }

    public function where_properties_in_polygon( $where, $query )
    {
        global $wpdb;

        if ( isset($query->query['post_type']) && $query->query['post_type'] == 'property')
        {
            $polygon_points = $this->decode_polygon( $_GET['pgp'] );

            $where .= " AND 
            ST_CONTAINS(
                ST_GEOMFROMTEXT('POLYGON((" . implode(", ", $polygon_points) . ", " . $polygon_points[0] . "))'), 
                ST_GEOMFROMTEXT(
                    CONCAT(
                        'POINT(', 
                        COALESCE((SELECT meta_value FROM $wpdb->postmeta WHERE $wpdb->postmeta.meta_key='_latitude' AND $wpdb->postmeta.meta_value != '' AND $wpdb->postmeta.meta_value != 0 AND $wpdb->postmeta.post_id = $wpdb->posts.ID LIMIT 1), '0'),
                        ' ',
                        COALESCE((SELECT meta_value FROM $wpdb->postmeta WHERE $wpdb->postmeta.meta_key='_longitude' AND $wpdb->postmeta.meta_value != '' AND $wpdb->postmeta.meta_value != 0 AND $wpdb->postmeta.post_id = $wpdb->posts.ID LIMIT 1), '0'),
                        ')'
                    )
                )
            )";

        }

        return $where;
    }

    public function hide_pagination_on_map_view($args)
    {
        $args['total'] = 0;
        return $args;
    }

    public function add_marker_icon_to_custom_field_header_column()
    {
        echo '<th class="map-icon" style="width:50px">Map Icon</th>';
    }

    public function add_marker_icon_to_custom_field_row_column( $term_id, $parent_term_id = '' )
    {
        $current_settings = get_option( 'propertyhive_map_search', array() );

        echo '<td class="map-icon">';
        if ( isset($current_settings['map_marker_icons'][$term_id]) && $current_settings['map_marker_icons'][$term_id] != '' )
        {
            echo '<img src="' . wp_get_attachment_url( $current_settings['map_marker_icons'][$term_id] ) . '" style="max-width:100%;" alt="Map marker icon">';
        }
        elseif ( isset($current_settings['map_marker_icons'][$parent_term_id]) && $current_settings['map_marker_icons'][$parent_term_id] != '' )
        {
            echo '<img src="' . wp_get_attachment_url( $current_settings['map_marker_icons'][$parent_term_id] ) . '" style="max-width:100%;" alt="Map marker icon">';
        }
        echo '</td>';
    }

    private function build_ajax_json_property_result( $post_id )
    {
        $property = new PH_Property( $post_id );

        if ( $property->_latitude == '' || $property->_latitude == '0' || $property->_longitude == '' || $property->_longitude == '0' )
        {
            return false;
        }

        $term = 'property_type';
        if ( $property->_department == 'commercial' )
        {
            $term = 'commercial_property_type';
        }
        $property_type_id = '';
        $property_type_name = '';
        $term_list = wp_get_post_terms($post_id, $term, array("fields" => "all"));
        if ( !is_wp_error($term_list) && is_array($term_list) && !empty($term_list) )
        {
            $property_type_id = $term_list[0]->term_id;
            $property_type_name = $term_list[0]->name;
        }

        $availability_id = '';
        $term_list = wp_get_post_terms($post_id, 'availability', array("fields" => "all"));
        if ( !is_wp_error($term_list) && is_array($term_list) && !empty($term_list) )
        {
            $availability_id = $term_list[0]->term_id;
        }

        $return = array(
            'id' => $post_id,
            'department' => $property->_department,
            'address' => get_the_title( $post_id ),
            'price' => $property->get_formatted_price(),
            'price_qualifier' => $property->price_qualifier,
            'bedrooms' => $property->_bedrooms,
            'availability_id' => $availability_id,
            'availability' => $property->availability,
            'image' => $property->get_main_photo_src(),
            'type_id' => $property_type_id,
            'type_name' => $property_type_name,
            'link' => get_permalink( $post_id ),
            'latitude' => $property->_latitude,
            'longitude' => $property->_longitude
        );

        $return = apply_filters( 'propertyhive_map_property_json', $return, $post_id, $property );

        return $return;
    }

    public function ajax_propertyhive_load_map_properties()
    {
        global $wpdb, $post;

        header( 'Content-Type: application/json; charset=utf-8' );

        $current_settings = get_option( 'propertyhive_map_search', array() );

        $return = array();

        $query = '';
        $over_limit = false;
        $total_results = -1;

        if ( isset($_POST['map_property_query']) && $_POST['map_property_query'] != '' && base64_decode($_POST['map_property_query']) !== FALSE )
        {
            $query = base64_decode($_POST['map_property_query']);

            if ( isset($current_settings['marker_limit']) && !empty($current_settings['marker_limit']) )
            {
                $results = $wpdb->get_results( $query );
                $total_results = count($results);

                if ( $total_results > (int)$current_settings['marker_limit'] )
                {
                    $over_limit = true;
                }

                if (preg_match('/.*(\bORDER\s+BY\s+.*)/s', $query, $matches, PREG_OFFSET_CAPTURE)) 
                {
                    $order_by = $matches[1][0];
                    $query = str_replace($order_by, 'ORDER BY RAND()', $query);
                }

                // get position of WHERE OUTSODE OF ANY BRACKETS
                $explode_query = explode(" ", $query);
                $current_pos = 0;
                $limit_pos = 0;
                $open_brackets = 0;
                foreach ( $explode_query as $word )
                {
                    if (strpos($word, '(') !== FALSE)
                    {
                        // entering a bracket
                        ++$open_brackets;
                    }
                    if (strpos($word, ')') !== FALSE)
                    {
                        // entering a bracket
                        --$open_brackets;
                    }
                    if ( $word == 'LIMIT' && $open_brackets == 0 )
                    {
                        $limit_pos = $current_pos;
                        break;
                    }
                    $current_pos = $current_pos + strlen($word);
                    ++$current_pos; // add pos for space
                }

                if ( $limit_pos != 0) 
                {
                    $query = substr($query, $where_pos + 6);
                    $query .= (int)$current_settings['marker_limit'];
                }
                else
                {
                    // Limit not found
                    $query .= ' LIMIT ' . $current_settings['marker_limit'];
                }
            }

            

            if ( 
                isset($current_settings['refresh_on_bounds_changed']) && $current_settings['refresh_on_bounds_changed'] == '1' &&
                isset($_POST['ne_lat']) && isset($_POST['ne_lng']) && isset($_POST['sw_lat']) && isset($_POST['sw_lng']) && 
                $_POST['ne_lat'] != '' && $_POST['ne_lng'] != '' && $_POST['sw_lat'] != '' && $_POST['sw_lng'] != ''
            )
            {
                // get position of WHERE OUTSODE OF ANY BRACKETS
                $explode_query = explode(" ", $query);
                $current_pos = 0;
                $where_pos = 0;
                $open_brackets = 0;
                foreach ( $explode_query as $word )
                {
                    if (strpos($word, '(') !== FALSE)
                    {
                        // entering a bracket
                        ++$open_brackets;
                    }
                    if (strpos($word, ')') !== FALSE)
                    {
                        // entering a bracket
                        --$open_brackets;
                    }
                    if ( $word == 'WHERE' && $open_brackets == 0 )
                    {
                        $where_pos = $current_pos;
                        break;
                    }
                    $current_pos = $current_pos + strlen($word);
                    ++$current_pos; // add pos for space
                }

                // add where to query
                $where_to_add = " 
                ( phms1.meta_key = '_latitude' AND CAST(phms1.meta_value AS DECIMAL(10,5)) BETWEEN '" . $_POST['sw_lat'] . "' AND '" . $_POST['ne_lat'] . "' )
                AND
                ( phms2.meta_key = '_longitude' AND CAST(phms2.meta_value AS DECIMAL(10,5)) BETWEEN '" . $_POST['sw_lng'] . "' AND '" . $_POST['ne_lng'] . "' ) AND
                ";
                $query = substr_replace($query, $where_to_add, $where_pos + 5, 0);

                // add joins to query
                $joins_to_add = ' INNER JOIN ' . $wpdb->postmeta . ' AS phms1 ON ( ' . $wpdb->posts . '.ID = phms1.post_id ) 
                INNER JOIN ' . $wpdb->postmeta . ' AS phms2 ON ( ' . $wpdb->posts . '.ID = phms2.post_id ) ';
                $query = substr_replace($query, $joins_to_add, $where_pos - 1, 0);
            }
        }

        if ( $query == '' )
        {
            // No query passed. Default to just return all on market properties
            $args = array(
                'post_type' => 'property',
                'fields' => 'ids',
                'nopaging' => true,
                'post_status' => 'publish',
                'meta_query' => array(
                    array(
                        'key' => '_on_market',
                        'value' => 'yes'
                    )
                ),
                'tax_query' => array(),
            );

            if ( 
                isset($current_settings['refresh_on_bounds_changed']) && $current_settings['refresh_on_bounds_changed'] == '1' &&
                isset($_POST['ne_lat']) && isset($_POST['ne_lng']) && isset($_POST['sw_lat']) && isset($_POST['sw_lng']) && 
                $_POST['ne_lat'] != '' && $_POST['ne_lng'] != '' && $_POST['sw_lat'] != '' && $_POST['sw_lng'] != ''
            )
            {
                $args['meta_query'][] = array(
                    'key' => '_latitude',
                    'value'   => array( $_POST['sw_lat'], $_POST['ne_lat'] ),
                    'type'    => 'DECIMAL(10,5)',
                    'compare' => 'BETWEEN',
                );
                $args['meta_query'][] = array(
                    'key' => '_longitude',
                    'value'   => array( $_POST['sw_lng'], $_POST['ne_lng'] ),
                    'type'    => 'DECIMAL(10,5)',
                    'compare' => 'BETWEEN',
                );
            }

            $atts = isset($_POST['atts']) ? $_POST['atts'] : array();

            if ( isset($atts['department']) && $atts['department'] != '' )
            {
                $departments = ph_get_departments();

                $department = in_array( $atts['department'], array_keys($departments) ) ? $atts['department'] : '';

                $args['meta_query'][] = array(
                    'key' => '_department',
                    'value' => $department
                );
            }

            if ( isset($atts['office_id']) && $atts['office_id'] != '' )
            {
                $args['meta_query'][] = array(
                    'key' => '_office_id',
                    'value' => (int)$atts['office_id']
                );
            }

            if ( isset($atts['negotiator_id']) && $atts['negotiator_id'] != '' )
            {
                $args['meta_query'][] = array(
                    'key' => '_negotiator_id',
                    'value' => (int)$atts['negotiator_id']
                );
            }

            if ( isset($atts['property_type_id']) && $atts['property_type_id'] != '' )
            {
                $args['tax_query'][] = array(
                    'taxonomy' => 'property_type',
                    'terms' => ph_clean( explode(",", $atts['property_type_id']) ),
                );
            }

            if ( isset($atts['availability_id']) && $atts['availability_id'] != '' )
            {
                $args['tax_query'][] = array(
                    'taxonomy' => 'availability',
                    'terms' => ph_clean( explode(",", $atts['availability_id']) ),
                );
            }

            foreach ( $atts as $key => $value )
            {
                if ( isset($atts['department']) && $atts['department'] == 'commercial' && $key == 'property_type_id' ) 
                { 

                }

                if ( taxonomy_exists($key) && isset( $atts[$key] ) && !empty($atts[$key]) )
                {
                    $args['meta_query'][] = array(
                        'taxonomy'  => $key,
                        'terms' => ph_clean( (is_array($value)) ? $value : array( $value ) )
                    );
                }
            }

            $args = apply_filters( 'propertyhive_map_search_ajax_query_args', $args, $_POST );

            $property_query = new WP_Query($args);

            $total_results = $property_query->found_posts;

            if ( isset($current_settings['marker_limit']) && !empty($current_settings['marker_limit']) )
            {
                if ( $total_results > (int)$current_settings['marker_limit'] )
                {
                    $over_limit = true;
                }

                $args['nopaging'] = false;
                $args['posts_per_page'] = (int)$current_settings['marker_limit'];

                $property_query = new WP_Query($args);
            }

            $query = $property_query->request;
        }

        // We have a query. Execute it and return properties
        $results = $wpdb->get_results( $query );

        if ( isset($current_settings['marker_limit']) && !empty($current_settings['marker_limit']) )
        {

        }
        else
        {
            $total_results = count($results);
        }

        if ($results)
        {
            foreach ( $results as $post )
            {
                $property_array = $this->build_ajax_json_property_result( get_the_ID() );

                if ($property_array !== FALSE)
                {
                    $return[] = $property_array;
                }
            }
        }

        echo json_encode(array('total' => $total_results, 'over_limit' => $over_limit, 'properties' => $return));

        wp_die(); // this is required to terminate immediately and return a proper response
    }

    public function google_maps_api_params( $params = array() )
    {
        $current_settings = get_option( 'propertyhive_map_search', array() );

        $api_key = get_option('propertyhive_google_maps_api_key', '');
        if ( $api_key != '' )
        {
            $params['key'] = $api_key;
        }

        if ( isset($current_settings['draw_a_search_enabled']) && $current_settings['draw_a_search_enabled'] == '1' )
        {
            if ( !isset($params['libraries']) )
            {
                $params['libraries'] = array();
            }
            $params['libraries'][] = 'drawing';
            $params['libraries'][] = 'geometry';
        }

        return $params;
    }

    public function load_map_search_scripts() {

        $current_settings = get_option( 'propertyhive_map_search', array() );

        $assets_path = str_replace( array( 'http:', 'https:' ), '', untrailingslashit( plugins_url( '/', __FILE__ ) ) ) . '/assets/';

        if ( get_option('propertyhive_maps_provider') == 'osm' )
        {
            // Include leaflet file from main Property Hive plugin
            wp_register_script('leaflet', PH()->plugin_url() . '/assets/js/leaflet/leaflet.js', array(), '1.7.1', false);

            wp_register_script('leaflet-draw', $assets_path . 'js/leaflet/leaflet.draw.js', array(), '1.0.3', false);

            wp_register_script('leaflet-encoded', $assets_path . 'js/leaflet/encoded.js', array(), '0.0.9', false);

            if ( isset($current_settings['marker_clustering_enabled']) && $current_settings['marker_clustering_enabled'] == '1' )
            {
                wp_register_script( 
                    'ph-marker-clusterer', 
                    $assets_path . 'js/leaflet/leaflet.markercluster.js', 
                    array(), 
                    '1.4.1',
                    true
                );
            }

            wp_register_script( 
                'ph-map-search', 
                $assets_path . 'js/ph-map-search-osm.js', 
                array(), 
                PH_MAP_SEARCH_VERSION,
                true
            );
        }
        else
        {
            $params = array();
            $params = apply_filters( 'propertyhive_google_maps_api_params', $params );

            if ( isset($params['libraries']) && is_array($params['libraries']) && !empty($params['libraries']) ) { $params['libraries'] = join(",", $params['libraries']); }

            wp_register_script( 
                'googlemaps', 
                '//maps.googleapis.com/maps/api/js?' . http_build_query($params), 
                array(), 
                '3', 
                true 
            );

            wp_register_script( 
                'ph-map-search', 
                $assets_path . 'js/ph-map-search.js', 
                array(), 
                PH_MAP_SEARCH_VERSION,
                true
            );

            if ( isset($current_settings['marker_clustering_enabled']) && $current_settings['marker_clustering_enabled'] == '1' )
            {
                wp_register_script( 
                    'ph-marker-clusterer', 
                    $assets_path . 'js/markerclusterer.js', 
                    array(), 
                    PH_MAP_SEARCH_VERSION,
                    true
                );
            }
        }
    }

    public function load_map_search_styles() {

        $current_settings = get_option( 'propertyhive_map_search', array() );

        $assets_path = str_replace( array( 'http:', 'https:' ), '', untrailingslashit( plugins_url( '/', __FILE__ ) ) ) . '/assets/';

        if ( get_option('propertyhive_maps_provider') == 'osm' )
        {
            // Include leaflet file from main Property Hive plugin
            wp_register_style('leaflet', PH()->plugin_url() . '/assets/js/leaflet/leaflet.css', array(), '1.7.1');

            wp_register_style('leaflet-draw', $assets_path . 'js/leaflet/leaflet.draw.css', array(), '1.0.3');

            if ( isset($current_settings['marker_clustering_enabled']) && $current_settings['marker_clustering_enabled'] == '1' )
            {
                wp_register_style('leaflet-clustering', $assets_path . 'js/leaflet/MarkerCluster.css', array(), '1.4.1');
                wp_register_style('leaflet-clustering-default', $assets_path . 'js/leaflet/MarkerCluster.Default.css', array(), '1.4.1');
            }
        }

        wp_register_style( 
            'ph-map-search', 
            $assets_path . 'css/ph-map-search.css', 
            array(), 
            PH_MAP_SEARCH_VERSION
        );
        
    }

    /**
     * Outputs the map in it's own view
     */
    public function propertyhive_output_map_view()
    {
        global $wp_query, $wpdb;

        $current_settings = get_option( 'propertyhive_map_search', array() );

        if ( isset($current_settings['format']) && $current_settings['format'] == 'view' )
        {
            $wp_query->set( 'paged', 1 );
            $wp_query->set( 'nopaging', true );
        }

        $query = $wp_query->request;

        if ( isset($current_settings['format']) && $current_settings['format'] == 'view' )
        {
            // Remove limit
            $last_limit_pos = strrpos(strtolower($query), "limit");
            if ($last_limit_pos !== FALSE)
            {
                // We found a limit
                $query = substr($query, 0, $last_limit_pos - 1); // -1 because strrpos return starts at zero
            }
        }

        $additional_attributes = '';
        
        if ( isset($current_settings['disable_scrollwheel']) && $current_settings['disable_scrollwheel'] == '1' )
        {
            $additional_attributes .= ' scrollwheel=false';
        }

        echo do_shortcode('[propertyhive_map_search format="view" query="' . base64_encode(str_replace($wpdb->placeholder_escape(), "%", $query)) . '"' . $additional_attributes . ']');
    }

    public function propertyhive_map_search_shortcode( $atts )
    {
        global $wp_query, $post;

        $current_settings = get_option( 'propertyhive_map_search', array() );
        
        $atts = shortcode_atts( apply_filters( 'propertyhive_map_search_shortcode_atts', array(
            'query' => '',
            'department' => '', // residential-sales / residential-lettings / commercial
            'availability_id' => '',
            'property_type_id' => '',
            'office_id' => '',
            'negotiator_id' => '',
            'scrollwheel' => true,
            'center_lat' => '',
            'center_lng' => '',
        ) ), $atts );

        $override_center = false;

        if ( $atts['center_lat'] != '' && $atts['center_lng'] != '' )
        {
            $map_center_lat = $atts['center_lat'];
            $map_center_lng = $atts['center_lng'];

            $override_center = true;
        }
        else
        {
            $map_center_lat = '37.4419';
            $map_center_lng = '-122.1419';

            // Get primary office lat/lng
            $args = array(
                'post_type' => 'office',
                'posts_per_page' => 1,
                'meta_query' => array(
                    array(
                        'key' => 'primary',
                        'value' => '1'
                    )
                )
            );

            $office_query = new WP_Query( $args );

            if ( $office_query->have_posts() )
            {
                while ( $office_query->have_posts() )
                {
                    $office_query->the_post();

                    $lat = get_post_meta( $post->ID, '_office_latitude', TRUE );
                    $lng = get_post_meta( $post->ID, '_office_longitude', TRUE );

                    if ( $lat != '' && $lat != 0 && is_numeric($lat) && $lng != '' && $lng != 0 && is_numeric($lng) )
                    {
                        $map_center_lat = $lat;
                        $map_center_lng = $lng;
                    }
                }
            }
            wp_reset_postdata();

            if ( isset($current_settings['default_center']) && $current_settings['default_center'] != '' )
            {
                $default_center = explode(",", $current_settings['default_center']);
                if (count($default_center) == 2)
                {
                    $map_center_lat = $default_center[0];
                    $map_center_lng = $default_center[1];

                    $override_center = true;
                }
            }

            // if a location is being searched, center on that location
            if ( isset($_GET['draw']) && $_GET['draw'] == '1' && isset($_GET['address_keyword']) && !empty($_GET['address_keyword']) )
            {
                $lat =  '';
                $lng = '';

                if ( class_exists('PH_Radial_Search') )
                {
                    // use existing functionality from Radial search add on
                    $lat_lng = PHRS()->get_cached_lat_long_or_cache_new( ph_clean($_GET['address_keyword']) );

                    $lat = $lat_lng[0];
                    $lng = $lat_lng[1];
                }

                if ( empty($lat) )
                {
                    $location = ph_clean($_GET['address_keyword']);

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

                if ( !empty($lat) )
                {
                    $map_center_lat = $lat;
                    $map_center_lng = $lng;
                }
            }
        }

        wp_enqueue_style( 'ph-map-search' );

        if ( get_option('propertyhive_maps_provider') == 'osm' )
        {
            wp_enqueue_style( 'leaflet' );
            wp_enqueue_script( 'leaflet' );

            wp_enqueue_style( 'leaflet-draw' );
            wp_enqueue_script( 'leaflet-draw' );

            wp_enqueue_script( 'leaflet-encoded' );
        }
        else
        {
            wp_enqueue_script( 'googlemaps' );
        }

        if ( isset($current_settings['marker_clustering_enabled']) && $current_settings['marker_clustering_enabled'] == '1' )
        {
            if ( get_option('propertyhive_maps_provider') == 'osm' )
            {
                wp_enqueue_style( 'leaflet-clustering' );
                wp_enqueue_style( 'leaflet-clustering-default' );
            }
            wp_enqueue_script( 'ph-marker-clusterer' );
        }

        wp_enqueue_script( 'ph-map-search' );

        // in JavaScript, object properties are accessed as ajax_object.ajax_url, ajax_object.we_value
        wp_localize_script( 'ph-map-search', 'ajax_object', array( 
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'infowindow_html' => apply_filters( 'propertyhive_map_infowindow_html', "<div class=\"property\"><div class=\"image\"><a href=\"' + property.link + '\"><img src=\"' + property.image + '\" alt=\"' + property.address + '\"></a></div><div class=\"details\"><div class=\"address\"><a href=\"' + property.link + '\">' + property.address + '</a></div><div class=\"price\">' + property.price + ' <span class=\"price-qualifier\">' + property.price_qualifier + '</span></div><div class=\"summary\">' + summary_html + '</div></div><div style=\"clear:both\"></div></div>" ), 
            'atts' => $atts,
            'format' => $current_settings['format'],
            'draw_options' => apply_filters( 'propertyhive_map_search_draw_options', array(
                'fill_color' => '#FFF',
                'fill_opacity' => 0.4,
                'stroke_color' => '#444',
                'stroke_opacity' => 1,
                'stroke_weight' => 3,
            ) ),
        ) );

        ob_start();

        echo '<script>
            var map_center_lat = ' . $map_center_lat . ';
            var map_center_lng = ' . $map_center_lng . ';
            var map_property_query = \'' . $atts['query'] . '\';
            var scrollwheel = ' . ( ( $atts['scrollwheel'] === false || $atts['scrollwheel'] === 'false' ) ? 'false' : 'true' ) . ';
            var draw_a_search_enabled = ' . ( ( isset($current_settings['draw_a_search_enabled']) && $current_settings['draw_a_search_enabled'] == '1' ) ? 'true' : 'false' ) . ';
            var draw_mode = ' . ( ( isset($current_settings['draw_a_search_enabled']) && $current_settings['draw_a_search_enabled'] == '1' && isset($_GET['draw']) && $_GET['draw'] == '1' ) ? 'true' : 'false' ) . ';
            var pgp = \'' . ( ( isset($_GET['pgp']) ) ? $_GET['pgp'] : '' ) . '\';
            var map_style_js = ' . ( ( isset($current_settings['style_js']) && trim($current_settings['style_js']) != '' ) ? $current_settings['style_js'] : '[]') . ';
            var icon_type = \'' . ( ( isset($current_settings['icon_type']) ) ? $current_settings['icon_type'] : '' ) . '\';
            var marker_clustering_enabled = ' . ( ( isset($current_settings['marker_clustering_enabled']) && $current_settings['marker_clustering_enabled'] == '1' ) ? 'true' : 'false' ) . ';
            var show_transit_layer = ' . ( ( isset($current_settings['show_transit_layer']) && $current_settings['show_transit_layer'] == '1' ) ? 'true' : 'false' ) . ';
            var refresh_on_bounds_changed = ' . ( ( isset($current_settings['refresh_on_bounds_changed']) && $current_settings['refresh_on_bounds_changed'] == '1' ) ? 'true' : 'false' ) . ';
            var default_zoom_level = ' . ( ( !isset($_REQUEST['pgp']) && isset($current_settings['default_zoom_level']) && $current_settings['default_zoom_level'] != '' ) ? $current_settings['default_zoom_level'] : 'false' ) . ';
            var override_center = ' . ( ( !isset($_REQUEST['pgp']) && $override_center ) ? 'true' : 'false' ) . ';
            ';
        if ( isset($current_settings['marker_clustering_enabled']) && $current_settings['marker_clustering_enabled'] == '1' )
        {
            $assets_path = str_replace( array( 'http:', 'https:' ), '', untrailingslashit( plugins_url( '/', __FILE__ ) ) ) . '/assets/';
            echo 'var marker_clustering_assets_path = \'' . $assets_path . 'img/m\';
            ';
        }
        echo 'var marker_icon_anchor = \'' . ( isset($current_settings['custom_icon_anchor_position']) ? $current_settings['custom_icon_anchor_position'] : '' ) . '\';';
        if ( isset($current_settings['icon_type']) && $current_settings['icon_type'] == 'custom_single' && isset($current_settings['custom_icon_attachment_id']) && $current_settings['custom_icon_attachment_id'] != '' )
        {
            $marker_icon_url = wp_get_attachment_url( $current_settings['custom_icon_attachment_id'] );
            if ( $marker_icon_url !== FALSE )
            {
                echo 'var marker_icon = \'' . $marker_icon_url . '\';';

                if ( get_option('propertyhive_maps_provider') == 'osm' )
                {
                    $size = getimagesize( get_attached_file(  $current_settings['custom_icon_attachment_id'] ) );
                    if ( $size !== FALSE && !empty($size) )
                    {
                        echo '
                        var marker_icon_width = ' . $size[0] . ';
                        var marker_icon_height = ' . $size[1] . ';';
                    }
                }
                else
                {
                    if ( isset($current_settings['custom_icon_anchor_position']) && $current_settings['custom_icon_anchor_position'] == 'center' )
                    {
                        $size = getimagesize( get_attached_file(  $current_settings['custom_icon_attachment_id'] ) );
                        if ( $size !== FALSE && !empty($size) )
                        {
                            echo 'var marker_icon_anchor_left = ' . floor( $size[0] / 2 ) . ';
                            var marker_icon_anchor_top = ' . floor( $size[1] / 2 ) . ';';
                        }
                    }
                }
            }
        }
        if ( isset($current_settings['icon_type']) && $current_settings['icon_type'] == 'custom_per_department' )
        {
            $anchors_output = false;
            if ( isset($current_settings['custom_icon_residential_sales_attachment_id']) && $current_settings['custom_icon_residential_sales_attachment_id'] != '' )
            {
                $marker_icon_url = wp_get_attachment_url( $current_settings['custom_icon_residential_sales_attachment_id'] );
                if ( $marker_icon_url !== FALSE )
                {
                    echo 'var marker_icon_residential_sales = \'' . $marker_icon_url . '\';';

                    if ( get_option('propertyhive_maps_provider') == 'osm' )
                    {
                        $size = getimagesize( get_attached_file(  $current_settings['custom_icon_residential_sales_attachment_id'] ) );
                        if ( $size !== FALSE && !empty($size) )
                        {
                            echo '
                            var marker_icon_residential_sales_width = ' . $size[0] . ';
                            var marker_icon_residential_sales_height = ' . $size[1] . ';';
                        }
                    }
                    else
                    {
                        if ( !$anchors_output && isset($current_settings['custom_icon_anchor_position']) && $current_settings['custom_icon_anchor_position'] == 'center' )
                        {
                            $size = getimagesize( get_attached_file(  $current_settings['custom_icon_attachment_id'] ) );
                            if ( $size !== FALSE && !empty($size) )
                            {
                                echo 'var marker_icon_anchor_left = ' . floor( $size[0] / 2 ) . ';
                                var marker_icon_anchor_top = ' . floor( $size[1] / 2 ) . ';';

                                $anchors_output = true;
                            }
                        }
                    }
                }
            }
            if ( isset($current_settings['custom_icon_residential_lettings_attachment_id']) && $current_settings['custom_icon_residential_lettings_attachment_id'] != '' )
            {
                $marker_icon_url = wp_get_attachment_url( $current_settings['custom_icon_residential_lettings_attachment_id'] );
                if ( $marker_icon_url !== FALSE )
                {
                    echo 'var marker_icon_residential_lettings = \'' . $marker_icon_url . '\';';

                    if ( get_option('propertyhive_maps_provider') == 'osm' )
                    {
                        $size = getimagesize( get_attached_file(  $current_settings['custom_icon_residential_lettings_attachment_id'] ) );
                        if ( $size !== FALSE && !empty($size) )
                        {
                            echo '
                            var marker_icon_residential_lettings_width = ' . $size[0] . ';
                            var marker_icon_residential_lettings_height = ' . $size[1] . ';';
                        }
                    }
                    else
                    {
                        if ( !$anchors_output && isset($current_settings['custom_icon_anchor_position']) && $current_settings['custom_icon_anchor_position'] == 'center' )
                        {
                            $size = getimagesize( get_attached_file(  $current_settings['custom_icon_attachment_id'] ) );
                            if ( $size !== FALSE && !empty($size) )
                            {
                                echo 'var marker_icon_anchor_left = ' . floor( $size[0] / 2 ) . ';
                                var marker_icon_anchor_top = ' . floor( $size[1] / 2 ) . ';';

                                $anchors_output = true;
                            }
                        }
                    }
                }
            }
            if ( isset($current_settings['custom_icon_commercial_attachment_id']) && $current_settings['custom_icon_commercial_attachment_id'] != '' )
            {
                $marker_icon_url = wp_get_attachment_url( $current_settings['custom_icon_commercial_attachment_id'] );
                if ( $marker_icon_url !== FALSE )
                {
                    echo 'var marker_icon_commercial = \'' . $marker_icon_url . '\';';

                    if ( get_option('propertyhive_maps_provider') == 'osm' )
                    {
                        $size = getimagesize( get_attached_file(  $current_settings['custom_icon_commercial_attachment_id'] ) );
                        if ( $size !== FALSE && !empty($size) )
                        {
                            echo '
                            var marker_icon_commercial_width = ' . $size[0] . ';
                            var marker_icon_commercial_height = ' . $size[1] . ';';
                        }
                    }
                    else
                    {
                        if ( !$anchors_output && isset($current_settings['custom_icon_anchor_position']) && $current_settings['custom_icon_anchor_position'] == 'center' )
                        {
                            $size = getimagesize( get_attached_file(  $current_settings['custom_icon_attachment_id'] ) );
                            if ( $size !== FALSE && !empty($size) )
                            {
                                echo 'var marker_icon_anchor_left = ' . floor( $size[0] / 2 ) . ';
                                var marker_icon_anchor_top = ' . floor( $size[1] / 2 ) . ';';

                                $anchors_output = true;
                            }
                        }
                    }
                }
            }
        }
        if ( isset($current_settings['icon_type']) && $current_settings['icon_type'] == 'custom_per_type' && isset($current_settings['map_marker_icons']) && !empty($current_settings['map_marker_icons']) )
        {
            $anchors_output = false;

            $marker_icons = array();
            $args = array(
                'hide_empty' => false,
                'parent' => 0
            );
            $terms = get_terms( 'property_type', $args );
            
            if ( !empty( $terms ) && !is_wp_error( $terms ) )
            {
                foreach ($terms as $term)
                {
                    if ( isset($current_settings['map_marker_icons'][$term->term_id]) && $current_settings['map_marker_icons'][$term->term_id] != '' )
                    {
                        $marker_icons[$term->term_id] = wp_get_attachment_url( $current_settings['map_marker_icons'][$term->term_id] );

                        if ( !$anchors_output )
                        {
                            if ( get_option('propertyhive_maps_provider') == 'osm' )
                            {
                                $size = getimagesize( get_attached_file(  $current_settings['map_marker_icons'][$term->term_id] ) );
                                if ( $size !== FALSE && !empty($size) )
                                {
                                    echo '
                                    var marker_icon_width = ' . $size[0] . ';
                                    var marker_icon_height = ' . $size[1] . ';';

                                    $anchors_output = true;
                                }
                            }
                            else
                            {
                                if (isset($current_settings['custom_icon_anchor_position']) && $current_settings['custom_icon_anchor_position'] == 'center' )
                                {
                                    $size = getimagesize( get_attached_file( $current_settings['map_marker_icons'][$term->term_id] ) );
                                    if ( $size !== FALSE && !empty($size) )
                                    {
                                        echo 'var marker_icon_anchor_left = ' . floor( $size[0] / 2 ) . ';
                                        var marker_icon_anchor_top = ' . floor( $size[1] / 2 ) . ';';

                                        $anchors_output = true;
                                    }
                                }
                            }
                        }
                    }

                    $parent_term_id = $term->term_id;

                    $args = array(
                        'hide_empty' => false,
                        'parent' => $parent_term_id
                    );
                    $subterms = get_terms( 'property_type', $args );

                    if ( !empty( $subterms ) && !is_wp_error( $subterms ) )
                    {
                        foreach ($subterms as $term)
                        {
                            if ( isset($current_settings['map_marker_icons'][$term->term_id]) && $current_settings['map_marker_icons'][$term->term_id] != '' )
                            {
                                $marker_icons[$term->term_id] = wp_get_attachment_url( $current_settings['map_marker_icons'][$term->term_id] );
                            }
                            elseif ( isset($current_settings['map_marker_icons'][$parent_term_id]) && $current_settings['map_marker_icons'][$parent_term_id] != '' )
                            {
                                $marker_icons[$term->term_id] = wp_get_attachment_url( $current_settings['map_marker_icons'][$parent_term_id] );
                            }
                        }
                    }
                }
            }

            $args = array(
                'hide_empty' => false,
                'parent' => 0
            );
            $terms = get_terms( 'commercial_property_type', $args );

            if ( !empty( $terms ) && !is_wp_error( $terms ) )
            {
                foreach ($terms as $term)
                {
                    if ( isset($current_settings['map_marker_icons'][$term->term_id]) && $current_settings['map_marker_icons'][$term->term_id] != '' )
                    {
                        $marker_icons[$term->term_id] = wp_get_attachment_url( $current_settings['map_marker_icons'][$term->term_id] );
                    }

                    $parent_term_id = $term->term_id;

                    $args = array(
                        'hide_empty' => false,
                        'parent' => $parent_term_id
                    );
                    $subterms = get_terms( 'commercial_property_type', $args );

                    if ( !empty( $subterms ) && !is_wp_error( $subterms ) )
                    {
                        foreach ($subterms as $term)
                        {
                            if ( isset($current_settings['map_marker_icons'][$term->term_id]) && $current_settings['map_marker_icons'][$term->term_id] != '' )
                            {
                                $marker_icons[$term->term_id] = wp_get_attachment_url( $current_settings['map_marker_icons'][$term->term_id] );
                            }
                            elseif ( isset($current_settings['map_marker_icons'][$parent_term_id]) && $current_settings['map_marker_icons'][$parent_term_id] != '' )
                            {
                                $marker_icons[$term->term_id] = wp_get_attachment_url( $current_settings['map_marker_icons'][$parent_term_id] );
                            }
                        }
                    }
                }
            }
            echo 'var marker_icons = ' . json_encode($marker_icons) . ';';
        }

        if ( isset($current_settings['icon_type']) && $current_settings['icon_type'] == 'custom_per_availability' && isset($current_settings['map_marker_icons']) && !empty($current_settings['map_marker_icons']) )
        {
            $anchors_output = false;

            $marker_icons = array();
            $args = array(
                'hide_empty' => false,
                'parent' => 0
            );
            $terms = get_terms( 'availability', $args );

            if ( !empty( $terms ) && !is_wp_error( $terms ) )
            {
                foreach ($terms as $term)
                {
                    if ( isset($current_settings['map_marker_icons'][$term->term_id]) && $current_settings['map_marker_icons'][$term->term_id] != '' )
                    {
                        $marker_icons[$term->term_id] = wp_get_attachment_url( $current_settings['map_marker_icons'][$term->term_id] );

                        if ( !$anchors_output )
                        {
                            if ( get_option('propertyhive_maps_provider') == 'osm' )
                            {
                                $size = getimagesize( get_attached_file(  $current_settings['map_marker_icons'][$term->term_id] ) );
                                if ( $size !== FALSE && !empty($size) )
                                {
                                    echo '
                                    var marker_icon_width = ' . $size[0] . ';
                                    var marker_icon_height = ' . $size[1] . ';';

                                    $anchors_output = true;
                                }
                            }
                            else
                            {
                                if (isset($current_settings['custom_icon_anchor_position']) && $current_settings['custom_icon_anchor_position'] == 'center' )
                                {
                                    $size = getimagesize( get_attached_file( $current_settings['map_marker_icons'][$term->term_id] ) );
                                    if ( $size !== FALSE && !empty($size) )
                                    {
                                        echo 'var marker_icon_anchor_left = ' . floor( $size[0] / 2 ) . ';
                                        var marker_icon_anchor_top = ' . floor( $size[1] / 2 ) . ';';

                                        $anchors_output = true;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            echo 'var marker_icons = ' . json_encode($marker_icons) . ';';
        }

        echo '</script>';
?>
<div class="propertyhive-map-canvas-wrapper">
    <div class="propertyhive-map-canvas" id="propertyhive_map_canvas"></div>
    <?php if ( isset($current_settings['draw_a_search_enabled']) && $current_settings['draw_a_search_enabled'] == '1' ) { ?>
    <div class="propertyhive-draw-a-search-controls">
        <?php if ( ( isset($_GET['draw']) && $_GET['draw'] == '1' ) || ( isset($_GET['pgp']) && $_GET['pgp'] != '' ) ) { ?>
        <a href="?<?php
            foreach ($_GET as $key => $value)
            {
                if ( $key == 'draw' || $key == 'pgp' ) { continue; }
                if (!is_array($value))
                {
                    echo '&' . $key . '=' . urlencode($value);
                }
                else
                {
                    foreach ($value as $sub_value)
                    {
                        echo '&' . $key . urlencode('[]') . '=' . urlencode($sub_value);
                    }
                }
            }
        ?>"><?php echo __( 'Exit Draw-a-Search', 'propertyhive' ); ?></a>
        <?php } ?>
        <?php if ( isset($_GET['draw']) && $_GET['draw'] == '1' ) { ?>
        <a href="" id="ph_draw_a_search_clear"><?php echo __( 'Clear Drawn Area', 'propertyhive' ); ?></a>
        <a href="?<?php
            foreach ($_GET as $key => $value)
            {   
                if ( $key == 'draw' || $key == 'pgp' ) { continue; }
                if (!is_array($value))
                {
                    echo '&' . $key . '=' . $value;
                }
                else
                {
                    foreach ($value as $sub_value)
                    {
                        echo '&' . $key . urlencode('[]') . '=' . $sub_value;
                    }
                }
            }
        ?>" id="ph_draw_a_search_view" disabled><?php echo __( 'View Properties', 'propertyhive' ); ?></a>
        <?php }else{ ?>
        <a href="?<?php
            foreach ($_GET as $key => $value)
            {
                if (!is_array($value))
                {
                    echo '&' . $key . '=' . urlencode($value);
                }
                else
                {
                    foreach ($value as $sub_value)
                    {
                        echo '&' . $key . urlencode('[]') . '=' . urlencode($sub_value);
                    }
                }
            }
        ?>&draw=1" id="ph_draw_a_search_draw"><?php echo ( (isset($_GET['pgp']) && $_GET['pgp'] != '' ) ? __( 'Edit Area', 'propertyhive' ) : __( 'Draw-A-Search', 'propertyhive' ) ); ?></a>
        <?php } ?>
    </div>
    <?php } ?>
    <div class="map-loading"><div class="ph-table"><div class="ph-table-cell"><?php _e( 'Loading Properties', 'propertyhive' ); ?>...</div></div></div>
    <?php
        if ( isset($current_settings['marker_limit']) && !empty($current_settings['marker_limit']) )
        {
            echo '<div class="map-over-limit">' . sprintf( __('Too many results. Showing the first %s of %s. Please refine your search', 'propertyhive'), number_format((int)$current_settings['marker_limit']), '<span></span>' ) . '</div>';
        }
    ?>
</div>
<?php
        return ob_get_clean();
    }

    public function save_property_type_marker_icon()
    {
        global $current_section, $post;

        if ( ( $current_section == 'property-type' || $current_section == 'commercial-property-type' ) && isset($_REQUEST['id']) && $_REQUEST['id'] != '' ) 
        {
            $current_id = sanitize_title( $_REQUEST['id'] );
            
            $current_settings = get_option( 'propertyhive_map_search', array() );

            if ( !isset($current_settings['map_marker_icons']) )
            {
                $current_settings['map_marker_icons'] = array();
            }

            $error = '';
            if ($_FILES['custom_icon']['size'] == 0)
            {
                // No file uploaded
            }
            else
            {
                // Check $_FILES['upfile']['error'] value.
                switch ($_FILES['custom_icon']['error']) {
                    case UPLOAD_ERR_OK:
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        throw new RuntimeException('No file sent.');
                    case UPLOAD_ERR_INI_SIZE:
                    case UPLOAD_ERR_FORM_SIZE:
                        $error = __( 'Marker icon exceeded filesize limit.', 'propertyhive' );
                    default:
                        $error = __( 'Unknown error when uploading marker icon.', 'propertyhive' );
                }

                if ($error == '')
                {
                    $attachment_id = media_handle_upload( 'custom_icon', 0 );
    
                    if ( is_wp_error( $attachment_id ) ) {
                        // There was an error uploading the image.
                    } else {
                        // The image was uploaded successfully!
                        $current_settings['map_marker_icons'][$current_id] = $attachment_id;
                    }
                }
            }

            update_option( 'propertyhive_map_search', $current_settings );
        }
    }

    public function save_availability_marker_icon()
    {
        global $current_section;

        if ( $current_section == 'availability' && isset($_REQUEST['id']) && $_REQUEST['id'] != '' )
        {
            $current_id = sanitize_title( $_REQUEST['id'] );

            $current_settings = get_option( 'propertyhive_map_search', array() );

            if ( !isset($current_settings['map_marker_icons']) )
            {
                $current_settings['map_marker_icons'] = array();
            }

            $error = '';
            if ($_FILES['custom_icon']['size'] == 0)
            {
                // No file uploaded
            }
            else
            {
                // Check $_FILES['upfile']['error'] value.
                switch ($_FILES['custom_icon']['error']) {
                    case UPLOAD_ERR_OK:
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        throw new RuntimeException('No file sent.');
                    case UPLOAD_ERR_INI_SIZE:
                    case UPLOAD_ERR_FORM_SIZE:
                        $error = __( 'Marker icon exceeded filesize limit.', 'propertyhive' );
                    default:
                        $error = __( 'Unknown error when uploading marker icon.', 'propertyhive' );
                }

                if ($error == '')
                {
                    $attachment_id = media_handle_upload( 'custom_icon', 0 );

                    if ( is_wp_error( $attachment_id ) ) {
                        // There was an error uploading the image.
                    } else {
                        // The image was uploaded successfully!
                        $current_settings['map_marker_icons'][$current_id] = $attachment_id;
                    }
                }
            }

            update_option( 'propertyhive_map_search', $current_settings );
        }
    }

    public function propertyhive_results_views()
    {
        // Remove any existing view parameter from the query string
        $new_query_string = '';
                    
        parse_str(http_build_query($_GET), $output);
        
        if (isset($output['draw']))
        {
            $output['draw'] = '';
            unset($output['draw']);
        }
        if (isset($output['view']))
        {
            $output['view'] = '';
            unset($output['view']);
        }
        
        $new_query_string = http_build_query($output);

        $views = array(
            'list' => array(
                'default' => true,
                'content' => __( 'View In List', 'propertyhive' )
            ),
            'map' => array(
                'content' => __( 'View On Map', 'propertyhive' )
            )
        );

        $views = apply_filters( 'propertyhive_results_views', $views );

        if ( !empty($views) )
        {
            $template = locate_template( array('propertyhive/map-search-results-views.php') );
            if ( !$template )
            {
                include( dirname( PH_MAP_SEARCH_PLUGIN_FILE ) . '/templates/map-search-results-views.php' );
            }
            else
            {
                include( $template );
            }
        }
    }

    /**
     * Define PH Map Search Constants
     */
    private function define_constants() 
    {
        define( 'PH_MAP_SEARCH_PLUGIN_FILE', __FILE__ );
        define( 'PH_MAP_SEARCH_VERSION', $this->version );
    }

    private function includes()
    {
        //include_once( dirname( __FILE__ ) . "/includes/class-ph-map-search-install.php" );
    }

    /**
     * Output error message if core Property Hive plugin isn't active
     */
    public function map_search_error_notices() 
    {
        if (!is_plugin_active('propertyhive/propertyhive.php'))
        {
            $message = __( "The Property Hive plugin must be installed and activated before you can use the Property Hive Map Search add-on", 'propertyhive' );
            echo"<div class=\"error\"> <p>$message</p></div>";
        }
        elseif ( PH()->version < '1.0.24' )
        {
            $message = __( "Please update to version 1.0.24 or higher of Property Hive to ensure full compatibility with the Map Search add on", 'propertyhive' );
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
        
        propertyhive_admin_fields( self::get_map_search_settings() );
    }

    /**
     * Uses the Property Hive options API to save settings.
     *
     * @uses propertyhive_update_options()
     * @uses self::get_settings()
     */
    public function save() {

        $existing_propertyhive_map_search = get_option( 'propertyhive_map_search', array() );

        $propertyhive_map_search = array(
            'format' => ( (isset($_POST['format'])) ? $_POST['format'] : '' ),
            'icon_type' => ( (isset($_POST['icon_type'])) ? $_POST['icon_type'] : '' ),
            'custom_icon_anchor_position' => ( (isset($_POST['custom_icon_anchor_position'])) ? $_POST['custom_icon_anchor_position'] : '' ),
            'style_js' => ( (isset($_POST['style_js'])) ? stripslashes($_POST['style_js']) : '' ),
            'show_transit_layer' => ( (isset($_POST['show_transit_layer'])) ? $_POST['show_transit_layer'] : '' ),
            'marker_clustering_enabled' => ( (isset($_POST['marker_clustering_enabled'])) ? $_POST['marker_clustering_enabled'] : '' ),
            'marker_limit' => ( (isset($_POST['marker_limit']) && $_POST['marker_limit'] != '') ? (int)$_POST['marker_limit'] : '' ),
            'default_zoom_level' => ( (isset($_POST['default_zoom_level'])) ? $_POST['default_zoom_level'] : '' ),
            'default_center' => ( (isset($_POST['default_center'])) ? $_POST['default_center'] : '' ),
            'disable_scrollwheel' => ( (isset($_POST['disable_scrollwheel'])) ? $_POST['disable_scrollwheel'] : '' ),
            'draw_a_search_enabled' => ( (isset($_POST['draw_a_search_enabled'])) ? $_POST['draw_a_search_enabled'] : '' ),
            'refresh_on_bounds_changed' => ( (isset($_POST['refresh_on_bounds_changed'])) ? $_POST['refresh_on_bounds_changed'] : '' ),
        );

        if ( isset($_POST['icon_type']) && $_POST['icon_type'] == 'custom_single' )
        {
            $error = '';
            if ($_FILES['custom_icon']['size'] == 0)
            {
                // No file uploaded
            }
            else
            {
                // Check $_FILES['upfile']['error'] value.
                switch ($_FILES['custom_icon']['error']) {
                    case UPLOAD_ERR_OK:
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        throw new RuntimeException('No file sent.');
                    case UPLOAD_ERR_INI_SIZE:
                    case UPLOAD_ERR_FORM_SIZE:
                        $error = __( 'Marker icon exceeded filesize limit.', 'propertyhive' );
                    default:
                        $error = __( 'Unknown error when uploading marker icon.', 'propertyhive' );
                }

                if ($error == '')
                {
                    $attachment_id = media_handle_upload( 'custom_icon', 0 );
    
                    if ( is_wp_error( $attachment_id ) ) {
                        // There was an error uploading the image.
                    } else {
                        // The image was uploaded successfully!
                        $propertyhive_map_search['custom_icon_attachment_id'] = $attachment_id;
                    }
                }
            }
        }

        if ( isset($_POST['icon_type']) && $_POST['icon_type'] == 'custom_per_department' )
        {
            $departments = array(
                'residential_sales',
                'residential_lettings',
                'commercial',
            );

            foreach ( $departments as $department )
            {
                $error = '';
                if ($_FILES['custom_icon_' . $department]['size'] == 0)
                {
                    // No file uploaded
                }
                else
                {
                    // Check $_FILES['upfile']['error'] value.
                    switch ($_FILES['custom_icon_' . $department]['error']) {
                        case UPLOAD_ERR_OK:
                            break;
                        case UPLOAD_ERR_NO_FILE:
                            throw new RuntimeException('No file sent.');
                        case UPLOAD_ERR_INI_SIZE:
                        case UPLOAD_ERR_FORM_SIZE:
                            $error = __( $department . ' marker icon exceeded filesize limit.', 'propertyhive' );
                        default:
                            $error = __( 'Unknown error when uploading ' . $department . ' marker icon.', 'propertyhive' );
                    }

                    if ($error == '')
                    {
                        $attachment_id = media_handle_upload( 'custom_icon_' . $department, 0 );
        
                        if ( is_wp_error( $attachment_id ) ) {
                            // There was an error uploading the image.
                        } else {
                            // The image was uploaded successfully!
                            $propertyhive_map_search['custom_icon_' . $department . '_attachment_id'] = $attachment_id;
                        }
                    }
                }
            }
        }

        $propertyhive_map_search = array_merge( $existing_propertyhive_map_search, $propertyhive_map_search );

        update_option( 'propertyhive_map_search', $propertyhive_map_search );
    }

    public function add_marker_icon_upload_to_property_type($args)
    {
        $current_settings = get_option( 'propertyhive_map_search', array() );

        $current_id = '';
        if (isset($_REQUEST['id']) && $_REQUEST['id'] != '')
        {
            $current_id = $_REQUEST['id'];
        }

        $args[] = array( 'title' => '', 'type' => 'title', 'desc' => '', 'id' => 'custom_field_property_type_map_marker_settings' );

        $args[] = array(
            'title'     => __( 'Custom Marker Icon', 'propertyhive' ),
            'id'        => 'custom_icon',
            'type'      => 'map_marker_icon',
            'default'   => ( isset($current_settings['map_marker_icons'][$current_id]) ? $current_settings['map_marker_icons'][$current_id] : ''),
            'desc_tip'  =>  false,
        );

        $args[] = array( 'type' => 'sectionend', 'id' => 'custom_field_property_type_map_marker_settings' );

        return $args;
    }

    public function add_marker_icon_upload_to_availability($args)
    {
        $current_settings = get_option( 'propertyhive_map_search', array() );

        $current_id = '';
        if (isset($_REQUEST['id']) && $_REQUEST['id'] != '')
        {
            $current_id = $_REQUEST['id'];
        }

        $args[] = array( 'title' => '', 'type' => 'title', 'desc' => '', 'id' => 'custom_field_availability_map_marker_settings' );

        $args[] = array(
            'title'     => __( 'Custom Marker Icon', 'propertyhive' ),
            'id'        => 'custom_icon',
            'type'      => 'map_marker_icon',
            'default'   => ( isset($current_settings['map_marker_icons'][$current_id]) ? $current_settings['map_marker_icons'][$current_id] : ''),
            'desc_tip'  =>  false,
        );

        $args[] = array( 'type' => 'sectionend', 'id' => 'custom_field_availability_map_marker_settings' );

        return $args;
    }

    public function map_marker_icon_file_upload( $value )
    {
        ?>
            <tr valign="top" id="<?php echo $value['id']; ?>_row">
                <th scope="row" class="titledesc"><?php echo esc_html( $value['title'] ) ?></th>
                <td class="forminp">

                    <?php
                        if ($value['default'] != '')
                        {
                            echo '<p><img src="' . wp_get_attachment_url( $value['default'] ) . '" alt="Custom map marker"></p>';
                        }
                    ?>

                    <input type="file" name="<?php echo esc_attr( $value['id'] ); ?>" id="<?php echo esc_attr( $value['id'] ); ?>" />
                    
                </td>
            </tr>
        <?php
    }

    /**
     * Get map search settings
     *
     * @return array Array of settings
     */
    public function get_map_search_settings() {

        $current_settings = get_option( 'propertyhive_map_search', array() );

        $settings = array(

            array( 'title' => __( 'Map Search Settings', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'map_search_settings' )

        );

        $settings[] = array(
            'title'     => __( 'Map Search Format', 'propertyhive' ),
            'id'        => 'format',
            'type'      => 'radio',
            'default'   => ( isset($current_settings['format']) ? $current_settings['format'] : ''),
            'options'   => array(
                'view' => __( 'Add a toggle allowing people to switch between standard view and map view', 'propertyhive' ),
                'split' => __( 'Display the map and standard results side-by-side', 'propertyhive' ),
                '' => __( 'Neither. I don\'t want map search shown on the search results', 'propertyhive' ),
            ),
        );

        $settings[] = array(
            'title'     => __( 'Enable Draw A Search', 'propertyhive' ),
            'id'        => 'draw_a_search_enabled',
            'type'      => 'checkbox',
            'default'   => ( ( isset($current_settings['draw_a_search_enabled']) && $current_settings['draw_a_search_enabled'] == 1 ) ? 'yes' : ''),
            'desc'      => __( 'If enabled, the user will be given the ability to draw a polygon on the map to specify their required area. Alternatively, you can use the following link to send users directs to the draw-a-search feature: ', 'propertyhive' ) . get_permalink( ph_get_page_id('search_results') ) . '?view=map&draw=1<br><br>' . __( 'Note: Requires MySQL version 5.6.1 or newer', 'propertyhive' ),
        );

        $settings[] = array( 'type' => 'sectionend', 'id' => 'map_search_settings');

        $settings[] = array( 'title' => __( 'Map Marker Settings', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'map_marker_settings' );

        $settings[] = array(
            'title'     => __( 'Map Marker Icons', 'propertyhive' ),
            'id'        => 'icon_type',
            'type'      => 'radio',
            'default'   => ( isset($current_settings['icon_type']) ? $current_settings['icon_type'] : ''),
            'options'   => array(
                '' => __( 'Use Google default marker', 'propertyhive' ),
                'custom_single' => __( 'Use custom marker icon', 'propertyhive' ),
                'custom_per_department' => __( 'Use different marker icon for each department', 'propertyhive' ),
                'custom_per_type' => __( 'Use different marker icon for each property type <small><em>(Icons can be uploaded in \'Custom Fields > Property Types\')</em></small>', 'propertyhive' ),
                'custom_per_availability' => __( 'Use different marker icon for each availability <small><em>(Icons can be uploaded in \'Custom Fields > Availabilities\')</em></small>', 'propertyhive' ),
            ),
        );

        $settings[] = array(
            'title'     => __( 'Custom Marker Icon', 'propertyhive' ),
            'id'        => 'custom_icon',
            'type'      => 'map_marker_icon',
            'default'   => ( isset($current_settings['custom_icon_attachment_id']) ? $current_settings['custom_icon_attachment_id'] : ''),
            'desc_tip'  =>  false,
        );

        if ( get_option( 'propertyhive_active_departments_sales' ) == 'yes' )
        {
            $settings[] = array(
                'title'     => __( 'Custom Marker Icon', 'propertyhive' ) . ' (' . __( 'Residential Sales', 'propertyhive' ) . ')',
                'id'        => 'custom_icon_residential_sales',
                'type'      => 'map_marker_icon',
                'default'   => ( isset($current_settings['custom_icon_residential_sales_attachment_id']) ? $current_settings['custom_icon_residential_sales_attachment_id'] : ''),
                'desc_tip'  =>  false,
            );
        }

        if ( get_option( 'propertyhive_active_departments_lettings' ) == 'yes' )
        {
            $settings[] = array(
                'title'     => __( 'Custom Marker Icon', 'propertyhive' ) . ' (' . __( 'Residential Lettings', 'propertyhive' ) . ')',
                'id'        => 'custom_icon_residential_lettings',
                'type'      => 'map_marker_icon',
                'default'   => ( isset($current_settings['custom_icon_residential_lettings_attachment_id']) ? $current_settings['custom_icon_residential_lettings_attachment_id'] : ''),
                'desc_tip'  =>  false,
            );
        }

        if ( get_option( 'propertyhive_active_departments_commercial' ) == 'yes' )
        {
            $settings[] = array(
                'title'     => __( 'Custom Marker Icon', 'propertyhive' ) . ' (' . __( 'Commercial', 'propertyhive' ) . ')',
                'id'        => 'custom_icon_commercial',
                'type'      => 'map_marker_icon',
                'default'   => ( isset($current_settings['custom_icon_commercial_attachment_id']) ? $current_settings['custom_icon_commercial_attachment_id'] : ''),
                'desc_tip'  =>  false,
            );
        }

        $settings[] = array(
            'title'     => __( 'Custom Marker Anchor Position', 'propertyhive' ),
            'id'        => 'custom_icon_anchor_position',
            'type'      => 'select',
            'default'   => ( isset($current_settings['custom_icon_anchor_position']) ? $current_settings['custom_icon_anchor_position'] : ''),
            'options'   => array(
                '' => __( 'Bottom Center', 'propertyhive' ),
                'center' => __( 'Center', 'propertyhive' ),
            ),
            'desc'      => 'Where is the icon in relation to the position on the map. Circle marker icons for example would likely need the icon centered over the position on the map'
        );

        $settings[] = array(
            'title'     => __( 'Enable Marker Clustering', 'propertyhive' ),
            'id'        => 'marker_clustering_enabled',
            'type'      => 'checkbox',
            'default'   => ( ( isset($current_settings['marker_clustering_enabled']) && $current_settings['marker_clustering_enabled'] == 1 ) ? 'yes' : ''),
            'desc'      => __( 'If enabled, properties in close proximity will be grouped together', 'propertyhive' ),
        );

        $settings[] = array(
            'title'     => __( 'Limit Number Of Markers', 'propertyhive' ),
            'id'        => 'marker_limit',
            'type'      => 'number',
            'default'   => ( ( isset($current_settings['marker_limit']) ) ? $current_settings['marker_limit'] : ''),
            'desc'      => __( 'For sites with thousands of properties a map can be slow, and unusable, if thousands of markers are shown on the map at once. With a limit set, should the number of markers be higher than this, we\'ll limit the number shown and display a message accordingly. Leave empty for no limit', 'propertyhive' ),
        );

        $settings[] = array( 'type' => 'sectionend', 'id' => 'map_marker_settings');

        $settings[] = array( 'title' => __( 'Advanced Settings', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'map_advanced_settings' );

        $settings[] = array(
            'title'     => __( 'Map Style JavaScript Array', 'propertyhive' ),
            'id'        => 'style_js',
            'type'      => 'textarea',
            'css'       => 'max-width:450px; width:100%; height:75px;',
            'default'   => ( isset($current_settings['style_js']) ? $current_settings['style_js'] : ''),
            'desc'      => __( 'Check out <a href="https://snazzymaps.com/" target="_blank">Snazzy Maps</a> for a range of alternative map styles', 'propertyhive' ),
        );

        $settings[] = array(
            'title'     => __( 'Show Transit Layer', 'propertyhive' ),
            'id'        => 'show_transit_layer',
            'type'      => 'checkbox',
            'default'   => ( ( isset($current_settings['show_transit_layer']) && $current_settings['show_transit_layer'] == 1 ) ? 'yes' : ''),
            'desc'      => __( 'If enabled, and the map is centered on a city that supports transit information, the map will display major transit lines as thick, colored lines. The color of the line is set based upon information from the transit line operator. Enabling the Transit Layer will alter the style of the base map to better emphasize transit routes. Only applicable when Google Maps is selected as the map provider.', 'propertyhive' ),
        );

        $settings[] = array(
            'title'     => __( 'Default Zoom Level', 'propertyhive' ),
            'id'        => 'default_zoom_level',
            'type'      => 'number',
            'default'   => ( isset($current_settings['default_zoom_level']) ? $current_settings['default_zoom_level'] : ''),
            'desc_tip'  =>  false,
            'desc'      => __( 'Leave this blank if you\'d like the map to automatically set the zoom level to fit all properties into view.', 'propertyhive' ),
            'custom_attributes' => array(
                'min' => 7,
                'max' => 15,
            ),
            'css' => 'width:75px',
        );

        $settings[] = array(
            'title'     => __( 'Default Center Coordinates', 'propertyhive' ),
            'id'        => 'default_center',
            'type'      => 'text',
            'default'   => ( isset($current_settings['default_center']) ? $current_settings['default_center'] : ''),
            'desc_tip'  =>  false,
            'desc'      => __( 'By default the map will center on the primary offices coordinates, before zooming out to fit all properties into view. Enter coordinates here in the format {latitude},{longitude} to override this functionality and set your own map center.', 'propertyhive' ),
        );

        $settings[] = array(
            'title'     => __( 'Disable Scrollwheel Zooming', 'propertyhive' ),
            'id'        => 'disable_scrollwheel',
            'type'      => 'checkbox',
            'default'   => ( ( isset($current_settings['disable_scrollwheel']) && $current_settings['disable_scrollwheel'] == 1 ) ? 'yes' : ''),
            'desc'      => __( 'By default you can use the mouse scrollwheel to zoom the map in and out. Check this option to disable this functionality', 'propertyhive' ),
        );

        $settings[] = array(
            'title'     => __( 'Reload Properties As Map Is Panned/Zoomed', 'propertyhive' ),
            'id'        => 'refresh_on_bounds_changed',
            'type'      => 'checkbox',
            'default'   => ( ( isset($current_settings['refresh_on_bounds_changed']) && $current_settings['refresh_on_bounds_changed'] == 1 ) ? 'yes' : ''),
            'desc'      => __( 'By default we\'ll load all the properties onto the map, regardless of whether they\'re in view or not. Enabling this option means only properties within the bounds of the map being viewed are loaded, and as the map is panned or zoomed the properties will refresh accordingly. Useful for larger sites with thousands of properties where speed can be an issue.', 'propertyhive' ),
        );

        $settings[] = array( 'type' => 'sectionend', 'id' => 'map_advanced_settings');

        $settings[] = array(
            'id'        => 'custom_js',
            'type'      => 'html',
            'html'      => '<script>
                jQuery(document).ready(function()
                {
                    jQuery(\'input[name=icon_type][type=radio]\').change(function() { ph_toggle_marker_icon_options(); });

                    ph_toggle_marker_icon_options();
                });

                function ph_toggle_marker_icon_options()
                {
                    var selected_val = jQuery(\'input[name=icon_type][type=radio]:checked\').val();

                    jQuery(\'#custom_icon_row\').hide();
                    jQuery(\'#custom_icon_residential_sales_row\').hide();
                    jQuery(\'#custom_icon_residential_lettings_row\').hide();
                    jQuery(\'#custom_icon_commercial_row\').hide();
                    jQuery(\'#row_custom_icon_anchor_position\').hide();

                    switch ( selected_val )
                    {
                        case "custom_single":
                        {
                            jQuery(\'#custom_icon_row\').show();
                            jQuery(\'#row_custom_icon_anchor_position\').show();
                            break;
                        }
                        case "custom_per_department":
                        {
                            jQuery(\'#custom_icon_residential_sales_row\').show();
                            jQuery(\'#custom_icon_residential_lettings_row\').show();
                            jQuery(\'#custom_icon_commercial_row\').show();
                            jQuery(\'#row_custom_icon_anchor_position\').show();
                            break;
                        }
                        case "custom_per_type":
                        case "custom_per_availability":
                        {
                            jQuery(\'#row_custom_icon_anchor_position\').show();
                            break;
                        }
                        default:
                        {

                        }
                    }
                }
            </script>'
        );

        return $settings;
    }

    private function decode_polygon($polygon)
    {
        // Decode polygon
        $length = strlen($polygon);
        $index = 0;
        $points = array();
        $lat = 0;
        $lng = 0;

        while ($index < $length)
        {
            // Temporary variable to hold each ASCII byte.
            $b = 0;
        
            // The encoded polyline consists of a latitude value followed by a
            // longitude value.  They should always come in pairs.  Read the
            // latitude value first.
            $shift = 0;
            $result = 0;
            do
            {
                // The `ord(substr($encoded, $index++))` statement returns the ASCII
                //  code for the character at $index.  Subtract 63 to get the original
                // value. (63 was added to ensure proper ASCII characters are displayed
                // in the encoded polyline string, which is `human` readable)
                $b = ord(substr($polygon, $index++)) - 63;
        
                // AND the bits of the byte with 0x1f to get the original 5-bit `chunk.
                // Then left shift the bits by the required amount, which increases
                // by 5 bits each time.
                // OR the value into $results, which sums up the individual 5-bit chunks
                // into the original value.  Since the 5-bit chunks were reversed in
                // order during encoding, reading them in this way ensures proper
                // summation.
                $result |= ($b & 0x1f) << $shift;
                $shift += 5;
            }
            // Continue while the read byte is >= 0x20 since the last `chunk`
            // was not OR'd with 0x20 during the conversion process. (Signals the end)
            while ($b >= 0x20);
        
            // Check if negative, and convert. (All negative values have the last bit set)
            $dlat = (($result & 1) ? ~($result >> 1) : ($result >> 1));
        
            // Compute actual latitude since value is offset from previous value.
            $lat += $dlat;
        
            // The next values will correspond to the longitude for this point.
            $shift = 0;
            $result = 0;
            do
            {
                $b = ord(substr($polygon, $index++)) - 63;
                $result |= ($b & 0x1f) << $shift;
                $shift += 5;
            }
            while ($b >= 0x20);
        
            $dlng = (($result & 1) ? ~($result >> 1) : ($result >> 1));
            $lng += $dlng;
            
            // Convert back to original values.
            $points[] = ($lat * 1e-5) . ' ' . ($lng * 1e-5);
        }

        return $points;
    }
}

endif;

/**
 * Returns the main instance of PH_Map_Search to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return PH_Map_Search
 */
function PHMAP() {
    return PH_Map_Search::instance();
}

$PHMAP = PHMAP();

if( is_admin() && file_exists(  dirname( __FILE__ ) . '/propertyhive-map-search-update.php' ) )
{
    include_once( dirname( __FILE__ ) . '/propertyhive-map-search-update.php' );
}