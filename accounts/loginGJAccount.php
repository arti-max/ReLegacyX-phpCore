<?php
include "../incl/lib/connection.php";
include "../incl/lib/Lib.php";
$gs = new Lib();

$ip = $gs->getIP();
$udid = $_POST["udid"];
$name = $_POST["userName"];
$pass = $_POST["password"];

$query = $db->prepare("SELECT accID FROM accounts WHERE name LIKE :userName");
$query->execute([':userName' => $name]);
if($query->rowCount() == 0){
	exit("-1");
}
$id = $query->fetchColumn();

//userID
$query2 = $db->prepare("SELECT userID FROM users WHERE udid = :id");

$query2->execute([':id' => $id]);
if ($query2->rowCount() > 0) {
    $userID = $query2->fetchColumn();
} else {
    $query = $db->prepare("INSERT INTO users (udid, userName, isReg) VALUES (:id, :userName, 1)");

    $query->execute([':id' => $id, ':userName' => $name]);
    $userID = $db->lastInsertId();
}
//logging
$query6 = $db->prepare("INSERT INTO actions (type, value, timestamp, value2) VALUES ('2', :username, :time, :ip)");
$query6->execute([':username'=>$name, ':time'=>time(), ':ip'=>$ip]);
//result
echo $id.",".$userID;
if(!is_numeric($udid)){
    $query2 = $db->prepare("SELECT userID FROM users WHERE udid = :udid");
    $query2->execute([':udid' => $udid]);
    $usrid2 = $query2->fetchColumn();
    $query2 = $db->prepare("UPDATE levels SET userID=:userID, udid=:udid WHERE userID=:usrid2");
    $query2->execute([':userID'=>$userID, ':udid'=>$id, ':usrid2'=>$usrid2]);	
}

?>