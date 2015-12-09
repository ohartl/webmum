<?php

// Include config
if(file_exists('config/config_override.inc.php')){
	require_once 'config/config_override.inc.php';
}
else{
	require_once 'config/config.inc.php';
}


// Establish database connection

$db = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DATABASE);

if($db->connect_errno > 0){
	die('Unable to connect to database [' . $db->connect_error . ']');
}

/* Import classes */
require_once 'include/php/classes/user.class.php';

$user = new USER();

require_once 'include/php/global.inc.php';
require_once 'include/php/checkpermissions.inc.php';

?>
