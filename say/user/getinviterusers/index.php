<?php
include ('../../header.php');

if(!check_login()) {
	$ret['ErrorMsg'] = '没有登录';
	exit (json_encode($ret));
}



if(empty($_POST['user_id']) ) {
	$ret['ErrorMsg'] = '参数错误';
	exit (json_encode($ret));
}

$user_id = $_POST['user_id'] + 0 ;

$myid = 0;
if(isset($_POST['myid']) && intval($_POST['myid']) > 0 ) {
	$myid = $_POST['myid'] + 0 ;
}
else {
	$myid = $user['user_id'];
}



$all_users = array();
$result = array();

if($get_users = $mysqli->query("SELECT inviter_userid,message FROM usrfriendinvite where invitee_userid = $user_id")) {
	while($users = $get_users->fetch_assoc()) {
		$all_users[] = $users;
	}
}

//print_r($all_users);

foreach($all_users as $user) {
	
	if($get_user = $mysqli->query("SELECT ui.*,u.cellphone FROM userinfo ui, user u where ui.user_id = u.user_id and  ui.user_id = {$user['inviter_userid']}")) {
		while($u = $get_user->fetch_assoc()) {
			$u['verify_message'] = $user['message'];
			$u['follow_user_count'] = get_my_follow_count($u['user_id']);
			$u['fan_count'] = get_fan_count($u['user_id']);
			$u['friend_count'] = get_friend_count($u['user_id']);
			$u['is_my_follow_user'] = is_my_follow_user($myid, $u['user_id']);
			$u['is_my_fan_user'] = is_my_fun($myid, $u['user_id']);	
			$result[] = $u;
		}
	}
}


$mysqli->close();

$ret['status'] = 1;
$ret['ErrorMsg'] = '';
$ret['users'] = $result;


exit (json_encode($ret,JSON_UNESCAPED_UNICODE));