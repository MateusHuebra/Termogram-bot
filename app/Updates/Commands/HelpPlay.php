<?php

namespace App\Updates\Commands;

use App\Services\TextString;

class HelpPlay extends Command {

    public function run($update, $bot) {
        $this->dieIfUnallowedChatType($update, $bot, ['private', 'group', 'supergroup']);
        
        if($this->getChatType=='private') {
            $userId = $this->getUserId($update);
            $bot->sendMessage($userId, TextString::get('help.play'));
        } else {
            $chatId = $this->getChatId($update);
            $messageId = $this->getMessageId($update);
            $bot->sendMessage($chatId, TextString::get('help.play'), null, false, $messageId);
        }
    }

}