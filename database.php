<?php
const DB_HOST = "eu-cdbr-west-02.cleardb.net";
const DB_USERNAME = "b2b48db1e8befd";
const DB_PASSWORD = "8113a8b7";
const DB_NAME = "heroku_717c9367403bbb5";

function getDatabaseConnection(): ?object {
    $mysqlli = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
    if($mysqlli->connect_errno) {
        error_log("Error: " . $mysqlli->connect_errno);
        return null;
    } else {
        return $mysqlli;
    }
}

function getDataFromDatabase($database, $sql): ?object {
    if(!$result = $database->query($sql)) {
        error_log("Error: ".$sql.PHP_EOL.$database->error);
        return null;
    } else {
        return $result;
    }
}

function updateDataInDatabase($database, $sql): ?bool {
    if(!$database->query($sql)) {
        error_log("Error: ".$sql.PHP_EOL.$database->error);
        return null;
    } else {
        return true;
    }
}

function getMailingList($database): ?object {
    $sql = "SELECT users.chatid, anime.name, anime.episodesAired
        FROM `users`, `watchlist`, `notifications`, `anime`
        WHERE users.chatid = watchlist.chatid
        AND watchlist.watchlistid = notifications.notificationid
        AND anime.shikiid = watchlist.shikiid";
    return getDataFromDatabase($database, $sql);
}

function truncateMailingList($database): ?bool {
    $sql = "TRUNCATE `notifications`";
    return updateDataInDatabase($database, $sql);
}

function updateAnimeList($database, $shikiId, $name, $shikiUrl, $episodesAired): ?bool {
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

function updateNotificationList($database, $shikiId): ?bool {
    $sql = "INSERT IGNORE INTO `notifications`
            SELECT `watchlistid` FROM `watchlist`
            WHERE `shikiid` = '".$shikiId."'";
    return updateDataInDatabase($database, $sql);
}

function checkIfNewUserAndAdd($database, $chatId): ?bool {
    $sql = "INSERT IGNORE INTO `users`(
                    `chatid`
                    )
            VALUES (
            '".$chatId."'
            )";
    return updateDataInDatabase($database, $sql);
}

function getOngoingList($database): ?object {
    $sql = "SELECT anime.name, anime.shikiid, anime.url
            FROM `anime`";
    return getDataFromDatabase($database, $sql);
}

function addToWatchlist($database, $shikiId, $chatId): ?bool {
    $sql = "INSERT IGNORE INTO watchlist (
                            watchlist.chatid, watchlist.shikiid
                            )
            SELECT users.chatid, anime.shikiid
            FROM users, anime
            WHERE users.chatid = ".$chatId." AND anime.shikiid = ".$shikiId;
    return updateDataInDatabase($database, $sql);
}

function getAnimeNameByShikiId($database, $shikiId): ?object {
    $sql = "SELECT anime.name
            FROM `anime`
            WHERE anime.shikiid = ".$shikiId;
    return getDataFromDatabase($database, $sql);
}

function getWatchList($database, $chatId): ?object {
    $sql = "SELECT anime.name, anime.shikiid 
            FROM anime, watchlist
            WHERE anime.shikiid = watchlist.shikiid AND watchlist.chatid = ".$chatId;
    return getDataFromDatabase($database, $sql);
}

function removeFromWatchlist($database, $shikiId, $chatId): ?bool {
    $sql = "DELETE FROM watchlist
            WHERE shikiid = ".$shikiId." AND chatid = ".$chatId;
    return updateDataInDatabase($database, $sql);
}