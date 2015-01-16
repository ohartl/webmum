<?php 
	if(isset($_GET['deleted']) && $_GET['deleted'] == "1"){
		add_message("success", "Domain deleted successfully.");
	}
	else if(isset($_GET['created']) && $_GET['created'] == "1"){
		add_message("success", "Domain created successfully.");
	}	
	else if(isset($_GET['adm_del']) && $_GET['adm_del'] == "1"){
		add_message("fail", "Domain could not be deleted because admin account would be affected.");
	}	
?>


<h1>Domains</h1>

<?php output_messages(); ?>

<p>
Add or delete domains.
</p>



<?php 
	$sql = "SELECT * FROM `".DBT_DOMAINS."` ORDER BY `".DBC_DOMAINS_DOMAIN."` ASC;";
	
	if(!$result = $db->query($sql)){
		die('There was an error running the query [' . $db->error . ']');
	}
?>

<p>
	<a class="button button-small" href="<?php echo FRONTEND_BASE_PATH; ?>admin/createdomain/">Create new domain</a>
</p>

<table class="list">
<tr class="head"><th>Domain</th> <th></th><tr>

<?php 
	while($row = $result->fetch_assoc()){
		echo "<tr><td>".strip_tags($row[DBC_DOMAINS_DOMAIN])."</td> <td><a href=\"".FRONTEND_BASE_PATH."admin/deletedomain/?id=".$row[DBC_DOMAINS_ID]."\">[Delete]</a></td> </tr>";
	}
?>
</table>