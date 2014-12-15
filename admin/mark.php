<?php

if(!empty($_POST)) {
	if(empty($_POST['category']) || empty($_POST['wall']) ) {
		$error = 'error';
	}
	$category = $_POST['category'] + 0;
	$wall = $_POST['wall'] + 0;
	$desc = $_POST['desc'];
	
	
	$date_name = date('Ymd');
	$file_path = "../say/static/".$date_name;
	if(!file_exists($file_path)) {
		mkdir($file_path);
	}
	$file_url = '/say/static/'.$date_name.'/';
	$allowed_image = array('jpg' => 'image/jpeg','png' => 'image/png','gif' => 'image/gif');
	$finfo = new finfo(FILEINFO_MIME_TYPE);
	$image_url = '';
	
	if(!empty($_FILES['image']['name'])) {
		$ext = array_search($finfo->file($_FILES['image']['tmp_name']),$allowed_image);
		if($ext === false) {
		//error
		}	
		$file_name = microtime(true) * 10000 .'1.'.$ext;
		$file_path_name = $file_path."/".$file_name;
		rename($_FILES['image']['tmp_name'],$file_path_name);
	
		$image_url = $file_url . $file_name;
	}
	
	$status = 1;
	
	$mysqli = new mysqli('localhost', 'root', 'root', 'say');
	
	

	if ($mysqli->connect_errno) {
		print_r($mysqli->error);
	}

	if (!$mysqli->set_charset("utf8")) {
		print_r($mysqli->error);
	}
	if(!($stmt = $mysqli->prepare("insert into msgmark (category_id,wall_id,info,image_url,status) values (?,?,?,?,?) "))) {
		//error
		print_r($mysqli->error);
	}
	if(!($stmt->bind_param("iissi",$category,$wall,$desc,$image_url,$status))) {
		print_r($mysqli->error);
	}
	if (!$stmt->execute()) {
		print_r($mysqli->error);
	}
	$stmt->close();
	$mysqli->close();
	
	header('Location: /admin/mark.php');
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
<form role="form" action="mark.php" method="post" enctype="multipart/form-data">
<div class="form-group">
<label>类型ID：</label><input name="category" class="form-control" value=""/>
</div>

<div class="form-group">
<label>墙ID：</label><input name="wall" class="form-control" value=""/>
</div>

<div class="form-group">
<label>水印描述：</label><textarea name="desc" class="form-control"></textarea>
</div>

<div class="form-group">
<label>水印图: </label><input type="file" name="image" class="form-control"/>
</div>

 <button type="submit" class="btn btn-default">提交</button>
</form>
</div>
</div>
</div>



</div>
</body>
</html>
