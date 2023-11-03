<div class="rental-affordability-calculator">

    <label><?php echo __( 'How would you like to calculate your affordability?', 'propertyhive' ); ?></label>
    <select name="calculation_basis" id="calculation_basis">
        <option value=""></option>
        <option value="rent"><?php echo __( 'Using monthly rent', 'propertyhive' ); ?></option>
        <option value="income"><?php echo __( 'Using your total annual income', 'propertyhive' ); ?></option>
    </select>

    <div id="from_rent" style="display:none">
        <label><?php echo __( 'Monthly rent', 'propertyhive' ); ?> (&pound;)</label>
        <input type="text" name="rent" value="" placeholder="e.g. 600">
    </div>

    <div id="from_income" style="display:none">
        <label><?php echo __( 'Annual income', 'propertyhive' ); ?> (&pound;)</label>
        <input type="text" name="income" value="" placeholder="e.g. 18000">
    </div>

    <button><?php echo __( 'Calculate', 'propertyhive' ); ?></button>

    <div id="results_rent" class="rental-affordability-results" style="display:none">
        Your total income will need to be:

        <h3 id="results_rent_total">£-</h3>

        If a guarantor is required then they will also need to have a total income of:

        <h3 id="results_rent_guarantor">£-</h3>
    </div>

    <div id="results_income" class="rental-affordability-results" style="display:none">
        With this total income the monthly rent that you might be able to afford would be:

        <h3 id="results_income_total">£-</h3>

        If a guarantor is required then they will also need to have a total income of:

        <h3 id="results_income_guarantor">£-</h3>
    </div>

</div>