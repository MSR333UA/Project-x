<div class="uap-banners-wrapp">

<?php if (!empty($data['title'])):?>
	<h3><?php echo $data['title'];?></h3>
<?php endif;?>
<?php if (!empty($data['message'])):?>
	<p><?php echo do_shortcode($data['message']);?></p>
<?php endif;?>

	<?php if (!empty($data['listing_items'])) : ?>
		<?php foreach ($data['listing_items'] as $arr) : ?>
			<div class="uap-banner">
				<div class="uap-banner-title"><?php echo $arr->name;?></div>
				<div class="uap-banner-content">
					<div class="uap-banner-img">
					<a href="<?php echo $arr->url;?>" target="_blank">
						<img src="<?php echo $arr->image;?>" alt="<?php echo $arr->name;?>" />
					</a>
					</div>
					<div class="uap-banner-description"><?php echo esc_html__('Description:', 'uap') . ' ' . uap_correct_text($arr->description);?></div>
					<?php $size = uap_get_image_size($arr->image);?>
					<?php if ( isset( $size['width'] ) && isset( $size['height'] ) ):?>
							<div><span class="uap-special-label"><?php echo esc_html__('Banner Size:', 'uap');?></span> <?php echo $size['width'] . 'px x ' . $size['height'] . 'px.';?></div>
					<?php endif;?>
					<div><span class="uap-special-label"><?php echo esc_html__('Target URL:', 'uap');?></span> <?php echo $arr->url;?></div>

					<div class="uap-banner-copypaste">
						<div><strong><i><?php esc_html_e('Copy & Paste this Source Code Into Your Website: ', 'uap');?></i></strong></div>
						<textarea><a href="<?php echo $arr->url;?>" target="_blank"><img src="<?php echo $arr->image;?>"  alt="<?php echo $arr->name;?>" /><?php echo $data['pixel_tracking'];?></a></textarea>
					</div>
				</div>
			</div>
		<?php endforeach;?>
	<?php else : ?>

	<?php endif;?>
</div>
