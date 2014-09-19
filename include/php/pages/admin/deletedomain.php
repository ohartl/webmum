<?php 

$id = $db->escape_string($_GET['id']);

if(isset($_POST['confirm'])){
	$confirm = $_POST['confirm'];
	
	if($confirm === "yes"){
		$sql = "SELECT `".DBC_DOMAINS_DOMAIN."` FROM `".DBT_DOMAINS."` WHERE `".DBC_DOMAINS_ID."` = '$id' LIMIT 1;";
			
		if(!$result = $db->query($sql)){
			die('There was an error running the query [' . $db->error . ']');
		}
		
		else{	
			while($row = $result->fetch_assoc()){
				$domain = $row[DBC_DOMAINS_DOMAIN];
			}
			
			$sql = "DELETE FROM `".DBT_DOMAINS."` WHERE `".DBC_DOMAINS_ID."` = '$id'";
				
			if(!$result = $db->query($sql)){
				die('There was an error running the query [' . $db->error . ']');
			}
			
			else{
				$sql = "DELETE FROM `".DBT_USERS."` WHERE `".DBC_USERS_DOMAIN."` = '$domain'";
					
				if(!$result = $db->query($sql)){
					die('There was an error running the query [' . $db->error . ']');
				}
				else{
					header("Location: ".FRONTEND_BASE_PATH."admin/listdomains/?deleted=1");
				}
			}
		}
	}
	
	else{
		header("Location: ".FRONTEND_BASE_PATH."admin/listdomains/");
	}
}

else{
	//Load user data from DB
	$sql = "SELECT `".DBC_DOMAINS_DOMAIN."` FROM `".DBT_DOMAINS."` WHERE `".DBC_DOMAINS_ID."` = '$id' LIMIT 1;";
	
	if(!$result = $db->query($sql)){
		die('There was an error running the query [' . $db->error . ']');
	}
	
	while($row = $result->fetch_assoc()){
		$domain = $row[DBC_DOMAINS_DOMAIN];
	}
}
?>

<h1>Delete domain "<?php echo $domain ?>"?</h1>

<p>
	<strong>All mailboxes matching the domain will be deleted from the user database!</strong><br>
	Mailbox directories in the filesystem won't be affected.
</p>

<form action="" method="post">
	<select name="confirm">
		<option value="no">No!</option>
		<option value="yes">Yes!</option>
	</select>
	
	<input type="submit" class="button button-small" value="Okay"/>
</form>