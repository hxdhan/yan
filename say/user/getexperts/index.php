<?php
include ('../../header.php');

if(!check_login()) {
	$ret['ErrorMsg'] = '没有登录';
	exit (json_encode($ret));
}

$memcache = memcache_connect($mem_host, $mem_port);

if($expert_types = memcache_get($memcache, 'expert_type')) {

}
else {
	$expert_types = array();

//echo "select user_id,receive_userid,chat_content,content_type,time,new from usrchat where receive_userid = $user_id union select user_id,receive_userid,chat_content,content_type, time,new from usrchat where user_id = $user_id order by time desc";

	if($get_expert = $mysqli->query("select * from usrexpert_type")) {
		while($e = $get_expert->fetch_assoc()) {
			
			$expert_types[] = $e;
		}
	}
	
	memcache_set($memcache, 'expert_type', $expert_types, 0, 60*60);

}



$mysqli->close();

$ret['status'] = 1;
$ret['ErrorMsg'] = '';
$ret['expert_types'] = $expert_types;

exit (json_encode($ret));