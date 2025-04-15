<?php
chdir(dirname(__FILE__));
include "../lib/connection.php";

$levelsInfo = $_POST["levelsInfo"];
$udid = $_POST["udid"];

$query = $db->prepare("SELECT count(*) FROM restore WHERE udid=:udid");
$query->execute([':udid' => $udid]);
$count = $query->fetchColumn();
$levelsArray = explode(';', $levelsInfo);
$completed = "";

$totalAtts = 0;
$totalJumps = 0;

foreach ($levelsArray as &$level) {
    $lvlArr = explode(',', $level);
    $levelID = $lvlArr[1];
    $completed .= "$levelID,";
    $totalAtts += $lvlArr[3];
    $totalJumps += $lvlArr[5];
}

if ($count < 1) {
    $query2 = $db->prepare("INSERT INTO restore (udid, levelsInfo, completed) VALUES (:udid, :li, :c)");
} else {
    $query2 = $db->prepare("UPDATE restore SET levelsInfo=:li, completed=:c WHERE udid=:udid");
}
$query2->execute([':udid' => $udid, ':li' => $levelsInfo, ':c' => $completed]);

echo $levelsInfo;