<?php
if(isset($_GET['deleted']) && $_GET['deleted'] == "1"){
	add_message("success", "Domain deleted successfully.");
}
else if(isset($_GET['created']) && $_GET['created'] == "1"){
	add_message("success", "Domain created successfully.");
}
else if(isset($_GET['adm_del']) && $_GET['adm_del'] == "1"){
	add_message("fail", "Domain could not be deleted because admin account would be affected.");
}

$sql = "SELECT d.*, COUNT(DISTINCT u.`".DBC_USERS_ID."`) AS `user_count`, COUNT(DISTINCT r.`".DBC_ALIASES_ID."`) AS `redirect_count`
FROM `".DBT_DOMAINS."` AS d
LEFT JOIN `".DBT_USERS."` AS u ON (u.`".DBC_USERS_DOMAIN."` = d.`".DBC_DOMAINS_DOMAIN."`)
LEFT JOIN `".DBT_ALIASES."` AS r ON (r.`".DBC_ALIASES_SOURCE."` LIKE CONCAT('%@', d.`".DBC_DOMAINS_DOMAIN."`))
GROUP BY d.`".DBC_DOMAINS_DOMAIN."`
ORDER BY `".DBC_DOMAINS_DOMAIN."` ASC;";

if(!$result = $db->query($sql)){
	dbError($db->error);
}

?>

<h1>Domains</h1>

<div class="buttons">
	<a class="button" href="<?php echo url('admin/createdomain'); ?>">Create new domain</a>
</div>

<?php output_messages(); ?>

<table class="table">
	<thead>
		<tr>
			<th>Domain</th>
			<th>User count</th>
			<th>Redirect count</th>
			<th></th>
		<tr>
	</thead>
	<tbody>
	<?php while($row = $result->fetch_assoc()): ?>
		<tr>
			<td><?php echo strip_tags($row[DBC_DOMAINS_DOMAIN]); ?></td>
			<td><?php echo strip_tags($row['user_count']); ?></td>
			<td><?php echo strip_tags($row['redirect_count']); ?></td>
			<td>
				<a href="<?php echo url('admin/deletedomain/?id='.$row[DBC_DOMAINS_ID]); ?>">[Delete]</a>
			</td>
		</tr>
	<?php endwhile; ?>
	</tbody>
<?php if ($result->num_rows > 0): ?>
	<tfoot>
	<tr>
		<th><?php echo $result->num_rows;?> Domains</th>
	</tr>
	</tfoot>
<?php endif; ?>
</table>