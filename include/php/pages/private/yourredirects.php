<?php

$redirects = Auth::getUser()->getAnonymizedRedirects();

?>

	<h1>Redirects to your mailbox</h1>

	<div class="buttons">
		<a class="button" href="<?php echo Router::url('private'); ?>">&#10092; Back to personal dashboard</a>
	</div>

<?php echo Message::getInstance()->render(); ?>

<?php if($redirects->count() > 0): ?>
	<table class="table">
		<thead>
		<tr>
			<th>Source</th>
			<th>Destination</th>
		<tr>
		</thead>
		<tbody>
		<?php foreach($redirects as $redirect): /** @var AbstractRedirect $redirect */ ?>
			<tr>
				<td><?php echo formatEmailsText($redirect->getSource()); ?></td>
				<td><?php echo formatEmailsText($redirect->getDestination()); ?></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
		<tfoot>
		<tr>
			<th><?php echo ($redirects->count() > 1) ? $redirects->count().' Redirects' : '1 Redirect'; ?></th>
		</tr>
		</tfoot>
	</table>
<?php else: ?>
	<div class="notification notification-warning">
		There are currently no redirects to your mailbox.
	</div>
<?php endif; ?>