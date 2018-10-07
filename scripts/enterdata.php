<?php

/* This script is used to populate the images table with links.
 *
 * This takes a file with all the image source links in separate lines.
 * Use the linux cmd 
 * 	php enterdata.php < filename
 *
 */
include_once '../init.php';

$ins = DB::pdo()->prepare("insert into images (src) values(?)");
$file = fopen("php://stdin","r");
$line = trim(fgets($file));

while($line != "") {
	if (substr($line, 0, 5) === 'http:') {
        $line = trim(fgets($file));
        continue;
    }
    echo $line . PHP_EOL;
	$ins->execute(array($line));
	$line = trim(fgets($file));
}
