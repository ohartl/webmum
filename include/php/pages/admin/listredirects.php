<?php

if(isset($_GET['deleted']) && $_GET['deleted'] == "1"){
	add_message("success", "Redirect deleted successfully.");
}
else if(isset($_GET['created']) && $_GET['created'] == "1"){
	add_message("success", "Redirect created successfully.");
}
else if(isset($_GET['edited']) && $_GET['edited'] == "1"){
	add_message("success", "Redirect edited successfully.");
}


$sql = "SELECT * FROM `".DBT_ALIASES."` ORDER BY `".DBC_ALIASES_SOURCE."` ASC;";

if(!$result = $db->query($sql)){
	die('There was an error running the query [' . $db->error . ']');
}

?>

<h1>Redirects</h1>

<?php output_messages(); ?>

<p>
	<a class="button button-small" href="<?php echo FRONTEND_BASE_PATH; ?>admin/editredirect/">Create new redirect</a>
</p>

<table class="list">
<tr class="head"><th>Source</th> <th>Destination</th> <th></th><th></th><tr>

<?php 
	while($row = $result->fetch_assoc()){
		echo "<tr><td>".strip_tags($row[DBC_ALIASES_SOURCE])."</td> <td>".strip_tags($row[DBC_ALIASES_DESTINATION])."</td> <td><a href=\"".FRONTEND_BASE_PATH."admin/editredirect/?id=".$row[DBC_ALIASES_ID]."\">[Edit]</a></td> <td><a href=\"".FRONTEND_BASE_PATH."admin/deleteredirect/?id=".$row[DBC_ALIASES_ID]."\">[Delete]</a></td></tr>";
	}
?>
</table>