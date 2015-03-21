<?php
include ('../../header.php');

if(!check_login()) {
	$ret['ErrorMsg'] = '没有登录';
	exit (json_encode($ret));
}

$user_id = $user['user_id'];

if(empty($_POST['category_id']) || empty($_POST['longitude']) || empty($_POST['latitude']) ) {
	$ret['ErrorMsg'] = '参数错误';
	exit (json_encode($ret));
}

$duration = 0;

if(isset($_POST['duration']) && intval($_POST['duration']) > 0 ) {
	$duration = $_POST['duration'] + 0 ;
}

$text = '';

$category_id = $_POST['category_id'];
$longitude = $_POST['longitude'];
$latitude = $_POST['latitude'];
if(isset($_POST['text']) && $_POST['text'] != '') {
	$text = $_POST['text'];

}

$platform = '';
if(isset($_POST['platform']) && $_POST['platform'] != '') {
	$platform = $_POST['platform'];
}


$time = time();

$date_name = date('Ymd');
$file_path = "../../static/".$date_name;
if(!file_exists($file_path)) {
	mkdir($file_path);
	
}

$file_url = $static_path.$date_name.'/';

$alowed_voice = array('amr'=>'application/octet-stream','mp3'=>'audio/mpeg');
$finfo = new finfo(FILEINFO_MIME_TYPE);

$voice_url = '';

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

$allowed_image = array('jpg' => 'image/jpeg','png' => 'image/png','gif' => 'image/gif');

$image_url = '';

if(isset($_FILES['image'])) {
	
	$ext = array_search($finfo->file($_FILES['image']['tmp_name']),$allowed_image);

	if($ext === false) {
		$ret['ErrorMsg'] = '图片格式错误';
		exit (json_encode($ret));
		
	}
	$prefix_name = microtime(true) * 10000;
	$file_name = $prefix_name .'.'.$ext;
	$small_file_name = $prefix_name .'_small.'.$ext;
	$file_path_name = $file_path."/".$file_name;
	$small_file_path_name = $file_path."/".$small_file_name;
	rename($_FILES['image']['tmp_name'],$file_path_name);
	half_image($file_path_name, $small_file_path_name);
	$image_url = $file_url . $file_name;
}

$smile_id = 0;
if(isset($_POST['smile_id'])) {
	$smile_id = $_POST['smile_id'];
}
$orig_message_id = 0;
if(isset($_POST['orig_message_id'])) {
	$orig_message_id = $_POST['orig_message_id'];
}

$image_color = null;

if(isset($_POST['image_color']) &&  $_POST['image_color'] != '') {
	$image_color = $_POST['image_color'];
}

$wall_id = 0;
if(isset($_POST['wall_id']) &&  $_POST['wall_id'] != '') {
	$wall_id = $_POST['wall_id'] + 0 ;
}

$wall_name = '';
$award_type = 0;
if($wall_id > 0) {
	if (!($stmt = $mysqli->prepare("select award_type, name from msgwall where wall_id = ?"))) {
		$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		exit (json_encode($ret));	
		
	}
	
	if (!$stmt->bind_param("i", $wall_id)) {
		$ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}
	
	if (!$stmt->execute()) {
		$ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}
	$stmt->bind_result($award_type, $wall_name);
	$stmt->fetch();
	$stmt->close();
	
	
}

$message_should_hongbao = 0;

if($award_type > 0) {
	if (!($stmt = $mysqli->prepare("select count(*) from  message where author_id = ? and wall_id = ?"))) {
		$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		exit (json_encode($ret));	
	}
	if (!$stmt->bind_param("ii", $user_id, $wall_id)) {
		$ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}
	if (!$stmt->execute()) {
		$ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}
	$stmt->bind_result($user_message_count);
	$stmt->fetch();
	$stmt->close();
	
	//if user have not add message at wall , then should send hongbao to user 
	if($user_message_count == 0) {
		$message_should_hongbao = 1;
	}
}

if (!($stmt = $mysqli->prepare("INSERT INTO message (author_id, category_id, voice_url, duration, longitude, latitude, time, smile_id, original_message_id, image_url, image_color, text, new_time, platform,wall_id,wall_name) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)"))) {
	$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	exit (json_encode($ret));	
		
}

if (!$stmt->bind_param("iisiddiiisssisis", $user_id, $category_id, $voice_url, $duration, $longitude, $latitude, $time, $smile_id, $orig_message_id, $image_url, $image_color, $text, $time, $platform, $wall_id, $wall_name)) {
  $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
	exit (json_encode($ret));
}

if (!$stmt->execute()) {
  $ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
	exit (json_encode($ret));
}

$stmt->close();

$message_id = $mysqli->insert_id;

if (!($stmt = $mysqli->prepare("update category set message_count = message_count + 1 where category_id = ? "))) {
	$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	exit (json_encode($ret));	
		
}

if (!$stmt->bind_param("i", $category_id)) {
  $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
	exit (json_encode($ret));
}

if (!$stmt->execute()) {
  $ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
	exit (json_encode($ret));
}

$stmt->close();

//update last message time
if (!($stmt = $mysqli->prepare("update userinfo set last_message_time = ? where user_id = ?"))) {
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

//update user score
$updated = update_user_point($user_id, 3);

if($message_should_hongbao == 1) {
	$get_hongbao = send_hongbao($user_id);
	if($get_hongbao > 0) {
		$content = "您获得一个支付宝红包，代码为：" . $get_hongbao;
		tieer_to_user($user_id, $content);
	}
}
if($award_type > 0 && $updated > 0) {
	$get_hongbao = send_hongbao($user_id);
	if($get_hongbao > 0) {
		$content = "您获得一个支付宝红包，代码为：" . $get_hongbao;
		tieer_to_user($user_id, $content);
	}
}
/**
if($wall_id > 0) {
	//notification
	$zero_id = 0;
	if($owner_user != $user_id) {
		$n_type = $noti_type['wall_new'];
		
		if($rets = $mysqli->query("SELECT * FROM usrnotification WHERE user_id = $owner_user AND active_userid = $zero_id AND type = '$n_type' AND wall_id = $wall_id ")) {
			if($rets->num_rows > 0) {
				if(!$mysqli->query("DELETE FROM usrnotification WHERE user_id = $owner_user AND active_userid = $zero_id AND type = '$n_type' AND wall_id = $wall_id ")) {
					printf("Error: %s\n", $mysqli->error);
				}
				
			}
		}
		else {
			printf("Error: %s\n", $mysqli->error);
		}
		
		if(!$mysqli->query("INSERT INTO usrnotification (user_id, active_userid, wall_id, type, time) VALUES ($owner_user, $zero_id, $wall_id, '$n_type', $time)")) {
			printf("Error: %s\n", $mysqli->error);
		}
		
	}
	
	//send all favourate user
	$fav_users = array();
	if($get_favourate = $mysqli->query("select user_id from msgwallfavourates where wall_id = $wall_id")) {
		while($favourate = $get_favourate->fetch_row()) {
			$fav_users[] = $favourate[0];
		}
	}
	else {
			printf("Error: %s\n", $mysqli->error);
	}
	
	foreach($fav_users as $fav_userid) {
		if($fav_userid != $user_id) {
			$n_type = $noti_type['fav_wall_new'];
			
			if($rets = $mysqli->query("SELECT * FROM usrnotification WHERE user_id = $fav_userid AND active_userid = $zero_id AND type = '$n_type' AND wall_id = $wall_id ")) {
			if($rets->num_rows > 0) {
				if(!$mysqli->query("DELETE FROM usrnotification WHERE user_id = $fav_userid AND active_userid = $zero_id AND type = '$n_type' AND wall_id = $wall_id ")) {
					printf("Error: %s\n", $mysqli->error);
				}
				
			}
		}
		else {
			printf("Error: %s\n", $mysqli->error);
		}
		
		if(!$mysqli->query("INSERT INTO usrnotification (user_id, active_userid, wall_id, type, time) VALUES ($fav_userid, $zero_id, $wall_id, '$n_type', $time)")) {
			printf("Error: %s\n", $mysqli->error);
		}
		}
	}
}

//notify follow user
$follow_users = array();
if($get_follow = $mysqli->query("select user_id from userfollow where follow_userid = $user_id")) {
	while($follow = $get_follow->fetch_row()) {
		$follow_users[] = $follow[0];
	}
}
else {
		printf("Error: %s\n", $mysqli->error);
}

foreach($follow_users as $follow_id) {
	if($follow_id != $user_id) {
		$n_type = $noti_type['post'];
		if($rets = $mysqli->query("SELECT * FROM usrnotification WHERE user_id = $follow_id AND active_userid = $user_id AND type = '$n_type' ")) {
		if($rets->num_rows > 0) {
			if(!$mysqli->query("DELETE FROM usrnotification WHERE user_id = $follow_id AND active_userid = $user_id AND type = '$n_type' ")) {
				printf("Error: %s\n", $mysqli->error);
			}
			
		}
	}
	else {
		printf("Error: %s\n", $mysqli->error);
	}
	
	if(!$mysqli->query("INSERT INTO usrnotification (user_id, active_userid,  type, time) VALUES ($follow_id, $user_id, '$n_type', $time)")) {
		printf("Error: %s\n", $mysqli->error);
	}
	}
}
**/

if (!($stmt = $mysqli->prepare("SELECT m.*,u.* FROM message m,userinfo u  WHERE m.author_id = u.user_id and message_id = ?"))) {
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

$stmt->store_result();

$meta = $stmt->result_metadata();

while ($column = $meta->fetch_field()) {
   $bindVarsArray[] = &$result[$column->name];
}        
call_user_func_array(array($stmt, 'bind_result'), $bindVarsArray);

$stmt->fetch();

$stmt->close();

update_receive_count($message_id);

//push 
if($wall_id > 0) {
	if(!$mysqli->query("update msgwall set newmsg_count = newmsg_count + 1, message_count = message_count + 1 where wall_id = $wall_id")) {
		printf("Error: %s\n", $mysqli->error);
	}
	if(!$mysqli->query("update msgwallfavourates set newmsg_count = newmsg_count + 1 where wall_id = $wall_id")) {
		printf("Error: %s\n", $mysqli->error);
	}
	
	if($get_push = $mysqli->query("SELECT push_registration FROM user where user_id = $owner_user")) {
		$push_registration = $get_push->fetch_row()[0];
	}
	else {
			printf("Error: %s\n", $mysqli->error);
	}
	if(!empty($push_registration)) {
		$data = '';
		$send_no = get_push_id();
	
		$data.= 'sendno='.$send_no;
	
		$data.= '&app_key='.$app_key;
		$data.= '&receiver_type='.$receive_type;
		$data.= '&receiver_value='.$push_registration;
	
		$verification_code = $send_no.$receive_type.$push_registration.$mast_secret;
	
		$data.='&verification_code='.md5($verification_code);
		$data.='&msg_type='.$msg_type;
		$ca['n_content'] = '有人在你的墙上贴贴儿';
		$ca["n_extras"] = array('ios'=>array('badge'=>1,'sound'=>'drop.caf','content-available'=>1),'type'=>'wallnew');
		$data.='&msg_content='.json_encode($ca);
		$data.='&platform='.$platform;
		$data.='&apns_production='.$apns_production;
		
		curl_post($data, $push_url);
	
	
	}
	
}


$mysqli->close();
$ret['status'] = 1;
$ret['ErrorMsg'] = '';
$ret['message'] = $result;


exit (json_encode($ret,JSON_UNESCAPED_UNICODE));