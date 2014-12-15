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


$start_id = PHP_INT_MAX;

if(isset($_POST['start_id']) && intval($_POST['start_id']) > 0) {
	$start_id = $_POST['start_id'] + 0;
}

$count = 20;


if(isset($_POST['count']) && intval($_POST['count']) > 0 ) {
	$count = $_POST['count'] + 0;
}

$myid = 0;
if(isset($_POST['myid']) && intval($_POST['myid']) > 0 ) {
	$myid = $_POST['myid'] + 0 ;
}
else {
	$myid = $user['user_id'];
}

$sort_method = '';

if(isset($_POST['sort_method']) && $_POST['sort_method'] === 'count') {
	$sort_method = 'count';
}

$encs = array();

if(empty($sort_method)) {

	if($get_enc = $mysqli->query("select t.* from (SELECT * FROM userencounter WHERE user_id = $user_id and status = 1 union select * from userencounter where encounter_userid = $user_id and status = 1) t where t.encounter_id < $start_id order by t.encounter_id desc limit $count ")) {
		while($enc = $get_enc->fetch_assoc()) {
			
			$encs[] = $enc;
		}
	}
	$usrs = array();
	foreach ($encs as $e ) {
		if($e['user_id'] == $user_id) {
			$u = $e['encounter_userid'];
			$new = $e['newtouser'];
		}
		elseif($e['encounter_userid'] == $user_id) {
			$u = $e['user_id'];
			$new = $e['newtoencounteruser'];
		}
		if($get_usr = $mysqli->query("select * from userinfo where user_id = $u ")) {
			while($usr = $get_usr->fetch_assoc()) {
				$usr['encounter_id'] = $e['encounter_id'];
				$usr['encounter_count'] = count(explode(',',$e['message_id']));
				$usr['new'] = $new;
				$usr['last_time'] = $e['last_time'] + 0 ;
				$usr['follow_user_count'] = get_my_follow_count($usr['user_id']);
				$usr['fan_count'] = get_fan_count($usr['user_id']);
				$usr['friend_count'] = get_friend_count($usr['user_id']);
				$usr['is_my_follow_user'] = is_my_follow_user($myid, $usr['user_id']);
				$usr['is_my_fan_user'] = is_my_fun($myid, $usr['user_id']);
				$usrs[] = $usr;
				
			}
		}
	}



	$mysqli->close();

	$ret['status'] = 1;
	$ret['ErrorMsg'] = '';
	$ret['encounteruses'] = $usrs;

	exit (json_encode($ret,JSON_UNESCAPED_UNICODE));

}
else {
	//get all encounter 
	if($get_enc = $mysqli->query("SELECT * FROM userencounter WHERE user_id = $user_id and status = 1 union select * from userencounter where encounter_userid = $user_id and status = 1 ")) {
		while($enc = $get_enc->fetch_assoc()) {
			
			$encs[] = $enc;
		}
	}
	usort($encs,function($a,$b) {  return count(explode(',',$b['message_id'])) - count(explode(',',$a['message_id']));});
	
	if($start_id < PHP_INT_MAX) {
		//find index of start encounter_id
		$offset = 0;
		foreach($encs as $key => $val) {
			if($val['encounter_id'] == $start_id) {
				$offset = $key + 1;
				break;
			}
		}
		//echo $offset;
		$encs = array_slice($encs,$offset,$count);
	}
	else {
		
		$encs = array_slice($encs,0,$count);
	}
	$usrs = array();
	foreach ($encs as $e ) {
		if($e['user_id'] == $user_id) {
			$u = $e['encounter_userid'];
			$new = $e['newtouser'];
		}
		elseif($e['encounter_userid'] == $user_id) {
			$u = $e['user_id'];
			$new = $e['newtoencounteruser'];
		}
		if($get_usr = $mysqli->query("select * from userinfo where user_id = $u ")) {
			while($usr = $get_usr->fetch_assoc()) {
				$usr['encounter_id'] = $e['encounter_id'];
				$usr['encounter_count'] = count(explode(',',$e['message_id']));
				$usr['new'] = $new;
				$usr['last_time'] = $e['last_time'] + 0 ;
				$usr['follow_user_count'] = get_my_follow_count($usr['user_id']);
				$usr['fan_count'] = get_fan_count($usr['user_id']);
				$usr['friend_count'] = get_friend_count($usr['user_id']);
				$usr['is_my_follow_user'] = is_my_follow_user($myid, $usr['user_id']);
				$usr['is_my_fan_user'] = is_my_fun($myid, $usr['user_id']);
				$usrs[] = $usr;
				
			}
		}
	}



	$mysqli->close();

	$ret['status'] = 1;
	$ret['ErrorMsg'] = '';
	$ret['encounteruses'] = $usrs;

	exit (json_encode($ret,JSON_UNESCAPED_UNICODE));
}