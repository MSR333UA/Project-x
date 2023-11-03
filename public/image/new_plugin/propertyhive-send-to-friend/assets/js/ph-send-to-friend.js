var ph_stf_is_submitting = false;
var ph_stf_form_obj;
jQuery( function($){
    
    // Enquiry form being submitted
    $('body').on('submit', 'form[name=\'ph_send_to_friend\']', function()
    {
        if (!ph_stf_is_submitting)
        {
            ph_stf_is_submitting = true;
            
            var data = $(this).serialize() + '&'+$.param({ 'action': 'propertyhive_send_to_friend' });

            ph_stf_form_obj = $(this);

            ph_stf_form_obj.find('#sendToFriendSuccess').hide();
            ph_stf_form_obj.find('#sendToFriendValidation').hide();
            ph_stf_form_obj.find('#sendToFriendError').hide();

            $.post( propertyhive_send_to_friend.ajax_url, data, function(response) {
                
                if (response.success == true)
                {
                    if ( propertyhive_send_to_friend.redirect_url && propertyhive_send_to_friend.redirect_url != '' )
                    {
                        window.location.href = propertyhive_send_to_friend.redirect_url;
                    }
                    else
                    {
                        ph_stf_form_obj.find('#sendToFriendSuccess').fadeIn();
                        ph_stf_form_obj.trigger('ph:success');
                        
                        ph_stf_form_obj.trigger("reset");
                    }
                }
                else
                {
                    if (response.reason == 'validation')
                    {
                        ph_stf_form_obj.find('#sendToFriendValidation').fadeIn();
                        ph_stf_form_obj.trigger('ph:validation');
                    }
                    else if (response.reason == 'nosend')
                    {
                        ph_stf_form_obj.find('#sendToFriendError').fadeIn();
                        ph_stf_form_obj.trigger('ph:nosend');
                    }
                }
                
                ph_stf_is_submitting = false;
                
            });
        }

        return false;
    });

});