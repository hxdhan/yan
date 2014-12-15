<?php
include ('../../header.php');

if(!check_login()) {
	$ret['ErrorMsg'] = '没有登录';
	exit (json_encode($ret));
}

if(isset($_POST['notif_id']) && $_POST['notif_id'] != '' ) {
	$notif_id = $_POST['notif_id'] + 0;
}

if($mysqli->query("UPDATE usrnotification SET new = 0 WHERE notif_id = $notif_id ")) {
	
}
else {
	//error?
}



$mysqli->close();

$ret['status'] = 1;
$ret['ErrorMsg'] = '';

exit (json_encode($ret));