<h1><?php the_title(); ?></h1>

<h2><?php echo $property->get_formatted_price(); ?></h2>

<?php
	$gallery_attachment_ids = $property->get_gallery_attachment_ids();
?>
<div style="float:left; width:68%;">

			<?php
				if ( !empty($gallery_attachment_ids) )
				{
			?>
			<img style="width:100%;" src="<?php if ($pdf) { echo get_attached_file( $gallery_attachment_ids[0] ); }else{ $image = wp_get_attachment_image_src( $gallery_attachment_ids[0], 'full' ); echo $image[0]; } ?>" alt="">
			<?php 
				}
			?>

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

			<h3><?php echo __( 'Full Description', 'propertyhive' ); ?></h3>
			<?php
				$full_description = $property->get_formatted_description();
				if ( trim($full_description) != '' )
				{
					echo $full_description;
				}
				else
				{
					the_excerpt();
				}
			?>

			<?php
				$office_id = $property->office_id;
				if ( $office_id != '' )
				{
			?>
			<br>
			<h3><?php echo __( 'Contact Us', 'propertyhive' ); ?></h3>

			<strong><?php echo get_bloginfo('name'); ?></strong><br>
			<?php
					$address = '';
					$separator = ', ';
	        
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

				}
			?>

</div>

<div style="float:right; width:29%;">


			<?php
				if ( !empty($gallery_attachment_ids) && count($gallery_attachment_ids) > 0 )
				{
					$i = 0;
					$j = 0;
					foreach ( $gallery_attachment_ids as $attachment_id )
					{
						if ( $i == 0 )
						{
							// Skip the first image
							++$i;
							continue;
						}
						/*if ( $j == 4 )
						{
							echo '</div>';
							$j = 0;
						}
						if ( $j == 0 )
						{
							echo '<div style="float:right; width:29%;">';
						}*/
			?><img style="width:100%; padding-bottom:2px;" src="<?php if ($pdf) { echo get_attached_file( $attachment_id ); }else{ $image = wp_get_attachment_image_src( $attachment_id, 'large' ); echo $image[0]; } ?>" alt=""><?php 
						echo '<br>';
						++$i;
						++$j;
					}
				}

				$floorplan_attachment_ids = $property->get_floorplan_attachment_ids();

				if ( !empty($floorplan_attachment_ids) )
				{
					foreach ( $floorplan_attachment_ids as $attachment_id )
					{
						// Only show images
						$attachment_filename = basename( get_attached_file( $attachment_id ) );
						if ( 
							strpos(strtolower($attachment_filename), 'jpg') || 
							strpos(strtolower($attachment_filename), 'jpeg') || 
							strpos(strtolower($attachment_filename), 'png') || 
							strpos(strtolower($attachment_filename), 'bmp') || 
							strpos(strtolower($attachment_filename), 'gif') 
						)
						{

							/*if ( $j == 4 )
							{
								echo '</div>';
								$j = 0;
							}
							if ( $j == 0 )
							{
								echo '';
							}*/
							
			?>
			<img style="width:100%; padding-bottom:2px;" src="<?php if ($pdf) { echo get_attached_file( $attachment_id ); }else{ $image = wp_get_attachment_image_src( $attachment_id, 'large' ); echo $image[0]; } ?>" alt="">
			<?php
							echo '<br>';
							++$j;
						}
					}
				}

				$epc_attachment_ids = $property->get_epc_attachment_ids();

				if ( !empty($epc_attachment_ids) )
				{
					foreach ( $epc_attachment_ids as $attachment_id )
					{
						// Only show images
						$attachment_filename = basename( get_attached_file( $attachment_id ) );
						if ( 
							strpos(strtolower($attachment_filename), 'jpg') || 
							strpos(strtolower($attachment_filename), 'jpeg') || 
							strpos(strtolower($attachment_filename), 'png') || 
							strpos(strtolower($attachment_filename), 'bmp') || 
							strpos(strtolower($attachment_filename), 'gif') 
						)
						{

							/*if ( $j == 4 )
							{
								echo '</div>';
								$j = 0;
							}
							if ( $j == 0 )
							{
								echo '';
							}*/
							
			?>
			<img style="width:100%; padding-bottom:2px;" src="<?php if ($pdf) { echo get_attached_file( $attachment_id ); }else{ $image = wp_get_attachment_image_src( $attachment_id, 'large' ); echo $image[0]; } ?>" alt="">
			<?php
							echo '<br>';
							++$j;
						}
					}
				}

				if ( $property->latitude != '' && $property->longitude != '' && ini_get('allow_url_fopen') )
				{
					/*if ( $j == 4 )
					{
						echo '</div>';
						$j = 0;
					}
					if ( $j == 0 )
					{
						echo '<div style="float:right; width:29%;">';
					}*/

					$api_key = get_option('propertyhive_google_maps_api_key', '');
			?><img style="width:100%; padding-bottom:2px;" src="https://maps.googleapis.com/maps/api/staticmap?center=<?php echo $property->latitude; ?>,<?php echo $property->longitude; ?>&zoom=12&size=350x270&maptype=roadmap&markers=color:red%7Clabel:%7C<?php echo $property->latitude; ?>,<?php echo $property->longitude; ?>&key=<?php echo $api_key; ?>" alt=""><?php
				}
			?>

</div>