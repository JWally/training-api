<?php

// ////////////////////////////////////////////
class Roles{
	
	
// //////////////////////////////////////////////////
// CREATE - a new role in the system
// //////////////////////////////////////////////////
	public function create(){
		//
		// Make sure the user gives us a 'name' through $_POST,
		// or fail them.
		//
		if(!isset($_POST["name"])){
			return array(
				"error" => true,
				"code" => 400,
				"data" => "Your request needs a `name`"
			);
		} else {
			return $GLOBALS["UTILITIES"]["database"]->query("post_roles_create",array(array("name" => $_POST["name"])));
		}
	}
	
// //////////////////////////////////////////////////
// RENDER - all roles in the system
// //////////////////////////////////////////////////
	public function list(){
		$data = $GLOBALS["UTILITIES"]["database"]->query("get_roles");
		return $data;
	}
	
	
// //////////////////////////////////////////////////
// UPDATE - the name of a role by row-id
// //////////////////////////////////////////////////
	public function update($id){
		//
		// Make sure the user gives us a 'name' through $_POST,
		// or fail them.
		//
		if(!isset($_POST["name"])){
			return array(
				"error" => true,
				"code" => 400,
				"data" => "Your request needs a `name`"
			);
		} else {
			return $GLOBALS["UTILITIES"]["database"]->query("put_roles_update",array(array("id" => $id,"name" => $_POST["name"])));
		}
	}

// //////////////////////////////////////////////////
// DELETE - a role out of the system by row-id
// //////////////////////////////////////////////////
	public function destroy($id){
		return $GLOBALS["UTILITIES"]["database"]->query("delete_roles",array(array("id" => $id)));
	}
}
?>