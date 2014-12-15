<?php
include ('../header.php');
if(empty($_GET['id']) ) {
	exit ('没有ID');
}

//是否要分享自己的

$message_id = $_GET['id'] + 0 ;

if($get_message = $mysqli->query("select * from message where message_id = {$message_id}")) {
	$message = $get_message->fetch_assoc();
}

if(empty($message)) {
	header("Content-type: text/html; charset=utf-8"); 
	exit('该留言不存在，或者已删除!');
}

if($get_user = $mysqli->query("select nickname,photo_url from userinfo where user_id = {$message['author_id']}")) {
	$user = $get_user->fetch_assoc();
}

if(empty($user)) {
	header("Content-type: text/html; charset=utf-8"); 
	exit('没有user');
}

if($get_cat = $mysqli->query("SELECT category_name FROM category WHERE category_id = {$message['category_id']} ")) {
	$category = $get_cat->fetch_row()[0];
}

if($get_recomm = $mysqli->query("select *,like_count*10 + comment_count*20 +receive_count as order_count from message where time > UNIX_TIMESTAMP(date_sub(now(), interval 1 day)) and message_id <> {$message['message_id']} order by order_count desc limit 2")) {
	$recomm = array();
	while($r = $get_recomm->fetch_assoc()) {
		if($get_ruser = $mysqli->query("select nickname, photo_url from userinfo where user_id = {$r['author_id']}")) {
			$ruser = $get_ruser->fetch_assoc();
		}
		$r = array_merge($r,$ruser);
		$recomm[] = $r;
	}
	//var_dump($recomm);
}




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

$trans_loc = GetMarsGSCoord($message['latitude'], $message['longitude']);




?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />

<title>分享</title>
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
<div>
<img id="slo" src='images/slo.png'/>
</div>
</div>

</div>
<div class="header-right">
<a href='http://a.app.qq.com/o/simple.jsp?pkgname=com.bu.yuyan'><img id="download" src='images/download.png'/></a>
</div>
</div>
<div class="info">
<div class="user">
<!--
<img id="user-bg" src='images/user-bg.png'/>
-->
<img id="user-image" src='<?php echo $user['photo_url'];?>'/>

</div>
<div class="user-name">
<?php echo $user['nickname'];?>
</div>
<div class="loc">
<img id="loc" src='images/loc.png'/>
<div class="loc-text" id="loc-text">
海淀区
</div>
</div>
<div>
<img id="myimg" src='<?php echo $message['image_url'];?>'/>
</div>
<div>
<img id="play-bg" src='images/play-bg.png'/>

</div>
<div>
<img id="play" onclick="javascript:start();" src='images/start.png'/>
</div>
<div class="category">
#<?php echo $category;?>
</div>
<div class="extra-info">
<div class="extra-left">
<img id="extra-left" src='images/time.png'/>
<?php echo time_elapsed_string($message['time']);?>
</div>
<div class="extra-right">
<div class="like">
<img id="like" src='images/like.png'/>
<?php echo $message['like_count'];?>
</div>
<div class="ting">
<img id="ting" src='images/ting.png'/>
<?php echo $message['receive_count'];?>
</div>
</div>
</div>
</div>
<div class="map" id="map" style="height:75px;overflow: hidden;">
</div>
<div class="rc-text">
<img id="rc-text" src='images/rt.png'/>
</div>
<div class="recommand">
<div class="recommand-left">
<div class="user-left">
<div class="rec-left">
<img id="rec-user" src='<?php echo $recomm[0]['photo_url'];?>'>
</div>
<div class="rec-right">
<div class="rec-time">
<?php echo time_elapsed_string($recomm[0]['time']);?>
</div>
<div class="rec-username">
<?php echo my_truncate($recomm[0]['nickname'],4) ;?>
</div>
</div>
</div>
<div class="rec-info">
<img id="rec-image" src='<?php echo $recomm[0]['image_url'];?>'/>
<div>
<img id="rec-playbg" src='images/rec-playbg.png'/>
</div>
<div>
<img class="rec-play" id="play_left" onclick="javascript:start_left();" src='images/start.png'/>
</div>
<hr class="rec-hr"/>
<div class="rec-footer">
<div class="rec-like">
<img id="like" src='images/like.png'/>
</div>
<div class="rec-liken">
<?php echo $recomm[0]['like_count'];?>
</div>
</div>
</div>
</div>
<div class="recommand-right">
<div class="user-left">
<div class="rec-left">
<img id="rec-user" src='<?php echo $recomm[1]['photo_url'];?>'>
</div>
<div class="rec-right">
<div class="rec-time">
<?php echo time_elapsed_string($recomm[1]['time']);?>
</div>
<div class="rec-username">
<?php echo my_truncate($recomm[1]['nickname'],4) ;?>
</div>
</div>
</div>
<div class="rec-info">
<img id="rec-image" src='<?php echo $recomm[1]['image_url'];?>'/>
<div>
<img id="rec-playbg" src='images/rec-playbg.png'/>
</div>
<div>
<img class="rec-play" id="play_right" onclick="javascript:start_right();" src='images/start.png'/>
</div>
<hr class="rec-hr"/>
<div class="rec-footer">
<div class="rec-like">
<img id="like" src='images/like.png'/>
</div>
<div class="rec-liken">
<?php echo $recomm[1]['like_count'];?>
</div>
</div>
</div>
</div>
</div>
<div class="header">
<div class="header-left">
<div class="logo-left">
<img id="logo" src='images/logo.png'/>

</div>
<div class="slo-all">
<img id="tieer" src='images/tieer.png'/>
<div>
<img id="slo" src='images/slo.png'/>
</div>
</div>

</div>
<div class="header-right">
<a href='http://a.app.qq.com/o/simple.jsp?pkgname=com.bu.yuyan'><img id="download" src='images/download.png'/></a>
</div>
</div>
</div>
</body>
</html>
<script type="text/javascript">
	mapObj = new AMap.Map("map",{
	center:new AMap.LngLat(<?php echo $trans_loc['lng'];?>,<?php echo $trans_loc['lat'];?>), 
	level:14,
	dragEnable:false,
	zoomEnable:false,
	}); 
	
	 // marker=new AMap.Marker({                  
	 // icon:"http://121.199.36.8/say/sh/images/pin.png",
	 // position:new AMap.LngLat(<?php echo $trans_loc['lng'];?>,<?php echo $trans_loc['lat'];?>)
	 // });
	 // marker.setTitle('清河翠微百货');
	 // marker.setMap(mapObj);  
	// marker=new AMap.Marker({                  
	// icon:"http://121.199.36.8/say/sh/images/pin-bg.png",
	// offset:new AMap.Pixel(15,-15),
	// position:new AMap.LngLat(<?php echo $trans_loc['lng'];?>,<?php echo $trans_loc['lat'];?>),
	// content:"清河翠微百货",
	// });
	// marker.setMap(mapObj); 
	
	
	var lnglatXY = new AMap.LngLat(<?php echo $trans_loc['lng'];?>,<?php echo $trans_loc['lat'];?>);
	var MGeocoder;
	mapObj.plugin(["AMap.Geocoder"], function() {       
        MGeocoder = new AMap.Geocoder({
            radius: 10,
            extensions: "base"
        });
        //返回地理编码结果
        AMap.event.addListener(MGeocoder, "complete", geocoder_CallBack);
        //逆地理编码
        MGeocoder.getAddress(lnglatXY);
    });
function geocoder_CallBack(data) {
	province = data.regeocode.addressComponent.province;
	district = data.regeocode.addressComponent.district;
	
	document.getElementById("loc-text").innerHTML =  district;
	
	  //自定义点标记内容   
	var markerContent = document.createElement("div");
	markerContent.className = "marker";
  //var markeri = document.createElement("div");
	// markeri.className="marki";
	 //markerContent.appendChild(markeri);
	//点标记中的图标
	var markerImg= document.createElement("img");
     markerImg.className="markerlnglat";
	 markerImg.src="/say/share/images/pin.png";	
	 markerContent.appendChild(markerImg);
	 var markerImg1= document.createElement("img");
     markerImg1.className="markerlnglat1";
	 markerImg1.src="/say/share/images/pin-bg.png";	
	 markerContent.appendChild(markerImg1);
	 
	 //点标记中的文本
	 var markerSpan = document.createElement("div");
	 markerSpan.className="markspan";
	 markerSpan.innerHTML = data.regeocode.addressComponent.township;
	 markerContent.appendChild(markerSpan);
	 
	 
	 
	 marker = new AMap.Marker({
		map:mapObj,
		position:new AMap.LngLat(<?php echo $trans_loc['lng'];?>,<?php echo $trans_loc['lat'];?>), //基点位置
		//offset:new AMap.Pixel(-18,-36), //相对于基点的偏移位置
		draggable:false,  //是否可拖动
		content:markerContent   //自定义点标记覆盖物内容
	});
	marker.setMap(mapObj);  //在地图上添加点
}

</script>

<script type="text/javascript">
<?php if(isset($message['voice_url'])) { ?>
var video;
 function start() {
	
	if(video) {
		
		if(video.paused) {
      video.play();
			document.getElementById("play").src="images/stop.png";
			
		}
		else {
      
			video.pause();
      document.getElementById("play").src="images/start.png";
		}
	} 
	else {
		
		video = document.createElement('audio');
  	video.setAttribute('src',"<?php echo $message['voice_url'];?>");
  	video.load();
  	video.play();
		video.addEventListener('ended', fini, false);
		document.getElementById("play").src="images/stop.png";
		$.post("/say/message/increasemessagereceivecount/",
				{
					login_token : '1852689bc9090133494bc638ea528491',
					message_id : <?php echo $message['message_id'];?>,
					
				},
				function(data,status){
					//alert(data);
				}
			);
  }
}
function fini() {
	document.getElementById("play").src="images/start.png";
}
<?php } ?>

<?php if(isset($recomm[0]['voice_url'])) { ?>
var video1;
 function start_left() {
	
	if(video1) {
		
		if(video1.paused) {
      video1.play();
			document.getElementById("play_left").src="images/stop.png";
		}
		else {
      
			video1.pause();
      document.getElementById("play_left").src="images/start.png";
		}
	} 
	else {
		
		video1 = document.createElement('audio');
  	video1.setAttribute('src',"<?php echo $recomm[0]['voice_url'];?>");
  	video1.load();
  	video1.play();
		video1.addEventListener('ended', fini1, false);
		document.getElementById("play_left").src="images/stop.png";
		$.post("/say/message/increasemessagereceivecount/",
				{
					login_token : '1852689bc9090133494bc638ea528491',
					message_id : <?php echo $recomm[0]['message_id'];?>,
					
				},
				function(data,status){
					//alert(data);
				}
			);
  }
}
function fini1() {
	document.getElementById("play_left").src="images/start.png";
}
<?php } ?>
<?php if(isset($recomm[1]['voice_url'])) { ?>
var video2;
 function start_right() {
	
	if(video2) {
		
		if(video2.paused) {
      video2.play();
			document.getElementById("play_right").src="images/stop.png";
		}
		else {
      
			video2.pause();
      document.getElementById("play_right").src="images/start.png";
		}
	} 
	else {
		
		video2 = document.createElement('audio');
  	video2.setAttribute('src',"<?php echo $recomm[1]['voice_url'];?>");
  	video2.load();
  	video2.play();
		video2.addEventListener('ended', fini2, false);
		document.getElementById("play_right").src="images/stop.png";
		
		$.post("/say/message/increasemessagereceivecount/",
				{
					login_token : '1852689bc9090133494bc638ea528491',
					message_id : <?php echo $recomm[1]['message_id'];?>,
					
				},
				function(data,status){
					//alert(data);
				}
			);
  }
}
function fini2() {
	document.getElementById("play_right").src="images/start.png";
}
<?php } ?>
</script>