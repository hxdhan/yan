<?php

if(!empty($_POST)) {
	if(empty($_POST['category_name']) || empty($_POST['category_desc']) || empty($_POST['category_time']) ) {
		$error = 'error';
	}
	$category_name = $_POST['category_name'];
	$category_type = $_POST['category_type'];
	$category_desc = $_POST['category_desc'];
	$category_time = $_POST['category_time'];
	
	$time = strtotime($category_time);
	//echo $time;
	
	$date_name = date('Ymd');
	$file_path = "../say/static/".$date_name;
	if(!file_exists($file_path)) {
		mkdir($file_path);
	}
	$file_url = '/say/static/'.$date_name.'/';
	$allowed_image = array('jpg' => 'image/jpeg','png' => 'image/png','gif' => 'image/gif');
	$finfo = new finfo(FILEINFO_MIME_TYPE);
	$big_image_url = '';
	$small_image_url = '';
	
	
	
	if(!empty($_FILES['big_image']['name'])) {
		$ext = array_search($finfo->file($_FILES['big_image']['tmp_name']),$allowed_image);
		if($ext === false) {
		//error
		}	
		$file_name = microtime(true) * 10000 .'1.'.$ext;
		$file_path_name = $file_path."/".$file_name;
		rename($_FILES['big_image']['tmp_name'],$file_path_name);
	
		$big_image_url = $file_url . $file_name;
	}
	
	if(!empty($_FILES['small_image']['name'])) {
		$ext = array_search($finfo->file($_FILES['small_image']['tmp_name']),$allowed_image);
		if($ext === false) {
		//error
		}	
		$file_name = microtime(true) * 10000 .'2.'.$ext;
		$file_path_name = $file_path."/".$file_name;
		rename($_FILES['small_image']['tmp_name'],$file_path_name);
	
		$small_image_url = $file_url . $file_name;
	}
	
	if(!empty($_FILES['zip']['name'])) {
		$zip = new ZipArchive;
		$res = $zip->open($_FILES['zip']['tmp_name']);
		if ($res === TRUE) {
			 $zip->extractTo('../event/');
			 $zip->close();
		}
	}
	
	$mysqli = new mysqli('localhost', 'root', 'root', 'say');
	
	

	if ($mysqli->connect_errno) {
		print_r($mysqli->error);
	}

	if (!$mysqli->set_charset("utf8")) {
		print_r($mysqli->error);
	}
	if(!($stmt = $mysqli->prepare("insert into category (category_name,category_type,category_desc,image_url,small_imgurl,time) values (?,?,?,?,?,?) "))) {
		//error
		print_r($mysqli->error);
	}
	if(!($stmt->bind_param("sisssi",$category_name,$category_type,$category_desc,$big_image_url,$small_image_url,$time))) {
		print_r($mysqli->error);
	}
	if (!$stmt->execute()) {
		print_r($mysqli->error);
	}
	$stmt->close();
	$mysqli->close();
	
	//delete memcache info
	$memcache = memcache_connect('localhost', 11211);
	memcache_delete($memcache , 'all_category');
	memcache_delete($memcache , 'category_'.$category_type);
	
	header('Location: /admin/index.php');
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
<form role="form" action="index.php" method="post" enctype="multipart/form-data">
<div class="form-group">
<label>活动名称：</label><input name="category_name" class="form-control" value=""/>
</div>
<div class="form-group">
<label>活动类型：</label><select name="category_type" class="form-control"><option value="0">0</option><option value="1">1</option><option value="2">2</option><option value="3">3</option></select>
</div>
<div class="form-group">
<label>活动描述：</label><textarea name="category_desc" class="form-control"></textarea>
</div>
<div class="form-group">
<label>活动时间：</label>

<input name="category_time" id = "datepicker" class="form-control" value=""/>
</div>
<div class="form-group">
<label>活动大图: </label><input type="file" name="big_image" class="form-control"/>
</div>
<div class="form-group">
<label>活动小图：</label><input type="file" name="small_image" class="form-control" />
</div>
<div class="form-group">
<label>活动zip文件：</label><input type="file" name="zip" class="form-control" />
</div>
 <button type="submit" class="btn btn-default">提交</button>
</form>
</div>
</div>
</div>



</div>
</body>
</html>
<script type="text/javascript">
 $(function() {
    $( "#datepicker" ).datepicker({ dateFormat: 'yy-mm-dd' });
  });
 
</script>