<?php

if(!isset($_GET['id'])){
	// Redirect id not set, redirect to overview
	Router::redirect("admin/listredirects");
}

$id = $_GET['id'];

/** @var User $user */
$user = User::find($id);

if(is_null($user)){
	// User doesn't exist, redirect to overview
	Router::redirect("admin/listusers");
}

if(!$user->isInLimitedDomains()){
	Router::redirect("admin/listusers/?missing-permission=1");
}

// Delete user
if(isset($_POST['confirm'])){
	$confirm = $_POST['confirm'];

	if($confirm === "yes"){
		// Check if admin is affected
		if(!in_array($user->getEmail(), $admins)){

			$user->delete();

			// Delete user successful, redirect to overview
			Router::redirect("admin/listusers/?deleted=1");
		}
		else{
			// Admin tried to delete himself, redirect to overview
			Router::redirect("admin/listusers/?adm_del=1");
		}
	}
	else{
		// Choose to not delete user, redirect to overview
		Router::redirect("admin/listusers");
	}
}

?>

<h1>Delete user "<?php echo $user->getEmail() ?>"?</h1>

<div class="buttons">
	<a class="button" href="<?php echo Router::url('admin/listusers'); ?>">&#10092; Back to user list</a>
</div>

<form class="form" action="" method="post" autocomplete="off">
	<div class="input-group">
		<label>The user's mailbox will be deleted from the database only!</label>
		<div class="input-info">The mailbox in the filesystem won't be affected.</div>
	</div>

	<div class="input-group">
		<label for="confirm">Do you realy want to delete this user?</label>
		<div class="input">
			<label>
				<select name="confirm" autofocus required>
					<option value="no">No!</option>
					<option value="yes">Yes!</option>
				</select>
			</label>
		</div>
	</div>

	<div class="buttons">
		<button type="submit" class="button button-primary">Delete</button>
	</div>
</form>