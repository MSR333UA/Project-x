jQuery(document).ready(function($) {

	$(document).on('click', 'a.viewing-action[href=\'#action_panel_viewing_feedback_request\']', function(e)
	{
		e.preventDefault();

		var data = {
	        action:         'propertyhive_viewing_feedback_request',
	        viewing_id:    	( ph_lightbox_open ? ph_lightbox_post_id : propertyhive_viewing_feedback_addon_params.post_id ),
	        security:       propertyhive_viewing_feedback_addon_params.ajax_nonce,
	    };

		jQuery.post( ajaxurl, data, function(response) 
	    {
	    	redraw_viewing_actions();
	    }, 'json');
		return;

	});

});