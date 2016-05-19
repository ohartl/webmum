<?php

$thisStep = 3;

if($_SESSION['installer']['lastStep'] > $thisStep){
	$_SESSION['installer']['subStep'] = 1;
}
elseif($_SESSION['installer']['lastStep'] < $thisStep || !isset($_SESSION['installer']['subStep'])){
	$_SESSION['installer']['subStep'] = 0;
}

$error = null;

/*-----------------------------------------------------------------------------*/

$exampleConfigValues = require_once 'config/config.php.example';

$tablesInDatabase = array();
try{
	Database::init($_SESSION['installer']['config']['mysql']);

	$db = Database::getInstance();
	$tablesResult = $db->query(
		"SELECT TABLE_NAME FROM information_schema.tables "
		."WHERE TABLE_SCHEMA='".$db->escape($_SESSION['installer']['config']['mysql']['database'])."';"
	);

	foreach($tablesResult->fetch_all() as $row){
		$tablesInDatabase[] = $row[0];
	}
}
catch(Exception $e){
}

function getTableAttributes($table)
{
	global $_SESSION;
	$attributes = array();

	if(Database::isInitialized()){
		try{
			$db = Database::getInstance();
			$tablesResult = $db->query(
				"SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT, COLUMN_KEY, EXTRA FROM information_schema.columns "
				."WHERE TABLE_SCHEMA = '".$db->escape($_SESSION['installer']['config']['mysql']['database'])."' "
				."AND TABLE_NAME = '".$db->escape($table)."' "
				."ORDER BY TABLE_NAME,ORDINAL_POSITION;"
			);

			foreach($tablesResult->fetch_all() as $row){
				$s = $row[0];

				if(!empty($row[1])){
					$s .= ' : '.$row[1];
				}

				if($row[2] == 'NO'){
					$s .= ', NOT NULL';
				}

				if(!is_null($row[3])){
					$s .= ', DEFAULT \''.$row[3].'\'';
				}

				if(!empty($row[4])){
					if(strpos($row[4], 'PR') !== false){
						$s .= ', PRIMARY KEY';
					}
					if(strpos($row[4], 'UN') !== false){
						$s .= ', UNIQUE KEY';
					}
				}

				if(!empty($row[5]) && strpos($row[5], 'auto_inc') !== false){
					$s .= ', AUTO_INCREMENT';
				}

				$attributes[$row[0]] = $s;
			}
		}
		catch(Exception $e){
		}
	}

	return $attributes;
}

$optionalAttributes = array(
	'users' => array('mailbox_limit', 'max_user_redirects'),
	'aliases' => array('multi_source', 'is_created_by_user'),
);

define('ATTR_SEP', '---');

function getAttr($name, $default = null)
{
	global $_SESSION, $_POST;
	
	if(isset($_POST[$name])){
		return strip_tags($_POST[$name]);
	}
	elseif(strpos($name, ATTR_SEP) !== false){
		list($table, $attribute) = explode(ATTR_SEP, $name);

		if(isset($_SESSION['installer']['config']['schema']['attributes'][$table][$attribute])){
			return $_SESSION['installer']['config']['schema']['attributes'][$table][$attribute];
		}
	}
	elseif(isset($_SESSION['installer']['config']['schema']['tables'][$name])){
		return $_SESSION['installer']['config']['schema']['tables'][$name];
	}

	return $default;
}

/*-----------------------------------------------------------------------------*/

if(isset($_GET['go'])){
	if($_GET['go'] == 'next' && $_SERVER['REQUEST_METHOD'] == 'POST'){
		try{
			if($_SESSION['installer']['subStep'] === 0){

				$tables = array();
				foreach($exampleConfigValues['schema']['tables'] as $table => $mappedTable){
					if(!isset($_POST[$table])
						|| !in_array($_POST[$table], $tablesInDatabase)
					){
						throw new InvalidArgumentException('Missing mapping for table "'.$table.'".');
					}

					if(in_array($_POST[$table], array_values($tables))){
						throw new Exception('You cannot map table "'.$_POST[$table].'" twice.');
					}

					$tables[$table] = $_POST[$table];
				}

				// saving information
				$_SESSION['installer']['config']['schema'] = array();
				$_SESSION['installer']['config']['schema']['tables'] = $tables;

				installer_message('Database tables were successfully mapped.');

				$_SESSION['installer']['subStep'] = 1;
				installer_next($thisStep, 0);
			}
			elseif($_SESSION['installer']['subStep'] === 1){

				$attributes = array();
				foreach($_SESSION['installer']['config']['schema']['tables'] as $table => $mappedTable){

					$attributes[$table] = array();

					$attributesInDatabase = getTableAttributes($table);

					foreach($exampleConfigValues['schema']['attributes'][$table] as $attribute => $mappedAttribute){
						$key = $table.'---'.$attribute;

						if(isset($optionalAttributes[$table])
							&& in_array($attribute, $optionalAttributes[$table])
							&& !isset($attributesInDatabase[$_POST[$key]])
						){
							$attributes[$table][$attribute] = '';
						}
						else{
							if(!isset($_POST[$key]) || !isset($attributesInDatabase[$_POST[$key]])){
								throw new InvalidArgumentException('Missing mapping for attribute "'.$attribute.'" on table "'.$table.'".');
							}

							if(in_array($_POST[$key], $attributes[$table])){
								throw new Exception('You cannot map attribute "'.$_POST[$key].'" twice on table "'.$table.'".');
							}

							$attributes[$table][$attribute] = $_POST[$key];
						}
					}
				}

				// saving information
				$_SESSION['installer']['config']['schema']['attributes'] = $attributes;

				installer_message('Database attributes were successfully mapped.');

				unset($_SESSION['installer']['subStep']);
				installer_next($thisStep);
			}
		}
		catch(Exception $e){
			$error = $e->getMessage();
		}
	}
	elseif($_GET['go'] == 'prev'){

		// reset
		if(isset($_SESSION['installer']['config']['schema']['tables'])){
			if($_SESSION['installer']['subStep'] === 0){
				unset($_SESSION['installer']['config']['schema']);
			}
			elseif($_SESSION['installer']['subStep'] === 1){
				unset($_SESSION['installer']['config']['schema']['attributes']);
			}
		}

		if($_SESSION['installer']['subStep'] === 0){
			unset($_SESSION['installer']['subStep']);
			installer_prev($thisStep, ($_SESSION['installer']['type'] === INSTALLER_TYPE_MAP) ? 2 : 1);
		}
		else{
			$_SESSION['installer']['subStep'] = 0;
			installer_prev($thisStep, 0);
		}
	}
}
?>

<?php echo installer_message(); ?>

<h2>
	Step 2:
	<?php if($_SESSION['installer']['subStep'] === 0): ?>
		Database - table mapping.
	<?php elseif($_SESSION['installer']['subStep'] === 1): ?>
		Database - attribute mapping.
	<?php else: ?>
		Wrong turn
	<?php endif; ?>
</h2>

<?php if(!empty($error)): ?>
	<div class="notification notification-fail"><?php echo $error; ?></div>
<?php endif; ?>

<?php if($_SESSION['installer']['subStep'] === 0): ?>
	<form class="form" action="/?step=<?php echo $thisStep; ?>&go=next" method="post">
		<?php foreach($exampleConfigValues['schema']['tables'] as $table => $mappedTable): ?>
			<div class="input-group">
				<label for="<?php echo $table; ?>">Table "<?php echo $table; ?>"</label>
				<div class="input">
					<select name="<?php echo $table; ?>">
						<option value="">-- Not mapped --</option>
						<?php foreach($tablesInDatabase as $t): ?>
							<option value="<?php echo $t; ?>" <?php echo getAttr($table, $mappedTable) == $t ? 'selected' : ''; ?>><?php echo $t; ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
		<?php endforeach; ?>

		<hr class="invisible">

		<div class="buttons">
			<a class="button" href="/?step=<?php echo $thisStep; ?>&go=prev">Back</a>
			<button class="button button-primary" type="submit">Continue</button>
		</div>
	</form>
<?php elseif($_SESSION['installer']['subStep'] === 1): ?>
	<form class="form" action="/?step=<?php echo $thisStep; ?>&go=next" method="post">
		<?php
		$lastTable = array_keys($_SESSION['installer']['config']['schema']['tables']);
		$lastTable = $lastTable[count($lastTable) - 1];
		foreach($_SESSION['installer']['config']['schema']['tables'] as $table => $mappedTable):
			$attributesInDatabase = getTableAttributes($mappedTable);
			?>
			<h3>
				Table "<?php echo $table; ?>"
				<div class="sub-header">Has been mapped to table "<?php echo $mappedTable; ?>".</div>
			</h3>
			<div style="margin-left: 25px;">
				<?php foreach($exampleConfigValues['schema']['attributes'][$table] as $attribute => $mappedAttribute): ?>
					<div class="input-group">
						<label for="<?php echo $table.ATTR_SEP.$attribute; ?>">Attribute "<?php echo $attribute; ?>"</label>
						<?php if(isset($optionalAttributes[$table]) && in_array($attribute, $optionalAttributes[$table])): ?>
							<div class="input-info">This attribute is optional (used by optional features) and doesn't need to be mapped.</div>
						<?php endif; ?>
						<div class="input">
							<select name="<?php echo $table.ATTR_SEP.$attribute; ?>">
								<option value="">-- Not mapped --</option>
								<?php foreach($attributesInDatabase as $dbAttr => $dbAttrText): ?>
									<option value="<?php echo $dbAttr; ?>" <?php echo getAttr($table.ATTR_SEP.$attribute, $mappedAttribute) == $dbAttr ? 'selected' : ''; ?>><?php echo $dbAttrText; ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
			<?php if($table != $lastTable): ?>
			<hr>
		<?php endif; ?>
		<?php endforeach; ?>

		<hr class="invisible">

		<div class="buttons">
			<a class="button" href="/?step=<?php echo $thisStep; ?>&go=prev">Back</a>
			<button class="button button-primary" type="submit">Continue</button>
		</div>
	</form>
<?php else: ?>
	<div class="notification notification-fail">You took the wrong turn, <a href="/">restart installation</a>.</div>
<?php endif; ?>
