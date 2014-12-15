<?php

$mysqli = new mysqli('localhost', 'root', 'root', 'say');

if ($mysqli->connect_errno) {
	print_r($mysqli->error);
}

if (!$mysqli->set_charset("utf8")) {
	print_r($mysqli->error);
}

if($get_allcount = $mysqli->query("select count(*) from usrchat")) {
	$all_count = $get_allcount->fetch_row()[0];
}

if($get_mc = $mysqli->query("select count(*) from usrchat c , userinfo u where c.user_id = u.user_id and u.gender = 'M'")) {
	$m_count = $get_mc->fetch_row()[0];
}

if($get_fc = $mysqli->query("select count(*) from usrchat c , userinfo u where c.user_id = u.user_id and u.gender = 'F'")) {
	$f_count = $get_fc->fetch_row()[0];
}

if($get_voice = $mysqli->query("select count(*) from usrchat where content_type = 1")) {
	$voice = $get_voice->fetch_row()[0];
}

if($get_text = $mysqli->query("select count(*) from usrchat where content_type = 0")) {
	$tet = $get_text->fetch_row()[0];
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
<td><?php echo $f_count;?></td>
</tr>
<tr>
<td>语音 </td>
<td><?php echo $voice;?></td>
<td>文字</td>
<td colspan="3"><?php echo $tet;?></td>
</tr>
</table>
</div>
</div>