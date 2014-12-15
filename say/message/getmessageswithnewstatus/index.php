<?php
include ('../../header.php');

if(!check_login()) {
	$ret['ErrorMsg'] = '没有登录';
	exit (json_encode($ret));
}

$user_id = $user['user_id'];

$myid = 0;

if(isset($_POST['myid']) && intval($_POST['myid']) > 0 ) {

	$myid = $_POST['myid'] + 0 ;
}
else {
	$myid = $user['user_id'];
}


$new_comment = 1;
$new_like = 1;

if (!($stmt = $mysqli->prepare("SELECT * FROM message WHERE author_id = ? AND (new_comment = ? OR new_like = ?)  ORDER BY new_time DESC "))) {
	$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	exit (json_encode($ret));	
		
}

if (!$stmt->bind_param("iii", $user_id, $new_comment, $new_like)) {
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

$results = array();

while ($stmt->fetch()) {
	$c = array();
	foreach($result as $key => $val) {
		$c[$key] = $val;
	}
	$c['like_status'] = get_like_status($c['message_id'],$myid);
	
	$user = get_userinfo($c['author_id']);
	
	$c = array_merge($c,$user);
	$results[] = $c;
}

$stmt->close();
	
$mysqli->close();
$ret['status'] = 1;
$ret['ErrorMsg'] = '';
$ret['messages'] = $results;
exit (json_encode($ret,JSON_UNESCAPED_UNICODE));