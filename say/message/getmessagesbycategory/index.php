<?php

include ('../../header.php');



if(!check_login()) {
	$ret['ErrorMsg'] = '没有登录';
	exit (json_encode($ret));
}

if(empty($_POST['category_id']) ) {
	$ret['ErrorMsg'] = '参数错误';
	exit (json_encode($ret));
}

$category_id = $_POST['category_id'];

if(isset($_POST['sort_method'])) {
	$sort_method = $_POST['sort_method'];
}

$start_id = PHP_INT_MAX;



if(isset($_POST['start_id']) && intval($_POST['start_id']) > 0) {
	$start_id = $_POST['start_id'];
}

$count = 20;

if(isset($_POST['count'])) {
	$count = $_POST['count'];
}

$myid = 0;

if(isset($_POST['myid']) && intval($_POST['myid']) > 0 ) {

	$myid = $_POST['myid'] + 0 ;
}
else {
	$myid = $user['user_id'];
}

if (!($stmt = $mysqli->prepare("SELECT m.*,u.* FROM message m,userinfo u WHERE m.author_id = u.user_id and category_id = ? and message_id < ? ORDER BY message_id desc LIMIT ? "))) {
	$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	exit (json_encode($ret));	
		
}

if (!$stmt->bind_param("iii", $category_id, $start_id, $count)) {
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
	$c = array();
	foreach($result as $key => $val) { 
		$c[$key] = $val; 
	}
	$c['like_status'] = get_like_status($c['message_id'],$myid);
	$results[] = $c;
	unset($c);
}

$stmt->close();



$mysqli->close();

$ret['status'] = 1;
$ret['ErrorMsg'] = '';
$ret['messages'] = $results;
exit (json_encode($ret,JSON_UNESCAPED_UNICODE));

