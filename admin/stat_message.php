<?php
	$mysqli = new mysqli('localhost', 'root', 'root', 'say');

	if ($mysqli->connect_errno) {
		print_r($mysqli->error);
	}

	if (!$mysqli->set_charset("utf8")) {
		print_r($mysqli->error);
	}
	
	if($get_allcount = $mysqli->query("select count(*) from message")) {
		$all_count = $get_allcount->fetch_row()[0];
	}
	
	if($get_dura = $mysqli->query("select sum(duration) from message")) {
		$sum_dura = $get_dura->fetch_row()[0];
	}
?>

<div class="panel panel-info">
<div class="panel-heading">总数统计</div>
<div class="panel-body">
<table class="table">
<tr>
<td>总留言数</td>
<td><?php echo $all_count;?></td>
<td>平均留言时长</td>
<td><?php echo $sum_dura/$all_count;?></td>

</tr>
</table>
</div>
</div>

<div class="panel panel-info">
<div class="panel-heading">按条件统计</div>
<div class="panel-body">
<table class="table">
<tr>
<td>
开始时间：
</td>
<td>
<input type="text" class="form-control " id="start" name="start" />
</td>
<td>
结束时间：
</td>

<td>
<input type="text" class="form-control " id="end" name="end" />
</td>
</tr>
<tr>
<td>
性别：
</td>
<td>
<select id="gender" name="gender" class="form-control ">
<option value="A">全部</option>
<option value="M">男</option>
<option value="F">女</option>
</select>
</td>
<td>
年龄段：
</td>
<td>
<select id="duan" name="duan" class="form-control ">
<option value="-1">全部</option>
<option value="0">18岁以下</option>
<option value="1">19~22</option>
<option value="2">23~26</option>
<option value="3">27~30</option>
<option value="4">31~35</option>
<option value="5">36~40</option>
<option value="6">40岁以上</option>
</select>
</td>
</tr>
<tr>
<td>
注册类别：
</td>
<td>
<select id="leib" name="leib" class="form-control ">
<option value="1">全部</option>
<option value="2">手机号</option>
<option value="3">QQ</option>
<option value="4">微信</option>
</select>
</td>
<td>
<button type="submit" class="btn btn-default" onclick="get_message();">提交</button>
</td>
</tr>
<tr>
<td>
查询结果：<span id="result"></span>   个
</td>
<td>
</td>
</table>
</div>
</div>

<div class="panel panel-info">
<div class="panel-heading">地图显示</div>
<div class="panel-body">
<form role="form" action="map.php" target="map" method="post">
<table class="table">
<tr>
<td>
开始时间：
</td>
<td>
<input type="text" class="form-control " id="datepicker3" name="start" />
</td>
<td>
结束时间：
</td>

<td>
<input type="text" class="form-control " id="datepicker4" name="end" />
</td>
<td>
<button type="submit" class="btn btn-default" >提交</button>
</td>
</tr>
</table>
</form>
<iframe name="map" src ="map.php" width="100%" height="420" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>
</div>
</div>

<script type="text/javascript">



$(function() {
	$( "#start" ).datepicker({ dateFormat: 'yy-mm-dd' });
	$( "#end" ).datepicker({ dateFormat: 'yy-mm-dd' });
	$( "#datepicker3" ).datepicker({ dateFormat: 'yy-mm-dd' });
	$( "#datepicker4" ).datepicker({ dateFormat: 'yy-mm-dd' });
});

function get_message() {
	if($('#start').val() == '' || $('#end').val() == '') {
		$('.alert-info').addClass('alert-warning');
		$('.alert-info').removeClass('alert-info');
		
		$('.alert-warning').html("请输入参数");
		
		return;
	}
	$.post("get_message_count.php",
  {
    start:$('#start').val(),
		end:$('#end').val(),
		gender:$('#gender').val(),
		duan:$('#duan').val(),
		leib:$('#leib').val(),
    
  },
	function(data,status){
		$('#result').html(data);
	});
}


</script>