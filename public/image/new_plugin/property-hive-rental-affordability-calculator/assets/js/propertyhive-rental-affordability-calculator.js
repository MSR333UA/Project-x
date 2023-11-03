function ph_rac_add_commas(nStr)
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

function ph_rac_calculate()
{
	var calculation_basis = jQuery('#calculation_basis').val();

	jQuery('#results_rent').hide();
	jQuery('#results_income').hide();

	if (calculation_basis != '')
	{
		switch (calculation_basis)
		{
			case "rent":
			{
				var rent = jQuery('.rental-affordability-calculator input[name=\'rent\']').val().replace(/,/g, '');

				var tenant_income_result = ( rent * 12 ) * 2.5;
				var guarantor_income_result = ( rent * 12 ) * 3;

				jQuery('#results_rent_total').html( '&pound;' + ph_rac_add_commas(tenant_income_result.toFixed(2).replace(".00", "")) );
				jQuery('#results_rent_guarantor').html( '&pound;' + ph_rac_add_commas(guarantor_income_result.toFixed(2).replace(".00", "")) );

				jQuery('#results_rent').slideDown();

				break;
			}
			case "income":
			{
				var income = jQuery('.rental-affordability-calculator input[name=\'income\']').val().replace(/,/g, '');

				var tenant_rent_result = ( income / 2.5 ) / 12;
				var guarantor_rent_result = ( tenant_rent_result * 12 ) * 3;

				jQuery('#results_income_total').html( '&pound;' + ph_rac_add_commas(tenant_rent_result.toFixed(2).replace(".00", "")) );
				jQuery('#results_income_guarantor').html( '&pound;' + ph_rac_add_commas(guarantor_rent_result.toFixed(2).replace(".00", "")) );

				jQuery('#results_income').slideDown();
				break;
			}
		}
	}
}

jQuery(document).ready(function()
{
	jQuery("body").on('change', '#calculation_basis', function() 
    {
    	jQuery('#results_rent').hide();
		jQuery('#results_income').hide();

        jQuery('#from_rent').hide();
        jQuery('#from_income').hide();

        jQuery('#from_' + jQuery(this).val()).slideDown();
    });

	jQuery("body").on('click', '.rental-affordability-calculator button', function() 
	{
		ph_rac_calculate();
	});
});