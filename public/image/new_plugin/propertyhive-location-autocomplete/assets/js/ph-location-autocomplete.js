jQuery( function($)
{
	if ( ( location_autocomplete_object.data_source == '' || location_autocomplete_object.data_source == 'manual' ) && location_autocomplete_object.location_values.length > 0 )
	{
		jQuery(document).keyup(function(e) 
		{
	    	if ( jQuery('.autocomplete-results').css('display') == 'block' )
	    	{
	    		if (e.key === "Escape") { // escape key maps to keycode `27`
		        	jQuery('.autocomplete-results').hide();
		    	}

		    	switch (e.keyCode) {
		    		case 38: // if the UP key is pressed
		    		{
		    			var index_of_current_active = jQuery('.autocomplete-results ul').find('li.active').index();
		    			var new_index = jQuery('.autocomplete-results ul li').length - 1;
		    			if ( index_of_current_active != -1 )
		    			{
		    				new_index = index_of_current_active - 1;
		    			}
		    			if ( new_index < 0 )
		    			{
		    				new_index = 0;
		    			}

		    			jQuery('.autocomplete-results li.active').removeClass('active');
		    			jQuery('.autocomplete-results li').eq(new_index).addClass('active');
		    			break;
		    		}
		    		case 40: // if the DOWN key is pressed
		    		{
		    			var index_of_current_active = jQuery('.autocomplete-results ul').find('li.active').index();
		    			var new_index = 0;
		    			if ( index_of_current_active != -1 )
		    			{
		    				new_index = index_of_current_active + 1;
		    			}
		    			if ( new_index >= jQuery('.autocomplete-results ul li').length )
		    			{
		    				new_index = jQuery('.autocomplete-results ul li').length - 1;
		    			}

		    			jQuery('.autocomplete-results li.active').removeClass('active');
		    			jQuery('.autocomplete-results li').eq(new_index).addClass('active');
		    			break;
		    		}
		    	}
		    }
		});

		jQuery('.property-search-form input[name=\'address_keyword\']').each(function()
		{
			jQuery(this).attr('autocomplete', 'off');
			jQuery(this).wrap( '<div class="autocomplete-container"></div>' );
			jQuery(this).after( '<div class="autocomplete-results" style="top:' + jQuery(this).outerHeight() + 'px;"><ul></ul></div>' );
		});

		jQuery('.property-search-form input[name=\'address_keyword\']').keydown(function(e)
		{
			if ( jQuery('.autocomplete-results').css('display') == 'block' )
	    	{
				if ( e.keyCode == 13 )
				{
					if ( jQuery('.autocomplete-results ul li.active').length > 0 )
	    			{
	    				e.preventDefault();
	    				jQuery('.autocomplete-results ul li.active').trigger('click');
	    				return;
	    			}
				}
			}
		});

		jQuery('.property-search-form input[name=\'address_keyword\']').keyup(function(e)
		{
			if ( e.keyCode == 38 || e.keyCode == 40 || e.keyCode == 13 )
			{
				return;
			}

			jQuery(this).next('.autocomplete-results').hide();

			var results = [];
			if ( jQuery(this).val() != '' )
			{
				for ( var i = 0; i < location_autocomplete_object.location_values.length; i++ ) 
				{
				    if ( 
				    	location_autocomplete_object.location_values[i].toLowerCase().indexOf( jQuery(this).val().toLowerCase() ) == 0 ||
				    	location_autocomplete_object.location_values[i].toLowerCase().indexOf( ' ' + jQuery(this).val().toLowerCase() ) != -1
				    ) 
				    {
				    	results.push(location_autocomplete_object.location_values[i]);
				    }
				}
			}
			
			if ( results.length > 0 )
			{
				jQuery(this).next('.autocomplete-results').find('ul').empty();
				for ( var i in results )
				{
					jQuery(this).next('.autocomplete-results').find('ul').append('<li>' + results[i] + '</li>');
				}
				jQuery(this).next('.autocomplete-results').show();
			}
		});

		jQuery('.property-search-form').on('click', '.autocomplete-results ul li', function()
		{
			jQuery('.autocomplete-results').hide();
			jQuery('.property-search-form input[name=\'address_keyword\']').val( jQuery(this).html() );
		});
	}
});

var placeSearch;
var autocomplete;
var componentForm = {
	street_number: 'short_name',
	route: 'long_name',
	locality: 'long_name',
	administrative_area_level_1: 'short_name',
	country: 'long_name',
	postal_code: 'short_name'
};
function init_location_autocomplete()
{
	var input = document.getElementById('address_keyword');
	var options = {
	   componentRestrictions: { country: location_autocomplete_object.country }
	};
	var autocomplete = new google.maps.places.Autocomplete(input, options);
	autocomplete.setFields(['address_components']);
}