<?php
include ('../../header.php');



if(!check_login()) {
	$ret['ErrorMsg'] = '没有登录';
	exit (json_encode($ret));
}


$user_id = $user['user_id'];

if(isset($_POST['user_id']) && $_POST['user_id'] != '') {
	$user_id = $_POST['user_id'] + 0;
}

if(empty($_POST['name']) || empty($_POST['longitude']) || empty($_POST['latitude']) ) {
	$ret['ErrorMsg'] = '参数错误';
	exit (json_encode($ret));
}
$name = $_POST['name'];
$longitude = $_POST['longitude'];
$latitude = $_POST['latitude'];

if (!($stmt = $mysqli->prepare("select count(*) from msgwall where name = ? "))) {
	$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	exit (json_encode($ret));	
		
}

if (!$stmt->bind_param("s",  $name)) {
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

if($count > 0) {
	$ret['ErrorMsg'] =  "该名字已经使用了";
	exit (json_encode($ret));
}

$info = '';
if(isset($_POST['info']) && $_POST['info'] != '') {
	$info = $_POST['info'];
}

$radius = 0;

if(isset($_POST['radius']) && doubleval($_POST['radius']) > 0) {
	$radius = doubleval($_POST['radius']);
}

$web_url = '';
if(isset($_POST['web_url']) && $_POST['web_url'] != '') {
	$web_url = $_POST['web_url'];
	
}

$type = 0;
if(isset($_POST['type']) && $_POST['type'] != '') {
	$type = $_POST['type'];
	
}


$time = time();

$allowed_image = array('jpg' => 'image/jpeg','png' => 'image/png','gif' => 'image/gif');

$image_url = '';

if(isset($_FILES['image'])) {
	$finfo = new finfo(FILEINFO_MIME_TYPE);
	$ext = array_search($finfo->file($_FILES['image']['tmp_name']),$allowed_image);

	if($ext === false) {
		$ret['ErrorMsg'] = '图片格式错误';
		exit (json_encode($ret));
		
	}
	$prefix_name = microtime(true) * 10000;
	$file_name = $prefix_name .'.'.$ext;
	$file_path = check_path ();
	$file_path_name = $file_path."/".$file_name;
	
	rename($_FILES['image']['tmp_name'],$file_path_name);
	$date_name = date('Ymd');
	$file_url = $static_path . $date_name . '/';
	$image_url = $file_url . $file_name;
}

$status = 1;

if(isset($_POST['status']) && intval($_POST['status']) > 0) {
	$status = intval($_POST['status']);
}


if (!($stmt = $mysqli->prepare("insert into msgwall(owner_userid,name,info,image_url,time,web_url,longitude,latitude,radius,`type`,status) values(?,?,?,?,?,?,?,?,?,?,?) "))) {
	$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	exit (json_encode($ret));	
		
}

if (!$stmt->bind_param("isssisdddii",  $user_id, $name,$info,$image_url,$time,$web_url,$longitude,$latitude,$radius,$type,$status)) {
  $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
	exit (json_encode($ret));
}

if (!$stmt->execute()) {
  $ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
	exit (json_encode($ret));
}

$stmt->close();

$wall_id = $mysqli->insert_id;

if (!($stmt = $mysqli->prepare("select * from msgwall where wall_id = ?"))) {
	$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	exit (json_encode($ret));	
		
}

if (!$stmt->bind_param("i",  $wall_id)) {
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

$stmt->fetch();

$stmt->close();

//update user score
update_user_point($user_id, 5);

$mysqli->close();


$ret['status'] = 1;
$ret['ErrorMsg'] = '';
$ret['wall'] = $result;

exit (json_encode($ret));