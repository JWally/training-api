<?php
// //////////////////////////////////
//
// ** PRE-AMBLE **
//
// //////////////////////////////////

	// Make sure sessions are started and our Time is CST
	date_default_timezone_set('America/Chicago');
	
	ini_set('session.gc_maxlifetime',strtotime('tomorrow midnight') - time());
	
	
	// Globally start session
	session_start();
    

    require_once realpath(__DIR__ . "/classes/router.php");
	require_once realpath(__DIR__ . "/classes/database.php");
	
	require_once realpath(__DIR__ . "/classes/validation.php");
	require_once realpath(__DIR__ . "/classes/users.php");
	require_once realpath(__DIR__ . "/classes/roles.php");
	require_once realpath(__DIR__ . "/classes/courses.php");
	
// //////////////////////////////////
//
// ** GLOBAL REGISTRATION OF CLASS INSTANCES (vomit) **
//
// //////////////////////////////////
	$GLOBALS["UTILITIES"] = array();

// //////////////////////////////////
//
// ** SIMPLE ROUTER CONFIG **
//
// //////////////////////////////////
    $router = new Router();
    $router->setBasePath("/api");
    $router->setBasePath("/api");	
// //////////////////////////////////
//
// ** DATABASE SETUP / CONFIG **
//
// //////////////////////////////////
    $GLOBALS["UTILITIES"]["database"] = new DataBase("./data", "./sql");
	$GLOBALS["UTILITIES"]["database"]->connect("training");
	
    $GLOBALS["UTILITIES"]["database_read"] = new DataBase("./data", "./sql");
	$GLOBALS["UTILITIES"]["database_read"]->connect("training", true);

// //////////////////////////////////
//
// ** MISC  OTHER SET UP **
//
// //////////////////////////////////

	
	// DON'T KNOW THAT I LOVE DOING THIS, BUT WTF...
	//
	$GLOBALS["UTILITIES"]["validation"] = new Validation();
	$GLOBALS["UTILITIES"]["user"] = new User();
	$GLOBALS["UTILITIES"]["roles"] = new Roles();
	$GLOBALS["UTILITIES"]["courses"] = new Courses();


	
// //////////////////////////////////
//
// ** THIS IS MY ROUTER...    **
// ** THERE ARE MANY LIKE IT  **
// ** BUT THIS ONE IS MINE... **
// //////////////////////////////////


// /////////////////////////////////
// CATCH-ALL ROUTE TO HANDLE ANY ROUTER CALL FROM AN XHR OPTIONS REQUEST...
// /////////////////////////////////
    $router->map("OPTIONS", "*", function(){
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: *");
        http_response_code(200);
    });
	

// ////////////////////////////////////////////////////////////////////////////
// ////////////////////////////////////////////////////////////////////////////

// ////////////////////////////////////////////////////////////////////////////
// ////////////////////////////////////////////////////////////////////////////

// USER IDENTIFICATION AND VALIDATION

// ////////////////////////////////////////////////////////////////////////////
// ////////////////////////////////////////////////////////////////////////////

// ////////////////////////////////////////////////////////////////////////////
// ////////////////////////////////////////////////////////////////////////////
	
// /////////////////////////////////
// RESET THE USER'S SESSION
// /////////////////////////////////
    $router->map("GET","/validate/reset", function(){
		$data = $GLOBALS["UTILITIES"]["validation"]->resetSession();
		response($data);
	});
	
	
// /////////////////////////////////
// USER GIVES US AN E-MAIL ADDRESS, WE SEND THEM AN E-MAIL WITH A HASH AND WHAT NOT...
// /////////////////////////////////
    $router->map("POST","/validate", function(){
		$data = $GLOBALS["UTILITIES"]["validation"]->sendLink();
		response($data);
	});	
	
// /////////////////////////////////
// USER GIVES US A HASH AND WE SEE IF ITS VALID OR NOT...
// /////////////////////////////////
    $router->map("GET","/validate", function(){;
		$data = $GLOBALS["UTILITIES"]["validation"]->validateLink();
		response($data);
	});	
	
// /////////////////////////////////
// USER GIVES US A HASH AND WE SEE IF ITS VALID OR NOT...
// /////////////////////////////////
    $router->map("GET","/validate/roles", function(){
		$GLOBALS["UTILITIES"]["validation"]->authorize();
		$data = $GLOBALS["UTILITIES"]["validation"]->loadRoles();
		response($data);
	});	
	
// /////////////////////////////////
// GET USER'S CURRENT VALIDATION STATUS
// /////////////////////////////////
    $router->map("GET","/validate/status", function(){
		response( $GLOBALS["UTILITIES"]["validation"]->getSelfStatus() );
	});


// ////////////////////////////////////////////////////////////////////////////
// ////////////////////////////////////////////////////////////////////////////

// ////////////////////////////////////////////////////////////////////////////
// ////////////////////////////////////////////////////////////////////////////

// USER FUNCTIONS

// ////////////////////////////////////////////////////////////////////////////
// ////////////////////////////////////////////////////////////////////////////

// ////////////////////////////////////////////////////////////////////////////
// ////////////////////////////////////////////////////////////////////////////

// /////////////////////////////////
// USER - CREATE
// /////////////////////////////////
    $router->map("POST","/users/[email:user_id]", function($user_id){
		$GLOBALS["UTILITIES"]["validation"]->authorize(array("admin","SELF"));
		response( $GLOBALS["UTILITIES"]["user"]->create($user_id) );
	});
	
// /////////////////////////////////
// USERS - LIST
// /////////////////////////////////
    $router->map("GET","/users", function(){
		$GLOBALS["UTILITIES"]["validation"]->authorize();
		response( $GLOBALS["UTILITIES"]["user"]->list() );
	});
	
// /////////////////////////////////
// USER - DELETE
// /////////////////////////////////
    $router->map("DELETE","/users/[email:user_id]", function($user_id){
		$GLOBALS["UTILITIES"]["validation"]->authorize("admin");
		response( $GLOBALS["UTILITIES"]["user"]->delete($user_id) );
	});


// /////////////////////////////////
// USER => ROLE - CREATE LINK
// /////////////////////////////////
    $router->map("POST","/users/[email:user_id]/roles/[i:role_id]", function($user_id, $role_id){
		$GLOBALS["UTILITIES"]["validation"]->authorize("admin");
		response( $GLOBALS["UTILITIES"]["user"]->linkUserToRole($user_id, $role_id) );
	});
	
// /////////////////////////////////
// USER => ROLE - DELETE LINK
// /////////////////////////////////
    $router->map("DELETE","/users/[email:user_id]/roles/[i:role_id]", function($user_id, $role_id){
		$GLOBALS["UTILITIES"]["validation"]->authorize("admin");
		response( $GLOBALS["UTILITIES"]["user"]->unlinkUserToRole($user_id, $role_id) );
	});

// /////////////////////////////////	
// USER => ROLES - LIST
// /////////////////////////////////
    $router->map("GET","/user/[email:user]/roles", function($user){
		$GLOBALS["UTILITIES"]["validation"]->authorize(array("admin","SELF"));
		response( $GLOBALS["UTILITIES"]["user"]->getUserRoles($user) );
	});
	
// /////////////////////////////////
// USER => COURSES - LIST
// /////////////////////////////////
    $router->map("GET","/users/[email:user_id]/courses/", function($user_id){
		$GLOBALS["UTILITIES"]["validation"]->authorize(array("admin","SELF"));
		response( $GLOBALS["UTILITIES"]["user"]->getUsersCourses($user_id) );
	});

// /////////////////////////////////
// USER => COURSE - LINK
// /////////////////////////////////
    $router->map("POST","/users/[email:user_id]/courses/[i:course_id]", function($user_id, $course_id){
		$GLOBALS["UTILITIES"]["validation"]->authorize("admin");
		response( $GLOBALS["UTILITIES"]["user"]->linkUserToCourse($user_id, $course_id) );
	});
	
// /////////////////////////////////
// USER => COURSE - UNLINK
// /////////////////////////////////
    $router->map("DELETE","/users/[email:user_id]/courses/[i:course_id]", function($user_id, $course_id){
		$GLOBALS["UTILITIES"]["validation"]->authorize("admin");
		response( $GLOBALS["UTILITIES"]["user"]->unlinkUserToCourse($user_id, $course_id) );
	});
	

// //////////////////////////////////
//  USER => COURSE - UPDATE STATUS
// //////////////////////////////////
    $router->map("POST","/users/[email:user_id]/courses/[i:course_id]/status", function($user_id, $course_id){
		$GLOBALS["UTILITIES"]["validation"]->authorize(array("admin","SELF"));
		response( $GLOBALS["UTILITIES"]["user"]->updateCourseStatus($user_id, $course_id) );
	});
	

// ////////////////////////////////////////////////////////////////////////////
// ////////////////////////////////////////////////////////////////////////////

// ////////////////////////////////////////////////////////////////////////////
// ////////////////////////////////////////////////////////////////////////////

// ROLES FUNCTIONS

// ////////////////////////////////////////////////////////////////////////////
// ////////////////////////////////////////////////////////////////////////////

// ////////////////////////////////////////////////////////////////////////////
// ////////////////////////////////////////////////////////////////////////////



// /////////////////////////////////	
// ROLES - LIST
// /////////////////////////////////
    $router->map("GET","/roles", function(){
		$GLOBALS["UTILITIES"]["validation"]->authorize("admin");
		response( $GLOBALS["UTILITIES"]["roles"]->list() );
	});
	
// /////////////////////////////////	
// ROLE - CREATE
// /////////////////////////////////
    $router->map("POST","/roles", function(){
		$GLOBALS["UTILITIES"]["validation"]->authorize("admin");
		response( $GLOBALS["UTILITIES"]["roles"]->create() );
	});
	
// /////////////////////////////////	
// ROLE - UPDATE
// /////////////////////////////////
    $router->map("POST","/roles/[i:id]", function($id){
		$GLOBALS["UTILITIES"]["validation"]->authorize("admin");
		response( $GLOBALS["UTILITIES"]["roles"]->update($id) );
	});
	
// /////////////////////////////////	
// ROLE - DELETE
// /////////////////////////////////
    $router->map("DELETE","/roles/[i:id]", function($id){
		$GLOBALS["UTILITIES"]["validation"]->authorize("admin");
		response( $GLOBALS["UTILITIES"]["roles"]->destroy($id) );
	});



// ////////////////////////////////////////////////////////////////////////////

// ////////////////////////////////////////////////////////////////////////////

// COURSE FUNCTIONS

// ////////////////////////////////////////////////////////////////////////////

// ////////////////////////////////////////////////////////////////////////////
	


// /////////////////////////////////
// COURSE - CREATE
// /////////////////////////////////
    $router->map("POST","/courses", function(){
		$GLOBALS["UTILITIES"]["validation"]->authorize("admin");
		response( $GLOBALS["UTILITIES"]["courses"]->create() );
	});
	
// /////////////////////////////////
// COURSE - LIST
// /////////////////////////////////
    $router->map("GET","/courses", function(){
		$GLOBALS["UTILITIES"]["validation"]->authorize();
		response( $GLOBALS["UTILITIES"]["courses"]->list() );
	});
	
// /////////////////////////////////
// COURSE - UPDATE NAME
// /////////////////////////////////
    $router->map("POST","/courses/[i:course_id]/name", function($course_id){
		$GLOBALS["UTILITIES"]["validation"]->authorize("admin");
		response( $GLOBALS["UTILITIES"]["courses"]->update($course_id, "name") );
	});
	
// /////////////////////////////////
// COURSE - UPDATE DUE-DATE
// /////////////////////////////////
    $router->map("POST","/courses/[i:course_id]/duedate", function($course_id){
		$GLOBALS["UTILITIES"]["validation"]->authorize("admin");
		response( $GLOBALS["UTILITIES"]["courses"]->update($course_id, "duedate") );
	});
	
// /////////////////////////////////
// COURSE - DELETE
// /////////////////////////////////
    $router->map("DELETE","/courses/[i:course_id]", function($course_id){
		$GLOBALS["UTILITIES"]["validation"]->authorize("admin");
		response( $GLOBALS["UTILITIES"]["courses"]->delete($course_id) );
	});


// /////////////////////////////////
// COURSE => ROLE - LINK
// /////////////////////////////////
    $router->map("POST","/courses/[i:course_id]/roles/[i:role_id]", function($course_id, $role_id){
		$GLOBALS["UTILITIES"]["validation"]->authorize("admin");
		response( $GLOBALS["UTILITIES"]["courses"]->linkCourseToRole($course_id, $role_id) );
	});
	
// /////////////////////////////////
// COURSE => ROLE - unLINK
// /////////////////////////////////
    $router->map("DELETE","/courses/[i:course_id]/roles/[i:role_id]", function($course_id, $role_id){
		$GLOBALS["UTILITIES"]["validation"]->authorize("admin");
		response( $GLOBALS["UTILITIES"]["courses"]->unlinkCourseToRole($course_id, $role_id) );
	});





// ////////////////////////////////////////////////////////////////////////////

// ////////////////////////////////////////////////////////////////////////////

// REPORTING FUNCTIONS

// ////////////////////////////////////////////////////////////////////////////

// ////////////////////////////////////////////////////////////////////////////

// /////////////////////////////////
// REPORTS => RUN NUDE QUERY (satan)
// /////////////////////////////////
    $router->map("POST","/reports/query", function(){
		$GLOBALS["UTILITIES"]["validation"]->authorize("admin");
		response( $GLOBALS["UTILITIES"]["database_read"]->raw($_POST["data"]) );
	});

	
	
// /////////////////////////////////
// /////////////////////////////////
// FUNCTION TO ECHO RESPONSE TO USER
//
// N.B. `data` must have the following attrs:
//
// 1.) `code` => what response code to use
// 2.) `data` => this is what gets json_encoded
//
// /////////////////////////////////
// /////////////////////////////////
function response($data){
	header("content-type: application/json");
	http_response_code($data["code"]);
	
	if(isset($_GET["__redirect__"])){
		echo json_encode($data);		
		header("Location: " . $_GET["__redirect__"]);
	} else {
		echo json_encode($data);			
	}
}


// //////////////////////////////////////////////////////////////////////
// //////////////////////////////////////////////////////////////////////
// //////////////////////////////////////////////////////////////////////
//
// So this is effectively the "server"
// 
// If a match is found, its returned with a function associated with the match
// and...if they were in there...
//
// //////////////////////////////////////////////////////////////////////
// //////////////////////////////////////////////////////////////////////
// //////////////////////////////////////////////////////////////////////
$match = $router->match();

if( $match && is_callable( $match['target'] ) ) {
    header("Access-Control-Allow-Origin: *");
    call_user_func_array( $match['target'], $match['params'] ); 
} else {
    header("X-Error: UNMATCHED ROUTE...GFY!");
	header( 'HTTP/1.1 404 RESOURCE DOES NOT EXIST' );
	header("content-type: application/json");
	echo json_encode(array(
		"data" => "No Resource Found Here",
		"code" => 404,
		"error" => true
	));
}



?>