<?php
/**
 * Plugin Name: Property Hive Documents Add On
 * Plugin Uri: http://wp-property-hive.com/addons/documents/
 * Description: Add On for Property Hive allowing the creation and management of documents such as sales packs
 * Version: 1.0.8
 * Author: PropertyHive
 * Author URI: http://wp-property-hive.com
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'PH_Documents' ) ) :

final class PH_Documents {

    /**
     * @var string
     */
    public $version = '1.0.8';

    /**
     * @var Property Hive The single instance of the class
     */
    protected static $_instance = null;
    
    /**
     * Main Property Hive Documents Instance
     *
     * Ensures only one instance of Property Hive Documents is loaded or can be loaded.
     *
     * @static
     * @return Property Hive Documents- Main instance
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

    	$this->id    = 'phdocuments';
        $this->label = __( 'Documents', 'propertyhive' );

        // Define constants
        $this->define_constants();

        // Include required files
        $this->includes(); 

        add_action( 'admin_notices', array( $this, 'documents_error_notices') );

        add_action( 'admin_init', array( $this, 'check_delete_template') );
        add_action( 'init', array( $this, 'check_for_file_download_attempt' ) );

        add_filter( 'propertyhive_settings_tabs_array', array( $this, 'add_settings_tab' ), 19 );
        add_action( 'propertyhive_settings_' . $this->id, array( $this, 'output' ) );
        add_action( 'propertyhive_settings_save_' . $this->id, array( $this, 'save' ) );

        add_action( 'propertyhive_admin_field_document_templates', array( $this, 'document_templates_settings' ) );
        add_action( 'propertyhive_admin_field_sample_templates', array( $this, 'sample_templates_settings' ) );
        add_action( 'propertyhive_admin_field_template_docx', array( $this, 'template_docx_file_upload' ) );

        add_filter( 'propertyhive_tabs', array( $this, 'ph_tabs_and_meta_boxes') );

        add_filter( 'propertyhive_my_account_pages', array( $this, 'add_documents_tab_to_account' ) );

        add_action( 'propertyhive_my_account_section_documents', array( $this, 'propertyhive_my_account_documents' ), 10 );
    }

    public function check_for_file_download_attempt() 
    {
        if ( isset($_GET['get_file']) && $_GET['get_file'] != '' ) 
        {
            // get attachment
            $upload     = wp_upload_dir();
            $file       = $_GET['get_file']; 
            $fullfile   = $upload[ 'basedir' ] . '/ph_documents/' . $file;

            if ( !is_file( $fullfile ) ) 
            {
                status_header( 404 );
                die( '404 - File not found.' );
            }
            else
            {
                $image = get_posts( array( 'post_type' => 'attachment', 'meta_query' => array( array( 'key' => '_wp_attached_file', 'value' => 'ph_documents/' . $file ) ) ) );

                if ( 0 < count( $image ) && 0 < $image[0]->post_parent ) 
                { 
                    $documents = get_post_meta( $image[0]->post_parent, '_documents', TRUE );
                    foreach ( $documents as $document )
                    {
                        if ( $document['attachment_id'] == $image[0]->ID )
                        {
                            if ( !isset($document['public']) || ( isset($document['public']) && $document['public'] === false ) )
                            {
                                if ( !current_user_can( 'edit_posts' ) )
                                {
                                    status_header( 403 );
                                    die( '403 - Access denied' );
                                }
                            }
                        }
                    }
                }
            }

            $mime = wp_check_filetype( $fullfile );

            if( false === $mime[ 'type' ] && function_exists( 'mime_content_type' ) )
                $mime[ 'type' ] = mime_content_type( $fullfile );

            if( $mime[ 'type' ] )
                $mimetype = $mime[ 'type' ];
            else
                $mimetype = 'image/' . substr( $file, strrpos( $file, '.' ) + 1 );

            header( 'Content-type: ' . $mimetype ); // always send this
            if ( false === strpos( $_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS' ) )
                header( 'Content-Length: ' . filesize( $fullfile ) );

            /*$last_modified = gmdate( 'D, d M Y H:i:s', filemtime( $file ) );
            $etag = '"' . md5( $last_modified ) . '"';
            header( "Last-Modified: $last_modified GMT" );
            header( 'ETag: ' . $etag );
            header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', time() + 100000000 ) . ' GMT' );

            // Support for Conditional GET
            $client_etag = isset( $_SERVER['HTTP_IF_NONE_MATCH'] ) ? stripslashes( $_SERVER['HTTP_IF_NONE_MATCH'] ) : false;

            if( ! isset( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) )
                $_SERVER['HTTP_IF_MODIFIED_SINCE'] = false;

            $client_last_modified = trim( $_SERVER['HTTP_IF_MODIFIED_SINCE'] );
            // If string is empty, return 0. If not, attempt to parse into a timestamp
            $client_modified_timestamp = $client_last_modified ? strtotime( $client_last_modified ) : 0;

            // Make a timestamp for our most recent modification...
            $modified_timestamp = strtotime($last_modified);

            if ( ( $client_last_modified && $client_etag )
                ? ( ( $client_modified_timestamp >= $modified_timestamp) && ( $client_etag == $etag ) )
                : ( ( $client_modified_timestamp >= $modified_timestamp) || ( $client_etag == $etag ) )
                ) {
                status_header( 304 );
                exit;
            }*/

            // If we made it this far, just serve the file
            readfile( $fullfile );
            die();
        }
    }

    public function check_delete_template()
    {
        if ( isset($_GET['action']) && $_GET['action'] == 'deletetemplate' && isset($_GET['id']) && $_GET['id'] != '' )
        {
            $existing_settings = get_option( 'propertyhive_documents', array() );

            $explode_template_ids = explode("-", $_GET['id']);

            foreach ( $explode_template_ids as $template_id )
            {
                if ( isset($existing_settings['templates'][$template_id]) )
                {
                   unset($existing_settings['templates'][$template_id]);
                   update_option( 'propertyhive_documents', $existing_settings );
                }
                else
                {
                    die('Trying to delete template that doesn\'t exist');
                }
            }
        }

        if ( isset($_GET['action']) && $_GET['action'] == 'importtemplate' && isset($_GET['id']) && $_GET['id'] != '' )
        {
            $existing_settings = get_option( 'propertyhive_documents', array() );

            $default_templates = $this->get_default_templates();

            $existing_templates = isset($existing_settings['templates']) && is_array($existing_settings['templates']) ? $existing_settings['templates'] : array();

            $template_ids_to_import = explode("-", $_GET['id']);

            foreach ( $template_ids_to_import as $template_id )
            {
                if ( isset($default_templates[$template_id]) )
                {
                    $attachment_id = $this->handle_default_template_attachment( $default_templates[$template_id]['filename'] );

                    if ( $attachment_id !== FALSE )
                    {
                        $existing_templates[] = array(
                            'name' => $default_templates[$template_id]['name'],
                            'post_types' => ( (isset($default_templates[$template_id]['post_types'])) ? $default_templates[$template_id]['post_types'] : '' ),
                            'attachment_id' => $attachment_id,
                        );
                    }
                    else
                    {
                        die('Failed to move sample template to template library');
                    }
                }
                else
                {
                    die('Trying to import sample template that doesn\'t exist');
                }
            }

            $existing_settings['templates'] = $existing_templates;

            update_option( 'propertyhive_documents', $existing_settings );
        }
    }

    private function includes()
    {
        include( dirname( __FILE__ ) . "/includes/class-ph-documents-install.php" );
        include( dirname( __FILE__ ) . "/includes/class-ph-documents-tag-manager.php" );
        include( dirname( __FILE__ ) . "/includes/class-ph-ajax.php" );
        include( dirname( __FILE__ ) . "/includes/meta-boxes/class-ph-meta-box-documents.php" );
    }

    /**
     * Define PH Documents Constants
     */
    private function define_constants() 
    {
        define( 'PH_DOCUMENTS_PLUGIN_FILE', __FILE__ );
        define( 'PH_DOCUMENTS_VERSION', $this->version );
    }

    /**
     * Output error message if core Property Hive plugin isn't active
     */
    public function documents_error_notices() 
    {
        if (!is_plugin_active('propertyhive/propertyhive.php'))
        {
            $message = "The Property Hive plugin must be installed and activated before you can use the Property Hive Documents add-on";
            echo"<div class=\"error\"> <p>$message</p></div>";
        }
        else
        {
            $error = '';    
            $uploads_dir = wp_upload_dir();
            if( $uploads_dir['error'] === FALSE )
            {
                $uploads_dir = $uploads_dir['basedir'] . '/ph_documents/';
                
                if ( ! @file_exists($uploads_dir) )
                {
                    if ( ! @mkdir($uploads_dir) )
                    {
                        $error = '<p>Unable to create \'ph_documents\' subdirectory in uploads folder for use by the Property Hive Documents Add On. Please ensure the <a href="http://codex.wordpress.org/Changing_File_Permissions" target="_blank" title="WordPress Codex - Changing File Permissions">correct permissions</a> are set.</p>';
                    }
                }
                else
                {
                    if ( ! @is_writeable($uploads_dir) )
                    {
                        $error = '<p>The uploads folder is not currently writeable and will need to be before properties can be imported. Please ensure the <a href="http://codex.wordpress.org/Changing_File_Permissions" target="_blank" title="WordPress Codex - Changing File Permissions">correct permissions</a> are set.</p>';
                    }
                }

                if ( $error == '' )
                {
                    // try and write to .htaccess
                    $htaccess_file = ABSPATH . '.htaccess';
                    $updated_htaccess = insert_with_markers($htaccess_file, 'PropertyHiveDocuments', array('RewriteRule ^wp-content/uploads/ph_documents/(.*)$ /index.php?get_file=$1'));

                    if ( !$updated_htaccess )
                    {
                        $error = '<p>Failed to write to .htaccess file (probably due to permissions). You\'ll need to add the following manually should you wish to restrict the public access to any documents generated with the Property Hive Documents Add On:</p><p><code>RewriteRule ^wp-content/uploads/ph_documents/(.*)$ /index.php?get_file=$1</code></p>';
                    }
                }
            }
            else
            {
                $error = '<p>n error occured whilst trying to create the uploads folder. Please ensure the <a href="http://codex.wordpress.org/Changing_File_Permissions" target="_blank" title="WordPress Codex - Changing File Permissions">correct permissions</a> are set. ' . $uploads_dir['error'] . '</p>';
            }

            if ( $error != '' )
            {
                echo '<div class="error">' . $error . '</div>';
            }
        }
    }

    /**
     * Add a new settings tab to the Property Hive settings tabs array.
     *
     * @param array $settings_tabs Array of Property Hive setting tabs & their labels.
     * @return array $settings_tabs Array of Property Hive setting tabs & their labels.
     */
    public function add_settings_tab( $settings_tabs ) {
        $settings_tabs['phdocuments'] = __( 'Documents', 'propertyhive' );
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

        if ( $current_section == 'addtemplate' || $current_section == 'edittemplate' )
        {
            $error = '';
            $attachment_id = '';
            if ($_FILES['template_docx']['size'] == 0)
            {
                // No file uploaded
            }
            else
            {
                // Check $_FILES['upfile']['error'] value.
                switch ($_FILES['template_docx']['error']) {
                    case UPLOAD_ERR_OK:
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        throw new RuntimeException('No file sent.');
                    case UPLOAD_ERR_INI_SIZE:
                    case UPLOAD_ERR_FORM_SIZE:
                        $error = __( 'Document template exceeded filesize limit.', 'propertyhive' );
                    default:
                        $error = __( 'Unknown error when uploading document template.', 'propertyhive' );
                }

                if ($error == '')
                {
                    $attachment_id = media_handle_upload( 'template_docx', 0 );
    
                    if ( is_wp_error( $attachment_id ) ) {
                        // There was an error uploading the image.
                        die($attachment_id->get_error_message());
                    } else {
                        // The template was uploaded successfully!

                    }
                }
                else
                {
                    die($error);
                }
            }
        }

        switch ($current_section)
        {
            case 'addtemplate': 
            {
                $existing_settings = get_option( 'propertyhive_documents', array() );

                $existing_templates = array();
                if ( isset($existing_settings['templates']) && is_array($existing_settings['templates']) )
                {
                    $existing_templates = $existing_settings['templates'];
                }

                $new_template = array(
                    'name' => ( (isset($_POST['name'])) ? $_POST['name'] : '' ),
                    'post_types' => ( (isset($_POST['post_types'])) ? $_POST['post_types'] : '' ),
                    'attachment_id' => $attachment_id,
                );

                $existing_templates[] = $new_template;

                $existing_settings['templates'] = $existing_templates;

                update_option( 'propertyhive_documents', $existing_settings );

                PH_Admin_Settings::add_message( __( 'Document template added successfully', 'propertyhive' ) . ' ' . '<a href="' . admin_url( 'admin.php?page=ph-settings&tab=phdocuments' ) . '">' . __( 'Return to Document Settings', 'propertyhive' ) . '</a>' );
                    
                break;
            }
            case 'edittemplate': 
            {
                $current_id = ( !isset( $_REQUEST['id'] ) ) ? '' : sanitize_title( $_REQUEST['id'] );

                $existing_settings = get_option( 'propertyhive_documents', array() );

                $template = array();
                if ($current_id != '')
                {
                    if ( isset($existing_settings['templates'][$current_id]) )
                    {
                       $template = $existing_settings['templates'][$current_id];
                    }
                    else
                    {
                        die('Trying to edit template that doesn\'t exist');
                    }
                }

                $new_template = array(
                    'name' => ( (isset($_POST['name'])) ? $_POST['name'] : '' ),
                    'post_types' => ( (isset($_POST['post_types'])) ? $_POST['post_types'] : '' ),
                    'attachment_id' => ( $attachment_id != '' ) ? $attachment_id : $template['attachment_id'],
                );

                $existing_settings['templates'][$current_id] = $new_template;

                update_option( 'propertyhive_documents', $existing_settings );

                PH_Admin_Settings::add_message( __( 'Document template updated successfully', 'propertyhive' ) . ' ' . '<a href="' . admin_url( 'admin.php?page=ph-settings&tab=phdocuments' ) . '">' . __( 'Return to Document Settings', 'propertyhive' ) . '</a>' );
                
                break;
            }
            default: 
            {
                propertyhive_update_options( self::get_documents_settings() );
            }
        }
    }

    /**
     * Uses the Property Hive admin fields API to output settings.
     *
     * @uses propertyhive_admin_fields()
     * @uses self::get_settings()
     */
    public function output() {
        global $current_section, $hide_save_button;

        switch ($current_section)
        {
            case "addtemplate":
            {
                propertyhive_admin_fields( self::get_document_template_settings() );
                break;
            }
            case "edittemplate":
            {
                propertyhive_admin_fields( self::get_document_template_settings() );
                break;
            }
            default:
            {
                $hide_save_button = true;
                propertyhive_admin_fields( self::get_documents_settings() );
            }
        }
	}

    private function get_template_tag_dictionary_html( $settings )
    {
        $PH_Tag_Manager = new PH_Documents_Tag_Manager();

        $settings[] = array( 'title' => __( 'Template Tag Dictionary', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'propertyhive_document_template_tag_settings' );

        list($merge_tags, $merge_values) = $PH_Tag_Manager->replace_general_tags( array(), array(), 0 );

        $settings[] = array(
            'id'        => 'document_template_general_tags',
            'title'     => 'General',
            'type'      => 'html',
            'html'      => implode(", ", $merge_tags)
        );

        list($merge_tags, $merge_values) = $PH_Tag_Manager->replace_negotiator_tags( array(), array(), get_current_user_id() );

        $settings[] = array(
            'id'        => 'document_template_negotiator_tags',
            'title'     => 'Negotiator',
            'type'      => 'html',
            'html'      => implode(", ", $merge_tags)
        );

        list($merge_tags, $merge_values) = $PH_Tag_Manager->replace_applicant_tags( array(), array(), 0 );

        $settings[] = array(
            'id'        => 'document_template_applicant_tags',
            'title'     => 'Applicant',
            'type'      => 'html',
            'html'      => implode(", ", $merge_tags)
        );

        list($merge_tags, $merge_values) = $PH_Tag_Manager->replace_applicant_solicitor_tags( array(), array(), 0 );

        $settings[] = array(
            'id'        => 'document_template_applicant_solicitor_tags',
            'title'     => 'Applicant Solicitor',
            'type'      => 'html',
            'html'      => implode(", ", $merge_tags)
        );

        list($merge_tags, $merge_values) = $PH_Tag_Manager->replace_property_tags( array(), array(), 0, false );

        $settings[] = array(
            'id'        => 'document_template_property_tags',
            'title'     => 'Property',
            'type'      => 'html',
            'html'      => implode(", ", $merge_tags)
        );

        list($merge_tags, $merge_values) = $PH_Tag_Manager->replace_owner_tags( array(), array(), 0 );

        $settings[] = array(
            'id'        => 'document_template_owner_tags',
            'title'     => 'Owner',
            'type'      => 'html',
            'html'      => implode(", ", $merge_tags)
        );

        list($merge_tags, $merge_values) = $PH_Tag_Manager->replace_owner_solicitor_tags( array(), array(), 0 );

        $settings[] = array(
            'id'        => 'document_template_owner_solicitor_tags',
            'title'     => 'Owner Solicitor',
            'type'      => 'html',
            'html'      => implode(", ", $merge_tags)
        );

        list($merge_tags, $merge_values) = $PH_Tag_Manager->replace_appraisal_tags( array(), array(), 0, false );

        $settings[] = array(
            'id'        => 'document_template_appraisal_tags',
            'title'     => 'Appraisal',
            'type'      => 'html',
            'html'      => implode(", ", $merge_tags)
        );

        list($merge_tags, $merge_values) = $PH_Tag_Manager->replace_viewing_tags( array(), array(), 0, false );

        $settings[] = array(
            'id'        => 'document_template_viewing_tags',
            'title'     => 'Viewing',
            'type'      => 'html',
            'html'      => implode(", ", $merge_tags)
        );

        list($merge_tags, $merge_values) = $PH_Tag_Manager->replace_offer_tags( array(), array(), 0, false );

        $settings[] = array(
            'id'        => 'document_template_offer_tags',
            'title'     => 'Offer',
            'type'      => 'html',
            'html'      => implode(", ", $merge_tags)
        );

        list($merge_tags, $merge_values) = $PH_Tag_Manager->replace_sale_tags( array(), array(), 0, false );

        $settings[] = array(
            'id'        => 'document_template_sale_tags',
            'title'     => 'Sale',
            'type'      => 'html',
            'html'      => implode(", ", $merge_tags)
        );

        list($merge_tags, $merge_values) = $PH_Tag_Manager->replace_tenancy_tags( array(), array(), 0, false );

        $settings[] = array(
            'id'        => 'document_template_tenancy_tags',
            'title'     => 'Tenancy',
            'type'      => 'html',
            'html'      => implode(", ", $merge_tags)
        );

        $settings[] = array( 'type' => 'sectionend', 'id' => 'propertyhive_document_template_tag_settings');

        return $settings;
    }

	/**
     * Get all the main settings for this plugin
     *
     * @return array Array of settings
     */
	public function get_documents_settings() {

	    $settings = array(

	        array( 'title' => __( 'Template Library', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'propertyhive_document_template_settings' ),

	        array(
                'type'      => 'document_templates',
            ),

            array( 'type' => 'sectionend', 'id' => 'propertyhive_document_template_settings'),

            array( 'title' => __( 'Sample Document Templates', 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'propertyhive_sample_template_settings' ),

            array(
                'type'      => 'sample_templates',
            ),

            array( 'type' => 'sectionend', 'id' => 'propertyhive_sample_template_settings'),

        );

        $settings = $this->get_template_tag_dictionary_html( $settings );

	    return apply_filters( 'ph_settings_document_template_settings', $settings );
	}

    /**
     * Output list of document templates
     *
     * @access public
     * @return void
     */
    public function document_templates_settings() {
        global $wpdb, $post;

        $current_settings = get_option('propertyhive_documents', array());

        
        ?>
        <tr valign="top">
            <th scope="row" class="titledesc">
                &nbsp;
            </th>
            <td class="forminp forminp-button">
                <a href="" class="button alignright batch-delete batch-delete-templates" disabled="" onclick="var confirmbox = confirm('Are you sure you wish to delete the selected templates? Please note that this cannot be undone.'); return confirmbox;">Delete Selected</a>
                <a href="<?php echo admin_url( 'admin.php?page=ph-settings&tab=phdocuments&section=addtemplate' ); ?>" class="button alignright"><?php echo __( 'Add New Document Template', 'propertyhive' ); ?></a>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" class="titledesc"><?php _e( 'Templates', 'propertyhive' ) ?></th>
            <td class="forminp">
                <table class="ph_document_templates ph_customfields widefat" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="cb" style="width:1px"></th>
                            <th class="name"><?php _e( 'Template Name', 'propertyhive' ); ?></th>
                            <th class="post-types"><?php _e( 'Related To', 'propertyhive' ); ?></th>
                            <th class="settings">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            if ( isset($current_settings['templates']) && is_array($current_settings['templates']) && !empty($current_settings['templates']) )
                            {
                                foreach ( $current_settings['templates'] as $i => $template )
                                {
                                    $post_types = array();
                                    if ( isset($template['post_types']) && is_array($template['post_types']) && !empty($template['post_types']) )
                                    {
                                        foreach ( $template['post_types'] as $post_type )
                                        {
                                            $post_types[] = ucfirst($post_type);
                                        }

                                        $post_types = implode(", ", $post_types);
                                    }
                                    else
                                    {
                                        $post_types = 'All';
                                    }

                                    echo '<tr>';
                                        echo '<td class="cb"><input type="checkbox" name="template_id[]" value="' . $i . '"></td>';
                                        echo '<td class="name">' . $template['name'] . '</td>';
                                        echo '<td class="post-types">' . $post_types . '</td>';
                                        echo '<td class="settings">
                                            <a class="button" href="' . admin_url( 'admin.php?page=ph-settings&tab=phdocuments&section=edittemplate&id=' . $i ) . '">' . __( 'Edit', 'propertyhive' ) . '</a> 
                                            <a class="button" href="' . admin_url( 'admin.php?page=ph-settings&tab=phdocuments&action=deletetemplate&id=' . $i ) . '" onclick="var confirmbox = confirm(\'Are you sure you wish to delete this template? Please note that this cannot be undone.\'); return confirmbox;">' . __( 'Delete', 'propertyhive' ) . '</a>
                                        </td>';
                                    echo '</tr>';
                                }
                            }
                            else
                            {
                                echo '<tr>';
                                    echo '<td align="center" colspan="4">' . __( 'No document templates exist', 'propertyhive' ) . '</td>';
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
                <a href="" class="button alignright batch-delete-templates" disabled="" onclick="var confirmbox = confirm('Are you sure you wish to delete the selected templates? Please note that this cannot be undone.'); return confirmbox;">Delete Selected</a>
                <a href="<?php echo admin_url( 'admin.php?page=ph-settings&tab=phdocuments&section=addtemplate' ); ?>" class="button alignright"><?php echo __( 'Add New Document Template', 'propertyhive' ); ?></a>
            </td>
        </tr>

        <script>

            jQuery( function($)
            {
                $('a.batch-delete-templates').click(function()
                {
                    var template_ids = new Array;

                    $('input[name=\'template_id[]\']:checked').each(function()
                    {
                        template_ids.push( $(this).val() );
                    });
                    
                    if ( template_ids.length > 0 )
                    {
                        window.location.href = propertyhive_admin_settings.admin_url + 'admin.php?page=ph-settings&tab=phdocuments&action=deletetemplate&id=' + template_ids.join("-");
                    }

                    return false;
                });

                $('input[name=\'template_id[]\']').change(function()
                {
                    if ( $('input[name=\'template_id[]\']:checked').length > 0 )
                    {
                        $('a.batch-delete-templates').attr('disabled', false);
                    }
                    else
                    {
                        $('a.batch-delete-templates').attr('disabled', 'disabled');
                    }
                });
            });

        </script>
        <?php
    }

    /**
     * Output list of sample templates
     *
     * @access public
     * @return void
     */
    public function sample_templates_settings() {
        global $wpdb, $post;

        $default_templates = $this->get_default_templates();

        ?>
        <?php /*<tr valign="top">
            <th scope="row" class="titledesc">
                &nbsp;
            </th>
            <td class="forminp forminp-button">
                <a href="<?php echo admin_url( 'admin.php?page=ph-settings&tab=phdocuments&section=addtemplate' ); ?>" class="button alignright"><?php echo __( 'Add New Document Template', 'propertyhive' ); ?></a>
            </td>
        </tr>*/ ?>
        <tr valign="top">
            <th scope="row" class="titledesc"><?php _e( 'Sample Templates', 'propertyhive' ) ?></th>
            <td class="forminp">
                <table class="ph_document_templates ph_customfields widefat" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="name"><?php _e( 'Template Name', 'propertyhive' ); ?></th>
                            <th class="settings">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            if ( is_array($default_templates) && !empty($default_templates) )
                            {
                                foreach ( $default_templates as $i => $template )
                                {
                                    echo '<tr>';
                                        echo '<td class="name">' . $template['name'] . '</td>';
                                        echo '<td class="settings">
                                            <a class="button" href="' . str_replace( array( 'http:', 'https:' ), '', untrailingslashit( plugins_url( '/', __FILE__ ) ) )  . '/default-document-templates/' . $template['filename'] . '">' . __( 'Preview', 'propertyhive' ) . '</a>
                                            <a class="button" href="' . admin_url( 'admin.php?page=ph-settings&tab=phdocuments&action=importtemplate&id=' . $i ) . '">' . __( 'Add To Template Library', 'propertyhive' ) . '</a>
                                        </td>';
                                    echo '</tr>';
                                }
                            }
                            else
                            {
                                echo '<tr>';
                                    echo '<td align="center" colspan="2">' . __( 'No sample templates exist', 'propertyhive' ) . '</td>';
                                echo '</tr>';
                            }
                        ?>
                    </tbody>
                </table>
            </td>
        </tr>
        <?php /*<tr valign="top">
            <th scope="row" class="titledesc">
                &nbsp;
            </th>
            <td class="forminp forminp-button">
                <a href="" class="button alignright batch-delete-templates" disabled="" onclick="var confirmbox = confirm('Are you sure you wish to delete the selected templates? Please note that this cannot be undone.'); return confirmbox;">Delete Selected</a>
                <a href="<?php echo admin_url( 'admin.php?page=ph-settings&tab=phdocuments&section=addtemplate' ); ?>" class="button alignright"><?php echo __( 'Add New Document Template', 'propertyhive' ); ?></a>
            </td>
        </tr>*/ ?>
        <?php
    }

    /**
     * Get add/edit template settings
     *
     * @return array Array of settings
     */
    public function get_document_template_settings() {

        global $current_section, $post;

        $current_id = ( !isset( $_REQUEST['id'] ) ) ? '' : sanitize_title( $_REQUEST['id'] );

        $template = array();
        if ($current_id != '')
        {
            // We're editing one
            $current_settings = get_option('propertyhive_documents', array());

            if ( isset($current_settings['templates'][$current_id]) )
            {
               $template = $current_settings['templates'][$current_id];
            }
            else
            {
                die('Trying to edit template that doesn\'t exist');
            }
        }

        $post_types_with_documents_tab = array(
            'property',
            'appraisal',
            'viewing',
            'offer',
            'sale',
            'contact',
            'tenancy',
        );
        $post_types_with_documents_tab = apply_filters( 'propertyhive_post_types_with_documents_tab', $post_types_with_documents_tab );

        $options = array();

        foreach ( $post_types_with_documents_tab as $post_type )
        {
            $options[$post_type] = ucfirst($post_type);
        }

        $settings = array(

            array( 'title' => __( ( $current_section == 'addtemplate' ? 'Add Document Template' : 'Edit Document Template' ), 'propertyhive' ), 'type' => 'title', 'desc' => '', 'id' => 'template_details' ),

            array(
                'title' => __( 'Template Name', 'propertyhive' ),
                'id'        => 'name',
                'default'   => ( isset($template['name']) ? $template['name'] : '' ),
                'type'      => 'text',
                'desc_tip'  =>  false,
            ),

            array(
                'title'     => __( 'Related To', 'propertyhive' ),
                'id'        => 'post_types',
                'type'      => 'multiselect',
                'default'   => ( isset($template['post_types']) ? $template['post_types'] : array() ),
                'options'   => $options,
                'desc_tip'  =>  false,
            ),

           array(
                'title'     => __( 'Template .docx', 'propertyhive' ),
                'id'        => 'template_docx',
                'type'      => 'template_docx',
                'default'   => $current_id,
                'desc_tip'  =>  false,
            ),

            array( 'type' => 'sectionend', 'id' => 'template_details'),

        );

        $settings = $this->get_template_tag_dictionary_html( $settings );

        return $settings;
    }

    public function template_docx_file_upload( $value )
    {
        $template = array();
        if ($value['default'] != '')
        {
            // We're editing one
            $current_settings = get_option('propertyhive_documents', array());

            if ( isset($current_settings['templates'][$value['default']]) )
            {
               $template = $current_settings['templates'][$value['default']];
            }
            else
            {
                die('Trying to edit template that doesn\'t exist');
            }
        }
        ?>
            <tr valign="top" id="template_docx_row">
                <th scope="row" class="titledesc"><?php echo esc_html( $value['title'] ) ?></th>
                <td class="forminp">

                    <?php
                        if ( isset($template['attachment_id']) && $template['attachment_id'] != '' )
                        {
                            echo '<p><a href="' . wp_get_attachment_url($template['attachment_id']) . '" target="_blank">Download Uploaded Template</a></p>';
                        }
                    ?>

                    <input type="file" name="<?php echo esc_attr( $value['id'] ); ?>" id="<?php echo esc_attr( $value['id'] ); ?>" />

                </td>
            </tr>
        <?php
    }

    public function ph_tabs_and_meta_boxes($tabs)
    {
        global $post, $pagenow;

        if ( $pagenow == 'post-new.php' )
        {
            return $tabs;
        }

        $post_types_with_documents_tab = array(
            'property',
            'appraisal',
            'viewing',
            'offer',
            'sale',
            'contact',
            'tenancy',
        );
        $post_types_with_documents_tab = apply_filters( 'propertyhive_post_types_with_documents_tab', $post_types_with_documents_tab );

        foreach ( $post_types_with_documents_tab as $post_type )
        {
            $meta_boxes = array();
            $meta_boxes[5] = array(
                'id' => 'propertyhive-' . $post_type . '-documents',
                'title' => __( 'Documents', 'propertyhive' ),
                'callback' => 'PH_Meta_Box_Documents::output',
                'screen' => $post_type,
                'context' => 'normal',
                'priority' => 'high'
            );

            $meta_boxes = apply_filters( 'propertyhive_documents_meta_boxes', $meta_boxes );
            $meta_boxes = apply_filters( 'propertyhive_' . $post_type . '_documents_meta_boxes', $meta_boxes );
            ksort($meta_boxes);
            
            $ids = array();
            foreach ($meta_boxes as $meta_box)
            {
                add_meta_box( $meta_box['id'], $meta_box['title'], $meta_box['callback'], $meta_box['screen'], $meta_box['context'], $meta_box['priority'] );
                $ids[] = $meta_box['id'];
            }
            
            $tabs['tab_' . $post_type . '_documents'] = array(
                'name' => __( 'Documents', 'propertyhive' ),
                'metabox_ids' => $ids,
                'post_type' => $post_type,
                'ajax_actions' => array( 'get_documents_meta_box^' . wp_create_nonce( 'get_documents_meta_box' ) ),
            );
        }
        return $tabs;
    }

    public function add_documents_tab_to_account( $pages )
    {
        $user_id = get_current_user_id();

        $contact = new PH_Contact( '', $user_id );

        $documents = get_post_meta( $contact->id, '_documents', TRUE );
        if ( !is_array($documents) )
        {
            $documents = array();
        }

        $public_documents = array();
        foreach ( $documents as $document )
        {
            if ( $document['public'] == true )
            {
                $public_documents[] = $document;
            }
        }

        if ( !empty($public_documents) )
        {
            $pages['documents'] = array(
                'name' => __( 'Documents', 'propertyhive' ),
            );
        }

        return $pages;
    }

    public function propertyhive_my_account_documents()
    {
        global $post;

        $template = locate_template( 'propertyhive/account/documents.php' );
        if ( $template == '' )
        {
            $template = dirname( PH_DOCUMENTS_PLUGIN_FILE ) . '/templates/account/documents.php';
        }

        $user_id = get_current_user_id();

        $contact = new PH_Contact( '', $user_id );

        $documents = get_post_meta( $contact->id, '_documents', TRUE );
        if ( !is_array($documents) )
        {
            $documents = array();
        }

        $public_documents = array();
        foreach ( $documents as $document )
        {
            if ( $document['public'] == true )
            {
                $public_documents[] = $document;
            }
        }

        include($template);
    }

    /**
     * Default templates
     *
     * Sets up the default templates that come with the plugin
     *
     * @access public
     */
    private function get_default_templates() 
    {
        $default_templates = array(
            array(
                'name' => 'Thank You For Registering',
                'filename' => 'thank-you-for-registering.docx',
                'post_types' => array('contact'),
            ),
            array(
                'name' => 'Price Change Confirmation',
                'filename' => 'price-change-confirmation.docx',
                'post_types' => array('property'),
            ),
            array(
                'name' => 'Offer Received',
                'filename' => 'offer-received.docx',
                'post_types' => array('offer'),
            ),
            array(
                'name' => 'Offer Rejected',
                'filename' => 'offer-rejected.docx',
                'post_types' => array('offer'),
            ),
            array(
                'name' => 'Offer Accepted',
                'filename' => 'offer-accepted.docx',
                'post_types' => array('offer'),
            ),
            array(
                'name' => 'Offer Withdrawn',
                'filename' => 'offer-withdrawn.docx',
                'post_types' => array('offer'),
            ),
            array(
                'name' => 'Memorandum of Sale',
                'filename' => 'memorandum-of-sale.docx',
                'post_types' => array('offer', 'sale'),
            ),
            array(
                'name' => 'Post Completion Applicant Letter',
                'filename' => 'post-completion-applicant-letter.docx',
                'post_types' => array('sale'),
            ),
            array(
                'name' => 'Post Completion Vendor Letter',
                'filename' => 'post-completion-vendor-letter.docx',
                'post_types' => array('sale'),
            ),
            array(
                'name' => 'Post Completion Applicant Solicitor Invoice',
                'filename' => 'post-completion-applicant-solicitor-invoice.docx',
                'post_types' => array('sale'),
            ),
            array(
                'name' => 'Withdrawal Confirmation',
                'filename' => 'withdrawal-confirmation.docx',
                'post_types' => array('property'),
            ),
            array(
                'name' => 'Blank Applicant Letter',
                'filename' => 'blank-applicant-letter.docx',
                'post_types' => array('contact', 'viewing', 'offer', 'sale'),
            ),
            array(
                'name' => 'Blank Owner Letter',
                'filename' => 'blank-owner-letter.docx',
                'post_types' => array('contact', 'property', 'viewing', 'offer', 'sale'),
            ),
        );

        /*foreach ( $default_templates as $default_template )
        {
            $attachment_id = $this->handle_default_template_attachment( $default_template['filename'] );
            
            if ( $attachment_id !== FALSE )
            {
                $templates[] = array(
                    'name' => $default_template['name'],
                    'attachment_id' => $attachment_id,
                    'post_types' => $default_template['post_types'],
                );
            }
        }

        $settings['templates'] = $templates;

        update_option( 'propertyhive_documents', $settings );*/

        return $default_templates;
    }

    private function handle_default_template_attachment( $filename )
    {
        $upload = wp_upload_bits( $filename, null, file_get_contents( dirname( PH_DOCUMENTS_PLUGIN_FILE ) . '/default-document-templates/' . $filename ) );  

        if( isset($upload['error']) && $upload['error'] !== FALSE )
        {
            return false;
        }
        else
        {
            // We don't already have a thumbnail and we're presented with an image
            $wp_filetype = wp_check_filetype( $upload['file'], null );

            $attachment = array(
                 'post_mime_type' => $wp_filetype['type'],
                 'post_title' => $filename,
                 'post_content' => '',
                 'post_status' => 'inherit'
            );
            $attach_id = wp_insert_attachment( $attachment, $upload['file'] );
            
            if ( $attach_id === FALSE || $attach_id == 0 )
            {    
                return false;
            }
            else
            {  
                $attach_data = wp_generate_attachment_metadata( $attach_id, $upload['file'] );
                wp_update_attachment_metadata( $attach_id, $attach_data );

                return $attach_id;
            }
        }
    }
}

endif;

/**
 * Returns the main instance of PH_Documents to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return PH_Documents
 */
function PHDOCS() {
    return PH_Documents::instance();
}

PHDOCS();

if( is_admin() && file_exists(  dirname( __FILE__ ) . '/propertyhive-documents-update.php' ) )
{
    include_once( dirname( __FILE__ ) . '/propertyhive-documents-update.php' );
}