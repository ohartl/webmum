<?php

if(strpos($_SERVER['REQUEST_URI'], 'installer/') !== false){
	die('You cannot directly access the installer files.');
}

/*-----------------------------------------------------------------------------*/

$thisStep = 6;

$error = null;

/*-----------------------------------------------------------------------------*/

$exampleConfigValues = require_once 'config/config.php.example';

function getAttr($name, $default = null)
{
	global $_SESSION, $_POST;

	if(isset($_POST[$name])){
		return strip_tags($_POST[$name]);
	}
	elseif(isset($_SESSION['installer']['config']['options'][$name])){
		return $_SESSION['installer']['config']['options'][$name];
	}

	return $default;
}

/*-----------------------------------------------------------------------------*/

if(isset($_GET['go'])){

	if($_GET['go'] == 'next' && $_SERVER['REQUEST_METHOD'] == 'POST'){
		try{
			$options = array();

			// Mailbox limits
			if(isset($_POST['enable_mailbox_limits']) && $_POST['enable_mailbox_limits'] == 1){
				if(empty($_SESSION['installer']['config']['schema']['attributes']['users']['mailbox_limit'])){
					throw new Exception('Mailbox limits couldn\'t be enabled, because the attribute "mailbox_limit" in database table "users" is missing or not mapped yet');
				}
				else{
					$options['enable_mailbox_limits'] = true;
				}
			}
			else{
				$options['enable_mailbox_limits'] = false;
			}

			// Validate source addresses in redirects
			if(isset($_POST['enable_validate_aliases_source_domain']) && $_POST['enable_validate_aliases_source_domain'] == 1){
				$options['enable_validate_aliases_source_domain'] = true;
			}
			else{
				$options['enable_validate_aliases_source_domain'] = false;
			}

			// Multiple source redirect support
			if(isset($_POST['enable_multi_source_redirects']) && $_POST['enable_multi_source_redirects'] == 1){
				if(empty($_SESSION['installer']['config']['schema']['attributes']['aliases']['multi_source'])){
					throw new Exception('Multiple source redirect support couldn\'t be enabled, because the attribute "multi_source" in database table "aliases" is missing or not mapped yet');
				}
				else{
					$options['enable_multi_source_redirects'] = true;
				}
			}
			else{
				$options['enable_multi_source_redirects'] = false;
			}

			// Admin domain limits
			if(isset($_POST['enable_admin_domain_limits']) && $_POST['enable_admin_domain_limits'] == 1){
				$options['enable_admin_domain_limits'] = true;
			}
			else{
				$options['enable_admin_domain_limits'] = false;
			}

			// Users redirects
			if(isset($_POST['enable_user_redirects']) && $_POST['enable_user_redirects'] == 1){
				if(empty($_SESSION['installer']['config']['schema']['attributes']['users']['max_user_redirects'])
					|| empty($_SESSION['installer']['config']['schema']['attributes']['aliases']['is_created_by_user'])
				){
					throw new Exception('Users redirects couldn\'t be enabled, because some database attributes are missing or not mapped yet');
				}
				else{
					$options['enable_user_redirects'] = true;
				}
			}
			else{
				$options['enable_user_redirects'] = false;
			}

			// Logging for failed login attempts
			$logPath = '';
			if(isset($_POST['enable_logging']) && $_POST['enable_logging'] == 1){
				$options['enable_logging'] = true;

				if(!isset($_POST['log_path']) || empty($_POST['log_path'])){
					throw new Exception('You need to set the log path if you enabled logging.');
				}

				$logPath = $_POST['log_path'];

				if(!file_exists($_POST['log_path'])){
					throw new Exception('The log path you set doesn\'t exist.');
				}

				if(!is_writable($_POST['log_path'])){
					throw new Exception('The log path you set isn\'t writable.');
				}
			}
			else{
				$options['enable_logging'] = false;
			}

			// saving information
			$_SESSION['installer']['config']['options'] = $options;
			$_SESSION['installer']['config']['log_path'] = $logPath;

			installer_message('Saved settings for optional features.');

			installer_next($thisStep);
		}
		catch(Exception $e){
			$error = $e->getMessage();
		}
	}
	elseif($_GET['go'] == 'prev'){
		// reset
		unset($_SESSION['installer']['config']['options']);
		unset($_SESSION['installer']['config']['log_path']);

		installer_prev($thisStep);
	}
}
?>

<?php echo installer_message(); ?>

<h2>Step 5 of <?php echo INSTALLER_MAX_STEP; ?>: Optional features</h2>

<?php if(!empty($error)): ?>
	<div class="notification notification-fail"><?php echo $error; ?></div>
<?php endif; ?>

<form class="form" action="/?step=<?php echo $thisStep; ?>&go=next" method="post">

	<div class="input-group">
		<label for="enable_mailbox_limits">Mailbox limits</label>
		<div class="input-info">Limit the maximum size of mailbox for users.</div>
		<?php if(empty($_SESSION['installer']['config']['schema']['attributes']['users']['mailbox_limit'])): ?>
			<p class="text-warning">
				<strong>This feature cannot be enabled because the attribute "mailbox_limit" in database table "users" is missing or not mapped yet.</strong>
				<br><br>You could go back and create / map the missing attribute.
			</p>
		<?php else: ?>
			<div class="input">
				<input type="checkbox" name="enable_mailbox_limits" id="enable_mailbox_limits" value="1" <?php echo getAttr('enable_mailbox_limits', false) ? 'checked' : ''; ?>>
				<label for="enable_mailbox_limits">Enable feature</label>
			</div>
		<?php endif; ?>
	</div>

	<hr>

	<div class="input-group">
		<label for="enable_validate_aliases_source_domain">Validate source addresses in redirects</label>
		<div class="input-info">Only email addresses ending with a domain from domains will be allowed.</div>
		<div class="input">
			<input type="checkbox" name="enable_validate_aliases_source_domain" id="enable_validate_aliases_source_domain" value="1" <?php echo getAttr('enable_validate_aliases_source_domain', true) ? 'checked' : ''; ?>>
			<label for="enable_validate_aliases_source_domain">Enable feature</label>
		</div>
	</div>

	<hr>

	<div class="input-group">
		<label for="enable_multi_source_redirects">Multiple source redirect support</label>
		<div class="input-info">Redirects can have multiple source addresses. This enables you to enter multiple redirects to a destination at once.</div>
		<?php if(empty($_SESSION['installer']['config']['schema']['attributes']['aliases']['multi_source'])): ?>
			<p class="text-warning">
				<strong>This feature cannot be enabled because the attribute "multi_source" in database table "aliases" is missing or not mapped yet.</strong>
				<br><br>You could go back and create / map the missing attribute.
			</p>
		<?php else: ?>
			<div class="input">
				<input type="checkbox" name="enable_multi_source_redirects" id="enable_multi_source_redirects" value="1" <?php echo getAttr('enable_multi_source_redirects', false) ? 'checked' : ''; ?>>
				<label for="enable_multi_source_redirects">Enable feature</label>
			</div>
		<?php endif; ?>
	</div>

	<hr>

	<div class="input-group">
		<label for="enable_admin_domain_limits">Admin domain limits</label>
		<div class="input-info">
			Limit certain admins to have access to certain domains only.
			<br>Note: This needs to be manually configured in the <code>'admin_domain_limits'</code> config variable.
		</div>
		<div class="input">
			<input type="checkbox" name="enable_admin_domain_limits" id="enable_admin_domain_limits" value="1" <?php echo getAttr('enable_admin_domain_limits', false) ? 'checked' : ''; ?>>
			<label for="enable_admin_domain_limits">Enable feature</label>
		</div>
	</div>

	<hr>

	<div class="input-group">
		<label for="enable_user_redirects">Users redirects</label>
		<div class="input-info">
			Enable users to create their redirects on their own.
			<br>Users can also be limited to a maximum number of redirects they can create.
		</div>
		<?php if(empty($_SESSION['installer']['config']['schema']['attributes']['users']['max_user_redirects']) || empty($_SESSION['installer']['config']['schema']['attributes']['aliases']['is_created_by_user'])): ?>
			<p class="text-warning">
			<strong>This feature cannot be enabled because,
			<?php if(empty($_SESSION['installer']['config']['schema']['attributes']['users']['max_user_redirects']) && empty($_SESSION['installer']['config']['schema']['attributes']['aliases']['is_created_by_user'])): ?>
				there are missing attributes in two database tables:</strong>
				<ul>
					<li>"max_user_redirects" in "users"</li>
					<li>"is_created_by_user" in "aliases"</li>
				</ul>
				<br>You could go back and create / map the missing attributes.
			<?php else: ?>
				the attribute <?php echo empty($_SESSION['installer']['config']['schema']['attributes']['users']['max_user_redirects']) ? '"max_user_redirects" in database table "users"' : '"is_created_by_user" in database table "aliases"'; ?> is missing or not mapped yet.
			<?php endif; ?>
			</strong>
			<br><br>You could go back and create / map the missing attributes.
			</p>
		<?php else: ?>
			<div class="input">
				<input type="checkbox" name="enable_user_redirects" id="enable_user_redirects" value="1" <?php echo getAttr('enable_user_redirects', false) ? 'checked' : ''; ?>>
				<label for="enable_user_redirects">Enable feature</label>
			</div>
		<?php endif; ?>
	</div>

	<hr>

	<div class="input-group">
		<label for="enable_logging">Logging for failed login attempts</label>
		<div class="input-info">
			WebMUM will write messages into the logfile.
			<br>The logfile could be used by <strong>Fail2ban</strong> to block brute-forcing attacks.
		</div>
		<div class="input">
			<input type="checkbox" name="enable_logging" id="enable_logging" value="1" <?php echo getAttr('enable_logging', false) ? 'checked' : ''; ?>>
			<label for="enable_logging">Enable feature</label>
		</div>
	</div>

	<div class="input-group">
		<label for="log_path">Log path</label>
		<div class="input-info">Directory where the <code>webmum.log</code> should be written to:</div>
		<div class="input">
			<input type="text" name="log_path" value="<?php echo getAttr('log_path'); ?>">
		</div>
	</div>
	
	<hr class="invisible">

	<div class="buttons">
		<a class="button" href="/?step=<?php echo $thisStep; ?>&go=prev">Back</a>
		<button class="button button-primary" type="submit">Continue</button>
	</div>
</form>