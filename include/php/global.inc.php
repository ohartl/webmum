<?php

/*
 * Message manager
 * Types of notifications:
 * success
 * fail
 * info
 */

$MESSAGES = array();

function add_message($type, $message)
{
	global $MESSAGES;
	$newmessage = array();
	$newmessage['type'] = $type;
	$newmessage['message'] = $message;

	$MESSAGES[] = $newmessage;
}

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


/*
 * Add message to logfile
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
 * @param string $url
 * @return string
 */
function url($url)
{
	$base = FRONTEND_BASE_PATH;
	if (substr($base, -1) === '/') {
		$base = substr($base, 0, -1);
	}
	if (strlen($url) > 0 && $url[0] === '/') {
		$url = substr($url, 1);
	}
	return $base.'/'.$url;
}

/**
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
	$separators = array(',', ';', "\r\n", "\r", "\n", '|', ':');

	$list = explode('|', str_replace($separators, '|', $input));
	foreach($list as $i => &$email){
		if(empty($email)){
			unset($list[$i]);
		}
		else{
			$email = trim($email);
		}
	}

	return array_values(
		array_map(
			'strtolower',
			array_unique(
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
	return implode($glue, $list);
}

/**
 * Format single email address
 *
 * @param string $input
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
 * @return string
 */
function formatEmails($input, $glue)
{
	if(!is_array($input)){
		$input = stringToEmails($input);
	}

	return emailsToString($input, $glue);
}


