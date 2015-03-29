<?php
include ('../header.php');
if(empty($_GET['id']) ) {
	exit ('没有ID');
}

//是否要分享自己的

$wall_id = $_GET['id'] + 0 ;


if($get_wall = $mysqli->query("select * ,(select count(*) from msgwallfavourates where wall_id = {$wall_id}) as f_count from msgwall where wall_id = {$wall_id}")) {
	$wall = $get_wall->fetch_assoc();
}
else {
	printf("error: %s", $mysqli->error);
}


if(empty($wall)) {
	header("Content-type: text/html; charset=utf-8"); 
	exit('该专栏不存在，或者已删除!');
}

$messages = array();
if($get_message = $mysqli->query("select * from message where wall_id = {$wall_id} order by message_id desc limit 10")) {
  
	while($message = $get_message->fetch_assoc()) {
		
		if($get_user = $mysqli->query("select nickname,photo_url from userinfo where user_id = {$message['author_id']}")) {
			$user = $get_user->fetch_assoc();
			$message = array_merge($message, $user);
			$messages[] = $message;
		}
		else {
			printf("error: %s", $mysqli->error);
		}
		
	}

}
else {
	printf("error: %s", $mysqli->error);
}




$pstr = implode("','",array_reduce($messages, function($carry, $item){
  //var_dump($carry);
	$carry[] = $item['voice_url'] ;
	return $carry;
}));
$pstr = "'".$pstr."'";





define("a", 6378245.0); 
define("ee", 0.00669342162296594323); 
  
// World Geodetic System ==> Mars Geodetic System 
function GetMarsGSCoord($wgLat, $wgLon) 
{ 
     $ret = array('lat' => $wgLat, 'lng' => $wgLon); 
     $dLat = transformLat($wgLon - 105.0, $wgLat - 35.0); 
     $dLon = transformLon($wgLon - 105.0, $wgLat - 35.0); 
     $radLat = $wgLat / 180.0 * M_PI; 
     $magic = sin($radLat); 
     $magic = 1 - ee * $magic * $magic; 
     $sqrtMagic = sqrt($magic); 
     $dLat = ($dLat * 180.0) / ((a * (1 - ee)) / ($magic * $sqrtMagic) * M_PI); 
     $dLon = ($dLon * 180.0) / (a / $sqrtMagic * cos($radLat) * M_PI); 
     $ret['lat'] = $wgLat + $dLat; 
     $ret['lng'] = $wgLon + $dLon; 
     return $ret; 
} 
  
function transformLat($x, $y) 
{ 
     $ret = -100.0 + 2.0 * $x + 3.0 * $y + 0.2 * $y * $y + 0.1 * $x  * $y + 0.2 * sqrt(abs($x)); 
     $ret += (20.0 * sin(6.0 * $x * M_PI) + 20.0 * sin(2.0 * $x *  M_PI)) * 2.0 / 3.0; 
     $ret += (20.0 * sin($y * M_PI) + 40.0 * sin($y / 3.0 * M_PI)) * 2.0 / 3.0; 
     $ret += (160.0 * sin($y / 12.0 * M_PI) + 320 * sin($y * M_PI / 30.0)) * 2.0 / 3.0; 
     return $ret; 
} 
  
function transformLon($x, $y) 
{ 
     $ret = 300.0 + $x + 2.0 * $y + 0.1 * $x * $x + 0.1 * $x * $y + 0.1 * sqrt(abs($x)); 
     $ret += (20.0 * sin(6.0 * $x * M_PI) + 20.0 * sin(2.0 * $x * M_PI)) * 2.0 / 3.0; 
     $ret += (20.0 * sin($x * M_PI) + 40.0 * sin($x / 3.0 * M_PI)) * 2.0 / 3.0; 
     $ret += (150.0 * sin($x / 12.0 * M_PI) + 300.0 * sin($x / 30.0 * M_PI)) * 2.0 / 3.0; 
     return $ret; 
} 

$trans_loc = GetMarsGSCoord($wall['latitude'], $wall['longitude']);




?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />

<title>专栏分享</title>
<link href="css/style.css" type="text/css" rel="stylesheet" />
<script language="javascript" src="http://webapi.amap.com/maps?v=1.2&key=11bb7d7579acf8302f8cff6aa53ac8b4"></script>
<script language="javascript" src="http://lib.sinaapp.com/js/jquery/2.0.3/jquery-2.0.3.min.js"></script>
</head>
<body>
<div class="header">
<div class="header-left">
<div class="logo-left">
<img id="logo" src='images/logo.png'/>
</div>
<div class="slo-all">
<img id="tieer" src='images/tieer.png'/>
<div class="slo">
地图上的话题分享社区
</div>
</div>

</div>
<div class="header-right">
<a href='http://a.app.qq.com/o/simple.jsp?pkgname=com.bu.yuyan'><img id="download" src='images/download.png'/></a>
</div>

</div>
<div class="wall_image">
<div class="w_image">
<img id="w_image" src="<?php echo $wall['image_url'];?> ">
<div class="ww_name">
<img id="zlzl" src="images/zlzl.png"/>
<div class="wall_name">
<?php echo $wall['name'];?>
</div>
<div class="wall_name1">
关注      &nbsp;&nbsp;<span style="color:#ffa200"><?php echo $wall['f_count'];?></span>  &nbsp;&nbsp;贴儿 &nbsp;&nbsp;    <span style="color:#ffa200"><?php echo $wall['message_count'];?></span>
</div>
</div>
</div>
<div class="wall_info">
<a href="/say/sharewall/wall.php?id=<?php echo $wall_id;?>"><img id="wall_info" src="images/info.png"></a>
</div>
</div>


<div class="zltr">
<img id="zltr" src="images/zltr.png">
</div>

<div class="recommand">

<?php
$index = 0;
$count = count($messages);

for($index=0, $count=count($messages);$index < $count; $index++)  {
?>
<div class="recommand-left">
<div class="user-left">
<div class="rec-left">
<img id="rec-user" src='<?php echo $messages[$index]['photo_url'];?>'>
</div>
<div class="rec-right">
<div class="rec-time">
<?php echo time_elapsed_string($messages[$index]['time']);?>
</div>
<div class="rec-username">
<?php echo my_truncate($messages[$index]['nickname'],4) ;?>
</div>
</div>
</div>
<div class="rec-info">
<?php
if($messages[$index]['image_url'] != '') {
?>
<img id="rec-image" src='<?php echo $messages[$index]['image_url'];?>'/>
<?php if($messages[$index]['duration'] > 0) { ?>
<div>
<img id="rec-playbg" src='images/rec-playbg.png'/>
</div>
<div>
<img class="rec-play" id="play<?php echo $index;?>" onclick="javascript:start(<?php echo $index;?>);" src='images/start.png'/>
</div>
<?php }?>
<?php
}
?>
<div class="message_text">
<?php echo $messages[$index]['text'];?>
</div>
<div class="ahr">
</div>
<div class="rec-footer">
<img id="r-like" src='images/view.png'/>
<div class="rec-liken">

<?php echo $messages[$index]['receive_count'];?>
</div>
</div>
</div>
</div>
<?php
 if($index++ < $count -1) {
  
?>
<div class="recommand-right">
<div class="user-left">
<div class="rec-left">
<img id="rec-user" src='<?php echo $messages[$index]['photo_url'];?>'>
</div>
<div class="rec-right">
<div class="rec-time">
<?php echo time_elapsed_string($messages[$index]['time']);?>
</div>
<div class="rec-username">
<?php echo my_truncate($messages[$index]['nickname'],4) ;?>
</div>
</div>
</div>
<div class="rec-info">
<?php
if($messages[$index]['image_url'] != '') {
?>
<img id="rec-image" src='<?php echo $messages[$index]['image_url'];?>'/>
<?php if($messages[$index]['duration'] > 0) { ?>
<div>
<img id="rec-playbg" src='images/rec-playbg.png'/>
</div>
<div>
<img class="rec-play" id="play<?php echo $index;?>" onclick="javascript:start(<?php echo $index;?>);" src='images/start.png'/>
</div>
<?php } ?>
<?php
}
?>
<div class="message_text">
<?php echo $messages[$index]['text'];?>
</div>
<div class="ahr">
</div>
<div class="rec-footer">
<img id="r-like" src='images/view.png'/>
<div class="rec-liken">

<?php echo $messages[$index]['like_count'];?>
</div>
</div>
</div>
</div>
<?php
}
} 
?>
</div>


<div class="footer">
<div class="header-left">
<div class="logo-left">
<img id="logo" src='images/logo.png'/>

</div>
<div class="slo-all">
<img id="tieer" src='images/tieer.png'/>
<div class="slo">
地图上的话题分享社区
</div>
</div>

</div>
<div class="header-right">
<a href='http://a.app.qq.com/o/simple.jsp?pkgname=com.bu.yuyan'><img id="download" src='images/download.png'/></a>
</div>
</div>
</body>
</html>


<script type="text/javascript">
var video;
var play_index;
var play_array = new Array(<?php echo $pstr;?>);
function start(index) {
  if(video) {
	  if(play_index == index) {
			if(video.paused) {
				video.play();
				document.getElementById("play"+index).src="images/stop.png";
			
			}
			else {
				
				video.pause();
				document.getElementById("play"+index).src="images/start.png";
			}
		}
		if(play_index != index) {
			  video.pause();
				document.getElementById("play"+play_index).src="images/start.png";
				
				play_index = index;
				video = document.createElement('audio');
				video.setAttribute('src',play_array[index]);
				video.load();
				video.play();
				video.addEventListener('ended', fini, false);
				document.getElementById("play"+index).src="images/stop.png";
		}
	}
	else {
		play_index = index;
		video = document.createElement('audio');
		video.setAttribute('src',play_array[index]);
		video.load();
		video.play();
		video.addEventListener('ended', fini, false);
		document.getElementById("play"+index).src="images/stop.png";
	}
}

function fini() {
	document.getElementById("play"+play_index).src="images/start.png";
}
</script>
