<?php

if(isset($_GET['deleted']) && $_GET['deleted'] == "1"){
	add_message("success", "Redirect deleted successfully.");
}
else if(isset($_GET['created']) && $_GET['created'] == "1"){
	add_message("success", "Redirect created successfully.");
}
else if(isset($_GET['edited']) && $_GET['edited'] == "1"){
	add_message("success", "Redirect edited successfully.");
}

if(defined('DBC_ALIASES_MULTI_SOURCE')){
	$sql = "SELECT r.* FROM (
		SELECT
			group_concat(g.`".DBC_ALIASES_ID."` ORDER BY g.`".DBC_ALIASES_ID."` SEPARATOR ',') AS `".DBC_ALIASES_ID."`,
			group_concat(g.`".DBC_ALIASES_SOURCE."` SEPARATOR ',') AS `".DBC_ALIASES_SOURCE."`,
			g.`".DBC_ALIASES_DESTINATION."`,
			g.`".DBC_ALIASES_MULTI_SOURCE."`
		FROM `".DBT_ALIASES."` AS g
		WHERE g.`".DBC_ALIASES_MULTI_SOURCE."` IS NOT NULL
		GROUP BY g.`".DBC_ALIASES_MULTI_SOURCE."`
	UNION
		SELECT
			s.`".DBC_ALIASES_ID."`,
			s.`".DBC_ALIASES_SOURCE."`,
			s.`".DBC_ALIASES_DESTINATION."`,
			s.`".DBC_ALIASES_MULTI_SOURCE."`
		FROM `".DBT_ALIASES."` AS s
		WHERE s.`".DBC_ALIASES_MULTI_SOURCE."` IS NULL
	) AS r
	ORDER BY `".DBC_ALIASES_SOURCE."` ASC";
}
else{
	$sql = "SELECT `".DBC_ALIASES_ID."`, `".DBC_ALIASES_SOURCE."`, `".DBC_ALIASES_DESTINATION."` FROM `".DBT_ALIASES."` ORDER BY `".DBC_ALIASES_SOURCE."` ASC;";
}

if(!$result = $db->query($sql)){
	dbError($db->error);
}

?>

<h1>Redirects</h1>

<?php output_messages(); ?>

<div class="buttons">
	<a class="button" href="<?php echo url('admin/editredirect'); ?>">Create new redirect</a>
</div>

<table class="table">
	<thead>
		<tr>
			<th>Source</th>
			<th>Destination</th>
			<th></th>
			<th></th>
		<tr>
	</thead>
	<tbody>
	<?php while($row = $result->fetch_assoc()): ?>
		<tr>
			<td><?php echo strip_tags(formatEmails($row[DBC_ALIASES_SOURCE], FRONTEND_EMAIL_SEPARATOR_TEXT)); ?></td>
			<td><?php echo strip_tags(formatEmails($row[DBC_ALIASES_DESTINATION], FRONTEND_EMAIL_SEPARATOR_TEXT)); ?></td>
			<td>
				<a href="<?php echo url('admin/editredirect/?id='.$row[DBC_ALIASES_ID]); ?>">[Edit]</a>
			</td>
			<td>
				<a href="<?php echo url('admin/deleteredirect/?id='.$row[DBC_ALIASES_ID]); ?>">[Delete]</a>
			</td>
		</tr>
	<?php endwhile; ?>
	</tbody>
<?php if ($result->num_rows > 0): ?>
		<tfoot>
		<tr>
			<th><?php echo $result->num_rows;?> Redirects</th>
		</tr>
		</tfoot>
	<?php endif; ?>
</table>