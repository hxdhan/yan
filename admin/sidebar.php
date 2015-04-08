<?php

$req_string = $_SERVER['REQUEST_URI'];

//echo $req_string;

echo '<div id="sidebar" class="col-sm-2">';
echo '<div class="list-group">';
if (preg_match('#^/admin/$#',$req_string) || preg_match('#^/admin/index#',$req_string)) {
	echo '<a href="#" class="list-group-item active">活动页面</a>';
}
else {
	echo '<a href="index.php" class="list-group-item">活动页面</a>';
}
if (preg_match('#^/admin/ae#',$req_string)) {
	echo '<a href="#" class="list-group-item active">活动修改</a>';
}
else {
	echo '<a href="ae.php" class="list-group-item">活动修改</a>';
}

if (preg_match('#^/admin/user#',$req_string)) {
	echo '<a href="#" class="list-group-item active">用户管理</a>';
}
else {
	echo '<a href="user.php" class="list-group-item">用户管理</a>';
}

if (preg_match('#^/admin/message#',$req_string)) {
	echo '<a href="#" class="list-group-item active">留言管理</a>';
}
else {
	echo '<a href="message.php" class="list-group-item">留言管理</a>';
}

if (preg_match('#^/admin/stat#',$req_string)) {
	echo '<a href="#" class="list-group-item active">统计页面</a>';
}
else {
	echo '<a href="stat.php" class="list-group-item">统计页面</a>';
}

if (preg_match('#^/admin/mark#',$req_string)) {
	echo '<a href="#" class="list-group-item active">增加水印</a>';
}
else {
	echo '<a href="mark.php" class="list-group-item">增加水印</a>';
}

if (preg_match('#^/admin/hongbao#',$req_string)) {
	echo '<a href="#" class="list-group-item active">增加阿里红包</a>';
}
else {
	echo '<a href="hongbao.php" class="list-group-item">增加阿里红包</a>';
}

if (preg_match('#^/admin/daka#',$req_string)) {
	echo '<a href="#" class="list-group-item active">打卡墙</a>';
}
else {
	echo '<a href="daka.php" class="list-group-item">打卡墙</a>';
}
if (preg_match('#^/admin/edaka#',$req_string)) {
	echo '<a href="#" class="list-group-item active">修改打卡墙</a>';
}
else {
	echo '<a href="edaka.php" class="list-group-item">修改打卡墙</a>';
}

echo '</div>';
echo '</div>';

