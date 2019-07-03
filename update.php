<?php
require_once ("api.php");
require_once ("database.php");
$schedule = getSchedule();

$database = getDatabaseConnection();

foreach ($schedule as $scheduleItem) {
    $shikiid = $scheduleItem->anime->id;
    $name = $scheduleItem->anime->name;
    $shikiurl = $scheduleItem->anime->url;
    $episodesAired = $scheduleItem->anime->episodes_aired;

    if($scheduleItem->anime->status == "ongoing" || $scheduleItem->anime->status == "release") {
        if(updateAnimeList($database, $shikiid, $name, $shikiurl, $episodesAired)) {
            if($database->affected_rows){
               $a = updateNotificationList($database, $shikiid);
            }
        }
    }
}