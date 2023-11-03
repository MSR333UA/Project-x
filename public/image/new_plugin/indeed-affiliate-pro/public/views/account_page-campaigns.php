<div class="uap-ap-wrap">

<?php if (!empty($data['title'])):?>
	<h3><?php echo $data['title'];?></h3>
<?php endif;?>
<?php if (!empty($data['message'])):?>
	<p><?php echo do_shortcode($data['message']);?></p>
<?php endif;?>

	<form method="post"  id="uap_campaign_form">

		<input type="hidden" name="uap_campaign_nonce" value="<?php echo wp_create_nonce( 'uap_campaign_nonce' );?>" />

        <div class="uap-profile-box-wrapper">
    	<div class="uap-profile-box-title"><span><?php esc_html_e('Add New Campaign', 'uap');?></span></div>
        <div class="uap-profile-box-content">
        	<div class="uap-row ">
            	<div class="uap-col-xs-8">
                <?php esc_html_e("Campaigns will help you to better promote your marketing strategy. Those are private and individual for each affiliate account.", 'uap');?>
                </div>
            </div>
        </div>
        <div class="uap-profile-box-content">
            <div class="uap-row ">
            	<div class="uap-col-xs-6">
                    <div class="uap-ap-field">
                        <div class="uap-account-title-label"><?php esc_html_e('Campaign unique Slug', 'uap');?></div>
                        <input type="text" name="campaign_name" value="" class="uap-public-form-control "/>
                        <div class="uap-account-notes"><?php echo esc_html__("Slug must be unique and based on only lowercase characters without extra symbols or spaces.", 'uap');?></div>
                    </div>
                    <div class="uap-ap-field">
                        <input type="submit" name="save" value="<?php esc_html_e('Add New', 'uap');?>" class="button button-primary button-large uap-js-submit-campaign" />
                    </div>
               </div>
            </div>
         </div>
         </div>

		<?php if (!empty($data['campaigns'])) : ?>
		<div class="uap-profile-box-wrapper">
    	<div class="uap-profile-box-title"><span><?php esc_html_e('Your own Campaigns', 'uap');?></span></div>
        <div class="uap-profile-box-content">
        	<div class="uap-row ">
            	<div class="uap-col-xs-8">
		<div class="uap-account-campaign-list-wrapper">
        	<div class="uap-account-campaign-list-header">
        		<div class="uap-aling-float-left"><?php esc_html_e('Campaign slug', 'uap');?></div>
        		<div class="uap-aling-float-right"><?php esc_html_e('Remove', 'uap');?></div>
						<div class="uap-clear"></div>
            </div>
		<?php foreach ($data['campaigns'] as $campaignId => $value ) : ?>
			<div class="uap-account-campaign-list">
				<div class="uap-aling-float-left"><strong><?php echo $value;?></strong></div>
				<div class="uap-aling-float-right"><i class="fa-uap fa-trash-uap uap-js-account-page-campaigns-delete-item" data-id="<?php echo $campaignId;?>"></i></div>
				<div class="uap-clear"></div>
			</div>
		<?php endforeach;?>
		</div>
			<input type="hidden" value="" name="uap_delete_campaign" id="uap_delete_campaign"/>
               </div>
            </div>
         </div>
         </div>
		<?php endif;?>
		<br/>

	</form>
</div>
