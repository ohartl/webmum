<?php

$id = null;
$redirect = null;

if(isset($_GET['id'])){
	$id = $_GET['id'];

	/** @var AbstractRedirect $redirect */
	$redirect = AbstractRedirect::findMulti($id);

	if(is_null($redirect)){
		// Redirect does not exist, redirect to overview
		redirect("admin/listredirects");
	}
}

if(isset($_POST['savemode'])){
	$savemode = $_POST['savemode'];

	$inputSources = stringToEmails($_POST['source']);
	$inputDestinations = stringToEmails($_POST['destination']);

	// validate emails
	$emailErrors = array();

	// basic email validation is not working 100% correct though
	foreach(array_merge($inputSources, $inputDestinations) as $email){
		if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
			$emailErrors[$email] = "Address \"{$email}\" is not a valid email address.";
		}
	}

	// validate source emails are on domains
	if(defined('VALIDATE_ALIASES_SOURCE_DOMAIN_ENABLED')){
		$domains = Domain::findAll();

		foreach($inputSources as $email){
			if(isset($emailErrors[$email])){
				continue;
			}

			$emailParts = explode('@', $email);
			$searchResult = $domains->search(
				function($domain) use ($emailParts){
					/** @var Domain $domain */
					return $domain->getDomain() === $emailParts[1];
				}
			);

			if(is_null($searchResult)){
				$emailErrors[$email] = "Domain of source address \"{$email}\" not in domains.";
			}
		}
	}

	// validate no redirect loops
	foreach(array_intersect($inputSources, $inputDestinations) as $email){
		$emailErrors[$email] = "Address \"{$email}\" cannot be in source and destination in same redirect.";
	}


	if(count($emailErrors) > 0){
		add_message("fail", implode("<br>", $emailErrors));
	}
	else{
		if(count($emailErrors) === 0 && $savemode === "edit" && !is_null($redirect)){

			if(count($inputSources) > 0 && count($inputDestinations) > 0){

				if(defined('DBC_ALIASES_MULTI_SOURCE') && $redirect instanceof AbstractMultiRedirect){
					$existingRedirectsToEdit = AbstractRedirect::findWhere(
						array(DBC_ALIASES_MULTI_SOURCE, $redirect->getMultiHash())
					);
				}
				else{
					$existingRedirectsToEdit = AbstractRedirect::findWhere(
						array(DBC_ALIASES_ID, $redirect->getId())
					);
				}

				$emailsToCheck = $inputSources;
				foreach($existingRedirectsToEdit as $r) {
					$key = array_search($r->getSource(), $emailsToCheck);
					if($key !== false) {
						unset($emailsToCheck[$key]);
					}
				}

				if (count($emailsToCheck) > 0) {
					$existingRedirectsOther = AbstractRedirect::findWhere(
						array(
							array(DBC_ALIASES_SOURCE, 'IN', $emailsToCheck)
						)
					);
				}
				else {
					$existingRedirectsOther = null;
				}

				if(!is_null($existingRedirectsOther) && $existingRedirectsOther->count() > 0){
					$errorMessages = array();
					/** @var AbstractRedirect $existingRedirect */
					foreach($existingRedirectsOther as $id => $existingRedirect){
						if(!$existingRedirectsToEdit->has($id)){
							$errorMessages[] = "Source address \"{$existingRedirect->getSource()}\" is already redirected to some destination.";
						}
					}

					add_message("fail", implode("<br>", $errorMessages));
				}
				else{
					// multi source handling
					$hash = (count($inputSources) === 1) ? null : md5(emailsToString($inputSources));

					foreach($inputSources as $sourceAddress){
						$sourceAddress = formatEmail($sourceAddress);

						/** @var AbstractRedirect $thisRedirect */
						$thisRedirect = $existingRedirectsToEdit->search(
							function($model) use ($sourceAddress){
								/** @var AbstractRedirect $model */
								return $model->getSource() === $sourceAddress;
							}
						);

						if(!is_null($thisRedirect)){
							// edit existing source

							$thisRedirect->setSource($sourceAddress);
							$thisRedirect->setDestination($inputDestinations);
							$thisRedirect->setMultiHash($hash);
							$thisRedirect->save();

							$existingRedirectsToEdit->delete($thisRedirect->getId()); // mark updated
						}
						else{
							$data = array(
								DBC_ALIASES_SOURCE => $sourceAddress,
								DBC_ALIASES_DESTINATION => emailsToString($inputDestinations),
							);
							if(defined('DBC_ALIASES_MULTI_SOURCE')){
								$data[DBC_ALIASES_MULTI_SOURCE] = $hash;
							}

							AbstractRedirect::createAndSave($data);
						}
					}

					// Delete none updated redirect
					foreach($existingRedirectsToEdit as $redirect){
						$redirect->delete();
					}

					// Edit successfull, redirect to overview
					redirect("admin/listredirects/?edited=1");
				}
			}
			else{
				add_message("fail", "Redirect could not be edited. Fill out all fields.");
			}
		}

		else if(count($emailErrors) === 0 && $savemode === "create"){
			if(count($inputSources) > 0 && count($inputDestinations) > 0){

				$existingRedirects = AbstractRedirect::findWhere(
					array(DBC_ALIASES_SOURCE, 'IN', $inputSources)
				);

				if($existingRedirects->count() > 0){
					$errorMessages = array();
					/** @var AbstractRedirect $existingRedirect */
					foreach($existingRedirects as $existingRedirect){
						$errorMessages[] = "Source address \"{$existingRedirect->getSource()}\" is already redirected to some destination.";
					}

					add_message("fail", implode("<br>", $errorMessages));
				}
				else{
					$inputDestination = emailsToString($inputDestinations);

					if(defined('DBC_ALIASES_MULTI_SOURCE') && count($inputSources) > 1){
						$hash = md5(emailsToString($inputSources));
					}
					else{
						$hash = null;
					}

					foreach($inputSources as $inputSource){
						$data = array(
							DBC_ALIASES_SOURCE => $inputSource,
							DBC_ALIASES_DESTINATION => $inputDestination,
						);

						if(defined('DBC_ALIASES_MULTI_SOURCE')){
							$data[DBC_ALIASES_MULTI_SOURCE] = $hash;
						}

						$a = AbstractRedirect::createAndSave($data);
					}

					// Redirect created, redirect to overview
					redirect("admin/listredirects/?created=1");
				}
			}
			else{
				add_message("fail", "Redirect could not be created. Fill out all fields.");
			}
		}
	}
}


// Select mode
$mode = "create";
if(isset($_GET['id'])){
	$mode = "edit";
}
?>

<h1><?php echo ($mode === "create") ? 'Create' : 'Edit'; ?> Redirect</h1>

<div class="buttons">
	<a class="button" href="<?php echo url('admin/listredirects'); ?>">&#10092; Back to redirects list</a>
</div>

<?php output_messages(); ?>

<form class="form" action="" method="post" autocomplete="off">
	<input name="savemode" type="hidden" value="<?php echo $mode; ?>"/>

	<div class="input-group">
		<div class="input-info">Enter single or multiple addresses separated by comma, semicolon or newline.</div>
	</div>

	<div class="input-group">
		<label for="source">Source</label>
		<div class="input">
			<?php if(defined('DBC_ALIASES_MULTI_SOURCE')): ?>
				<textarea name="source" placeholder="Source" required autofocus><?php echo formatEmails(isset($_POST['source']) ? strip_tags($_POST['source']) : (is_null($redirect) ? '' : $redirect->getSource()), FRONTEND_EMAIL_SEPARATOR_FORM); ?></textarea>
			<?php else: ?>
				<input type="text" name="source" placeholder="Source (single address)" required autofocus value="<?php echo strip_tags(formatEmails(isset($_POST['source']) ? $_POST['source'] : (is_null($redirect) ? '' : $redirect->getSource()), FRONTEND_EMAIL_SEPARATOR_FORM)); ?>"/>
			<?php endif; ?>
		</div>
	</div>

	<div class="input-group">
		<label for="destination">Destination</label>
		<div class="input">
			<textarea name="destination" placeholder="Destination" required><?php echo formatEmails(isset($_POST['destination']) ? strip_tags($_POST['destination']) : (is_null($redirect) ? '' : $redirect->getDestination()), FRONTEND_EMAIL_SEPARATOR_FORM); ?></textarea>
		</div>
	</div>

	<div class="buttons">
		<button type="submit" class="button button-primary">Save settings</button>
	</div>
</form>