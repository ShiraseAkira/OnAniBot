<?php
$mysqlli = new mysqli("localhost", "root",
    "", "bot");
if($mysqlli->connect_errno) {
    error_log("Ошибка: " . $mysqlli->connect_errno);
}

$sql = "SELECT users.chatid, anime.name, anime.episodesAired
        FROM `users`, `watchlist`, `notifications`, `anime`
        WHERE users.chatid = watchlist.chatid
        AND watchlist.watchlistid = notifications.notificationid
        AND anime.shikiid = watchlist.shikiid";

if(!$result = $mysqlli->query($sql)) {
    error_log("Error: ".$sql.PHP_EOL.$mysqlli->error);
}

$sql = "TRUNCATE `notifications`";
if(! $mysqlli->query($sql)) {
    error_log("Error: ".$sql.PHP_EOL.$mysqlli->error);
}

include('vendor/autoload.php'); //Подключаем библиотеку
use Telegram\Bot\Api;
$telegram = new Api('639677299:AAEIo8bfRnC5axKEUuJG1l_LuBSHLmSD3ao'); //Устанавливаем токен, полученный у BotFather

while($message = $result->fetch_object()){
    $reply = "Вышла ".$message->episodesAired." серия ".$message->name.PHP_EOL;
    $telegram->sendMessage(['chat_id' => $message->chatid, 'text' => $reply]);
}