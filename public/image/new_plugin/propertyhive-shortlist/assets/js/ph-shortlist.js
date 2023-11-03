var ph_doing_shortlist_request = false;
jQuery(document).ready(function()
{
	jQuery('html,body').on('click', 'a[data-add-to-shortlist]', function(e)
	{
		e.preventDefault();

		if ( ph_doing_shortlist_request )
		{
			return;
		}

		ph_doing_shortlist_request = true;

		var property_id = jQuery(this).attr('data-add-to-shortlist');

		if ( propertyhive_shortlist.loading_text != '' )
		{
			jQuery('a[data-add-to-shortlist=\'' + property_id + '\']').html(propertyhive_shortlist.loading_text + '...');
		}
		jQuery('a[data-add-to-shortlist=\'' + property_id + '\']').attr('disabled', 'disabled');

		var data = {
			action: 'add_to_shortlist',
			property_id: property_id
		};

		jQuery.post(
			propertyhive_shortlist.ajax_url, 
			data, 
			function(response) 
			{
				if ( response.success )
				{
					if ( response.action == 'added' )
					{
						jQuery('a[data-add-to-shortlist=\'' + property_id + '\']').html(propertyhive_shortlist.remove_link_text);
						jQuery( document ).trigger('ph:added_to_shortlist', [ property_id ]);
					}
					else if ( response.action == 'removed' )
					{
						jQuery('a[data-add-to-shortlist=\'' + property_id + '\']').html(propertyhive_shortlist.add_link_text);
						jQuery( document ).trigger('ph:removed_From_shortlist', [ property_id ]);
					}
				}
				else
				{
					alert('An error occured when trying to add this property to your shortlist. Please try again.');
					console.log(response);
					jQuery( document ).trigger('ph:shortlist_error', [ response, property_id ]);
				}

				jQuery('a[data-add-to-shortlist=\'' + property_id + '\']').attr('disabled', false);

				ph_doing_shortlist_request = false;
			}
		);
	});

	jQuery('a[data-add-to-shortlist]').each(function() {
		var property_id = jQuery(this).attr('data-add-to-shortlist');
		var current_text = jQuery(this).text();
		var this_button = jQuery(this);

		var data = {
			action: 'check_if_shortlisted',
			property_id: property_id,
		};

		jQuery.post(
			propertyhive_shortlist.ajax_url,
			data,
			function(response)
			{
				console.log(response);
				if ( response.success )
				{
					if ( response.on_shortlist )
					{
						console.log("it's on the shortlist");
						var correct_button_text = propertyhive_shortlist.remove_link_text;
					}
					else
					{
						console.log("it's not on the shortlist");
						var correct_button_text = propertyhive_shortlist.add_link_text;
					}
					console.log('correct:' + correct_button_text);
					console.log('current:' + current_text);
					if ( current_text != correct_button_text )
					{
						console.log("button text is wrong");
						this_button.html(correct_button_text);
					}
					else
					{
						console.log("text is already correct");
					}
				}
				else
				{
					console.log("user logged in, don't need to prevent caching");
				}
			}
		);

	});
});