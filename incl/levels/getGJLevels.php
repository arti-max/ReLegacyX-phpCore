<?php
//header
chdir(dirname(__FILE__));
include "../lib/connection.php";
require_once "../lib/Lib.php";
$gs = new Lib();


$lvlstring = ""; $userstring = ""; $lvlsmultistring = []; $str = ""; $order = "";
$orderenabled = true;
$morejoins = "";

if(!empty($_POST["uncompleted"])){
	$uncompleted = $_POST["uncompleted"];
	$comLvls = $_POST["completedLevels"];
	$params[] = "NOT levelID IN $comLvls";
}else{
	$uncompleted = 0;
}

if(!empty($_POST["song"])){
	$song = $_POST["song"]-1;
	$params[] = "audioTrack = '$song'";
}


if(!empty($_POST["gameVersion"])){
	$gameVersion = $_POST["gameVersion"];
}else{
	$gameVersion = 0;
}
if(!is_numeric($gameVersion)){
	exit("-1");
}

if(!empty($_POST["type"])){
	$type = $_POST["type"];
}else{
	$type = 0;
}
if(!empty($_POST["diff"])){
	$diff = $_POST["diff"];
}else{
	$diff = "-";
}

//ADDITIONAL PARAMETERS
if($gameVersion==0){
	$params[] = "levels.gameVersion <= 18";
}else{
	$params[] = "levels.gameVersion <= '$gameVersion'";
}
if(!empty($_POST["featured"]) AND $_POST["featured"]==1){
	$params[] = "isFeatured = 1";
}
if ($_POST["len"] != "-") {
	$len = $_POST["len"];
	$params[] = "levelLength IN ($len)";
} else {
	$len = "-";
}
$isStar = $_POST["star"];
if($isStar == 1){
	$params[] = "NOT isStars = 0";
}



//DIFFICULTY FILTERS
switch($diff){
	case -1:
		$params[] = "difficulty = '0'";
		break;
	case -2:
		$params[] = "isDemon = 1";
		break;
	case -3:
		$params[] = "isAuto = 1";
		break;
	case "-";
		break;
	default:
		if($diff){
			$diff = str_replace(",", "0,", $diff) . "0";
			$params[] = "difficulty IN ($diff) AND isDemon = 0";
		}
		break;
		
}

if(!empty($_POST["str"])){
	$str = $_POST["str"];
}
if(isset($_POST["page"]) AND is_numeric($_POST["page"])){
	$offset = $_POST["page"] . "0";
}else{
	$offset = 0;
}

$params[] = "levels.unlisted = 0";

switch($type){
	case 0: //NULL str AND str
		$order = "likes";
		if(!empty($str)){
			if(is_numeric($str)){
				$params = array("levelID = '$str'");
			}else{
				$params[] = "levelName LIKE '%$str%'";
			}
		}
		break;
	case 1: // most download
		$order = "downloads";
		break;
	case 2: // most liked
		$order = "likes";
		break;
	case 3: //TRENDING
		$uploadDate = time() - (7 * 24 * 60 * 60);
		$params[] = "uploadDate > $uploadDate ";
		$order = "likes";
		break;
  	case 4: // recent
    	$order = "levelID";
		break;
	case 5: // more by this users
		$params[] = "levels.userID = '$str'";
		$order = "levelID";
		break;
	case 6: //featured
		$params[] = "NOT isFeatured = 0";
		$order = "F_POS";
		break;
	case 7: // magic
		$params[] = "objects >= 1000 AND isStars=0";
		$order = "levelID";
		break;
	case 10: //map packs
		$order = "packID";
		$params[] = "levelID IN ($str)";
		break;
}

//ACTUAL QUERY EXECUTION
$querybase = "FROM levels LEFT JOIN users ON levels.userID = users.userID $morejoins";
if(!empty($params)){
	$querybase .= " WHERE (" . implode(" ) AND ( ", $params) . ")";
}
$query = "SELECT levels.*, users.userName, users.udid $querybase $morejoins ";
if($order){
		$query .= "ORDER BY $order DESC";
}
$query .= " LIMIT 10 OFFSET $offset";
//echo $query;
$countquery = "SELECT count(*) $querybase";
//echo $query;
$query = $db->prepare($query);
$query->execute();
//echo $countquery;
$countquery = $db->prepare($countquery);
$countquery->execute();
$totallvlcount = $countquery->fetchColumn();
$result = $query->fetchAll();
$levelcount = $query->rowCount();
foreach($result as &$level1) {
	if($level1["levelID"]!=""){
		$lvlsmultistring[] = ["levelID" => $level1["levelID"], "stars" => $level1["isStars"]];
		$lvlstring .= "1:".$level1["levelID"].":2:".$level1["levelName"].":5:".$level1["levelVersion"].":6:".$level1["userID"].":8:10:9:".$level1["difficulty"].":10:".$level1["downloads"].":12:".$level1["audioTrack"].":13:".$level1["gameVersion"].":14:".$level1["likes"].":19:".$level1["isFeatured"].":3:".$level1["levelDesc"].":15:".$level1["levelLength"].":17:".$level1["isDemon"].":18:".$level1["isStars"].":25:".$level1["isAuto"]."|";
		$userstring .= $gs->getUserString($level1)."|";
	}
}
$lvlstring = substr($lvlstring, 0, -1);
$userstring = substr($userstring, 0, -1);
echo $lvlstring."#".$userstring;
if ($gameVersion > 18) {
	echo "#" . "";
}
echo "#".$totallvlcount.":".$offset.":10";
echo "#";
echo $lvlsmultistring;
?>
