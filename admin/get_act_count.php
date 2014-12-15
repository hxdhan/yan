<?php

$mysqli = new mysqli('localhost', 'root', 'root', 'say');

if ($mysqli->connect_errno) {
	print_r($mysqli->error);
}

if (!$mysqli->set_charset("utf8")) {
	print_r($mysqli->error);
}

if($get_all_count = $mysqli->query("select count(*) from user ")) {
	$all_count = $get_all_count->fetch_row()[0];
}

if($get_message_count = $mysqli->query("select count(distinct author_id) from message ")) {
	$message_count = $get_message_count->fetch_row()[0];
	$result['message_count'] = $all_count - $message_count;
}

if($get_comment_count = $mysqli->query("select count(distinct comment_userid) from comment ")) {
	$comment_count = $get_comment_count->fetch_row()[0];
	$result['comment_count'] = $all_count - $comment_count;
}

if($get_like_count = $mysqli->query("select count(distinct like_userid) from `like` ")) {
	$like_count = $get_like_count->fetch_row()[0];
	$result['like_count'] = $all_count - $like_count;
}

if($get_chat_count = $mysqli->query("select count(distinct user_id) from usrchat ")) {
	$chat_count = $get_chat_count->fetch_row()[0];
	$result['chat_count'] = $all_count - $chat_count;
}

exit(json_encode($result));