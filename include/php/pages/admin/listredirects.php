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
else if(isset($_GET['missing-permission']) && $_GET['missing-permission'] == "1"){
	add_message("fail", "You don't have the permission to edit/delete redirects of that domain.");
}

$redirects = AbstractRedirect::getMultiByLimitedDomains();

?>

	<h1>Redirects</h1>

<?php if(!(Auth::getUser()->isDomainLimited() && count(Domain::getByLimitedDomains()) === 0)): ?>
	<div class="buttons">
		<a class="button" href="<?php echo Router::url('admin/editredirect'); ?>">Create new redirect</a>
	</div>
<?php else: ?>
	<div class="notification notification-warning">
		You are listed for limited access to domains, but it seems there are no domains listed you can access.
	</div>
<?php endif; ?>

<?php output_messages(); ?>

<?php if($redirects->count() > 0): ?>
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
		<?php foreach($redirects as $redirect): /** @var AbstractRedirect $redirect */ ?>
			<tr<?php echo $redirect->getConflictingUsers()->count() > 0 ? ' class="warning"' : ''; ?>>
				<td>
					<?php if($redirect->getConflictingUsers()->count() > 0): ?>
						<strong><?php echo $redirect->getConflictingUsers()->count() === 1 ? 'The marked redirect overrides a mailbox.' : 'The marked redirects override mailboxes.'; ?></strong><br>
					<?php endif; ?>
					<?php echo formatEmails($redirect->getConflictingMarkedSource(), str_replace(PHP_EOL, '<br>', FRONTEND_EMAIL_SEPARATOR_TEXT)); ?>
				</td>
				<td><?php echo formatEmails($redirect->getDestination(), str_replace(PHP_EOL, '<br>', FRONTEND_EMAIL_SEPARATOR_TEXT)); ?></td>
				<td>
					<a href="<?php echo Router::url('admin/editredirect/?id='.$redirect->getId()); ?>">[Edit]</a>
				</td>
				<td>
					<a href="<?php echo Router::url('admin/deleteredirect/?id='.$redirect->getId()); ?>">[Delete]</a>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
		<tfoot>
		<tr>
			<th><?php echo ($redirects->count() > 1) ? $redirects->count().' Redirects' : '1 Redirect'; ?></th>
		</tr>
		</tfoot>
	</table>
<?php elseif(!(Auth::getUser()->isDomainLimited() && count(Domain::getByLimitedDomains()) === 0)): ?>
	<div class="notification notification-warning">
		There are currently no redirects created you can manage.
	</div>
<?php endif; ?>