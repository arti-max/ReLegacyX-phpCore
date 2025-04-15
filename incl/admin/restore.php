<?php
chdir(dirname(__FILE__));
include "../lib/connection.php";

$udid = $_POST['udid'];


$query = $db->prepare("SELECT * FROM users WHERE udid=:udid");
$query->execute([':udid' => $udid]);
$result = $query->fetch();


$query2 = $db->prepare("SELECT * FROM restore WHERE udid=:udid");
$query2->execute([':udid' => $udid]);
$result2 = $query2->fetch();

$stars = $result["stars"];
$demons = $result["demons"];
$completed = $result2["completed"];
$jumps = $result2["jumps"];
$attempts = $result2["attempts"];

echo "1:1,1,3;:2:$stars:3:$demons:4:$jumps:5:$attempts:6:$completed";