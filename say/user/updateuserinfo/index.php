<?php
include ('../../header.php');

if(!check_login()) {
	$ret['ErrorMsg'] = '没有登录';
	exit (json_encode($ret));
}

$user_id = $user['user_id'];

//echo $user_id;

$par_type = '';
$param = array(&$par_type);
$sql_array = array();
if(isset($_POST['nickname'])) {
	$sql_array[] = "nickname = ?";
	$par_type .= 's';
	$param[] = &$_POST['nickname'];

}

$gender_check = array('M','F');

if(isset($_POST['gender'])) {
	$gender = $_POST['gender'];

	if(! in_array($gender, $gender_check)) {
		$ret['ErrorMsg'] = '参数错误';
		exit (json_encode($ret));
	}
	
	$sql_array[] ='gender = ?';
  $par_type .= 's';
  $param[] = &$_POST['gender'];
}

if(isset($_POST['description'])) {
	$sql_array[] = "description = ?";
	$par_type .= 's';
	$param[] = &$_POST['description'];

}

if(isset($_POST['expert_type']) && intval($_POST['expert_type']) > 0 ) {
	$sql_array[] = "expert_type = ?";
	$par_type .= 'i';
	$param[] = &$_POST['expert_type'];
}

if(isset($_POST['birthday'])) {
	if (!filter_var($_POST['birthday'], FILTER_VALIDATE_REGEXP,array("options"=>array("regexp"=>"/^\d{4}-\d{1,2}-\d{1,2}$/")))) {
    	$ret['ErrorMsg'] = '生日格式错误';
			exit (json_encode($ret));
	}
	$birthday = strtotime($_POST['birthday']);
	$sql_array[] = "birthday = ?";
	$par_type .= 'i';
	$param[] = &$birthday;

}

if(isset($_FILES['photo'])) {
	$date_name = date('Ymd');
	$file_path = "../../static/".$date_name;
	if(!file_exists($file_path)) {
	mkdir($file_path);
	
	}

	$file_url = $static_path.$date_name.'/';

	$finfo = new finfo(FILEINFO_MIME_TYPE);
	$allowed_image = array('jpg' => 'image/jpeg','png' => 'image/png','gif' => 'image/gif');

	$image_url = '';
	
	$ext = array_search($finfo->file($_FILES['photo']['tmp_name']),$allowed_image);

	if($ext === false) {
		$ret['ErrorMsg'] = '图片格式错误';
		exit (json_encode($ret));
		
	}
	
	$prefix_name = microtime(true) * 10000;
	$file_name = $prefix_name .'.'.$ext;
	$small_file_name = $prefix_name .'_small.'.$ext;
	$file_path_name = $file_path."/".$file_name;
	$small_file_path_name = $file_path."/".$small_file_name;
	rename($_FILES['photo']['tmp_name'],$file_path_name);
	half_image($file_path_name, $small_file_path_name);
	$image_url = $file_url . $file_name;

	
  $sql_array[] = 'photo_url = ?';
	$par_type .= 's';
	$param[] = &$image_url;
	
}

$sql = "UPDATE userinfo SET " . implode(",",$sql_array) . " where user_id = ?";
$par_type .= 'i';
$param[] = &$user_id;

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

if(isset($_POST['expert_type']) && intval($_POST['expert_type']) > 0 ) {
	if($mysqli->query("update usrexpert_type set count = count + 1 where expert_id = " . intval($_POST['expert_type']))) {
	
	}	
}


if($get_user = $mysqli->query("select * from userinfo where user_id = $user_id")) {
	$usr = $get_user->fetch_assoc();
	//var_dump($usr);
}	
	
$memcache = memcache_connect($mem_host, $mem_port);
memcache_set($memcache, $_POST['login_token'], json_encode($usr), 0, 60*60*24);

$mysqli->close();


$ret['status'] = 1;
$ret['ErrorMsg'] = '';
$ret['user'] = $usr;

exit (json_encode($ret,JSON_UNESCAPED_UNICODE));