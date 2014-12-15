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

$category_id = 0;

$except_id = 0;

if(isset($_POST['category_id'])) {
	$category_id = $_POST['category_id'];
}

if(isset($_POST['except_id'])) {
	$except_id = $_POST['except_id'];
}

$myid = 0;

if(isset($_POST['myid']) && intval($_POST['myid']) > 0 ) {

	$myid = $_POST['myid'] + 0 ;
}
else {
	$myid = $user['user_id'];
}

$cat = '('.implode(',',explode(',',$category_id)).")";

$except = '('.implode(',',explode(',',$except_id)).")";

$count = 20;

if(isset($_POST['count']) && intval($_POST['count']) > 0) {
	$count = $_POST['count'] + 0 ;
}

$longitude = $_POST['longitude'] + 0 ;
$latitude = $_POST['latitude'] + 0 ;

$distance = 1;

if(isset($_POST['distance'])) {
	$distance = $_POST['distance'] + 0;
}

$gender = 'A';
if(isset($_POST['gender'])) {
	if($_POST['gender'] == 'M') {
		$gender = 'M';
	}
	if($_POST['gender'] == 'F') {
		$gender = 'F';
	}
}

//sort method
$sort_method = 'optimal';

if(isset($_POST['sort_method'])) {
	if($_POST['sort_method'] === 'distance') {
		$sort_method = 'distance';
	}
	if($_POST['sort_method'] === 'time') {
		$sort_method = 'time';
	}
}

$R = 6371;

$maxLat = $latitude + rad2deg($distance/$R);

//var_dump($maxLat);

$minLat = $latitude - rad2deg($distance/$R);

//var_dump($minLat);
  
$maxLon = $longitude + rad2deg($distance/$R/cos(deg2rad($latitude)));

//var_dump($maxLon);

$minLon = $longitude - rad2deg($distance/$R/cos(deg2rad($latitude)));

//var_dump($minLon);

if($distance > 0) {
	//sort method
  if($sort_method === 'optimal') {
		//no category
		if($category_id == 0) {
			if($gender == 'A') {
				//get all gender
				if (!($stmt = $mysqli->prepare("SELECT *, acos(sin(?)*sin(radians(latitude)) + cos(?)*cos(radians(latitude))*cos(radians(longitude)-?)) * ? AS distance
				FROM
					(
					SELECT *
					FROM message
					WHERE latitude BETWEEN ? AND ?
						AND longitude BETWEEN ? And ?	 
						AND message_id not in $except 
					) As m  
					WHERE acos(sin(?)*sin(radians(latitude)) + cos(?)*cos(radians(latitude))*cos(radians(longitude)-?)) * ? < ?
					ORDER BY new_time DESC  LIMIT ?"))) {
				$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
				exit (json_encode($ret));	
				
				}
				
				if (!$stmt->bind_param("dddidddddddidi", deg2rad($latitude), deg2rad($latitude), deg2rad($longitude), $R, $minLat, $maxLat, $minLon, $maxLon,deg2rad($latitude), deg2rad($latitude), deg2rad($longitude), $R, $distance,  $count)) {
					$ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
					exit (json_encode($ret));
				}
			}
			else {
				//join user table

				if (!($stmt = $mysqli->prepare("SELECT *, acos(sin(?)*sin(radians(latitude)) + cos(?)*cos(radians(latitude))*cos(radians(longitude)-?)) * ? AS distance
				FROM
					(
					SELECT me.*
					FROM message me,userinfo ui
					WHERE me.author_id = ui.user_id and ui.gender = ? 
						AND me.latitude BETWEEN ? AND ?
						AND me.longitude BETWEEN ? And ?	
						AND me.message_id not in $except 
					) As m  
					WHERE acos(sin(?)*sin(radians(latitude)) + cos(?)*cos(radians(latitude))*cos(radians(longitude)-?)) * ? < ?
					ORDER BY new_time DESC  LIMIT ?"))) {
				$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
				exit (json_encode($ret));	
				
				}

				if (!$stmt->bind_param("dddisdddddddidi", deg2rad($latitude), deg2rad($latitude), deg2rad($longitude), $R, $gender, $minLat, $maxLat, $minLon, $maxLon,deg2rad($latitude), deg2rad($latitude), deg2rad($longitude), $R, $distance,  $count)) {
					$ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
					exit (json_encode($ret));
				}
			}
		}

		//category id should include
		else {
			
			if($gender == 'A') {
				//get all gender
				if (!($stmt = $mysqli->prepare("SELECT *, acos(sin(?)*sin(radians(latitude)) + cos(?)*cos(radians(latitude))*cos(radians(longitude)-?)) * ? AS distance
				FROM
					(
					SELECT *
					FROM message
					WHERE latitude BETWEEN ? AND ?
						AND longitude BETWEEN ? And ?	 
						and message_id not in $except 
						and category_id in $cat
					) As m  
					WHERE acos(sin(?)*sin(radians(latitude)) + cos(?)*cos(radians(latitude))*cos(radians(longitude)-?)) * ? < ?
					ORDER BY new_time DESC  LIMIT ?"))) {
				$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
				exit (json_encode($ret));	
				
				}
				
				if (!$stmt->bind_param("dddidddddddidi", deg2rad($latitude), deg2rad($latitude), deg2rad($longitude), $R, $minLat, $maxLat, $minLon, $maxLon,deg2rad($latitude), deg2rad($latitude), deg2rad($longitude), $R, $distance,  $count)) {
					$ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
					exit (json_encode($ret));
				}
			}

			else {
				//join user table
				if (!($stmt = $mysqli->prepare("SELECT *, acos(sin(?)*sin(radians(latitude)) + cos(?)*cos(radians(latitude))*cos(radians(longitude)-?)) * ? AS distance
				FROM
					(
					SELECT me.*
					FROM message me,userinfo ui
					WHERE me.author_id = ui.user_id and ui.gender = ? and me.latitude BETWEEN ? AND ?
						AND me.longitude BETWEEN ? And ?	and me.message_id not in $except  and category_id in $cat
					) As m  
					WHERE acos(sin(?)*sin(radians(latitude)) + cos(?)*cos(radians(latitude))*cos(radians(longitude)-?)) * ? < ?
					ORDER BY new_time DESC  LIMIT ?"))) {
				$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
				exit (json_encode($ret));	
				
				}

				if (!$stmt->bind_param("dddisdddddddidi", deg2rad($latitude), deg2rad($latitude), deg2rad($longitude), $R, $gender, $minLat, $maxLat, $minLon, $maxLon,deg2rad($latitude), deg2rad($latitude), deg2rad($longitude), $R, $distance,  $count)) {
					$ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
					exit (json_encode($ret));
				}
			}
		}
	}
	//sort by distance
	elseif($sort_method === 'distance') {
		//no category 
		
		if($category_id == 0) {
			// no gender
			if($gender == 'A') {
				
				if (!($stmt = $mysqli->prepare("SELECT *, acos(sin(?)*sin(radians(latitude)) + cos(?)*cos(radians(latitude))*cos(radians(longitude)-?)) * ? AS distance
				FROM
					(
					SELECT *
					FROM message
					WHERE latitude BETWEEN ? AND ?
						AND longitude BETWEEN ? And ?	 
						and message_id not in $except 
					) As m  
					WHERE acos(sin(?)*sin(radians(latitude)) + cos(?)*cos(radians(latitude))*cos(radians(longitude)-?)) * ? < ?
					ORDER BY distance ASC  LIMIT ?"))) {
				$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
				exit (json_encode($ret));	
				
				}
				
				if (!$stmt->bind_param("dddidddddddidi", deg2rad($latitude), deg2rad($latitude), deg2rad($longitude), $R, $minLat, $maxLat, $minLon, $maxLon,deg2rad($latitude), deg2rad($latitude), deg2rad($longitude), $R, $distance,  $count)) {
					$ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
					exit (json_encode($ret));
				}
			}
			// gender
			else {
				if (!($stmt = $mysqli->prepare("SELECT *, acos(sin(?)*sin(radians(latitude)) + cos(?)*cos(radians(latitude))*cos(radians(longitude)-?)) * ? AS distance
				FROM
					(
					SELECT me.*
					FROM message me,userinfo ui
					WHERE me.author_id = ui.user_id and ui.gender = ? and me.latitude BETWEEN ? AND ?
						AND me.longitude BETWEEN ? And ?	and me.message_id not in $except 
					) As m  
					WHERE acos(sin(?)*sin(radians(latitude)) + cos(?)*cos(radians(latitude))*cos(radians(longitude)-?)) * ? < ?
					Order by distance ASC  LIMIT ?"))) {
				$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
				exit (json_encode($ret));	
				
				}

				if (!$stmt->bind_param("dddisdddddddidi", deg2rad($latitude), deg2rad($latitude), deg2rad($longitude), $R, $gender, $minLat, $maxLat, $minLon, $maxLon,deg2rad($latitude), deg2rad($latitude), deg2rad($longitude), $R, $distance,  $count)) {
					$ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
					exit (json_encode($ret));
				}
				
			}
		
		}
		// category 
		else {
			//no gender
			if($gender == 'A') {
				if (!($stmt = $mysqli->prepare("SELECT *, acos(sin(?)*sin(radians(latitude)) + cos(?)*cos(radians(latitude))*cos(radians(longitude)-?)) * ? AS distance
				FROM
					(
					SELECT *
					FROM message
					WHERE latitude BETWEEN ? AND ?
						AND longitude BETWEEN ? And ?	 
						and message_id not in $except 
						and category_id in $cat
					) As m  
					WHERE acos(sin(?)*sin(radians(latitude)) + cos(?)*cos(radians(latitude))*cos(radians(longitude)-?)) * ? < ?
					Order by distance ASC  LIMIT ?"))) {
				$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
				exit (json_encode($ret));	
				
				}
				
				if (!$stmt->bind_param("dddidddddddidi", deg2rad($latitude), deg2rad($latitude), deg2rad($longitude), $R, $minLat, $maxLat, $minLon, $maxLon,deg2rad($latitude), deg2rad($latitude), deg2rad($longitude), $R, $distance,  $count)) {
					$ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
					exit (json_encode($ret));
				}
			}
			//gender join user table
			else {
					if (!($stmt = $mysqli->prepare("SELECT *, acos(sin(?)*sin(radians(latitude)) + cos(?)*cos(radians(latitude))*cos(radians(longitude)-?)) * ? AS distance
				FROM
					(
					SELECT me.*
					FROM message me,userinfo ui
					WHERE me.author_id = ui.user_id and ui.gender = ? and me.latitude BETWEEN ? AND ?
						AND me.longitude BETWEEN ? And ?	and me.message_id not in $except  and category_id in $cat
					) As m  
					WHERE acos(sin(?)*sin(radians(latitude)) + cos(?)*cos(radians(latitude))*cos(radians(longitude)-?)) * ? < ?
					Order by distance ASC  LIMIT ?"))) {
				$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
				exit (json_encode($ret));	
				
				}

				if (!$stmt->bind_param("dddisdddddddidi", deg2rad($latitude), deg2rad($latitude), deg2rad($longitude), $R, $gender, $minLat, $maxLat, $minLon, $maxLon,deg2rad($latitude), deg2rad($latitude), deg2rad($longitude), $R, $distance,  $count)) {
					$ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
					exit (json_encode($ret));
				}
			
			}
			
		}
	}
	elseif($sort_method === 'time') {
		//no category
		if($category_id == 0) {
			//no gender
			if($gender == 'A') {
				if (!($stmt = $mysqli->prepare("SELECT *, acos(sin(?)*sin(radians(latitude)) + cos(?)*cos(radians(latitude))*cos(radians(longitude)-?)) * ? AS distance
				FROM
					(
					SELECT *
					FROM message
					WHERE latitude BETWEEN ? AND ?
						AND longitude BETWEEN ? And ?	 
						and message_id not in $except 
					) As m  
					WHERE acos(sin(?)*sin(radians(latitude)) + cos(?)*cos(radians(latitude))*cos(radians(longitude)-?)) * ? < ?
					Order by time DESC  LIMIT ?"))) {
				$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
				exit (json_encode($ret));	
				
				}
				
				if (!$stmt->bind_param("dddidddddddidi", deg2rad($latitude), deg2rad($latitude), deg2rad($longitude), $R, $minLat, $maxLat, $minLon, $maxLon,deg2rad($latitude), deg2rad($latitude), deg2rad($longitude), $R, $distance,  $count)) {
					$ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
					exit (json_encode($ret));
				}
			}
			//gender
			else {
				if (!($stmt = $mysqli->prepare("SELECT *, acos(sin(?)*sin(radians(latitude)) + cos(?)*cos(radians(latitude))*cos(radians(longitude)-?)) * ? AS distance
				FROM
					(
					SELECT me.*
					FROM message me,userinfo ui
					WHERE me.author_id = ui.user_id and ui.gender = ? and me.latitude BETWEEN ? AND ?
						AND me.longitude BETWEEN ? And ?	and me.message_id not in $except 
					) As m  
					WHERE acos(sin(?)*sin(radians(latitude)) + cos(?)*cos(radians(latitude))*cos(radians(longitude)-?)) * ? < ?
					Order by time DESC  LIMIT ?"))) {
				$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
				exit (json_encode($ret));	
				
				}

				if (!$stmt->bind_param("dddisdddddddidi", deg2rad($latitude), deg2rad($latitude), deg2rad($longitude), $R, $gender, $minLat, $maxLat, $minLon, $maxLon,deg2rad($latitude), deg2rad($latitude), deg2rad($longitude), $R, $distance,  $count)) {
					$ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
					exit (json_encode($ret));
				}
				
			}
		}
		//category
		else {
			//no gender
			if($gender == 'A') {
				if (!($stmt = $mysqli->prepare("SELECT *, acos(sin(?)*sin(radians(latitude)) + cos(?)*cos(radians(latitude))*cos(radians(longitude)-?)) * ? AS distance
				FROM
					(
					SELECT *
					FROM message
					WHERE latitude BETWEEN ? AND ?
						AND longitude BETWEEN ? And ?	 
						and message_id not in $except 
						and category_id in $cat
					) As m  
					WHERE acos(sin(?)*sin(radians(latitude)) + cos(?)*cos(radians(latitude))*cos(radians(longitude)-?)) * ? < ?
					Order by time DESC  LIMIT ?"))) {
				$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
				exit (json_encode($ret));	
				
				}
				
				if (!$stmt->bind_param("dddidddddddidi", deg2rad($latitude), deg2rad($latitude), deg2rad($longitude), $R, $minLat, $maxLat, $minLon, $maxLon,deg2rad($latitude), deg2rad($latitude), deg2rad($longitude), $R, $distance,  $count)) {
					$ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
					exit (json_encode($ret));
				}
			}
			//gender
			else {
					if (!($stmt = $mysqli->prepare("SELECT *, acos(sin(?)*sin(radians(latitude)) + cos(?)*cos(radians(latitude))*cos(radians(longitude)-?)) * ? AS distance
				FROM
					(
					SELECT me.*
					FROM message me,userinfo ui
					WHERE me.author_id = ui.user_id and ui.gender = ? and me.latitude BETWEEN ? AND ?
						AND me.longitude BETWEEN ? And ?	and me.message_id not in $except  and me.category_id in $cat
					) As m  
					WHERE acos(sin(?)*sin(radians(latitude)) + cos(?)*cos(radians(latitude))*cos(radians(longitude)-?)) * ? < ?
					Order by time DESC  LIMIT ?"))) {
				$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
				exit (json_encode($ret));	
				
				}

				if (!$stmt->bind_param("dddisdddddddidi", deg2rad($latitude), deg2rad($latitude), deg2rad($longitude), $R, $gender, $minLat, $maxLat, $minLon, $maxLon,deg2rad($latitude), deg2rad($latitude), deg2rad($longitude), $R, $distance,  $count)) {
					$ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
					exit (json_encode($ret));
				}
				
			}
		}
	
	}

}

//no distance  should search from all data
else {
	//distance not setting so should seatch by other way
	if($sort_method === 'optimal') {
	
		if($category_id == 0) {
			if($gender == 'A') {
				if (!($stmt = $mysqli->prepare("SELECT * FROM (SELECT *, acos(sin(?)*sin(radians(latitude)) + cos(?)*cos(radians(latitude))*cos(radians(longitude)-?)) * ? AS distance
				FROM message WHERE message_id not in $except ORDER BY distance ASC LIMIT ?) m ORDER BY m.new_time DESC "))) {
				$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
				exit (json_encode($ret));	
				
				}
				
				if (!$stmt->bind_param("dddii", deg2rad($latitude), deg2rad($latitude), deg2rad($longitude), $R, $count)) {
					$ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
					exit (json_encode($ret));
				}
			
			}
			//gender join user table 
		
			else {
				if (!($stmt = $mysqli->prepare("SELECT * FROM (SELECT me.*, acos(sin(?)*sin(radians(latitude)) + cos(?)*cos(radians(latitude))*cos(radians(longitude)-?)) * ? AS distance
				FROM message me, userinfo ui WHERE me.author_id = ui.user_id and ui.gender = ? and me.message_id not in $except Order by distance ASC LIMIT ?) m ORDER BY m.new_time DESC "))) {
				$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
				exit (json_encode($ret));	
				
				}
				
				if (!$stmt->bind_param("dddisi", deg2rad($latitude), deg2rad($latitude), deg2rad($longitude), $R, $gender, $count)) {
					$ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
					exit (json_encode($ret));
				}
			}
		}
		
		//category id 
		else {
			if($gender == 'A') {
				if (!($stmt = $mysqli->prepare("SELECT * FROM (SELECT *, acos(sin(?)*sin(radians(latitude)) + cos(?)*cos(radians(latitude))*cos(radians(longitude)-?)) * ? AS distance
				FROM message WHERE message_id not in $except and category_id in $cat ORDER BY distance ASC LIMIT ?) m ORDER BY m.new_time DESC"))) {
				$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
				exit (json_encode($ret));	
				
				}
				
				if (!$stmt->bind_param("dddii", deg2rad($latitude), deg2rad($latitude), deg2rad($longitude), $R, $count)) {
					$ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
					exit (json_encode($ret));
				}
			
			}
			//gender
			else {
				if (!($stmt = $mysqli->prepare("SELECT * FROM (SELECT me.*, acos(sin(?)*sin(radians(latitude)) + cos(?)*cos(radians(latitude))*cos(radians(longitude)-?)) * ? AS distance
				FROM message me, userinfo ui WHERE me.author_id = ui.user_id and ui.gender = ? and me.message_id not in $except and me.category_id in $cat Order by distance ASC LIMIT ?) m ORDER BY m.new_time DESC"))) {
				$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
				exit (json_encode($ret));	
				
				}
				
				if (!$stmt->bind_param("dddisi", deg2rad($latitude), deg2rad($latitude), deg2rad($longitude), $R, $gender, $count)) {
					$ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
					exit (json_encode($ret));
				}
			}
		}
	
	}
	elseif($sort_method === 'distance') {
		if($category_id == 0) {
			if($gender == 'A') {
				if (!($stmt = $mysqli->prepare("SELECT *, acos(sin(?)*sin(radians(latitude)) + cos(?)*cos(radians(latitude))*cos(radians(longitude)-?)) * ? AS distance
				FROM message WHERE message_id not in $except Order by distance ASC  LIMIT ?"))) {
				$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
				exit (json_encode($ret));	
				
				}
				
				if (!$stmt->bind_param("dddii", deg2rad($latitude), deg2rad($latitude), deg2rad($longitude), $R, $count)) {
					$ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
					exit (json_encode($ret));
				}
			}
			//gender join user table
			else {
				if (!($stmt = $mysqli->prepare("SELECT me.*, acos(sin(?)*sin(radians(latitude)) + cos(?)*cos(radians(latitude))*cos(radians(longitude)-?)) * ? AS distance
				FROM message me, userinfo ui WHERE me.author_id = ui.user_id and ui.gender = ? and message_id not in $except Order by distance ASC  LIMIT ?"))) {
				$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
				exit (json_encode($ret));	
				
				}
				
				if (!$stmt->bind_param("dddisi", deg2rad($latitude), deg2rad($latitude), deg2rad($longitude), $R, $gender, $count)) {
					$ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
					exit (json_encode($ret));
				}
			}
		}
		//category 
		else {
			// no gender 
			if($gender == 'A') {
				if (!($stmt = $mysqli->prepare("SELECT *, acos(sin(?)*sin(radians(latitude)) + cos(?)*cos(radians(latitude))*cos(radians(longitude)-?)) * ? AS distance
				FROM message WHERE message_id not in $except and category_id in $cat Order by distance ASC  LIMIT ?"))) {
				$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
				exit (json_encode($ret));	
				
				}
				
				if (!$stmt->bind_param("dddii", deg2rad($latitude), deg2rad($latitude), deg2rad($longitude), $R, $count)) {
					$ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
					exit (json_encode($ret));
				}
			
			}
			//gender
			else {
				if (!($stmt = $mysqli->prepare("SELECT me.*, acos(sin(?)*sin(radians(latitude)) + cos(?)*cos(radians(latitude))*cos(radians(longitude)-?)) * ? AS distance
				FROM message me, userinfo ui WHERE me.author_id = ui.user_id and ui.gender = ? and me.message_id not in $except and me.category_id in $cat Order by distance ASC  LIMIT ?"))) {
				$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
				exit (json_encode($ret));	
				
				}
				
				if (!$stmt->bind_param("dddisi", deg2rad($latitude), deg2rad($latitude), deg2rad($longitude), $R, $gender, $count)) {
					$ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
					exit (json_encode($ret));
				}
			}
		}
	}
	elseif($sort_method === 'time') {
		//all category
		if($category_id == 0) {
			//all gender
			if($gender == 'A') {
				if (!($stmt = $mysqli->prepare("SELECT * FROM message WHERE message_id not in $except  Order by time DESC  LIMIT ?"))) {
				$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
				exit (json_encode($ret));	
				
				}
				
				if (!$stmt->bind_param("i", $count)) {
					$ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
					exit (json_encode($ret));
				}
			
			}
			//gender join user table
			else {
				if (!($stmt = $mysqli->prepare("SELECT me.* FROM message me, userinfo ui WHERE me.author_id = ui.user_id and ui.gender = ? and  me.message_id not in $except  Order by me.time DESC  LIMIT ?"))) {
				$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
				exit (json_encode($ret));	
				
				}
				
				if (!$stmt->bind_param("si", $gender, $count)) {
					$ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
					exit (json_encode($ret));
				}
			}
		}
		//category
		else {
			//all gender
			if($gender == 'A') {
				if (!($stmt = $mysqli->prepare("SELECT * FROM message WHERE message_id not in $except and category_id in $cat Order by time DESC  LIMIT ?"))) {
				$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
				exit (json_encode($ret));	
				
				}
				
				if (!$stmt->bind_param("i", $count)) {
					$ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
					exit (json_encode($ret));
				}
			}
			//gender
			else {
				if (!($stmt = $mysqli->prepare("SELECT me.* FROM message me, userinfo ui WHERE me.author_id = ui.user_id and ui.gender = ? and  me.message_id not in $except and me.category_id in $cat  Order by me.time DESC  LIMIT ?"))) {
				$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
				exit (json_encode($ret));	
				
				}
				
				if (!$stmt->bind_param("si", $gender, $count)) {
					$ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
					exit (json_encode($ret));
				}
			}
		}
	
	} 
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
$results = array();

while($stmt->fetch()) {
	$ele = array();
	foreach($result as $key => $val) {
		$ele[$key] = $val;
	}
	$ele['like_status'] = get_like_status($ele['message_id'],$myid);
	
	$user = get_userinfo($ele['author_id']);
	
	$ele = array_merge($ele,$user);
	
	$results[] = $ele;
}


$stmt->close();

$mysqli->close();

$ret['status'] = 1;
$ret['ErrorMsg'] = '';
$ret['messages'] = $results;
exit (json_encode($ret,JSON_UNESCAPED_UNICODE));

