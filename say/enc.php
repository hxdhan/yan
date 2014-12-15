<?php

$mysqli = new mysqli('localhost','root','root','say');

if ($mysqli->connect_errno) {
	$ret['ErrorMsg'] = "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
	exit (json_encode($ret));	
}

if (!$mysqli->set_charset("utf8")) {
	$ret['ErrorMsg'] = "Error loading character set utf8". $mysqli->error;
	exit (json_encode($ret));	
}

if($get_encounter = $mysqli->query("SELECT encounter_id, time FROM userencounter WHERE status = 1")) {

	while($encounter = $get_encounter->fetch_row()) {
		var_dump($encounter);
	}		
	
}
else {
	printf("%s",$mysqli->error);
}