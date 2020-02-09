<?php

/* This script is used to populate the quotes table with links.
 *
 * This takes a file with all the image source links in separate lines.
 * Use the linux cmd 
 * 	php enterdata.php < filename
 *
 */
include_once '../init.php';

$ins = DB::pdo()->prepare("insert into quotes (quote, context) values(?, ?)");
$file = fopen("php://stdin","r");
$line = trim(fgets($file));

while($line != "") {
    echo $line . PHP_EOL;
    $quote = explode(' - ', $line, 2);
    if (!isset($quote[1])) {
        $quote[1] = '';
    }
    for ($i = 0; $i < count($quote); $i++) {
        $quote[$i] = trim($quote[$i]);
    }
	$ins->execute($quote);
	$line = trim(fgets($file));
}
