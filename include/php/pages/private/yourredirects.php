<?php

$user = Auth::getUser();
$activateUserRedirects = Config::get('options.enable_user_redirects', false) && $user->isAllowedToCreateUserRedirects();

$redirects = $user->getAnonymizedRedirects();

$userRedirectsCount = $user->getSelfCreatedRedirects()->count();
?>

	<h1>Redirects to your mailbox</h1>

	<div class="buttons">
		<a class="button" href="<?php echo Router::url('private'); ?>">&#10092; Back to personal dashboard</a>
	<?php if($activateUserRedirects): ?>
		<?php if($user->canCreateUserRedirects()): ?>
			<a class="button" href="<?php echo Router::url('private/redirect/create'); ?>">Create new redirect</a>
		<?php else: ?>
			<a class="button button-disabled" title="You reached your user redirect limit of <?php echo $user->getMaxUserRedirects(); ?>.">Create new redirect</a>
		<?php endif; ?>
	<?php endif; ?>
	</div>

	<?php echo Message::getInstance()->render(); ?>

<?php if($activateUserRedirects): ?>
	<div class="notifications notification">
		You are allowed to create <strong><?php echo $user->getMaxUserRedirects() === 0 ? 'unlimited user redirects' : textValue('up to _ user redirect', $user->getMaxUserRedirects()); ?></strong> on your own.
	<?php if($user->getMaxUserRedirects() > 0): ?>
		<?php if($user->canCreateUserRedirects()): ?>
			<br><br>You can still create <strong><?php echo textValue('_ more user redirect', $user->getMaxUserRedirects() - $userRedirectsCount); ?></strong>.
		<?php else: ?>
			<br><br>You cannot create anymore redirects as your limit is reached.
			<br>Consider deleting unused redirects or ask an admin to extend your limit.
		<?php endif; ?>
	<?php endif; ?>
	</div>
<?php endif; ?>

<?php if($redirects->count() > 0): ?>
	<table class="table">
		<thead>
			<tr>
				<th>Source</th>
				<th>Destination</th>
			<?php if($activateUserRedirects): ?>
				<th>Created by you</th>
				<th></th>
			<?php endif; ?>
			<tr>
		</thead>
		<tbody>
		<?php foreach($redirects as $redirect): /** @var AbstractRedirect $redirect */ ?>
			<tr>
				<td><?php echo formatEmailsText($redirect->getSource()); ?></td>
				<td><?php echo formatEmailsText($redirect->getDestination()); ?></td>
			<?php if($activateUserRedirects): ?>
				<td><?php echo $redirect->isCreatedByUser() ? 'Yes' : 'No'; ?></td>
				<td>
				<?php if($redirect->isCreatedByUser()): ?>
					<a href="<?php echo Router::url('private/redirect/delete/?id='.$redirect->getId()); ?>">[Delete]</a>
				<?php endif; ?>
				</td>
			<?php endif; ?>
			</tr>
		<?php endforeach; ?>
		</tbody>
		<tfoot>
			<tr>
				<th><?php echo textValue('_ redirect', $redirects->count()); ?></th>
			<?php if($activateUserRedirects): ?>
				<th></th>
				<th>
				<?php if($user->getMaxUserRedirects() === 0): ?>
					<?php echo textValue('_ user redirect', $userRedirectsCount); ?>
				<?php else: ?>
					<?php echo $userRedirectsCount.textValue(' of _ user redirect', $user->getMaxUserRedirects()); ?>
				<?php endif; ?>
				</th>
			<?php endif; ?>
			</tr>
		</tfoot>
	</table>
<?php else: ?>
	<div class="notification notification-warning">
		There are currently no redirects to your mailbox.
	</div>
<?php endif; ?>