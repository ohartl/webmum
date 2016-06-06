<?php

if(strpos($_SERVER['REQUEST_URI'], 'installer/') !== false){
	die('You cannot directly access the installer files.');
}

/*-----------------------------------------------------------------------------*/

$thisStep = 2;

/*-----------------------------------------------------------------------------*/

$exampleConfigValues = require_once 'config/config.php.example';

$tablesInDatabase = array();
try{
	Database::init($_SESSION['installer']['config']['mysql']);

	$tablesResult = Database::getInstance()->query("SELECT table_name FROM information_schema.tables WHERE table_schema='".$_SESSION['installer']['config']['mysql']['database']."';");
	foreach($tablesResult->fetch_all() as $row){
		$tablesInDatabase[] = $row[0];
	}
}
catch(Exception $e){
}

/*-----------------------------------------------------------------------------*/

$databaseSchema = array(
	'domains' => "CREATE TABLE IF NOT EXISTS ___database___.___table___ (___id___ INT(10) UNSIGNED NOT NULL AUTO_INCREMENT, ___domain___ VARCHAR(128) NOT NULL, PRIMARY KEY (___domain___), UNIQUE KEY ___id___ (___id___)) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;",
	'users' => "CREATE TABLE IF NOT EXISTS ___database___.___table___ (___id___ INT(10) UNSIGNED NOT NULL AUTO_INCREMENT, ___username___ VARCHAR(128) NOT NULL DEFAULT '', ___domain___ VARCHAR(128) NOT NULL DEFAULT '', ___password___ VARCHAR(128) NOT NULL DEFAULT '', ___mailbox_limit___ INT(10) NOT NULL DEFAULT '128', ___max_user_redirects___ INT(10) NOT NULL DEFAULT '0', PRIMARY KEY (___username___,___domain___), UNIQUE KEY ___id___ (___id___)) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;",
	'aliases' => "CREATE TABLE IF NOT EXISTS ___database___.___table___ (___id___ INT(10) UNSIGNED NOT NULL AUTO_INCREMENT, ___source___ VARCHAR(128) NOT NULL, ___destination___ TEXT NOT NULL, ___multi_source___ VARCHAR(32) DEFAULT NULL, ___is_created_by_user___ INT(1) NOT NULL DEFAULT '0', PRIMARY KEY (___source___), UNIQUE KEY ___id___ (___id___)) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;",
);

/**
 * @param string $stmt
 * @param string $database
 * @param string $table
 * @param array $attributes
 *
 * @return string
 */
function prepareSchemaTableStmt($stmt, $database, $table, $attributes)
{
	$attributes['database'] = $database;
	$attributes['table'] = $table;

	foreach($attributes as $search => $replace){
		$stmt = str_replace('___'.$search.'___', '`'.Database::getInstance()->escape($replace).'`', $stmt);
	}

	return $stmt;
}

$preparedSchemaStmt = '';
$allTablesFromSchemaExist = true;

foreach($databaseSchema as $table => $stmt){
	$preparedSchemaStmt .= prepareSchemaTableStmt(
			$stmt,
			$_SESSION['installer']['config']['mysql']['database'],
			$exampleConfigValues['schema']['tables'][$table],
			$exampleConfigValues['schema']['attributes'][$table]
		).PHP_EOL;

	// check if tables exist, should be enough for now
	if(!in_array($exampleConfigValues['schema']['tables'][$table], $tablesInDatabase)){
		$allTablesFromSchemaExist = false;
	}
}

$commandDenied = false;

/*-----------------------------------------------------------------------------*/

if(isset($_GET['go'])){
	if($_GET['go'] == 'next' && $_SERVER['REQUEST_METHOD'] == 'POST'){
		if(isset($_POST['manual'])){
			if($_POST['manual'] == 1){
				// display SQL
			}
			elseif($_POST['manual'] == 2){
				// check if schema was created
				if($allTablesFromSchemaExist){
					// saving information
					$_SESSION['installer']['config']['schema'] = $exampleConfigValues['schema'];

					installer_message('Database schema was manually created.');

					installer_next($thisStep, 2);
				}
				else{
					$_POST['manual'] = 1;
				}
			}
		}
		else{
			if(!$allTablesFromSchemaExist){
				try{
					foreach(explode(PHP_EOL, $preparedSchemaStmt) as $stmt){
						Database::getInstance()->query($stmt);
					}

					// saving information
					$_SESSION['installer']['config']['schema'] = $exampleConfigValues['schema'];

					installer_message('Database schema was automatically created.');

					installer_next($thisStep, 2);
				}
				catch(Exception $e){
					if(strpos($e->getMessage(), 'command denied') !== false){
						$commandDenied = true;
					}
					else{
						throw $e;
					}
				}
			}
		}
	}
	elseif($_GET['go'] == 'prev'){
		// reset
		unset($_SESSION['installer']['config']['schema']);

		installer_prev($thisStep);
	}
}
?>
<?php echo installer_message(); ?>

	<h2>Step 2 of <?php echo INSTALLER_MAX_STEP; ?>: Create database schema.</h2>

<?php if($allTablesFromSchemaExist): ?>
	<div class="notification notification-fail">
		The schema already exists in database "<?php echo $_SESSION['installer']['config']['mysql']['database']; ?>".
	</div>

	<div>
		Your next possible steps:
		<ul>
			<li>Either <strong>delete</strong> the existing schema.</li>
			<li>Go Back and <strong>change</strong> the used database.</li>
			<li>Go Back and <strong>start mapping</strong> the existing database schema.</li>
		</ul>
	</div>

	<div class="buttons">
		<a class="button" href="/?step=<?php echo $thisStep; ?>&go=prev">Back</a>
		<a class="button" href="/?step=<?php echo $thisStep; ?>">Retry</a>
	</div>
<?php else: ?>
	<form class="form" action="/?step=<?php echo $thisStep; ?>&go=next" method="post">
		<?php if(isset($_POST['manual']) && $_POST['manual'] == 1): ?>
			<textarea readonly style="width: 100%; height: 170px"><?php echo $preparedSchemaStmt; ?></textarea>

			<div class="notification notification-warning">
				Copy the SQL-Code above and import it into your database "<?php echo $_SESSION['installer']['config']['mysql']['database']; ?>".
			</div>

			<hr class="invisible">

			<p>Once you have imported the schema, you can continue by clicking on the Continue button.</p>

			<div class="buttons">
				<a class="button" href="/?step=<?php echo $thisStep; ?>">Back</a>
				<button class="button button-primary" name="manual" value="2" type="submit">Continue</button>
			</div>
		<?php else: ?>
			<div class="notification notification-warning">
				The following database schema will be created in
				<strong>database "<?php echo $_SESSION['installer']['config']['mysql']['database']; ?>"</strong>.
				<br><strong>Please make sure that "<?php echo $_SESSION['installer']['config']['mysql']['database']; ?>" is clean / empty database!</strong>
			</div>

			<?php if($commandDenied): ?>
				<div class="notification notification-fail">The
					<strong>user "<?php echo $_SESSION['installer']['config']['mysql']['user']; ?>" is missing the permission</strong> to execute MySQL "CREATE" commands.
				</div>
			<?php else: ?>
				<div class="notification notification-warning">
					Also <strong>make sure</strong> that the database
					<strong>user "<?php echo $_SESSION['installer']['config']['mysql']['user']; ?>" has the privileges to create</strong> the schema.
				</div>
			<?php endif; ?>

			<?php foreach($exampleConfigValues['schema']['tables'] as $table => $mappedTable): ?>
				<div>
					<strong>Table "<?php echo $table; ?>"</strong>
					<ul>
						<?php foreach($exampleConfigValues['schema']['attributes'][$table] as $attribute => $mappedAttribute): ?>
							<li><?php echo $attribute; ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endforeach; ?>

			<hr class="invisible">

			<p>Click on the Continue button to try creating the schema automatically.</p>

			<div class="buttons">
				<a class="button" href="/?step=<?php echo $thisStep; ?>&go=prev">Back</a>
				<button class="button" name="manual" value="1" type="submit">Import schema manually</button>
				<button class="button button-primary" type="submit">Continue</button>
			</div>
		<?php endif; ?>
	</form>
<?php endif; ?>