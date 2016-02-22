<?php

if(!isset($_GET['id'])){
	// Redirect id not set, redirect to overview
	redirect("admin/listredirects");
}

$id = $_GET['id'];

/** @var AbstractRedirect $redirect */
$redirect = AbstractRedirect::findMulti($id);

if(is_null($redirect)){
	// Redirect doesn't exist, redirect to overview
	redirect("admin/listredirects");
}

if(!$redirect->isInLimitedDomains()){
	//redirect("admin/listredirects/?missing-permission=1");
}

if(isset($_POST['confirm'])){
	$confirm = $_POST['confirm'];

	if($confirm === "yes"){

		if ($redirect instanceof AbstractMultiRedirect){

			// Get single source rows of multi source redirect/alias instead
			$hash = $redirect->getMultiHash();
			$singleRedirects = AbstractRedirect::findWhere(array(DBC_ALIASES_MULTI_SOURCE, $hash));

			/** @var AbstractRedirect $redirectToDelete */
			foreach($singleRedirects as $redirectToDelete){
				$redirectToDelete->delete();
			}
		}
		else {
			$redirect->delete();
		}

		// Delete redirect successfull, redirect to overview
		redirect("admin/listredirects/?deleted=1");
	}
	else{
		// Choose to not delete redirect, redirect to overview
		redirect("admin/listredirects");
	}
}

else{
	?>

	<h1>Delete redirection?</h1>

	<div class="buttons">
		<a class="button" href="<?php echo url('admin/listredirects'); ?>">&#10092; Back to redirect list</a>
	</div>

	<form class="form" action="" method="post" autocomplete="off">
		<div class="input-group">
			<label>Source</label>
			<div class="input-info"><?php echo formatEmails($redirect->getSource(), str_replace(PHP_EOL, '<br>', FRONTEND_EMAIL_SEPARATOR_TEXT)); ?></div>
		</div>

		<div class="input-group">
			<label>Destination</label>
			<div class="input-info"><?php echo formatEmails($redirect->getDestination(), str_replace(PHP_EOL, '<br>', FRONTEND_EMAIL_SEPARATOR_TEXT)); ?></div>
		</div>

		<div class="input-group">
			<label for="confirm">Do you realy want to delete this redirect?</label>
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