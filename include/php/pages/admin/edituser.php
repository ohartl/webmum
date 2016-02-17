<?php 
// If mailbox_limit is supported in the MySQL database
$mailbox_limit_default = 0;
if(defined('DBC_USERS_MAILBOXLIMIT')){
	// Get mailbox_limit default value from DB
	$sql = "SELECT DEFAULT(".DBC_USERS_MAILBOXLIMIT.") AS `".DBC_USERS_MAILBOXLIMIT."` FROM `".DBT_USERS."` LIMIT 1;";

	if(!$result = $db->query($sql)){
		dbError($db->error);
	}
	else{
		while($row = $result->fetch_assoc()){
			$mailbox_limit_default = $row[DBC_USERS_MAILBOXLIMIT];
		}
	}
}

$username = isset($_POST['username']) ? $db->escape_string(strtolower($_POST['username'])) : '';
$domain = isset($_POST['domain']) ? $db->escape_string(strtolower($_POST['domain'])) : '';

if(isset($_POST['savemode'])){
	$savemode = $_POST['savemode'];

	if($savemode === "edit"){
		// Edit mode entered

		if(!isset($_POST['id'])){
			// User id not set, redirect to overview
			redirect("admin/listusers");
		}

		$id = $db->escape_string($_POST['id']);

		$sql = "SELECT `".DBC_USERS_ID."` FROM `".DBT_USERS."` WHERE `".DBC_USERS_ID."` = '$id' LIMIT 1;";
		if(!$resultExists = $db->query($sql)){
			dbError($db->error);
		}

		if($resultExists->num_rows !== 1){
			// User does not exist, redirect to overview
			redirect("admin/listusers");
		}

		if(defined('DBC_USERS_MAILBOXLIMIT')){
			$mailbox_limit = $db->escape_string($_POST['mailbox_limit']);
			if($mailbox_limit == ""){
				$mailbox_limit = $mailbox_limit_default;
			}

			$sql = "UPDATE `".DBT_USERS."` SET `".DBC_USERS_MAILBOXLIMIT."` = '$mailbox_limit' WHERE `".DBC_USERS_ID."` = '$id';";
			if(!$result = $db->query($sql)){
				dbError($db->error);
			}
		}

		// Is there a changed password?
		if($_POST['password'] !== ""){
			$pass_ok = check_new_pass($_POST['password'], $_POST['password_repeat']);
			if($pass_ok === true){
				// Password is okay and can be set
				$pass_hash = gen_pass_hash($_POST['password']);
				write_pass_hash_to_db($pass_hash, $id);

				// Edit user password successfull, redirect to overview
				redirect("admin/listusers/?edited=1");
			}
			else{
				// Password is not okay
				// $editsuccessful = 2;
				add_message("fail", $PASS_ERR_MSG);
			}
		}
		else{
			// Edit user successfull, redirect to overview
			redirect("admin/listusers/?edited=1");
		}
	}

	else if($savemode === "create"){
		// Create mode entered

		if(defined('DBC_USERS_MAILBOXLIMIT')){
			$mailbox_limit = $db->escape_string($_POST['mailbox_limit']);
		}
		else{
			// make mailbox_limit dummy for "if"
			$mailbox_limit = 0;
		}

		$pass = $_POST['password'];
		$pass_rep = $_POST['password_repeat'];

		if(!empty($username) && !empty($domain) && !empty($mailbox_limit)){
			// Check if user already exists
			$user_exists = $db->query("SELECT `".DBC_USERS_USERNAME."`, `".DBC_USERS_DOMAIN."` FROM `".DBT_USERS."` WHERE `".DBC_USERS_USERNAME."` = '$username' AND `".DBC_USERS_DOMAIN."` = '$domain';");
			if($user_exists->num_rows == 0){
				// All fields filled with content
				// Check passwords
				$pass_ok = check_new_pass($pass, $pass_rep);
				if($pass_ok === true){
					// Password is okay ... continue
					$pass_hash = gen_pass_hash($pass);

					// Differ between version with mailbox_limit and version without
					if(defined('DBC_USERS_MAILBOXLIMIT')){
						$sql = "INSERT INTO `".DBT_USERS."` (`".DBC_USERS_USERNAME."`, `".DBC_USERS_DOMAIN."`, `".DBC_USERS_PASSWORD."`, `".DBC_USERS_MAILBOXLIMIT."`) VALUES ('$username', '$domain', '$pass_hash', '$mailbox_limit')";
					}
					else{
						$sql = "INSERT INTO `".DBT_USERS."` (`".DBC_USERS_USERNAME."`, `".DBC_USERS_DOMAIN."`, `".DBC_USERS_PASSWORD."`) VALUES ('$username', '$domain', '$pass_hash')";
					}

					if(!$result = $db->query($sql)){
						dbError($db->error);
					}

					// Redirect user to user list
					redirect("admin/listusers/?created=1");
				}
				else{
					// Password not okay
					add_message("fail", $PASS_ERR_MSG);
				}
			}
			else{
				add_message("fail", "User already exists in database.");
			}
		}
		else{
			// Fields missing
			add_message("fail", "Not all fields were filled out.");
		}
	}
}

// Select mode
$mode = "create";
if(isset($_GET['id'])){
	$mode = "edit";
	$id = $db->escape_string($_GET['id']);

	//Load user data from DB
	$sql = "SELECT * from `".DBT_USERS."` WHERE `".DBC_USERS_ID."` = '$id' LIMIT 1;";

	if(!$result = $db->query($sql)){
		dbError($db->error);
	}

	if($result->num_rows !== 1){
		// User does not exist, redirect to overview
		redirect("admin/listusers");
	}

	$row = $result->fetch_assoc();

	$username = $row[DBC_USERS_USERNAME];
	$domain = $row[DBC_USERS_DOMAIN];
	if(defined('DBC_USERS_MAILBOXLIMIT')){
		$mailbox_limit = $row[DBC_USERS_MAILBOXLIMIT];
	}
}

//Load user data from DB
$sql = "SELECT `".DBC_DOMAINS_DOMAIN."` FROM `".DBT_DOMAINS."`;";

if(!$resultDomains = $db->query($sql)){
	dbError($db->error);
}

?>

<h1><?php echo ($mode === "create") ? 'Create User' : 'Edit user "'.$username.'@'.$domain.'"'; ?></h1>

<div class="buttons">
	<a class="button" href="<?php echo url('admin/listusers'); ?>">&#10092; Back to user list</a>
</div>

<form class="form" action="" method="post">
	<input type="hidden" name="savemode" value="<?php echo $mode; ?>"/>
<?php if($mode === "edit" && isset($id)): ?>
	<input type="hidden" name="id" value="<?php echo $id; ?>"/>
<?php endif; ?>

	<?php output_messages(); ?>

<?php if($mode === "edit"): ?>
	<div class="input-group">
		<label>Username and Group cannot be edited</label>
		<div class="input-info">To rename or move a mailbox, you have to move in the filesystem first and create a new user here after.</div>
	</div>
<?php else: ?>
	<div class="input-group">
		<label for="username">Username</label>
		<div class="input">
			<input type="text" name="username" placeholder="Username" value="<?php echo isset($username) ? strip_tags($username) : ''; ?>" autofocus required/>
		</div>
	</div>

	<div class="input-group">
		<label for="domain">Domain</label>
		<div class="input">
			<select name="domain" required>
				<option value="">-- Select a domain --</option>
			<?php while($row = $resultDomains->fetch_assoc()): ?>
				<option value="<?php echo strip_tags($row[DBC_DOMAINS_DOMAIN]); ?>" <?php echo (isset($domain) && $row[DBC_DOMAINS_DOMAIN] == $domain) ? 'selected' : ''; ?>>
					<?php echo strip_tags($row[DBC_DOMAINS_DOMAIN]); ?>
				</option>
			<?php endwhile; ?>
			</select>
		</div>
	</div>
<?php endif; ?>

	<div class="input-group">
		<label for="password">Password</label>
		<div class="input-info">The new password must be at least <?php echo MIN_PASS_LENGTH; ?> characters long.</div>
		<div class="input input-action">
			<input type="password" name="password" placeholder="New password" <?php echo ($mode === "create") ? 'required' : ''; ?> minlength="<?php echo MIN_PASS_LENGTH; ?>"/>
			<button type="button" class="button" onclick="pass=generatePassword();this.form.password.value=pass;this.form.password_repeat.value=pass;this.form.password.type='text';this.form.password_repeat.type='text'">Generate password</button>
		</div>
		<div class="input">
			<input type="password" name="password_repeat" placeholder="Repeat password" <?php echo ($mode === "create") ? 'required' : ''; ?> minlength="<?php echo MIN_PASS_LENGTH; ?>"/>
		</div>
	</div>

<?php if(defined('DBC_USERS_MAILBOXLIMIT')): ?>
	<div class="input-group">
		<label>Mailbox limit</label>
		<div class="input-info">The default limit is <?php echo $mailbox_limit_default; ?> MB. Limit set to 0 means no limit in size.</div>
		<div class="input input-labeled input-labeled-right">
			<input name="mailbox_limit" type="number" value="<?php echo strip_tags(isset($mailbox_limit) ? $mailbox_limit : $mailbox_limit_default); ?>" placeholder="Mailbox limit in MB" min="0" required/>
			<span class="input-label">MB</span>
		</div>
	</div>
<?php endif; ?>

	<div class="buttons">
		<button type="submit" class="button button-primary">Save settings</button>
	</div>
</form>
