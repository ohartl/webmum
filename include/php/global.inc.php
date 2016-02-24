<?php

/**
 * @param string $errorMessage
 * @param null|string $sql
 */
function dbError($errorMessage, $sql = null)
{
	die('There was an error running the query ['.$errorMessage.']'.(!is_null($sql)?' with statement "'.$sql.'"':''));
}


/**
 * Holds all messages
 * @var array
 */
$MESSAGES = array();


/**
 * Add a new message
 * @param string $type Supported types: success, fail, info
 * @param string $message
 */
function add_message($type, $message)
{
	global $MESSAGES;
	$newmessage = array();
	$newmessage['type'] = $type;
	$newmessage['message'] = $message;

	$MESSAGES[] = $newmessage;
}

/**
 * Print all messages
 */
function output_messages()
{
	global $MESSAGES;
	if(count($MESSAGES) > 0) {
		echo '<div class="messages">';
		foreach($MESSAGES as $message){
			echo '<div class="notification notification-'.$message['type'].'">'.$message['message'].'</div>';
		}
		echo '</div>';
	}
}


/**
 * Add message to logfile
 *
 * @param string $text
 */
function writeLog($text)
{
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


/**
 * Generate full url
 *
 * @param string $url
 *
 * @return string
 */
function url($url)
{
	return sprintf('%s/%s', rtrim(FRONTEND_BASE_PATH, '/'), trim($url, '/'));
}

/**
 * Redirect user to an url
 *
 * @param string $url
 */
function redirect($url)
{
	header("Location: ".url($url));
	exit;
}


/**
 * Split comma, semicolon or newline separated list of emails to string
 *
 * @param string $input
 *
 * @return array
 */
function stringToEmails($input)
{
	$list = explode(
		'|',
		str_replace(
			array(' ', ',', ';', "\r\n", "\r", "\n", '|', ':'),
			'|',
			$input
		)
	);

	foreach($list as $i => &$email){
		if(empty($email)){
			unset($list[$i]);
		}
	}

	return array_values(
		array_unique(
			array_map(
				'formatEmail',
				$list
			)
		)
	);
}

/**
 * List of emails to comma or $glue separated list string
 *
 * @param array $list
 * @param string $glue
 *
 * @return string
 */
function emailsToString($list, $glue = ',')
{
	if(is_string($list)){
		return $list;
	}

	return implode($glue, $list);
}

/**
 * Format single email address
 *
 * @param string $input
 *
 * @return string
 */
function formatEmail($input)
{
	return strtolower(trim($input));
}

/**
 * Format email addresses (single, multiple in separated list, or array of email addresses)
 *
 * @param string|array $input
 * @param string $glue
 *
 * @return string
 */
function formatEmails($input, $glue)
{
	if(!is_array($input)){
		$input = stringToEmails($input);
	}

	return emailsToString($input, $glue);
}


