<?php

if(!empty($_POST)) {
	
	if(empty($_POST['wall_id']) || empty($_POST['success_score']) ) {
		$error = 'error';
	}
	else {
		$wall_id = $_POST['wall_id'] + 0;
		$success_score = $_POST['success_score'] + 0;
		$fail_score = $_POST['fail_score'] + 0;
		$fail_word = $_POST['fail_word'];
		$start_time = strtotime($_POST['start_time']);
		
		$end_time = strtotime($_POST['end_time'].' 23:59:59');
		
		$start_hour = $_POST['start_hour'];
		$start_minute = $_POST['start_minute'];
		
		$end_hour = $_POST['end_hour'];
		$end_minute = $_POST['end_minute'];
		$mysqli = new mysqli('localhost', 'root', 'root', 'say');

		if ($mysqli->connect_errno) {
			print_r($mysqli->error);
		}

		if (!$mysqli->set_charset("utf8")) {
			print_r($mysqli->error);
		}
		
		if(!($stmt = $mysqli->prepare("insert into msgwallsignininfo (wall_id,SuccessScore,FailScore,FailWord,StartTime,EndTime) values (?,?,?,?,?,?) "))) {
			print_r($mysqli->error);
		}
		if(!($stmt->bind_param("iiisii",$wall_id,$success_score,$fail_score,$fail_word,$start_time,$end_time))) {
			print_r($mysqli->error);
		}
		if (!$stmt->execute()) {
			print_r($mysqli->error);
		}
		$stmt->close();
		
		if(!($stmt = $mysqli->prepare("insert into msgwallsignintime (wall_id,SignInTimeFromHour,SignInTimeFromMinute,SignInTimeToHour,SignInTimeToMinute) values (?,?,?,?,?) "))) {
			print_r($mysqli->error);
		}
		for ($index=0,$count=count($start_hour); $index < $count; $index++) {
			
			if(!($stmt->bind_param("iiiii",$wall_id,$start_hour[$index],$start_minute[$index],$end_hour[$index],$end_minute[$index]))) {
				print_r($mysqli->error);
			}
			if (!$stmt->execute()) {
				print_r($mysqli->error);
			}
		
		}
		$stmt->close();
		
		header('Location: /admin/daka.php');
		
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
<form role="form" action="daka.php" method="post" enctype="multipart/form-data">
<div class="form-group">
<label>Wall Id：</label><input name="wall_id" class="form-control" value=""/>
</div>

<div class="form-group">
<label>SuccessScore：</label><input name="success_score" class="form-control"></input>
</div>
<div class="form-group">
<label>FailScore：</label><input name="fail_score" class="form-control"></input>
</div>
<div class="form-group">
<label>开始时间：</label>

<input name="start_time" id = "datepicker1" class="form-control" value=""/>
</div>
<div class="form-group">
<label>结束时间：</label>

<input name="end_time" id = "datepicker2" class="form-control" value=""/>
</div>

<div class="form-group">
<label>Failword：</label><input name="fail_word" class="form-control"></input>
</div>

<div id="con" class="form-inline">
<div class="entry">
<div class="form-group">

<label>签到开始时间：</label>

<div class="form-group">
<input name="start_hour[]"  class="form-control" value=""/>
</div>
<label>
时
</label>
<div class="form-group">
<input name="start_minute[]"  class="form-control" value=""/>
</div>
<label>
分
</label>

</div>
<div class="form-group">

<label>签到结束时间：</label>

<div class="form-group">
<input name="end_hour[]"  class="form-control" value=""/>
</div>
<label>
时
</label>
<div class="form-group">
<input name="end_minute[]"  class="form-control" value=""/>
</div>
<label>
分
</label>

<span class="input-group-btn">
<button id="add" class="btn btn-success btn-add" type ="button">
<span class="glyphicon glyphicon-plus"></span>
</button>
</span>

</div>
</div>
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
    $( "#datepicker1" ).datepicker({ dateFormat: 'yy-mm-dd' });
		$( "#datepicker2" ).datepicker({ dateFormat: 'yy-mm-dd' });
  });
 $(function () {
		$(document).on('click', '#add', function(e) {
			e.preventDefault();
			var controlForm = $('#con:first'),
			currentEntry = $(this).parents('.entry:first'),
			newEntry = $(currentEntry.clone()).appendTo(controlForm);
			
			controlForm.find('.entry:not(:last) .btn-add').removeClass('btn-add').addClass('btn-remove').attr("id", "remove").removeClass('btn-success').addClass('btn-danger').html('<span class="glyphicon glyphicon-minus"></span>');
		}).on('click','#remove', function(e) {
			$(this).parents('.entry:first').remove();
			e.preventDefault();
			return false;
		});
 });
</script>