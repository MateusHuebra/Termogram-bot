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
        try {
            $this->bot->answerCallbackQuery($this->getId(), TextString::get('settings.loading'));
        } catch(Exception $e) {
            //
        }
        $start = new Start($this->update, $this->bot, true);
        $start->chatType = $this->getChatType();
        $start->userId = $this->getUserId();
        $start->firstName = $this->getFirstName();
        $start->run();
    }

}
