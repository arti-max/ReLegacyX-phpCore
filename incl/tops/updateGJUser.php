<?php
chdir(dirname(__FILE__));
include "../lib/connection.php";
require_once "../lib/Lib.php";
$gs = new Lib();

$stars = 0;
$demons = 0;
$coins = 0;
$ship = 0;
$special = 0;

if(empty($_POST["udid"]) AND empty($_POST["accountID"]))
	exit("-1");


$userName = filter_input(INPUT_POST, 'userName', FILTER_SANITIZE_STRING);
$stars = filter_input(INPUT_POST, 'stars', FILTER_VALIDATE_INT, ['options' => ['default' => 0]]);
$demons = filter_input(INPUT_POST, 'demons', FILTER_VALIDATE_INT, ['options' => ['default' => 0]]);
$icon = filter_input(INPUT_POST, 'icon', FILTER_VALIDATE_INT); 
$ship = filter_input(INPUT_POST, 'ship', FILTER_VALIDATE_INT, ['options' => ['default' => 0]]);
$color1 = filter_input(INPUT_POST, 'color1', FILTER_VALIDATE_INT);
$color2 = filter_input(INPUT_POST, 'color2', FILTER_VALIDATE_INT);
$udid = filter_input(INPUT_POST, 'udid', FILTER_SANITIZE_STRING);
$coins = filter_input(INPUT_POST, 'coins', FILTER_VALIDATE_INT, ['options' => ['default' => 0]]);
$iconType = filter_input(INPUT_POST, 'iconType', FILTER_VALIDATE_INT, ['options' => ['default' => 0]]);
$special = filter_input(INPUT_POST, 'special', FILTER_VALIDATE_INT);
$gameVersion = filter_input(INPUT_POST, 'gameVersion', FILTER_SANITIZE_STRING);
$secret = filter_input(INPUT_POST, 'secret', FILTER_SANITIZE_STRING); 
$ip = $gs->getIP();

if (!empty($_POST["accountID"])) {
    $udid = $_POST["accountID"];
}

//$udid = $gs->getIDFromPost();
$userID = $gs->getUserID($udid, $userName, $ip);

$query = $db->prepare("SELECT stars, demons, coins FROM users WHERE userID=:userID LIMIT 1");
$query->execute([':userID' => $userID]);
$old = $query->fetch();

$query2 = $db->prepare("INSERT INTO actions (type, value, timestamp, value2, value3, account, accIP) VALUES ('9', :stars, :timestamp, :coinsd, :demon, :account, :ip)"); //creating the action

$starsdiff = $stars - $old["stars"];
$coindiff = $coins - $old["coins"];
$demondiff = $demons - $old["demons"];

$query = $db->prepare("UPDATE users SET userName=:userName, stars=:stars, demons=:demons, icon=:icon, color1=:color1, color2=:color2, ship=:ship, iconType=:iconType, coins=:coins, IP=:ip WHERE userID=:userID");
$query->execute([':stars' => $stars, ':demons' => $demons, ':icon' => $icon, ':color1' => $color1, ':color2' => $color2, ':userName' => $userName, ':userID' => $userID, ':ship' => $ship, ':iconType' => $iconType, ':coins' => $coins, ':ip'=>$ip]);

$query2->execute([':timestamp' => time(), ':stars' => $starsdiff, ':account' => $userID, ':coinsd' => $coindiff, ':demon' => $demondiff, ':ip' => $ip]);


echo '-9';
//echo $userID;

