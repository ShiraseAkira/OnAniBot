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

function processStartCommand($telegram, $chatId): void {
    $database = getDatabaseConnection();
    checkIfNewUserAndAdd($database, $chatId);
    $keyboard = [[ongoingListButton], [watchListButton]];

    $reply = "Добро пожаловать в бота!";
    $reply_markup = $telegram->replyKeyboardMarkup(['keyboard' => $keyboard, 'resize_keyboard' => true, 'one_time_keyboard' => false]);
    $telegram->sendMessage(['chat_id' => $chatId, 'text' => $reply, 'reply_markup' => $reply_markup]);
}

function processHelpCommand($telegram, $chatId): void {
    $reply = "Добро пожаловать в бота!".PHP_EOL.
        "Он предназначерн для отслеживания выходящих в эфир anime сериалов.".PHP_EOL.
        "Используйте /start для начала работы с ботом.";
    $telegram->sendMessage(['chat_id' => $chatId, 'text' => $reply]);
}

function processWatchOngoingListCommand($telegram, $chatId): void {
    $database = getDatabaseConnection();
    $ongoingList = getOngoingList($database);

    $listIndex = 1;
    $reply = "В данный момент выходят сериалы:".PHP_EOL;
    while($ongoing = $ongoingList->fetch_object()){
        $reply .= $listIndex.") ".$ongoing->name." /add".$ongoing->shikiid.PHP_EOL;
        $listIndex++;
    }

    $reply .= "Нажмите соответсвующую команду(/addXXX), чтобы добавить сериал в список отслеживаемого";
    $telegram->sendMessage(['chat_id' => $chatId, 'text' => $reply]);
}