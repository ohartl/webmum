<?php

/**
 * @covers Auth
 */
abstract class TestCase extends PHPUnit_Framework_TestCase
{
	const USER_ROLE_ADMIN_ID = 100001;
	const USER_ROLE_ADMIN_ID_LIMITED_NO_ACCESS = 100002;
	const USER_ROLE_ADMIN_ID_LIMITED_HAS_ACCESS = 100003;
	const USER_ROLE_USER_ID = 100013;


	public static function setUpBeforeClass()
	{

		Database::getInstance()->insert(
			'users',
			array(
				'id' => static::USER_ROLE_ADMIN_ID,
				'username' => 'admin',
				'domain' => 'domain.tld',
				'password' => Auth::generatePasswordHash('testtest'),
				'mailbox_limit' => 0,
			)
		);

		Database::getInstance()->insert(
			'users',
			array(
				'id' => static::USER_ROLE_ADMIN_ID_LIMITED_NO_ACCESS,
				'username' => 'no-access-limited-admin',
				'domain' => 'domain.tld',
				'password' => Auth::generatePasswordHash('testtest'),
				'mailbox_limit' => 0,
			)
		);

		Database::getInstance()->insert(
			'users',
			array(
				'id' => static::USER_ROLE_ADMIN_ID_LIMITED_HAS_ACCESS,
				'username' => 'has-access-limited-admin',
				'domain' => 'domain.tld',
				'password' => Auth::generatePasswordHash('testtest'),
				'mailbox_limit' => 0,
			)
		);

		Database::getInstance()->insert(
			'users',
			array(
				'id' => static::USER_ROLE_USER_ID,
				'username' => 'user',
				'domain' => 'domain.tld',
				'password' => Auth::generatePasswordHash('testtest'),
				'mailbox_limit' => 64,
			)
		);

		Config::set('admins', array('admin@domain.tld', 'limited-admin@domain.tld'));
		Config::set('admin_domain_limits', array(
			'no-access-limited-admin@domain.tld' => array(),
			'has-access-limited-admin@domain.tld' => array('his-domain.tld'),
		));
	}


	public static function tearDownAfterClass()
	{
		Database::getInstance()->delete('users', 'id', static::USER_ROLE_ADMIN_ID);
		Database::getInstance()->delete('users', 'id', static::USER_ROLE_ADMIN_ID_LIMITED_NO_ACCESS);
		Database::getInstance()->delete('users', 'id', static::USER_ROLE_ADMIN_ID_LIMITED_HAS_ACCESS);
		Database::getInstance()->delete('users', 'id', static::USER_ROLE_USER_ID);
	}

}