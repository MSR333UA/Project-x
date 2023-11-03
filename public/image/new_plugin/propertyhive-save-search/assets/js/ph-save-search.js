var ph_doing_save_search_request = false;
jQuery(document).ready(function()
{
	if ( window.location.hash == '#savesearch' ) 
	{
		jQuery.fancybox.open({
			src  : '#save_search_popup',
			type : 'inline'
		});
	}

	jQuery('html,body').on('click', '#save_search_button', function(e)
	{
		if ( ph_doing_save_search_request )
		{
			return;
		}

		if ( jQuery('#saved_search_name').val() == '' )
		{
			jQuery('#saved_search_name').css('border', '1px solid #900');
			return;
		}

		jQuery(this).html(propertyhive_save_search.loading_text);
		ph_doing_save_search_request = true;
		
		var data = {
			action: 'save_search',
			search_parameters: location.search.replace(/^\?/g, ''),
			saved_search_name: jQuery('#saved_search_name').val(),
			saved_search_email_alerts: jQuery('input[name="saved_search_email_alerts"]').is(':checked'),
		};

		jQuery.post(
			propertyhive_save_search.ajax_url,
			data,
			function(response)
			{
				if ( response.success )
				{
					jQuery('#save_search_form').fadeOut('fast', function()
					{
						jQuery('#save_search_success').fadeIn();
					});
					setTimeout( function() { jQuery.fancybox.close() }, 3000 );
				}
				else
				{
					alert(response.error_message);
					console.log(response);
					jQuery('#save_search_button').html(propertyhive_save_search.save_link_text);
				}
				ph_doing_save_search_request = false;
			}
		);
	});
	
	jQuery('html,body').on('click', '#remove_saved_search', function(e)
	{
		if ( ph_doing_save_search_request )
		{
			return;
		}

		jQuery(this).html(propertyhive_save_search.removing_text);
		ph_doing_save_search_request = true;

		var data = {
			action: 'remove_saved_search',
			profile_to_remove: jQuery(this).attr('profile_to_remove')
		};

		jQuery.post(
			propertyhive_save_search.ajax_url,
			data,
			function(response)
			{
				if ( response.success )
				{
					location.reload();
				}
				else
				{
					alert(response.error_message);
					console.log(response);
					jQuery('#remove_saved_search').html(propertyhive_save_search.remove_link_text);
				}
				ph_doing_save_search_request = false;
			}
		);
	});

	jQuery('html,body').on('change', ".update_search_send_emails", function(e)
	{

		if ( ph_doing_save_search_request )
		{
			return;
		}
		ph_doing_save_search_request = true;

		var send_email_alerts = '';
		if(this.checked)
		{
			send_email_alerts = 'yes';
		}

		var data = {
			action: 'update_search_send_emails',
			profile_to_update: jQuery(this).val(),
			send_email_alerts: send_email_alerts,
		};

		jQuery.post(
			propertyhive_save_search.ajax_url,
			data,
			function(response)
			{
				if ( response.success )
				{

				}
				else
				{
					alert(response.error_message);
					console.log(response);
				}
				ph_doing_save_search_request = false;
			}
		);
	});
});