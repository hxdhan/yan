<?php
include ('../../header.php');

if(!check_login()) {
	$ret['ErrorMsg'] = '没有登录';
	exit (json_encode($ret));
}

$category_id = 0;
if(isset($_POST['category_id']) && $_POST['category_id'] != '') {
	$category_id = $_POST['category_id'];
	$cat = '('.implode(',',explode(',',$category_id)).")";
}

$gender = 'A';
if(isset($_POST['gender'])) {
	if($_POST['gender'] == 'M') {
		$gender = 'M';
	}
	if($_POST['gender'] == 'F') {
		$gender = 'F';
	}
}

$count = 10;

if(isset($_POST['count']) && intval($_POST['count']) > 0 ) {

	$count = $_POST['count'] + 0 ;
}

$myid = 0;
if(isset($_POST['myid']) && intval($_POST['myid']) > 0 ) {
	$myid = $_POST['myid'] + 0 ;
}
else {
	$myid = $user['user_id'];
}

if($category_id == 0) {
	if($gender == 'A') {
		if (!($stmt = $mysqli->prepare("SELECT * FROM message ORDER BY (like_count*2 + comment_count + receive_count * 0.01 - 30 * (UNIX_TIMESTAMP() - time)/(60*60*24) + recommand_level) desc LIMIT ? "))) {
			$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
			exit (json_encode($ret));	
			
		}

		if (!$stmt->bind_param("i", $count)) {
			$ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
			exit (json_encode($ret));
		}
	}
	else {
		if (!($stmt = $mysqli->prepare("SELECT m.* FROM message m,userinfo ui where m.author_id = ui.user_id and ui.gender = ? ORDER BY (m.like_count*2 + m.comment_count + m.receive_count * 0.01 - 30 * (UNIX_TIMESTAMP() - m.time)/(60*60*24) + recommand_level) DESC LIMIT ? "))) {
			$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
			exit (json_encode($ret));	
			
		}

		if (!$stmt->bind_param("si", $gender, $count)) {
			$ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
			exit (json_encode($ret));
		}
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
		
		$ele['like_status'] = get_like_status($ele['message_id'],$myid);
		$user = get_userinfo($ele['author_id']);
		$ele = array_merge($ele,$user);
		update_receive_count($ele['message_id']);
		$results[] = $ele;
	}
	
	$stmt->close();
	
	$mysqli->close();

	$ret['status'] = 1;
	$ret['ErrorMsg'] = '';
	$ret['messages'] = $results;
	 exit (json_encode($ret,JSON_UNESCAPED_UNICODE));
}
else {
	if($gender == 'A') {
		if (!($stmt = $mysqli->prepare("SELECT * FROM message where category_id in $cat ORDER BY (like_count*2 + comment_count + receive_count * 0.01 - 30 * (UNIX_TIMESTAMP() - time)/(60*60*24) + recommand_level) DESC LIMIT ? "))) {
			$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
			exit (json_encode($ret));	
			
		}

		if (!$stmt->bind_param("i", $count)) {
			$ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
			exit (json_encode($ret));
		}
	}
	else {
		if (!($stmt = $mysqli->prepare("SELECT m.* FROM message m,userinfo ui where m.author_id = ui.user_id and ui.gender = ? and m.category_id in $cat ORDER BY (m.like_count*2 + m.comment_count + m.receive_count * 0.01 - 30 * (UNIX_TIMESTAMP() - m.time)/(60*60*24) + recommand_level) DESC LIMIT ? "))) {
			$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
			exit (json_encode($ret));	
			
		}

		if (!$stmt->bind_param("si", $gender, $count)) {
			$ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
			exit (json_encode($ret));
		}
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
		$ele['like_status'] = get_like_status($ele['message_id'],$myid);
		$user = get_userinfo($ele['author_id']);
		$ele = array_merge($ele,$user);
		update_receive_count($ele['message_id']);
		$results[] = $ele;
	}
	
	$stmt->close();
	
	$mysqli->close();

	$ret['status'] = 1;
	$ret['ErrorMsg'] = '';
	$ret['messages'] = $results;
	exit (json_encode($ret,JSON_UNESCAPED_UNICODE));
}

