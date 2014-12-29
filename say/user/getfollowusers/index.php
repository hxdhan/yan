<?php
include ('../../header.php');

if(!check_login()) {
	$ret['ErrorMsg'] = '没有登录';
	exit (json_encode($ret));
}

$user_id = $user['user_id'];

if(isset($_POST['user_id'])) {

	$user_id = $_POST['user_id'] + 0 ;
}

$count = 20;


if(isset($_POST['count']) && intval($_POST['count']) > 0 ) {

	$count = $_POST['count'] + 0 ;
}

$myid = 0;
if(isset($_POST['myid']) && intval($_POST['myid']) > 0 ) {
	$myid = $_POST['myid'] + 0 ;
}
else {
	$myid = $user['user_id'];
}

$start = PHP_INT_MAX;

if(isset($_POST['start_id']) && intval($_POST['start_id']) > 0) {
	$start = $_POST['start_id'] + 0 ;
}

if (!($stmt = $mysqli->prepare("SELECT follow_id, follow_userid, newtouser FROM userfollow WHERE user_id = ?  and follow_id < ? order by follow_id desc limit ? "))) {
	$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	exit (json_encode($ret));	
		
}

if (!$stmt->bind_param("iii", $user_id, $start, $count)) {
  $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
	exit (json_encode($ret));
}

if (!$stmt->execute()) {
  $ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
	exit (json_encode($ret));
}

$stmt->store_result();

//$stmt->close();

$meta = $stmt->result_metadata();

while ($column = $meta->fetch_field()) {
   $bindVarsArray[] = &$result[$column->name];
}        
call_user_func_array(array($stmt, 'bind_result'), $bindVarsArray);

$all_users = array();
while($stmt->fetch()) {
	$ele = array();
	foreach($result as $key => $val) {
		$ele[$key] = $val;
	}
	$all_users[] = $ele;
}

$stmt->close();

$results = array();

if (!($stmt = $mysqli->prepare("SELECT * FROM userinfo WHERE user_id = ? "))) {
	$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	exit (json_encode($ret));	
		
}
foreach ($all_users as $all_user) {
	
		$u = array();		
		$u['follow_id'] = $all_user['follow_id'];
		$u['newtouser'] = $all_user['newtouser'];	
		  	
		if (!$stmt->bind_param("i", $all_user['follow_userid'])) {
			$ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
			exit (json_encode($ret));
		}
	
		if (!$stmt->execute()) {
			$ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
			exit (json_encode($ret));
		}
		
		$stmt->store_result();
		
		$meta = $stmt->result_metadata();
		$bindVarsArray = array();
		$result = array();
		while ($column = $meta->fetch_field()) {
			 $bindVarsArray[] = &$result[$column->name];
		}        
		call_user_func_array(array($stmt, 'bind_result'), $bindVarsArray);
			
		while($stmt->fetch()) {
			
			foreach($result as $key => $val) {
				$u[$key] = $val;
			}
			$u['follow_user_count'] = get_my_follow_count($u['user_id']);
			$u['fan_count'] = get_fan_count($u['user_id']);
			$u['friend_count'] = get_friend_count($u['user_id']);
			$u['is_my_follow_user'] = is_my_follow_user($myid, $u['user_id']);
			$u['is_my_fan_user'] = is_my_fun($myid, $u['user_id']);	
		}
		
		$results[] = $u;
		unset($u);
  
	
}

$stmt->close();

$mysqli->close();

//$ret = array();
$ret['status'] = 1;
$ret['ErrorMsg'] = '';
$ret['follows'] = $results;

exit (json_encode($ret,JSON_UNESCAPED_UNICODE));