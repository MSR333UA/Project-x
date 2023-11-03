<?php
/**
 * Property Maintenance Jobs
 *
 * @author      PropertyHive
 * @category    Admin
 * @package     PropertyHive/Admin/Meta Boxes
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Property_Maintenance_Jobs
 */
class PH_Meta_Box_Property_Maintenance_Jobs {

    /**
     * Output the metabox
     */
    public static function output( $post ) {
        
        echo '<div id="propertyhive_property_maintenance_jobs_meta_box">Loading...</div>';
        
    }

    /**
     * Save meta box data
     */
    public static function save( $post_id, $post ) {
        global $wpdb;
        

    }

}
