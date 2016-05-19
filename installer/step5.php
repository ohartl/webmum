<?php

if(strpos($_SERVER['REQUEST_URI'], 'installer/') !== false){
	die('You cannot directly access the installer files.');
}

/*-----------------------------------------------------------------------------*/

$thisStep = 5;

$error = null;

/*-----------------------------------------------------------------------------*/

$exampleConfigValues = require_once 'config/config.php.example';

$possibleEmailSeparatorsText = array(', ', '; ', "\n");
$possibleEmailSeparatorsForm = array(',', ';', "\n");

function getAttr($name, $default = null)
{
	global $_SESSION, $_POST;

	if(isset($_POST[$name])){
		return strip_tags($_POST[$name]);
	}
	elseif($name === 'base_url' && isset($_SESSION['installer']['config']['base_url'])){
		return $_SESSION['installer']['config']['base_url'];
	}
	elseif(isset($_SESSION['installer']['config']['frontend_options'][$name])){
		return $_SESSION['installer']['config']['frontend_options'][$name];
	}

	return $default;
}

/*-----------------------------------------------------------------------------*/

if(isset($_GET['go'])){
	if($_GET['go'] == 'next' && $_SERVER['REQUEST_METHOD'] == 'POST'){
		try{
			if(!isset($_POST['base_url']) || empty($_POST['base_url'])){
				throw new Exception('The field URL isn\'t filled out yet.');
			}
			if(!isset($_POST['email_separator_text'])
				|| !is_numeric($_POST['email_separator_text'])
				|| !isset($possibleEmailSeparatorsText[$_POST['email_separator_text']])
				|| !isset($_POST['email_separator_form'])
				|| !is_numeric($_POST['email_separator_form'])
				|| !isset($possibleEmailSeparatorsForm[$_POST['email_separator_form']])
			){
				throw new InvalidArgumentException;
			}

			// saving information
			$_SESSION['installer']['config']['base_url'] = $_POST['base_url'];
			$_SESSION['installer']['config']['frontend_options'] = array(
				'email_separator_text' => $possibleEmailSeparatorsText[$_POST['email_separator_text']],
				'email_separator_form' => $possibleEmailSeparatorsForm[$_POST['email_separator_form']],
			);

			installer_message('General settings saved.');

			installer_next($thisStep);
		}
		catch(InvalidArgumentException $e){
			$error = 'Some field is missing.';
		}
		catch(Exception $e){
			$error = $e->getMessage();
		}
	}
	elseif($_GET['go'] == 'prev'){
		// reset
		unset($_SESSION['installer']['config']['base_url']);
		unset($_SESSION['installer']['config']['frontend_options']);

		installer_prev($thisStep);
	}
}
?>

<?php echo installer_message(); ?>

<h2>Step 4 of <?php echo INSTALLER_MAX_STEP; ?>: General settings</h2>

<?php if(!empty($error)): ?>
	<div class="notification notification-fail"><?php echo $error; ?></div>
<?php endif; ?>

<form class="form" action="/?step=<?php echo $thisStep; ?>&go=next" method="post">

	<div class="input-group">
		<label for="base_url">URL to this WebMUM installation</label>
		<div class="input-info">
			The URL your WebMUM installation is accessible from outside including subdirectories, ports and the protocol.
			<br><br>Some examples:
			<ul style="margin: 2px 0">
				<li>http://localhost/webmum</li>
				<li>http://webmum.mydomain.tld</li>
				<li>https://mydomain.tld/dir</li>
				<li>http://mydomain.tld:8080</li>
			</ul>
		</div>
		<div class="input">
			<input type="text" name="base_url" value="<?php echo getAttr('base_url'); ?>"/>
		</div>
	</div>

	<hr>

	<div class="input-group">
		<label>Separator for email lists</label>

		<div class="input-group">
			<label for="email_separator_text">&hellip; in texts.</label>
			<div class="input">
				<input type="radio" name="email_separator_text" id="email_separator_text_0" value="0" <?php echo (getAttr('email_separator_text', 0) == 0) ? 'checked' : ''; ?>>
				<label for="email_separator_text_0">comma: <code>', '</code></label>

				<input type="radio" name="email_separator_text" id="email_separator_text_1" value="1" <?php echo (getAttr('email_separator_text', 0) == 1) ? 'checked' : ''; ?>>
				<label for="email_separator_text_1">semicolon: <code>'; '</code></label>

				<input type="radio" name="email_separator_text" id="email_separator_text_2" value="2" <?php echo (getAttr('email_separator_text', 0) == 2) ? 'checked' : ''; ?>>
				<label for="email_separator_text_2">newline: <code>'&lt;br&gt;'</code></label>
			</div>
		</div>

		<div class="input-group">
			<label for="email_separator_form">&hellip; in forms.</label>
			<div class="input">
				<input type="radio" name="email_separator_form" id="email_separator_form_0" value="0" <?php echo (getAttr('email_separator_form', 0) == 0) ? 'checked' : ''; ?>>
				<label for="email_separator_form_0">comma: <code>','</code></label>

				<input type="radio" name="email_separator_form" id="email_separator_form_1" value="1" <?php echo (getAttr('email_separator_form', 0) == 1) ? 'checked' : ''; ?>>
				<label for="email_separator_form_1">semicolon: <code>';'</code></label>

				<input type="radio" name="email_separator_form" id="email_separator_form_2" value="2" <?php echo (getAttr('email_separator_form', 0) == 2) ? 'checked' : ''; ?>>
				<label for="email_separator_form_2">newline: <code>'\n'</code></label>
			</div>
		</div>
	</div>

	<hr class="invisible">

	<div class="buttons">
		<a class="button" href="/?step=<?php echo $thisStep; ?>&go=prev">Back</a>
		<button class="button button-primary" type="submit">Continue</button>
	</div>
</form>