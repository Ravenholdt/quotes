<?php

require_once '../init.php';
use Loek\DB;
use Loek\ReturnInfo;

function numberOrDefault($raw, $default) {
    if (isset($raw) && is_numeric($raw)) {
        return $raw;
    }
    return $default;
}

if ($_SERVER['REQUEST_METHOD']=="GET") {
    $scoreLimitLow = numberOrDefault($_GET['lowest'], 0);
    $scoreLimitHigh = numberOrDefault($_GET['highest'],10000);

    $query = DB::pdo()->prepare("SELECT * FROM `quotes` WHERE deleted = FALSE AND rating >= :low AND rating <= :high ORDER by RAND() LIMIT 1;");
    $query->execute(array(
        ':low' => $scoreLimitLow,
        ':high' => $scoreLimitHigh
    ));
    header('Content-Type:application/json;Charset:UTF8;');
    $result = $query->fetch(PDO::FETCH_OBJ);
    if ($result) {
        echo json_encode(new ReturnInfo($result));
    } else {
        echo json_encode(new StdClass);
    }
} else {
    http_response_code(405);
}