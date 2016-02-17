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

<form action="" method="post">
	<input name="email" class="textinput" type="text" placeholder="E-Mail Address" autofocus/><br>
	<input name="password" class="textinput" type="password" placeholder="Password"/>
	
	<p>
		<input type="submit" class="button button-small" value="Log in"/>
	</p>
</form>

