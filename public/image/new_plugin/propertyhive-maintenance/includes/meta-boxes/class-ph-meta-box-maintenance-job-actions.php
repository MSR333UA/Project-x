<?php
/**
 * Maintenance Job Actions
 *
 * @author 		PropertyHive
 * @category 	Admin
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * PH_Meta_Box_Maintenance_Job_Actions
 */
class PH_Meta_Box_Maintenance_Job_Actions {

	/**
	 * Output the metabox
	 */
	public static function output( $post ) {
        global $wpdb, $thepostid;

        echo '<div id="propertyhive_maintenance_job_actions_meta_box_container">

            Loading...';

        echo '</div>';
?>
<script>

jQuery(document).ready(function($)
{
	$('#propertyhive_maintenance_job_actions_meta_box_container').on('click', 'a.maintenance-job-action', function(e)
	{
		e.preventDefault();

		var this_href = $(this).attr('href');

		$(this).attr('disabled', 'disabled');

		$('#propertyhive_maintenance_job_actions_meta_box').stop().fadeOut(300, function()
		{
			$(this_href).stop().fadeIn(300, function()
			{

			});
		});
	});

	$('#propertyhive_maintenance_job_actions_meta_box_container').on('click', 'a.action-cancel', function(e)
	{
		e.preventDefault();

		redraw_maintenance_job_actions();
	});

	$('#propertyhive_maintenance_job_actions_meta_box_container').on('click', 'a.acknowledged-action-submit', function(e)
	{
		e.preventDefault();

		$(this).attr('disabled', 'disabled');

		var data = {
	        action:             'propertyhive_maintenance_job_acknowledged',
	        maintenance_job_id: <?php echo $post->ID; ?>,
	        job_title:          $('#_maintenance_job_title').val(),
	        security:           '<?php echo wp_create_nonce( 'maintenance-job-actions' ); ?>',
	    };

	    jQuery.post( '<?php echo admin_url('admin-ajax.php'); ?>', data, function(response)
	    {
			location.reload();
	    }, 'json');
	});
});

jQuery(window).on('load', function($)
{
	redraw_maintenance_job_actions();
});

function redraw_maintenance_job_actions()
{
	jQuery('#propertyhive_maintenance_job_actions_meta_box_container').html('Loading...');

	var data = {
        action:             'propertyhive_get_maintenance_job_actions',
        maintenance_job_id: <?php echo $post->ID; ?>,
        security:           '<?php echo wp_create_nonce( 'maintenance-job-actions' ); ?>',
    };

    jQuery.post( '<?php echo admin_url('admin-ajax.php'); ?>', data, function(response)
    {
        jQuery('#propertyhive_maintenance_job_actions_meta_box_container').html(response);
    });
}

</script>
<?php
    }
}
