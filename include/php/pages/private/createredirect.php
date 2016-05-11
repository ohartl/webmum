<?php

if(!Config::get('options.enable_user_redirects', false)
	|| !Auth::getUser()->canCreateUserRedirects()
){
	Router::redirect('private/redirects');
}

if(isset($_POST['source'])){

	$destination = Auth::getUser()->getEmail();
	$domain = Auth::getUser()->getDomain();

	$inputSources = stringToEmails($_POST['source']);

	// validate emails
	$emailErrors = array();

	// basic email validation isn't working 100% correct though
	foreach($inputSources as $email){
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
			if($emailParts[1] != $domain){
				$emailErrors[$email] = "Domain of source address \"{$email}\" must be \"{$domain}\".";
			}
		}
	}

	// validate no redirect loops
	if(in_array($destination, $inputSources)){
		$emailErrors[$destination] = "Address \"{$destination}\" cannot be in source and destination in same redirect.";
	}


	if(count($emailErrors) > 0){
		Message::getInstance()->fail(implode("<br>", $emailErrors));
	}
	elseif(count($inputSources) !== 1){
		Message::getInstance()->fail("Only one email address as source.");
	}
	else{
		if(count($inputSources) > 0){

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
				foreach($inputSources as $inputSource){
					$data = array(
						AbstractRedirect::attr('source') => $inputSource,
						AbstractRedirect::attr('destination') => $destination,
						AbstractRedirect::attr('multi_hash') => null,
						AbstractRedirect::attr('is_created_by_user') => true,
					);

					$a = Alias::createAndSave($data);
				}

				// Redirect created, redirect to overview
				Router::redirect('private/redirects');
			}
		}
		else{
			Message::getInstance()->fail("Redirect couldn't be created. Fill out all fields.");
		}
	}
}


$domains = Domain::getByLimitedDomains();
?>

	<h1>Create Redirect</h1>
	
	<div class="buttons">
		<a class="button" href="<?php echo Router::url('private/redirects'); ?>">&#10092; Back to your redirects</a>
	</div>
	
	<?php echo Message::getInstance()->render(); ?>
	
	<form class="form" action="" method="post" autocomplete="off">
	
		<div class="input-group">
			<label for="source">Source</label>
			<div class="input-info">
				<?php if($domains->count() > 0): ?>
					You can only create redirects with this domain:
					<ul>
						<li><?php echo Auth::getUser()->getDomain(); ?></li>
					</ul>
				<?php else: ?>
					There are no domains managed by WebMUM yet.
				<?php endif; ?>
			</div>
			<div class="input">
				<input type="text" name="source" placeholder="Source address" required autofocus value="<?php echo formatEmailsForm(isset($_POST['source']) ? $_POST['source'] : ''); ?>"/>
			</div>
		</div>
	
		<div class="input-group">
			<label for="destination">Destination</label>
			<div class="input">
				<?php echo formatEmailsText(Auth::getUser()->getEmail()); ?>
			</div>
		</div>
	
		<div class="buttons">
			<button type="submit" class="button button-primary">Create redirect</button>
		</div>
	</form>