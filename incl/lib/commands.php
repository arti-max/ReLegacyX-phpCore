<?php

class Commands {

    private static function getUserName($userID) {
        include "connection.php";

        $query = $db->prepare("SELECT userName FROM users WHERE userID=:userID");
        $query->execute([':userID' => $userID]);
        $userName = $query->fetchColumn();

        return $userName;
    }

    private static function checkIsRate($levelID) {
        include "connection.php";
    
        $query = $db->prepare("SELECT isStars FROM levels WHERE levelID=:levelID");
        $query->execute([':levelID' => $levelID]);
    
        return $query->fetchColumn();
    }

    private static function getUserID($udid) {
        include "connection.php";

        $query = $db->prepare("SELECT userID FROM users WHERE udid=:udid");
        $query->execute([':udid' => $udid]);
        $userID = $query->fetchColumn();

        return $userID;

    }

    private static function getRoleID($userID) {
        include "connection.php";

        $query = $db->prepare("SELECT roleID FROM users WHERE userID=:userID");
        $query->execute([':userID' => $userID]);
        $roleID = $query->fetchColumn();

        return $roleID;

    }
    
    private static function getCreatorID($levelID) {
        include "connection.php";

        $query = $db->prepare("SELECT userID FROM levels WHERE levelID=:levelID");
        $query->execute([':levelID' => $levelID]);
        $userID = $query->fetchColumn();

        return $userID;
    }

    private static function updateUserCP($userID, $cp) {
        include "connection.php";

        $query = $db->prepare("UPDATE users SET cp=cp+$cp WHERE userID=:userID");
        $query->execute([':userID' => $userID]);

    }

    private static function getLevelName($levelID) {
        include "connection.php";

        $query = $db->prepare("SELECT levelName FROM levels WHERE levelID=:levelID");
        $query->execute([':levelID' => $levelID]);
        $levelName = $query->fetchColumn();

        return $levelName;
    }

    private static function getRequestedStars($levelID) {
        include "connection.php";

        $query = $db->prepare("SELECT reqStars FROM levels WHERE levelID=:levelID");
        $query->execute([':levelID' => $levelID]);

        $stars = $query->fetchColumn();

        return $stars;
    }

    public static function command($udid, $comment, $levelID) {
        include "connection.php";
        require_once "roles.php";
        require_once "hook.php";

        $userID = self::getUserID($udid);
        $roleID = self::getRoleID($userID);
        $creatorID = self::getCreatorID($levelID);
        $creatorName = self::getUserName($creatorID);
        $modName = self::getUserName($userID);
        $levelName = self::getLevelName($levelID);
        echo "$userID::$roleID::$creatorID";

        $commentarr = explode(' ', $comment);

        $reqData = [
            "levelID" => $levelID,
            "modName" => $modName,
            "creatorName" => $creatorName,
            "levelName" => $levelName,
        ];

        if(substr($comment,0,8) == "!feature" and Roles::getPermissions($roleID, 'setFeature')){
             $isF = $commentarr[1];
             $pos = $commentarr[2];

            $query = $db->prepare("UPDATE levels SET F_POS=:pos, isFeatured=:f WHERE levelID=:levelID");
            $query->execute([':f' => $isF, ':pos' => $pos, ':levelID' => $levelID]);
            self::updateUserCP($creatorID, 1);

             return true;
        }

        if(substr($comment,0,5) == "!rate" and Roles::getPermissions($roleID, 'setStars')) {
            $stars = $commentarr[1];
            $isDemon = 0;
            $CP = -1;
            $diff = 0;
            $stars > 0 ? $CP = 1 : $CP = -1;
            $VERSION = 2;

            switch ($stars) {
                case 0:
                    $CP = -1;
                    break;
                case 1:
                    if ($VERSION > 1) {
                        $diff = 60;
                    } else {
                        $diff = 10;
                    }
                    break;
                case 2:
                    $diff = 10;
                    break;
                case 3:
                    $diff = 20;
                    break;
                case 4:
                case 5:
                    $diff = 30;
                    break;
                case 6:
                case 7:
                    $diff = 40;
                    break;
                case 8:
                case 9:
                    $diff = 50;
                    break;
                case 10:
                    $diff = 50;
                    $isDemon = 1;
            }

            $oldStars = self::checkIsRate($levelID);

            if ($oldStars < 1) {
                $CP = 1;
            } else {
                if ($stars > 0) {
                    $CP = 0;
                } else {
                    $CP = -1;
                    $stars = 0;
                }
            }

            $query = $db->prepare("UPDATE levels SET isStars=:stars, difficulty=:diff, isDemon=:demon, diffOverride=:diff WHERE levelID=:levelID");
            $query->execute([':levelID' => $levelID, ':stars' => $stars, ':diff' => $diff, ':demon' => $isDemon]);
            self::updateUserCP($creatorID, $CP);

            $reqData["stars"] = $stars;

            Hook::PostToFH(1, 2, $reqData);

            return true;
        }

        if (substr($comment,0,4) == "!req" and Roles::getPermissions($roleID, 'req')) {
            $stars = $commentarr[1];
            $isFeatured = $commentarr[2];

            $reqData["stars"] = $stars;
            $reqData["featured"] = $isFeatured;

            $oldReq = self::getRequestedStars($levelID);

            $query = $db->prepare("UPDATE levels SET reqStars=:stars WHERE levelID=:levelID");
            $query->execute([':stars' => $stars, ':levelID' => $levelID]);

            $type = 1;

            if ($oldReq > 0) {
                $type = 3;
            }

            Hook::PostToFH(0, $type, $reqData);

            return true;
        }

        if (substr($comment, 0, 9) == "!unlisted" and Roles::getPermissions($roleID, 'unlisted') and $userID == $creatorID) {

            $query = $db->prepare("UPDATE levels SET unlisted=1 WHERE levelID=:levelID");
            $query->execute([':levelID' => $levelID]);

            return true;

        }

        if (substr($comment, 0, 9) == "!inlisted" and Roles::getPermissions($roleID, 'inlisted') and $userID == $creatorID) {

            $query = $db->prepare("UPDATE levels SET unlisted=0 WHERE levelID=:levelID");
            $query->execute([':levelID' => $levelID]);

            return true;

        }



        return false;


    }


}