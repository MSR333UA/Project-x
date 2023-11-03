var is_submitting = false;
var form_obj;
jQuery( function($){

    // Report Maintenance Issue form being submitted
    $('body').on('submit', 'form[name=\'ph_report_maintenance_issue\']', function()
    {
        if (!is_submitting)
        {
            is_submitting = true;

            var data = $(this).serialize() + '&' + $.param({ 'action': 'report_maintenance_issue' });

            form_obj = $(this);

            form_obj.find('#reportMaintenanceSuccess').hide();
            form_obj.find('#reportMaintenanceValidation').hide();
            form_obj.find('#reportMaintenanceError').hide();

            $.post( propertyhive_report_maintenance_issue.ajax_url, data, function(response) {

                if (response.success == true)
                {
                    form_obj.find('#reportMaintenanceSuccess').fadeIn();
                    form_obj.trigger('ph:success');

                    form_obj.trigger("reset");
                }
                else
                {
                    if (response.reason == 'validation')
                    {
                        form_obj.find('#reportMaintenanceValidation').fadeIn();
                        form_obj.trigger('ph:validation');
                    }
                    else if (response.reason == 'nosend')
                    {
                        form_obj.find('#reportMaintenanceError').fadeIn();
                        form_obj.trigger('ph:nosend');
                    }
                }

                is_submitting = false;

                if ( typeof grecaptcha != 'undefined' && $( "div.g-recaptcha" ).length > 0 )
                {
                    grecaptcha.reset();
                }
            });
        }

        return false;
    });

});