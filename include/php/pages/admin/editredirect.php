<?php

$id = null;
$redirect = null;

if(isset($_GET['id'])){
	$id = $db->escape_string($_GET['id']);

	if(defined('DBC_ALIASES_MULTI_SOURCE')){
		$sql = "SELECT r.* FROM (
			SELECT
				group_concat(g.`".DBC_ALIASES_ID."` ORDER BY g.`".DBC_ALIASES_ID."` SEPARATOR ',') AS `".DBC_ALIASES_ID."`,
				group_concat(g.`".DBC_ALIASES_SOURCE."` SEPARATOR ',') AS `".DBC_ALIASES_SOURCE."`,
				g.`".DBC_ALIASES_DESTINATION."`,
				g.`".DBC_ALIASES_MULTI_SOURCE."`
			FROM `".DBT_ALIASES."` AS g
			WHERE g.`".DBC_ALIASES_MULTI_SOURCE."` IS NOT NULL
			GROUP BY g.`".DBC_ALIASES_MULTI_SOURCE."`
		UNION
			SELECT
				s.`".DBC_ALIASES_ID."`,
				s.`".DBC_ALIASES_SOURCE."`,
				s.`".DBC_ALIASES_DESTINATION."`,
				s.`".DBC_ALIASES_MULTI_SOURCE."`
			FROM `".DBT_ALIASES."` AS s
			WHERE s.`".DBC_ALIASES_MULTI_SOURCE."` IS NULL
		) AS r
		WHERE `".DBC_ALIASES_ID."` = '$id' LIMIT 1;";
	}
	else{
		$sql = "SELECT `".DBC_ALIASES_ID."`, `".DBC_ALIASES_SOURCE."`, `".DBC_ALIASES_DESTINATION."` FROM `".DBT_ALIASES."` WHERE `".DBC_ALIASES_ID."` = '$id' LIMIT 1;";
	}

	if(!$result = $db->query($sql)){
		dbError($db->error);
	}

	if($result->num_rows !== 1){
		// Redirect does not exist, redirect to overview
		redirect("admin/listredirects/");
	}

	$redirect = $result->fetch_assoc();

	$sources = stringToEmails($redirect[DBC_ALIASES_SOURCE]);
	$destinations = stringToEmails($redirect[DBC_ALIASES_DESTINATION]);
}

if(isset($_POST['savemode'])){
	$savemode = $_POST['savemode'];

	$sources = stringToEmails($_POST['source']);
	$destinations = stringToEmails($_POST['destination']);

	// validate emails
	$emailErrors = array();

	// basic email validation is not working 100% correct though
	foreach(array_merge($sources, $destinations) as $email){
		if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
			$emailErrors[$email] = "Address \"$email\" is not a valid email address.";
		}
	}

	if(defined('VALIDATE_ALIASES_SOURCE_ENABLED')){
		$sql = "SELECT GROUP_CONCAT(`".DBC_DOMAINS_DOMAIN."` SEPARATOR ',') as `".DBC_DOMAINS_DOMAIN."` FROM `".DBT_DOMAINS."`";
		if(!$resultDomains = $db->query($sql)){
			dbError($db->error);
		}
		$domainRow = $resultDomains->fetch_assoc();
		$domains = explode(',', $domainRow[DBC_DOMAINS_DOMAIN]);

		// validate source emails are on domains
		foreach($sources as $email){
			if(isset($emailErrors[$email])){
				continue;
			}
			$splited = explode('@', $email);
			if(count($splited) !== 2 || !in_array($splited[1], $domains)){
				$emailErrors[$email] = "Domain of source address \"$email\" not in domains.";
			}
		}
	}

	if(count($emailErrors) > 0){
		add_message("fail", implode("<br>", $emailErrors));
	}
	else{
		if(count($emailErrors) === 0 && $savemode === "edit" && !is_null($redirect)){

			if(count($sources) > 0 && count($destinations) > 0){
				$destination = $db->escape_string(emailsToString($destinations));
				$source = $db->escape_string(emailsToString($sources));

				$key = DBC_ALIASES_ID;
				if(defined('DBC_ALIASES_MULTI_SOURCE') && !empty($redirect[DBC_ALIASES_MULTI_SOURCE])){
					$key = DBC_ALIASES_MULTI_SOURCE;
				}
				$value = $redirect[$key];

				$sql = "SELECT `".DBC_ALIASES_ID."`, `".DBC_ALIASES_SOURCE."` FROM `".DBT_ALIASES."` WHERE `$key` = '$value'";
				if(!$resultExisting = $db->query($sql)){
					dbError($db->error);
				}

				$sourceIdMap = array();
				while($existingRedirect = $resultExisting->fetch_assoc()){
					$sourceIdMap[$existingRedirect[DBC_ALIASES_SOURCE]] = $existingRedirect[DBC_ALIASES_ID];
				}

				// multi source handling
				$hash = (count($sources) === 1) ? "NULL" : "'".md5($source)."'";

				foreach($sources as $sourceAddress){
					$sourceAddress = $db->escape_string(formatEmail($sourceAddress));

					if(isset($sourceIdMap[$sourceAddress])){
						// edit existing source
						$id = $sourceIdMap[$sourceAddress];

						$additionalSql = defined('DBC_ALIASES_MULTI_SOURCE') ? ", `".DBC_ALIASES_MULTI_SOURCE."` = $hash " : "";
						$sql = "UPDATE `".DBT_ALIASES."` SET `".DBC_ALIASES_SOURCE."` = '$sourceAddress', `".DBC_ALIASES_DESTINATION."` = '$destination' $additionalSql WHERE `".DBC_ALIASES_ID."` = '$id';";

						if(!$result = $db->query($sql)){
							dbError($db->error);
						}

						unset($sourceIdMap[$sourceAddress]); // mark updated
					}
					else{
						// add new source
						$additionalSql = defined('DBC_ALIASES_MULTI_SOURCE') ? ", `".DBC_ALIASES_MULTI_SOURCE."`" : "";
						$additionalSqlValue = defined('DBC_ALIASES_MULTI_SOURCE') ? ", $hash" : "";
						$sql = "INSERT INTO `".DBT_ALIASES."` (`".DBC_ALIASES_SOURCE."`, `".DBC_ALIASES_DESTINATION."` $additionalSql) VALUES ('$sourceAddress', '$destination' $additionalSqlValue);";

						if(!$result = $db->query($sql)){
							dbError($db->error);
						}
					}
				}

				// delete none updated redirect
				foreach($sourceIdMap as $source => $id){
					$sql = "DELETE FROM `".DBT_ALIASES."` WHERE `".DBC_ALIASES_ID."` = '$id';";

					if(!$result = $db->query($sql)){
						dbError($db->error);
					}
				}

				// Edit successfull, redirect to overview
				redirect("admin/listredirects/?edited=1");
			}
			else{
				add_message("fail", "Redirect could not be edited. Fill out all fields.");
			}
		}

		else if(count($emailErrors) === 0 && $savemode === "create"){
			if(count($sources) > 0 && count($destinations) > 0){

				$values = array();
				foreach($sources as $source){
					$values[] = "'$source'";
				}
				$sql = "SELECT `".DBC_ALIASES_SOURCE."` FROM `".DBT_ALIASES."` WHERE `".DBC_ALIASES_SOURCE."` IN (".implode(',', $values).");";
				if(!$resultExisting = $db->query($sql)){
					dbError($db->error);
				}

				$errorExisting = array();
				while($existingRedirect = $resultExisting->fetch_assoc()){
					$email = $existingRedirect[DBC_ALIASES_SOURCE];
					$errorExisting[] = "Source address \"$email\" is already redirected to some destination.";
				}

				if(count($errorExisting) > 0){
					add_message("fail", implode("<br>", $errorExisting));
				}
				else{
					$destination = $db->escape_string(emailsToString($destinations));
					$source = $db->escape_string(emailsToString($sources));

					$values = array();
					if(count($sources) === 1){
						$values[] = "('$source', '$destination', NULL)";
					}
					else{
						// multi source handling
						$hash = md5($source);

						foreach($sources as $sourceAddress){
							$sourceAddress = $db->escape_string(formatEmail($sourceAddress));
							$additionalSqlValue = defined('DBC_ALIASES_MULTI_SOURCE') ? ", '$hash'" : "";
							$values[] = "('$sourceAddress', '$destination' $additionalSqlValue)";
						}
					}

					$additionalSql = defined('DBC_ALIASES_MULTI_SOURCE') ? ", `".DBC_ALIASES_MULTI_SOURCE."`" : "";
					$sql = "INSERT INTO `".DBT_ALIASES."` (`".DBC_ALIASES_SOURCE."`, `".DBC_ALIASES_DESTINATION."` $additionalSql) VALUES ".implode(',', $values).";";

					if(!$result = $db->query($sql)){
						dbError($db->error);
					}
					else{
						// Redirect created, redirect to overview
						redirect("admin/listredirects/?created=1");
					}
				}
			}
			else{
				add_message("fail", "Redirect could not be created. Fill out all fields.");
			}
		}
	}
}


// Select mode
$mode = "create";
if(isset($_GET['id'])){
	$mode = "edit";
}
?>

<h1><?php echo ($mode === "create") ? 'Create' : 'Edit'; ?> Redirect</h1>

<?php output_messages(); ?>

<p>
	<a class="button button-small" href="<?php echo FRONTEND_BASE_PATH; ?>admin/listredirects/">&#10092; Back to redirects list</a>
</p>

<form action="" method="post">
	<input name="savemode" type="hidden" value="<?php echo isset($mode) ? $mode : ''; ?>"/>

	<p>
		Enter single or multiple addresses separated by comma, semicolon or newline.
	</p>

	<table>
		<tr>
			<th>Source</th>
			<th>Destination</th>
		</tr>
		<tr>
			<td>
				<?php if(defined('DBC_ALIASES_MULTI_SOURCE')): ?>
					<textarea name="source" class="textinput" placeholder="Source" required="required" autofocus><?php echo isset($sources) ? strip_tags(emailsToString($sources, FRONTEND_EMAIL_SEPARATOR_FORM)) : ''; ?></textarea>
				<?php else: ?>
					<input type="text" name="source" class="textinput" placeholder="Source (single address)" required="required" autofocus value="<?php echo isset($sources) ? strip_tags(emailsToString($sources, FRONTEND_EMAIL_SEPARATOR_FORM)) : ''; ?>"/>
				<?php endif; ?>
			</td>
			<td>
				<textarea name="destination" class="textinput" placeholder="Destination" required="required"><?php echo isset($destinations) ? strip_tags(emailsToString($destinations, FRONTEND_EMAIL_SEPARATOR_FORM)) : ''; ?></textarea>
			</td>
		</tr>
	</table>

	<p>
		<input type="submit" class="button button-small" value="Save settings">
	</p>
</form>