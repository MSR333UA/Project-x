<?php
/**
 * Display properties submitted by logged in user
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $post;

$settings = get_option( 'propertyhive_frontend_property_submissions', array() );

?>

<div class="propertyhive-submitted-properties">

	<?php

		if ($property_query->have_posts())
        {
        	echo '<ul>';
			while ($property_query->have_posts())
        	{
        		$property_query->the_post();

        		$property = new PH_Property( $post->ID );
    ?>
    	<li class="submitted-property">

    		<div class="image">
    			<img src="<?php echo $property->get_main_photo_src(); ?>" alt="<?php the_title(); ?>">
    		</div>

    		<div class="details">

    			<h3><?php echo $property->get_formatted_full_address(); ?></h3>

    			<div class="price"><?php echo $property->get_formatted_price(); ?></div>

    			<div class="status"><?php 
    				if ( $post->post_status == 'draft' ) 
    				{ 
    					echo '<span style="color:#FFA500">' . __( 'Awaiting Moderation', 'propertyhive' ) . '</span>'; 
    				}
    				elseif ( $property->on_market == 'yes' ) 
    				{
    					echo '<span style="color:#090">' . __( 'On Market', 'propertyhive' ) . '</span>'; 
    				}
    				else
    				{
    					echo '<span style="color:#900">' . __( 'Off Market', 'propertyhive' ) . '</span>'; 
    				}
    			?></div>

    			<div class="actions">
	    			<ul>

	    				<?php if ($post->post_status == 'publish') { echo '<li><a href="' . get_permalink() . '" target="_blank">View</a></li>'; } ?>

	    				<?php
	    					if ( isset($settings['allow_editing']) && $settings['allow_editing'] == 1 )
	    					{
	    						if ( isset($settings['edit_url']) && $settings['edit_url'] != '' ) 
	    						{ 
	    							$edit_url_base = $settings['edit_url'];
	    							if ( strpos($edit_url_base, '?') === false )
	    							{
	    								$edit_url = $edit_url_base . '?property_post_id=' . $post->ID;
	    							}
	    							else
	    							{
	    								$edit_url = $edit_url_base . '&property_post_id=' . $post->ID;
	    							}
	    				?>
	    				<li><a href="<?php echo $edit_url; ?>">Edit</a></li>
	    				<?php 
			    				}
		    				}
		    			?>
	    			</ul>
    			</div>

    		</div>

    	</li>
    <?php
        	}
        	echo '</ul>';
		}
		else
		{
	?>
	<div class="no-results">
		<?php _e( 'No properties submitted', 'propertyhive' ); ?>
	</div>
	<?php
		}
		wp_reset_postdata();

	?>

</div>