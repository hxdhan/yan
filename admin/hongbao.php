<?php

if(!empty($_FILES)) {
	
	$allowed_file = array('txt' => 'text/plain');
	$finfo = new finfo(FILEINFO_MIME_TYPE);
	
	
	if(!empty($_FILES['image']['name'])) {
		
		
		$ext = array_search($finfo->file($_FILES['image']['tmp_name']),$allowed_file);
		if($ext === false) {
		  //error
		}	
		$hongbaos = array();
		$file = new SplFileObject($_FILES['image']['tmp_name']);
		while (!$file->eof()) {
				$hongbaos[] = $file->fgets();
		}
		
		$file = null;
		//var_dump($hongbaos);
		//$file_name = microtime(true) * 10000 .'1.'.$ext;
		//$file_path_name = $file_path."/".$file_name;
		//rename($_FILES['image']['tmp_name'],$file_path_name);
	
		//$image_url = $file_url . $file_name;
	}
	
	//$status = 1;
	
	 $mysqli = new mysqli('localhost', 'root', 'root', 'say');
	
	

	 if ($mysqli->connect_errno) {
		print_r($mysqli->error);
	 }

	 if (!$mysqli->set_charset("utf8")) {
		 print_r($mysqli->error);
	 }
	 if(!($stmt = $mysqli->prepare("insert into eventredenvelope (AliLuckyMoneyCode,CreateTime) values (?,?) "))) {
		//error
		 print_r($mysqli->error);
	 }
	 $t = time();
	 
	 
	 
	 foreach ($hongbaos as $hongbao) {
		 if(!empty($hongbao)) {
			 if(!($stmt->bind_param("si",$hongbao, $t))) {
				 print_r($mysqli->error);
			 }
			 if (!$stmt->execute()) {
				 print_r($mysqli->error);
			 }
		 }
	 }
	 $stmt->close();
	 $mysqli->close();
	
	header('Location: /admin/hongbao.php');
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
<form role="form" action="hongbao.php" method="post" enctype="multipart/form-data">
<div class="form-group">
<label>红包文件: </label><input type="file" name="image" class="form-control"/>
txt文件，一行一个记录，不要一行重复，行数不要太多 ，几百行就行，不要几千行
</div>

 <button type="submit" class="btn btn-default">提交</button>
</form>
</div>
</div>
</div>



</div>
</body>
</html>
