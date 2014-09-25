<?php

if(isset($_POST['sent'])){
	// User tries to change password
	$change_pass_success = $user->change_password($_POST['password'], $_POST['password_repeat']);
	
	if($change_pass_success === true){
		add_message("success", "Password changed successfully!");
	}
	else if($change_pass_success === false){
		add_message("fail", "Error while changing password! ".$PASS_ERR_MSG);
	}
}

?>



<h1>Change password</h1>

<?php output_messages(); ?>

<p>
	<a class="button button-small" href="<?php echo FRONTEND_BASE_PATH; ?>private/">&#10092; Back to personal dashboard</a>
</p>


<p>
	Your new password must contain <?php echo MIN_PASS_LENGTH; ?> characters or more.
</p>

<form action="" method="post">
	<p>
		<input name="password" class="textinput" type="password" placeholder="New password"/><br/>
		<input name="password_repeat" class="textinput" type="password" placeholder="New password (repeat)"/>
		<input name="sent" type="hidden" value="1"/>
	</p>
	
	<p>
		<input type="submit" class="button button-small" value="Change password"/>
	</p>
</form>