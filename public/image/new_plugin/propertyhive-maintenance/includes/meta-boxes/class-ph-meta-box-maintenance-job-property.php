<?php
/**
 * Maintenance Job Property Details
 *
 * @author 		PropertyHive
 * @category 	Admin
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Maintenance_Job_Property
 */
class PH_Meta_Box_Maintenance_Job_Property {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        global $wpdb, $thepostid;
        
        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';
        
        $property_id = get_post_meta( $post->ID, '_property_id', true );

        if ( !empty($property_id) )
        {
            $property = new PH_Property((int)$property_id);

            echo '<p class="form-field">
            
                <label>' . __('Address', 'propertyhive') . '</label>
                
                <a href="' . get_edit_post_link($property_id, '') . '">' . $property->get_formatted_full_address() . '</a> (<a href="' . get_permalink($property_id) . '" target="_blank">View On Website</a>)
                
            </p>';

            echo '<p class="form-field">
            
                <label>' . ( ( $property->department == 'residential-lettings' ) ? __('Landlord', 'propertyhive') : __('Owner', 'propertyhive') ) . '</label>';

            $owner_contact_ids = $property->_owner_contact_id;
            if ( 
                ( !is_array($owner_contact_ids) && $owner_contact_ids != '' && $owner_contact_ids != 0 ) 
                ||
                ( is_array($owner_contact_ids) && !empty($owner_contact_ids) )
            )
            {
                if ( !is_array($owner_contact_ids) )
                {
                    $owner_contact_ids = array($owner_contact_ids);
                }

                foreach ( $owner_contact_ids as $owner_contact_id )
                {
                    $owner = new PH_Contact((int)$owner_contact_id);
                    echo '<a href="' . get_edit_post_link($owner_contact_id, '') . '">' . get_the_title($owner_contact_id) . '</a><br>';
                    echo 'Telephone: ' . ( ( $owner->telephone_number != '' ) ? $owner->telephone_number : '-' ) . '<br>';
                    echo 'Email: ' . ( ( $owner->email_address != '' ) ? '<a href="mailto:' . $owner->email_address . '">' . $owner->email_address . '</a>' : '-' );
                    echo '<br><br>';
                }
            }
            else
            {
                echo 'No ' . ( ( $property->department == 'residential-lettings' ) ? __('landlord', 'propertyhive') : __('owner', 'propertyhive') ) . ' specified';
            }
                
            echo '</p>';
        }
        else
        {
            $reported_property_address = get_post_meta( $post->ID, '_reported_property_address', true );
            if ( get_post_meta( $post->ID, '_externally_reported', true ) == 'yes' && $reported_property_address != '' )
            {
                ?>
                <p class="form-field">
                    <label for=""><?php echo __('Property Address', 'propertyhive'); ?></label>
                    <?php echo __($reported_property_address, 'propertyhive'); ?>
                </p>
                <?php
            }

echo '<p class="form-field">
            
                <label for="maintenance_property_search">' . __('Search Properties', 'propertyhive') . '</label>
                
                <span style="position:relative;">

                    <input type="text" class="short" name="maintenance_property_search" id="maintenance_property_search" style="width:100%;" placeholder="' . __( 'Start typing to search...', 'propertyhive' ) . '..." autocomplete="false">

                    <div id="maintenance_search_property_results" style="display:none; position:absolute; z-index:99; background:#EEE; left:0; width:100%; border:1px solid #999; overflow-y:auto; max-height:150px;"></div>

                    <div id="maintenance_selected_properties" style="display:none;"></div>

                </span>
                
            </p>';

            echo '<input type="hidden" name="_property_id" id="_property_id" value="">';
?>
<script>

var maintenance_selected_properties = [];
<?php if (isset($_GET['property_id']) && $_GET['property_id'] != '') { $property = new PH_Property((int)$_GET['property_id']); ?>
maintenance_selected_properties[<?php echo $_GET['property_id']; ?>] = ({ post_title: '<?php echo $property->get_formatted_full_address(); ?>' });
<?php } ?>

jQuery(document).ready(function($)
{
    maintenance_update_selected_properties();
    
    $('#maintenance_property_search').on('keyup keypress', function(e)
    {
        var keyCode = e.charCode || e.keyCode || e.which;
        if (keyCode == 13)
        {
            event.preventDefault();
            return false;
        }
    });

    $('#maintenance_property_search').keyup(function()
    {
        var keyword = $(this).val();

        if (keyword.length == 0)
        {
            $('#maintenance_search_property_results').html('');
            $('#maintenance_search_property_results').hide();
            return false;
        }

        if (keyword.length < 3)
        {
            $('#maintenance_search_property_results').html('<div style="padding:10px;">Enter ' + (3 - keyword.length ) + ' more characters...</div>');
            $('#maintenance_search_property_results').show();
            return false;
        }

        var data = {
            action:         'propertyhive_search_properties',
            keyword:        keyword,
            department:     'residential-lettings',
            security:       '<?php echo wp_create_nonce( 'search-properties' ); ?>',
        };
        $.post( '<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) 
        {
            if (response == '' || response.length == 0)
            {
                $('#maintenance_search_property_results').html('<div style="padding:10px;">No results found for \'' + keyword + '\'</div>');
            }
            else
            {
                $('#maintenance_search_property_results').html('<ul style="margin:0; padding:0;"></ul>');
                for ( var i in response )
                {
                    $('#maintenance_search_property_results ul').append('<li style="margin:0; padding:0;"><a href="' + response[i].ID + '" style="color:#666; display:block; padding:7px 10px; background:#FFF; border-bottom:1px solid #DDD; text-decoration:none;">' + response[i].post_title + '</a></li>');
                }
            }
            $('#maintenance_search_property_results').show();
        });
    });

    $('body').on('click', '#maintenance_search_property_results ul li a', function(e)
    {
        e.preventDefault();

        maintenance_selected_properties = []; // reset to only allow one property for now
        maintenance_selected_properties[$(this).attr('href')] = ({ post_title: $(this).text() });

        $('#maintenance_search_property_results').html('');
        $('#maintenance_search_property_results').hide();

        $('#maintenance_property_search').val('');

        maintenance_update_selected_properties();
    });

    $('body').on('click', 'a.offer-remove-property', function(e)
    {
        e.preventDefault();

        var property_id = $(this).attr('href');

        delete(maintenance_selected_properties[property_id]);

        maintenance_update_selected_properties();
    });
});

function maintenance_update_selected_properties()
{
    jQuery('#_property_id').val();

    if ( Object.keys(maintenance_selected_properties).length > 0 )
    {
        jQuery('#maintenance_selected_properties').html('<ul></ul>');
        for ( var i in maintenance_selected_properties )
        {
            jQuery('#maintenance_selected_properties ul').append('<li><a href="' + i + '" class="offer-remove-property" style="color:inherit; text-decoration:none;"><span class="dashicons dashicons-no-alt"></span></a> ' + maintenance_selected_properties[i].post_title + '</li>');

            jQuery('#_property_id').val(i);
        }
        jQuery('#maintenance_selected_properties').show();
    }
    else
    {
        jQuery('#maintenance_selected_properties').html('');
        jQuery('#maintenance_selected_properties').hide();
    }
}

</script>
<?php
        }

        do_action('propertyhive_maintenance_job_property_fields');
	    
        echo '</div>';
        
        echo '</div>';
        
    }

    /**
     * Save meta box data
     */
    public static function save( $post_id, $post ) {
        global $wpdb;

        if ( isset($_POST['_property_id']) && !empty($_POST['_property_id']) )
        {
            update_post_meta( $post_id, '_property_id', $_POST['_property_id'] );
        }
    }

}
