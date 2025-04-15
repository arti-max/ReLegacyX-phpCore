<?php
chdir(dirname(__FILE__));
include "../lib/connection.php";

$page = $_POST['page'];
$gameVersion = (!empty($_POST['gameVersion'])) ? $_POST['gameVersion'] : 10;

$packpage = $page*10;
$mappackstring = "";
$lvlsmultistring = "";
$query = $db->prepare("SELECT color,id,name,levels,stars,coins,diff FROM `mappacks` WHERE gameVersion <= :gm ORDER BY `id` ASC LIMIT 10 OFFSET $packpage ");
$query->execute([':gm' => $gameVersion]);
$result = $query->fetchAll();
$packcount = $query->rowCount();
foreach($result as &$mappack) {
	$lvlsmultistring .= $mappack["id"] . ",";
	$color = $mappack["color"];
	$mappackstring .= "1:".$mappack["id"].":2:".$mappack["name"].":3:".$mappack["levels"].":4:".$mappack["stars"].":5:".$mappack["coins"].":6:".$mappack["diff"].":7:".$mappack["color"]."|";
}
$query = $db->prepare("SELECT count(*) FROM mappacks WHERE gameVersion <= :gm");
$query->execute([':gm' => $gameVersion]);
$totalpackcount = $query->fetchColumn();
$mappackstring = substr($mappackstring, 0, -1);
$lvlsmultistring = substr($lvlsmultistring, 0, -1);
echo $mappackstring;
echo "#".$totalpackcount.":".$packpage.":10";
echo "#";