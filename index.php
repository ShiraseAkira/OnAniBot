<?php
include('vendor/autoload.php'); //Подключаем библиотеку
use Telegram\Bot\Api;

$telegram = new Api('639677299:AAEIo8bfRnC5axKEUuJG1l_LuBSHLmSD3ao'); //Устанавливаем токен, полученный у BotFather
$result = $telegram->getWebhookUpdates(); //Передаем в переменную $result полную информацию о сообщении пользователя

$text = $result["message"]["text"]; //Текст сообщения
$chat_id = $result["message"]["chat"]["id"]; //Уникальный идентификатор чата
$user_id = $result["message"]["user"]["id"]; //Уникальный идентификатор пользователя
$name = $result["message"]["from"]["username"]; //Юзернейм пользователя
$keyboard = [["Посмотреть список онгоингов"], ["Посмотреть список отслеживаемого"], ["Hello, username"]]; //Клавиатура

if ($text) {
    if ($text == "/start") {
        $mysqlli = new mysqli("eu-cdbr-west-02.cleardb.net", "b2b48db1e8befd",
            "8113a8b7", "heroku_717c9367403bbb5");
        if($mysqlli->connect_errno) {
            error_log("Ошибка: " . $mysqlli->connect_errno);
        }

        $sql = "INSERT IGNORE INTO `users`(
                    `chatid`,
                    `userid`
                    )
            VALUES (
            '".$chat_id."',
            '".$user_id."'
            )";
        if($mysqlli->query($sql) === FALSE) {
            error_log("Error: ".$sql.PHP_EOL.$mysqlli->error);
        }

        $reply = "Добро пожаловать в бота!";
        $reply_markup = $telegram->replyKeyboardMarkup(['keyboard' => $keyboard, 'resize_keyboard' => true, 'one_time_keyboard' => false]);
        $telegram->sendMessage(['chat_id' => $chat_id, 'text' => $reply, 'reply_markup' => $reply_markup]);
    } elseif ($text == "/help") {
        $reply = "Добро пожаловать в бота!\nОн предназначерн для отслеживания выходящих в эфир anime сериалов.";
        $telegram->sendMessage(['chat_id' => $chat_id, 'text' => $reply]);
    } elseif ($text == "Hello, username") {
        if ($name) {
            $reply = "Hello, " . $name;
        } else {
            $reply = "Hello, anon";
        }
        $telegram->sendMessage(['chat_id' => $chat_id, 'text' => $reply]);
    }
} else {
    $telegram->sendMessage(['chat_id' => $chat_id, 'text' => "Отправьте текстовое сообщение."]);
}