<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function ph_agent_add_meta_boxes() {
    
    global $tabs, $post;
    
    // Agent
    add_meta_box( 'propertyhive-agent-details', __( 'Agent Details', 'propertyhive' ), 'PH_Meta_Box_Agent_Details::output', 'agent', 'normal', 'low' );
    add_meta_box( 'propertyhive-agent-branches', __( 'Branches', 'propertyhive' ), 'PH_Meta_Box_Agent_Branches::output', 'agent', 'normal', 'low' );
    add_meta_box( 'propertyhive-agent-actions', __( 'Actions', 'propertyhive' ), 'PH_Meta_Box_Agent_Actions::output', 'agent', 'side', 'low' );
}