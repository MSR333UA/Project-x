<?php
/**
 * Owner maintenance jobs page within My Account
 *
 * This template can be overridden by copying it to yourtheme/propertyhive/account/owner-maintenance-jobs.php.
 *
 * @author      PropertyHive
 * @package     PropertyHive/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="propertyhive-owner-maintenance-jobs">

	<?php
		if ( !empty($maintenance_jobs) )
		{
			echo '
			<table class="viewings-table upcoming-viewings-table" width="100%">
				<tr>
					<th>' . __( 'Property', 'propertyhive' ) . '</th>
					<th>' . __( 'Job', 'propertyhive' ) . '</th>
					<th>' . __( 'Description', 'propertyhive' ) . '</th>
					<th>' . __( 'Status', 'propertyhive' ) . '</th>
				</tr>
			';
			foreach ($maintenance_jobs as $maintenance_job)
			{
				$property = new PH_Property( (int)get_post_meta($maintenance_job, '_property_id', TRUE) );

				echo '<tr>
					<td>' . $property->get_formatted_full_address() . '</td>
					<td>' . get_the_title($maintenance_job) . '</td>
					<td>' . nl2br(get_post_meta($maintenance_job, '_description', TRUE)) . '</td>
					<td>' . ucfirst(get_post_meta($maintenance_job, '_status', TRUE)) . '</td>
				</tr>';
			}
			echo '</table>';
		}
		else
		{
			'<p class="propertyhive-info">' . _e( 'No maintenance jobs to display', 'propertyhive' ) . '</p>';
		}
	?>

</div>
