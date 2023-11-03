
<?php wp_enqueue_style( 'uap-croppic_css', UAP_URL . 'assets/css/croppic.css', array(), 7.0 );?>
<?php wp_enqueue_script( 'uap-jquery_mousewheel', UAP_URL . 'assets/js/jquery.mousewheel.min.js', ['jquery'], 7.4 );?>
<?php wp_enqueue_script( 'uap-croppic', UAP_URL . 'assets/js/croppic.js', ['jquery'], 7.4 );?>
<?php wp_enqueue_script( 'uap-image_croppic', UAP_URL . 'assets/js/image_croppic.js', ['jquery'], 7.4 );?>
<?php wp_enqueue_script( 'uap-public-upload-image', UAP_URL . 'assets/js/public-upload-image.js', ['jquery'], 7.4 );?>
<span class="uap-js-public-upload-image-data"
data-trigger_id="<?php echo 'js_uap_trigger_avatar' . $data['rand'];?>"
data-name="<?php echo $data['name'];?>"
data-button_label="<?php echo esc_html__('Upload', 'uap');?>"
data-save_image_target="<?php echo UAP_URL . 'public/ajax-upload.php?publicn=' . wp_create_nonce( 'publicn' );?>"
data-crop_image_target="<?php echo UAP_URL . 'public/ajax-upload.php?publicn=' . wp_create_nonce( 'publicn' );?>"
data-remove_selector="<?php echo '#uap_upload_image_remove_bttn_' . $data['rand'];?>"
></span>


<div class="uap-upload-image-wrapper">

    <div class="uap-upload-image-wrapp" >
        <?php if ( !empty($data['imageUrl']) ):?>
            <img src="<?php echo $data['imageUrl'];?>" class="<?php echo $data['imageClass'];?>" />
        <?php else:?>
            <?php if ( $data['name']=='uap_avatar' ):?>
                <div class="uap-no-avatar uap-member-photo"></div>
            <?php endif;?>
        <?php endif;?>
        <div class="uap-clear"></div>
    </div>
    <div class="uap-content-left">
    	<div class="uap-avatar-trigger" id="<?php echo 'js_uap_trigger_avatar' . $data['rand'];?>" >
        	<div id="uap-avatar-button" class="uap-upload-avatar"><?php esc_html_e('Upload', 'uap');?></div>
        </div>
        <span  class="uap-upload-image-remove-bttn uap-visibility-hidden" id="<?php echo 'uap_upload_image_remove_bttn_' . $data['rand'];?>"><?php esc_html_e('Remove', 'uap');?></span>
    </div>
    <input type="hidden" value="<?php echo $data['value'];?>" name="<?php echo $data['name'];?>" id="<?php echo 'uap_upload_hidden_' . $data['rand'];?>" data-new_user="<?php echo ( $data['user_id'] == -1 ) ? 1 : 0;?>" />

    <?php if (!empty($data['sublabel'])):?>
        <label class="uap-form-sublabel"><?php echo uap_correct_text($data['sublabel']);?></label>
    <?php endif;?>
</div>
