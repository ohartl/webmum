<?php
// Start session
session_start();
session_regenerate_id();

// Include config
if(file_exists('config/config.inc.php')){
	require_once 'config/config.inc.php';
}
else{
	require_once 'config/config.inc.php.example';
}


/**
 * @param string $errorMessage
 */
function dbError($errorMessage){
	die('There was an error running the query ['.$errorMessage.']');
}

// Establish database connection

$db = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DATABASE);
if($db->connect_errno > 0){
	die('Unable to connect to database [' . $db->connect_error . ']');
}

// register automatic loading for dependency injection
spl_autoload_register(function($class){
	if(file_exists('include/php/models/'.$class.'.php')){
		include 'include/php/models/'.$class.'.php';
	}
	elseif(file_exists('include/php/classes/'.$class.'.php')){
		include 'include/php/classes/'.$class.'.php';
	}
});

/* Initialize Authentication (Login User if in session) */
Auth::init();

require_once 'include/php/global.inc.php';

