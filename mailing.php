<?php
require_once("database.php");
$database = getDatabaseConnection();
$mailingList = getMailingList($database);

include('vendor/autoload.php');
use Telegram\Bot\Api;
$telegram = new Api('639677299:AAEIo8bfRnC5axKEUuJG1l_LuBSHLmSD3ao');

if($telegram->getMe()) {
    truncateMailingList($database);
    while($message = $mailingList->fetch_object()){
        $reply = "Вышла ".$message->episodesAired." серия ".$message->name.PHP_EOL;
        $telegram->sendMessage(['chat_id' => $message->chatid, 'text' => $reply]);
    }
}