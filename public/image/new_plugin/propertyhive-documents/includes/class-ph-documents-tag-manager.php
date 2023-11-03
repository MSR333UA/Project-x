<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class PH_Documents_Tag_Manager {

	public function replace_property_tags( $merge_tags, $merge_values, $post_id, $recurring = true )
	{
		$property = new PH_Property( (int)$post_id );

		$merge_tags[] = 'property_reference_number';
        $merge_values[] = $property->reference_number;

        $merge_tags[] = 'property_full_address';
        $merge_values[] = $property->get_formatted_full_address();

        $merge_tags[] = 'property_full_address_br';
        $merge_values[] = $property->get_formatted_full_address('</w:t><w:br/><w:t>');

        $merge_tags[] = 'property_price';
        $merge_values[] = $property->price_actual;

        $merge_tags[] = 'property_price_formatted';
        $merge_values[] = html_entity_decode($property->get_formatted_price());

        $merge_tags[] = 'tenure';
        $merge_values[] = $property->tenure;

        $merge_tags = apply_filters( 'propertyhive_document_property_merge_tags', $merge_tags, $post_id );
        $merge_values = apply_filters( 'propertyhive_document_property_merge_values', $merge_values, $post_id );

        if ( !$recurring )
		{
			return array($merge_tags, $merge_values);
		}

        $owner_contact_id = $property->owner_contact_id;
		if ( $owner_contact_id != '' && !is_array($owner_contact_id) )
		{
			$owner_contact_id = array($owner_contact_id);
		}
		if ( is_array($owner_contact_id) && !empty($owner_contact_id) )
		{
			$owner_contact_id = array_shift(array_values($owner_contact_id));
		}
		list($merge_tags, $merge_values) = $this->replace_owner_tags( $merge_tags, $merge_values, $owner_contact_id );

		return array($merge_tags, $merge_values);
	}

	public function replace_applicant_tags( $merge_tags, $merge_values, $post_id )
	{
		list($merge_tags, $merge_values) = $this->replace_contact_tags( $merge_tags, $merge_values, $post_id, 'applicant_' );

		$merge_tags = apply_filters( 'propertyhive_document_applicant_merge_tags', $merge_tags, $post_id );
        $merge_values = apply_filters( 'propertyhive_document_applicant_merge_values', $merge_values, $post_id );

		return array($merge_tags, $merge_values);
	}

	public function replace_owner_tags( $merge_tags, $merge_values, $post_id )
	{
		list($merge_tags, $merge_values) = $this->replace_contact_tags( $merge_tags, $merge_values, $post_id, 'owner_' );

		$merge_tags = apply_filters( 'propertyhive_document_owner_merge_tags', $merge_tags, $post_id );
        $merge_values = apply_filters( 'propertyhive_document_owner_merge_values', $merge_values, $post_id );

		return array($merge_tags, $merge_values);
	}

	public function replace_applicant_solicitor_tags( $merge_tags, $merge_values, $post_id )
	{
		return $this->replace_contact_tags( $merge_tags, $merge_values, $post_id, 'applicant_solicitor_' );
	}

	public function replace_owner_solicitor_tags( $merge_tags, $merge_values, $post_id )
	{
		return $this->replace_contact_tags( $merge_tags, $merge_values, $post_id, 'owner_solicitor_' );
	}

	public function replace_contact_tags( $merge_tags, $merge_values, $post_id, $prefix = '' )
	{
		$contact = new PH_Contact( (int)$post_id );

		$merge_tags[] = $prefix . 'name';
		$merge_values[] = str_replace('&', '&amp;', get_post_field( 'post_title', $post_id, 'raw' ));

        $merge_tags[] = $prefix . 'full_address';
        $merge_values[] = str_replace('&', '&amp;', $contact->get_formatted_full_address());

        $merge_tags[] = $prefix . 'full_address_br';
        $merge_values[] = str_replace('&', '&amp;', $contact->get_formatted_full_address('</w:t><w:br/><w:t>'));

        $merge_tags[] = $prefix . 'telephone_number';
        $merge_values[] = $contact->telephone_number;

        $merge_tags[] = $prefix . 'email_address';
        $merge_values[] = $contact->email_address;

        $merge_tags[] = $prefix . 'dear';
        $merge_values[] = str_replace('&', '&amp;', $contact->dear());

        $merge_tags = apply_filters( 'propertyhive_document_contact_merge_tags', $merge_tags, $post_id, $prefix  );
        $merge_values = apply_filters( 'propertyhive_document_contact_merge_values', $merge_values, $post_id, $prefix  );

		return array($merge_tags, $merge_values);
	}

	public function replace_appraisal_tags( $merge_tags, $merge_values, $post_id, $recurring = true )
	{
		$appraisal = new PH_Appraisal( (int)$post_id );

		list($merge_tags, $merge_values) = $this->replace_event_tags( $merge_tags, $merge_values, 'appraisal', $appraisal );

		$merge_tags[] = 'appraisal_full_address';
		$merge_values[] = $appraisal->get_formatted_full_address();

		$merge_tags[] = 'appraisal_full_address_br';
		$merge_values[] = $appraisal->get_formatted_full_address('</w:t><w:br/><w:t>');

		$merge_tags[] = 'valued_price';
		if ( $appraisal->department == 'residential-sales' )
		{
			$merge_values[] = $appraisal->valued_price;
		}
		else
		{
			$merge_values[] = $appraisal->valued_rent;
		}

		$merge_tags[] = 'valued_price_formatted';
		$merge_values[] = html_entity_decode($appraisal->get_formatted_price());

		$merge_tags = apply_filters( 'propertyhive_document_appraisal_merge_tags', $merge_tags, $post_id );
		$merge_values = apply_filters( 'propertyhive_document_appraisal_merge_values', $merge_values, $post_id );

		if ( !$recurring )
		{
			return array($merge_tags, $merge_values);
		}

		$owner_contact_id = $appraisal->property_owner_contact_id;
		if ( $owner_contact_id != '' && !is_array($owner_contact_id) )
		{
			$owner_contact_id = array($owner_contact_id);
		}
		if ( !empty($owner_contact_id) )
		{
			$owner_contact_id_temp_array = array_values($owner_contact_id);
			$owner_contact_id = array_shift($owner_contact_id_temp_array);
		}
		list($merge_tags, $merge_values) = $this->replace_owner_tags( $merge_tags, $merge_values, $owner_contact_id );

		list($merge_tags, $merge_values) = $this->replace_negotiator_tags( $merge_tags, $merge_values, $appraisal->negotiator_id );

		return array($merge_tags, $merge_values);
	}

	public function replace_viewing_tags( $merge_tags, $merge_values, $post_id, $recurring = true )
	{
		$viewing = new PH_Viewing( (int)$post_id );

		list($merge_tags, $merge_values) = $this->replace_event_tags( $merge_tags, $merge_values, 'viewing', $viewing );

		$merge_tags = apply_filters( 'propertyhive_document_viewing_merge_tags', $merge_tags, $post_id );
        $merge_values = apply_filters( 'propertyhive_document_viewing_merge_values', $merge_values, $post_id );

        if ( !$recurring )
		{
			return array($merge_tags, $merge_values);
		}

		$applicant_contact_id = $viewing->applicant_contact_id;
		list($merge_tags, $merge_values) = $this->replace_applicant_tags( $merge_tags, $merge_values, $applicant_contact_id );

		$property_id = $viewing->property_id;
		list($merge_tags, $merge_values) = $this->replace_property_tags( $merge_tags, $merge_values, $property_id );

		$property = new PH_Property( (int)$property_id );

		$owner_contact_id = $property->owner_contact_id;
		if ( $owner_contact_id != '' && !is_array($owner_contact_id) )
		{
			$owner_contact_id = array($owner_contact_id);
		}
		if ( !empty($owner_contact_id) )
		{
			$owner_contact_id_temp_array = array_values($owner_contact_id);
			$owner_contact_id = array_shift($owner_contact_id_temp_array);
		}
		list($merge_tags, $merge_values) = $this->replace_owner_tags( $merge_tags, $merge_values, $owner_contact_id );

		return array($merge_tags, $merge_values);
	}


	public function replace_offer_tags( $merge_tags, $merge_values, $post_id, $recurring = true )
	{
		$offer = new PH_Offer( (int)$post_id );

		$merge_tags[] = 'offer_amount';
        $merge_values[] = $offer->amount;

        $merge_tags[] = 'offer_amount_formatted';
        $merge_values[] = html_entity_decode($offer->get_formatted_amount());

        $merge_tags[] = 'offer_amount_in_words';
        if (class_exists('NumberFormatter'))
        {
	        $f = new NumberFormatter("en", NumberFormatter::SPELLOUT);
			$merge_values[] = $f->format($offer->amount);
		}
		else
		{
			$merge_values[] = '';
		}

		$merge_tags = apply_filters( 'propertyhive_document_offer_merge_tags', $merge_tags, $post_id );
        $merge_values = apply_filters( 'propertyhive_document_offer_merge_values', $merge_values, $post_id );

		if ( !$recurring )
		{
			return array($merge_tags, $merge_values);
		}

		$applicant_contact_id = $offer->applicant_contact_id;
		list($merge_tags, $merge_values) = $this->replace_applicant_tags( $merge_tags, $merge_values, $applicant_contact_id );

		$property_id = $offer->property_id;
		list($merge_tags, $merge_values) = $this->replace_property_tags( $merge_tags, $merge_values, $property_id );

		$property = new PH_Property( (int)$property_id );

		$owner_contact_id = $property->owner_contact_id;
		if ( $owner_contact_id != '' && !is_array($owner_contact_id) )
		{
			$owner_contact_id = array($owner_contact_id);
		}
		if ( !empty($owner_contact_id) )
		{
			$owner_contact_id = array_shift(array_values($owner_contact_id));
		}
		list($merge_tags, $merge_values) = $this->replace_owner_tags( $merge_tags, $merge_values, $owner_contact_id );

		$applicant_solicitor_contact_id = $offer->applicant_solicitor_contact_id;
		list($merge_tags, $merge_values) = $this->replace_applicant_solicitor_tags( $merge_tags, $merge_values, $applicant_solicitor_contact_id );

		$owner_solicitor_contact_id = $offer->property_owner_solicitor_contact_id;
		list($merge_tags, $merge_values) = $this->replace_owner_solicitor_tags( $merge_tags, $merge_values, $owner_solicitor_contact_id );

		$sale_id = $offer->sale_id;
		list($merge_tags, $merge_values) = $this->replace_sale_tags( $merge_tags, $merge_values, $sale_id );

		list($merge_tags, $merge_values) = $this->replace_negotiator_tags( $merge_tags, $merge_values, get_post_meta( $property_id, '_negotiator_id', TRUE ) );

		return array($merge_tags, $merge_values);
	}

	public function replace_sale_tags( $merge_tags, $merge_values, $post_id, $recurring = true )
	{
		$sale = new PH_Sale( (int)$post_id );

		$merge_tags[] = 'sale_amount';
        $merge_values[] = $sale->amount;

        $merge_tags[] = 'sale_amount_formatted';
        $merge_values[] = html_entity_decode($sale->get_formatted_amount());

        $merge_tags[] = 'sale_amount_in_words';
        if (class_exists('NumberFormatter'))
        {
	        $f = new NumberFormatter("en", NumberFormatter::SPELLOUT);
			$merge_values[] = $f->format($sale->amount);
		}
		else
		{
			$merge_values[] = '';
		}

		$merge_tags = apply_filters( 'propertyhive_document_sale_merge_tags', $merge_tags, $post_id );
        $merge_values = apply_filters( 'propertyhive_document_sale_merge_values', $merge_values, $post_id );

        if ( !$recurring )
		{
			return array($merge_tags, $merge_values);
		}

		$applicant_contact_id = $sale->applicant_contact_id;
		list($merge_tags, $merge_values) = $this->replace_applicant_tags( $merge_tags, $merge_values, $applicant_contact_id );

		$property_id = $sale->property_id;
		list($merge_tags, $merge_values) = $this->replace_property_tags( $merge_tags, $merge_values, $property_id );

		$property = new PH_Property( (int)$property_id );

		$owner_contact_id = $property->owner_contact_id;
		if ( $owner_contact_id != '' && !is_array($owner_contact_id) )
		{
			$owner_contact_id = array($owner_contact_id);
		}
		if ( !empty($owner_contact_id) )
		{
			$owner_contact_id = array_shift(array_values($owner_contact_id));
		}
		list($merge_tags, $merge_values) = $this->replace_owner_tags( $merge_tags, $merge_values, $owner_contact_id );

		$applicant_solicitor_contact_id = $sale->applicant_solicitor_contact_id;
		list($merge_tags, $merge_values) = $this->replace_applicant_solicitor_tags( $merge_tags, $merge_values, $applicant_solicitor_contact_id );

		$owner_solicitor_contact_id = $sale->property_owner_solicitor_contact_id;
		list($merge_tags, $merge_values) = $this->replace_owner_solicitor_tags( $merge_tags, $merge_values, $owner_solicitor_contact_id );

		list($merge_tags, $merge_values) = $this->replace_negotiator_tags( $merge_tags, $merge_values, get_post_meta( $property_id, '_negotiator_id', TRUE ) );

		return array($merge_tags, $merge_values);
	}

	public function replace_tenancy_tags( $merge_tags, $merge_values, $post_id, $recurring = true )
	{
		$tenancy = new PH_Tenancy( (int)$post_id );

		$merge_tags[] = 'tenancy_start_date';
		$merge_values[] = date( get_option( 'date_format' ), strtotime($tenancy->_start_date) );

		$merge_tags[] = 'tenancy_end_date';
		$merge_values[] = date( get_option( 'date_format' ), strtotime($tenancy->_end_date) );

		$tenant_names = $tenancy->get_tenants();

		if ( strpos($tenant_names, '<br>') !== false)
		{
			$tenant_names_array = explode( '<br>', $tenant_names );
			$last_tenant_name = array_pop($tenant_names_array);

			$tenant_names = implode(', ', $tenant_names_array) . ' & ' . $last_tenant_name;
		}

		$merge_tags[] = 'tenancy_tenant_names';
		$merge_values[] = str_replace('&', '&amp;', $tenant_names);

		$merge_tags[] = 'tenancy_rent';
		$merge_values[] = $tenancy->_rent;

		$merge_tags[] = 'tenancy_rent_formatted';
		$merge_values[] = html_entity_decode($tenancy->get_formatted_rent());

		$merge_tags[] = 'tenancy_deposit';
		$merge_values[] = $tenancy->_deposit;

		$merge_tags = apply_filters( 'propertyhive_document_tenancy_merge_tags', $merge_tags, $post_id );
		$merge_values = apply_filters( 'propertyhive_document_tenancy_merge_values', $merge_values, $post_id );

		if ( !$recurring )
		{
			return array($merge_tags, $merge_values);
		}

		$first_tenant_contact_id = $tenancy->applicant_contact_id;
		list($merge_tags, $merge_values) = $this->replace_applicant_tags( $merge_tags, $merge_values, $first_tenant_contact_id );

		$property_id = $tenancy->property_id;
		list($merge_tags, $merge_values) = $this->replace_property_tags( $merge_tags, $merge_values, $property_id );

		$property = new PH_Property( (int)$property_id );

		$owner_contact_id = $property->owner_contact_id;
		if ( $owner_contact_id != '' && !is_array($owner_contact_id) )
		{
			$owner_contact_id = array($owner_contact_id);
		}
		if ( !empty($owner_contact_id) )
		{
			$owner_contact_id = array_shift(array_values($owner_contact_id));
		}
		list($merge_tags, $merge_values) = $this->replace_owner_tags( $merge_tags, $merge_values, $owner_contact_id );

		list($merge_tags, $merge_values) = $this->replace_negotiator_tags( $merge_tags, $merge_values, get_post_meta( $property_id, '_negotiator_id', TRUE ) );

		return array($merge_tags, $merge_values);
	}

	public function replace_event_tags( $merge_tags, $merge_values, $prefix, $event_object )
	{
		$merge_tags[] = $prefix . '_start_date';
		$merge_values[] = date( get_option( 'date_format' ), strtotime($event_object->start_date_time) );

		$merge_tags[] = $prefix . '_start_time';
		$merge_values[] = date( get_option( 'time_format' ), strtotime($event_object->start_date_time) );

		$duration_seconds = (int)$event_object->duration;
		$merge_tags[] = $prefix . '_end_date';
		$merge_values[] = date( get_option( 'date_format' ), strtotime($event_object->start_date_time)+$duration_seconds );

		$merge_tags[] = $prefix . '_end_time';
		$merge_values[] = date( get_option( 'time_format' ), strtotime($event_object->start_date_time)+$duration_seconds );

		$duration_minutes = (floor($duration_seconds / 60));
		$hours = floor($duration_minutes / 60);
		$minutes = $duration_minutes % 60;
		$merge_tags[] = $prefix . '_duration';
		$merge_values[] = ( $hours > 0 ? $hours . ' hour' . ( $hours != 1 ? 's' : '' ) : '' ) . ( $minutes != '' ? ' '. $minutes . ' minutes' : '' );

		return array($merge_tags, $merge_values);
	}

	public function replace_negotiator_tags( $merge_tags, $merge_values, $user_id )
	{
		$user_info = get_userdata($user_id);

		$merge_tags[] = 'negotiator_name';
        $merge_values[] = $user_info->nickname;

        $merge_tags = apply_filters( 'propertyhive_document_negotiator_merge_tags', $merge_tags, $user_id );
        $merge_values = apply_filters( 'propertyhive_document_negotiator_merge_values', $merge_values, $user_id );

		return array($merge_tags, $merge_values);
	}

	public function replace_general_tags( $merge_tags, $merge_values, $post_id )
	{
		$merge_tags[] = 'date';
        $merge_values[] = date("jS F Y");

        $merge_tags[] = 'company_name';
        $merge_values[] = get_bloginfo('name');

        $merge_tags = apply_filters( 'propertyhive_document_general_merge_tags', $merge_tags, $post_id );
        $merge_values = apply_filters( 'propertyhive_document_general_merge_values', $merge_values, $post_id );

		return array($merge_tags, $merge_values);
	}
}