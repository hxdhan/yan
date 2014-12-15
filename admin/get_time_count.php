<?php


if(empty($_POST)) {
	echo -1;
	exit;
}

$start = strtotime($_POST['start']);
$end = strtotime($_POST['end']);

$mysqli = new mysqli('localhost', 'root', 'root', 'say');

if ($mysqli->connect_errno) {
	print_r($mysqli->error);
}

if (!$mysqli->set_charset("utf8")) {
	print_r($mysqli->error);
}

$result = array();
if($get_message_count = $mysqli->query("select count(distinct author_id) from message where time between $start and $end")) {
	$message_count = $get_message_count->fetch_row()[0];
	$result['message_count'] = $message_count;
}

if($get_comment_count = $mysqli->query("select count(distinct comment_userid) from comment where time between $start and $end")) {
	$comment_count = $get_comment_count->fetch_row()[0];
	$result['comment_count'] = $comment_count;
}

if($get_like_count = $mysqli->query("select count(distinct like_userid) from `like` where time between $start and $end")) {
	$like_count = $get_like_count->fetch_row()[0];
	$result['like_count'] = $like_count;
}

if($get_chat_count = $mysqli->query("select count(distinct user_id) from usrchat where time between $start and $end")) {
	$chat_count = $get_chat_count->fetch_row()[0];
	$result['chat_count'] = $chat_count;
}

exit(json_encode($result));





