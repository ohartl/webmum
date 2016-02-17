<?php 

if(isset($_POST['domain'])){
	$domain = $db->escape_string($_POST['domain']);
	$domain = strtolower($domain);
	
	if($domain !== ""){
		// Check if domain exists in database
		$domain_exists = $db->query("SELECT `".DBC_DOMAINS_DOMAIN."` FROM `".DBT_DOMAINS."` WHERE `".DBC_DOMAINS_DOMAIN."` = '$domain';");
		if($domain_exists->num_rows == 0){
			$sql = "INSERT INTO `".DBT_DOMAINS."` (`".DBC_DOMAINS_DOMAIN."`) VALUES ('$domain');";
				
			if(!$result = $db->query($sql)){
				dbError($db->error);
			}
			else{
				// Created domain successfull, redirect to overview
				redirect("admin/listdomains/?created=1");
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

<div class="buttons">
	<a class="button" href="<?php echo url('admin/listdomains'); ?>">&#10092; Back to domain list</a>
</div>

<form class="form" action="" method="post" autocomplete="off">
	<div class="input-group">
		<label>Domain</label>
		<div class="input">
			<input type="text" name="domain" placeholder="domain.tld" autofocus required/>
		</div>
	</div>

	<div class="buttons">
		<button type="submit" class="button button-primary">Create domain</button>
	</div>
</form>