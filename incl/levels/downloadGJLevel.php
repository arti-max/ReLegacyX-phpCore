<?php
chdir(dirname(__FILE__));
include "../lib/connection.php";
require_once "../lib/Lib.php";
$gs = new Lib();

if(empty($_POST["gameVersion"])){
	$gameVersion = 1;
}else{
	$gameVersion = $_POST["gameVersion"];
}
if(empty($_POST["levelID"])){
	exit("-1");
}

$inc = !empty($_POST["inc"]) && $_POST["inc"];
$levelID = $_POST["levelID"];
if(!is_numeric($levelID)){
	echo -1;
}else{
	//downloading the level
	$query=$db->prepare("SELECT * FROM levels WHERE levelID = :levelID");

	$query->execute([':levelID' => $levelID]);
	$lvls = $query->rowCount();
	if($lvls!=0){
		$result = $query->fetch();

		//Verifying friends only unlisted

		//adding the download
		$query6 = $db->prepare("INSERT INTO downloads (levelID) VALUES (:levelID)");
		$query6->execute([':levelID' => $levelID]);
		
		$query2=$db->prepare("UPDATE levels SET downloads = downloads + 1 WHERE levelID = :levelID");
		$query2->execute([':levelID' => $levelID]);
		//getting the days since uploaded... or outputting the date in Y-M-D format at least for now...
		$uploadDate = date("d-m-Y G-i", $result["uploadDate"]);
		$updateDate = date("d-m-Y G-i", $result["updateDate"]);
		//password xor
		$desc = $result["levelDesc"];
		$pass = $result["password"];
		//submitting data
		if(file_exists("../../data/$levelID")){
			$levelstring = file_get_contents("../../data/$levelID");
		}else{
			$levelstring = $result["levelString"];
		}
		$response = "1:".$result["levelID"].":2:".$result["levelName"].":3:".$desc.":4:".$levelstring.":5:".$result["levelVersion"].":6:".$result["userID"].":8:10:9:".$result["difficulty"].":10:".$result["downloads"].":11:10:12:".$result["audioTrack"].":13:".$result["gameVersion"].":14:".$result["likes"].":19:".$result["F_POS"].":15:".$result["levelLength"].":28:$uploadDate".":18:".$result["isStars"].":17:".$result["isDemon"].":35:".$result["songID"].":29:".$updateDate.":27:".$pass;
		echo $response;
	}else{
		echo -1;
	}
}
?>


