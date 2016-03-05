<?php

/**
 * @covers Router
 */
class RouterTest extends TestCase
{

	const BASE_URL = 'http://test.tld/somedir';


	public function setUp()
	{
		Config::set('base_url', self::BASE_URL);
		Router::init(array());
	}


	public function testUrl()
	{
		$this->assertEquals(self::BASE_URL, Router::url());
		$this->assertEquals(self::BASE_URL, Router::url('/'));

		$this->assertEquals(self::BASE_URL.'/this/sub/dir?get=123', Router::url('this/sub/dir?get=123'));
	}


	public function testAdd()
	{
		Router::addRoute(Router::METHOD_GET, 'test-get', 'test-get-file');
		Router::execute('test-get', Router::METHOD_GET);


		Router::addRoute(Router::METHOD_POST, 'test-post', 'test-post-file');
		Router::execute('test-post', Router::METHOD_POST);


		Router::addRoute(array(Router::METHOD_GET, Router::METHOD_POST), 'test-mixed', 'test-mixed-file');
		Router::execute('test-mixed', Router::METHOD_GET);
		Router::execute('test-mixed', Router::METHOD_POST);
	}


	public function testAddCallback()
	{
		$reachedCallback = false;
		Router::addRoute(Router::METHOD_GET, 'test-callback', function() use(&$reachedCallback) {
			$reachedCallback = true;
		});
		Router::execute('test-callback', Router::METHOD_GET);

		$this->assertTrue($reachedCallback);
	}


	/**
	 * @expectedException Exception
	 * @expectedExceptionMessageRegExp /unsupported/i
	 */
	public function testAddMethodUnsupported()
	{
		Router::addRoute('not-a-method', 'test-fail', 'test-fail-file');
	}


	public function testAddShortcuts()
	{
		Router::addGet('test-get', 'test-get-file');
		Router::execute('test-get', Router::METHOD_GET);

		Router::addPost('test-post', 'test-post-file');
		Router::execute('test-post', Router::METHOD_POST);

		Router::addMixed('test-mixed', 'test-mixed-file');
		Router::execute('test-mixed', Router::METHOD_GET);
		Router::execute('test-mixed', Router::METHOD_POST);
	}


	/**
	 * @expectedException Exception
	 * @expectedExceptionMessageRegExp /unsupported/i
	 */
	public function testExecuteMethodUnsupported()
	{
		Router::execute('test-fail', 'not-a-method');
	}


	public function testExecuteCurrentRequest()
	{
		$_SERVER['REQUEST_METHOD'] = 'GET';
		$_SERVER['REQUEST_URI'] = '/somedir/test-get';

		Router::executeCurrentRequest();
	}


	public function testRouteWithPermission()
	{
		$this->assertFalse(Auth::isLoggedIn());

		$reachedCallback = false;
		Router::addRoute(Router::METHOD_GET, 'test-perm-admin', function() use(&$reachedCallback) {
			$reachedCallback = true;
		}, User::ROLE_ADMIN);

		Router::addRoute(Router::METHOD_GET, 'test-perm-user', function() use(&$reachedCallback) {
			$reachedCallback = true;
		}, User::ROLE_USER);


		$reachedCallback = false;
		Router::execute('test-perm-admin', Router::METHOD_GET);
		$this->assertFalse($reachedCallback);

		$reachedCallback = false;
		Router::execute('test-perm-user', Router::METHOD_GET);
		$this->assertFalse($reachedCallback);

		// Now auth as admin and try again
		Auth::login('admin@domain.tld', 'testtest');

		$reachedCallback = false;
		Router::execute('test-perm-admin', Router::METHOD_GET);
		$this->assertTrue($reachedCallback);

		$reachedCallback = false;
		Router::execute('test-perm-user', Router::METHOD_GET);
		$this->assertTrue($reachedCallback);

		Auth::logout();

		// Now auth as user and try again
		Auth::login('user@domain.tld', 'testtest');

		$reachedCallback = false;
		Router::execute('test-perm-admin', Router::METHOD_GET);
		$this->assertFalse($reachedCallback);

		$reachedCallback = false;
		Router::execute('test-perm-user', Router::METHOD_GET);
		$this->assertTrue($reachedCallback);

		Auth::logout();
	}
}