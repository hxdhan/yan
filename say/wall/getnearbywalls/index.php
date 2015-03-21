<?php
include ('../../header.php');

if(!check_login()) {
	$ret['ErrorMsg'] = '没有登录';
	exit (json_encode($ret));
}

if(empty($_POST['longitude']) || empty($_POST['latitude'])  ) {
	$ret['ErrorMsg'] = '参数错误';
	exit (json_encode($ret));
}

$myid = 0;
if(isset($_POST['myid']) && intval($_POST['myid']) > 0 ) {

	$myid = $_POST['myid'] + 0 ;
}
else {
	$myid = $user['user_id'];
}

$longitude = $_POST['longitude'] + 0 ;
$latitude = $_POST['latitude'] + 0 ;

$count = 10;

if(isset($_POST['count']) && intval($_POST['count']) > 0 ) {

	$count = $_POST['count'] + 0 ;
}

$distance = 10.0;


$R = 6371;

$maxLat = $latitude + rad2deg($distance/$R);

//var_dump($maxLat);

$minLat = $latitude - rad2deg($distance/$R);

//var_dump($minLat);
  
$maxLon = $longitude + rad2deg($distance/$R/cos(deg2rad($latitude)));

//var_dump($maxLon);

$minLon = $longitude - rad2deg($distance/$R/cos(deg2rad($latitude)));
//var_dump($minLon);

// if (!($stmt = $mysqli->prepare("SELECT w.*,acos(sin(?)*sin(radians(w.latitude)) + cos(?)*cos(radians(w.latitude))*cos(radians(w.longitude)-?)) * ? AS distance
// FROM
	// (
	// SELECT *
	// FROM msgwall
	// WHERE latitude BETWEEN ? AND ?
		// AND longitude BETWEEN ? And ?	 
	// ) As w 
	// WHERE acos(sin(?)*sin(radians(w.latitude)) + cos(?)*cos(radians(w.latitude))*cos(radians(w.longitude)-?)) * ? < ?
	// ORDER BY distance ASC limit ? "))) {
// $ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
// exit (json_encode($ret));	

// }

if (!($stmt = $mysqli->prepare("SELECT w.*,acos(sin(?)*sin(radians(w.latitude)) + cos(?)*cos(radians(w.latitude))*cos(radians(w.longitude)-?)) * ? AS distance
FROM
	msgwall w
where message_count > 2	and recommand_level > 0
	ORDER BY recommand_level desc, message_count/(distance + 0.001) desc limit ? "))) {
$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
exit (json_encode($ret));	

}
				
// if (!$stmt->bind_param("dddidddddddidi",deg2rad($latitude), deg2rad($latitude), deg2rad($longitude), $R,$minLat, $maxLat, $minLon, $maxLon,deg2rad($latitude), deg2rad($latitude), deg2rad($longitude), $R, $distance, $count)) {
	// $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
	// exit (json_encode($ret));
// }

if (!$stmt->bind_param("dddii",deg2rad($latitude), deg2rad($latitude), deg2rad($longitude), $R, $count)) {
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

$all_walls = array();

while($stmt->fetch()) {
	$ele = array();
	foreach($result as $key => $val) { 
		$ele[$key] = $val;
	}
	$ele['favourate_count'] = get_wallfavourate_count($ele['wall_id']);
	
	$ele['my_favourate'] = is_my_favourage_wall($myid, $ele['wall_id']);
	$all_walls[] = $ele;
	
}

$stmt->close();

$mysqli->close();

$ret['status'] = 1;
$ret['ErrorMsg'] = '';
$ret['walls'] = $all_walls;

exit (json_encode($ret,JSON_UNESCAPED_UNICODE));