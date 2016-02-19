<?php 

if(isset($_POST['domain'])){
	$inputDomain = $_POST['domain'];

	if(!empty($inputDomain)){

		$existingDomain = Domain::findWhere(array(DBC_DOMAINS_DOMAIN, $inputDomain));

		if(!is_null($existingDomain)){

			Domain::createAndSave(
				array(
					DBC_DOMAINS_DOMAIN => $inputDomain,
				)
			);

			// Created domain successfull, redirect to overview
			redirect("admin/listdomains/?created=1");
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