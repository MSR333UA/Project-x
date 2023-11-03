<?php
/**
 * Plugin Name: Property Hive what3words Add On
 * Plugin Uri: http://wp-property-hive.com/addons/what3words/
 * Description: Add On for Property Hive allowing users to retrieve and save a location from what3words
 * Version: 1.0.3
 * Author: PropertyHive
 * Author URI: http://wp-property-hive.com
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'PH_What3words' ) ) :

final class PH_What3words {

    /**
     * @var string
     */
    public $version = '1.0.3';

    /**
     * @var Property Hive The single instance of the class
     */
    protected static $_instance = null;
    
    /**
     * Main Property Hive what3words Instance
     *
     * Ensures only one instance of Property Hive what3words is loaded or can be loaded.
     *
     * @static
     * @return Property Hive what3words - Main instance
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

        $this->id    = 'what3words';
        $this->label = __( 'what3words', 'propertyhive' );

        // Define constants
        $this->define_constants();

        // Include required files
        $this->includes();

        add_action( 'admin_notices', array( $this, 'what3words_error_notices') );

        add_action( 'admin_enqueue_scripts', array( $this, 'load_what3words_scripts' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'load_what3words_styles' ) );

        add_filter( 'propertyhive_settings_tabs_array', array( $this, 'add_settings_tab' ), 19 );
        add_action( 'propertyhive_settings_' . $this->id, array( $this, 'output' ) );
        add_action( 'propertyhive_settings_save_' . $this->id, array( $this, 'save' ) );

        add_filter( "plugin_action_links_" . plugin_basename( __FILE__ ), array( $this, 'plugin_add_settings_link' ) );

        add_action( 'propertyhive_property_coordinates_fields', array( $this, 'add_propertyhive_location_selector_fields'), 1, 1 );
        add_action( 'propertyhive_save_property_coordinates', array( $this, 'do_propertyhive_save_property_location_selector_fields'), 1 );

        add_action( 'wp_ajax_propertyhive_get_grid_section', array( $this, 'ajax_propertyhive_get_grid_section' ) );

        add_action( 'wp_ajax_propertyhive_get_three_word_location', array( $this, 'ajax_propertyhive_get_three_word_location' ) );

        add_action( 'propertyhive_property_map_actions', array( $this, 'add_custom_locations_to_property_map' ) );

        add_action( 'propertyhive_property_map_after', array( $this, 'add_property_map_custom_locations_key' ) );

        add_action( 'propertyhive_property_imported', array( $this, 'map_location_post_import' ), 10, 2 );

        add_filter( 'propertyhive_document_property_merge_tags', array( $this, 'document_property_what3words_merge_tags' ), 10, 2 );
        add_filter( 'propertyhive_document_property_merge_values', array( $this, 'document_property_what3words_merge_values' ), 10, 2 );

        add_filter( 'viewing_applicant_booking_confirmation_email_body', array( $this, 'replace_what3words_tag' ), 10, 3 );
        add_filter( 'viewing_applicant_booking_confirmation_sms_body', array( $this, 'replace_what3words_tag' ), 10, 3 );

        add_shortcode( 'what3words_location', array( $this, 'shortcode_get_three_word_location' ) );
    }

    public function plugin_add_settings_link( $links )
    {
        $settings_link = '<a href="' . admin_url('admin.php?page=ph-settings&tab=what3words') . '">' . __( 'Settings' ) . '</a>';
        array_push( $links, $settings_link );
        return $links;
    }

    /**
     * Define PH what3words Constants
     */
    private function define_constants() 
    {
        define( 'PH_WHAT3WORDS_PLUGIN_FILE', __FILE__ );
        define( 'PH_WHAT3WORDS_VERSION', $this->version );
    }

    public function load_what3words_scripts()
    {
        global $pagenow, $post;

        if ( in_array($pagenow, array('post-new.php', 'post.php')) && isset($post->ID) && get_post_type($post->ID) == 'property' )
        {
            $assets_path = str_replace( array( 'http:', 'https:' ), '', untrailingslashit( plugins_url( '/', __FILE__ ) ) ) . '/assets/';

            wp_register_script(
                'ph-what3words',
                $assets_path . 'js/propertyhive-what3words.js',
                array(),
                PH_WHAT3WORDS_VERSION,
                true
            );

            wp_enqueue_script( 'ph-what3words' );

            // in JavaScript, object properties are accessed as ajax_object.ajax_url, ajax_object.we_value
            wp_localize_script( 'ph-what3words', 'ph_what3words_ajax_object', array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'post_id' => $post->ID,
            ) );
        }
    }

    public function load_what3words_styles() {

        $assets_path = str_replace( array( 'http:', 'https:' ), '', untrailingslashit( plugins_url( '/', __FILE__ ) ) ) . '/assets/';

        wp_register_style(
            'ph-what3words',
            $assets_path . 'css/ph-what3words.css',
            array(),
            PH_WHAT3WORDS_VERSION
        );

        wp_enqueue_style( 'ph-what3words' );
    }

    private function includes()
    {
        include_once( dirname( __FILE__ ) . "/includes/class-ph-what3words-install.php" );
    }

    /**
     * Output error message if core Property Hive plugin isn't active
     */
    public function what3words_error_notices() 
    {
        if (!is_plugin_active('propertyhive/propertyhive.php'))
        {
            $message = __( "The Property Hive plugin must be installed and activated before you can use the Property Hive what3words add-on", 'propertyhive' );
            echo"<div class=\"error\"> <p>$message</p></div>";
        }
    }

    public function add_propertyhive_location_selector_fields()
    {
        global $post;

        $three_word_location = $this->get_saved_or_new_property_three_word_location($post->ID, false);

        $args = array(
            'id' => '_what3words_location',
            'label' => __( 'Three Word Location', 'propertyhive' ),
            'desc_tip' => true,
            'description' => __( 'This is the three word location from what3words. If you change the property co-ordinates, it will recalculate the location', 'propertyhive' ),
            'type' => 'text',
            'value' => $three_word_location
        );
        propertyhive_wp_text_input( $args );

        ?>
        <p class="form-field">
            <label><?php echo __( 'what3words Custom Locations', 'propertyhive' ); ?></label>
        </p>
        <?php
        $propertyhive_what3words = get_option( 'propertyhive_what3words' );
        if ( isset($propertyhive_what3words['location_types']) && count($propertyhive_what3words['location_types']) > 0 )
        {
            $custom_locations = get_post_meta($post->ID, '_what3words_custom_locations', TRUE);
            foreach ( $propertyhive_what3words['location_types'] as $location_type_id => $location_type)
            {
                ?>
                <p class="form-field _what3words_custom_location_<?php echo esc_attr( $location_type_id ); ?>_field">
                    <label for="_what3words_custom_location_<?php echo esc_attr( $location_type_id ); ?>"><?php echo wp_kses_post( __( $location_type['name'], 'propertyhive' ) ); ?></label>
                    <input
                        type="text"
                        class="<?php echo esc_attr( 'short what3words_custom_location'); ?>"
                        name="_what3words_custom_location_<?php echo esc_attr( $location_type_id ); ?>"
                        id="_what3words_custom_location_<?php echo esc_attr( $location_type_id ); ?>"
                        value="<?php echo isset( $custom_locations[$location_type_id] ) ? $custom_locations[$location_type_id]['location'] : '';  ?>"
                    />
                    &nbsp;
                    <input type="checkbox" class="what3words_checkbox" id="_what3words_checkbox_<?php echo esc_attr( $location_type_id ); ?>" />
                    <input type="hidden" id="_what3words_custom_location_colour_<?php echo esc_attr( $location_type_id ); ?>" value="<?php echo $location_type['colour']; ?>" />
                    <input
                        type="hidden"
                        name="_what3words_custom_location_coords_<?php echo esc_attr( $location_type_id ); ?>"
                        id="_what3words_custom_location_coords_<?php echo esc_attr( $location_type_id ); ?>"
                        value="<?php echo isset( $custom_locations[$location_type_id] ) ? $custom_locations[$location_type_id]['coords'] : ''; ?>"
                    />
                </p>
                <?php
            }
        }
        else
        {
            echo __( 'No what3words locations have been set up', 'propertyhive' );
        }
        ?>
        <input type="hidden" id="_what3words_maps_provider_option" value="<?php echo get_option('propertyhive_maps_provider') == 'osm' ? 'osm' : 'google'; ?>" />
        <?php
    }

    public function do_propertyhive_save_property_location_selector_fields()
    {
        global $post;

        if ( isset($_POST['_what3words_location']) )
        {
            update_post_meta( $post->ID, '_what3words_location', ph_clean($_POST['_what3words_location']) );
        }

        $custom_locations = array();
        $propertyhive_what3words = get_option( 'propertyhive_what3words' );
        if ( isset($propertyhive_what3words['location_types']) && count($propertyhive_what3words['location_types']) > 0 )
        {
            foreach ( $propertyhive_what3words['location_types'] as $location_type_id => $location_type)
            {
                if ( isset($_POST['_what3words_custom_location_' . $location_type_id]) && !empty($_POST['_what3words_custom_location_' . $location_type_id]) )
                {
                    $custom_locations[$location_type_id] = array(
                        'location' => ph_clean($_POST['_what3words_custom_location_' . $location_type_id]),
                        'coords' => ph_clean($_POST['_what3words_custom_location_coords_' . $location_type_id]),
                    );
                }
            }
            if ( count( $custom_locations ) > 0 )
            {
                update_post_meta( $post->ID, '_what3words_custom_locations', $custom_locations );
            }
            else
            {
                delete_post_meta( $post->ID, '_what3words_custom_locations' );
            }
        }
    }

    public function get_three_word_location($latitude, $longitude)
    {
        $location = '';
        $square   = '';

        $propertyhive_what3words = get_option( 'propertyhive_what3words' );
        $api_key = isset($propertyhive_what3words['api_key']) ? $propertyhive_what3words['api_key'] : '';

        $data = array(
            'key' => $api_key,
            'coordinates' => $latitude . ',' . $longitude,
        );
        $response = wp_remote_get( 'https://api.what3words.com/v3/convert-to-3wa?' . http_build_query($data) );

        if ( !is_wp_error( $response ) )
		{
            $json = json_decode( $response['body'], TRUE );

            if ($json !== FALSE && isset($json['words']) )
            {
                $location = $json['words'];
                $square   = $json['square'];
            }
        }
        return array($location, $square);
    }

    public function ajax_propertyhive_get_three_word_location()
    {
        list($three_word_location, $square) = $this->get_three_word_location($_POST['latitude'], $_POST['longitude']);

        header("Content-type:application/json");
        echo json_encode(array(
            'success' => true,
            'location' => $three_word_location,
            'square' => $square,
        ));
        exit;
    }

    public function shortcode_get_three_word_location( $atts )
    {
        global $post;

        $three_word_location = '';

        if ( isset($post->ID) && get_post_type($post->ID) == 'property' )
        {
            $atts = shortcode_atts( apply_filters( 'propertyhive_what3words_shortcode_atts', array(
                'custom_location_id' => '',
            ) ), $atts );

            if ( $atts['custom_location_id'] != '' )
            {
                $custom_locations = get_post_meta($post->ID, '_what3words_custom_locations', TRUE);
                if ( !empty($custom_locations) && is_array($custom_locations) && isset($custom_locations[$atts['custom_location_id']]) && is_array($custom_locations[$atts['custom_location_id']]) )
                {
                    $three_word_location = $custom_locations[$atts['custom_location_id']]['location'];
                }
            }
            else
            {
                $three_word_location = $this->get_saved_or_new_property_three_word_location($post->ID);
            }
        }
        return $three_word_location;
    }

    public function ajax_propertyhive_get_grid_section()
    {
        $propertyhive_what3words = get_option( 'propertyhive_what3words' );
        $api_key = isset($propertyhive_what3words['api_key']) ? $propertyhive_what3words['api_key'] : '';

        $data = array(
            'key' => $api_key,
            'bounding-box' => $_POST['boundingBox'],
            'format' => 'geojson'
        );
        $response = wp_remote_get( 'https://api.what3words.com/v3/grid-section?' . http_build_query($data) );

        header("Content-type:application/json");
        echo json_encode(array(
            'success' => true,
            'data' => $response,
        ));
        exit;
    }

    public function add_custom_locations_to_property_map()
    {
        global $post;
        if ( isset($post->ID) && get_post_type($post->ID) == 'property' )
        {
            $admin_custom_locations = array();
            $propertyhive_what3words = get_option( 'propertyhive_what3words' );
            if ( isset($propertyhive_what3words['location_types']) && count($propertyhive_what3words['location_types']) > 0 )
            {
                $admin_custom_locations = $propertyhive_what3words['location_types'];
            }

            $custom_locations = get_post_meta($post->ID, '_what3words_custom_locations', TRUE);
            if ( !empty($custom_locations) && is_array($custom_locations) )
            {
                foreach ( $custom_locations as $location_id => $custom_location )
                {
                    if ( isset($admin_custom_locations[$location_id]) )
                    {
                        $coordinates_array = explode('|', $custom_location['coords']);
                        $square_colour = $admin_custom_locations[$location_id]['colour'];
                        if ( get_option('propertyhive_maps_provider') == 'osm' )
                        {
                            echo "
                            var bounds" . $location_id . " = [[" . $coordinates_array[0] . ", " . $coordinates_array[2] . "], [" . $coordinates_array[1] . ", " . $coordinates_array[3] . "]];

                            var options" . $location_id . " = {
                                color: '#000000',
                                opacity: 0.8,
                                weight: 1,
                                fillColor: '" . $square_colour . "',
                                fillOpacity: 0.35,
                            };
                            var rectangle" . $location_id . " = L.rectangle(bounds" . $location_id . ", options" . $location_id . ").addTo(property_map);";

                            echo "
                            jQuery( '.w3w_location#w3w_location_" . $location_id . "' ).on( 'click', function() {
                                property_map.panTo(new L.LatLng(" . $coordinates_array[0] . ", " . $coordinates_array[2] . "));
                                property_map.setZoom(property_map.getMaxZoom());
                            });";
                        }
                        else
                        {
                            echo "
                            var rectangle" . $location_id . " = new google.maps.Rectangle({
                                strokeColor: '#000000',
                                strokeOpacity: 0.8,
                                strokeWeight: 1,
                                fillColor: '" . $square_colour . "',
                                fillOpacity: 0.35,
                                property_map,
                                bounds: {
                                    north: " . $coordinates_array[0] . ",
                                    south: " . $coordinates_array[1] . ",
                                    east: " . $coordinates_array[2] . ",
                                    west: " . $coordinates_array[3] . ",
                                },
                            });
                            rectangle" . $location_id . ".setMap(property_map);";

                            echo "
                            jQuery( '.w3w_location#w3w_location_" . $location_id . "' ).on( 'click', function() {
                                var rectangleCentre" . $location_id . " = new google.maps.LatLng(" . $coordinates_array[0] . ", " . $coordinates_array[2] . ");
                                property_map.setMapTypeId('satellite');
                                property_map.setCenter(rectangleCentre" . $location_id . ");
                                property_map.setZoom(20);
                            });";
                        }
                    }
                }
            }
        }
    }

    public function add_property_map_custom_locations_key()
    {
        global $post;
        if ( isset($post->ID) && get_post_type($post->ID) == 'property' )
        {
            $three_word_location = $this->get_saved_or_new_property_three_word_location($post->ID);

            if ( $three_word_location != '' )
            {
                $admin_custom_locations = array();
                $propertyhive_what3words = get_option( 'propertyhive_what3words' );
                if ( isset($propertyhive_what3words['location_types']) && count($propertyhive_what3words['location_types']) > 0 )
                {
                    $admin_custom_locations = $propertyhive_what3words['location_types'];
                }

                $marker_icon_url = plugins_url() . '/propertyhive-what3words/assets/img/maps_marker_icon.png';

                if ( class_exists( 'PH_Map_Search' ) )
                {
                    $map_search_settings = get_option( 'propertyhive_map_search', array() );
                    if ( isset($map_search_settings['icon_type']) && $map_search_settings['icon_type'] == 'custom_single' && isset($map_search_settings['custom_icon_attachment_id']) && $map_search_settings['custom_icon_attachment_id'] != '' )
                    {
                        $marker_icon_url = wp_get_attachment_url( $map_search_settings['custom_icon_attachment_id'] );
                    }
                }

                echo '
                    <h4>what3words Locations</h4>
                    <div class="w3w_location">
                        <div class="w3w_icon">
                            <img src="' . $marker_icon_url . '" >
                        </div>
                        <div class="w3w_three_words"><b>Property Location</b><br>' . $three_word_location . '</div>
                    </div>';

                $custom_locations = get_post_meta($post->ID, '_what3words_custom_locations', TRUE);
                if ( !empty($custom_locations) && is_array($custom_locations) )
                {
                    foreach ( $custom_locations as $location_id => $custom_location )
                    {
                        if ( isset($admin_custom_locations[$location_id]) )
                        {
                            echo '
                            <div class="w3w_location" id="w3w_location_' . $location_id . '">
                                <div class="w3w_icon">
                                    <div style="background-color:' . $admin_custom_locations[$location_id]['colour'] . '" class="w3w_colour_div"></div>
                                </div>
                                <div class="w3w_three_words"><b>'. $admin_custom_locations[$location_id]['name'] . '</b><br>' . $custom_location['location'] . '</div>
                            </div>';
                        }
                    }
                }
            }
        }
    }

    public function map_location_post_import($post_id, $property)
    {
        // When property is imported, get and save three-word-location if not set yet
        $this->get_saved_or_new_property_three_word_location( $post_id, true );
    }

    public function document_property_what3words_merge_tags($merge_tags, $post_id)
    {
        // Add additional custom field names to tags array
        $merge_tags[] = 'what3words_location';
        return $merge_tags;
    }

    public function document_property_what3words_merge_values($merge_values, $post_id)
    {
        $three_word_location = $this->get_saved_or_new_property_three_word_location($post_id);
        $merge_values[] = $three_word_location;
        return $merge_values;
    }

    public function replace_what3words_tag( $body, $viewing_id, $property_id )
    {
        $three_word_location = $this->get_saved_or_new_property_three_word_location( $property_id );
        return str_replace( '[what3words_location]', $three_word_location, $body );
    }

    public function get_saved_or_new_property_three_word_location( $post_id, $save_value = true )
    {
        $three_word_location = get_post_meta($post_id, '_what3words_location', TRUE);

        if ( $three_word_location == '' )
        {
            $latitude = get_post_meta($post_id, '_latitude', TRUE);
            $longitude = get_post_meta($post_id, '_longitude', TRUE);

            if ( $latitude != '' && $longitude != '' )
            {
                list($three_word_location, $square) = $this->get_three_word_location($latitude, $longitude);
            }

            if ( $save_value )
            {
                update_post_meta($post_id, '_what3words_location', $three_word_location);
            }
        }
        return $three_word_location;
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

        $propertyhive_what3words = get_option( 'propertyhive_what3words' );
        if (isset($_REQUEST['id'])) // we're either adding or editing
        {
            $current_id = empty( $_REQUEST['id'] ) ? '' : sanitize_text_field($_REQUEST['id']);

            if ( isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete' )
            {
                if ( isset($_POST['confirm_removal']) && $_POST['confirm_removal'] == 1 )
                {
                    // A term has just been deleted
                    $args = array();

                    $args[] = array( 'title' => __( 'Successfully Deleted Custom Location', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'custom_field_location_type_delete' );

                    $args[] = array(
                        'title'     => __( 'Custom Location Deleted', 'propertyhive' ),
                        'id'        => '',
                        'html'      => __('Custom Location deleted successfully', 'propertyhive' ) . ' <a href="' . admin_url( 'admin.php?page=ph-settings&tab=what3words' ) . '">' . __( 'Go Back', 'propertyhive' ) . '</a>',
                        'type'      => 'html',
                        'desc_tip'  =>  false,
                    );

                    $args[] = array( 'type' => 'sectionend', 'id' => 'custom_field_location_type_delete' );
                }
                else
                {
                    $location_type_name = '';
                    if ($current_id == '')
                    {
                        die("ID not passed");
                    }
                    else
                    {
                        $args = array();

                        $args[] = array( 'title' => __( 'Confirm Removal', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'location_type_confirm_delete' );

                        $args[] = array(
                            'title' => __( 'Confirm Removal?', 'propertyhive' ),
                            'id'        => 'confirm_removal',
                            'type'      => 'checkbox',
                            'desc_tip'  =>  false,
                        );

                        $args[] = array( 'type' => 'sectionend', 'id' => 'location_type_confirm_delete' );
                    }
                }
            }
            else
            {
                $location_type_name = '';
                if ( isset($propertyhive_what3words['location_types']) && isset($propertyhive_what3words['location_types'][$current_id]['name']) )
                {
                    $location_type_name = $propertyhive_what3words['location_types'][$current_id]['name'];
                }

                $location_type_colour = '';
                if ( isset($propertyhive_what3words['location_types']) && isset($propertyhive_what3words['location_types'][$current_id]['colour']) )
                {
                    $location_type_colour = $propertyhive_what3words['location_types'][$current_id]['colour'];
                }
                $args = array(

                    array( 'title' => __( ( $current_id == '' ? 'Add New Custom Location' : 'Edit Custom Location' ), 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'what3words_location_types' ),

                    array(
                        'title' => __( 'what3words Custom Location', 'propertyhive' ),
                        'id'        => 'location_type_name',
                        'default'   => $location_type_name,
                        'type'      => 'text',
                        'desc_tip'  =>  false,
                    ),

                    array(
                        'title'    => __( 'Colour', 'propertyhive' ),
                        'desc'     => __( 'The colour of the point on the map.', 'propertyhive' ),
                        'id'       => 'location_type_colour',
                        'type'     => 'color',
                        'css'      => 'width:6em;',
                        'default'  => $location_type_colour != '' ? $location_type_colour : '#333333',
                        'autoload' => false,
                        'desc_tip' => true,
                    ),

                    array( 'type' => 'sectionend', 'id' => 'what3words_location_types' )

                );
            }
            PH_Admin_Settings::output_fields( $args );
        }
        else
        {
            propertyhive_admin_fields( self::get_what3words_settings() );
            ?>
            <style>
                .colour-box {
                    float: left;
                    height: 20px;
                    width: 20px;
                    border: 1px solid black;
                }
            </style>
            <table class="form-table">
                <tr valign="top">
                    <td class="forminp forminp-button">
                        <a href="" class="button alignright batch-delete" disabled><?php echo __( 'Delete Selected', 'propertyhive' ); ?></a>
                        <a href="<?php echo admin_url( 'admin.php?page=ph-settings&tab=what3words&id=' ); ?>" class="button alignright"><?php echo __( 'Add New Custom Location', 'propertyhive' ); ?></a>
                    </td>
                </tr>
                <tr valign="top">
                    <td class="forminp">
                        <table class="ph_customfields widefat" cellspacing="0">
                            <thead>
                                <tr>
                                    <th class="cb" style="width:1px;">&nbsp;</th>
                                    <th class="id" style="width:45px;"><?php _e( 'ID', 'propertyhive' ); ?></th>
                                    <th class="type"><?php _e( 'Custom Location', 'propertyhive' ); ?></th>
                                    <th class="colour"><?php _e( 'Colour', 'propertyhive' ); ?></div></th>
                                    <th class="settings">&nbsp;</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                                if ( isset( $propertyhive_what3words['location_types'] ) )
                                {
                                    foreach ($propertyhive_what3words['location_types'] as $id => $location_type)
                                    {
                                ?>
                                <tr>
                                    <td class="cb"><input type="checkbox" name="location_type_id[]" value="<?php echo $id; ?>"></td>
                                    <td class="id"><?php echo $id; ?></td>
                                    <td class="type"><?php echo $location_type['name']; ?></td>
                                    <td class="colour">
                                        &nbsp;<?php echo $location_type['colour']; ?>
                                        <div style="background-color: <?php echo $location_type['colour']; ?>;" class='colour-box'></div>
                                    </td>
                                    <td class="settings">
                                        <a class="button" href="<?php echo admin_url( 'admin.php?page=ph-settings&tab=what3words&id=' . $id ); ?>"><?php echo __( 'Edit', 'propertyhive' ); ?></a>
                                        <a class="button" href="<?php echo admin_url( 'admin.php?page=ph-settings&tab=what3words&action=delete&id=' . $id ); ?>"><?php echo __( 'Delete', 'propertyhive' ); ?></a>
                                    </td>
                                </tr>
                                <?php
                                    }
                                }
                                else
                                {
                                ?>
                                <tr>
                                    <td colspan="3"><?php echo __( 'No custom locations found', 'propertyhive' ); ?></td>
                                </tr>
                                <?php
                                }
                            ?>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <tr valign="top">
                    <td class="forminp forminp-button">
                        <a href="" class="button alignright batch-delete" disabled><?php echo __( 'Delete Selected', 'propertyhive' ); ?></a>
                        <a href="<?php echo admin_url( 'admin.php?page=ph-settings&tab=what3words&id=' ); ?>" class="button alignright"><?php echo __( 'Add New Custom Location', 'propertyhive' ); ?></a>
                    </td>
                </tr>
            </table>
            <?php
        }
    }

    /**
     * Uses the Property Hive options API to save settings.
     *
     * @uses propertyhive_update_options()
     * @uses self::get_settings()
     */
    public function save() {

        $existing_propertyhive_what3words = get_option( 'propertyhive_what3words' );

        if ( isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete' )
        {
            if ( isset($_POST['confirm_removal']) && $_POST['confirm_removal'] == '1' )
            {
                $id_to_delete = empty( $_REQUEST['id'] ) ? '' : sanitize_text_field($_REQUEST['id']);
                unset($existing_propertyhive_what3words['location_types'][$id_to_delete]);
            }
            $propertyhive_what3words = $existing_propertyhive_what3words;
        }
        else
        {
            if (isset($_REQUEST['id'])) // we're either adding or editing
            {
                $current_id = empty( $_REQUEST['id'] ) ? '' : sanitize_text_field($_REQUEST['id']);

                if ( $current_id == '' )
                {
                    // Adding new custom location
                    $existing_propertyhive_what3words['location_types'][] = array(
                        'name' => ph_clean($_POST['location_type_name']),
                        'colour' => ph_clean($_POST['location_type_colour']),
                    );
                }
                else
                {
                    // Editing custom location
                    $existing_propertyhive_what3words['location_types'][$current_id] = array(
                        'name' => ph_clean($_POST['location_type_name']),
                        'colour' => ph_clean($_POST['location_type_colour']),
                    );
                }
                $propertyhive_what3words = $existing_propertyhive_what3words;
            }
            else
            {
                $propertyhive_what3words = array(
                    'api_key' => ( (isset($_POST['api_key'])) ? ph_clean($_POST['api_key']) : '' ),
                );

                $propertyhive_what3words = array_merge( $existing_propertyhive_what3words, $propertyhive_what3words );
            }
        }
        update_option( 'propertyhive_what3words', $propertyhive_what3words );
    }

    /**
     * Get what3words settings
     *
     * @return array Array of settings
     */
    public function get_what3words_settings() {

        $current_settings = get_option( 'propertyhive_what3words' );

        $settings = array(

            array( 'title' => __( 'what3words Settings', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'what3words_settings' )

        );

        $settings[] = array(
            'title'     => __( 'API Key', 'propertyhive' ),
            'id'        => 'api_key',
            'type'      => 'text',
            'default'   => ( isset($current_settings['api_key']) ? $current_settings['api_key'] : ''),
            'desc_tip'  =>  false,
        );

        $settings[] = array( 'type' => 'sectionend', 'id' => 'what3words_settings');

        return $settings;
    }
}

endif;

/**
 * Returns the main instance of PH_What3words to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return PH_What3words
 */
function PHW3W() {
    return PH_What3words::instance();
}

$PHW3W = PHW3W();

if( is_admin() && file_exists(  dirname( __FILE__ ) . '/propertyhive-what3words-update.php' ) )
{
    include_once( dirname( __FILE__ ) . '/propertyhive-what3words-update.php' );
}