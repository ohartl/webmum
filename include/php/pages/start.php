<?php
if(Auth::isLoggedIn()){
	redirect("private");
}
?>

<h1>WebMUM</h1>

<p>
	WebMUM is an easy to use web interface for managing user accounts on your e-mail server with a MySQL user backend.<br/>
	Users of your server can log in here to change their passwords.
</p>

<div class="buttons buttons-horizontal">
	<a class="button" href="<?php echo url('login'); ?>">Log in</a>
</div>

