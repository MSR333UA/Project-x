<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function ph_board_management_add_meta_boxes() {
    
    global $tabs, $post;
    
    // Property
    add_meta_box( 'propertyhive-property-board-contractor', __( 'Board Contractor', 'propertyhive' ), 'PH_Meta_Box_Property_Board_Contractor::output', 'property', 'normal', 'low' );
    add_meta_box( 'propertyhive-property-board-details', __( 'Board Details', 'propertyhive' ), 'PH_Meta_Box_Property_Board_Details::output', 'property', 'normal', 'low' );
}