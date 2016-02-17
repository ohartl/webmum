<?php

if(!isset($_GET['id'])){
	// Redirect id not set, redirect to overview
	redirect("admin/listredirects/");
}

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

if(isset($_POST['confirm'])){
	$confirm = $_POST['confirm'];

	if($confirm === "yes"){

		$key = DBC_ALIASES_ID;
		if(defined('DBC_ALIASES_MULTI_SOURCE') && !empty($redirect[DBC_ALIASES_MULTI_SOURCE])){
			$key = DBC_ALIASES_MULTI_SOURCE;
		}
		$value = $redirect[$key];

		$sql = "DELETE FROM `".DBT_ALIASES."` WHERE `$key` = '$value'";

		if(!$result = $db->query($sql)){
			dbError($db->error);
		}
		else{
			// Delete redirect successfull, redirect to overview
			redirect("admin/listredirects/?deleted=1");
		}
	}
	else{
		// Choose to not delete redirect, redirect to overview
		redirect("admin/listredirects/");
	}
}

else{
	$source = $redirect[DBC_ALIASES_SOURCE];
	$destination = $redirect[DBC_ALIASES_DESTINATION];
	?>
	<h1>Delete redirection?</h1>

	<table>
		<tr>
			<th>Source</th>
			<th>Destination</th>
		</tr>
		<tr>
			<td><?php echo strip_tags(formatEmails($source, FRONTEND_EMAIL_SEPARATOR_TEXT)); ?></td>
			<td><?php echo strip_tags(formatEmails($destination, FRONTEND_EMAIL_SEPARATOR_TEXT)); ?></td>
		</tr>
	</table>

	<form action="" method="post">
		<select name="confirm">
			<option value="no">No!</option>
			<option value="yes">Yes!</option>
		</select>

		<input type="submit" class="button button-small" value="Okay"/>
	</form>
	<?php
}
?>