<?php
include ('../../header.php');

$ret['ErrorMsg'] = '接口已不再使用';
exit (json_encode($ret));

if(!check_login()) {
	$ret['ErrorMsg'] = '没有登录';
	exit (json_encode($ret));
}

$user_id = $user['user_id'];

if(isset($_POST['user_id'])) {
	$user_id = $_POST['user_id'] + 0 ;
}


if(empty($_POST['encounter_userid']) or empty($_POST['message_id'])) {
	$ret['ErrorMsg'] = '参数错误';
	exit (json_encode($ret));
}

$encounter_userid = $_POST['encounter_userid'] + 0;



$message = $_POST['message_id'];


if (!($stmt = $mysqli->prepare("SELECT message_id,time FROM userencounter WHERE user_id = ? and encounter_userid = ?"))) {
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

$stmt->bind_result($message_id,$time_str);

while($stmt->fetch()) {
}


if($message_id) {
	
	$old_message = explode(",", $message_id);
	$time_array = explode(',',$time_str);
	//echo $old_message;
	$new_message = explode(",", $message);
	$time_count = count($new_message);
	$time = time();
	
	for($i=0;$i<$time_count;$i++) {
		array_push($time_array,$time);
	}
	
  $update_message = array_unique(array_merge($old_message,$new_message));
  $um = implode(",",$update_message);
  $ut = implode(',',$time_array);
	
	if (!($stmt = $mysqli->prepare("UPDATE  userencounter SET message_id = ?, time = ? WHERE user_id = ? and encounter_userid = ?"))) {
		$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		exit (json_encode($ret));	
			
	}

	if (!$stmt->bind_param("ssii", $um, $ut, $user_id, $encounter_userid)) {
	  $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

	if (!$stmt->execute()) {
	  $ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}
}


else {
	
	$time = time();
	$time_array = array();
	$update_message = explode(',',$message);
	$time_count = count($update_message);
	
	for($i=0; $i<$time_count; $i++) {
		$time_array[] = $time;
	}
	$um = $message;
	$ut = implode(',',$time_array);

	if (!($stmt = $mysqli->prepare("INSERT INTO userencounter (user_id,encounter_userid,message_id,time) VALUES (?,?,?,?)"))) {
		$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		exit (json_encode($ret));	
			
	}



 
	if (!$stmt->bind_param("iiss", $user_id, $encounter_userid,$um,$ut)) {
	  $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

	if (!$stmt->execute()) {
	  $ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

	

}

$results = array();

$update_count = count($update_message);
for($i=0; $i<$update_count; $i++) {
	 
	if ($result = $mysqli->query("SELECT m.*,u.* FROM message m ,userinfo u WHERE m.author_id = u.user_id AND m.message_id = {$update_message[$i]}")) {
	
		if($result->num_rows > 0) {
			$row = $result->fetch_assoc();
			$row['message_id'] = $row['message_id'] +0;
			$row['author_id'] = $row['author_id'] +0;

			$row['time'] = $row['time'] +0;
			$row['new_time'] = $row['new_time'] +0;
			$row['encounter_time'] = $time_array[$i] + 0 ;
			$results[] = $row;
		}
	}
		
}


/**
if($get_nickname = $mysqli->query("SELECT nickname FROM userinfo WHERE user_id = $user_id")) {
	$nickname = $get_nickname->fetch_assoc()['nickname'];
}

if($get_encountername = $mysqli->query("SELECT push_registration FROM user WHERE user_id = $encounter_userid")) {
	$receive_value = $get_encountername->fetch_assoc()['push_registration'];
}

$data = '';
$send_no = get_push_id();

$data.= 'sendno='.$send_no;

$data.= '&app_key='.$app_key;
$data.= '&receiver_type='.$receive_type;
$data.= '&receiver_value='.$receive_value;

$verification_code = $send_no.$receive_type.$receive_value.$mast_secret;

$data.='&verification_code='.md5($verification_code);
$data.='&msg_type='.$msg_type;
$c['n_content'] = $nickname.'邂逅了你';
$c["n_extras"] = array('ios'=>array('badge'=>1,'sound'=>'default','content-available'=>1),'type'=>'encounter');
$data.='&msg_content='.json_encode($c);
$data.='&platform='.$platform;


$ch = curl_init();

curl_setopt($ch,CURLOPT_URL,$push_url);
curl_setopt($ch,CURLOPT_POST,1);

curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//$response = curl_exec($ch);
//echo $response;
curl_exec($ch);

**/


$stmt->close();


$mysqli->close();

$ret['status'] = 1;
$ret['ErrorMsg'] = '';
$ret['user_id'] = $user_id;
$ret['encounter_userid'] = $encounter_userid;
$ret['encounters'] = $results;

exit (json_encode($ret));