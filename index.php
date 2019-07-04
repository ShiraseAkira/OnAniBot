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
$chatId = $result["message"]["chat"]["id"];

if ($text) {
    if ($text == startCommand) {
        processStartCommand($telegram, $chatId);
    } elseif ($text == helpCommand) {
        processHelpCommand($telegram, $chatId);
    } elseif ($text == watchOngoingListCommand) {
        processWatchOngoingListCommand($telegram, $chatId);
    } elseif ($text == watchWatchListCommand) {
        processWatchWatchListCommand($telegram, $chatId);
    } elseif (substr($text, 0, 5) === addToWatchListCommand) {
        processAddToWatchListCommand($telegram, $chatId, $text);
    } elseif (substr($text, 0, 8) === removeFromWatchListCommand) {
        processRemoveFromWatchListCommand($telegram, $chatId, $text);
    } else {
        processNonCommandMessage($telegram, $chatId);
    }
} else {
    processNonTextMessage($telegram, $chatId);
}