<?php

if(isset($_GET['deleted']) && $_GET['deleted'] == "1"){
	Message::getInstance()->success("User deleted successfully.");
}
else if(isset($_GET['created']) && $_GET['created'] == "1"){
	Message::getInstance()->success("User created successfully.");
}
else if(isset($_GET['edited']) && $_GET['edited'] == "1"){
	Message::getInstance()->success("User edited successfully.");
}
else if(isset($_GET['adm_del']) && $_GET['adm_del'] == "1"){
	Message::getInstance()->fail("Admin user cannot be deleted.");
}
else if(isset($_GET['missing-permission']) && $_GET['missing-permission'] == "1"){
	Message::getInstance()->fail("You don't have the permission to edit/delete users of that domain.");
}

$users = User::getByLimitedDomains();

?>

<h1>List of all mailbox accounts</h1>

<?php if(!(Auth::getUser()->isDomainLimited() && count(Domain::getByLimitedDomains()) === 0)): ?>
	<div class="buttons">
		<a class="button button-small" href="<?php echo Router::url('admin/edituser'); ?>">Create new user</a>
	</div>
<?php else: ?>
	<div class="notification notification-warning">
		You are listed for limited access to domains, but it seems there are no domains listed you can access.
	</div>
<?php endif; ?>

<?php echo Message::getInstance()->render(); ?>

<?php if($users->count() > 0): ?>
	<table class="table">
		<thead>
			<tr>
				<th>Username</th>
				<th>Domain</th>
			<?php if(Config::get('options.enable_mailbox_limits', false)): ?>
				<th>Mailbox Limit</th>
			<?php endif; ?>
				<th>Redirect count</th>
			<?php if(Config::get('options.enable_user_redirects', false)): ?>
				<th>User Redirects</th>
			<?php endif; ?>
				<th>Role</th>
				<th></th>
				<th></th>
			<tr>
		</thead>
		<tbody>
		<?php foreach($users as $user): /** @var User $user */ ?>
			<tr<?php echo !is_null($user->getConflictingRedirect()) ? ' class="warning"' : ''; ?>>
				<td>
					<?php if(!is_null($user->getConflictingRedirect())): ?>
						<strong>This mailbox is overridden by a redirect.</strong><br>
					<?php endif; ?>
					<?php echo $user->getUsername(); ?>
				</td>
				<td><?php echo $user->getDomain(); ?></td>
			<?php if(Config::get('options.enable_mailbox_limits', false)): ?>
				<td style="text-align: right"><?php echo ($user->getMailboxLimit() > 0) ? $user->getMailboxLimit().' MB' : 'No limit'; ?></td>
			<?php endif; ?>
				<td style="text-align: right">
					<?php echo $user->getRedirects()->count(); ?>
				</td>
			<?php if(Config::get('options.enable_user_redirects', false)): ?>
				<td>
				<?php if($user->getMaxUserRedirects() < 0): ?>
					Not Allowed
				<?php elseif($user->getMaxUserRedirects() > 0): ?>
					Limited (<?php echo $user->getMaxUserRedirects(); ?>)
				<?php else: ?>
					Unlimited
				<?php endif; ?>
				</td>
			<?php endif; ?>
				<td><?php echo ($user->getRole() === User::ROLE_ADMIN) ? 'Admin' : 'User'; ?></td>
				<td>
					<a href="<?php echo Router::url('admin/edituser/?id='.$user->getId()); ?>">[Edit]</a>
				</td>
				<td>
					<a href="<?php echo Router::url('admin/deleteuser/?id='.$user->getId()); ?>">[Delete]</a>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
		<tfoot>
			<tr>
				<th><?php echo textValue('_ user', $users->count()); ?></th>
			</tr>
		</tfoot>
	</table>
<?php elseif(!(Auth::getUser()->isDomainLimited() && count(Domain::getByLimitedDomains()) === 0)): ?>
	<div class="notification notification-warning">
		There are currently no users created you can manage.
	</div>
<?php endif; ?>
