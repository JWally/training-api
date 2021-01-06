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
		public function connect($dbName){

			// Open said DB
			// and store it on our class level attr
			//
			$this->db = new PDO("sqlite:$this->dbPath/$dbName.sqlite");
			
			if(!$this->db){
				throw new Error( $this->db->lastErrorMsg() );
				die();
			}
			
			$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
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
	}

?>