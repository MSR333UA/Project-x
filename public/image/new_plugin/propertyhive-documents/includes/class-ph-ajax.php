<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PropertyHive PH_Documents_AJAX
 *
 * AJAX Event Handler for Maintenance add on
 *
 * @class 		PH_Documents_AJAX
 * @version		1.0.0
 * @package		PropertyHive/Classes
 * @category	Class
 * @author 		PropertyHive
 */
class PH_Documents_AJAX {

	/**
	 * Hook into ajax events
	 */
	public function __construct() {

		// propertyhive_EVENT => nopriv
		$ajax_events = array(
            'get_documents_meta_box' => false,
            'create_document' => false,
            'change_document_public' => false,
            'delete_document' => false,
            'upload_document' => false,
		);

		foreach ( $ajax_events as $ajax_event => $nopriv ) {
			add_action( 'wp_ajax_propertyhive_' . $ajax_event, array( $this, $ajax_event ) );

			if ( $nopriv ) {
				add_action( 'wp_ajax_nopriv_propertyhive_' . $ajax_event, array( $this, $ajax_event ) );
			}
		}
	}
    
    public function ph_documents_upload_dir( $arr ) 
    {
        $mydir = '/ph_documents';

        $arr['path'] = $arr['basedir'] . $mydir;
        $arr['url'] = $arr['baseurl'] . $mydir;
        //$arr['subdir'] .= $folder;

        return $arr;
    }

	/**
	 * Output headers for JSON requests
	 */
	private function json_headers() {
		header( 'Content-Type: application/json; charset=utf-8' );
	}

    public function get_documents_meta_box()
    {
        check_ajax_referer( 'get_documents_meta_box', 'security' );

        global $post;

        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';

        $documents = get_post_meta( $_POST['post_id'], '_documents', TRUE );

        if ( is_array($documents) && !empty($documents) )
        {
            echo '<table style="width:100%">
                    <thead>
                        <tr>
                            <th style="text-align:left;">' . __( 'Document Name', 'propertyhive' ) . '</th>';
            if ( get_post_type($_POST['post_id']) == 'contact' ) { echo '<th style="text-align:center;">' . __( 'Public', 'propertyhive' ) . '<img class="help_tip" data-tip="If you permit users to login, associated public documents will appear within their account." src="' . esc_url( PH()->plugin_url() ) . '/assets/images/help.png" height="16" width="16" /></th>'; }
            echo '<th style="text-align:left;">' . __( 'Created', 'propertyhive' ) . '</th>
                            <th style="text-align:left;"></th>
                        </tr>
                    </thead>
                    <tbody>';

            foreach ( $documents as $i => $document )
            {
                echo '<tr>';
                    echo '<td style="text-align:left;"><a href="' . wp_get_attachment_url($document['attachment_id']) . '" title="' . __( 'Open Document', 'propertyhive' ) . '" target="_blank">' . ( ( isset($document['name']) && $document['name'] != '' ) ? $document['name'] : '-' ) . '</a></td>';
                    if ( get_post_type($_POST['post_id']) == 'contact' ) { echo '<td style="text-align:center;"><input type="checkbox" class="document-public" value="' . $i . '" name="public[]" id="public_"' . $i . '"' . ( ( isset($document['public']) && $document['public'] ) ? ' checked' : '' ) . '></td>'; }
                    echo '<td style="text-align:left;">';
                    if ( isset($document['created_at']) && $document['created_at'] != '' )
                    {
                        echo '<span title="' . $document['created_at'] . '">' . date("jS F Y", strtotime($document['created_at'])) . '</span>';
                    }
                    else
                    {
                        echo '-';
                    }
                    echo '</td>';
                    echo '<td style="text-align:left;"><a href="' . wp_get_attachment_url($document['attachment_id']) . '" class="button" title="' . __( 'Open Document', 'propertyhive' ) . '" target="_blank">Download</a> <a href="" data-document-id="' . $i . '" class="delete-document button">Delete</a></td>';
                echo '</tr>';
            }

            echo '
                    </tbody>
                </table>
                <br>';
        }
        else
        {
            echo '<p>' . __( 'No documents created yet for this record', 'propertyhive') . '</p>';
        }
        
        echo '</div>';
        
        echo '</div>';

        wp_die();
    }

    public function create_document()
    {
        $response = array();

        check_ajax_referer( 'create_document', 'security' );

        if ( !isset($_POST['attachment_id']) || (isset($_POST['attachment_id']) && ph_clean($_POST['attachment_id']) == '') )
        {    
            $response = array('success' => false, 'error' => 'No template selected');

            header("Content-Type: application/json");
            echo json_encode($response);

            wp_die();
        }

        if ( !isset($_POST['name']) || (isset($_POST['name']) && ph_clean($_POST['name']) == '') )
        {    
            $response = array('success' => false, 'error' => 'No name entered');

            header("Content-Type: application/json");
            echo json_encode($response);

            wp_die();
        }

        require_once dirname(__FILE__) . '/../vendor/autoload.php';

        $existing_documents = get_post_meta( $_POST['post_id'], '_documents', TRUE );

        if ( !is_array($existing_documents) )
        {
            $existing_documents = array();
        }

        $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor(get_attached_file($_POST['attachment_id']));
        
        $PH_Tag_Manager = new PH_Documents_Tag_Manager();

        $merge_tags = array();
        $merge_values = array();

        switch ( get_post_type((int)$_POST['post_id']) )
        {
            case "property":
            {
                list($merge_tags, $merge_values) = $PH_Tag_Manager->replace_property_tags( $merge_tags, $merge_values, (int)$_POST['post_id'] );
                break;
            }
            case "contact":
            {
                $contact_types = get_post_meta( $_POST['post_id'], '_contact_types', TRUE );
                if ( is_array($contact_types) && !empty($contact_types) )
                {
                    foreach ( $contact_types as $contact_type )
                    {
                        switch ( $contact_type )
                        {
                            case "applicant":
                            {
                                list($merge_tags, $merge_values) = $PH_Tag_Manager->replace_applicant_tags( $merge_tags, $merge_values, (int)$_POST['post_id'] );
                                break;
                            }
                            case "owner":
                            {
                                list($merge_tags, $merge_values) = $PH_Tag_Manager->replace_owner_tags( $merge_tags, $merge_values, (int)$_POST['post_id'] );
                                break;
                            }
                        }
                    }
                }
                list($merge_tags, $merge_values) = $PH_Tag_Manager->replace_contact_tags( $merge_tags, $merge_values, (int)$_POST['post_id'] );
                break;
            }
            case "appraisal":
            {
                list($merge_tags, $merge_values) = $PH_Tag_Manager->replace_appraisal_tags( $merge_tags, $merge_values, (int)$_POST['post_id'] );
                break;
            }
            case "viewing":
            {
                list($merge_tags, $merge_values) = $PH_Tag_Manager->replace_viewing_tags( $merge_tags, $merge_values, (int)$_POST['post_id'] );
                break;
            }
            case "offer":
            {
                list($merge_tags, $merge_values) = $PH_Tag_Manager->replace_offer_tags( $merge_tags, $merge_values, (int)$_POST['post_id'] );
                break;
            }
            case "sale":
            {
                list($merge_tags, $merge_values) = $PH_Tag_Manager->replace_sale_tags( $merge_tags, $merge_values, (int)$_POST['post_id'] );
                break;
            }
            case "tenancy":
            {
                list($merge_tags, $merge_values) = $PH_Tag_Manager->replace_tenancy_tags( $merge_tags, $merge_values, (int)$_POST['post_id'] );
                break;
            }
        }

        // replace negotiator tags again in case being done on something where no negotiator exists, and  use current logged in user as fallback/default
        list($merge_tags, $merge_values) = $PH_Tag_Manager->replace_negotiator_tags( $merge_tags, $merge_values, get_current_user_id() );

        list($merge_tags, $merge_values) = $PH_Tag_Manager->replace_general_tags( $merge_tags, $merge_values, (int)$_POST['post_id'] );

        list($merge_tags, $merge_values) = apply_filters('propertyhive_documents_merge_tags_values', array($merge_tags, $merge_values));

        $templateProcessor->setValue(
            $merge_tags,
            $merge_values
        ); 

        add_filter( 'upload_dir', array( $this, 'ph_documents_upload_dir' ) );

        $tmpfname = tempnam(sys_get_temp_dir(), 'phdoc');
         
        $templateProcessor->saveAs($tmpfname);

        $upload = wp_upload_bits( (int)$_POST['post_id'] . '-' . (int)$_POST['attachment_id'] . '-' . sanitize_title(ph_clean($_POST['name'])) . '-' . time() . '.docx', null, file_get_contents($tmpfname));

        unlink($tmpfname);
                                        
        if( isset($upload['error']) && $upload['error'] !== FALSE )
        {
            $response = array('success' => false, 'error' => $upload['error']);
        }
        else
        {
            $wp_filetype = wp_check_filetype( $upload['file'], null );

            $attachment = array(
                 'post_mime_type' => $wp_filetype['type'],
                 'post_title' => ph_clean($_POST['name']) . ' (' . (int)$_POST['post_id'] . ')',
                 'post_content' => '',
                 'post_status' => 'inherit'
            );
            $attach_id = wp_insert_attachment( $attachment, $upload['file'], (int)$_POST['post_id'] );
            
            if ( $attach_id === FALSE || $attach_id == 0 )
            {    
                $response = array('success' => false, 'error' => 'Failed inserting attachment');
            }
            else
            {  
                $attach_data = wp_generate_attachment_metadata( $attach_id, $upload['file'] );
                wp_update_attachment_metadata( $attach_id, $attach_data );

                $existing_documents[] = array(
                    'name' => ph_clean($_POST['name']),
                    'attachment_id' => $attach_id,
                    'template_attachment_id' => (int)$_POST['attachment_id'],
                    'public' => false,
                    'created_at' => date("Y-m-d H:i:s")
                );

                update_post_meta( $_POST['post_id'], '_documents', $existing_documents );

                $response = array('success' => true);
            }
        }

        remove_filter( 'upload_dir', array( $this, 'ph_documents_upload_dir' ) );

        header("Content-Type: application/json");
        echo json_encode($response);

        wp_die();
    }

    public function change_document_public()
    {
        $documents = get_post_meta( $_POST['post_id'], '_documents', TRUE );
        if ( isset($documents[$_POST['document_id']]) )
        {
            $documents[$_POST['document_id']]['public'] = ( ( $_POST['public'] == 'true' ) ? true : false );
            update_post_meta( $_POST['post_id'], '_documents', $documents );
        }
        
        wp_die();
    }

    public function delete_document()
    {
        $documents = get_post_meta( (int)$_POST['post_id'], '_documents', TRUE );
        if ( isset($documents[(int)$_POST['document_id']]) )
        {
            wp_delete_attachment($documents[(int)$_POST['document_id']]['attachment_id'], TRUE);

            unset($documents[(int)$_POST['document_id']]);
            update_post_meta( (int)$_POST['post_id'], '_documents', $documents );
        }
        
        wp_die();
    }

    public function upload_document()
    {
        $response = array();

        check_ajax_referer( 'upload_document', 'security' );

        add_filter( 'upload_dir', array( $this, 'ph_documents_upload_dir' ) );

        if ( !isset($_POST['name']) || (isset($_POST['name']) && $_POST['name'] == '') )
        {    
            $response = array('success' => false, 'error' => 'No name entered');

            header("Content-Type: application/json");
            echo json_encode($response);

            wp_die();
        }

        $existing_documents = get_post_meta( $_POST['post_id'], '_documents', TRUE );

        if ( !is_array($existing_documents) )
        {
            $existing_documents = array();
        }

        $uploadedfile = $_FILES['file'];
        $upload_overrides = array('test_form' => false);
        $upload = wp_handle_upload($uploadedfile, $upload_overrides);
                              
        if( isset($upload['error']) && $upload['error'] !== FALSE )
        {
            $response = array('success' => false, 'error' => $upload['error']);
        }
        else
        {
            // We don't already have a thumbnail and we're presented with an image
            $wp_filetype = wp_check_filetype( $upload['file'], null );

            $attachment = array(
                 'post_mime_type' => $wp_filetype['type'],
                 'post_title' => $_POST['name'] . ' (' . get_the_title($_POST['post_id']) . ')',
                 'post_content' => '',
                 'post_status' => 'inherit'
            );
            $attach_id = wp_insert_attachment( $attachment, $upload['file'], $_POST['post_id'] );
            
            if ( $attach_id === FALSE || $attach_id == 0 )
            {    
                $response = array('success' => false, 'error' => 'Failed inserting attachment');
            }
            else
            {  
                $attach_data = wp_generate_attachment_metadata( $attach_id, $upload['file'] );
                wp_update_attachment_metadata( $attach_id, $attach_data );

                $existing_documents[] = array(
                    'name' => $_POST['name'],
                    'attachment_id' => $attach_id,
                    'public' => false,
                    'created_at' => date("Y-m-d H:i:s")
                );

                update_post_meta( $_POST['post_id'], '_documents', $existing_documents );

                $response = array('success' => true);
            }
        }

        remove_filter( 'upload_dir', array( $this, 'ph_documents_upload_dir' ) );

        header("Content-Type: application/json");
        echo json_encode($response);

        wp_die();
    }
}

new PH_Documents_AJAX();