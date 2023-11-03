<?php
/**
 * Appointment Details
 *
 * @author 		PropertyHive
 * @category 	Admin
 * @package 	PropertyHive/Admin/Meta Boxes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Appointment_Details
 */
class PH_Meta_Box_Appointment_Details {

	/**
	 * Output the metabox
	 */
	public static function output( $post, $args = array() ) {
        global $wpdb, $thepostid;

        $thepostid = $post->ID;

        $original_post = $post;
        $original_thepostid = $thepostid;

        $all_day = false;
        if ( isset($_GET['all_day']) && $_GET['all_day'] == 'yes' )
        {
            $all_day = true;
        }
        else
        {
            $all_day = get_post_meta( get_the_ID(), '_all_day', TRUE ) == 'yes' ? true : false;
        }
        
        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';

        wp_nonce_field( 'propertyhive_save_data', 'propertyhive_meta_nonce' );

        $args = array( 
            'id' => '_details', 
            'label' => __( 'Details', 'propertyhive' ), 
            'desc_tip' => false, 
        );
        propertyhive_wp_textarea_input( $args );

        if ( isset($_GET['start']) && $_GET['start'] != '' )
        {
            $start_date_time = date("Y-m-d H:i:s", ph_clean($_GET['start']));
        }
        else
        {
            $start_date_time = get_post_meta( get_the_ID(), '_start_date_time', TRUE );
        }

        echo '<p class="form-field event_start_time_field">
        
            <label for="_start_date">' . __('Start Date / Time', 'propertyhive') . '</label>

            <input type="date" class="small" name="_start_date" id="_start_date" value="' . date("Y-m-d", strtotime($start_date_time)) . '" placeholder="">
            <select id="_start_time_hours" name="_start_time_hours" class="select short" style="width:55px;' . ( $all_day ? 'display:none;' : '' ) . '">';
        
        if ( $start_date_time == '' )
        {
            $value = date("H");
        }
        else
        {
            $value = date( "H", strtotime( $start_date_time ) );
        }
        for ( $i = 0; $i < 23; ++$i )
        {
            $j = str_pad($i, 2, '0', STR_PAD_LEFT);
            echo '<option value="' . $j . '"';
            if ($i == $value) { echo ' selected'; }
            echo '>' . $j . '</option>';
        }
        
        echo '</select>
        <span id="start_time_separator" style="' . ( $all_day ? 'display:none;' : '' ) . '">:</span>
        <select id="_start_time_minutes" name="_start_time_minutes" class="select short" style="width:55px;' . ( $all_day ? 'display:none;' : '' ) . '">';
        
        if ( $start_date_time == '' )
        {
            $value = '';
        }
        else
        {
            $value = date( "i", strtotime( $start_date_time ) );
        }
        for ( $i = 0; $i < 60; $i+=5 )
        {
            $j = str_pad($i, 2, '0', STR_PAD_LEFT);
            echo '<option value="' . $j . '"';
            if ($i == $value) { echo ' selected'; }
            echo '>' . $j . '</option>';
        }
        
        echo '</select>
            
        </p>';

        if ( isset($_GET['end']) && $_GET['end'] != '' )
        {
            if ( $all_day )
            {
                $_GET['end'] = $_GET['end'] - 1;
            }
            $end_date_time = date("Y-m-d H:i:s", ph_clean($_GET['end']));
        }
        else
        {
            $end_date_time = get_post_meta( get_the_ID(), '_end_date_time', TRUE );
        }

        echo '<p class="form-field event_end_time_field">
        
            <label for="_end_date">' . __('End Date / Time', 'propertyhive') . '</label>

            <input type="date" class="small" name="_end_date" id="_end_date" value="' . date("Y-m-d", strtotime($end_date_time)) . '" placeholder="">
            <select id="_end_time_hours" name="_end_time_hours" class="select short" style="width:55px;' . ( $all_day ? 'display:none;' : '' ) . '">';
        
        if ( $end_date_time == '' )
        {
            $value = date("H");
        }
        else
        {
            $value = date( "H", strtotime( $end_date_time ) );
        }
        for ( $i = 0; $i < 23; ++$i )
        {
            $j = str_pad($i, 2, '0', STR_PAD_LEFT);
            echo '<option value="' . $j . '"';
            if ($i == $value) { echo ' selected'; }
            echo '>' . $j . '</option>';
        }
        
        echo '</select>
        <span id="end_time_separator" style="' . ( $all_day ? 'display:none;' : '' ) . '">:</span>
        <select id="_end_time_minutes" name="_end_time_minutes" class="select short" style="width:55px;' . ( $all_day ? 'display:none;' : '' ) . '">';
        
        if ( $end_date_time == '' )
        {
            $value = '';
        }
        else
        {
            $value = date( "i", strtotime( $end_date_time ) );
        }
        for ( $i = 0; $i < 60; $i+=5 )
        {
            $j = str_pad($i, 2, '0', STR_PAD_LEFT);
            echo '<option value="' . $j . '"';
            if ($i == $value) { echo ' selected'; }
            echo '>' . $j . '</option>';
        }
        
        echo '</select>
            
        </p>';

        $args = array( 
            'id' => '_all_day', 
            'label' => __( 'All Day Appointment', 'propertyhive' ), 
            'desc_tip' => false, 
        );
        if ( $all_day )
        {
            $args['value'] = 'yes';
        }
        propertyhive_wp_checkbox( $args );

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
            'role__not_in' => array('property_hive_contact', 'subscriber') 
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

                <label for="appointment_related_to">' . __('Related To', 'propertyhive') . '</label>

                <span style="position:relative;">

                    <input type="text" name="appointment_related_to_search" id="appointment_related_to_search" style="width:100%;" placeholder="' . __( 'Search Properties and Contacts', 'propertyhive' ) . '..." autocomplete="false">

                    <div id="appointment_related_to_search_results" style="display:none; position:absolute; z-index:99; background:#EEE; left:0; width:100%; border:1px solid #999; overflow-y:auto; max-height:150px;"></div>

                    <div id="appointment_selected_related_to" style="display:none;"></div>

                </span>

            </p>';

            echo '<input type="hidden" name="_related_to" id="_related_to" value="">';
            ?>
            <script>

            var appointment_selected_related_to = [];
            var appointment_search_related_to_timeout;

            jQuery(document).ready(function($)
            {
                appointment_update_selected_related_to();

                $('#appointment_related_to_search').on('keyup keypress', function(e)
                {
                    var keyCode = e.charCode || e.keyCode || e.which;
                    if (keyCode == 13)
                    {
                        event.preventDefault();
                        return false;
                    }
                });

                $('#appointment_related_to_search').keyup(function()
                {
                    clearTimeout(appointment_search_related_to_timeout);
                    appointment_search_related_to_timeout = setTimeout(function() { appointment_perform_related_to_search(); }, 400);
                });

                $('body').on('click', '#appointment_related_to_search_results ul li a', function(e)
                {
                    e.preventDefault();

                    appointment_selected_related_to = []; // reset to only allow one property for now
                    appointment_selected_related_to.push({ id: $(this).attr('href'), post_title: $(this).text() });

                    $('#appointment_related_to_search_results').html('');
                    $('#appointment_related_to_search_results').hide();

                    $('#appointment_related_to_search').val('');

                    appointment_update_selected_related_to();
                });

                $('body').on('click', 'a.appointment-remove-related-to', function(e)
                {
                    e.preventDefault();

                    var related_to = $(this).attr('href');

                    for (var key in appointment_selected_related_to)
                    {
                        if (appointment_selected_related_to[key].id == related_to )
                        {
                            appointment_selected_related_to.splice(key, 1);
                        }
                    }

                    appointment_update_selected_related_to();
                });
            });

            function appointment_perform_related_to_search()
            {
                var keyword = jQuery('#appointment_related_to_search').val();

                if (keyword.length == 0)
                {
                    jQuery('#appointment_related_to_search_results').html('');
                    jQuery('#appointment_related_to_search_results').hide();
                    return false;
                }

                if (keyword.length < 3)
                {
                    jQuery('#appointment_related_to_search_results').html('<div style="padding:10px;">Enter ' + (3 - keyword.length ) + ' more characters...</div>');
                    jQuery('#appointment_related_to_search_results').show();
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
                            jQuery('#appointment_related_to_search_results').html('<div style="padding:10px;">No results found for \'' + keyword + '\'</div>');
                        }
                        else
                        {
                            jQuery('#appointment_related_to_search_results').html('<ul style="margin:0; padding:0;"></ul>');
                            for ( var i in results )
                            {
                                jQuery('#appointment_related_to_search_results ul').append(results[i]);
                            }
                        }

                        jQuery('#appointment_related_to_search_results').show();
                    });
                });
            }

            function appointment_update_selected_related_to()
            {
                jQuery('#_related_to').val('');

                if ( appointment_selected_related_to.length > 0 )
                {
                    jQuery('#appointment_selected_related_to').html('<ul></ul>');
                    for ( var i in appointment_selected_related_to )
                    {
                        jQuery('#appointment_selected_related_to ul').append('<li><a href="' + appointment_selected_related_to[i].id + '" class="appointment-remove-related-to" style="color:inherit; text-decoration:none;"><span class="dashicons dashicons-no-alt"></span></a> ' + appointment_selected_related_to[i].post_title + '</li>');

                        jQuery('#_related_to').val(appointment_selected_related_to[i].id);
                    }
                    jQuery('#appointment_selected_related_to').show();
                }
                else
                {
                    jQuery('#appointment_selected_related_to').html('');
                    jQuery('#appointment_selected_related_to').hide();
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

        echo '
        <p class="form-field"><label for="_recurrence">' . __( 'Repeats', 'propertyhive' ) . '</label>
            <select id="_recurrence_type" name="_recurrence_type">
                <option value=""></option>
            </select>
        </p>';

        $recurrence_types = array(
            '' => __( 'Doesn\'t Repeat', 'propertyhive' ),
            'daily' => __( 'Daily', 'propertyhive' ),
            'weekly' => __( 'Weekly On', 'propertyhive' ) . ' %%weekday_name%%',
            'monthly' => __( 'Monthly On', 'propertyhive' ) . ' %%month_day%%',
            'annually' => __( 'Annually On', 'propertyhive' ) . ' %%annual_month_day%%',
        );

        echo '<script>

            var selected_recurrence_type = \'' . get_post_meta( $thepostid, '_recurrence_type', TRUE ) . '\';
            var recurrence_types = ' . json_encode($recurrence_types) . ';
            var days = [\'Sunday\', \'Monday\', \'Tuesday\', \'Wednesday\', \'Thursday\', \'Friday\', \'Saturday\'];
            var month_names = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

            function load_recurrence_types()
            {
                var current_option = jQuery(\'#_recurrence_type\').val();

                jQuery(\'#_recurrence_type\').empty();

                for ( var i in recurrence_types )
                {
                    var option_value = recurrence_types[i];

                    var d = new Date(jQuery(\'#_start_date\').val());
                    
                    // Replace %%weekday_name%%
                    option_value = option_value.replace("%%weekday_name%%", days[d.getDay()]);

                    // Replace %%month_day%%
                    var day = d.getDate();
                    var suffix = (day > 0 ? [\'th\', \'st\', \'nd\', \'rd\'][(day > 3 && day < 21) || day % 10 > 3 ? 0 : day % 10] : \'\');
                    option_value = option_value.replace("%%month_day%%", day + suffix);

                    // Replace %%annual_month_day%%
                    option_value = option_value.replace("%%annual_month_day%%", month_names[d.getMonth()] + \' \' + day + suffix );

                    jQuery("<option />", {
                        val: i,
                        text: option_value
                    }).appendTo( jQuery(\'#_recurrence_type\') );
                }

                jQuery(\'#_recurrence_type\').val(selected_recurrence_type);
            }

            jQuery(document).ready(function()
            {
                jQuery(\'#_start_date\').change(function()
                {
                    load_recurrence_types();
                });

                jQuery(\'input[name=\\\'_all_day\\\']\').change(function()
                {
                    if ( jQuery(this).prop(\'checked\') === true )
                    {
                        jQuery(\'#_start_time_hours\').hide();
                        jQuery(\'#_start_time_minutes\').hide();
                        jQuery(\'#start_time_separator\').hide();

                        jQuery(\'#_end_time_hours\').hide();
                        jQuery(\'#_end_time_minutes\').hide();
                        jQuery(\'#end_time_separator\').hide();
                    }
                    else
                    {
                        jQuery(\'#_start_time_hours\').show();
                        jQuery(\'#_start_time_minutes\').show();
                        jQuery(\'#start_time_separator\').show();

                        jQuery(\'#_end_time_hours\').show();
                        jQuery(\'#_end_time_minutes\').show();
                        jQuery(\'#end_time_separator\').show();
                    }
                });

                load_recurrence_types();
            });

        </script>';

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

        update_post_meta( $post_id, '_details', sanitize_textarea_field( $_POST['_details'] ) );

        $start_date_time = $_POST['_start_date'];
        if ( isset($_POST['_all_day']) && $_POST['_all_day'] == 'yes' )
        {
            $start_date_time .= ' 00:00:00';
        }
        else
        {
            $start_date_time .= ' ' . $_POST['_start_time_hours'] . ':' . $_POST['_start_time_minutes'] . ':00';
        }
        update_post_meta( $post_id, '_start_date_time', ph_clean( $start_date_time ) );

        $end_date_time = $_POST['_end_date'];
        if ( isset($_POST['_all_day']) && $_POST['_all_day'] == 'yes' )
        {
            $end_date_time .= ' 00:00:00';
        }
        else
        {
            $end_date_time .= ' ' . $_POST['_end_time_hours'] . ':' . $_POST['_end_time_minutes'] . ':00';
        }
        update_post_meta( $post_id, '_end_date_time', ph_clean( $end_date_time ) );

        update_post_meta( $post_id, '_all_day', (isset($_POST['_all_day']) && $_POST['_all_day'] == 'yes') ? 'yes' : '' );
        update_post_meta( $post_id, '_assigned_to', ( isset($_POST['_assigned_to']) && is_array($_POST['_assigned_to']) && !empty($_POST['_assigned_to']) ) ? ph_clean( $_POST['_assigned_to'] ) : '' );
        update_post_meta( $post_id, '_related_to', wp_strip_all_tags( $_POST['_related_to'] ) );

        if ( isset($_POST['_recurrence_type']) && $_POST['_recurrence_type'] != '' )
        {
            $sql = $wpdb->prepare(
                "DELETE FROM `" . $wpdb->prefix . "ph_calendar_recurrence` WHERE `post_id` = '%d'", 
                $post_id
            );
            $wpdb->query($sql);

            switch ( ph_clean($_POST['_recurrence_type']) )
            {
                case "daily":
                {
                    $sql = $wpdb->prepare(
                        "INSERT INTO `" . $wpdb->prefix . "ph_calendar_recurrence` (
                            `post_id`, `repeat_start`, `repeat_year`, `repeat_month`, `repeat_day`, `repeat_week`, `repeat_weekday`
                        ) 
                        values (
                            %d, %d, %s, %s, %s, %s, %s
                        )", 
                        $post_id, 
                        strtotime(ph_clean($start_date_time)), 
                        '*', 
                        '*', 
                        '*',
                        '*', 
                        '*'
                    );
                    $wpdb->query($sql);

                    break;
                }
                case "weekly":
                {
                    $sql = $wpdb->prepare(
                        "INSERT INTO `" . $wpdb->prefix . "ph_calendar_recurrence` (
                            `post_id`, `repeat_start`, `repeat_year`, `repeat_month`, `repeat_day`, `repeat_week`, `repeat_weekday`
                        ) 
                        values (
                            %d, %d, %s, %s, %s, %s, %s
                        )", 
                        $post_id, 
                        strtotime(ph_clean($start_date_time)), 
                        '*', 
                        '*', 
                        '*',
                        '*', 
                        date("N", strtotime($start_date_time))
                    );
                    $wpdb->query($sql);

                    break;
                }
                case "monthly":
                {
                    $sql = $wpdb->prepare(
                        "INSERT INTO `" . $wpdb->prefix . "ph_calendar_recurrence` (
                            `post_id`, `repeat_start`, `repeat_year`, `repeat_month`, `repeat_day`, `repeat_week`, `repeat_weekday`
                        ) 
                        values (
                            %d, %d, %s, %s, %s, %s, %s
                        )", 
                        $post_id, 
                        strtotime(ph_clean($start_date_time)), 
                        '*', 
                        '*', 
                        date("d", strtotime($start_date_time)),
                        '*', 
                        '*'
                    );
                    $wpdb->query($sql);

                    break;
                }
                case "annually":
                {
                    $sql = $wpdb->prepare(
                        "INSERT INTO `" . $wpdb->prefix . "ph_calendar_recurrence` (
                            `post_id`, `repeat_start`, `repeat_year`, `repeat_month`, `repeat_day`, `repeat_week`, `repeat_weekday`
                        ) 
                        values (
                            %d, %d, %s, %s, %s, %s, %s
                        )", 
                        $post_id, 
                        strtotime(ph_clean($start_date_time)), 
                        '*', 
                        date("m", strtotime($start_date_time)), 
                        date("d", strtotime($start_date_time)),
                        '*', 
                        '*'
                    );
                    $wpdb->query($sql);

                    break;
                }
                default:
                {
                    // Unknown recurrence type
                }
            }
        }
        update_post_meta( $post_id, '_recurrence_type', ph_clean($_POST['_recurrence_type']) );
    }
}
