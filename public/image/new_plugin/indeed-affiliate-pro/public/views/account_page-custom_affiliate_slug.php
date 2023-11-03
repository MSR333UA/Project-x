<div class="uap-ap-wrap">

<?php if (!empty($data['title'])):?>
	<h3><?php echo $data['title'];?></h3>
<?php endif;?>
<?php if (!empty($data['message'])):?>
	<p><?php echo do_shortcode($data['message']);?></p>
<?php endif;?>

<div class="uap-profile-box-wrapper">
    	<div class="uap-profile-box-title"><span><?php esc_html_e("Manage your Affiliate Slug", 'uap');?></span></div>
        <div class="uap-profile-box-content">
        	<div class="uap-row ">
            	<div class="uap-col-xs-12">
                <?php esc_html_e("Personalize your personal Affliate slug besides the default username or ID so you can hide your identity or company name behind a custom slug.", 'uap');?>
                </div>
             </div>
                <div class="uap-row ">
            	<div class="uap-col-xs-6">
	<form method="post"  id="uap_campaign_form">

		<div class="uap-ap-field">

			<input type="text" name="uap_affiliate_custom_slug" value="<?php echo $data['uap_affiliate_custom_slug'];?>" class="uap-public-form-control "/>
			<div class="uap-account-notes"><?php echo esc_html__("Slug must be unique, between ", 'uap').$data['slug_condition_min'].esc_html__(" and ", 'uap').$data['slug_condition_max'].esc_html__(" characters", 'uap').$data['slug_condition_rule'];?></div>
        </div>

        <div class="uap-ap-field">
        <?php if (isset($saved)):?>
			<?php if ($saved===FALSE):?>
				<div class="uap-warning-box"><?php esc_html_e('An error has occurred. Please try to submit a different slug.', 'uap');?></div>
			<?php else :?>
				<div class="uap-success-box"><?php esc_html_e('You Slug have been saved', 'uap');?></div>
			<?php endif;?>
		<?php endif;?>
        </div>
		<div class="uap-ap-field">
			<input type="submit" name="save" value="<?php esc_html_e('Save Changes', 'uap');?>" class="button button-primary button-large" />
		</div>

	</form>
			</div>
         </div>
    </div>
    </div>
</div>
