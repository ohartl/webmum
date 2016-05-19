<?php

$thisStep = 4;

$error = null;

/*-----------------------------------------------------------------------------*/

$exampleConfigValues = require_once 'config/config.php.example';

$hashAlgorithms = array(
	'SHA-512',
	'SHA-256',
	'BLOWFISH',
);

Database::init($_SESSION['installer']['config']['mysql']);

$databaseUserCount = Database::getInstance()->count(
	$_SESSION['installer']['config']['schema']['tables']['users'],
	$_SESSION['installer']['config']['schema']['attributes']['users']['id']
);

function getAttr($name, $default = null)
{
	global $_SESSION, $_POST;

	if(isset($_POST[$name])){
		return strip_tags($_POST[$name]);
	}
	elseif(isset($_SESSION['installer']['config']['password'][$name])){
		return $_SESSION['installer']['config']['password'][$name];
	}
	elseif($name === 'admin_user'){
		return $_SESSION['installer']['user']['user'];
	}
	elseif($name === 'admin_password'){
		return $_SESSION['installer']['user']['password'];
	}

	return $default;
}

/*-----------------------------------------------------------------------------*/

if(isset($_GET['go'])){

	if($_GET['go'] == 'next' && $_SERVER['REQUEST_METHOD'] == 'POST'){
		try{
			if(!isset($_POST['hash_algorithm']) || !isset($_POST['min_length']) || !isset($_POST['admin_user']) || !isset($_POST['admin_password'])){
				throw new InvalidArgumentException;
			}

			$passwordConfig = array(
				'hash_algorithm' => in_array($_POST['hash_algorithm'], $hashAlgorithms) ? $_POST['hash_algorithm'] : $exampleConfigValues['password']['hash_algorithm'],
				'min_length' => intval($_POST['min_length']),
			);

			// init system for testing
			Config::init(array('password' => $passwordConfig));

			// handle user
			if($databaseUserCount > 0){
				// testing existing login

				$validLogin = Auth::login($_POST['admin_user'], $_POST['admin_password']);
				unset($_SESSION[Auth::SESSION_IDENTIFIER]);

				if(!$validLogin){
					throw new Exception('Invalid combination of user and password.');
				}
			}
			else{
				// create user in database

				if(strpos($_POST['admin_user'], '@') === false){
					throw new Exception('The field "Your user" must be an email address.');
				}
				else{
					list($username, $domain) = explode('@', $_POST['admin_user']);
					$passwordHash = Auth::generatePasswordHash($_POST['admin_password']);

					$hasDomain = Database::getInstance()->count(
						$_SESSION['installer']['config']['schema']['tables']['domains'],
						$_SESSION['installer']['config']['schema']['attributes']['domains']['id'],
						array($_SESSION['installer']['config']['schema']['attributes']['domains']['domain'], $domain)
					);
					if($hasDomain === 0){
						Database::getInstance()->insert(
							$_SESSION['installer']['config']['schema']['tables']['domains'],
							array(
								$_SESSION['installer']['config']['schema']['attributes']['domains']['domain'] => $domain,
							)
						);
					}

					Database::getInstance()->insert(
						$_SESSION['installer']['config']['schema']['tables']['users'],
						array(
							$_SESSION['installer']['config']['schema']['attributes']['users']['username'] => $username,
							$_SESSION['installer']['config']['schema']['attributes']['users']['domain'] => $domain,
							$_SESSION['installer']['config']['schema']['attributes']['users']['password'] => $passwordHash,
						)
					);
				}
			}

			// saving information
			$_SESSION['installer']['config']['password'] = $passwordConfig;
			$_SESSION['installer']['config']['admins'] = array($_POST['admin_user']);
			$_SESSION['installer']['config']['admin_domain_limits'] = array();
			$_SESSION['installer']['user'] = array(
				'user' => $_POST['admin_user'],
				'password' => $_POST['admin_password'],
			);

			installer_next($thisStep);
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
		unset($_SESSION['installer']['config']['password']);
		unset($_SESSION['installer']['config']['admins']);
		unset($_SESSION['installer']['config']['admin_domain_limits']);
		unset($_SESSION['installer']['user']);

		installer_prev($thisStep, ($_SESSION['installer']['type'] === INSTALLER_TYPE_MAP) ? 1 : 2);
	}
}
?>

<?php echo installer_message(); ?>

<h2>Step 3: Your first admin user.</h2>

<?php if(!empty($error)): ?>
	<div class="notification notification-fail"><?php echo $error; ?></div>
<?php endif; ?>

<form class="form" action="/?step=<?php echo $thisStep; ?>&go=next" method="post">
	<div class="input-group">
		<label for="password">Password hash algorithm</label>
		<div class="input-info">Hash algorithm that you chose in your mailserver installation process.</div>
		<div class="input">
			<select name="hash_algorithm">
				<?php foreach($hashAlgorithms as $algo): ?>
					<option value="<?php echo $algo; ?>" <?php echo getAttr('hash_algorithm', $exampleConfigValues['password']['hash_algorithm']) == $algo ? 'selected' : ''; ?>>
						<?php echo $algo; ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>
	</div>

	<div class="input-group">
		<label for="min_length">Minimum password length</label>
		<div class="input">
			<div class="input input-labeled input-labeled-right">
				<input name="min_length" type="number" value="<?php echo getAttr('min_length', $exampleConfigValues['password']['min_length']); ?>" placeholder="Mailbox limit in MB" min="0"/>
				<span class="input-label">chars</span>
			</div>
		</div>
	</div>

	<hr>

	<?php if($databaseUserCount === 0): ?>
		<div class="notification notification-warning">
			There is no user created yet, please create one now as your admin user.
			<br>Please note that once the user is created you will have to remember the password.
		</div>
	<?php endif; ?>

	<p>This user will be mark as an admin in the configuration.</p>

	<div class="input-group">
		<label for="admin_user">Your user</label>
		<div class="input-info">
			Must be an email address (user@domain).<br>
			<?php if($databaseUserCount > 0): ?>
				This user must have been added in mailserver installation process.<br>
			<?php endif; ?>
		</div>
		<div class="input">
			<input type="text" name="admin_user" value="<?php echo getAttr('admin_user'); ?>"/>
		</div>
	</div>

	<div class="input-group">
		<label for="admin_password">Your password</label>
		<div class="input">
			<input type="password" name="admin_password" value="<?php echo getAttr('admin_password'); ?>"/>
		</div>
	</div>

	<hr class="invisible">

	<div class="buttons">
		<a class="button" href="/?step=<?php echo $thisStep; ?>&go=prev">Back</a>
		<button class="button button-primary" type="submit">Continue</button>
	</div>
</form>