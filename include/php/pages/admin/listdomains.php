<?php

if(Auth::getUser()->isDomainLimited()){
	redirect('not-allowed');
}

if(isset($_GET['deleted']) && $_GET['deleted'] == "1"){
	add_message("success", "Domain deleted successfully.");
}
else if(isset($_GET['created']) && $_GET['created'] == "1"){
	add_message("success", "Domain created successfully.");
}
else if(isset($_GET['adm_del']) && $_GET['adm_del'] == "1"){
	add_message("fail", "Domain couldn't be deleted because admin account would be affected.");
}
else if(isset($_GET['missing-permission']) && $_GET['missing-permission'] == "1"){
	add_message("fail", "You don't have the permission to delete that domain.");
}

$domains = Domain::findAll();

?>

	<h1>Domains</h1>

<?php if(!Auth::getUser()->isDomainLimited()): ?>
	<div class="buttons">
		<a class="button" href="<?php echo url('admin/createdomain'); ?>">Create new domain</a>
	</div>
<?php endif; ?>

<?php output_messages(); ?>

<?php if($domains->count() > 0): ?>
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
		<tfoot>
		<tr>
			<th><?php echo ($domains->count() > 1) ? $domains->count().' Domains' : '1 Domain'; ?></th>
		</tr>
		</tfoot>
	</table>
<?php else: ?>
	<div class="notification notification-warning">
		There are currently no domains created you can manage.
	</div>
<?php endif; ?>