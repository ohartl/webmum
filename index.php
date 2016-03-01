<?php

/**
 * Loading system
 */
require_once 'include/php/default.inc.php';


/**
 * Handle request
 */
$content = Router::executeCurrentRequest();

if(defined('USING_OLD_CONFIG') && Auth::hasPermission(User::ROLE_ADMIN) && !Auth::getUser()->isDomainLimited()){
	$content = '<div class="notification notification-fail"><strong>Your WebMUM installation is still using the old deprecated config style!</strong><br><br>Please update your config to the new style (an example config can be found in <cite>config.php.example</cite>)<br>and delete your old <cite>config.inc.php</cite> and <cite>config.inc.php.example</cite>.</div>'.$content;
}

echo Router::loadAndBufferOutput(
	'include/php/template/layout.php',
	array(
		'content' => $content,
	)
);