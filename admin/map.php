<?php
	if(!empty($_POST['start']) && !empty($_POST['end'])) {
		$start = strtotime($_POST['start']);
		$end = strtotime($_POST['end']);
		$mysqli = new mysqli('localhost', 'root', 'root', 'say');

		if ($mysqli->connect_errno) {
			print_r($mysqli->error);
		}

		if (!$mysqli->set_charset("utf8")) {
			print_r($mysqli->error);
		}
		$locs = array();
		
		if($get_loc = $mysqli->query("select longitude, latitude from message where time between $start and $end ")) {
			while($l = $get_loc->fetch_row()) {
				$locs[] = $l;
			}
		}else {
			printf("%s",$mysqli->error);
		}
		
		//var_dump($locs);
		
	}
?>
<html>
<head>
<script language="javascript" src="http://webapi.amap.com/maps?v=1.3&key=f894742f77849b60adfbfa8b48706898"></script>
<link rel="stylesheet" type="text/css" href="http://developer.amap.com/Public/css/demo.Default.css" />
</head>
<body onLoad="mapInit()">
<div id="iCenter"></div>
</body>
</html>
<script type="text/javascript">
var mapObj,marker;
//初始化地图对象，加载地图
function mapInit(){
	mapObj = new AMap.Map("iCenter",{//二维地图显示视口
		view: new AMap.View2D({
			//center:new AMap.LngLat(116.405467,39.907761),//地图中心点
			zoom:13 //地图显示的缩放级别
		})
	});	
	mapObj.plugin(["AMap.ToolBar"],function() {		
		toolBar = new AMap.ToolBar(); 
		mapObj.addControl(toolBar);		
	});
	addMarker();
	mapObj.setFitView();
}

function addMarker(){
	<?php
		
		foreach($locs as $l) {
	?>
	marker = new AMap.Marker({				  
		icon:"http://webapi.amap.com/images/marker_sprite.png",
		position:new AMap.LngLat(<?php echo $l[0];?>,<?php echo $l[1];?>)
	});
	marker.setMap(mapObj);  //在地图上添加点
	<?php
		
		}
	?>
}


</script>