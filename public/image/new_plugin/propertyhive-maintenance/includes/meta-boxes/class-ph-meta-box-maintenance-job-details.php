<?php
/**
 * Maintenance Job Details
 *
 * @author 		PropertyHive
 * @category 	Admin
 * @package 	PropertyHive/Admin/Meta Boxes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Maintenance_Job_Details
 */
class PH_Meta_Box_Maintenance_Job_Details {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        global $post, $wpdb, $thepostid;
        
        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';

        wp_nonce_field( 'propertyhive_save_data', 'propertyhive_meta_nonce' );
        
        $options = array(
            'pending' => __( 'Pending', 'propertyhive' ),
            'completed' => __( 'Completed', 'propertyhive' ),
            'paid' => __( 'Paid', 'propertyhive' ),
            'cancelled' => __( 'Cancelled', 'propertyhive' ),
        );

        $args = array( 
            'id' => '_status', 
            'label' => __( 'Maintenance Job Status', 'propertyhive' ), 
            'desc_tip' => false, 
            'options' => $options
        );
        propertyhive_wp_select( $args );

        $args = array( 
            'id' => '_previous_status',  
            'value' => get_post_meta( $post->ID, '_status', TRUE )
        );
        propertyhive_wp_hidden_input( $args );

        propertyhive_wp_text_input( array( 
            'id' => '_cost', 
            'label' => __( 'Quote / Cost', 'propertyhive' ), 
            'desc_tip' => false,
            'class' => 'short',
        ) );

        propertyhive_wp_checkbox( array( 
            'id' => '_cost_approved', 
            'label' => __( 'Cost Approved', 'propertyhive' ), 
            'desc_tip' => false,
        ) );

        $status = get_post_meta( $post->ID, '_status', TRUE );
        propertyhive_wp_text_input( array( 
            'id' => '_date_carried_out', 
            'label' => ( ( $status == '' || $status == 'pending' || $status == 'cancelled' ) ? __( 'Work To Be Carried Out', 'propertyhive' ) : __( 'Date Work Carried Out', 'propertyhive' ) ), 
            'desc_tip' => false,
            'class' => 'short date-picker',
            'placeholder' => 'YYYY-MM-DD',
            'custom_attributes' => array(
                'maxlength' => 10,
                'pattern' => "[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])"
            )
        ) );

        propertyhive_wp_textarea_input( array( 
            'id' => '_details', 
            'label' => __( 'Details', 'propertyhive' ), 
            'desc_tip' => false,
            'class' => '',
        ) );

        do_action('propertyhive_maintenance_job_details_fields');
	    
        echo '</div>';
        
        echo '</div>';
        
    }

    /**
     * Save meta box data
     */
    public static function save( $post_id, $post ) {
        global $wpdb;
        
        update_post_meta( $post_id, '_status', ph_clean($_POST['_status']) );

        $cost = preg_replace("/[^0-9]/", '', ph_clean($_POST['_cost']));
        update_post_meta( $post_id, '_cost', $cost );

        update_post_meta( $post_id, '_cost_approved', ph_clean($_POST['_cost_approved']) );
        update_post_meta( $post_id, '_date_carried_out', ph_clean($_POST['_date_carried_out']) );
        update_post_meta( $post_id, '_details', sanitize_textarea_field($_POST['_details']) );

        if ( $_POST['_previous_status'] != '' && $_POST['_previous_status'] != $_POST['_status'] )
        {
            $current_user = wp_get_current_user();
            
            // Updated status. Add note
            $comment = array(
                'note_type' => 'action',
                'action' => 'maintenance_job_' . ph_clean($_POST['_status']),
            );

            $data = array(
                'comment_post_ID'      => $post_id,
                'comment_author'       => $current_user->display_name,
                'comment_author_email' => 'propertyhive@noreply.com',
                'comment_author_url'   => '',
                'comment_date'         => date("Y-m-d H:i:s"),
                'comment_content'      => serialize($comment),
                'comment_approved'     => 1,
                'comment_type'         => 'propertyhive_note',
            );
            wp_insert_comment( $data );
        }
    }

}
