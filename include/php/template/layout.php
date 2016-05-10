<!doctype html>
<html>
<head>
	<title>WebMUM</title>
	<meta http-equiv="cleartype" content="on">
	<meta name="MobileOptimized" content="320">
	<meta name="HandheldFriendly" content="True">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
	<link rel=stylesheet href="<?php echo Router::url('include/css/webmum.style.css?v=1.00'); ?>" type="text/css" media=screen>
	<script src="<?php echo Router::url('include/js/slideout.min.js?v=0.1.12'); ?>"></script>
	<script type="text/javascript">
		function generatePassword() {
			var length = <?php echo Config::get('password.min_length', 8) + 1; ?>,
				charset = "abcdefghijklnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!#",
				retVal = "";
			for (var i = 0, n = charset.length; i < length; ++i) {
				retVal += charset.charAt(Math.floor(Math.random() * n));
			}
			return retVal;
		}
	</script>
</head>

<body>
	<div id="header">
		<div class="title"><a href="<?php echo Router::url('/'); ?>">WebMUM - Web Mailserver User Manager</a></div>
		<div class="header-menu">
			<?php if(Auth::hasPermission(User::ROLE_ADMIN)): ?>
				<div class="header-button">
					<a href="<?php echo Router::url('admin'); ?>">[Admin Dashboard]</a>
				</div>
			<?php endif; ?>
			<?php if(Auth::hasPermission(User::ROLE_USER)): ?>
				<div class="header-button">
					<a href="<?php echo Router::url('private'); ?>">[Personal Dashboard]</a>
				</div>
			<?php endif; ?>
			<?php if(Auth::isLoggedIn()): ?>
				<div class="header-button">
					Logged in as <?php echo Auth::getUser()->getEmail(); ?>
					<a href="<?php echo Router::url('logout'); ?>">[Logout]</a>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<div id="content">
		<?php echo $content; ?>
	</div>

	<div id="footer">
		<ul>
			<li>Powered by WebMUM (<a target="_blank" href="https://git.io/vwXhh">https://github.com/ohartl/webmum</a>).</li>
			<li>Developed by Oliver Hartl, Thomas Leister and contributors.</li>
			<li>License: MIT</li>
		</ul>
	</div>
</body>
</html>
