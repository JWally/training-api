<?php
// /////////////////////////////////////////////////////////////////////////
// /////////////////////////////////////////////////////////////////////////
// /////////////////////////////////////////////////////////////////////////
// /////////////////////////////////////////////////////////////////////////

// N.B. This should probably go somewhere else in a separate file; but
// for brevity...this works

	class Validation{

		private $config;

	//
	// This is our constructor function that runs whenever you create a new instance
	// of this class.
	//
	// For now, we'll make the config data "global" here...
	//
		function __construct(){
			//
			// Open our config file, and turn it into a PHP object
			// (no error handling because...fuggit...)
			//
			$this->config = json_decode(file_get_contents("C:/phpconf/training/config.json"),true);
		}


	//
	// Quick and dirty function to build out the JSON
	// we're going to send to postage-app (which sends e-mails)
	//
		private function build_mail($recipients, $subject, $from, 
			$content = false, 
			$attachments = false, 
			$template = false,
			$variables = false	
		){
			
			// Body of request
			$body = array();
			
			$body["api_key"] = $this->config["postage-app"]["api-key"];
			$body["uid"] = md5(random_int(1,100000));
			$body["arguments"] = array(
				"recipients" => $recipients,
				"headers" => array(
					"subject" => $subject,
					"from" => $from
				)
			);
			
			if($content){
				$body["arguments"]["content"] = $content;
			}
			
			if($attachments){
				$body["arguments"]["attachments"] = $attachments;
			}
			
			if($template){
				$body["arguments"]["template"] = $template;
			}
			
			if($variables){
				$body["arguments"]["variables"] = $variables;
			}
			//
			// Send it and return whatever it gives up...
			//
			return $this->send_mail($body);
		}
		
		
	//
	// This is what actually sends data across the wire...
	// we're going to send to postage-app (which sends e-mails)
	//
		private function send_mail($body){
			
			// encode our data for transport
			$post_data = json_encode($body);
			
			// This is the address we want to send our data to
			$crl = curl_init("https://api.postageapp.com/v.1.1/send_message");
			
			// ???
			curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
			
			// ???
			curl_setopt($crl, CURLINFO_HEADER_OUT, true);
			
			// We're posting data so we need to send it as such
			curl_setopt($crl, CURLOPT_POST, true);
			
			// This is the acutal data we're sendign
			curl_setopt($crl, CURLOPT_POSTFIELDS, $post_data);

			// Set HTTP Header for POST request 
			curl_setopt($crl, CURLOPT_HTTPHEADER, array(
			  'Content-Type: application/json',
			  'Content-Length: ' . strlen($post_data))
			);

			// ...SEND IT!
			$result = curl_exec($crl);

			// handle curl error
			if ($result === false) {
				//
				// Return the error we got
				//
				return json_encode('Curl error: ' . curl_error($crl));
			} else {
				//
				// k.i.s.s: return what we got
				//
				return($result);
			}
			// Close cURL session handle
			curl_close($crl);
		}
		
		
		
	//
	// Publically accessable interface to the class.
	// This is what developers can use without worrying about the rest
	// of the class and its innards
	//
		private function send($recipients, $subject, $from, 
			$content = false, 
			$attachments = false, 
			$template = false,
			$variables = false	
		){
			$this->build_mail($recipients, $subject, $from, $content,$attachments,$template,$variables);
		}
		
		
	public function resetSession(){
		session_unset();
		session_destroy();
		session_start();
		
		$_SESSION["session-eol"] = strtotime('tomorrow midnight');
		$_SESSION["validation-status"] = "new";
		
		return array(
			"error" => false,
			"code" => 200,
			"data" => "good to go"
		);
	}	
		
	//
	// So this is what the API'll use to send mail
	// We'll assume that an e-mail address is in the POST variable
	// until it bites us in the ass...
	//
		public function sendLink(){
			
		//
		// So here's what we're trying to do here:
		// 
		// We want to know that the user who came to this page
		// owns the e-mail they say that they own. Here's how we do it:
		//
		// 1.) Create a random number and leave it with the client in their
		//     Session
		//
		// 2.) E-Mail them with a hash of that random number PLUS our
		//     Secret Key (in the form of a link with a query string)
		//
		//     (e.g. http://example.com/validate?hash=k3y804RDC4T)
		//
		// 3.) When the client clicks that link, our server will take
		//     the random number in the client's session, combine it 
		//     with our secret-key and if it's hash equals what the client
		//     gave us; mark them as valid
		//
		// 4.) This sounds way more complicated than it really is. Its not
		//     that bad.
		//
		
			//
			// Reset our session so we don't have to deal with any shenanigans...
			//
			$this->resetSession();
		

			if(isset($_POST["inputEmail"])){
				//
				// Make sure they are with argenticmgmt.net or .com
				//
				if(preg_match("/\@argenticmgmt\.(net|com)/",strtolower($_POST["inputEmail"]))){
					
					

					//
					// Load a bunch of variables into the user's session
					//
					$_SESSION["email-address"] = strtolower($_POST["inputEmail"]);
				
					$_SESSION["validation-status"] = "pending";
					
					$_SESSION["validation-salt"] = md5(random_int(1,100000));

					// This is what we'll send in our e-mail for them to click...
					$hash = md5($this->config["validation-key"] . "" . $_SESSION["validation-salt"]);

					// So it looks like we've made it through everything and nobody's shat themselves...super!
					//
					// Let's try sending the user an e-mail
					//
					$link_url = "";
					$link_url .= $this->config["domain"];
					$link_url .= $this->config["root-url"];
					$link_url .= "/validate?hash=$hash&__redirect__=";
					$link_url .= $this->config["domain"];
					$link_url .= "/training";
					
					
					$mail = $this->send(
						array($_SESSION["email-address"]), 
						"Here Is Your Validation Link...", 
						$this->config["postage-app"]["e-mail-sender"], 
						false,
						false,
						"ez_template",
						array(
							"link" => $link_url
						)
					);
					
					return array(
						"code" => 200,
						"error" => false,
						"data" => $_SESSION
					);
					
				} else {
					//
					// Fail the user for providing a bullshit e-mail address...
					//
					return array(
						"code" => 400,
						"error" => true,
						"data" => "E-Mail Given does not fit pattern required. Please use valid e-mail address."
					);
				}
			} else {
			//
			// Fail the user for not providing a required field
			//
				return array(
					"code" => 400,
					"error" => true,
					"data" => "No E-Mail Given. `inputEmail` is a required field"
				);
			}
			
		}
		
		
	//
	// Here is how we validate the hash we got in our e-mail.
	// It sets up some stuff in the user's session...
	//
		public function validateLink(){
			
			if(isset($_GET["hash"])){
				$hash = $_GET["hash"];
				//
				// Quick check that math works
				//
				if(md5($this->config["validation-key"] . "" . $_SESSION["validation-salt"]) == $hash){
					//
					// we're good to go...
					//
					$_SESSION["validation-status"] = "valid";
					
					//
					// THE USER PASSED
					// -------------------
					// CREATE THE USER IN THE DATABASE
					// IF THEY DON'T ALREADY EXIST
					//
					// LOAD ROLE DATA ONTO THE SESSION
					//
					//
					
					$GLOBALS["UTILITIES"]["user"]->create($_SESSION["email-address"]);
					$this->loadRoles();
					
					return $this->getSelfStatus();
					
				} else {
					//
					// Fail the user for giving us an invalid hash
					//
					return array(
						"code" => 400,
						"error" => true,
						"data" => "The Provided hash is invalid. Please try harder next time!"
					);
				}
			} else {
				//
				// Fail the user for not giving us a hash
				//
				return array(
					"code" => 400,
					"error" => true,
					"data" => "This resource requires you provide a hash...DIE!"
				);
			}
		}

	//
	// Look up the user's roles from the database, and load them into our session...
	//
	//
		public function loadRoles(){
		
			//
			// Collect data from the database
			//
			$data = $GLOBALS["UTILITIES"]["database"]->query("get_user_roles",array(array("email" => $_SESSION["email-address"])));
		
			if($data["error"] == false){
				
				$_SESSION["roles"] = array();
				
				forEach($data["results"]["data"] as $key => $val){
					$_SESSION["roles"][$val["role_name"]] = true;
				}
				
				//
				// And for good measure, we'll put our 
				// e-mail as a role too...
				//
				$_SESSION["roles"][$_SESSION["email-address"]] = true;
				
				return array(
					"code" => 200,
					"error" => false,
					"data" => NULL
				);
				
			} else {
				return $data;
			}
		}


// //////////////////////////////////////////////////
// Manipulate the user's session,
// so we can know who they are,
// if they're valid,
// ...etc...
// //////////////////////////////////////////////////
    public function getSelfStatus(){
		//
		// Hard limit to kill the session on the server side
		// at midnight every night...
		//
		if(isset($_SESSION["session-eol"])){
			//
			// Yay, lets make sure its not too old...
			//
			if($_SESSION["session-eol"] < time()){
				
				//
				// Uh-oh, the session is stale...
				// nuke it, and reset
				//
				$this->resetSession();
			} else {
				//
				// Do nothing else clause...
				//
			}
		} else {
			//
			// So I notice its your first time here...
			// We're gonna set a session and maybe tag you
			// with some crap...
			//
				$this->resetSession();
		}
		
		//error_log(var_export($_SESSION));
		return array(
			"error" => false,
			"code" => 200,
			"data" => $_SESSION
		);
    }



	//
	// Authorize is a utility function that doesn't explicitly return anything,
	// but kicks users around based on if they're valid, and if they should have access
	// to a resource or not
	//
	public function authorize($roles = NULL){
		header("content-type: application/json");	
		
		
		//
		// Make sure shit is kept real...
		//
		$this->getSelfStatus();
		
		//
		// First, make sure the session is valid. If not,
		// Bomb them out
		//
		if($_SESSION["validation-status"] !== "valid"){
			http_response_code(403);
			echo json_encode( array(
				"error" => true,
				"data" => "Your Session is Invalid. Please Re-Validate."
			));
			die(); return 0;
		} else {
			//
			// If they didn't set the roles variable, no need to go further...
			// return true and move on...
			//
			if($roles === NULL){
				return true;
			} else if(gettype($roles) == "string"){
				
				//
				// Syntatic sugar
				//
				if($roles == "SELF"){
					$roles = $_SESSION["email-address"];
				}
				
				//
				// If we're checking for a single string, and that string is somewhere in the
				// ROLES; we're good. Return True.
				//
				if( isset($_SESSION["roles"][$roles]) ){
					return true;
				}
			} else if(gettype($roles === "array")){
				forEach($roles as $role){
					
					if($role == "SELF"){
						$role = $_SESSION["email-address"];
					}
					
					if( isset($_SESSION["roles"][$role]) ){
						return true;
					}
				}
			}
				// OH SHIT!
				// WE DONE FELL THROUGH...ERGO, THE REQUIRED CREDENTIALS ARE NOT MET...
				//
				http_response_code(403);
				echo json_encode( array(
					"error" => true,
					"data" => "You Don't Have Credentials To View This Resource"
				));
				die();
				return 0;
			
		}
		
	}

}

?>