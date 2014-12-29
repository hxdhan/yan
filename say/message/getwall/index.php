<?php
include ('../../header.php');

if(!check_login()) {
	$ret['ErrorMsg'] = '没有登录';
	exit (json_encode($ret));
}



if(empty($_POST['wall_id'])) {
	$ret['ErrorMsg'] = '参数错误';
	exit (json_encode($ret));
}
$wall_id = $_POST['wall_id'] + 0 ;


if (!($stmt = $mysqli->prepare("select * from msgwall where wall_id = ?"))) {
	$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	exit (json_encode($ret));	
		
}

if (!$stmt->bind_param("i",  $wall_id)) {
  $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
	exit (json_encode($ret));
}

if (!$stmt->execute()) {
  $ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
	exit (json_encode($ret));
}

//$stmt->store_result();

$meta = $stmt->result_metadata();

while ($column = $meta->fetch_field()) {
   $bindVarsArray[] = &$result[$column->name];
}        
call_user_func_array(array($stmt, 'bind_result'), $bindVarsArray);

$stmt->fetch();

$stmt->close();

$result['favourate_count'] = get_wallfavourate_count($result['wall_id']);
$result['message_count'] = get_wallmsg_count($result['wall_id']);

$mysqli->close();


$ret['status'] = 1;
$ret['ErrorMsg'] = '';
$ret['wall'] = $result;

exit (json_encode($ret,JSON_UNESCAPED_UNICODE));