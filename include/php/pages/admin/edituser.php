<?php

$mailboxLimitDefault = User::getMailboxLimitDefault();

$saveMode = (isset($_POST['savemode']) && in_array($_POST['savemode'], array('edit', 'create')))
	? $_POST['savemode']
	: null;

if(!is_null($saveMode)){

	$inputPassword = isset($_POST['password']) ? $_POST['password'] : null;
	$inputPasswordRepeated = isset($_POST['password_repeat']) ? $_POST['password_repeat'] : null;

	$inputMailboxLimit = null;
	if(Config::get('options.enable_mailbox_limits', false)){
		$inputMailboxLimit = isset($_POST['mailbox_limit']) ? intval($_POST['mailbox_limit']) : $mailboxLimitDefault;
		if(!$inputMailboxLimit === 0 && empty($inputMailboxLimit)){
			$inputMailboxLimit = $mailboxLimitDefault;
		}
	}

	if($saveMode === 'edit'){
		// Edit mode entered

		if(!isset($_POST['id'])){
			// User id not set, redirect to overview
			Router::redirect("admin/listusers");
		}

		$inputId = $_POST['id'];

		/** @var User $userToEdit */
		$userToEdit = User::find($inputId);

		if(is_null($userToEdit)){
			// User doesn't exist, redirect to overview
			Router::redirect("admin/listusers");
		}

		if(!$userToEdit->isInLimitedDomains()){
			Router::redirect("admin/listusers/?missing-permission=1");
		}

		if(Config::get('options.enable_mailbox_limits', false) && !is_null($inputMailboxLimit)){
			$userToEdit->setMailboxLimit($inputMailboxLimit);
		}

		$passwordError = false;

		// Is there a changed password?
		if(!empty($inputPassword) || !empty($inputPasswordRepeated)){
			try{
				$userToEdit->changePassword($inputPassword, $inputPasswordRepeated);
			}
			catch(Exception $passwordInvalidException){
				Message::getInstance()->fail($passwordInvalidException->getMessage());
				$passwordError = true;
			}
		}

		$userToEdit->save();

		if(!$passwordError){
			// Edit user successfull, redirect to overview
			Router::redirect("admin/listusers/?edited=1");
		}
	}

	else if($saveMode === 'create'){
		// Create mode entered

		$inputUsername = isset($_POST['username']) ? $_POST['username'] : null;
		$inputDomain = isset($_POST['domain']) ? $_POST['domain'] : null;

		if(!empty($inputUsername)
			&& !empty($inputDomain)
			&& (!empty($inputPassword) || !empty($inputPasswordRepeated))
		){

			/** @var Domain $selectedDomain */
			$selectedDomain = Domain::findWhereFirst(
				array(Domain::attr('domain'), $inputDomain)
			);

			if(!is_null($selectedDomain)){

				if(!$selectedDomain->isInLimitedDomains()){
					Router::redirect("admin/listusers/?missing-permission=1");
				}

				/** @var User $user */
				$user = User::findWhereFirst(
					array(
						array(User::attr('username'), $inputUsername),
						array(User::attr('domain'), $selectedDomain->getDomain()),
					)
				);

				// Check if user already exists
				if(is_null($user)){
					try{
						// Check password then go on an insert user first
						Auth::validateNewPassword($inputPassword, $inputPasswordRepeated);

						$data = array(
							User::attr('username') => $inputUsername,
							User::attr('domain') => $selectedDomain->getDomain(),
							User::attr('password_hash') => Auth::generatePasswordHash($inputPassword)
						);

						if(Config::get('options.enable_mailbox_limits', false) && !is_null($inputMailboxLimit)){
							$data[User::attr('mailbox_limit')] = $inputMailboxLimit;
						}

						/** @var User $user */
						$user = User::createAndSave($data);

						// Redirect user to user list
						Router::redirect("admin/listusers/?created=1");
					}
					catch(Exception $passwordInvalidException){
						Message::getInstance()->fail($passwordInvalidException->getMessage());
					}
				}
				else{
					Message::getInstance()->fail("User already exists in database.");
				}
			}
			else{
				Message::getInstance()->fail("The selected domain doesn't exist.");
			}
		}
		else{
			var_dump($_POST);
			// Fields missing
			Message::getInstance()->fail("Not all fields were filled out.");
		}
	}
}

// Select mode
$mode = "create";
if(isset($_GET['id'])){
	$mode = "edit";
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
}

?>

<h1><?php echo ($mode === "create") ? "Create User" : "Edit user \"{$user->getEmail()}\""; ?></h1>

<div class="buttons">
	<a class="button" href="<?php echo Router::url('admin/listusers'); ?>">&#10092; Back to user list</a>
</div>

<form class="form" action="" method="post" autocomplete="off">
	<input type="hidden" name="savemode" value="<?php echo $mode; ?>"/>
	<?php if($mode === "edit"): ?>
		<input type="hidden" name="id" value="<?php echo $user->getId(); ?>"/>
	<?php endif; ?>

	<?php echo Message::getInstance()->render(); ?>

	<?php if($mode === "edit"): ?>
		<div class="input-group">
			<label>Username and Group cannot be edited</label>
			<div class="input-info">To rename or move a mailbox, you have to move in the filesystem first and create a new user here after.</div>
		</div>
	<?php else:
		/** @var ModelCollection $domains */
		$domains = Domain::getByLimitedDomains();
		?>
		<div class="input-group">
			<label for="username">Username</label>
			<div class="input">
				<input type="text" name="username" placeholder="Username" value="<?php echo isset($_POST['username']) ? strip_tags($_POST['username']) : ''; ?>" autofocus required/>
			</div>
		</div>

		<div class="input-group">
			<label for="domain">Domain</label>
			<div class="input">
				<select name="domain" required>
					<option value="">-- Select a domain --</option>
					<?php foreach($domains as $domain): /** @var Domain $domain */ ?>
						<option value="<?php echo $domain->getDomain(); ?>" <?php echo ((isset($_POST['domain']) && $_POST['domain'] === $domain->getDomain()) || ($mode === "create" && $domains->count() === 1)) ? 'selected' : ''; ?>>
							<?php echo $domain->getDomain(); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
	<?php endif; ?>

	<div class="input-group">
		<label for="password">Password</label>
		<?php if(Config::has('password.min_length')): ?>
			<div class="input-info">The new password must be at least <?php echo Config::get('password.min_length'); ?> characters long.</div>
		<?php endif; ?>
		<div class="input input-action">
			<input type="password" name="password" placeholder="New password" <?php echo ($mode === "create") ? 'required' : ''; ?> minlength="<?php echo Config::get('password.min_length', 0); ?>"/>
			<button type="button" class="button" onclick="pass=generatePassword();this.form.password.value=pass;this.form.password_repeat.value=pass;this.form.password.type='text';this.form.password_repeat.type='text'">Generate password</button>
		</div>
		<div class="input">
			<input type="password" name="password_repeat" placeholder="Repeat password" <?php echo ($mode === "create") ? 'required' : ''; ?> minlength="<?php echo Config::get('password.min_length', 0); ?>"/>
		</div>
	</div>

	<?php if(Config::get('options.enable_mailbox_limits', false)): ?>
		<div class="input-group">
			<label>Mailbox limit</label>
			<div class="input-info">The default limit is <?php echo $mailboxLimitDefault; ?> MB. Limit set to 0 means no limit in size.</div>
			<div class="input input-labeled input-labeled-right">
				<input name="mailbox_limit" type="number" value="<?php echo isset($_POST['mailbox_limit']) ? strip_tags($_POST['mailbox_limit']) : ((isset($user) && Config::get('options.enable_mailbox_limits', false)) ? $user->getMailboxLimit() : $mailboxLimitDefault); ?>" placeholder="Mailbox limit in MB" min="0" required/>
				<span class="input-label">MB</span>
			</div>
		</div>
	<?php endif; ?>

	<div class="buttons">
		<button type="submit" class="button button-primary">Save settings</button>
	</div>
</form>
