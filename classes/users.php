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
		if(!preg_match("/\@argentic.*\.(net|com)/",$user_id)){
			return array(
				"code" => 400,
				"error" => true,
				"data" => "The User's ID has to be {{whatever}}@argentic.com"
			);
		}
		
		
		
		return $GLOBALS["UTILITIES"]["database"]->query("post_create_user",array(array("user_id" => strtolower( $user_id ))));
	}
	
// //////////////////////////////////////////////////
// RENDER USERS
// //////////////////////////////////////////////////
	public function list(){
		return $GLOBALS["UTILITIES"]["database_read"]->query("get_users");
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

		return $GLOBALS["UTILITIES"]["database_read"]->query("get_user_roles",array(array("email" => $user)));
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
	
// //////////////////////////////////////////////////
// CREATE LINK => Courses
// //////////////////////////////////////////////////
	public function linkUserToCourse($user_id, $course_id){
		//
		// N.B. This creates a link if one does not exist
		// *and* returns the record id for future use...
		//
		
		//
		// Clean this up a little so that
		//
		$data = $GLOBALS["UTILITIES"]["database"]->query("post_user_to_course",array(array("user_id" => $user_id, "course_id" => $course_id),array("user_id" => $user_id, "course_id" => $course_id)));
	
		// Since we're doing a double-query :-(
		// and only need the second result
		// lets just return that part of the return object
		// assuming its given...
		
		if(isset($data["results"])){
			if(gettype($data["results"]) == "array" && count($data["results"]) == 2){
				$data["results"] = array($data["results"][1]);
			}
		}
	
		return $data;

	}
	
// //////////////////////////////////////////////////
// DELETE LINK => Courses
// //////////////////////////////////////////////////
	public function unlinkUserToCourse($user_id, $course_id){
		return $GLOBALS["UTILITIES"]["database"]->query("delete_user_to_course",array(array("user_id" => $user_id, "course_id" => $course_id)));
	}
	
// //////////////////////////////////////////////////
// SET USER'S STATUS ON A COURSE
// //////////////////////////////////////////////////
	public function updateCourseStatus($user_id, $course_id){
		
		$data = $this->linkUserToCourse($user_id, $course_id);
		
		//OMFG NO!
		$id = $data["results"][0]["data"][0]["_id"];
		
		return $GLOBALS["UTILITIES"]["database"]->query("eav_link_users_to_courses",array(array("id" => $id, "value" => $_POST["checked"])));
		
	}

// //////////////////////////////////////////////////
// FANCY => GET A USER'S COURSE LISTING
// //////////////////////////////////////////////////
	public function getUsersCourses($user_id = null){
		if($user_id == null){
			return $GLOBALS["UTILITIES"]["database"]->query("get_user_courses",array(array("user_id" => "")));
		} else {
			return $GLOBALS["UTILITIES"]["database"]->query("get_user_courses",array(array("user_id" => $user_id)));
		}
	}	
	

}
?>