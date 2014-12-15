<?php
include ('../../header.php');

if(!check_login()) {
	$ret['ErrorMsg'] = '没有登录';
	exit (json_encode($ret));
}

if(empty($_POST['inviter_userid'])  || empty($_POST['invitee_userid']) || empty($_POST['longitude']) || empty($_POST['latitude'])) {
	$ret['ErrorMsg'] = '参数错误';
	exit (json_encode($ret));
}

$inviter_userid = $_POST['inviter_userid'] + 0 ;
$invitee_userid = $_POST['invitee_userid'] + 0 ;

$message = '';

/**
if(isset($_POST['message']) && $_POST['message'] !== '') {
	$message = $_POST['message']  ;
}
**/
if(!empty($_POST['message'])) {
	$message = $_POST['message']  ;
}


$longitude = $_POST['longitude'] + 0 ;
$latitude = $_POST['latitude'] + 0 ;

$time = time();

if(!($stmt = $mysqli->prepare("INSERT INTO usrfriendinvite (inviter_userid,invitee_userid,message,time,longitude,latitude) VALUES (?,?,?,?,?,?) "))) {
  $ret['ErrorMsg'] = "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
  exit (json_encode($ret));	
}

if(!($stmt->bind_param("iisidd",$inviter_userid, $invitee_userid, $message,$time,$longitude,$latitude))) {
  $ret['ErrorMsg'] = "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
  exit (json_encode($ret));
}

if (!$stmt->execute()) {
	  $ret['ErrorMsg'] = "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
	  exit (json_encode($ret));
}

$stmt->close();

//notification.

$n_type = $noti_type['invi'];

if($rets = $mysqli->query("SELECT * FROM usrnotification WHERE user_id = $invitee_userid AND active_userid = $inviter_userid AND type = '$n_type' ")) {
	if($rets->num_rows > 0) {
		if(!$mysqli->query("DELETE FROM usrnotification WHERE user_id = $invitee_userid AND active_userid = $inviter_userid AND type = '$n_type' ")) {
			printf("Error: %s\n", $mysqli->error);
		}
		
	}
}
else {
	printf("Error: %s\n", $mysqli->error);
}
	
if(!$mysqli->query("INSERT INTO usrnotification (user_id, active_userid, type, time) VALUES ($invitee_userid, $inviter_userid, '$n_type', $time)")) {
	printf("Error: %s\n", $mysqli->error);
}

//send message 
if($get_nickname = $mysqli->query("SELECT nickname FROM userinfo WHERE user_id = $inviter_userid")) {
	$nickname = $get_nickname->fetch_assoc()['nickname'];
}

if($get_registration = $mysqli->query("SELECT push_registration FROM user WHERE user_id = $invitee_userid")) {
	$receive_value = $get_registration->fetch_assoc()['push_registration'];
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

$c['n_content'] = $nickname.'邀请你成为好友';

$c["n_extras"] = array('ios'=>array('badge'=>1,'sound'=>'drop.caf','content-available'=>1),'type'=>'friendinvite');
$data.='&msg_content='.json_encode($c);
$data.='&platform='.$platform;
$data.='&apns_production='.$apns_production;

$ch = curl_init();

curl_setopt($ch,CURLOPT_URL,$push_url);
curl_setopt($ch,CURLOPT_POST,1);

curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//$response = curl_exec($ch);
//echo $response;
curl_exec($ch);

$mysqli->close();

$ret['status'] = 1;
$ret['ErrorMsg'] = '';
$ret['inviter_userid'] = $inviter_userid;
$ret['invitee_userid'] = $invitee_userid;
exit (json_encode($ret));