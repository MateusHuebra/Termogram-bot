<?php

namespace App\Updates\CallbackQueries;

use App\Models\User;
use App\Services\ServerLog;
use App\Services\TextString;
use App\Updates\Commands\Start;
use Exception;

class Play extends CallbackQuery {

    public function run() {
        ServerLog::log('Play by Notification > run');
        $message = $this->bot->sendMessage($this->getUserId(), '/start');
        try {
            $this->bot->answerCallbackQuery($this->getId(), TextString::get('settings.loading'));
            $start = new Start($this->update, $this->bot, true);
            $start->chatType = $this->getChatType();
            $start->userId = $this->getUserId();
            $start->firstName = $this->getFirstName();
            $start->username = $this->getUsername();
            if($start->run() == 'started') {
                $this->bot->deleteMessage($this->getUserId(), $message->getMessageId());
            }
        } catch(Exception $e) {
            $this->bot->sendMessage(env('TG_MYID'), 'Play by Notification error: '.$e->getMessage());
        }
        ServerLog::log('Play by Notification > end');
    }

}
