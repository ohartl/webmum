<?php
// Start session as the very first thing
session_start();
session_regenerate_id();


define("BACKEND_BASE_PATH", preg_replace("#index.php#", "", $_SERVER['SCRIPT_FILENAME']));


require_once 'include/php/default.inc.php';


/**
 * @param string $file
 * @return string
 */
function loadAndBufferOutput($file)
{
	ob_start();

	require $file;

	return ob_get_clean();
}


/**
 * @param string $url
 * @return string
 */
function loadPageByRoute($url)
{
	$file = 'include/php/pages/404.php';

	$routes = array(
		'/login/' => 'include/php/pages/login.php',
		'/logout/' => 'include/php/pages/logout.php',
		'/' => 'include/php/pages/start.php',
	);

	$adminRoutes = array(
		'/admin/' => 'include/php/pages/admin/start.php',
		'/admin/listusers/' => 'include/php/pages/admin/listusers.php',
		'/admin/edituser/' => 'include/php/pages/admin/edituser.php',
		'/admin/deleteuser/' => 'include/php/pages/admin/deleteuser.php',
		'/admin/listdomains/' => 'include/php/pages/admin/listdomains.php',
		'/admin/deletedomain/' => 'include/php/pages/admin/deletedomain.php',
		'/admin/createdomain/' => 'include/php/pages/admin/createdomain.php',
		'/admin/listredirects/' => 'include/php/pages/admin/listredirects.php',
		'/admin/editredirect/' => 'include/php/pages/admin/editredirect.php',
		'/admin/deleteredirect/' => 'include/php/pages/admin/deleteredirect.php',
	);

	$userRoutes = array(
		'/private/' => 'include/php/pages/private/start.php',
		'/private/changepass/' => 'include/php/pages/private/changepass.php',
	);


	if(preg_match("/^\/private(.*)$/", $url) == 1){
		// Page is user page
		if(Auth::hasPermission(User::ROLE_USER)){
			if(isset($userRoutes[$url])){
				$file = $userRoutes[$url];
			}
		}
		else{
			$file = 'include/php/pages/not-allowed.php';
		}
	}
	else if(preg_match("/^\/admin(.*)$/", $url) == 1){
		// Page is admin page
		if(Auth::hasPermission(User::ROLE_ADMIN)){
			if(isset($adminRoutes[$url])){
				$file = $adminRoutes[$url];
			}
		}
		else{
			$file = 'include/php/pages/not-allowed.php';
		}
	}
	else{
		// Page is public accessible
		if(isset($routes[$url])){
			$file = $routes[$url];
		}
	}

	if(file_exists($file)){
		return loadAndBufferOutput($file);
	}

	die('Page file "'.$file.'" couldn\'t be found');
}

/**
 * @param bool $removeGetParameters
 *
 * @return string
 */
function getCurrentUrlPath($removeGetParameters = true)
{
	$baseUrl = parse_url(FRONTEND_BASE_PATH);
	$basePath = isset($baseUrl['path']) ? rtrim($baseUrl['path'], '/') : '';

	$url = $_SERVER['REQUEST_URI'];

	if($removeGetParameters) {
		$url = preg_replace('/\?.*/', '', $url); // Trim GET Parameters
	}

	// Trim all leading slashes
	$url = rtrim($url, '/');

	if(!empty($basePath) && ($basePathPos = strpos($url, $basePath)) === 0){
		$url = substr($url, strlen($basePath));
	}

	return $url;
}

/**
 * @return string
 */
function preparedUrlForRouting()
{
	return getCurrentUrlPath(true).'/';
}


/*
 * Build page
 */

$content = loadPageByRoute(
	preparedUrlForRouting()
);

$header = loadAndBufferOutput('include/php/template/header.php');
$footer = loadAndBufferOutput('include/php/template/footer.php');

echo $header.$content.$footer;