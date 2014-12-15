<?php
include ('../../header.php');

if(!check_login()) {
	$ret['ErrorMsg'] = '没有登录';
	exit (json_encode($ret));
}

$user_id = $user['user_id'];

if(isset($_POST['user_id']) && $_POST['user_id'] != '') {
	$user_id = $_POST['user_id'] + 0 ;
}

if(empty($_POST['message_id'])  ) {
	$ret['ErrorMsg'] = '参数错误';
	exit (json_encode($ret));
}

$message_id = $_POST['message_id'] + 0;
$time = time();
$new_like = 1;

if (!($stmt = $mysqli->prepare("SELECT * FROM  `like` where like_userid = ? and message_id = ? "))) {
	$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	exit (json_encode($ret));	
		
}

if (!$stmt->bind_param("ii", $user_id, $message_id )) {
  $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
	exit (json_encode($ret));
}

if (!$stmt->execute()) {
  $ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
	exit (json_encode($ret));
}

$stmt->store_result();

if($stmt->num_rows >= 1 ) {
	
	$ret['ErrorMsg'] = '已经赞过了';
	exit (json_encode($ret));
}

$stmt->close();


if (!($stmt = $mysqli->prepare("UPDATE message set new_like = new_like + 1, like_count = like_count + 1, new_time = ? WHERE message_id = ? "))) {
	$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	exit (json_encode($ret));	
		
}

if (!$stmt->bind_param("ii", $time, $message_id)) {
  $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
	exit (json_encode($ret));
}

if (!$stmt->execute()) {
  $ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
	exit (json_encode($ret));
}

$stmt->close();

if (!($stmt = $mysqli->prepare("INSERT INTO  `like` (message_id, like_userid, time) VALUES (?,?,?) "))) {
	$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	exit (json_encode($ret));	
		
}

if (!$stmt->bind_param("iii", $message_id, $user_id, $time)) {
  $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
	exit (json_encode($ret));
}

if (!$stmt->execute()) {
  $ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
	exit (json_encode($ret));
}

$stmt->close();

if (!($stmt = $mysqli->prepare("update userinfo set last_like_time = ? where user_id = ?"))) {
	$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	exit (json_encode($ret));	
		
}

if (!$stmt->bind_param("ii", $time, $user_id)) {
  $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
	exit (json_encode($ret));
}

if (!$stmt->execute()) {
  $ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
	exit (json_encode($ret));
}

$stmt->close();

if (!($stmt = $mysqli->prepare("SELECT count(*) as count FROM`like` WHERE message_id = ? "))) {
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

$stmt->bind_result($count);
$stmt->fetch();

$stmt->close();

//notifications
if($get_author = $mysqli->query("SELECT author_id FROM message WHERE message_id = $message_id")) {
	$author = $get_author->fetch_row()[0];
	if($author != $user_id) {
		$n_type = $noti_type['like'];
		if($rets = $mysqli->query("SELECT * FROM usrnotification WHERE user_id = $author AND active_userid = $user_id AND type = '$n_type' AND message_id = $message_id ")) {
			if($rets->num_rows > 0) {
				if($mysqli->query("DELETE FROM usrnotification WHERE user_id = $author AND active_userid = $user_id AND type = '$n_type' AND message_id = $message_id ")) {
					
				}
				//error ??
			}
		}
		
		if(!$mysqli->query("INSERT INTO usrnotification (user_id, active_userid, message_id, type, time) VALUES ($author, $user_id, $message_id, '$n_type', $time)")) {
			printf("Error: %s\n", $mysqli->error);
		}
	}
	
}
//update user score
update_user_point($user_id, 1);



$mysqli->close();

$ret['status'] = 1;
$ret['ErrorMsg'] = '';
$ret['like_count'] = $count;
exit (json_encode($ret));

