var ph_mcc_buying_only_pages = ['buying-selling', 'stamp-duty', 'legal-costs', 'valuation-survey-costs', 'other-costs', 'mortgage-financial-advice', 'summary'];
var ph_mcc_selling_only_pages = ['buying-selling', 'selling-details', 'legal-costs', 'other-costs', 'financial-advice', 'summary'];
var ph_mcc_buying_and_selling_pages = ['buying-selling', 'stamp-duty', 'selling-details', 'legal-costs', 'valuation-survey-costs', 'other-costs', 'mortgage-financial-advice', 'financial-advice', 'summary'];

var ph_mcc_current_page = 'buying-selling';
var ph_mcc_current_page_list = new Array();

var ph_mcc_is_submitting = false;

jQuery(document).ready(function()
{
	jQuery('#mcc-page-legal-costs input[name=\'other_legal_costs\']').val(ph_mcc_other_legal_costs_buying);

	jQuery('#mcc-page-buying-selling [name=\'looking_to\']').change(function()
	{
		switch ( ph_mcc_get_looking_to() )
		{
			case "buy": 
			{ 
				jQuery('.sell-only').hide();
				jQuery('.buy-only').show();
				jQuery('#mcc-page-legal-costs input[name=\'other_legal_costs\']').val(ph_mcc_other_legal_costs_buying); 
				break; 
			}
			case "sell": 
			{
				jQuery('.sell-only').show();
				jQuery('.buy-only').hide();
				jQuery('#mcc-page-legal-costs input[name=\'other_legal_costs\']').val(ph_mcc_other_legal_costs_selling);
				break;
			}
			case "buy_sell":
			{
				jQuery('.sell-only').show();
				jQuery('.buy-only').show();
				jQuery('#mcc-page-legal-costs input[name=\'other_legal_costs\']').val(ph_mcc_other_legal_costs_buying);
				break;
			}
		}
	});

	jQuery('[id^=\'mcc-page-\'] a.back').click(function(e)
	{
		e.preventDefault();

		ph_mcc_transition_back_page();
	});

	jQuery('[id^=\'mcc-page-\'] a.next').click(function(e)
	{
		e.preventDefault();

		switch (ph_mcc_current_page)
		{
			case "buying-selling": { if (!ph_mcc_validate_page_buying_selling()) { return false; } break; }
			case "stamp-duty": { if (!ph_mcc_validate_page_stamp_duty()) { return false; } break; }
			case "selling-details": { if (!ph_mcc_validate_page_selling_details()) { return false; } break; }
			case "legal-costs": { if (!ph_mcc_validate_page_legal_costs()) { return false; } break; }
			case "valuation-survey-costs": { if (!ph_mcc_validate_page_valuation_survey_costs()) { return false; } break; }
			case "other-costs": { if (!ph_mcc_validate_page_other_costs()) { return false; } break; }
			case "mortgage-financial-advice": { if (!ph_mcc_validate_page_mortgage_financial_advice()) { return false; } break; }
			case "financial-advice": { if (!ph_mcc_validate_page_financial_advice()) { return false; } break; }
			default: {
				console.log('unknown page: ' + ph_mcc_current_page);
				return false;
			}
		}

		ph_mcc_transition_next_page();
	});

	jQuery('#mcc-page-valuation-survey-costs select[name=\'valuation_type\']').change(function()
	{
		switch (jQuery(this).val())
		{
			case "none":
			{
				jQuery('[name=\'valuation_survey_cost\']').hide();
				jQuery('#valuation_survey_cost_fixed').show();
				break;
			}
			case "mv":
			{
				jQuery('[name=\'valuation_survey_cost\']').val('0').show();
				jQuery('#valuation_survey_cost_fixed').hide();
				break;
			}
			case "hbr":
			{
				jQuery('[name=\'valuation_survey_cost\']').val(ph_mcc_homebuyer_cost).show();
				jQuery('#valuation_survey_cost_fixed').hide();
				break;
			}
			case "full":
			{
				jQuery('[name=\'valuation_survey_cost\']').val(ph_mcc_full_survey_cost).show();
				jQuery('#valuation_survey_cost_fixed').hide();
				break;
			}
			default: {
				alert('Unknown valuation type selected');
			}
		}
	});

	jQuery('[id^=\'mcc-page-\'] select[name=\'financing_type\']').change(function()
	{
		jQuery('#cash_content').hide();
		jQuery('#mortgage_content').hide();

		jQuery('#' + jQuery(this).val() + '_content').show();

		if ( jQuery(this).val() == 'mortgage' )
		{
			jQuery('.mcc-requested-quotes-table-row-mortgage-advisor').show();
		}
		else
		{
			jQuery('.mcc-requested-quotes-table-row-mortgage-advisor').hide();
		}
	});

	jQuery('#mcc-page-selling-details input[name=\'selling_price\']').keyup(function()
	{
		ph_mcc_calculate_agency_fees();
	});

	jQuery('.propertyhive-moving-cost-calculator input[type=\'submit\']').click(function(e)
	{
		e.preventDefault();

		if ( jQuery('.propertyhive-moving-cost-calculator input[name=\'name\']').val() == '' || jQuery('.propertyhive-moving-cost-calculator input[name=\'email_address\']').val() == '' || jQuery('.propertyhive-moving-cost-calculator input[name=\'telephone_number\']').val() == '' )
		{
			alert('Please ensure a name, email address and telephone number have been entered.');
			return false;
		}

		var third_parties = new Array();
        third_parties['solicitors'] = new Array();
        third_parties['surveyors'] = new Array();
        third_parties['removal_companies'] = new Array();
        third_parties['mortgage_advisors'] = new Array();
        third_parties['financial_advisors'] = new Array();
        var third_parties_ticked = 0;
        jQuery('input[name=\'requested_contact_solicitors[]\']:checked').each(function()
        {
        	third_parties['solicitors'].push(parseInt(jQuery(this).val()));
        	third_parties_ticked = third_parties_ticked + 1;
        });
        jQuery('input[name=\'requested_contact_surveyors[]\']:checked').each(function()
        {
        	third_parties['surveyors'].push(parseInt(jQuery(this).val()));
        	third_parties_ticked = third_parties_ticked + 1;
        });
        jQuery('input[name=\'requested_contact_removal_companies[]\']:checked').each(function()
        {
        	third_parties['removal_companies'].push(parseInt(jQuery(this).val()));
        	third_parties_ticked = third_parties_ticked + 1;
        });
        jQuery('input[name=\'requested_contact_mortgage_advisors[]\']:checked').each(function()
        {
        	third_parties['mortgage_advisors'].push(parseInt(jQuery(this).val()));
        	third_parties_ticked = third_parties_ticked + 1;
        });
        jQuery('input[name=\'requested_contact_financial_advisors[]\']:checked').each(function()
        {
        	third_parties['financial_advisors'].push(parseInt(jQuery(this).val()));
        	third_parties_ticked = third_parties_ticked + 1;
        });

        if ( third_parties_ticked == 0 )
		{
			alert('Please select at least one company to contact and/or request a quote from.');
			return false;
		}

		if (!ph_mcc_is_submitting)
        {
            ph_mcc_is_submitting = true;

            jQuery('.propertyhive-moving-cost-calculator #movingCostCalculatorSuccess').hide();
            jQuery('.propertyhive-moving-cost-calculator #movingCostCalculatorValidation').hide();

			var data = {
		        'action': 'propertyhive_request_moving_quotes',
		        'name': jQuery('.propertyhive-moving-cost-calculator input[name=\'name\']').val(),
		        'email_address': jQuery('.propertyhive-moving-cost-calculator input[name=\'email_address\']').val(),
		        'telephone_number': jQuery('.propertyhive-moving-cost-calculator input[name=\'telephone_number\']').val(),
		        'message': jQuery('.propertyhive-moving-cost-calculator textarea[name=\'message\']').val(),
		        'third_parties': JSON.stringify({
		        	'solicitors':third_parties['solicitors'],
		        	'surveyors':third_parties['surveyors'],
		        	'removal_companies':third_parties['removal_companies'],
		        	'mortgage_advisors':third_parties['mortgage_advisors'],
		        	'financial_advisors':third_parties['financial_advisors']
		        })
		    };

			jQuery.post(ajax_object.ajax_url, data, function( response ) 
		    {
		        if (response.success == true)
                {
                    jQuery('.propertyhive-moving-cost-calculator #movingCostCalculatorSuccess').fadeIn();

                    jQuery('.propertyhive-moving-cost-calculator input[name=\'name\']').val('');
                    jQuery('.propertyhive-moving-cost-calculator input[name=\'email_address\']').val('');
                    jQuery('.propertyhive-moving-cost-calculator input[name=\'telephone_number\']').val('');
                    jQuery('.propertyhive-moving-cost-calculator input[name=\'message\']').val('');
                }
                else if (response.success == false && response.reason == 'validation')
                {
                    jQuery('.propertyhive-moving-cost-calculator #movingCostCalculatorValidation').html(response.errors.join(', ')).fadeIn();
                }

		    	ph_mcc_is_submitting = false;
		    }, 'json');

		}
	});
	

	ph_mcc_calculate_agency_fees();
});

function ph_mcc_calculate_agency_fees()
{
	// TO DO : Cater for flat fees
	var selling_price = jQuery('#mcc-page-selling-details input[name=\'selling_price\']').val().replace(/,/g, '');
	var fees = (ph_mcc_agency_fees_percentage * selling_price) / 100;
	/*if ( fees < 2000 ) // removed minimum fee
	{
		fees = 2000;
	}*/
	var vat = (20 * fees) / 100;
	fees = fees + vat;
	jQuery('#mcc-page-selling-details #estate_agency_fees').html( '&pound;' + ph_mcc_add_commas(Math.round(fees)) );

	return fees;
}

function ph_mcc_get_looking_to()
{
	var looking_to = '';

	var looking_to_el = jQuery('[name=\'looking_to\']');
    if (looking_to_el.length > 0)
    {
        switch (looking_to_el.prop('tagName').toLowerCase())
        {
            case "select":
            {
                var looking_to = looking_to_el.val();
                break;
            }
            default:
            {
                if ( looking_to_el.attr('type') == 'hidden' )
                {
                    var looking_to = looking_to_el.val();
                }
                else
                {
                    var looking_to = looking_to_el.filter(':checked').val();
                }
            }
        }
    }

    if ( looking_to == '' )
    {
    	alert('No looking to found. Need this for the rest of the calculator');
    	return false;
    }

	return looking_to;
}

function ph_mcc_validate_page_buying_selling()
{
	// Make sure one of the looking_to options is selected
	var looking_to = ph_mcc_get_looking_to();
	if ( looking_to == '' )
	{
		// throw error
		alert('No option selected');
		return false;
	}

	switch ( looking_to )
	{
		case "buy": { ph_mcc_current_page_list = ph_mcc_buying_only_pages; break; }
		case "sell": { ph_mcc_current_page_list = ph_mcc_selling_only_pages; break; }
		case "buy_sell": { ph_mcc_current_page_list = ph_mcc_buying_and_selling_pages; break; }
	}

	return true;
}

function ph_mcc_validate_page_stamp_duty()
{
	var purchase_price = jQuery('#mcc-page-stamp-duty input[name=\'purchase_price\']').val().replace(/,/g, '');
	if ( purchase_price == '' )
	{
		// throw error
		alert('No purchase price entered');
		return false;
	}

	return true;
}

function ph_mcc_validate_page_selling_details()
{
	var purchase_price = jQuery('#mcc-page-selling-details input[name=\'selling_price\']').val().replace(/,/g, '');
	if ( purchase_price == '' )
	{
		// throw error
		alert('No selling price entered');
		return false;
	}

	return true;
}

function ph_mcc_validate_page_legal_costs()
{

	return true;
}

function ph_mcc_validate_page_valuation_survey_costs()
{

	return true;
}

function ph_mcc_validate_page_other_costs()
{

	return true;
}

function ph_mcc_validate_page_mortgage_financial_advice()
{

	return true;
}

function ph_mcc_validate_page_financial_advice()
{

	return true;
}

function ph_mcc_transition_next_page()
{
	ph_mcc_calculate();

	var page_to_hide = '';
	var page_to_show = '';

	for ( var i in ph_mcc_current_page_list )
	{
		if ( page_to_hide != '' )
		{
			page_to_show = ph_mcc_current_page_list[i];
			break;
		}
		if ( ph_mcc_current_page == ph_mcc_current_page_list[i] )
		{
			page_to_hide = ph_mcc_current_page;
		}
	}
	jQuery('#mcc-page-' + page_to_hide).fadeOut('fast', function()
	{
		jQuery('#mcc-page-' + page_to_show).fadeIn();
	});
	ph_mcc_current_page = page_to_show;

	if (typeof ph_mcc_transitioned_page == 'function') { // make sure the callback is a function
        ph_mcc_transitioned_page(); // brings the scope to the callback
    }
}

function ph_mcc_transition_back_page()
{
	var page_to_hide = '';
	var page_to_show = '';
	for ( var i in ph_mcc_current_page_list )
	{
		if ( ph_mcc_current_page == ph_mcc_current_page_list[i] )
		{
			page_to_hide = ph_mcc_current_page;
			break;
		}
		page_to_show = ph_mcc_current_page_list[i];
	}
	jQuery('#mcc-page-' + page_to_hide).fadeOut('fast', function()
	{
		jQuery('#mcc-page-' + page_to_show).fadeIn();
	});
	ph_mcc_current_page = page_to_show;

	if (typeof ph_mcc_transitioned_page == 'function') { // make sure the callback is a function
        ph_mcc_transitioned_page(); // brings the scope to the callback
    }
}

function ph_mcc_add_commas(nStr)
{
	nStr += '';
	x = nStr.split('.');
	x1 = x[0];
	x2 = x.length > 1 ? '.' + x[1] : '';
	var rgx = /(\d+)(\d{3})/;
	while (rgx.test(x1)) {
		x1 = x1.replace(rgx, '$1' + ',' + '$2');
	}
	return x1 + x2;
}

function ph_mcc_calculate()
{
	// Summary page
	jQuery('tr[id^=\'mcc-summary-table-row-\']').hide();

	if ( ph_mcc_get_looking_to() == 'buy' || ph_mcc_get_looking_to() == 'buy_sell' )
	{	
		var price = jQuery('#mcc-page-stamp-duty input[name=\'purchase_price\']').val().replace(/,/g, '');

		var bands = [
            { min: 0, max: 125000, pct: 0 },
            { min: 125000, max: 250000, pct: 0.02 },
            { min: 250000, max: 925000, pct: 0.05 },
            { min: 925000, max: 1500000, pct: 0.1 },
            { min: 1500000, max: null, pct: 0.12 }
        ];

        if ( jQuery('input[name=\'btl_second\']').is(':checked') ) 
        {
            bands = [
                { min: 0, max: 125000, pct: 0.03 },
                { min: 125000, max: 250000, pct: 0.05 },
                { min: 250000, max: 925000, pct: 0.08 },
                { min: 925000, max: 1500000, pct: 0.13 },
                { min: 1500000, max: null, pct: 0.15 }
            ];
        }

        var number_bands = bands.length;
        var stamp_duty = 0;

        for (var i = 0; i < number_bands; ++i)
        {
            var band = bands[i];
            var max = price;
            if (band.max != null)
            {
                max = Math.min(band.max, max);
            }
            else
            {

            }
            var taxable_sum = Math.max(0, max - band.min);
            var tax = taxable_sum * band.pct;
            stamp_duty += tax;
        }

		jQuery('#mcc-summary-table-row-stamp-duty span').html( '&pound;' + ph_mcc_add_commas(Math.round(stamp_duty)) );
		jQuery('#mcc-summary-table-row-stamp-duty').show();
	}

	if ( ph_mcc_get_looking_to() == 'sell' || ph_mcc_get_looking_to() == 'buy_sell' )
	{
		jQuery('#mcc-summary-table-row-agency-fees span').html( '&pound;' + ph_mcc_add_commas(Math.round(ph_mcc_calculate_agency_fees())) );
		jQuery('#mcc-summary-table-row-agency-fees').show();
	}

	// legal costs - conveyancing costs
	var conveyancing_cost_buying = 0;
	var conveyancing_cost_selling = 0;

	if ( ph_mcc_get_looking_to() == 'buy' || ph_mcc_get_looking_to() == 'buy_sell' )
	{
		var price = jQuery('#mcc-page-stamp-duty input[name=\'purchase_price\']').val().replace(/,/g, '');
		var conveyancing_cost_buying = (ph_mcc_conveyancing_percentage_buying * price) / 100;

		if ( conveyancing_cost_buying < ph_mcc_conveyancing_cost_minimum )
		{
			conveyancing_cost_buying = ph_mcc_conveyancing_cost_minimum;
		}
		if ( conveyancing_cost_buying > ph_mcc_conveyancing_cost_maximum )
		{
			conveyancing_cost_buying = ph_mcc_conveyancing_cost_maximum;
		}

		jQuery('#mcc-page-legal-costs #conveyancing_costs_buying').html( '&pound;' + ph_mcc_add_commas(Math.round(conveyancing_cost_buying)) );

		jQuery('#mcc-summary-table-row-conveyancing-buying span').html( '&pound;' + ph_mcc_add_commas(Math.round(conveyancing_cost_buying)) );
		jQuery('#mcc-summary-table-row-conveyancing-buying').show();
	}
	if ( ph_mcc_get_looking_to() == 'sell' || ph_mcc_get_looking_to() == 'buy_sell' )
	{
		var price = jQuery('#mcc-page-selling-details input[name=\'selling_price\']').val().replace(/,/g, '');
		var conveyancing_cost_selling = (ph_mcc_conveyancing_percentage_selling * price) / 100;

		if ( conveyancing_cost_selling < ph_mcc_conveyancing_cost_minimum )
		{
			conveyancing_cost_selling = ph_mcc_conveyancing_cost_minimum;
		}
		if ( conveyancing_cost_selling > ph_mcc_conveyancing_cost_maximum )
		{
			conveyancing_cost_selling = ph_mcc_conveyancing_cost_maximum;
		}

		jQuery('#mcc-page-legal-costs #conveyancing_costs_selling').html( '&pound;' + ph_mcc_add_commas(Math.round(conveyancing_cost_selling)) );

		jQuery('#mcc-summary-table-row-conveyancing-selling span').html( '&pound;' + ph_mcc_add_commas(Math.round(conveyancing_cost_selling)) );
		jQuery('#mcc-summary-table-row-conveyancing-selling').show();
	}
	
	var other_legal = jQuery('#mcc-page-legal-costs input[name=\'other_legal_costs\']').val().replace(/,/g, '');
	if ( other_legal == '' ) { other_legal = 0; }
	jQuery('#mcc-summary-table-row-other-legal span').html( '&pound;' + ph_mcc_add_commas(other_legal) );
	jQuery('#mcc-summary-table-row-other-legal').show();

	if ( ph_mcc_get_looking_to() == 'buy' || ph_mcc_get_looking_to() == 'buy_sell' )
	{
		var valuation_survey = 0;
		if ( jQuery('#mcc-page-valuation-survey-costs select[name=\'valuation_type\']').val() != '' )
		{
			valuation_survey = jQuery('[name=\'valuation_survey_cost\']').val().replace(/,/g, '');
			if ( valuation_survey == '' ) { valuation_survey = 0; }
		}
		jQuery('#mcc-summary-table-row-valuation-survey span').html( '&pound;' + ph_mcc_add_commas(valuation_survey) );
		jQuery('#mcc-summary-table-row-valuation-survey').show();
	}

	var removal_storage = jQuery('[name=\'removal_storage_costs\']').val().replace(/,/g, '');
	if ( removal_storage == '' ) { removal_storage = 0; }
	jQuery('#mcc-summary-table-row-removal-storage span').html( '&pound;' + ph_mcc_add_commas(removal_storage) );
	jQuery('#mcc-summary-table-row-removal-storage').show();

	var other_moving = jQuery('[name=\'other_moving_costs\']').val().replace(/,/g, '');
	if ( other_moving == '' ) { other_moving = 0; }
	jQuery('#mcc-summary-table-row-other-moving span').html( '&pound;' + ph_mcc_add_commas(other_moving) );
	jQuery('#mcc-summary-table-row-other-moving').show();

	if ( ph_mcc_get_looking_to() == 'buy' || ph_mcc_get_looking_to() == 'buy_sell' )
	{
		var mortgage_advice = 0;
		if ( jQuery('[name=\'financing_type\']').val() == 'mortgage' && jQuery('input[name=\'mortgage_advisors[]\']:checked').length > 0 )
		{
			// get highest arrangement fee of selected mortgage advisors
			jQuery('input[name=\'mortgage_advisors[]\']:checked').each(function()
			{
				for ( var i in ph_mcc_mortgage_advisors )
				{
					if ( i == jQuery(this).val() && ph_mcc_mortgage_advisors[i].arrangement_fee != '' )
					{
						var arrangement_fee = ph_mcc_mortgage_advisors[i].arrangement_fee.replace(/,/g, '');
						if ( arrangement_fee > mortgage_advice )
						{
							mortgage_advice = arrangement_fee;
						}
					}
				}
			});

			if ( mortgage_advice == '' ) { mortgage_advice = 0; }
			jQuery('#mcc-summary-table-row-mortgage-advice span').html( '&pound;' + ph_mcc_add_commas(mortgage_advice) );
			jQuery('#mcc-summary-table-row-mortgage-advice').show();
		}
	}

	var buying_total = 0;
	var selling_total = 0;
	if ( ph_mcc_get_looking_to() == 'buy' || ph_mcc_get_looking_to() == 'buy_sell' )
	{
		buying_total = parseFloat(stamp_duty) + parseFloat(conveyancing_cost_buying) + parseFloat(other_legal) + parseFloat(valuation_survey) + parseFloat(removal_storage) + parseFloat(other_moving) + parseFloat(mortgage_advice);
	}
	if ( ph_mcc_get_looking_to() == 'sell' || ph_mcc_get_looking_to() == 'buy_sell' )
	{
		selling_total = parseFloat(ph_mcc_calculate_agency_fees()) + parseFloat(conveyancing_cost_selling) + parseFloat(other_legal) + parseFloat(removal_storage) + parseFloat(other_moving);
	}
	var total = buying_total + selling_total;

	jQuery('#mcc-summary-table-row-total span').html( '&pound;' + ph_mcc_add_commas(Math.round(total)) );
	jQuery('#mcc-summary-table-row-total').show();
	
	// Requested Quotes table
	jQuery('#mcc-page-legal-costs input[name=\'solicitors[]\']').each(function()
	{
		jQuery('[name=\'requested_contact_solicitors[]\'][value=\'' + jQuery(this).val() + '\']').prop('checked', jQuery(this).is(':checked') );
	});
	jQuery('#mcc-page-valuation-survey-costs input[name=\'surveyors[]\']').each(function()
	{
		jQuery('[name=\'requested_contact_surveyors[]\'][value=\'' + jQuery(this).val() + '\']').prop('checked', jQuery(this).is(':checked') );
	});
	jQuery('#mcc-page-other-costs input[name=\'removal_companies[]\']').each(function()
	{
		jQuery('[name=\'requested_contact_removal_companies[]\'][value=\'' + jQuery(this).val() + '\']').prop('checked', jQuery(this).is(':checked') );
	});
	jQuery('#mcc-page-mortgage-financial-advice input[name=\'mortgage_advisors[]\']').each(function()
	{
		jQuery('[name=\'requested_contact_mortgage_advisors[]\'][value=\'' + jQuery(this).val() + '\']').prop('checked', jQuery(this).is(':checked') );
	});
	jQuery('#mcc-page-financial-advice input[name=\'financial_advisors[]\']').each(function()
	{
		jQuery('[name=\'requested_contact_financial_advisors[]\'][value=\'' + jQuery(this).val() + '\']').prop('checked', jQuery(this).is(':checked') );
	});

	if (typeof ph_mcc_calculated == 'function') { // make sure the callback is a function
        ph_mcc_calculated(); // brings the scope to the callback
    }
}