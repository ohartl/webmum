<?php 
	// If mailbox_limit is supported in the MySQL database
	if(defined('DBC_USERS_MAILBOXLIMIT')){
		// Get mailbox_limit default value from DB
		$sql = "SELECT DEFAULT(".DBC_USERS_MAILBOXLIMIT.") AS `".DBC_USERS_MAILBOXLIMIT."` FROM `".DBT_USERS."` LIMIT 1;";
		
		if(!$result = $db->query($sql)){
			die('There was an error running the query [' . $db->error . ']');
		}
		
		else{
			while($row = $result->fetch_assoc()){
				$mailbox_limit_default = $row[DBC_USERS_MAILBOXLIMIT];
			}
		}
	}
	
	
	if(isset($_POST['savemode'])){
		$savemode = $_POST['savemode'];
		
		if($savemode === "edit"){
			// Edit mode entered
			$id = $db->escape_string($_POST['id']);	
			
			if(defined('DBC_USERS_MAILBOXLIMIT')){
				if($mailbox_limit == ""){
					$mailbox_limit = $mailbox_limit_default;
				}	
				$mailbox_limit = $db->escape_string($_POST['mailbox_limit']);
				
				$sql = "UPDATE `".DBT_USERS."` SET `".DBC_USERS_MAILBOXLIMIT."` = '$mailbox_limit' WHERE `".DBC_USERS_ID."` = '$id';";
				if(!$result = $db->query($sql)){
					die('There was an error running the query [' . $db->error . ']');
				}
			}

			// Is there a changed password?
			if($_POST['password'] !== ""){
				$pass_ok = check_new_pass($_POST['password'], $_POST['password_rep']);
				if($pass_ok === true){
					// Password is okay and can be set
					$pass_hash = gen_pass_hash($_POST['password']);
					write_pass_hash_to_db($pass_hash, $id);
					// $editsuccessful = true;
					add_message("success", "User edited successfully.");
					
				}
				else{
					// Password is not okay
					// $editsuccessful = 2;
					add_message("fail", $PASS_ERR_MSG);
				}
			}
			else{
				// Redirect user to user list
				header("Location: ".FRONTEND_BASE_PATH."admin/listusers/?edited=1");
			}				
		}
		
		else if($savemode === "create"){
			// Create mode entered
			$username = $db->escape_string($_POST['username']);
			$username = strtolower($username);
			$domain = $db->escape_string($_POST['domain']);
			if(defined('DBC_USERS_MAILBOXLIMIT')){
				$mailbox_limit = $db->escape_string($_POST['mailbox_limit']);	
			}
			else{
				// make mailbox_limit dummy for "if"
				$mailbox_limit = 0;
			}		
			$pass = $_POST['password'];
			$pass_rep = $_POST['password_rep'];
			
			if($username !== "" && $domain !== "" && $quota !== "" && $mailbox_limit !== ""){
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
							die('There was an error running the query [' . $db->error . ']');
						}
						
						// Redirect user to user list
						header("Location: ".FRONTEND_BASE_PATH."admin/listusers/?created=1");
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
	}
	
	if($mode === "edit"){
		//Load user data from DB
		$sql = "SELECT * from `".DBT_USERS."` WHERE `".DBC_USERS_ID."` = '$id' LIMIT 1;";
		
		if(!$result = $db->query($sql)){
			die('There was an error running the query [' . $db->error . ']');
		}
		
		while($row = $result->fetch_assoc()){
			$username = $row[DBC_USERS_USERNAME];
			$domain = $row[DBC_USERS_DOMAIN];
			if(defined('DBC_USERS_MAILBOXLIMIT')){
				$mailbox_limit = $row[DBC_USERS_MAILBOXLIMIT];
			}
		}
	}
?>



<h1><?php if($mode === "create") { ?> Create <?php } else {?>Edit <?php } ?>User</h1>


<?php output_messages(); ?>


<p>
	<a class="button button-small" href="<?php echo FRONTEND_BASE_PATH; ?>admin/listusers/">&#10092; Back to user list</a>
</p>

<p>
<?php 
	if($mode === "edit"){
		echo "Username and domain cannot be edited.";
	}
?>
</p>

<form action="" method="post">	
	<table>
	<tr> <th>Username</th> <th>Domain</th> <th>Password</th> <?php if(defined('DBC_USERS_MAILBOXLIMIT')){ ?><th>Mailbox limit (in MB)</th> <?php } ?> </tr>
	
	<tr>
		<td>
			<input name="username" class="textinput" type="text" autofocus value="<?php if(isset($username)){echo $username;} ?>" placeholder="Username" required="required"/>
		</td>
		
		<td>
			@ 
			<select name="domain">
				<?php  
				//Load user data from DB
				$sql = "SELECT `".DBC_DOMAINS_DOMAIN."` FROM `".DBT_DOMAINS."`;";
				
				if(!$result = $db->query($sql)){
					die('There was an error running the query [' . $db->error . ']');
				}
				
				while($row = $result->fetch_assoc()){
					$selected = "";
					if(isset($domain) && $row[DBC_DOMAINS_DOMAIN] === $domain){$selected = "selected=\"selected\"";}
					echo "<option value=\"".$row[DBC_DOMAINS_DOMAIN]."\" ".$selected." >".$row[DBC_DOMAINS_DOMAIN]."</option>";
				}
				?>
			</select>
		</td>
		
		<td>
			<input name="password" class="textinput" type="password" placeholder="New password"/></br>
			<input name="password_rep"  class="textinput" type="password" placeholder="New password (repeat)"/>
		</td>
		
		<?php if(defined('DBC_USERS_MAILBOXLIMIT')){ ?>
		<td>
			<input name="mailbox_limit" class="textinput" type="number" value="<?php if(isset($mailbox_limit)){echo $mailbox_limit;} else{echo $mailbox_limit_default;} ?>" placeholder="Mailbox size (MB)" required="required"/> 
		</td>
		<?php } ?>
	</tr>
	
	</table>
	
	<input name="savemode" type="hidden" value="<?php if(isset($mode)){echo $mode;} ?>"/>
	<input name="id" class="sendbutton" type="hidden" value="<?php if(isset($id)){echo $id;} ?>"/>
	
	<p>
		<input type="button" class="button button-small" name="Text 1" value="Generate password"
		      onclick="pass=generatePassword();this.form.password.value=pass;this.form.password_rep.value=pass;this.form.password.type='text';this.form.password_rep.type='text'">
	</p>
	<p>
		<input type="submit" class="button button-small" value="Save settings">
	</p>
</form>
