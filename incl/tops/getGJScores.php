<?php
chdir(dirname(__FILE__));
include "../lib/connection.php";
$stars = 0;
$count = 0;
$xx = 0;

// Валидация и санитизация входных данных
$udid = filter_input(INPUT_POST, 'udid', FILTER_SANITIZE_STRING);
$type = filter_input(INPUT_POST, 'type', FILTER_SANITIZE_STRING);

if (empty($type) || !in_array($type, ['top', 'relative', 'week'])) {
    exit("-1");
}

$lbstring = "";

if ($type != "") {
    if ($type == "top" || $type == "relative") {
        if ($type == "top") {
            //$query = "SELECT * FROM users WHERE stars > 0 AND lbBan = 0 ORDER BY stars DESC LIMIT 50";
            $query = $db->prepare("SELECT * FROM users WHERE stars > 0 AND lbBan = 0 ORDER BY stars DESC LIMIT 50");
            $query->execute();
        }
        if($type == "relative"){
            // Сначала получим все данные пользователя
            $query = $db->prepare("SELECT * FROM users WHERE udid = :udid AND lbBan = 0");
            $query->execute([':udid' => $udid]);
            $result = $query->fetchAll();
            
            // Проверяем, найден ли пользователь
            if(empty($result)) {
                exit("-4");
            }
            
            $currentUser = $result[0];
            
            // Получим всех пользователей с звездами, отсортированных по звездам
            $query = $db->prepare("SELECT * FROM users WHERE stars > 0 AND lbBan = 0 ORDER BY stars DESC");
            $query->execute();
            $allUsers = $query->fetchAll();
            
            if(empty($allUsers)) {
                exit("-3");
            }
            
            // Найдем позицию текущего пользователя
            $rank = 1;
            $found = false;
            foreach($allUsers as $index => $user) {
                if($user['userID'] == $currentUser['userID']) {
                    $rank = $index + 1;
                    $found = true;
                    break;
                }
            }
            
            if(!$found) {
                $rank = count($allUsers); // Если пользователь не найден, поместим его в конец
            }
            
            // Определим диапазон для отображения (10 выше и 10 ниже)
            $startIndex = max(0, $rank - 11);
            $endIndex = min($startIndex + 20, count($allUsers));
            
            // Выберем только нужный диапазон пользователей
            $result = array_slice($allUsers, $startIndex, $endIndex - $startIndex);
            
            if(empty($result)) {
                exit("-2");
            }
            
            // Устанавливаем начальную позицию для отображения
            $xx = $startIndex;
        } else {
            $result = $query->fetchAll();
        }
        foreach($result as &$user) {
            $udid = 0;
            $udid = $user["udid"];
            $xx++;
            if ($user['lbBan'] == 0) {
                $lbstring .= "1:".$user["userName"].":2:".$user["userID"].":6:".$xx.":9:".$user["icon"].":10:".$user["color1"].":11:".$user["color2"].":3:".$user["stars"].":8:".round($user["creatorPoints"],0,PHP_ROUND_HALF_DOWN).":4:".$user["demons"].":7:".$udid.":12:".$user["ship"].":14:".$user["iconType"].":13:".$user["coins"]."|";
            } else {
                $xx--;
            }
        }

        // if($type == "relative")
        // {
        //     $query = "SELECT * FROM users WHERE udid = :udid";
        //     $query = $db->prepare($query);
        //     $query->execute([':udid' => $udid]);
        //     $result = $query->fetchAll();
        //     $user = $result[0];
        //     $stars = $user["stars"];
        //     $query = $db->prepare("SELECT A.* FROM ((SELECT * FROM users WHERE stars <= :stars ORDER BY stars DESC LIMIT 10) UNION (SELECT * FROM users WHERE stars >= :stars ORDER BY stars ASC LIMIT 10)) as A ORDER BY A.stars DESC");
        //     $query->execute([':stars' => $stars]);
        // }
        // //$query = $db->prepare($query);

        // $result = $query->fetchAll();
        // if($type == "relative"){
        //     $user = $result[0];
        //     $udid = $user["udid"];
        //     $query = $db->prepare("SET @rownum = 0;");
        //     $query->execute();
        //     $query = $db->prepare("SELECT rank, stars FROM (SELECT @rownum := @rownum + 1 AS rank, stars, udid, lbBan FROM users WHERE lbBan = 0 ORDER BY stars DESC) AS result WHERE udid=:udid");
        //     $query->execute([':udid' => $udid]);
        //     $leaderboard = $query->fetchAll();
        //     //var_dump($leaderboard);
        //     $leaderboard = $leaderboard[0];
        //     $xx = $leaderboard["rank"] - 1;
        // }
        // foreach($result as &$user) {
        //     $xx++;
        //     $udid = $user['udid'];
        //     $lbstring .= "1:".$user["userName"].":2:".$user["userID"].":6:".$xx.":9:".$user["icon"].":10:".$user["color1"].":11:".$user["color2"].":3:".$user["stars"].":8:".round($user["creatorPoints"],0,PHP_ROUND_HALF_DOWN).":4:".$user["demons"].":7:".$udid.":12:".$user["ship"].":14:".$user["iconType"]."|";
        // }
    }
    if ($type == "week") {
        $gains = array();
        $time = time() - 604800;
        $xx = 0;
        $query = $db->prepare("SELECT * FROM actions WHERE type = '9' AND timestamp > :time");
        $query->execute([':time' => $time]);
        $result = $query->fetchAll();
        foreach ($result as &$gain) {
            $account = $gain["account"];
            if (!isset($gains[$account])) {
                $gains[$account] = ['stars' => 0, 'demons' => 0];
            }
            $gains[$account]['stars'] += $gain["value"];
            $gains[$account]['demons'] += $gain["value3"];
        }
        
         // Sort by stars and then demons if stars are the same
        uasort($gains, function($a, $b) {
             if ($a['stars'] == $b['stars']) {
               return $b['demons'] <=> $a['demons']; // Descending by demons if stars are equal
             }
             return $b['stars'] <=> $a['stars']; // Descending by stars
        });
        
        foreach ($gains as $userID => $gain) {
            if ($gain['stars'] == 0 or $xx >= 50) {
               break;
            }
             $query = $db->prepare("SELECT * FROM users WHERE userID = :userID");
            $query->execute([':userID' => $userID]);
            $user = $query->fetchAll()[0];
               if ($user["lbBan"] == 0) {
                $xx++;
                $udid = $user["udid"];
                $stars = $gain['stars'];
                $demons = $gain['demons'];
                $lbstring .= "1:" . $user["userName"] . ":2:" . $user["userID"] . ":6:" . $xx . ":9:" . $user["icon"] . ":10:" . $user["color1"] . ":11:" . $user["color2"] . ":3:" . $stars . ":8:" . round($user["creatorPoints"], 0, PHP_ROUND_HALF_DOWN) . ":4:" . $demons . ":7:" . $udid . ":12:" . $user["ship"] . ":14:" . $user["iconType"] . ":13:" . $user["coins"] . "|";
               }
        }
    }
}

if($lbstring == ""){
	exit("-1");
}
$lbstring = substr($lbstring, 0, -1);
echo $lbstring;
?>

