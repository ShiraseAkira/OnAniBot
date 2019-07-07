<?php
const SHIKIMORI_URL = "https://shikimori.one";
const MESSAGE_LENGTH_CAP = 3000;
const ONGOING_LIST_BUTTON = "Выходящие в текущем сезоне сериалы";
const WATCHLIST_BUTTON = "Отслеживаемые сериалы";
const PARSE_MODE = "HTML";

const NON_TEXT_MESSAGE_REPLY = "Отправьте текстовое сообщение.";
const NON_COMMAND_MESSAGE_REPLY = "Используйте /start для начала работы с ботом или /help для вызова справки.";
const START_COMMAND_MESSAGE_REPLY = "Добро пожаловать в бота!";
const HELP_COMMAND_MESSAGE_REPLY = " Он предназначерн для отслеживания выходящих в эфир anime сериалов. Используйте /start для начала работы с ботом.";
const WATCH_ONGOING_LIST_COMMAND_REPLY_START = "В данный момент выходят сериалы:\n";
const WATCH_ONGOING_LIST_COMMAND_REPLY_END =  "\nИспользуйте соответсвующую команду, чтобы добавить сериал в список отслеживаемого или нажмите на \"о сериале...\", чтобы перейти на сраницу с информациейо сериале.";
const WATCH_WATCHLIST_COMMAND_REPLY_START = "В данный момент вы отслеживаете сериалы:\n";
const WATCH_WATCHLIST_COMMAND_REPLY_END = "\nИспользуйте соответсвующую команду, чтобы удалить сериал из списка отслеживаемого";
const WATCH_WATCHLIST_COMMAND_REPLY_EMPTY_LIST = "В данный момент вы не отслеживаете сериалов";
const ADD_TO_WATCHLIST_COMMAND_REPLY = " был добавлен в список отслеживаемого";
const REMOVE_FROM_WATCHLIST_COMMAND_REPLY = " был удален из списка отслеживаемого";

function processNonTextMessage($telegram, $chatId): void {
    $reply = NON_TEXT_MESSAGE_REPLY;
    $telegram->sendMessage([
                            'chat_id' => $chatId,
                            'text' => $reply
                            ]);
}

function processNonCommandMessage($telegram, $chatId): void {
    $reply = NON_COMMAND_MESSAGE_REPLY;
    $telegram->sendMessage([
                            'chat_id' => $chatId,
                            'text' => $reply
                            ]);
}

function processStartCommand($telegram, $chatId): void {
    $database = getDatabaseConnection();
    checkIfNewUserAndAdd($database, $chatId);
    $keyboard = [[ONGOING_LIST_BUTTON], [WATCHLIST_BUTTON]];

    $reply = START_COMMAND_MESSAGE_REPLY;
    $reply_markup = $telegram->replyKeyboardMarkup([
                                                    'keyboard' => $keyboard,
                                                    'resize_keyboard' => true,
                                                    'one_time_keyboard' => false
                                                    ]);
    $telegram->sendMessage([
                            'chat_id' => $chatId,
                            'text' => $reply,
                            'reply_markup' => $reply_markup
                            ]);
}

function processHelpCommand($telegram, $chatId): void {
    $reply = START_COMMAND_MESSAGE_REPLY.HELP_COMMAND_MESSAGE_REPLY;
    $telegram->sendMessage([
                            'chat_id' => $chatId,
                            'text' => $reply
                            ]);
}

function processWatchOngoingListCommand($telegram, $chatId): void {
    $database = getDatabaseConnection();
    $ongoingList = getOngoingList($database);
    $listIndex = 1;
    $reply = WATCH_ONGOING_LIST_COMMAND_REPLY_START;
    while($ongoing = $ongoingList->fetch_object()){
        $reply .= $listIndex.") ".$ongoing->name." /add_".$ongoing->shikiid." <a href='".SHIKIMORI_URL.
            $ongoing->url."'>о сериале...</a>".PHP_EOL;
        $listIndex++;
        if(strlen($reply) > MESSAGE_LENGTH_CAP) {
            $telegram->sendMessage([
                                    'chat_id' => $chatId,
                                    'text' => $reply,
                                    'parse_mode' => PARSE_MODE,
                                    'disable_web_page_preview' => true
                                    ]);
            $reply = "";
        }
    }
    $reply .= WATCH_ONGOING_LIST_COMMAND_REPLY_END;
    $telegram->sendMessage([
                            'chat_id' => $chatId,
                            'text' => $reply,
                            'parse_mode' => PARSE_MODE,
                            'disable_web_page_preview' => true
                            ]);
}

function processWatchWatchListCommand($telegram, $chatId): void {
    $database = getDatabaseConnection();
    $watchList = getWatchList($database, $chatId);

    $listIndex = 1;
    $reply = WATCH_WATCHLIST_COMMAND_REPLY_START;
    while($watch = $watchList->fetch_object()){
        $reply .= $listIndex.") ". $watch->name." /remove_".$watch->shikiid.PHP_EOL;
        $listIndex++;
        if(strlen($reply) > MESSAGE_LENGTH_CAP) {
            $telegram->sendMessage([
                                    'chat_id' => $chatId,
                                    'text' => $reply
                                    ]);
            $reply = "";
        }
    }
    $reply .= WATCH_WATCHLIST_COMMAND_REPLY_END;
    if($listIndex == 1){
        $reply = WATCH_WATCHLIST_COMMAND_REPLY_EMPTY_LIST;
    }
    $telegram->sendMessage([
                            'chat_id' => $chatId,
                            'text' => $reply
                            ]);
}

function processAddToWatchListCommand($telegram, $chatId, $text): void {
    $shikiId = (int)substr($text, 5);

    $database = getDatabaseConnection();
    addToWatchlist($database, $shikiId, $chatId);

    $animeNameSQL = getAnimeNameByShikiId($database, $shikiId);
    $animeNameObj = $animeNameSQL->fetch_object();

    $reply = $animeNameObj->name.ADD_TO_WATCHLIST_COMMAND_REPLY;
    $telegram->sendMessage([
                            'chat_id' => $chatId,
                            'text' => $reply
                            ]);
}

function processRemoveFromWatchListCommand($telegram, $chatId, $text): void {
    $shikiId = (int)substr($text, 8);

    $database = getDatabaseConnection();
    removeFromWatchlist($database, $shikiId, $chatId);

    $animeNameSQL = getAnimeNameByShikiId($database, $shikiId);
    $animeNameObj = $animeNameSQL->fetch_object();

    $reply = $animeNameObj->name.REMOVE_FROM_WATCHLIST_COMMAND_REPLY;
    $telegram->sendMessage([
                            'chat_id' => $chatId,
                            'text' => $reply
                            ]);
}