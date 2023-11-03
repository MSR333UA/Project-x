<?php
/**
 * Form to report a maintenance issue
 *
 * This template can be overridden by copying it to yourtheme/propertyhive/report-maintenance-job.php.
 *
 * @author      PropertyHive
 * @package     PropertyHive/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

?>

<div id="report_maintenance_issue">

    <form name="ph_report_maintenance_issue" class="propertyhive-form report-maintenance-form" action="" method="post">

        <div id="reportMaintenanceSuccess" style="display:none;" class="alert alert-success alert-box success">
            <?php _e( 'Thank you. Your maintenance issue has been reported successfully.', 'propertyhive' ); ?>
        </div>
        <div id="reportMaintenanceError" style="display:none;" class="alert alert-danger alert-box">
            <?php _e( 'An error occurred whilst trying to send your issue. Please try again.', 'propertyhive' ); ?>
        </div>
        <div id="reportMaintenanceValidation" style="display:none;" class="alert alert-danger alert-box">
            <?php _e( 'Please ensure all required fields have been completed', 'propertyhive' ); ?>
        </div>

        <?php foreach ( $form_controls as $key => $field ) : ?>

            <?php ph_form_field( $key, $field ); ?>

        <?php endforeach; ?>

        <input type="submit" value="<?php _e( 'Submit', 'propertyhive' ); ?>">

    </form>

</div>
