<?php
$data = "a=b";
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, "http://121.199.36.8/say/index.php");
curl_setopt($curl, CURLOPT_HEADER, false);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_NOBODY, true);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
//curl_exec($curl);
$mh = curl_multi_init();
curl_multi_add_handle($mh,$curl);
$running = 'idc';
curl_multi_exec($mh,$running);