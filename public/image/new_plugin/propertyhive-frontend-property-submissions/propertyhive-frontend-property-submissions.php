<?php
/**
 * Plugin Name: Property Hive Frontend Property Submissions Add On
 * Plugin Uri: http://wp-property-hive.com/addons/front-end-property-submissions/
 * Description: Add On for Property Hive allowing users to submit properties via the frontend of the website
 * Version: 1.0.12
 * Author: PropertyHive
 * Author URI: http://wp-property-hive.com
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'PH_Frontend_Property_Submissions' ) ) :

final class PH_Frontend_Property_Submissions {

    /**
     * @var string
     */
    public $version = '1.0.12';

    /**
     * @var PropertyHive The single instance of the class
     */
    protected static $_instance = null;
    
    /**
     * Main Property Hive Frontend Property Submissions Instance
     *
     * Ensures only one instance of Property Hive Frontend Property Submissions is loaded or can be loaded.
     *
     * @static
     * @return Property Hive Frontend Property Submissions - Main instance
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

        $this->id    = 'frontend-property-submissions';
        $this->label = __( 'Frontend Property Submissions', 'propertyhive' );

        // Define constants
        $this->define_constants();

        // Include required files
        $this->includes();

        add_action( 'init', array( $this, 'register_shortcodes') );

        add_action( 'wp_enqueue_scripts', array( $this, 'load_frontend_property_submissions_styles' ) );

        add_action( 'admin_notices', array( $this, 'frontend_property_submissions_error_notices') );

        add_filter( 'propertyhive_settings_tabs_array', array( $this, 'add_settings_tab' ), 19 );
        add_action( 'propertyhive_settings_' . $this->id, array( $this, 'output' ) );
        add_action( 'propertyhive_settings_save_' . $this->id, array( $this, 'save' ) );

        add_action( 'wp_ajax_propertyhive_frontend_property_submission', array( $this, 'ajax_propertyhive_frontend_property_submission' ) );
        add_action( 'wp_ajax_nopriv_propertyhive_frontend_property_submission', array( $this, 'ajax_propertyhive_frontend_property_submission' ) );

        add_filter( 'propertyhive_admin_property_column_post_address_output', array( $this, 'flag_properties_awaiting_approval' ), 1 );

        add_action( 'propertyhive_property_record_details_fields', array( $this, 'add_propertyhive_property_record_details_fields'), 1, 1 );
        add_action( 'propertyhive_save_property_record_details', array( $this, 'do_propertyhive_save_property_record_details'), 1 );

        add_filter( 'propertyhive_my_account_pages', array( $this, 'my_account_submitted_properties') );
        add_action( 'propertyhive_my_account_section_submitted_properties', array( $this, 'submitted_properties_my_account_content' ) );
    }

    public function my_account_submitted_properties( $pages )
    {
        $user_id = get_current_user_id();

        $args = array(
            'post_type'   => 'property', 
            'nopaging'    => true,
            'post_status'   => 'publish',
            'fields' => 'ids',
            'meta_query'  => array(
                array(
                    'key' => '_frontend_submission_user_id',
                    'value' => $user_id
                )
            )
        );
        $properties_query = new WP_Query( $args );

        if ( $properties_query->have_posts() )
        {
            $pages['submitted_properties'] = array(
                'name' => __( 'Submitted Properties', 'propertyhive' ),
            );
        }
        wp_reset_postdata();

        return $pages;
    }

    public function submitted_properties_my_account_content()
    {
        echo do_shortcode('[propertyhive_submitted_properties]');
    }

    public function flag_properties_awaiting_approval( $post_address_output = '' )
    {
        global $post;

        if ( get_post_status( $post->ID ) == 'draft' && get_post_meta( $post->ID, '_frontend_submission_user_id', TRUE ) != '' )
        {
            return __( "Awaiting Moderation", 'propertyhive' );
        }

        return $post_address_output;
    }

    private function includes()
    {
        include_once( 'includes/class-ph-frontend-property-submissions-install.php' );
    }

    public function load_frontend_property_submissions_styles()
    {
        $assets_path = str_replace( array( 'http:', 'https:' ), '', untrailingslashit( plugins_url( '/', __FILE__ ) ) ) . '/assets/';

        if (!is_admin())
        {
            wp_enqueue_style( 'propertyhive_frontend_property_submission_css', $assets_path . 'css/frontend.css', array(), PH_FRONTEND_PROPERTY_SUBMISSIONS_VERSION );
        }
    }

    public function add_propertyhive_property_record_details_fields()
    {
        global $post;

        $current_user_id = get_post_meta( $post->ID, '_frontend_submission_user_id', TRUE );

        ?>
        <p class="form-field submitted_by_field">
            <label for="_frontend_submission_user_id"><?php echo __('Submitted By', 'propertyhive'); ?></label>
            <select id="_frontend_submission_user_id" name="_frontend_submission_user_id" class="select short">
            <option value="" <?php echo empty($current_user_id) ? ' selected' : '' ?>>None</option>
            <?php

            $args = array(
                'orderby' => 'display_name',
                'order' => 'ASC',
            );
            $users = get_users($args);
            foreach ($users as $user)
            {
                echo '<option value="' . $user->ID . '"';
                if (!empty($current_user_id) && $user->ID == $current_user_id)
                {
                    echo ' selected';
                }
                echo '>' . $user->display_name . '</option>';
            }
            ?>
            </select>
        </p>
        <?php
    }

    public function do_propertyhive_save_property_record_details()
    {
        global $post;
        if ( isset($_POST['_frontend_submission_user_id']) )
        {
            if ( (int)$_POST['_frontend_submission_user_id'] == 0 )
            {
                delete_post_meta( $post->ID, '_frontend_submission_user_id' );
            }
            else
            {
                update_post_meta( $post->ID, '_frontend_submission_user_id', (int)$_POST['_frontend_submission_user_id'] );
            }
        }
    }

    public function ajax_propertyhive_frontend_property_submission()
    {
        global $current_user;

        $return = array();

        // Validate
        $errors = array();

        if (!isset($_POST['display_address']) || (isset($_POST['display_address']) && trim(wp_strip_all_tags( $_POST['display_address'])) == ''))
        {
            $errors[] = __( 'Display Address is a required field', 'propertyhive' );
        }

        if (!isset($_POST['department']) || (isset($_POST['department']) && trim(wp_strip_all_tags( $_POST['department'])) == ''))
        {
            $errors[] = __( 'Department is a required field', 'propertyhive' );
        }

        if (isset($_POST['department']) && $_POST['department'] == 'residential-sales')
        {
            if (!isset($_POST['price']) || (isset($_POST['price']) && (trim(wp_strip_all_tags( $_POST['price'])) == '' || trim(wp_strip_all_tags( $_POST['price'])) == '0')))
            {
                $errors[] = __( 'Price is a required field', 'propertyhive' );
            }
        }

        if (isset($_POST['department']) && $_POST['department'] == 'residential-lettings')
        {
            if (!isset($_POST['rent']) || (isset($_POST['rent']) && (trim(wp_strip_all_tags( $_POST['rent'])) == '' || trim(wp_strip_all_tags( $_POST['rent'])) == '0')))
            {
                $errors[] = __( 'Rent is a required field', 'propertyhive' );
            }
        }

        if ( !empty($errors) )
        {
            // Failed validation
            
            $return['success'] = false;
            $return['reason'] = 'validation';
            $return['errors'] = $errors;

            header( 'Content-Type: application/json; charset=utf-8' );
            echo json_encode( $return );
            
            // Quit out
            die();
        }

        $settings = get_option( 'propertyhive_frontend_property_submissions', array() );

        // Upload media first
        $uploads_dir = sys_get_temp_dir();

        $uploaded_media = array();

        $media_types = array(
            'photo',
            'floorplan',
            'brochure',
            'epc'
        );
        foreach ( $media_types as $media_type )
        {
            if ( isset($_FILES[$media_type]))
            {
                foreach ( $_FILES[$media_type]["error"] as $key => $error ) 
                {
                    $tmp_name = $_FILES[$media_type]["tmp_name"][$key];
                    $name = $_FILES[$media_type]["name"][$key];

                    if ( $error == UPLOAD_ERR_OK ) 
                    {
                        if ( move_uploaded_file($tmp_name, "$uploads_dir/$name") )
                        {
                            if ( !isset($uploaded_media[$media_type]) )
                            {
                                $uploaded_media[$media_type] = array();
                            }
                            $uploaded_media[$media_type][] = "$uploads_dir/$name";
                        }
                        else
                        {
                            $return['success'] = false;
                            $return['reason'] = 'validation';
                            $return['errors'] = 'Error moving ' . $media_type . ' ' . $name;

                            // cleanup files
                            if ( !empty($uploaded_media) )
                            {
                                foreach ( $uploaded_media as $type => $files )
                                {
                                    foreach ( $files as $file )
                                    {
                                        unlink($file);
                                    }
                                }
                            }

                            header( 'Content-Type: application/json; charset=utf-8' );
                            echo json_encode( $return );
                            
                            // Quit out
                            die();
                        }
                    }
                    else
                    {
                        $return['success'] = false;
                        $return['reason'] = 'validation';
                        $return['errors'] = 'Error uploading ' . $media_type . ' ' . $name;

                        // cleanup files
                        if ( !empty($uploaded_media) )
                        {
                            foreach ( $uploaded_media as $type => $files )
                            {
                                foreach ( $files as $file )
                                {
                                    unlink($file);
                                }
                            }
                        }
                        
                        header( 'Content-Type: application/json; charset=utf-8' );
                        echo json_encode( $return );
                        
                        // Quit out
                        die();
                    }
                }
            }
        }

        $default_country = get_option( 'propertyhive_default_country', 'GB' );

        if ( isset($_POST['property_post_id']) && $_POST['property_post_id'] != '' )
        {
            // Updating property post. Firstly make sure it belongs to the logged in user
            $added_by_user_id = get_post_meta( (int)$_POST['property_post_id'], '_frontend_submission_user_id', TRUE );
            if ( get_current_user_id() != $added_by_user_id )
            {
                die("Trying to edit a property which doesn't belong to you");
            }

            // Update property post
            $postdata = array(
                'ID'           => $_POST['property_post_id'],
                'post_excerpt'   => $_POST['excerpt'],
                'post_title'     => wp_strip_all_tags( ph_clean($_POST['display_address']) ),
            );

            $post_id = wp_update_post( $postdata, true );
        }
        else
        {
            // Insert property post
            $postdata = array(
                'post_excerpt'   => $_POST['excerpt'],
                'post_content'   => '',
                'post_title'     => wp_strip_all_tags( ph_clean($_POST['display_address']) ),
                'post_status'    => ( ( isset($settings['moderate']) && $settings['moderate'] == 'no' ) ? 'publish' : 'draft' ),
                'post_type'      => 'property',
                'comment_status' => 'closed',
            );

            $post_id = wp_insert_post( $postdata, true );

            update_post_meta( $post_id, '_on_market', 'yes' );

            update_post_meta( $post_id, '_address_country', $default_country );
        }

        if ( is_wp_error( $post_id ) ) 
        {
            $return['success'] = false;
            $return['reason'] = 'error-inserting-post';

            header( 'Content-Type: application/json; charset=utf-8' );
            echo json_encode( $return );
            
            // Quit out
            die();
        }

        update_post_meta( $post_id, '_frontend_submission', '1' );
        update_post_meta( $post_id, '_frontend_submission_user_id', get_current_user_id() );

        // Add address meta data
        update_post_meta( $post_id, '_reference_number', ph_clean($_POST['reference_number']) );
        update_post_meta( $post_id, '_address_name_number', ph_clean($_POST['address_name_number']) );
        update_post_meta( $post_id, '_address_street', ph_clean($_POST['address_street']) );
        update_post_meta( $post_id, '_address_two', ph_clean($_POST['address_two']) );
        update_post_meta( $post_id, '_address_three', ph_clean($_POST['address_three']) );
        update_post_meta( $post_id, '_address_four', ph_clean($_POST['address_four']) );
        update_post_meta( $post_id, '_address_postcode', ph_clean($_POST['address_postcode']) );

        if ( ini_get('allow_url_fopen') )
        {
            $lat = get_post_meta( $post_id, '_latitude', TRUE);
            $lng = get_post_meta( $post_id, '_longitude', TRUE);

            if ( $lat == '' || $lng == '' )
            {
                $api_key = get_option('propertyhive_google_maps_api_key', '');
                if ( $api_key != '' )
                {
                    // No lat lng. Let's get it
                    $address_to_geocode = array();
                    if ( isset($_POST['address_name_number']) && trim($_POST['address_name_number']) != '' ) { $address_to_geocode[] = ph_clean($_POST['address_name_number']); }
                    if ( isset($_POST['address_street']) && trim($_POST['address_street']) != '' ) { $address_to_geocode[] = ph_clean($_POST['address_street']); }
                    if ( isset($_POST['address_two']) && trim($_POST['address_two']) != '' ) { $address_to_geocode[] = ph_clean($_POST['address_two']); }
                    if ( isset($_POST['address_three']) && trim($_POST['address_three']) != '' ) { $address_to_geocode[] = ph_clean($_POST['address_three']); }
                    if ( isset($_POST['address_four']) && trim($_POST['address_four']) != '' ) { $address_to_geocode[] = ph_clean($_POST['address_four']); }
                    if ( isset($_POST['address_postcode']) && trim($_POST['address_postcode']) != '' ) { $address_to_geocode[] = ph_clean($_POST['address_postcode']); }

                    if (!empty($address_to_geocode))
                    {
                        $request_url = "http://maps.googleapis.com/maps/api/geocode/xml?address=" . urlencode( implode( ", ", $address_to_geocode ) ) . "&sensor=false&region=" . strtolower($default_country); // the request URL you'll send to google to get back your XML feed

                        if ( $api_key != '' ) { $request_url .= "&key=" . $api_key; }

                        $xml = simplexml_load_file($request_url);

                        if ( $xml !== FALSE )
                        {
                            $status = $xml->status; // Get the request status as google's api can return several responses
                            
                            if ($status == "OK") 
                            {
                                //request returned completed time to get lat / lang for storage
                                $lat = (string)$xml->result->geometry->location->lat;
                                $lng = (string)$xml->result->geometry->location->lng;
                                
                                if ($lat != '' && $lng != '')
                                {
                                    update_post_meta( $post_id, '_latitude', $lat );
                                    update_post_meta( $post_id, '_longitude', $lng );
                                }
                            }
                        }
                    }
                }
            }
        }

        // Add details meta data
        update_post_meta( $post_id, '_department', ph_clean($_POST['department']) );

        if ( !empty($_POST['availability']) )
        {
            wp_set_post_terms( $post_id, ph_clean($_POST['availability']), 'availability' );
        }

        $ph_countries = new PH_Countries();
        $country = $ph_countries->get_country( $default_country );
        $currency = $country['currency_code'];

        if ( $_POST['department'] == 'residential-sales' )
        {
            update_post_meta( $post_id, '_currency', $currency );

            $price = preg_replace("/[^0-9]/", '', ph_clean($_POST['price']));
            update_post_meta( $post_id, '_price', $price );
            
            // Store price in common currency (GBP) used for ordering
            $ph_countries = new PH_Countries();
            $ph_countries->update_property_price_actual( $post_id );

            update_post_meta( $post_id, '_poa', ( isset($_POST['sale_poa']) ? ph_clean($_POST['sale_poa']) : '' ) );
            
            if ( !empty($_POST['price_qualifier']) )
            {
                wp_set_post_terms( $post_id, ph_clean($_POST['price_qualifier']), 'price_qualifier' );
            }
            
            if ( !empty($_POST['sale_by']) )
            {
                wp_set_post_terms( $post_id, ph_clean($_POST['sale_by']), 'sale_by' );
            }
            
            if ( !empty($_POST['tenure']) )
            {
                wp_set_post_terms( $post_id, ph_clean($_POST['tenure']), 'tenure' );
            }
        }

        if ( $_POST['department'] == 'residential-lettings' )
        {
            update_post_meta( $post_id, '_currency', $currency );

            $rent = preg_replace("/[^0-9.]/", '', ph_clean($_POST['rent']));
            update_post_meta( $post_id, '_rent', $rent );
            update_post_meta( $post_id, '_rent_frequency', ph_clean($_POST['rent_frequency']) );
            
            // Store price in common currency (GBP) and frequency (PCM) used for ordering
            $ph_countries = new PH_Countries();
            $ph_countries->update_property_price_actual( $post_id );

            update_post_meta( $post_id, '_poa', ( isset($_POST['rent_poa']) ? ph_clean($_POST['rent_poa']) : '' ) );
            
            update_post_meta( $post_id, '_deposit', preg_replace("/[^0-9.]/", '', ph_clean($_POST['deposit'])) );
            update_post_meta( $post_id, '_available_date', $_POST['available_date'] );
            
            if ( !empty($_POST['furnished']) )
            {
                wp_set_post_terms( $post_id, ph_clean($_POST['furnished']), 'furnished' );
            }
        }

        if ( $_POST['department'] == 'residential-sales' || $_POST['department'] == 'residential-lettings' )
        {
            $rooms = preg_replace("/[^0-9]/", '', ph_clean($_POST['bedrooms']));
            update_post_meta( $post_id, '_bedrooms', $rooms );

            $rooms = preg_replace("/[^0-9]/", '', ph_clean($_POST['bathrooms']));
            update_post_meta( $post_id, '_bathrooms', $rooms );

            $rooms = preg_replace("/[^0-9]/", '', ph_clean($_POST['reception_rooms']));
            update_post_meta( $post_id, '_reception_rooms', $rooms );
            
            if ( !empty($_POST['property_type']) )
            {
                wp_set_post_terms( $post_id, ph_clean($_POST['property_type']), 'property_type' );
            }

            if ( !empty($_POST['parking']) )
            {
                wp_set_post_terms( $post_id, ph_clean($_POST['parking']), 'parking' );
            }
        }

        if ( $_POST['department'] == 'commercial' )
        {
            update_post_meta( $post_id, '_for_sale', '' );
            update_post_meta( $post_id, '_to_rent', '' );

            if ( isset($_POST['for_sale']) && $_POST['for_sale'] == 'yes' )
            {
                update_post_meta( $post_id, '_for_sale', 'yes' );

                update_post_meta( $post_id, '_commercial_price_currency', $currency );

                $price = preg_replace("/[^0-9.]/", '', ph_clean($_POST['price_from']));
                if ( $price == '' )
                {
                    $price = preg_replace("/[^0-9.]/", '', ph_clean($_POST['price_to']));
                }
                update_post_meta( $post_id, '_price_from', $price );

                $price = preg_replace("/[^0-9.]/", '', ph_clean($_POST['price_to']));
                if ( $price == '' )
                {
                    $price = preg_replace("/[^0-9.]/", '', ph_clean($_POST['price_from']));
                }
                update_post_meta( $post_id, '_price_to', $price );

                update_post_meta( $post_id, '_price_units', ph_clean($_POST['price_units']) );

                update_post_meta( $post_id, '_price_poa', '' );
            }

            if ( isset($_POST['to_rent']) && $_POST['to_rent'] == 'yes' )
            {
                update_post_meta( $post_id, '_to_rent', 'yes' );

                update_post_meta( $post_id, '_commercial_rent_currency', $currency );

                $rent = preg_replace("/[^0-9.]/", '', ph_clean($_POST['rent_from']));
                if ( $rent == '' )
                {
                    $rent = preg_replace("/[^0-9.]/", '', ph_clean($_POST['rent_to']));
                }
                update_post_meta( $post_id, '_rent_from', $rent );

                $rent = preg_replace("/[^0-9.]/", '', ph_clean($_POST['rent_to']));
                if ( $rent == '' )
                {
                    $rent = preg_replace("/[^0-9.]/", '', ph_clean($_POST['rent_from']));
                }
                update_post_meta( $post_id, '_rent_to', $rent );

                update_post_meta( $post_id, '_rent_units', ph_clean($_POST['rent_units']) );

                update_post_meta( $post_id, '_rent_poa', '' );
            }

            $ph_countries = new PH_Countries();
            $ph_countries->update_property_price_actual( $post_id );

            if ( !empty($_POST['commercial_property_type']) )
            {
                wp_set_post_terms( $post_id, ph_clean($_POST['commercial_property_type']), 'commercial_property_type' );
            }

            $size = preg_replace("/[^0-9.]/", '', ph_clean($_POST['floor_area_from']));
            if ( $size == '' )
            {
                $size = preg_replace("/[^0-9.]/", '', ph_clean($_POST['floor_area_to']));
            }
            update_post_meta( $post_id, '_floor_area_from', $size );

            update_post_meta( $post_id, '_floor_area_from_sqft', convert_size_to_sqft( $size, ph_clean($_POST['floor_area_units']) ) );

            $size = preg_replace("/[^0-9.]/", '', ph_clean($_POST['floor_area_to']));
            if ( $size == '' )
            {
                $size = preg_replace("/[^0-9.]/", '', ph_clean($_POST['floor_area_from']));
            }
            update_post_meta( $post_id, '_floor_area_to', $size );

            update_post_meta( $post_id, '_floor_area_to_sqft', convert_size_to_sqft( $size, ph_clean($_POST['floor_area_units']) ) );

            update_post_meta( $post_id, '_floor_area_units', ph_clean($_POST['floor_area_units']) );
        }

        // Add features
        if ( get_option('propertyhive_features_type') == 'checkbox' )
        {
            $features = array();
            if ( isset( $_POST['feature'] ) && !empty( $_POST['feature'] ) )
            {
                foreach ( $_POST['feature'] as $feature_id )
                {
                    $features[] = (int)$feature_id;
                }
            }
            if ( !empty($features) )
            {
                wp_set_post_terms( $post_id, $features, 'property_feature' );
            }
            else
            {
                wp_delete_object_term_relationships( $post_id, 'property_feature' );
            }
        }
        else
        {
            if ( isset($_POST['feature']) && is_array($_POST['feature']) && !empty($_POST['feature']) )
            {
                $i = 0;
                foreach ( $_POST['feature'] as $feature )
                {
                    update_post_meta($post_id, '_feature_' . $i, ph_clean($feature));

                    ++$i;
                }

                update_post_meta($post_id, '_features', $i);
            }
        }

        // Add descriptions meta data
        if ( $_POST['department'] == 'residential-sales' || $_POST['department'] == 'residential-lettings' )
        {
            update_post_meta($post_id, '_rooms', 1 );
            update_post_meta($post_id, '_room_name_0', '');
            update_post_meta($post_id, '_room_dimensions_0', '');
            update_post_meta($post_id, '_room_description_0', strip_tags($_POST['full_description'], '<b><strong><i><em><a>'));
        }
        elseif ( $_POST['department'] == 'commercial' )
        {
            update_post_meta( $post_id, '_descriptions', 1 );
            update_post_meta( $post_id, '_description_name_0', '' );
            update_post_meta( $post_id, '_description_0', strip_tags($_POST['full_description'], '<b><strong><i><em><a>'));
        }

        $files_to_unlink = array();

        foreach ( $media_types as $media_type )
        {   
            $existing_media_ids = get_post_meta( $post_id, '_' . $media_type . 's', TRUE );
            if ( !is_array($existing_media_ids) )
            {
                $existing_media_ids = array();
            }

            if ( isset($_POST['delete_' . $media_type]) && !empty($_POST['delete_' . $media_type]) )
            {
                foreach ( $_POST['delete_' . $media_type] as $delete_attachment_id )
                {
                    if (($key = array_search($delete_attachment_id, $existing_media_ids)) !== false) 
                    {
                        unset($existing_media_ids[$key]);
                    }
                }
            }

            $media_ids = $existing_media_ids;

            if ( isset($uploaded_media[$media_type]) &&  !empty($uploaded_media[$media_type]) )
            {
                foreach ( $uploaded_media[$media_type] as $file)
                {
                    $upload = wp_upload_bits(basename($file), null, file_get_contents($file));  
                                        
                    if( isset($upload['error']) && $upload['error'] !== FALSE )
                    {
                        // Need to do something here
                    }
                    else
                    {
                        // We don't already have a thumbnail and we're presented with an image
                        $wp_filetype = wp_check_filetype( $upload['file'], null );
                    
                        $attachment = array(
                             //'guid' => $wp_upload_dir['url'] . '/' . trim($media_file_name, '_'), 
                             'post_mime_type' => $wp_filetype['type'],
                             'post_title' => basename($file),
                             'post_content' => '',
                             'post_status' => 'inherit'
                        );
                        $attach_id = wp_insert_attachment( $attachment, $upload['file'], $post_id );
                        
                        $attach_data = wp_generate_attachment_metadata( $attach_id, $upload['file'] );
                        wp_update_attachment_metadata( $attach_id,  $attach_data );

                        $media_ids[] = $attach_id;
                    }

                    $files_to_unlink[] = $file;
                }
            }
            update_post_meta( $post_id, '_' . $media_type . 's', $media_ids );
        }

        if ( !empty($files_to_unlink) )
        {
            $files_to_unlink = array_unique($files_to_unlink);

            foreach ($files_to_unlink as $file_to_unlink)
            {
                unlink($file_to_unlink);
            }
        }

        if ( isset($_POST['virtual_tour']) && trim($_POST['virtual_tour']) != '' )
        {
            update_post_meta($post_id, '_virtual_tours', '1' );
            update_post_meta($post_id, '_virtual_tour_0', trim(ph_clean($_POST['virtual_tour'])));
        }

        do_action( 'propertyhive_save_submit_property_fields', $post_id );

        $return['success'] = true;

        header( 'Content-Type: application/json; charset=utf-8' );
        echo json_encode( $return );

        // Quit out
        die();
    }

    /**
     * Define PH Frontend Property Submissions Constants
     */
    private function define_constants() 
    {
        define( 'PH_FRONTEND_PROPERTY_SUBMISSIONS_PLUGIN_FILE', __FILE__ );
        define( 'PH_FRONTEND_PROPERTY_SUBMISSIONS_VERSION', $this->version );
    }

    public function register_shortcodes()
    {
        add_shortcode( apply_filters( "propertyhive_submitted_properties_shortcode_tag", 'propertyhive_submitted_properties' ), array( $this, 'register_shortcode_propertyhive_submitted_properties' ) );
        add_shortcode( apply_filters( "propertyhive_submit_property_form_shortcode_tag", 'propertyhive_submit_property_form' ), array( $this, 'register_shortcode_propertyhive_submit_property_form' ) );
        
    }

    public function register_shortcode_propertyhive_submit_property_form()
    {
        global $post, $current_user;

        $original_post = $post;

        if ( isset($_GET['property_post_id']) && $_GET['property_post_id'] != '' )
        {
            // Check property being edited was added by the logged in user
            $added_by_user_id = get_post_meta( $_GET['property_post_id'], '_frontend_submission_user_id', TRUE );
            if ( get_current_user_id() != $added_by_user_id )
            {
                die("Trying to edit a property which doesn't belong to you");
            }

            $post = get_post($_GET['property_post_id']);

            $property = new PH_Property( $post->ID );
        }

        ob_start();

        $template = locate_template( array(PH_TEMPLATE_PATH . 'submit-property-form.php') );
        if ( !$template )
        {
            include( dirname( PH_FRONTEND_PROPERTY_SUBMISSIONS_PLUGIN_FILE ) . '/templates/submit-property-form.php' );
        }
        else
        {
            include( $template );
        }

        $post = $original_post;

        return ob_get_clean();
    }

    public function register_shortcode_propertyhive_submitted_properties()
    {
        global $current_user;
       
        ob_start();

        $settings = get_option( 'propertyhive_frontend_property_submissions', array() );

        $args = array(
            'post_type' => 'property',
            'post_status' => array('publish', 'draft'),
            'nopaging' => TRUE,
            'meta_query' => array(
                array(
                    'key' => '_frontend_submission_user_id',
                    'value' => get_current_user_id(),
                    'compare' => '='
                )
            )
        );

        $property_query = new WP_Query( $args );

        $template = locate_template( array(PH_TEMPLATE_PATH . 'submitted-properties.php') );
        if ( !$template )
        {
            include( dirname( PH_FRONTEND_PROPERTY_SUBMISSIONS_PLUGIN_FILE ) . '/templates/submitted-properties.php' );
        }
        else
        {
            include( $template );
        }
        
        return ob_get_clean();
    }

    /**
     * Output error message if core Property Hive plugin isn't active
     */
    public function frontend_property_submissions_error_notices() 
    {
        global $post;

        if (!is_plugin_active('propertyhive/propertyhive.php'))
        {
            $message = __( "The Property Hive plugin must be installed and activated before you can use the Property Hive Frontend Property Submissions add-on", 'propertyhive' );
            echo "<div class=\"error\"> <p>$message</p></div>";
        }

        $screen = get_current_screen();
        if ($screen->id == 'property' && $post->post_type == 'property' && $post->post_status == 'draft' && get_post_meta($post->ID, '_frontend_submission', true) == '1')
        {
            $message = __( "This property was submitted via the frontend and is awaiting approval. Please carefully check the details entered and click 'Publish' to approve it", 'propertyhive' );
            echo "<div class=\"notice notice-info\"> <p>$message</p></div>";
        }
    }

    /**
     * Add a new settings tab to the Property Hive settings tabs array.
     *
     * @param array $settings_tabs Array of Property Hive setting tabs & their labels, excluding the Subscription tab.
     * @return array $settings_tabs Array of Property Hive setting tabs & their labels, including the Subscription tab.
     */
    public function add_settings_tab( $settings_tabs ) {
        $settings_tabs['frontend-property-submissions'] = __( 'Frontend Submissions', 'propertyhive' );
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
        
        propertyhive_admin_fields( self::get_frontend_property_submissions_settings() );
    }

    /**
     * Uses the Property Hive options API to save settings.
     *
     * @uses propertyhive_update_options()
     * @uses self::get_settings()
     */
    public function save() {

        $propertyhive_frontend_property_submissions = array(
            'logged_in' => ( (isset($_POST['logged_in'])) ? $_POST['logged_in'] : '' ),
            'login_url' => $_POST['login_url'],
            'register_url' => $_POST['register_url'],
            'moderate' => $_POST['moderate'],
            'allow_editing' => $_POST['allow_editing'],
            'edit_url' => $_POST['edit_url'],
        );

        update_option( 'propertyhive_frontend_property_submissions', $propertyhive_frontend_property_submissions );
    }

    /**
     * Get frontend property submissions settings
     *
     * @return array Array of settings
     */
    public function get_frontend_property_submissions_settings() {

        $current_settings = get_option( 'propertyhive_frontend_property_submissions', array() );

        $settings = array(

            array( 'title' => __( 'Frontend Property Submissions Settings', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'frontend_property_submission_settings' )

        );

        $settings[] = array(
            'title' => __( 'User must be logged in?', 'propertyhive' ),
            'id'        => 'logged_in',
            'type'      => 'checkbox',
            'default'   => ( (isset($current_settings['logged_in']) && $current_settings['logged_in'] == 1) ? 'yes' : ''),
            'desc'      => __( 'If ticked and a user tries to enter a property that isn\'t logged in they\'ll be shown links to login and register', 'propertyhive' ),
        );

        $settings[] = array(
            'title' => __( 'Login URL', 'propertyhive' ),
            'id'        => 'login_url',
            'type'      => 'text',
            'default'   => ( (isset($current_settings['login_url'])) ? $current_settings['login_url'] : wp_login_url() ),
            'desc'      => __( 'Only applicable if users must be logged in to submit properties', 'propertyhive' ),
        );

        $settings[] = array(
            'title' => __( 'Register URL', 'propertyhive' ),
            'id'        => 'register_url',
            'type'      => 'text',
            'default'   => ( (isset($current_settings['register_url'])) ? $current_settings['register_url'] : wp_registration_url() ),
            'desc'      => __( 'Only applicable if users must be logged in to submit properties', 'propertyhive' ),
        );

        $settings[] = array(
            'title' => __( 'When properties are added', 'propertyhive' ) . '...',
            'id'        => 'moderate',
            'type'      => 'select',
            'default'   => ( isset($current_settings['moderate']) ? $current_settings['moderate'] : 'yes'),
            'options'   => array(
                'yes' => 'They must be approved before going live',
                'no' => 'Automatically make them live'
            )
        );

        $settings[] = array(
            'title' => __( 'Allow editing of properties?', 'propertyhive' ),
            'id'        => 'allow_editing',
            'type'      => 'checkbox',
            'default'   => ( (isset($current_settings['allow_editing']) && $current_settings['allow_editing'] == 1) ? 'yes' : ''),
            'desc'      => __( 'Should logged in users be able to edit properties they\'ve added?', 'propertyhive' ),
        );

        $settings[] = array(
            'title' => __( 'Edit Property URL', 'propertyhive' ),
            'id'        => 'edit_url',
            'type'      => 'text',
            'default'   => ( (isset($current_settings['edit_url'])) ? $current_settings['edit_url'] : '' ),
            'desc'      => __( 'Only applicable if users are allowed to edit properties. Should be to a page that contains the form shortcode', 'propertyhive' ),
        );

        $settings[] = array( 'type' => 'sectionend', 'id' => 'frontend_property_submission_settings');


        /*$settings[] = array( 'title' => __( 'Payment Settings', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'frontend_property_submission_payment_settings' );

        $settings[] = array(
            'type'      => 'html',
            'title'     => __( 'About Payment', 'propertyhive' ),
            'html'      => '<p>With <a href="https://en-gb.wordpress.org/plugins/woocommerce/" target="_blank">WooCommerce</a> enabled you can take payments from users before they\'re permitted to submit properties.</pre>'
        );

        $settings[] = array(
            'title' => __( 'Enable Payment Integration', 'propertyhive' ),
            'id'        => 'enable_payment',
            'type'      => 'checkbox',
            'default'   => ( (isset($current_settings['enable_payment']) && $current_settings['enable_payment'] == 1) ? 'yes' : ''),
            'desc'      => ( ( !class_exists( 'WooCommerce' ) ) ? __( 'WooCommerce must be installed and activated', 'propertyhive' ) : '' ),
        );

        $settings[] = array( 'type' => 'sectionend', 'id' => 'frontend_property_submission_payment_settings');*/

        $settings[] = array( 'title' => __( 'Shortcodes', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'frontend_property_submission_shortcodes' );

        $settings[] = array(
            'type'      => 'html',
            'title'     => __( 'Shortcodes', 'propertyhive' ),
            'html'      => '<p>' . __( 'To display the form', 'propertyhive' ) . ':</p>
                            <pre>[propertyhive_submit_property_form]</pre>
                            <p>' . __( 'To display a list of the properties added by the user (only applicable when users must be logged in to submit properties)', 'propertyhive' ) . ':</p>
                            <pre>[propertyhive_submitted_properties]</pre>'
        );

        $settings[] = array( 'type' => 'sectionend', 'id' => 'frontend_property_submission_shortcodes');

        return $settings;
    }
}

endif;

/**
 * Returns the main instance of PH_Frontend_Property_Submissions to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return PH_Frontend_Property_Submissions
 */
function PHFPS() {
    return PH_Frontend_Property_Submissions::instance();
}

PHFPS();

if( is_admin() && file_exists(  dirname( __FILE__ ) . '/propertyhive-frontend-property-submissions-update.php' ) )
{
    include_once( dirname( __FILE__ ) . '/propertyhive-frontend-property-submissions-update.php' );
}