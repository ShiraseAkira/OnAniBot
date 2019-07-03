<?php
const dbHost = "eu-cdbr-west-02.cleardb.net";
const dbUsername = "b2b48db1e8befd";
const dbPasswd = "8113a8b7";
const dbName = "heroku_717c9367403bbb5";

function getDatabaseConnection() {
    $mysqlli = new mysqli(dbHost, dbUsername, dbPasswd, dbName);
    if($mysqlli->connect_errno) {
        error_log("Ошибка: " . $mysqlli->connect_errno);
        return NULL;
    } else {
        return $mysqlli;
    }
}

function getDataFromDatabase($database, $sql) {
    if(!$result = $database->query($sql)) {
        error_log("Error: ".$sql.PHP_EOL.$database->error);
        return NULL;
    } else {
        return $result;
    }
}

function updateDataInDatabase($database, $sql) {
    if(!$database->query($sql)) {
        error_log("Error: ".$sql.PHP_EOL.$database->error);
        return NULL;
    } else {
        return TRUE;
    }
}

function getMailingList($database) {
    $sql = "SELECT users.chatid, anime.name, anime.episodesAired
        FROM `users`, `watchlist`, `notifications`, `anime`
        WHERE users.chatid = watchlist.chatid
        AND watchlist.watchlistid = notifications.notificationid
        AND anime.shikiid = watchlist.shikiid";
    return getDataFromDatabase($database, $sql);
}

function truncateMailingList($database) {
    $sql = "TRUNCATE `notifications`";
    return updateDataInDatabase($database, $sql);
}

function updateAnimeList($database, $shikiid, $name, $shikiurl, $episodesAired) {
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
    return updateDataInDatabase($database, $sql);
}

function updateNotificationList($database, $shikiid) {
    $sql = "INSERT IGNORE INTO `notifications`
            SELECT `watchlistid` FROM `watchlist`
            WHERE `shikiid` = '".$shikiid."'";
    $temp = updateDataInDatabase($database, $sql);
    error_log("updNotList worked ". $temp);
    return $temp;
}