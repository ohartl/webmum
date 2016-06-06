<?php

require_once 'TestCase.php';

/**
 * @covers Auth
 */
class AuthTest extends TestCase
{

	public function tearDown()
	{
		Auth::logout();
		$_SESSION = array();
	}


	public function testInitGuest()
	{
		$_SESSION = array();

		Auth::init();

		$this->assertFalse(Auth::isLoggedIn());
		$this->assertNull(Auth::getUser());
		$this->assertFalse(Auth::hasPermission(User::ROLE_USER));
		$this->assertFalse(Auth::hasPermission(User::ROLE_ADMIN));
	}

	public function testInitUser()
	{
		$_SESSION = array(
			Auth::SESSION_IDENTIFIER => self::USER_ROLE_USER_ID
		);

		Auth::init();

		$this->assertTrue(Auth::isLoggedIn());
		$this->assertInstanceOf('User', Auth::getUser());
		$this->assertTrue(Auth::hasPermission(User::ROLE_USER));
		$this->assertFalse(Auth::hasPermission(User::ROLE_ADMIN));
	}


	public function testInitAdmin()
	{
		$_SESSION = array(
			Auth::SESSION_IDENTIFIER => self::USER_ROLE_ADMIN_ID
		);

		Auth::init();

		$this->assertTrue(Auth::isLoggedIn());
		$this->assertInstanceOf('User', Auth::getUser());
		$this->assertTrue(Auth::hasPermission(User::ROLE_USER));
		$this->assertTrue(Auth::hasPermission(User::ROLE_ADMIN));
	}


	public function testLogin()
	{
		$_SESSION = array();

		Auth::init();

		$this->assertFalse(Auth::isLoggedIn());

		$this->assertTrue(Auth::login('user@domain.tld', 'testtest'));

		$this->assertTrue(Auth::isLoggedIn());
	}


	public function testLoginInvalidEmail()
	{
		$_SESSION = array();

		Auth::init();

		$this->assertFalse(Auth::isLoggedIn());

		$this->assertFalse(Auth::login('domain.tld', 'test'));

		$this->assertFalse(Auth::isLoggedIn());
	}


	public function testLoginInvalidUser()
	{
		$_SESSION = array();

		Auth::init();

		$this->assertFalse(Auth::isLoggedIn());

		$this->assertFalse(Auth::login('no.user@domain.tld', 'test'));

		$this->assertFalse(Auth::isLoggedIn());
	}


	public function testLogout()
	{
		$_SESSION = array(
			Auth::SESSION_IDENTIFIER => self::USER_ROLE_USER_ID
		);

		Auth::init();

		$this->assertTrue(Auth::isLoggedIn());

		Auth::logout();

		$this->assertFalse(Auth::isLoggedIn());
		$this->assertArrayNotHasKey(Auth::SESSION_IDENTIFIER, $_SESSION);
	}


	/**
	 * @param int $length
	 * @return string
	 */
	protected static function genTestPw($length)
	{
		return substr(str_shuffle("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789.-+=_,!@$#*%<>[]{}"), 0, $length);
	}


	/**
	 * @expectedException AuthException
	 * @expectedExceptionCode 2
	 */
	public function testValidateNewPasswordFirstEmpty()
	{
		Auth::validateNewPassword('', static::genTestPw(Config::get('password.min_length', 8)));
	}


	/**
	 * @expectedException AuthException
	 * @expectedExceptionCode 2
	 */
	public function testValidateNewPasswordLastEmpty()
	{
		Auth::validateNewPassword(static::genTestPw(Config::get('password.min_length', 8)), '');
	}


	/**
	 * @expectedException AuthException
	 * @expectedExceptionCode 3
	 */
	public function testValidateNewPasswordNotEqual()
	{
		$pw = static::genTestPw(Config::get('password.min_length', 8));
		Auth::validateNewPassword($pw, $pw.'neq');
	}


	/**
	 * @expectedException AuthException
	 * @expectedExceptionCode 4
	 */
	public function testValidateNewPasswordTooShort()
	{
		$pw = static::genTestPw(Config::get('password.min_length', 8) - 1);
		Auth::validateNewPassword($pw, $pw);
	}


	public function testValidateNewPasswordOk()
	{
		$pw = static::genTestPw(Config::get('password.min_length', 8));
		Auth::validateNewPassword($pw, $pw);
	}


	public function testGeneratePasswordHash()
	{
		Auth::generatePasswordHash(static::genTestPw(Config::get('password.min_length', 8)));
	}


	public function testGeneratePasswordHashAlgorithmFallback()
	{
		Config::set('password.hash_algorithm', '--not-an-algorithm--');
		Auth::generatePasswordHash(static::genTestPw(Config::get('password.min_length', 8)));
	}


	public function testChangeUserPassword()
	{
		$this->assertTrue(Auth::login('user@domain.tld', 'testtest'));

		Auth::changeUserPassword(static::USER_ROLE_USER_ID, 'newpassword');

		$this->assertFalse(Auth::login('user@domain.tld', 'testtest'));

		$this->assertTrue(Auth::login('user@domain.tld', 'newpassword'));
	}

}