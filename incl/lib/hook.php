<?php

class Hook {
    public static function PostToFH($index, $type, $dataR) {
        $data = array(
        'index' => $index, 
        'type' => $type,
        'data' => $dataR
        );

        $ch = curl_init("https://onegdpslgcy.ps.fhgdps.com/sendHook.php");

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);

        if(curl_errno($ch)){
            echo 'cURL Error:' . curl_error($ch);
        } else {
            // Обработка ответа
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            // echo "HTTP Code: " . $httpCode . "\n";
            // echo "Response: " . $response . "\n";
        }

        curl_close($ch);

    }

    public static function Send($index, $type, $data) {
        include "connection.php";

        $stars = $data['stars'];
        $levelID = $data['levelID'];
        $levelName = $data['levelName'];
        $modName = (isset($data['modName'])) ? $data['modName'] : "";
        $creatorName = $data['creatorName'];

        switch ($stars) {
            case 1:
            case 2:
                $diff = "easy";
                break;
            case 3:
                $diff = "normal";
                break;
            case 4:
            case 5:
                $diff = "hard";
                break;
            case 6:
            case 7:
                $diff = "harder";
                break;
            case 8:
            case 9:
                $diff = "insane";
                break;
            case 10:
                $diff = "demon";
                break;
        }

        $emoji = [
            "easy" => "<:Easy:1275203043834597500>",
            "normal" => "<:Normal:1275203131092766781>",
            "hard" => "<:Hard:1275203204795207791>",
            "harder" => "<:harder:1275203277796937819>",
            "insane" => "<:Insane:1275203390023929856>",
            "demon" => "<:Demon:1275203483577880576>"
        ];
        
        $desc = "";

        $diff = $emoji[$diff];
        $color = "";
        $hookName = '';

        switch ($type) {
            case 3: // SubReq
            case 1: // req

                $title2 = "";
                if ($type == 3) {
                    $title2 = "$levelName by $creatorName has been Sub - Requested";
                } else {
                    $title2 = "$levelName by $creatorName has been requested";
                }
                $desc = "levelID: $levelID \n stars: $stars \n diff: $diff";
                $color = "#FFDE59";
                $hookName = "Requests";
                break;
            case 2: // rate
                $title2 = "$levelName by $creatorName has been rated!";
                $desc = "levelID: $levelID \n stars: $stars \n diff: $diff";
                $color = "#FFE57F";
                $hookName = "New Rates";
                break;

        }




        //=======================================================================================================
        // Create new webhook in your Discord channel settings and copy&paste URL
        //=======================================================================================================

        $webhookurl = $HOOKS[$index];

        //=======================================================================================================
        // Compose message. You can use Markdown
        // Message Formatting -- https://discordapp.com/developers/docs/reference#message-formatting
        //========================================================================================================

        $timestamp = date("c", strtotime("now"));

        $json_data = json_encode([
            // Message
            //"content" => "Hello World! This is message line ;) And here is the mention, use userID <@12341234123412341>",
            
            // Username
            "username" => $hookName,

            // Avatar URL.
            // Uncoment to replace image set in webhook
            //"avatar_url" => "https://ru.gravatar.com/userimage/28503754/1168e2bddca84fec2a63addb348c571d.jpg?size=512",

            // Text-to-speech
            "tts" => false,

            // File upload
            // "file" => "",

            // Embeds Array
            "embeds" => [
                [
                    // Embed Title
                    "title" => $title2,

                    // Embed Type
                    "type" => "rich",

                    // Embed Description
                    "description" => $desc,

                    // URL of title link
                    //"url" => "https://gist.github.com/Mo45/cb0813cb8a6ebcd6524f6a36d4f8862c",

                    // Timestamp of embed must be formatted as ISO8601
                    "timestamp" => $timestamp,

                    // Embed left border color in HEX
                    "color" => hexdec($color),

                    // Footer
                    "footer" => [
                        "text" => $modName,
                        //"icon_url" => "https://ru.gravatar.com/userimage/28503754/1168e2bddca84fec2a63addb348c571d.jpg?size=375"
                    ],

                    // Image to send
                    //"image" => [
                    //    "url" => "https://ru.gravatar.com/userimage/28503754/1168e2bddca84fec2a63addb348c571d.jpg?size=600"
                    //],

                    // Thumbnail
                    //"thumbnail" => [
                    //    "url" => "https://ru.gravatar.com/userimage/28503754/1168e2bddca84fec2a63addb348c571d.jpg?size=400"
                    //],

                    // Author
                    /*"author" => [
                        "name" => "krasin.space",
                        "url" => "https://krasin.space/"
                    ],

                    // Additional Fields array
                    "fields" => [
                        // Field 1
                        [
                            "name" => "Field #1 Name",
                            "value" => "Field #1 Value",
                            "inline" => false
                        ],
                        // Field 2
                        [
                            "name" => "Field #2 Name",
                            "value" => "Field #2 Value",
                            "inline" => true
                        ]
                        // Etc..
                    ]*/
                ]
            ]

        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );


        $ch = curl_init( $webhookurl );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
        curl_setopt( $ch, CURLOPT_POST, 1);
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $json_data);
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt( $ch, CURLOPT_HEADER, 0);
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec( $ch );
        // If you need to debug, or find out why you can't send message uncomment line below, and execute script.
        // echo $response;
        curl_close( $ch );

    }

}