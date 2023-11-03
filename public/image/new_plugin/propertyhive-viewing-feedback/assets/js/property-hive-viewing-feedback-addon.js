var submitting_feedback = false; // used to prevent double form submission

jQuery(document).ready(function( $ ) 
{
	$("form").submit(function(e)
	{
		e.preventDefault();

		if ( submitting_feedback )
		{
			return;
		}

		submitting_feedback = true;

		var data = {
	        action:         "propertyhive_submit_viewing_feedback",
	        viewing_id:    	propertyhive_viewing_feedback_addon_js_params.post_id,
	        interested: 	$("input[name='viewing-feedback-request-interest']:checked").val(),
	        feedback: 		$("#viewing-feedback-request-note").val(), 
	        security:       propertyhive_viewing_feedback_addon_js_params.ajax_nonce,
	    };

	    $.post(
	    	propertyhive_viewing_feedback_addon_js_params.ajaxurl,
	    	data,
            function( data, status ) {
                $("#feedback-wrapper").html(data);
                submitting_feedback = false;
            }
        );

		return;
		
	});
});