<?php

if(!isset($_GET['id'])){
	// Redirect id not set, redirect to overview
	Router::redirect('admin/listredirects');
}

$id = $_GET['id'];

/** @var User $user */
$user = User::find($id);

if(is_null($user)){
	// User doesn't exist, redirect to overview
	Router::redirect('admin/listusers');
}

if(!$user->isInLimitedDomains()){
	Router::redirect('admin/listusers/?missing-permission=1');
}

// Delete user
if(isset($_POST['confirm'])){
	$confirm = $_POST['confirm'];

	if($confirm === 'yes'){
		// Check if admin is affected
		if(!in_array($user->getEmail(), Config::get('admins', array()))){

			// Delete redirects of this user
			if(isset($_POST['delete_redirects']) && $_POST['delete_redirects'] === 'yes'
				&& isset($_POST['selected_redirects']) && is_array($_POST['selected_redirects'])
			){
				$redirectMultiIds = $_POST['selected_redirects'];

				foreach($redirectMultiIds as $redirectMultiId){
					$redirectIds = explode(',', $redirectMultiId);

					foreach($redirectIds as $redirectId){

						// Note: No Multi* selected, so there is only Alias & Redirect
						$redirects = AbstractRedirect::findWhere(
							array(
								array(AbstractRedirect::attr('id'), $redirectId),
								array(AbstractRedirect::attr('destination'), 'LIKE', '%'.$user->getEmail().'%')
							)
						);

						/** @var AbstractRedirect $redirect */
						foreach($redirects as $redirect){
							if($redirect instanceof Alias) {
								$redirect->delete();
							}
							elseif($redirect instanceof Redirect) {
								$redirect->setDestination(
									array_diff(
										$redirect->getDestination(),
										array($user->getEmail())
									)
								);
								$redirect->save();
							}
						}
					}
				}
			}

			$user->delete();

			// Delete user successful, redirect to overview
			Router::redirect('admin/listusers/?deleted=1');
		}
		else{
			// Admin tried to delete himself, redirect to overview
			Router::redirect('admin/listusers/?adm_del=1');
		}
	}
	else{
		// Choose to not delete user, redirect to overview
		Router::redirect('admin/listusers');
	}
}

$redirects = $user->getAnonymizedRedirects();

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
		<label>Redirects to this user:</label>
	<?php if($redirects->count() > 0): ?>
		<div class="input-info">Do you also want to delete the following redirects to this user?</div>
		<table class="table table-compact">
			<thead>
				<tr>
					<th></th>
					<th>Source</th>
					<th>Destination</th>
				<tr>
			</thead>
			<tbody>
			<?php foreach($redirects as $redirect): /** @var AbstractRedirect $redirect */ ?>
				<tr>
					<td><input type="checkbox" name="selected_redirects[]" value="<?php echo $redirect->getId(); ?>" checked></td>
					<td><?php echo formatEmailsText($redirect->getSource()); ?></td>
					<td><?php echo formatEmailsText($redirect->getDestination()); ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<div class="input">
			<label>
				<select name="delete_redirects" required>
					<option value="no">Don't delete the redirects.</option>
					<option value="yes">Yes, delete the selected redirects!</option>
				</select>
			</label>
		</div>
	<?php else: ?>
		<div class="input-info">There are currently no redirects to this user.</div>
	<?php endif; ?>
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