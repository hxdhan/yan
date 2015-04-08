<?php

$mysqli = new mysqli('localhost', 'root', 'root', 'say');

if ($mysqli->connect_errno) {
	print_r($mysqli->error);
}

if (!$mysqli->set_charset("utf8")) {
	print_r($mysqli->error);
}

//渠道在合适的时间打卡的墙
$t = time();
$walls = array();
$walls_info = array();
$start_date = strtotime(date('Y-m-d'));
//var_dump($start_date);
if($get_walls = $mysqli->query("select mws.wall_id, w.name, mws.SuccessScore, mws.FailScore, mws.FailWord from msgwallsignininfo mws , msgwall w where mws.wall_id = w.wall_id and mws.StartTime <= $t and mws.EndTime > $t")) {
	while($get_wall = $get_walls->fetch_row()) {
		$wall = $get_wall[0];
		$info_array = array();
		$info_array[] = $get_wall[1];
		$info_array[] = $get_wall[2];
		$info_array[] = $get_wall[3];
		$info_array[] = $get_wall[4];
		$walls_info[$wall] = $info_array;
		if($get_walls_time = $mysqli->query("select SignInTimeFromHour, SignInTimeFromMinute, SignInTimeToHour, SignInTimeToMinute from msgwallsignintime where wall_id = $wall")) {
			
			while($wall_time = $get_walls_time->fetch_row()) {
				$time_array = array();
				$from_hour = $wall_time[0];
				$from_minute = $wall_time[1];
				$time_array[] = $start_date + $from_hour * 60 * 60 + $from_minute * 60;
				$to_hour = $wall_time[2];
				$to_minute = $wall_time[3];
				$time_array[] = $start_date + $to_hour * 60 * 60 + $to_minute * 60;
				$walls[$wall][] = $time_array;
			}
		}
	}
}

$user_result = array();

//$today_start = strtotime(date('Y-m-d'));
//$today_end = strtotime(date('Y-m-d'). " 23:59:59");

foreach($walls as $key => $val) {
	//找到今天在该墙上的帖子,并按照用户分割
	//不能计算当天的原因是，需要统计用户连续打卡的问题，如果昨天成功了，今天没打卡，那还是没成功
	if($get_message = $mysqli->query("select author_id, time from message where wall_id = $key  ")) {
	//if($get_message = $mysqli->query("select author_id, time from message where wall_id = $key and time between $today_start and $today_end ")) {
		$user_message = array();
		while($message = $get_message->fetch_row()) {
			$user_message[$message[0]][] = $message[1];
		}
	}
	//针对每一个用户判断是否在打卡区间内，如果在打卡区间内，则打卡成功
	foreach ($val as $wall_time_value) {
		
		foreach($user_message as $user => $tietime) {
		
			if(($t = daka($wall_time_value[0],$wall_time_value[1], $tietime)) > 0 ) {
				$user_result[$user][$key][] = array(0,$t);
			}
			else {
				$user_result[$user][$key][] = array(1);
			}
			
		}
		
	}
	
}

//var_dump($user_result);
//当所有打卡成功的时候，才是真正成功

foreach($user_result as $ret_user => $ret_val) {
	foreach($ret_val as $ret_wall => $ret_sorf) {
		$user_wall_sorf = 0;
		$sign_time = '';
		foreach($ret_sorf as $s_ret_sorf) {
			$user_wall_sorf += $s_ret_sorf[0];
			if(isset($s_ret_sorf[1])) {
				if($sign_time === '') {
					$sign_time .= date('Y-m-d H:i:s', $s_ret_sorf[1]);
				}
				else {
				  $sign_time .= '和'.date('Y-m-d H:i:s', $s_ret_sorf[1]);
				}
			}
		}
		//var_dump($user_wall_sorf);
		if ($user_wall_sorf == 0) {
			
			if(!$mysqli->query("insert into msgwallsigninresult (UserId, WallId, FinishTime, SuccessCount, FailCount, score) values($ret_user, $ret_wall,$t,1, 0,{$walls_info[$ret_wall][1]}) on duplicate key update FinishTime = $t, SuccessCount = SuccessCount + 1, score = score + {$walls_info[$ret_wall][1]};")) {
				print_r($mysqli->error);
			}
			
			if($get_sign_result = $mysqli->query("select SuccessCount, FailCount, score from msgwallsigninresult where UserId = $ret_user and WallId = $ret_wall")) {
				$sign_result = $get_sign_result->fetch_row();
				
				$content = $sign_time.", 您在".$walls_info[$ret_wall][0]."签到成功。"."已经签到 {$sign_result[0]} 次，现在的签到积分是 {$sign_result[2]}";
				tieer_to_user($ret_user, $content);
			}
			else {
				print_r($mysqli->error);
			}
		}
		else {
			//签到失败
			
			if(!$mysqli->query("insert into msgwallsigninresult (UserId, WallId, FinishTime, SuccessCount, FailCount, score) values($ret_user, $ret_wall,$t,0, 1, {$walls_info[$ret_wall][2]}) on duplicate key update FinishTime = $t, FailCount = FailCount + 1, score = if(score + {$walls_info[$ret_wall][2]} <= 0,0,score + {$walls_info[$ret_wall][2]}) ")) {
				print_r($mysqli->error);
			}
			
			$content = date('Y-m-d')." " .$walls_info[$ret_wall][3];
			tieer_to_user($ret_user, $content); 
		}
	}
}

echo "ok";

function daka($start_time, $end_time , Array $time_array) {
	//sort array
	usort($time_array, function ($a,$b){
		if(intval($a) == intval($b)) return 0;
		else if(intval($a) < intval($b)) return -1;
		else return 1;
	});
	foreach($time_array as $t) {
				
		if( intval($start_time) <= intval($t) &&  intval($t) < intval($end_time)) {
			return $t;
		}
	}
	return 0;
}

function tieer_to_user ($user_id, $content) {
	global $mysqli;
	$tieer_id = 1;
	$receive_userid = $user_id;
	$longitude = 116.339889;
	$latitude = 40.029367;
	//$content = "亲爱的贴友，贴儿小妞儿在此鞠躬感谢你参加贴儿的有奖活动哦！稍后会有获奖信息的通知~敬请期待！";
	$duration = 0;
	$content_type = 0;
	$time = time();
	if (!($stmt = $mysqli->prepare("INSERT INTO  usrchat (user_id,receive_userid, longitude,latitude,chat_content,duration, content_type, time) values (?,?,?,?,?,?,?,?) "))) {
				
	}

	if (!$stmt->bind_param("iiddsiii", $tieer_id, $receive_userid,$longitude, $latitude, $content,$duration,$content_type,$time)) {
		
	}

	if (!$stmt->execute()) {
	
	}
	$stmt->close();
}




		
	