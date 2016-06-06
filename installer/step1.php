<?php

if(strpos($_SERVER['REQUEST_URI'], 'installer/') !== false){
	die('You cannot directly access the installer files.');
}

/*-----------------------------------------------------------------------------*/

$thisStep = 1;

$error = null;

/*-----------------------------------------------------------------------------*/

if(isset($_GET['go'])){
	if($_GET['go'] == 'next' && $_SERVER['REQUEST_METHOD'] == 'POST'){
		try{
			// testing db settings
			Database::init($_POST);

			// saving information
			$_SESSION['installer']['config']['mysql'] = array(
				'host' => $_POST['host'],
				'user' => $_POST['user'],
				'password' => $_POST['password'],
				'database' => $_POST['database'],
			);
			$_SESSION['installer']['type'] = (isset($_POST['install_type']) && $_POST['install_type'] == INSTALLER_TYPE_MAP)
				? INSTALLER_TYPE_MAP
				: INSTALLER_TYPE_CREATE;

			installer_message('Database connection was successfully established.');

			installer_next($thisStep, ($_SESSION['installer']['type'] === INSTALLER_TYPE_MAP) ? 2 : 1);
		}
		catch(InvalidArgumentException $e){
			$error = 'Some fields are missing.';
		}
		catch(Exception $e){
			$error = $e->getMessage();
		}
	}
	elseif($_GET['go'] == 'prev'){
		// reset
		unset($_SESSION['installer']['config']['mysql']);
		unset($_SESSION['installer']['type']);

		installer_prev($thisStep);
	}
}

function getAttr($name, $default = null)
{
	global $_SESSION, $_POST;

	if(isset($_POST[$name])){
		return strip_tags($_POST[$name]);
	}
	elseif(isset($_SESSION['installer']['config']['mysql'][$name])){
		return $_SESSION['installer']['config']['mysql'][$name];
	}
	elseif($name === 'install_type' && isset($_SESSION['installer']['type'])){
		return $_SESSION['installer']['type'];
	}

	return $default;
}

?>
<?php echo installer_message(); ?>

<h2>Step 1 of <?php echo INSTALLER_MAX_STEP; ?>: Database connection.</h2>

<?php if(!empty($error)): ?>
	<div class="notification notification-fail"><?php echo $error; ?></div>
<?php endif; ?>

<form class="form" action="/?step=<?php echo $thisStep; ?>&go=next" method="post">

	<p>Setup your MySQL database connection.</p>

	<div class="input-group">
		<label for="host">Database Host</label>
		<div class="input">
			<input type="text" name="host" value="<?php echo getAttr('host', 'localhost'); ?>" autofocus/>
		</div>
	</div>

	<div class="input-group">
		<label for="database">Database Name</label>
		<div class="input">
			<input type="text" name="database" value="<?php echo getAttr('database'); ?>"/>
		</div>
	</div>

	<div class="input-group">
		<label for="user">Database Username</label>
		<div class="input">
			<input type="text" name="user" value="<?php echo getAttr('user'); ?>"/>
		</div>
	</div>

	<div class="input-group">
		<label for="password">Database Password</label>
		<div class="input">
			<input type="password" name="password" value="<?php echo getAttr('password'); ?>"/>
		</div>
	</div>

	<hr>

	<div class="input-group">
		<label for="install_type">Installation Type</label>
		<div class="input-info">Be sure to select the correct option.</div>
		<div class="input">
			<input type="radio" name="install_type" id="install_type_0" value="0" <?php echo getAttr('install_type', 0) == 0 ? 'checked' : ''; ?>/>
			<label for="install_type_0">Create new database schema</label>
		</div>
		<div class="input">
			<input type="radio" name="install_type" id="install_type_1" value="1" <?php echo getAttr('install_type', 0) == 1 ? 'checked' : ''; ?>/>
			<label for="install_type_1">Map existing database schema</label>
		</div>
	</div>

	<hr class="invisible">

	<div class="buttons">
		<a class="button" href="/?step=<?php echo $thisStep; ?>&go=prev">Back</a>
		<button class="button button-primary" type="submit">Continue</button>
	</div>
</form>