<?php
include ('../../header.php');

if(!check_login()) {
	$ret['ErrorMsg'] = '没有登录';
	exit (json_encode($ret));
}

$user_id = $user['user_id'];

if($user_id == 0) {
	$ret['ErrorMsg'] = '没有登录';
	exit (json_encode($ret));
}

if(empty($_POST['user_id']) || empty($_POST['message_id']) || empty($_POST['platform']) ) {
	$ret['ErrorMsg'] = '参数错误';
	exit (json_encode($ret));
}


$message_id = $_POST['message_id'] + 0;
$user_id = $_POST['user_id'] + 0;
$platform = $_POST['platform'];
$time = time();

if (!($stmt = $mysqli->prepare("insert into msgshare (user_id, message_id, platform, time) values (?,?,?,?)"))) {
	$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
	exit (json_encode($ret));	
		
}

if (!$stmt->bind_param("iisi", $user_id, $message_id, $platform, $time)) {
  $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
	exit (json_encode($ret));
}

if (!$stmt->execute()) {
  $ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
	exit (json_encode($ret));
}

$stmt->close();

//if share to pengyouquan
if (strcasecmp($platform, 'pengyouquan') == 0) {

	$chance = 100;

	//probality 100% get 
	if(mt_rand(1,100) < $chance) {
		//check message 
		if($rets = $mysqli->query("select * from message where author_id = $user_id and message_id = $message_id and category_id in (102)")) {
				//send gift message
				if($rets->num_rows > 0) {
						
						$content = "亲爱的贴友，贴儿小妞儿在此鞠躬感谢你参加贴儿的有奖活动哦！稍后会有获奖信息的通知~敬请期待！";
						tieer_to_user ($user_id, $content);
						
						//notifications . only one chat data notifaction
						/**
						$n_type = $noti_type['chat'];

						if($rets = $mysqli->query("SELECT * FROM usrnotification WHERE user_id = $receive_userid AND active_userid = $tieer_id AND type = '$n_type' ")) {
							if($rets->num_rows > 0) {
								if(!$mysqli->query("DELETE FROM usrnotification WHERE user_id = $receive_userid AND active_userid = $tieer_id AND type = '$n_type' ")) {
									printf("Error: %s\n", $mysqli->error);
								}
								
							}
						}
						else {
							printf("Error: %s\n", $mysqli->error);
						}
							
						if(!$mysqli->query("INSERT INTO usrnotification (user_id, active_userid, type, time) VALUES ($receive_userid, $tieer_id, '$n_type', $time)")) {
							printf("Error: %s\n", $mysqli->error);
						}
						**/
						
						if($get_registration = $mysqli->query("SELECT push_registration FROM user WHERE user_id = $receive_userid")) {
							$receive_value = $get_registration->fetch_assoc()['push_registration'];
						}
						if(!empty($receive_value)) {
				
							$charset = 'UTF-8';
							$length = 40;
							
							if(mb_strlen($content, $charset) > $length) {
								$content = mb_substr($content, 0, $length - 3, $charset) . '...';
							}
							$send = $content;
							push_message($receive_value, $send, "share");

						}
				}
		}
		else {
			printf("Error: %s\n", $mysqli->error);
		}


	}

}

$mysqli->close();


$ret['status'] = 1;
$ret['ErrorMsg'] = '';

exit (json_encode($ret));