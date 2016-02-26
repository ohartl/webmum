<?php

if(isset($_POST['password']) && isset($_POST['password_repeat'])){
	try {
		Auth::getUser()->changePassword($_POST['password'], $_POST['password_repeat']);

		add_message("success", "Password changed successfully!");
	}
	catch(Exception $passwordInvalidException){
		add_message("fail", $passwordInvalidException->getMessage());
	}
}

?>

<h1>Change password</h1>

<div class="buttons">
	<a class="button" href="<?php echo Router::url('private'); ?>">&#10092; Back to personal dashboard</a>
</div>

<?php output_messages(); ?>

<form class="form" action="" method="post" autocomplete="off">
	<div class="input-group">
		<label for="password">Password</label>
		<div class="input-info">Your new password must be at least <?php echo MIN_PASS_LENGTH; ?> characters long.</div>
		<div class="input input-action">
			<input type="password" name="password" placeholder="New password" required minlength="<?php echo MIN_PASS_LENGTH; ?>" autofocus/>
			<button type="button" class="button" onclick="pass=generatePassword();this.form.password.value=pass;this.form.password_repeat.value=pass;this.form.password.type='text';this.form.password_repeat.type='text'">Generate password</button>
		</div>
		<div class="input">
			<input type="password" name="password_repeat" placeholder="Repeat password" required minlength="<?php echo MIN_PASS_LENGTH; ?>"/>
		</div>
	</div>

	<div class="buttons">
		<button type="submit" class="button button-primary">Change password</button>
	</div>
</form>