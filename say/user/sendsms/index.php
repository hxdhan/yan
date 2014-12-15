<?php

include ('../../header.php');

function Post($curlPost,$url){
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
/**
function xml_to_array($xml){
	$reg = "/<(\w+)[^>]*>([\\x00-\\xFF]*)<\\/\\1>/";
	if(preg_match_all($reg, $xml, $matches)){
		$count = count($matches[0]);
		for($i = 0; $i < $count; $i++){
		$subxml= $matches[2][$i];
		$key = $matches[1][$i];
			if(preg_match( $reg, $subxml )){
				$arr[$key] = xml_to_array( $subxml );
			}else{
				$arr[$key] = $subxml;
			}
		}
	}
	return $arr;
}
**/
if(empty($_POST['cellphone']) || empty($_POST['smscode'])) {
	$ret['ErrorMsg'] = '参数错误';
	exit (json_encode($ret));
}

$mobile = $_POST['cellphone'];
$mobile_code = $_POST['smscode'];

$pw = '123456';



$post_data = "ua=xueyan&pw=$pw&mb=".$mobile."&ms="."验证码：{$mobile_code}，请不要告诉别人哦。【遇言】";


$gets =  Post($post_data, $target);

$re = explode(',',$gets);

if($re[0] == 0) {
	$ret['status'] = 1;
	$ret['ErrorMsg'] = '';
}
else {
	$ret['ErrorMsg'] = '发送失败';
}

exit (json_encode($ret));
