<?php
chdir(dirname(__FILE__));
include "../lib/connection.php";

// тут херовая защита, сразу говорю

if (empty($_POST["songID"])) {
    exit("-1");
}

$songid = filter_input(INPUT_POST, 'songID', FILTER_SANITIZE_NUMBER_INT);
$query = $db->prepare("SELECT songName, author, authorID, size, download FROM songs WHERE songID = :songid LIMIT 1");
$query->execute([':songid' => $songid]);

if ($query->rowCount() > 0) {
    $result = $query->fetch();

    $dwnl = $result["download"];
    if(strpos($dwnl, ':') !== false){
		$dwnl = urlencode($dwnl);
	}

    echo "1~|~".$songid."~|~2~|~".$result["songName"]."~|~3~|~".$result["authorID"]."~|~4~|~".$result["author"]."~|~5~|~".$result["size"]."~|~6~|~~|~10~|~".$dwnl."~|~7~|~~|~8~|~0";

} else {
    exit("-2");
}
