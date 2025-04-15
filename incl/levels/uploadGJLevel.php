<?php
//error_reporting(0);
chdir(dirname(__FILE__));
include "../lib/connection.php";
require_once "../lib/Lib.php";
$Lib = new Lib();
require_once "../lib/Lib.php";
$gs = new Lib();


$gameVersion = filter_input(INPUT_POST, 'gameVersion', FILTER_SANITIZE_STRING);
$userName = filter_input(INPUT_POST, 'userName', FILTER_SANITIZE_STRING);
$levelID = filter_input(INPUT_POST, 'levelID', FILTER_SANITIZE_NUMBER_INT); // Если levelID используется только в качестве номера
$levelName = filter_input(INPUT_POST, 'levelName', FILTER_SANITIZE_STRING);
$levelDesc = filter_input(INPUT_POST, 'levelDesc', FILTER_SANITIZE_STRING);
$levelVersion = filter_input(INPUT_POST, 'levelVersion', FILTER_SANITIZE_STRING);
$levelLength = filter_input(INPUT_POST, 'levelLength', FILTER_SANITIZE_NUMBER_INT);
$audioTrack = filter_input(INPUT_POST, 'audioTrack', FILTER_SANITIZE_STRING);
$secret = filter_input(INPUT_POST, 'secret', FILTER_SANITIZE_STRING);
$levelString = filter_input(INPUT_POST, 'levelString', FILTER_UNSAFE_RAW); //  FILTER_SANITIZE_STRING слишком строгий в данном случае
$songID = !empty($_POST["songID"]) ? $_POST["songID"] : 0;
$password = !empty($_POST["password"]) ? $_POST["password"] : 0;

if (empty($levelString) || empty($levelName)) {
    echo -1;
    exit;
}

$levelName = str_replace("?", "", $levelName);
$levelName = str_replace("}", "", $levelName);
$levelName = str_replace("{", "", $levelName);
$levelName = str_replace(")", "", $levelName);
$levelName = str_replace("(", "", $levelName);
$levelName = str_replace("/", "", $levelName);
$levelName = str_replace(".", "", $levelName);
$levelName = str_replace(":", "", $levelName);
$levelName = str_replace(";", "", $levelName);
$levelName = str_replace("~", "", $levelName);
$levelName = str_replace("`", "", $levelName);
$levelName = str_replace("%", "", $levelName);
$levelName = str_replace("#", "", $levelName);



$objString = explode(';', $levelString);
if (count($objString) - 1 > 40500) {
    echo -1;
    exit;
}


$id = $gs->getIDFromPost();
$ip = $gs->getIP();
$userID = $Lib->getUserID($id, $userName, $ip);
$uploadDate = time();
$query = $db->prepare("SELECT count(*) FROM levels WHERE uploadDate > :time AND (userID = :userID)");
$query->execute([':time' => $uploadDate - 60, ':userID' => $userID]);
if($query->fetchColumn() > 0){
	exit("-1");
}
$query = $db->prepare("INSERT INTO levels (levelName, levelDesc, userName, levelVersion, gameVersion, audioTrack, levelLength, userID, secret, levelString, udid, uploadDate, objects, songID, password, updateDate)
VALUES (:levelName, :levelDesc, :userName, :levelVersion, :gameVersion, :audioTrack, :levelLength, :userID, :secret, :levelString, :udid, :uploadDate, :objects, :songID, :password, :uploadDate)");


if($levelString != "" AND $levelName != ""){
	$querye=$db->prepare("SELECT levelID FROM levels WHERE levelName = :levelName AND userID = :userID");
	$querye->execute([':levelName' => $levelName, ':userID' => $userID]);
	$levelID = $querye->fetchColumn();
	$lvls = $querye->rowCount();
	if($lvls==1){
		$filePath = '../../data/' . $levelID;
		$dir = dirname($filePath);
		if (!is_dir($dir) || !is_writable($dir)) {
			echo -1;
			exit;
		}
		$query = $db->prepare("UPDATE levels SET levelName=:levelName, gameVersion=:gameVersion, userName=:userName, levelDesc=:levelDesc, levelVersion=:levelVersion, levelLength=:levelLength, audioTrack=:audioTrack, levelString=:levelString, objects=:objects, secret=:secret, songID=:songID, updateDate=:updateDate WHERE levelName=:levelName AND udid=:udid");	
		$query->execute([':levelName' => $levelName, ':levelDesc' => $levelDesc, ':userName' => $userName, ':levelVersion' => $levelVersion, ':gameVersion' => $gameVersion, ':audioTrack' => $audioTrack, ':levelLength' => $levelLength, ':userID' => $userID, ':secret' => $secret, ':levelString' => "", ':udid' => $id, ':objects' => count($objString)-1, ':songID' => $songID, ':updateDate' => $uploadDate - 60]);
		file_put_contents("../../data/$levelID",$levelString);
		echo $levelID;
	}else{
		$query->execute([':levelName' => $levelName, ':levelDesc' => $levelDesc, ':userName' => $userName, ':levelVersion' => $levelVersion, ':gameVersion' => $gameVersion, ':audioTrack' => $audioTrack, ':levelLength' => $levelLength, ':userID' => $userID, ':secret' => $secret, ':levelString' => "", ':udid' => $id, ':uploadDate' => $uploadDate - 60, ':objects' => count($objString)-1, ':songID' => $songID, ':password' => $password]);
		$levelID = $db->lastInsertId();
		$filePath = '../../data/' . $levelID;
		$dir = dirname($filePath);
		if (!is_dir($dir) || !is_writable($dir)) {
			echo -1;
			exit;
		}
		file_put_contents("../../data/$levelID",$levelString);
		echo $levelID;
	}
}else{
	echo -1;
}
?>
