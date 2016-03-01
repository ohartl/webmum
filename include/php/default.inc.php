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
if(file_exists('config/config.php')){
	$configValues = require_once 'config/config.php';
	if(!is_array($configValues)){
		die('Config must return an array of config values.');
	}

	Config::init($configValues);
}
else{
	$configValues = require_once 'config/config.php.example';
	if(!is_array($configValues)){
		die('Config must return an array of config values.');
	}

	Config::init($configValues);

	/**
	 * Handle old config style, if it still exists.
	 **/
	if(file_exists('config/config.inc.php') || file_exists('config/config.inc.php.example')){
		define('USING_OLD_CONFIG', true);

		if(file_exists('config/config.inc.php')){
			require_once 'config/config.inc.php';
		}
		else{
			require_once 'config/config.inc.php.example';
		}

		Config::set('base_url', FRONTEND_BASE_PATH);

		Config::set('mysql.host', MYSQL_HOST);
		Config::set('mysql.user', MYSQL_USER);
		Config::set('mysql.password', MYSQL_PASSWORD);
		Config::set('mysql.database', MYSQL_DATABASE);

		Config::set('schema.tables.users', DBT_USERS);
		Config::set('schema.tables.domains', DBT_DOMAINS);
		Config::set('schema.tables.aliases', DBT_ALIASES);

		Config::set('schema.attributes.users.id', DBC_USERS_ID);
		Config::set('schema.attributes.users.username', DBC_USERS_USERNAME);
		Config::set('schema.attributes.users.domain', DBC_USERS_DOMAIN);
		Config::set('schema.attributes.users.password', DBC_USERS_PASSWORD);
		Config::set('schema.attributes.users.mailbox_limit', defined('DBC_USERS_MAILBOXLIMIT') ? DBC_USERS_MAILBOXLIMIT : 'mailbox_limit');

		Config::set('schema.attributes.domains.id', DBC_DOMAINS_ID);
		Config::set('schema.attributes.domains.domain', DBC_DOMAINS_DOMAIN);

		Config::set('schema.attributes.aliases.id', DBC_ALIASES_ID);
		Config::set('schema.attributes.aliases.source', DBC_ALIASES_SOURCE);
		Config::set('schema.attributes.aliases.destination', DBC_ALIASES_DESTINATION);
		Config::set('schema.attributes.aliases.multi_source', defined('DBC_ALIASES_MULTI_SOURCE') ? DBC_ALIASES_MULTI_SOURCE : 'multi_source');


		Config::set('options.enable_mailbox_limits', defined('DBC_USERS_MAILBOXLIMIT'));
		Config::set('options.enable_validate_aliases_source_domain', defined('VALIDATE_ALIASES_SOURCE_DOMAIN_ENABLED'));
		Config::set('options.enable_multi_source_redirects', defined('DBC_ALIASES_MULTI_SOURCE'));
		Config::set('options.enable_admin_domain_limits', defined('ADMIN_DOMAIN_LIMITS_ENABLED') ? ADMIN_DOMAIN_LIMITS_ENABLED : false);
		Config::set('options.enable_logging', defined('WRITE_LOG') ? WRITE_LOG : false);

		Config::set('admins', isset($admins) ? $admins : array());
		Config::set('admin_domain_limits', isset($adminDomainLimits) ? $adminDomainLimits : array());

		Config::set('password.hash_algorithm', PASS_HASH_SCHEMA);
		Config::set('password.min_length', MIN_PASS_LENGTH);

		Config::set('log_path', defined('WRITE_LOG_PATH') ? WRITE_LOG_PATH : '/var/www/webmum/log/');

		Config::set('frontend_options.email_separator_text', FRONTEND_EMAIL_SEPARATOR_TEXT);
		Config::set('frontend_options.email_separator_form', FRONTEND_EMAIL_SEPARATOR_FORM);
	}
}


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