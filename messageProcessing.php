<?php
function processNonTextMessage(): void {
    global $telegram, $chat_id;
    $telegram->sendMessage(['chat_id' => $chat_id, 'text' => "Отправьте текстовое сообщение."]);
}