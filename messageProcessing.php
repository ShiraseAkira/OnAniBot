<?php
const ongoingListButton = "Посмотреть список онгоингов";
const watchListButton = "Посмотреть список отслеживаемого";

function processNonTextMessage($telegram, $chatId): void {
    $telegram->sendMessage(['chat_id' => $chatId, 'text' => "Отправьте текстовое сообщение."]);
}
function processNonCommandMessage($telegram, $chatId): void {
    $reply = "Используйте /start для начала работы с ботом".PHP_EOL."или /help для вызова справки.";
    $telegram->sendMessage(['chat_id' => $chatId, 'text' => $reply]);
}
function processHelpMessage($telegram, $chatId): void {
    $database = getDatabaseConnection();
    checkIfNewUserAndAdd($database, $chatId);
    $keyboard = [[ongoingListButton], [watchListButton]];

    $reply = "Добро пожаловать в бота!";
    $reply_markup = $telegram->replyKeyboardMarkup(['keyboard' => $keyboard, 'resize_keyboard' => true, 'one_time_keyboard' => false]);
    $telegram->sendMessage(['chat_id' => $chatId, 'text' => $reply, 'reply_markup' => $reply_markup]);
}