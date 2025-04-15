<?php
chdir(dirname(__FILE__));
include "../lib/hook.php";
include "../lib/connection.php";
require_once "../lib/Lib.php";
$LIB = new Lib();


class FNC {

    public static function getLevelLen($levelID) {
        include "../lib/connection.php";

        $query = $db->prepare("SELECT levelLength FROM levels WHERE levelID=:levelID");
        $query->execute([':levelID' => $levelID]);
        $levelLen = $query->fetchColumn();

        return $levelLen;
    }
    public static function getLevelName($levelID) {
        include "../lib/connection.php";

        $query = $db->prepare("SELECT levelName FROM levels WHERE levelID=:levelID");
        $query->execute([':levelID' => $levelID]);
        $levelName = $query->fetchColumn();

        return $levelName;
    }

    public static function getCreatorID($levelID) {
        include "../lib/connection.php";

        $query = $db->prepare("SELECT userID FROM levels WHERE levelID=:levelID");
        $query->execute([':levelID' => $levelID]);
        $userID = $query->fetchColumn();

        return $userID;
    }

    public static function getUserName($userID) {
        include "../lib/connection.php";

        $query = $db->prepare("SELECT userName FROM users WHERE userID=:userID");
        $query->execute([':userID' => $userID]);
        $userName = $query->fetchColumn();

        return $userName;
    }

}

$stars = $_POST["rating"];
$levelID = $_POST["levelID"];
$levelName = FNC::getLevelName($levelID);
$creatorID = FNC::getCreatorID($levelID);
$creatorName = FNC::getUserName($creatorID);
$levelLen = FNC::getLevelLen($levelID);
$levelLen = $LIB->getLevelLength__STR($levelLen);
$ip = $LIB->getIP();

if (empty($levelID) || empty($stars)) {exit("-1");}

$query = $db->prepare("SELECT userID FROM users WHERE IP=:ip");
$query->execute([':ip' => $ip]);
$resultIP = $query->rowCount();
$userID = $query->fetchColumn();
$timestamp = time();

$query2 = $db->prepare("SELECT * FROM bansIP WHERE ip=:ip");
$query2->execute([':ip' => $ip]);
if ($query2->rowCount() > 0) {
    exit("-2");
}

$query = $db->prepare("INSERT INTO `actions` (`ID`, `type`, `value`, `timestamp`, `value2`, `value3`, `value4`, `value5`, `value6`, `account`, `accIP`) VALUES (NULL, '26', '$levelID', '$timestamp', '$stars', '0', '0', '0', '0', '$userID', '$ip')");
$query->execute();

$dataArr = [
    "stars" => $stars,
    "levelID" => $levelID,
    "creatorName" => $creatorName,
    "levelName" => $levelName,
    "length" => $levelLen

];

Hook::PostToFH(2, 4, $dataArr);

echo "1";