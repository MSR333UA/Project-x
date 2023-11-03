<?php

if (!defined('WP_UNINSTALL_PLUGIN')){
	exit();
}

if ( !defined('UAP_PATH')){
    define( 'UAP_PATH', plugin_dir_path(__FILE__) );
}

require_once UAP_PATH . 'autoload.php';

// revoke
$uapElCheck = new \Indeed\Uap\ElCheck();
$uapElCheck->doRevoke();


if ( get_option('uap_keep_data_after_delete') == 1 ){
		return;
}


include UAP_PATH . 'classes/UapDb.class.php';
$uap_uninstall_object = new UapDb();
$uap_uninstall_object->unistall();
