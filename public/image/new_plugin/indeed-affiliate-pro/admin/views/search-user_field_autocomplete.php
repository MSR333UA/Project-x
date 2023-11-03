<div class="uap-autocomplete-field">
	<div class="col-xs-6">
		<?php if (!empty($attr['title'])):?>
		<h2><?php echo $attr['title'];?></h2>
		<?php endif;?>
		<div class="input-group">
			<span class="input-group-addon" id="basic-addon1"><?php echo $attr['label'];?></span>
			<?php
				global $indeed_db;
				if ( !empty( $attr['value'] ) ){
						$mlmParentUsername =  $indeed_db->get_username_by_wpuid( $indeed_db->get_uid_by_affiliate_id( $attr['value'] ) );
				}
				$textName = isset( $attr['text_name'] ) ? "name='{$attr['text_name']}'" : '';
			?>
			<input type="text"  class="form-control" <?php echo $textName;?> value="<?php echo ( isset( $mlmParentUsername ) ) ? $mlmParentUsername : '';?>" <?php echo $attr['field_style'];?> id="usernames_search" />
			<input type="hidden" name="<?php echo $attr['hidden_name'];?>" value="<?php echo ( isset( $attr['value'] ) ) ? $attr['value'] : '';?>" id="<?php echo $attr['hidden_name'];?>" />
		</div>
	</div>
	<div class="uap-clear"></div>
</div>
<?php
	$url = UAP_URL . 'admin/uap-offers-ajax-autocomplete.php?users=true&without_all=true&uapAdminAjaxNonce=' . wp_create_nonce( 'uapAdminAjaxNonce' );
	if (!empty($attr['exclude_user_id'])){
		$url .= '&exclude_user=' . $attr['exclude_user_id'];
	}
?>
<span class="uap-js-search-user-field-autocomplete" data-id="<?php echo $attr['hidden_name'];?>" data-url="<?php echo $url;?>" ></spam>
<?php
wp_enqueue_script( 'uap-search-user-field-autocomplete', UAP_URL . 'assets/js/search-user-field-autocomplete.js', ['jquery'], 1.1 );
?>
