<?php
require_once ("database.php");
require_once ("messageProcessing.php");
include('vendor/autoload.php');
use Telegram\Bot\Api;

const startCommand = "/start";
const helpCommand = "/help";
const watchOngoingListCommand = "Посмотреть список онгоингов";
const watchWatchListCommand = "Посмотреть список отслеживаемого";
const addToWatchListCommand = "/add_";
const removeFromWatchListCommand = "/remove_";

$telegram = new Api('639677299:AAEIo8bfRnC5axKEUuJG1l_LuBSHLmSD3ao');
$result = $telegram->getWebhookUpdates();

$text = $result["message"]["text"];
$chat_id = $result["message"]["chat"]["id"];


if ($text) {
    if ($text == startCommand) {
        processStartCommand($telegram, $chat_id);
    } elseif ($text == helpCommand) {
        processHelpCommand($telegram, $chat_id);
    } elseif ($text == watchOngoingListCommand) {
        processWatchOngoingListCommand($telegram, $chat_id);
    } elseif ($text == watchWatchListCommand) {
        processWatchWatchListCommand($telegram, $chat_id);
    } elseif (substr($text, 0, 5) === addToWatchListCommand) {
        processAddToWatchListCommand($telegram, $chat_id, $text);
    } elseif (substr($text, 0, 8) === removeFromWatchListCommand) {
        $numberInList = (int)substr($text, 8);

        $database = getDatabaseConnection();
        $watchListReply = getWatchlistItemByNumberInList($database, $numberInList);
        $watchListObj = $watchListReply ->fetch_object();
        $watchlistid = $watchListObj->watchlistid;

        removeFromWatchlist($database, $watchlistid);

        $reply = "Сериал из списка под номером ".$numberInList." был удален из списока отслеживаемого.";
        $telegram->sendMessage(['chat_id' => $chat_id, 'text' => $reply]);
    } else {
        processNonCommandMessage($telegram, $chat_id);
    }
} else {
    processNonTextMessage($telegram, $chat_id);
}