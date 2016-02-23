<?php

class Router
{
	/**
	 * @var array
	 */
	private static $routes = array();


	/**
	 * @var array
	 */
	private static $errorPages = array(
		404 => 'include/php/pages/404.php',
		403 => 'include/php/pages/not-allowed.php'
	);


	private function __construct()
	{
	}

	private function __clone()
	{
	}


	/**
	 * @param string|array $methods
	 * @param string $pattern
	 * @param callable|array|string $routeConfig
	 * @param array $permission
	 */
	public static function addRoute($methods, $pattern, $routeConfig, $permission = null)
	{
		if(!is_array($methods)){
			$methods = array($methods);
		}

		$config = array(
			'pattern' => $pattern,
			'config' => $routeConfig,
			'permission' => $permission,
		);

		foreach($methods as $method){
			$method = strtoupper($method);

			if(!isset(static::$routes[$method])){
				static::$routes[$method] = array();
			}

			static::$routes[$method][] = $config;
		}
	}


	/**
	 * @param string $pattern
	 * @param callable|array|string $routeConfig
	 * @param array $permission
	 */
	public static function addGet($pattern, $routeConfig, $permission = null)
	{
		static::addRoute('GET', $pattern, $routeConfig, $permission);
	}


	/**
	 * @param string $pattern
	 * @param callable|array|string $routeConfig
	 * @param array $permission
	 */
	public static function addPost($pattern, $routeConfig, $permission = null)
	{
		static::addRoute('POST', $pattern, $routeConfig, $permission);
	}


	/**
	 * @param string $pattern
	 * @param callable|array|string $routeConfig
	 * @param array $permission
	 */
	public static function addMixed($pattern, $routeConfig, $permission = null)
	{
		static::addRoute(array('GET', 'POST'), $pattern, $routeConfig, $permission);
	}


	/**
	 * @param string $url
	 * @param string $method
	 *
	 * @return string
	 */
	public static function execute($url, $method = 'GET')
	{
		$method = strtoupper($method);

		if(!in_array($method, array('GET', 'POST')) && !isset(self::$routes[$method])){
			return 'Unsupported HTTP method.';
		}

		foreach(self::$routes[$method] as $route){
			if(rtrim($route['pattern'], '/') === rtrim($url, '/')){
				if(!is_null($route['permission'])){
					if(!Auth::isLoggedIn() || !Auth::hasPermission($route['permission'])){
						return static::loadAndBufferOutput(static::$errorPages[403]);
					}
				}

				return static::resolveRouteConfig($route['config']);
			}
		}

		return static::loadAndBufferOutput(static::$errorPages[404]);
	}

	/**
	 * @return string
	 */
	public static function executeCurrentRequest()
	{
		return static::execute(
			static::getCurrentUrlPath(),
			isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET'
		);
	}


	/**
	 * @param bool $removeGetParameters
	 *
	 * @return string
	 */
	public static function getCurrentUrlPath($removeGetParameters = true)
	{
		$baseUrl = parse_url(FRONTEND_BASE_PATH);
		$basePath = isset($baseUrl['path']) ? rtrim($baseUrl['path'], '/') : '';

		$url = $_SERVER['REQUEST_URI'];

		if($removeGetParameters){
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
	 * @param array $config
	 *
	 * @return string
	 */
	public static function resolveRouteConfig($config)
	{
		if(is_string($config)){
			if(file_exists($config)){
				return static::loadAndBufferOutput($config);
			}
		}

		return static::loadAndBufferOutput(static::$errorPages[404]);
	}

	/**
	 * @param string $file
	 * @param array $variables
	 *
	 * @return string
	 */
	public static function loadAndBufferOutput($file, $variables = array())
	{
		ob_start();

		extract($variables);

		require $file;

		return ob_get_clean();
	}
}