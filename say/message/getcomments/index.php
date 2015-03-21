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

$ver = $start_verson;
if(isset($_POST['ver']) && $_POST['ver'] != '') {
	$ver = $_POST['ver'];
}

$message_id = $_POST['message_id'] + 0;

$start = PHP_INT_MAX;

if(isset($_POST['start_id']) && intval($_POST['start_id']) > 0) {
	$start = $_POST['start_id'] + 0 ;
}

$count = 20;

if(isset($_POST['count']) && intval($_POST['count']) > 0 ) {
	$count = $_POST['count'] + 0 ;
}

//start change from version 1.1
$start_change_verson = '1.1';
if( $ver < $start_change_verson ) {
	if (!($stmt = $mysqli->prepare("SELECT * FROM comment WHERE message_id = ? AND comment_id < ? AND type = 1 ORDER BY comment_id DESC limit ? "))) {
		$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		exit (json_encode($ret));	
			
	}
}
else {
	if (!($stmt = $mysqli->prepare("SELECT * FROM comment WHERE message_id = ? AND comment_id < ? ORDER BY comment_id DESC limit ? "))) {
		$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		exit (json_encode($ret));	
			
	}
}

if (!$stmt->bind_param("iii", $message_id, $start, $count)) {
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

if (!($stmt1 = $mysqli->prepare("SELECT nickname,photo_url,gender,birthday,description,expert_type,grade,point FROM userinfo WHERE user_id = ?"))) {
	$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	exit (json_encode($ret));	
		
}

while($stmt->fetch()) {
  
	foreach($result as $key => $val) { 
  	
		$c[$key] = $val; 
		
		if($key === 'comment_userid') {	

			if (!$stmt1->bind_param("i", $val)) {
			 $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt1->errno . ") " . $stmt1->error;
				exit (json_encode($ret));
			}
		
			if (!$stmt1->execute()) {
				$ret['ErrorMsg'] =  "Execute failed: (" . $stmt1->errno . ") " . $stmt1->error;
				exit (json_encode($ret));
			}
			
			$stmt1->bind_result($nickname,$photo_url,$gender,$birthday,$description,$expert_type,$grade,$point);
			while($stmt1->fetch()) {
				
				$c['nickname'] = $nickname;
				$c['photo_url'] = $photo_url;
				$c['gender'] = $gender;
				$c['birthday'] = $birthday;
				$c['description'] = $description;
				$c['expert_type'] = $expert_type;
				$c['grade'] = $grade;
				$c['point'] = $point;
			}
		}
		
		if($key === 'touser_id') {
			//echo $val;
			if (!$stmt1->bind_param("i", $val)) {
			 $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt1->errno . ") " . $stmt1->error;
				exit (json_encode($ret));
			}
		
			if (!$stmt1->execute()) {
				$ret['ErrorMsg'] =  "Execute failed: (" . $stmt1->errno . ") " . $stmt1->error;
				exit (json_encode($ret));
			}
			
			$stmt1->bind_result($nickname,$photo_url,$gender,$birthday,$description,$expert_type,$grade,$point);
			while($stmt1->fetch()) {
				
				$c['touser_nickname'] = $nickname;
				
				
			}
		}
		
  } 
	$results[] = $c;
	unset($c);
}

$stmt1->close();
$stmt->close();

$mysqli->close();

$ret['status'] = 1;
$ret['ErrorMsg'] = '';
$ret['comments'] = $results;
exit (json_encode($ret,JSON_UNESCAPED_UNICODE));