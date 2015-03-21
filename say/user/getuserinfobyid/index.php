<?php
include ('../../header.php');

if(!check_login()) {
	$ret['ErrorMsg'] = '没有登录';
	exit (json_encode($ret));
}

if(isset($_POST['user_id']) && $_POST['user_id'] != '' ) {
	$user_id = $_POST['user_id'] + 0;
}

if (!($stmt = $mysqli->prepare("SELECT u.cellphone, ui.* FROM user u, userinfo ui WHERE u.user_id = ui.user_id and  u.user_id = ? "))) {
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


$result = array();

$meta = $stmt->result_metadata();

while ($column = $meta->fetch_field()) {
	 $bindVarsArray[] = &$result[$column->name];
}        
call_user_func_array(array($stmt, 'bind_result'), $bindVarsArray);

while($stmt->fetch()) {
}

$stmt->close();

$mysqli->close();

$ret['status'] = 1;
$ret['ErrorMsg'] = '';
$ret['user'] = $result;

exit (json_encode($ret,JSON_UNESCAPED_UNICODE));