<?php

if(!isset($_GET['id'])){
	// Redirect id not set, redirect to overview
	redirect("admin/listredirects/");
}

$id = $db->escape_string($_GET['id']);

if(isset($_POST['confirm'])){
	$confirm = $_POST['confirm'];
	
	if($confirm === "yes"){
		$sql = "DELETE FROM `".DBT_ALIASES."` WHERE `".DBC_ALIASES_ID."` = '$id'";
			
		if(!$result = $db->query($sql)){
			dbError($db->error);
		}
		else{
			// Delete redirect successfull, redirect to overview
			redirect("admin/listredirects/?deleted=1");
		}
	}
	else{
		// Choose to not delete redirect, redirect to overview
		redirect("admin/listredirects/");
	}
}

else{
	//Load user data from DB
	$sql = "SELECT `".DBC_ALIASES_SOURCE."`, `".DBC_ALIASES_DESTINATION."` FROM `".DBT_ALIASES."` WHERE `".DBC_ALIASES_ID."` = '$id' LIMIT 1;";
	
	if(!$result = $db->query($sql)){
		dbError($db->error);
	}

	if($result->num_rows !== 1){
		// Redirect does not exist, redirect to overview
		redirect("admin/listredirects/");
	}

	$row = $result->fetch_assoc();

	$source = $row[DBC_ALIASES_SOURCE];
	$destination = $row[DBC_ALIASES_DESTINATION];
}
?>

<h1>Delete redirection?</h1>

<p>
	<table>
	<tr> <th>From</th> <th>To</th> </tr>
	<tr> <td><?php echo $source; ?></td> <td><?php echo $destination; ?></td> </tr>
	</table>
</p>

<p>
	<form action="" method="post">
		<select name="confirm">
			<option value="no">No!</option>
			<option value="yes">Yes!</option>
		</select>
		
		<input type="submit" class="button button-small" value="Okay"/>
	</form>
</p>