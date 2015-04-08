<?php

if(isset($_POST['m'])  && $_POST['m'] == 'g') {
	if(empty($_POST['wall_id'])) {
		$error = 'error';
	}
	$wall_id = $_POST['wall_id'] + 0;
	
	$mysqli = new mysqli('localhost', 'root', 'root', 'say');

	if ($mysqli->connect_errno) {
		print_r($mysqli->error);
	}

	if (!$mysqli->set_charset("utf8")) {
		print_r($mysqli->error);
	}
	if($get_wall = $mysqli->query("select * from msgwallsignininfo where wall_id = $wall_id")) {
		$wall = $get_wall->fetch_assoc();
		//var_dump($category);
	}
	else {
		printf("%s",$mysqli->error);
	}
	$mysqli->close();
	
	//header('Location: /admin/index.php');
}

if(isset($_POST['m']) && $_POST['m'] == 'u') {
	
		$wall_id = $_POST['wall_id'] + 0;
		$success_score = $_POST['success_score'] + 0;
		$fail_score = $_POST['fail_score'] + 0;
		$fail_word = $_POST['fail_word'];
		$start_time = strtotime($_POST['start_time']);
		
		$end_time = strtotime($_POST['end_time'].' 23:59:59');
	
	
		$mysqli = new mysqli('localhost', 'root', 'root', 'say');

		if ($mysqli->connect_errno) {
			print_r($mysqli->error);
		}

		if (!$mysqli->set_charset("utf8")) {
			print_r($mysqli->error);
		}
		if(!($stmt = $mysqli->prepare("update msgwallsignininfo set SuccessScore = ?, FailScore = ?, FailWord = ?, StartTime = ?, EndTime = ? where wall_id = ? "))) {
			print_r($mysqli->error);
		}
		if(!($stmt->bind_param("iisiii",$success_score, $fail_score, $fail_word, $start_time, $end_time, $wall_id))) {
			print_r($mysqli->error);
		}
		if (!$stmt->execute()) {
			print_r($mysqli->error);
		}
		$stmt->close();
		
		header('Location: /admin/edaka.php');
		
		
	
	
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
<form role="form"  action="edaka.php" method="post" >
<div class="input-group">

<input name="wall_id" class="form-control " value="" placeholder="请输入Wall ID"/>
<input type="hidden" name="m" value="g"/>
<span class="input-group-btn">
<button type="submit" class="btn btn-default">提交</button>
 </span>
</div>

</form>
<div class="panel panel-info">
<div class="panel-heading">打卡内容</div>
<div class="panel-body">


<form role="form"  action="edaka.php" method="post" enctype="multipart/form-data">
<table class="table ">


<tr>
<td>SuccessScore</td>
<td><input class="form-control" name="success_score" value="<?php echo $wall['SuccessScore'];?>" /></td>
</tr>
<tr>
<td>FailScore</td>
<td><input class="form-control" name="fail_score" value="<?php echo $wall['FailScore'];?>" /></td>
</tr>
<tr>
<td>FailWord</td>
<td><input class="form-control" name="fail_word" value="<?php echo $wall['FailWord'];?>" /></td>
</tr>

<tr>
<td>start_time</td>
<td><input class="form-control" id="date" name="start_time" value="<?php echo date('Y-m-d',$wall['StartTime']);?>" /></td>
</tr>

<tr>
<td>end_time</td>
<td><input class="form-control" id="date1" name="end_time" value="<?php echo date('Y-m-d',$wall['EndTime']);?>" /></td>
</tr>

</table>
<input type="hidden" name="wall_id" value="<?php echo $wall['wall_id'];?>" />
<input type="hidden" name="m" value="u" />
<button type="submit" class="btn btn-default">修改</button>
</form>

</div>
</div>

</div>



</div>
</body>
</html>
<script type="text/javascript">
 $(function() {
    $( "#date" ).datepicker({ dateFormat: 'yy-mm-dd' });
		$( "#date1" ).datepicker({ dateFormat: 'yy-mm-dd' });
  });
 
</script>