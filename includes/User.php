<?php
/**
 * 
 * @author Krunal
 * User Class to handle users currently logged in
 * 
 */
require_once 'Database.php';
require_once 'Builder.php';

class User {
	private static $user = NULL;
	// The parameters to set the maximum size of the world
	const MAX_WIDTH = 3;
	const MAX_LENGTH = 3;
	const MAX_HEIGHT = 3;
	private $username;
	private $curr_room;
	private $password;
	
	// To make sure there is only one user object per application/call
	public static function getInstance() {
		if(self::$user == NULL)
			self::$user = new User();
		
		return self::$user;
	}
	
	private function __construct() {
		
	}
	
	public function setUsername($username) {
		$this->username = $username;
	}
	
	/**
	 * Returns the username of the user logged in
	 * @return Ambigous <NULL, string>
	 */
	public function getUsername() {
		return $this->username;
	}
	
	/**
	 * Function to set the room for the user object as well as updates the room coordinates field in the users table
	 */ 
	public function setRoom($room) {
		$this->curr_room = $room;
		$this->updateRoom();
	}
	
	/**
	 * Function to create a room with random coordinates limited to the max parameter set and assigns it to the user
	 * @param integer $x
	 * @param integer $y
	 * @param integer $z
	 */
	public function createRoom($x=FALSE, $y=FALSE, $z=FALSE) {
		if($x===FALSE || $y===FALSE || $z===FALSE) {
			do {
				$x = rand(0, self::MAX_HEIGHT);
				$y = rand(0, self::MAX_WIDTH);
				$z = rand(0, self::MAX_LENGTH);
			} 
			while(Room::checkOpacity($x, $y, $z) != TRUE);
		}
		$builder = new RoomBuilder();
		$room = $builder->setX($x)->setY($y)->setZ($z)->buildRoom();
		$this->setRoom($room);
	}
	
	/**
	 * Returns the current room for the user
	 * @return Ambigous <NULL, Room>
	 */
	public function getRoom() {
		return $this->curr_room;
	}
	
	/**
	 * Function to get information from the DB about the user logged in
	 * @param string $username
	 * @return array
	 */
	public function getUser($username) {
		$db = Database::getInstance();
		$sql = "SELECT * FROM users WHERE username = '$username'";
		$db->ExecQuery($sql);
		$user = $db->GetAssoc();
		$this->username = $user['username'];
		$this->password = $user['password'];
		
		if(strlen($user['room_coord']) < 3) {
			$x = 0;
			$y = $user['room_coord'][0];
			$z = $user['room_coord'][1];
		}
		else {
			$x = $user['room_coord'][0];
			$y = $user['room_coord'][1];
			$z = $user['room_coord'][2];
		}
		$builder = new RoomBuilder();
		$this->curr_room = $builder->setX($x)->setY($y)->setZ($z)->buildRoom();
		return $user;
	}
	
	/**
	 * Incase of new user registration, inserts the info in the DB as well as initializes the attributes
	 * @param string $username
	 * @param string $password
	 */
	public function insertUser($username, $password) {
		$db = Database::getInstance();
		$sql = "INSERT INTO users (username, password, logged_in) VALUES ('" . mysql_escape_string($username) . "', '" . mysql_escape_string($password) . "', 1)";
		$db->ExecQuery($sql);
		$this->setUsername($username);
	}
	
	/**
	 * Function to toggle the flag in the DB to determine whether a user is logged in or vice versa
	 * @param integer $login
	 */
	public function updateLogin($login) {
		$db = Database::getInstance();
		$sql = "SELECT logged_in FROM users WHERE username = '$this->username'";
		$db->ExecQuery($sql);
		$logged = $db->GetAssoc();
		$logged = $logged['logged_in'];
		if($login != $logged) {
			$sql = "UPDATE users SET logged_in = 1 - $login WHERE username = '$this->username'";
			$db->ExecQuery($sql);
		}
	}
	
	/**
	 * Resets the object. Used in logout
	 */
	public function reset() {
		$this->username = NULL;
		$this->curr_room = NULL;
	}
	
	/**
	 * Function to check the password when a user logs in
	 * @param string $password
	 * @return boolean
	 */
	public function checkPassword($password) {
		return $password == $this->password;
	}
	
	/**
	 * Function to get all the users who are logged in and playing in the same room as the current user
	 * @return multitype:array
	 */
	public function getUsersRoom() {
		$db = Database::getInstance();
		$x = $this->curr_room->getX();
		$y = $this->curr_room->getY();
		$z = $this->curr_room->getZ();
		$coord = "{$x}{$y}{$z}";
		$sql = "SELECT * FROM users WHERE room_coord = '$coord' AND logged_in = 1 AND username <> '$this->username'";
		$db->ExecQuery($sql);
		$users = array();
		while($user = $db->GetAssoc())
			$users[] = $user['username'];
		
		return $users;
	}
	
	/**
	 * Function to update the room coordinates in the DB
	 * @return boolean
	 */
	private function updateRoom() {
		$db = Database::getInstance();
		if(empty($this->username))
			return FALSE;
		$x = $this->curr_room->getX();
		$y = $this->curr_room->getY();
		$z = $this->curr_room->getZ();
		$coord = "{$x}{$y}{$z}";
		$sql = "UPDATE users SET room_coord = $coord WHERE username = '$this->username'";
		
		if(!$db->ExecQuery($sql))
			return FALSE;
	}
	
}