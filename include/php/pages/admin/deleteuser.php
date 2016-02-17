<?php 

$id = $db->escape_string($_GET['id']);

//Load user data from DB
$sql = "SELECT `".DBC_USERS_USERNAME."`, `".DBC_USERS_DOMAIN."` FROM `".DBT_USERS."` WHERE `".DBC_USERS_ID."` = '$id' LIMIT 1;";

if(!$result = $db->query($sql)){
	dbError($db->error);
}

$row = $result->fetch_assoc();

$username = $row[DBC_USERS_USERNAME];
$domain = $row[DBC_USERS_DOMAIN];

$mailAddress = $username."@".$domain;

// Delete user
if(isset($_POST['confirm'])){
	$confirm = $_POST['confirm'];
	
	if($confirm === "yes"){
		// Check if admin is affected
		if (!in_array($mailAddress, $admins)) {
			$sql = "DELETE FROM `".DBT_USERS."` WHERE `".DBC_USERS_ID."` = '$id'";
				
			if(!$result = $db->query($sql)){
				dbError($db->error);
			}
			else{
				// Delete user successfull, redirect to overview
				redirect("admin/listusers/?deleted=1");
			}
		}
		else{
			// Admin tried to delete himself, redirect to overview
			redirect("admin/listusers/?adm_del=1");
		}
	}
	else{
		// Choose to not delete user, redirect to overview
		redirect("admin/listusers");
	}
}

?>

<h1>Delete user "<?php echo strip_tags($mailAddress) ?>"?</h1>

<div class="buttons">
	<a class="button" href="<?php echo url('admin/listusers'); ?>">&#10092; Back to user list</a>
</div>

<form class="form" action="" method="post">
	<div class="input-group">
		<label>The user's mailbox will be deleted from the database only!</label>
		<div class="input-info">The mailbox in the filesystem won't be affected.</div>
	</div>

	<div class="input-group">
		<label>Do you realy want to delete this user?</label>
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