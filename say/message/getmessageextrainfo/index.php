<?php
include ('../../header.php');



if(!check_login()) {
	$ret['ErrorMsg'] = '没有登录';
	exit (json_encode($ret));
}

//$user_id = $user['user_id'];

//if(isset($_POST['user_id'])) {
//	$user_id = $_POST['user_id'] + 0;
//}

if(empty($_POST['message_id']) ) {
	$ret['ErrorMsg'] = '参数错误';
	exit (json_encode($ret));
}


$message_id = $_POST['message_id'] + 0;




if (!($stmt = $mysqli->prepare("SELECT like_count,comment_count FROM message WHERE message_id = ? "))) {
		$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		exit (json_encode($ret));	
			
	}

if (!$stmt->bind_param("i", $message_id)) {
	  $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

if (!$stmt->execute()) {
	  $ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

$stmt->bind_result($like_count,$comment_count);

$stmt->fetch();

$stmt->close();


$mysqli->close();

$ret['status'] = 1;
$ret['ErrorMsg'] = '';
$ret['message_id'] = $message_id;
$ret['like_count'] = $like_count;
$ret['comment_count'] = $comment_count;


exit (json_encode($ret));