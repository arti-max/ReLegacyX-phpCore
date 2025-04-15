<?php
chdir(dirname(__FILE__));
include "../lib/connection.php";
require_once "../lib/Lib.php";
$gs = new Lib();

if(!isset($_POST['itemID']))
	exit(-1);

// Валидация и санитизация входных данных
$levelID = filter_input(INPUT_POST, 'itemID', FILTER_VALIDATE_INT);
$type = filter_input(INPUT_POST, 'type', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 2]]); // Проверка типа на 1 или 2
$isLike = filter_input(INPUT_POST, 'like', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 1]]); // Проверка на 0 или 1
$ip = $gs->getIP();

if ($levelID === false || $type === false || $isLike === false) {
    exit("-1");
}

$query = $db->prepare("SELECT userID FROM users WHERE IP=:ip");
$query->execute([':ip' => $ip]);
$resultIP = $query->rowCount();
$udid = $query->fetchColumn();

$query2 = $db->prepare("SELECT * FROM bansIP WHERE ip=:ip");
$query2->execute([':ip' => $ip]);
if ($query2->rowCount() > 0) {
    exit("-2");
}

$query = $db->prepare("INSERT INTO likes (levelID, type, isLike, ip, udid) VALUES (:levelID, :type, :isLike, :ip, :udid)");
$query->execute([':levelID' => $levelID, ':type' => $type, ':isLike' => $isLike, ':ip' => $ip, ':udid' => $udid]);

switch($type){
	case 1:
		$table = "levels";
		$column = "levelID";
		break;
	case 2:
		$table = "comments";
		$column = "commentID";
		break;
    }

$query=$db->prepare("SELECT likes FROM $table WHERE $column = :levelID LIMIT 1");
$query->execute([':levelID' => $levelID]);
$likes = $query->fetchColumn();

if ($isLike == 1) {
    $sign = "+";
} else {
    $sign = "-";
}

$query = $db->prepare("UPDATE $table SET likes = likes $sign 1 WHERE $column = :levelID");
$query->execute([':levelID' => $levelID]);
echo "1";
?>
