<?php
session_start();
require_once 'includes/Database.php';
require_once 'includes/CommandHandler.php';
require_once 'includes/User.php';
if(isset($_REQUEST['type']) && $_REQUEST['type'] == 'post') {
	if(isset($_REQUEST['text']))
	$response = CommandHandler::handleCommand($_REQUEST['text']);
	echo json_encode($response);
}

// The handler for pulling mechanism from the client side
if(isset($_REQUEST['type']) && $_REQUEST['type'] == 'get') {
	if(isset($_REQUEST['time'])) {
		$db = Database::getInstance();
		$sql = "SELECT * FROM users WHERE username = '" . $_SESSION['name'] . "'";
		$db->ExecQuery($sql);
		$user = $db->GetAssoc();
		$userid = $user['id'];
		$response = array();
		$room = $user['room_coord'];
		if($room < 100)
			$room = "0$room";
		$sql = "SELECT m.*, u.username FROM users AS u, messages AS m WHERE u.id = m.user_id AND (m.message_type = " . CommandHandler::YELL . " OR (m.message_type = " . CommandHandler::TELL . " AND m.target_user_id = $userid) OR (m.message_type AND m.room_coord='$room')) AND m.time_added > '" . $_REQUEST['time'] . "' AND m.user_id <> $userid";
		$db->ExecQuery($sql);
		$response['messages'] = array();
		while($message = $db->GetAssoc())
			$response['messages'][] = $message['username'] . ': ' . $message['message'];
		
		$User = User::getInstance();
		$User->getUser($_SESSION['name']);
		$users = $User->getUsersRoom();
		$response['users'] = array();
		if(!empty($users)) {
			foreach($users as $u)
				$response['users'][] = $u;
		}
		
		echo json_encode($response);
	}
}