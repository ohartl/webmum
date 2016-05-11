<?php

$id = null;
$redirect = null;

if(isset($_GET['id'])){
	$id = $_GET['id'];

	/** @var AbstractRedirect $redirect */
	$redirect = AbstractRedirect::findMulti($id);

	if(is_null($redirect)){
		// Redirect doesn't exist, redirect to overview
		Router::redirect("admin/listredirects");
	}

	if(!$redirect->isInLimitedDomains()){
		Router::redirect("admin/listredirects/?missing-permission=1");
	}
}

if(isset($_POST['savemode'])){
	$savemode = $_POST['savemode'];

	$inputSources = stringToEmails($_POST['source']);
	$inputDestinations = stringToEmails($_POST['destination']);

	// validate emails
	$emailErrors = array();

	// basic email validation isn't working 100% correct though
	foreach(array_merge($inputSources, $inputDestinations) as $email){
		if(strpos($email, '@') === false){
			$emailErrors[$email] = "Address \"{$email}\" isn't a valid email address.";
		}
	}

	// validate source emails are on domains
	if(Config::get('options.enable_validate_aliases_source_domain', true)){
		$domains = Domain::getByLimitedDomains();

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
				$emailErrors[$email] = "Domain of source address \"{$email}\" not in your domains.";
			}
		}
	}

	// validate no redirect loops
	foreach(array_intersect($inputSources, $inputDestinations) as $email){
		$emailErrors[$email] = "Address \"{$email}\" cannot be in source and destination in same redirect.";
	}


	if(count($emailErrors) > 0){
		Message::getInstance()->fail(implode("<br>", $emailErrors));
	}
	else{
		if(count($emailErrors) === 0 && $savemode === "edit" && !is_null($redirect)){

			if(count($inputSources) > 0 && count($inputDestinations) > 0){

				if(Config::get('options.enable_multi_source_redirects', false) && $redirect instanceof AbstractMultiRedirect){
					$existingRedirectsToEdit = AbstractRedirect::findWhere(
						array(AbstractRedirect::attr('multi_hash'), $redirect->getMultiHash())
					);
				}
				else{
					$existingRedirectsToEdit = AbstractRedirect::findWhere(
						array(AbstractRedirect::attr('id'), $redirect->getId())
					);
				}

				$emailsToCheck = $inputSources;
				foreach($existingRedirectsToEdit as $r){
					$key = array_search($r->getSource(), $emailsToCheck);
					if($key !== false){
						unset($emailsToCheck[$key]);
					}
				}

				if(count($emailsToCheck) > 0){
					$existingRedirectsOther = AbstractRedirect::findWhere(
						array(
							array(AbstractRedirect::attr('source'), 'IN', $emailsToCheck)
						)
					);
				}
				else{
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

					Message::getInstance()->fail(implode("<br>", $errorMessages));
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
							// Don't set 'isCreatedByUser' here, it will overwrite redirects created by user
							$thisRedirect->save();

							$existingRedirectsToEdit->delete($thisRedirect->getId()); // mark updated
						}
						else{
							$data = array(
								AbstractRedirect::attr('source') => $sourceAddress,
								AbstractRedirect::attr('destination') => emailsToString($inputDestinations),
								AbstractRedirect::attr('multi_hash') => $hash,
								AbstractRedirect::attr('is_created_by_user') => false,
							);

							AbstractRedirect::createAndSave($data);
						}
					}

					// Delete none updated redirect
					foreach($existingRedirectsToEdit as $redirect){
						$redirect->delete();
					}

					// Edit successfull, redirect to overview
					Router::redirect("admin/listredirects/?edited=1");
				}
			}
			else{
				Message::getInstance()->fail("Redirect couldn't be edited. Fill out all fields.");
			}
		}

		else if(count($emailErrors) === 0 && $savemode === "create"){
			if(count($inputSources) > 0 && count($inputDestinations) > 0){

				$existingRedirects = AbstractRedirect::findWhere(
					array(AbstractRedirect::attr('source'), 'IN', $inputSources)
				);

				if($existingRedirects->count() > 0){
					$errorMessages = array();
					/** @var AbstractRedirect $existingRedirect */
					foreach($existingRedirects as $existingRedirect){
						$errorMessages[] = "Source address \"{$existingRedirect->getSource()}\" is already redirected to some destination.";
					}

					Message::getInstance()->fail(implode("<br>", $errorMessages));
				}
				else{
					$inputDestination = emailsToString($inputDestinations);
					$hash = (count($inputSources) === 1) ? null : md5(emailsToString($inputSources));

					foreach($inputSources as $inputSource){
						$data = array(
							AbstractRedirect::attr('source') => $inputSource,
							AbstractRedirect::attr('destination') => $inputDestination,
							AbstractRedirect::attr('multi_hash') => $hash,
							AbstractRedirect::attr('is_created_by_user') => false,
						);

						$a = AbstractRedirect::createAndSave($data);
					}

					// Redirect created, redirect to overview
					Router::redirect("admin/listredirects/?created=1");
				}
			}
			else{
				Message::getInstance()->fail("Redirect couldn't be created. Fill out all fields.");
			}
		}
	}
}


// Select mode
$mode = "create";
if(isset($_GET['id'])){
	$mode = "edit";
}

$domains = Domain::getByLimitedDomains();
?>

	<h1><?php echo ($mode === "create") ? 'Create' : 'Edit'; ?> Redirect</h1>

	<div class="buttons">
		<a class="button" href="<?php echo Router::url('admin/listredirects'); ?>">&#10092; Back to redirects list</a>
	</div>

<div class="notification">
	Please note that mailservers will prefer to deliver mails to redirects over mailboxes.<br>
	So make sure you don't accidentally override a mailbox with a redirect.
</div>

<?php echo Message::getInstance()->render(); ?>

<?php if(Config::get('options.enable_validate_aliases_source_domain', true) && Auth::getUser()->isDomainLimited() && $domains->count() === 0): ?>
	<div class="notification notification-fail">
		You are listed for limited access to domains, but it seems there are no domains listed you can access.
	</div>
<?php else: ?>
	<form class="form" action="" method="post" autocomplete="off">
		<input name="savemode" type="hidden" value="<?php echo $mode; ?>"/>

		<div class="input-group">
			<div class="input-info">Enter single or multiple addresses separated by comma, semicolon or newline.</div>
		</div>

		<div class="input-group">
			<label for="source">Source</label>
			<div class="input-info">
				<?php if($domains->count() > 0): ?>
					<?php if(Auth::getUser()->isDomainLimited()): ?>
						You can create redirects for source addresses from these domains only:
					<?php else: ?>
						You can create redirects for every domain you want,<br>
						but here's a list of domains managed by WebMUM:
					<?php endif; ?>
					<ul>
						<?php foreach($domains as $domain): /** @var Domain $domain */ ?>
							<li><?php echo $domain->getDomain(); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php else: ?>
					There are no domains managed by WebMUM yet.
				<?php endif; ?>
			</div>
			<div class="input">
				<?php if(Config::get('options.enable_multi_source_redirects', false)): ?>
					<textarea name="source" placeholder="Source" required autofocus><?php echo formatEmailsForm(isset($_POST['source']) ? $_POST['source'] : (is_null($redirect) ? '' : $redirect->getSource())); ?></textarea>
				<?php else: ?>
					<input type="text" name="source" placeholder="Source (single address)" required autofocus value="<?php echo formatEmailsForm(isset($_POST['source']) ? $_POST['source'] : (is_null($redirect) ? '' : $redirect->getSource())); ?>"/>
				<?php endif; ?>
			</div>
		</div>

		<div class="input-group">
			<label for="destination">Destination</label>
			<div class="input">
				<textarea name="destination" placeholder="Destination" required><?php echo formatEmailsForm(isset($_POST['destination']) ? $_POST['destination'] : (is_null($redirect) ? '' : $redirect->getDestination())); ?></textarea>
			</div>
		</div>

		<div class="buttons">
			<button type="submit" class="button button-primary">Save settings</button>
		</div>
	</form>
<?php endif; ?>