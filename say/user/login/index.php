<?php
include ('../../header.php');

if(!isset($_POST['type']) ) {
  $ret['ErrorMsg'] = '参数错误';
	exit (json_encode($ret));
}

$type = $_POST['type'] + 0;

if($type === 0 ) {
	//cellphone 登录
	if(empty($_POST['cellphone']) || empty($_POST['password']) ) {
		$ret['ErrorMsg'] = '参数错误';
		exit (json_encode($ret));
	}

	$cellphone = $_POST['cellphone'];
	//echo $pwd_salt;
	$password = md5($_POST['password'] . $pwd_salt);
	//echo $password;
	$login_token = md5($_POST['cellphone']);

	if(!($stmt = $mysqli->prepare("SELECT u.user_id, u.push_registration,ui.nickname,ui.photo_url,ui.photo_color,ui.gender,ui.birthday,ui.description,ui.expert_type,u.type,u.qq_token,u.cellphone,u.wx_token FROM user u, userinfo ui WHERE u.user_id = ui.user_id and  u.cellphone = ? and u.password = ? "))) {
		$ret['ErrorMsg'] = "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		exit (json_encode($ret));	
	}

	if(!($stmt->bind_param("ss",$cellphone, $password))) {
	  $ret['ErrorMsg'] = "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
	  exit (json_encode($ret));
	}
}
elseif($type === 1) {
	//qq token 登录
	//$ret['ErrorMsg'] = '对不起，暂时不支持QQ登录';
	//exit (json_encode($ret));
	if(empty($_POST['qqtoken']) ) {
		$ret['ErrorMsg'] = '参数错误';
		exit (json_encode($ret));
	}
	$qq_token = $_POST['qqtoken'];

	if(!($stmt = $mysqli->prepare("SELECT u.user_id, u.push_registration,ui.nickname,ui.photo_url,ui.photo_color,ui.gender,ui.birthday,ui.description,ui.expert_type,u.type,u.qq_token,u.cellphone,u.wx_token FROM user u, userinfo ui WHERE u.user_id = ui.user_id and  u.qq_token = ? "))) {
		$ret['ErrorMsg'] = "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		exit (json_encode($ret));	
	}

	if(!($stmt->bind_param("s",$qq_token))) {
	  $ret['ErrorMsg'] = "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
	  exit (json_encode($ret));
	}

	$login_token = $qq_token;
}
elseif($type === 2) {
	//qq token 登录
	//$ret['ErrorMsg'] = '对不起，暂时不支持QQ登录';
	//exit (json_encode($ret));
	if(empty($_POST['wx_token']) ) {
		$ret['ErrorMsg'] = '参数错误';
		exit (json_encode($ret));
	}
	$wx_token = $_POST['wx_token'];

	if(!($stmt = $mysqli->prepare("SELECT u.user_id, u.push_registration,ui.nickname,ui.photo_url,ui.photo_color,ui.gender,ui.birthday,ui.description,ui.expert_type,u.type,u.qq_token,u.cellphone,u.wx_token FROM user u, userinfo ui WHERE u.user_id = ui.user_id and  u.wx_token = ? "))) {
		$ret['ErrorMsg'] = "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		exit (json_encode($ret));	
	}

	if(!($stmt->bind_param("s",$wx_token))) {
	  $ret['ErrorMsg'] = "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
	  exit (json_encode($ret));
	}

	$login_token = $wx_token;
}

if (!$stmt->execute()) {
	  $ret['ErrorMsg'] = "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
	  exit (json_encode($ret));
}


$stmt->store_result();

if($stmt->num_rows < 1) {
	if($type === 0 ) {
		$ret['ErrorMsg'] = '手机号或密码错误';
	}elseif($type === 1) {
		$ret['ErrorMsg'] = '没有注册';
	}
	elseif($type === 2) {
		$ret['ErrorMsg'] = '没有注册';
	}
	exit (json_encode($ret));
}

$meta = $stmt->result_metadata();

while ($column = $meta->fetch_field()) {
   $bindVarsArray[] = &$result[$column->name];
}        
call_user_func_array(array($stmt, 'bind_result'), $bindVarsArray);

//while ($stmt->fetch()) {

//	var_dump($result);
//}

$stmt->fetch();
//var_dump($result);
//var_dump(json_encode($result));

//if(empty($result)) {
//	$ret['ErrorMsg'] = '登录失败';
//	exit (json_encode($ret));
//}

$has_qqtoken = 0;
$has_cellphone = 0;
$has_wxtoken = 0;

$u_qqtoken = $result['qq_token'];
$u_cellphone = $result['cellphone'];

unset($result['qq_token']);
unset($result['cellphone']);

//if qq_token does not empty and qq_token does not equal default qq_token
if(!empty($u_qqtoken) and $u_qqtoken !== md5($u_cellphone) and $u_qqtoken !== md5($result['wx_token'])) {
	$has_qqtoken = 1;
}

//if cellphone does not empty and cellphone does not equal default cellphone
if(!empty($u_cellphone) and $u_cellphone !== $def_phone) {
	$has_cellphone = 1;
}

if(!empty($result['wx_token'])) {
	$has_wxtoken = 1;
	unset($result['wx_token']);
}


$result['has_qqtoken'] = $has_qqtoken;
$result['has_cellphone'] = $has_cellphone;
$result['has_wxtoken'] = $has_wxtoken;

$memcache = memcache_connect($mem_host, $mem_port);
memcache_set($memcache, $login_token, json_encode($result), 0, 60*2);

$stmt->close();

$user_id = $result['user_id'];
$time = time();

//update user status
if(!($stmt = $mysqli->prepare("UPDATE userinfo SET last_login_time = ? WHERE user_id = ? "))) {
		$ret['ErrorMsg'] = "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		exit (json_encode($ret));	
}

if(!($stmt->bind_param("ii",$time,$user_id))) {
	  $ret['ErrorMsg'] = "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
	  exit (json_encode($ret));
}

if (!$stmt->execute()) {
	  $ret['ErrorMsg'] = "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
	  exit (json_encode($ret));
}

$stmt->close();

$mysqli->close();

$ret['status'] = 1;
$ret['ErrorMsg'] = '';
$ret['login_token'] = $login_token;
$ret['user'] = $result;
exit (json_encode($ret,JSON_UNESCAPED_UNICODE));