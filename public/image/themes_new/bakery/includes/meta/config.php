<?php 
	/**
	 * This file is used to save the data of meta boxes
 	 *
	 * @package Bakery WordPress Theme
	 * @since 1.0
	 * @see https://codex.wordpress.org/Plugin_API/Action_Reference/save_post
	 */

	if( !function_exists('vu_save_meta_box') ){
		function vu_save_meta_box( $post_id ) {
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
				return;

			if ( wp_is_post_revision( $post_id ) )
				return;

			if ( !isset($_POST['vu_field']) or empty($_POST['vu_field']) or !wp_verify_nonce( $_POST['vu_metabox_nonce'], 'vu_metabox_nonce' ) )
				return;

			if ( 'page' == $_POST['post_type'] ) {
				if ( !current_user_can( 'edit_page', $post_id ) )
					return;
			} else {
				if ( !current_user_can( 'edit_post', $post_id ) )
					return;
			}

			foreach( $_POST['vu_field'] as $key => $value ){
				vu_update_post_meta( $post_id, $key, $value );
			}
		}

		add_action( 'save_post', 'vu_save_meta_box' );
	}
?>