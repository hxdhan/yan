<?php

include("header.php");

$get_user = mysqli_query($mysqli,"select user_id from userinfo");

$grade = get_grade();
var_dump($grade);
while($u = mysqli_fetch_assoc($get_user)) {
	
	$user_id =$u["user_id"];
	
	$count = 0;
	
	$get_message_count = mysqli_query($mysqli,"select count(*) from message where author_id = $user_id");
	$message_count = mysqli_fetch_row($get_message_count)[0];
	
	$count += $message_count * 3;

	
	$get_wall_count = mysqli_query($mysqli,"select count(*) from msgwall where owner_userid = $user_id");
	$wall_count = mysqli_fetch_row($get_wall_count)[0];
	
	$count += $wall_count * 5;
	

	
	$get_comment_count = mysqli_query($mysqli,"select count(*) from comment where comment_userid = $user_id");
	$comment_count = mysqli_fetch_row($get_comment_count)[0];
	
	$count += $comment_count;
	
	$get_like_count = mysqli_query($mysqli,"select count(*) from `like` where like_userid = $user_id");
	$like_count = mysqli_fetch_row($get_like_count)[0];
	
	$count += $like_count;
	
	$index =1 ;
	var_dump("count is ". $count);
	$i = $index;
	$c = $count;
	while(($count = $count - $grade[$index++]) > 0) {
		$last_index = $index;
		$last_count = $count;
	}
	if(!isset($last_index)) {
		$last_index = $i;
	}
	if(!isset($last_count)) {
		$last_count = $c;
	}
	var_dump("index is " . $last_index);
	var_dump("last count" . $last_count);
	mysqli_query($mysqli,"update userinfo set grade = $last_index, point = $last_count where user_id = $user_id");
	unset($last_index);
	unset($last_count);
	
}

msqli_close($mysqli);