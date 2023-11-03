<?php if ( $type !== false && $type == 'v3' ):?>
    <div class="js-uap-recaptcha-v3-item"></div>
    <span class="uap-js-register-captcha-key" data-value="<?php echo $key;?>"></span>
<?php else :?>
    <div class="g-recaptcha-wrapper" class="<?php echo $class;?>">
        <div class="g-recaptcha" data-sitekey="<?php echo $key;?>"></div>
    </div>
<?php endif;?>
