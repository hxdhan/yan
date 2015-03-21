<?php
include ('../../header.php');

if(!isset($_POST['type'])) {
	$ret['ErrorMsg'] = '类型参数错误';
	exit (json_encode($ret));
}

$type = 0;

if(isset($_POST['type']) && $_POST['type'] != '') {
	$type = $_POST['type'] + 0;
}

if($type === 1) {
	//qq token register
	//$ret['ErrorMsg'] = '对不起，暂时不支持QQ注册';
	//exit (json_encode($ret));
	if(empty($_POST['qqtoken']) || empty($_POST['nickname']) ) {
		$ret['ErrorMsg'] = '参数错误';
		exit (json_encode($ret));
	}
		$cellphone = $def_phone;
		$password = md5($_POST['qqtoken'] . $pwd_salt);
		$nickname = $_POST['nickname'];
		$qq_token = $_POST['qqtoken'];
		$wx_token = '';
}
elseif($type === 0) {
	// cell phone register
	
	if(empty($_POST['cellphone']) || empty($_POST['password']) || empty($_POST['nickname']) ) {
		$ret['ErrorMsg'] = '参数错误';
		exit (json_encode($ret));
		
	}
	if (!filter_var($_POST['cellphone'], FILTER_VALIDATE_REGEXP,array("options"=>array("regexp"=>"/^\+\d{2}-1[3|4|5|8][0-9]\d{8}$/")))) {
    	$ret['ErrorMsg'] = '电话号码格式错误';
			exit (json_encode($ret));
		} 
		
		$cellphone = $_POST['cellphone'];
		$password = md5($_POST['password'] . $pwd_salt);
		$nickname = $_POST['nickname'];
		$qq_token = md5($_POST['cellphone']);
		$wx_token = '';
}

elseif($type === 2 ) {
		if(empty($_POST['wx_token']) || empty($_POST['nickname']) ) {
		$ret['ErrorMsg'] = '参数错误';
		exit (json_encode($ret));
	}
	
		$cellphone = $def_phone;
		$password = md5($_POST['wx_token'] . $pwd_salt);
		$nickname = $_POST['nickname'];
		$qq_token = md5($_POST['wx_token']);
		$wx_token = $_POST['wx_token'];
}

else {
	$ret['ErrorMsg'] = '类型参数错误';
	exit (json_encode($ret));
}

$qqid = $def_qqid;

if(isset($_POST['qqid']) && $_POST['qqid'] != '') {
	$qqid = $_POST['qqid'];
}

$birthday = 0;

if(isset($_POST['birthday'])) {
	if (!filter_var($_POST['birthday'], FILTER_VALIDATE_REGEXP,array("options"=>array("regexp"=>"/^\d{4}-\d{1,2}-\d{1,2}$/")))) {
    $ret['ErrorMsg'] = '生日格式错误';
		exit (json_encode($ret));
	} 
}

$birthday = strtotime($_POST['birthday']);

$gender = 'M';

$gender_check = array('M','F');

if(isset($_POST['gender'])) {
	$gender = $_POST['gender'];

	if(! in_array($gender, $gender_check)) {
		$ret['ErrorMsg'] = '参数错误';
		exit (json_encode($ret));
	}
}

$description = '';

if(isset($_POST['description']) &&  $_POST['description'] != '') {
	$description = $_POST['description'];
}

$photo_color = null;

if(isset($_POST['photo_color']) &&  $_POST['photo_color'] != '') {
	$photo_color = $_POST['photo_color'];
}

$image_url = '';
if(isset($_FILES['photo'])) {
	
	 $date_name = date('Ymd');
	// $file_path = "../../static/".$date_name;
	// if(!file_exists($file_path)) {
		// mkdir($file_path);
		
	// }
	
	$file_path = check_path();
	
	$file_url = $static_path.$date_name.'/';
	
	$allowed_image = array('jpg' => 'image/jpeg','png' => 'image/png','gif' => 'image/gif');
	
	$finfo = new finfo(FILEINFO_MIME_TYPE);

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
	
}


if(!($stmt = $mysqli->prepare("SELECT user_id FROM user WHERE  qq_token = ? "))) {
	
  $ret['ErrorMsg'] = "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
  exit (json_encode($ret));	
}

if(!($stmt->bind_param("s",$qq_token))) {
  $ret['ErrorMsg'] = "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
  exit (json_encode($ret));
}

if (!$stmt->execute()) {
  $ret['ErrorMsg'] = "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
  exit (json_encode($ret));
}

$stmt->store_result();

if($stmt->num_rows > 0) {
	$ret['ErrorMsg'] = '您已经注册过了！';
	exit (json_encode($ret));
}

$mysqli->autocommit(FALSE);

if (!($stmt = $mysqli->prepare("INSERT INTO user(qq_token, wx_token, qq_id, cellphone, password, `type`) VALUES (?,?,?,?,?,?)"))) {
	
	$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	exit (json_encode($ret));	
		
}

if (!$stmt->bind_param("sssssi", $qq_token,$wx_token,$qqid,$cellphone,$password,$type)) {
  $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
	exit (json_encode($ret));
}

if (!$stmt->execute()) {
  $ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
	exit (json_encode($ret));
}

$user_id = $mysqli->insert_id;

$time = time();

if (!($stmt = $mysqli->prepare("INSERT INTO userinfo(user_id, photo_url, photo_color,nickname, gender, birthday, description,reg_time) VALUES (?,?,?,?,?,?,?,?)"))) {
	$mysqli->rollback();
	$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	exit (json_encode($ret));	
		
}

if (!$stmt->bind_param("sssssisi", $user_id, $image_url, $photo_color, $nickname, $gender,$birthday,$description,$time)) {
	$mysqli->rollback();
  $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
	exit (json_encode($ret));
}

if (!$stmt->execute()) {
	$mysqli->rollback();
  $ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
	exit (json_encode($ret));
}

$master = 1;

if (!($stmt = $mysqli->prepare("INSERT INTO userfollow (user_id, follow_userid) VALUES (?,?)"))) {
	$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	exit (json_encode($ret));	
		
}

if (!$stmt->bind_param("ii", $user_id, $master)) {
  $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
	exit (json_encode($ret));
}

if (!$stmt->execute()) {
  $ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
	exit (json_encode($ret));
}

if (!$mysqli->commit()) {
	$mysqli->rollback();
	$ret['ErrorMsg'] =  "Transaction commit failed\n";
	exit (json_encode($ret));
}

$stmt->close();
$mysqli->autocommit(TRUE);
if(empty($nickname)) {
	$content = "谢天谢地你来了！从此你可以在“身边”的专栏里撒野发贴儿，还可以任性地“收藏”感兴趣的专栏。惊喜还在后头，很快你就能开辟自己的专栏了！我还会悄悄告诉你总和你擦肩而过的Ta！";
}
else {
	$content = $nickname."，谢天谢地你来了！从此你可以在“身边”的专栏里撒野发贴儿，还可以任性地“收藏”感兴趣的专栏。惊喜还在后头，很快你就能开辟自己的专栏了！我还会悄悄告诉你总和你擦肩而过的Ta！";
}
tieer_to_user ($user_id, $content);

$mysqli->close();

$ret['status'] = 1;
$ret['ErrorMsg'] = '';
$ret['user_id'] = $user_id;
 
exit (json_encode($ret));