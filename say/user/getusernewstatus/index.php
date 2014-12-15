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

if(empty($_POST['user_id'])) {
	$ret['ErrorMsg'] = '参数错误';
	exit (json_encode($ret));
}

$user_id = $_POST['user_id'] + 0 ;


if (!($stmt = $mysqli->prepare("SELECT count(*) FROM userfollow WHERE user_id = ? and newtouser = 1"))) {
		$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		exit (json_encode($ret));	
			
	}

if (!$stmt->bind_param("i", $user_id)) {
	  $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

if (!$stmt->execute()) {
	  $ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

$stmt->bind_result($newfollowusers);

$stmt->fetch();

$stmt->close();

if (!($stmt = $mysqli->prepare("SELECT count(*) FROM userfollow WHERE follow_userid = ? and newtofollowuser = 1 "))) {
		$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		exit (json_encode($ret));	
			
	}

if (!$stmt->bind_param("i", $user_id)) {
	  $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

if (!$stmt->execute()) {
	  $ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

$stmt->bind_result($newfans);

$stmt->fetch();

$stmt->close();

if (!($stmt = $mysqli->prepare("SELECT count(*) FROM userencounter WHERE user_id = ?  and newtouser = 1 "))) {
		$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		exit (json_encode($ret));	
			
	}

if (!$stmt->bind_param("i", $user_id)) {
	  $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

if (!$stmt->execute()) {
	  $ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

$stmt->bind_result($newtoencounterusers);

$stmt->fetch();

$stmt->close();

if (!($stmt = $mysqli->prepare("SELECT count(*) FROM userencounter WHERE encounter_userid = ? and newtoencounteruser = 1 "))) {
		$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		exit (json_encode($ret));	
			
	}

if (!$stmt->bind_param("i", $user_id)) {
	  $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

if (!$stmt->execute()) {
	  $ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

$stmt->bind_result($newtoencounteredusers);

$stmt->fetch();

$stmt->close();

if (!($stmt = $mysqli->prepare("SELECT count(*) FROM usrchat WHERE receive_userid = ? and new = 1 "))) {
		$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		exit (json_encode($ret));	
			
	}

if (!$stmt->bind_param("i", $user_id)) {
	  $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

if (!$stmt->execute()) {
	  $ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

$stmt->bind_result($newchats);

$stmt->fetch();

$stmt->close();

$new_notif = 0;

if (!($stmt = $mysqli->prepare("SELECT count(*) FROM usrnotification WHERE user_id = ? and new = 1 "))) {
		$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		exit (json_encode($ret));	
			
	}

if (!$stmt->bind_param("i", $user_id)) {
	  $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

if (!$stmt->execute()) {
	  $ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

$stmt->bind_result($new_notif);

$stmt->fetch();

$stmt->close();

$mysqli->close();

$ret['status'] = 1;
$ret['ErrorMsg'] = '';
$ret['newfollowusers'] = $newfollowusers;
$ret['newfans'] = $newfans;
$ret['newencounterusers'] = $newtoencounterusers;
$ret['newencounteredusers'] = $newtoencounteredusers;
$ret['newchats'] = $newchats;
$ret['newnotif'] = $new_notif;
exit (json_encode($ret));