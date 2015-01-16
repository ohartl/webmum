<?php 
	if(isset($_POST['savemode'])){
		$savemode = $_POST['savemode'];
		
		if($savemode === "edit"){
			$id = $db->escape_string($_POST['id']);
			
			$source = $db->escape_string($_POST['source']);
			$source = strtolower($source);
			$destination = $db->escape_string($_POST['destination']);
			$destination = strtolower($destination);
			
			if($source !== "" && $destination !== ""){
			
				$sql = "UPDATE `".DBT_ALIASES."` SET `".DBC_ALIASES_SOURCE."` = '$source', `".DBC_ALIASES_DESTINATION."` = '$destination' WHERE `".DBC_ALIASES_ID."` = '$id'";
				
				if(!$result = $db->query($sql)){
					die('There was an error running the query [' . $db->error . ']');
				}
				else{
					// Edit successfull, redirect to overview
					header("Location: ".FRONTEND_BASE_PATH."admin/listredirects/?edited=1");
				}
			}
			else{
				add_message("fail", "Redirect could not be edited. Fill out all fields.");
			}
		}
		
		else if($savemode === "create"){
			$source = $db->escape_string($_POST['source']);
			$source = strtolower($source);
			$destination = $db->escape_string($_POST['destination']);
			$destination = strtolower($destination);
			
			if($source !== "" && $destination !== ""){
				$sql = "INSERT INTO `".DBT_ALIASES."` (`".DBC_ALIASES_SOURCE."`, `".DBC_ALIASES_DESTINATION."`) VALUES ('$source', '$destination')";
					
				if(!$result = $db->query($sql)){
					die('There was an error running the query [' . $db->error . ']');
				}
				
				else{
					// Redirect to user edit page when user is created
					header("Location: ".FRONTEND_BASE_PATH."admin/listredirects/?created=1");
				}
			}
			else{
				add_message("fail", "Redirect could not be created. Fill out all fields.");
			}
		}
	}
	
	
	// Select mode 
	$mode = "create";	
	if(isset($_GET['id'])){
		$mode = "edit";
		$id = $db->escape_string($_GET['id']);
	}
	
	if($mode === "edit"){
		//Load user data from DB
		$sql = "SELECT `".DBC_ALIASES_SOURCE."`, `".DBC_ALIASES_DESTINATION."` from `".DBT_ALIASES."` WHERE `".DBC_ALIASES_ID."` = $id LIMIT 1;";
		
		if(!$result = $db->query($sql)){
			die('There was an error running the query [' . $db->error . ']');
		}
		
		while($row = $result->fetch_assoc()){
			$source = $row[DBC_ALIASES_SOURCE];
			$destination = $row[DBC_ALIASES_DESTINATION];
		}
	}
?>

<h1><?php if($mode === "create") { ?> Create <?php } else {?>Edit <?php } ?>Redirect</h1>

<?php output_messages(); ?>

<p>
Here you can edit a redirect.
</p>

<p>
	<a class="button button-small" href="<?php echo FRONTEND_BASE_PATH; ?>admin/listredirects/">&#10092; Back to redirects list</a>
</p>

<form action="" method="post">	
	<table>
	<tr> <th>Source</th> <th>Destination</th> </tr>
	
	<tr>
		<td>
			<input type="text" name="source" class="textinput" placeholder="Source (single address)" required="required" value="<?php if(isset($source)){echo strip_tags($source);}?>" autofocus/>
		</td>
		
		<td>
			<textarea name="destination" class="textinput" placeholder="Destination (multiple addresses separated by comma possible)" required="required"><?php if(isset($destination)){echo strip_tags($destination);} ?></textarea>
		</td>
	</tr>
	
	</table>
	
	<input name="savemode" type="hidden" value="<?php if(isset($mode)){echo $mode;} ?>"/>
	<input name="id" type="hidden" value="<?php if(isset($id)){echo $id;} ?>"/>
	
	<p>
		<input type="submit" class="button button-small" value="Save settings">
	</p>
</form>