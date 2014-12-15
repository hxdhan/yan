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

if(empty($_POST['user_id'])  ) {
	$ret['ErrorMsg'] = '参数错误';
	exit (json_encode($ret));
}

$user_id = $_POST['user_id'] + 0 ;


$users = array();

//echo "select user_id,receive_userid,chat_content,content_type,time,new from usrchat where receive_userid = $user_id union select user_id,receive_userid,chat_content,content_type, time,new from usrchat where user_id = $user_id order by time desc";

if($get_users = $mysqli->query("select user_id,receive_userid,chat_content,content_type,time,new from usrchat where receive_userid = $user_id union select user_id,receive_userid,chat_content,content_type, time,new from usrchat where user_id = $user_id order by time desc")) {
	while($usr = $get_users->fetch_assoc()) {
		
		$users[] = $usr;
	}
}

//var_dump($users);




//$usrs = array();

$check_users = array();




foreach($users as $u) {
		
		if($u['user_id'] <=$u['receive_userid']) {
			$id_key = $u['user_id'] .'-'. $u['receive_userid'] ;
		}
    else {
			$id_key = $u['receive_userid'] .'-'. $u['user_id'] ;
		}
		//echo $id_key;

		if(array_key_exists($id_key,$check_users)) {
				if($user_id == $u['receive_userid']) {
        	$check_users[$id_key]['new'] += $u['new'];
				}
		}
		else {
			
			$check_users[$id_key]['last_chat_sender'] = $u['user_id'];
			$check_users[$id_key]['last_chat_time'] = $u['time'];
			$check_users[$id_key]['last_chat_type'] = $u['content_type'];
			$check_users[$id_key]['last_chat_content'] = $u['chat_content'];

			if($user_id == $u['user_id']) {
				$check_users[$id_key]['user_id'] = $u['receive_userid'];
				$check_users[$id_key]['new'] = 0;
			}
			elseif($user_id == $u['receive_userid'])   {
				$check_users[$id_key]['user_id'] = $u['user_id'];
				$check_users[$id_key]['new'] = $u['new'];
			}
			
			
		}

		

}

//var_dump($check_users);

$usrss = array();

foreach($check_users as $ckey=>$cval) {
	//var_dump($cval);
	if($get_usr = $mysqli->query("select * from userinfo where user_id = {$cval['user_id']} ")) {
			$usr = $get_usr->fetch_assoc();
			$usr['new_chat_count'] = $cval['new'];
			$usr['last_chat_sender'] = $cval['last_chat_sender'];
			$usr['last_chat_time'] = $cval['last_chat_time'] + 0;
			$usr['last_chat_type'] = $cval['last_chat_type'] + 0;
			$usr['last_chat_content'] = $cval['last_chat_content'] ;
			$usr['follow_user_count'] = get_my_follow_count($usr['user_id']);
			$usr['fan_count'] = get_fan_count($usr['user_id']);
			$usr['friend_count'] = get_friend_count($usr['user_id']);
			$usr['is_my_follow_user'] = is_my_follow_user($myid, $usr['user_id']);
			$usr['is_my_fan_user'] = is_my_fun($myid, $usr['user_id']);
			$usrss[] = $usr;
 		}
}



$mysqli->close();

$ret['status'] = 1;
$ret['ErrorMsg'] = '';
$ret['users'] = $usrss;

exit (json_encode($ret,JSON_UNESCAPED_UNICODE));