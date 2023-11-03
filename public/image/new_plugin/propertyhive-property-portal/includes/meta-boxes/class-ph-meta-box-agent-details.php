<?php
/**
 * Agent Details
 *
 * @author      PropertyHive
 * @category    Admin
 * @package     PropertyHive/Admin/Meta Boxes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Agent_Details
 */
class PH_Meta_Box_Agent_Details {

    /**
     * Output the metabox
     */
    public static function output( $post ) {
        global $post, $wpdb, $thepostid;
        
        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';

        wp_nonce_field( 'propertyhive_save_data', 'propertyhive_meta_nonce' );
        
        propertyhive_wp_photo_upload( array( 
            'id' => '_logo', 
            'label' => __( 'Logo', 'propertyhive' ), 
            'button_label' => __( 'Select Logo', 'propertyhive' ), 
            'desc_tip' => false,
        ) );

        do_action('propertyhive_agent_details_fields');
        
        echo '</div>';
        
        echo '</div>';
        
    }

    /**
     * Save meta box data
     */
    public static function save( $post_id, $post ) {
        global $wpdb;
        
        update_post_meta( $post_id, '_logo', $_POST['_logo'] );

        do_action( 'propertyhive_save_agent_details', $post_id );
    }

}
