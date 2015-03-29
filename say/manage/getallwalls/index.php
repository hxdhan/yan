<?php
include ('../../header.php');

if(!check_login()) {
	$ret['ErrorMsg'] = '没有登录';
	exit (json_encode($ret));
}

if(empty($_POST['key'])) {
	$ret['ErrorMsg'] = '参数错误';
	exit (json_encode($ret));
}

if(md5($admin_key) != $_POST['key']) {
	$ret['ErrorMsg'] = '非管理员';
	exit (json_encode($ret));
}

$start_id = PHP_INT_MAX;

if(isset($_POST['start_id']) && intval($_POST['start_id'] > 0) ){
	$start_id = $_POST['start_id'] +0 ;
}

$count = 20;
if(isset($_POST['count']) && intval($_POST['count'] > 0)) {
	$count = $_POST['count'] +0 ;
}

if (!($stmt = $mysqli->prepare("SELECT * from msgwall WHERE wall_id < ? order by wall_id desc limit ? "))) {
	$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	exit (json_encode($ret));	
		
}

if (!$stmt->bind_param("ii", $start_id, $count)) {
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

$all_walls = array();
while($stmt->fetch()) {
	$ele = array();
	foreach($result as $key => $val) {
		$ele[$key] = $val;
	}
	$all_walls[] = $ele;
}


$ret['status'] = 1;
$ret['ErrorMsg'] = '';
$ret['walls'] = $all_walls;
exit (json_encode($ret,JSON_UNESCAPED_UNICODE));