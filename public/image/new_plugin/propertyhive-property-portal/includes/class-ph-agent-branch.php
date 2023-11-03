<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Property
 *
 * The Property Hive property agent branch class handles property agent and branch data.
 *
 * @class       PH_Property_Agent_Branch
 * @version     1.0.0
 * @package     PropertyHive/Classes
 * @category    Class
 * @author      PropertyHive
 */
class PH_Agent_Branch {

	/** @public int Property (post) ID */
    public $id;

    /**
     * Get the branch if ID is passed, otherwise the branch is new and empty.
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
            $this->get_branch( $id );
        }
    }

    /**
     * Gets agent from the database.
     *
     * @access public
     * @param int $id (default: 0)
     * @return bool
     */
    public function get_branch( $id = 0 ) {
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

    public function get_formatted_full_address( $separator = ', ' )
    {
        $address = array();
        $address_part = trim($this->address_name_number . ' ' . $this->address_street );
        if ($address_part != '')
        {
            $address[] = $address_part;
        }
        $address_part = trim($this->address_two);
        if ($address_part != '')
        {
            $address[] = $address_part;
        }
        $address_part = trim($this->address_three);
        if ($address_part != '')
        {
            $address[] = $address_part;
        }
        $address_part = trim($this->address_four);
        if ($address_part != '')
        {
            $address[] = $address_part;
        }
        $address_part = trim($this->address_postcode);
        if ($address_part != '')
        {
            $address[] = $address_part;
        }
        return implode($separator, $address);
    }
}