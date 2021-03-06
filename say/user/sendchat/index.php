<?php
include ('../../header.php');

if(!check_login()) {
	$ret['ErrorMsg'] = '没有登录';
	exit (json_encode($ret));
}

if(empty($_POST['user_id']) || empty($_POST['receive_userid']) || empty($_POST['longitude']) || empty($_POST['latitude']) ) {
	$ret['ErrorMsg'] = '参数错误';
	exit (json_encode($ret));
}

$user_id = $_POST['user_id'] + 0 ;
$receive_userid = $_POST['receive_userid'] + 0 ;

$longitude = $_POST['longitude'] + 0;
$latitude = $_POST['latitude'] + 0;


$content_type = 0;

if(isset($_POST['type']) && intval($_POST['type']) > 0) {
	$content_type = $_POST['type'] + 0;
}

$time = time();

$duration = 0;
if(isset($_POST['duration']) && intval($_POST['duration']) > 0) {
	$duration = $_POST['duration'] + 0;
}

$date_name = date('Ymd');
$file_path = "../../static/".$date_name;
if(!file_exists($file_path)) {
	mkdir($file_path);
	
}

$file_url = '/say/static/'.$date_name.'/';
$finfo = new finfo(FILEINFO_MIME_TYPE);

if($content_type === 0) {
	$content = $_POST['content'];
}
elseif($content_type === 2) {
	
	$allowed_image = array('jpg' => 'image/jpeg','png' => 'image/png','gif' => 'image/gif');

	$image_url = '';

	$ext = array_search($finfo->file($_FILES['content']['tmp_name']),$allowed_image);

	if($ext === false) {
		$ret['ErrorMsg'] = '图片格式错误';
		exit (json_encode($ret));
		
	}
	
	$file_name = microtime(true) * 10000 .'.'.$ext;
	$file_path_name = $file_path."/".$file_name;
	rename($_FILES['content']['tmp_name'],$file_path_name);
	
	$content = $file_url . $file_name;

	

}

elseif($content_type === 1) {
		$alowed_voice = array('amr'=>'application/octet-stream');
		$ext = array_search($finfo->file($_FILES['content']['tmp_name']),$alowed_voice);
		if($ext === false) {
			$ret['ErrorMsg'] = '音频格式错误';
			exit (json_encode($ret));
			
		}

	$file_name = microtime(true) * 10000 .'.'.$ext;
	$file_path_name = $file_path."/".$file_name;
	rename($_FILES['content']['tmp_name'],$file_path_name);
	
	$content = $file_url . $file_name;

}

$auto_reply = 0;

if($receive_userid === 1) {
  //send chat to tieer
	//check chat one hour
	$one_hour_ago = $time - 60 * 60;
	
	if (!($stmt = $mysqli->prepare("select count(*) from  usrchat where user_id = ? and receive_userid = ? and time > ? "))) {
		$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		exit (json_encode($ret));	
	}
	if (!$stmt->bind_param("iii", $receive_userid, $user_id, $one_hour_ago)) {
		$ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}
	if (!$stmt->execute()) {
		$ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}
	$stmt->bind_result($should_auto);
	$stmt->fetch();
	$stmt->close();
	
	if($should_auto == 0) {
		//no reply 
		$auto_reply = 1; 
	}
}


if (!($stmt = $mysqli->prepare("INSERT INTO  usrchat (user_id,receive_userid, longitude,latitude,chat_content,duration, content_type, time) values (?,?,?,?,?,?,?,?) "))) {
		$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		exit (json_encode($ret));	
			
	}

if (!$stmt->bind_param("iiddsiii", $user_id, $receive_userid,$longitude, $latitude, $content,$duration,$content_type,$time)) {
	  $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

if (!$stmt->execute()) {
	  $ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}


$chat_id = $mysqli->insert_id;

if($get_chat = $mysqli->query("SELECT * FROM usrchat WHERE chat_id = $chat_id")) {
	$chat = $get_chat->fetch_assoc();
	$chat['time'] = $chat['time'] + 0;
}


$stmt->close();

if (!($stmt = $mysqli->prepare("update userinfo set last_chat_time = ? where user_id = ?"))) {
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

if($auto_reply === 1) {
	$content = "是我做得不够好吗？请指正，我会努力改正的。你提出的问题我会在24小时之内响应。";
	tieer_to_user($user_id, $content);
}


//notifications . only one chat data notifaction
/**
$n_type = $noti_type['chat'];

if($rets = $mysqli->query("SELECT * FROM usrnotification WHERE user_id = $receive_userid AND active_userid = $user_id AND type = '$n_type' ")) {
	if($rets->num_rows > 0) {
		if(!$mysqli->query("DELETE FROM usrnotification WHERE user_id = $receive_userid AND active_userid = $user_id AND type = '$n_type' ")) {
			printf("Error: %s\n", $mysqli->error);
		}
		
	}
}
else {
	printf("Error: %s\n", $mysqli->error);
}
	
if(!$mysqli->query("INSERT INTO usrnotification (user_id, active_userid, type, time) VALUES ($receive_userid, $user_id, '$n_type', $time)")) {
	printf("Error: %s\n", $mysqli->error);
}

**/

if($get_nickname = $mysqli->query("SELECT nickname FROM userinfo WHERE user_id = $user_id")) {
	$nickname = $get_nickname->fetch_assoc()['nickname'];
}

if($get_registration = $mysqli->query("SELECT push_registration FROM user WHERE user_id = $receive_userid")) {
	$receive_value = $get_registration->fetch_assoc()['push_registration'];
}
if(!empty($receive_value)) {
	
	if(intval($chat['content_type']) === 0) {
		
		$charset = 'UTF-8';
		$length = 40;
		$ct = $chat['chat_content'];
		if(mb_strlen($ct, $charset) > $length) {
			$ct = mb_substr($ct, 0, $length - 3, $charset) . '...';
		}
		$send = $nickname.':'.$ct;
		
	}
	elseif(intval($chat['content_type']) === 1) {
		$send = $nickname.':[语音]';
	}
	elseif(intval($chat['content_type']) === 2) {
		$send = $nickname.':[图片]';
	}
	push_message($receive_value, $send, "chat");

}
$mysqli->close();

$ret['status'] = 1;
$ret['ErrorMsg'] = '';
$ret['chat'] = $chat;
exit (json_encode($ret,JSON_UNESCAPED_UNICODE));