<?php

try {
	/**
	 * Loading system
	 */
	require_once 'include/php/default.inc.php';


	if(defined('INSTALLER_ENABLED')){
		/**
		 * Load installer
		 */
		$content = Router::loadAndBufferOutput('installer/index.php');
	}
	else {
		/**
		 * Handle request
		 */
		$content = Router::executeCurrentRequest();
	}
}
catch(DatabaseException $e){
	$content = '<div class="notification notification-fail">Faulty database query: "'.$e->getQuery().'".</div>';
}
catch(Exception $e){
	$content = '<div class="notification notification-fail">'.$e->getMessage().'</div>';
}

if(defined('USING_OLD_CONFIG')){
	$content = '<div class="notification notification-fail"><strong>Your WebMUM installation is still using the old deprecated config style!</strong><br><br>Please update your config to the new style (an example config can be found in <cite>config.php.example</cite>)<br>and delete your old <cite>config.inc.php</cite> and <cite>config.inc.php.example</cite>.</div>'.$content;
}

echo Router::loadAndBufferOutput(
	'include/php/template/layout.php',
	array(
		'content' => $content,
	)
);