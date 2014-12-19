<?php 

/*
 * Message manager
 * Types of notifications:
 * success
 * fail
 * info
 */

$MESSAGES = array();

function add_message($type, $message){
	global $MESSAGES;
	$newmessage = array();
	$newmessage['type'] = $type;
	$newmessage['message'] = $message;

	$MESSAGES[] = $newmessage;
}

function output_messages(){
	echo "<div class=\"messages\">";
	global $MESSAGES;
	foreach($MESSAGES as $message){
		echo "<div class=\"notification notification-".$message['type']."\">".$message['message']."</div>";
	}
	echo "</div>";
}




/*
 * Function checks password input for new password
 * Return codes:
 * true: password is okay
 * 2: One password field is empty
 * 3: Passwords are not equal
 * 4: Passwort is too snort
 */

function check_new_pass($pass1, $pass2){
	global $PASS_ERR;
	global $PASS_ERR_MSG;
	// Check if one passwort input is empty
	if($pass1 !== "" && $pass2 !== ""){
		// Check if password are equal
		if($pass1 === $pass2){
			// Check if password length is okay
			if(strlen($pass1) >= MIN_PASS_LENGTH){
				// Password is okay.
				return true;
			}
			else{
				// Password is not long enough
				$PASS_ERR = 4;
				$PASS_ERR_MSG = "Password is not long enough. Please enter a password which has ".MIN_PASS_LENGTH." characters or more.";
				return $PASS_ERR;
			}
		}
		else{
			// Passwords are not equal
			$PASS_ERR = 3;
			$PASS_ERR_MSG = "Passwords are not equal.";
			return $PASS_ERR;
		}
	}
	else{
		// One password is empty.
		$PASS_ERR = 2;
		$PASS_ERR_MSG = "Not all password fields were filled out";
		return $PASS_ERR;
	}
}


function gen_pass_hash($pass){
	$salt = base64_encode(rand(1,1000000) + microtime());
	$pass_hash = crypt($pass, '$6$rounds=5000$'.$salt.'$');
	return $pass_hash;
}


function write_pass_hash_to_db($pass_hash, $uid){
	global $db;
	$uid = $db->escape_string($uid);
	$pass_hash = $db->escape_string($pass_hash);
	$db->query("UPDATE `".DBT_USERS."` SET `".DBC_USERS_PASSWORD."` = '$pass_hash' WHERE `".DBC_USERS_ID."` = '$uid';");
}


/*
 * Add message to logfile
 */
function writeLog($text){
	if(defined('WRITE_LOG') && defined('WRITE_LOG_PATH')){
		$logdestination = realpath(WRITE_LOG_PATH).DIRECTORY_SEPARATOR."webmum.log";
		if(is_writable(WRITE_LOG_PATH)){
			$logfile = fopen($logdestination, "a") or die("Unable to create or open logfile \"".$logdestination."\" in root directory!");
			fwrite($logfile, date('M d H:i:s').": ".$text."\n");
			fclose($logfile);
		}
		else{
			die("Directory \"".WRITE_LOG_PATH."\" is not writable");
		}
	}
}




?>
