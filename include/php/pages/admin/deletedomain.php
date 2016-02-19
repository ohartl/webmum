<?php

if(!isset($_GET['id'])){
	// Domain id not set, redirect to overview
	redirect("admin/listdomains");
}

$id = $_GET['id'];

/** @var Domain $domain */
$domain = Domain::find($id);

if(is_null($domain)){
	// Domain does not exist, redirect to overview
	redirect("admin/listdomains");
}

// Delete domain
if(isset($_POST['confirm'])){
	$confirm = $_POST['confirm'];

	if($confirm === "yes"){

		// Check if admin domain is affected
		$isAdminDomain = false;
		foreach($admins as $admin){
			$parts = explode("@", $admin);
			if(count($parts) === 2 && $parts[2] === $domain->getDomain()){
				$isAdminDomain = true;
				break;
			}
		}

		if(!$isAdminDomain){

			$users = User::findWhere(array(DBC_USERS_DOMAIN, $domain->getDomain()));

			/** @var User $user */
			foreach($users as $user){
				$user->delete();
			}

			$domain->delete();

			// Delete domain successfull, redirect to overview
			redirect("admin/listdomains/?deleted=1");
		}
		else{
			// Cannot delete domain with admin emails, redirect to overview
			redirect("admin/listdomains/?adm_del=1");
		}
	}
	
	else{
		// Choose to not delete domain, redirect to overview
		redirect("admin/listdomains");
	}
}
?>

<h1>Delete domain "<?php echo $domain->getDomain() ?>"?</h1>

<div class="buttons">
	<a class="button" href="<?php echo url('admin/listdomains'); ?>">&#10092; Back to domain list</a>
</div>

<form class="form" action="" method="post">
	<div class="input-group">
		<label>All mailboxes matching the domain will be deleted from the user database!</label>
		<div class="input-info">Mailbox directories in the filesystem won't be affected.</div>
	</div>

	<div class="input-group">
		<label>Do you realy want to delete this domain?</label>
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