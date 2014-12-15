<?php
include ('../../header.php');



if(!check_login()) {
	$ret['ErrorMsg'] = '没有登录';
	exit (json_encode($ret));
}




if(empty($_POST['userid'])) {
	$ret['ErrorMsg'] = '参数错误';
	exit (json_encode($ret));
}

$count = 20;


if(isset($_POST['count'])) {

	$count = $_POST['count'] + 0 ;
}

$start = 0;

if(isset($_POST['start_id'])) {

	$start = $_POST['start_id'] + 0 ;
}


$user_id = $_POST['userid'];


if($start == 0) {

	if (!($stmt = $mysqli->prepare("SELECT * FROM message WHERE author_id = ? ORDER BY message_id DESC limit ?"))) {
		$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		exit (json_encode($ret));	
			
	}
	
	
	
	if (!$stmt->bind_param("ii", $user_id, $count)) {
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

}

elseif($start > 0) {
	if (!($stmt = $mysqli->prepare("SELECT * FROM message WHERE author_id = ? and message_id < ? ORDER BY message_id DESC limit ?"))) {
		$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		exit (json_encode($ret));	
			
	}
	
	
	
	if (!$stmt->bind_param("iii", $user_id, $start, $count)) {
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
}

$results = array();
if (!($stmt1 = $mysqli->prepare("SELECT nickname,photo_url,photo_color,gender,birthday,description,expert_type FROM userinfo WHERE user_id = ?"))) {
		$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		exit (json_encode($ret));	
		
	}
while($stmt->fetch()) {
	foreach($result as $key => $val) { 
  	$c[$key] = $val; 
		if($key == 'author_id') {
				if (!$stmt1->bind_param("i", $val)) {
 	 				$ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
					exit (json_encode($ret));
				}
				
				if (!$stmt1->execute()) {
  				$ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
					exit (json_encode($ret));
				}
				$stmt1->bind_result($nickname,$photo_url,$photo_color,$gender,$birthday,$description,$expert_type);
				while($stmt1->fetch()) {
					$c['nickname'] = $nickname;
					$c['photo_url'] = $photo_url;
					$c['photo_color'] = $photo_color;
					$c['gender'] = $gender;
					$c['birthday'] = $birthday;
					$c['description'] = $description;
					$c['expert_type'] = $expert_type;
				}
				
			}
  } 
	$results[] = $c;
}
$stmt1->close();
$stmt->close();
$mysqli->close();

$ret['status'] = 1;
$ret['ErrorMsg'] = '';
$ret['messages'] = $results;
exit (json_encode($ret));