<?php
include ('../../header.php');

if(!check_login()) {
	$ret['ErrorMsg'] = '没有登录';
	exit (json_encode($ret));
}

if(empty($_POST['message_id'])  ) {
	$ret['ErrorMsg'] = '参数错误';
	exit (json_encode($ret));
}

$myid = 0;
if(isset($_POST['myid']) && intval($_POST['myid']) > 0 ) {

	$myid = $_POST['myid'] + 0 ;
}
else {
	$myid = $user['user_id'];
}

$message_id = $_POST['message_id'] + 0 ;


if (!($stmt = $mysqli->prepare("SELECT * FROM message m LEFT JOIN userinfo u ON m.author_id = u.user_id WHERE m.message_id = ? "))) {
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

$result = array();

$meta = $stmt->result_metadata();

while ($column = $meta->fetch_field()) {
	 $bindVarsArray[] = &$result[$column->name];
}        
call_user_func_array(array($stmt, 'bind_result'), $bindVarsArray);

while($stmt->fetch()) {
}

$stmt->close();

$result['like_status'] = get_like_status($result['message_id'],$myid);

$mysqli->close();

$ret['status'] = 1;
$ret['ErrorMsg'] = '';
$ret['message'] = $result;
exit (json_encode($ret,JSON_UNESCAPED_UNICODE));