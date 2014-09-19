<!doctype html>
<html>
	<head>
		<title>WebMUM</title>
		<link rel=stylesheet href="<?php echo FRONTEND_BASE_PATH; ?>include/css/style.css" type="text/css" media=screen>
	</head>
	
	<body>
		<div id="header">
			<div class="title"><a href="<?php echo FRONTEND_BASE_PATH; ?>">WebMUM - Web Mailsystem User Manager</a></div>
			<div class="header-menu">
				<?php if(user_has_permission("admin")){ ?><div class="header-button"> <a href="<?php echo FRONTEND_BASE_PATH ?>admin/">[Admin Dashboard]</a> </div> <div class="header-button"> <a href="<?php echo FRONTEND_BASE_PATH ?>private/">[Personal Dashboard]</a> </div><?php } ?>
				<?php if($user->isLoggedIn()){?><div class="header-button">Logged in as <?php echo $_SESSION['email']; ?> <a href="<?php echo FRONTEND_BASE_PATH ?>logout/">[Logout]</a></div><?php }?>
			</div>
		</div>
		
		<div id="content"> <!-- Opening content -->