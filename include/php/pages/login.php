<?php 

if(isset($_POST['email']) && isset($_POST['password'])){
	// Start login
	$login_success = $user->login($_POST['email'], $_POST['password']);
	if($login_success){
		header("Location: ".FRONTEND_BASE_PATH."private/");
	}
	else{
		add_message("fail", "Sorry, I couldn't log you in :(");
	}
}

// If user is already logged in, redirect to start.
if($user->isLoggedIn()){
	header("Location: ".FRONTEND_BASE_PATH."private/");
}

?>


<h1>Login</h1>

<?php output_messages(); ?>

<form action="" method="post">
	<input name="email" class="textinput" type="text" placeholder="E-Mail Address"/><br>
	<input name="password" class="textinput" type="password" placeholder="Password"/>
	
	<p>
		<input type="submit" class="button button-small" value="Log in"/>
	</p>
</form>

