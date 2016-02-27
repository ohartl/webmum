<?php

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