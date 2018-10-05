<?php

/* This script is used to populate the images table with links.
 *
 * This takes a file with all the image source links in separate lines.
 * Use the linux cmd 
 * 	php enterdata.php < filename
 *
 */

	$pdo = new PDO('mysql:host=127.0.0.1;dbname=loek','loek','DsH3e4X6rS57TQXU');
	$ins = $pdo->prepare("insert into images (src) values(?)");
	$file = fopen("php://stdin","r");
	$line = fgets($file);
while(trim($line)!=""){
	echo trim($line);
	$ins->execute(array($line));
	$line = fgets($file);
}
?>
