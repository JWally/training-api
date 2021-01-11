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
		return $GLOBALS["UTILITIES"]["database_read"]->query("get_courses");
	}
	
// //////////////////////////////////////////////////
// UPDATE COURSE
// //////////////////////////////////////////////////
	public function update($course_id, $field){
		
		if(!isset($_POST["value"])){
			return array(
				"code" => 400,
				"error" => true,
				"data" => "The body of your post requires a 'value' parameter"
			);
		}
		
		$field = strtolower($field);
		
		if($field === "name"){
			return $GLOBALS["UTILITIES"]["database"]->query("post_update_course_name",array(array("id" => $course_id, "name" => $_POST["value"])));
		} else {
			//.this
			//..this is
			//...this is fine
			return $GLOBALS["UTILITIES"]["database"]->query("post_update_course_generic",array(array("id" => $course_id, "field" => $field, "value" => $_POST["value"])));
		}
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
	public function linkCourseToRole($course_id, $role_id){
		return $GLOBALS["UTILITIES"]["database"]->query("post_course_to_role",array(array("course_id" => $course_id, "role_id" => $role_id)));
	}
	
// //////////////////////////////////////////////////
// DELETE LINK => Roles
// //////////////////////////////////////////////////
	public function unlinkCourseToRole($course_id, $role_id){
		return $GLOBALS["UTILITIES"]["database"]->query("delete_course_to_role",array(array("course_id" => $course_id, "role_id" => $role_id)));
	}

}
?>