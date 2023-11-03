<?php
if (class_exists('IndeedImport') && !class_exists('UapIndeedImport')):

class UapIndeedImport extends IndeedImport{

	/*
	 * @param string ($entity_name)
	 * @param string ($entity_opt)
	 * @param object ($xml_object)
	 * @return none
	 */
	protected function do_import_custom_table($entity_name, $entity_opt, &$xml_object){
		global $wpdb;
		$table = $wpdb->prefix . $entity_name;

		if (!$xml_object->$entity_name->Count()){
			return;
		}

		switch ($entity_name){
			case 'uap_affiliates':
				foreach ($xml_object->$entity_name->children() as $meta_key=>$object){
					$insert_string = "VALUES(
											 " . $this->esc_sql( $object->id ) . ",
											'" . $this->esc_sql( $object->uid ) . "',
											'" . $this->esc_sql( $object->rank_id ) . "',
											'" . $this->esc_sql( $object->start_data ) . "',
											'" . $this->esc_sql( $object->status ) . "'
					)";
					$this->do_basic_insert($table, $insert_string);
				}
				break;
			case 'uap_banners':
				foreach ($xml_object->$entity_name->children() as $meta_key=>$object){
					$insert_string = "VALUES(null,
											'" . $this->esc_sql( $object->name ) . "',
											'" . $this->esc_sql( $object->description ) . "',
											'" . $this->esc_sql( $object->url ). "',
											'" . $this->esc_sql( $object->image ) . "',
											'" . $this->esc_sql( $object->status ) . "',
											'" . $this->esc_sql( $object->DATE ) . "'
					)";
					$this->do_basic_insert($table, $insert_string);
				}
				break;
			case 'uap_notifications':
				foreach ($xml_object->$entity_name->children() as $meta_key=>$object){
					$insert_string = "VALUES(null,
											'" . $this->esc_sql( $object->type ) . "',
											'" . $this->esc_sql( $object->rank_id ) . "',
											'" . $this->esc_sql( $object->subject ) . "',
											'" . $this->esc_sql( $object->message ) . "',
											'" . $this->esc_sql( $object->pushover_message ) . "',
											'" . $this->esc_sql( $object->pushover_status ) . "',
											'" . $this->esc_sql( $object->status ) . "'
					)";
					$this->do_basic_insert($table, $insert_string);
				}
				break;
			case 'uap_ranks':
				foreach ($xml_object->$entity_name->children() as $meta_key=>$object){
					$insert_string = "VALUES(null,
											'" . $this->esc_sql( $object->slug ) . "',
											'" . $this->esc_sql( $object->label ) . "',
											'" . $this->esc_sql( $object->amount_type ) . "',
											'" . $this->esc_sql( $object->amount_value ) . "',
											'" . $this->esc_sql( $object->bonus ) . "',
											'" . $this->esc_sql( $object->pay_per_click ) . "',
											'" . $this->esc_sql( $object->cpm_commission ) . "',
											'" . $this->esc_sql( $object->sign_up_amount_value ) . "',
											'" . $this->esc_sql( $object->lifetime_amount_type ) . "',
											'" . $this->esc_sql( $object->lifetime_amount_value ) . "',
											'" . $this->esc_sql( $object->reccuring_amount_type ) . "',
											'" . $this->esc_sql( $object->reccuring_amount_value ) . "',
											'" . $this->esc_sql( $object->mlm_amount_type ) . "',
											'" . $this->esc_sql( $object->mlm_amount_value ) . "',
											'" . $this->esc_sql( $object->achieve ) . "',
											'" . $this->esc_sql( $object->settings ) . "',
											'" . $this->esc_sql( $object->rank_order ) . "',
											'" . $this->esc_sql( $object->status ) . "'
					)";
					$this->do_basic_insert($table, $insert_string);
				}
				break;
			case 'uap_offers':
				foreach ($xml_object->$entity_name->children() as $meta_key=>$object){
					$insert_string = "VALUES(null,
											'" . $this->esc_sql( $object->name ) . "',
											'" . $this->esc_sql( $object->start_date ) . "',
											'" . $this->esc_sql( $object->end_date ) . "',
											'" . $this->esc_sql( $object->amount_type ) . "',
											'" . $this->esc_sql( $object->amount_value ) . "',
											'" . $this->esc_sql( $object->settings ) . "',
											'" . $this->esc_sql( $object->status ) . "'
					)";
					$this->do_basic_insert($table, $insert_string);
				}
				break;
			case 'uap_offers_affiliates_reference':
				foreach ($xml_object->$entity_name->children() as $meta_key=>$object){
					$insert_string = "VALUES(null,
											'" . $this->esc_sql( $object->offer_id ) . "',
											'" . $this->esc_sql( $object->affiliate_id ) . "',
											'" . $this->esc_sql( $object->source ) . "',
											'" . $this->esc_sql( $object->products ) . "'
					)";
					$this->do_basic_insert($table, $insert_string);
				}
				break;
			case 'uap_mlm_relations':
				foreach ($xml_object->$entity_name->children() as $meta_key=>$object){
					$insert_string = "VALUES(null,
											'" . $this->esc_sql( $object->affiliate_id ) . "',
											'" . $this->esc_sql( $object->parent_affiliate_id ) . "'
					)";
					$this->do_basic_insert($table, $insert_string);
				}
				break;
			case 'uap_ranks_history':
				foreach ($xml_object->$entity_name->children() as $meta_key=>$object){
					$insert_string = "VALUES(null,
											'" . $this->esc_sql( $object->affiliate_id ) . "',
											'" . $this->esc_sql( $object->prev_rank_id ) . "',
											'" . $this->esc_sql( $object->rank_id ) . "',
											'" . $this->esc_sql( $object->add_date ) . "'
					)";
					$this->do_basic_insert($table, $insert_string);
				}
				break;
			case 'uap_landing_commissions':
				foreach ($xml_object->$entity_name->children() as $meta_key=>$object){
					$insert_string = "VALUES(null,
											'" . $this->esc_sql( $object->affiliate_id ) . "',
											'" . $this->esc_sql( $object->prev_rank_id ) . "',
											'" . $this->esc_sql( $object->rank_id ) . "',
											'" . $this->esc_sql( $object->add_date ) . "'
					)";
					$this->do_basic_insert($table, $insert_string);
				}
				break;
			case 'uap_coupons_code_affiliates':
				foreach ($xml_object->$entity_name->children() as $meta_key=>$object){
					$insert_string = "VALUES(null,
											'" . $this->esc_sql( $object->code ) . "',
											'" . $this->esc_sql( $object->affiliate_id ) . "',
											'" . $this->esc_sql( $object->type ) . "',
											'" . $this->esc_sql( $object->settings ) . "',
											'" . $this->esc_sql( $object->status ) . "'
					)";
					$this->do_basic_insert($table, $insert_string);
				}
				break;
			case 'uap_reports':
				foreach ($xml_object->$entity_name->children() as $meta_key=>$object){
					$insert_string = "VALUES( '" . $this->esc_sql($object->affiliate_id ) . "',
											'" . $this->esc_sql( $object->email ) . "',
											'" . $this->esc_sql( $object->period ) . "',
											'" . $this->esc_sql( $object->last_sent ) . "'
					)";
					$this->do_basic_insert($table, $insert_string);
				}
				break;
			case 'uap_ref_links':
				foreach ($xml_object->$entity_name->children() as $meta_key=>$object){
					$insert_string = "VALUES(null,
											'" . $this->esc_sql( $object->affiliate_id ) . "',
											'" . $this->esc_sql( $object->url ) . "',
											'" . $this->esc_sql( $object->status ) . "'
					)";
					$this->do_basic_insert($table, $insert_string);
				}
				break;
		}
	}


	/*
	 * @param string (table name)
	 * @param string (insert values)
	 * @return none
	 */
	private function do_basic_insert($table='', $insert_values=''){
		global $wpdb;
		$query = "INSERT INTO $table $insert_values;";
		$wpdb->query( $query );
	}

	private function esc_sql( $value='' )
	{
			global $wpdb;
			return esc_sql( $value );
	}

}

endif;
