<?php

echo "stop";
exit;

$mysqli = mysqli_connect('localhost','root','root','say');

$get_message = mysqli_query($mysqli,"select * from message");

while($m = mysqli_fetch_assoc($get_message)) {
	$get_count = mysqli_query($mysqli,"select count(*) from comment where message_id = {$m['message_id']}");
	$count = mysqli_fetch_row($get_count)[0];
	
	mysqli_query($mysqli,"update message set comment_count = {$count} where message_id = {$m['message_id']}");
	
}

msqli_close($mysqli);