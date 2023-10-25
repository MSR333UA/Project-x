<?php
/**
 *	Bakery WordPress Theme
 */

add_action( 'add_meta_boxes', 'vu_post_format_settings_meta_box' );

function vu_post_format_settings_meta_box() {
	add_meta_box(
		'vu_post-format-settings',
		__( 'Post Format Settings', 'bakery' ),
		'vu_post_format_settings_meta_box_content',
		'post',
		'normal',
		'core'
	);
}

function vu_post_format_settings_meta_box_content() {
	global $post;

	//Get Post Format Settings
	$vu_post_format_settings = vu_get_post_meta( $post->ID, 'vu_post_format_settings' );

	wp_nonce_field( 'vu_metabox_nonce', 'vu_metabox_nonce' ); ?>
	
	<!-- Video Settings -->
	<div class="vu_metabox-container" data-format="video">
		<table class="form-table vu_metabox-table">
			<tr class="vu_bt-none">
				<td scope="row">
					<label for="vu_field_video-m4v"><?php echo __('M4V File URL', 'bakery'); ?></label>
					<span class="vu_desc"><?php echo __('Please upload the .m4v video file.<br><b>You must include both formats.</b>', 'bakery'); ?></span>
				</td>
				<td>
					<input id="vu_field_video-m4v" name="vu_field[vu_post_format_settings][video][m4v]" class="regular-text" style="margin: 0 0 10px 0;" type="text" value="<?php echo esc_url($vu_post_format_settings['video']['m4v']); ?>" /><br>
					<a href="#" data-input="vu_field_video-m4v" data-title="<?php echo __('Choose a File', 'bakery'); ?>" data-button="<?php echo __('Select File', 'bakery'); ?>" data-type="video" class="vu-open-media button button-default"><?php echo __('Add Media', 'bakery'); ?></a>
				</td>
			</tr>
			<tr>
				<td scope="row">
					<label for="vu_field_video-ogg"><?php echo __('OGV File URL', 'bakery'); ?></label>
					<span class="vu_desc"><?php echo __('Please upload the .ogv video file  <br/><b>You must include both formats.</b>', 'bakery'); ?></span>
				</td>
				<td>
					<input id="vu_field_video-ogg" name="vu_field[vu_post_format_settings][video][ogg]" class="regular-text" style="margin: 0 0 10px 0;" type="text" value="<?php echo esc_url($vu_post_format_settings['video']['ogg']); ?>" /><br>
					<a href="#" data-input="vu_field_video-ogg" data-title="<?php echo __('Choose a File', 'bakery'); ?>" data-button="<?php echo __('Select File', 'bakery'); ?>" data-type="video" class="vu-open-media button button-default"><?php echo __('Add Media', 'bakery'); ?></a>
				</td>
			</tr>
			<tr>
				<td scope="row">
					<label for=""><?php echo __('Preview Image', 'bakery'); ?></label>
					<span class="vu_desc"><?php echo __('Image should be at least 680px wide. Click the "Upload" button to begin uploading your image, followed by "Select File" once you have made your selection. Only applies to self hosted videos.', 'bakery'); ?></span>
				</td>
				<td>
					<img id="vu_img_video-poster" class="vu_media-img" src="<?php echo esc_url($vu_post_format_settings['video']['poster']); ?>">
					<input id="vu_field_video-poster" name="vu_field[vu_post_format_settings][video][poster]" class="regular-text" type="hidden" value="<?php echo esc_url($vu_post_format_settings['video']['poster']); ?>" />
					<a href="#" data-input="vu_field_video-poster" data-img="vu_img_video-poster" data-title="<?php echo __('Add Image', 'bakery'); ?>" data-button="<?php echo __('Add Image', 'bakery'); ?>" class="vu-open-media button button-default"><?php echo __('Upload', 'bakery'); ?></a>
				</td>
			</tr>
			<tr>
				<td scope="row">
					<label for="vu_field_video-embed-code"><?php echo __('Embedded Code', 'bakery'); ?></label>
					<span class="vu_desc"><?php echo __('If the video is an embed rather than self hosted, enter in a Vimeo or Youtube embed code here. <b>Embeds work worse with the parallax effect, but if you must use this, Vimeo is recommended.</b>', 'bakery'); ?></span>
				</td>
				<td>
					<textarea name="vu_field[vu_post_format_settings][video][embed-code]" id="vu_field_video-embed-code" rows="7"><?php echo esc_url($vu_post_format_settings['video']['embed-code']); ?></textarea>
				</td>
			</tr>
		</table>
	</div>
	<!-- Video Settings -->
	
	<!-- Audio Settings -->
	<div class="vu_metabox-container" data-format="audio">
		<table class="form-table vu_metabox-table">
			<tr class="vu_bt-none">
				<td scope="row">
					<label for="vu_field_audio-mp3-file-url"><?php echo __('MP3 File URL', 'bakery'); ?></label>
					<span class="vu_desc"><?php echo __('Please enter in the URL to the .mp3 file', 'bakery'); ?></span>
				</td>
				<td><input id="vu_field_audio-mp3-file-url" name="vu_field[vu_post_format_settings][audio][mp3-file-url]" class="regular-text" type="text" value="<?php echo esc_url($vu_post_format_settings['audio']['mp3-file-url']); ?>" /></td>
			</tr>
			<tr>
				<td scope="row">
					<label for="vu_field_audio-oga-file-url"><?php echo __('OGA File URL', 'bakery'); ?></label>
					<span class="vu_desc"><?php echo __('Please enter in the URL to the .ogg or .oga file', 'bakery'); ?></span>
				</td>
				<td><input id="vu_field_audio-oga-file-url" name="vu_field[vu_post_format_settings][audio][oga-file-url]" class="regular-text" type="text" value="<?php echo esc_url($vu_post_format_settings['audio']['oga-file-url']); ?>" /></td>
			</tr>
		</table>
	</div>
	<!-- /Audio Settings -->
	
	<!-- Gallery Settings -->
	<div class="vu_metabox-container" data-format="gallery">
		<table class="form-table vu_metabox-table">
			<tr class="vu_bt-none">
				<td scope="row">
					<label><?php echo __('Gallery', 'bakery'); ?></label>
					<span class="vu_desc"><?php echo __('Click the "Upload" button to begin uploading your images, followed by "Add Images" once you have made your selection.', 'bakery'); ?></span>
				</td>
				<td>
					<div id="vu_img_gallery-images" data-input="vu_field_gallery-images" class="vu_media-images">
						<?php 
							$gallery_images = !empty($vu_post_format_settings['gallery']['images']) ? explode(',', $vu_post_format_settings['gallery']['images']) : '';

							if( !empty($gallery_images) ){
								foreach ($gallery_images as $img_id) {
									echo '<div><img data-id="'. $img_id .'" src="'. vu_get_attachment_image_src($img_id) .'"><span>&times;</span></div>';
								}
							}
						?>
					</div>

					<input id="vu_field_gallery-images" name="vu_field[vu_post_format_settings][gallery][images]" class="regular-text" type="hidden" value="<?php echo esc_attr($vu_post_format_settings['gallery']['images']); ?>" />
					<a href="#" data-input="vu_field_gallery-images" data-title="<?php echo __('Add Images', 'bakery'); ?>" data-button="<?php echo __('Add Images', 'bakery'); ?>" data-img="vu_img_gallery-images" class="vu-open-media multiple button button-default"><?php echo __('Upload', 'bakery'); ?></a>
				</td>
			</tr>
		</table>
	</div>
	<!-- Gallery Settings -->
<?php
}