<?php
const ONGOING_STATUS = "ongoing";
const RELEASE_STATUS = "release";

require_once ("api.php");
require_once ("database.php");

$schedule = getSchedule();
$database = getDatabaseConnection();

foreach ($schedule as $scheduleItem) {
    $shikiId = $scheduleItem->anime->id;
    $name = $scheduleItem->anime->name;
    $shikiUrl = $scheduleItem->anime->url;
    $episodesAired = $scheduleItem->anime->episodes_aired;

    if($scheduleItem->anime->status == ONGOING_STATUS || $scheduleItem->anime->status == RELEASE_STATUS) {
        if(updateAnimeList($database, $shikiId, $name, $shikiUrl, $episodesAired)) {
            if($database->affected_rows){
                updateNotificationList($database, $shikiId);
            }
        }
    }
}