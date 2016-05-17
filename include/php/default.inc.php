<?php

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
 * Start session as the very first thing
 */
session_start();
session_regenerate_id();


/**
 * Load some global accessible functions
 */
require_once 'include/php/global.inc.php';


/**
 * Setting up
 */
if(file_exists('config/config.php')){

	/**
	 * Loading config
	 */
	$configValues = require_once 'config/config.php';

	if(!is_array($configValues)){
		throw new Exception('Config must return an array of config values.');
	}

	Config::init($configValues);

	/**
	 * Establish database connection
	 */
	Database::init(Config::get('mysql'));


	/**
	 * Initialize Authentication (Login User if in session)
	 */
	Auth::init();


	/**
	 * Setup routes
	 */
	require_once 'include/php/routes.inc.php';
}
else{

	/**
	 * Switching to install mode
	 */
	define('INSTALLER_ENABLED', true);

}
