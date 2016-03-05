<?php

class Router
{
	const METHOD_GET = 'GET';
	const METHOD_POST = 'POST';


	/**
	 * @var array
	 */
	private static $routes = array();


	/**
	 * @var array
	 */
	private static $errorPages = array(
		404 => 'include/php/template/error/not-found.php',
		403 => 'include/php/template/error/not-allowed.php'
	);


	/**
	 * @codeCoverageIgnore
	 */
	private function __construct()
	{
	}


	/**
	 * @codeCoverageIgnore
	 */
	private function __clone()
	{
	}


	/**
	 * @param array $routes
	 */
	public static function init($routes)
	{
		static::$routes = $routes;
	}


	/**
	 * @param string $method
	 *
	 * @return bool
	 */
	protected static function isValidMethod($method)
	{
		return in_array(
			$method,
			array(
				static::METHOD_GET,
				static::METHOD_POST
			)
		);
	}


	/**
	 * @param string|array $methods
	 * @param string $pattern
	 * @param callable|array|string $routeConfig
	 * @param array $permission
	 *
	 * @throws Exception
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

			if(!static::isValidMethod($method)){
				throw new Exception('Unsupported HTTP method "'.$method.'".');
			}

			if(!isset(static::$routes[$method])){
				static::$routes[$method] = array();
			}

			static::$routes[$method][] = $config;
		}
	}


	/**
	 * @param string $pattern
	 * @param callable|string $routeConfig
	 * @param array $permission
	 */
	public static function addGet($pattern, $routeConfig, $permission = null)
	{
		static::addRoute(static::METHOD_GET, $pattern, $routeConfig, $permission);
	}


	/**
	 * @param string $pattern
	 * @param callable|string $routeConfig
	 * @param array $permission
	 */
	public static function addPost($pattern, $routeConfig, $permission = null)
	{
		static::addRoute(static::METHOD_POST, $pattern, $routeConfig, $permission);
	}


	/**
	 * @param string $pattern
	 * @param callable|string $routeConfig
	 * @param array $permission
	 */
	public static function addMixed($pattern, $routeConfig, $permission = null)
	{
		static::addRoute(array(static::METHOD_GET, static::METHOD_POST), $pattern, $routeConfig, $permission);
	}


	/**
	 * @param string $url
	 * @param string $method
	 *
	 * @return string
	 *
	 * @throws Exception
	 */
	public static function execute($url, $method = self::METHOD_GET)
	{
		$method = strtoupper($method);

		if(!static::isValidMethod($method) && !isset(self::$routes[$method])){
			throw new Exception('Unsupported HTTP method "'.$method.'".');
		}

		if(isset(self::$routes[$method])){
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
			isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : static::METHOD_GET
		);
	}


	/**
	 * @param int $errorNumber
	 *
	 * @return string|null
	 *
	 * @codeCoverageIgnore
	 */
	public static function displayError($errorNumber)
	{
		$errorPage = isset(static::$errorPages[$errorNumber])
			? static::loadAndBufferOutput(static::$errorPages[$errorNumber])
			: '';

		echo Router::loadAndBufferOutput(
			'include/php/template/layout.php',
			array(
				'content' => $errorPage,
			)
		);
		exit;
	}


	/**
	 * @param bool $removeGetParameters
	 *
	 * @return string
	 */
	public static function getCurrentUrlPath($removeGetParameters = true)
	{
		$baseUrl = parse_url(Config::get('base_url'));
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
		elseif(is_callable($config) && $config instanceof Closure){
			return $config();
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


	/**
	 * Generate full url
	 *
	 * @param string $url
	 *
	 * @return string
	 */
	public static function url($url = '')
	{
		return rtrim(
			sprintf(
				'%s/%s',
				rtrim(Config::get('base_url'), '/'),
				trim($url, '/')
			),
			'/'
		);
	}


	/**
	 * Redirect user to an url
	 *
	 * @param string $url
	 *
	 * @codeCoverageIgnore
	 */
	public static function redirect($url)
	{
		header("Location: ".static::url($url));
		exit;
	}
}