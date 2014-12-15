<?php
include ('../../header.php');



if(!check_login()) {
	$ret['ErrorMsg'] = '没有登录';
	exit (json_encode($ret));
}

if(empty($_POST['myid']) || empty($_POST['user_id'])  ) {
	$ret['ErrorMsg'] = '参数错误';
	exit (json_encode($ret));
}

$myid = $_POST['myid'] + 0 ;
$user_id = $_POST['user_id'] + 0 ;

$start_id = PHP_INT_MAX;

if(isset($_POST['start_id']) && intval($_POST['start_id'] > 0) ){
	$start_id = $_POST['start_id'] +0 ;
}

$count = 20;
if(isset($_POST['count']) && intval($_POST['count'] > 0)) {
	$count = $_POST['count'] +0 ;
}




//echo "select t.* from (select * from usrchat where user_id = $myid and receive_userid = $user_id union select * from usrchat where user_id = $user_id and receive_userid = $myid ) t where t.chat_id < $start_id order by t.chat_id desc limit $count ";

$chats = array();
if($get_chats = $mysqli->query("select t.* from (select * from usrchat where user_id = $myid and receive_userid = $user_id union select * from usrchat where user_id = $user_id and receive_userid = $myid ) t where t.chat_id < $start_id order by t.chat_id desc limit $count ")) {
	while($chat = $get_chats->fetch_assoc()) {
		$chat['time'] = $chat['time'] + 0;
		$chats[] = $chat; 
	}
}

$mysqli->close();

$ret['status'] = 1;
$ret['ErrorMsg'] = '';
$ret['chats'] = $chats;

exit (json_encode($ret,JSON_UNESCAPED_UNICODE));