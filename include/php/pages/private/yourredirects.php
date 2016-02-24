<?php

$ownEmail = Auth::getUser()->getEmail();

$redirects = AbstractRedirect::findMultiWhere(
	array(DBC_ALIASES_DESTINATION, 'LIKE', '%'.$ownEmail.'%')
);

function anonymizeEmails($emails, $ownEmail){
	if(is_string($emails) || count($emails) === 1){
		return $ownEmail;
	}

	return array($ownEmail, '&hellip;');
}
?>

	<h1>Redirects to your mailbox</h1>

	<div class="buttons">
		<a class="button" href="<?php echo url('private'); ?>">&#10092; Back to personal dashboard</a>
	</div>

<?php output_messages(); ?>

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
				<td><?php echo formatEmails($redirect->getSource(), str_replace(PHP_EOL, '<br>', FRONTEND_EMAIL_SEPARATOR_TEXT)); ?></td>
				<td><?php echo formatEmails(anonymizeEmails($redirect->getDestination(), $ownEmail), str_replace(PHP_EOL, '<br>', FRONTEND_EMAIL_SEPARATOR_TEXT)); ?></td>
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