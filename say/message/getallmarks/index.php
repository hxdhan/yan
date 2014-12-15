<?php
include ('../../header.php');

if(!check_login()) {
	$ret['ErrorMsg'] = '没有登录';
	exit (json_encode($ret));
}



if (!($stmt = $mysqli->prepare("select * from msgmark order by mark_id desc"))) {
	$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	exit (json_encode($ret));	
		
}

// if (!$stmt->bind_param("iii",  $user_id,$start,$count)) {
  // $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
	// exit (json_encode($ret));
// }

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

$marks = array();
while($stmt->fetch()) {
	$e = array();
	foreach($result as $key => $val) { 
		$e[$key] = $val;
	}
	
	$marks[] = $e;
	unset($e);

}

$stmt->close();

$mysqli->close();


$ret['status'] = 1;
$ret['ErrorMsg'] = '';
$ret['marks'] = $marks;

exit (json_encode($ret));