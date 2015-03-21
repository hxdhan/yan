<?php
include ('../../header.php');

if(!check_login()) {
	$ret['ErrorMsg'] = '没有登录';
	exit (json_encode($ret));
}

if(empty($_POST['user_id'])  ) {
	$ret['ErrorMsg'] = '参数错误';
	exit (json_encode($ret));
}

$user_id = $_POST['user_id'] + 0;

$start_id = PHP_INT_MAX;

if(isset($_POST['start_id']) && intval($_POST['start_id']) > 0) {
	$start_id = $_POST['start_id'];
}

$count = 10;

if(isset($_POST['count']) && intval($_POST['count']) > 0 ) {

	$count = $_POST['count'] + 0 ;
}

if (!($stmt = $mysqli->prepare("select w.*,f.newmsg_count from msgwall w, msgwallfavourates f where w.wall_id = f.wall_id and f.user_id =? and f.favourate_id < ? order by f.favourate_id desc limit ? "))) {
	$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	exit (json_encode($ret));	
		
}

if (!$stmt->bind_param("iii",  $user_id, $start_id, $count)) {
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
while($stmt->fetch()) {
	$ele = array();
	foreach($result as $key => $val) { 
		$ele[$key] = $val; 
	}
	$ele['favourate_count'] = get_wallfavourate_count($ele['wall_id']);
	
	$results[] = $ele;
	
}

$mysqli->close();

$ret['status'] = 1;
$ret['ErrorMsg'] = '';
$ret['walls'] = $results;

exit (json_encode($ret,JSON_UNESCAPED_UNICODE));