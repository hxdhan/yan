<?php
$mysqli = new mysqli('localhost', 'root', 'root', 'say');

if ($mysqli->connect_errno) {
	print_r($mysqli->error);
}

if (!$mysqli->set_charset("utf8")) {
	print_r($mysqli->error);
}

if($get_count = $mysqli->query("select count(*) from userencounter ")) {
	$all_count = $get_count->fetch_row()[0];
}

if($get_count = $mysqli->query("select count(*) from userencounter where status = 1 ")) {
	$count = $get_count->fetch_row()[0];
}
if($get_count = $mysqli->query("select count(distinct user_id) from userencounter where status = 1 ")) {
	$u_count = $get_count->fetch_row()[0];
}
?>
<div class="panel panel-info">
<div class="panel-heading">总数统计</div>
<div class="panel-body">
<table class="table">
<tr>
<td>总邂逅数</td>
<td><?php echo $all_count;?></td>
<td>总有效邂逅数</td>
<td><?php echo $count;?></td>
<td>总邂逅用户数</td>
<td><?php echo $u_count;?></td>

</tr>

</table>
</div>
</div>

<div class="panel panel-info">
<div class="panel-heading">按时间统计</div>
<div class="panel-body">
<table class="table">
<tr>
<td>
开始时间：
</td>
<td>
<input type="text" class="form-control " id="e_start" name="start" />
</td>
<td>
结束时间：
</td>

<td>
<input type="text" class="form-control " id="e_end" name="end" />
</td>
<td>
<button type="button" class="btn btn-default" onclick="get_enc_stat()">提交</button>
</td>
</tr>
<tr>
<td>邂逅人数</td>
<td id="u_end_count" colspan="4"></td>
</tr>

</table>
</div>
</div>
<script type="text/javascript">
$(function() {
	$( "#e_start" ).datepicker({ dateFormat: 'yy-mm-dd' });
	$( "#e_end" ).datepicker({ dateFormat: 'yy-mm-dd' });
	
});

function get_enc_stat() {
  if($('#e_start').val() == '' || $('#e_end').val() == '') {
		$('.alert').addClass('alert-warning');
		$('.alert').removeClass('alert-info');
		
		$('.alert-warning').html("请输入参数");
		
		return;
	}
	
	$.post("get_enc_count.php",
  {
    start:$('#e_start').val(),
		end:$('#e_end').val(),
	
  },
	function(data,status){
		
    if(data == -1) {
			$('.alert-info').html("发生错误");
		}
		else {
			$('#u_end_count').html(data);
		}
  }).fail(function(data,status){
		$('.alert-info').html("发生错误");
		//alert(data);
	});
	
}

</script>