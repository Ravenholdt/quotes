<?php

require_once '../init.php';
use Loek\DB;
use Loek\ReturnInfo;

if ($_SERVER['REQUEST_METHOD']=="GET") {
    $sql = "SELECT * FROM `quotes` WHERE deleted = FALSE ORDER by RAND() LIMIT 1;";
    $row = DB::pdo()->query($sql);
    header('Content-Type:application/json;Charset:UTF8;');
    echo json_encode(new ReturnInfo($row->fetch(PDO::FETCH_OBJ)));
} else {
    http_response_code(405);
}