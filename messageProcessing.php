<?php
const shikimoriUrl = "https://shikimori.one";
const messageLengthCap = 3000;
const ongoingListButton = "Посмотреть список онгоингов";
const watchListButton = "Посмотреть список отслеживаемого";

function processNonTextMessage($telegram, $chatId): void {
    $reply = "Отправьте текстовое сообщение.";
    $telegram->sendMessage(['chat_id' => $chatId, 'text' => $reply]);
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
    $parsemode = "HTML";

    $listIndex = 1;
    $reply = "В данный момент выходят сериалы:".PHP_EOL;
    while($ongoing = $ongoingList->fetch_object()){
        $reply .= $listIndex.") ".$ongoing->name." /add_".$ongoing->shikiid." <a href='".shikimoriUrl.
            $ongoing->url."'>подробнее...</a>".PHP_EOL;
        $listIndex++;
        if(strlen($reply) > messageLengthCap) {
            $telegram->sendMessage(['chat_id' => $chatId, 'text' => $reply, 'parse_mode' => $parsemode,
                'disable_web_page_preview' => true]);
            $reply = "";
        }
    }

    $reply .= PHP_EOL."Используйте соответсвующую команду, чтобы добавить сериал в список отслеживаемого".PHP_EOL.
        "или нажмите на \"подробнее...\", чтобы перейти на сраницу с информацией.";
    $telegram->sendMessage(['chat_id' => $chatId, 'text' => $reply, 'parse_mode' => $parsemode,
        'disable_web_page_preview' => true]);
}

function processWatchWatchListCommand($telegram, $chatId): void {
    $database = getDatabaseConnection();
    $watchList = getWatchList($database, $chatId);

    $listIndex = 1;
    $reply = "В данный момент вы отслеживаете сериалы:".PHP_EOL;
    while($watch = $watchList->fetch_object()){
        $reply .= $listIndex.") ". $watch->name." /remove_".$watch->shikiid.PHP_EOL;
        $listIndex++;
        if(strlen($reply) > messageLengthCap) {
            $telegram->sendMessage(['chat_id' => $chatId, 'text' => $reply]);
            $reply = "";
        }
    }
    $reply .= PHP_EOL."Используйте соответсвующую команду, чтобы удалить сериал из списка отслеживаемого";
    $telegram->sendMessage(['chat_id' => $chatId, 'text' => $reply]);
}

function processAddToWatchListCommand($telegram, $chatId, $text): void {
    $shikiId = (int)substr($text, 5);

    $database = getDatabaseConnection();
    addToWatchlist($database, $shikiId, $chatId);

    $animeNameSQL = getAnimeNameByShikiId($database, $shikiId);
    $animeNameObj = $animeNameSQL->fetch_object();

    $reply = "Сериал ".$animeNameObj->name." был добавлен в список отслеживаемого";
    $telegram->sendMessage(['chat_id' => $chatId, 'text' => $reply]);
}

function processRemoveFromWatchListCommand($telegram, $chatId, $text): void {
    $shikiId = (int)substr($text, 8);

    $database = getDatabaseConnection();
    removeFromWatchlist($database, $shikiId, $chatId);

    $animeNameSQL = getAnimeNameByShikiId($database, $shikiId);
    $animeNameObj = $animeNameSQL->fetch_object();

    $reply = "Сериал ".$animeNameObj->name." был удален из списка отслеживаемого";
    $telegram->sendMessage(['chat_id' => $chatId, 'text' => $reply]);
}