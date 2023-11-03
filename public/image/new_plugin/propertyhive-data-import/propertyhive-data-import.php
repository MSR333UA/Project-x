<?php
/**
 * Plugin Name: Property Hive Data Import Add On
 * Plugin Uri: http://wp-property-hive.com/addons/data-import/
 * Description: Add On for Property Hive allowing you to import contacts, viewings and more
 * Version: 1.0.13
 * Author: PropertyHive
 * Author URI: http://wp-property-hive.com
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'PH_Data_Import' ) ) :

final class PH_Data_Import {

    /**
     * @var string
     */
    public $version = '1.0.13';

    /**
     * @var PropertyHive The single instance of the class
     */
    protected static $_instance = null;
    
    /**
     * Main Property Hive Data Import Instance
     *
     * Ensures only one instance of Property Hive Data Import is loaded or can be loaded.
     *
     * @static
     * @return Property Hive Data Import - Main instance
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

        $this->id    = 'data-import';
        $this->label = __( 'Data Import', 'propertyhive' );

        // Define constants
        $this->define_constants();

        // Include required files
        $this->includes();

        add_action( 'admin_notices', array( $this, 'data_import_error_notices') );

        add_action( 'admin_menu', array( $this, 'admin_menu' ) );

        add_filter( "plugin_action_links_" . plugin_basename( __FILE__ ), array( $this, 'plugin_add_settings_link' ) );
    }

    /**
     * Admin Menu
     */
    public function admin_menu() 
    {
        add_submenu_page( 'propertyhive', __( 'Import Data', 'propertyhive' ),  __( 'Import Data', 'propertyhive' ) , 'manage_propertyhive', 'propertyhive_import_data', array( $this, 'admin_page' ) );
    }

    /**
     * Admin Page
     */
    public function admin_page() 
    {
        global $propertyhive;

        $include = 'main.php';
        $errors = array();

        // APPLICANTS 
        if ( isset($_POST['applicant_csv_nonce']) )
        {
            @set_time_limit(0);

            if ( !wp_verify_nonce( $_POST['applicant_csv_nonce'], 'import-applicant-csv' ) )
            {
                $errors[] = 'Invalid nonce. Please try again';
            }
            
            list($errors, $target_file) = $this->handle_csv_upload('applicant_csv');

            if ( empty($errors) )
            {
                // File is uploaded and we're ready to start importing
                $column_headers = $this->get_csv_column_headers( $target_file );
                $column_mappings = get_option( 'propertyhive_applicant_import_csv_column_mapping', array() );
                $propertyhive_fields = $this->get_applicant_csv_fields();

                $id = 'applicant';
                $include = 'csv-mapping.php';
            }
        }

        if ( isset($_POST['applicant_csv_mapping_none']) )
        {
            if ( isset($_POST['column_mapping']) && is_array($_POST['column_mapping']) && !empty($_POST['column_mapping']) )
            {
                update_option( 'propertyhive_applicant_import_csv_column_mapping', $_POST['column_mapping'] );
            }

            if ( !wp_verify_nonce( $_POST['applicant_csv_mapping_none'], 'import-applicant-csv-mapping' ) )
            {
                $errors[] = 'Invalid nonce. Please try again';
            }
            
            if ( empty($errors) )
            {
                $applicants = $this->put_csv_data_into_array();

                if ( $applicants === false )
                {
                    $errors[] = 'Failed to read CSV file';
                }

                if ( empty($errors) )
                {
                    $ph_fields = $this->get_applicant_csv_fields();

                    $errors = $this->validate_csv($ph_fields, $applicants);
                }
            }

            if ( empty($errors) )
            {
                $log = array();

                foreach ($applicants as $i => $applicant)
                {
                    $inserted_updated = false;

                    // Check if contact exists already based on email address
                    $args = array(
                        'post_type' => 'contact',
                        'posts_per_page' => 1,
                        'fields' => 'ids',
                        'post_status' => array( 'any', 'trash', 'future', 'auto-draft' ),
                        'meta_query' => array(
                            array(
                                'key' => '_imported_ref',
                                'value' => $this->get_csv_field($applicant, 'imported_ref', $ph_fields),
                            )
                        )
                    );

                    $contact_query = new WP_Query( $args );

                    if ( $contact_query->have_posts() )
                    {
                        // Contact exists already
                        while ( $contact_query->have_posts() )
                        {
                            $contact_query->the_post();

                            $post_id = get_the_ID();

                            $my_post = array(
                                'ID'             => $post_id,
                                'post_title'     => wp_strip_all_tags( $this->get_csv_field($applicant, 'post_title', $ph_fields) ),
                                'post_excerpt'   => '',
                                'post_content'   => '',
                                'post_status'    => 'publish',
                            );

                            // Update the post into the database
                            $post_id = wp_update_post( $my_post );

                            if ( is_wp_error( $post_id ) ) 
                            {
                                $log[] = array(
                                    'message' => 'Failed to update post for applicant on row ' . ($i + 1) . '. The error was as follows: ' . $post_id->get_error_message(),
                                    'post_id' => $post_id,
                                );
                            }
                            else
                            {
                                $inserted_updated = 'updated';
                            }
                        }
                    }
                    else
                    {
                        // We've not imported this applicant before
                        $postdata = array(
                            'post_excerpt'   => '',
                            'post_content'   => '',
                            'post_title'     => wp_strip_all_tags( $this->get_csv_field($applicant, 'post_title', $ph_fields) ),
                            'post_status'    => 'publish',
                            'post_type'      => 'contact',
                            'comment_status' => 'closed',
                        );

                        $post_id = wp_insert_post( $postdata, true );

                        if ( is_wp_error( $post_id ) ) 
                        {
                            $log[] = array(
                                'message' => 'Failed to insert post for applicant on row ' . ($i + 1) . '. The error was as follows: ' . $post_id->get_error_message(),
                                'post_id' => $post_id,
                            );
                        }
                        else
                        {
                            $inserted_updated = 'inserted';
                        }
                    }
                    wp_reset_postdata();

                    if ( $inserted_updated !== FALSE )
                    {
                        update_post_meta( $post_id, '_imported_ref', $this->get_csv_field($applicant, 'imported_ref', $ph_fields) );

                        // Update contact type
                        $existing_contact_types = get_post_meta( $post_id, '_contact_types', TRUE );
                        if ( $existing_contact_types == '' || !is_array($existing_contact_types) )
                        {
                            $existing_contact_types = array();
                        }
                        if ( !in_array( 'applicant', $existing_contact_types ) )
                        {
                            $existing_contact_types[] = 'applicant';
                            update_post_meta( $post_id, '_contact_types', $existing_contact_types );
                        }

                        // Update contact details
                        update_post_meta( $post_id, '_address_name_number', $this->get_csv_field($applicant, 'address_name_number', $ph_fields) );
                        update_post_meta( $post_id, '_address_street', $this->get_csv_field($applicant, 'address_street', $ph_fields) );
                        update_post_meta( $post_id, '_address_two', $this->get_csv_field($applicant, 'address_2', $ph_fields) );
                        update_post_meta( $post_id, '_address_three', $this->get_csv_field($applicant, 'address_3', $ph_fields) );
                        update_post_meta( $post_id, '_address_four', $this->get_csv_field($applicant, 'address_4', $ph_fields) );
                        update_post_meta( $post_id, '_address_postcode', $this->get_csv_field($applicant, 'address_postcode', $ph_fields) );

                        $country = $this->get_csv_field($applicant, 'address_country', $ph_fields);
                        if ($country == '')
                        {
                            $country = get_option( 'propertyhive_default_country', 'GB' );
                        }
                        update_post_meta( $post_id, '_address_country', $country );

                        update_post_meta( $post_id, '_telephone_number', $this->get_csv_field($applicant, 'telephone_number', $ph_fields) );
                        update_post_meta( $post_id, '_email_address', $this->get_csv_field($applicant, 'email_address', $ph_fields) );
                        update_post_meta( $post_id, '_contact_notes', $this->get_csv_field($applicant, 'contact_notes', $ph_fields) );

                        if (class_exists('PH_Template_Assistant'))
                        {
                            $current_settings = get_option( 'propertyhive_template_assistant', array() );

                            $custom_fields = ( ( isset($current_settings['custom_fields']) ) ? $current_settings['custom_fields'] : array() );

                            if ( !empty($custom_fields) )
                            {
                                $contact_custom_fields_exist = false;
                                foreach ( $custom_fields as $custom_field )
                                {
                                    if ( substr($custom_field['meta_box'], 0, 7) == 'contact' )
                                    {
                                        $contact_custom_fields_exist = true;
                                    }
                                }

                                if ( $contact_custom_fields_exist )
                                {
                                    $fields['section_start_custom_fields'] = array(
                                        'label' => 'Custom Fields',
                                        'value_type' => 'section_start',
                                    );

                                    foreach ( $custom_fields as $custom_field )
                                    {
                                        if ( substr($custom_field['meta_box'], 0, 7) != 'contact' )
                                        {
                                            continue;
                                        }

                                        update_post_meta( $post_id, $custom_field['field_name'], $this->get_csv_field($applicant, $custom_field['field_name'], $ph_fields) );
                                    }

                                    $fields['section_end_custom_fields'] = array(
                                        'value_type' => 'section_end',
                                    );
                                }
                            }
                        }

                        // Check if applicant profile already imported
                        $num_applicant_profiles = get_post_meta( $post_id, '_applicant_profiles', TRUE );
                        if ( $num_applicant_profiles == '' )
                        {
                            $num_applicant_profiles = 0;
                        }

                        $found_previously_imported = false;
                        for ( $i = 0; $i < $num_applicant_profiles; ++$i )
                        {
                            $profile = get_post_meta( $post_id, '_applicant_profile_' . $i, TRUE );

                            if ( isset($profile['imported_ref']) && $profile['imported_ref'] == $this->get_csv_field($applicant, 'imported_ref', $ph_fields) )
                            {
                                $found_previously_imported = $i;
                            }
                        }

                        $applicant_profile = array(
                            'imported_ref' => $this->get_csv_field($applicant, 'imported_ref', $ph_fields),
                        );

                        $department = str_replace(" ", "-", strtolower($this->get_csv_field($applicant, 'department', $ph_fields)));
                        if ( $this->get_csv_field($applicant, 'department', $ph_fields) == '' )
                        {
                            $department = get_option( 'propertyhive_primary_department', '' );
                        }
                        if ( $department == 'residential-sales' )
                        {
                            $applicant_profile['department'] = 'residential-sales';

                            $price = preg_replace("/[^0-9]/", '', ph_clean($this->get_csv_field($applicant, 'maximum_price', $ph_fields)));

                            $applicant_profile['max_price'] = $price;

                            // Not used yet but could be if introducing currencies in the future.
                            $applicant_profile['max_price_actual'] = $price;

                            $match_price_lower = preg_replace("/[^0-9]/", '', ph_clean($this->get_csv_field($applicant, 'match_price_lower', $ph_fields)));
                            if ($match_price_lower != '')
                            {
                                $applicant_profile['match_price_range_lower'] = $match_price_lower;
                                $applicant_profile['match_price_range_lower_actual'] = $match_price_lower;
                            }
                            $match_price_higher = preg_replace("/[^0-9]/", '', ph_clean($this->get_csv_field($applicant, 'match_price_higher', $ph_fields)));
                            if ($match_price_higher != '')
                            {
                                $applicant_profile['match_price_range_higher'] = $match_price_higher;
                                $applicant_profile['match_price_range_higher_actual'] = $match_price_higher;
                            }
                        }

                        if ( $department == 'residential-lettings' )
                        {
                            $applicant_profile['department'] = 'residential-lettings';

                            $rent = preg_replace("/[^0-9.]/", '', ph_clean($this->get_csv_field($applicant, 'maximum_price', $ph_fields)));

                            $applicant_profile['max_rent'] = $rent;
                            $applicant_profile['rent_frequency'] = ph_clean($this->get_csv_field($applicant, 'rent_frequency', $ph_fields));

                            $price_actual = $rent; // Used for ordering properties. Stored in pcm
                            switch ($this->get_csv_field($applicant, 'rent_frequency', $ph_fields))
                            {
                                case "pw": { $price_actual = ($rent * 52) / 12; break; }
                                case "pcm": { $price_actual = $rent; break; }
                                case "pq": { $price_actual = ($rent * 4) / 52; break; }
                                case "pa": { $price_actual = ($rent / 52); break; }
                            }
                            $applicant_profile['max_price_actual'] = $price_actual;
                        }

                        if ( $department == 'residential-sales' || $department == 'residential-lettings' )
                        {
                            $beds = preg_replace("/[^0-9]/", '', ph_clean($this->get_csv_field($applicant, 'minimum_bedrooms', $ph_fields)));
                            $applicant_profile['min_beds'] = $beds;

                            if ( $this->get_csv_field($applicant, 'property_type', $ph_fields) != '' )
                            {   
                                $explode_values = explode(",", $this->get_csv_field($applicant, 'property_type', $ph_fields));
                                $term_ids = array();
                                foreach ( $explode_values as $value )
                                {
                                    $value = preg_replace('/^[\s\x00]+|[\s\x00]+$/u', '', $value);

                                    $term_id = array_search($value, $ph_fields['property_type']['possible_values']);

                                    if ( $term_id !== FALSE )
                                    {
                                        $term_ids[] = $term_id;
                                    }
                                }

                                $applicant_profile['property_types'] = $term_ids;
                            }
                        }

                        if ( $department == 'commercial' )
                        {
                            $applicant_profile['department'] = 'commercial';

                            $applicant_profile['available_as'] = array();

                            if ( $this->get_csv_field($applicant, 'for_sale', $ph_fields) == 'yes' )
                            {
                                $applicant_profile['available_as'][] = 'sale';
                            }
                            if ( $this->get_csv_field($applicant, 'to_rent', $ph_fields) == 'yes' )
                            {
                                $applicant_profile['available_as'][] = 'rent';
                            }

                            if ( $this->get_csv_field($applicant, 'property_type', $ph_fields) != '' )
                            {   
                                $explode_values = explode(",", $this->get_csv_field($applicant, 'property_type', $ph_fields));
                                $term_ids = array();
                                foreach ( $explode_values as $value )
                                {
                                    $value = preg_replace('/^[\s\x00]+|[\s\x00]+$/u', '', $value);

                                    $term_id = array_search($value, $ph_fields['property_type']['possible_values']);

                                    if ( $term_id !== FALSE )
                                    {
                                        $term_ids[] = $term_id;
                                    }
                                }

                                $applicant_profile['commercial_property_types'] = $term_ids;
                            }
                        }

                        if ( $this->get_csv_field($applicant, 'location', $ph_fields) != '' )
                        {   
                            $explode_values = explode(",", $this->get_csv_field($applicant, 'location', $ph_fields));
                            $term_ids = array();
                            foreach ( $explode_values as $value )
                            {
                                $value = preg_replace('/^[\s\x00]+|[\s\x00]+$/u', '', $value);

                                $term_id = array_search($value, $ph_fields['location']['possible_values']);

                                if ( $term_id !== FALSE )
                                {
                                    $term_ids[] = $term_id;
                                }
                            }

                            $applicant_profile['locations'] = $term_ids;
                        }

                        $applicant_profile['notes'] = $this->get_csv_field($applicant, 'additional_requirements', $ph_fields);

                        $applicant_profile['send_matching_properties'] = $this->get_csv_field($applicant, 'send_matching_properties', $ph_fields);

                        if ( apply_filters( 'propertyhive_always_show_applicant_relationship_name', false ) === true )
                        {
                            $applicant_profile['relationship_name'] = $this->get_csv_field($applicant, 'relationship_name', $ph_fields);
                        }

                        if (class_exists('PH_Template_Assistant'))
                        {
                            $current_settings = get_option( 'propertyhive_template_assistant', array() );

                            $custom_fields = ( ( isset($current_settings['custom_fields']) ) ? $current_settings['custom_fields'] : array() );

                            if ( !empty($custom_fields) )
                            {
                                $contact_custom_fields_exist = false;
                                foreach ( $custom_fields as $custom_field )
                                {
                                    if ( isset($custom_field['display_on_applicant_requirements']) && $custom_field['display_on_applicant_requirements'] == '1' && substr($custom_field['meta_box'], 0, 9) == 'property_' )
                                    {
                                        $applicant_profile[$custom_field['field_name']] = $this->get_csv_field($applicant, $custom_field['field_name'], $ph_fields);
                                    }
                                }
                            }
                        }

                        if ( $found_previously_imported === FALSE )
                        {
                            update_post_meta( $post_id, '_applicant_profile_' . $num_applicant_profiles, $applicant_profile );

                            ++$num_applicant_profiles;
                            update_post_meta( $post_id, '_applicant_profiles', $num_applicant_profiles );
                        }
                        else
                        {
                            update_post_meta( $post_id, '_applicant_profile_' . $found_previously_imported, $applicant_profile );
                        }

                        $log[] = array(
                            'message' => 'Imported applicant ' . $this->get_csv_field($applicant, 'post_title', $ph_fields) . ' successfully',
                            'post_id' => $post_id,
                        );
                    }
                }

                $include = 'log.php';
            }
            else
            {
                $include = 'main.php';
            }

            @unlink($_POST['target_file']);
        }

        // VENDORS/LANDLORDS
        if ( isset($_POST['owner_csv_nonce']) )
        {
            @set_time_limit(0);

            if ( !wp_verify_nonce( $_POST['owner_csv_nonce'], 'import-owner-csv' ) )
            {
                $errors[] = 'Invalid nonce. Please try again';
            }
            
            list($errors, $target_file) = $this->handle_csv_upload('owner_csv');

            if ( empty($errors) )
            {
                // File is uploaded and we're ready to start importing
                $column_headers = $this->get_csv_column_headers( $target_file );
                $column_mappings = get_option( 'propertyhive_owner_import_csv_column_mapping', array() );
                $propertyhive_fields = $this->get_owner_csv_fields();

                $id = 'owner';
                $include = 'csv-mapping.php';
            }
        }

        if ( isset($_POST['owner_csv_mapping_none']) )
        {
            if ( isset($_POST['column_mapping']) && is_array($_POST['column_mapping']) && !empty($_POST['column_mapping']) )
            {
                update_option( 'propertyhive_owner_import_csv_column_mapping', $_POST['column_mapping'] );
            }

            if ( !wp_verify_nonce( $_POST['owner_csv_mapping_none'], 'import-owner-csv-mapping' ) )
            {
                $errors[] = 'Invalid nonce. Please try again';
            }
            
            if ( empty($errors) )
            {
                $owners = $this->put_csv_data_into_array();

                if ( $owners === false )
                {
                    $errors[] = 'Failed to read CSV file';
                }

                if ( empty($errors) )
                {
                    $ph_fields = $this->get_owner_csv_fields();

                    $errors = $this->validate_csv($ph_fields, $owners);

                    if ( empty($errors) )
                    {
                        // Make sure a property can be found that relates to this owner
                        foreach ($owners as $i => $owner)
                        {
                            if ( $this->get_csv_field($owner, 'property_post_id', $ph_fields) == '' && $this->get_csv_field($owner, 'property_imported_ref', $ph_fields) == '' )
                            {
                                $errors[] = 'Either the Property Post ID or Property Imported ID field is required on row ' . ($i + 1);
                            }
                            else
                            {
                                if ( $this->get_csv_field($owner, 'property_post_id', $ph_fields) != '' )
                                {
                                    $args = array(
                                        'post_type' => 'property',
                                        'posts_per_page' => 1,
                                        'fields' => 'ids',
                                        'post_status' => array( 'any', 'trash', 'future', 'auto-draft' ),
                                        'p' => $this->get_csv_field($owner, 'property_post_id', $ph_fields)
                                    );

                                    $property_query = new WP_Query( $args );

                                    if ( $property_query->have_posts() )
                                    {

                                    }
                                    else
                                    {
                                        $errors[] = 'The Property Post ID (' . $this->get_csv_field($owner, 'property_post_id', $ph_fields) . ') on row ' . ($i + 1) . ' doesn\'t exist';
                                    }
                                    wp_reset_postdata();
                                }
                                elseif ( $this->get_csv_field($owner, 'property_imported_ref', $ph_fields) != '' )
                                {
                                    $found_property_with_ref = false;
                                    $args = array(
                                        'post_type' => 'property',
                                        'fields' => 'ids',
                                        'post_status' => array( 'any', 'trash', 'future', 'auto-draft' ),
                                        'nopaging' => true,
                                    );

                                    $property_query = new WP_Query( $args );

                                    if ( $property_query->have_posts() )
                                    {
                                        while ( $property_query->have_posts() )
                                        {
                                            $property_query->the_post();

                                            $post_meta = get_post_meta(get_the_ID());

                                            foreach ($post_meta as $key => $val )
                                            {
                                                if ( strpos($key, '_imported_ref') !== FALSE )
                                                {
                                                    if ( $val[0] == $this->get_csv_field($owner, 'property_imported_ref', $ph_fields) )
                                                    {
                                                        $found_property_with_ref = true;
                                                        $owners[$i]['property_post_id'] = get_the_ID();
                                                    }
                                                }
                                            }
                                        }

                                        if ( !$found_property_with_ref )
                                        {
                                            $errors[] = 'The Property Imported Ref ID (' . $this->get_csv_field($owner, 'property_imported_ref', $ph_fields) . ') on row ' . ($i + 1) . ' doesn\'t exist';
                                        }
                                    }
                                    else
                                    {
                                        $errors[] = 'The Property Imported Ref ID (' . $this->get_csv_field($owner, 'property_imported_ref', $ph_fields) . ') on row ' . ($i + 1) . ' doesn\'t exist';
                                    }
                                    wp_reset_postdata();
                                }
                            }
                        }
                    }
                }
            }

            if ( empty($errors) )
            {
                $log = array();

                foreach ($owners as $i => $owner)
                {
                    $inserted_updated = false;

                    // Check if contact exists already
                    $args = array(
                        'post_type' => 'contact',
                        'posts_per_page' => 1,
                        'fields' => 'ids',
                        'post_status' => array( 'any', 'trash', 'future', 'auto-draft' ),
                        'meta_query' => array(
                            array(
                                'key' => '_imported_ref',
                                'value' => $this->get_csv_field($owner, 'imported_ref', $ph_fields),
                            )
                        )
                    );

                    $contact_query = new WP_Query( $args );

                    if ( $contact_query->have_posts() )
                    {
                        // Contact exists already
                        while ( $contact_query->have_posts() )
                        {
                            $contact_query->the_post();

                            $post_id = get_the_ID();

                            $my_post = array(
                                'ID'             => $post_id,
                                'post_title'     => wp_strip_all_tags( $this->get_csv_field($owner, 'post_title', $ph_fields) ),
                                'post_excerpt'   => '',
                                'post_content'   => '',
                                'post_status'    => 'publish',
                            );

                            // Update the post into the database
                            $post_id = wp_update_post( $my_post );

                            if ( is_wp_error( $post_id ) ) 
                            {
                                $log[] = array(
                                    'message' => 'Failed to update post for owner on row ' . ($i + 1) . '. The error was as follows: ' . $post_id->get_error_message(),
                                    'post_id' => $post_id,
                                );
                            }
                            else
                            {
                                $inserted_updated = 'updated';
                            }
                        }
                    }
                    else
                    {
                        // We've not imported this contact before
                        $postdata = array(
                            'post_excerpt'   => '',
                            'post_content'   => '',
                            'post_title'     => wp_strip_all_tags( $this->get_csv_field($owner, 'post_title', $ph_fields) ),
                            'post_status'    => 'publish',
                            'post_type'      => 'contact',
                            'comment_status' => 'closed',
                        );

                        $post_id = wp_insert_post( $postdata, true );

                        if ( is_wp_error( $post_id ) ) 
                        {
                            $log[] = array(
                                'message' => 'Failed to insert post for owner on row ' . ($i + 1) . '. The error was as follows: ' . $post_id->get_error_message(),
                                'post_id' => $post_id,
                            );
                        }
                        else
                        {
                            $inserted_updated = 'inserted';
                        }
                    }
                    wp_reset_postdata();

                    if ( $inserted_updated !== FALSE )
                    {
                        update_post_meta( $post_id, '_imported_ref', $this->get_csv_field($owner, 'imported_ref', $ph_fields) );

                        // Update contact type
                        $existing_contact_types = get_post_meta( $post_id, '_contact_types', TRUE );
                        if ( $existing_contact_types == '' || !is_array($existing_contact_types) )
                        {
                            $existing_contact_types = array();
                        }
                        if ( !in_array( 'owner', $existing_contact_types ) )
                        {
                            $existing_contact_types[] = 'owner';
                            update_post_meta( $post_id, '_contact_types', $existing_contact_types );
                        }

                        // Update contact details
                        update_post_meta( $post_id, '_address_name_number', $this->get_csv_field($owner, 'address_name_number', $ph_fields) );
                        update_post_meta( $post_id, '_address_street', $this->get_csv_field($owner, 'address_street', $ph_fields) );
                        update_post_meta( $post_id, '_address_two', $this->get_csv_field($owner, 'address_2', $ph_fields) );
                        update_post_meta( $post_id, '_address_three', $this->get_csv_field($owner, 'address_3', $ph_fields) );
                        update_post_meta( $post_id, '_address_four', $this->get_csv_field($owner, 'address_4', $ph_fields) );
                        update_post_meta( $post_id, '_address_postcode', $this->get_csv_field($owner, 'address_postcode', $ph_fields) );

                        $country = $this->get_csv_field($owner, 'address_country', $ph_fields);
                        if ($country == '')
                        {
                            $country = get_option( 'propertyhive_default_country', 'GB' );
                        }
                        update_post_meta( $post_id, '_address_country', $country );

                        update_post_meta( $post_id, '_telephone_number', $this->get_csv_field($owner, 'telephone_number', $ph_fields) );
                        update_post_meta( $post_id, '_email_address', $this->get_csv_field($owner, 'email_address', $ph_fields) );
                        update_post_meta( $post_id, '_contact_notes', $this->get_csv_field($owner, 'contact_notes', $ph_fields) );

                        // Set owner ID on property
                        update_post_meta( $this->get_csv_field($owner, 'property_post_id', $ph_fields), '_owner_contact_id', $post_id );

                        $log[] = array(
                            'message' => 'Imported owner ' . $this->get_csv_field($owner, 'post_title', $ph_fields) . ' successfully',
                            'post_id' => $post_id,
                        );
                    }
                }

                $include = 'log.php';
            }
            else
            {
                $include = 'main.php';
            }

            @unlink($_POST['target_file']);
        }

        // THIRD PARTIES 
        if ( isset($_POST['thirdparty_csv_nonce']) )
        {
            @set_time_limit(0);

            if ( !wp_verify_nonce( $_POST['thirdparty_csv_nonce'], 'import-thirdparty-csv' ) )
            {
                $errors[] = 'Invalid nonce. Please try again';
            }
            
            list($errors, $target_file) = $this->handle_csv_upload('thirdparty_csv');

            if ( empty($errors) )
            {
                // File is uploaded and we're ready to start importing
                $column_headers = $this->get_csv_column_headers( $target_file );
                $column_mappings = get_option( 'propertyhive_thirdparty_import_csv_column_mapping', array() );
                $propertyhive_fields = $this->get_thirdparty_csv_fields();

                $id = 'thirdparty';
                $include = 'csv-mapping.php';
            }
        }

        if ( isset($_POST['thirdparty_csv_mapping_none']) )
        {
            if ( isset($_POST['column_mapping']) && is_array($_POST['column_mapping']) && !empty($_POST['column_mapping']) )
            {
                update_option( 'propertyhive_thirdparty_import_csv_column_mapping', $_POST['column_mapping'] );
            }

            if ( !wp_verify_nonce( $_POST['thirdparty_csv_mapping_none'], 'import-thirdparty-csv-mapping' ) )
            {
                $errors[] = 'Invalid nonce. Please try again';
            }
            
            if ( empty($errors) )
            {
                $thirdparties = $this->put_csv_data_into_array();

                if ( $thirdparties === false )
                {
                    $errors[] = 'Failed to read CSV file';
                }

                if ( empty($errors) )
                {
                    $ph_fields = $this->get_thirdparty_csv_fields();

                    $errors = $this->validate_csv($ph_fields, $thirdparties);
                }
            }

            if ( empty($errors) )
            {
                $log = array();

                foreach ($thirdparties as $i => $thirdparty)
                {
                    $inserted_updated = false;

                    // Check if contact exists
                    $args = array(
                        'post_type' => 'contact',
                        'posts_per_page' => 1,
                        'fields' => 'ids',
                        'post_status' => array( 'any', 'trash', 'future', 'auto-draft' ),
                        'meta_query' => array(
                            array(
                                'key' => '_imported_ref',
                                'value' => $this->get_csv_field($thirdparty, 'imported_ref', $ph_fields),
                            )
                        )
                    );

                    $contact_query = new WP_Query( $args );

                    if ( $contact_query->have_posts() )
                    {
                        // Contact address exists already
                        while ( $contact_query->have_posts() )
                        {
                            $contact_query->the_post();

                            $post_id = get_the_ID();

                            $my_post = array(
                                'ID'             => $post_id,
                                'post_title'     => wp_strip_all_tags( $this->get_csv_field($thirdparty, 'post_title', $ph_fields) ),
                                'post_excerpt'   => '',
                                'post_content'   => '',
                                'post_status'    => 'publish',
                            );

                            // Update the post into the database
                            $post_id = wp_update_post( $my_post );

                            if ( is_wp_error( $post_id ) ) 
                            {
                                $log[] = array(
                                    'message' => 'Failed to update post for third party on row ' . ($i + 1) . '. The error was as follows: ' . $post_id->get_error_message(),
                                    'post_id' => $post_id,
                                );
                            }
                            else
                            {
                                $inserted_updated = 'updated';
                            }
                        }
                    }
                    else
                    {
                        // We've not imported this contact before
                        $postdata = array(
                            'post_excerpt'   => '',
                            'post_content'   => '',
                            'post_title'     => wp_strip_all_tags( $this->get_csv_field($thirdparty, 'post_title', $ph_fields) ),
                            'post_status'    => 'publish',
                            'post_type'      => 'contact',
                            'comment_status' => 'closed',
                        );

                        $post_id = wp_insert_post( $postdata, true );

                        if ( is_wp_error( $post_id ) ) 
                        {
                            $log[] = array(
                                'message' => 'Failed to insert post for third party on row ' . ($i + 1) . '. The error was as follows: ' . $post_id->get_error_message(),
                                'post_id' => $post_id,
                            );
                        }
                        else
                        {
                            $inserted_updated = 'inserted';
                        }
                    }
                    wp_reset_postdata();

                    if ( $inserted_updated !== FALSE )
                    {
                        update_post_meta( $post_id, '_imported_ref', $this->get_csv_field($thirdparty, 'imported_ref', $ph_fields) );

                        // Update contact type
                        $existing_contact_types = get_post_meta( $post_id, '_contact_types', TRUE );
                        if ( $existing_contact_types == '' || !is_array($existing_contact_types) )
                        {
                            $existing_contact_types = array();
                        }
                        if ( !in_array( 'thirdparty', $existing_contact_types ) )
                        {
                            $existing_contact_types[] = 'thirdparty';
                            update_post_meta( $post_id, '_contact_types', $existing_contact_types );
                        }

                        // Update contact details
                        update_post_meta( $post_id, '_address_name_number', $this->get_csv_field($thirdparty, 'address_name_number', $ph_fields) );
                        update_post_meta( $post_id, '_address_street', $this->get_csv_field($thirdparty, 'address_street', $ph_fields) );
                        update_post_meta( $post_id, '_address_two', $this->get_csv_field($thirdparty, 'address_2', $ph_fields) );
                        update_post_meta( $post_id, '_address_three', $this->get_csv_field($thirdparty, 'address_3', $ph_fields) );
                        update_post_meta( $post_id, '_address_four', $this->get_csv_field($thirdparty, 'address_4', $ph_fields) );
                        update_post_meta( $post_id, '_address_postcode', $this->get_csv_field($thirdparty, 'address_postcode', $ph_fields) );

                        $country = $this->get_csv_field($thirdparty, 'address_country', $ph_fields);
                        if ($country == '')
                        {
                            $country = get_option( 'propertyhive_default_country', 'GB' );
                        }
                        update_post_meta( $post_id, '_address_country', $country );

                        update_post_meta( $post_id, '_telephone_number', $this->get_csv_field($thirdparty, 'telephone_number', $ph_fields) );
                        update_post_meta( $post_id, '_email_address', $this->get_csv_field($thirdparty, 'email_address', $ph_fields) );
                        update_post_meta( $post_id, '_contact_notes', $this->get_csv_field($thirdparty, 'contact_notes', $ph_fields) );

                        $category_id = array_search($this->get_csv_field($thirdparty, 'category', $ph_fields), $ph_fields['category']['possible_values']);
                        update_post_meta( $post_id, '_third_party_categories', array($category_id) );

                        $log[] = array(
                            'message' => 'Imported third party ' . $this->get_csv_field($thirdparty, 'post_title', $ph_fields) . ' successfully',
                            'post_id' => $post_id,
                        );
                    }
                }

                $include = 'log.php';
            }
            else
            {
                $include = 'main.php';
            }

            @unlink($_POST['target_file']);
        }

        // VIEWINGS
        if ( isset($_POST['viewing_csv_nonce']) )
        {
            @set_time_limit(0);

            if ( !wp_verify_nonce( $_POST['viewing_csv_nonce'], 'import-viewing-csv' ) )
            {
                $errors[] = 'Invalid nonce. Please try again';
            }
            
            list($errors, $target_file) = $this->handle_csv_upload('viewing_csv');

            if ( empty($errors) )
            {
                // File is uploaded and we're ready to start importing
                $column_headers = $this->get_csv_column_headers( $target_file );
                $column_mappings = get_option( 'propertyhive_viewing_import_csv_column_mapping', array() );
                $propertyhive_fields = $this->get_viewing_csv_fields();

                $id = 'viewing';
                $include = 'csv-mapping.php';
            }
        }

        if ( isset($_POST['viewing_csv_mapping_none']) )
        {
            if ( isset($_POST['column_mapping']) && is_array($_POST['column_mapping']) && !empty($_POST['column_mapping']) )
            {
                update_option( 'propertyhive_viewing_import_csv_column_mapping', $_POST['column_mapping'] );
            }

            if ( !wp_verify_nonce( $_POST['viewing_csv_mapping_none'], 'import-viewing-csv-mapping' ) )
            {
                $errors[] = 'Invalid nonce. Please try again';
            }
            
            if ( empty($errors) )
            {
                $viewings = $this->put_csv_data_into_array();

                if ( $viewings === false )
                {
                    $errors[] = 'Failed to read CSV file';
                }

                if ( empty($errors) )
                {
                    $ph_fields = $this->get_viewing_csv_fields();

                    $errors = $this->validate_csv($ph_fields, $viewings);

                    if ( empty($errors) )
                    {
                        // Make sure a property can be found that
                        foreach ($viewings as $i => $viewing)
                        {
                            if ( $this->get_csv_field($viewing, 'property_post_id', $ph_fields) == '' && $this->get_csv_field($viewing, 'property_imported_ref', $ph_fields) == '' )
                            {
                                $errors[] = 'Either the Property Post ID or Property Imported ID field is required on row ' . ($i + 1);
                            }
                            else
                            {
                                if ( $this->get_csv_field($viewing, 'property_post_id', $ph_fields) != '' )
                                {
                                    $args = array(
                                        'post_type' => 'property',
                                        'posts_per_page' => 1,
                                        'fields' => 'ids',
                                        'post_status' => array( 'any', 'trash', 'future', 'auto-draft' ),
                                        'p' => $this->get_csv_field($viewing, 'property_post_id', $ph_fields)
                                    );

                                    $property_query = new WP_Query( $args );

                                    if ( $property_query->have_posts() )
                                    {

                                    }
                                    else
                                    {
                                        $errors[] = 'The Property Post ID (' . $this->get_csv_field($viewing, 'property_post_id', $ph_fields) . ') on row ' . ($i + 1) . ' doesn\'t exist';
                                    }
                                    wp_reset_postdata();
                                }
                                elseif ( $this->get_csv_field($viewing, 'property_imported_ref', $ph_fields) != '' )
                                {
                                    $found_property_with_ref = false;
                                    $args = array(
                                        'post_type' => 'property',
                                        'fields' => 'ids',
                                        'post_status' => array( 'any', 'trash', 'future', 'auto-draft' ),
                                        'nopaging' => true,
                                    );

                                    $property_query = new WP_Query( $args );

                                    if ( $property_query->have_posts() )
                                    {
                                        while ( $property_query->have_posts() )
                                        {
                                            $property_query->the_post();

                                            $post_meta = get_post_meta(get_the_ID());

                                            foreach ($post_meta as $key => $val )
                                            {
                                                if ( strpos($key, '_imported_ref') !== FALSE )
                                                {
                                                    if ( $val[0] == $this->get_csv_field($viewing, 'property_imported_ref', $ph_fields) )
                                                    {
                                                        $found_property_with_ref = true;
                                                        $viewings[$i]['property_post_id'] = get_the_ID();
                                                    }
                                                }
                                            }
                                        }

                                        if ( !$found_property_with_ref )
                                        {
                                            $errors[] = 'The Property Imported Ref ID (' . $this->get_csv_field($viewing, 'property_imported_ref', $ph_fields) . ') on row ' . ($i + 1) . ' doesn\'t exist';
                                        }
                                    }
                                    else
                                    {
                                        $errors[] = 'The Property Imported Ref ID (' . $this->get_csv_field($viewing, 'property_imported_ref', $ph_fields) . ') on row ' . ($i + 1) . ' doesn\'t exist';
                                    }
                                    wp_reset_postdata();
                                }
                            }
                        }
                    }

                    if ( empty($errors) )
                    {
                        // Make sure an applicant can be found
                        foreach ($viewings as $i => $viewing)
                        {
                            /*if ( $this->get_csv_field($viewing, 'applicant_post_id', $ph_fields) == '' && $this->get_csv_field($viewing, 'applicant_imported_ref', $ph_fields) == '' )
                            {
                                $errors[] = 'Either the Applicant Post ID or Applicant Imported ID field is required on row ' . ($i + 1);
                            }
                            else
                            {*/
                                if ( $this->get_csv_field($viewing, 'applicant_post_id', $ph_fields) != '' )
                                {
                                    $args = array(
                                        'post_type' => 'contact',
                                        'posts_per_page' => 1,
                                        'fields' => 'ids',
                                        'post_status' => array( 'any', 'trash', 'future', 'auto-draft' ),
                                        'p' => $this->get_csv_field($viewing, 'applicant_post_id', $ph_fields),
                                        'meta_query' => array(
                                            array(
                                                'key' => '_contact_types',
                                                'value' => 'applicant',
                                                'compare' => 'LIKE'
                                            ),
                                        )
                                    );

                                    $applicant_query = new WP_Query( $args );

                                    if ( $applicant_query->have_posts() )
                                    {

                                    }
                                    else
                                    {
                                        $errors[] = 'The Applicant Post ID (' . $this->get_csv_field($viewing, 'applicant_post_id', $ph_fields) . ') on row ' . ($i + 1) . ' doesn\'t exist';
                                    }
                                    wp_reset_postdata();
                                }
                                elseif ( $this->get_csv_field($viewing, 'applicant_imported_ref', $ph_fields) != '' )
                                {
                                    $found_applicant_with_ref = false;
                                    $args = array(
                                        'post_type' => 'contact',
                                        'fields' => 'ids',
                                        'post_status' => array( 'any', 'trash', 'future', 'auto-draft' ),
                                        'nopaging' => true,
                                        'meta_query' => array(
                                            array(
                                                'key' => '_contact_types',
                                                'value' => 'applicant',
                                                'compare' => 'LIKE'
                                            ),
                                        )
                                    );

                                    $applicant_query = new WP_Query( $args );

                                    if ( $applicant_query->have_posts() )
                                    {
                                        while ( $applicant_query->have_posts() )
                                        {
                                            $applicant_query->the_post();

                                            $post_meta = get_post_meta(get_the_ID());

                                            foreach ($post_meta as $key => $val )
                                            {
                                                if ( strpos($key, '_imported_ref') !== FALSE )
                                                {
                                                    if ( $val[0] == $this->get_csv_field($viewing, 'applicant_imported_ref', $ph_fields) )
                                                    {
                                                        $found_applicant_with_ref = true;
                                                        $viewings[$i]['applicant_post_id'] = get_the_ID();
                                                    }
                                                }
                                            }
                                        }

                                        if ( !$found_applicant_with_ref )
                                        {
                                            $errors[] = 'The Applicant Imported Ref ID (' . $this->get_csv_field($viewing, 'applicant_imported_ref', $ph_fields) . ') on row ' . ($i + 1) . ' doesn\'t exist';
                                        }
                                    }
                                    else
                                    {
                                        $errors[] = 'The Applicant Imported Ref ID (' . $this->get_csv_field($viewing, 'applicant_imported_ref', $ph_fields) . ') on row ' . ($i + 1) . ' doesn\'t exist';
                                    }
                                    wp_reset_postdata();
                                }
                            //}
                        }
                    }

                    if ( empty($errors) )
                    {
                        // Make sure an offer can be found
                        foreach ($viewings as $i => $viewing)
                        {
                            if ( $this->get_csv_field($viewing, 'offer_post_id', $ph_fields) != '' )
                            {
                                $args = array(
                                    'post_type' => 'offer',
                                    'posts_per_page' => 1,
                                    'fields' => 'ids',
                                    'post_status' => array( 'any', 'trash', 'future', 'auto-draft' ),
                                    'p' => $this->get_csv_field($viewing, 'offer_post_id', $ph_fields),
                                );

                                $offer_query = new WP_Query( $args );

                                if ( $offer_query->have_posts() )
                                {

                                }
                                else
                                {
                                    $errors[] = 'The Offer Post ID (' . $this->get_csv_field($viewing, 'offer_post_id', $ph_fields) . ') on row ' . ($i + 1) . ' doesn\'t exist';
                                }
                                wp_reset_postdata();
                            }
                            elseif ( $this->get_csv_field($viewing, 'offer_imported_ref', $ph_fields) != '' )
                            {
                                $found_offer_with_ref = false;
                                $args = array(
                                    'post_type' => 'offer',
                                    'fields' => 'ids',
                                    'post_status' => array( 'any', 'trash', 'future', 'auto-draft' ),
                                    'nopaging' => true
                                );

                                $offer_query = new WP_Query( $args );

                                if ( $offer_query->have_posts() )
                                {
                                    while ( $offer_query->have_posts() )
                                    {
                                        $offer_query->the_post();

                                        $post_meta = get_post_meta(get_the_ID());

                                        foreach ($post_meta as $key => $val )
                                        {
                                            if ( strpos($key, '_imported_ref') !== FALSE )
                                            {
                                                if ( $val[0] == $this->get_csv_field($viewing, 'offer_imported_ref', $ph_fields) )
                                                {
                                                    $found_offer_with_ref = true;
                                                    $viewings[$i]['offer_post_id'] = get_the_ID();
                                                }
                                            }
                                        }
                                    }

                                    if ( !$found_offer_with_ref )
                                    {
                                        $errors[] = 'The Offer Imported Ref ID (' . $this->get_csv_field($viewing, 'offer_imported_ref', $ph_fields) . ') on row ' . ($i + 1) . ' doesn\'t exist';
                                    }
                                }
                                else
                                {
                                    $errors[] = 'The Offer Imported Ref ID (' . $this->get_csv_field($viewing, 'offer_imported_ref', $ph_fields) . ') on row ' . ($i + 1) . ' doesn\'t exist';
                                }
                                wp_reset_postdata();
                            }
                        }
                    }
                }
            }

            if ( empty($errors) )
            {
                $log = array();

                foreach ($viewings as $i => $viewing)
                {
                    $inserted_updated = false;

                    // Check if viewing exists
                    $args = array(
                        'post_type' => 'viewing',
                        //'posts_per_page' => 1,
                        //'fields' => 'ids',
                        //'post_status' => array( 'any', 'trash', 'future', 'auto-draft' ),
                        'meta_query' => array(
                            array(
                                'key' => '_imported_ref',
                                'value' => $this->get_csv_field($viewing, 'imported_ref', $ph_fields),
                            )
                        )
                    );

                    $viewing_query = new WP_Query( $args );

                    if ( $viewing_query->have_posts() )
                    {
                        // Contact address exists already
                        while ( $viewing_query->have_posts() )
                        {
                            $viewing_query->the_post();

                            $post_id = get_the_ID();

                            $my_post = array(
                                'ID'             => $post_id,
                                'post_title'     => wp_strip_all_tags( $this->get_csv_field($viewing, 'post_title', $ph_fields) ),
                                'post_excerpt'   => '',
                                'post_content'   => '',
                                'post_status'    => 'publish',
                            );

                            // Update the post into the database
                            $post_id = wp_update_post( $my_post );

                            if ( is_wp_error( $post_id ) ) 
                            {
                                $log[] = array(
                                    'message' => 'Failed to update post for viewing on row ' . ($i + 1) . '. The error was as follows: ' . $post_id->get_error_message(),
                                    'post_id' => $post_id,
                                );
                            }
                            else
                            {
                                $inserted_updated = 'updated';
                            }
                        }
                    }
                    else
                    {
                        // We've not imported this contact before
                        $postdata = array(
                            'post_excerpt'   => '',
                            'post_content'   => '',
                            'post_title'     => wp_strip_all_tags( $this->get_csv_field($viewing, 'post_title', $ph_fields) ),
                            'post_status'    => 'publish',
                            'post_type'      => 'viewing',
                            'comment_status' => 'closed',
                        );

                        $post_id = wp_insert_post( $postdata, true );

                        if ( is_wp_error( $post_id ) ) 
                        {
                            $log[] = array(
                                'message' => 'Failed to insert post for viewing on row ' . ($i + 1) . '. The error was as follows: ' . $post_id->get_error_message(),
                                'post_id' => $post_id,
                            );
                        }
                        else
                        {
                            $inserted_updated = 'inserted';
                        }
                    }
                    wp_reset_postdata();

                    if ( $inserted_updated !== FALSE )
                    {
                        update_post_meta( $post_id, '_imported_ref', $this->get_csv_field($viewing, 'imported_ref', $ph_fields) );

                        update_post_meta( $post_id, '_start_date_time', $this->get_csv_field($viewing, 'start_date_time', $ph_fields) );

                        $duration = $this->get_csv_field($viewing, 'duration_end_date_time', $ph_fields);
                        if ( !is_numeric($duration) )
                        {
                            // must be a date/time stamp passed
                            $duration = strtotime($this->get_csv_field($viewing, 'duration_end_date_time', $ph_fields)) - strtotime($this->get_csv_field($viewing, 'start_date_time', $ph_fields));
                        }
                        update_post_meta( $post_id, '_duration', $duration );

                        delete_post_meta( $post_id, '_negotiator_id' );
                        if ( $this->get_csv_field($viewing, 'negotiator_id', $ph_fields, TRUE) != '' )
                        {
                            update_post_meta( $post_id, '_negotiator_id', $this->get_csv_field($viewing, 'negotiator_id', $ph_fields, TRUE) );
                        }

                        update_post_meta( $post_id, '_booking_notes', $this->get_csv_field($viewing, 'booking_notes', $ph_fields) );

                        update_post_meta( $post_id, '_property_id', $this->get_csv_field($viewing, 'property_post_id', $ph_fields) );

                        if ( $this->get_csv_field($viewing, 'applicant_post_id', $ph_fields) != '' )
                        {
                            update_post_meta( $post_id, '_applicant_contact_id', $this->get_csv_field($viewing, 'applicant_post_id', $ph_fields) );
                        }

                        update_post_meta( $post_id, '_status', $this->get_csv_field($viewing, 'status', $ph_fields, TRUE) );

                        if ( $this->get_csv_field($viewing, 'feedback_status', $ph_fields, TRUE) != '' )
                        {
                            update_post_meta( $post_id, '_feedback_status', $this->get_csv_field($viewing, 'feedback_status', $ph_fields, TRUE) );
                        }
                        else
                        {
                            if ( $this->get_csv_field($viewing, 'status', $ph_fields) == 'carried_out' || $this->get_csv_field($viewing, 'status', $ph_fields) == 'offer_made' )
                            {
                                update_post_meta( $post_id, '_feedback_status', 'not_required' );
                            }
                        }

                        update_post_meta( $post_id, '_feedback', $this->get_csv_field($viewing, 'feedback', $ph_fields) );

                        update_post_meta( $post_id, '_feedback_passed_on', $this->get_csv_field($viewing, 'feedback_passed_on', $ph_fields) );

                        if ( $this->get_csv_field($viewing, 'offer_post_id', $ph_fields) != '' )
                        {
                            update_post_meta( $post_id, '_offer_id', $this->get_csv_field($viewing, 'offer_post_id', $ph_fields) );
                        }

                        $log[] = array(
                            'message' => 'Imported viewing ' . $this->get_csv_field($viewing, 'imported_ref', $ph_fields) . ' successfully',
                            'post_id' => $post_id,
                        );
                    }
                }

                $include = 'log.php';
            }
            else
            {
                $include = 'main.php';
            }

            @unlink($_POST['target_file']);
        }

        // CUSTOM FIELD TAXOMONIES
        $taxonomy_names = get_object_taxonomies( 'property' );
        sort($taxonomy_names);
        foreach ( $taxonomy_names as $taxonomy_name )
        {
            if ( isset($_POST[$taxonomy_name . '_csv_nonce']) )
            {
                @set_time_limit(0);

                if ( !wp_verify_nonce( $_POST[$taxonomy_name . '_csv_nonce'], 'import-' . $taxonomy_name . '-csv' ) )
                {
                    $errors[] = 'Invalid nonce. Please try again';
                }
                
                list($errors, $target_file) = $this->handle_csv_upload($taxonomy_name . '_csv');

                if ( empty($errors) )
                {
                    // File is uploaded and we're ready to start importing
                    $column_headers = $this->get_csv_column_headers( $target_file );
                    $column_mappings = get_option( 'propertyhive_custom_field_' . $taxonomy_name . '_import_csv_column_mapping', array() );
                    $propertyhive_fields = $this->get_taxonomy_csv_fields( $taxonomy_name );

                    $id = $taxonomy_name;
                    $include = 'csv-mapping.php';
                }
            }

            if ( isset($_POST[$taxonomy_name . '_csv_mapping_none']) )
            {
                if ( isset($_POST['column_mapping']) && is_array($_POST['column_mapping']) && !empty($_POST['column_mapping']) )
                {
                    update_option( 'propertyhive_custom_field_' . $taxonomy_name . '_import_csv_column_mapping', $_POST['column_mapping'] );
                }

                if ( !wp_verify_nonce( $_POST[$taxonomy_name . '_csv_mapping_none'], 'import-' . $taxonomy_name . '-csv-mapping' ) )
                {
                    $errors[] = 'Invalid nonce. Please try again';
                }
                
                if ( empty($errors) )
                {
                    $custom_fields = $this->put_csv_data_into_array();

                    if ( $custom_fields === false )
                    {
                        $errors[] = 'Failed to read CSV file';
                    }

                    if ( empty($errors) )
                    {
                        $ph_fields = $this->get_taxonomy_csv_fields( $taxonomy_name );

                        $errors = $this->validate_csv($ph_fields, $custom_fields);
                    }
                }

                if ( empty($errors) )
                {
                    $log = array();

                    foreach ($custom_fields as $i => $custom_field)
                    {
                        $inserted_updated = false;
                        $parent_term_id = null;

                        // Validate parent
                        if ( $taxonomy_name == 'location' || $taxonomy_name == 'property_type' || $taxonomy_name == 'commercial_property_type' )
                        {
                            $parent = $this->get_csv_field($custom_field, 'parent', $ph_fields);
                            if ( $parent != '' )
                            {
                                $parent_term = term_exists( $this->get_csv_field($custom_field, 'parent', $ph_fields), $taxonomy_name );
                                
                                if ( $parent_term === null ) 
                                {
                                    $log[] = array(
                                        'message' => 'Skipping ' . $taxonomy_name . ' ' . $this->get_csv_field($custom_field, 'name', $ph_fields) . ' as parent ' . $this->get_csv_field($custom_field, 'parent', $ph_fields) . ' doesn\'t exist',
                                        'post_id' => '',
                                    );
                                    continue;
                                }

                                $parent_term_id = isset( $parent_term['term_id'] ) ? (int)$parent_term['term_id'] : null;
                            }
                        }

                        // Check if term with this name exists
                        $term = term_exists( $this->get_csv_field($custom_field, 'name', $ph_fields), $taxonomy_name, $parent_term_id );
                        if ( $term === null ) 
                        {
                            // Term doesn't exist. Can import
                            $args = array();

                            if ( $taxonomy_name == 'location' || $taxonomy_name == 'property_type' || $taxonomy_name == 'commercial_property_type' )
                            {
                                $parent = $this->get_csv_field($custom_field, 'parent', $ph_fields);
                                if ( $parent != '' )
                                {
                                    $parent_term = term_exists( $this->get_csv_field($custom_field, 'parent', $ph_fields), $taxonomy_name );
                                    if ( $parent_term !== null ) 
                                    {
                                        $args = array('parent' => $parent_term['term_id']);
                                    }
                                }
                            }

                            $new_term = wp_insert_term( $this->get_csv_field($custom_field, 'name', $ph_fields), $taxonomy_name, $args );
                            if ( is_wp_error($new_term) ) 
                            {
                                // oops WP_Error obj returned, so the term existed prior
                                // echo $term_id->get_error_message();
                            }
                            else
                            {
                                $log[] = array(
                                    'message' => 'Imported ' . $taxonomy_name . ' ' . $this->get_csv_field($custom_field, 'name', $ph_fields) . ' successfully',
                                    'post_id' => '',
                                );
                            }
                        }
                        else
                        {
                            $log[] = array(
                                'message' => 'Skipping ' . $taxonomy_name . ' ' . $this->get_csv_field($custom_field, 'name', $ph_fields) . ' as it already exists',
                                'post_id' => '',
                            );
                        }
                        
                    }

                    $include = 'log.php';
                }
                else
                {
                    $include = 'main.php';
                }

                @unlink($_POST['target_file']);
            }
        }

        include( dirname( __FILE__ ) . '/includes/views/' . $include );
    }

    private function put_csv_data_into_array()
    {
        $records = array();

        $row = 1;
        if ( ($handle = fopen($_POST['target_file'], "r")) !== FALSE ) 
        {
            $column_mappings = $_POST['column_mapping'];

            $reverse_column_mappings = array();
            foreach ( $column_mappings as $ph_field => $csv_i )
            {
                if ( $csv_i != '' )
                {
                    $reverse_column_mappings[$csv_i] = $ph_field;
                }
            }

            while ( ($data = fgetcsv($handle, 10000, ",")) !== FALSE ) 
            {
                if ( $row > 1 )
                {
                    $record = array();
                    $num = count($data);
                    for ( $c = 0; $c < $num; ++$c ) 
                    {
                        if ( isset($reverse_column_mappings[$c]) )
                        {
                            $record[ $reverse_column_mappings[$c] ] = $data[$c];
                        }
                    }

                    $records[] = $record;
                }

                ++$row;

            }
            fclose($handle);
        }
        else
        {
            return false;
        }

        return $records;
    }

    private function validate_csv( $ph_fields, $records )
    {
        $errors = array();

        foreach ($records as $i => $record)
        {
            foreach ( $ph_fields as $ph_field_id => $ph_field )
            {
                if ( isset($ph_field['required']) && $ph_field['required'] === true && ( !isset($record[$ph_field_id]) || ( isset($record[$ph_field_id]) && trim($record[$ph_field_id]) == '' ) ) )
                {
                    $errors[] = 'The ' . __( $ph_field['label'], 'propertyhive' ) . ' field is required on row ' . ($i + 1);
                }
                else
                {
                    if ( isset($ph_field['possible_values']) && !empty($ph_field['possible_values']) )
                    {
                        if ( isset($record[$ph_field_id]) && $record[$ph_field_id] != '' )
                        {
                            $explode_values = explode(",", $record[$ph_field_id]);
                            foreach ( $explode_values as $value )
                            {
                                $value = preg_replace('/^[\s\x00]+|[\s\x00]+$/u', '', $value);

                                if ( !in_array($value, $ph_field['possible_values']) )
                                {
                                    $errors[] = 'The ' . __( $ph_field['label'], 'propertyhive' ) . ' field needs to be one of the following: ' . implode(", ", $ph_field['possible_values']) . '. Provided value was: ' . $value . ' on row ' . ($i + 1);
                                }
                            }
                        }
                    }
                }
            }
        }

        return $errors;
    }

    public function plugin_add_settings_link( $links )
    {
        $settings_link = '<a href="' . admin_url('admin.php?page=propertyhive_import_data') . '">' . __( 'Settings' ) . '</a>';
        array_push( $links, $settings_link );
        return $links;
    }

    private function includes()
    {
        include_once( 'includes/class-ph-data-import-install.php' );
    }

    /**
     * Define PH Data Import Constants
     */
    private function define_constants() 
    {
        define( 'PH_DATA_IMPORT_PLUGIN_FILE', __FILE__ );
        define( 'PH_DATA_IMPORT_VERSION', $this->version );
    }

    /**
     * Output error message if core Property Hive plugin isn't active
     */
    public function data_import_error_notices() 
    {
        global $post;

        if (!is_plugin_active('propertyhive/propertyhive.php'))
        {
            $message = __( "The Property Hive plugin must be installed and activated before you can use the Property Hive Data Import add-on", 'propertyhive' );
            echo "<div class=\"error\"> <p>$message</p></div>";
        }
        else
        {
            $error = '';    
            $uploads_dir = wp_upload_dir();
            if( $uploads_dir['error'] === FALSE )
            {
                $uploads_dir = $uploads_dir['basedir'] . '/ph_data_import/';
                
                if ( ! @file_exists($uploads_dir) )
                {
                    if ( ! @mkdir($uploads_dir) )
                    {
                        $error = 'Unable to create \'ph_data_import\' subdirectory in uploads folder for use by Property Hive Data Import plugin. Please ensure the <a href="http://codex.wordpress.org/Changing_File_Permissions" target="_blank" title="WordPress Codex - Changing File Permissions">correct permissions</a> are set.';
                    }
                }
                else
                {
                    if ( ! @is_writeable($uploads_dir) )
                    {
                        $error = 'The \'ph_data_import\' uploads folder is not currently writeable and will need to be before properties can be imported. Please ensure the <a href="http://codex.wordpress.org/Changing_File_Permissions" target="_blank" title="WordPress Codex - Changing File Permissions">correct permissions</a> are set.';
                    }
                }
            }
            else
            {
                $error = 'An error occured whilst trying to create the \'ph_data_import\' uploads folder. Please ensure the <a href="http://codex.wordpress.org/Changing_File_Permissions" target="_blank" title="WordPress Codex - Changing File Permissions">correct permissions</a> are set. '.$uploads_dir['error'];
            }

            if( $error != '' )
            {
                echo '<div class="error"><p><strong>'.$error.'</strong></p></div>';
            }
        }
    }

    private function handle_csv_upload( $file_upload_name )
    {
        $errors = array();

        // Delete any previous imports
        $uploads_dir = wp_upload_dir();
        
        if( $uploads_dir['error'] === FALSE )
        {
            $uploads_dir = $uploads_dir['basedir'] . '/ph_data_import/';

            $handle = opendir($uploads_dir);
            if ( $handle !== FALSE )
            {
                while ( ($file = readdir($handle)) !== false ) 
                {
                    @unlink( $uploads_dir . '/' . $file );
                }
                closedir($handle);
            }
        }
        else
        {
            $errors[] = 'Error obtaining upload directory';
        }

        if ( $_FILES[$file_upload_name]['size'] == 0 )
        {
            $errors[] = 'Please select a CSV file to import';
        }
        else
        {
            try {

                // Check $_FILES[$file_upload_name]['error'] value.
                switch ($_FILES[$file_upload_name]['error']) {
                    case UPLOAD_ERR_OK:
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        throw new RuntimeException('No file sent.');
                    case UPLOAD_ERR_INI_SIZE:
                    case UPLOAD_ERR_FORM_SIZE:
                        $errors[] = __( 'File exceeded filesize limit.', 'propertyhive' );
                    default:
                        $errors[] = __( 'Unknown error when uploading file.', 'propertyhive' );
                }
            }
            catch ( RuntimeException $e ) {

                $errors[] = $e->getMessage();

            }

            $name = $_FILES[$file_upload_name]["name"];
            $name_explode = explode(".", $name);
            $ext = end($name_explode);

            if ( strtolower($ext) != 'csv' )
            {
                $errors[] = __( 'Uploaded file must be of type CSV', 'propertyhive' );
            }

            /*if ( class_exists('finfo') )
            {
                // DO NOT TRUST $_FILES['upfile']['mime'] VALUE !!
                // Check MIME Type by yourself.
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                if (false === array_search(
                    $finfo->file($_FILES[$file_upload_name]['tmp_name']),
                    array(
                        'csv' => 'text/plain',
                        'csv' => 'text/x-c',
                        'text/plain' => 'text/plain',
                    ),
                    true
                )) {
                    $errors[] = __( 'Uploaded file must be of type CSV', 'propertyhive' );
                }
            }*/
        }

        $target_file = false;
        if ( empty($errors) )
        {
            $import_file_name = sha1_file($_FILES[$file_upload_name]['tmp_name']) . '.csv';
            $target_file = sprintf(
                $uploads_dir . '%s',
                $import_file_name
            );

            // You should name it uniquely.
            // DO NOT USE $_FILES['upfile']['name'] WITHOUT ANY VALIDATION !!
            // On this example, obtain safe unique name from its binary data.
            if (!move_uploaded_file(
                $_FILES[$file_upload_name]['tmp_name'],
                $target_file
            )) {
                $errors[] = __( 'Failed to move uploaded file.', 'propertyhive' );
            }
            else
            {
                $file_contents = file_get_contents($target_file);
                $file_contents = str_replace("\r\n", "\n", $file_contents);
                $file_contents = str_replace("\r", "\n", $file_contents);
                file_put_contents($target_file, $file_contents);
            }
        }

        return array($errors, $target_file);
    }

    private function get_csv_field($data, $key, $ph_fields, $get_value = false)
    {
        if (isset($data[$key]))
        {
            if ($get_value)
            {
                $field = $ph_fields[$key];

                if (isset($field['possible_values']))
                {
                    if( ($search_key = array_search($data[$key], $field['possible_values'])) !== false ) 
                    {
                        return $search_key;
                    }
                }
            }
            return trim(preg_replace('/^[\s\x00]+|[\s\x00]+$/u', '', $data[$key]));
        }

        return '';
    }

    private function get_csv_column_headers($target_file)
    {
        $columns = array();

        $row = 1;
        if ( ($handle = fopen($target_file, "r")) !== FALSE ) 
        {
            while ( ($data = fgetcsv($handle, 10000, ",")) !== FALSE ) 
            {
                if ( $row > 1 )
                {
                    break;
                }

                $num = count($data);
                for ( $c = 0; $c < $num; ++$c )
                {
                    $columns[] = $data[$c];
                }

                ++$row;
            }
            fclose($handle);
        }

        return $columns;
    }

    private function get_general_contact_details_csv_fields()
    {
        $fields = array(
            'post_title' => array(
                'label' => 'Name',
                'value_type' => 'post',
                'field_name' => 'post_title'
            ),
            'address_name_number' => array(
                'label' => 'Building Name/Number',
                'value_type' => 'meta',
                'field_name' => '_address_name_number'
            ),
            'address_street' => array(
                'label' => 'Street',
                'value_type' => 'meta',
                'field_name' => '_address_street'
            ),
            'address_2' => array(
                'label' => 'Address Line 2',
                'value_type' => 'meta',
                'field_name' => '_address_two'
            ),
            'address_3' => array(
                'label' => 'Town / City',
                'value_type' => 'meta',
                'field_name' => '_address_three'
            ),
            'address_4' => array(
                'label' => 'County',
                'value_type' => 'meta',
                'field_name' => '_address_four'
            ),
            'address_postcode' => array(
                'label' => 'Postcode',
                'value_type' => 'meta',
                'field_name' => '_address_postcode'
            )
        );

        $countries = get_option( 'propertyhive_countries', array() );
        if ( is_array($countries) && count($countries) > 1 )
        {
            $fields['address_country'] = array(
                'label' => 'Country',
                'value_type' => 'meta',
                'field_name' => '_address_country',
                'desc' => 'If not provided we\'ll set this to ' . get_option('propertyhive_default_country', 'GB')
            );
        }

        $fields['telephone_number'] = array(
            'label' => 'Telephone Number',
            'value_type' => 'meta',
            'field_name' => '_telephone_number',
        );

        $fields['email_address'] = array(
            'label' => 'Email Address',
            'value_type' => 'meta',
            'field_name' => '_email_address',
        );

        $fields['contact_notes'] = array(
            'label' => 'Contact Notes',
            'value_type' => 'meta',
            'field_name' => '_contact_notes',
        );

        return $fields;
    }

    private function get_applicant_csv_fields()
    {
        $departments = array();
        if ( get_option('propertyhive_active_departments_sales') == 'yes' )
        {
            $departments['residential-sales'] = 'Residential Sales';
        }
        if ( get_option('propertyhive_active_departments_lettings') == 'yes' )
        {
            $departments['residential-lettings'] = 'Residential Lettings';
        }
        if ( get_option('propertyhive_active_departments_commercial') == 'yes' )
        {
            $departments['commercial'] = 'Commercial';
        }

        $fields = array(

            'section_start_general' => array(
                'label' => 'General',
                'value_type' => 'section_start',
            ),

            'imported_ref' => array(
                'label' => 'Applicant Profile ID',
                'value_type' => 'meta',
                'field_name' => '_imported_ref',
                'required' => true,
                'desc' => 'Should contain a unique ID. We\'ll use this if you need to re-run the import to prevent duplicate applicant profiles being created'
            ),

            'section_end_general' => array(
                'value_type' => 'section_end',
            ),

            'section_start_contact_details' => array(
                'label' => 'Contact Details',
                'value_type' => 'section_start',
            ),

        );

        $fields = array_merge($fields, $this->get_general_contact_details_csv_fields());

        $fields['section_end_contact_details'] = array(
            'value_type' => 'section_end',
        );

        $fields['section_start_requirements'] = array(
            'label' => 'Requirements',
            'value_type' => 'section_start',
        );

        if ( apply_filters( 'propertyhive_always_show_applicant_relationship_name', false ) === true )
        {
            $fields['relationship_name'] = array(
                'label' => 'Relationship Name',
                'value_type' => 'special',
                'field_name' => '_relationship_name',
                'desc' => '',
            );
        }

        $fields['department'] = array(
            'label' => 'Department',
            'value_type' => 'special',
            'field_name' => '_department',
            'desc' => 'If not provided we\'ll set this to the primary department: ' . ucwords(str_replace("-", " ", get_option('propertyhive_primary_department', 'residential-sales'))),
            'possible_values' => $departments
        );

        if (in_array('Residential Sales', $departments) || in_array('Residential Lettings', $departments))
        {
            $fields['maximum_price'] = array(
                'label' => 'Maximum Price',
                'value_type' => 'special',
                'field_name' => '_maximum_price',
                'desc' => '',
            );
        }

        if (in_array('Residential Sales', $departments))
        {
            $match_price_lower_setting = get_option('propertyhive_applicant_match_price_range_percentage_lower', '');
            $match_price_higher_setting = get_option('propertyhive_applicant_match_price_range_percentage_higher', '');

            if ($match_price_lower_setting != '' && $match_price_higher_setting != '')
            {
                $fields['match_price_lower'] = array(
                    'label' => 'Match Price From',
                    'value_type' => 'special',
                    'field_name' => '_match_price_lower',
                    'desc' => 'Only applicable when department set to \'Residential Sales\'. If not provided we\'ll default this to the Match Price Range % (Lower) specified in your Settings (' . $match_price_lower_setting . '% below Maximum Price)',
                );

                $fields['match_price_higher'] = array(
                    'label' => 'Match Price To',
                    'value_type' => 'special',
                    'field_name' => '_match_price_higher',
                    'desc' => 'Only applicable when department set to \'Residential Sales\'. If not provided we\'ll default this to the Match Price Range % (Higher) specified in your Settings (' . $match_price_higher_setting . '% above Maximum Price)',
                );
            }
        }

        if (in_array('Residential Lettings', $departments))
        {
            $fields['rent_frequency'] = array(
                'label' => 'Rent Frequency',
                'value_type' => 'special',
                'field_name' => '_rent_frequency',
                'possible_values' => array('pw' => 'PW', 'pcm' => 'PCM', 'pq' => 'PQ', 'pa' => 'PA'),
                'desc' => 'Only applicable when department set to \'Residential Lettings\'. If not provided we\'ll default this to PCM',
            );
        }

        if (in_array('Residential Sales', $departments) || in_array('Residential Lettings', $departments))
        {
            $fields['minimum_bedrooms'] = array(
                'label' => 'Minimum Bedrooms',
                'value_type' => 'special',
                'field_name' => '_minimum_bedrooms',
            );
        }

        if (in_array('Commercial', $departments))
        {
            $fields['for_sale'] = array(
                'label' => 'Commercial For Sale',
                'value_type' => 'special',
                'field_name' => '_for_sale',
                'possible_values' => array('' => '(empty)', 'yes' => 'yes'),
            );

            $fields['to_rent'] = array(
                'label' => 'Commercial To Rent',
                'value_type' => 'special',
                'field_name' => '_to_rent',
                'possible_values' => array('' => '(empty)', 'yes' => 'yes'),
            );
        }

        $options = array();

        if (in_array('Residential Sales', $departments) || in_array('Residential Lettings', $departments))
        {
            // Residential Property Type
            $args = array(
                'hide_empty' => false,
                'parent' => 0
            );
            $terms = get_terms( 'property_type', $args );
            
            if ( !empty( $terms ) && !is_wp_error( $terms ) )
            {
                foreach ($terms as $term)
                {
                    $options[$term->term_id] = $term->name;

                    $args = array(
                        'hide_empty' => false,
                        'parent' => $term->term_id
                    );
                    $subterms = get_terms( 'property_type', $args );
                    
                    if ( !empty( $subterms ) && !is_wp_error( $subterms ) )
                    {
                        foreach ($subterms as $term)
                        {
                            $options[$term->term_id] = $term->name;
                        }
                    }
                }
            }
        }

        if (in_array('Commercial', $departments))
        {
            // Commercial Property Type
            $args = array(
                'hide_empty' => false,
                'parent' => 0
            );
            $terms = get_terms( 'commercial_property_type', $args );
            
            if ( !empty( $terms ) && !is_wp_error( $terms ) )
            {
                foreach ($terms as $term)
                {
                    $options[$term->term_id] = $term->name;

                    $args = array(
                        'hide_empty' => false,
                        'parent' => $term->term_id
                    );
                    $subterms = get_terms( 'commercial_property_type', $args );
                    
                    if ( !empty( $subterms ) && !is_wp_error( $subterms ) )
                    {
                        foreach ($subterms as $term)
                        {
                            $options[$term->term_id] = $term->name;
                        }
                    }
                }
            }
        }

        $fields['property_type'] = array(
            'label' => 'Property Type',
            'value_type' => 'special',
            'field_name' => 'property_type',
            'possible_values' => $options,
        );

        $args = array(
            'hide_empty' => false,
            'parent' => 0
        );
        $terms = get_terms( 'location', $args );
        
        $options = array();
        if ( !empty( $terms ) && !is_wp_error( $terms ) )
        {
            foreach ($terms as $term)
            {
                $options[$term->term_id] = $term->name;

                $args = array(
                    'hide_empty' => false,
                    'parent' => $term->term_id
                );
                $subterms = get_terms( 'location', $args );
                
                if ( !empty( $subterms ) && !is_wp_error( $subterms ) )
                {
                    foreach ($subterms as $term)
                    {
                        $options[$term->term_id] = $term->name;

                        $args = array(
                            'hide_empty' => false,
                            'parent' => $term->term_id
                        );
                        $subsubterms = get_terms( 'location', $args );
                        
                        if ( !empty( $subsubterms ) && !is_wp_error( $subsubterms ) )
                        {
                            foreach ($subsubterms as $term)
                            {
                                $options[$term->term_id] = $term->name;
                            }
                        }
                    }
                }
            }
        }

        $fields['location'] = array(
            'label' => 'Location',
            'value_type' => 'special',
            'field_name' => '_location',
            'possible_values' => $options
        );

        if (class_exists('PH_Template_Assistant'))
        {
            $current_settings = get_option( 'propertyhive_template_assistant', array() );

            $custom_fields = ( ( isset($current_settings['custom_fields']) ) ? $current_settings['custom_fields'] : array() );

            if ( !empty($custom_fields) )
            {
                $contact_custom_fields_exist = false;
                foreach ( $custom_fields as $custom_field )
                {
                    if ( isset($custom_field['display_on_applicant_requirements']) && $custom_field['display_on_applicant_requirements'] == '1' && substr($custom_field['meta_box'], 0, 9) == 'property_' )
                    {
                        $fields[$custom_field['field_name']] = array(
                            'label' => $custom_field['field_label'],
                            'value_type' => 'meta',
                            'field_name' => $custom_field['field_name'],
                        );

                        if ( 
                            ( $custom_field['field_type'] == 'select' || $custom_field['field_type'] == 'multiselect' ) && 
                            isset($custom_field['dropdown_options']) && 
                            is_array($custom_field['dropdown_options']) 
                        )
                        {
                            $possible_values = array('' => '(empty)');

                            foreach ( $custom_field['dropdown_options'] as $dropdown_option )
                            {
                                $possible_values[$dropdown_option] = $dropdown_option;
                            }
                            
                            $fields[$custom_field['field_name']]['possible_values'] = $possible_values;
                        }
                    }
                }
            }
        }

        $fields['additional_requirements'] = array(
            'label' => 'Additional Requirements',
            'value_type' => 'special',
            'field_name' => '_notes',
        );

        $fields['send_matching_properties'] = array(
            'label' => 'Send Matching Properties',
            'value_type' => 'special',
            'field_name' => '_send_matching_properties',
            'possible_values' => array('' => '(empty)', 'yes' => 'yes'),
        );

        $fields['section_end_requirements'] = array(
            'value_type' => 'section_end',
        );

        if (class_exists('PH_Template_Assistant'))
        {
            $current_settings = get_option( 'propertyhive_template_assistant', array() );

            $custom_fields = ( ( isset($current_settings['custom_fields']) ) ? $current_settings['custom_fields'] : array() );

            if ( !empty($custom_fields) )
            {
                $contact_custom_fields_exist = false;
                foreach ( $custom_fields as $custom_field )
                {
                    if ( substr($custom_field['meta_box'], 0, 7) == 'contact' )
                    {
                        $contact_custom_fields_exist = true;
                    }
                }

                if ( $contact_custom_fields_exist )
                {
                    $fields['section_start_custom_fields'] = array(
                        'label' => 'Custom Fields',
                        'value_type' => 'section_start',
                    );

                    foreach ( $custom_fields as $custom_field )
                    {
                        if ( substr($custom_field['meta_box'], 0, 7) != 'contact' )
                        {
                            continue;
                        }

                        $fields[$custom_field['field_name']] = array(
                            'label' => $custom_field['field_label'],
                            'value_type' => 'meta',
                            'field_name' => $custom_field['field_name'],
                        );

                        if ( 
                            ( $custom_field['field_type'] == 'select' || $custom_field['field_type'] == 'multiselect' ) && 
                            isset($custom_field['dropdown_options']) && 
                            is_array($custom_field['dropdown_options']) 
                        )
                        {
                            $possible_values = array('' => '(empty)');

                            foreach ( $custom_field['dropdown_options'] as $dropdown_option )
                            {
                                $possible_values[$dropdown_option] = $dropdown_option;
                            }
                            
                            $fields[$custom_field['field_name']]['possible_values'] = $possible_values;
                        }
                    }

                    $fields['section_end_custom_fields'] = array(
                        'value_type' => 'section_end',
                    );
                }
            }
        }

        $fields = apply_filters( 'propertyhive_data_import_applicant_csv_fields', $fields );

        return $fields;
    }

    private function get_owner_csv_fields()
    {
        $fields = array(

            'section_start_general' => array(
                'label' => 'General',
                'value_type' => 'section_start',
            ),

            'imported_ref' => array(
                'label' => 'Owner ID',
                'value_type' => 'meta',
                'field_name' => '_imported_ref',
                'required' => true,
                'desc' => 'Should contain a unique ID. We\'ll use this if you need to re-run the import to prevent duplicate contacts being created'
            ),

            'property_post_id' => array(
                'label' => 'Property Post ID',
                'value_type' => 'special',
                'field_name' => '_property_post_id',
                'desc' => 'We need some way to link this owner/landlord to an existing property in WordPress. To do this we need either the post ID of the property in WordPress (this field), or, if you\'ve imported the property via our <a href="https://wp-property-hive.com/addons/property-import/" target="_blank">Property Import add on</a>, the unique ID that was present from the third party file (the next field).',
            ),

            'property_imported_ref' => array(
                'label' => 'Property Imported ID',
                'value_type' => 'special',
                'field_name' => '_property_imported_ref',
                'desc' => '',
            ),

            'section_end_general' => array(
                'value_type' => 'section_end',
            ),

            'section_start_contact_details' => array(
                'label' => 'Contact Details',
                'value_type' => 'section_start',
            )

        );

        $fields = array_merge($fields, $this->get_general_contact_details_csv_fields());

        $fields['section_end_contact_details'] = array(
            'value_type' => 'section_end',
        );

        $fields = apply_filters( 'propertyhive_data_import_owner_csv_fields', $fields );

        return $fields;
    }

    private function get_thirdparty_csv_fields()
    {
        $fields = array(

            'section_start_general' => array(
                'label' => 'General',
                'value_type' => 'section_start',
            ),

            'imported_ref' => array(
                'label' => 'Third Party ID',
                'value_type' => 'meta',
                'field_name' => '_imported_ref',
                'required' => true,
                'desc' => 'Should contain a unique ID. We\'ll use this if you need to re-run the import to prevent duplicate third parties being created'
            ),

            'section_end_general' => array(
                'value_type' => 'section_end',
            ),

            'section_start_contact_details' => array(
                'label' => 'Contact Details',
                'value_type' => 'section_start',
            )

        );

        $fields = array_merge($fields, $this->get_general_contact_details_csv_fields());

        $fields['section_end_contact_details'] = array(
            'value_type' => 'section_end',
        );

        $fields['section_start_thirdparty_category'] = array(
            'label' => 'Third Party Category',
            'value_type' => 'section_start',
        );

        $ph_third_party_contacts = new PH_Third_Party_Contacts();
        $categories = $ph_third_party_contacts->get_categories();
        $fields['category'] = array(
            'label' => 'Category',
            'value_type' => 'special',
            'field_name' => '_third_party_categories',
            'desc' => '',
            'possible_values' => $categories,
            'required' => true,
        );

        $fields['section_end_thirdparty_category'] = array(
            'value_type' => 'section_end',
        );

        $fields = apply_filters( 'propertyhive_data_import_thirdparty_csv_fields', $fields );

        return $fields;
    }

    private function get_viewing_csv_fields()
    {
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
                $negotiators[$user->ID] = $user->display_name;
            }
        }

        $fields = array(

            'section_start_general' => array(
                'label' => 'General',
                'value_type' => 'section_start',
            ),

            'imported_ref' => array(
                'label' => 'Viewing ID',
                'value_type' => 'meta',
                'field_name' => '_imported_ref',
                'required' => true,
                'desc' => 'Should contain a unique ID. We\'ll use this if you need to re-run the import to prevent duplicate viewings being created'
            ),

            'section_end_general' => array(
                'value_type' => 'section_end',
            ),

            'section_start_details' => array(
                'label' => 'Details',
                'value_type' => 'section_start',
            ),

            'property_post_id' => array(
                'label' => 'Property Post ID',
                'value_type' => 'special',
                'field_name' => '_property_post_id'
            ),

            'property_imported_ref' => array(
                'label' => 'Property Imported ID',
                'value_type' => 'special',
                'field_name' => '_property_imported_ref'
            ),

            'applicant_post_id' => array(
                'label' => 'Applicant Post ID',
                'value_type' => 'special',
                'field_name' => '_applicant_post_id'
            ),

            'applicant_imported_ref' => array(
                'label' => 'Applicant Imported ID',
                'value_type' => 'special',
                'field_name' => '_applicant_imported_ref'
            ),

            'start_date_time' => array(
                'label' => 'Start Date Time',
                'value_type' => 'meta',
                'field_name' => '_start_date_time',
                'required' => true,
                'desc' => 'Format YYYY-MM-DD HH:ii:ss'
            ),

            'duration_end_date_time' => array(
                'label' => 'Duration (minutes) or End Date Time',
                'value_type' => 'special',
                'field_name' => '_end_date_time',
                'desc' => 'Either the duration in minutes, or the end date/time in format YYYY-MM-DD HH:ii:ss. If left blank will default to a 30 minute duration'
            ),

            'negotiator_id' => array(
                'label' => 'Attending Negotiator',
                'value_type' => 'meta',
                'field_name' => '_negotiator_id',
                'possible_values' => $negotiators,
            ),

            'booking_notes' => array(
                'label' => 'Booking Notes',
                'value_type' => 'meta',
                'field_name' => '_booking_notes',
            ),

            'status' => array(
                'label' => 'Viewing Status',
                'value_type' => 'meta',
                'field_name' => '_status',
                'required' => true,
                'possible_values' => array('pending' => 'Pending', 'carried_out' => 'Carried Out', 'offer_made' => 'Offer Made', 'cancelled' => 'Cancelled', 'no_show' => 'No Show'),
            ),

            'feedback_status' => array(
                'label' => 'Applicant Feedback Status',
                'value_type' => 'meta',
                'field_name' => '_feedback_status',
                'possible_values' => array('interested' => 'Interested', 'not_interested' => 'Not Interested', 'not_required' => 'Not Required'),
            ),

            'feedback' => array(
                'label' => 'Applicant Feedback',
                'value_type' => 'meta',
                'field_name' => '_feedback',
            ),

            'feedback_passed_on' => array(
                'label' => 'Feedback Passed On',
                'value_type' => 'meta',
                'field_name' => '_feedback_passed_on',
                'possible_values' => array('' => '(empty)', 'yes' => 'yes'),
            ),

            'offer_post_id' => array(
                'label' => 'Offer Post ID',
                'value_type' => 'special',
                'field_name' => '_offer_post_id'
            ),

            'offer_imported_ref' => array(
                'label' => 'Offer Imported ID',
                'value_type' => 'special',
                'field_name' => '_offer_imported_ref'
            ),

            'cancelled_reason' => array(
                'label' => 'Reason Cancelled',
                'value_type' => 'meta',
                'field_name' => '_cancelled_reason',
                'desc' => 'Only applicable if Viewing Status is \'Cancelled\''
            ),
        );

        $fields['section_end_details'] = array(
            'value_type' => 'section_end',
        );

        $fields = apply_filters( 'propertyhive_data_import_viewing_csv_fields', $fields );

        return $fields;
    }

    private function get_taxonomy_csv_fields( $taxonomy_name )
    {
        $fields = array(

            'section_start_general' => array(
                'label' => 'General',
                'value_type' => 'section_start',
            ),

            'name' => array(
                'label' =>  ucwords(str_replace("_", " ", $taxonomy_name)) . ' Name',
                'value_type' => 'special',
                'field_name' => '_name',
                'required' => true,
            ),
        );

        if ( $taxonomy_name == 'location' || $taxonomy_name == 'property_type' || $taxonomy_name == 'commercial_property_type' )
        {
            $fields['parent'] = array(
                'label' =>  ucwords(str_replace("_", " ", $taxonomy_name)) . ' Parent',
                'value_type' => 'special',
                'field_name' => '_parent',
            );
        }

        $fields['section_end_general'] = array(
            'value_type' => 'section_end',
        );

        $fields = apply_filters( 'propertyhive_data_import_custom_field_csv_fields', $fields );
        $fields = apply_filters( 'propertyhive_data_import_' . $taxonomy_name . '_csv_fields', $fields );

        return $fields;
    }
}

endif;

/**
 * Returns the main instance of PH_Data_Import to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return PH_Data_Import
 */
function PHDI() {
    return PH_Data_Import::instance();
}

PHDI();

if( is_admin() && file_exists(  dirname( __FILE__ ) . '/propertyhive-data-import-update.php' ) )
{
    include_once( dirname( __FILE__ ) . '/propertyhive-data-import-update.php' );
}