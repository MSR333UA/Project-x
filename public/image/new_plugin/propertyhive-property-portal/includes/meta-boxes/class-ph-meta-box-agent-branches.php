<?php
/**
 * Agent Branches
 *
 * @author      PropertyHive
 * @category    Admin
 * @package     PropertyHive/Admin/Meta Boxes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Agent_Branches
 */
class PH_Meta_Box_Agent_Branches {

    /**
     * Output the metabox
     */
    public static function output( $post ) {
        global $post, $wpdb, $thepostid;
        
        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';

            echo '<div id="agent_branches">';
                
                $args = array(
                    'post_type' => 'branch',
                    'nopaging' => true,
                    'orderby' => 'menu_order',
                    'order' => 'ASC',
                    'meta_query' => array(
                        array(
                            'key' => '_agent_id',
                            'value' => $thepostid
                        )
                    )
                );
                $branches_query = new WP_Query( $args );

                if ($branches_query->have_posts())
                {
                    while ($branches_query->have_posts())
                    {
                        $branches_query->the_post();

                        // Don't show delete button if properties assigned to this branch
                        $properties_assigned = false;
                        $args = array(
                            'post_type' => 'property',
                            'posts_per_page' => 1,
                            'meta_query' => array(
                                array(
                                    'key' => '_branch_id',
                                    'value' => $post->ID
                                )
                            )
                        );
                        $properties_query = new WP_Query( $args );
                        if ( $properties_query->have_posts() )
                        {
                            $properties_assigned = true;
                        }
                        wp_reset_postdata();

                        echo '<div class="branch">';
                    
                            echo '<h3>
                                <button type="button" class="remove_branch button ' . ( $properties_assigned ? 'assigned' : 'not_assigned' ) . '">' . __( 'Delete', 'propertyhive' ) . '</button>
                                <div class="handlediv" title="' . __( 'Click to toggle', 'propertyhive' ) . '"></div>
                                <strong>' . get_the_title() . '</strong>
                            </h3>';
                            
                            echo '<div class="branch-details">';
                                
                                propertyhive_wp_text_input( array( 
                                    'id' => '',
                                    'name' => '_branch_name[existing_' . $post->ID . ']', 
                                    'label' => __( 'Branch Name', 'propertyhive' ), 
                                    'desc_tip' => false,
                                    'value' => get_the_title(),
                                    'placeholder' => __( 'e.g. London Office', 'propertyhive' ), 
                                    'type' => 'text'
                                ) );

                                propertyhive_wp_text_input( array( 
                                    'id' => '',
                                    'name' => '_branch_address_name_number[existing_' . $post->ID . ']', 
                                    'label' => __( 'Building Name / Number', 'propertyhive' ), 
                                    'desc_tip' => false, 
                                    'value' => get_post_meta( $post->ID, '_address_name_number', true ),
                                    'placeholder' => __( 'e.g. Thistle Cottage, or Flat 10', 'propertyhive' ), 
                                    'type' => 'text'
                                ) );
                                
                                propertyhive_wp_text_input( array( 
                                    'id' => '_branch_address_street[existing_' . $post->ID . ']', 
                                    'label' => __( 'Street', 'propertyhive' ), 
                                    'desc_tip' => false, 
                                    'value' => get_post_meta( $post->ID, '_address_street', true ),
                                    'placeholder' => __( 'e.g. High Street', 'propertyhive' ), 
                                    'type' => 'text'
                                ) );
                                
                                propertyhive_wp_text_input( array( 
                                    'id' => '_branch_address_two[existing_' . $post->ID . ']', 
                                    'label' => __( 'Address Line 2', 'propertyhive' ), 
                                    'desc_tip' => false, 
                                    'value' => get_post_meta( $post->ID, '_address_two', true ),
                                    'type' => 'text'
                                ) );
                                
                                propertyhive_wp_text_input( array( 
                                    'id' => '_branch_address_three[existing_' . $post->ID . ']', 
                                    'label' => __( 'Town / City', 'propertyhive' ), 
                                    'desc_tip' => false, 
                                    'value' => get_post_meta( $post->ID, '_address_three', true ),
                                    'type' => 'text'
                                ) );
                                
                                propertyhive_wp_text_input( array( 
                                    'id' => '_branch_address_four[existing_' . $post->ID . ']', 
                                    'label' => __( 'County / State', 'propertyhive' ), 
                                    'desc_tip' => false, 
                                    'value' => get_post_meta( $post->ID, '_address_four', true ),
                                    'type' => 'text'
                                ) );
                                
                                propertyhive_wp_text_input( array( 
                                    'id' => '_branch_address_postcode[existing_' . $post->ID . ']', 
                                    'label' => __( 'Postcode / Zip Code', 'propertyhive' ), 
                                    'desc_tip' => false, 
                                    'value' => get_post_meta( $post->ID, '_address_postcode', true ),
                                    'type' => 'text'
                                ) );

                                if ( get_option( 'propertyhive_active_departments_sales' ) == 'yes' )
                                {
                                    propertyhive_wp_text_input( array( 
                                        'id' => '_branch_telephone_number_sales[existing_' . $post->ID . ']', 
                                        'label' => __( 'Telephone (Sales)', 'propertyhive' ), 
                                        'desc_tip' => false, 
                                        'value' => get_post_meta( $post->ID, '_telephone_number_sales', true ),
                                        'type' => 'text'
                                    ) );

                                    propertyhive_wp_text_input( array( 
                                        'id' => '_branch_email_address_sales[existing_' . $post->ID . ']', 
                                        'label' => __( 'Email Address (Sales)', 'propertyhive' ), 
                                        'desc_tip' => false, 
                                        'value' => get_post_meta( $post->ID, '_email_address_sales', true ),
                                        'type' => 'text'
                                    ) );
                                }

                                if ( get_option( 'propertyhive_active_departments_lettings' ) == 'yes' )
                                {
                                    propertyhive_wp_text_input( array( 
                                        'id' => '_branch_telephone_number_lettings[existing_' . $post->ID . ']', 
                                        'label' => __( 'Telephone (Lettings)', 'propertyhive' ), 
                                        'desc_tip' => false, 
                                        'value' => get_post_meta( $post->ID, '_telephone_number_lettings', true ),
                                        'type' => 'text'
                                    ) );

                                    propertyhive_wp_text_input( array( 
                                        'id' => '_branch_email_address_lettings[existing_' . $post->ID . ']', 
                                        'label' => __( 'Email Address (Lettings)', 'propertyhive' ), 
                                        'desc_tip' => false, 
                                        'value' => get_post_meta( $post->ID, '_email_address_lettings', true ),
                                        'type' => 'text'
                                    ) );
                                }

                                if ( get_option( 'propertyhive_active_departments_commercial' ) == 'yes' )
                                {
                                    propertyhive_wp_text_input( array( 
                                        'id' => '_branch_telephone_number_commercial[existing_' . $post->ID . ']', 
                                        'label' => __( 'Telephone (Commercial)', 'propertyhive' ), 
                                        'desc_tip' => false, 
                                        'value' => get_post_meta( $post->ID, '_telephone_number_commercial', true ),
                                        'type' => 'text'
                                    ) );

                                    propertyhive_wp_text_input( array( 
                                        'id' => '_branch_email_address_commercial[existing_' . $post->ID . ']', 
                                        'label' => __( 'Email Address (Commercial)', 'propertyhive' ), 
                                        'desc_tip' => false, 
                                        'value' => get_post_meta( $post->ID, '_email_address_commercial', true ),
                                        'type' => 'text'
                                    ) );
                                }

                                do_action('propertyhive_agent_branch_existing_fields', $post->ID);
                            
                            echo '</div>';
                        
                        echo '</div>';
                    } 
                }
                wp_reset_postdata();
                
                echo '</div>';
                
                echo '<div id="agent_branch_template" style="display:none">';
                echo '<div class="branch">';
                
                    echo '<h3>
                        <button type="button" class="remove_branch button not_assigned">' . __( 'Delete', 'propertyhive' ) . '</button>
                        <div class="handlediv" title="' . __( 'Click to toggle', 'propertyhive' ) . '"></div>
                        <strong>New Branch</strong>
                    </h3>';
                    
                    echo '<div class="branch-details">';
                    
                        propertyhive_wp_text_input( array( 
                            'id' => '',
                            'name' => '_branch_name[]', 
                            'label' => __( 'Branch Name', 'propertyhive' ), 
                            'desc_tip' => false,
                            'value' => '',
                            'placeholder' => __( 'e.g. London Office', 'propertyhive' ), 
                            'type' => 'text'
                        ) );

                        propertyhive_wp_text_input( array( 
                            'id' => '',
                            'name' => '_branch_address_name_number[]', 
                            'label' => __( 'Building Name / Number', 'propertyhive' ), 
                            'desc_tip' => false, 
                            'value' => '',
                            'placeholder' => __( 'e.g. Thistle Cottage, or Flat 10', 'propertyhive' ), 
                            'type' => 'text'
                        ) );
                        
                        propertyhive_wp_text_input( array( 
                            'id' => '_branch_address_street[]', 
                            'label' => __( 'Street', 'propertyhive' ), 
                            'desc_tip' => false, 
                            'value' => '',
                            'placeholder' => __( 'e.g. High Street', 'propertyhive' ), 
                            'type' => 'text'
                        ) );
                        
                        propertyhive_wp_text_input( array( 
                            'id' => '_branch_address_two[]', 
                            'label' => __( 'Address Line 2', 'propertyhive' ), 
                            'desc_tip' => false, 
                            'value' => '',
                            'type' => 'text'
                        ) );
                        
                        propertyhive_wp_text_input( array( 
                            'id' => '_branch_address_three[]', 
                            'label' => __( 'Town / City', 'propertyhive' ), 
                            'desc_tip' => false, 
                            'value' => '',
                            'type' => 'text'
                        ) );
                        
                        propertyhive_wp_text_input( array( 
                            'id' => '_branch_address_four[]', 
                            'label' => __( 'County / State', 'propertyhive' ), 
                            'desc_tip' => false, 
                            'value' => '',
                            'type' => 'text'
                        ) );
                        
                        propertyhive_wp_text_input( array( 
                            'id' => '_branch_address_postcode[]', 
                            'label' => __( 'Postcode / Zip Code', 'propertyhive' ), 
                            'desc_tip' => false, 
                            'value' => '',
                            'type' => 'text'
                        ) );

                        if ( get_option( 'propertyhive_active_departments_sales' ) == 'yes' )
                        {
                            propertyhive_wp_text_input( array( 
                                'id' => '_branch_telephone_number_sales[]', 
                                'label' => __( 'Telephone (Sales)', 'propertyhive' ), 
                                'desc_tip' => false, 
                                'value' => '',
                                'type' => 'text'
                            ) );

                            propertyhive_wp_text_input( array( 
                                'id' => '_branch_email_address_sales[]', 
                                'label' => __( 'Email Address (Sales)', 'propertyhive' ), 
                                'desc_tip' => false, 
                                'value' => '',
                                'type' => 'email'
                            ) );
                        }

                        if ( get_option( 'propertyhive_active_departments_lettings' ) == 'yes' )
                        {
                            propertyhive_wp_text_input( array( 
                                'id' => '_branch_telephone_number_lettings[]', 
                                'label' => __( 'Telephone (Lettings)', 'propertyhive' ), 
                                'desc_tip' => false, 
                                'value' => '',
                                'type' => 'text'
                            ) );

                            propertyhive_wp_text_input( array( 
                                'id' => '_branch_email_address_lettings[]', 
                                'label' => __( 'Email Address (Lettings)', 'propertyhive' ), 
                                'desc_tip' => false, 
                                'value' => '',
                                'type' => 'email'
                            ) );
                        }

                        if ( get_option( 'propertyhive_active_departments_commercial' ) == 'yes' )
                        {
                            propertyhive_wp_text_input( array( 
                                'id' => '_branch_telephone_number_commercial[]', 
                                'label' => __( 'Telephone (Commercial)', 'propertyhive' ), 
                                'desc_tip' => false, 
                                'value' => '',
                                'type' => 'text'
                            ) );

                            propertyhive_wp_text_input( array( 
                                'id' => '_branch_email_address_commercial[]', 
                                'label' => __( 'Email Address (Commercial)', 'propertyhive' ), 
                                'desc_tip' => false, 
                                'value' => '',
                                'type' => 'email'
                            ) );
                        }

                        do_action('propertyhive_agent_branch_template_fields');
                    
                    echo '</div>';
                
                echo '</div>';
                echo '</div>';
                
                echo '<p class="form-field">
                    <label for="">&nbsp;</label>
                    <a href="#" class="button button-primary add_agent_branch"><span class="fa fa-plus"></span> Add Branch</a>
                </p>';

            do_action('propertyhive_agent_branch_fields');
        
            echo '</div>';
        
        echo '</div>';

        echo '<script>
            
            jQuery(document).ready(function()
            {
                jQuery(\'#agent_branches\').on(\'keyup\', \'input[name=\\\'_branch_name[]\\\']\', function()
                {
                    var branch_name = jQuery(this).val();
                    if (branch_name == \'\')
                    {
                        branch_name = \'(' . __('untitled', 'propertyhive') . ')\';
                    }
                    jQuery(this).parent().parent().parent().children(\'h3\').children(\'strong\').html(branch_name);
                });
                
                jQuery(\'.add_agent_branch\').click(function()
                {
                    var agent_branch_template = jQuery(\'#agent_branch_template\').html();
                    
                    jQuery(\'#agent_branches\').append(agent_branch_template);
                    
                    return false;
                });
                
                jQuery(document).on(\'click\', \'.remove_branch.not_assigned\', function()
                {
                    jQuery(this).parent().parent().fadeOut(\'slow\', function()
                    {
                        jQuery(this).remove();
                    });
                    
                    return false;
                });

                jQuery(document).on(\'click\', \'.remove_branch.assigned\', function()
                {
                    alert(\'There are properties assigned to this branch. Please remove or reassign these properties to another branch before deleting it\');
                    
                    return false;
                });
            });
            
        </script>';
        
    }

    /**
     * Save meta box data
     */
    public static function save( $post_id, $post ) {
        global $wpdb;
        
        $existing_branch_ids = array();
        $args = array(
            'post_type' => 'branch',
            'nopaging' => true,
            'orderby' => 'menu_order',
            'order' => 'ASC',
            'meta_query' => array(
                array(
                    'key' => '_agent_id',
                    'value' => $post_id
                )
            )
        );
        $branches_query = new WP_Query( $args );

        if ($branches_query->have_posts())
        {
            while ($branches_query->have_posts())
            {
                $branches_query->the_post();

                $existing_branch_ids[] = get_the_ID();
            }
        }
        wp_reset_postdata();

        $new_num_agent_branches = count($_POST['_branch_name']) - 1; // Minus one because of the template room. Don't want to include this

        $i = 0;
        $processed_branch_ids = array();
        foreach ($_POST['_branch_name'] as $key => $value)
        {
            if ( $i >= $new_num_agent_branches )
            {
                break;
            }

            $existing = FALSE;
            if ( strpos($key, 'existing_') !== FALSE )
            {
                $existing = str_replace('existing_', '', $key);
            }

            if ($existing === FALSE)
            {
                // Insert new branch
                $postdata = array(
                    'post_title'     => wp_strip_all_tags( ($_POST['_branch_name'][$key] != '') ? $_POST['_branch_name'][$key] : 'Untitled Branch' ),
                    'post_status'    => 'publish',
                    'post_type'      => 'branch',
                    'menu_order'     => $i,
                    'comment_status' => 'closed',
                );

                $branch_id = wp_insert_post( $postdata, true );
            }
            else
            {
                // Update existing branch
                $branch_id = $existing;

                $postdata = array(
                    'ID'             => $branch_id,
                    'post_title'     => wp_strip_all_tags( ($_POST['_branch_name'][$key] != '') ? $_POST['_branch_name'][$key] : 'Untitled Branch' ),
                    'post_status'    => 'publish',
                    'menu_order'     => $i,
                );

                // Update the post into the database
                wp_update_post( $postdata );
            }

            $processed_branch_ids[] = $branch_id;

            update_post_meta( $branch_id, '_agent_id', $post_id );

            update_post_meta( $branch_id, '_address_name_number', $_POST['_branch_address_name_number'][$key] );
            update_post_meta( $branch_id, '_address_street', $_POST['_branch_address_street'][$key] );
            update_post_meta( $branch_id, '_address_two', $_POST['_branch_address_two'][$key] );
            update_post_meta( $branch_id, '_address_three', $_POST['_branch_address_three'][$key] );
            update_post_meta( $branch_id, '_address_four', $_POST['_branch_address_four'][$key] );
            update_post_meta( $branch_id, '_address_postcode', $_POST['_branch_address_postcode'][$key] );

            if ( get_option( 'propertyhive_active_departments_sales' ) == 'yes' )
            {
                update_post_meta( $branch_id, '_telephone_number_sales', $_POST['_branch_telephone_number_sales'][$key] );
                update_post_meta( $branch_id, '_email_address_sales', $_POST['_branch_email_address_sales'][$key] );
            }
            else
            {
                update_post_meta( $branch_id, '_telephone_number_sales', '' );
                update_post_meta( $branch_id, '_email_address_sales', '' );
            }
            if ( get_option( 'propertyhive_active_departments_lettings' ) == 'yes' )
            {
                update_post_meta( $branch_id, '_telephone_number_lettings', $_POST['_branch_telephone_number_lettings'][$key] );
                update_post_meta( $branch_id, '_email_address_lettings', $_POST['_branch_email_address_lettings'][$key] );
            }
            else
            {
                update_post_meta( $branch_id, '_telephone_number_lettings', '' );
                update_post_meta( $branch_id, '_email_address_lettings', '' );
            }
            if ( get_option( 'propertyhive_active_departments_commercial' ) == 'yes' )
            {
                update_post_meta( $branch_id, '_telephone_number_commercial', $_POST['_branch_telephone_number_commercial'][$key] );
                update_post_meta( $branch_id, '_email_address_commercial', $_POST['_branch_email_address_commercial'][$key] );
            }
            else
            {
                update_post_meta( $branch_id, '_telephone_number_commercial', '' );
                update_post_meta( $branch_id, '_email_address_commercial', '' );
            }

            ++$i;
        }

        // Remove deleted branches#
        foreach ($existing_branch_ids as $existing_branch_id)
        {
            if (!in_array($existing_branch_id, $processed_branch_ids))
            {
                // This branchID wasn't processed. Assume it's been removed
                wp_trash_post( $existing_branch_id );
            }
        }

        do_action('propertyhive_save_agent_branches');
    }

}
