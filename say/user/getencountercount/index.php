<?php
include ('../../header.php');



if(!check_login()) {
	$ret['ErrorMsg'] = '没有登录';
	exit (json_encode($ret));
}

$user_id = $user['user_id'];

if(isset($_POST['user_id'])) {
	$user_id = $_POST['user_id'] + 0;
}


if(empty($_POST['encounter_userid'])  ) {
	$ret['ErrorMsg'] = '参数错误';
	exit (json_encode($ret));
}

$encounter_userid = $_POST['encounter_userid'] + 0 ;

if($user_id > $encounter_userid) {

	list($encounter_userid,$user_id) = array($user_id,$encounter_userid);
}


if (!($stmt = $mysqli->prepare("SELECT message_id FROM userencounter WHERE user_id = ? and encounter_userid = ? "))) {
		$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		exit (json_encode($ret));	
			
	}

if (!$stmt->bind_param("ii", $user_id, $encounter_userid)) {
	  $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

if (!$stmt->execute()) {
	  $ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

$stmt->bind_result($message_id);



while($stmt->fetch()) {
	
}




$stmt->close();

$mysqli->close();

$ret['status'] = 1;
$ret['ErrorMsg'] = '';
$ret['user_id'] = $user_id;
$ret['encounter_userid'] = $encounter_userid;
$ret['count'] = count(explode(',',$message_id));


exit (json_encode($ret));