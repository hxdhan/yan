<?php
include ('../../header.php');

if(!check_login()) {
	$ret['ErrorMsg'] = '没有登录';
	exit (json_encode($ret));
}

$user_id = $user['user_id'];

if($user_id == 0) {
	$ret['ErrorMsg'] = '没有登录';
	exit (json_encode($ret));
}

if(empty($_POST['message_id']) || empty($_POST['longitude']) || empty($_POST['latitude']) ) {
	$ret['ErrorMsg'] = '参数错误';
	exit (json_encode($ret));
}

$duration = 0;

if(isset($_POST['duration']) && intval($_POST['duration']) > 0 ) {
	$duration = $_POST['duration'] + 0 ;
}

$message_id = $_POST['message_id'];
$longitude = $_POST['longitude'];
$latitude = $_POST['latitude'];

$text = '';
if(isset($_POST['text']) && $_POST['text'] != '') {
	$text = $_POST['text'];

}

$content_type = 1;

if(isset($_POST['type']) && intval($_POST['type']) >= 0) {
	$content_type = $_POST['type'] + 0;
}

$touser_id = 0;

if(isset($_POST['touser_id']) && intval($_POST['touser_id']) > 0) {
	$touser_id = $_POST['touser_id'] + 0;
}

$time = time();

$date_name = date('Ymd');
$file_path = check_path();
// if(!file_exists($file_path)) {
	// mkdir($file_path);
	
// }

$file_url = $static_path.$date_name.'/';

$finfo = new finfo(FILEINFO_MIME_TYPE);

$alowed_voice = array('amr'=>'application/octet-stream');

$voice_url = '';

$allowed_image = array('jpg' => 'image/jpeg','png' => 'image/png','gif' => 'image/gif');

$image_url = '';

if(isset($_FILES['voice'])) {

	$ext = array_search($finfo->file($_FILES['voice']['tmp_name']),$alowed_voice);
	if($ext === false) {
		$ret['ErrorMsg'] = '音频格式错误';
		exit (json_encode($ret));
		
	}
	$file_name = microtime(true) * 10000 .'.'.$ext;
	$file_path_name = $file_path."/".$file_name;
	rename($_FILES['voice']['tmp_name'],$file_path_name);
	
	$voice_url = $file_url . $file_name;

}

if(isset($_FILES['image'])) {
	$ext = array_search($finfo->file($_FILES['image']['tmp_name']),$allowed_image);

	if($ext === false) {
		$ret['ErrorMsg'] = '图片格式错误';
		exit (json_encode($ret));
		
	}
	
	$file_name = microtime(true) * 10000 .'.'.$ext;
	$file_path_name = $file_path."/".$file_name;
	rename($_FILES['image']['tmp_name'],$file_path_name);
	
	$image_url = $file_url . $file_name;
}

$new_comment = 1;

if (!($stmt = $mysqli->prepare("UPDATE message set new_comment = new_comment + 1, comment_count = comment_count + 1, new_time = ? WHERE message_id = ? "))) {
	$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	exit (json_encode($ret));	
		
}

if (!$stmt->bind_param("ii",  $time, $message_id)) {
  $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
	exit (json_encode($ret));
}

if (!$stmt->execute()) {
  $ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
	exit (json_encode($ret));
}

$stmt->close();

//update user status
if (!($stmt = $mysqli->prepare("UPDATE userinfo SET last_comment_time = ? WHERE user_id = ? "))) {
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

if (!($stmt = $mysqli->prepare("INSERT INTO comment (message_id, comment_userid, voice_url, image_url, duration, longitude, latitude, text, time, type, touser_id) VALUES (?,?,?,?,?,?,?,?,?,?,?)"))) {
	$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	exit (json_encode($ret));	
		
}

if (!$stmt->bind_param("iissiddsiii", $message_id, $user_id, $voice_url, $image_url, $duration, $longitude, $latitude, $text, $time, $content_type,$touser_id)) {
  $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
	exit (json_encode($ret));
}

if (!$stmt->execute()) {
  $ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
	exit (json_encode($ret));
}

$stmt->close();

$comment_id = $mysqli->insert_id;

if (!($stmt = $mysqli->prepare("SELECT * FROM comment WHERE comment_id = ?"))) {
	$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	exit (json_encode($ret));	
		
}

if (!$stmt->bind_param("i", $comment_id)) {
  $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
	exit (json_encode($ret));
}

if (!$stmt->execute()) {
  $ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
	exit (json_encode($ret));
}

$stmt->store_result();

$meta = $stmt->result_metadata();

while ($column = $meta->fetch_field()) {
   $bindVarsArray[] = &$result[$column->name];
}        
call_user_func_array(array($stmt, 'bind_result'), $bindVarsArray);

$stmt->fetch();

$stmt->close();

//notification

if($get_author = $mysqli->query("SELECT author_id FROM message WHERE message_id = $message_id")) {
	$author = $get_author->fetch_row()[0];
	if($author != $user_id) {
		$n_type = $noti_type['com'];
		if($rets = $mysqli->query("SELECT * FROM usrnotification WHERE user_id = $author AND active_userid = $user_id AND type = '$n_type' AND message_id = $message_id ")) {
			if($rets->num_rows > 0) {
				if(!$mysqli->query("DELETE FROM usrnotification WHERE user_id = $author AND active_userid = $user_id AND type = '$n_type' AND message_id = $message_id ")) {
					printf("Error: %s\n", $mysqli->error);
				}
				
			}
		}
		else {
			printf("Error: %s\n", $mysqli->error);
		}
		
		if(!$mysqli->query("INSERT INTO usrnotification (user_id, active_userid, message_id, type, time) VALUES ($author, $user_id, $message_id, '$n_type', $time)")) {
			printf("Error: %s\n", $mysqli->error);
		}
	}
	//send reply
	if($touser_id > 0 && $touser_id != $user_id ) {
		$n_type = $noti_type['rep'];
		if($rets = $mysqli->query("SELECT * FROM usrnotification WHERE user_id = $touser_id AND active_userid = $user_id AND type = '$n_type' AND message_id = $message_id ")) {
			if($rets->num_rows > 0) {
				if(!$mysqli->query("DELETE FROM usrnotification WHERE user_id = $touser_id AND active_userid = $user_id AND type = '$n_type' AND message_id = $message_id ")) {
						printf("Error: %s\n", $mysqli->error);
				}
				
			}
		}
	
		if(!$mysqli->query("INSERT INTO usrnotification (user_id, active_userid, message_id, type, time) VALUES ($touser_id, $user_id, $message_id, '$n_type', $time)")) {
			printf("Error: %s\n", $mysqli->error);
		}
	}

}
else {
	printf("Error: %s\n", $mysqli->error);
}

//update user score
update_user_point($user_id, 1);


$nickname = $user['nickname'];

if($get_user = $mysqli->query("SELECT u.push_registration,m.author_id FROM user u, message m WHERE u.user_id = m.author_id AND  m.message_id = $message_id")) {
	if($v = $get_user->fetch_assoc()) {
		$receive_value = $v['push_registration'];
		$auth = $v['author_id'];
		if($user_id != $auth) {
			
			$send = $nickname.'评论了你的贴儿';
			push_message($receive_value, $send, "comment");
		}
	}
}

if($touser_id > 0 && $touser_id != $user_id) {
	if($get_user = $mysqli->query("SELECT push_registration FROM user WHERE user_id = $touser_id")) {
		if($v = $get_user->fetch_assoc()) {
			$receive_value = $v['push_registration'];
			
			if(!empty($receive_value)) {
				$send = $nickname.'回复了你的评论';
				push_message($receive_value, $send, "reply");
			}
			
		}
	}
}

$mysqli->close();


$ret['status'] = 1;
$ret['ErrorMsg'] = '';
$ret['comment'] = $result;


exit (json_encode($ret,JSON_UNESCAPED_UNICODE));