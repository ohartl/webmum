<?php

class Auth
{
	const SESSION_IDENTIFIER = 'uid';

	/**
	 * @var User|null
	 */
	private static $loggedInUser = null;


	/**
	 * Init Authentication
	 */
	public static function init()
	{
		static::loginUserViaSession();
	}


	/**
	 * Check whether the user is logged in or not.
	 *
	 * @return bool
	 */
	public static function isLoggedIn()
	{
		return !is_null(static::$loggedInUser);
	}


	/**
	 * Get the currently logged in user.
	 *
	 * @return null|User
	 */
	public static function getUser()
	{
		return static::$loggedInUser;
	}


	/**
	 * @param AbstractModel $user
	 */
	private static function loginUserByModel($user)
	{
		static::$loggedInUser = $user;
	}


	/**
	 * Checks session for logged in user, validates the login and finally logs him in.
	 */
	private static function loginUserViaSession()
	{
		global $_SESSION;

		if(isset($_SESSION[static::SESSION_IDENTIFIER])
			&& !empty($_SESSION[static::SESSION_IDENTIFIER])
		){
			$userId = $_SESSION[static::SESSION_IDENTIFIER];

			/** @var User $user */
			$user = User::find($userId);

			// check if user still exists in database
			if(!is_null($user)){
				static::loginUserByModel($user);
			}
		}
	}


	/**
	 * Login user with provided credentials and save login in session
	 *
	 * @param string $email
	 * @param string $password
	 *
	 * @return bool
	 */
	public static function login($email, $password)
	{
		$email = strtolower($email);

		$emailInParts = explode("@", $email);
		if(count($emailInParts) !== 2){
			return false;
		}
		$username = $emailInParts[0];
		$domain = $emailInParts[1];

		/** @var User $user */
		$user = User::findWhereFirst(
			array(
				array(User::attr('username'), $username),
				array(User::attr('domain'), $domain),
			)
		);

		// Check if user exists
		if(!is_null($user)){
			if(static::checkPasswordByHash($password, $user->getPasswordHash())){

				static::loginUserByModel($user);

				$_SESSION[static::SESSION_IDENTIFIER] = $user->getId();

				return true;
			}
		}

		return false;
	}


	/**
	 * @return void
	 */
	public static function logout()
	{
		unset($_SESSION[static::SESSION_IDENTIFIER]);

		static::$loggedInUser = null;

		if(session_status() === PHP_SESSION_ACTIVE){
			session_destroy();
		}
	}


	/**
	 * Check if current user has a certain role, but User::ROLE_ADMIN will have access to all
	 *
	 * @param string $requiredRole
	 *
	 * @return bool
	 */
	public static function hasPermission($requiredRole)
	{
		if(static::isLoggedIn()){
			$user = static::getUser();

			return $user->getRole() === $requiredRole
			|| $user->getRole() === User::ROLE_ADMIN;
		}

		return false;
	}


	/**
	 * Checks the new password entered by user on certain criteria, and throws an Exception if its invalid.
	 *
	 * @param string $password
	 * @param string $passwordRepeated
	 *
	 * @throws Exception Codes explained below
	 *        2: One password field is empty
	 *        3: Passwords aren't equal
	 *        4: Passwort is too snort
	 */
	public static function validateNewPassword($password, $passwordRepeated)
	{
		// Check if one passwort input is empty
		if(empty($password)){
			throw new Exception("First password field was'nt filled out.", 2);
		}
		if(empty($passwordRepeated)){
			throw new Exception("Repeat password field was'nt filled out.", 2);
		}

		// Check if password are equal
		if($password !== $passwordRepeated){
			throw new Exception("The repeated password must be equal to the first one.", 3);
		}

		// Check if password length is okay
		if(Config::has('password.min_length')
			&& strlen($password) < Config::get('password.min_length')
		){
			throw new Exception("Passwords must be at least ".Config::get('password.min_length')." characters long.", 4);
		}
	}


	/**
	 * @param string $password
	 * @param string $hash
	 *
	 * @return bool
	 */
	public static function checkPasswordByHash($password, $hash)
	{
		return crypt($password, $hash) === $hash;
	}


	/**
	 * @return string
	 */
	private static function getPasswordSchemaPrefix()
	{
		$map = array(
			'SHA-256' => '$5$rounds=5000$',
			'BLOWFISH' => '$2a$09$',
			'SHA-512' => '$6$rounds=5000$',
		);

		$key = Config::get('password.hash_algorithm', 'SHA-512');

		if(!isset($map[$key])){
			$key = 'SHA-512';
		}

		return $map[$key];
	}


	/**
	 * @param string $password
	 *
	 * @return string
	 */
	public static function generatePasswordHash($password)
	{
		$salt = base64_encode(rand(1, 1000000) + microtime());
		$schemaPrefix = static::getPasswordSchemaPrefix();

		$hash = crypt($password, $schemaPrefix.$salt.'$');

		return $hash;
	}


	/**
	 * @param string $userId
	 * @param $password
	 */
	public static function changeUserPassword($userId, $password)
	{
		$passwordHash = static::generatePasswordHash($password);

		/** @var User $user */
		$user = User::find($userId);

		if(!is_null($user)){
			$user->setPasswordHash($passwordHash);
			$user->save();
		}
	}
}
