<?php

if(isset($_POST['m'])  && $_POST['m'] == 'g') {
	if(empty($_POST['category_id'])) {
		$error = 'error';
	}
	$category_id = $_POST['category_id'] + 0;
	
	$mysqli = new mysqli('localhost', 'root', 'root', 'say');

	if ($mysqli->connect_errno) {
		print_r($mysqli->error);
	}

	if (!$mysqli->set_charset("utf8")) {
		print_r($mysqli->error);
	}
	if($get_category = $mysqli->query("select * from category where category_id = $category_id")) {
		$category = $get_category->fetch_assoc();
		//var_dump($category);
	}
	else {
		printf("%s",$mysqli->error);
	}
	$mysqli->close();
	
	//header('Location: /admin/index.php');
}

if(isset($_POST['m']) && $_POST['m'] == 'u') {
	//update category
	//var_dump($_POST);
	$category_id = $_POST['category_id'] + 0;
	
	if(!isset($_POST['category_name']) && $_POST['category_name'] == '') {
		$error = 'category_name 为空';
	}
	elseif (!isset($_POST['category_type']) && $_POST['category_type'] == '') {
		$error = 'category_type 为空';
	}
	elseif (!isset($_POST['category_desc']) && $_POST['category_desc'] == '') {
		$error = 'category_desc 为空'; 
	}
	else {
		$category_name = $_POST['category_name'];
		$category_type = $_POST['category_type'] + 0;
		$category_desc = $_POST['category_desc'];
		
		$param_type = '';
		$params = array(&$param_type);
		$sql_array = array();
		$sql_array[] = "category_name = ?";
		$param_type .= 's';
		$params[] = &$category_name;
		
		$sql_array[] = "category_type = ?";
		$param_type .= 'i';
		$params[] = &$category_type;
		
		$sql_array[] = "category_desc = ?";
		$param_type .= 's';
		$params[] = &$category_desc;
			
		$time = 0;
		if(isset($_POST['time']) && $_POST['time'] != '') {
			$time = strtotime($_POST['time']);
		}
		
		$sql_array[] = "time = ?";
		$param_type .= 'i';
		$params[] = &$time;
		
		$date_name = date('Ymd');
		$file_path = "../say/static/".$date_name;
		if(!file_exists($file_path)) {
			mkdir($file_path);
		}
		$file_url = '/say/static/'.$date_name.'/';
		$allowed_image = array('jpg' => 'image/jpeg','png' => 'image/png','gif' => 'image/gif');
		$finfo = new finfo(FILEINFO_MIME_TYPE);
		$image_url = '';
		$small_image_url = '';
		if(!empty($_FILES['image']['name'])) {
			$ext = array_search($finfo->file($_FILES['image']['tmp_name']),$allowed_image);
			if($ext === false) {
			//error
			}	
			$file_name = microtime(true) * 10000 .'1.'.$ext;
			$file_path_name = $file_path."/".$file_name;
			rename($_FILES['image']['tmp_name'],$file_path_name);
			$image_url = $file_url . $file_name;
			
			$sql_array[] = "image_url = ?";
			$param_type .= 's';
			$params[] = &$image_url;
		}
	
		if(!empty($_FILES['smallimg']['name'])) {
			$ext = array_search($finfo->file($_FILES['smallimg']['tmp_name']),$allowed_image);
			if($ext === false) {
			//error
			}	
			$file_name = microtime(true) * 10000 .'2.'.$ext;
			$file_path_name = $file_path."/".$file_name;
			rename($_FILES['smallimg']['tmp_name'],$file_path_name);
		
			$small_image_url = $file_url . $file_name;
			
			$sql_array[] = "small_imgurl = ?";
			$param_type .= 's';
			$params[] = &$small_image_url;
		}
		
		$category_id = $_POST['category_id'] + 0;
	
		$mysqli = new mysqli('localhost', 'root', 'root', 'say');

		if ($mysqli->connect_errno) {
			print_r($mysqli->error);
		}

		if (!$mysqli->set_charset("utf8")) {
			print_r($mysqli->error);
		}
		
		$sql = "update category set " . implode(',',$sql_array) . " where category_id = ?";
		$param_type .= 'i';
		$params[] = &$category_id;
		
		//var_dump($sql);
		//var_dump($params);
		
		
		if(!($stmt = $mysqli->prepare($sql))) {
			//printf('%s', $mysqli->error);
			$error = $mysqli->error;
 
		}

		call_user_func_array(array($stmt, 'bind_param'), $params);

		if (!$stmt->execute()) {
			$error = $stmt->error;
		}

		$stmt->close();
		$mysqli->close();
		//delete memcache info
		$memcache = memcache_connect('localhost', 11211);
		memcache_delete($memcache , 'all_category');
		memcache_delete($memcache , 'category_'.$category_type);
		
		if(empty($error)) {
			header('Location: /admin/ae.php');
		}
		
	}
	
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
<div class="alert alert-warning" role="alert"><?php echo $error;?></div>
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
<form role="form"  action="ae.php" method="post" >
<div class="input-group">

<input name="category_id" class="form-control " value="" placeholder="请输入Category ID"/>
<input type="hidden" name="m" value="g"/>
<span class="input-group-btn">
<button type="submit" class="btn btn-default">提交</button>
 </span>
</div>

</form>
<div class="panel panel-info">
<div class="panel-heading">活动内容</div>
<div class="panel-body">
<div class="col-md-3">
    <a href="#" class="thumbnail">
      <img src="<?php echo $category['image_url'];?>" >
    </a>
		<a href="#" class="thumbnail">
      <img src="<?php echo $category['small_imgurl'];?>" >
    </a>
  </div>
<div class="col-md-9">
<form role="form"  action="ae.php" method="post" enctype="multipart/form-data">
<table class="table ">


<tr>
<td>category_name</td>
<td><input class="form-control" name="category_name" value="<?php echo $category['category_name'];?>" /></td>
</tr>
<tr>
<td>category_type</td>
<td><input class="form-control" name="category_type" value="<?php echo $category['category_type'];?>" /></td>
</tr>
<tr>
<td>category_desc</td>
<td><input class="form-control" name="category_desc" value="<?php echo $category['category_desc'];?>" /></td>
</tr>
<tr>
<td>time</td>
<td><input class="form-control" id="date" name="time" value="<?php echo date('Y-m-d',$category['time']);?>" /></td>
</tr>
<tr>
<td>image</td>
<td><input class="form-control"  name="image" type="file"/></td>
</tr>
<tr>
<td>small image</td>
<td><input class="form-control" name="smallimg" type="file" /></td>
</tr>
<tr>
<td>message_count</td>
<td><input class="form-control" name="message_count" value="<?php echo $category['message_count'];?>" readonly/></td>
</tr>

</table>
<input type="hidden" name="category_id" value="<?php echo $category['category_id'];?>" />
<input type="hidden" name="m" value="u" />
<button type="submit" class="btn btn-default">修改</button>
</form>
</div>
</div>
</div>

</div>



</div>
</body>
</html>
<script type="text/javascript">
 $(function() {
    $( "#date" ).datepicker({ dateFormat: 'yy-mm-dd' });
  });
 
</script>