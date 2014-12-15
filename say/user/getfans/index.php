<?php
include ('../../header.php');

if(!check_login()) {
	$ret['ErrorMsg'] = '没有登录';
	exit (json_encode($ret));
}

$user_id = $user['user_id'];

if(isset($_POST['user_id']) && $_POST['user_id'] != '') {
	$user_id = $_POST['user_id'] + 0;
}


$start_id = PHP_INT_MAX;

if(isset($_POST['start_id']) && intval($_POST['start_id']) > 0 ) {
	$start_id = $_POST['start_id'] + 0;
}

$count = 10;


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


if (!($stmt = $mysqli->prepare("select follow_id,user_id,newtofollowuser FROM userfollow WHERE follow_userid = ? and follow_id < ? order by follow_id desc limit ?"))) {
		$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		exit (json_encode($ret));	
			
}

if (!$stmt->bind_param("iii", $user_id, $start_id,$count)) {
	  $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
}

if (!$stmt->execute()) {
	  $ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
}

$stmt->bind_result($follow_id, $user_id,$newtofollowuser);

$es = array();

while($stmt->fetch()) {
	$a['user_id'] = $user_id;
	$a['follow_id'] = $follow_id;
	$a['newtofollowuser'] = $newtofollowuser;
	$es[] = $a;
}

$stmt->close();

if (!($stmt = $mysqli->prepare("SELECT * FROM userinfo WHERE user_id = ?"))) {
		$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		exit (json_encode($ret));	
			
	}

$fans = array();

foreach($es as $e) {

	if (!$stmt->bind_param("i", $e['user_id'])) {
	  $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt1->error;
		exit (json_encode($ret));
	}
	if (!$stmt->execute()) {
	  $ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt1->error;
		exit (json_encode($ret));
	}
	
	$result = array();
	$bindVarsArray = array();
	$meta = $stmt->result_metadata();
	
	while ($column = $meta->fetch_field()) {
	   $bindVarsArray[] = &$result[$column->name];
	}  
	$meta->free_result();	
	call_user_func_array(array($stmt, 'bind_result'), $bindVarsArray);
	
	while($stmt->fetch()) {
		$c = array();
		foreach($result as $key => $val) { 
			$c['follow_id'] = $e['follow_id'];
			$c['newtofollowuser'] = $e['newtofollowuser'];
			$c[$key] = $val; 
		}
		$c['follow_user_count'] = get_my_follow_count($c['user_id']);
		$c['fan_count'] = get_fan_count($c['user_id']);
		$c['friend_count'] = get_friend_count($c['user_id']);
		$c['is_my_follow_user'] = is_my_follow_user($myid, $c['user_id']);
		$c['is_my_fan_user'] = is_my_fun($myid, $c['user_id']);
		$fans[] = $c;
	}
	
}
$stmt->close();
$mysqli->close();

$ret['status'] = 1;
$ret['ErrorMsg'] = '';
$ret['fans'] = $fans;

exit (json_encode($ret,JSON_UNESCAPED_UNICODE));