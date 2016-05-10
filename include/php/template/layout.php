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
		document.addEventListener('DOMContentLoaded', function(){
			var slideout = new Slideout({
				'panel': document.getElementById('content'),
				'menu': document.getElementById('mobile'),
				'padding': 256,
				'tolerance': 70,
			});

			// Toggle button
			document.querySelector('.menu-toggle').addEventListener('click', function() {
				slideout.toggle();
			});
		}, false);
	</script>
</head>

<body>
	<?php if(Auth::isLoggedIn()): ?>
		<div id="mobile" class="grid">
			<ul class="unit">
				<li><a href="<?php echo Router::url('admin'); ?>">[Admin Dashboard]</a></li>
				<li><a href="<?php echo Router::url('private'); ?>">[Personal Dashboard]</a></li>
				<li><?php echo Auth::getUser()->getEmail(); ?>
					<a href="<?php echo Router::url('logout'); ?>">[Logout]</a>
				</li>
			</ul>
		</div>
	<?php endif; ?>

	<div id="header" class="grid">
		<nav class="unit no-gutters">
			<div class="title">
				<a href="<?php echo Router::url('/'); ?>">WebMUM<span class="hide-on-mobiles"> - Web Mailserver User Manager</span></a>
			</div>
			<?php if(Auth::isLoggedIn()): ?>
				<ul class="pull-left header-menu hide-on-mobiles">
					<li><a href="<?php echo Router::url('admin'); ?>">[Admin Dashboard]</a></li>
					<li><a href="<?php echo Router::url('private'); ?>">[Personal Dashboard]</a></li>
				</ul>
				<div class="pull-right header-button hide-on-mobiles">
						<p class="header-text">Logged in as <?php echo Auth::getUser()->getEmail(); ?></p>
						<a href="<?php echo Router::url('logout'); ?>" class="pull-right">[Logout]</a>
				</div>
				<div class="header-button pull-right only-mobiles">
					<div class="menu-toggle">Menu<img class="menu-icon" src="<?php echo Router::url('include/img/menu-icon.svg?v=1.0'); ?>"></div>
				</div>
			<?php endif; ?>
		</nav>
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
