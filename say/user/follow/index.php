<?php
include ('../../header.php');



if(!check_login()) {
	$ret['ErrorMsg'] = '没有登录';
	exit (json_encode($ret));
}

if(empty($_POST['followuserid']) ) {
	$ret['ErrorMsg'] = '参数错误';
	exit (json_encode($ret));
}

$follow = $_POST['followuserid'] + 0;
$user_id = $user['user_id'];

if(isset($_POST['user_id']) && intval($_POST['user_id']) > 0) {
	$user_id = $_POST['user_id'] + 0;
}

if(!($stmt = $mysqli->prepare("select count(*) from user where user_id = ? "))) {
  $ret['ErrorMsg'] = "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
  exit (json_encode($ret));	
}

if(!($stmt->bind_param("i",$follow))) {
  $ret['ErrorMsg'] = "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
  exit (json_encode($ret));
}

if (!$stmt->execute()) {
	  $ret['ErrorMsg'] = "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
	  exit (json_encode($ret));
}

$stmt->bind_result($count);

$stmt->fetch();

$stmt->close();

if($count == 0) {
	
	$ret['ErrorMsg'] = "没有找到遇言号为{$follow}的用户";
	
	exit (json_encode($ret));
}


if (!($stmt = $mysqli->prepare("SELECT * FROM userfollow WHERE user_id = ? and follow_userid = ?"))) {
	$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	exit (json_encode($ret));	
		
}

if (!$stmt->bind_param("ii", $user_id, $follow)) {
  $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
	exit (json_encode($ret));
}

if (!$stmt->execute()) {
  $ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
	exit (json_encode($ret));
}

$stmt->store_result();


if($stmt->num_rows >= 1 ) {
	
	$ret['ErrorMsg'] = '已经关注过了';
	exit (json_encode($ret));
}

/**
if (!($stmt = $mysqli->prepare("SELECT COUNT(*) FROM userfollow WHERE user_id = ?"))) {
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

$stmt->bind_result($follow_count);

$stmt->fetch();

//var_dump($follow_count);

if($follow_count > $max_follow_count) {
	$ret['status'] = 0;
	$ret['ErrorMsg'] = '超出最大的数量:'.$max_follow_count;
	exit (json_encode($ret));
}
**/

$stmt->close();


if (!($stmt = $mysqli->prepare("INSERT INTO userfollow (user_id, follow_userid) VALUES (?,?)"))) {
	$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	exit (json_encode($ret));	
		
}

if (!$stmt->bind_param("ii", $user_id, $follow)) {
  $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
	exit (json_encode($ret));
}

if (!$stmt->execute()) {
  $ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
	exit (json_encode($ret));
}

$stmt->close();

if (!($stmt = $mysqli->prepare("select count(*) from  userfollow where user_id = ? and follow_userid = ? "))) {
	$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	exit (json_encode($ret));	
		
}

if (!$stmt->bind_param("ii", $follow, $user_id)) {
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

$u2 = $user_id;
$f2 = $follow;

if($count == 1) {

	if($u2 > $f2) {
		list($f2,$u2) = array($u2,$f2);
	}

	
	$get_count = $mysqli->query("SELECT count(*) FROM usrfriend WHERE user_id = {$u2} AND friend_userid = {$f2} ");
	$c = $get_count->fetch_row()[0];
	
	if($c == 0) {
		$mysqli->query("INSERT INTO usrfriend (user_id,friend_userid) VALUES ({$u2},{$f2})");
	}
	
	
}

$time = time();
$n_type = $noti_type['fol'];

//if exist records delete 

if($rets = $mysqli->query("SELECT * FROM usrnotification WHERE user_id = $follow AND active_userid = $user_id AND type = '$n_type'")) {
	if($rets->num_rows > 0) {
		if($mysqli->query("DELETE FROM usrnotification WHERE user_id = $follow AND active_userid = $user_id AND type = '$n_type'")) {
			
		}
		else {
			printf("Error: %s\n", $mysqli->error);
		}
	}
}

if(!$mysqli->query("INSERT INTO usrnotification (user_id, active_userid, type, time) VALUES ($follow, $user_id, '$n_type', $time)")) {
	printf("Error: %s\n", $mysqli->error);
}

if($get_nick = $mysqli->query("SELECT nickname FROM userinfo where user_id = $user_id")) {
	$nickname = $get_nick->fetch_row()[0];
}

//echo $nickname.'send to '.$follow;
if($get_followuser = $mysqli->query("SELECT push_registration FROM user where user_id = $follow")) {
	$receive_value = $get_followuser->fetch_assoc()['push_registration'];
	//echo 'receive value is'.$receive_value;
	if(!empty($receive_value)) {
		$data = '';
		$send_no = get_push_id();
	
		$data.= 'sendno='.$send_no;
	
		$data.= '&app_key='.$app_key;
		$data.= '&receiver_type='.$receive_type;
		$data.= '&receiver_value='.$receive_value;
	
		$verification_code = $send_no.$receive_type.$receive_value.$mast_secret;
	
	
	
		$data.='&verification_code='.md5($verification_code);
		$data.='&msg_type='.$msg_type;
		$ca['n_content'] = $nickname.'关注了你';
		$ca["n_extras"] = array('ios'=>array('badge'=>1,'sound'=>'drop.caf','content-available'=>1),'type'=>'follow');
		$data.='&msg_content='.json_encode($ca);
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
  }
}


$mysqli->close();


$ret['status'] = 1;
$ret['ErrorMsg'] = '';
//$ret['user_id'] = $user_id;
 
exit (json_encode($ret));

