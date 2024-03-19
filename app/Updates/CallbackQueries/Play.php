<?php

namespace App\Updates\CallbackQueries;

use App\Models\User;
use App\Services\ServerLog;
use App\Services\TextString;
use App\Updates\Commands\Start;

class Play extends CallbackQuery {

    public function run() {
        ServerLog::log('Play by Notification > run');
        $this->bot->answerCallbackQuery($this->getId(), TextString::get('settings.loading'));
        $start = new Start($this->update, $this->bot, true);
        $start->chatType = $this->getChatType();
        $start->userId = $this->getUserId();
        $start->firstName = $this->getFirstName();
        $start->run();
    }

}
