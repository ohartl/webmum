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
	 * @param array $userData
	 */
	private static function loginUserByArray($userData)
	{
		static::$loggedInUser = new User($userData);
	}


	/**
	 * Checks session for logged in user, validates the login and finally logs him in.
	 */
	private static function loginUserViaSession()
	{
		global $_SESSION, $db;

		if(isset($_SESSION[static::SESSION_IDENTIFIER])
			&& !empty($_SESSION[static::SESSION_IDENTIFIER])
		){
			$userId = $_SESSION[static::SESSION_IDENTIFIER];

			// check if user still exists in database
			$sql = "SELECT * FROM `".DBT_USERS."` WHERE `".DBC_USERS_ID."` = '$userId' LIMIT 1;";
			if(!$userExists = $db->query($sql)){
				dbError($db->error);
			}

			// User exists,
			if($userExists->num_rows === 1){
				$userData = $userExists->fetch_assoc();

				static::loginUserByArray($userData);
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
		global $db;

		$email = $db->escape_string(strtolower($email));
		$password = $db->escape_string($password);

		$emailInParts = explode("@", $email);
		if(count($emailInParts) !== 2) {
			return false;
		}
		$username = $emailInParts[0];
		$domain = $emailInParts[1];

		// Check for user in database
		$sql = "SELECT * FROM `".DBT_USERS."` WHERE `".DBC_USERS_USERNAME."` = '$username' AND `".DBC_USERS_DOMAIN."` = '$domain' LIMIT 1;";
		if(!$result = $db->query($sql)){
			dbError($db->error);
		}

		// Check if user exists
		if($result->num_rows === 1){
			$userData = $result->fetch_assoc();

			if(static::checkPasswordByHash($password, $userData[DBC_USERS_PASSWORD])){

				static::loginUserByArray($userData);

				$_SESSION[static::SESSION_IDENTIFIER] = $userData[DBC_USERS_ID];

				return true;
			}
		}

		return false;
	}


	/**
	 * Check if current user has a certain role
	 *
	 * @param string $requiredRole
	 *
	 * @return bool
	 */
	public static function hasPermission($requiredRole)
	{
		if(static::isLoggedIn()) {
			$user = static::getUser();
			return $user->getRole() === $requiredRole || $user->getRole() === User::ROLE_ADMIN;
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
	 * 		2: One password field is empty
	 * 		3: Passwords are not equal
	 * 		4: Passwort is too snort
	 */
	public static function validateNewPassword($password, $passwordRepeated)
	{
		// Check if one passwort input is empty
		if(empty($password)){
			throw new Exception("First password field was'nt filled out.", 2);
		}
		elseif(empty($passwordRepeated)){
			throw new Exception("Repeat password field was'nt filled out.", 2);
		}
		else {
			// Check if password are equal
			if($password !== $passwordRepeated){
				throw new Exception("The repeated password must be equal to the first one.", 3);
			}
			else {
				// Check if password length is okay
				if(strlen($password) < MIN_PASS_LENGTH){
					throw new Exception("Passwords must be at least ".MIN_PASS_LENGTH." characters long.", 4);
				}
			}
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
		switch(PASS_HASH_SCHEMA){
			case "SHA-256":
				return '$5$rounds=5000$';

			case "BLOWFISH":
				return '$2a$09$';

			case "SHA-512":
			default:
				return '$6$rounds=5000$';
		}
	}


	/**
	 * @param string $password
	 *
	 * @return string
	 */
	private static function generatePasswordHash($password)
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
		global $db;

		$passwordHash = static::generatePasswordHash($password);

		$userId = $db->escape_string($userId);
		$passwordHash = $db->escape_string($passwordHash);

		if(!$db->query("UPDATE `".DBT_USERS."` SET `".DBC_USERS_PASSWORD."` = '$passwordHash' WHERE `".DBC_USERS_ID."` = '$userId';")){
			dbError($db->error);
		}
	}
}
