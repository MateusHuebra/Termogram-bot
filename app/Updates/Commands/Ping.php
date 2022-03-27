<?php

namespace App\Updates\Commands;

class Ping extends Command {

    public function run($update, $bot) {
        $this->dieIfUnallowedChatType($update, $bot, ['private', 'group', 'supergroup']);
        
        if($this->getChatType=='private') {
            $userId = $this->getUserId($update);
            $bot->sendMessage($userId, 'pong');
        } else {
            $chatId = $this->getChatId($update);
            $messageId = $this->getMessageId($update);
            $bot->sendMessage($chatId, 'pong', null, false, $messageId);
        }
    }

}