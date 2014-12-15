<?php
include ('../../header.php');

if(!check_login()) {
	$ret['ErrorMsg'] = '没有登录';
	exit (json_encode($ret));
}

if(empty($_POST['user_id'])  || empty($_POST['friend_userid'])) {
	$ret['ErrorMsg'] = '参数错误';
	exit (json_encode($ret));
}

$user_id = $_POST['user_id'] + 0 ;

$friend_userid = $_POST['friend_userid'] + 0 ;

if($user_id > $friend_userid) {
	list($friend_userid,$user_id) = array($user_id,$friend_userid);
}


if($get_count = $mysqli->query("select count(*) from usrfriend where user_id = $user_id and friend_userid = $friend_userid")) {
	$count = $get_count->fetch_row()[0];
	if($count > 0) {
		$ret['ErrorMsg'] = '已经加为好友了';
	exit (json_encode($ret));
	}
}

if($mysqli->query("insert into usrfriend (user_id,friend_userid) values ($user_id,$friend_userid)")) {

}

if($get_count = $mysqli->query("select count(*) from userfollow where user_id = $user_id and follow_userid = $friend_userid")) {
	$count = $get_count->fetch_row()[0];
	if($count == 0) {
		if($mysqli->query("insert into userfollow (user_id,follow_userid) values ($user_id,$friend_userid)")) {
		}
	}
}

if($get_count = $mysqli->query("select count(*) from userfollow where user_id = $friend_userid and follow_userid = $user_id")) {
	$count = $get_count->fetch_row()[0];
	if($count == 0) {
		if($mysqli->query("insert into userfollow (user_id,follow_userid) values ($friend_userid,$user_id)")) {
		}
	}
}


$mysqli->close();

$ret['status'] = 1;
$ret['ErrorMsg'] = '';
$ret['user_id'] = $_POST['user_id'];
$ret['friend_userid'] = $_POST['friend_userid'];
exit (json_encode($ret));
 
?>