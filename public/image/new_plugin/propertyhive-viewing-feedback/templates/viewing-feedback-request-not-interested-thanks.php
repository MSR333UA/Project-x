<p><strong>Thanks for providing your feedback. We're sorry to hear that you weren't interested in this property.</strong></p>

<?php
	$similar_properties = do_shortcode("[similar_properties property_id=" . $property_id . " columns=1]");
	if ( trim(strip_tags($similar_properties)) != '' )
	{
?>
<hr>
<div class="similar-properties">
	<p>We found these similar properties which might be of interest to you:</p>
	<?php echo $similar_properties; ?>
</div>
<?php
	}
?>