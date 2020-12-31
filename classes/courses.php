<?php
//
// ////////////////////////////////////////////

class Courses{

// //////////////////////////////////////////////////
// CREATE COURSE
// //////////////////////////////////////////////////
	public function create(){
		
		if(!isset($_POST["name"])){
			return array(
				"code" => 400,
				"error" => true,
				"data" => "The body of your post requires a 'name' parameter"
			);
		} else {
			$name = $_POST["name"];
		}
		
		return $GLOBALS["UTILITIES"]["database"]->query("post_create_course",array(array("name" => $name)));
	}
	
// //////////////////////////////////////////////////
// RENDER COURSES
// //////////////////////////////////////////////////
	public function list(){
		return $GLOBALS["UTILITIES"]["database"]->query("get_courses");
	}
	
// //////////////////////////////////////////////////
// DELETE COURSE
// //////////////////////////////////////////////////
	public function delete($course_id){
		return $GLOBALS["UTILITIES"]["database"]->query("delete_course",array(array("course_id" => $course_id)));
	}

	
// //////////////////////////////////////////////////
// CREATE LINK => Roles
// //////////////////////////////////////////////////
	public function linkUserToRole($course_id, $role_id){
		return $GLOBALS["UTILITIES"]["database"]->query("post_course_to_role",array(array("course_id" => $course_id, "role_id" => $role_id)));
	}
	
// //////////////////////////////////////////////////
// DELETE LINK => Roles
// //////////////////////////////////////////////////
	public function unlinkUserToRole($course_id, $role_id){
		return $GLOBALS["UTILITIES"]["database"]->query("delete_course_to_role",array(array("course_id" => $course_id, "role_id" => $role_id)));
	}

}
?>