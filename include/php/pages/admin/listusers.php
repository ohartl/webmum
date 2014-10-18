<?php 

if($_GET['deleted'] == "1"){
	add_message("success", "User deleted successfully."); 
}
else if($_GET['created'] == "1"){
	add_message("success", "User created successfully.");
}
else if($_GET['edited'] == "1"){
	add_message("success", "User edited successfully.");
}
else if($_GET['adm_del'] == "1"){
	add_message("fail", "Admin user cannot be deleted.");
}
	
?>



<h1>List of all mailbox accounts</h1>


<?php output_messages(); ?>


<?php 

$sql = "SELECT * FROM `".DBT_USERS."` ORDER BY `".DBC_USERS_DOMAIN."`, `".DBC_USERS_USERNAME."` ASC;";

if(!$result = $db->query($sql)){
	die('There was an error running the query [' . $db->error . ']');
}

?>

<p>
	<a class="button button-small" href="<?php echo FRONTEND_BASE_PATH; ?>admin/edituser/">Create new user</a>
</p>

<table class="list">
<tr class="head"><th>Username</th> <th>Domain</th> <th>Mailbox Limit (MB)</th> <th></th> <th></th><tr>

<?php 
	while($row = $result->fetch_assoc()){
		echo "<tr><td>".$row[DBC_USERS_USERNAME]."</td><td>".$row[DBC_USERS_DOMAIN]."</td><td>".$row[DBC_USERS_MAILBOXLIMIT]."<td><a href=\"".FRONTEND_BASE_PATH."admin/edituser/?id=".$row[DBC_USERS_ID]."\">[Edit]</a></td> <td><a href=\"".FRONTEND_BASE_PATH."admin/deleteuser/?id=".$row[DBC_USERS_ID]."\">[Delete]</a></td> </tr>";
	}
?>
</table>