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
    return updateDataInDatabase($database, $sql);
}

function checkIfNewUserAndAdd($database, $chat_id) {
    $sql = "INSERT IGNORE INTO `users`(
                    `chatid`
                    )
            VALUES (
            '".$chat_id."'
            )";
    return updateDataInDatabase($database, $sql);
}

function getOngoingList($database){
    $sql = "SELECT anime.name FROM `anime`";
    return getDataFromDatabase($database, $sql);
}

function getShikiidByNumberInList($database, $numberInList) {
    $sql = "SELECT `shikiid` 
            FROM `anime`
            LIMIT ".($numberInList - 1).", 1";
    return getDataFromDatabase($database, $sql);
}

function addToWatchlist($database, $shikiid, $chatid) {
    $sql = "INSERT IGNORE INTO watchlist (
                            watchlist.chatid, watchlist.shikiid
                            )
            SELECT users.chatid, anime.shikiid
            FROM users, anime
            WHERE users.chatid = ".$chatid." AND anime.shikiid = ".$shikiid;
    return updateDataInDatabase($database, $sql);
}

function getWatchList($database, $chatid) {
    $sql = "SELECT anime.name 
            FROM anime, watchlist
            WHERE anime.shikiid = watchlist.shikiid AND watchlist.chatid = ".$chatid;
    return getDataFromDatabase($database, $sql);
}

function getWatchlistItemByNumberInList($database, $numberInList) {
    $sql = "SELECT watchlistid 
            FROM watchlist
            ORDER BY watchlistid
            LIMIT ".($numberInList - 1).", 1";
    return getDataFromDatabase($database, $sql);
}

function removeFromWatchlist($database, $watchlistid) {
    $sql = "DELETE FROM watchlist
            WHERE watchlistid = ".$watchlistid;
    return updateDataInDatabase($database, $sql);
}