<?php

/**
 * Start session as the very first thing
 */
session_start();
session_regenerate_id();


/**
 * Register automatic loading for dependency injection
 */
spl_autoload_register(function($class){
	if(file_exists('include/php/models/'.$class.'.php')){
		include_once 'include/php/models/'.$class.'.php';
	}
	elseif(file_exists('include/php/classes/'.$class.'.php')){
		include_once 'include/php/classes/'.$class.'.php';
	}
});


/**
 * Load some global accessible functions
 */
require_once 'include/php/global.inc.php';


/**
 * Require config
 */
if(file_exists('config/config.inc.php')){
	require_once 'config/config.inc.php';
}
else{
	require_once 'config/config.inc.php.example';
}


/**
 * Establish database connection
 */
$db = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DATABASE);
if($db->connect_errno > 0){
	die('Unable to connect to database [' . $db->connect_error . ']');
}


/**
 * Initialize Authentication (Login User if in session)
 */
Auth::init();


/**
 * Setup routes
 */
require_once 'include/php/routes.inc.php';