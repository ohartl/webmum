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

$domains = Domain::findAll();

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
	<?php foreach($domains as $domain): /** @var Domain $domain */ ?>
		<tr>
			<td><?php echo $domain->getDomain(); ?></td>
			<td><?php echo $domain->countUsers(); ?></td>
			<td><?php echo $domain->countRedirects(); ?></td>
			<td>
				<a href="<?php echo url('admin/deletedomain/?id='.$domain->getId()); ?>">[Delete]</a>
			</td>
		</tr>
	<?php endforeach; ?>
	</tbody>
<?php if ($domains->count() > 0): ?>
	<tfoot>
	<tr>
		<th><?php echo $domains->count();?> Domains</th>
	</tr>
	</tfoot>
<?php endif; ?>
</table>