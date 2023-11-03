<?php
/**
 * Plugin Name: Property Hive Facebook Marketplace Property Export
 * Plugin Uri: http://wp-property-hive.com/addons/facebook-marketplace-property-export/
 * Description: Add On for Property Hive allowing feeds to be sent to Facebook Marketplace
 * Version: 1.0.7
 * Author: PropertyHive
 * Author URI: http://wp-property-hive.com
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'PH_Facebookexport' ) ) :

final class PH_Facebookexport {

    /**
     * @var string
     */
    public $version = '1.0.7';

    /**
     * @var Property Hive The single instance of the class
     */
    protected static $_instance = null;
    
    /**
     * Main Property Hive Facebook Export Instance
     *
     * Ensures only one instance of Property Hive Facebook Export is loaded or can be loaded.
     *
     * @static
     * @return Property Hive Facebook Export - Main instance
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

    	$this->id    = 'facebookexport';
        $this->label = __( 'Facebook Export', 'propertyhive' );

        // Define constants
        $this->define_constants();

        // Include required files
        $this->includes(); 

        add_action( 'admin_init', array( $this, 'run_custom_facebook_cron') );

        add_action( 'admin_notices', array( $this, 'facebookexport_error_notices') );

        add_filter( 'propertyhive_settings_tabs_array', array( $this, 'add_settings_tab' ), 19 );
        add_action( 'propertyhive_settings_' . $this->id, array( $this, 'output' ) );
        add_action( 'propertyhive_settings_save_' . $this->id, array( $this, 'save' ) );

        add_action( 'propertyhive_admin_field_facebook_portals', array( $this, 'portals_setting' ) );

        add_action( 'phfacebookexportcronhook', array( $this, 'facebook_generate_xml_feed' ) );

        add_filter( "plugin_action_links_" . plugin_basename( __FILE__ ), array( $this, 'plugin_add_settings_link' ) );
    }

    public function plugin_add_settings_link( $links )
    {
        $settings_link = '<a href="' . admin_url('admin.php?page=ph-settings&tab=facebookexport') . '">' . __( 'Settings' ) . '</a>';
        array_push( $links, $settings_link );
        return $links;
    }

    public function run_custom_facebook_cron() 
    {
        if( isset($_GET['custom_facebook_export_cron']) && $_GET['custom_facebook_export_cron'] == 'phfacebookexportcronhook' )
        {
            do_action($_GET['custom_facebook_export_cron']);
        }
    }

    private function includes()
    {
        include_once( 'includes/class-ph-facebook-export-install.php' );
    }

    /**
     * Define PH_Facebookexport Constants
     */
    private function define_constants() 
    {
        define( 'PH_FACEBOOK_EXPORT_PLUGIN_FILE', __FILE__ );
        define( 'PH_FACEBOOK_EXPORT_VERSION', $this->version );
    }

    public function facebook_generate_xml_feed() 
    {
        require( __DIR__ . '/cron.php' );
    }

    /**
     * Output error message if core Property Hive plugin isn't active
     */
    public function facebookexport_error_notices() 
    {
        if (!is_plugin_active('propertyhive/propertyhive.php'))
        {
            $message = "The Property Hive plugin must be installed and activated before you can use the Property Hive Facebook Marketplace Export add-on";
            echo"<div class=\"error\"> <p>$message</p></div>";
        }
        else
        {
            $error = '';    
            $uploads_dir = wp_upload_dir();
            if( $uploads_dir['error'] === FALSE )
            {
                $uploads_dir = $uploads_dir['basedir'] . '/ph_facebook/';
                
                if ( ! @file_exists($uploads_dir) )
                {
                    if ( ! @mkdir($uploads_dir) )
                    {
                        $error = 'Unable to create subdirectory in uploads folder for use by Property Hive Facebook Marketplace plugin. Please ensure the <a href="http://codex.wordpress.org/Changing_File_Permissions" target="_blank" title="WordPress Codex - Changing File Permissions">correct permissions</a> are set.';
                    }
                }
                else
                {
                    if ( ! @is_writeable($uploads_dir) )
                    {
                        $error = 'The uploads folder is not currently writeable and will need to be before the feed can be ran. Please ensure the <a href="http://codex.wordpress.org/Changing_File_Permissions" target="_blank" title="WordPress Codex - Changing File Permissions">correct permissions</a> are set.';
                    }
                }
            }
            else
            {
                $error = 'An error occured whilst trying to create the uploads folder. Please ensure the <a href="http://codex.wordpress.org/Changing_File_Permissions" target="_blank" title="WordPress Codex - Changing File Permissions">correct permissions</a> are set. '.$uploads_dir['error'];
            }
            
            if( $error != '' )
            {
                echo '<div class="error"><p><strong>'.$error.'</strong></p></div>';
            }
        }
    }

    /**
     * Add a new settings tab to the Property Hive settings tabs array.
     */
    public function add_settings_tab( $settings_tabs ) {
        $settings_tabs['facebookexport'] = __( 'Facebook Export', 'propertyhive' );
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

        if (strpos($current_section, 'mapping_') !== FALSE)
        {
            $custom_field = str_replace("mapping_", "", $current_section);

            $current_facebookexport_options = get_option( 'propertyhive_facebookexport' );
            $current_mappings = ( ( isset($current_facebookexport_options['mappings']) ) ? $current_facebookexport_options['mappings'] : array() );
            $current_mappings = ( ( isset($current_mappings[$custom_field]) ) ? $current_mappings[$custom_field] : array() );

            $term_ids = explode(",", $_POST[$custom_field . '_term_ids']);

            $new_mapping = array();
            foreach ($term_ids as $term_id)
            {
                if ($_POST[$custom_field . '_' . $term_id] != '')
                {
                    $new_mapping[$term_id] = $_POST[$custom_field . '_' . $term_id];
                }
            }

            if (!isset($current_facebookexport_options['mappings']))
            {
                $current_facebookexport_options['mappings'] = array();
            }
            $current_facebookexport_options['mappings'][$custom_field] = $new_mapping;

            update_option( 'propertyhive_facebookexport', $current_facebookexport_options );

            PH_Admin_Settings::add_message( __( ucwords($custom_field) . ' custom field mapping updated successfully', 'propertyhive' ) . ' ' . '<a href="' . admin_url( 'admin.php?page=ph-settings&tab=facebookexport' ) . '">' . __( 'Return to Facebook Export Options', 'propertyhive' ) . '</a>' );
        }
        else
        {
            switch ($current_section)
            {
                case 'addportal': 
                {
                    $error = '';

                    if ($error == '')
                    {                    
                        $current_facebookexport_options = get_option( 'propertyhive_facebookexport' );
                        
                        if ($current_facebookexport_options === FALSE)
                        {
                        	// This is a new option
                        	$new_facebookexport_options = array();
                        	$new_facebookexport_options['portals'] = array();
                        }
                        else
                        {
                        	$new_facebookexport_options = $current_facebookexport_options;
                        }

                        $portal = array(
                        	'department' => isset($_POST['department']) ? ph_clean($_POST['department']) : '',
                            'office_id' => isset($_POST['office_id']) ? ph_clean($_POST['office_id']) : '',
                            'availability' => isset($_POST['availability']) ? ph_clean($_POST['availability']) : '',
                        );

                        $new_facebookexport_options['portals'][] = $portal;

                        update_option( 'propertyhive_facebookexport', $new_facebookexport_options );

                        PH_Admin_Settings::add_message( __( 'Export added successfully', 'propertyhive' ) . ' ' . '<a href="' . admin_url( 'admin.php?page=ph-settings&tab=facebookexport' ) . '">' . __( 'Return to Facebook Export Options', 'propertyhive' ) . '</a>' );
                    }
                    else
                    {
                        PH_Admin_Settings::add_error( __( 'Error: ', 'propertyhive' ) . ' ' . $error . ' <a href="' . admin_url( 'admin.php?page=ph-settings&tab=facebookexport' ) . '">' . __( 'Return to Facebook Options', 'propertyhive' ) . '</a>' );
                    }

                    break;
                }
                case 'editportal': {

                    $current_id = ( !isset( $_REQUEST['id'] ) ) ? '' : sanitize_title( $_REQUEST['id'] );
                    $current_facebookexport_options = get_option( 'propertyhive_facebookexport' );

                    $error = '';
                    
                    if ($error == '')
                    {
                   		$new_facebookexport_options = $current_facebookexport_options;

                        $portal = array(
                            'department' => isset($_POST['department']) ? ph_clean($_POST['department']) : '',
                            'office_id' => isset($_POST['office_id']) ? ph_clean($_POST['office_id']) : '',
                            'availability' => isset($_POST['availability']) ? ph_clean($_POST['availability']) : '',
                        );

                        $new_facebookexport_options['portals'][$current_id] = $portal;

                   		update_option( 'propertyhive_facebookexport', $new_facebookexport_options );
                        
                        PH_Admin_Settings::add_message( __( 'Export details updated successfully', 'propertyhive' ) . ' ' . '<a href="' . admin_url( 'admin.php?page=ph-settings&tab=facebookexport' ) . '">' . __( 'Return to Facebook Export Options', 'propertyhive' ) . '</a>' );
                    }
                    else
                    {
                        PH_Admin_Settings::add_error( __( 'Error: ', 'propertyhive' ) . ' ' . $error . ' <a href="' . admin_url( 'admin.php?page=ph-settings&tab=facebookexport' ) . '">' . __( 'Return to Facebook Export Options', 'propertyhive' ) . '</a>' );
                    }

                    break;
                }
                default:
                {
                	propertyhive_update_options( self::get_portals_settings() );
                }
            }
        }

        do_action( 'phfacebookexportcronhook' );
    }

    /**
     * Uses the Property Hive admin fields API to output settings.
     *
     * @uses propertyhive_admin_fields()
     * @uses self::get_settings()
     */
    public function output() {

    	global $current_section, $hide_save_button;
        
        if (strpos($current_section, 'mapping_') !== FALSE)
        {
            // Doing custom field mapping
            propertyhive_admin_fields( self::get_customfields_settings() );
        }
        else
        {
            switch ($current_section)
            {
                case "addportal":
                {
                    propertyhive_admin_fields( self::get_portals_settings() );
                    break;
                }
                case "editportal":
                {
                    propertyhive_admin_fields( self::get_portals_settings() );
                    break;
                }
                default:
                {
                    $hide_save_button = true;
                    propertyhive_admin_fields( self::get_facebookexport_settings() );
                }
            }
        }
	}

	/**
     * Get all the main settings for this plugin
     *
     * @return array Array of settings
     */
	public function get_facebookexport_settings() {

        $html = '';
        
        $sections = $this->get_customfields_sections();

        $current_facebookexport_options = get_option( 'propertyhive_facebookexport' );
        $current_mappings = ( ( isset($current_facebookexport_options['mappings']) ) ? $current_facebookexport_options['mappings'] : array() );
        
        $i = 0;
        foreach ($sections as $key => $value)
        {
            $html .= '<p>
                <a href="' . admin_url( 'admin.php?page=ph-settings&tab=facebookexport&section=mapping_' . $key ) . '">' . $value . '</a>';

            $this_mapping = ( isset($current_mappings[$key]) ? $current_mappings[$key] : array() );

            if (empty($this_mapping))
            {
                $html .= ' - <span style="color:#900"><strong>No mappings set</strong></span>';
            }
            else
            {
                // get number of options in custom field
                $num_terms = 0;

                $args = array(
                    'hide_empty' => false,
                    'parent' => 0
                );
                $terms = get_terms( str_replace("-", "_", $key), $args );
                if ( !empty( $terms ) && !is_wp_error( $terms ) )
                {
                    $num_terms = count($terms);

                    foreach ($terms as $term)
                    {
                        $args = array(
                            'hide_empty' => false,
                            'parent' => $term->term_id
                        );
                        $subterms = get_terms( str_replace("-", "_", $key), $args );
                        
                        if ( !empty( $subterms ) && !is_wp_error( $subterms ) )
                        {
                            $num_terms = $num_terms + count($subterms);
                        }
                    }
                }
                if ($num_terms == count($this_mapping))
                {
                    $html .= ' - <span style="color:#090"><strong>All mappings set</strong></span>';
                }
                else
                {
                    $html .= ' - <span style="color:#FC0"><strong>Custom fields exist with no mapping set</strong></span>';
                }
            }

            $html .= '
            </p>';
        }

	    $settings = array(

	        array( 'title' => __( 'Exports', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'portals' ),

	        array(
                'type'      => 'facebook_portals',
            ),

	        array( 'type' => 'sectionend', 'id' => 'portals'),

            array( 'title' => __( 'Custom Field Mapping', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'custom_field_mapping' ),

            array(
                'type'      => 'html',
                'title'     => __( 'Custom Fields', 'propertyhive' ),
                'html'      => $html
            ),

            array( 'type' => 'sectionend', 'id' => 'custom_field_mapping'),
	    );
	    return apply_filters( 'ph_settings_facebookexport_settings', $settings );
	}

    public function get_facebook_export_mapping_values($custom_field) {

        if ($custom_field == 'availability')
        {
            return array(
                'for_sale' => 'for_sale',
                'for_rent' => 'for_rent',
                'sale_pending' => 'sale_pending',
                'recently_sold' => 'recently_sold',
                'off_market' => 'off_market',
                'available_soon' => 'available_soon',
            );
        }
        if ($custom_field == 'property-type')
        {
            $types = array(
                'apartment' => 'apartment',
                'builder_floor' => 'builder_floor',
                'condo' => 'condo',
                'house' => 'house',
                'house_in_condominium' => 'house_in_condominium',
                'house_in_villa' => 'house_in_villa',
                'loft' => 'loft',
                'penthouse' => 'penthouse',
                'studio' => 'studio',
                'townhouse' => 'townhouse',
                'other' => 'other',
            );
            return $types;
        }
        if ($custom_field == 'parking')
        {
            return array(
                'garage' => 'garage',
                'street' => 'street',
                'off_street' => 'off_street',
                'other' => 'other',
                'none' => 'none'
            );
        }
        if ($custom_field == 'furnished')
        {
            return array(
                'furnished' => 'furnished',
                'semi-furnished' => 'semi-furnished',
                'unfurnished' => 'unfurnished',
            );
        }

    }

    /**
     * Get custom field mapping settings
     *
     * @return array Array of settings
     */
    public function get_customfields_settings() {

        global $current_section, $post;

        $custom_field = str_replace("mapping_", "", $current_section);

        $current_facebookexport_options = get_option( 'propertyhive_facebookexport' );
        $current_mappings = ( ( isset($current_facebookexport_options['mappings']) ) ? $current_facebookexport_options['mappings'] : array() );
        $current_mappings = ( ( isset($current_mappings[$custom_field]) ) ? $current_mappings[$custom_field] : array() );

        $custom_field_options = array();
        $options = array();
        $args = array(
            'hide_empty' => false,
            'parent' => 0
        );
        $terms = get_terms( str_replace("-", "_", $custom_field), $args );

        $term_ids = array();

        $selected_value = '';
        if ( !empty( $terms ) && !is_wp_error( $terms ) )
        {
            foreach ($terms as $term)
            {
                $options[$term->term_id] = $term->name;
                $term_ids[] = $term->term_id;

                $args = array(
                    'hide_empty' => false,
                    'parent' => $term->term_id
                );
                $subterms = get_terms( str_replace("-", "_", $custom_field), $args );
                
                if ( !empty( $subterms ) && !is_wp_error( $subterms ) )
                {
                    foreach ($subterms as $term)
                    {
                        $options[$term->term_id] = ' - ' . $term->name;
                        $term_ids[] = $term->term_id;
                    }
                }
            }
        }



        $settings = array(

            array( 'title' => __( ucwords( str_replace("-", " ", $custom_field) ) . ' Mapping', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'customfield_mapping' )

        );

        $mapping_values = $this->get_facebook_export_mapping_values($custom_field);

        foreach ($options as $term_id => $term_name)
        {
            $settings[] = array(
                'title' => __( $term_name, 'propertyhive' ),
                'id'        => $custom_field . '_' . $term_id,
                'type'      => 'select',
                'options'   => array('' => '') + $mapping_values,
                'default' => ( (isset($current_mappings[$term_id])) ? $current_mappings[$term_id] : ''),
            );
        }

        $settings[] = array(
            'id'        => $custom_field . '_term_ids',
            'type'      => 'hidden',
            'default'   => implode(",", $term_ids)
        );

        $settings[] = array( 'type' => 'sectionend', 'id' => 'customfield_mapping');

        return $settings;

    }

	/**
     * Get add/edit portal settings
     *
     * @return array Array of settings
     */
	public function get_portals_settings() {

		global $current_section, $post;

		$current_id = ( !isset( $_REQUEST['id'] ) ) ? '' : sanitize_title( $_REQUEST['id'] );

		$portal_details = array();

		if ($current_id != '')
		{
			// We're editing one

			$current_facebookexport_options = get_option( 'propertyhive_facebookexport' );

			$portals = $current_facebookexport_options['portals'];

			if (isset($portals[$current_id]))
			{
				$portal_details = $portals[$current_id];
			}
		}

        $departments = ph_get_departments();
        $department_options = array( '' => 'All Departments' );
        foreach ( $departments as $key => $value )
        {
            if ( get_option( 'propertyhive_active_departments_' . str_replace("residential-", "", $key) ) == 'yes' )
            {
                $department_options[$key] = $value;
            }
        }

        $office_options = array( '' => 'All Offices' );
        $args = array(
            'post_type' => 'office',
            'nopaging' => TRUE,
            'orderby' => 'title',
            'order' => 'ASC'
        );
        $office_query = new WP_Query($args);
        
        if ($office_query->have_posts())
        {
            while ($office_query->have_posts())
            {
                $office_query->the_post();
                
                $office_options[get_the_ID()] = get_the_title();
            }
        }

        $availability_options = array();
        $args = array(
            'taxonomy' => 'availability',
            'parent' => 0,
        );
        $term_query = new WP_Term_Query( $args );
        if ( ! empty( $term_query->terms ) ) 
        {
            foreach ( $term_query->terms as $term ) 
            {
                $availability_options[$term->term_id] = $term->name;
            }
        }

		$settings = array(

	        array( 'title' => __( ( $current_section == 'addportal' ? 'Add Export' : 'Edit Export' ), 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'export_settings' ),

            array(
                'title'     => __( 'Department', 'propertyhive' ),
                'id'        => 'department',
                'type'      => 'select',
                'options' 	=> $department_options,
                'default'   => ( isset($portal_details['department']) ? $portal_details['department'] : ''),
            ),

            array(
                'title'     => __( 'Office', 'propertyhive' ),
                'id'        => 'office_id',
                'type'      => 'select',
                'options'   => $office_options,
                'default' => ( isset($portal_details['office_id']) ? $portal_details['office_id'] : ''),
            ),

            array(
                'title'     => __( 'Availabilities', 'propertyhive' ),
                'id'        => 'availability',
                'type'      => 'multiselect',
                'options'   => $availability_options,
                'default' => ( isset($portal_details['availability']) && is_array($portal_details['availability']) && !empty($portal_details['availability']) ? $portal_details['availability'] : ''),
            ),

	        array( 'type' => 'sectionend', 'id' => 'export_settings'),
	    );

	    return $settings ;
	}

	/**
     * Output list of exports
     *
     * @access public
     * @return void
     */
    public function portals_setting() {
        global $wpdb, $post;
        
        ?>
        <tr valign="top">
            <th scope="row" class="titledesc">
                &nbsp;
            </th>
            <td class="forminp forminp-button">
                <a href="<?php echo admin_url( 'admin.php?page=ph-settings&tab=facebookexport&section=addportal' ); ?>" class="button alignright"><?php echo __( 'Add New Export', 'propertyhive' ); ?></a>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" class="titledesc"><?php _e( 'Exports', 'propertyhive' ) ?></th>
            <td class="forminp">
                <table class="ph_portals widefat" cellspacing="0">
                    <thead>
                        <tr>
                        	<th class="details"><?php _e( 'Send Properties', 'propertyhive' ); ?></th>
                            <th class="url"><?php _e( 'URL To XML File', 'propertyhive' ); ?></th>
                            <th class="settings">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php

                        	$current_facebookexport_options = get_option( 'propertyhive_facebookexport' );
                        	$portals = array();
                        	if ($current_facebookexport_options !== FALSE)
                        	{
                        		if (isset($current_facebookexport_options['portals']))
                        		{
                        			$portals = $current_facebookexport_options['portals'];
                        		}
                        	}

                        	if (!empty($portals))
                        	{
                                $upload_dir = wp_upload_dir();

                        		foreach ($portals as $i => $portal)
                        		{
                                    $xml_exists = false;
                                    if ( file_exists($upload_dir['basedir'] . '/ph_facebook/' . $i . '.xml') )
                                    {
                                        $xml_exists = true;
                                    }

                                    $availabilities = array();
                                    if ( isset($portal['availability']) && is_array($portal['availability']) && !empty($portal['availability']) )
                                    {
                                        $args = array(
                                            'taxonomy' => 'availability',
                                            'parent' => 0,
                                            'include' => $portal['availability'],
                                        );
                                        $term_query = new WP_Term_Query( $args );
                                        if ( ! empty( $term_query->terms ) ) 
                                        {
                                            foreach ( $term_query->terms as $term ) 
                                            {
                                                $availabilities[] = $term->name;
                                            }
                                        }
                                    }
		                        	echo '<tr>';
		                        		echo '<td class="active">
                                            Department: ' . ( ( isset($portal['department']) && $portal['department'] != '' ) ? ucwords(str_replace("-", " ", $portal['department'])) : 'All' ) . '<br>
                                            Office: ' . ( ( isset($portal['office_id']) && $portal['office_id'] != '' ) ? get_the_title($portal['office_id']) : 'All' ) . '<br>
                                            Availability: ' . ( !empty($availabilities) ? implode(", ", $availabilities) : 'All' ) . '
                                        </td>';
		                        		echo '<td class="url">' .
                                            ( ($xml_exists) ? '<a href="' . $upload_dir['baseurl'] . '/ph_facebook/' . $i . '.xml" target="_blank">' : '' ) .
                                            $upload_dir['baseurl'] . '/ph_facebook/' . $i . '.xml' .
                                            ( ($xml_exists) ? '</a>' : '' ) .
                                            ( (!$xml_exists) ? '<br><em>File not generated yet. Please check back soon</em>' : '' )
                                            . '
                                        </td>';
		                        		echo '<td class="settings">
		                        			<a class="button" href="' . admin_url( 'admin.php?page=ph-settings&tab=facebookexport&section=editportal&id=' . $i ) . '">' . __( 'Edit', 'propertyhive' ) . '</a>
		                        		</td>';
		                        	echo '</tr>';
	                        	}
                        	}
                        	else
                        	{
                        		echo '<tr>';
	                        		echo '<td align="center" colspan="3">' . __( 'No feeds exist', 'propertyhive' ) . '</td>';
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
                <a href="<?php echo admin_url( 'admin.php?page=ph-settings&tab=facebookexport&section=addportal' ); ?>" class="button alignright"><?php echo __( 'Add New Export', 'propertyhive' ); ?></a>
            </td>
        </tr>
        <?php
    }

    /**
     * Get custom fields sections
     * Cloned from core class-ph-settings-custom-fields.php
     *
     * @return array
     */
    public function get_customfields_sections() {
        $sections = array();
        
        $sections[ 'availability' ] = __( 'Availabilities', 'propertyhive' );
        add_action( 'propertyhive_admin_field_custom_fields_availability', array( $this, 'custom_fields_availability_setting' ) );
        
        $sections[ 'property-type' ] = __( 'Property Types', 'propertyhive' );
        add_action( 'propertyhive_admin_field_custom_fields_property_type', array( $this, 'custom_fields_property_type_setting' ) );

        $sections[ 'parking' ] = __( 'Parking', 'propertyhive' );
        add_action( 'propertyhive_admin_field_custom_fields_parking', array( $this, 'custom_fields_parking_setting' ) );

        $sections[ 'furnished' ] = __( 'Furnished', 'propertyhive' );
        add_action( 'propertyhive_admin_field_custom_fields_furnished', array( $this, 'custom_fields_furnished_setting' ) );
        
        return $sections;
    }

    public function get_mapped_value($post_id, $taxonomy)
    {
        $term_list = wp_get_post_terms($post_id, str_replace("-", "_", $taxonomy), array("fields" => "ids"));

        if ( !is_wp_error($term_list) && !empty($term_list) )
        {
            $current_facebookexport_options = get_option( 'propertyhive_facebookexport' );
            $current_mappings = ( ( isset($current_facebookexport_options['mappings']) ) ? $current_facebookexport_options['mappings'] : array() );
            $current_mappings = ( ( isset($current_mappings[str_replace("_", "-", $taxonomy)]) ) ? $current_mappings[str_replace("_", "-", $taxonomy)] : array() );

            if (isset($current_mappings[$term_list[0]]))
            {
                return $current_mappings[$term_list[0]];
            }
        }

        return '';
    }
}

endif;

/**
 * Returns the main instance of PH_Facebookexport to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return PH_Facebookexport
 */
function PHFBE() {
    return PH_Facebookexport::instance();
}

PHFBE();

if( is_admin() && file_exists(  dirname( __FILE__ ) . '/propertyhive-facebook-export-update.php' ) )
{
    include_once( dirname( __FILE__ ) . '/propertyhive-facebook-export-update.php' );
}

class SimpleXMLExtendedFacebook extends SimpleXMLElement {

    public function addCData($cdata_text) {
        $node = dom_import_simplexml($this); 
        $no   = $node->ownerDocument; 
        $node->appendChild($no->createCDATASection($cdata_text)); 
    } 

}