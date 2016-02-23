<?php

// Home
Router::addGet('/', 'include/php/pages/start.php');

/**
 * Auth
 */
Router::addMixed('/login', 'include/php/pages/login.php');
Router::addGet('/logout', 'include/php/pages/logout.php');

/**
 * Private area
 */
Router::addGet('/private', 'include/php/pages/private/start.php', User::ROLE_USER);
Router::addMixed('/private/changepass', 'include/php/pages/private/changepass.php', User::ROLE_USER);


/**
 * Admin area
 */
Router::addGet('/admin', 'include/php/pages/admin/start.php', User::ROLE_ADMIN);

// Users / Mailboxes
Router::addGet('/admin/listusers', 'include/php/pages/admin/listusers.php', User::ROLE_ADMIN);
Router::addMixed('/admin/edituser', 'include/php/pages/admin/edituser.php', User::ROLE_ADMIN);
Router::addMixed('/admin/deleteuser', 'include/php/pages/admin/deleteuser.php', User::ROLE_ADMIN);

// Domains
Router::addGet('/admin/listdomains', 'include/php/pages/admin/listdomains.php', User::ROLE_ADMIN);
Router::addMixed('/admin/deletedomain', 'include/php/pages/admin/deletedomain.php', User::ROLE_ADMIN);
Router::addMixed('/admin/createdomain', 'include/php/pages/admin/createdomain.php', User::ROLE_ADMIN);

// Redirects
Router::addGet('/admin/listredirects', 'include/php/pages/admin/listredirects.php', User::ROLE_ADMIN);
Router::addMixed('/admin/editredirect', 'include/php/pages/admin/editredirect.php', User::ROLE_ADMIN);
Router::addMixed('/admin/deleteredirect', 'include/php/pages/admin/deleteredirect.php', User::ROLE_ADMIN);