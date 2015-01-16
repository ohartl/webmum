<?php
/*
 * #################### This is WebMUM Version 0.1.9 ######################
 * 
 * Project on GitHub: https://github.com/ThomasLeister/webmum
 * Author's Blog: https://thomas-leister.de
 * 
 * Please report bugs on GitHub.
 * 
 * Copyright (C) 2014 Thomas Leister
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */


define("BACKEND_BASE_PATH", preg_replace("#index.php#", "", $_SERVER['SCRIPT_FILENAME']));
require_once 'include/php/default.inc.php';

require_once 'include/php/template/header.php';

function load_page($p){
	
	if(preg_match("/^\/private(.*)$/", $p) == 1){
		// Page is user page
		if(user_has_permission("user")){
			switch($p){
				case "/private/":
					return "include/php/pages/private/start.php";
					break;
				case "/private/changepass/":
					return "include/php/pages/private/changepass.php";
					break;
				default:
					return "include/php/pages/404.php";
			}
		}
		else{ return "include/php/pages/not-allowed.php"; }
	}
	
	else if(preg_match("/^\/admin(.*)$/", $p) == 1){
		// Page is admin page
		if(user_has_permission("admin")){
			switch($p){
				case "/admin/":
					return "include/php/pages/admin/start.php";
					break;
				case "/admin/listusers/":
					return "include/php/pages/admin/listusers.php";
					break;
				case "/admin/edituser/":
					return "include/php/pages/admin/edituser.php";
					break;
				case "/admin/deleteuser/":
					return "include/php/pages/admin/deleteuser.php";
					break;
				case "/admin/listdomains/":
					return "include/php/pages/admin/listdomains.php";
					break;
				case "/admin/deletedomain/":
					return "include/php/pages/admin/deletedomain.php";
					break;
				case "/admin/createdomain/":
					return "include/php/pages/admin/createdomain.php";
					break;
				case "/admin/listredirects/":
					return "include/php/pages/admin/listredirects.php";
					break;
				case "/admin/editredirect/":
					return "include/php/pages/admin/editredirect.php";
					break;
				case "/admin/deleteredirect/":
					return "include/php/pages/admin/deleteredirect.php";
					break;
				default:
					return "include/php/pages/404.php";
			}
		}
		else{ return "include/php/pages/not-allowed.php"; }
	}
	
	else{
		// Page is public accessible
		switch($p){
			case "/login/":
				return "include/php/pages/login.php";
				break;
			case "/logout/":
				return "include/php/pages/logout.php";
				break;
			case "/":
				return "include/php/pages/start.php";
				break;
			default:
				return "include/php/pages/404.php";
		}
	}
}


$path = $_SERVER["REQUEST_URI"];
// Remove GET Parameters
$path = preg_replace('/\?.*/', '', $path);
// Remove prescending directory part e.g. webmum/ defined in SUBDIR
$path = preg_replace("#".SUBDIR."#", '', $path);

// Webserver should add trailing slash, but if there is no trailing slash for any reason, add one ;)
if(strrpos($path,"/") != strlen($path)-1){
	$path = $path."/";
}


/*
 * Include page content here
 */

include load_page($path);


/*
 * End of dynamic content
 */

require_once 'include/php/template/footer.php';
include_once 'include/php/db_close.inc.php';
?>


