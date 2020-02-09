<?php

/* This script is used to populate the quotes table with links.
 *
 * This takes a file with all the image source links in separate lines.
 * Use the linux cmd 
 * 	php enterdata.php < filename
 *
 */
include_once '../init.php';

$ins = DB::pdo()->prepare("insert into quotes (quote) values(?)");
$file = fopen("php://stdin","r");
$line = trim(fgets($file));

while($line != "") {
    echo $line . PHP_EOL;
	$ins->execute(array($line));
	$line = trim(fgets($file));
}
