<h1><?php the_title(); ?></h1>

<h2><?php echo $property->get_formatted_price(); ?> | <?php echo $property->availability; ?></h2>

<?php
	$gallery_attachment_ids = $property->get_gallery_attachment_ids();

	if ( !empty($gallery_attachment_ids) )
	{
?>
<img style="width:100%;" src="<?php if ($pdf) { echo get_attached_file( $gallery_attachment_ids[0] ); }else{ $image = wp_get_attachment_image_src( $gallery_attachment_ids[0], 'full' ); echo $image[0]; } ?>" alt=""><br>
<?php 
		if ( count($gallery_attachment_ids) > 1 )
		{
?>
	<table width="100%" cellspacing="0" cellpadding="0">
		<tr>
			<td width="33%">
			<?php if ( isset($gallery_attachment_ids[1]) ) { ?>
			<img style="width:100%;" src="<?php if ($pdf) { echo get_attached_file( $gallery_attachment_ids[1] ); }else{ $image = wp_get_attachment_image_src( $gallery_attachment_ids[1], 'medium' ); echo $image[0]; } ?>" alt="">
			<?php } ?>
			</td>
			<td width="33%">
			<?php if ( isset($gallery_attachment_ids[2]) ) { ?>
			<img style="width:100%;" src="<?php if ($pdf) { echo get_attached_file( $gallery_attachment_ids[2] ); }else{ $image = wp_get_attachment_image_src( $gallery_attachment_ids[2], 'medium' ); echo $image[0]; } ?>" alt="">
			<?php } ?>
			</td>
			<td>
			<?php if ( isset($gallery_attachment_ids[3]) ) { ?>
			<img style="width:100%;" src="<?php if ($pdf) { echo get_attached_file( $gallery_attachment_ids[3] ); }else{ $image = wp_get_attachment_image_src( $gallery_attachment_ids[3], 'medium' ); echo $image[0]; } ?>" alt="">
			<?php } ?>
			</td>
		</tr>
	</table>
<?php
		}
	}
?>
<div style="float:left; width:66%;">
		
	<h3><?php echo __( 'About The Property', 'propertyhive' ); ?></h3>

	<?php
		echo get_the_excerpt();
	?>

</div>
<div style="float:right; width:30%;">
			
	<?php
		$features = $property->get_features();

		if ( !empty($features) )
		{
	?>
	<h3><?php echo __( 'Features', 'propertyhive' ); ?></h3>

	<ul style="padding-left:13px">
	<?php
		foreach ( $features as $feature )
		{
			echo '<li>' . $feature . '</li>';
		}
	?>
	</ul>
	<br>
	<?php
		}
	?>

	<?php
		$office_id = $property->office_id;
		if ( $office_id != '' )
		{
	?>
	<h3><?php echo __( 'Contact Us', 'propertyhive' ); ?></h3>

	<strong><?php echo get_bloginfo('name'); ?></strong><br>
	<?php
		$address = '';
		$separator = '<br>';

        $address_1 = get_post_meta( $office_id, '_office_address_1', TRUE );
        if ($address_1 != '')
        {
            $address .= $address_1;
        }
        $address_2 = get_post_meta( $office_id, '_office_address_2', TRUE );
        if ($address_2 != '')
        {
            if ($address != '') { $address .= ', '; }
            $address .= $address_2;
        }
        $address_3 = get_post_meta( $office_id, '_office_address_3', TRUE );
        if ($address_3 != '')
        {
            if ($address != '') { $address .= $separator; }
            $address .= $address_3;
        }
        $address_4 = get_post_meta( $office_id, '_office_address_4', TRUE );
        if ($address_4 != '')
        {
            if ($address != '') { $address .= $separator; }
            $address .= $address_4;
        }
        $address_postcode = get_post_meta( $office_id, '_office_address_postcode', TRUE );
        if ($address_postcode != '')
        {
            if ($address != '') { $address .= $separator; }
            $address .= $address_postcode;
        }
        echo $address;

        $department = $property->department;
        $telephone = get_post_meta( $office_id, '_office_telephone_number_' . str_replace("residential-", "", $department), TRUE );
        $email = get_post_meta( $office_id, '_office_email_address_' . str_replace("residential-", "", $department), TRUE );

        if ( $telephone != '' )
        {
        	if ($address != '') { echo '<br>'; }
        	echo 'T: ' . $telephone;
        }
        if ( $email != '' )
        {
        	if ($address != '' || $telephone != '' ) { echo '<br>'; }
        	echo 'E: ' . $email;
        }
	?>

	<?php
		}
	?>

</div>
<div style="clear:both"></div>