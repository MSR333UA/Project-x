<?php
/**
 * Property Agent
 *
 * @author      PropertyHive
 * @category    Admin
 * @package     PropertyHive/Admin/Meta Boxes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Property_Agent
 */
class PH_Meta_Box_Property_Agent {

    /**
     * Output the metabox
     */
    public static function output( $post ) {
        global $post, $wpdb, $thepostid;

        $thepostid = $post->ID;

        $original_post = $post;
        $original_thepostid = $thepostid;
        
        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';

        $options = array();

        $args = array(
            'post_type' => 'agent',
            'nopaging' => true,
            'orderby' => 'title',
            'order' => 'ASC'
        );
        $agent_query = new WP_Query( $args );
        if ( $agent_query->have_posts() )
        {
            while ( $agent_query->have_posts() )
            {
                $agent_query->the_post();

                $agent_id = $post->ID;
                $agent_name = get_the_title();

                $options[$agent_name] = array();

                $agent_branches = get_post_meta($agent_id, '_branches', true);

                if ( !empty($agent_branches) && is_array($agent_branches) )
                {
                    foreach( $agent_branches as $branch_id => $branch)
                    {
                        $options[$agent_name][$agent_id . '|' . $branch_id] = $branch['name'];
                    }
                }
                else
                {
                    $options[$agent_name][$agent_id.'|'] = __( 'No branches exist for this agent. Please add one', 'propertyhive' );
                }
            }
        }
        else
        {
            $options[__( 'No agents exist', 'propertyhive' )] = array();
        }
        wp_reset_postdata();

        $args = array( 
            'id' => '_agent_branch_id', 
            'label' => __( 'Agent', 'propertyhive' ), 
            'desc_tip' => false,
            'desc' => '',
            'options' => $options
        );
        $selected_agent_id = get_post_meta( $thepostid, '_agent_id', TRUE );
        $selected_branch_id = get_post_meta( $thepostid, '_branch_id', TRUE );
        if ( $selected_agent_id != '')
        {
            $args['value'] = $selected_agent_id . '|' . $selected_branch_id;
        }
        propertyhive_wp_select_optgroups( $args );

        do_action('propertyhive_agent_details_fields');
        
        echo '</div>';
        
        echo '</div>';
        
        $post = $original_post;
    }

    /**
     * Save meta box data
     */
    public static function save( $post_id, $post ) {
        global $wpdb;
        
        if ( isset($_POST['_agent_branch_id']) && $_POST['_agent_branch_id'] != '' )
        {
            $explode_agent_branch_id = explode("|", $_POST['_agent_branch_id']);

            update_post_meta( $post_id, '_agent_id', $explode_agent_branch_id[0] );
            update_post_meta( $post_id, '_branch_id', $explode_agent_branch_id[1] );
        }
        else
        {
            update_post_meta( $post_id, '_agent_id', '' );
            update_post_meta( $post_id, '_branch_id', '' );
        }
    }

}
