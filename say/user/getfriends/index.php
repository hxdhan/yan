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

$start_id = PHP_INT_MAX;

$count = 20;

if(isset($_POST['count']) && intval($_POST['count']) > 0) {
	$count = $_POST['count'] + 0;
}

if(isset($_POST['start_id']) && intval($_POST['start_id']) > 0) {
	$start_id = $_POST['start_id'] + 0;
}

$myid = 0;
if(isset($_POST['myid']) && intval($_POST['myid']) > 0 ) {
	$myid = $_POST['myid'] + 0 ;
}
else {
	$myid = $user['user_id'];
}

$all_users = array();
$result = array();

//get all friends of user should union 
if($get_users = $mysqli->query("SELECT friend_id,user_id,friend_userid FROM usrfriend WHERE (user_id = $user_id OR friend_userid = $user_id) AND friend_id < $start_id ORDER BY friend_id DESC LIMIT $count")) {
	while($user = $get_users->fetch_assoc()) {
		$all_users[] = $user;
	}
}

//var_dump($all_users);

foreach($all_users as $au) {
	//var_dump($au);
	if($au['user_id'] == $user_id) {
		$select_id = $au['friend_userid'];
	}
	elseif($au['friend_userid'] == $user_id) {
		$select_id = $au['user_id'];
	}
	//echo $select_id;
	if($get_user = $mysqli->query("SELECT {$au['friend_id']} as friend_id, u.* FROM userinfo u where u.user_id = $select_id")) {
		while($u = $get_user->fetch_assoc()) {
			$u['follow_user_count'] = get_my_follow_count($u['user_id']);
			$u['fan_count'] = get_fan_count($u['user_id']);
			$u['friend_count'] = get_friend_count($u['user_id']);
			$u['is_my_follow_user'] = is_my_follow_user($myid, $u['user_id']);
			$u['is_my_fan_user'] = is_my_fun($myid, $u['user_id']);	
			$result[] = $u;
	}	
	}
}

//print_r($all_users);

$mysqli->close();

$ret['status'] = 1;
$ret['ErrorMsg'] = '';
$ret['users'] = $result;
exit (json_encode($ret,JSON_UNESCAPED_UNICODE));