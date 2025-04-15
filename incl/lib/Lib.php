<?php

include_once __DIR__ . "/ip_in_range.php";
class Lib {
  public function getIDFromPost(){

		if(!empty($_POST["udid"])) 
		{
			$id = $_POST["udid"];
		}
		else
		{
			exit("-1");
		}
		return $id;
	}

	public function getIP(){
		if (isset($_SERVER['HTTP_CF_CONNECTING_IP']) && $this->isCloudFlareIP($_SERVER['REMOTE_ADDR'])) //CLOUDFLARE REVERSE PROXY SUPPORT
  			return $_SERVER['HTTP_CF_CONNECTING_IP'];
		if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && ipInRange::ipv4_in_range($_SERVER['REMOTE_ADDR'], '127.0.0.0/8')) //LOCALHOST REVERSE PROXY SUPPORT (7m.pl)
			return $_SERVER['HTTP_X_FORWARDED_FOR'];
		return $_SERVER['REMOTE_ADDR'];
	}

  public function getUserID($udid, $userName = "Undefined", $ip) {
		include __DIR__ . "/connection.php";
		$query = $db->prepare("SELECT userID FROM users WHERE udid LIKE BINARY :id");
		$query->execute([':id' => $udid]);
		if ($query->rowCount() > 0) {
			$userID = $query->fetchColumn();
		} else {

			$query = $db->prepare("INSERT INTO users (udid, userName, IP) VALUES (:id, :userName, :ip)");

			$query->execute([':id' => $udid, ':userName' => $userName, ':ip' => $ip]);
			$userID = $db->lastInsertId();
		}
		return $userID;
	}

	public function getUserString($userdata) {
		include __DIR__ . "/connection.php";
		$udid = is_numeric($userdata['udid']) ? $userdata['udid'] : 0;
		return "${userdata['userID']}:${userdata['userName']}:${udid}";
	}

	public function getLevelLength__STR($length) {
		$arr = [
			0 => "tiny",
			1 => "short",
			2 => "medium",
			3 => "long",
			4 => "extra-long"
		];

		return $arr[$length];

	}
	

  
}

?>
