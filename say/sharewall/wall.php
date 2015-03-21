<?php
include ('../header.php');
if(empty($_GET['id']) ) {
	exit ('没有ID');
}

//是否要分享自己的

$wall_id = $_GET['id'] + 0 ;


if($get_wall = $mysqli->query("select * from msgwall where wall_id = {$wall_id}")) {
	$wall = $get_wall->fetch_assoc();
}
else {
	printf("error: %s", $mysqli->error);
}


if(empty($wall)) {
	header("Content-type: text/html; charset=utf-8"); 
	exit('该墙不存在，或者已删除!');
}

if($get_user = $mysqli->query("select nickname,photo_url,grade from userinfo where user_id = {$wall['owner_userid']}")) {
  
	$user = $get_user->fetch_assoc();

}
else {
	printf("error: %s", $mysqli->error);
}

//var_dump($user);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />

<title><?php echo $wall['name'];?></title>
<link href="css/style.css" type="text/css" rel="stylesheet" />

</head>
<body>
<div class="wall_header">
<div class="wall_user">
<img id="wall_user" src="<?php echo $user['photo_url'];?>"></img>
<div class="level">
<?php echo $user['grade'];?>
</div>
</div>

<div class="owner">
专栏主
</br> 
<?php echo $user['nickname'];?>
</div>
</div>
<div class="wall_info0">
专栏主留言：
</div>

<div class="wall_info1">
<?php 
if($wall['info'] == '') {
	echo "欢迎大家访问这个专栏。这个专栏上的贴儿都是这附近的人发的，因为只有这里附近的人才能在这里发哦。你看看，我们这里都有什么新鲜事儿啊";
}
else {
	echo $wall['info'];
}
?>
</div>


</body>
</html>
