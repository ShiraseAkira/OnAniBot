<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://shikimori.one/api/calendar");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$output = curl_exec($ch);
curl_close($ch);
$schedule = json_decode($output); //TODO будет обернуо в функцию вынесено в отдельный файл

$mysqlli = new mysqli("eu-cdbr-west-02.cleardb.net", "b2b48db1e8befd",
    "8113a8b7", "heroku_717c9367403bbb5");
if($mysqlli->connect_errno) {
    error_log("Ошибка: " . $mysqlli->connect_errno);
}

foreach ($schedule as $scheduleItem) {
    $shikiid = $scheduleItem->anime->id;
    $name = $scheduleItem->anime->name;
    $shikiurl = $scheduleItem->anime->url;
    $episodesAired = $scheduleItem->anime->episodes_aired;

    $sql = "INSERT INTO `anime`(
                    `shikiid`,
                    `name`,
                    `url`,
                    `episodesAired`
                    )
            VALUES (
            '".$shikiid."',
            '".$name."',
            '".$shikiurl."',
            '".$episodesAired."'
            )
            ON DUPLICATE KEY UPDATE
            `episodesAired` = '".$episodesAired."'";

    if($scheduleItem->anime->status == "ongoing" || $scheduleItem->anime->status == "release") {
        if($mysqlli->query($sql) === TRUE) {
            if($mysqlli->affected_rows){
                $sql = "INSERT IGNORE INTO `notifications`
                        SELECT `watchlistid` FROM `watchlist`
                        WHERE `shikiid` = '".$shikiid."'";
                if($mysqlli->query($sql) === FALSE) {
                    error_log("Error: ".$sql.PHP_EOL.$mysqlli->error);
                }
            }
        } else {
            error_log("Error: ".$sql.PHP_EOL.$mysqlli->error);
        }
    }
}