<div>
	<h2>Indeed Ultimate Affiliate Pro</h2>
	<label class="uap-edit-wp-user-label"><?php esc_html_e('Become Affiliate', 'uap');?></label>
	<div class="uap-edit-wp-user-status">
		<?php if ($data['is_affiliate']): ?>
			<?php esc_html_e('Already registered as Affiliate.', 'uap');?>
			<div>
				<button type="button" class="button button-secondary" onclick="uapMakeAffiliateSimpleUser(<?php echo $data['id'];?>);"><?php esc_html_e('Remove from Affiliates list', 'uap');?></button>
			</div>
		<?php else:?>
			<button type="button" class="button button-secondary" onclick="uapMakeUserAffiliate(<?php echo $data['id'];?>);"><?php esc_html_e('Make This User Affiliate', 'uap');?></button>
		<?php endif?>
	</div>
</div>
