<?php
include ('../../header.php');



if(!check_login()) {
	$ret['ErrorMsg'] = '没有登录';
	exit (json_encode($ret));
}



if(empty($_POST['message_id']) ) {
	$ret['ErrorMsg'] = '参数错误';
	exit (json_encode($ret));
}

$message_id = $_POST['message_id'] + 0;



if($get_image = $mysqli->query("select voice_url from message where message_id = $message_id")) {
	$voice_url = $get_image->fetch_assoc()['voice_url'];
	$path =   preg_replace("/\/say\//",'../../',$voice_url);
  $path =  realpath($path);
	$mp3 =  preg_replace('/\.amr/','.mp3',$path);
	
	
  if(file_exists($mp3)) {
		$result = 0;
	}
	else {
		$result = 1;
		
		$ex =  "sox $path $mp3";
		
		exec($ex);
		

		
	}	
}

  $ret['status'] = 1;
	$ret['ErrorMsg'] = '';
	$ret['result'] = $result;

  
  exit (json_encode($ret));
  
 
?>