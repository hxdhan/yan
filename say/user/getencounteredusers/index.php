<?php
include ('../../header.php');
$ret['ErrorMsg'] = '接口已不再使用';
exit (json_encode($ret));


if(!check_login()) {
	$ret['ErrorMsg'] = '没有登录';
	exit (json_encode($ret));
}

$user_id = $user['user_id'];

if(isset($_POST['encounter_userid'])) {
	$encounter_userid = $_POST['encounter_userid'] + 0;
}


$start_id = PHP_INT_MAX;

if(isset($_POST['start_id']) && intval($_POST['start_id']) > 0 ) {
	$start_id = $_POST['start_id'] + 0;
}

$count = 20;


if(isset($_POST['count']) && intval($_POST['count']) > 0 ) {
	$count = $_POST['count'] + 0;
}

$myid = 0;
if(isset($_POST['myid']) && intval($_POST['myid']) > 0 ) {
	$myid = $_POST['myid'] + 0 ;
}
else {
	$myid = $user['user_id'];
}


if (!($stmt = $mysqli->prepare("SELECT encounter_id, user_id,message_id,newtoencounteruser FROM userencounter WHERE encounter_userid = ? and encounter_id < ? order by encounter_id desc limit ?"))) {
		$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		exit (json_encode($ret));	
			
	}

if (!$stmt->bind_param("iii", $encounter_userid, $start_id,$count)) {
	  $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

if (!$stmt->execute()) {
	  $ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

$stmt->bind_result($encounter_id,$user_id,$message_id,$newtoencounteruser);

$es = array();

while($stmt->fetch()) {
	$c = array();
	$c['encounter_id'] = $encounter_id;
	$c['user_id'] = $user_id;
	$c['encounter_count'] = count(explode(',',$message_id));
	$c['newtoencounteruser'] = $newtoencounteruser;

	$es[] = $c;
}

$stmt->close();



if (!($stmt = $mysqli->prepare("SELECT * FROM userinfo WHERE user_id = ?"))) {
		$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		exit (json_encode($ret));	
			
}

$encounters = array();

foreach($es as $e) {
 
if (!$stmt->bind_param("i", $e['user_id'])) {
	  $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt1->error;
		exit (json_encode($ret));
	}
	if (!$stmt->execute()) {
	  $ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt1->error;
		exit (json_encode($ret));
	}
	$stmt->store_result();
	
	$meta = $stmt->result_metadata();
	
	while ($column = $meta->fetch_field()) {
	   $bindVarsArray[] = &$result[$column->name];
	}        
	call_user_func_array(array($stmt, 'bind_result'), $bindVarsArray);
	
	while($stmt->fetch()) {
		$c = array();
		$c['encounter_id'] = $e['encounter_id'];
		$c['user_id'] = $e['user_id'];
		$c['encounter_count'] = $e['encounter_count'];
		$c['newtoencounteruser'] = $e['newtoencounteruser'];

		foreach($result as $key => $val) { 
			$c[$key] = $val; 
		}
		$c['follow_user_count'] = get_my_follow_count($c['user_id']);
		$c['fan_count'] = get_fan_count($c['user_id']);
		$c['friend_count'] = get_friend_count($c['user_id']);
		$c['is_my_follow_user'] = is_my_follow_user($myid, $c['user_id']);
		$c['is_my_fan_user'] = is_my_fun($myid, $c['user_id']);
		$encounters[] = $c;
	}
}
$stmt->close();
$mysqli->close();

$ret['status'] = 1;
$ret['ErrorMsg'] = '';
$ret['encounter_userid'] = $encounter_userid;
$ret['encounters'] = $encounters;

exit (json_encode($ret,JSON_UNESCAPED_UNICODE));