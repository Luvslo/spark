<?php
/**
 * @author Krunal
 * CommandHandler Class to handle all user commands. Presently supportly all the directional commands and communication commands
 * 
 */
include_once 'User.php';
include_once 'Room.php';
include_once 'Database.php';

class CommandHandler {
	
	// Array of all the supported commands
	private static $command_list = array(
		'say',
		'yell',
		'tell',
		'north',
		'south',
		'east',
		'west',
		'up',
		'down',
	);
	
	//Message types
	const TELL = 1;
	const SAY = 2;
	const YELL = 3;
	
	/**
	 * Mapper function which maps the appropriate command to its handler
	 * @param string $request
	 * @return boolean|Ambigous <boolean, multitype:string , multitype:array >
	 */
	public static function handleCommand($request) {
		$arr = explode(' ', $request, 2);
		$arr[0] = strtolower($arr[0]);
		if(!in_array($arr[0], self::$command_list))
			return FALSE;		
		
		$return = FALSE;
		switch($arr[0]) {
			case 'say':
				$return = self::sayCommandHandler($arr[1]);
				break;
			case 'yell':
				$return = self::yellCommandHandler($arr[1]);
				break;
			case 'tell':
				$return = self::tellCommandHandler($arr[1]);
				break;
			case 'north':
				$return = self::northCommandHandler();
				break;
			case 'south':
				$return = self::southCommandHandler();
				break;
			case 'east':
				$return = self::eastCommandHandler();
				break;
			case 'west':
				$return = self::westCommandHandler();
				break;
			case 'up':
				$return = self::upCommandHandler();
				break;
			case 'down':
				$return = self::downCommandHandler();
				break;
		}
		
		return $return;
	}
	
	/**
	 * Function to handle the 'say' command which can be use to broadcast in the room where the user resides
	 * @param string $request
	 * @return multitype:array
	 */
	private static function sayCommandHandler($request) {
		$db = Database::getInstance();
		$user = User::getInstance();
		$User = $user->getUser($_SESSION['name']);
		$room = $user->getRoom();
		$coord = $room->getX() . $room->getY() . $room->getZ();
		$sql = "INSERT INTO messages (user_id, message_type, message, room_coord) VALUES ($User[id], " . self::SAY . ", '$request', '$coord')";
		$response = array();
		$response['type'] = 'message';
		$response['success'] = ($db->ExecQuery($sql) ? 'true' : 'false');
		return $response;
	}
	
	/**
	 * Function to handle the 'yell' command which can be use to broadcast all over the world
	 * @param string $request
	 * @return multitype:array
	 */
	private static function yellCommandHandler($request) {
		$db = Database::getInstance();
		$user = User::getInstance();
		$User = $user->getUser($_SESSION['name']);
		$room = $user->getRoom();
		$sql = "INSERT INTO messages (user_id, message_type, message) VALUES ($User[id], " . self::YELL . ", '$request')";
		$response = array();
		$response['type'] = 'message';
		$response['success'] = ($db->ExecQuery($sql) ? 'true' : 'false');
		return $response;
	}
	
	/**
	 * Function to handle the 'tell' command which can be use to privately send a message to other user
	 * @param string $request
	 * @return multitype:array
	 */
	private static function tellCommandHandler($request) {
		$arr = explode(' ', $request, 2);
		$db = Database::getInstance();
		$user = User::getInstance();
		$User = $user->getUser($_SESSION['name']);
		$room = $user->getRoom();
		$sql = "SELECT * FROM users WHERE username = '$arr[0]'";
		$db->ExecQuery($sql);
		$response = array();
		$response['type'] = 'message';
		if($t = $db->GetAssoc())
			$target = $t['id'];
		else {
			$response['success'] = 'false';
			return $response;
		}
		
		$sql = "INSERT INTO messages (user_id, message_type, message, target_user_id) VALUES ($User[id], " . self::TELL . ", '$arr[1]', $target)";
		$response['success'] = ($db->ExecQuery($sql) ? 'true' : 'false');
		return $response;
	}
	
	/**
	 * Handler for moving up north (across the Y-axis)
	 * @return multitype:string |multitype:array
	 */
	private static function northCommandHandler() {
		$response = array();
		$response['type'] = 'direction';
		$user = User::getInstance();
		$user->getUser($_SESSION['name']);
		$curr_room = $user->getRoom();
		$x = $curr_room->getX();
		$y = $curr_room->getY();
		$z = $curr_room->getZ();
		$y++;
		if($y >= User::MAX_WIDTH) {
			$response['success'] = 'false';
			$response['reason'] = 'out of bounds';
			return $response;
		}
		
		if(Room::checkOpacity($x, $y, $z) === FALSE) {
			$response['success'] = 'false';
			$response['reason'] = 'opaque';
			return $response;
		}
		
		$user->createRoom($x, $y, $z);
		$response['success'] = 'true';
		$response['room']['name'] = $user->getRoom()->getName();
		$response['room']['description'] = $user->getRoom()->getDescription();
		$response['users'] = $user->getUsersRoom();
		
		return $response;
	}
	
	/**
	 * Handler for moving down south (across the Y-axis)
	 * @return multitype:string |multitype:array
	 */
	private static function southCommandHandler() {
		$response = array();
		$response['type'] = 'direction';
		$user = User::getInstance();
		$user->getUser($_SESSION['name']);
		$curr_room = $user->getRoom();
		$x = $curr_room->getX();
		$y = $curr_room->getY();
		$z = $curr_room->getZ();
		$y--;
		if($y < 0) {
			$response['success'] = 'false';
			$response['reason'] = 'out of bounds';
			return $response;
		}
		
		if(Room::checkOpacity($x, $y, $z) === FALSE) {
			$response['success'] = 'false';
			$response['reason'] = 'opaque';
			return $response;
		}
		
		$user->createRoom($x, $y, $z);
		$response['success'] = 'true';
		$response['room']['name'] = $user->getRoom()->getName();
		$response['room']['description'] = $user->getRoom()->getDescription();
		$response['users'] = $user->getUsersRoom();
		
		return $response;
	}
	
	/**
	 * Handler for moving right to the east (across the X-axis)
	 * @return multitype:string |multitype:array
	 */
	private static function eastCommandHandler() {
		$response = array();
		$response['type'] = 'direction';
		$user = User::getInstance();
		$user->getUser($_SESSION['name']);
		$curr_room = $user->getRoom();
		$x = $curr_room->getX();
		$y = $curr_room->getY();
		$z = $curr_room->getZ();
		$x++;
		if($x >= User::MAX_LENGTH) {
			$response['success'] = 'false';
			$response['reason'] = 'out of bounds';
			return $response;
		}
		
		if(Room::checkOpacity($x, $y, $z) === FALSE) {
			$response['success'] = 'false';
			$response['reason'] = 'opaque';
			return $response;
		}
		
		$user->createRoom($x, $y, $z);
		$response['success'] = 'true';
		$response['room']['name'] = $user->getRoom()->getName();
		$response['room']['description'] = $user->getRoom()->getDescription();
		$response['users'] = $user->getUsersRoom();
		
		return $response;
	}
	
	/**
	 * Handler for moving left to the west (across the X-axis)
	 * @return multitype:string |multitype:array
	 */
	private static function westCommandHandler() {
		$response = array();
		$response['type'] = 'direction';
		$user = User::getInstance();
		$user->getUser($_SESSION['name']);
		$curr_room = $user->getRoom();
		$x = $curr_room->getX();
		$y = $curr_room->getY();
		$z = $curr_room->getZ();
		$x--;
		if($x < 0) {
			$response['success'] = 'false';
			$response['reason'] = 'out of bounds';
			return $response;
		}
		
		if(Room::checkOpacity($x, $y, $z) === FALSE) {
			$response['success'] = 'false';
			$response['reason'] = 'opaque';
			return $response;
		}
		
		$user->createRoom($x, $y, $z);
		$response['success'] = 'true';
		$response['room']['name'] = $user->getRoom()->getName();
		$response['room']['description'] = $user->getRoom()->getDescription();
		$response['users'] = $user->getUsersRoom();
		
		return $response;
	}
	
	/**
	 * Handler for moving up one level (across the Z-axis)
	 * @return multitype:string |multitype:array
	 */
	private static function upCommandHandler() {
		$response = array();
		$response['type'] = 'direction';
		$user = User::getInstance();
		$user->getUser($_SESSION['name']);
		$curr_room = $user->getRoom();
		$x = $curr_room->getX();
		$y = $curr_room->getY();
		$z = $curr_room->getZ();
		$z++;
		if($z >= User::MAX_HEIGHT) {
			$response['success'] = 'false';
			$response['reason'] = 'out of bounds';
			return $response;
		}
		
		if(Room::checkOpacity($x, $y, $z) === FALSE) {
			$response['success'] = 'false';
			$response['reason'] = 'opaque';
			return $response;
		}
		
		$user->createRoom($x, $y, $z);
		$response['success'] = 'true';
		$response['room']['name'] = $user->getRoom()->getName();
		$response['room']['description'] = $user->getRoom()->getDescription();
		$response['users'] = $user->getUsersRoom();
		
		return $response;
	}
	
	/**
	 * Handler for moving down one level (across the Z-axis)
	 * @return multitype:string |multitype:array
	 */
	private static function downCommandHandler() {
		$response = array();
		$response['type'] = 'direction';
		$user = User::getInstance();
		$user->getUser($_SESSION['name']);
		$curr_room = $user->getRoom();
		$x = $curr_room->getX();
		$y = $curr_room->getY();
		$z = $curr_room->getZ();
		$z--;
		if($z < 0) {
			$response['success'] = 'false';
			$response['reason'] = 'out of bounds';
			return $response;
		}
		
		if(Room::checkOpacity($x, $y, $z) === FALSE) {
			$response['success'] = 'false';
			$response['reason'] = 'opaque';
			return $response;
		}
		
		$user->createRoom($x, $y, $z);
		$response['success'] = 'true';
		$response['room']['name'] = $user->getRoom()->getName();
		$response['room']['description'] = $user->getRoom()->getDescription();
		$response['users'] = $user->getUsersRoom();
		
		return $response;
	}
}