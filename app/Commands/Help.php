<?php

namespace App\Commands;

use App\Services\TextString;

class Help extends Command {

    public function run($update, $bot) {
        $userId = $this->getUserId($update);
        $bot->sendMessage($userId, TextString::get('settings.help'));
    }

}