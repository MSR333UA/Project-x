<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PropertyHive PH_Board_Management_AJAX
 *
 * AJAX Event Handler for Board Management add on
 *
 * @class 		PH_Board_Management_AJAX
 * @version		1.0.0
 * @package		PropertyHive/Classes
 * @category	Class
 * @author 		PropertyHive
 */
class PH_Board_Management_AJAX {

	/**
	 * Hook into ajax events
	 */
	public function __construct() {

		// propertyhive_EVENT => nopriv
		$ajax_events = array(
			'search_board_contractors'   => false,
			'load_existing_board_contractor'   => false,
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
     * Load existing board contractor on property record
     */
    public function load_existing_board_contractor() {
        
        check_ajax_referer( 'load-existing-board-contractor', 'security' );
        
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
            echo __( 'Invalid board contractor record', 'propertyhive' );
        }
        
        echo '<p class="form-field">';
            echo '<label></label>';
            echo '<a href="" class="button" id="remove-board-contractor">Remove Board Contractor</a>';
        echo '</p>';
        
        // Quit out
        die();
        
    }
    
    /**
     * Search contacts via ajax
     */
    public function search_board_contractors() {
        
        global $post;
        
        check_ajax_referer( 'search-board-contractors', 'security' );
        
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
                    array(
                        'relation' => 'OR',
                        array(
                            'key' => '_third_party_categories',
                            'value' => ':"3";', // 3 = Board contractor
                            'compare' => 'LIKE'
                        ),
                        array(
                            'key' => '_third_party_categories',
                            'value' => ':3;', // 3 = Board contractor
                            'compare' => 'LIKE'
                        )
                    )
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
    
    function search_contacts_where( $where, &$wp_query )
    {
        global $wpdb;
        
        $where .= ' AND ' . $wpdb->posts . '.post_title LIKE \'%' . esc_sql( $wpdb->esc_like( trim( $_POST['keyword'] ) ) ) . '%\'';
        
        return $where;
    }

}

new PH_Board_Management_AJAX();