<?php
/**
 * The template for displaying the moving cost calculator form and results
 *
 * Override this template by copying it to yourtheme/propertyhive/moving-cost-calculator.php
 *
 * NOTE: For the calculation to still occur it's important that most classes, ids and input names remain unchanged
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

<div class="propertyhive-moving-cost-calculator propertyhive-form">

<!-- Buying and/or selling -->
<div id="mcc-page-buying-selling">

	<h2>Buying or Selling</h2>

	<?php
		$fields = array();

		$fields['looking_to'] = array(
	        'type' => 'select',
	        'show_label' => true, 
	        'label' => __( 'Are you looking to', 'propertyhive' ),
	        'required' => true,
	        'options' => array(
	        	'buy' => 'Buy Only',
	        	'sell' => 'Sell Only',
	        	'buy_sell' => 'Buy and Sell',
	        ),
	    );

	    foreach ( $fields as $key => $field )
	    {
	    	ph_form_field( $key, $field );
	    }
	?>

	<br>

	<div class="buttons">
		<a href="" class="button next">Next</a>
	</div>

</div>


<!-- Stamp Duty (Buying only) -->
<div id="mcc-page-stamp-duty" style="display:none">

	<h2>Stamp Duty Costs</h2>

	<?php
		$fields = array();

		$fields['purchase_price'] = array(
	        'type' => 'number',
	        'show_label' => true, 
	        'label' => __( 'Price of property you want to buy (&pound;)', 'propertyhive' ),
	        'required' => true,
	    );

	    $fields['btl_second'] = array(
	        'type' => 'checkbox',
	        'show_label' => true,
	        'before' => '<div class="control control-btl_second"><label></label>',
	        'label' => __( 'Property is a buy-to-let or second home', 'propertyhive' ),
	    );

	    foreach ( $fields as $key => $field )
	    {
	    	ph_form_field( $key, $field );
	    }
	?>
	<br>
	<div class="buttons">
		<a href="" class="button back">Back</a>
		<a href="" class="button next">Next</a>
	</div>

</div>

<!-- Selling Details (Selling only) -->
<div id="mcc-page-selling-details" style="display:none">

	<h2>Selling</h2>

	<?php
		$fields = array();

		$fields['selling_price'] = array(
	        'type' => 'number',
	        'show_label' => true, 
	        'label' => __( 'Price of property you are selling (&pound;)', 'propertyhive' ),
	        'required' => true,
	    );

	    $fields['estate_agency_fees'] = array(
	        'type' => 'html',
	        'html' => '<label>Estate Agency Fees (' . ( ( isset($current_moving_cost_calculator_options['agency_fees_percentage']) && $current_moving_cost_calculator_options['agency_fees_percentage'] != '' ) ? $current_moving_cost_calculator_options['agency_fees_percentage'] : 1.5 ) . '% inc VAT)</label><span id="estate_agency_fees"></span>'
	    );

	    foreach ( $fields as $key => $field )
	    {
	    	ph_form_field( $key, $field );
	    }
	?>
	<br>
	<div class="buttons">
		<a href="" class="button back">Back</a>
		<a href="" class="button next">Next</a>
	</div>

</div>

<!-- Legal costs (Buying and selling) -->
<div id="mcc-page-legal-costs" style="display:none">

	<h2>Legal Costs</h2>

	<?php
		$fields = array();

	    $fields['conveyancing_costs_buying'] = array(
	        'type' => 'html',
	        'before' => '<div class="control control-conveyancing_costs_buying buy-only">',
	        'html' => '<label>Conveyancing Costs (Buying)</label><span id="conveyancing_costs_buying"></span>'
	    );

	    $fields['conveyancing_costs_selling'] = array(
	        'type' => 'html',
	        'before' => '<div class="control control-conveyancing_costs_buying sell-only" style="display:none;">',
	        'html' => '<label>Conveyancing Costs (Selling)</label><span id="conveyancing_costs_selling"></span>'
	    );

	    $fields['other_legal_costs'] = array(
	        'type' => 'number',
	        'show_label' => true, 
	        'label' => __( 'Other Legal Costs (&pound;)', 'propertyhive' ),
	        'required' => false,
	    );

	    foreach ( $fields as $key => $field )
	    {
	    	ph_form_field( $key, $field );
	    }
	?>

	<?php
		if ( !empty($solicitors) )
		{
	?>
	<p>Would you like a detailed quote from the following respected local solicitors?</p>

	<?php
		foreach ($solicitors as $i => $solicitor)
		{
			echo '<div>
				<label>
					<input type="checkbox" name="solicitors[]" value="' . $i . '">
					' . $solicitor['name'] . '
				</label>
			</div>';
		}
	?>
	<?php
		}
	?>
	<br>
	<div class="buttons">
		<a href="" class="button back">Back</a>
		<a href="" class="button next">Next</a>
	</div>

</div>

<!-- Valuation/Survey Costs (Buying only) -->
<div id="mcc-page-valuation-survey-costs" style="display:none">

	<h2>Valuation/Survey Costs</h2>

	<?php
		$fields = array();

		$fields['valuation_type'] = array(
	        'type' => 'select',
	        'show_label' => true, 
	        'label' => __( 'Type of valuation', 'propertyhive' ),
	        'required' => true,
	        'options' => array(
	        	'none' => 'None',
	        	'mv' => 'Mortgage Valuation only',
	        	'hbr' => 'Home Buyer Report including Mortgage Valuation',
	        	'full' => 'Full Survey (including Mortgage Valuation)',
	        ),
	    );

	    $fields['valuation_survey_cost'] = array(
	        'type' => 'html',
	        'before' => '<div class="control control-valuation_survey_cost">',
	        'html' => '<label>Valuation/Survey Cost (&pound;)</label><input type="number" name="valuation_survey_cost" style="display:none"><span id="valuation_survey_cost_fixed">0</span>'
	    );

	    foreach ( $fields as $key => $field )
	    {
	    	ph_form_field( $key, $field );
	    }
	?>

	<?php
		if ( !empty($surveyors) )
		{
	?>
	<p>Would you like a detailed quote from the following respected local, independent surveyors?</p>

	<?php
		foreach ($surveyors as $i => $surveyor)
		{
			echo '<div>
				<label>
					<input type="checkbox" name="surveyors[]" value="' . $i . '">
					' . $surveyor['name'] . '
				</label>
			</div>';
		}
	?>
	<?php
		}
	?>
	<br>
	<div class="buttons">
		<a href="" class="button back">Back</a>
		<a href="" class="button next">Next</a>
	</div>

</div>

<!-- Other Costs (Buying only) -->
<div id="mcc-page-other-costs" style="display:none">

	<h2>Other Costs</h2>

	<?php
		$fields = array();

	    $fields['removal_storage_costs'] = array(
	        'type' => 'number',
	        'show_label' => true, 
	        'label' => __( 'Removal/Storage Costs (&pound;)', 'propertyhive' ),
	        'value' => ( ( isset($current_moving_cost_calculator_options['removal_storage_costs']) && $current_moving_cost_calculator_options['removal_storage_costs'] != '' ) ? $current_moving_cost_calculator_options['removal_storage_costs'] : '' ),
	        'required' => false,
	    );

	    foreach ( $fields as $key => $field )
	    {
	    	ph_form_field( $key, $field );
	    }
	?>

	<?php
		if ( !empty($removal_companies) )
		{
	?>
	<p>Would you like a detailed quote from the following respected local, independent removal and storage companies?</p>

	<?php
		foreach ($removal_companies as $i => $removal_company)
		{
			echo '<div>
				<label>
					<input type="checkbox" name="removal_companies[]" value="' . $i . '">
					' . $removal_company['name'] . '
				</label>
			</div>';
		}
	?>
	<?php
		}
	?>
	<br>
	<?php
		$fields = array();

	    $fields['other_moving_costs'] = array(
	        'type' => 'number',
	        'show_label' => true, 
	        'label' => __( 'Other Moving Costs (&pound;)', 'propertyhive' ),
	        'value' => ( ( isset($current_moving_cost_calculator_options['other_moving_costs']) && $current_moving_cost_calculator_options['other_moving_costs'] != '' ) ? $current_moving_cost_calculator_options['other_moving_costs'] : 200 ),
	        'after' => '<p class="description">e.g. gas service, electrical check, connecting domestic services, settling outstanding fees/changes</p></div>',
	        'required' => false,
	    );

	    foreach ( $fields as $key => $field )
	    {
	    	ph_form_field( $key, $field );
	    }
	?>
	
	<br>
	<div class="buttons">
		<a href="" class="button back">Back</a>
		<a href="" class="button next">Next</a>
	</div>

</div>

<!-- Mortgage/Financial Advice (Buying only) -->
<div id="mcc-page-mortgage-financial-advice" style="display:none">

	<h2>Mortgage Financial Advice</h2>

	<?php
		$fields = array();

		$fields['financing_type'] = array(
	        'type' => 'select',
	        'show_label' => true, 
	        'label' => __( 'How will you be financing your purchase', 'propertyhive' ),
	        'required' => true,
	        'options' => array(
	        	'cash' => 'Full cash',
	        	'mortgage' => 'Mortgage with a deposit',
	        ),
	    );

	    foreach ( $fields as $key => $field )
	    {
	    	ph_form_field( $key, $field );
	    }
	?>

	<div id="mortgage_content" style="display:none;">

		<?php
			if ( !empty($mortgage_advisors) )
			{
		?>
		<p>Would you like to receive sound, independent mortgage advice from the following respected mortgage advisors/companies?</p>

		<?php
			foreach ($mortgage_advisors as $i => $mortgage_advisor)
			{
				echo '<div>
					<label>
						<input type="checkbox" name="mortgage_advisors[]" value="' . $i . '">
						' . $mortgage_advisor['name'] . '
					</label>
				</div>';
			}
		?>
		<?php
			}
		?>

	</div>
	<br>
	<div class="buttons">
		<a href="" class="button back">Back</a>
		<a href="" class="button next">Next</a>
	</div>

</div>

<!-- Financial Advice (Selling only) -->
<div id="mcc-page-financial-advice" style="display:none">

	<h2>Financial Advice</h2>

	<p>As part of our service, we act as introducers to reputable, local, independent financial advisors.</p>

	<?php
		if ( !empty($financial_advisors) )
		{
	?>
	<p>Would you like to receive sound, independent financial advice from:</p>

	<?php
		foreach ($financial_advisors as $i => $financial_advisor)
		{
			echo '<div>
				<label>
					<input type="checkbox" name="financial_advisors[]" value="' . $i . '">
					' . $financial_advisor['name'] . '
				</label>
			</div>';
		}
	?>
	<?php
		}
	?>
	<br>
	<div class="buttons">
		<a href="" class="button back">Back</a>
		<a href="" class="button next">Next</a>
	</div>

</div>

<!-- Summary -->
<div id="mcc-page-summary" style="display:none">

	<h2>Your Estimated Moving Costs</h2>

	<table width="100%" cellpadding="3" cellspacing="0" border="0" id="mcc-summary-table">
		<tr>
			<td><strong>Type of Cost</strong></td>
			<td><strong>Cost</strong></td>
		</tr>
		<tr id="mcc-summary-table-row-stamp-duty">
			<td>Stamp Duty</td>
			<td><span></span></td>
		</tr>
		<tr id="mcc-summary-table-row-agency-fees">
			<td>Estate Agency Fees</td>
			<td><span></span></td>
		</tr>
		<tr id="mcc-summary-table-row-conveyancing-buying">
			<td>Conveyancing Costs (Buying)</td>
			<td><span></span></td>
		</tr>
		<tr id="mcc-summary-table-row-conveyancing-selling">
			<td>Conveyancing Costs (Selling)</td>
			<td><span></span></td>
		</tr>
		<tr id="mcc-summary-table-row-other-legal">
			<td>Other Legal Costs</td>
			<td><span></span></td>
		</tr>
		<tr id="mcc-summary-table-row-valuation-survey">
			<td>Valuation/Survey Costs</td>
			<td><span></span></td>
		</tr>
		<tr id="mcc-summary-table-row-removal-storage">
			<td>Removal/Storage Costs</td>
			<td><span></span></td>
		</tr>
		<tr id="mcc-summary-table-row-other-moving">
			<td>Other Moving Costs</td>
			<td><span></span></td>
		</tr>
		<tr id="mcc-summary-table-row-mortgage-advice">
			<td>Independent Mortgage Advice</td>
			<td><span></span></td>
		</tr>
		<tr id="mcc-summary-table-row-total">
			<td><strong>Total Estimated Moving Costs</strong></td>
			<td><strong><span></span></strong></td>
		</tr>
	</table>

	<h2>Requested Quotes and Contact</h2>

	<table width="100%" cellpadding="3" cellspacing="0" border="0" id="mcc-requested-quotes-table">
		<tr>
			<td><strong>Service</strong></td>
			<td><strong>Company</strong></td>
			<td style="text-align:center"><strong>Selected</strong></td>
		</tr>
		<?php
			$j = 0;
			foreach ( $solicitors as $i => $solicitor )
			{
		?>
		<tr>
			<td><?php echo ( ( $j == 0 ) ? 'Conveyancing' : '&nbsp;' ); ?></td>
			<td><?php echo $solicitor['name']; ?></td>
			<td style="text-align:center"><input type="checkbox" name="requested_contact_solicitors[]" value="<?php echo $i; ?>"></td>
		</tr>
		<?php
				++$j;
			}
		?>
		<?php
			$j = 0;
			foreach ( $surveyors as $i => $surveyor )
			{
		?>
		<tr>
			<td><?php echo ( ( $j == 0 ) ? 'Survey' : '&nbsp;' ); ?></td>
			<td><?php echo $surveyor['name']; ?></td>
			<td style="text-align:center"><input type="checkbox" name="requested_contact_surveyors[]" value="<?php echo $i; ?>"></td>
		</tr>
		<?php
				++$j;
			}
		?>
		<?php
			$j = 0;
			foreach ( $removal_companies as $i => $removal_company )
			{
		?>
		<tr>
			<td><?php echo ( ( $j == 0 ) ? 'Removals/Storage' : '&nbsp;' ); ?></td>
			<td><?php echo $removal_company['name']; ?></td>
			<td style="text-align:center"><input type="checkbox" name="requested_contact_removal_companies[]" value="<?php echo $i; ?>"></td>
		</tr>
		<?php
				++$j;
			}
		?>
		<?php
			$j = 0;
			foreach ( $mortgage_advisors as $i => $mortgage_advisor )
			{
		?>
		<tr class="mcc-requested-quotes-table-row-mortgage-advisor" style="display:none">
			<td><?php echo ( ( $j == 0 ) ? 'Independent Mortgage Advice' : '&nbsp;' ); ?></td>
			<td><?php echo $mortgage_advisor['name']; ?></td>
			<td style="text-align:center"><input type="checkbox" name="requested_contact_mortgage_advisors[]" value="<?php echo $i; ?>"></td>
		</tr>
		<?php
				++$j;
			}
		?>
		<?php
			$j = 0;
			foreach ( $financial_advisors as $i => $financial_advisor )
			{
		?>
		<tr class="mcc-requested-quotes-table-row-financial-advisor">
			<td><?php echo ( ( $j == 0 ) ? 'Independent Financial Advice' : '&nbsp;' ); ?></td>
			<td><?php echo $financial_advisor['name']; ?></td>
			<td style="text-align:center"><input type="checkbox" name="requested_contact_financial_advisors[]" value="<?php echo $i; ?>"></td>
		</tr>
		<?php
				++$j;
			}
		?>
	</table>

	<br>

	<p>Before the selected companies can contact you, please can you provide us with the following information:</p>

	<div id="request-quote-form">

		<div id="movingCostCalculatorSuccess" style="display:none;" class="alert alert-success alert-box success">
		    <?php _e( 'Thank you. Your contact/quote requests have been sent succesfully.', 'propertyhive' ); ?>
		</div>
		<div id="movingCostCalculatorValidation" style="display:none;" class="alert alert-danger alert-box">
		    <?php _e( 'Please ensure all required fields have been completed', 'propertyhive' ); ?>
		</div>

		<?php
			$fields = array();

			$fields['name'] = array(
		        'type' => 'text',
		        'show_label' => true, 
		        'label' => __( 'Full Name', 'propertyhive' ),
		        'required' => true, 
		    );

		    $fields['email_address'] = array(
		        'type' => 'text',
		        'show_label' => true, 
		        'label' => __( 'Email Address', 'propertyhive' ),
		        'required' => true, 
		    );

		    $fields['telephone_number'] = array(
		        'type' => 'text',
		        'show_label' => true, 
		        'label' => __( 'Telephone Number', 'propertyhive' ),
		        'required' => true, 
		    );

		    $fields['message'] = array(
		        'type' => 'textarea',
		        'show_label' => true, 
		        'label' => __( 'Additional Information', 'propertyhive' ),
		    );

		    foreach ( $fields as $key => $field )
		    {
		    	ph_form_field( $key, $field );
		    }
		?>

		<input type="submit" class="button" value="Request Quotes">

	</div>

</div>

</div>