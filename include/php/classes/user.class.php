<?php
class USER {
	
	/* 
	 * Class attributes
	 */
	
	private $uid;
	private $email;
	private $role;
	private $loggedin = false;
	
	/*
	 * Constructor
	 * 
	 * Fills the user object up with anonymous data
	 */
	
	function __construct(){
		// Start session
		session_start();
		session_regenerate_id();
		
		if($_SESSION['email'] === ADMIN_EMAIL){
			$this->role = "admin";
		}
		else{
			$this->role = "user";
		}	
		
		if(isset($_SESSION['uid']) && $_SESSION['uid'] != ""){
			// revive session ...
			$this->uid = $_SESSION['uid'];
			$this->loggedin = true;
		}
	}
	
	/*
	 * Getter functions
	 */
	
	function getUID(){
		return $this->uid;
	}
	
	function getRole(){
		return $this->role;
	}
	
	function isLoggedIn(){
		return $this->loggedin;
	}
	
	
	
	/*
	 * Login function. Checks login data and writes information to SESSION
	*
	* Returns:
	* true: Login was successful
	* false: Login was not successful
	*/
	
	function login($email, $password){
		global $db;
		// Prepare e-mail address
		$email = $db->escape_string($email);
		$password = $db->escape_string($password);
		$email_part = explode("@", $email);
		$username = $email_part[0];
		$domain = $email_part[1];
	
	
		// Check e-mail address
		$sql = "SELECT `".DBC_USERS_ID."`, `".DBC_USERS_PASSWORD."` FROM `".DBT_USERS."` WHERE `".DBC_USERS_USERNAME."` = '$username' AND `".DBC_USERS_DOMAIN."` = '$domain' LIMIT 1;";
	
		if(!$result = $db->query($sql)){
			die('There was an error running the query [' . $db->error . ']');
		}
	
		if($result->num_rows === 1){
			$userdata = $result->fetch_array(MYSQLI_ASSOC);
			$uid = $userdata[DBC_USERS_ID];
			$password_hash = $userdata[DBC_USERS_PASSWORD];
				
			// Check password
			if (crypt($password, $password_hash) === $password_hash) {
				// Password is valid, start a logged-in user session
				$this->loggedin = true;
				$_SESSION['uid'] = $uid;
				$_SESSION['email'] = $email;
	
				return true;
			}
			else {
				// Password is invalid
				return false;
			}
		}
		else{
			// User could not be found
			return false;
		}
	}
	
	
	/*
	 * Changes user password. 
	 * Returns:
	 * true: Change success
	 * false: Error
	 */
	
	function change_password($newpass, $newpass_rep){
		$pass_ok = check_new_pass($newpass, $newpass_rep);
		if($pass_ok === true){
			$pass_hash = gen_pass_hash($newpass);
			write_pass_hash_to_db($pass_hash, $this->uid);
			return true;
		}
		else{
			return false;
		}
	}
}
?>