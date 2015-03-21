<?php
include ('../../header.php');

if(!check_login()) {
	$ret['ErrorMsg'] = '没有登录';
	exit (json_encode($ret));
}

if(!isset($_POST['wall_id'])) {
	$ret['ErrorMsg'] = '参数错误';
	exit (json_encode($ret));
}
$wall_id = $_POST['wall_id'] + 0 ;

$start = PHP_INT_MAX;

if(isset($_POST['start_id']) && intval($_POST['start_id']) > 0) {
	$start = $_POST['start_id'] + 0 ;
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


if (!($stmt = $mysqli->prepare("select * from message where wall_id = ? and message_id < ? order by message_id desc limit ?"))) {
	$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	exit (json_encode($ret));	
		
}

if (!$stmt->bind_param("iii",  $wall_id, $start,$count)) {
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

$messages = array();



while($stmt->fetch()) {
	$ele = array();
	foreach($result as $key => $val) {
		$ele[$key] = $val;
	}
	$ele['like_status'] = get_like_status($ele['message_id'],$myid);
	
	$user = get_userinfo($ele['author_id']);
	
	$ele = array_merge($ele,$user);
	
	update_receive_count($ele['message_id']);
	
	$messages[] = $ele;
}

$stmt->close();

$mysqli->close();


$ret['status'] = 1;
$ret['ErrorMsg'] = '';
$ret['messages'] = $messages;

exit (json_encode($ret,JSON_UNESCAPED_UNICODE));