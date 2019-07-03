<?php
require_once ("database.php");
include('vendor/autoload.php'); //Подключаем библиотеку
use Telegram\Bot\Api;

$telegram = new Api('639677299:AAEIo8bfRnC5axKEUuJG1l_LuBSHLmSD3ao'); //Устанавливаем токен, полученный у BotFather
$result = $telegram->getWebhookUpdates(); //Передаем в переменную $result полную информацию о сообщении пользователя

$text = $result["message"]["text"]; //Текст сообщения
$chat_id = $result["message"]["chat"]["id"]; //Уникальный идентификатор пользователя
$name = $result["message"]["from"]["username"]; //Юзернейм пользователя
$keyboard = [["Посмотреть список онгоингов"], ["Посмотреть список отслеживаемого"]]; //Клавиатура

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

        $buttonindex = 1;
        $row = 0;
        $keyboard = [[]];
        $reply = "В данный момент выходят сериалы:".PHP_EOL;
        while($ongoing = $ongoingList->fetch_object()){
            $reply .= $buttonindex.") ". $ongoing->name.PHP_EOL;
            array_push($keyboard[$row], "/add ".$buttonindex);
            $buttonindex++;
            if (intdiv($buttonindex - 1, 5) AND !(($buttonindex-1) % 5)) {
                array_push($keyboard, []);
                $row++;
            }
        }
        $reply .= "Нажмите соответсвующую кнопку, чтобы добавить сериал в список отслеживаемого".PHP_EOL
            ."или введите /start для возврата.";
        $reply_markup = $telegram->replyKeyboardMarkup(['keyboard' => $keyboard, 'resize_keyboard' => true, 'one_time_keyboard' => true]);
        $telegram->sendMessage(['chat_id' => $chat_id, 'text' => $reply, 'reply_markup' => $reply_markup]);

    } elseif (substr($text, 0, "5") === "/add ") {
        $numberInList = (int)substr($text, 5);

        $database = getDatabaseConnection();
        $shikiidReply = getShikiidByNumberInList($database, $numberInList);
        $shikiidObj = $shikiidReply->fetch_object();
        $shikiid = $shikiidObj->shikiid;

        addToWatchlist($database, $shikiid, $chat_id);

        $reply = "Сериал из списка под номером ".$numberInList." был добавлен в список отслеживаемого";
        $telegram->sendMessage(['chat_id' => $chat_id, 'text' => $reply]);
    } elseif ($text == "Посмотреть список отслеживаемого") {
        $reply = "Список отслеживаемого";
        $telegram->sendMessage(['chat_id' => $chat_id, 'text' => $reply]);
    }
} else {
    $telegram->sendMessage(['chat_id' => $chat_id, 'text' => "Отправьте текстовое сообщение."]);
}