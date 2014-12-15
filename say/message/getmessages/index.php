<?php

include ('../../header.php');
$ret['ErrorMsg'] = '接口已不再使用';
exit (json_encode($ret));
if(!check_login()) {
	$ret['ErrorMsg'] = '没有登录';
	exit (json_encode($ret));
}

if(empty($_POST['longitude']) || empty($_POST['latitude']) || empty($_POST['distance']) || empty($_POST['range'])  ) {
	$ret['ErrorMsg'] = '参数错误';
	exit (json_encode($ret));
}

$category_id = 0;

if(isset($_POST['category_id'])) {
	$category_id = $_POST['category_id'];
}


$cat = '('.implode(',',explode(',',$category_id)).")";



if(isset($_POST['sort_method'])) {
	$sort_method = $_POST['sort_method'];
}

$sort_check = array('distance','time','like_count');

if(! in_array($sort_method,$sort_check)) {

	$sort_method = 'distance';
}

$range = explode('-',$_POST['range']);

$start = $range[0];

$offset = $range[1] - $range[0];

$longitude = $_POST['longitude'];
$latitude = $_POST['latitude'];
$distance = $_POST['distance'];

$R = 6371;

$maxLat = $latitude + rad2deg($distance/$R);

//var_dump($maxLat);

$minLat = $latitude - rad2deg($distance/$R);

//var_dump($minLat);
  
$maxLon = $longitude + rad2deg($distance/$R/cos(deg2rad($latitude)));

//var_dump($maxLon);

$minLon = $longitude - rad2deg($distance/$R/cos(deg2rad($latitude)));

//var_dump($minLon);

if($category_id == 0) {

	if (!($stmt = $mysqli->prepare("SELECT *, acos(sin(?)*sin(radians(latitude)) + cos(?)*cos(radians(latitude))*cos(radians(longitude)-?)) * ? AS distance
    FROM (
      SELECT *
      FROM message
      WHERE latitude BETWEEN ? AND ?
        AND longitude BETWEEN ? And ?
      ) As m 
    WHERE acos(sin(?)*sin(radians(latitude)) + cos(?)*cos(radians(latitude))*cos(radians(longitude)-?)) * ? < ?
    Order by $sort_method desc LIMIT ?,? "))) {
	$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	exit (json_encode($ret));	
		
	}

	if (!$stmt->bind_param("dddidddddddidii", deg2rad($latitude), deg2rad($latitude), deg2rad($longitude), $R, $minLat, $maxLat, $minLon, $maxLon, deg2rad($latitude), deg2rad($latitude), deg2rad($longitude), $R, $distance, $start, $offset)) {
  	$ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}
	

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
	$results = array();

	if (!($stmt1 = $mysqli->prepare("SELECT u.nickname,ui.photo_url,ui.description FROM user u, userinfo ui WHERE u.user_id = ui.user_id and u.user_id = ?"))) {
		$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		exit (json_encode($ret));	
		
	}
	while($stmt->fetch()) {
		foreach($result as $key => $val) { 
  		$c[$key] = $val; 
			if($key == 'author_id') {
				if (!$stmt1->bind_param("i", $val)) {
 	 				$ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
					exit (json_encode($ret));
				}
				
				if (!$stmt1->execute()) {
  				$ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
					exit (json_encode($ret));
				}
				$stmt1->bind_result($nickname,$photo_url,$description);
				while($stmt1->fetch()) {
					$c['nickname'] = $nickname;
					$c['photo_url'] = $photo_url;
					$c['description'] = $description;
				}
				
			}
  	} 
	$results[] = $c;
	}
	$stmt1->close();
	$stmt->close();
	
	$mysqli->close();

	$ret['status'] = 1;
	$ret['ErrorMsg'] = '';
	$ret['messages'] = $results;
	exit (json_encode($ret));
}

else {
	if (!($stmt = $mysqli->prepare("SELECT *, acos(sin(?)*sin(radians(latitude)) + cos(?)*cos(radians(latitude))*cos(radians(longitude)-?)) * ? AS distance
    FROM (
      SELECT *
      FROM message
      WHERE latitude BETWEEN ? AND ?
        AND longitude BETWEEN ? And ?
				AND category_id in $cat
      ) As m 
    WHERE acos(sin(?)*sin(radians(latitude)) + cos(?)*cos(radians(latitude))*cos(radians(longitude)-?)) * ? < ?
    Order by $sort_method desc LIMIT ?,? "))) {
	$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	exit (json_encode($ret));	
		
	}

	if (!$stmt->bind_param("dddidddddddidii", deg2rad($latitude), deg2rad($latitude), deg2rad($longitude), $R, $minLat, $maxLat, $minLon, $maxLon,  deg2rad($latitude), deg2rad($latitude), deg2rad($longitude), $R, $distance, $start, $offset)) {
  	$ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}
	

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
	$results = array();
	if (!($stmt1 = $mysqli->prepare("SELECT nickname,photo_url,gender,birthday,description FROM userinfo  WHERE  user_id = ?"))) {
		$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		exit (json_encode($ret));	
		
	}
	while($stmt->fetch()) {
		foreach($result as $key => $val) { 
  		$c[$key] = $val;
			if($key == 'author_id') {
				if (!$stmt1->bind_param("i", $val)) {
 	 				$ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
					exit (json_encode($ret));
				}
				
				if (!$stmt1->execute()) {
  				$ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
					exit (json_encode($ret));
				}
				$stmt1->bind_result($nickname,$photo_url,$gender,$birthday,$description);
				while($stmt1->fetch()) {
					$c['nickname'] = $nickname;
					$c['photo_url'] = $photo_url;
					$c['gender'] = $gender;
					$c['birthday'] = $birthday;
					$c['description'] = $description;
				}
				
			} 
  	} 
	$results[] = $c;
	}

	$stmt1->close();
	$stmt->close();
	
	$mysqli->close();

	$ret['status'] = 1;
	$ret['ErrorMsg'] = '';
	$ret['messages'] = $results;
	exit (json_encode($ret));
}


