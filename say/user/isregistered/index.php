<?php
include ('../../header.php');

if(empty($_POST['cellphone']) ) {
	$ret['ErrorMsg'] = '参数错误';
	exit (json_encode($ret));
}
$cellphone = $_POST['cellphone'];


if (!($stmt = $mysqli->prepare("SELECT count(*) FROM user WHERE cellphone = ? "))) {
		$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		exit (json_encode($ret));	
			
	}

if (!$stmt->bind_param("s", $cellphone)) {
	  $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

if (!$stmt->execute()) {
	  $ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

$stmt->bind_result($count);

$stmt->fetch();

$stmt->close();

$mysqli->close();

$ret['status'] = 1;
$ret['ErrorMsg'] = '';

$ret['result'] = $count > 0 ? 1: 0 ;
exit (json_encode($ret));
 
?>