<?php

if(!isset($_GET['id'])){
	// Domain id not set, redirect to overview
	redirect("admin/listdomains/");
}

$id = $db->escape_string($_GET['id']);

//Load user data from DB
$sql = "SELECT `".DBC_DOMAINS_DOMAIN."` FROM `".DBT_DOMAINS."` WHERE `".DBC_DOMAINS_ID."` = '$id' LIMIT 1;";

if(!$result = $db->query($sql)){
	dbError($db->error);
}

if($result->num_rows !== 1){
	// Domain does not exist, redirect to overview
	redirect("admin/listdomains/");
}

$row = $result->fetch_assoc();
$domain = $row[DBC_DOMAINS_DOMAIN];

// Delete domain
if(isset($_POST['confirm'])){
	$confirm = $_POST['confirm'];
	
	if($confirm === "yes"){

		$admin_domains = array();
		foreach($admins as $admin) {
			$parts = explode("@", $admin);
			$admin_domains[] = $parts[1];
		}

		// Check if admin domain is affected
		if(!in_array($domain, $admin_domains)){
			$sql = "DELETE FROM `".DBT_DOMAINS."` WHERE `".DBC_DOMAINS_ID."` = '$id'";

			if(!$result = $db->query($sql)){
				dbError($db->error);
			}
			else{
				$sql = "DELETE FROM `".DBT_USERS."` WHERE `".DBC_USERS_DOMAIN."` = '$domain'";

				if(!$result = $db->query($sql)){
					dbError($db->error);
				}
				else{
					// Delete domain successfull, redirect to overview
					redirect("admin/listdomains/?deleted=1");
				}
			}
		}
		else{
			// Cannot delete domain with admin emails, redirect to overview
			redirect("admin/listdomains/?adm_del=1");
		}
	}
	
	else{
		// Choose to not delete domain, redirect to overview
		redirect("admin/listdomains/");
	}
}
?>

<h1>Delete domain "<?php echo $domain ?>"?</h1>

<p>
	<strong>All mailboxes matching the domain will be deleted from the user database!</strong><br>
	Mailbox directories in the filesystem won't be affected.
</p>

<form action="" method="post">
	<select name="confirm" autofocus>
		<option value="no">No!</option>
		<option value="yes">Yes!</option>
	</select>
	
	<input type="submit" class="button button-small" value="Okay"/>
</form>