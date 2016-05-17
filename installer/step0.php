<?php

$thisStep = 0;


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
if(file_exists('config') && is_writable('config') && !file_exists('config/config.php')){
	$requirements[] = 'config_path_writable';
}
if(file_exists('config') && file_exists('config/config.php.example')){
	$requirements[] = 'config_example';
}


if(isset($_GET['go']) && $_GET['go'] == 'next'){
	if(count($requirements) === $numberOfRequirements){
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
	<li class="text-success">PHP version: <strong><?php echo phpversion(); ?></strong> (>=5.4.0 or >=7.0.0)</li>
<?php else: ?>
	<li class="text-fail">PHP version: <strong><?php echo phpversion(); ?></strong> (>=5.4.0 or >=7.0.0)</li>
<?php endif; ?>
</ul>

<strong>Required PHP settings</strong>
<ul>
<?php if(in_array('php_extension_mysqli', $requirements)): ?>
	<li class="text-success">Database extension (mysqli): enabled</li>
<?php else: ?>
	<li class="text-fail">Database extension (mysqli): disabled</li>
<?php endif; ?>
<?php if(in_array('php_session_enabled', $requirements)): ?>
	<li class="text-success">Session support: enabled</li>
<?php else: ?>
	<li class="text-fail">Session support: disabled</li>
<?php endif; ?>
</ul>

<strong>Directories and files</strong>
<ul>
<?php if(in_array('config_path_writable', $requirements)): ?>
	<li class="text-success">"config/": writable</li>
<?php else: ?>
	<li class="text-fail">"config/": not writable</li>
<?php endif; ?>
<?php if(in_array('config_example', $requirements)): ?>
	<li class="text-success">"config/config.php.example": exists</li>
<?php else: ?>
	<li class="text-fail">"config/config.php.example": is missing</li>
<?php endif; ?>
</ul>

<hr>

<?php if(count($requirements) === $numberOfRequirements):?>
	<p>Click on Start button to continue.</p>
	<a class="button button-primary" href="/?step=<?php echo $thisStep; ?>&go=next">Start</a>
<?php else:?>
	<p class="notification notification-fail">Some requirements aren't fulfilled.</p>
<?php endif; ?>