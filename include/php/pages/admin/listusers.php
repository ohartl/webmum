<?php 

if(isset($_GET['deleted']) && $_GET['deleted'] == "1"){
	add_message("success", "User deleted successfully."); 
}
else if(isset($_GET['created']) && $_GET['created'] == "1"){
	add_message("success", "User created successfully.");
}
else if(isset($_GET['edited']) && $_GET['edited'] == "1"){
	add_message("success", "User edited successfully.");
}
else if(isset($_GET['adm_del']) && $_GET['adm_del'] == "1"){
	add_message("fail", "Admin user cannot be deleted.");
}

$sql = "SELECT * FROM `".DBT_USERS."` ORDER BY `".DBC_USERS_DOMAIN."`, `".DBC_USERS_USERNAME."` ASC;";

if(!$result = $db->query($sql)){
	dbError($db->error);
}

?>

<h1>List of all mailbox accounts</h1>

<div class="buttons">
	<a class="button button-small" href="<?php echo url('admin/edituser'); ?>">Create new user</a>
</div>

<?php output_messages(); ?>

<table class="table">
	<thead>
		<tr>
			<th>Username</th>
			<th>Domain</th>
		<?php if(defined('DBC_USERS_MAILBOXLIMIT')): ?>
			<th>Mailbox Limit</th>
		<?php endif; ?>
			<th>Role</th>
			<th></th>
			<th></th>
		<tr>
	</thead>
	<tbody>
	<?php while($row = $result->fetch_assoc()): ?>
		<tr>
			<td><?php echo strip_tags($row[DBC_USERS_USERNAME]); ?></td>
			<td><?php echo strip_tags($row[DBC_USERS_DOMAIN]); ?></td>
		<?php if(defined('DBC_USERS_MAILBOXLIMIT')):
			$limit = strip_tags($row[DBC_USERS_MAILBOXLIMIT]);
			?>
			<td style="text-align: right"><?php echo ($limit > 0) ? $limit.' MB' : 'No limit'; ?></td>
		<?php endif;?>
			<td><?php echo in_array($row[DBC_USERS_USERNAME].'@'.$row[DBC_USERS_DOMAIN], $admins) ? 'Admin' : 'User'; ?></td>
			<td>
				<a href="<?php echo url('admin/edituser/?id='.$row[DBC_USERS_ID]); ?>">[Edit]</a>
			</td>
			<td>
				<a href="<?php echo url('admin/deleteuser/?id='.$row[DBC_USERS_ID]); ?>">[Delete]</a>
			</td>
		</tr>
	<?php endwhile; ?>
	</tbody>
<?php if ($result->num_rows > 0): ?>
	<tfoot>
		<tr>
			<th><?php echo $result->num_rows;?> User</th>
		</tr>
	</tfoot>
<?php endif; ?>
</table>