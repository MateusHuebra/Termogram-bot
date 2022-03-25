<?php

namespace App\Updates\Commands;

use App\Services\TextString;

class HelpNotifications extends Command {

    public function run($update, $bot) {
        $userId = $this->getUserId($update);
        $bot->sendMessage($userId, TextString::get('help.notifications'));
    }

}