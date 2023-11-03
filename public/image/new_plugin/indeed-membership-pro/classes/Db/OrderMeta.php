<?php
namespace Indeed\Ihc\Db;

class OrderMeta
{

    public function __construct(){}

    public function save( $orderId=0, $orderMetaName='', $orderMetaValue='' )
    {
        global $wpdb;
        if ( !$orderId || !$orderMetaName ){
            return false;
        }
        $query = $wpdb->prepare( "SELECT meta_value FROM {$wpdb->prefix}ihc_orders_meta WHERE order_id=%d AND meta_key=%s;", $orderId, $orderMetaName );
        $exists = $wpdb->get_var( $query );
        if ( $exists == null ){
            /// save
            $query = $wpdb->prepare( "INSERT INTO {$wpdb->prefix}ihc_orders_meta VALUES( NULL, %d, %s, %s );", $orderId, $orderMetaName, $orderMetaValue );
        } else {
            /// update
            $query = $wpdb->prepare( "UPDATE {$wpdb->prefix}ihc_orders_meta SET meta_value=%s WHERE order_id=%d AND meta_key=%s;", $orderMetaValue, $orderId, $orderMetaName );
        }
        return $wpdb->query( $query );
    }

    public function get( $orderId=0, $orderMetaName='' )
    {
        global $wpdb;
        if ( !$orderId || !$orderMetaName ){
            return false;
        }
        $query = $wpdb->prepare( "SELECT meta_value FROM {$wpdb->prefix}ihc_orders_meta WHERE order_id=%d AND meta_key=%s;", $orderId, $orderMetaName );
        return $wpdb->get_var( $query );
    }

    public function getIdFromMetaNameMetaValue( $metaKey='', $metaValue='' )
    {
        global $wpdb;
        if ( !$metaKey || !$metaValue ){
            return;
        }
        $query = $wpdb->prepare( "SELECT order_id FROM {$wpdb->prefix}ihc_orders_meta WHERE meta_key=%s AND meta_value=%s;", $metaKey, $metaValue );
        return $wpdb->get_var( $query );
    }

}
