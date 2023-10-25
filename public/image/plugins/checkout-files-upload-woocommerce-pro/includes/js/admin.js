/**
 * Checkout Files Upload for WooCommerce - admin scripts
 *
 * @version 2.1.1
 * @since   2.1.0
 * @author  WP Wham
 */

(function( $ ){
	
	$( document ).ready( function(){
		
		/*
		 * post metabox
		 */
		$( '.wpwham-checkout-files-upload-file-delete-button' ).on( 'click', function(){
			return confirm( wpwham_checkout_files_upload_admin.i18n.confirmation_message );
		});
		
		/*
		 * settings page - File Uploader #X
		 */
		var toggleMinMaxFiles = function() {
			var fileUploader = false;
			var show = false;
			$( '.alg_checkout_files_upload_multiple' ).each( function(){
				fileUploader = $( this ).data( 'file-uploader' );
				if ( $( this ).prop( 'checked' ) ) {
					show = true;
					return false;
				}
			});
			if ( show ) {
				$( '#wpwham_checkout_files_upload_min_files_' + fileUploader ).closest( 'tr' ).show();
				$( '#wpwham_checkout_files_upload_max_files_' + fileUploader ).closest( 'tr' ).show();
			} else {
				$( '#wpwham_checkout_files_upload_min_files_' + fileUploader ).closest( 'tr' ).hide();
				$( '#wpwham_checkout_files_upload_max_files_' + fileUploader ).closest( 'tr' ).hide();
			}
		}
		$( '.alg_checkout_files_upload_multiple' ).on( 'change', toggleMinMaxFiles );
		toggleMinMaxFiles();
		
		var toggleImageWidthHeight = function() {
			var fileUploader = false;
			var show = false;
			$( '.alg_checkout_files_upload_file_validate_image_dimensions' ).each( function(){
				fileUploader = $( this ).data( 'file-uploader' );
				if ( $( this ).val() > '' ) {
					show = true;
					return false;
				}
			});
			if ( show ) {
				$( '#alg_checkout_files_upload_file_validate_image_dimensions_w_' + fileUploader ).closest( 'tr' ).show();
				$( '#alg_checkout_files_upload_file_validate_image_dimensions_h_' + fileUploader ).closest( 'tr' ).show();
			} else {
				$( '#alg_checkout_files_upload_file_validate_image_dimensions_w_' + fileUploader ).closest( 'tr' ).hide();
				$( '#alg_checkout_files_upload_file_validate_image_dimensions_h_' + fileUploader ).closest( 'tr' ).hide();
			}
		}
		$( '.alg_checkout_files_upload_file_validate_image_dimensions' ).on( 'change', toggleImageWidthHeight );
		toggleImageWidthHeight();
		
		/*
		 * settings page - don't trigger WC "are you sure?" warning when updating license key
		 */
		$( '#wpwham_checkout_files_upload_license' ).on( 'change', function(){
			setTimeout( function(){
				window.onbeforeunload = "";
			}, 1 );
		});
		
	});
	
})( jQuery );
