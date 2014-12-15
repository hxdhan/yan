<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns:wb="http://open.weibo.com/wb">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />

<title>遇言</title>
<link href="css/style.css" type="text/css" rel="stylesheet" />
<script type="text/javascript" src="http://lib.sinaapp.com/js/jquery/2.0.3/jquery-2.0.3.min.js"></script>
</head>
<body >

<div class="header">

<img id="yw" src="images/yw.png"/>

<img id="icon-yw" src="images/icon.png"/>

</div>
<div class="yuyan-yw">
<img id="yuyan" src="images/yuyan.png"/>
</div>
<div class="faxian-yw">
<img id="faxian" src="images/faxian.png"/>
</div>

<div class="iphone-yw">
<div id="pop-up" style="position:absolute;margin-left:16%;display:none"><img src="images/qrcode.png"/></div>
<a href="https://itunes.apple.com/cn/app/id895694369?l=zh&mt=8"><img id="iphone" src="images/iphone.png"/></a>

<img id="android" src="images/android.png"/>
</div>

<div class="guanzhu">
<img id="guanzhu" src="images/guanzhu.png"/>
<a href="http://t.qq.com/yuyanapp"><img id="qq" src="images/qq.png"/></a>
<a href="http://weibo.com/yuyanapp"><img id="sina" src="images/sina.png"/></a>
</div>


<div class="footer" >
Copyright © 2014 <a href="http://www.1bu2bu.com" style="color:#00baff">北京一步两步科技有限公司</a> 版权所有
<br/><br/>
<a href="http://www.iyuyan.cn/terms" style="color:#00baff">服务条款</a> | <a href="http://www.miibeian.gov.cn" style="color:#00baff">京ICP备10037622号-4 </a>
</div>

</body>
</html>
<script type="text/javascript">
$(document).ready(function(){

$("#iphone").hover(function(){
													
                           $("#pop-up").show();
													 //$("#pop-up").css('top', e.pageY ).css('left', e.pageX );
                         },function(){
                           $("#pop-up").hide();
                         }
                                                 );
																								 });
</script>
