<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Property
 *
 * The Property Hive agent class handles agent data.
 *
 * @class       PH_Agent
 * @version     1.0.0
 * @package     PropertyHive/Classes
 * @category    Class
 * @author      PropertyHive
 */
class PH_Agent {

	/** @public int Agent (post) ID */
    public $id;

    /**
     * Get the agent if ID is passed, otherwise the agent is new and empty.
     *
     * @access public
     * @param string|object $id (default: '')
     * @return void
     */
    public function __construct( $id = '' ) {
        if ( $id != '' ) 
        {
            if ( is_int($id) && $id > 0 )
            {
                
            }
            else
            {
                // Must be post object
                $id = $id->ID;
            }       
            $this->get_agent( $id );
        }
    }

    /**
     * Gets agent from the database.
     *
     * @access public
     * @param int $id (default: 0)
     * @return bool
     */
    public function get_agent( $id = 0 ) {
        if ( ! $id ) {
            return false;
        }
        if ( $result = get_post( $id ) ) {
            
            $this->id                  = $result->ID;
            $this->post_title          = $result->post_title;

            return true;
        }
        return false;
    }

    /**
     * __isset function.
     *
     * @access public
     * @param mixed $key
     * @return bool
     */
    public function __isset( $key ) {
        if ( ! $this->id ) {
            return false;
        }
        return metadata_exists( 'post', $this->id, $key );
    }

    /**
     * __get function.
     *
     * @access public
     * @param mixed $key
     * @return mixed
     */
    public function __get( $key ) {
    	// Get values or default if not set
        $value = get_post_meta( $this->id, $key, true );
        if ($value == '')
        {
            $value = get_post_meta( $this->id, '_' . $key, true );
        }
        return $value;
    }

    /**
     * Get logo src
     *
     * @access public
     * @param string $size
     * @return string
     */
    public function get_logo_src( $size = 'full' ) {
        
        $logo = wp_get_attachment_image_src( $this->logo, $size );

        if ($logo !== FALSE)
        {
            return $logo[0];
        }
        
        return '';
    }

    public function get_branches()
    {
        global $post;

        $branches = array();

        $args = array(
            'post_type' => 'branch',
            'nopaging' => TRUE,
            'meta_query' => array(
                array(
                    'key' => '_agent_id',
                    'value' => $this->id,
                )
            )
        );

        $branch_query = new WP_Query( $args );

        if ( $branch_query->have_posts() )
        {
            while ( $branch_query->have_posts() )
            {
                $branch_query->the_post();

                $branches[] = new PH_Agent_Branch( $post->ID );
            }
        }

        wp_reset_postdata();

        return $branches;
    }
}