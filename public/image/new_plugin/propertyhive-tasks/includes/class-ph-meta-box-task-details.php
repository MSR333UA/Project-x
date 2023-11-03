<?php
/**
 * Task Details
 *
 * @author 		PropertyHive
 * @category 	Admin
 * @package 	PropertyHive/Admin/Meta Boxes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Task_Details
 */
class PH_Meta_Box_Task_Details {

	/**
	 * Output the metabox
	 */
	public static function output( $post, $args = array() ) {
        global $wpdb, $thepostid;

        $thepostid = $post->ID;

        $original_post = $post;
        $original_thepostid = $thepostid;
        
        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';

        wp_nonce_field( 'propertyhive_save_data', 'propertyhive_meta_nonce' );

        $args = array( 
            'id' => '_original_status', 
            'value' => get_post_meta( $thepostid, '_status', TRUE ),
        );
        propertyhive_wp_hidden_input( $args );

        $args = array( 
            'id' => '_status', 
            'label' => __( 'Status', 'propertyhive' ),
            'options' => array(
                'open' => 'Open',
                'completed' => 'Completed',
            ), 
            'desc_tip' => false, 
            'description' => ( ( get_post_meta( $thepostid, '_status', TRUE ) == 'completed' ) ? 'Completed on ' . date("jS F Y", strtotime( get_post_meta( $thepostid, '_completed', TRUE ) )) : '' )
        );
        propertyhive_wp_select( $args );

        $args = array( 
            'id' => '_details', 
            'label' => __( 'Details', 'propertyhive' ), 
            'desc_tip' => false, 
        );
        propertyhive_wp_textarea_input( $args );

        $args = array( 
            'id' => '_due_date', 
            'label' => __( 'Due Date', 'propertyhive' ), 
            'desc_tip' => false, 
            'description' => __( 'Leave blank if no due date', 'propertyhive' ),
            'type' => 'date',
            'class' => 'date-picker',
        );
        if ( isset($_GET['start']) && $_GET['start'] != '' )
        {
            // $_GET['start'] should be a unix timestamp
            $args['value'] = date("Y-m-d", $_GET['start']);
        }
        propertyhive_wp_text_input( $args );

        echo '
        <p class="form-field"><label for="_assigned_to">' . __( 'Assigned To', 'propertyhive' ) . '</label>
        <select id="_assigned_to" name="_assigned_to[]" multiple="multiple" data-placeholder="' . __( 'Everyone', 'propertyhive' ) . '" class="multiselect attribute_values">';

        $assigned_to_options = array();
        if ( get_post_meta( $thepostid, '_assigned_to', TRUE ) != '' )
        {
            $assigned_to = get_post_meta( $thepostid, '_assigned_to', TRUE );
            if ( is_array($assigned_to) && !empty($assigned_to) )
            {
                $assigned_to_options = $assigned_to;
            }
        }

        $args = array(
            'number' => 9999,
            'orderby' => 'display_name',
            'role__not_in' => array('property_hive_contact') 
        );
        $user_query = new WP_User_Query( $args );

        if ( ! empty( $user_query->results ) ) 
        {
            foreach ( $user_query->results as $user ) 
            {
                echo '<option value="' . $user->ID . '"';
                if ( in_array($user->ID, $assigned_to_options) )
                {
                    echo ' selected';
                }
                echo '>' . $user->display_name . '</option>';
            }
        }

        echo '</select>
        </p>';

        if ( get_post_meta( $thepostid, '_related_to', TRUE ) == '' )
        {
            echo '<p class="form-field">
            
                <label for="task_related_to">' . __('Related To', 'propertyhive') . '</label>
                
                <span style="position:relative;">

                    <input type="text" name="task_related_to_search" id="task_related_to_search" style="width:100%;" placeholder="' . __( 'Search Properties and Contacts', 'propertyhive' ) . '..." autocomplete="false">

                    <div id="task_related_to_search_results" style="display:none; position:absolute; z-index:99; background:#EEE; left:0; width:100%; border:1px solid #999; overflow-y:auto; max-height:150px;"></div>

                    <div id="task_selected_related_to" style="display:none;"></div>

                </span>
                
            </p>';

            echo '<input type="hidden" name="_related_to" id="_related_to" value="">';
?>
<script>

var task_selected_related_to = [];
var task_search_related_to_timeout;

jQuery(document).ready(function($)
{
    task_update_selected_related_to();
    
    $('#task_related_to_search').on('keyup keypress', function(e)
    {
        var keyCode = e.charCode || e.keyCode || e.which;
        if (keyCode == 13)
        {
            event.preventDefault();
            return false;
        }
    });

    $('#task_related_to_search').keyup(function()
    {
        clearTimeout(task_search_related_to_timeout);
        task_search_related_to_timeout = setTimeout(function() { task_perform_related_to_search(); }, 400);
    });

    $('body').on('click', '#task_related_to_search_results ul li a', function(e)
    {
        e.preventDefault();

        task_selected_related_to = []; // reset to only allow one property for now
        task_selected_related_to.push({ id: $(this).attr('href'), post_title: $(this).text() });

        $('#task_related_to_search_results').html('');
        $('#task_related_to_search_results').hide();

        $('#task_related_to_search').val('');

        task_update_selected_related_to();
    });

    $('body').on('click', 'a.task-remove-related-to', function(e)
    {
        e.preventDefault();

        var related_to = $(this).attr('href');

        for (var key in task_selected_related_to) 
        {
            if (task_selected_related_to[key].id == related_to ) 
            {
                task_selected_related_to.splice(key, 1);
            }
        }

        task_update_selected_related_to();
    });
});

function task_perform_related_to_search()
{
    var keyword = jQuery('#task_related_to_search').val();

    if (keyword.length == 0)
    {
        jQuery('#task_related_to_search_results').html('');
        jQuery('#task_related_to_search_results').hide();
        return false;
    }

    if (keyword.length < 3)
    {
        jQuery('#task_related_to_search_results').html('<div style="padding:10px;">Enter ' + (3 - keyword.length ) + ' more characters...</div>');
        jQuery('#task_related_to_search_results').show();
        return false;
    }

    var results = [];

    var data = {
        action:         'propertyhive_search_properties',
        keyword:        keyword,
        security:       '<?php echo wp_create_nonce( 'search-properties' ); ?>',
    };
    jQuery.post( '<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) 
    {
        if (response == '' || response.length == 0)
        {
            
        }
        else
        {
            results.push('<li style="margin:0; padding:7px 10px; font-weight:600;">Properties (' + response.length + ')</li>');
            for ( var i in response )
            {
                results.push('<li style="margin:0; padding:0;"><a href="property|' + response[i].ID + '" style="color:#666; display:block; padding:7px 10px; background:#FFF; border-bottom:1px solid #DDD; text-decoration:none;">' + response[i].post_title + '</a></li>');
            }
        }

        var data = {
            action:         'propertyhive_search_contacts',
            keyword:        keyword,
            security:       '<?php echo wp_create_nonce( 'search-contacts' ); ?>',
        };
        jQuery.post( '<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) 
        {
            if (response == '' || response.length == 0)
            {
                
            }
            else
            {
                results.push('<li style="margin:0; padding:7px 10px; font-weight:600;">Contacts (' + response.length + ')</li>');
                for ( var i in response )
                {
                    results.push('<li style="margin:0; padding:0;"><a href="contact|' + response[i].ID + '" style="color:#666; display:block; padding:7px 10px; background:#FFF; border-bottom:1px solid #DDD; text-decoration:none;">' + response[i].post_title + '</a></li>');
                }
            }

            if ( results.length == 0 )
            {
                jQuery('#task_related_to_search_results').html('<div style="padding:10px;">No results found for \'' + keyword + '\'</div>');
            }
            else
            {
                jQuery('#task_related_to_search_results').html('<ul style="margin:0; padding:0;"></ul>');
                for ( var i in results )
                {
                    jQuery('#task_related_to_search_results ul').append(results[i]);
                }
            }

            jQuery('#task_related_to_search_results').show();
        });
    });
}

function task_update_selected_related_to()
{
    jQuery('#_related_to').val('');

    if ( task_selected_related_to.length > 0 )
    {
        jQuery('#task_selected_related_to').html('<ul></ul>');
        for ( var i in task_selected_related_to )
        {
            jQuery('#task_selected_related_to ul').append('<li><a href="' + task_selected_related_to[i].id + '" class="task-remove-related-to" style="color:inherit; text-decoration:none;"><span class="dashicons dashicons-no-alt"></span></a> ' + task_selected_related_to[i].post_title + '</li>');

            jQuery('#_related_to').val(task_selected_related_to[i].id);
        }
        jQuery('#task_selected_related_to').show();
    }
    else
    {
        jQuery('#task_selected_related_to').html('');
        jQuery('#task_selected_related_to').hide();
    }

    jQuery('#_related_to').trigger('change');
}

</script>
<?php
        }
        else
        {
            $related_to = '-';
            $explode_related_to = explode("|", get_post_meta( $thepostid, '_related_to', TRUE ));
            if ( $explode_related_to[0] == 'property' )
            {
                $property = new PH_Property( (int)$explode_related_to[1] );
                $related_to = $property->get_formatted_full_address();
            }
            elseif ( $explode_related_to[0] == 'contact' )
            {
                $related_to = get_the_title($explode_related_to[1]);
            }
            echo '
            <p class="form-field">
            <label for="_related_to">' . __( 'Related To', 'propertyhive' ) . '</label>
                <a href="' . get_edit_post_link($explode_related_to[1]) . '">' . $related_to . '</a>
            </p>';

            echo '<input type="hidden" name="_related_to" id="_related_to" value="' . get_post_meta( $thepostid, '_related_to', TRUE ) . '">';
        }

        echo '</div>';

        echo '</div>';
        
        $post = $original_post;
        $thepostid = $original_thepostid;
        setup_postdata($post);
    }

    /**
     * Save meta box data
     */
    public static function save( $post_id, $post ) {
        global $wpdb;

        update_post_meta( $post_id, '_status', $_POST['_status'] );
        if ( $_POST['_status'] == 'completed' && $_POST['_original_status'] != 'completed' )
        {
            update_post_meta( $post_id, '_completed', date("Y-m-d") );
        }

        update_post_meta( $post_id, '_details', wp_strip_all_tags( $_POST['_details'] ) );
        update_post_meta( $post_id, '_due_date', wp_strip_all_tags( $_POST['_due_date'] ) );
        update_post_meta( $post_id, '_assigned_to', ( isset($_POST['_assigned_to']) && is_array($_POST['_assigned_to']) && !empty($_POST['_assigned_to']) ) ? $_POST['_assigned_to'] : '' );
        update_post_meta( $post_id, '_related_to', wp_strip_all_tags( $_POST['_related_to'] ) );
    }
}
