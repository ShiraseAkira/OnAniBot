<?php
require_once("database.php");
$databse = getDatabaseConnection();

$sql = "SELECT users.chatid, anime.name, anime.episodesAired
        FROM `users`, `watchlist`, `notifications`, `anime`
        WHERE users.chatid = watchlist.chatid
        AND watchlist.watchlistid = notifications.notificationid
        AND anime.shikiid = watchlist.shikiid";

if(!$result = $databse >query($sql)) {
    error_log("Error: ".$sql.PHP_EOL.$mysqlli->error);
}

$sql = "TRUNCATE `notifications`";
if(! $databse->query($sql)) {
    error_log("Error: ".$sql.PHP_EOL.$mysqlli->error);
}

include('vendor/autoload.php'); //Подключаем библиотеку
use Telegram\Bot\Api;
$telegram = new Api('639677299:AAEIo8bfRnC5axKEUuJG1l_LuBSHLmSD3ao'); //Устанавливаем токен, полученный у BotFather

while($message = $result->fetch_object()){
    $reply = "Вышла ".$message->episodesAired." серия ".$message->name.PHP_EOL;
    $telegram->sendMessage(['chat_id' => $message->chatid, 'text' => $reply]);
}