<?php

$start = strtotime($_POST['start']);
$end = strtotime($_POST['end']);
$gender = $_POST['gender'];
$duan = $_POST['duan'] + 0;
$leib = $_POST['leib'] + 0;

$this_year = date('Y',time());

//less than 18
$duan_1 = strtotime($this_year-18 . '-01-01');

//19~22
$duan_2 = strtotime($this_year-22 . '-01-01');

//23~26
$duan_3 = strtotime($this_year-26 . '-01-01');

//27~30
$duan_4 = strtotime($this_year-30 . '-01-01');

//31~35
$duan_5 = strtotime($this_year-35 . '-01-01');

//36~40
$duan_6 = strtotime($this_year-40 . '-01-01');

//greater than 40


$sql = "select count(*) from message m , userinfo ui, user u where m.author_id = ui.user_id and ui.user_id = u.user_id and ui.reg_time between $start and $end ";

if($gender == 'M') {
	$sql .= "and ui.gender = 'M'";
}
elseif($gender == 'F') {
	$sql .= "and ui.gender = 'F'";
}


if($duan >= 0) {
  
	if($duan == 0) {
		$sql .= " and ui.birthday > $duan_1";
	}
	elseif($duan == 1) {
		$sql .= " and ui.birthday between $duan_2 and $duan_1";
	}
	elseif($duan == 2) {
		$sql .= " and ui.birthday between $duan_3 and $duan_2";
	}
	elseif($duan == 3) {
		$sql .= " and ui.birthday between $duan_4 and $duan_3";
	}
	elseif($duan == 4) {
		$sql .= " and ui.birthday between $duan_5 and $duan_4";
	}
	elseif($duan == 5) {
		$sql .= " and ui.birthday between $duan_6 and $duan_5";
	}
	elseif($duan == 6) {
		$sql .= " and ui.birthday < $duan_6";
	}

}

if($leib == 2) {
	$sql .= " and u.type = 0";
}
elseif($leib == 3) {
	$sql .= " and u.type = 1";
}
elseif($leib == 4) {
	$sql .= " and u.type = 2";
}

$mysqli = new mysqli('localhost', 'root', 'root', 'say');

if ($mysqli->connect_errno) {
	print_r($mysqli->error);
}

if (!$mysqli->set_charset("utf8")) {
	print_r($mysqli->error);
}



if($get_count = $mysqli->query($sql)) {
	$count = $get_count->fetch_row()[0];
	
}
else {
	printf("%s",$mysqli->error);
}

echo $count;
exit;

