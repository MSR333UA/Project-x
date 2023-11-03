<?php
global $indeed_db;
if ( !isset( $affiliate_id ) ){
		$affiliate_id = $data['id'];
}
if ( !isset( $affiliate_avatar ) ){
			$affiliateuid = $indeed_db->get_uid_by_affiliate_id($affiliate_id);
			$affiliate_avatar = uap_get_avatar_for_uid( $affiliateuid );
}
if ( !isset( $affiliate_full_name ) ){
		$affiliate_full_name = $indeed_db->get_full_name_of_user($affiliate_id);
}
?>
<div class="uap-ap-wrap">

	<?php if (!empty($data['title'])):?>
		<h3><?php echo $data['title'];?></h3>
	<?php endif;?>
	<?php if (!empty($data['message'])):?>
		<p><?php echo do_shortcode($data['message']);?></p>
	<?php endif;?>

		<?php if (!empty($data['items'])):?>

			<?php wp_enqueue_script( 'uap-gstatic-charts', 'https://www.gstatic.com/charts/loader.js', ['jquery'], 7.4 );?>
			<?php wp_enqueue_script( 'uap-public-mlm', UAP_URL . 'assets/js/public-mlm.js', ['jquery'], 7.4 );?>

			<span class="uap-js-mlm-view-affiliate-children-parent-data"
						data-parent_id='<?php echo $data['parent_id'];?>'
						data-parent_avatar="<?php echo $data['parent_avatar'];?>"
						data-parent_full_name="<?php echo $data['parent_full_name'];?>"
						data-parent="<?php echo $data['parent'];?>"
			></span>

			<span class="uap-js-mlm-view-affiliate-data"
						data-affiliate_id='<?php echo $affiliate_id;?>'
						data-affiliate_avatar="<?php echo $affiliate_avatar;?>"
						data-parent_full_name="<?php echo $affiliate_full_name;?>"
			></span>

			<?php if ( !empty( $data['items'] ) ):?>
				<?php foreach ( $data['items'] as $item ):?>
						<span class="uap-js-mlm-view-affiliate-children-data"
									data-avatar="<?php echo $item['avatar'];?>"
									data-full_name="<?php echo $item['full_name'];?>"
									data-amount="<?php echo $item['amount_value'] . ' rewards';?>"
									data-id="<?php echo $item['id'];?>"
									data-parent_id="<?php echo $item['parent_id'];?>"
						></span>
				<?php endforeach;?>
			<?php endif;?>

<div id="uap_mlm_chart"></div>

			<table class="uap-account-table">
				<tbody>
					<thead>
						<tr>
							<th><?php esc_html_e('Subaffiliate', 'uap');?></th>
							<th><?php esc_html_e('E-mail Address', 'uap');?></th>
							<th><?php esc_html_e('Level', 'uap');?></th>
							<th><?php esc_html_e('Amount', 'uap');?></th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th><?php esc_html_e('Subaffiliate', 'uap');?></th>
							<th><?php esc_html_e('E-mail Address', 'uap');?></th>
							<th><?php esc_html_e('Level', 'uap');?></th>
							<th><?php esc_html_e('Amount', 'uap');?></th>
						</tr>
					</tfoot>
					<?php foreach ($data['items'] as $item):?>
					<tr>
						<td><?php echo $item['username'];?></td>
						<td><?php echo $item['email'];?></td>
						<td><?php echo $item['level'];?></td>
						<td><?php echo $item['amount_value'];?></td>
					</tr>
					<?php endforeach;?>
				</tbody>
			</table>
		<?php else : ?>
			<div class="uap-account-detault-message">
              <div><?php esc_html_e('In order to have affiliates inside your MLM Matrix just promote the affiliate program and bring new affiliates registered with your Affiliate Link.', 'uap');?></div>
          </div>
		<?php endif;?>

</div>
