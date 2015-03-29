<?php
include ('../../header.php');

if(!check_login()) {
	$ret['ErrorMsg'] = '没有登录';
	exit (json_encode($ret));
}

$user_id = $user['user_id'];

if(isset($_POST['user_id']) && $_POST['user_id'] != '' ) {
	$user_id = $_POST['user_id'] + 0 ;
}

if($user_id == 0) {
	$ret['ErrorMsg'] = '无效用户';
	exit (json_encode($ret));
}


if(empty($_POST['encounter_userid']) or empty($_POST['message_id'])) {
	$ret['ErrorMsg'] = '参数错误';
	exit (json_encode($ret));
}

$encounter_userid = $_POST['encounter_userid'] + 0;

$t_user = $user_id;
$t_enc = $encounter_userid;


//just on recode each user and encounter

if($user_id > $encounter_userid) {

	list($encounter_userid,$user_id) = array($user_id,$encounter_userid);
}


$message = $_POST['message_id'] + 0;

$return = 0;

if (!($stmt = $mysqli->prepare("SELECT message_id,time FROM userencounter WHERE user_id = ? and encounter_userid = ?"))) {
		$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
		exit (json_encode($ret));	
			
	}

if (!$stmt->bind_param("ii", $user_id, $encounter_userid)) {
	  $ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

if (!$stmt->execute()) {
	  $ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
		exit (json_encode($ret));
	}

$stmt->bind_result($message_id,$time_str);

while($stmt->fetch()) {
}


if($message_id) {

		$old_message = explode(",", $message_id);

		if(in_array($message,$old_message)) {
				$return = 1;
		}
		else {
			$return = 2;
			$time_array = explode(',',$time_str);
			$time = time();
			$time_array[] = $time;
			$old_message[] = $message;
			$um = implode(",",$old_message);
  		$ut = implode(',',$time_array);
			$um_count = count($old_message);
			
			if($um_count === 2) {
				$status = 1;
				if (!($stmt = $mysqli->prepare("UPDATE  userencounter SET newtouser = ?, newtoencounteruser = ?, message_id = ?, time = ?,last_time = ?, encounter_time = ?, status = ? WHERE user_id = ? and encounter_userid = ?"))) {
					$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
					exit (json_encode($ret));	
				
				}
	
				if (!$stmt->bind_param("iissiiiii", $status,$status,$um, $ut, $time, $time, $status, $user_id, $encounter_userid)) {
		  		$ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
					exit (json_encode($ret));
				}
	
				if (!$stmt->execute()) {
		  		$ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
					exit (json_encode($ret));
				}
				
				$stmt->close();
				
				$n_type = $noti_type['enc'];
				
				if($rets = $mysqli->query("SELECT * FROM usrnotification WHERE user_id = $t_enc AND active_userid = $t_user AND type = '$n_type' ")) {
					if($rets->num_rows > 0) {
						if(!$mysqli->query("DELETE FROM usrnotification WHERE user_id = $t_enc AND active_userid = $t_user AND type = '$n_type' ")) {
							printf("Error: %s\n", $mysqli->error);
						}
		
					}
				}
				else {
					printf("Error: %s\n", $mysqli->error);
				}
	
				if(!$mysqli->query("INSERT INTO usrnotification (user_id, active_userid, type, time) VALUES ($t_enc, $t_user, '$n_type', $time)")) {
					printf("Error: %s\n", $mysqli->error);
				}
				
				if($rets = $mysqli->query("SELECT * FROM usrnotification WHERE user_id = $t_user AND active_userid = $t_enc AND type = '$n_type' ")) {
					if($rets->num_rows > 0) {
						if(!$mysqli->query("DELETE FROM usrnotification WHERE user_id = $t_user AND active_userid = $t_enc AND type = '$n_type' ")) {
							printf("Error: %s\n", $mysqli->error);
						}
		
					}
				}
				else {
					printf("Error: %s\n", $mysqli->error);
				}
	
				if(!$mysqli->query("INSERT INTO usrnotification (user_id, active_userid, type, time) VALUES ($t_user, $t_enc, '$n_type', $time)")) {
					printf("Error: %s\n", $mysqli->error);
				}
				
				//send message 1
				
        if($get_user = $mysqli->query("select nickname from userinfo where user_id = $user_id")) {
					
					$nickname = $get_user->fetch_assoc()['nickname'];
					//echo $nickname;
					
					if($get_push = $mysqli->query("select push_registration from user where user_id = $encounter_userid")) {
						$receive_value = $get_push->fetch_assoc()['push_registration'];
						//echo $receive_value;
						if(!empty($receive_value)) {
						
							$send = $nickname.'与你擦肩而过';
							push_message($receive_value, $send, "encounter");
            }
					}
				}
       
				//send message 2
        
				if($get_user = $mysqli->query("select nickname from userinfo where user_id = $encounter_userid")) {
					
					$nickname = $get_user->fetch_assoc()['nickname'];
					//echo $nickname;
					
					if($get_push = $mysqli->query("select push_registration from user where user_id = $user_id")) {
						$receive_value = $get_push->fetch_assoc()['push_registration'];
						//echo $receive_value;
						if(!empty($receive_value)) {
							$send = $nickname.'与你擦肩而过';
							push_message($receive_value, $send, "encounter");
							
            }
					}
				}
				
				
			}
			else {
				if (!($stmt = $mysqli->prepare("UPDATE  userencounter SET message_id = ?, time = ?, last_time = ? WHERE user_id = ? and encounter_userid = ?"))) {
					$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
					exit (json_encode($ret));	
				
				}
	
				if (!$stmt->bind_param("ssiii", $um, $ut, $time, $user_id, $encounter_userid)) {
		  		$ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
					exit (json_encode($ret));
				}
	
				if (!$stmt->execute()) {
		  		$ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
					exit (json_encode($ret));
				}
				
				$stmt->close();
				
			}
		}

}
else {

			if (!($stmt = $mysqli->prepare("insert into userencounter (user_id, encounter_userid, message_id, time, last_time) values (?,?,?,?,?)"))) {
				$ret['ErrorMsg'] =  "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
				exit (json_encode($ret));	
			
			}
			$t = time();
			if (!$stmt->bind_param("iiiii", $user_id, $encounter_userid, $message, $t, $t)) {
	  		$ret['ErrorMsg'] =  "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
				exit (json_encode($ret));
			}

			if (!$stmt->execute()) {
	  		$ret['ErrorMsg'] =  "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
				exit (json_encode($ret));
			}
			$stmt->close();
	
}

$mysqli->close();

$ret['status'] = 1;
$ret['ErrorMsg'] = '';
$ret['result'] = $return;


exit (json_encode($ret));