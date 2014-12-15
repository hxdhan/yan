<?php
include ('../../header.php');

if(!check_login()) {
	$ret['ErrorMsg'] = '没有登录';
	exit (json_encode($ret));
}



if(empty($_POST['inviter_userid'])  || empty($_POST['invitee_userid']) ) {
	$ret['ErrorMsg'] = '参数错误';
	exit (json_encode($ret));
}

$inviter_userid = $_POST['inviter_userid'] + 0 ;
$invitee_userid = $_POST['invitee_userid'] + 0 ;



if(!($stmt = $mysqli->prepare("delete from usrfriendinvite  where inviter_userid = ? and invitee_userid = ?"))) {
  $ret['ErrorMsg'] = "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
  exit (json_encode($ret));	
}

if(!($stmt->bind_param("ii",$inviter_userid, $invitee_userid))) {
  $ret['ErrorMsg'] = "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
  exit (json_encode($ret));
}

if (!$stmt->execute()) {
	  $ret['ErrorMsg'] = "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
	  exit (json_encode($ret));
}


$stmt->close();

$mysqli->close();

$ret['status'] = 1;
$ret['ErrorMsg'] = '';
$ret['inviter_userid'] = $inviter_userid;
$ret['invitee_userid'] = $invitee_userid;
exit (json_encode($ret));
 
?>