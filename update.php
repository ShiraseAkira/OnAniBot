<?php
const ongoingStatus = "ongoing";
const releaseStatus = "release";

require_once ("api.php");
require_once ("database.php");
$schedule = getSchedule();

$database = getDatabaseConnection();

foreach ($schedule as $scheduleItem) {
    $shikiId = $scheduleItem->anime->id;
    $name = $scheduleItem->anime->name;
    $shikiUrl = $scheduleItem->anime->url;
    $episodesAired = $scheduleItem->anime->episodes_aired;

    if($scheduleItem->anime->status == ongoingStatus || $scheduleItem->anime->status == releaseStatus) {
        if(updateAnimeList($database, $shikiId, $name, $shikiUrl, $episodesAired)) {
            if($database->affected_rows){
                updateNotificationList($database, $shikiId);
            }
        }
    }
}