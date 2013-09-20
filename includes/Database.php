<?php
/**
 * 
 * @author Krunal
 * Database class to handle the database connection
 *
 */
 class Database {
 	private $con;
 	private $result;
 	private static $db = NULL;
 	
 	//Function to make sure only one instance of Database exist per application/call
 	public static function getInstance() {
 		if(self::$db == NULL)
 			self::$db = new Database();
 		
 		return self::$db;
 	}
 	
 	// Constructs the object as well as initiates the connection.
 	private function __construct() {
 		$host = 'localhost';
 		$username = 'root';
 		$password = 'root';
 		$db_name = 'dungeon';
 		$this->con = mysqli_connect($host, $username, $password, $db_name);
 		
 		if(mysqli_connect_errno($this->con))
 			echo 'Failed to connect';
 	}
 	
 	// Function to execute the query based on SQL provided
 	public function ExecQuery($sql) {
 		if(!$this->result = mysqli_query($this->con, $sql))
 			return FALSE;
 		
 		return TRUE;
 	}
 	
 	// Get the query result in form of an object
 	public function GetObject() {
 		return mysqli_fetch_object($this->result);
 	}
 	
 	//Get the query result in form of an associative array
 	public function GetAssoc() {
 		return mysqli_fetch_assoc($this->result);
 	}
 }