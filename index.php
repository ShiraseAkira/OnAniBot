<?php
include('vendor/autoload.php'); //Подключаем библиотеку
use Telegram\Bot\Api;

$telegram = new Api('639677299:AAEIo8bfRnC5axKEUuJG1l_LuBSHLmSD3ao'); //Устанавливаем токен, полученный у BotFather
$result = $telegram->getWebhookUpdates(); //Передаем в переменную $result полную информацию о сообщении пользователя

$text = $result["message"]["text"]; //Текст сообщения
$chat_id = $result["message"]["chat"]["id"]; //Уникальный идентификатор пользователя
$name = $result["message"]["from"]["username"]; //Юзернейм пользователя
$keyboard = [["Посмотреть список онгоингов"], ["Посмотреть список отслеживаемого"]]; //Клавиатура

if($result->isType('callback_query')) {
    $telegram->sendMessage(['chat_id' => $result->callbackQuery->from->id, 'text' => $result->callbackQuery->data]);
}

if ($text) {
    if ($text == "/start") {
        $mysqlli = new mysqli("eu-cdbr-west-02.cleardb.net", "b2b48db1e8befd",
            "8113a8b7", "heroku_717c9367403bbb5");
        if($mysqlli->connect_errno) {
            error_log("Ошибка: " . $mysqlli->connect_errno);
        }

        $sql = "INSERT IGNORE INTO `users`(
                    `chatid`
                    )
            VALUES (
            '".$chat_id."'
            )";
        if($mysqlli->query($sql) === FALSE) {
            error_log("Error: ".$sql.PHP_EOL.$mysqlli->error);
        }

        $inline_keyboard = json_encode([
            'inline_keyboard'=>[
                [
                ['text'=>'Посмотреть список онгоингов', 'callback_data'=>'1'],
                ['text'=>'Посмотреть список онгоингов', 'callback_data'=>'2']
                ],
            ]
        ]);
        $reply = "Добро пожаловать в бота!";
//        $reply_markup = $telegram->replyKeyboardMarkup(['keyboard' => $keyboard, 'resize_keyboard' => true, 'one_time_keyboard' => false]);
        $telegram->sendMessage(['chat_id' => $chat_id, 'text' => $reply, 'reply_markup' =>  $inline_keyboard]);
    } elseif ($text == "/help") {
        $reply = "Добро пожаловать в бота!\nОн предназначерн для отслеживания выходящих в эфир anime сериалов.";
        $telegram->sendMessage(['chat_id' => $chat_id, 'text' => $reply]);
    } elseif ($text == "Посмотреть список онгоингов") {

        $mysqlli = new mysqli("eu-cdbr-west-02.cleardb.net", "b2b48db1e8befd",
            "8113a8b7", "heroku_717c9367403bbb5");
        if($mysqlli->connect_errno) {
            error_log("Ошибка: " . $mysqlli->connect_errno);
        }

        $sql = "SELECT anime.name FROM `anime` LIMIT 5";

        if(!$result = $mysqlli->query($sql)) {
            error_log("Error: ".$sql.PHP_EOL.$mysqlli->error);
        }

        $reply = "В данный момент выходят сериалы:".PHP_EOL;
        while($message = $result->fetch_object()){
            $reply .= $message->name.PHP_EOL;
        }
        $telegram->sendMessage(['chat_id' => $chat_id, 'text' => $reply]);

    } elseif ($text == "Посмотреть список отслеживаемого") {
        $reply = "Список отслеживаемого";
        $telegram->sendMessage(['chat_id' => $chat_id, 'text' => $reply]);
    }
} else {
    $telegram->sendMessage(['chat_id' => $chat_id, 'text' => "Отправьте текстовое сообщение."]);
}