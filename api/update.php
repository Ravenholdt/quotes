<?php

require_once '../init.php';

use Loek\DB;
use Loek\ReturnInfo;
use Zelenin\Elo\EloMatch;
use Zelenin\Elo\Player;

/**
 * Returns two random quotes
 * @return ReturnInfo[]
 */
function newQuotes(): array
{
    //$sql = "SELECT * FROM `quotes` WHERE deleted = FALSE ORDER by RAND() LIMIT 2;";
    $sql = "SELECT *, (RAND() / (matches + 1)) AS prio FROM `quotes` WHERE deleted = FALSE ORDER by prio LIMIT 2";
    $row = DB::pdo()->query($sql);

    return [
        $row->fetchObject(ReturnInfo::class),
        $row->fetchObject(ReturnInfo::class)
    ];
}

/**
 * Battles two quotes against each other and updates their score
 * @param int $score1 The score of the first quote
 * @param int $score2 The score of the second quote
 * @param int $id1 The id of the first quote
 * @param int $id2 The id of the second quote
 * @param bool $swipe If the battle fought with a swipe
 * @return ReturnInfo[] Returns two new random quotes
 */
function battleQuotes(int $score1, int $score2, int $id1, int $id2, bool $swipe): array
{
    $sql = DB::pdo()->prepare("SELECT * FROM quotes WHERE id = :id");

    $sql->execute(array(':id' => $id1));
    $first = $sql->fetchObject();

    $sql->execute(array(':id' => $id2));
    $second = $sql->fetchObject();

    $m = new EloMatch(new Player($first->rating), new Player($second->rating));
    $m->setScore($score1, $score2)->setK(32)->count();

    $leftWon = $score1 > $score2;
    $rightWon = $score2 > $score1;
    $ins1 = DB::pdo()->prepare(
        "UPDATE quotes SET matches = :matches, rating = :ratings, leftWon = :leftWon, topSwipe = :topSwipe WHERE id = :id;"
    );
    $ins2 = DB::pdo()->prepare(
        "UPDATE quotes SET matches = :matches, rating = :ratings, rightWon = :rightWon, bottomSwipe = :bottomSwipe WHERE id = :id;"
    );
    $ins1->execute(array(
        ':id' => $id1,
        ':matches' => $first->matches + 1,
        ':ratings' => $m->getPlayer1()->getRating(),
        ':leftWon' => $leftWon ? $first->leftWon + 1 : $first->leftWon,
        ':topSwipe' => $leftWon && $swipe ? $first->topSwipe + 1 : $first->topSwipe
    ));
    $ins2->execute(array(
        ':id' => $id2,
        ':matches' => $second->matches + 1,
        ':ratings' => $m->getPlayer2()->getRating(),
        ':rightWon' => $rightWon ? $second->rightWon + 1 : $second->rightWon,
        ':bottomSwipe' => $rightWon && $swipe ? $second->bottomSwipe + 1 : $second->bottomSwipe
    ));
    return newQuotes();
}

/**
 * Return the rankings of all quotes
 * @param object $input
 * @return array|StdClass
 */
function getRankings(object $input)
{
    $dir = 'DESC';
    $order = 'rating';

    if (isset($input->dir) && $input->dir === 'asc') {
        $dir = 'ASC';
    }
    if (isset($input->order) && $input->order === 'matches') {
        $order = 'matches';
    }

    if (!isset($input->nr)) {
        $sql = DB::pdo()->prepare(
            'SELECT * FROM quotes WHERE deleted = FALSE ORDER BY ' . $order . ' ' . $dir . ';'
        );
    } elseif (is_numeric($input->nr)) {
        $sql = DB::pdo()->prepare(
            'SELECT * FROM quotes WHERE deleted = FALSE ORDER BY ' . $order . ' ' . $dir . ' LIMIT ?;'
        );
        $sql->bindValue(1, $input->nr, PDO::PARAM_INT);
    } else {
        http_response_code(400);
        return [];
    }
    $sql->execute();

    $result = [];
    foreach ($sql->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $won = $row['leftWon'] + $row['rightWon'];
        $row['win_rate'] = $won > 0 ? round(($won / $row['matches']) * 100, 2) : 0;
        $result[] = $row;
    }
    $obj = new StdClass();
    $obj->matches = (int)DB::pdo()->query("SELECT (SELECT SUM(matches) FROM quotes) / 2")->fetch(PDO::FETCH_COLUMN);
    $obj->ranks = $result;
    return $obj;
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    header('Content-Type:application/json;Charset:UTF8;');
    $input = json_decode(file_get_contents("php://input"));

    $result = new stdClass();
    if ($input->action == "start") {
        $result = newQuotes();
    } elseif ($input->action == "change") {
        $result = battleQuotes($input->score1, $input->score2, $input->id1, $input->id2, $input->swipe);
    } elseif ($input->action == "rankings") {
        $result = getRankings($input);
    }
    echo json_encode($result);
} else {
    echo json_encode(newQuotes());
}
