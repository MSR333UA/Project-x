<?php
/**
 * Agent Actions
 *
 * @author      PropertyHive
 * @category    Admin
 * @package     PropertyHive/Admin/Meta Boxes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Agent_Actions
 */
class PH_Meta_Box_Agent_Actions {

    /**
     * Output the metabox
     */
    public static function output( $post ) {
        global $post, $wpdb, $thepostid;
        
        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';

        $original_post = $post;

        $actions = array();

        $user_id = get_post_meta( $post->ID, '_user_id', TRUE );
        if ( $user_id != '' )
        {
            // Has a user associated
            $actions[] = '<a 
                        href="' . get_edit_user_link( $user_id ) . '&wp_http_referer=' . urlencode(get_edit_post_link($post->ID)) . '" 
                        class="button"
                        style="width:100%; margin-bottom:7px; text-align:center" 
                    >' . __('View User / Change Password', 'propertyhive') . '</a>';
        }
        else
        {
            $actions[] = '<a 
                        href="#action_panel_create_login" 
                        class="button agent-action"
                        style="width:100%; margin-bottom:7px; text-align:center" 
                    >' . __('Create Agent Login', 'propertyhive') . '</a>';

            // get email address of first branch
            $email_address = '';

            $args = array(
                'post_type' => 'branch',
                'nopaging' => true,
                'posts_per_page' => 1,
                'orderby' => 'menu_order',
                'order' => 'ASC',
                'meta_query' => array(
                    array(
                        'key' => '_agent_id',
                        'value' => $post->ID
                    )
                )
            );

            $branches_query = new WP_Query( $args );

            if ($branches_query->have_posts())
            {
                while ($branches_query->have_posts())
                {
                    $branches_query->the_post();

                    $email_address = get_post_meta( get_the_ID(), '_email_address_sales', true );
                    if ( $email_address == '' )
                    {
                        $email_address = get_post_meta( get_the_ID(), '_email_address_lettings', true );
                    }
                    if ( $email_address == '' )
                    {
                        $email_address = get_post_meta( get_the_ID(), '_email_address_commercial', true );
                    }
                }
            }
            wp_reset_postdata();

            $post = $original_post;

            // Create user action panel
            echo '<div id="action_panel_create_login" class="propertyhive_meta_box propertyhive_meta_box_actions" style="display:none;">
                         
                <div class="options_group">

                    <div class="form-field">

                        <label for="_email_address">' . __( 'Email Address', 'propertyhive' ) . '</label>
                        
                        <input type="email" id="_email_address" name="_email_address" style="width:100%; margin-bottom:10px;" value="' . $email_address . '">

                        <label for="_password">' . __( 'Password', 'propertyhive' ) . '</label>
                        
                        <input type="text" id="_password" name="_password" style="width:100%;" value="' . esc_attr( wp_generate_password( 16 ) ) . '">
                        
                    </div>

                    <a class="button action-cancel" href="#">' . __( 'Cancel', 'propertyhive' ) . '</a>
                    <a class="button button-primary login-action-submit" href="#">' . __( 'Create Login', 'propertyhive' ) . '</a>

                </div>

            </div>';
        }

        // Success action panel
        echo '<div id="action_panel_success" class="propertyhive_meta_box propertyhive_meta_box_actions" style="display:none;">
                     
            <div class="options_group" style="padding-top:8px;">

                <div id="success_actions"></div>

                <!--<a class="button action-cancel" style="width:100%;" href="#">' . __( 'Back To Actions', 'propertyhive' ) . '</a>-->

            </div>

        </div>';

        echo '<div class="propertyhive_meta_box" id="propertyhive_agent_actions_meta_box">';
            
            echo '<div class="options_group" style="padding-top:8px;">';

                $actions = apply_filters( 'propertyhive_admin_agent_actions', $actions, $post->ID );

                if ( !empty($actions) )
                {
                    echo implode("", $actions);
                }
                else
                {
                    echo '<div style="text-align:center">' . __( 'No actions to display', 'propertyhive' ) . '</div>';
                }

            echo '</div>';

        echo '</div>';
        
        echo '</div>';
        
        echo '</div>';
?>
<script>

jQuery(document).ready(function($)
{
    $('a.agent-action').click(function(e)
    {
        e.preventDefault();

        var this_href = $(this).attr('href');

        $('#propertyhive_agent_actions_meta_box').stop().fadeOut(300, function()
        {
            $(this_href).stop().fadeIn(300, function()
            {
                //$('input#viewing_property_search').focus();
            });
        });
    });

    $('a.action-cancel').click(function(e)
    {
        e.preventDefault();

        $('.propertyhive_meta_box_actions').stop().fadeOut(300, function()
        {
            $('#propertyhive_agent_actions_meta_box').stop().fadeIn(300, function()
            {

            });
        });
    });

    // User / Login evebts
    $('a.login-action-submit').click(function(e)
    {
        e.preventDefault();

        // Validation
        if ($('#_email_address').val() == '')
        {
            $('#_email_address').focus();
            $('#_email_address').css('transition', 'background 0.6s');
            $('#_email_address').css('background', '#ff9999');
            setTimeout(function() { $('#_email_address').css('background', '#FFF'); }, 1000);
            return false;
        }

        if ($('#_password').val() == '')
        {
            $('#_password').focus();
            $('#_password').css('transition', 'background 0.6s');
            $('#_password').css('background', '#ff9999');
            setTimeout(function() { $('#_password').css('background', '#FFF'); }, 1000);
            return false;
        }

        $(this).attr('disabled', 'disabled');
        $(this).text('Saving...');

        // Validation passed. Submit form
        var data = {
            action:         'propertyhive_create_agent_login',
            agent_id:       <?php echo $post->ID; ?>,
            email_address:  $('#_email_address').val(),
            password:       $('#_password').val(),
            security:       '<?php echo wp_create_nonce( 'create-login' ); ?>',
        };

        var that = this;
        $.post( '<?php echo admin_url('admin-ajax.php'); ?>', data, function(response) 
        {
            if (response.error)
            {
                alert(response.error);
            }
            if (response.success)
            {
                $('#success_actions').html('');

                $('#success_actions').append('<strong>User login created successfully.</strong><br>This agent can now login using their email address and password.<br><br>');

                $('#action_panel_create_login').stop().fadeOut(300, function()
                {
                    $('#action_panel_success').stop().fadeIn(300);
                });

                $('a[href=\'#action_panel_create_login\']').hide();
            }

            $(that).attr('disabled', false);
            $(that).text('Create Login');
        });
    });
});

</script>
<?php
    }

}
