<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PropertyHive PH_Maintenance_AJAX
 *
 * AJAX Event Handler for Maintenance add on
 *
 * @class 		PH_Maintenance_AJAX
 * @version		1.0.0
 * @package		PropertyHive/Classes
 * @category	Class
 * @author 		PropertyHive
 */
class PH_Maintenance_AJAX {

	/**
	 * Hook into ajax events
	 */
	public function __construct() {

		// propertyhive_EVENT => nopriv
		$ajax_events = array(
			'search_maintenance_contractors'   => false,
			'load_existing_maintenance_contractor'   => false,
            'get_property_maintenance_jobs_meta_box' => false,
            'get_maintenance_job_actions' => false,
            'maintenance_job_acknowledged' => false,
		);

		foreach ( $ajax_events as $ajax_event => $nopriv ) {
			add_action( 'wp_ajax_propertyhive_' . $ajax_event, array( $this, $ajax_event ) );

			if ( $nopriv ) {
				add_action( 'wp_ajax_nopriv_propertyhive_' . $ajax_event, array( $this, $ajax_event ) );
			}
		}
	}

	/**
	 * Output headers for JSON requests
	 */
	private function json_headers() {
		header( 'Content-Type: application/json; charset=utf-8' );
	}

	/**
     * Load existing third_party_contact on property record
     */
    public function load_existing_maintenance_contractor() {
        
        check_ajax_referer( 'load-existing-maintenance-contractor', 'security' );
        
        $contact_id = $_POST['contact_id'];
        
        $contact = get_post($contact_id);
        
        if ( !is_null( $contact ) )
        {
            echo '<p class="form-field">';
                echo '<label>' . __('Name', 'propertyhive') . '</label>';
                echo '<a href="' . get_edit_post_link( $contact_id ) . '">' . get_the_title($contact_id) . '</a>';
            echo '</p>';
            
            echo '<p class="form-field">';
                echo '<label>' . __('Address', 'propertyhive') . '</label>';
                echo get_post_meta($contact_id, '_address_name_number', TRUE) . ' ';
                echo get_post_meta($contact_id, '_address_street', TRUE) . ', ';
                echo get_post_meta($contact_id, '_address_two', TRUE) . ', ';
                echo get_post_meta($contact_id, '_address_three', TRUE) . ', ';
                echo get_post_meta($contact_id, '_address_four', TRUE) . ', ';
                echo get_post_meta($contact_id, '_address_postcode', TRUE);
            echo '</p>';
            
            echo '<p class="form-field">';
                echo '<label>' . __('Telephone Number', 'propertyhive') . '</label>';
                echo get_post_meta($contact_id, '_telephone_number', TRUE);
            echo '</p>';
            
            echo '<p class="form-field">';
                echo '<label>' . __('Email Address', 'propertyhive') . '</label>';
                echo get_post_meta($contact_id, '_email_address', TRUE);
            echo '</p>';
        }
        else
        {
            echo __( 'Invalid third party contact record', 'propertyhive' );
        }
        
        echo '<p class="form-field">';
            echo '<label></label>';
            echo '<a href="" class="button" id="remove-maintenance-contractor">Remove Contractor</a>';
        echo '</p>';
        
        // Quit out
        die();
        
    }
    
    /**
     * Search third_party_contact via ajax
     */
    public function search_maintenance_contractors() {
        
        global $post;
        
        check_ajax_referer( 'search-maintenance-contractors', 'security' );
        
        $return = array();
        
        $keyword = trim( $_POST['keyword'] );
        
        if ( !empty( $keyword ) && strlen( $keyword ) > 2 )
        {
            // Get all contacts that match the name
            $args = array(
                'post_type' => 'contact',
                'nopaging' => true,
                'post_status' => array( 'publish' ),
                'meta_query' => array(
	                array(
	                    'key' => '_contact_types',
	                    'value' => 'thirdparty',
	                    'compare' => 'LIKE'
	                ),
	            )
            );
            
            add_filter( 'posts_where', array( $this, 'search_contacts_where' ), 10, 2 );
            
            $contact_query = new WP_Query( $args );
            
            remove_filter( 'posts_where', array( $this, 'search_contacts_where' ) );
            
            if ( $contact_query->have_posts() )
            {
                while ( $contact_query->have_posts() )
                {
                    $contact_query->the_post();
                    
                    $return[] = array(
                        'ID' => $post->ID,
                        'post_title' => get_the_title()
                    );
                }
            }
            
            wp_reset_postdata();
        }
        
        $this->json_headers();
        echo json_encode( $return );
        
        // Quit out
        die();
    }
    
    public function search_contacts_where( $where, &$wp_query )
    {
        global $wpdb;
        
        $where .= ' AND ' . $wpdb->posts . '.post_title LIKE \'%' . esc_sql( $wpdb->esc_like( trim( $_POST['keyword'] ) ) ) . '%\'';
        
        return $where;
    }

    public function get_property_maintenance_jobs_meta_box()
    {
        check_ajax_referer( 'get_property_maintenance_jobs_meta_box', 'security' );

        global $post;

        echo '<div class="propertyhive_meta_box">';
        
        echo '<div class="options_group">';

            $args = array(
                'post_type'   => 'maintenance_job', 
                'nopaging'    => true,
                'orderby'   => 'meta_value',
                'order'       => 'DESC',
                'meta_key'  => '_date_carried_out',
                'post_status'   => 'publish',
                'meta_query'  => array(
                    array(
                        'key' => '_property_id',
                        'value' => $_POST['post_id']
                    )
                )
            );
            $maintenance_jobs_query = new WP_Query( $args );

            if ( $maintenance_jobs_query->have_posts() )
            {
                echo '<table style="width:100%">
                    <thead>
                        <tr>
                            <th style="text-align:left;">' . __( 'Job Title', 'propertyhive' ) . '</th>
                            <th style="text-align:left;">' . __( 'Work Carried Out', 'propertyhive' ) . '</th>
                            <th style="text-align:left;">' . __( 'Quote / Cost', 'propertyhive' ) . '</th>
                            <th style="text-align:left;">' . __( 'Contractor', 'propertyhive' ) . '</th>
                            <th style="text-align:left;">' . __( 'Status', 'propertyhive' ) . '</th>
                        </tr>
                    </thead>
                    <tbody>';

                while ( $maintenance_jobs_query->have_posts() )
                {
                    $maintenance_jobs_query->the_post();

                    echo '<tr>';
                        echo '<td style="text-align:left;"><a href="' . get_edit_post_link( get_the_ID(), '' ) . '">' . get_the_title() . '</a></td>';
                        echo '<td style="text-align:left;">';
                        if ( get_post_meta(get_the_ID(), '_date_carried_out', TRUE) != '' )
                        {
                            echo date("jS F Y", strtotime(get_post_meta(get_the_ID(), '_date_carried_out', TRUE)));
                        }
                        else
                        {
                            echo '-';
                        }
                        echo '</td>';
                        echo '<td style="text-align:left;">' . get_post_meta(get_the_ID(), '_cost', TRUE) . '</td>';
                        echo '<td style="text-align:left;">';
                            $contractor_id = get_post_meta(get_the_ID(), '_contractor_id', TRUE);
                            if ( $contractor_id != '' && $contractor_id != '0' && get_post_type($contractor_id) == 'contact' )
                            {
                                echo get_the_title($contractor_id);
                            }
                            else
                            {
                                echo '-';
                            }
                        echo '</td>';

                        $status = ucwords(get_post_meta( get_the_ID(), '_status', TRUE ));

                        if ( get_post_meta( get_the_ID(), '_externally_reported', TRUE ) === 'yes' && get_post_meta( get_the_ID(), '_acknowledged', TRUE ) === 'no' )
                        {
                            $status .= ' - Unconfirmed';
                        }
                        echo '<td style="text-align:left;">' . $status . '</td>';
                    echo '</tr>';
                }

                echo '
                    </tbody>
                </table>
                <br>';
            }
            else
            {
                echo '<p>' . __( 'No maintenance jobs exist for this property', 'propertyhive') . '</p>';
            }
            wp_reset_postdata();

        do_action('propertyhive_property_maintenance_jobs_fields');
        
        echo '</div>';
        
        echo '</div>';

        die();
    }

    public function get_maintenance_job_actions()
    {
        check_ajax_referer( 'maintenance-job-actions', 'security' );

        $post_id = (int)$_POST['maintenance_job_id'];

        $externally_reported = get_post_meta( $post_id, '_externally_reported', TRUE );
        $acknowledged = get_post_meta( $post_id, '_acknowledged', TRUE );

        echo '<div class="propertyhive_meta_box propertyhive_meta_box_actions" id="propertyhive_maintenance_job_actions_meta_box">

        <div class="options_group" style="padding-top:8px;">';

        $show_unacknowledged_meta_boxes = false;

        $actions = array();

        if ( $externally_reported == 'yes' && $acknowledged == 'no' )
        {
            $actions[] = '<a
                    href="#action_panel_maintenance_job_acknowledged"
                    class="button button-primary maintenance-job-action"
                    style="width:100%; margin-bottom:7px; text-align:center"
                >' . __('Maintenance Job Acknowledged', 'propertyhive') . '</a>';

            $show_unacknowledged_meta_boxes = true;
        }

        $actions = apply_filters( 'propertyhive_admin_maintenance_job_actions', $actions, $post_id );

        if ( !empty($actions) )
        {
            echo implode("", $actions);
        }
        else
        {
            echo '<div style="text-align:center">' . __( 'No actions to display', 'propertyhive' ) . '</div>';
        }

        echo '</div>

        </div>';

        if ( $show_unacknowledged_meta_boxes )
        {
            echo '<div class="propertyhive_meta_box propertyhive_meta_box_actions" id="action_panel_maintenance_job_acknowledged" style="display:none;">

                <div class="options_group" style="padding-top:8px;">

                    <div class="form-field">

                        <label for="_maintenance_job_title">' . __( 'Maintenance Job Title', 'propertyhive' ) . '</label>

                        <input type="text" id="_maintenance_job_title" name="_maintenance_job_title" style="width:100%;" value="' . addslashes(get_the_title( $post_id )) . '" />

                    </div>

                    <a class="button action-cancel" href="#">' . __( 'Cancel', 'propertyhive' ) . '</a>
                    <a class="button button-primary acknowledged-action-submit" href="#">' . __( 'Save', 'propertyhive' ) . '</a>

                </div>

            </div>';
        }
        die();
    }

    public function maintenance_job_acknowledged()
    {
        check_ajax_referer( 'maintenance-job-actions', 'security' );

        $post_id = (int)$_POST['maintenance_job_id'];

        $args = array(
            'ID'           => $post_id,
            'post_title'   => sanitize_text_field( $_POST['job_title'] ),
        );

        wp_update_post( $args );

        update_post_meta( $post_id, '_acknowledged', 'yes' );

        header( 'Content-Type: application/json; charset=utf-8' );
        $return = array('success' => true);
        echo json_encode( $return );

        die();
    }
}

new PH_Maintenance_AJAX();