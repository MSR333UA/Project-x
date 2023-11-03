<?php
/**
 * Frontend form allowing users to submit properties
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$settings = get_option( 'propertyhive_frontend_property_submissions', array() );

if ( isset($settings['logged_in']) && $settings['logged_in'] == 1 && !is_user_logged_in() )
{
	// Requires login but not logged in

	$login_url = ( isset($settings['login_url']) && $settings['login_url'] != '' ) ? $settings['login_url'] : wp_login_url();
	if ( $login_url == wp_login_url() )
	{
		$login_url = wp_login_url( get_permalink() );
	}

	$register_url = ( isset($settings['register_url']) && $settings['register_url'] != '' ) ? $settings['register_url'] : wp_registration_url();
?>
<div class="ph-login-to-submit">

	<p><?php echo __( 'You need to <a href="' . $login_url . '">login</a> or <a href="' . $register_url . '">register</a> in order to submit properties', 'propertyhive' ); ?>.</p>

</div>
<?php
}
else
{
	// Either doesn't require login or logged in already

	$departments = array();
    $default_department = '';
    if ( get_option( 'propertyhive_active_departments_sales' ) == 'yes' )
    {
        $departments['residential-sales'] = __( 'Sales', 'propertyhive' );
        if ($default_department == '' && (get_option( 'propertyhive_primary_department' ) == 'residential-sales' || get_option( 'propertyhive_primary_department' ) === FALSE) )
        {
            $default_department = 'residential-sales';
        }
    }
    if ( get_option( 'propertyhive_active_departments_lettings' ) == 'yes' )
    {
        $departments['residential-lettings'] = __( 'Lettings', 'propertyhive' );
        if ($default_department == '' && get_option( 'propertyhive_primary_department' ) == 'residential-lettings')
        {
            $default_department = 'residential-lettings';
        }
    }
    if ( get_option( 'propertyhive_active_departments_commercial' ) == 'yes' )
    {
        $departments['commercial'] = __( 'Commercial', 'propertyhive' );
        if ($default_department == '' && get_option( 'propertyhive_primary_department' ) == 'commercial')
        {
            $default_department = 'commercial';
        }
    }

    if ( $post->post_type == 'property' )
    {
    	$default_department = get_post_meta( $post->ID, '_department', true );
    }

    $price_options = get_commercial_price_units();
?>
<form class="propertyhive-form propertyhive-submit-property-form" name="propertyhive_submit_property_form" id="propertyhive_submit_property_form" method="POST" action="" enctype="multipart/form-data">

	<div id="submitPropertySuccess" style="display:none;" class="alert alert-success alert-box success">
        <?php _e( 'Thank you. Your property has been submitted succesfully and will be moderated by a member of our team shortly', 'propertyhive' ); ?>
    </div>
    <div id="submitPropertyError" style="display:none;" class="alert alert-danger alert-box">
        <?php _e( 'An error occurred whilst trying to submit your property. Please try again.', 'propertyhive' ); ?>
    </div>
    <div id="submitPropertyValidation" style="display:none;" class="alert alert-danger alert-box">
        <?php _e( 'Please ensure all required fields have been completed', 'propertyhive' ); ?>
    </div>

	<div class="steps">

		<div class="step" id="step1">

			<h3>1. <?php echo __( 'Property Address', 'propertyhive' ); ?></h3>

			<?php
				$fields = array();

				$fields['reference_number'] = array(
			        'type' => 'text',
			        'show_label' => true, 
			        'label' => __( 'Reference Number', 'propertyhive' ),
			    );
			    
			    $fields['address_name_number'] = array(
			        'type' => 'text',
			        'show_label' => true, 
			        'label' => __( 'Building Name/Number', 'propertyhive' )
			    );

			    $fields['address_street'] = array(
			        'type' => 'text',
			        'show_label' => true, 
			        'label' => __( 'Street', 'propertyhive' )
			    );

			    $fields['address_two'] = array(
			        'type' => 'text',
			        'show_label' => true, 
			        'label' => __( 'Address Line 2', 'propertyhive' )
			    );

			    $fields['address_three'] = array(
			        'type' => 'text',
			        'show_label' => true, 
			        'label' => __( 'Town / City', 'propertyhive' )
			    );

			    $fields['address_four'] = array(
			        'type' => 'text',
			        'show_label' => true, 
			        'label' => __( 'County / State', 'propertyhive' )
			    );

			    $fields['address_postcode'] = array(
			        'type' => 'text',
			        'show_label' => true, 
			        'label' => __( 'Postcode / Zip Code', 'propertyhive' )
			    );

			    $fields['display_address'] = array(
			        'type' => 'text',
			        'show_label' => true, 
			        'label' => __( 'Display Address', 'propertyhive' ) . ' <span class="required">*</span>',
			        'placeholder' => __( 'How the address will be shown to the public', 'propertyhive' ),
			        'value' => ( $post->post_type == 'property' ? get_the_title() : '' )
			    );

			    $fields = apply_filters( 'propertyhive_submit_property_address_form_fields', $fields );

			    foreach ( $fields as $key => $field )
			    {
			    	ph_form_field( $key, $field );
			    }
			?>

		</div>

		<div class="step" id="step2">

			<h3>2. <?php echo __( 'Property Details', 'propertyhive' ); ?></h3>

			<?php
				$fields = array();

				$fields['department'] = array(
			        'type' => 'radio',
			        'show_label' => true, 
			        'label' => __( 'Department', 'propertyhive' ),
			        'options' => $departments,
			        'value' => $default_department
			    );

			    $options = array();
                $args = array(
                    'hide_empty' => false,
                    'parent' => 0
                );
                $terms = get_terms( 'availability', $args );

                $term_list = array();

                if ( !empty( $terms ) && !is_wp_error( $terms ) )
                {
                    foreach ($terms as $term)
                    {
                        $options[$term->term_id] = $term->name;
                    }

                    if ( $post->post_type == 'property' )
                    {
                    	$term_list = wp_get_post_terms($post->ID, 'availability', array("fields" => "ids"));
                    }
                }
                $fields['availability'] = array(
			        'type' => 'select',
			        'show_label' => true, 
			        'label' => __( 'Availability', 'propertyhive' ),
			        'options' => $options,
			        'value' => ( (!empty($term_list)) ? $term_list[0] : '' ),
			    );

			    $fields = apply_filters( 'propertyhive_submit_property_details_form_fields', $fields );

			    foreach ( $fields as $key => $field )
			    {
			    	ph_form_field( $key, $field );
			    }
			?>

			<div class="residential-sales-only"<?php if ( $default_department != 'residential-sales' ) { echo ' style="display:none;"'; } ?>>
			<?php
				$fields = array();

				$fields['price'] = array(
			        'type' => 'text',
			        'show_label' => true, 
			        'label' => __( 'Price', 'propertyhive' ) . ' <span class="required">*</span>'
			    );

			    $fields['sale_poa'] = array(
			        'type' => 'checkbox',
			        'show_label' => true, 
			        'label' => __( 'Price On Application', 'propertyhive' ),
			        'value' => '1',
			    );

			    $options = array( '' => '' );
		        $args = array(
		            'hide_empty' => false,
		            'parent' => 0
		        );
		        $terms = get_terms( 'price_qualifier', $args );
		        
		        if ( !empty( $terms ) && !is_wp_error( $terms ) )
		        {
		            foreach ($terms as $term)
		            {
		                $options[$term->term_id] = $term->name;
		            }
		        }
		        $fields['price_qualifier'] = array(
			        'type' => 'select',
			        'show_label' => true, 
			        'label' => __( 'Price Qualifier', 'propertyhive' ),
			        'options' => $options
			    );

			    $options = array( '' => '' );
		        $args = array(
		            'hide_empty' => false,
		            'parent' => 0
		        );
		        $terms = get_terms( 'sale_by', $args );
		        
		        $term_list = array();

		        if ( !empty( $terms ) && !is_wp_error( $terms ) )
		        {
		            foreach ($terms as $term)
		            {
		                $options[$term->term_id] = $term->name;
		            }

                    if ( $post->post_type == 'property' )
                    {
			            $term_list = wp_get_post_terms($post->ID, 'sale_by', array("fields" => "ids"));
			        }
		        }
		        $fields['sale_by'] = array(
			        'type' => 'select',
			        'show_label' => true, 
			        'label' => __( 'Sale By', 'propertyhive' ),
			        'options' => $options,
			        'value' => ( (!empty($term_list)) ? $term_list[0] : '' ),
			    );

			    $options = array( '' => '' );
		        $args = array(
		            'hide_empty' => false,
		            'parent' => 0
		        );
		        $terms = get_terms( 'tenure', $args );
		        
		        $term_list = array();

		        if ( !empty( $terms ) && !is_wp_error( $terms ) )
		        {
		            foreach ($terms as $term)
		            {
		                $options[$term->term_id] = $term->name;
		            }

                    if ( $post->post_type == 'property' )
                    {
		            	$term_list = wp_get_post_terms($post->ID, 'tenure', array("fields" => "ids"));
		            }
		        }
		        $fields['tenure'] = array(
			        'type' => 'select',
			        'show_label' => true, 
			        'label' => __( 'Tenure', 'propertyhive' ),
			        'options' => $options,
			        'value' => ( (!empty($term_list)) ? $term_list[0] : '' ),
			    );

		        $fields = apply_filters( 'propertyhive_submit_property_residential_sales_details_form_fields', $fields );

			    foreach ( $fields as $key => $field )
			    {
			    	ph_form_field( $key, $field );
			    }
			?>
			</div>

			<div class="residential-lettings-only"<?php if ( $default_department != 'residential-lettings' ) { echo ' style="display:none;"'; } ?>>
			<?php
				$fields = array();

				$fields['rent'] = array(
			        'type' => 'text',
			        'show_label' => true, 
			        'label' => __( 'Rent', 'propertyhive' ) . ' <span class="required">*</span>'
			    );

			    $fields['rent_frequency'] = array(
			        'type' => 'select',
			        'show_label' => true, 
			        'label' => __( 'Rent Frequency', 'propertyhive' ),
			        'options' => array(
			        	'pw' => __('Per Week', 'propertyhive'),
			        	'pcm' => __('Per Calendar Month', 'propertyhive'),
			        	'pq' => __('Per Quarter', 'propertyhive'),
			        	'pa' => __('Per Annum', 'propertyhive')
			        )
			    );

			    $fields['rent_poa'] = array(
			        'type' => 'checkbox',
			        'show_label' => true, 
			        'label' => __( 'Rent On Application', 'propertyhive' ),
			        'value' => '1',
			    );

			    $fields['deposit'] = array(
			        'type' => 'text',
			        'show_label' => true, 
			        'label' => __( 'Deposit', 'propertyhive' )
			    );

			    $options = array( '' => '' );
		        $args = array(
		            'hide_empty' => false,
		            'parent' => 0
		        );
		        $terms = get_terms( 'furnished', $args );
		        
		        $term_list = array();

		        if ( !empty( $terms ) && !is_wp_error( $terms ) )
		        {
		            foreach ($terms as $term)
		            {
		                $options[$term->term_id] = $term->name;
		            }

                    if ( $post->post_type == 'property' )
                    {
		            	$term_list = wp_get_post_terms($post->ID, 'furnished', array("fields" => "ids"));
		            }
		        }
		        $fields['furnished'] = array(
			        'type' => 'select',
			        'show_label' => true, 
			        'label' => __( 'Furnished', 'propertyhive' ),
			        'options' => $options,
			        'value' => ( (!empty($term_list)) ? $term_list[0] : '' ),
			    );

			    $fields['available_date'] = array(
			        'type' => 'text',
			        'show_label' => true, 
			        'label' => __( 'Available Date', 'propertyhive' ),
			        'placeholder' => 'dd/mm/yyyy'
			    );

			    $fields = apply_filters( 'propertyhive_submit_property_residential_lettings_details_form_fields', $fields );

			    foreach ( $fields as $key => $field )
			    {
			    	ph_form_field( $key, $field );
			    }
			?>
			</div>

			<div class="residential-only"<?php if ( $default_department != 'residential-sales' && $default_department != 'residential-lettings' ) { echo ' style="display:none;"'; } ?>>

			<?php
				$fields = array();

				$fields['bedrooms'] = array(
			        'type' => 'text',
			        'show_label' => true, 
			        'label' => __( 'Bedrooms', 'propertyhive' )
			    );

			    $fields['bathrooms'] = array(
			        'type' => 'text',
			        'show_label' => true, 
			        'label' => __( 'Bathrooms', 'propertyhive' )
			    );

			    $fields['reception_rooms'] = array(
			        'type' => 'text',
			        'show_label' => true, 
			        'label' => __( 'Reception Rooms', 'propertyhive' )
			    );

			    $options = array( '' => '' );
		        $args = array(
		            'hide_empty' => false,
		            'parent' => 0
		        );
		        $terms = get_terms( 'property_type', $args );
		        
		        $term_list = array();

		        if ( !empty( $terms ) && !is_wp_error( $terms ) )
		        {
		            foreach ($terms as $term)
		            {
		                $options[$term->term_id] = $term->name;
		                
		                $args = array(
		                    'hide_empty' => false,
		                    'parent' => $term->term_id
		                );
		                $subterms = get_terms( 'property_type', $args );
		                
		                if ( !empty( $subterms ) && !is_wp_error( $subterms ) )
		                {
		                    foreach ($subterms as $term)
		                    {
		                        $options[$term->term_id] = '- ' . $term->name;
		                    }
		                }
		            }

                    if ( $post->post_type == 'property' )
                    {
		            	$term_list = wp_get_post_terms($post->ID, 'property_type', array("fields" => "ids"));
		            }
		        }
		        $fields['property_type'] = array(
			        'type' => 'select',
			        'show_label' => true, 
			        'label' => __( 'Property Type', 'propertyhive' ),
			        'options' => $options,
			        'value' => ( (!empty($term_list)) ? $term_list[0] : '' ),
			    );

			    $options = array( '' => '' );
		        $args = array(
		            'hide_empty' => false,
		            'parent' => 0
		        );
		        $terms = get_terms( 'parking', $args );

		        $term_list = array();
		        
		        if ( !empty( $terms ) && !is_wp_error( $terms ) )
		        {
		            foreach ($terms as $term)
		            {
		                $options[$term->term_id] = $term->name;
		            }

                    if ( $post->post_type == 'property' )
                    {
			            $term_list = wp_get_post_terms($post->ID, 'parking', array("fields" => "ids"));
			        }
		        }
		        $fields['parking'] = array(
			        'type' => 'select',
			        'show_label' => true, 
			        'label' => __( 'Parking', 'propertyhive' ),
			        'options' => $options,
			        'value' => ( (!empty($term_list)) ? $term_list[0] : '' ),
			    );

			    $options = array( '' => '' );
		        $args = array(
		            'hide_empty' => false,
		            'parent' => 0
		        );
		        $terms = get_terms( 'outside_space', $args );

		        $term_list = array();
		        
		        if ( !empty( $terms ) && !is_wp_error( $terms ) )
		        {
		            foreach ($terms as $term)
		            {
		                $options[$term->term_id] = $term->name;
		            }

                    if ( $post->post_type == 'property' )
                    {
			            $term_list = wp_get_post_terms($post->ID, 'outside_space', array("fields" => "ids"));
			        }
		        }
		        $fields['outside_space'] = array(
			        'type' => 'select',
			        'show_label' => true, 
			        'label' => __( 'Outside Space', 'propertyhive' ),
			        'options' => $options,
			        'value' => ( (!empty($term_list)) ? $term_list[0] : '' ),
			    );

		        $fields = apply_filters( 'propertyhive_submit_property_residential_details_form_fields', $fields );

			    foreach ( $fields as $key => $field )
			    {
			    	ph_form_field( $key, $field );
			    }
			?>

			</div>

			<div class="commercial-only"<?php if ( $default_department != 'commercial' ) { echo ' style="display:none;"'; } ?>>

			<?php
				$fields = array();

			    // available as for sale or to rent
			    $fields['for_sale'] = array(
			        'type' => 'checkbox',
			        'show_label' => true, 
			        'label' => __( 'For Sale', 'propertyhive' )
			    );

			    $fields['to_rent'] = array(
			        'type' => 'checkbox',
			        'show_label' => true, 
			        'label' => __( 'To Rent', 'propertyhive' )
			    );

			    // floor area from to
			    $fields['floor_area_from'] = array(
			        'type' => 'text',
			        'show_label' => true, 
			        'label' => __( 'Floor Area From', 'propertyhive' )
			    );

			    $fields['floor_area_to'] = array(
			        'type' => 'text',
			        'show_label' => true, 
			        'label' => __( 'Floor Area To', 'propertyhive' )
			    );

			    $options = array();

		        foreach ( $price_options as $key => $value )
		        {
		        	$options[$key] = $value;
		        }

			    $fields['floor_area_units'] = array(
			        'type' => 'select',
			        'show_label' => true, 
			        'label' => __( 'Floor Area Units', 'propertyhive' ),
			        'options' => $options,
			    );

			    $options = array( '' => '' );
		        $args = array(
		            'hide_empty' => false,
		            'parent' => 0
		        );
		        $terms = get_terms( 'commercial_property_type', $args );
		        
		        $term_list = array();

		        if ( !empty( $terms ) && !is_wp_error( $terms ) )
		        {
		            foreach ($terms as $term)
		            {
		                $options[$term->term_id] = $term->name;
		                
		                $args = array(
		                    'hide_empty' => false,
		                    'parent' => $term->term_id
		                );
		                $subterms = get_terms( 'commercial_property_type', $args );
		                
		                if ( !empty( $subterms ) && !is_wp_error( $subterms ) )
		                {
		                    foreach ($subterms as $term)
		                    {
		                        $options[$term->term_id] = '- ' . $term->name;
		                    }
		                }
		            }

                    if ( $post->post_type == 'property' )
                    {
		            	$term_list = wp_get_post_terms($post->ID, 'commercial_property_type', array("fields" => "ids"));
		            }
		        }
		        $fields['commercial_property_type'] = array(
			        'type' => 'select',
			        'show_label' => true, 
			        'label' => __( 'Property Type', 'propertyhive' ),
			        'options' => $options,
			        'value' => ( (!empty($term_list)) ? $term_list[0] : '' ),
			    );

		        $fields = apply_filters( 'propertyhive_submit_property_commercial_details_form_fields', $fields );

			    foreach ( $fields as $key => $field )
			    {
			    	ph_form_field( $key, $field );
			    }
			?>

			</div>

			<div class="commercial-sales-only" style="display:none;">

			<?php
				$fields = array();

				$fields['price_from'] = array(
			        'type' => 'text',
			        'show_label' => true, 
			        'label' => __( 'Price From', 'propertyhive' )
			    );

			    $fields['price_to'] = array(
			        'type' => 'text',
			        'show_label' => true, 
			        'label' => __( 'Price To', 'propertyhive' )
			    );

			    $options = array('' => '');

		        foreach ( $price_options as $key => $value )
		        {
		        	$options[$key] = $value;
		        }

			    $fields['price_units'] = array(
			        'type' => 'select',
			        'show_label' => true, 
			        'label' => __( 'Price Units', 'propertyhive' ),
			        'options' => $options,
			    );

				$fields = apply_filters( 'propertyhive_submit_property_commercial_sales_details_form_fields', $fields );

			    foreach ( $fields as $key => $field )
			    {
			    	ph_form_field( $key, $field );
			    }
			?>

			</div>

			<div class="commercial-rent-only" style="display:none;">

				<?php
				$fields = array();

				$fields['rent_from'] = array(
			        'type' => 'text',
			        'show_label' => true, 
			        'label' => __( 'Rent From', 'propertyhive' )
			    );

			    $fields['rent_to'] = array(
			        'type' => 'text',
			        'show_label' => true, 
			        'label' => __( 'Rent To', 'propertyhive' )
			    );

			    $options = array(
		        	'pw' => __('Per Week', 'propertyhive'),
		        	'pcm' => __('Per Calendar Month', 'propertyhive'),
		        	'pq' => __('Per Quarter', 'propertyhive'),
		        	'pa' => __('Per Annum', 'propertyhive')
		        );

		        foreach ( $price_options as $key => $value )
		        {
		        	$options[$key] = $value;
		        }

			    $fields['rent_units'] = array(
			        'type' => 'select',
			        'show_label' => true, 
			        'label' => __( 'Rent Frequency', 'propertyhive' ),
			        'options' => $options,
			    );

				$fields = apply_filters( 'propertyhive_submit_property_commercial_rent_details_form_fields', $fields );

			    foreach ( $fields as $key => $field )
			    {
			    	ph_form_field( $key, $field );
			    }
			?>

			</div>

		</div>

		<div class="step" id="step3">

			<h3>3. <?php echo __( 'Descriptions', 'propertyhive' ); ?></h3>

			<strong><?php echo __( 'Summary Description', 'propertyhive' ); ?></strong>
			<?php 
				$settings =   array(
				    'wpautop' => true, // use wpautop?
				    'media_buttons' => false, // show insert/upload button(s)
				    'textarea_name' => 'excerpt', // set the textarea name to something different, square brackets [] can be used here
				    'textarea_rows' => 4, // rows="..."
				    'tabindex' => '',
				    'editor_css' => '', // intended for extra styles for both visual and HTML editors buttons, needs to include the <style> tags, can use "scoped".
				    'editor_class' => '', // add extra class(es) to the editor textarea
				    'teeny' => true, // output the minimal editor config used in Press This
				    'dfw' => false, // replace the default fullscreen with DFW (supported on the front-end in WordPress 3.4)
				    'quicktags' 	=> array( 'buttons' => 'em,strong,link' ),
					'tinymce' 	=> array(
						'toolbar1' => 'bold,italic',
						'toolbar2' => '',
					),
				);
				wp_editor( ( ($post->post_type == 'property') ? get_the_excerpt() : '' ), 'excerpt', $settings ); 
			?>
			<br>
			<strong><?php echo __( 'Full Description', 'propertyhive' ); ?></strong>
			<?php 
				$settings =   array(
				    'wpautop' => true, // use wpautop?
				    'media_buttons' => false, // show insert/upload button(s)
				    'textarea_name' => 'full_description', // set the textarea name to something different, square brackets [] can be used here
				    'textarea_rows' => 9, // rows="..."
				    'tabindex' => '',
				    'editor_css' => '', // intended for extra styles for both visual and HTML editors buttons, needs to include the <style> tags, can use "scoped".
				    'editor_class' => '', // add extra class(es) to the editor textarea
				    'teeny' => true, // output the minimal editor config used in Press This
				    'dfw' => false, // replace the default fullscreen with DFW (supported on the front-end in WordPress 3.4)
				    'quicktags' 	=> array( 'buttons' => 'em,strong,link' ),
					'tinymce' 	=> array(
						'toolbar1' => 'bold,italic',
						'toolbar2' => '',
					),
				);
				wp_editor( ( ($post->post_type == 'property') ? $property->get_formatted_description() : '' ), 'full_description', $settings ); 
			?>
			<br>
			<strong><?php echo __( 'Features', 'propertyhive' ); ?></strong>

			<?php 
				if ( get_option('propertyhive_features_type') == 'checkbox' )
        		{
        			$features = array();
		            $args = array(
		                'hide_empty' => false,
		                'parent' => 0
		            );
		            $terms = get_terms( 'property_feature', $args );
		            
		            if ( !empty( $terms ) && !is_wp_error( $terms ) )
		            {
		                foreach ($terms as $term)
		                { 
		                    $features[$term->term_id] = $term->name;
		                }
		            }

		            if ( !empty($features) )
		            {
		            	echo '<div class="features">';

		            	$selected_values = array();
		                $term_list = wp_get_post_terms($property->id, 'property_feature', array("fields" => "ids"));
		                if ( !is_wp_error($term_list) && is_array($term_list) && !empty($term_list) )
		                {
		                    foreach ( $term_list as $term_id )
		                    {
		                        $selected_values[] = $term_id;
		                    }
		                }

		                foreach ( $features as $term_id => $name )
                		{
                			echo '<label><input type="checkbox" name="feature[]" value="' . $term_id .'"> ' . $name . '</label>';
                		}

                		echo '</div>';
		            }
        		}
        		else
        		{
        			$features = array();
					if ( $post->post_type == 'property' )
					{
						$features = $property->get_features();
					}

					for ( $i = 0; $i < 10; ++$i ) 
					{ 
						$value = '';
						if ( isset($features[$i]) )
						{
							$value = $features[$i];
						}
				?>
				<div class="control control-feature control-feature-<?php echo $i + 1; ?>">
					<label for="feature_<?php echo $i; ?>"><?php _e( 'Feature ' . ($i + 1), 'propertyhive' ); ?></label>
					<input type="text" name="feature[]" id="feature_<?php echo $i; ?>" value="<?php echo $value; ?>">
				</div>
				<?php 
					}
				} 
			?>

		</div>

		<div class="step" id="step4">

			<h3>4. <?php echo __( 'Media', 'propertyhive' ); ?></h3>

			<div class="media media-images">

				<strong><?php echo __( 'Images', 'propertyhive' ); ?></strong>

				<?php
					if ( $post->post_type == 'property' )
                    {
                    	$attachment_ids = $property->get_gallery_attachment_ids();

                    	if ( !empty($attachment_ids) )
                    	{
                    		echo '<div class="previous-media">';

                    		foreach ( $attachment_ids as $attachment_id )
                    		{
                    			$image = wp_get_attachment_image( $attachment_id, 'thumbnail' );
                    			echo '<div>';
                    			echo $image;
                    			echo '<label><input type="checkbox" name="delete_photo[]" value="' . $attachment_id . '"> Delete</label>';
                    			echo '</div>';
                    		}

                    		echo '</div>';
                    	}
                    }
				?>

				<?php for ( $i = 0; $i < apply_filters( 'propertyhive_submit_property_num_images', 6 ); ++$i ) { ?>
				<div class="file-upload">
					<label for="photo_<?php echo $i; ?>"><?php _e( 'Upload Photo', 'propertyhive' ); ?></label>
					<input type="file" name="photo[]" id="photo_<?php echo $i; ?>">
				</div>
				<?php } ?>

			</div>
			
			<div class="media media-floorplans">

				<strong><?php echo __( 'Floorplans', 'propertyhive' ); ?></strong>

				<?php
					if ( $post->post_type == 'property' )
                    {
                    	$attachment_ids = $property->get_floorplan_attachment_ids();

                    	if ( !empty($attachment_ids) )
                    	{
                    		echo '<div class="previous-media">';

                    		foreach ( $attachment_ids as $attachment_id )
                    		{
                    			$image = wp_get_attachment_image( $attachment_id, 'thumbnail' );
                    			echo '<div>';
                    			echo $image;
                    			echo '<label><input type="checkbox" name="delete_floorplan[]" value="' . $attachment_id . '"> Delete</label>';
                    			echo '</div>';
                    		}

                    		echo '</div>';
                    	}
                    }
				?>

				<?php for ( $i = 0; $i < apply_filters( 'propertyhive_submit_property_num_floorplans', 1 ); ++$i ) { ?>
				<div class="file-upload">
					<label for="floorplan_<?php echo $i; ?>"><?php _e( 'Upload Floorplan', 'propertyhive' ); ?></label>
					<input type="file" name="floorplan[]" id="floorplan_<?php echo $i; ?>">
				</div>
				<?php } ?>

			</div>

			<div class="media media-brochures">

				<strong><?php echo __( 'Brochures', 'propertyhive' ); ?></strong>

				<?php
					if ( $post->post_type == 'property' )
                    {
                    	$attachment_ids = $property->get_brochure_attachment_ids();

                    	if ( !empty($attachment_ids) )
                    	{
                    		echo '<div class="previous-media">';

                    		foreach ( $attachment_ids as $attachment_id )
                    		{
                    			$url = wp_get_attachment_url( $attachment_id );
                    			echo '<div>';
                    			echo '<a href="' . $url . '" target="_blank">View Brochure</a>';
                    			echo '<label><input type="checkbox" name="delete_brochure[]" value="' . $attachment_id . '"> Delete</label>';
                    			echo '</div>';
                    		}

                    		echo '</div>';
                    	}
                    }
				?>

				<?php for ( $i = 0; $i < apply_filters( 'propertyhive_submit_property_num_brochures', 1 ); ++$i ) { ?>
				<div class="file-upload">
					<label for="brochure_<?php echo $i; ?>"><?php _e( 'Upload Brochure', 'propertyhive' ); ?></label>
					<input type="file" name="brochure[]" id="brochure_<?php echo $i; ?>">
				</div>
				<?php } ?>

			</div>

			<div class="media media-epcs">

				<strong><?php echo __( 'Energy Performance Certificates', 'propertyhive' ); ?></strong>

				<?php
					if ( $post->post_type == 'property' )
                    {
                    	$attachment_ids = $property->get_epc_attachment_ids();

                    	if ( !empty($attachment_ids) )
                    	{
                    		echo '<div class="previous-media">';

                    		foreach ( $attachment_ids as $attachment_id )
                    		{
                    			$url = wp_get_attachment_url( $attachment_id );
                    			echo '<div>';
                    			echo '<a href="' . $url . '" target="_blank">View EPC</a>';
                    			echo '<label><input type="checkbox" name="delete_epc[]" value="' . $attachment_id . '"> Delete</label>';
                    			echo '</div>';
                    		}

                    		echo '</div>';
                    	}
                    }
				?>

				<?php for ( $i = 0; $i < apply_filters( 'propertyhive_submit_property_num_epcs', 1 ); ++$i ) { ?>
				<div class="file-upload">
					<label for="epc_<?php echo $i; ?>"><?php _e( 'Upload EPC', 'propertyhive' ); ?></label>
					<input type="file" name="epc[]" id="epc_<?php echo $i; ?>">
				</div>
				<?php } ?>

			</div>

			<div class="media media-virtual-tours">

				<strong><?php echo __( 'Virtual Tour', 'propertyhive' ); ?></strong>

				<?php
					$value = '';
					if ( $post->post_type == 'property' )
					{
						$virtual_tours = $property->get_virtual_tour_urls();
						if ( !empty($virtual_tours) )
						{
							$value = $virtual_tours[0];
						}
					}
				?>

				<div class="control control-virtual-tour">
					<label for="virtual_tour"><?php _e( 'Virtual Tour URL', 'propertyhive' ); ?></label>
					<input type="text" name="virtual_tour" id="virtual_tour" placeholder="http://" value="<?php echo $value; ?>">
				</div>

			</div>

		</div>

		<br>
		<input type="hidden" name="property_post_id" value="<?php echo ( (isset($_GET['property_post_id'])) ? $_GET['property_post_id'] : '' ); ?>">
		<input type="submit" value="<?php echo ( ( $post->post_type == 'property' ) ? __( 'Update Property', 'propertyhive' ) : __( 'Submit Property', 'propertyhive' ) ); ?>">

	</div>

</form>

<script>
	var is_submitting = false;
	var form_obj;

	function submit_form_hide_show_fields()
	{
		var selected_department = jQuery('form[name=\'propertyhive_submit_property_form\'] input[name=\'department\']').filter(':checked').val();

		jQuery('.residential-sales-only').hide();
		jQuery('.residential-lettings-only').hide();
		jQuery('.residential-only').hide();
		jQuery('.commercial-only').hide();
		jQuery('.commercial-sales-only').hide();
		jQuery('.commercial-rent-only').hide();

		jQuery('.' + selected_department + '-only').show();
		if ( selected_department == 'residential-sales' || selected_department == 'residential-lettings' )
		{
			jQuery('.residential-only').show();
		}
		if ( selected_department == 'commercial' )
		{
			if ( jQuery('input[name=\'for_sale\']').prop('checked') == true )
			{
				jQuery('.commercial-sales-only').show();
			}
			if ( jQuery('input[name=\'to_rent\']').prop('checked') == true )
			{
				jQuery('.commercial-rent-only').show();
			}
		}
	}

	jQuery( function($){

		// Department changed
		$('form[name=\'propertyhive_submit_property_form\'] input[name=\'department\']').change(function()
		{
			submit_form_hide_show_fields();
		});

		// Commercial for sale/to rent changed
		$('form[name=\'propertyhive_submit_property_form\'] input[name=\'for_sale\']').change(function()
		{
			submit_form_hide_show_fields();
		});
		$('form[name=\'propertyhive_submit_property_form\'] input[name=\'to_rent\']').change(function()
		{
			submit_form_hide_show_fields();
		});
	    
	    // Form being submitted
	    $('body').on('submit', 'form[name=\'propertyhive_submit_property_form\']', function()
	    {
	        if (!is_submitting)
	        {
	            is_submitting = true;

	            var original_button_value = $('#propertyhive_submit_property_form input[type=\'submit\']').val();
	            $('#propertyhive_submit_property_form input[type=\'submit\']').attr('disabled', 'disabled');
	            $('#propertyhive_submit_property_form input[type=\'submit\']').val('<?php _e( 'Submitting', 'propertyhive' ); ?>...');
	            
	            //var data = $(this).serialize() + '&'+$.param({ 'action': 'propertyhive_frontend_property_submission' });
	           	var formData = new FormData();
	           	$('#propertyhive_submit_property_form input[type=\'text\'], #propertyhive_submit_property_form input[type=\'hidden\'], #propertyhive_submit_property_form input[type=\'number\'], #propertyhive_submit_property_form input[type=\'date\'], textarea, select').each(function()
	           	{
	           		formData.append($(this).attr('name'), $(this).val());
	           	});

	           	$('#propertyhive_submit_property_form input[type=\'checkbox\']:checked').each(function()
	           	{
	           		formData.append($(this).attr('name'), $(this).val());
	           	});

	           	$('#propertyhive_submit_property_form input[type=\'radio\']:checked').each(function()
	           	{
	           		formData.append($(this).attr('name'), $(this).val());
	           	});

	           	formData.append('action', 'propertyhive_frontend_property_submission');

	           	$.each($("input[type='file']"), function(i, obj) {
			        $.each(obj.files,function(j,file){
			            formData.append(obj.name, file);
			        });
				});

	            form_obj = $(this);

	            form_obj.find('#submitPropertySuccess').hide();
	            form_obj.find('#submitPropertyValidation').hide();
	            form_obj.find('#submitPropertyError').hide();

	            var scrollTop = $(this).offset().top;

	            $.ajax({
	            	url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
	            	type: 'POST',
				    data: formData,
				    contentType: false,
				    processData: false,
				    success: function(response)
				    {
				    	$('html,body').animate({
		            		scrollTop: scrollTop
		            	});

		                if (response.success == true)
		                {
		                    form_obj.find('#submitPropertySuccess').fadeIn();
		                    
		                    <?php if ( $post->post_type != 'property' ) { ?>
		                    form_obj.trigger("reset");
		                    <?php } ?>
		                }
		                else
		                {
		                    if (response.reason == 'validation')
		                    {
		                        form_obj.find('#submitPropertyValidation').fadeIn();
		                    }
		                    else
		                    {
		                        form_obj.find('#submitPropertyError').fadeIn();
		                    }
		                }
		                
		                is_submitting = false;

		                $('#propertyhive_submit_property_form input[type=\'submit\']').attr('disabled', false);
	            		$('#propertyhive_submit_property_form input[type=\'submit\']').val(original_button_value);
				    }
	            });
	        }

	        return false;
	    });

	});
</script>
<?php
}