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

$redirects = AbstractRedirect::findMultiAll();

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
	<?php foreach($redirects as $redirect): /** @var AbstractRedirect $redirect */ ?>
		<tr>
			<td><?php echo formatEmails($redirect->getSource(), FRONTEND_EMAIL_SEPARATOR_TEXT); ?></td>
			<td><?php echo formatEmails($redirect->getDestination(), FRONTEND_EMAIL_SEPARATOR_TEXT); ?></td>
			<td>
				<a href="<?php echo url('admin/editredirect/?id='.$redirect->getId()); ?>">[Edit]</a>
			</td>
			<td>
				<a href="<?php echo url('admin/deleteredirect/?id='.$redirect->getId()); ?>">[Delete]</a>
			</td>
		</tr>
	<?php endforeach; ?>
	</tbody>
<?php if ($redirects->count() > 0): ?>
		<tfoot>
		<tr>
			<th><?php echo $redirects->count();?> Redirects</th>
		</tr>
		</tfoot>
	<?php endif; ?>
</table>