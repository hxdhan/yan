<?php
include ('../../header.php');

if(!check_login()) {
	$ret['ErrorMsg'] = '没有登录';
	exit (json_encode($ret));
}

if(empty($_POST['longitude']) || empty($_POST['latitude']) ) {
	$ret['ErrorMsg'] = '参数错误';
	exit (json_encode($ret));
}

$longitude = $_POST['longitude'];
$latitude = $_POST['latitude'];

$R = 6371;
//$maxLat = $latitude + rad2deg($distance/$R);
//$minLat = $latitude - rad2deg($distance/$R);
//$maxLon = $longitude + rad2deg($distance/$R/cos(deg2rad($latitude)));
//$minLon = $longitude - rad2deg($distance/$R/cos(deg2rad($latitude)));

// if (!($stmt = $mysqli->prepare("SELECT *, acos(sin(?)*sin(radians(latitude)) + cos(?)*cos(radians(latitude))*cos(radians(longitude)-?)) * ? AS distance
				// FROM
					// (
					// SELECT *
					// FROM msgwall
					// WHERE latitude BETWEEN ? AND ?
						// AND longitude BETWEEN ? And ?	 
						
					// ) As w 
					// WHERE acos(sin(?)*sin(radians(latitude)) + cos(?)*cos(radians(latitude))*cos(radians(longitude)-?)) * ? < radius
					// ORDER BY distance ASC "))) {
if (!($stmt = $mysqli->prepare("SELECT *, acos(sin(?)*sin(radians(latitude)) + cos(?)*cos(radians(latitude))*cos(radians(longitude)-?)) * ? AS distance FROM msgwall WHERE acos(sin(?)*sin(radians(latitude)) + cos(?)*cos(radians(latitude))*cos(radians(longitude)-?)) * ? < radius ORDER BY distance ASC "))) {
	$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	exit (json_encode($ret));	
		
}

if (!$stmt->bind_param("dddidddi", deg2rad($latitude), deg2rad($latitude), deg2rad($longitude), $R, deg2rad($latitude), deg2rad($latitude), deg2rad($longitude), $R)) {
  $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
	exit (json_encode($ret));
}

if (!$stmt->execute()) {
  $ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
	exit (json_encode($ret));
}

//$stmt->store_result();

$meta = $stmt->result_metadata();

while ($column = $meta->fetch_field()) {
   $bindVarsArray[] = &$result[$column->name];
}        
call_user_func_array(array($stmt, 'bind_result'), $bindVarsArray);

$walls = array();
while($stmt->fetch()) {
	$e = array();
	foreach($result as $key => $val) { 
		$e[$key] = $val;
	}
	
	$walls[] = $e;
	unset($e);

}

$stmt->close();

$mysqli->close();


$ret['status'] = 1;
$ret['ErrorMsg'] = '';
$ret['walls'] = $walls;

exit (json_encode($ret));