<?php
include ('../../header.php');

if(!check_login()) {
	$ret['ErrorMsg'] = '没有登录';
	exit (json_encode($ret));
}

$myid = 0;
if(isset($_POST['myid']) && intval($_POST['myid']) > 0 ) {
	$myid = $_POST['myid'] + 0 ;
}
else {
	$myid = $user['user_id'];
}

$users = array();

//echo "select user_id,receive_userid,chat_content,content_type,time,new from usrchat where receive_userid = $user_id union select user_id,receive_userid,chat_content,content_type, time,new from usrchat where user_id = $user_id order by time desc";

if($get_users = $mysqli->query("select * from userinfo where  expert_type > 0")) {
	while($usr = $get_users->fetch_assoc()) {
		$usr['follow_user_count'] = get_my_follow_count($usr['user_id']);
		$usr['fan_count'] = get_fan_count($usr['user_id']);
		$usr['friend_count'] = get_friend_count($usr['user_id']);
		$usr['is_my_follow_user'] = is_my_follow_user($myid, $usr['user_id']);
		$usr['is_my_fan_user'] = is_my_fun($myid, $usr['user_id']);
		$users[] = $usr;
	}
}



$mysqli->close();

$ret['status'] = 1;
$ret['ErrorMsg'] = '';
$ret['users'] = $users;

exit (json_encode($ret,JSON_UNESCAPED_UNICODE));