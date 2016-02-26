<h1>Admin Dashboard</h1>

<div class="buttons buttons-horizontal button-large">
	<a class="button" href="<?php echo Router::url('admin/listusers'); ?>">Manage users</a>

	<?php if(!Auth::getUser()->isDomainLimited()): ?>
		<a class="button" href="<?php echo Router::url('admin/listdomains'); ?>">Manage domains</a>
	<?php endif; ?>

	<a class="button" href="<?php echo Router::url('admin/listredirects'); ?>">Manage redirects</a>
</div>