<?php 

if(isset($_POST['domain'])){
	$domain = $db->escape_string($_POST['domain']);
	
	if($domain !== ""){
		// Check if domain exists in database
		$domain_exists = $db->query("SELECT `".DBC_DOMAINS_DOMAIN."` FROM `".DBT_DOMAINS."` WHERE `".DBC_DOMAINS_DOMAIN."` = '$domain';");
		if($domain_exists->num_rows == 0){
			$sql = "INSERT INTO `".DBT_DOMAINS."` (`".DBC_DOMAINS_DOMAIN."`) VALUES ('$domain');";
				
			if(!$result = $db->query($sql)){
				die('There was an error running the query [' . $db->error . ']');
			}
			else{
				header("Location: ".FRONTEND_BASE_PATH."admin/listdomains/?created=1");
			}
		}
		else{
			add_message("fail", "Domain already exists in database.");
		}
	}
	else{
		add_message("fail", "Empty domain could not be created.");
	}
}

?>

<h1>Create new domain</h1>

<?php output_messages(); ?>

<p>
	<a class="button button-small" href="<?php echo FRONTEND_BASE_PATH; ?>admin/listdomains/">&#10092; Back to domain list</a>
</p>

<form action="" method="post">
	<p><input name="domain" class="textinput" type="text" placeholder="domain.tld" autofocus/></p>
	<p><input type="submit" class="button button-small" value="Create domain"/>
</form>