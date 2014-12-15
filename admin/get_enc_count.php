<?php

$start = strtotime($_POST['start']);
$end = strtotime($_POST['end']);

$mysqli = new mysqli('localhost', 'root', 'root', 'say');

if ($mysqli->connect_errno) {
	print_r($mysqli->error);
}

if (!$mysqli->set_charset("utf8")) {
	print_r($mysqli->error);
}

$count = 0;

if($get_count = $mysqli->query("select count(*) from userencounter where encounter_time between $start and $end and status = 1")) {
	$count = $get_count->fetch_row()[0];
}

echo $count;
exit;