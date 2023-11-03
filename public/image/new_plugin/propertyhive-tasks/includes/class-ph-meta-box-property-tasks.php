<?php
/**
 * Property Tasks
 *
 * @author 		PropertyHive
 * @category 	Admin
 * @package 	PropertyHive/Admin/Meta Boxes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Property_Tasks
 */
class PH_Meta_Box_Property_Tasks {

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

        echo '<div id="propertyhive_tasks_grid" data-related-to="property|' . $thepostid . '" class="tasks-grid">Loading Tasks...</div>';
        
        echo '<br><a href="" class="button add-new-task">' . __( 'Create Task', 'propertyhive' ) . '</a>';

        echo '<div id="create_task_form" class="create-task-form">';

            $args = array( 
                'id' => '_task_title', 
                'label' => __( 'Task Title', 'propertyhive' ), 
                'desc_tip' => false, 
                'type' => 'text'
            );
            propertyhive_wp_text_input( $args );

            $args = array( 
                'id' => '_task_details', 
                'label' => __( 'Details', 'propertyhive' ), 
                'desc_tip' => false, 
            );
            propertyhive_wp_textarea_input( $args );

            $args = array( 
                'id' => '_task_due_date', 
                'label' => __( 'Due Date', 'propertyhive' ), 
                'desc_tip' => false, 
                'description' => __( 'Leave blank if no due date', 'propertyhive' ),
                'type' => 'date',
                'class' => 'date-picker',
            );
            propertyhive_wp_text_input( $args );

            echo '
            <p class="form-field"><label for="_task_assigned_to">' . __( 'Assigned To', 'propertyhive' ) . '</label>
            <select id="_task_assigned_to" name="_task_assigned_to[]" multiple="multiple" data-placeholder="' . __( 'Everyone', 'propertyhive' ) . '" class="multiselect attribute_values">';

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
                    if ( $user->ID == get_current_user_id() )
                    {
                        echo ' selected';
                    }
                    echo '>' . $user->display_name . '</option>';
                }
            }

            echo '</select>
            </p>';

            echo '<input type="hidden" name="_task_related_to" id="_task_related_to" value="property|' . $thepostid . '">';
            echo '<input type="hidden" name="_task_id" id="_task_id" value="">';

            echo '<a href="" class="button" id="create_task_form_cancel">Cancel</a> <a href="" class="button button-primary" id="create_task_form_submit">Save Task</a> <a href="" class="delete-task">Delete</a>';

        echo '</div>';

        echo '</div>';

        echo '</div>';
        
        $post = $original_post;
        $thepostid = $original_thepostid;
        setup_postdata($post);
    }
}
