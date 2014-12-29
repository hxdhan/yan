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

$ver = $start_verson;
if(isset($_POST['ver']) && $_POST['ver'] != '') {
	$ver = $_POST['ver'];
}

$start_change_verson = '1.7';

$user_id = $_POST['user_id'] + 0;

$start = PHP_INT_MAX;

if(isset($_POST['start_id']) && intval($_POST['start_id']) > 0) {
	$start = $_POST['start_id'] + 0 ;
}

$myid = 0;
if(isset($_POST['myid']) && intval($_POST['myid']) > 0 ) {
	$myid = $_POST['myid'] + 0 ;
}
else {
	$myid = $user['user_id'];
}

$count = 20;

if(isset($_POST['count']) && intval($_POST['count']) > 0 ) {
	$count = $_POST['count'] + 0 ;
}
if( $ver < $start_change_verson ) {
	if (!($stmt = $mysqli->prepare("SELECT * FROM usrnotification WHERE user_id = ? AND notif_id < ? and type not in('post','wallnew','favwallnew')  ORDER BY notif_id DESC limit ? "))) {
	$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	exit (json_encode($ret));	
		
	}
}
else {
	if (!($stmt = $mysqli->prepare("SELECT * FROM usrnotification WHERE user_id = ? AND notif_id < ?  ORDER BY notif_id DESC limit ? "))) {
		$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		exit (json_encode($ret));	
			
	}
}

if (!$stmt->bind_param("iii", $user_id, $start, $count)) {
	$ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
	exit (json_encode($ret));
}

if (!$stmt->execute()) {
	$ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
	exit (json_encode($ret));
}

$meta = $stmt->result_metadata();

while ($column = $meta->fetch_field()) {
	 $bindVarsArray[] = &$result[$column->name];
}        
call_user_func_array(array($stmt, 'bind_result'), $bindVarsArray);

$stmt->store_result();
$results = array();
while($stmt->fetch()) {
	$ele = array();
	foreach($result as $key=>$val) {
		if($key == 'active_userid' && $val != 0) {
				//get active user
				//$mysqli->next_result();
				$ui = $user_id;
				$eu = $val;
				if($ui > $eu) {
					list($eu,$ui) = array($ui,$eu);
				}
				if($get_message_id = $mysqli->query("SELECT message_id FROM userencounter WHERE user_id = $ui and encounter_userid = $eu ")) {
						$message_id = $get_message_id->fetch_row()[0];
						$ele['user_encounter_count'] = count(explode(',',$message_id));
				}
				if($get_user = $mysqli->query("select * from userinfo where user_id = {$val}")) {
					//echo $get_user->num_rows;
					if($get_user->num_rows == 1) {
						$active_user = $get_user->fetch_assoc();
						$active_user['follow_user_count'] = get_my_follow_count($active_user['user_id']);
						$active_user['fan_count'] = get_fan_count($active_user['user_id']);
						$active_user['friend_count'] = get_friend_count($active_user['user_id']);
						$active_user['is_my_follow_user'] = is_my_follow_user($myid, $active_user['user_id']);
						$active_user['is_my_fan_user'] = is_my_fun($myid, $active_user['user_id']);
						$ele['active_user'] = $active_user;
					}
				}
				else {
					printf("%s", $mysqli->error);
				}
		}
		elseif($key == 'message_id' && $val != 0) {
			if($get_message = $mysqli->query("select m.*,u.* from message m , userinfo u where m.author_id = u.user_id and m.message_id = {$val}")) {
				if($get_message ->num_rows == 1) {
					$message = $get_message->fetch_assoc();
					$message['time'] = $message['time'] + 0;
					$message['new_time'] = $message['new_time'] + 0;
					$message['like_status'] = get_like_status($message['message_id'],$myid);
					$ele['message'] = $message;
				}
			}
		}
		elseif($key == 'wall_id' && $val != 0  ) {
			if($get_wall = $mysqli->query("select * from msgwall where wall_id = {$val} ")) {
				if($get_wall->num_rows == 1) {
					$wall = $get_wall->fetch_assoc();
					$wall['favourate_count'] = get_wallfavourate_count($wall['wall_id']);
					$wall['message_count'] = get_wallmsg_count($wall['wall_id']);
					$ele['wall'] = $wall;
				}
			}
		}
		else {
			$ele[$key] = $val;
		}
	}
	unset($ele['user_id']);
	$results[] = $ele;
	//$stmt->free_result();
}

$stmt->close();

$mysqli->close();

$ret['status'] = 1;
$ret['ErrorMsg'] = '';
$ret['notifications'] = $results;
exit (json_encode($ret,JSON_UNESCAPED_UNICODE));