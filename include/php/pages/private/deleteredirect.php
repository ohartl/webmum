<?php

if(!Config::get('options.enable_user_redirects', false)
	|| !Auth::getUser()->isAllowedToCreateUserRedirects()
){
	Router::redirect('private/redirects');
}

if(!isset($_GET['id'])){
	// Redirect id not set, redirect to overview
	Router::redirect('private/redirects');
}

$id = $_GET['id'];

/** @var AbstractRedirect $redirect */
$redirect = AbstractRedirect::findMultiWhereFirst(
	array(
		array(AbstractRedirect::attr('id'), $id),
		array(AbstractRedirect::attr('is_created_by_user'), true),
		array(AbstractRedirect::attr('destination'), Auth::getUser()->getEmail()),
	)
);

if(is_null($redirect)){
	// Redirect doesn't exist, redirect to overview
	Router::redirect('private/redirects');
}

if(isset($_POST['confirm'])){
	$confirm = $_POST['confirm'];

	if($confirm === "yes"){

		$redirect->delete();

		// Delete redirect successfull, redirect to overview
		Router::redirect('private/redirects/?deleted=1');
	}
	else{
		// Choose to not delete redirect, redirect to overview
		Router::redirect('private/redirects');
	}
}

else{
	?>

	<h1>Delete redirection?</h1>

	<div class="buttons">
		<a class="button" href="<?php echo Router::url('private/redirects'); ?>">&#10092; Back to your redirects</a>
	</div>

	<form class="form" action="" method="post" autocomplete="off">
		<div class="input-group">
			<label>Source</label>
			<div class="input-info"><?php echo formatEmailsText($redirect->getSource()); ?></div>
		</div>

		<div class="input-group">
			<label>Destination</label>
			<div class="input-info"><?php echo formatEmailsText($redirect->getDestination()); ?></div>
		</div>

		<div class="input-group">
			<label for="confirm">Do you realy want to delete this redirection?</label>
			<div class="input">
				<select name="confirm" autofocus required>
					<option value="no">No!</option>
					<option value="yes">Yes!</option>
				</select>
			</div>
		</div>

		<div class="buttons">
			<button type="submit" class="button button-primary">Delete</button>
		</div>
	</form>
	<?php
}
?>