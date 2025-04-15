<?php
chdir(dirname(__FILE__));
include "../lib/connection.php";
require_once "../lib/Lib.php";
require_once "../lib/commands.php";
$lib = new Lib();



$userName = $_POST["userName"];
$levelID = $_POST["levelID"];
$comment = $_POST["comment"];

$udid = $_POST["udid"];

if (!empty($_POST["accountID"])) {
    $udid = $_POST["accountID"];
}

$ip = $lib->getIP();

$userID = $lib->getUserID($udid, $userName, $ip);
$uploadDate = time();


if(Commands::command($udid, $comment, $levelID) == true){
	exit("-1");
}

if($udid != "" AND $comment != ""){
    $query = $db->prepare("INSERT INTO comments (userID, userName, levelID, timestamp, comment) VALUES (:userID, :userName, :levelID, :uploadDate, :comment)");
    $query->execute([':userID' => $userID, ':userName' => $userName, ':levelID' => $levelID, ':uploadDate' => $uploadDate, ':comment' => $comment]);
    echo "1";
} else {
    echo "-1";
}

?>
