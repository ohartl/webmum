<?php
// Start session as the very first thing
session_start();
session_regenerate_id();


/**
 * Loading system
 */
require_once 'include/php/default.inc.php';


/**
 * Handle request
 */
$content = Router::executeCurrentRequest();

echo Router::loadAndBufferOutput(
	'include/php/template/layout.php',
	array(
		'content' => $content,
	)
);