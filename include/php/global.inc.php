<?php


/**
 * Add message to logfile
 *
 * @param string $text
 */
function writeLog($text)
{
	if(Config::get('options.enable_logging', false) && Config::has('log_path')){
		$logdestination = realpath(Config::get('log_path')).DIRECTORY_SEPARATOR."webmum.log";
		if(is_writable(Config::get('log_path'))){
			$logfile = fopen($logdestination, "a") or die("Unable to create or open logfile \"".$logdestination."\" in root directory!");
			fwrite($logfile, date('M d H:i:s').": ".$text."\n");
			fclose($logfile);
		}
		else{
			die("Directory \"".Config::get('log_path')."\" isn't writable");
		}
	}
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


function formatEmailsText($input)
{
	return formatEmails(
		$input,
		str_replace(PHP_EOL, '<br>', Config::get('frontend_options.email_separator_text', ', '))
	);
}


function formatEmailsForm($input)
{
	return strip_tags(
		formatEmails(
			$input,
			Config::get('frontend_options.email_separator_form', ',')
		)
	);
}
