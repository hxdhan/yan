<?php

$mysqli = new mysqli('localhost', 'root', 'root', 'say');

if ($mysqli->connect_errno) {
	print_r($mysqli->error);
}

if (!$mysqli->set_charset("utf8")) {
	print_r($mysqli->error);
}

if($get_count = $mysqli->query("select count(*) from userinfo where description = ''")) {
	$count = $get_count->fetch_row()[0];
}
else {
	printf("%s",$mysqli->error);
}

echo $count;
exit;