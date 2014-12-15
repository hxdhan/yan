<?php



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
<body >
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

<ul class="nav nav-tabs" role="tablist" >
					<li  class="active"><a href="stat_message.php" id="def" data-toggle="tab" data-target="#stat_message">留言</a></li>
					<li ><a href="stat_user.php" data-toggle="tab" data-target="#stat_user">用户</a></li>
					<li ><a href="stat_chat.php" data-toggle="tab"  data-target="#stat_chat">聊聊</a></li>
					<li ><a href="stat_enc.php" data-toggle="tab"   data-target="#stat_enc">邂逅</a></li>
				
</ul>

	<div class="tab-content">
		<div id="stat_message" class="tab-pane active">
		
		</div>
		<div id="stat_user" class="tab-pane">
		
		</div>
		<div id="stat_chat" class="tab-pane">
		
		</div>
		<div id="stat_enc" class="tab-pane">
		
		</div>
	</div>
</div>	

</div>



</div>
</body>
</html>
<script type="text/javascript">

var ld = $('#def').attr('href');

        var t  = $('#def').attr('data-target');
				
		
    $.get(ld, function(data) {
				 
        $(t).html(data);
    });

    
$('[data-toggle="tab"]').click(function(e) {
       var $this = $(this),
        loadurl = $this.attr('href'),
        targ = $this.attr('data-target');
		
    $.get(loadurl, function(data) {
				
        $(targ).html(data);
    });

    $this.tab('show');
    return false;
});
</script>