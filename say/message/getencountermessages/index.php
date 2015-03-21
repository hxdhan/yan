<?php
include ('../../header.php');

if(!check_login()) {
	$ret['ErrorMsg'] = '没有登录';
	exit (json_encode($ret));
}

$user_id = $user['user_id'];

if(isset($_POST['user_id'])) {
	$user_id = $_POST['user_id'] + 0;
}

if(empty($_POST['encounter_userid'])) {
	$ret['ErrorMsg'] = '参数错误';
	exit (json_encode($ret));
}

$myid = 0;
if(isset($_POST['myid']) && intval($_POST['myid']) > 0 ) {

	$myid = $_POST['myid'] + 0 ;
}
else {
	$myid = $user['user_id'];
}


$encounter_userid = $_POST['encounter_userid'] + 0 ;

if($user_id > $encounter_userid) {

	list($encounter_userid,$user_id) = array($user_id,$encounter_userid);
}


$start_id = 0;

if(isset($_POST['start_id']) && intval($_POST['start_id']) > 0 ) {
	$start_id = $_POST['start_id'] + 0;
}

$count = 20;


if(isset($_POST['count']) && intval($_POST['count']) > 0 ) {
	$count = $_POST['count'] + 0;
}

	if (!($stmt = $mysqli->prepare("select message_id,time FROM userencounter WHERE user_id = ? and encounter_userid = ? "))) {
			$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
			exit (json_encode($ret));	
				
	}

	if (!$stmt->bind_param("ii", $user_id, $encounter_userid)) {
			$ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
			exit (json_encode($ret));
	}

	if (!$stmt->execute()) {
			$ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
			exit (json_encode($ret));
	}

	$stmt->bind_result($message_id,$time_str);



	while($stmt->fetch()) {
		
	}
  
	$message_array = explode(',',$message_id);



	$m_count = count($message_array);

	if($start_id > 0) {
		
		$m_count = array_search($start_id, $message_array)  ;
	}

	$time_array = explode(',',$time_str);
	$message = array();
	$rows = 0;
	for($i = $m_count -1  ; $i >= 0 ; $i--) {
		if ($result = $mysqli->query("SELECT m.*,u.* FROM message m,userinfo u WHERE  m.author_id = u.user_id and m.message_id = {$message_array[$i]}")) {
				
					if($result->num_rows > 0) {
					 
						if($rows++ == $count) {
							break;
						}

						$row = $result->fetch_assoc();
						$row['message_id'] = $row['message_id'] +0;
						$row['author_id'] = $row['author_id'] +0;
						$row['like_status'] = get_like_status($row['message_id'],$myid);
						$row['time'] = $row['time'] +0;
						$row['new_time'] = $row['new_time'] +0;
						$row['encounter_time'] = $time_array[$i] + 0 ;
						
						$message[] = $row;
					}
					else {
						$message[] = array('message_id'=>$message_array[$i] + 0);
					}
					
			}
			
			update_receive_count($message_array[$i]);
			
	}





$stmt->close();

$mysqli->close();

$ret['status'] = 1;
$ret['ErrorMsg'] = '';
$ret['messages'] = $message;
exit (json_encode($ret,JSON_UNESCAPED_UNICODE));
