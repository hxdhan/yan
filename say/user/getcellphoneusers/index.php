<?php
include ('../../header.php');

if(!check_login()) {
	$ret['ErrorMsg'] = '没有登录';
	exit (json_encode($ret));
}

if(empty($_POST['user_id'])) {
	$ret['ErrorMsg'] = '参数错误';
	exit (json_encode($ret));
}

if(empty($_POST['cellphone'])) {
	
	$ret['status'] = 1;
	$ret['ErrorMsg'] = '';
	$ret['users'] = array();
	exit (json_encode($ret));
}

$myid = 0;
if(isset($_POST['myid']) && intval($_POST['myid']) > 0 ) {
	$myid = $_POST['myid'] + 0 ;
}
else {
	$myid = $user['user_id'];
}


$user_id = $_POST['user_id'] + 0 ;
$cellphones = explode(',',$_POST['cellphone']);

$ces = array();

foreach($cellphones as $c) {
	if($c != '+86-99999999999') {
		$ces[] = $c;
	}
}

$ces_string =implode("','",$ces);

$all_users = array();

if($get_allusers = $mysqli->query("select user_id from user where cellphone in ('" . $ces_string . "')")) {
	while($u = $get_allusers->fetch_assoc()) {
		$all_users[] = $u['user_id'];
	}
}

//print_r($all_users);

$my_friends = array();

if($get_friends = $mysqli->query("select friend_userid from usrfriend where user_id = $user_id union select user_id as friend_userid from usrfriend where friend_userid = $user_id ")) {
	while($f = $get_friends->fetch_assoc()) {
		$my_friends[] = $f['friend_userid'];
	}
}

//print_r($my_friends);

$my_invite = array();

if($get_invite = $mysqli->query("select invitee_userid as userid from usrfriendinvite where inviter_userid = $user_id union select inviter_userid as userid from usrfriendinvite where invitee_userid = $user_id")) {
	while($inv = $get_invite->fetch_assoc()) {
		$my_invite[] = $inv['userid'];
	}	
}

//print_r($my_invite);

$result = array();

foreach($all_users as $user) {
	if(!in_array($user,$my_friends) and !in_array($user,$my_invite)) {
		if($get_res = $mysqli->query("select ui.*,u.cellphone from userinfo ui, user u where ui.user_id = u.user_id and ui.user_id = $user")) {
				while($res = $get_res->fetch_assoc()) {
					$res['follow_user_count'] = get_my_follow_count($res['user_id']);
					$res['fan_count'] = get_fan_count($res['user_id']);
					$res['friend_count'] = get_friend_count($res['user_id']);
					$res['is_my_follow_user'] = is_my_follow_user($myid, $res['user_id']);
					$res['is_my_fan_user'] = is_my_fun($myid, $res['user_id']);
					$result[] = $res;
				}
		}
	}
}

$mysqli->close();

$ret['status'] = 1;
$ret['ErrorMsg'] = '';
$ret['users'] = $result;
exit (json_encode($ret,JSON_UNESCAPED_UNICODE));