<?php
/**
 * Documents
 *
 * @author      PropertyHive
 * @category    Admin
 * @package     PropertyHive/Admin/Meta Boxes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Documents
 */
class PH_Meta_Box_Documents {

    /**
     * Output the metabox
     */
    public static function output( $post ) {
        
        echo '<div id="propertyhive_documents_meta_box">Loading...</div>';

        $existing_settings = get_option( 'propertyhive_documents', array() );

        $existing_templates = array();
        if ( isset($existing_settings['templates']) && is_array($existing_settings['templates']) )
        {
            $existing_templates = $existing_settings['templates'];
        }

        echo '<hr><p>
        <select name="document_template" id="document_template">
            <option value="">' . __( 'Select Template', 'propertyhive' ) . '...</option>';
        foreach ( $existing_templates as $i => $template )
        {
            if ( 
                isset($template['attachment_id']) && $template['attachment_id'] != '' 
                &&
                (
                    !isset($template['post_types'])
                    ||
                    ( isset($template['post_types']) && empty($template['post_types']) )
                    || 
                    ( isset($template['post_types']) && is_array($template['post_types']) && in_array(get_post_type($post->ID), $template['post_types']) )
                )
            )
            {
                echo '<option value="' . $template['attachment_id'] . '">' . $template['name'] . '</option>';
            }
        }
        echo '</select><br><input type="text" name="create_document_name" id="create_document_name" placeholder="Document Name" value=""><br><a href="" class="create-document button">' . __( 'Create New Document', 'propertyhive' ) . '</a></p>';

        echo '<p>' . __( 'or', 'propertyhive' ) . '</p>';

        echo '<p>
        <input type="file" name="document_upload" id="document_upload"><br><input type="text" name="upload_document_name" id="upload_document_name" placeholder="Document Name" value=""><br><a href="" class="upload-document button">' . __( 'Upload Document', 'propertyhive' ) . '</a></p>';
?>
<script>
var original_document_template_border;
var creating_document = false;
jQuery(document).ready(function()
{
    original_document_template_border = jQuery('#document_template').css('border');

    jQuery('body').on('change', '.document-public', function()
    {
        var public = false;
        if ( jQuery(this).is(':checked') )
        {
            public = true;
        }

        var document_id = jQuery(this).val();
        
        var data = {
            action: 'propertyhive_change_document_public',
            post_id: <?php echo $post->ID; ?>,
            document_id: document_id,
            public: public
        }

        jQuery.post( '<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) 
        {
            //alert(response);
        });
    });

    jQuery('body').on('click', 'a.delete-document', function(e)
    {
        e.preventDefault();

        var confirmbox = confirm('Are you sure you wish to delete this document? Please note that this cannot be undone.');
        if ( !confirmbox )
        {
            return false;
        }

        jQuery(this).html('Deleting...');
        jQuery(this).attr('disabled', 'disabled');

        var document_id = jQuery(this).attr('data-document-id');
        
        var data = {
            action: 'propertyhive_delete_document',
            post_id: <?php echo $post->ID; ?>,
            document_id: document_id
        }

        jQuery.post( '<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) 
        {
            //alert(response);
            load_documents_meta_box();
        });
    });

    jQuery('#document_template').change(function()
    {
        //jQuery('#document_template').css('border', original_document_template_border);
        //if ( jQuery('#create_document_name').val() == '' )
        //{
            jQuery('#create_document_name').val( jQuery('#document_template :selected').text() );
        //}
    });

    jQuery('a.create-document').click(function(e)
    {
        e.preventDefault();

        if ( creating_document )
        {
            return false;
        }

        // Validate a template is selected
        if ( jQuery('#document_template').val() == '' )
        {
            jQuery('#document_template').css('border', '1px solid #A00');
            return false;
        }

        creating_document = true;
        jQuery('a.create-document').html('Creating...');
        jQuery('a.create-document').attr('disabled', 'disabled');

        // do AJAX request then reload documents grid
        var data = {
            action: 'propertyhive_create_document',
            post_id: <?php echo $post->ID; ?>,
            name:  jQuery('#create_document_name').val(),
            attachment_id: jQuery('#document_template').val(),
            security: '<?php echo wp_create_nonce( 'create_document' ); ?>'
        }

        jQuery.post( '<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) 
        {
            load_documents_meta_box();

            if (response.success == false)
            {
                alert(response.error);
            }
            else
            {
                jQuery('#create_document_name').val('');
                jQuery('#document_template').val('');
            }

            creating_document = false;
            jQuery('a.create-document').html('Create New Document');
            jQuery('a.create-document').attr('disabled', false);
        }).fail(function( jqXHR, textStatus, errorThrown ) 
        {
            alert( "Failed: " + errorThrown );
            console.log(textStatus);
            console.log(errorThrown);

            creating_document = false;
            jQuery('a.create-document').html('Create New Document');
            jQuery('a.create-document').attr('disabled', false);
        });
    });

    jQuery('#upload_document_name').keyup(function()
    {
        jQuery('#upload_document_name').css('border', original_document_template_border);
    });

    jQuery('a.upload-document').click(function(e)
    {
        e.preventDefault();

        if ( creating_document )
        {
            return false;
        }

        // Validate a name is entered
        if ( jQuery('#upload_document_name').val() == '' )
        {
            jQuery('#upload_document_name').css('border', '1px solid #A00');
            return false;
        }

        creating_document = true;
        jQuery('a.upload-document').html('Uploading...');
        jQuery('a.upload-document').attr('disabled', 'disabled');

        var file_data = jQuery('#document_upload').prop('files')[0];   
        var form_data = new FormData();   
        form_data.append('action', 'propertyhive_upload_document');
        form_data.append('post_id', <?php echo $post->ID; ?>);
        form_data.append('name', jQuery('#upload_document_name').val());
        form_data.append('file', file_data);               
        form_data.append('security', '<?php echo wp_create_nonce( 'upload_document' ); ?>');

        jQuery.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'post',
            contentType: false,
            processData: false,
            data: form_data,
            success: function (response) 
            {
                load_documents_meta_box();

                if (response.success == false)
                {
                    alert(response.error);
                }

                creating_document = false;
                jQuery('a.upload-document').html('Upload Document');
                jQuery('a.upload-document').attr('disabled', false);
            }
        });
    });
});

function load_documents_meta_box()
{
    var data = {
        action: 'propertyhive_get_documents_meta_box',
        post_id: <?php echo $post->ID; ?>,
        security: '<?php echo wp_create_nonce( 'get_documents_meta_box' ); ?>'
    }

    jQuery.post( '<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) 
    {
        jQuery('#propertyhive_documents_meta_box').html(response);
        activateTipTip();
    }, 'html');
}
</script>
<?php
    }

    /**
     * Save meta box data
     */
    public static function save( $post_id, $post ) {
        global $wpdb;
        

    }

}
