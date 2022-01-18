<?php

require_once '../init.php';

use Loek\DB;
use Loek\ReturnInfo;

function numberOrDefault($option, $default)
{
    if (isset($_GET[$option]) && is_numeric($_GET[$option])) {
        return $_GET[$option];
    }
    return $default;
}

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $scoreLimitLow = numberOrDefault('lowest', 0);
    $scoreLimitHigh = numberOrDefault('highest', 10000);

    $query = DB::pdo()->prepare(
        "SELECT * FROM `quotes` WHERE deleted = FALSE AND rating >= :low AND rating <= :high ORDER by RAND() LIMIT 1;"
    );
    $query->execute(array(
        ':low' => $scoreLimitLow,
        ':high' => $scoreLimitHigh
    ));
    header('Content-Type:application/json;Charset:UTF8;');
    $result = $query->fetchObject(ReturnInfo::class);
    if (!$result) {
        http_response_code(500);
        $result = new StdClass();
    }
    echo json_encode($result);
} else {
    http_response_code(405);
}