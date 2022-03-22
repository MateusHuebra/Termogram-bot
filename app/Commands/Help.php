<?php

namespace App\Commands;

class Help extends Command {

    public function run($update, $bot) {
        $userId = $this->getUserId($update);
        $bot->sendMessage($userId, 'help');
    }

}