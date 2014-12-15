<?php

handle_image_file('static');

function handle_image_file($dir) {
	if ($handle = opendir($dir)) { 
		while (false !== ($file = readdir($handle))) { 
			if ($file != "." && $file != ".." && $file != "categories") { 
				if(is_dir($dir.'/'.$file)) { 
					$dir2 = $dir.'/'.$file;
					
					handle_image_file($dir2);
				}
				else {
					if(preg_match('/\.jpg/',$file) && !preg_match('/small/',$file) ) {
						$from = $dir.'/'.$file;
						//var_dump($from);
						$to = $dir.'/'.preg_replace('/\.jpg/','_small.jpg',$file);
						//var_dump($to);
						half_image($from, $to);
					}
				}
			}
		}
		
		closedir($handle); 
	}
}

function half_image($from_path, $to_path) {
	$percent = 0.5;
	list($width, $height) = getimagesize($from_path);
	$newwidth = $width * $percent;
	$newheight = $height * $percent;
	$thumb = imagecreatetruecolor($newwidth, $newheight);
	$source = imagecreatefromjpeg($from_path);
	imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
	imagejpeg($thumb,$to_path);
}