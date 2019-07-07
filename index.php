<?php
require_once ("database.php");
require_once ("messageProcessing.php");
include('vendor/autoload.php');
use Telegram\Bot\Api;

const TOKEN = "639677299:AAEIo8bfRnC5axKEUuJG1l_LuBSHLmSD3ao";
const START_COMMAND = "/start";
const HELP_COMMAND = "/help";
const WATCH_ONGOING_LIST_COMMAND = "Выходящие в текущем сезоне сериалы";
const WATCH_WATCHLIST_COMMAND = "Отслеживаемые сериалы";
const ADD_TO_WATCHLIST_COMMAND = "/add_";
const REMOVE_FROM_WATCHLIST_COMMAND = "/remove_";

$telegram = new Api(TOKEN);
$result = $telegram->getWebhookUpdates();

$text = $result["message"]["text"];
$chatId = $result["message"]["chat"]["id"];

if ($text) {
    if ($text == START_COMMAND) {
        processStartCommand($telegram, $chatId);
    } elseif ($text == HELP_COMMAND) {
        processHelpCommand($telegram, $chatId);
    } elseif ($text == WATCH_ONGOING_LIST_COMMAND) {
        processWatchOngoingListCommand($telegram, $chatId);
    } elseif ($text == WATCH_WATCHLIST_COMMAND) {
        processWatchWatchListCommand($telegram, $chatId);
    } elseif (!strpos($text, ADD_TO_WATCHLIST_COMMAND)) {
        processAddToWatchListCommand($telegram, $chatId, $text);
    } elseif (!strpos($text, REMOVE_FROM_WATCHLIST_COMMAND)) {
        processRemoveFromWatchListCommand($telegram, $chatId, $text);
    } else {
        processNonCommandMessage($telegram, $chatId);
    }
} else {
    processNonTextMessage($telegram, $chatId);
}