<?php

if(!empty($_POST)) {
	if(empty($_POST['message_id'])  ) {
		$error = 'error';
	}
	$message_id = $_POST['message_id'] + 0 ;
	
	$mysqli = new mysqli('localhost', 'root', 'root', 'say');
	
	

	if ($mysqli->connect_errno) {
		print_r($mysqli->error);
	}

	if (!$mysqli->set_charset("utf8")) {
		print_r($mysqli->error);
	}
	
	if($get_message = $mysqli->query("select * from message where message_id = $message_id")) {
		$message = $get_message->fetch_assoc();
		//var_dump($message);
	}
	else {
		printf("%s",$mysqli->error);
	}
	
	$mysqli->close();
	
	//header('Location: /admin/message.php');
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

<form role="form"  action="message.php" method="post" >

<div class="input-group">

<input name="message_id" class="form-control " value="" placeholder="请输入消息ID"/>

<span class="input-group-btn">
<button type="submit" class="btn btn-default">提交</button>
 </span>
</div>

</form>
<div class="panel panel-info">
<div class="panel-heading">消息内容</div>
<table class="table">
  <tr>
	<td>message_id</td>
	<td><?php echo $message['message_id'];?></td>
	
	<td>author_id</td>
	<td><?php echo $message['author_id'];?></td>
	</tr>
	<tr>
	<td>category_id</td>
	<td><?php echo $message['category_id'];?></td>
	
	<td>voice_url</td>
	<td><button id="play" type="button" class="btn btn-default" onclick="play();" >play</button></td>
	</tr>
	<tr>
	<td>duration</td>
	<td><?php echo $message['duration'];?></td>
	<td>time</td>
	<td><?php echo date('Y-m-d H:i:s',$message['time']);?></td>
	
	</tr>
	<tr>
	<td>longitude</td>
	<td><?php echo $message['longitude'];?></td>
	<td>latitude</td>
	<td><?php echo $message['latitude'];?></td>
	
	
	</tr>
	<tr>
	<td>smile_id</td>
	<td><?php echo $message['smile_id'];?></td>
	
	<td>like_count</td>
	<td><?php echo $message['like_count'];?></td>
	</tr>
	<tr>
	<td>comment_count</td>
	<td><?php echo $message['comment_count'];?></td>
	
	<td>receive_count</td>
	<td><?php echo $message['receive_count'];?></td>
	</tr>
	<tr>
	<td>original_message_id</td>
	<td><?php echo $message['original_message_id'];?></td>
	
	<td>image_url</td>
	<td>
	<button id="img" type="button" class="btn btn-default"  data-toggle="popover" data-placement="right"  >
  点击查看
</button>
	</td>
	</tr>
	<tr>
	<td>text</td>
	<td><?php echo $message['text'];?></td>
	
	<td>new_comment</td>
	<td><?php echo $message['new_comment'];?></td>
	</tr>
	<tr>
	<td>new_like</td>
	<td><?php echo $message['new_like'];?></td>
	
	<td>new_time</td>
	<td><?php echo date('Y-m-d H:i:s',$message['new_time']);?></td>
	</tr>
	<tr>
	<td>active</td>
	<td><?php echo $message['active'];?></td>
	
	<td></td>
	<td></td>
	</tr>
</table>
</div>
<button type="submit" class="btn btn-default" onclick="del_message(<?php echo $message['message_id'];?>);">删除留言</button>
</div>
</div>
</div>



</div>
</body>
</html>
<script type="text/javascript">
 $(function ()
{ $("#img").popover({
  html: true,
  trigger: 'click',
  content: function () {
    return '<img src="<?php echo $message['image_url'];?>" width="100%"/>';
  }
});
});

<?php if(isset($message['voice_url'])) { ?>
var video;
 function play() {
	
	if(video) {
		
		if(video.paused) {
      video.play();
			$('#play').html('stop');
		}
		else {
      
			video.pause();
      $('#play').html('play');
		}
	} 
	else {
		
		video = document.createElement('audio');
  	video.setAttribute('src',"<?php echo $message['voice_url'];?>");
  	video.load();
  	video.play();
		video.addEventListener('ended', fini, false);
		$('#play').html('stop');
  }
}
function fini() {
	$('#play').html('play');
}
<?php } ?>
 
</script>