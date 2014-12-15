<?php
	$mysqli = new mysqli('localhost', 'root', 'root', 'say');

	if ($mysqli->connect_errno) {
		print_r($mysqli->error);
	}

	if (!$mysqli->set_charset("utf8")) {
		print_r($mysqli->error);
	}
	
	if($get_allcount = $mysqli->query("select count(*) from user")) {
		$all_count = $get_allcount->fetch_row()[0];
	}
	
	if($get_male = $mysqli->query("select count(*) from userinfo where gender = 'M'")) {
		$m_count = $get_male->fetch_row()[0];
	}
	
	
?>
<div class="panel panel-info">
<div class="panel-heading">总数统计</div>
<div class="panel-body">
<table class="table">
<tr>
<td>总用户数</td>
<td><?php echo $all_count;?></td>
<td>男用户数</td>
<td><?php echo $m_count;?></td>
<td>女用户数</td>
<td><?php echo $all_count - $m_count;?></td>
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
<input  type="text" class="form-control " id="start1" name="start" />
</td>
<td>
结束时间：
</td>

<td>
<input type="text" class="form-control " id="end1" name="end" />
</td>
</tr>
<tr>
<td>
性别：
</td>
<td>
<select  id="gender1" name="gender" class="form-control ">
<option value="A">全部</option>
<option value="M">男</option>
<option value="F">女</option>
</select>
</td>
<td>
年龄段：
</td>
<td>
<select id="duan1" name="duan" class="form-control ">
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
<select id="leib1" name="leib" class="form-control ">
<option value="1">全部</option>
<option value="2">手机号</option>
<option value="3">QQ</option>
<option value="4">微信</option>
</select>
</td>
<td>
<button type="submit" class="btn btn-default" onclick="get_user();">提交</button>
</td>
</tr>
<tr>
<td>
查询结果：<span id="result1"></span>   个
</td>
<td>
</td>
</table>
</div>
</div>

<div class="panel panel-info">
<div class="panel-heading">"从未"统计</div>
<div class="panel-body">
<table class="table">
<tr>
<td><button type="submit" class="btn btn-default" onclick="get_desc();">签名为空</button>
 </td>
 <td id="desc" colspan="9"></td>
</tr>
<tr > 
</td>
<td><button type="submit" class="btn btn-default" onclick="get_birth();">默认生日</button>
</td>
 <td id="birth" colspan="9"></td>

</tr>

<tr> 
</td>
<td><button type="submit" class="btn btn-default" onclick="get_act();">各种从未</button>
</td>

	<td>从未发言</td>
 <td id="act_message"></td>
 <td>从未聊天</td>
 <td id="act_chat"></td>
 <td>从未点赞</td>
 <td id="act_like"></td>
 <td>从未评论</td>
 <td id="act_comment"></td>

</tr>
</table>
</div>
</div>

<div class="panel panel-info">
<div class="panel-heading">分时段统计</div>
<div class="panel-body">
<table class="table">
<tr>
<td>
开始时间：
</td>
<td>
<input type="text" class="form-control " id="u_start" name="start" />
</td>
<td>
结束时间：
</td>

<td>
<input type="text" class="form-control " id="u_end" name="end" />
</td>
<td>
<button type="button" class="btn btn-default" onclick="get_time_stat()">提交</button>
</td>
</tr>
<tr>
<td>留言人数</td>
<td id="u_message_count" colspan="4"></td>
</tr>
<tr>
<td>评论人数</td>
<td id="u_comment_count" colspan="4"></td>
</tr>
<tr>
<td>点赞人数</td>
<td id="u_like_count" colspan="4"></td>
</tr>
<tr>
<td>聊天人数</td>
<td id="u_chat_count" colspan="4"></td>
</tr>
</table>
</div>
</div>

<script type="text/javascript">
$(function() {
	$( "#u_start" ).datepicker({ dateFormat: 'yy-mm-dd' });
	$( "#u_end" ).datepicker({ dateFormat: 'yy-mm-dd' });
	$( "#start1" ).datepicker({ dateFormat: 'yy-mm-dd' });
	$( "#end1" ).datepicker({ dateFormat: 'yy-mm-dd' });
});

function get_user() {
  if($('#start1').val() == '' || $('#end1').val() == '') {
		$('.alert').addClass('alert-warning');
		$('.alert').removeClass('alert-info');
		
		$('.alert-warning').html("请输入参数");
		
		return;
	}
	
	$.post("get_user_count.php",
  {
    start:$('#start1').val(),
		end:$('#end1').val(),
		gender:$('#gender1').val(),
		duan:$('#duan1').val(),
		leib:$('#leib1').val(),
    
  },
	function(data,status){
		
    if(data == -1) {
			$('.alert-info').html("发生错误");
		}
		else {
			$('#result1').html(data);
		}
  }).fail(function(data,status){
		$('.alert-info').html("发生错误");
		//alert(data);
	});
	
}

function get_desc() {
	$.get("get_user_desc.php",function(data,status) {$('#desc').html(data);});
	
}

function get_birth() {
	$.get("get_user_birth.php",function(data,status) {$('#birth').html(data);});
	
}

function get_time_stat() {
	if($('#u_start').val() == '' || $('#u_end').val() == '') {
		$('.alert').addClass('alert-warning');
		$('.alert').removeClass('alert-info');
		
		$('.alert-warning').html("请输入参数");
		
		return;
	}
	
	$.post("get_time_count.php",
  {
    start:$('#u_start').val(),
		end:$('#u_end').val(),
		
    
  },
	function(data,status){
		
    if(data == -1) {
			$('.alert-info').html("发生错误");
		}
		else {
			var d = $.parseJSON(data);
			$('#u_message_count').html(d.message_count);
			$('#u_comment_count').html(d.comment_count);
			$('#u_like_count').html(d.like_count);
			$('#u_chat_count').html(d.chat_count);
		}
  }).fail(function(data,status){
		$('.alert-info').html("发生错误");
		//alert(data);
	});
}

function get_act() {
		$.get("get_act_count.php",function(data,status) {
			var dd = $.parseJSON(data);
			$('#act_message').html(dd.message_count);
			$('#act_comment').html(dd.comment_count);
			$('#act_like').html(dd.like_count);
			$('#act_chat').html(dd.chat_count);
		});
}

</script>
