<?php
header("Content-type: text/html; charset=utf-8");
header("Cache-Control: no-cache"); 

$db_host = 'localhost';
$db_name = 'say';
$db_user = 'root';
$db_pwd = 'root';
$salt = 'say123';
$mem_host = 'localhost';
$mem_port = 11211;

$max_follow_count = 100;

$ret = array();
$ret['status'] = 0;

$host = 'http://121.199.36.8';

$target = "http://42.121.81.183:18002/send.do";

$push_url = 'http://api.jpush.cn:8800/v2/push/';

$mast_secret = '2a0aef65eb700d8eb6bec39b';
$app_key = '336e00d6925644d3b2566b45';
$receive_type = 5;
$msg_type = 1;
$platform = 'android,ios';
$apns_production = 0;

$pwd_salt = '1bu2bu';

$def_phone = '+86-99999999999';
$def_qqid = '000000';



//default login token , if this token is send , login ok .


$static_path = '/say/static/';

//version info , the first version is 1.0 , this version modified: add comment type, 
//1.0 version, just voice comment, 
//1.1 add text comment and image comment
 
$version = '1.7';

$start_verson = '1.0';

$noti_type = array('fol'=>'follow', 'com'=>'comment', 'rep'=>'reply', 'like'=>'like', 'chat'=>'chat', 'enc'=>'encounter', 'invi'=>'invite' );

//connect mysql db 
$mysqli = new mysqli($db_host, $db_user, $db_pwd, $db_name);

if ($mysqli->connect_errno) {
	$ret['ErrorMsg'] = "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
	exit (json_encode($ret));	
}

if (!$mysqli->set_charset("utf8")) {
	$ret['ErrorMsg'] = "Error loading character set utf8". $mysqli->error;
	exit (json_encode($ret));	
}
	


function check_login() {

	global $user, $mem_host, $mem_port;
	
	if(isset($_POST['login_token'])) {
		$token = $_POST['login_token'];
	}
	
  if(empty($token)) {
		return false;
	}
	
	//default user 
	$def_token = '1852689bc9090133494bc638ea528491';
	if($token === $def_token) {
		$user = array();
		$user['user_id'] = 0;
		$user['nickname'] = 'anonymous';
		return true;
	}

	$memcache = memcache_connect($mem_host, $mem_port);
	
	$user = json_decode(memcache_get($memcache,$token),true);
	memcache_close($memcache);
 	if(empty($user)) {
		return false;
	}
	return true;
}

function get_push_id() {
	global $mem_host,$mem_port,$mysqli;

	$memcache = memcache_connect($mem_host, $mem_port);
	if($push_id = memcache_increment($memcache, 'push_id')) {
		
		if($ret = $mysqli->query("update push_sequence set push_id = $push_id")) {
		
		}
		
		return $push_id;
	
	}
	else {
		if($get_id = $mysqli->query("select push_id from push_sequence") ) {
			$push_id = $get_id->fetch_assoc()['push_id'] + 1 ;
			
			memcache_set($memcache,'push_id',$push_id);
			
			return $push_id;
			
		}
	}
}

function curl_post($curlPost,$url){
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_NOBODY, true);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $curlPost);
		$return_str = curl_exec($curl);
		curl_close($curl);
		return $return_str;
}

function check_path () {
	$date_name = date('Ymd');
	$curr_path = __DIR__;
	$file_path = $curr_path.'/static/'.$date_name;
	
	if(!file_exists($file_path)) {
		mkdir($file_path);
		
	}
	
	return $file_path;
}

function half_image($from_path, $to_path) {
	$percent = 0.5;
	list($width, $height) = getimagesize($from_path);
	$newwidth = $width * $percent;
	$newheight = $height * $percent;
	$thumb = imagecreatetruecolor($newwidth, $newheight);
	$source = imagecreatefromjpeg($from_path);
	imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
	imagejpeg($thumb,$to_path);
}

function get_my_follow_count ($user_id) {
	global $mysqli;
	
	if (!($stmt = $mysqli->prepare("SELECT count(*) FROM userfollow WHERE user_id = ? "))) {
		$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		exit (json_encode($ret));	
			
	}

	if (!$stmt->bind_param("i", $user_id)) {
	  $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

	if (!$stmt->execute()) {
	  $ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

	$stmt->bind_result($follow_user_count);

	$stmt->fetch();

	$stmt->close();
	
	return $follow_user_count;
	
}

function get_fan_count ($user_id) {
	global $mysqli;
	
	if (!($stmt = $mysqli->prepare("SELECT count(*) FROM userfollow WHERE follow_userid = ? "))) {
		$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		exit (json_encode($ret));	
			
	}

	if (!$stmt->bind_param("i", $user_id)) {
	  $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

	if (!$stmt->execute()) {
	  $ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

	$stmt->bind_result($fan_count);

	$stmt->fetch();

	$stmt->close();
	
	return $fan_count;

}

function get_friend_count($user_id) {
	global $mysqli;
	
	if (!($stmt = $mysqli->prepare("SELECT count(*) FROM usrfriend WHERE user_id = ? OR friend_userid = ? "))) {
		$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		exit (json_encode($ret));	
			
	}

	if (!$stmt->bind_param("ii", $user_id,$user_id)) {
	  $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

	if (!$stmt->execute()) {
	  $ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

	$stmt->bind_result($friend_count);

	$stmt->fetch();

	$stmt->close();
	
	return $friend_count;
}

function is_my_fun($myid, $userid) {
	global $mysqli;
	if (!($stmt = $mysqli->prepare("SELECT count(*) FROM userfollow USE INDEX(follow_userid) WHERE follow_userid = ? and user_id = ? "))) {
		$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		exit (json_encode($ret));	
			
	}

	if (!$stmt->bind_param("ii", $myid,$userid)) {
	  $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

	if (!$stmt->execute()) {
	  $ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

	$stmt->bind_result($is_my_fan_user);

	$stmt->fetch();

	$stmt->close();
	
	return $is_my_fan_user;
	
}

function is_my_follow_user($myid, $user_id) {
	global $mysqli;
	
	if (!($stmt = $mysqli->prepare("SELECT count(*) FROM userfollow USE INDEX(user_id) WHERE user_id = ? and follow_userid = ? "))) {
		$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		exit (json_encode($ret));	
			
	}

if (!$stmt->bind_param("ii", $myid,$userid)) {
	  $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

if (!$stmt->execute()) {
	  $ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

$stmt->bind_result($is_my_follow_user);

$stmt->fetch();

$stmt->close();

}

function get_like_status($message_id, $user_id) {
	global $mysqli;
	if (!($stmt = $mysqli->prepare(" SELECT count(*) FROM  `like`  WHERE message_id = ? and like_userid = ? "))) {
		$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		exit (json_encode($ret));	
		
	}

	if (!$stmt->bind_param("ii", $message_id, $user_id)) {
		$ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

	if (!$stmt->execute()) {
		$ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

	$stmt->bind_result($like);
	$stmt->fetch();
	$stmt->close();

	if(empty($like)) {
		$like = 0;
	}
	
	return $like;

}

function get_userinfo($user_id) {
	global $mysqli;
	
	if (!($stmt = $mysqli->prepare("SELECT nickname,photo_url,photo_color,gender,birthday,description,expert_type FROM userinfo WHERE user_id = ?"))) {
	$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	exit (json_encode($ret));	
	
	}
	if (!$stmt->bind_param("i", $user_id)) {
		$ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}
			
	if (!$stmt->execute()) {
		$ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}
	
	$stmt->bind_result($nickname,$photo_url,$photo_color,$gender,$birthday,$description,$expert_type);
	
	$ele = array();
	while($stmt->fetch()) {
		$ele['nickname'] = $nickname;
		$ele['photo_url'] = $photo_url;
		$ele['photo_color'] = $photo_color;
		$ele['gender'] = $gender;
		$ele['birthday'] = $birthday;
		$ele['description'] = $description;
		$ele['expert_type'] = $expert_type;
	}
	$stmt->close();
	return $ele;
	

}

function time_elapsed_string($ptime) {
    $etime = time() - $ptime;

    if ($etime < 1)
    {
        return '0秒';
    }

    $a = array( 365 * 24 * 60 * 60  =>  'year',
                 30 * 24 * 60 * 60  =>  'month',
                      24 * 60 * 60  =>  'day',
                           60 * 60  =>  'hour',
                                60  =>  'minute',
                                 1  =>  'second'
                );
    $a_plural = array( 'year'   => '年',
                       'month'  => '个月',
                       'day'    => '天',
                       'hour'   => '小时',
                       'minute' => '分钟',
                       'second' => '秒'
                );

    foreach ($a as $secs => $str)
    {
        $d = $etime / $secs;
        if ($d >= 1)
        {
            $r = round($d);
            return $r . ($r >= 1 ? $a_plural[$str] : $str) . '前';
        }
    }
}

function my_truncate($str,$len) {
	if(mb_strlen($str) == mb_strlen($str,'utf-8')) {
		if(mb_strlen($str) > $len * 3) {
			return mb_substr($str,0,$len * 3) . '...';
		}
		else {
			return $str;
		}
	}
	else {
		if(mb_strlen($st,'utf-8') < $len) {
			return mb_substr($str,0,$len,'utf-8').'...';
		}
		else {
			return $str;
		}
	}
}

function get_wallmsg_count($wall_id) {
	global $mysqli;
	
	if (!($stmt = $mysqli->prepare("SELECT count(*) FROM message WHERE wall_id = ? "))) {
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

	$stmt->bind_result($count);

	$stmt->fetch();

	$stmt->close();
	
	return $count;
}

function get_wallfavourate_count($wall_id) {
	global $mysqli;
	
	if (!($stmt = $mysqli->prepare("SELECT count(*) FROM msgwallfavourates WHERE wall_id = ? "))) {
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

	$stmt->bind_result($count);

	$stmt->fetch();

	$stmt->close();
	
	return $count;
}

function get_grade () {
	global $mem_host,$mem_port,$mysqli;

	$memcache = memcache_connect($mem_host, $mem_port);
	if($grade =  memcache_get($memcache, 'grade')) {
		return $grade;
	}
	else {
		if($get_grade = $mysqli->query("select * from usrgradepoint order by grade")) {
			$grade = array();
			while($g = $get_grade->fetch_row()) {
				$grade[$g[0]] = $g[1];
			}
		}
		else {
			printf("%s",$mysqli->error);
		}	
		memcache_set($memcache, 'grade', $grade);
		return $grade;
	}

}

function update_user_point ($user, $point) {
	global $noti_type, $mem_host, $mem_port, $mysqli, $app_key, $receive_type, $mast_secret, $msg_type, $platform, $apns_production, $push_url;
	
	$grade = get_grade();
	if($get_userpoint = $mysqli->query("select grade, point from userinfo where user_id = " . $user)) {
		$user_point = $get_userpoint->fetch_assoc();
		$u_grade = $user_point['grade'];
		$u_point = $user_point['point'];
	}
	else {
		printf("%s",$mysqli->error);
	}
	
	if($u_point + $point >= $grade[$u_grade]) {
		//upgrade
	
		if($mysqli->query("update userinfo set grade = grade + 1 , point = ". ($u_point + $point - $grade[$u_grade]) . " where user_id = {$user}")) {
		
		}
		else {
				printf("%s",$mysqli->error);
		}
		//send chat
		$user_id = 1;
		$receive_userid = $user;
		$longitude = 116.339889;
		$latitude = 40.029367;
		$content = "恭喜您，您的等级提升到" . ($u_grade + 1) . "级了";
		$duration = 0;
		$content_type = 0;
		$time = time();
		if (!($stmt = $mysqli->prepare("INSERT INTO  usrchat (user_id,receive_userid, longitude,latitude,chat_content,duration, content_type, time) values (?,?,?,?,?,?,?,?) "))) {
			$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
			exit (json_encode($ret));	
			
		}

		if (!$stmt->bind_param("iiddsiii", $user_id, $receive_userid,$longitude, $latitude, $content,$duration,$content_type,$time)) {
			$ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
			exit (json_encode($ret));
		}

		if (!$stmt->execute()) {
			$ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
			exit (json_encode($ret));
		}
		$stmt->close();
		//notifaction
		$n_type = $noti_type['chat'];

		if($rets = $mysqli->query("SELECT * FROM usrnotification WHERE user_id = $receive_userid AND active_userid = $user_id AND type = '$n_type' ")) {
			if($rets->num_rows > 0) {
				if(!$mysqli->query("DELETE FROM usrnotification WHERE user_id = $receive_userid AND active_userid = $user_id AND type = '$n_type' ")) {
					printf("Error: %s\n", $mysqli->error);
				}
				
			}
		}
		else {
			printf("Error: %s\n", $mysqli->error);
		}
			
		if(!$mysqli->query("INSERT INTO usrnotification (user_id, active_userid, type, time) VALUES ($receive_userid, $user_id, '$n_type', $time)")) {
			printf("Error: %s\n", $mysqli->error);
		}
		
		//send push 
		if($get_nickname = $mysqli->query("SELECT nickname FROM userinfo WHERE user_id = $user_id")) {
			$nickname = $get_nickname->fetch_assoc()['nickname'];
		}

		if($get_registration = $mysqli->query("SELECT push_registration FROM user WHERE user_id = $receive_userid")) {
			$receive_value = $get_registration->fetch_assoc()['push_registration'];
		}
		
		if(!empty($receive_value)) {
			$data = '';
			$send_no = get_push_id();

			$data.= 'sendno='.$send_no;

			$data.= '&app_key='.$app_key;
			$data.= '&receiver_type='.$receive_type;
			$data.= '&receiver_value='.$receive_value;

			$verification_code = $send_no.$receive_type.$receive_value.$mast_secret;



			$data.='&verification_code='.md5($verification_code);
			$data.='&msg_type='.$msg_type;
  	
			$c['n_content'] = $nickname.':'.$content;
		
			$c["n_extras"] = array('ios'=>array('badge'=>1,'sound'=>'drop.caf','content-available'=>1),'type'=>'chat','user_param_1'=>$user_id);
			$data.='&msg_content='.json_encode($c);
			$data.='&platform='.$platform;
			$data.='&apns_production='.$apns_production;

			$ch = curl_init();

			curl_setopt($ch,CURLOPT_URL,$push_url);
			curl_setopt($ch,CURLOPT_POST,1);

			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			//$response = curl_exec($ch);
			//echo $response;
			curl_exec($ch);

}
		
	}
	else {
		//add point
			if($mysqli->query("update userinfo set point = point + {$point} where user_id = {$user}")) {
		
		}
		else {
				printf("%s",$mysqli->error);
		}
	}
	
	//$memcache = memcache_connect($mem_host, $mem_port);
	
	
}
 