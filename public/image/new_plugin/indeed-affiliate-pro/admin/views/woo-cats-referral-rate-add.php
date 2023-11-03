<div id="uap_woo_cats"  >

    <p><?php esc_html_e( 'Ultimate Affiliate Pro - Customize Referral Rate for current category.', 'uap');?></p>

    <p class="form-field">
        <label><?php esc_html_e('Referral Rate Type', 'uap');?></label>
          <select name="uap_referral_type" class="select short">
              <?php if ( $data['types'] ):?>
                  <?php foreach ( $data['types'] as $key => $value ):?>
                      <option value="<?php echo $key;?>" <?php echo ( $data['uap_referral_type'] == $key ) ? 'selected' : '';?> ><?php echo $value;?></option>
                  <?php endforeach;?>
              <?php endif;?>
          </select>
    </p>

    <p class="form-field">
        <label><?php esc_html_e('Referral Value', 'uap');?></label>
        <input type="number" step="0.01" min="0" class="short" name="uap_referral_value" value="<?php echo $data['uap_referral_value'];?>" />
    </p>
    <p class="form-field">
    <?php
    $offerType = get_option( 'uap_referral_offer_type' );
    if ( $offerType == 'biggest' ){
    		$offerType = esc_html__( 'Biggest', 'uap' );
    } else {
    		$offerType = esc_html__( 'Lowest', 'uap' );
    }
    echo esc_html__( 'If there are multiple Amounts set for the same action, like Ranks, Offers, Product or Category rate the ', 'uap' ) . '<strong>' . $offerType . '</strong> ' . esc_html__( 'will be taken in consideration. You may change that from', 'uap' ) . ' <a href="' . admin_url( 'admin.php?page=ultimate_affiliates_pro&tab=settings' ) . '" target="_blank">' . esc_html__( 'here.', 'uap' ) . '</a>';
    ?>
    </p>
</div>
