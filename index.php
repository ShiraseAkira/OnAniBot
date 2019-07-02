<?php
include('vendor/autoload.php'); //Подключаем библиотеку
use Telegram\Bot\Api;

$telegram = new Api('639677299:AAEIo8bfRnC5axKEUuJG1l_LuBSHLmSD3ao'); //Устанавливаем токен, полученный у BotFather
$result = $telegram->getWebhookUpdates(); //Передаем в переменную $result полную информацию о сообщении пользователя

$text = $result["message"]["text"]; //Текст сообщения
$chat_id = $result["message"]["chat"]["id"]; //Уникальный идентификатор пользователя
$name = $result["message"]["from"]["username"]; //Юзернейм пользователя
$keyboard = [["Hello World!"], ["Hello, username"], ["Next anime"]]; //Клавиатура

function getNextAnime(): string
{
    // create curl resource
    $ch = curl_init();
    // set url
    curl_setopt($ch, CURLOPT_URL, "https://shikimori.one/api/calendar");
    //return the transfer as a string
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    // $output contains the output string
    $output = curl_exec($ch);
    // close curl resource to free up system resources
    curl_close($ch);

    $data = json_decode($output);
    return $data[0]->anime->name;
}

if ($text) {
    if ($text == "/start") {
        $reply = "Добро пожаловать в бота!";
        $reply_markup = $telegram->replyKeyboardMarkup(['keyboard' => $keyboard, 'resize_keyboard' => true, 'one_time_keyboard' => false]);
        $telegram->sendMessage(['chat_id' => $chat_id, 'text' => $reply, 'reply_markup' => $reply_markup]);
    } elseif ($text == "Hello World!") {
        $reply = "Hello World!";
        $telegram->sendMessage(['chat_id' => $chat_id, 'text' => $reply]);
    } elseif ($text == "Hello, username") {
        if ($name) {
            $reply = "Hello, " . $name;
        } else {
            $reply = "Hello, anon";
        }
        $telegram->sendMessage(['chat_id' => $chat_id, 'text' => $reply]);
    } elseif ($text == "Next anime") {
        $reply = "Ближайшее выхожящее в эфир аниме: \"" . getNextAnime() . "\"";
        $telegram->sendMessage(['chat_id' => $chat_id, 'text' => $reply]);
    }
} else {
    $telegram->sendMessage(['chat_id' => $chat_id, 'text' => "Отправьте текстовое сообщение."]);
}