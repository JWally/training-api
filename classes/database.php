<?php
// ////////////////////
//
// File Name: database.php
//
// Purpose:   run queries against sqlite3 databases
// 
//
// ////////////////////



	class DataBase{
		//
		// Theses are the directories where
		// our database file lives, and our queries
		// are to be put...
		//
		private $dbPath;
		private $sqlPath;
		
		// This will be the database we work with...
		private $db;
		
		// Make sure we're working with read-only
		private $readOnly;
		
// /////////////////////////////////////////////
//
// Instantiation function to run some checks, and 
//
// /////////////////////////////////////////////
		public function __construct($dbPath, $sqlPath){
			
			// make sure at a minimum the db folder
			// exists
			//
			if(!is_dir($dbPath)){
				throw new Error("Given Database Folder Does Not Exist");
				die();
			}
			
			// ditto the path to the queries
			//
			if(!is_dir($sqlPath)){
				throw new Error("Given Query Folder Does Not Exist");
				die();
			}
			
			$this->dbPath = $dbPath;
			$this->sqlPath = $sqlPath;
			
		}
		
// /////////////////////////////////////////////
//
// Here is where we connect to s***
//
// /////////////////////////////////////////////
		public function connect($dbName, $readOnly = false){

			// Open said DB
			// and store it on our class level attr
			//
			if($readOnly){
				
				// Flag It
				$this->readOnly = true;
				
				// Cleaner pdo args array
				$pdos = array(
					PDO::SQLITE_ATTR_OPEN_FLAGS => PDO::SQLITE_OPEN_READONLY
				);
				//$this->db = new PDO("sqlite:$this->dbPath/$dbName.sqlite", null, null, [PDO::SQLITE_ATTR_OPEN_FLAGS => PDO::SQLITE_OPEN_READONLY]);
				
				$this->db = new PDO("sqlite:$this->dbPath/$dbName.sqlite", null, null, $pdos);
			} else {
				$this->db = new PDO("sqlite:$this->dbPath/$dbName.sqlite");				
			}
			
			if(!$this->db){
				throw new Error( $this->db->lastErrorMsg() );
				die();
			}
			
			get_defined_constants (true);
			
			$this->db->exec( 'PRAGMA foreign_keys = ON;' );
			
		}
		
// /////////////////////////////////////////////
//
// Here we run queries...
// To Do so, we have to feed the class the path to a sql file...
//
// If we plan on running more than one query; each query
// is separated by a double semi-colon;;
//
// /////////////////////////////////////////////
		public function query($fileName, $args = null){

				//
				// Try opening the file
				// and if it doesn't exist;
				// belch back an error
				// and let the user deal with it there...
				//
				$file = $this->sqlPath . "/" . $fileName . ".sql";

				if(!file_exists($file)){
					return array(
						"error" => true,
						"data" => "$file does not appear to exist"
					);
					die();
				}
				
				
				//
				// In case there's more than one query in our file,
				// let's explode it into multiple queries so they
				// can each be loaded in as series to be run under a transaction
				//
				$query = file_get_contents($this->sqlPath . "/" . $fileName . ".sql");
				$queries = explode(";;",$query);
				$results = array();
				
				//
				// START TRANSACTION
				//
				$this->db->beginTransaction();
				
					//
					// Loop through all the queries that came out of the file
					//
					for($i = 0; $i < count($queries); $i++){
						
						//
						// Ignore if the query is blank
						//
						
						if($queries[$i] !== ""){
							
							//
							// Prep the statement and try to fail gracefully
							// if necessary
							//
							try{
								$stmt = $this->db->prepare($queries[$i]);
							} catch(PDOException $e){
								$this->db->rollback();
								//
								// ///////////////////////////> RETURN
								//
								return array(
									"code" => 400,
									"error" => true,
									"data" => $this->db->errorInfo(),
									"etc" => "Failed on `prepare`"
								);
								die();
							}
							
							//
							// Execute the statement, and try falling over with grace...
							//
							try{
								if($args != null){
									$stmt->execute($args[$i]);
								} else {
									$stmt->execute();
								}
								
								$tmp = $stmt->fetchAll(\PDO::FETCH_ASSOC);	
							
								
								$results []= array(
									"data" => $tmp,
									"last_insert_id" => $this->db->lastInsertId(),
									"affected_row_count" => $stmt->rowCount()
								);
								

							
							} catch(PDOException $e){
								$this->db->rollback();
								//
								// ///////////////////////////> RETURN
								//
								return array(
									"code" => 400,
									"error" => true,
									"data" => $stmt->errorInfo(),
									"etc" => "failed on execute"
								);
								die();
							}
						}
					}
				
				$this->db->commit();
				//
				// END TRANSACTION
				//
			

				//
				// Clean up our results, given that we can issue multiple
				// what-nots...
				//
				if(count($results) == 1){
					//
					// No need to return an array of arrays
					// when we're only getting one result back
					//
					$results = $results[0];
				
					//
					// Figure out if we got any results
					// or hit anything...
					// 
					// If not, 404; else 200
					//

					if(count($results["data"]) == 0 && $results["affected_row_count"] == 0){
						$code = 404;
					} else {
						$code = 200;
					}
				
				} else {
					//
					// I don't know what you're doing and will just assume success
					// and let someone downstream handle it...
					// 
					// Here's your damn code!
					//
					//
					$code = 200;
				}

				//
				// Here's what we're going to return
				// to the user
				//
				
				//
				// ///////////////////////////> RETURN
				//
				return array(
					"code" => $code,
					"error" => false,
					"results" => $results
				);		


		}
		
		
// /////////////////////////////////////////////
//
// Method to let the user run raw SQL against our database.
// .
// ..
// ...
// Yup, this is not just stupid, its pretty goddamn stupid.
// But...in my defense, given where this thing *SHOULD* live (behind a firewall)
// and we're allowing it in Read-Only mode
// and the user base is probably not wanting to break this thing via sql-injection
// and the typical user probably isn't sophisticated enough to intentionally abuse this
//
// net-net; the threat model is pretty low and this is probably an acceptable risk to take
// given the utility it provides
//
// that said, this is dangerous as fuck, so be careful please...
//
// /////////////////////////////////////////////
		public function raw($query){
			//
			// Kill this thing if we ever try and launch it out of readOnly mode
			//
			if(!$this->readOnly){
				return array(
					"code" => 500,
					"error" => true,
					"data" => "Method limited to read-only instances",
					"etc" => "Method limited to read-only instances"
				);
				die();
			}
			
			
			//
			// Prepare 
			//
			
			$results = array();
			
			try{
				$stmt = $this->db->prepare($query);
			} catch(PDOException $e){
				$this->db->rollback();
				//
				// ///////////////////////////> RETURN
				//
				return array(
					"code" => 400,
					"error" => true,
					"data" => $this->db->errorInfo(),
					"etc" => "Failed on `prepare`"
				);
				die();
			}
			
			//
			// Execute the statement, and try falling over with grace...
			//
			try{
				
				$stmt->execute();
				
				$tmp = $stmt->fetchAll(\PDO::FETCH_ASSOC);	
			
				
				$results []= array(
					"data" => $tmp,
					"last_insert_id" => $this->db->lastInsertId(),
					"affected_row_count" => $stmt->rowCount()
				);
				

			
			} catch(PDOException $e){
				$this->db->rollback();
				//
				// ///////////////////////////> RETURN
				//
				return array(
					"code" => 400,
					"error" => true,
					"data" => $stmt->errorInfo(),
					"etc" => "failed on execute"
				);
				die();
			}
			
			
			// //////////////////////////////////
			//
			// /////////
			// //////////////////////////> RETURN
			// /////////
			//
			// //////////////////////////////////
			
			$results = $results[0];
			return array(
				"code" => 200,
				"error" => false,
				"results" => $results
			);		
			
		}
	}

?>