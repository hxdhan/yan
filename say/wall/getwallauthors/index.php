<?php
include ('../../header.php');

if(!check_login()) {
	$ret['ErrorMsg'] = '没有登录';
	exit (json_encode($ret));
}

if(empty($_POST['wall_id'])  ) {
	$ret['ErrorMsg'] = '参数错误';
	exit (json_encode($ret));
}

$wall_id = $_POST['wall_id'] + 0;

$start_id = 0;

if(isset($_POST['start_id']) && intval($_POST['start_id']) > 0) {
	$start_id = $_POST['start_id'];
}

$count = 10;

if(isset($_POST['count']) && intval($_POST['count']) > 0 ) {

	$count = $_POST['count'] + 0 ;
}

if (!($stmt = $mysqli->prepare("select u.* from userinfo u, (SELECT distinct(author_id) FROM `message` where wall_id = ?) a where u.user_id = a.author_Id limit ?, offset ? "))) {
	$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	exit (json_encode($ret));	
		
}

if (!$stmt->bind_param("iii",  $wall_id, $count, $start_id)) {
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

$users = array();
while($stmt->fetch()) {
	$ele = array();
	foreach($result as $key => $val) { 
		$ele[$key] = $val; 
	}
	
	$users[] = $ele;
	
}

$mysqli->close();

$ret['status'] = 1;
$ret['ErrorMsg'] = '';
$ret['authors'] = $users;

exit (json_encode($ret,JSON_UNESCAPED_UNICODE));