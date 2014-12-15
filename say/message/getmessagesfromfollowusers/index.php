<?php
include ('../../header.php');

if(!check_login()) {
	$ret['ErrorMsg'] = '没有登录';
	exit (json_encode($ret));
}

$user_id = $user['user_id'];

if(isset($_POST['user_id']) && $_POST['user_id'] != '' ) {
	$user_id = $_POST['user_id'];
}

$start = 0;

if(isset($_POST['start_id']) && intval($_POST['start_id']) > 0 ) {
	$start = $_POST['start_id'] + 0 ;
}

$count = 20;

if(isset($_POST['count']) &&  intval($_POST['count']) > 0 ) {
	$count = $_POST['count'] + 0 ;
}

$myid = 0;

if(isset($_POST['myid']) && intval($_POST['myid']) > 0 ) {

	$myid = $_POST['myid'] + 0 ;
}
else {
	$myid = $user['user_id'];
}

if($start == 0) {
	if (!($stmt = $mysqli->prepare("SELECT m.* FROM message m,userfollow u where m.author_id = u.follow_userid and u.user_id = ? ORDER BY m.message_id desc LIMIT ? "))) {
		$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		exit (json_encode($ret));	
			
	}
	
	if (!$stmt->bind_param("ii",  $user_id, $count)) {
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

}
elseif($start > 0) {

	if (!($stmt = $mysqli->prepare("SELECT m.* FROM message m,userfollow u WHERE m.author_id = u.follow_userid and u.user_id = ? AND message_id < ? ORDER BY m.message_id desc LIMIT ? "))) {
		$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		exit (json_encode($ret));	
			
	}
	
	if (!$stmt->bind_param("iii",  $user_id, $start, $count)) {
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

}

$results = array();


while($stmt->fetch()) {
	$c = array();
	foreach($result as $key => $val) { 
		$c[$key] = $val; 
	} 
	$c['like_status'] = get_like_status($c['message_id'],$myid);
	
	$user = get_userinfo($c['author_id']);
	
	$c = array_merge($c,$user);
	$results[] = $c;
	unset($c);
}

$stmt->close();

$mysqli->close();

$ret['status'] = 1;
$ret['ErrorMsg'] = '';
$ret['messages'] = $results;
exit (json_encode($ret,JSON_UNESCAPED_UNICODE));
