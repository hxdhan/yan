<?php
include ('../../header.php');

if(!check_login()) {
	$ret['ErrorMsg'] = '没有登录';
	exit (json_encode($ret));
}


if(isset($_POST['type']) && $_POST['type'] != '') {
	$type = $_POST['type'] + 0 ;
}

$result = array();

$memcache = memcache_connect($mem_host, $mem_port);

if(isset($type)) {
	if($result = memcache_get($memcache, 'category_' . $type)) {
	
	}
	else {
		if($get_categories = $mysqli->query("SELECT * FROM category where category_type = $type order by message_count desc")) {
			
			while($category = $get_categories->fetch_assoc()) {
						
				$result[] = $category;
			}
		}
		memcache_set($memcache, 'category_' . $type, $result);
	}

}

else {
	
	if($result = memcache_get($memcache, 'all_category')) {
	
	}
	else {
		if($get_categories = $mysqli->query("SELECT * FROM category order by message_count desc")) {
			
			while($category = $get_categories->fetch_assoc()) {
						
				$result[] = $category;
			}
		}
		memcache_set($memcache, 'all_category', $result);
	}
}


$ret['status'] = 1;
$ret['ErrorMsg'] = '';
$ret['categories'] = $result;


exit (json_encode($ret));
  
 
?>