<?php
include ('../../header.php');

//print_r($_POST);

if(!check_login()) {
	$ret['ErrorMsg'] = '没有登录';
	exit (json_encode($ret));
}

if(empty($_POST['longitude']) || empty($_POST['latitude'])  ) {
	$ret['ErrorMsg'] = '参数错误';
	exit (json_encode($ret));
}

$longitude = $_POST['longitude'] + 0 ;
$latitude = $_POST['latitude'] + 0 ;


$distance = 5.0;


$R = 6371;

$count = 20;

$maxLat = $latitude + rad2deg($distance/$R);

//var_dump($maxLat);

$minLat = $latitude - rad2deg($distance/$R);

//var_dump($minLat);
  
$maxLon = $longitude + rad2deg($distance/$R/cos(deg2rad($latitude)));

//var_dump($maxLon);

$minLon = $longitude - rad2deg($distance/$R/cos(deg2rad($latitude)));
//var_dump($minLon);

if (!($stmt = $mysqli->prepare("SELECT m.author_id, m.time, m.longitude,m.latitude,acos(sin(?)*sin(radians(m.latitude)) + cos(?)*cos(radians(m.latitude))*cos(radians(m.longitude)-?)) * ? AS distance
FROM
	(
	SELECT *
	FROM message
	WHERE latitude BETWEEN ? AND ?
		AND longitude BETWEEN ? And ?	 
	) As m  
	WHERE acos(sin(?)*sin(radians(m.latitude)) + cos(?)*cos(radians(m.latitude))*cos(radians(m.longitude)-?)) * ? < ?
	ORDER BY distance ASC"))) {
$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
exit (json_encode($ret));	

}
				
if (!$stmt->bind_param("dddidddddddid",deg2rad($latitude), deg2rad($latitude), deg2rad($longitude), $R,$minLat, $maxLat, $minLon, $maxLon,deg2rad($latitude), deg2rad($latitude), deg2rad($longitude), $R, $distance)) {
	$ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
	exit (json_encode($ret));
}
	

//execute
	
if (!$stmt->execute()) {
	$ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
	exit (json_encode($ret));
}

$stmt->store_result();

$meta = $stmt->result_metadata();

while ($column = $meta->fetch_field()) {
	 $bindVarsArray[] = &$result[$column->name];
} 
       
call_user_func_array(array($stmt, 'bind_result'), $bindVarsArray);
$all_users = array();
while($stmt->fetch()) {
	$ele = array();
	foreach($result as $key => $val) { 
		$ele[$key] = $val;
	}
	$all_users[] = $ele;
	
}
$results = array();
$check = array();
if (!($stmt = $mysqli->prepare("SELECT * FROM userinfo WHERE user_id = ?"))) {
	$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	exit (json_encode($ret));	
	
}

foreach($all_users as $u) {
	//var_dump($result);
	$c = array();
	$c = array_merge($c, $u);
	if (!$stmt->bind_param("i", $c['author_id'])) {
				$ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
				exit (json_encode($ret));
	}
	if (!$stmt->execute()) {
				$ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
				exit (json_encode($ret));
	}
	
	$stmt->store_result();

	$meta = $stmt->result_metadata();
	$result = array();
	while ($column = $meta->fetch_field()) {
		 $bindVarsArray[] = &$result[$column->name];
	} 
	call_user_func_array(array($stmt, 'bind_result'), $bindVarsArray);
	$stmt->fetch();
	$c = array_merge($c,$result);
	
	if(!isset($check[$c['user_id']]) ) {
		$check[$c['user_id']] = $c;
		
	}
	else {
		if( $check[$c['user_id']]['time'] < $c['time']) {
			$check[$c['user_id']]['time'] = $c['time'];
		}
	}
	unset($c);
}

foreach($check as $key=>$val) {
	$results[] = $val;
}


$stmt->close();

$mysqli->close();

$ret['status'] = 1;
$ret['ErrorMsg'] = '';
$ret['users'] = $results;
exit (json_encode($ret,JSON_UNESCAPED_UNICODE));

