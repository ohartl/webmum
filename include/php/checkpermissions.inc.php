<?php
/*
 * Checks, if the current user has the permission, which is required for an action.
 * $role_req = required role [string]
 * 
 * Returns:
 * true: User has role $role_req
 * false: User doesn't have role $role_req
 * 
 * Possible roles: user, admin
 */

function user_has_permission($role_req){
	global $user;
	if($user->isLoggedIn() === true){
		// User is logged in. Check permissions
		// To be done. Load user role from database or better: save in SESSION
		if($role_req === "user"){
			if($user->getRole() == "user" || $user->getRole() == "admin"){
				return true;
			}
			else{
				return false;
			}
		}
		else if($role_req === "admin"){
			if($user->getRole() == "admin"){
				return true;
			}
		}
	}
	else{
		// User is not logged in => public user => no permissions
		return false;
	}
}

?>