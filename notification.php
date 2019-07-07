<?php
require_once("database.php");
$database = getDatabaseConnection();
$mailingList = getMailingList($database);

include('vendor/autoload.php');
use Telegram\Bot\Api;
const TOKEN = "639677299:AAEIo8bfRnC5axKEUuJG1l_LuBSHLmSD3ao";
$telegram = new Api(TOKEN);

if($telegram->getMe()) {
    truncateMailingList($database);
    while($message = $mailingList->fetch_object()){
        $reply = "Вышла ".$message->episodesAired." серия ".$message->name.PHP_EOL;
        $telegram->sendMessage([
                                'chat_id' => $message->chatid,
                                'text' => $reply
                                ]);
    }
}