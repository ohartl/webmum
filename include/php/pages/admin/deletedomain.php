<?php

if(Auth::getUser()->isDomainLimited()){
	Router::displayError(403);
}

if(!isset($_GET['id'])){
	// Domain id not set, redirect to overview
	Router::redirect("admin/listdomains");
}

$id = $_GET['id'];

/** @var Domain $domain */
$domain = Domain::find($id);

if(is_null($domain)){
	// Domain doesn't exist, redirect to overview
	Router::redirect("admin/listdomains");
}

if(!$domain->isInLimitedDomains()){
	Router::redirect("admin/listdomains/?missing-permission=1");
}

// Delete domain
if(isset($_POST['confirm'])){
	$confirm = $_POST['confirm'];

	if($confirm === "yes"){

		// Check if admin domain is affected
		$isAdminDomain = false;
		foreach(Config::get('admins', array()) as $admin){
			$parts = explode("@", $admin);
			if(count($parts) === 2 && $parts[2] === $domain->getDomain()){
				$isAdminDomain = true;
				break;
			}
		}

		if(!$isAdminDomain){

			$users = User::findWhere(array(User::attr('domain'), $domain->getDomain()));

			/** @var User $user */
			foreach($users as $user){
				$user->delete();
			}

			$domain->delete();

			// Delete domain successfull, redirect to overview
			Router::redirect("admin/listdomains/?deleted=1");
		}
		else{
			// Cannot delete domain with admin emails, redirect to overview
			Router::redirect("admin/listdomains/?adm_del=1");
		}
	}
	
	else{
		// Choose to not delete domain, redirect to overview
		Router::redirect("admin/listdomains");
	}
}
?>

<h1>Delete domain "<?php echo $domain->getDomain() ?>"?</h1>

<div class="buttons">
	<a class="button" href="<?php echo Router::url('admin/listdomains'); ?>">&#10092; Back to domain list</a>
</div>

<form class="form" action="" method="post" autocomplete="off">
	<div class="input-group">
		<label>All mailboxes matching the domain will be deleted from the user database!</label>
		<div class="input-info">Mailbox directories in the filesystem won't be affected.</div>
	</div>

	<div class="input-group">
		<label for="confirm">Do you realy want to delete this domain?</label>
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