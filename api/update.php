<?php

require_once '../init.php';

use Loek\DB;
use Loek\ReturnInfo;
use Zelenin\Elo\Match;
use Zelenin\Elo\Player;

function newQuotes(){
	$sql = "SELECT * FROM `quotes` WHERE deleted = FALSE ORDER by RAND() LIMIT 2;";
	$weightedSql = "SELECT *, LOG(1-RAND()) / (matches + 1) AS prio FROM `quotes` WHERE deleted = FALSE ORDER by prio LIMIT 2";

    $result = array();

    $row = DB::pdo()->query($weightedSql);

    $result[] = new ReturnInfo($row->fetch(PDO::FETCH_OBJ));
    $result[] = new ReturnInfo($row->fetch(PDO::FETCH_OBJ));

    echo json_encode($result);
}

if ($_SERVER['REQUEST_METHOD']=="POST") {

	header('Content-Type:application/json;Charset:UTF8;');
	$input = json_decode(file_get_contents("php://input"));

	if($input->action == "start"){
		newQuotes();
	} else if($input->action == "change"){

		$sql = DB::pdo()->prepare("SELECT * FROM quotes WHERE id = :id");

		$score1 = $input->score1;
		$score2 = $input->score2;
        if ($score1 == $score2)
            $score1 = $score2 = 0;

        $sql->execute(array(':id'=>$input->id1));
        $first = $sql->fetch(PDO::FETCH_OBJ);

        $sql->execute(array(':id'=>$input->id2));
        $second= $sql->fetch(PDO::FETCH_OBJ);

        $match = new Match(new Player($first->rating), new Player($second->rating));
        $match->setScore($score1, $score2)->setK(32)->count();

        $leftWon = $score1 > $score2;
        $rightWon = $score2 > $score1;
        $ins1 = DB::pdo()->prepare("UPDATE quotes SET matches = :matches, rating = :ratings, leftWon = :leftWon, topSwipe = :topSwipe WHERE id = :id;");
        $ins2 = DB::pdo()->prepare("UPDATE quotes SET matches = :matches, rating = :ratings, rightWon = :rightWon, bottomSwipe = :bottomSwipe WHERE id = :id;");
        $ins1->execute(array(
            ':id' => $input->id1,
            ':matches' => $first->matches + 1,
            ':ratings' => $match->getPlayer1()->getRating(),
            ':leftWon' => $leftWon ? $first->leftWon + 1: $first->leftWon,
            ':topSwipe' => $leftWon && $input->swipe ? $first->topSwipe + 1: $first->topSwipe
        ));
        $ins2->execute(array(
            ':id' => $input->id2,
            ':matches' => $second->matches + 1,
            ':ratings' => $match->getPlayer2()->getRating(),
            ':rightWon' => $rightWon ? $second->rightWon + 1: $second->rightWon,
            ':bottomSwipe' => $rightWon && $input->swipe ? $second->bottomSwipe + 1: $second->bottomSwipe
        ));
		newQuotes();

	} else if ($input->action == "rankings"){
	    $dir = 'DESC';
	    if (isset($input->dir) && $input->dir === 'asc') {
	        $dir = 'ASC';
        }
        $order = 'rating';
        if (isset($input->order) && $input->order === 'matches') {
            $order = 'matches';
        }


        if (!isset($input->nr)) {
            $sql = DB::pdo()->prepare('SELECT * FROM quotes WHERE deleted = FALSE ORDER BY ' . $order .  ' ' . $dir . ';');
            $data = $sql->execute();
        } else if (is_numeric($input->nr)) {
            $sql = DB::pdo()->prepare('SELECT * FROM quotes WHERE deleted = FALSE ORDER BY ' . $order .  ' ' . $dir . ' LIMIT ?;');
            $sql->bindValue(1, $input->nr, PDO::PARAM_INT);
            $data = $sql->execute();
        } else {
            http_response_code(400);
            return;
        }

        $result = array();
		$i = 1;
		foreach($sql->fetchAll(PDO::FETCH_ASSOC) as $row) {
		    $won = $row['leftWon'] + $row['rightWon'];
		    $row['win_rate'] = $won > 0 ? round(($won / $row['matches']) * 100, 2): 0;
			$result[] = $row;
			$i++;
		}
		$obj = new StdClass;
		$obj->matches = (int)DB::pdo()->query("SELECT (SELECT SUM(matches) FROM quotes) / 2")->fetch(PDO::FETCH_COLUMN);
		$obj->ranks = $result;
		echo json_encode($obj);
	}

} else {
	http_response_code(405);
}
