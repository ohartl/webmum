<?php

if(isset($_POST['password']) && isset($_POST['password_repeat'])){
	try{
		Auth::getUser()->changePassword($_POST['password'], $_POST['password_repeat']);

		Message::getInstance()->success("Password changed successfully!");
	}
	catch(Exception $passwordInvalidException){
		Message::getInstance()->fail($passwordInvalidException->getMessage());
	}
}

?>

<h1>Change password</h1>

<div class="buttons">
	<a class="button" href="<?php echo Router::url('private'); ?>">&#10092; Back to personal dashboard</a>
</div>

<?php echo Message::getInstance()->render(); ?>

<form class="form" action="" method="post" autocomplete="off">
	<div class="input-group">
		<label for="password">Password</label>
		<?php if(Config::has('password.min_length')): ?>
			<div class="input-info">Your new password must be at least <?php echo Config::get('password.min_length'); ?> characters long.</div>
		<?php endif; ?>
		<div class="input input-action">
			<input type="password" name="password" placeholder="New password" required minlength="<?php echo Config::get('password.min_length', 0); ?>" autofocus/>
			<button type="button" class="button" onclick="pass=generatePassword();this.form.password.value=pass;this.form.password_repeat.value=pass;this.form.password.type='text';this.form.password_repeat.type='text'">Generate password</button>
		</div>
		<div class="input">
			<input type="password" name="password_repeat" placeholder="Repeat password" required minlength="<?php echo Config::get('password.min_length', 0); ?>"/>
		</div>
	</div>

	<div class="buttons">
		<button type="submit" class="button button-primary">Change password</button>
	</div>
</form>