<?php

if(strpos($_SERVER['REQUEST_URI'], 'installer/') !== false){
	die('You cannot directly access the installer files.');
}

/*-----------------------------------------------------------------------------*/

$thisStep = 0;

/*-----------------------------------------------------------------------------*/

$requirements = array();
$numberOfRequirements = 5;
if(version_compare(phpversion(), '5.4.0', '>=')){
	$requirements[] = 'php_version';
}
if(function_exists('mysqli_connect')){
	$requirements[] = 'php_extension_mysqli';
}
if(session_status() != PHP_SESSION_DISABLED){
	$requirements[] = 'php_session_enabled';
}
if(file_exists('config') && is_dir('config')){
	$requirements[] = 'config_directory';
}
if(file_exists('config/config.php.example')){
	$requirements[] = 'config_example';
}

/*-----------------------------------------------------------------------------*/

if(isset($_GET['go']) && $_GET['go'] == 'next'){
	if(count($requirements) === $numberOfRequirements){
		installer_message('All requirements fulfilled, let\'s get started with the installation!');

		installer_next($thisStep);
	}
}
?>
<?php echo installer_message(); ?>

<h2>Getting started</h2>

<p>By following this wizard you will install and configure your new WebMUM installation.</p>

<hr>

<strong>System Info:</strong>
<ul>
	<li>System: <strong><?php echo php_uname(); ?></strong></li>
	<li>Hostname: <strong><?php echo $_SERVER['SERVER_NAME']; ?></strong></li>
	<li>IP: <strong><?php echo $_SERVER['SERVER_ADDR']; ?></strong></li>
	<li>PHP version: <strong><?php echo phpversion(); ?></strong></li>
	<li>Server API: <strong><?php echo php_sapi_name(); ?></strong></li>
	<li>WebMUM directory: <strong><?php echo dirname($_SERVER['SCRIPT_FILENAME']); ?></strong></li>
</ul>

<strong>Server requirements</strong>
<ul>
<?php if(in_array('php_version', $requirements)): ?>
	<li class="text-success">PHP version (>=5.4.0 or >=7.0.0): <strong><?php echo phpversion(); ?>  &#x2713;</strong></li>
<?php else: ?>
	<li class="text-fail">PHP version (>=5.4.0 or >=7.0.0): <strong><?php echo phpversion(); ?> &#x274c;</strong></li>
<?php endif; ?>
</ul>

<strong>Required PHP settings</strong>
<ul>
<?php if(in_array('php_extension_mysqli', $requirements)): ?>
	<li class="text-success">Database extension (mysqli): <strong>enabled &#x2713;</strong></li>
<?php else: ?>
	<li class="text-fail">Database extension (mysqli): <strong>disabled &#x274c;</strong></li>
<?php endif; ?>
<?php if(in_array('php_session_enabled', $requirements)): ?>
	<li class="text-success">Session support: <strong>enabled &#x2713;</strong></li>
<?php else: ?>
	<li class="text-fail">Session support: <strong>disabled &#x274c;</strong></li>
<?php endif; ?>
</ul>

<strong>Directories and files</strong>
<ul>
<?php if(in_array('config_directory', $requirements)): ?>
	<li class="text-success">"config/": <strong>exists &#x2713;</strong></li>
<?php else: ?>
	<li class="text-fail">"config/": <strong>is missing &#x274c;</strong></li>
<?php endif; ?>
<?php if(in_array('config_example', $requirements)): ?>
	<li class="text-success">"config/config.php.example": <strong>exists &#x2713;</strong></li>
<?php else: ?>
	<li class="text-fail">"config/config.php.example": <strong>is missing &#x274c;</strong></li>
<?php endif; ?>
</ul>

<hr>

<?php if(count($requirements) === $numberOfRequirements):?>
	<p>Click on the Start button to continue.</p>
	<a class="button button-primary" href="/?step=<?php echo $thisStep; ?>&go=next">Start</a>
<?php else:?>
	<p class="notification notification-fail">Some requirements aren't fulfilled.</p>
<?php endif; ?>