<?php
chdir(dirname(__FILE__));
include "../lib/connection.php";
require_once "../lib/hook.php";

function getCreatorID($levelID) {
    include "../lib/connection.php";

    $query = $db->prepare("SELECT userID FROM levels WHERE levelID=:levelID");
    $query->execute([':levelID' => $levelID]);
    $userID = $query->fetchColumn();

    return $userID;
}

function updateCP($userID, $cp) {
    include "../lib/connection.php";

    $query = $db->prepare("UPDATE users SET cp=cp+$cp WHERE userID=:userID");
    $query->execute([':userID' => $userID]);

}

function checkIsRate($levelID) {
    include "../lib/connection.php";

    $query = $db->prepare("SELECT isStars FROM levels WHERE levelID=:levelID");
    $query->execute([':levelID' => $levelID]);

    return $query->fetchColumn();
}

function getCreatorName($userID) {
    include "../lib/connection.php";

    $query = $db->prepare("SELECT userName FROM users WHERE userID=:userID");
    $query->execute([':userID' => $userID]);
    $userName = $query->fetchColumn();

    return $userName;
}

function getLevelName($levelID) {
    include "../lib/connection.php";

    $query = $db->prepare("SELECT levelName FROM levels WHERE levelID=:levelID");
    $query->execute([':levelID' => $levelID]);
    $levelName = $query->fetchColumn();

    return $levelName;
}


$levelID = $_POST['levelID'];
$stars = $_POST['stars'];
$secret = $_POST["secret"];
$diff = 0;
$isDemon = 0;

if (substr($secret, 0, 10) != "Aafq1640ub") {
    exit("-1");
}


$CID = getCreatorID($levelID);
$creatorName = getCreatorName($CID);
$levelName = getLevelName($levelID);

switch ($stars) {
    case 11:
        $diff = 0;
        break;
    case 1:
    case 2:
        $diff = 10;
        break;
    case 3:
        $diff = 20;
        break;
    case 4:
    case 5:
        $diff = 30;
        break;
    case 6:
    case 7:
        $diff = 40;
        break;
    case 8:
    case 9:
        $diff = 50;
        break;
    case 10:
        $diff = 50;
        $isDemon = 1;
        break;
}

$oldStars = checkIsRate($levelID);
$CP = 1;

if ($oldStars < 1) {
    $CP = 1;
} else {
    if ($stars < 11) {
        $CP = 0;
    } else {
        $CP = -1;
        $stars = 0;
    }
}

$query = $db->prepare("UPDATE levels SET isStars=:stars, difficulty=:diff, diffOverride=:diff, isDemon=:demon WHERE levelID=:levelID");
$query->execute([':stars' => $stars, ':levelID' => $levelID, ':diff' => $diff, ':demon' => $isDemon]);
updateCP($CID, $CP);

$hookData = ["levelID" => $levelID,
            "creatorName" => $creatorName,
            "levelName" => $levelName,
            "stars" => $stars];
        
Hook::Send(1, 2, $hookData);

echo "1";
