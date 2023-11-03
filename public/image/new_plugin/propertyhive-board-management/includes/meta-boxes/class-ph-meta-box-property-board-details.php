<?php
/**
 * Property Board Details
 *
 * @author 		PropertyHive
 * @category 	Admin
 * @package 	PropertyHive/Admin/Meta Boxes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Property_Board_Details
 */
class PH_Meta_Box_Property_Board_Details {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        global $post, $wpdb, $thepostid;
        
        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';
        
        $options = array('' => '');
        $args = array(
            'hide_empty' => false,
            'parent' => 0
        );
        $terms = get_terms( 'board_status', $args );

        $selected_value = '';
        if ( !empty( $terms ) && !is_wp_error( $terms ) )
        {
            foreach ($terms as $term)
            {
                $options[$term->term_id] = $term->name;
            }

            $term_list = wp_get_post_terms($thepostid, 'board_status', array("fields" => "ids"));
            if ( !is_wp_error($term_list) && is_array($term_list) && !empty($term_list) )
            {
                $selected_value = $term_list[0];
            }
        }

        $args = array( 
            'id' => '_board_status', 
            'label' => __( 'Board Status', 'propertyhive' ), 
            'desc_tip' => false, 
            'options' => $options
        );
        if ($selected_value != '')
        {
            $args['value'] = $selected_value;
        }
        //var_dump($args);
        propertyhive_wp_select( $args );

        // Available Date
        propertyhive_wp_text_input( array( 
            'id' => '_board_date_required', 
            'label' => __( 'Date Required', 'propertyhive' ), 
            'desc_tip' => false,
            'class' => 'short date-picker',
            'placeholder' => 'YYYY-MM-DD',
            'custom_attributes' => array(
                'maxlength' => 10,
                'pattern' => "[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])"
            )
        ) );

        propertyhive_wp_textarea_input( array( 
            'id' => '_board_notes', 
            'label' => __( 'Notes', 'propertyhive' ), 
            'desc_tip' => false, 
            'type' => 'text'
        ) );
	    
        echo '</div>';
        
        echo '</div>';
        
    }

    /**
     * Save meta box data
     */
    public static function save( $post_id, $post ) {
        global $wpdb;
        
        if ( !empty($_POST['_board_status']) )
        {
            wp_set_post_terms( $post_id, $_POST['_board_status'], 'board_status' );
        }
        else
        {
            // Setting to blank
            wp_delete_object_term_relationships( $post_id, 'board_status' );
        }

        update_post_meta( $post_id, '_board_date_required', $_POST['_board_date_required'] );
        update_post_meta( $post_id, '_board_notes', $_POST['_board_notes'] );
    }

}
