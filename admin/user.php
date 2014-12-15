<?php

if(!empty($_POST)) {
	if(empty($_POST['user_id'])) {
		$error = 'error';
	}
	$user_id = $_POST['user_id'] + 0;
	
	$mysqli = new mysqli('localhost', 'root', 'root', 'say');

	if ($mysqli->connect_errno) {
		print_r($mysqli->error);
	}

	if (!$mysqli->set_charset("utf8")) {
		print_r($mysqli->error);
	}
	if($get_user = $mysqli->query("select * from userinfo where user_id = $user_id")) {
		$user = $get_user->fetch_assoc();
		//var_dump($user);
	}
	else {
		printf("%s",$mysqli->error);
	}
	$mysqli->close();
	
	//header('Location: /admin/index.php');
}

?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
<script type="text/javascript" src="http://lib.sinaapp.com/js/jquery/2.0.3/jquery-2.0.3.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
<script src="//code.jquery.com/ui/1.11.1/jquery-ui.js"></script>
<link rel="stylesheet" href="//code.jquery.com/ui/1.11.1/themes/smoothness/jquery-ui.css">
</head>
<body>
<div class="container-fluid">

<div class="row-fluid" >
<?php
if(isset($error)) {
?>
<div class="alert alert-warning" role="alert">数据填写错误</div>
<?php
}else {
?>
<div class="alert alert-info" role="alert">请填入数据</div>
<?php
}
?>
<?php include('sidebar.php');?>

<div id="main" class="col-sm-8">
<div id="huodong" class="huodong">
<form role="form"  action="user.php" method="post" >
<div class="input-group">

<input name="user_id" class="form-control " value="" placeholder="请输入User ID"/>

<span class="input-group-btn">
<button type="submit" class="btn btn-default">提交</button>
 </span>
</div>

</form>
<div class="panel panel-info">
<div class="panel-heading">用户内容</div>
<div class="panel-body">
<div class="col-md-3">
    <a href="#" class="thumbnail">
      <img src="<?php echo $user['photo_url'];?>" >
    </a>
  </div>
<div class="col-md-9">
<table class="table ">
<tr>
<td>user_id</td>
<td><?php echo $user['user_id'];?></td>
</tr>

<tr>
<td>nickname</td>
<td><?php echo $user['nickname'];?></td>
</tr>
<tr>
<td>gender</td>
<td><?php echo $user['gender'];?></td>
</tr>
<tr>
<td>birthday</td>
<td><?php echo date('Y-m-d',$user['birthday']);?></td>
</tr>
<tr>
<td>description</td>
<td><?php echo $user['description'];?></td>
</tr>
<tr>
<td>reg_time</td>
<td><?php echo date('Y-m-d H:i:s',$user['reg_time']);?></td>
</tr>
<tr>
<td>last_login_time</td>
<td><?php echo date('Y-m-d H:i:s',$user['last_login_time']);?></td>
</tr>
<tr>
<td>last_message_time</td>
<td><?php echo date('Y-m-d H:i:s',$user['last_message_time']);?></td>
</tr>
<tr>
<td>last_like_time</td>
<td><?php echo date('Y-m-d H:i:s',$user['last_like_time']);?></td>
</tr>
<tr>
<td>last_comment_time</td>
<td><?php echo date('Y-m-d H:i:s',$user['last_comment_time']);?></td>
</tr>
<tr>
<td>last_chat_time</td>
<td><?php echo date('Y-m-d H:i:s',$user['last_chat_time']);?></td>
</tr>
</table>
</div>
</div>
</div>
<button type="submit" class="btn btn-default" onclick="del_user(<?php echo $user['user_id'];?>);">删除用户</button>
</div>



</div>
</body>
</html>
<script type="text/javascript">
 function del_user(id) {
	$.post("del_user.php",
  {
    id:id,
    
  },
	function(data,status){
    if(data == -1) {
			$('.alert-info').html("发生错误");
		}
		else {
			$('.alert-info').html("删除完成");
		}
  }).fail(function(data,status){
		$('.alert-info').html("发生错误");
		//alert(data);
	});
 }
 
</script>