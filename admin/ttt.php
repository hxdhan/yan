<?php

$mysqli = new mysqli('localhost', 'root', 'root', 'say');
	
if ($mysqli->connect_errno) {
print_r($mysqli->error);
}

if (!$mysqli->set_charset("utf8")) {
 print_r($mysqli->error);
}

$query = "update eventredenvelope set AliLuckyMoneyCode = @hongbao := AliLuckyMoneyCode , PostUserId = 1, PostTime = ".time()." where PostUserId = 0 limit 1 ; select @hongbao;";

//var_dump($query);

if($ret = $mysqli->multi_query($query)) {
	do {
		if ($result = $mysqli->store_result()) {
			while ($row = $result->fetch_row()) {
				printf("%s\n", $row[0]);
			}
			$result->free();
		}
		if ($mysqli->more_results()) {
			printf("-----------------\n");
		}
	} while ($mysqli->next_result());
}
else {
	print_r($mysqli->error);
}