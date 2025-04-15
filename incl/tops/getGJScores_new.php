<?php

chdir(dirname(__FILE__));
//error_reporting(0);
include "../lib/connection.php";
$stars = 0;
$count = 0;
$xi = 0;
$lbstring = "";
if(empty($_POST["gameVersion"])){
	$sign = "<> 'ffff'";
}else{
	$sign = "<> 'ffff'";
}
if(!empty($_POST["accountID"])){
	$accountID = $_POST["accountID"];
}else{
	$accountID = $_POST["udid"];
	if(is_numeric($accountID)){
		exit("-1");
	}
}

$type = $_POST["type"];
if($type == "top" OR $type == "creators" OR $type == "relative")
{
	if($type == "top")
	{
		$query = "SELECT * FROM users WHERE lbBan = '0' AND stars > 0 ORDER BY stars DESC LIMIT 50";
	}
	if($type == "creators")
	{
		$query = "SELECT * FROM users WHERE lbBan = '0' AND creatorPoints > 0 ORDER BY creatorPoints DESC LIMIT 50";
	}
	if($type == "relative")
	{
		$query = "SELECT * FROM users WHERE udid = :accountID";
		$query = $db->prepare($query);
		$query->execute([':accountID' => $accountID]);
		$result = $query->fetchAll();
		$user = $result[0];
		$stars = $user["stars"];
		if($_POST["count"]){
			$count = $_POST["count"];
		}else{
			$count = 50;
		}
		$count = floor($count / 2);
		$query = "SELECT	A.* FROM	(
			(
				SELECT	*	FROM users
				WHERE stars <= :stars
				AND lbBan = 0
				ORDER BY stars DESC
				LIMIT $count
			)
			UNION
			(
				SELECT * FROM users
				WHERE stars >= :stars
				AND lbBan = 0
				ORDER BY stars ASC
				LIMIT $count
			)
		) as A
		ORDER BY A.stars DESC";
	}
	$query = $db->prepare($query);
	$query->execute([':stars' => $stars, ':count' => $count]);
	$result = $query->fetchAll();
	if($type == "relative"){
		$user = $result[0];
		$extid = $user["udid"];
		$e = "SET @rownum := 0;";
		$query = $db->prepare($e);
		$query->execute();
		$f = "SELECT rank, stars FROM (
							SELECT @rownum := @rownum + 1 AS rank, stars, udid, lbBan
							FROM users WHERE lbBan = '0' ORDER BY stars DESC
							) as result WHERE udid=:extid";
		$query = $db->prepare($f);
		$query->execute([':extid' => $extid]);
		$leaderboard = $query->fetchAll();
		//var_dump($leaderboard);
		$leaderboard = $leaderboard[0];
		$xi = $leaderboard["rank"] - 1;
	}
	foreach($result as &$user) {
		$extid = 0;
		if(is_numeric($user["udid"])){
			$extid = $user["udid"];
		}
		$xi++;
		
		$lbstring .= "1:".$user["userName"].":2:".$user["userID"].":13:".$user["coins"].":17:".$user["userCoins"].":6:".$xi.":9:".$user["icon"].":10:".$user["color1"].":11:".$user["color2"].":14:".$user["iconType"].":15:".$user["special"].":16:".$extid.":3:".$user["stars"].":8:".round($user["creatorPoints"],0,PHP_ROUND_HALF_DOWN).":4:".$user["demons"].":7:".$extid.":46:".$user["diamonds"]."|";
		
	}
}

if($type == "week")
{
	$starsgain = array();
	$time = time() - 604800;
	$xi = 0;
	$query = $db->prepare("SELECT * FROM actions WHERE type = '9' AND timestamp > :time");
	$query->execute([':time' => $time]);
	$result = $query->fetchAll();
	foreach($result as &$gain)
	{
		if(!empty($starsgain[$gain["account"]]))
		{
			$starsgain[$gain["account"]] += $gain["value"];
		}
		else
		{
			$starsgain[$gain["account"]] = $gain["value"];
		}
	}
	arsort($starsgain);
	foreach ($starsgain as $userID => $stars)
	{
		if ($stars == 0 or $xi >= 100)
		{
			break;
		}
		$query = $db->prepare("SELECT * FROM users WHERE userID = :userID");
		$query->execute([':userID' => $userID]);
		$user = $query->fetchAll()[0];
		if($user["isBanned"] == 0)
		{
			$xi++;
			$lbstring .= "1:".$user["userName"].":2:".$user["userID"].":4:-1:13:-1:17:".$user["userCoins"].":6:".$xi.":9:".$user["icon"].":10:".$user["color1"].":11:".$user["color2"].":14:".$user["iconType"].":15:".$user["special"].":16:".$extid.":3:".$stars.":7:".$user["extID"]."|";
		}
	}  
}

if($type == "friends"){
	$query = "SELECT * FROM friendships WHERE person1 = :accountID OR person2 = :accountID";
	$query = $db->prepare($query);
	$query->execute([':accountID' => $accountID]);
	$result = $query->fetchAll();
	$people = "";
	foreach ($result as &$friendship) {
		$person = $friendship["person1"];
		if($friendship["person1"] == $accountID){
			$person = $friendship["person2"];
		}
		$people .= ",".$person;
	}
	$query = "SELECT * FROM users WHERE extID IN (:accountID $people ) ORDER BY stars DESC";
	$query = $db->prepare($query);
	$query->execute([':accountID' => $accountID]);
	$result = $query->fetchAll();
	foreach($result as &$user){
		if(is_numeric($user["extID"])){
			$extid = $user["extID"];
		}else{
			$extid = 0;
		}
		$xi++;
		$lbstring .= "1:".$user["userName"].":2:".$user["userID"].":13:".$user["coins"].":17:".$user["userCoins"].":6:".$xi.":9:".$user["icon"].":10:".$user["color1"].":11:".$user["color2"].":14:".$user["iconType"].":15:".$user["special"].":16:".$extid.":3:".$user["stars"].":8:".round($user["creatorPoints"],0,PHP_ROUND_HALF_DOWN).":4:".$user["demons"].":7:".$extid.":46:".$user["diamonds"]."|";
	}
}
if($lbstring == ""){
	exit("-1");
}
$lbstring = substr($lbstring, 0, -1);
echo $lbstring;
?>