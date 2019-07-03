<?php
require_once("database.php");
$database = getDatabaseConnection();
$mailingList = getMailingList($database);
truncateMailingList($database);

include('vendor/autoload.php'); //Подключаем библиотеку
use Telegram\Bot\Api;
$telegram = new Api('639677299:AAEIo8bfRnC5axKEUuJG1l_LuBSHLmSD3ao'); //Устанавливаем токен, полученный у BotFather

while($message = $mailingList->fetch_object()){
    $reply = "Вышла ".$message->episodesAired." серия ".$message->name.PHP_EOL;
    $telegram->sendMessage(['chat_id' => $message->chatid, 'text' => $reply]);
}