<?php
include "../incl/lib/connection.php";
include "../incl/lib/Lib.php";

$name = $_POST["userName"];
$pass = $_POST["password"];
$mail = $_POST["email"];

$query = $db->prepare("SELECT * FROM accounts WHERE name = '$name' LIMIT 1");
$query->execute();
if ($query->rowCount() > 0) {
    exit("-1");
}

$query2 = $db->prepare("INSERT INTO accounts (name, pass, mail) VALUES (:name, :pass, :mail)");
$query2->execute([':name' => $name, ':pass' => $pass, ':mail' => $mail]);

//echo $db->lastInsertId();
echo 1;
?>