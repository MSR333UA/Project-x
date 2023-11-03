<p><strong>Thanks for providing your feedback. If necessary we'll be in touch shortly.</strong></p>

<?php
	$similar_properties = do_shortcode("[similar_properties property_id=" . $property_id . " columns=1]");
	if ( trim(strip_tags($similar_properties)) != '' )
	{
?>
<div class="similar-properties">
	<p>In the meantime we found these similar properties which might be of interest to you:</p>
	<?php echo $similar_properties; ?>
</div>
<?php
	}
?>