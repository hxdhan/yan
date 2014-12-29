<?php
include ('../../header.php');

if(!check_login()) {
	$ret['ErrorMsg'] = '没有登录';
	exit (json_encode($ret));
}

if(empty($_POST['wall_id']) ) {
	$ret['ErrorMsg'] = '参数错误';
	exit (json_encode($ret));
}

$wall_id = $_POST['wall_id'] + 0;

$par_type = '';
$param = array(&$par_type);
$sql_array = array();

if(isset($_POST['name']) && $_POST['name'] != '') {
	$sql_array[] = "name = ?";
	$par_type .= 's';
	$param[] = &$_POST['name'];
	
}

if(isset($_POST['info']) && $_POST['info'] != '') {
	$sql_array[] = "info = ?";
	$par_type .= 's';
	$param[] = &$_POST['info'];
}

if(isset($_POST['radius']) && doubleval($_POST['radius']) > 0 ) {
	$sql_array[] = "radius = ?";
	$par_type .= 'd';
	$param[] = &$_POST['radius'];
}

if(isset($_FILES['image'])) {
	$date_name = date('Ymd');
	
	$file_url = $static_path.$date_name.'/';

	$finfo = new finfo(FILEINFO_MIME_TYPE);
	$allowed_image = array('jpg' => 'image/jpeg','png' => 'image/png','gif' => 'image/gif');

	$image_url = '';
	
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
	
	$image_url = $file_url . $file_name;

	
  $sql_array[] = 'image_url = ?';
	$par_type .= 's';
	$param[] = &$image_url;
	
}

$sql = "UPDATE msgwall SET " . implode(",",$sql_array) . " where wall_id = ?";
$par_type .= 'i';
$param[] = &$wall_id;

if(!($stmt = $mysqli->prepare($sql))) {
	$ret['ErrorMsg'] = "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	exit (json_encode($ret));	
}

call_user_func_array(array($stmt, 'bind_param'), $param);

if (!$stmt->execute()) {
  $ret['ErrorMsg'] = "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
  exit (json_encode($ret));
}

$stmt->close();

if(isset($_POST['name']) && $_POST['name'] != '') {
	
	if (!($stmt = $mysqli->prepare("update message set wall_name = ? where wall_id = ?"))) {
		$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		exit (json_encode($ret));	
		
	}

	if (!$stmt->bind_param("si",  $_POST['name'],$wall_id)) {
		$ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

	if (!$stmt->execute()) {
		$ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

	$stmt->close();

	
}


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

//$stmt->store_result();

$meta = $stmt->result_metadata();

while ($column = $meta->fetch_field()) {
   $bindVarsArray[] = &$result[$column->name];
}        
call_user_func_array(array($stmt, 'bind_result'), $bindVarsArray);

$stmt->fetch();

$stmt->close();

$result['favourate_count'] = get_wallfavourate_count($result['wall_id']);
$result['message_count'] = get_wallmsg_count($result['wall_id']);

$mysqli->close();


$ret['status'] = 1;
$ret['ErrorMsg'] = '';
$ret['wall'] = $result;

exit (json_encode($ret));