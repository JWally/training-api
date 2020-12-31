<?php

// ////////////////////////////////////////////

class User{

// //////////////////////////////////////////////////
// CREATE USER
// //////////////////////////////////////////////////
	public function create($user_id){
		
		//
		// Make sure the e-mail address is argentic
		//
		if(!preg_match("/\@argenticmgmt\.(net|com)/",$user_id)){
			return array(
				"code" => 400,
				"error" => true,
				"data" => "The User's ID has to be {{whatever}}@argenticmgmt.com"
			);
		}
		
		
		
		return $GLOBALS["UTILITIES"]["database"]->query("post_create_user",array(array("user_id" => strtolower( $user_id ))));
	}
	
// //////////////////////////////////////////////////
// RENDER USERS
// //////////////////////////////////////////////////
	public function list(){
		return $GLOBALS["UTILITIES"]["database"]->query("get_users");
	}
	
// //////////////////////////////////////////////////
// DELETE USER
// //////////////////////////////////////////////////
	public function delete($user_id){
		return $GLOBALS["UTILITIES"]["database"]->query("delete_user",array(array("user_id" => $user_id)));
	}

// //////////////////////////////////////////////////
// List out the roles held by a user.
// N.B. If no user is given, we take what's in the session
// 
// //////////////////////////////////////////////////
	public function getUserRoles($user = NULL){
		
		if($user == NULL){
			$user = $_SESSION["email-address"];
		}

		return $GLOBALS["UTILITIES"]["database"]->query("get_user_roles",array(array("email" => $user)));
	}
	
// //////////////////////////////////////////////////
// CREATE LINK => Roles
// //////////////////////////////////////////////////
	public function linkUserToRole($user_id, $role_id){
		return $GLOBALS["UTILITIES"]["database"]->query("post_user_to_role",array(array("user_id" => $user_id, "role_id" => $role_id)));
	}
	
// //////////////////////////////////////////////////
// DELETE LINK => Roles
// //////////////////////////////////////////////////
	public function unlinkUserToRole($user_id, $role_id){
		return $GLOBALS["UTILITIES"]["database"]->query("delete_user_to_role",array(array("user_id" => $user_id, "role_id" => $role_id)));
	}

}
?>