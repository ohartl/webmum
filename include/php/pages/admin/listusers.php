<?php 

if(isset($_GET['deleted']) && $_GET['deleted'] == "1"){
	add_message("success", "User deleted successfully."); 
}
else if(isset($_GET['created']) && $_GET['created'] == "1"){
	add_message("success", "User created successfully.");
}
else if(isset($_GET['edited']) && $_GET['edited'] == "1"){
	add_message("success", "User edited successfully.");
}
else if(isset($_GET['adm_del']) && $_GET['adm_del'] == "1"){
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
<tr class="head"><th>Username</th> <th>Domain</th> <?php if(defined('DBC_USERS_MAILBOXLIMIT')){ ?><th>Mailbox Limit (MB)</th> <?php } ?><th></th> <th></th><tr>

<?php 	
	while($row = $result->fetch_assoc()){
		if(defined('DBC_USERS_MAILBOXLIMIT')){
			$mailbox_limit_column = '<td>'.strip_tags($row[DBC_USERS_MAILBOXLIMIT]).'</td>';
		}
		else{
			$mailbox_limit_column = '';
		}

		echo "<tr> <td>".strip_tags($row[DBC_USERS_USERNAME])."</td><td>".strip_tags($row[DBC_USERS_DOMAIN])."</td>".$mailbox_limit_column."<td><a href=\"".FRONTEND_BASE_PATH."admin/edituser/?id=".$row[DBC_USERS_ID]."\">[Edit]</a></td> <td><a href=\"".FRONTEND_BASE_PATH."admin/deleteuser/?id=".$row[DBC_USERS_ID]."\">[Delete]</a></td> </tr>";
	}
?>
</table>