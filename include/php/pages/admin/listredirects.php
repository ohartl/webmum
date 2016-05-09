<?php

if(isset($_GET['deleted']) && $_GET['deleted'] == "1"){
	Message::getInstance()->success("Redirect deleted successfully.");
}
else if(isset($_GET['created']) && $_GET['created'] == "1"){
	Message::getInstance()->success("Redirect created successfully.");
}
else if(isset($_GET['edited']) && $_GET['edited'] == "1"){
	Message::getInstance()->success("Redirect edited successfully.");
}
else if(isset($_GET['missing-permission']) && $_GET['missing-permission'] == "1"){
	Message::getInstance()->fail("You don't have the permission to edit/delete redirects of that domain.");
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

<?php echo Message::getInstance()->render(); ?>

<?php if($redirects->count() > 0): ?>
	<table class="table">
		<thead>
		<tr>
			<th>Source</th>
			<th>Destination</th>
		<?php if(Config::get('options.enable_user_redirects', false)): ?>
			<th>Created by user</th>
		<?php endif; ?>
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
					<?php echo formatEmailsText($redirect->getConflictingMarkedSource()); ?>
				</td>
				<td><?php echo formatEmailsText($redirect->getDestination()); ?></td>
			<?php if(Config::get('options.enable_user_redirects', false)): ?>
				<td><?php echo $redirect->isCreatedByUser() ? 'Yes' : 'No'; ?></td>
			<?php endif; ?>
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
				<th><?php echo textValue('_ redirect', $redirects->count()); ?></th>
			<?php if(Config::get('options.enable_user_redirects', false)):
				$userRedirectsCount = AbstractRedirect::countWhere(
					array(AbstractRedirect::attr('is_created_by_user'), 1)
				);
				?>
				<th></th>
				<th><?php echo textValue('_ user redirect', $userRedirectsCount); ?></th>
			<?php endif; ?>
			</tr>
		</tfoot>
	</table>
<?php elseif(!(Auth::getUser()->isDomainLimited() && count(Domain::getByLimitedDomains()) === 0)): ?>
	<div class="notification notification-warning">
		There are currently no redirects created you can manage.
	</div>
<?php endif; ?>