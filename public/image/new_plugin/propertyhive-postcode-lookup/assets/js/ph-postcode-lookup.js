jQuery(document).ready(function()
{
	// Hide initial address fields except postcode
	jQuery('.form-field._address_name_number_field').hide();
	jQuery('.form-field._address_street_field').hide();
	jQuery('.form-field._address_two_field').hide();
	jQuery('.form-field._address_three_field').hide();
	jQuery('.form-field._address_four_field').hide();
	jQuery('.form-field._address_country_field').hide();

	// Add placeholder instructions to postcode
	jQuery('.form-field._address_postcode_field input').attr( 'placeholder', 'Lookup addresses...' );
	jQuery('.form-field._address_postcode_field input').after( ' <span><button>Lookup</button> or <a href="">Enter Address Manually</a></span>' );

	jQuery('.form-field._address_postcode_field').after('<p class="form-field _address_postcode_lookup_results_field" style="display:none;"><label for="_address_postcode">Select Address</label><select></select></p>');

	jQuery('.form-field._address_postcode_field input').keydown(function(event)
	{
		jQuery('.form-field._address_postcode_lookup_results_field').hide();
		jQuery('.form-field._address_postcode_lookup_results_field select')
			.find('option')
    		.remove();

		if( event.keyCode == 13 )
		{
	      	event.preventDefault();

	      	lookupAddress( jQuery(this).val(), '' );

	      	return false;
	    }
	});

	jQuery('.form-field._address_postcode_field span button').click(function(event)
	{
      	event.preventDefault();

      	lookupAddress( jQuery('.form-field._address_postcode_field input').val(), '' );

      	return false;
	});

	jQuery('.form-field._address_postcode_field span a').click(function(event)
	{
		event.preventDefault();

		showAddressFields('');
		jQuery('.form-field._address_name_number_field input').focus();

		return false;
	});

	jQuery('body').on('change', '.form-field._address_postcode_lookup_results_field select', function()
	{
		if ( jQuery(this).val() != '' )
		{
			addressSelected( jQuery(this).val(), '' );
		}
	});


	jQuery('.form-field._owner_address_name_number_field').hide();
	jQuery('.form-field._owner_address_street_field').hide();
	jQuery('.form-field._owner_address_two_field').hide();
	jQuery('.form-field._owner_address_three_field').hide();
	jQuery('.form-field._owner_address_four_field').hide();
	jQuery('.form-field._owner_address_country_field').hide();

	// Add placeholder instructions to postcode
	jQuery('.form-field._owner_address_postcode_field input').attr( 'placeholder', 'Lookup addresses...' );
	jQuery('.form-field._owner_address_postcode_field input').after( ' <span><button>Lookup</button> or <a href="">Enter Address Manually</a></span>' );

	jQuery('.form-field._owner_address_postcode_field').after('<p class="form-field _owner_address_postcode_lookup_results_field" style="display:none;"><label for="_owner_address_postcode">Select Address</label><select></select></p>');

	jQuery('.form-field._owner_address_postcode_field input').keydown(function(event)
	{
		jQuery('.form-field._owner_address_postcode_lookup_results_field').hide();
		jQuery('.form-field._owner_address_postcode_lookup_results_field select')
			.find('option')
    		.remove();

		if( event.keyCode == 13 )
		{
	      	event.preventDefault();

	      	lookupAddress( jQuery(this).val(), 'owner_' );

	      	return false;
	    }
	});

	jQuery('.form-field._owner_address_postcode_field span button').click(function(event)
	{
      	event.preventDefault();

      	lookupAddress( jQuery('.form-field._owner_address_postcode_field input').val(), 'owner_' );

      	return false;
	});

	jQuery('.form-field._owner_address_postcode_field span a').click(function(event)
	{
		event.preventDefault();

		showAddressFields('owner_');
		jQuery('.form-field._owner_address_name_number_field input').focus();

		return false;
	});

	jQuery('body').on('change', '.form-field._owner_address_postcode_lookup_results_field select', function()
	{
		if ( jQuery(this).val() != '' )
		{
			addressSelected( jQuery(this).val(), 'owner_' );
		}
	});
});

function lookupAddress( postcode, prefix )
{
	if ( postcode == '' )
	{
		alert('Please enter a postcode');
		return false;
	}

	switch ( propertyhive_postcode_lookup.service )
	{
		case "Postcode Anywhere":
		{
			var request = jQuery.ajax({
				crossOrigin: true,
				url: 'http://services.postcodeanywhere.co.uk/CapturePlus/Interactive/Find/v2.10/json3.ws',
				data: { 'Key': propertyhive_postcode_lookup.api_key, 'SearchTerm': postcode },
				dataType: 'json'
			});

			request.done(function( data, textStatus, jqXHR )
			{
				if ( textStatus == 'success' )
				{
					console.log(data);
					if ( data.Items.length > 0 )
					{
						jQuery('.form-field._' + prefix + 'address_postcode_lookup_results_field select')
					         	.append(jQuery("<option></option>")
					                .attr("value", '' )
					                .text( '' )); 

						for ( var i in data.Items )
						{
							// Remove empty address elements
							var addressFields = new Array();
							var explodeAddress = data.Items[i].Text.replace(postcode.toUpperCase(), "").split(",");

							var addressFields = new Array();
							for ( var j in explodeAddress )
							{
								var addressPart = explodeAddress[j].trim();
								if ( addressPart.trim() != '' )
								{
									addressFields.push(addressPart);
								}
							}

							jQuery('.form-field._' + prefix + 'address_postcode_lookup_results_field select')
					         	.append(jQuery("<option></option>")
					                .attr("value", addressFields.join(", "))
					                .text( addressFields.join(", "))); 
						}
						jQuery('.form-field._' + prefix + 'address_postcode_lookup_results_field').show();
					}
					else
					{
						alert( "Even though the address lookup was successful, no addresses were found" );
						console.log(data);
						console.log(textStatus);
					}
				}
				else
				{
					alert( "Address lookup request failed: " + textStatus );
					console.log(data);
					console.log(textStatus);
				}
			});

			request.fail(function( jqXHR, textStatus )
			{
				alert( "Address lookup request failed: " + textStatus );
				console.log(jqXHR);
				console.log(textStatus);
			});

			break;
		}
		case "getAddress":
		{
			var request = jQuery.ajax({
				crossOrigin: true,
				url: 'https://api.getAddress.io/v2/uk/' + postcode,
				data: { 'api-key': propertyhive_postcode_lookup.api_key },
				dataType: 'json'
			});

			request.done(function( data, textStatus, jqXHR )
			{
				if ( textStatus == 'success' )
				{
					if ( data.Addresses.length > 0 )
					{
						jQuery('.form-field._' + prefix + 'address_postcode_lookup_results_field select')
					         	.append(jQuery("<option></option>")
					                .attr("value", '' )
					                .text( '' )); 

						for ( var i in data.Addresses )
						{
							// Remove empty address elements
							var addressFields = new Array();
							var explodeAddress = data.Addresses[i].split(",");

							var addressFields = new Array();
							for ( var j in explodeAddress )
							{
								var addressPart = explodeAddress[j].trim();
								if ( addressPart.trim() != '' )
								{
									addressFields.push(addressPart);
								}
							}

							jQuery('.form-field._' + prefix + 'address_postcode_lookup_results_field select')
					         	.append(jQuery("<option></option>")
					                .attr("value", addressFields.join(", "))
					                .text( addressFields.join(", "))); 
						}
						jQuery('.form-field._' + prefix + 'address_postcode_lookup_results_field').show();
					}
					else
					{
						alert( "Even though the address lookup was successful, no addresses were found" );
						console.log(data);
						console.log(textStatus);
					}
				}
				else
				{
					alert( "Address lookup request failed: " + textStatus );
					console.log(data);
					console.log(textStatus);
				}
			});

			request.fail(function( jqXHR, textStatus )
			{
				alert( "Address lookup request failed: " + textStatus );
				console.log(jqXHR);
				console.log(textStatus);
			});

			break;
		}
		case "IdealPostcodes":
		{
			var request = jQuery.ajax({
				crossOrigin: true,
				url: 'https://api.ideal-postcodes.co.uk/v1/postcodes/' + postcode,
				data: { 'api_key': propertyhive_postcode_lookup.api_key },
				dataType: 'json'
			});

			request.done(function( data, textStatus, jqXHR )
			{
				if ( textStatus == 'success' )
				{
					if ( data.result.length > 0 )
					{
						jQuery('.form-field._' + prefix + 'address_postcode_lookup_results_field select')
					         	.append(jQuery("<option></option>")
					                .attr("value", '' )
					                .text( '' )); 

						for ( var i in data.result )
						{
							// Remove empty address elements
							var addressFields = new Array();
							if ( data.result[i].line_1 != '' )
							{
								addressFields.push(data.result[i].line_1);
							}
							if ( data.result[i].line_2 != '' )
							{
								addressFields.push(data.result[i].line_2);
							}
							if ( data.result[i].line_3 != '' )
							{
								addressFields.push(data.result[i].line_3);
							}
							if ( data.result[i].district != '' )
							{
								addressFields.push(data.result[i].district);
							}
							if ( data.result[i].traditional_county != '' )
							{
								addressFields.push(data.result[i].traditional_county);
							}

							jQuery('.form-field._' + prefix + 'address_postcode_lookup_results_field select')
					         	.append(jQuery("<option></option>")
					                .attr("value", addressFields.join(", "))
					                .text( addressFields.join(", "))); 
						}
						jQuery('.form-field._' + prefix + 'address_postcode_lookup_results_field').show();
					}
					else
					{
						alert( "Even though the address lookup was successful, no addresses were found" );
						console.log(data);
						console.log(textStatus);
					}
				}
				else
				{
					alert( "Address lookup request failed: " + textStatus );
					console.log(data);
					console.log(textStatus);
				}
			});

			request.fail(function( jqXHR, textStatus )
			{
				alert( "Address lookup request failed: " + textStatus );
				console.log(jqXHR);
				console.log(textStatus);
			});
			break;
		}
		case "Google Geocoding":
		{
			var geocoder = new google.maps.Geocoder();

			var address = jQuery('#_address_postcode').val();
			geocoder.geocode( { 'address': address}, function(results, status) {

	            if (status == google.maps.GeocoderStatus.OK) 
	            {
	            	var explodeAddress = results[0].formatted_address.split(",");
	            	explodeAddress.pop();

	            	for ( var j in explodeAddress )
					{
						// remove postcode from line if it exists
						var index_of_postcode = explodeAddress[j].toLowerCase().indexOf(address.toLowerCase());
						if ( index_of_postcode != -1 )
						{
							// found the postcode in this address part
							explodeAddress[j] = explodeAddress[j].substr(0, index_of_postcode);
						}
						switch ( parseInt(j) )
						{
							case 0: { jQuery('.form-field._' + prefix + 'address_street_field input').val(explodeAddress[j].trim()); break; }
							case 1: { jQuery('.form-field._' + prefix + 'address_three_field input').val(explodeAddress[j].trim()); break; }
							case 2: { jQuery('.form-field._' + prefix + 'address_four_field input').val(explodeAddress[j].trim()); break; }
						}
					}

	                showAddressFields(prefix);
	            }
	            else
	            {
	                alert('Postcode lookup was not successful for the following reason: ' + status);
	            }
	        });

			break;
		}
	}
}

function addressSelected(address, prefix)
{
	var explodeAddress = address.split(",");

	var addressFields = new Array();
	for ( var j in explodeAddress )
	{
		var addressPart = explodeAddress[j].trim();
		if ( addressPart.trim() != '' )
		{
			// This is the first part, check if it contains a number first
			if ( j == 0 )
			{
				var explodeAddressPart = addressPart.split(/\s+/);
				var explodeAddressPart = [explodeAddressPart.shift(), explodeAddressPart.join(' ')];
				if ( jQuery.isNumeric(explodeAddressPart[0]) )
				{
					jQuery('.form-field._' + prefix + 'address_name_number_field input').val(explodeAddressPart[0]);
					jQuery('.form-field._' + prefix + 'address_street_field input').val(explodeAddressPart[1]);
				}
				else
				{
					jQuery('.form-field._' + prefix + 'address_street_field input').val(addressPart);
				}
			}
			else
			{
				addressFields.push(addressPart);
			}
		}
	}

	if ( addressFields.length > 0 )
	{
		for ( var i in addressFields )
		{
			if ( jQuery('.form-field._' + prefix + 'address_street_field input').val() == '' )
			{
				jQuery('.form-field._' + prefix + 'address_street_field input').val( addressFields[i] );
			}
			else if ( jQuery('.form-field._' + prefix + 'address_two_field input').val() == '' )
			{
				jQuery('.form-field._' + prefix + 'address_two_field input').val( addressFields[i] );
			}
			else if ( jQuery('.form-field._' + prefix + 'address_three_field input').val() == '' )
			{
				jQuery('.form-field._' + prefix + 'address_three_field input').val( addressFields[i] );
			}
			else if ( jQuery('.form-field._' + prefix + 'address_four_field input').val() == '' )
			{
				jQuery('.form-field._' + prefix + 'address_four_field input').val( addressFields[i] );
			}
		}
	}

	showAddressFields(prefix);
}

function showAddressFields(prefix)
{
	jQuery('.form-field._' + prefix + 'address_name_number_field').show();
	jQuery('.form-field._' + prefix + 'address_street_field').show();
	jQuery('.form-field._' + prefix + 'address_two_field').show();
	jQuery('.form-field._' + prefix + 'address_three_field').show();
	jQuery('.form-field._' + prefix + 'address_four_field').show();
	jQuery('.form-field._' + prefix + 'address_country_field').show();

	jQuery('.form-field._' + prefix + 'address_postcode_field input').attr( 'placeholder', '' );
	jQuery('.form-field._' + prefix + 'address_postcode_field span').remove();
	jQuery('.form-field._' + prefix + 'address_postcode_lookup_results_field').hide();
}