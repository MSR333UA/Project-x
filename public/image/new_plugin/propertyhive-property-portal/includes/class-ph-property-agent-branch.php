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
class PH_Property_Agent_Branch {

	/** @public object PH_Property */
    private $property;

	/** @public object PH_Agent */
    public $agent;

    /** @public object PH_Agent_Branch */
    public $agent_branch;

    /**
     * Get the property if ID is passed, otherwise the property is new and empty.
     *
     * @access public
     * @param string|object $id (default: '')
     * @return void
     */
    public function __construct( $property ) {
        $this->property = $property;
        $this->agent = new PH_Agent((int)$property->agent_id);
        $this->agent_branch = new PH_Agent_Branch((int)$property->branch_id);
    }

    public function get_agent_branch_telephone_number()
    {
    	if ($this->property->department == 'residential-sales')
        {
            return $this->agent_branch->telephone_number_sales;
        }
        elseif ($this->property->department == 'residential-lettings')
        {
            return $this->agent_branch->telephone_number_lettings;
        }
        elseif ($this->property->department == 'commercial')
        {
            return $this->agent_branch->telephone_number_commercial;
        }
    }

    public function get_agent_branch_email_address()
    {
    	if ($this->property->department == 'residential-sales')
        {
            return $this->agent_branch->email_address_sales; 
        }
        elseif ($this->property->department == 'residential-lettings')
        {
            return $this->agent_branch->email_address_lettings;
        }
        elseif ($this->property->department == 'commercial')
        {
            return $this->agent_branch->email_address_commercial;
        }
    }
}