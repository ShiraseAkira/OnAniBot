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

function updateAnimeList($database, $shikiId, $name, $shikiUrl, $episodesAired) {
    $sql = "INSERT INTO `anime`(
                    `shikiid`,
                    `name`,
                    `url`,
                    `episodesAired`
                    )
            VALUES (
            '".$shikiId."',
            '".$name."',
            '".$shikiUrl."',
            '".$episodesAired."'
            )
            ON DUPLICATE KEY UPDATE
            `episodesAired` = '".$episodesAired."'";
    return updateDataInDatabase($database, $sql);
}

function updateNotificationList($database, $shikiId) {
    $sql = "INSERT IGNORE INTO `notifications`
            SELECT `watchlistid` FROM `watchlist`
            WHERE `shikiid` = '".$shikiId."'";
    return updateDataInDatabase($database, $sql);
}

function checkIfNewUserAndAdd($database, $chatId) {
    $sql = "INSERT IGNORE INTO `users`(
                    `chatid`
                    )
            VALUES (
            '".$chatId."'
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

function addToWatchlist($database, $shikiId, $chatId) {
    $sql = "INSERT IGNORE INTO watchlist (
                            watchlist.chatid, watchlist.shikiid
                            )
            SELECT users.chatid, anime.shikiid
            FROM users, anime
            WHERE users.chatid = ".$chatId." AND anime.shikiid = ".$shikiId;
    return updateDataInDatabase($database, $sql);
}

function getWatchList($database, $chatId) {
    $sql = "SELECT anime.name 
            FROM anime, watchlist
            WHERE anime.shikiid = watchlist.shikiid AND watchlist.chatid = ".$chatId;
    return getDataFromDatabase($database, $sql);
}

function getWatchlistItemByNumberInList($database, $numberInList) {
    $sql = "SELECT watchlistid 
            FROM watchlist
            ORDER BY watchlistid
            LIMIT ".($numberInList - 1).", 1";
    return getDataFromDatabase($database, $sql);
}

function removeFromWatchlist($database, $watchlistId) {
    $sql = "DELETE FROM watchlist
            WHERE watchlistid = ".$watchlistId;
    return updateDataInDatabase($database, $sql);
}