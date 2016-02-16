<?php
if($user->isLoggedIn() === true){
	redirect("private/");
}
?>

<h1>WebMUM</h1>

<p>
WebMUM is an easy to use webinterface for managing user accounts on your mailserver's MySQL user backend.<br/>
Users of your server can log in here to change their passwords.
</p>

<p style="margin-top:30px;">
	<a class="button button-small" href="<?php echo FRONTEND_BASE_PATH; ?>login/">Log in</a>
</p>

