<?php
/**
 *	Bakery WordPress Theme
 */

add_action( 'add_meta_boxes', 'vu_page_header_settings_meta_box' );

function vu_page_header_settings_meta_box() {
	add_meta_box(
		'vu_page-header-settings',
		__( 'Page Header Settings', 'bakery' ),
		'vu_page_header_settings_meta_box_content',
		'page',
		'normal',
		'core'
	);
}

function vu_page_header_settings_meta_box_content() {
	global $post;

	//Get Page Header Settings
	$vu_page_header_settings = vu_get_post_meta( $post->ID, 'vu_page_header_settings' );

	wp_nonce_field( 'vu_metabox_nonce', 'vu_metabox_nonce' ); ?>

	<div class="vu_metabox-container">
		<table class="form-table vu_metabox-table">
			<tr class="vu_bt-none">
				<td scope="row">
					<label for="vu_field_header-style"><?php echo __('Page Header Style', 'bakery'); ?></label>
					<span class="vu_desc"></span>
				</td>
				<td>
					<label for="vu_field_header-style-default" style="margin-right: 15px;"><input type="radio" name="vu_field[vu_page_header_settings][style]" id="vu_field_header-style-default" value="default" <?php echo (empty($vu_page_header_settings['style']) || $vu_page_header_settings['style'] == 'default') ? 'checked="checked"' : ''; ?>><?php echo __('Default', 'bakery'); ?><sup>1</sup></label> 
					<label for="vu_field_header-style-custom" style="margin-right: 15px;"><input type="radio" name="vu_field[vu_page_header_settings][style]" id="vu_field_header-style-custom" value="custom"<?php echo (!empty($vu_page_header_settings['style']) && $vu_page_header_settings['style'] == 'custom') ? 'checked="checked"' : ''; ?>><?php echo __('Custom', 'bakery'); ?><sup>2</sup></label>
					<label for="vu_field_header-style-none"><input type="radio" name="vu_field[vu_page_header_settings][style]" id="vu_field_header-style-none" value="none"<?php echo (!empty($vu_page_header_settings['style']) && $vu_page_header_settings['style'] == 'none') ? 'checked="checked"' : ''; ?>><?php echo __('None', 'bakery'); ?></label>
				</td>
			</tr>
			<tr class="vu_dependency" data-element="vu_field[vu_page_header_settings][style]" data-value="custom">
				<td scope="row">
					<label for="vu_field_header-title"><?php echo __('Titile', 'bakery'); ?></label>
					<span class="vu_desc"><?php echo __('Enter in the page header title', 'bakery'); ?></span>
				</td>
				<td><input id="vu_field_header-title" name="vu_field[vu_page_header_settings][title]" class="regular-text" type="text" value="<?php echo esc_attr($vu_page_header_settings['title']); ?>" /></td>
			</tr>
			<tr class="vu_dependency" data-element="vu_field[vu_page_header_settings][style]" data-value="custom">
				<td scope="row">
					<label for="vu_field_header-subtitle"><?php echo __('Subtitle', 'bakery'); ?></label>
					<span class="vu_desc"><?php echo __('Enter in the page header subtitle', 'bakery'); ?></span>
				</td>
				<td><input id="vu_field_header-subtitle" name="vu_field[vu_page_header_settings][subtitle]" class="regular-text" type="text" value="<?php echo esc_attr($vu_page_header_settings['subtitle']); ?>" /></td>
			</tr>
			<tr class="vu_dependency" data-element="vu_field[vu_page_header_settings][style]" data-value="custom">
				<td scope="row">
					<label><?php echo __('Background Image', 'bakery'); ?></label>
					<span class="vu_desc"><?php echo __('The image should be between 1600px - 2000px in width and have a minimum height of 300px for best results. Click the "Upload" button to begin uploading your image, followed by "Select File" once you have made your selection.', 'bakery'); ?></span>
				</td>
				<td>
					<img id="vu_img_header-bg" class="vu_media-img" src="<?php echo esc_url($vu_page_header_settings['bg']); ?>">
					<input id="vu_field_header-bg" name="vu_field[vu_page_header_settings][bg]" class="regular-text" type="hidden" value="<?php echo esc_url($vu_page_header_settings['bg']); ?>" />
					<a href="#" data-input="vu_field_header-bg" data-img="vu_img_header-bg" data-title="<?php echo __('Add Image', 'bakery'); ?>" data-button="<?php echo __('Add Image', 'bakery'); ?>"  class="vu-open-media button button-default"><?php echo __('Upload', 'bakery'); ?></a>
				</td>
			</tr>
		</table>
	</div>
<?php
}