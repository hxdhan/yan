<?php
include ('../../header.php');

if(!check_login()) {
	$ret['ErrorMsg'] = '没有登录';
	exit (json_encode($ret));
}

$user_id = $user['user_id'];

if(empty($_POST['category_id']) || empty($_POST['longitude']) || empty($_POST['latitude']) ) {
	$ret['ErrorMsg'] = '参数错误';
	exit (json_encode($ret));
}

$duration = 0;

if(isset($_POST['duration']) && intval($_POST['duration']) > 0 ) {
	$duration = $_POST['duration'] + 0 ;
}

$text = '';

$category_id = $_POST['category_id'];
$longitude = $_POST['longitude'];
$latitude = $_POST['latitude'];
if(isset($_POST['text']) && $_POST['text'] != '') {
	$text = $_POST['text'];

}

$platform = '';
if(isset($_POST['platform']) && $_POST['platform'] != '') {
	$platform = $_POST['platform'];
}


$time = time();

$date_name = date('Ymd');
$file_path = "../../static/".$date_name;
if(!file_exists($file_path)) {
	mkdir($file_path);
	
}

$file_url = $static_path.$date_name.'/';

$alowed_voice = array('amr'=>'application/octet-stream','mp3'=>'audio/mpeg');
$finfo = new finfo(FILEINFO_MIME_TYPE);

$voice_url = '';

if(isset($_FILES['voice'])) {
	
	$ext = array_search($finfo->file($_FILES['voice']['tmp_name']),$alowed_voice);
	if($ext === false) {
		$ret['ErrorMsg'] = '音频格式错误';
		exit (json_encode($ret));
		
	}

	$file_name = microtime(true) * 10000 .'.'.$ext;
	$file_path_name = $file_path."/".$file_name;
	rename($_FILES['voice']['tmp_name'],$file_path_name);
	
	$voice_url = $file_url . $file_name;

}

$allowed_image = array('jpg' => 'image/jpeg','png' => 'image/png','gif' => 'image/gif');

$image_url = '';

if(isset($_FILES['image'])) {
	
	$ext = array_search($finfo->file($_FILES['image']['tmp_name']),$allowed_image);

	if($ext === false) {
		$ret['ErrorMsg'] = '图片格式错误';
		exit (json_encode($ret));
		
	}
	$prefix_name = microtime(true) * 10000;
	$file_name = $prefix_name .'.'.$ext;
	$small_file_name = $prefix_name .'_small.'.$ext;
	$file_path_name = $file_path."/".$file_name;
	$small_file_path_name = $file_path."/".$small_file_name;
	rename($_FILES['image']['tmp_name'],$file_path_name);
	half_image($file_path_name, $small_file_path_name);
	$image_url = $file_url . $file_name;
}

$smile_id = 0;
if(isset($_POST['smile_id'])) {
	$smile_id = $_POST['smile_id'];
}
$orig_message_id = 0;
if(isset($_POST['orig_message_id'])) {
	$orig_message_id = $_POST['orig_message_id'];
}

$image_color = null;

if(isset($_POST['image_color']) &&  $_POST['image_color'] != '') {
	$image_color = $_POST['image_color'];
}

$wall_id = 0;
if(isset($_POST['wall_id']) &&  $_POST['wall_id'] != '') {
	$wall_id = $_POST['wall_id'] + 0 ;
}

$wall_name = '';

if($wall_id > 0) {
	if (!($stmt = $mysqli->prepare("select name from msgwall where wall_id = ?"))) {
		$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		exit (json_encode($ret));	
		
	}
	
	if (!$stmt->bind_param("i", $wall_id)) {
		$ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}
	
	if (!$stmt->execute()) {
		$ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}
	$stmt->bind_result($wall_name);
	$stmt->fetch();
	$stmt->close();
}

if (!($stmt = $mysqli->prepare("INSERT INTO message (author_id, category_id, voice_url, duration, longitude, latitude, time, smile_id, original_message_id, image_url, image_color, text, new_time, platform,wall_id,wall_name) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)"))) {
	$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	exit (json_encode($ret));	
		
}

if (!$stmt->bind_param("iisiddiiisssisis", $user_id, $category_id, $voice_url, $duration, $longitude, $latitude, $time, $smile_id, $orig_message_id, $image_url, $image_color, $text, $time, $platform, $wall_id, $wall_name)) {
  $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
	exit (json_encode($ret));
}

if (!$stmt->execute()) {
  $ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
	exit (json_encode($ret));
}

$stmt->close();

$message_id = $mysqli->insert_id;

if (!($stmt = $mysqli->prepare("update category set message_count = message_count + 1 where category_id = ? "))) {
	$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	exit (json_encode($ret));	
		
}

if (!$stmt->bind_param("i", $category_id)) {
  $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
	exit (json_encode($ret));
}

if (!$stmt->execute()) {
  $ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
	exit (json_encode($ret));
}

$stmt->close();

//update last message time
if (!($stmt = $mysqli->prepare("update userinfo set last_message_time = ? where user_id = ?"))) {
	$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	exit (json_encode($ret));	
		
}

if (!$stmt->bind_param("ii", $time, $user_id)) {
  $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
	exit (json_encode($ret));
}

if (!$stmt->execute()) {
  $ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
	exit (json_encode($ret));
}

$stmt->close();

//update user score
update_user_point($user_id, 3);


if (!($stmt = $mysqli->prepare("SELECT m.*,u.* FROM message m,userinfo u  WHERE m.author_id = u.user_id and message_id = ?"))) {
	$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	exit (json_encode($ret));	
		
}

if (!$stmt->bind_param("i", $message_id)) {
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
$mysqli->close();

//$r = array();

//foreach ($result as $key => $value) {
  //      $r[$key] = $value;
    //}

//var_dump($r);



	
	//$ret = array();
$ret['status'] = 1;
$ret['ErrorMsg'] = '';
$ret['message'] = $result;


exit (json_encode($ret));
  
 
?>