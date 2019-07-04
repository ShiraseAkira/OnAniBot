<?php
require_once ("database.php");
include('vendor/autoload.php');
use Telegram\Bot\Api;

$telegram = new Api('639677299:AAEIo8bfRnC5axKEUuJG1l_LuBSHLmSD3ao');
$result = $telegram->getWebhookUpdates();

$text = $result["message"]["text"];
$chat_id = $result["message"]["chat"]["id"];
$keyboard = [["Посмотреть список онгоингов"], ["Посмотреть список отслеживаемого"]];

if ($text) {
    if ($text == "/start") {
        $database = getDatabaseConnection();
        checkIfNewUserAndAdd($database, $chat_id);

        $reply = "Добро пожаловать в бота!";
        $reply_markup = $telegram->replyKeyboardMarkup(['keyboard' => $keyboard, 'resize_keyboard' => true, 'one_time_keyboard' => true]);
        $telegram->sendMessage(['chat_id' => $chat_id, 'text' => $reply, 'reply_markup' => $reply_markup]);
    } elseif ($text == "/help") {
        $reply = "Добро пожаловать в бота!\nОн предназначерн для отслеживания выходящих в эфир anime сериалов.";
        $telegram->sendMessage(['chat_id' => $chat_id, 'text' => $reply]);
    } elseif ($text == "Посмотреть список онгоингов") {
        $database = getDatabaseConnection();
        $ongoingList = getOngoingList($database);

        $animeindex = 1;
        $row = 0;
        $keyboard = [[]];
        $reply = "В данный момент выходят сериалы:".PHP_EOL;
        while($ongoing = $ongoingList->fetch_object()){
            $reply .= $animeindex.") ". $ongoing->name.PHP_EOL;
            array_push($keyboard[$row], "/add ".$animeindex);
            $animeindex++;
            if (intdiv($animeindex - 1, 5) AND !(($animeindex - 1) % 5)) {
                array_push($keyboard, []);
                $row++;
            }
        }
        $reply .= "Нажмите соответсвующую кнопку, чтобы добавить сериал в список отслеживаемого".PHP_EOL
            ."или введите /start для возврата.";
        $reply_markup = $telegram->replyKeyboardMarkup(['keyboard' => $keyboard, 'resize_keyboard' => true, 'one_time_keyboard' => true]);
        $telegram->sendMessage(['chat_id' => $chat_id, 'text' => $reply, 'reply_markup' => $reply_markup]);
    } elseif (substr($text, 0, 5) === "/add ") {
        $numberInList = (int)substr($text, 5);

        $database = getDatabaseConnection();
        $shikiidReply = getShikiidByNumberInList($database, $numberInList);
        $shikiidObj = $shikiidReply->fetch_object();
        $shikiid = $shikiidObj->shikiid;

        addToWatchlist($database, $shikiid, $chat_id);

        $reply = "Сериал из списка под номером ".$numberInList." был добавлен в список отслеживаемого";
        $telegram->sendMessage(['chat_id' => $chat_id, 'text' => $reply]);
    } elseif ($text == "Посмотреть список отслеживаемого") {
        $database = getDatabaseConnection();
        $watchList = getWatchList($database, $chat_id);

        $animeindex = 1;
        $row = 0;
        $keyboard = [[]];
        $reply = "В данный момент вы отслеживаете сериалы:".PHP_EOL;
        while($watch = $watchList->fetch_object()){
            $reply .= $animeindex.") ". $watch->name.PHP_EOL;
            array_push($keyboard[$row], "/remove ".$animeindex);
            $animeindex++;
            if (intdiv($animeindex - 1, 5) AND !(($animeindex - 1) % 5)) {
                array_push($keyboard, []);
                $row++;
            }
        }
        $reply .= "Нажмите соответсвующую кнопку, чтобы удалить сериал из списка отслеживаемого".PHP_EOL
            ."или введите /start для возврата.";
        $reply_markup = $telegram->replyKeyboardMarkup(['keyboard' => $keyboard, 'resize_keyboard' => true, 'one_time_keyboard' => true]);
        $telegram->sendMessage(['chat_id' => $chat_id, 'text' => $reply, 'reply_markup' => $reply_markup]);
    } elseif (substr($text, 0, 8) === "/remove ") {
        $numberInList = (int)substr($text, 8);

        $database = getDatabaseConnection();
        $watchListReply = getWatchlistItemByNumberInList($database, $numberInList);
        $watchListObj = $watchListReply ->fetch_object();
        $watchlistid = $watchListObj->watchlistid;

        removeFromWatchlist($database, $watchlistid);

        $reply = "Сериал из списка под номером ".$numberInList." был удален из списока отслеживаемого";
        $telegram->sendMessage(['chat_id' => $chat_id, 'text' => $reply]);
    } else {
        $reply = "Используйте команду /start для начала работы с ботом".PHP_EOL."или /help для вызова справки";
        $telegram->sendMessage(['chat_id' => $chat_id, 'text' => $reply]);
    }
} else {
    $telegram->sendMessage(['chat_id' => $chat_id, 'text' => "Отправьте текстовое сообщение."]);
}