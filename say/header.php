<?php
header("Content-type: text/html; charset=utf-8");
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
$db_host = 'localhost';
$db_name = 'say';
$db_user = 'root';
$db_pwd = 'root';
$salt = 'say123';
$mem_host = 'localhost';
$mem_port = 11211;

$admin_key = 'tieer';

$max_follow_count = 100;

$ret = array();
$ret['status'] = 0;

$host = 'http://121.199.36.8';

$target = "http://42.121.81.183:18002/send.do";

$push_url = 'https://api.jpush.cn/v3/push';

$mast_secret = '2a0aef65eb700d8eb6bec39b';
$app_key = '336e00d6925644d3b2566b45';

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

$noti_type = array('fol'=>'follow', 'com'=>'comment', 'rep'=>'reply', 'like'=>'like', 'chat'=>'chat', 'enc'=>'encounter', 'invi'=>'invite', 'wall_new' => 'wallnew', 'fav_wall_new' =>'favwallnew', 'post' => 'post' );

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

/**
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
**/
function curl_post($curlPost,$url){
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_NOBODY, true);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $curlPost);
		$mh = curl_multi_init();
		curl_multi_add_handle($mh,$curl);
		$running = 'idc';
		//get response???
		curl_multi_exec($mh,$running);
		curl_multi_remove_handle($mh, $curl);
		curl_multi_close($mh);
		//close??
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
	global $ret, $mysqli;
	
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
	global $ret, $mysqli;
	
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
	global $ret, $mysqli;
	
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
	global $ret, $mysqli;
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
	global $ret, $mysqli;
	
	if (!($stmt = $mysqli->prepare("SELECT count(*) FROM userfollow USE INDEX(user_id) WHERE user_id = ? and follow_userid = ? "))) {
		$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		exit (json_encode($ret));	
			
	}

if (!$stmt->bind_param("ii", $myid,$user_id)) {
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
return $is_my_follow_user;

}

function get_like_status($message_id, $user_id) {
	global $ret, $mysqli;
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
	global $ret, $mysqli;
	
	if (!($stmt = $mysqli->prepare("SELECT * FROM userinfo WHERE user_id = ?"))) {
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
	
	$stmt->store_result();
	$bindVarsArray = array();
	$result = array();
	$meta = $stmt->result_metadata();

	while ($column = $meta->fetch_field()) {
		 $bindVarsArray[] = &$result[$column->name];
	} 
       
	call_user_func_array(array($stmt, 'bind_result'), $bindVarsArray);
	$ele = array();
	while ($stmt->fetch()) {
		foreach($result as $key => $val) {
			$ele[$key] = $val;
		}
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

    $a = array( 365 * 24 * 60 * 60  =>  '年',
                 30 * 24 * 60 * 60  =>  '个月',
                      24 * 60 * 60  =>  '天',
                           60 * 60  =>  '小时',
                                60  =>  '分钟',
                                 1  =>  '秒'
                );
   
    foreach ($a as $secs => $str)
    {
        $d = $etime / $secs;
        if ($d >= 1)
        {
            $r = round($d);
            return $r . $str . '前';
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
		if(mb_strlen($str,'utf-8') < $len) {
			return mb_substr($str,0,$len,'utf-8').'...';
		}
		else {
			return $str;
		}
	}
}

/**
function get_wallmsg_count($wall_id) {
	global $ret, $mysqli;
	
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
**/
function get_wallfavourate_count($wall_id) {
	global $ret, $mysqli;
	
	if (!($stmt = $mysqli->prepare("SELECT count(*) FROM msgwallfavourates USE INDEX(wall_id) WHERE wall_id = ? "))) {
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
	global $ret, $noti_type, $mem_host, $mem_port, $mysqli;
	
	$grade = get_grade();
	if($get_userpoint = $mysqli->query("select grade, point from userinfo where user_id = " . $user)) {
		$user_point = $get_userpoint->fetch_assoc();
		$u_grade = $user_point['grade'];
		$u_point = $user_point['point'];
	}
	else {
		printf("%s",$mysqli->error);
	}
	
	if($u_grade > 15) {
		$grade[$u_grade] = $grade[15];
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
		/**
		$longitude = 116.339889;
		$latitude = 40.029367;
		**/
		$content = "恭喜你，你的等级提升到" . ($u_grade + 1) . "级了";
		tieer_to_user($user, $content);
		
		if(check_user_hongbong($user) > 0) {
			
				$get_hongbao = send_hongbao($user);
				if(!empty($get_hongbao)) {
					$cont = "你获得一个由贴儿赠与的支付宝红包，口令是 $get_hongbao ，请在4小时内兑换。也请给贴儿发个5星评价";
					tieer_to_user($user, $cont);
				}

		}
		
		/**
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
		**/
		//notifaction
		/**
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
		**/
		//send push 
		if($get_nickname = $mysqli->query("SELECT nickname FROM userinfo WHERE user_id = $user_id")) {
			$nickname = $get_nickname->fetch_assoc()['nickname'];
		}

		if($get_registration = $mysqli->query("SELECT push_registration FROM user WHERE user_id = $receive_userid")) {
			$receive_value = $get_registration->fetch_assoc()['push_registration'];
		}
		
		if(!empty($receive_value)) {
			
			
			$send = $nickname.':'.$content;
			push_message($receive_value, $send, "upgrade");

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

function update_receive_count($message_id) {
	global $ret, $mysqli;
	
	if (!($stmt = $mysqli->prepare("UPDATE message set receive_count = receive_count + 1 WHERE message_id = ? "))) {
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
	
	$stmt->close();
}

function is_my_favourage_wall($user_id, $wall_id) {
	global $ret, $mysqli;
	
	if (!($stmt = $mysqli->prepare("select count(*) from msgwallfavourates USE INDEX(user_id) where user_id =? and wall_id = ? "))) {
	$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	exit (json_encode($ret));	
		
	}

	if (!$stmt->bind_param("ii",  $user_id, $wall_id)) {
		$ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

	if (!$stmt->execute()) {
		$ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

	$stmt->bind_result($count);

	while($stmt->fetch()) {

	}
	$stmt->close();
	return $count;

}

function tieer_to_user ($user_id, $content) {
	global $ret, $mysqli;
	$tieer_id = 1;
	$receive_userid = $user_id;
	$longitude = 116.339889;
	$latitude = 40.029367;
	//$content = "亲爱的贴友，贴儿小妞儿在此鞠躬感谢你参加贴儿的有奖活动哦！稍后会有获奖信息的通知~敬请期待！";
	$duration = 0;
	$content_type = 0;
	$time = time();
	if (!($stmt = $mysqli->prepare("INSERT INTO  usrchat (user_id,receive_userid, longitude,latitude,chat_content,duration, content_type, time) values (?,?,?,?,?,?,?,?) "))) {
		$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		exit (json_encode($ret));	
		
	}

	if (!$stmt->bind_param("iiddsiii", $tieer_id, $receive_userid,$longitude, $latitude, $content,$duration,$content_type,$time)) {
		$ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

	if (!$stmt->execute()) {
		$ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}
	$stmt->close();
}

function send_hongbao ($user_id) {
	global $ret, $mysqli;
	$curr_time = time();
	$hongbao = 0;
	if(stop_user_hongbong($user_id) > 0) {
	  return $hongbao;
	}
	$two_days_ago = $curr_time - 60 * 60 * 24 * 2;
	$query = "update eventredenvelope set AliLuckyMoneyCode = @hongbao := AliLuckyMoneyCode , PostStatus = 1, PostUserId = {$user_id}, PostTime = {$curr_time} where PostStatus = 0 and CreateTime > {$two_days_ago} limit 1 ; select @hongbao;";
	if($mysqli->multi_query($query)) {
		do {
			if ($result = $mysqli->store_result()) {
				while ($row = $result->fetch_row()) {
					$hongbao = $row[0];
				}
				$result->free();
			}
			//if ($mysqli->more_results()) {
				//printf("-----------------\n");
			//}
		} while ($mysqli->next_result());
	}
	else {
		$ret['ErrorMsg'] =  "Execute failed: (" . $mysqli->error . ") ";
		exit (json_encode($ret));
	}
	return $hongbao;
}

function stop_user_hongbong($user_id) {
	global $ret, $mysqli;

	if (!($stmt = $mysqli->prepare("select stop from user where user_id = ? "))) {
	$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	exit (json_encode($ret));	
		
	}

	if (!$stmt->bind_param("i",  $user_id)) {
		$ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

	if (!$stmt->execute()) {
		$ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

	$stmt->bind_result($count);

	while($stmt->fetch()) {

	}
	
	$stmt->close();
	
	return $count;

}

function check_user_hongbong($user_id) {
	global $ret, $mysqli;

	if (!($stmt = $mysqli->prepare("select count(*) from message m, msgwall w where m.wall_id = w.wall_id and m.author_id = ? and w.award_type = 1 "))) {
	$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	exit (json_encode($ret));	
		
	}

	if (!$stmt->bind_param("i",  $user_id)) {
		$ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

	if (!$stmt->execute()) {
		$ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

	$stmt->bind_result($count);

	while($stmt->fetch()) {

	}
	
	$stmt->close();
	
	return $count;

}

function push_message($to, $mesage, $m_type) {
	global $push_url;
	$platform = array();
	$platform[] = "android";
	$platform[] = "ios";
	$data['platform'] = $platform;
	$registration_id[]= $to;
	$data["audience"]["registration_id"] = $registration_id;
	$ios["alert"] = $mesage;
	$ios["sound"] = "drop.caf";
	$ios["badge"] = "+1";
	$extra["type"] = $m_type;
	//$extra["user_param_1"] = $to;
	$ios["extra"] = $extra;

	$data["notification"]["ios"] = $ios;

	$android["alert"] = $mesage;
	$android["title"] = "贴儿";
	$android["builder_id"] = 1;
	$android["extra"] = $extra;
	$data["notification"]["android"] = $android;
	$send = json_encode($data);
	$cmd = "curl --insecure -X POST -H 'Content-Type: application/json' -u '336e00d6925644d3b2566b45:2a0aef65eb700d8eb6bec39b'";
  $cmd.= " -d '" . $send . "' " . "'" . $push_url . "'";
  $cmd .= " > /dev/null 2>&1 &";
  exec($cmd, $output, $exit);
  return $exit == 0;


}
 