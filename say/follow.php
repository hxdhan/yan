<?php

exit('stop');

$mysqli = mysqli_connect('localhost','root','root','say');

$get_user = mysqli_query($mysqli,"select user_id from user");

while($u = mysqli_fetch_assoc($get_user)) {
	
	if ($u['user_id'] != 1) {
		if($get_follow = mysqli_query($mysqli,"select * from userfollow where user_id = {$u['user_id']} and follow_userid = 1 ")) {
			if($get_follow->num_rows == 0) {
				mysqli_query($mysqli,"insert into userfollow (user_id, follow_userid) values ({$u['user_id']},1)");
			}
		}
	}
	
	
}

msqli_close($mysqli);