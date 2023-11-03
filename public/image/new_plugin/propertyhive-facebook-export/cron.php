<?php

error_reporting( 0 );
set_time_limit( 0 );
ini_set('memory_limit','20000M');

global $wpdb, $post;

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

// Check Property Hive Plugin is active as we'll need this
if ( is_plugin_active( 'propertyhive/propertyhive.php' ) )
{

	$portals = array();
    $current_facebookexport_options = get_option( 'propertyhive_facebookexport' );

    if ($current_facebookexport_options !== FALSE)
    {
       	if (isset($current_facebookexport_options['portals']))
       	{
            $portals = $current_facebookexport_options['portals'];
       	}
    }

    if (!empty($portals))
    {
    	$wp_upload_dir = wp_upload_dir();
        if( $wp_upload_dir['error'] !== FALSE )
        {
            die("Unable to create uploads folder. Please check permissions");
        }
        else
        {
            $uploads_dir = $wp_upload_dir['basedir'] . '/ph_facebook/';

            if ( ! @file_exists($uploads_dir) )
            {
                if ( ! @mkdir($uploads_dir) )
                {
                    die("Unable to create directory " . $uploads_dir);
                }
            }
            else
            {
                if ( ! @is_writeable($uploads_dir) )
                {
                    die("Directory " . $uploads_dir . " isn't writeable");
                }
            }
        }

        $PH_Countries = new PH_Countries();

    	foreach ( $portals as $portal_id => $portal )
        {
	        // Loop through portals

	    	// Get properties
            $args = array(
                'post_type' => 'property',
                'nopaging' => true,
                'has_password' => false,
            );

            $meta_query = array(
                array(
                    'key' => '_on_market',
                    'value' => 'yes'
                )
            );

            if ( isset($portal['department']) && $portal['department'] != '' )
            {
                $meta_query[] = array(
                    'key' => '_department',
                    'value' => $portal['department']
                );
            }

            if ( isset($portal['office_id']) && $portal['office_id'] != '' )
            {
                $meta_query[] = array(
                    'key' => '_office_id',
                    'value' => $portal['office_id']
                );
            }

            $args['meta_query'] = $meta_query;

            if ( isset($portal['availability']) && is_array($portal['availability']) && !empty($portal['availability']) )
            {
                $tax_query = array(
                    array(
                        'taxonomy' => 'availability',
                        'terms' => ph_clean( $portal['availability'] )
                    )
                );

                $args['tax_query'] = $tax_query;
            }

            $args = apply_filters( 'propertyhive_facebook_export_query_args', $args );

            $properties_query = new WP_Query( $args );
            $num_properties = $properties_query->found_posts;

            $xml = new SimpleXMLExtendedFacebook("<listings></listings>");

            $xml->addChild('title');
            $xml->title = get_bloginfo('name') . ' Feed';

            $i = 0;

            if ( $properties_query->have_posts() )
            {
                while ( $properties_query->have_posts() )
                {
                    $properties_query->the_post();

                    $property = new PH_Property($post->ID);

                    $listing_xml = $xml->addChild('listing');

                    $listing_xml->addChild('home_listing_id', $property->id);

                    $listing_xml->addChild('name', get_the_title());

                    $availability = $this->get_mapped_value($post->ID, 'availability');
                    $listing_xml->addChild('availability', $availability);

                    $listing_xml->addChild('description');
                    $xml->listing[$i]->description->addCData(strip_tags(get_the_excerpt()));

                    $address_xml = $listing_xml->addChild('address');
                    $address_xml->addAttribute('format', 'simple');

                    $component_i = 0;

                    $component_xml = $address_xml->addChild('component');
                    $component_xml->addAttribute('name', 'addr1');
                    $xml->listing[$i]->address->component[$component_i]->addCData(trim($property->_address_name_number . ' ' . $property->_address_street));
                    ++$component_i;

                    if ( $property->_address_three != '' )
                    {
                        $component_xml = $address_xml->addChild('component');
                        $component_xml->addAttribute('name', 'city');
                        $xml->listing[$i]->address->component[$component_i]->addCData($property->_address_three);
                        ++$component_i;
                    }
                    elseif ( $property->_address_two != '' )
                    {
                        $component_xml = $address_xml->addChild('component');
                        $component_xml->addAttribute('name', 'city');
                        $xml->listing[$i]->address->component[$component_i]->addCData($property->_address_two);
                        ++$component_i;
                    }
                    elseif ( $property->_address_four != '' )
                    {
                        $component_xml = $address_xml->addChild('component');
                        $component_xml->addAttribute('name', 'city');
                        $xml->listing[$i]->address->component[$component_i]->addCData($property->_address_four);
                        ++$component_i;
                    }

                    if ( $property->_address_four != '' )
                    {
                        $component_xml = $address_xml->addChild('component');
                        $component_xml->addAttribute('name', 'region');
                        $xml->listing[$i]->address->component[$component_i]->addCData($property->_address_four);
                        ++$component_i;
                    }
                    elseif ( $property->_address_three != '' )
                    {
                        $component_xml = $address_xml->addChild('component');
                        $component_xml->addAttribute('name', 'region');
                        $xml->listing[$i]->address->component[$component_i]->addCData($property->_address_three);
                        ++$component_i;
                    }
                    elseif ( $property->_address_two != '' )
                    {
                        $component_xml = $address_xml->addChild('component');
                        $component_xml->addAttribute('name', 'region');
                        $xml->listing[$i]->address->component[$component_i]->addCData($property->_address_two);
                        ++$component_i;
                    }

                    $component_xml = $address_xml->addChild('component');
                    $component_xml->addAttribute('name', 'country');

                    $property_country = $property->_address_country;
                    if ( $property_country == '' )
                    {
                        $property_country = get_option( 'propertyhive_default_country', 'GB' );
                    }
                    
                    $country = $PH_Countries->get_country( $property_country );
                    if ( $country === false )
                    {
                        $country = 'United Kingdom';
                    }
                    else
                    {
                        $country = $country['name'];
                    }
                    $xml->listing[$i]->address->component[$component_i] = $country;
                    ++$component_i;

                    if ( $property->_address_postcode != '' )
                    {
                        $component_xml = $address_xml->addChild('component');
                        $component_xml->addAttribute('name', 'postal_code');
                        $xml->listing[$i]->address->component[$component_i]->addCData($property->_address_postcode);
                        ++$component_i;
                    }

                    $listing_xml->addChild('latitude', $property->_latitude);
                    $listing_xml->addChild('longitude', $property->_longitude);

                    $price = number_format($property->_price_actual) . ' GBP';
                    if ( $property->_currency != '' && $property->_currency != 'GBP' )
                    {
                        if ( $property->_department == 'residential-sales' )
                        {
                            $price = number_format($property->_price) . ' ' . $property->_currency;
                        }
                        elseif ( $property->_department == 'residential-lettings' )
                        {
                            $price = number_format($property->_rent) . ' ' . $property->_currency;
                        }
                    }
                    $listing_xml->addChild('price', $price);

                    if ( get_option('propertyhive_images_stored_as', '') == 'urls' )
                    {
                        $photo_urls = get_post_meta($post->ID, '_photo_urls', TRUE);
                        if ( !is_array($photo_urls) ) { $photo_urls = array(); }

                        if ( !empty($photo_urls) )
                        {
                            $j = 0;
                            foreach ( $photo_urls as $photo )
                            {
                                if ( $j >= 20 )
                                {
                                    break;
                                }

                                $image_xml = $listing_xml->addChild('image');
                                $image_url_xml = $image_xml->addChild('url', $photo['url']);

                                ++$j;
                            }
                        }
                    }
                    else
                    {
                        $gallery_attachment_ids = $property->get_gallery_attachment_ids();
                        if ( !empty($gallery_attachment_ids) )
                        {
                            $j = 0;
                            foreach ( $gallery_attachment_ids as $attachment_id )
                            {
                                if ( $j >= 20 )
                                {
                                    break;
                                }

                                $image_xml = $listing_xml->addChild('image');
                                $image_url_xml = $image_xml->addChild('url', wp_get_attachment_url($attachment_id));

                                ++$j;
                            }
                        }
                    }

                    $listing_xml->addChild('listing_type', ( $property->_department == 'residential-lettings' ) ? 'for_rent_by_agent' : 'for_sale_by_agent' );

                    $listing_xml->addChild('url', get_permalink());

                    $listing_xml->addChild('num_beds', $property->_bedrooms);
                    $listing_xml->addChild('num_baths', $property->_bathrooms);
                    $listing_xml->addChild('num_rooms', $property->_reception_rooms);

                    $property_type = $this->get_mapped_value($post->ID, 'property_type');
                    $listing_xml->addChild('property_type', $property_type);

                    $furnished = $this->get_mapped_value($post->ID, 'furnished');
                    $listing_xml->addChild('furnish_type', $furnished);

                    $parking = $this->get_mapped_value($post->ID, 'parking');
                    $listing_xml->addChild('parking_type', $parking);

                    $xml->listing[$i] = apply_filters( 'propertyhive_facebook_export_property_values', $xml->listing[$i], $post->ID );

                    ++$i;
                }
            }

            wp_reset_postdata();

            $xml = $xml->asXML();

            // Write XML string to file
            $handle = fopen($uploads_dir . $portal_id . '.xml', 'w+');
            fwrite($handle, $xml);
            fclose($handle);

	    } // end foreach portals
    }
}

?>