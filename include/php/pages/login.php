<?php 

if(isset($_POST['email']) && isset($_POST['password'])){
	// Start login
	$login_success = $user->login($_POST['email'], $_POST['password']);
	if($login_success){
		redirect("private");
	}
	// If login is not successful
	else{
		//Log error message
		writeLog("WebMUM login failed for IP ".$_SERVER['REMOTE_ADDR']);
		add_message("fail", "Sorry, couldn't log you in :(");
	}
}

// If user is already logged in, redirect to start.
if($user->isLoggedIn()){
	redirect("private");
}

?>

<h1>Login</h1>

<?php output_messages(); ?>

<form class="form" action="" method="post">
	<div class="input-group">
		<label>Email address</label>
		<div class="input">
			<input type="text"  name="email" placeholder="Your email address" autofocus required/><br>
		</div>
	</div>

	<div class="input-group">
		<label>Password</label>
		<div class="input">
			<input type="password"  name="password" placeholder="Your password" required/>
		</div>
	</div>

	<div class="buttons">
		<button type="submit" class="button button-primary">Log in</button>
	</div>
</form>

