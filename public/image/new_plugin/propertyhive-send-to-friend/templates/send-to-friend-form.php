<?php
/**
 * Send to friend form
 *
 * @author      PropertyHive
 * @package     PropertyHive/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

<form name="ph_send_to_friend" class="property-send-to-friend-form" action="" method="post">
    
    <div id="sendToFriendSuccess" style="display:none;" class="alert alert-success alert-box success">
        <?php _e( 'Thank you. Your friend has been sent a link to this property.', 'propertyhive' ); ?>
    </div>
    <div id="sendToFriendError" style="display:none;" class="alert alert-danger alert-box">
        <?php _e( 'An error occurred whilst trying to send your email. Please try again.', 'propertyhive' ); ?>
    </div>
    <div id="sendToFriendValidation" style="display:none;" class="alert alert-danger alert-box">
        <?php _e( 'Please ensure all required fields have been completed', 'propertyhive' ); ?>
    </div>
    
    <?php foreach ( $form_controls as $key => $field ) : ?>

        <?php ph_form_field( $key, $field ); ?>

    <?php endforeach; ?>

    <input type="submit" value="<?php _e( 'Submit', 'propertyhive' ); ?>">

</form>