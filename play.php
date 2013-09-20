<?php  
/**
 * Main app file exposed to the users.
 * Race conditions:
 * The client will pull the messages and the online users in the same room at a specific interval set (presently set to 3 seconds).
 * This will create race conditions when the user inputs a communication command and the recepient user pulls or checks for new commands just before the message is logged.
 * The solution would be to use a push mechanism instead of clients pulling the data. By that, the server will push the messages and info about users as and when the data is available.
 */

include_once 'includes/User.php';
session_start(); 
$User = User::getInstance();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">  
<html xmlns="http://www.w3.org/1999/xhtml">  
<head>  
<title>Welcome to the Dungeon!</title>  
<link type="text/css" rel="stylesheet" href="css/style.css" />  
</head>   

<?php 
function loginForm(){  
    echo' 
    <div id="loginform"> 
    <form action="play.php" method="post"> 
        <p>Please enter your name to continue:</p>
		<table>
			<tr>
				<td style="text-align:right; width:50%"> 
        			<label for="name">Name:</label> 
				</td>
				<td>
        			<input type="text" name="name" id="name" />
				</td>
			</tr>
			<tr>
				<td style="text-align:right; width:50%">
					<label for="password">Password:</label>
				</td>
				<td>
					<input type="password" name="password" id="password" />
				</td>
			</tr>
			<tr>
				<td colspan=2>
        			<input type="submit" name="enter" id="enter" value="Enter" /> 
				</td>
			</tr>
    </form> 
    </div> 
    ';  
}  

// On logging out, sets the user as logged out in DB, resets the object and head backs to login
if(isset($_GET['logout']) && $_GET['logout'] == 'true') {
	$User->getUser($_SESSION['name']);
	$User->updateLogin(1);
	$User->reset();

	session_destroy();
	header('Location:play.php');
}

// If user is already logged in just get him/her his/her room and let the play begin 
if(isset($_SESSION['name'])) {
	$User->getUser($_SESSION['name']);
	$User->updateLogin(1);
	if($User->getRoom() == NULL) {
		$User->createRoom();
	}
}

//Checks to see if new user and calidating username/password for existing user
if(isset($_POST['enter'])){  
    if($_POST['name'] != "" && $_POST['password'] != '') {  
    	$password = md5($_POST['password']);
    	$User->getUser($_POST['name']);
		if($User->getUsername() == NULL) {
			$User->insertUser($_POST['name'], $password);
			$_SESSION['name'] = stripslashes(htmlspecialchars($_POST['name']));
			$User->createRoom();
        }
        else {
        	if(!$User->checkPassword($password))
        		echo '<span class="error">Wrong Password!</span>';
        	else {
				$User->updateLogin(1);
				if($User->getRoom() == NULL)
					$User->createRoom();
				
        		$_SESSION['name'] = stripslashes(htmlspecialchars($_POST['name']));  
        	}
        }
    }  
    else{  
        echo '<span class="error">Please type in a name and/or password</span>';  
    }  
}  
?>

<?php
if(!isset($_SESSION['name'])):
    loginForm();  
 
else:  
$users = $User->getUsersRoom();
?>  
<table align="center">
<tr>
<td>
<div id="wrapper">  
    <div id="menu">  
        <span class="welcome">Welcome, <b><?php echo $_SESSION['name']; ?></b></span>  
        <span class="room">Room: <?php echo $User->getRoom()->getName();?></span>
        <span class="logout" style="margin-left: 50px;"><a id="exit" href="#">Exit Game</a></span>  
        <div style="clear:both"></div>  
    </div>      
    <div id="messagebox">You are in Room: <?php echo $User->getRoom()->getName();?>.<br/><?php echo $User->getRoom()->getDescription();?><br/></div>  
      
    <form name="message" action="" method="post">  
        <input name="usermsg" type="text" id="usermsg" size="63" />  
        <input name="submitmsg" type="button"  id="submitmsg" value="Send" />  
    </form>  
</div> 
</td> 

<td>
<div id="users">
	<span>Adventurers in Room: <?php echo $User->getRoom()->getName();?><span><br/>
	<div id="user_list">
		<?php if(!empty($users)): ?>
			<?php foreach($users as $user): ?>
				<?php echo $user;?><br/>
			<?php endforeach; ?>
		<?php endif;?>
	</div>
</div>
</td>
</tr>
</table>
<script type="text/javascript" src="js/jquery.js"></script>  
<script type="text/javascript">  
// jQuery Document  
$(document).ready(function(){ 
	var username = '<?php echo $_SESSION['name']?>';
	var curr_room = '<?php echo $User->getRoom()->getName(); ?>';
	var curr_time = getTime(); 
	$("#exit").click(function(){  
        var exit = confirm("Are you sure you want to end the session?");  
        if(exit==true){window.location = 'play.php?logout=true';}        
    }); 

	// Submits the user input to the server where it will be parsed and handled by appropriate command handler
	$("#submitmsg").click(function(){     
	    var clientmsg = $("#usermsg").val();  
	    $.post("post.php", {type:'post', text: clientmsg}, function(data) {
		    var oldscrollHeight = $("#messagebox").attr("scrollHeight") - 20;
		    if(data['success'] == 'true') {
				if(data['type'] == 'direction') {
					curr_room = data['room']['name'];
					$('.room').html('Room: ' + curr_room);
					$('#messagebox').append('<br/>You are in Room: ' + curr_room + '.<br/>' + data['room']['description'] + '<br/>'); 
					$('#user_list').html(''); 

					if(data['users'].length > 0) {
						var length = data['users'].length;
						for(i=0; i<length; i++)
							$('#user_list').append(data['users'][i] + '<br/>');
					}
				}

				if(data['type'] == 'message') {
					$('#messagebox').append(username + ': ' + clientmsg + '<br/>');
				}
		    }
		    else if(data['success'] == 'false') {
		    	if(data['type'] == 'direction') {
					var reason = data['reason'];
					if(reason == 'out of bounds')
						$('#messsagebox').append('<br/>Oops! You reached the horizon of the world!<br/>');

					if(reason == 'opaque')
						$('#messagebox').append('<br/>You hit the walls of a blackhole! I will not let you die! Not yet.<br/>');
		    	}

		    	if(data['type'] == 'message') {
		    		$('#messagebox').append('<font color="red">Error occured in logging your message</font>');
		    	}
		    }
		    var newscrollHeight = $("#messagebox").attr("scrollHeight") - 20; //Scroll height after the request  
            if(newscrollHeight > oldscrollHeight){  
                $("#messagebox").animate({ scrollTop: newscrollHeight }, 'normal'); //Autoscroll to bottom of div  
            }  
	    }, 'json');                
	    $("#usermsg").val('');  
	    return false;  
	}); 

	function loadMessages(){       
	    var oldscrollHeight = $("#messagebox").attr("scrollHeight") - 20; //Scroll height before the request  
	    $.post("post.php", {type:'get', time:curr_time}, function(data) {
		    data = JSON.parse(data);
			if(typeof data['messages'] == 'undefined' || data['messages'].length>0) {
				var length = data['messages'].length;
				for(i=0; i<length; i++) {
					$('#messagebox').append(data['messages'][i] + '<br/>');
				}
			}

			$('#user_list').html('');
			if(data['users'].length > 0) {
				var length = data['users'].length;
				for(i=0; i<length; i++)
					$('#user_list').append(data['users'][i] + '<br/>');
			}
	    });
	    var newscrollHeight = $("#messagebox").attr("scrollHeight") - 20; //Scroll height after the request  
        if(newscrollHeight > oldscrollHeight){  
            $("#messagebox").animate({ scrollTop: newscrollHeight }, 'normal'); //Autoscroll to bottom of div 
        }
            curr_time = getTime();
	}  

	function AddZero(num) {
	    return (num >= 0 && num < 10) ? "0" + num : num + "";
	}

	function getTime() {
		var now = new Date();
		return now.getFullYear() + '-' + AddZero(now.getMonth() + 1) + '-' + AddZero(now.getDate()) + ' ' + AddZero(now.getHours()) + ':' + AddZero(now.getMinutes()) + ':' + AddZero(now.getSeconds());
	}

	setInterval (loadMessages, 5000); 
});  
</script>  
<?php  
endif; 
?>  